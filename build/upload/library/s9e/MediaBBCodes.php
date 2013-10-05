<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

class s9e_MediaBBCodes
{
	public static function scrape($url, $regexp)
	{
		$page = file_get_contents(
			"compress.zlib://" . $url,
			false,
			stream_context_create(array(
				"http" => array(
					"header" => "Accept-Encoding: gzip"
				)
			))
		);

		return (preg_match($regexp, $page, $m)) ? $m["id"] : false;
	}

	public static function matchComedycentral($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!mgid:arc:video:comedycentral.com:(?\'id\'[-\\w]+)!');
	}

	public static function matchGamespot($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!og:video.*?id=(?\'id\'[0-9]+)!');
	}

	public static function embedGist($url, $site)
	{
		if (!preg_match('!gist\\.github\\.com/(?\'user\'[^/]+)/(?\'id\'[0-9]+)!', $url, $m))
		{
			return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
		}

		$html='<script src="https://gist.github.com/'.htmlspecialchars($m['user'],2).'/'.htmlspecialchars($m['id'],2).'.js"/></script>';

		return $html;
	}

	public static function matchIndiegogo($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!indiegogo\\.com/projects/(?\'id\'[0-9]+)/!');
	}

	public static function embedKickstarter($url, $site)
	{
		if (!preg_match('!kickstarter.com/projects/(?\'id\'[^/]+/[^/?]+)(?:/widget/(?:(?\'card\'card)|(?\'video\'video)))?!', $url, $m))
		{
			return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
		}

		$html='';if(!empty($m['video'])){$html.='<iframe width="480" height="360" src="http://www.kickstarter.com/projects/'.htmlspecialchars($m['id'],2).'/widget/video.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>';}else{$html.='<iframe width="220" height="380" src="http://www.kickstarter.com/projects/'.htmlspecialchars($m['id'],2).'/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>';}

		return $html;
	}

	public static function matchSlideshare($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!"presentationId":(?\'id\'[0-9]+)!');
	}

	public static function embedTwitch($url, $site)
	{
		if (!preg_match('#twitch\\.tv/(?\'channel\'(?!m/)[A-Za-z0-9]+)(?:/b/(?\'archive_id\'[0-9]+)|/c/(?\'chapter_id\'[0-9]+))?#', $url, $m))
		{
			return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
		}

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/'.htmlspecialchars(((!empty($m['archive_id'])||!empty($m['chapter_id']))?'arch':'l'),2).'ive_embed_player.swf"><param name="flashvars" value="channel='.htmlspecialchars($m['channel'],2);if(!empty($m['archive_id'])){$html.='&amp;archive_id='.htmlspecialchars($m['archive_id'],2);}if(!empty($m['chapter_id'])){$html.='&amp;chapter_id='.htmlspecialchars($m['chapter_id'],2);}$html.='"/></param><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/'.htmlspecialchars(((!empty($m['archive_id'])||!empty($m['chapter_id']))?'arch':'l'),2).'ive_embed_player.swf"/></embed></object>';

		return $html;
	}

	public static function matchTwitch($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!channel=(?\'channel\'[^&]+)&amp;archive_id=(?\'archive_id\'[0-9]+)!');
	}
}