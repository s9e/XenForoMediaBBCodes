<?php

namespace s9e\TextFormatter\Tests;

use PHPUnit_Framework_TestCase;
use s9e_MediaBBCodes;

class Test extends PHPUnit_Framework_TestCase
{
	/**
	* @requires PHP 5.4
	*/
	public function testBuild()
	{
		$_SERVER['argv'] = array('', '-dev');
		include __DIR__ . '/../scripts/build.php';
	}

	public function testLint()
	{
		include_once __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
	}

	public function testInstall()
	{
		if (!class_exists('s9e_MediaBBCodes'))
		{
			include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
		}

		s9e_MediaBBCodes::install();
	}

	/**
	* @dataProvider getMatchCallbackTests
	*/
	public function testMatchCallback($id, $url, $expected)
	{
		if (!class_exists('s9e_MediaBBCodes'))
		{
			include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';
		$methodName = 'match' . ucfirst($id);

		$this->assertSame($expected, s9e_MediaBBCodes::$methodName($url));
	}

	public function getMatchCallbackTests()
	{
		return array(
			array(
				'bandcamp',
				'http://proleter.bandcamp.com/album/curses-from-past-times-ep',
				'album_id=1122163921'
			),
			array(
				'bandcamp',
				'http://proleter.bandcamp.com/track/april-showers',
				'album_id=1122163921;track_num=1'
			),
			array(
				'blip',
				'http://blip.tv/hilah-cooking/hilah-cooking-vegetable-beef-stew-6663725',
				'AYOW3REC'
			),
			array(
				'blip',
				'http://blip.tv/play/g6VTgpjxbQA',
				'g6VTgpjxbQA'
			),
			array(
				'colbertnation',
				'http://www.colbertnation.com/the-colbert-report-videos/429637/october-14-2013/5-x-five---colbert-moments--under-the-desk',
				'mgid:cms:video:colbertnation.com:429637'
			),
			array(
				'comedycentral',
				'http://www.comedycentral.com/video-clips/uu5qz4/key-and-peele-dueling-hats',
				'mgid:arc:video:comedycentral.com:bc275e2f-48e3-46d9-b095-0254381497ea'
			),
			array(
				'dailyshow',
				'http://www.thedailyshow.com/watch/mon-july-16-2012/louis-c-k-',
				'mgid:cms:video:thedailyshow.com:416478'
			),
			array(
				'grooveshark',
				'http://grooveshark.com/playlist/Purity+Ring+Shrines/74854761',
				'playlistid=74854761'
			),
			array(
				'grooveshark',
				'http://grooveshark.com/#!/playlist/Purity+Ring+Shrines/74854761',
				'playlistid=74854761'
			),
			array(
				'grooveshark',
				'http://grooveshark.com/s/Soul+Below/4zGL7i?src=5',
				'songid=35292216'
			),
			array(
				'grooveshark',
				'http://grooveshark.com/#!/s/Soul+Below/4zGL7i?src=5',
				'songid=35292216'
			),
			array(
				'hulu',
				'http://www.hulu.com/watch/445716',
				'lbxMKBY8oOd3pvOBhM8lqQ'
			),
			array(
				'kickstarter',
				'http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/',
				'1869987317/wish-i-was-here-1'
			),
			array(
				'kickstarter',
				'http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html',
				'card=card;id=1869987317%2Fwish-i-was-here-1'
			),
			array(
				'kickstarter',
				'http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/video.html',
				'card=;id=1869987317%2Fwish-i-was-here-1;video=video'
			),
			array(
				'spotify',
				'spotify:track:5JunxkcjfCYcY7xJ29tLai',
				'uri=spotify%3Atrack%3A5JunxkcjfCYcY7xJ29tLai'
			),
			array(
				'spotify',
				'spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe',
				'uri=spotify%3Atrackset%3APREFEREDTITLE%3A5Z7ygHQo02SUrFmcgpwsKW%2C1x6ACsKV4UdWS2FMuPFUiT%2C4bi73jCM02fMpkI11Lqmfe'
			),
			array(
				'spotify',
				'http://open.spotify.com/user/ozmoetr/playlist/4yRrCWNhWOqWZx5lmFqZvt',
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt'
			),
			array(
				'spotify',
				'https://play.spotify.com/album/5OSzFvFAYuRh93WDNCTLEz',
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz'
			),
			array(
				'teamcoco',
				'http://teamcoco.com/video/conan-highlight-gigolos-mug-hunt',
				'54003'
			),
			array(
				'traileraddict',
				'http://www.traileraddict.com/trailer/watchmen/feature-trailer',
				'7376'
			),
			array(
				'twitch',
				'http://www.twitch.tv/minigolf2000/b/361358487',
				'archive_id=361358487;channel=minigolf2000'
			),
			array(
				'youtube',
				'http://www.youtube.com/watch?v=-cEzsCAzTak',
				'-cEzsCAzTak'
			),
			array(
				'youtube',
				'http://youtu.be/-cEzsCAzTak',
				'-cEzsCAzTak'
			),
			array(
				'youtube',
				'http://www.youtube.com/watch?feature=player_detailpage&amp;v=9bZkp7q19f0#t=113',
				'id=9bZkp7q19f0;t=113'
			),
		);
	}

	/**
	* @dataProvider getEmbedCallbackTests
	*/
	public function testEmbedCallback($mediaKey, $site, $expected)
	{
		if (!class_exists('s9e_MediaBBCodes'))
		{
			include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';

		$this->assertSame($expected, s9e_MediaBBCodes::embed($mediaKey, $site));
	}

	public function getEmbedCallbackTests()
	{
		return array(
			array(
				'foo',
				array('embed_html' => '<b>{$id}</b>'),
				'<b>foo</b>'
			),
			array(
				'foo&bar',
				array('embed_html' => '<b>{$id}</b>'),
				'<b>foo&amp;bar</b>'
			),
			array(
				'foo=bar;baz=quux',
				array('embed_html' => '{$foo} {$baz}'),
				'bar quux'
			),
			array(
				'album_id=1122163921',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderBandcamp -->'),
				'<iframe width="400" height="120" allowfullscreen="" frameborder="0" scrolling="no" src="http://bandcamp.com/EmbeddedPlayer/album=1122163921/size=medium"/></iframe>'
			),
			array(
				'album_id=1122163921;track_num=7',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderBandcamp -->'),
				'<iframe width="400" height="42" allowfullscreen="" frameborder="0" scrolling="no" src="http://bandcamp.com/EmbeddedPlayer/album=1122163921/size=small/t=7"/></iframe>'
			),
			array(
				'playlistid=74854761',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderGrooveshark -->'),
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="250" data="http://grooveshark.com/widget.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="playlistID=74854761&amp;songID="/></param><embed type="application/x-shockwave-flash" src="http://grooveshark.com/widget.swf" width="250" height="250" allowfullscreen="" flashvars="playlistID=74854761&amp;songID="/></embed></object>'
			),
			array(
				'songid=35292216',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderGrooveshark -->'),
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="40" data="http://grooveshark.com/songWidget.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="playlistID=&amp;songID=35292216"/></param><embed type="application/x-shockwave-flash" src="http://grooveshark.com/songWidget.swf" width="250" height="40" allowfullscreen="" flashvars="playlistID=&amp;songID=35292216"/></embed></object>'
			),
			array(
				'-cEzsCAzTak',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderYoutube -->'),
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak"/></iframe>'
			),
			array(
				'uri=spotify%3Atrack%3A5JunxkcjfCYcY7xJ29tLai',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderSpotify -->'),
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:track:5JunxkcjfCYcY7xJ29tLai"/></iframe>'
			),
			array(
				'uri=spotify%3Atrackset%3APREFEREDTITLE%3A5Z7ygHQo02SUrFmcgpwsKW%2C1x6ACsKV4UdWS2FMuPFUiT%2C4bi73jCM02fMpkI11Lqmfe',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderSpotify -->'),
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe"/></iframe>'
			),
			array(
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderSpotify -->'),
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:user:ozmoetr:playlist:4yRrCWNhWOqWZx5lmFqZvt"/></iframe>'
			),
			array(
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderSpotify -->'),
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:album:5OSzFvFAYuRh93WDNCTLEz"/></iframe>'
			),
			array(
				'id=9bZkp7q19f0;t=113',
				array('embed_html' => '<!-- s9e_MediaBBCodes::renderYoutube -->'),
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/9bZkp7q19f0?start=113"/></iframe>'
			),
		);
	}
}