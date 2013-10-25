<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

class s9e_MediaBBCodes
{
	public static function match($url, $regexps, $scrapes)
	{
		$vars = array();

		foreach ($scrapes as $scrape)
		{
			foreach ($scrape['match'] as $regexp)
			{
				if (preg_match($regexp, $url))
				{
					$vars = self::scrape($url, $scrape['extract']);

					break;
				}
			}
		}

		if (!empty($regexps))
		{
			$vars += self::getNamedCaptures($url, $regexps);
		}

		// No vars = no match
		if (empty($vars))
		{
			return false;
		}

		// If there's only one capture named "id", we store its value as-is
		if (array_keys($vars) === array('id'))
		{
			return $vars['id'];
		}

		// If there are more than one capture, or it's not named "id", we store it as a series of
		// URL-encoded key=value pairs
		$pairs = array();
		ksort($vars);
		foreach ($vars as $k => $v)
		{
			$pairs[] = urlencode($k) . '=' . urlencode($v);
		}

		// NOTE: XenForo silently nukes the mediaKey if it contains any HTML special characters,
		//       that's why we use ; rather than the standard &
		return implode(';', $pairs);
	}

	public static function embed($mediaKey, $site)
	{
		if (preg_match('(^https?://)', $mediaKey))
		{
			// If the URL is stored in the media site, reparse it and store the captures
			$vars = self::getNamedCaptures($mediaKey, $site['regexes']);
		}
		elseif (preg_match('(^([-\\w]+=[^;]*)(?>;(?1))*$)', $mediaKey))
		{
			// If the URL looks like a series of key=value pairs, add them to $vars
			$vars = array();
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}
		else
		{
			$vars = array('id' => $mediaKey);
		}

		// No vars = no match, return a link to the content, or the BBCode as text
		if (empty($vars))
		{
			$mediaKey = htmlspecialchars($mediaKey);

			return (preg_match('(^https?://)', $mediaKey))
				? "<a href=\"$mediaKey\">$mediaKey</a>"
				: "[media={$site['media_site_id']}]{$mediaKey}[/media]";
		}

		// Test whether this particular site has its own renderer
		if (preg_match('(' . __CLASS__ . '::(render\\w+))', $site['embed_html'], $m))
		{
			$methodName = $m[1];

			if (method_exists(__CLASS__, $methodName))
			{
				return self::$methodName($vars);
			}
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
		$page = file_get_contents(
			'compress.zlib://' . $url,
			false,
			stream_context_create(array(
				'http' => array(
					'header' => 'Accept-Encoding: gzip'
				)
			))
		);

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
					// Add named captures to the vars
					if (!is_numeric($k))
					{
						$vars[$k] = $v;
					}
				}
			}
		}

		return $vars;
	}

	public static function matchColbertnation($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!colbertnation\\.com/[^/]*(?:collection|video)s/!'),
				'extract' => array('!(?\'id\'mgid:cms:video:colbertnation\\.com:[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchComedycentral($url)
	{
		$regexps = array();
		$scrapes = array(
			array(
				'match'   => array('!comedycentral\\.com/video-clips/!'),
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
				'match'   => array('!thedailyshow\\.com/(?:collection|watch)/!'),
				'extract' => array('!(?\'id\'mgid:cms:video:thedailyshow\\.com:[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchGrooveshark($url)
	{
		$regexps = array('%grooveshark\\.com(?:/#!?)?/playlist/[^/]+/(?\'playlistid\'[0-9]+)%');
		$scrapes = array(
			array(
				'match'   => array('%grooveshark\\.com/s/(?\'path\'[^/]+/\\w+)%'),
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
				'extract' => array('!eid=(?\'id\'\\w+)!')
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

	public static function renderKickstarter($vars)
	{
		$html='';if(isset($vars['video'])){$html.='<iframe width="480" height="360" src="http://www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/video.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>';}else{$html.='<iframe width="220" height="380" src="http://www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>';}

		return $html;
	}

	public static function matchKickstarter($url)
	{
		$regexps = array('!kickstarter.com/projects/(?\'id\'[^/]+/[^/?]+)(?:/widget/(?:(?\'card\'card)|(?\'video\'video)))?!');
		$scrapes = array();

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchRutube($url)
	{
		$regexps = array('!rutube\\.ru/tracks/(?\'id\'[0-9]+)!');
		$scrapes = array(
			array(
				'match'   => array('!rutube\\.ru/video/[0-9a-f]{32}!'),
				'extract' => array('!rutube\\.ru/video/embed/(?\'id\'[0-9]+)!')
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

	public static function renderTwitch($vars)
	{
		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/'.htmlspecialchars(((isset($vars['archive_id'])||isset($vars['chapter_id']))?'arch':'l'),2).'ive_embed_player.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['archive_id'])){$html.='&amp;archive_id='.htmlspecialchars($vars['archive_id'],2);}if(isset($vars['chapter_id'])){$html.='&amp;chapter_id='.htmlspecialchars($vars['chapter_id'],2);}$html.='"/></param><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/'.htmlspecialchars(((isset($vars['archive_id'])||isset($vars['chapter_id']))?'arch':'l'),2).'ive_embed_player.swf" allowfullscreen=""/></embed></object>';

		return $html;
	}

	public static function matchTwitch($url)
	{
		$regexps = array('#twitch\\.tv/(?\'channel\'(?!m/)\\w+)(?:/b/(?\'archive_id\'[0-9]+)|/c/(?\'chapter_id\'[0-9]+))?#');
		$scrapes = array(
			array(
				'match'   => array('!twitch\\.tv/m/[0-9]+!'),
				'extract' => array('!channel=(?\'channel\'[^&]+)&amp;archive_id=(?\'archive_id\'[0-9]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}

	public static function matchVk($url)
	{
		$regexps = array('!vk\\.com/video(?\'oid\'-?[0-9]+)_(?\'vid\'[0-9]+)!');
		$scrapes = array(
			array(
				'match'   => array('!vk\\.com/video-?[0-9]+_[0-9]+!'),
				'extract' => array('!embed_hash=(?\'hash\'[0-9a-f]+)!')
			)
		);

		return self::match($url, $regexps, $scrapes);
	}
}