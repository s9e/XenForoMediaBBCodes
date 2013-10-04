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

	public static function matchIndiegogo($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!indiegogo\\.com/projects/(?\'id\'[0-9]+)/!');
	}

	public static function matchSlideshare($url, $id, $site)
	{
		return ($id) ? $id : self::scrape($url, '!"presentationId":(?\'id\'[0-9]+)!');
	}
}