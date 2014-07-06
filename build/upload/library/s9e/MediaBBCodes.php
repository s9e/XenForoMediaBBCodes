<?php

/**
* @copyright Copyright (c) 2013-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

class s9e_MediaBBCodes
{
	/**
	* Path to a cache dir, used to cache scraped pages
	*/
	public static $cacheDir;

	public static function install()
	{
	}

	public static function match($url, $regexps, $scrapes)
	{
		$vars = array();

		if (!empty($regexps))
		{
			$vars = self::getNamedCaptures($url, $regexps);
		}

		foreach ($scrapes as $scrape)
		{
			$scrapeVars = array();

			$skip = true;
			foreach ($scrape['match'] as $regexp)
			{
				if (preg_match($regexp, $url, $m))
				{
					// Add the named captures to the available vars
					$scrapeVars += $m;

					$skip = false;
				}
			}

			if ($skip)
			{
				continue;
			}

			if (isset($scrape['url']))
			{
				// Add the vars from non-scrape "extract" regexps
				$scrapeVars += $vars;

				// Add the original URL
				if (!isset($scrapeVars['url']))
				{
					$scrapeVars['url'] = $url;
				}

				// Replace {@var} tokens in the URL
				$scrapeUrl = preg_replace_callback(
					'#\\{@(\\w+)\\}#',
					function ($m) use ($scrapeVars)
					{
						return (isset($scrapeVars[$m[1]])) ? $scrapeVars[$m[1]] : '';
					},
					$scrape['url']
				);
			}
			else
			{
				// Use the same URL for scraping
				$scrapeUrl = $url;
			}

			// Overwrite vars extracted from URL with vars extracted from content
			$vars = array_merge($vars, self::scrape($scrapeUrl, $scrape['extract']));
		}

		// No vars = no match
		if (empty($vars))
		{
			// NOTE: we return the URL to sidestep a bug in XenForo that occurs when the match
			//       callback returns false and there is no "id" capture in the site's regexp
			return $url;
		}

		// If there's only one capture named "id" we store its value as-is
		$keys = array_keys($vars);
		if ($keys === array('id'))
		{
			return $vars['id'];
		}

		// If there's only one capture named "url" and it looks like a URL, we store its value as-is
		if ($keys === array('url') && preg_match('#^\\w+://#', $vars['url']))
		{
			return $vars['url'];
		}

		// If there are more than one capture, or it's not named "id", we store it as a series of
		// URL-encoded key=value pairs
		$pairs = array();
		ksort($vars);
		foreach ($vars as $k => $v)
		{
			if ($v !== '')
			{
				$pairs[] = urlencode($k) . '=' . urlencode($v);
			}
		}

		// NOTE: XenForo silently nukes the mediaKey if it contains any HTML special characters,
		//       that's why we use ; rather than the standard &
		return implode(';', $pairs);
	}

	public static function embed($mediaKey, $site)
	{
		// If the value looks like a URL, we copy its value to the "url" var
		if (preg_match('#^\\w+://#', $mediaKey))
		{
			$vars['url'] = $mediaKey;
		}

		// If the value looks like a series of key=value pairs, add them to $vars
		if (preg_match('(^(\\w+=[^;]*)(?>;(?1))*$)', $mediaKey))
		{
			$vars = array();
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}

		// The value is used as the "id" var if it hasn't been defined already
		if (!isset($vars['id']))
		{
			$vars['id'] = $mediaKey;
		}

		// No vars = no match, return a link to the content, or the BBCode as text
		if (empty($vars))
		{
			$mediaKey = htmlspecialchars($mediaKey);

			return (preg_match('(^https?://)', $mediaKey))
				? "<a href=\"$mediaKey\">$mediaKey</a>"
				: "[media={$site['media_site_id']}]{$mediaKey}[/media]";
		}

		// Prepare the HTML
		$html = $site['embed_html'];

		// Test whether this particular site has its own renderer
		$html = preg_replace_callback(
			'(<!-- (' . __CLASS__ . '::render\\w+)\\((?:(\\d+), *(\\d+))?\\) -->)',
			function ($m) use ($vars)
			{
				$callback = $m[1];

				if (!is_callable($callback))
				{
					return $m[0];
				}

				$html = call_user_func($callback, $vars);

				if (isset($m[2], $m[3]))
				{
					$html = preg_replace('/( width=")[^"]*/',  '${1}' . $m[2], $html);
					$html = preg_replace('/( height=")[^"]*/', '${1}' . $m[3], $html);
				}

				return $html;
			},
			$html,
			-1,
			$cnt
		);

		if ($cnt)
		{
			return $html;
		}

		// Otherwise use the configured template
		return preg_replace_callback(
			// Interpolate {$id} and other {$vars}
			'(\\{\\$([a-z]+)\\})',
			function ($m) use ($vars)
			{
				return (isset($vars[$m[1]])) ? htmlspecialchars($vars[$m[1]]) : '';
			},
			$site['embed_html']
		);
	}

	protected static function scrape($url, $regexps)
	{
		// Return the content from the cache if applicable
		if (isset(self::$cacheDir) && file_exists(self::$cacheDir))
		{
			$cacheFile = self::$cacheDir . '/http.' . crc32($url) . '.gz';

			if (file_exists($cacheFile))
			{
				$page = file_get_contents('compress.zlib://' . $cacheFile);
			}
		}

		if (empty($page))
		{
			$page = @file_get_contents(
				'compress.zlib://' . $url,
				false,
				stream_context_create(array(
					'http' => array(
						'header' => 'Accept-Encoding: gzip'
					)
				))
			);

			if ($page && isset($cacheFile))
			{
				file_put_contents($cacheFile, gzencode($page, 9));
			}
		}

		return self::getNamedCaptures($page, $regexps);
	}

	protected static function getNamedCaptures($string, $regexps)
	{
		$vars = array();

		foreach ($regexps as $regexp)
		{
			if (preg_match($regexp, $string, $m))
			{
				foreach ($m as $k => $v)
				{
					// Add named captures to the vars without overwriting existing vars
					if (!is_numeric($k) && !isset($vars[$k]))
					{
						$vars[$k] = $v;
					}
				}
			}
		}

		return $vars;
	}

	public static function renderAmazon($vars)
	{
		$vars += array('id' => null, 'tld' => null);

		$html='<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm';if(isset($vars['tld'])&&(strpos('cadefritjpuk',$vars['tld'])!==false))$html.='-'.htmlspecialchars($vars['tld'],2);$html.='.amazon.';if($vars['tld']==='jp'||$vars['tld']==='uk')$html.='co.'.htmlspecialchars($vars['tld'],2);elseif(isset($vars['tld'])&&(strpos('cadefrit',$vars['tld'])!==false))$html.=htmlspecialchars($vars['tld'],2);else$html.='com';$html.='/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins='.htmlspecialchars($vars['id'],2).'&amp;o=';if($vars['tld']==='ca')$html.='15';elseif($vars['tld']==='de')$html.='3';elseif($vars['tld']==='fr')$html.='8';elseif($vars['tld']==='it')$html.='29';elseif($vars['tld']==='jp')$html.='9';elseif($vars['tld']==='uk')$html.='2';else$html.='1';$html.='&amp;t=';if(!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG,2);else$html.='_';$html.='"></iframe>';

		return $html;
	}

	public static function matchAmazon($url)
	{
		$regexps = array('#(?=.*?amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)).*?/(?:dp|gp/product)/(?\'id\'[A-Z0-9]+)#', '#(?=.*?amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)).*?amazon\\.(?:co\\.)?(?\'tld\'ca|de|fr|it|jp|uk)#');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderAudiomack($vars)
	{
		$vars += array('id' => null, 'mode' => null);

		$html='<iframe width="100%" allowfullscreen="" frameborder="0" scrolling="no" height="';if($vars['mode']==='album')$html.='352';else$html.='144';$html.='" src="//www.audiomack.com/embed3';if($vars['mode']==='album')$html.='-album';$html.='/'.htmlspecialchars($vars['id'],2).'"></iframe>';

		return $html;
	}

	public static function matchAudiomack($url)
	{
		$regexps = array('!audiomack\\.com/(?\'mode\'album|song)/(?\'id\'[-\\w]+/[-\\w]+)!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderBandcamp($vars)
	{
		$vars += array('album_id' => null, 'track_id' => null, 'track_num' => null);

		$html='<iframe width="400" height="'.htmlspecialchars((isset($vars['track_id'])||isset($vars['track_num'])?42:120),2).'" allowfullscreen="" frameborder="0" scrolling="no" src="//bandcamp.com/EmbeddedPlayer/';if(isset($vars['album_id'])){$html.='album='.htmlspecialchars($vars['album_id'],2).'/size=';if(isset($vars['track_num']))$html.='small/t='.htmlspecialchars($vars['track_num'],2);else$html.='medium';}else$html.='track='.htmlspecialchars($vars['track_id'],2).'/size=small';$html.='"></iframe>';

		return $html;
	}

	public static function matchBandcamp($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!bandcamp\\.com/album/.!'),
				'extract' => array('!/album=(?\'album_id\'\\d+)!')
			),
			array(
				'match'   => array('!bandcamp\\.com/track/.!'),
				'extract' => array('!"album_id":(?\'album_id\'\\d+)!', '!"track_num":(?\'track_num\'\\d+)!', '!/track=(?\'track_id\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchBlip($url)
	{
		$regexps = array('!blip\\.tv/play/(?\'id\'[\\w+%/_]+)!');
		$scrapes = array(
			array(
				'match'   => array('!blip\\.tv/[^/]+/[^/]+-\\d+$!'),
				'extract' => array('!blip\\.tv/play/(?\'id\'[\\w%+/_]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderCbsnews($vars)
	{
		$vars += array('id' => null, 'pid' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="';if(isset($vars['pid']))$html.='http://www.cbsnews.com/common/video/cbsnews_player.swf';else$html.='http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf';$html.='"><param name="allowfullscreen" value="true"><param name="flashvars" value="';if(isset($vars['pid']))$html.='pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2);else$html.='si=254&amp;contentValue='.htmlspecialchars($vars['id'],2);$html.='"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="';if(isset($vars['pid']))$html.='http://www.cbsnews.com/common/video/cbsnews_player.swf';else$html.='http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf';$html.='" flashvars="';if(isset($vars['pid']))$html.='pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2);else$html.='si=254&amp;contentValue='.htmlspecialchars($vars['id'],2);$html.='"></object>';

		return $html;
	}

	public static function matchCbsnews($url)
	{
		$regexps = array('#cbsnews\\.com/video/watch/\\?id=(?\'id\'[0-9]+)#');
		$scrapes = array(
			array(
				'match'   => array('#cbsnews\\.com/videos/(?!watch/)#'),
				'extract' => array('#"pid":"(?\'pid\'\\w+)"#')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchColbertnation($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!thecolbertreport\\.cc\\.com/videos/!'),
				'extract' => array('!(?\'id\'mgid:arc:video:colbertnation\\.com:[-0-9a-f]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchComedycentral($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!c(?:c|omedycentral)\\.com/video-clips/!'),
				'extract' => array('!(?\'id\'mgid:arc:video:comedycentral\\.com:[-\\w]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchDailyshow($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!thedailyshow\\.c(?:c\\.c)?om/(?:collection|extended-interviews|videos|watch)/!'),
				'extract' => array('!(?\'id\'mgid:arc:(?:playlist|video):thedailyshow\\.com:[-0-9a-f]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderEbay($vars)
	{
		$vars += array('id' => null, 'itemid' => null, 'lang' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="355" height="300" data="http://togo.ebay.com/togo/togo.swf?2008013100"><param name="allowfullscreen" value="true"><param name="flashvars" value="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=';if(isset($vars['itemid']))$html.=htmlspecialchars($vars['itemid'],2);else$html.=htmlspecialchars($vars['id'],2);if(isset($vars['lang']))$html.='&amp;lang='.htmlspecialchars(strtr($vars['lang'],'_','-'),2);$html.='"><embed type="application/x-shockwave-flash" src="http://togo.ebay.com/togo/togo.swf?2008013100" width="355" height="300" allowfullscreen="" flashvars="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=';if(isset($vars['itemid']))$html.=htmlspecialchars($vars['itemid'],2);else$html.=htmlspecialchars($vars['id'],2);if(isset($vars['lang']))$html.='&amp;lang='.htmlspecialchars(strtr($vars['lang'],'_','-'),2);$html.='"></object>';

		return $html;
	}

	public static function matchEbay($url)
	{
		$regexps = array('#(?=.*?ebay\\.(?:at|c(?:a|o(?:\\.uk|m(?>\\.au)?))|de|es|fr|i[nt])).*?ebay.[\\w.]+/itm/(?:[-\\w]+/)?(?\'id\'\\d+)#', '#(?=.*?ebay\\.(?:at|c(?:a|o(?:\\.uk|m(?>\\.au)?))|de|es|fr|i[nt])).*?[?&]item=(?\'id\'\\d+)#');
		$scrapes = array(
			array(
				'match'   => array('#ebay\\.(?!com/)#'),
				'extract' => array('#"locale":"(?\'lang\'[\\w_]+)"#')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchEspn($url)
	{
		$regexps = array('#(?=.*?espn\\.go\\.com).*?\\?id=(?\'cms\'deportes|espn):(?\'id\'\\d+)#', '#(?=.*?espn\\.go\\.com).*?\\?v?id=(?\'id\'\\d+)#', '#(?=.*?espn\\.go\\.com).*?(?\'cms\'deportes|espn(?!d))#');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderFacebook($vars)
	{
		$vars += array('id' => null, 'mode' => null);

		$html='<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="';if($vars['mode']==='post')$html.='//s9e.github.io/iframe/facebook.min.html#';else$html.='https://www.facebook.com/video/embed?video_id=';$html.=htmlspecialchars($vars['id'],2).'"';if($vars['mode']==='post')$html.=' onload="window.addEventListener(\'message\',function(a){/^https?:\\/\\/s9e\\.github\\.io$/.test(a.origin)&amp;&amp;a.data.url&amp;&amp;a.data.height&amp;&amp;src==a.data.url&amp;&amp;(style.height=a.data.height+\'px\')});contentWindow.postMessage(\'s9e:init\',\'*\')"';$html.='></iframe>';

		return $html;
	}

	public static function matchFacebook($url)
	{
		$regexps = array('!facebook\\.com/(?\'mode\'photo|video)(?:/video)?\\.php\\?v=(?\'id\'\\d+)!', '!facebook\\.com/[\\w.]+/(?\'mode\'post)s/(?\'id\'\\d+)!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchGametrailers($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!gametrailers\\.com/(?:full-episode|review|video)s/!'),
				'extract' => array('!(?\'id\'mgid:arc:(?:episode|video):gametrailers\\.com:[-\\w]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderGetty($vars)
	{
		$vars += array('et' => null, 'height' => null, 'id' => null, 'sig' => null, 'width' => null);

		$html='<iframe width="'.htmlspecialchars($vars['width'],2).'" height="'.htmlspecialchars((49+$vars['height']),2).'" src="//embed.gettyimages.com/embed/'.htmlspecialchars($vars['id'],2).'?et='.htmlspecialchars($vars['et'],2).'&amp;sig='.htmlspecialchars($vars['sig'],2).'" allowfullscreen="" frameborder="0" scrolling="no"></iframe>';

		return $html;
	}

	public static function matchGetty($url)
	{
		$regexps = array('!gty\\.im/(?\'id\'\\d+)!', '!(?=.*?g(?:ettyimages\\.(?:c(?:n|o(?:\\.(?>jp|uk)|m(?>\\.au)?))|d[ek]|es|fr|i[et]|nl|pt|[bs]e)|ty\\.im)).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?\'id\'\\d+)!');
		$scrapes = array(
			array(
				'url'     => 'http://embed.gettyimages.com/preview/{@id}',
				'match'   => array('//'),
				'extract' => array('!"height":[ "]*(?\'height\'\\d+)!', '!"width":[ "]*(?\'width\'\\d+)!', '!et=(?\'et\'[-=\\w]+)!', '!sig=(?\'sig\'[-=\\w]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchGfycat($url)
	{
		$regexps = array('!gfycat\\.com/(?\'id\'\\w+)!');
		$scrapes = array(
			array(
				'url'     => 'http://gfycat.com/{@id}',
				'match'   => array('//'),
				'extract' => array('!gfyHeight[ ="]+(?\'height\'\\d+)!', '!gfyWidth[ ="]+(?\'width\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchGoogleplus($url)
	{
		$regexps = array('!//plus\\.google\\.com/(?:\\+\\w+|(?\'oid\'\\d+))/posts/(?\'pid\'\\w+)!');
		$scrapes = array(
			array(
				'match'   => array('!//plus\\.google\\.com/\\+[^/]+/posts/\\w!'),
				'extract' => array('!oid="?(?\'oid\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderGrooveshark($vars)
	{
		$vars += array('playlistid' => null, 'songid' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="'.htmlspecialchars((isset($vars['songid'])?40:250),2).'" data="//grooveshark.com/'.htmlspecialchars((isset($vars['songid'])?'songW':'w'),2).'idget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID='.htmlspecialchars($vars['playlistid'],2).'&amp;songID='.htmlspecialchars($vars['songid'],2).'"><embed type="application/x-shockwave-flash" src="//grooveshark.com/'.htmlspecialchars((isset($vars['songid'])?'songW':'w'),2).'idget.swf" width="250" height="'.htmlspecialchars((isset($vars['songid'])?40:250),2).'" allowfullscreen="" flashvars="playlistID='.htmlspecialchars($vars['playlistid'],2).'&amp;songID='.htmlspecialchars($vars['songid'],2).'"></object>';

		return $html;
	}

	public static function matchGrooveshark($url)
	{
		$regexps = array('%grooveshark\\.com(?:/#!?)?/playlist/[^/]+/(?\'playlistid\'[0-9]+)%');
		$scrapes = array(
			array(
				'url'     => 'http://grooveshark.com/s/{@path}',
				'match'   => array('%grooveshark\\.com(?:/#!?)?/s/(?\'path\'[^/]+/.+)%'),
				'extract' => array('%songID=(?\'songid\'[0-9]+)%')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchHulu($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!hulu\\.com/watch/!'),
				'extract' => array('!eid=(?\'id\'[-\\w]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchIndiegogo($url)
	{
		$regexps = array('!indiegogo\\.com/projects/(?\'id\'[0-9]+)$!');
		$scrapes = array(
			array(
				'match'   => array('!indiegogo\\.com/projects/.!'),
				'extract' => array('!indiegogo\\.com/projects/(?\'id\'[0-9]+)/!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchInternetarchive($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!archive\\.org/details/!'),
				'extract' => array('!meta property="twitter:player" content="https://archive.org/embed/(?\'id\'[^/"]+)!', '!meta property="og:video:width" content="(?\'width\'\\d+)!', '!meta property="og:video:height" content="(?\'height\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderKickstarter($vars)
	{
		$vars += array('id' => null, 'video' => null);

		$html='';if(isset($vars['video']))$html.='<iframe width="480" height="360" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/video.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>';else$html.='<iframe width="220" height="380" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>';

		return $html;
	}

	public static function matchKickstarter($url)
	{
		$regexps = array('!kickstarter\\.com/projects/(?\'id\'[^/]+/[^/?]+)(?:/widget/(?:(?\'card\'card)|(?\'video\'video)))?!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchMixcloud($url)
	{
		$regexps = array('@mixcloud\\.com/(?!categories|tag)(?\'id\'[-\\w]+/[^/&]+)/@');
		$scrapes = array(
			array(
				'match'   => array('@//i\\.mixcloud\\.com/\\w+$@'),
				'extract' => array('@link rel="canonical" href="https?://[^/]+/(?\'id\'[-\\w]+/[^/&]+)/@')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchPodbean($url)
	{
		$regexps = array('!podbean\\.com/site/player/index/pid/\\d+/eid/(?\'id\'\\d+)!');
		$scrapes = array(
			array(
				'match'   => array('!podbean\\.com/e/!'),
				'extract' => array('!embed/postId/(?\'id\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchRdio($url)
	{
		$regexps = array('!rd\\.io/./(?\'id\'\\w+)!');
		$scrapes = array(
			array(
				'url'     => 'http://www.rdio.com/api/oembed/?url={@url}',
				'match'   => array('!rdio\\.com/.*?(?:playlist|track)!'),
				'extract' => array('!rd\\.io/./(?\'id\'\\w+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchRutube($url)
	{
		$regexps = array('!rutube\\.ru/tracks/(?\'id\'[0-9]+)!');
		$scrapes = array(
			array(
				'match'   => array('!rutube\\.ru/video/[0-9a-f]{32}!'),
				'extract' => array('!rutube\\.ru/play/embed/(?\'id\'[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchSlideshare($url)
	{
		$regexps = array('!slideshare\\.net/[^/]+/[-\\w]+-(?\'id\'[0-9]{6,})$!');
		$scrapes = array(
			array(
				'match'   => array('!slideshare\\.net/[^/]+/\\w!'),
				'extract' => array('!"presentationId":(?\'id\'[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderSoundcloud($vars)
	{
		$vars += array('id' => null, 'playlist_id' => null, 'secret_token' => null, 'track_id' => null);

		$html='<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=';if(isset($vars['secret_token'])&&isset($vars['playlist_id']))$html.='https://api.soundcloud.com/playlists/'.htmlspecialchars($vars['playlist_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);elseif(isset($vars['secret_token'])&&isset($vars['track_id']))$html.='https://api.soundcloud.com/tracks/'.htmlspecialchars($vars['track_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);else{if((strpos($vars['id'],'://')===false))$html.='https://soundcloud.com/';$html.=htmlspecialchars($vars['id'],2);if(isset($vars['secret_token']))$html.='&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);}$html.='"></iframe>';

		return $html;
	}

	public static function matchSoundcloud($url)
	{
		$regexps = array('@(?\'id\'https?://(?:(?:api\\.soundcloud\\.com/(?:playlist|track)s/\\d+)|soundcloud\\.com/[^/]+/(?:sets/)?[^/]+)(?:(?:\\?secret_token=|/(?=s-))(?\'secret_token\'[-\\w]+))?|^[^/]+/[^/]+$)@');
		$scrapes = array(
			array(
				'url'     => 'https://api.soundcloud.com/resolve?url={@id}&_status_code_map%5B302%5D=200&_status_format=json&client_id=b45b1aa10f1ac2941910a7f0d10f8e28&app_version=7a35847b',
				'match'   => array('@soundcloud\\.com/(?!playlists/|tracks/)[^/]+/(?:sets/)?[^/]+/s-@'),
				'extract' => array('@playlists/(?\'playlist_id\'\\d+)@', '@tracks/(?\'track_id\'\\d+)@')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderSpotify($vars)
	{
		$vars += array('path' => null, 'uri' => null);

		$html='<iframe width="300" height="'.htmlspecialchars((strpos($vars['uri'],':track:')!==false||strpos($vars['path'],'track/')===0?80:380),2).'" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=';if(isset($vars['uri']))$html.=htmlspecialchars($vars['uri'],2);else$html.='spotify:'.htmlspecialchars(strtr($vars['path'],'/',':'),2);$html.='"></iframe>';

		return $html;
	}

	public static function matchSpotify($url)
	{
		$regexps = array('!(?\'uri\'spotify:(?:album|artist|user|track(?:set)?):[,:\\w]+)!', '!(?:open|play)\\.spotify\\.com/(?\'path\'(?:album|artist|track|user)/[/\\w]+)!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchTeamcoco($url)
	{
		$regexps = array('!teamcoco\\.com/video/(?\'id\'\\d+)!');
		$scrapes = array(
			array(
				'match'   => array('!teamcoco\\.com/video/.!'),
				'extract' => array('!teamcoco\\.com/embed/v/(?\'id\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderTed($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="http://embed.ted.com/'.htmlspecialchars($vars['id'],2);if((strpos($vars['id'],'.html')===false))$html.='.html';$html.='"></iframe>';

		return $html;
	}

	public static function matchTraileraddict($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('@traileraddict\\.com/(?!tags/)[^/]+/.@'),
				'extract' => array('@v\\.traileraddict\\.com/(?\'id\'\\d+)@')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderTwitch($vars)
	{
		$vars += array('archive_id' => null, 'channel' => null, 'chapter_id' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="//www.twitch.tv/widgets/';if(isset($vars['archive_id'])||isset($vars['chapter_id']))$html.='arch';else$html.='l';$html.='ive_embed_player.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['archive_id']))$html.='&amp;archive_id='.htmlspecialchars($vars['archive_id'],2);if(isset($vars['chapter_id']))$html.='&amp;chapter_id='.htmlspecialchars($vars['chapter_id'],2);$html.='&amp;auto_play=false"><embed type="application/x-shockwave-flash" width="620" height="378" allowfullscreen="" src="//www.twitch.tv/widgets/';if(isset($vars['archive_id'])||isset($vars['chapter_id']))$html.='arch';else$html.='l';$html.='ive_embed_player.swf" flashvars="channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['archive_id']))$html.='&amp;archive_id='.htmlspecialchars($vars['archive_id'],2);if(isset($vars['chapter_id']))$html.='&amp;chapter_id='.htmlspecialchars($vars['chapter_id'],2);$html.='&amp;auto_play=false"></object>';

		return $html;
	}

	public static function matchTwitch($url)
	{
		$regexps = array('#twitch\\.tv/(?\'channel\'(?!m/)\\w+)(?:/b/(?\'archive_id\'[0-9]+)|/c/(?\'chapter_id\'[0-9]+))?#');
		$scrapes = array(
			array(
				'match'   => array('!twitch\\.tv/m/[0-9]+!'),
				'extract' => array('!channel=(?\'channel\'\\w+)&.*?archive_id=(?\'archive_id\'[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderUstream($vars)
	{
		$vars += array('cid' => null, 'vid' => null);

		$html='<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="//www.ustream.tv/embed/';if(isset($vars['vid']))$html.='recorded/'.htmlspecialchars($vars['vid'],2);else$html.=htmlspecialchars($vars['cid'],2);$html.='"></iframe>';

		return $html;
	}

	public static function matchUstream($url)
	{
		$regexps = array('!ustream\\.tv/recorded/(?\'vid\'\\d+)!');
		$scrapes = array(
			array(
				'match'   => array('#ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#'),
				'extract' => array('!embed/(?\'cid\'\\d+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchVk($url)
	{
		$regexps = array('!vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?\'oid\'-?[0-9]+)_(?\'vid\'[0-9]+)!', '!vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?\'oid\'-?[0-9]+)&id=(?\'vid\'[0-9]+)&hash=(?\'hash\'[0-9a-f]+)!');
		$scrapes = array(
			array(
				'url'     => 'http://vk.com/video{@oid}_{@vid}',
				'match'   => array('!vk.*?video-?[0-9]+_[0-9]+!'),
				'extract' => array('!embed_hash=(?\'hash\'[0-9a-f]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function renderWsj($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="http://live.wsj.com/public/page/embed-'.htmlspecialchars(strtr($vars['id'],'-','_'),2).'.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>';

		return $html;
	}

	public static function renderYoutube($vars)
	{
		$vars += array('id' => null, 'list' => null, 't' => null);

		$html='<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/'.htmlspecialchars($vars['id'],2).'?controls=2';if(isset($vars['list']))$html.='&amp;list='.htmlspecialchars($vars['list'],2);if(isset($vars['t']))$html.='&amp;start='.htmlspecialchars($vars['t'],2);$html.='"></iframe>';

		return $html;
	}

	public static function matchYoutube($url)
	{
		$regexps = array('!youtube\\.com/(?:watch.*?v=|v/)(?\'id\'[-\\w]+)!', '!youtu\\.be/(?\'id\'[-\\w]+)!', '!(?=.*?youtu(?>\\.be|be\\.com)).*?[#&?]t=(?\'t\'\\d+)!', '!(?=.*?youtu(?>\\.be|be\\.com)).*?&list=(?\'list\'[-\\w]+)!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}
}