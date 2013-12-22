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
				'album_id=1122163921;track_id=1048345661;track_num=1'
			),
			array(
				'bandcamp',
				'http://therunons.bandcamp.com/track/still-feel',
				'track_id=2146686782'
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
				'gametrailers',
				'http://www.gametrailers.com/videos/jz8rt1/tom-clancy-s-the-division-vgx-2013--world-premiere-featurette',
				'mgid:arc:video:gametrailers.com:85dee3c3-60f6-4b80-8124-cf3ebd9d2a6c'
			),
			array(
				'gametrailers',
				'http://www.gametrailers.com/reviews/zalxz0/crimson-dragon-review',
				'mgid:arc:video:gametrailers.com:31c93ab8-fe77-4db2-bfee-ff37837e6704'
			),
			array(
				'gametrailers',
				'http://www.gametrailers.com/full-episodes/zdzfok/pop-fiction-episode-40--jak-ii--sandover-village',
				'mgid:arc:episode:gametrailers.com:1e287a4e-b795-4c7f-9d48-1926eafb5740'
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
				'http://www.hulu.com/watch/484180',
				'zPFCgxncn97IFkqEnZ-kRA'
			),
			array(
				'indiegogo',
				'http://www.indiegogo.com/projects/gameheart-redesigned',
				'513633'
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
				'id=1869987317%2Fwish-i-was-here-1;video=video'
			),
			array(
				'soundcloud',
				'http://api.soundcloud.com/tracks/98282116',
				'http://api.soundcloud.com/tracks/98282116'
			),
			array(
				'soundcloud',
				'https://soundcloud.com/andrewbird/three-white-horses',
				'https://soundcloud.com/andrewbird/three-white-horses'
			),
			array(
				'soundcloud',
				'[soundcloud url="https://api.soundcloud.com/tracks/12345?secret_token=s-foobar" width="100%" height="166" iframe="true" /]',
				'id=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F12345%3Fsecret_token%3Ds-foobar;secret_token=s-foobar'
			),
			array(
				'soundcloud',
				'https://soundcloud.com/matt0753/iroh-ii-deep-voice/s-UpqTm',
				'id=https%3A%2F%2Fsoundcloud.com%2Fmatt0753%2Firoh-ii-deep-voice%2Fs-UpqTm;secret_token=s-UpqTm;track_id=51465673'
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
				'http://teamcoco.com/video/serious-jibber-jabber-a-scott-berg-full-episode',
				'73784'
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
				'ustream',
				'http://www.ustream.tv/channel/ps4-ustream-gameplay',
				'cid=16234409'
			),
			array(
				'ustream',
				'http://www.ustream.tv/baja1000tv',
				'cid=9979779'
			),
			array(
				'ustream',
				'http://www.ustream.tv/recorded/40688256',
				'vid=40688256'
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
			array(
				'youtube',
				'http://www.youtube.com/watch?v=pC35x6iIPmo&list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA'
			),
			array(
				'youtube',
				'http://www.youtube.com/watch?v=pC35x6iIPmo&list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA#t=123',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA;t=123'
			),
			array(
				'youtube',
				'http://www.youtube.com/watch_popup?v=qybUFnY7Y8w',
				'qybUFnY7Y8w'
			),
		);
	}

	/**
	* @dataProvider getEmbedCallbackTests
	*/
	public function testEmbedCallback($mediaKey, $template, $expected)
	{
		if (!class_exists('s9e_MediaBBCodes'))
		{
			include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';

		$site = array('embed_html' => $template);
		$this->assertSame($expected, s9e_MediaBBCodes::embed($mediaKey, $site));
	}

	public function getEmbedCallbackTests()
	{
		return array(
			array(
				'foo',
				'<b>{$id}</b>',
				'<b>foo</b>'
			),
			array(
				'foo&bar',
				'<b>{$id}</b>',
				'<b>foo&amp;bar</b>'
			),
			array(
				'foo=bar;baz=quux',
				'{$foo} {$baz}',
				'bar quux'
			),
			array(
				'album_id=1122163921',
				'<!-- s9e_MediaBBCodes::renderBandcamp -->',
				'<iframe width="400" height="120" allowfullscreen="" frameborder="0" scrolling="no" src="http://bandcamp.com/EmbeddedPlayer/album=1122163921/size=medium"/></iframe>'
			),
			array(
				'album_id=1122163921;track_num=7',
				'<!-- s9e_MediaBBCodes::renderBandcamp -->',
				'<iframe width="400" height="42" allowfullscreen="" frameborder="0" scrolling="no" src="http://bandcamp.com/EmbeddedPlayer/album=1122163921/size=small/t=7"/></iframe>'
			),
			array(
				'playlistid=74854761',
				'<!-- s9e_MediaBBCodes::renderGrooveshark -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="250" data="http://grooveshark.com/widget.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="playlistID=74854761&amp;songID="/></param><embed type="application/x-shockwave-flash" src="http://grooveshark.com/widget.swf" width="250" height="250" allowfullscreen="" flashvars="playlistID=74854761&amp;songID="/></embed></object>'
			),
			array(
				'songid=35292216',
				'<!-- s9e_MediaBBCodes::renderGrooveshark -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="40" data="http://grooveshark.com/songWidget.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="playlistID=&amp;songID=35292216"/></param><embed type="application/x-shockwave-flash" src="http://grooveshark.com/songWidget.swf" width="250" height="40" allowfullscreen="" flashvars="playlistID=&amp;songID=35292216"/></embed></object>'
			),
			array(
				'1869987317/wish-i-was-here-1',
				'<!-- s9e_MediaBBCodes::renderKickstarter -->',
				'<iframe width="220" height="380" src="http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>'
			),
			array(
				'card=card;id=1869987317%2Fwish-i-was-here-1',
				'<!-- s9e_MediaBBCodes::renderKickstarter -->',
				'<iframe width="220" height="380" src="http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>'
			),
			array(
				'id=1869987317%2Fwish-i-was-here-1;video=video',
				'<!-- s9e_MediaBBCodes::renderKickstarter -->',
				'<iframe width="480" height="360" src="http://www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/video.html" allowfullscreen="" frameborder="0" scrolling="no"/></iframe>'
			),
			array(
				'http://api.soundcloud.com/tracks/98282116',
				'<!-- s9e_MediaBBCodes::renderSoundcloud -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=http://api.soundcloud.com/tracks/98282116"/></iframe>'
			),
			array(
				'id=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F12345%3Fsecret_token%3Ds-foobar;secret_token=s-foobar',
				'<!-- s9e_MediaBBCodes::renderSoundcloud -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/12345?secret_token=s-foobar&amp;secret_token=s-foobar"/></iframe>'
			),
			array(
				'id=https%3A%2F%2Fsoundcloud.com%2Fmatt0753%2Firoh-ii-deep-voice%2Fs-UpqTm;secret_token=s-UpqTm;track_id=51465673',
				'<!-- s9e_MediaBBCodes::renderSoundcloud -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/51465673&amp;secret_token=s-UpqTm"/></iframe>'
			),
			array(
				'uri=spotify%3Atrack%3A5JunxkcjfCYcY7xJ29tLai',
				'<!-- s9e_MediaBBCodes::renderSpotify -->',
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:track:5JunxkcjfCYcY7xJ29tLai"/></iframe>'
			),
			array(
				'uri=spotify%3Atrackset%3APREFEREDTITLE%3A5Z7ygHQo02SUrFmcgpwsKW%2C1x6ACsKV4UdWS2FMuPFUiT%2C4bi73jCM02fMpkI11Lqmfe',
				'<!-- s9e_MediaBBCodes::renderSpotify -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe"/></iframe>'
			),
			array(
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt',
				'<!-- s9e_MediaBBCodes::renderSpotify -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:user:ozmoetr:playlist:4yRrCWNhWOqWZx5lmFqZvt"/></iframe>'
			),
			array(
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz',
				'<!-- s9e_MediaBBCodes::renderSpotify -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:album:5OSzFvFAYuRh93WDNCTLEz"/></iframe>'
			),
			array(
				'channel=minigolf2000',
				'<!-- s9e_MediaBBCodes::renderTwitch -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/live_embed_player.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="channel=minigolf2000"/></param><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/live_embed_player.swf" allowfullscreen=""/></embed></object>',
			),
			array(
				'archive_id=361358487;channel=minigolf2000',
				'<!-- s9e_MediaBBCodes::renderTwitch -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/archive_embed_player.swf"><param name="allowfullscreen" value="true"/></param><param name="flashvars" value="channel=minigolf2000&amp;archive_id=361358487"/></param><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/archive_embed_player.swf" allowfullscreen=""/></embed></object>',
			),
			array(
				'cid=16234409',
				'<!-- s9e_MediaBBCodes::renderUstream -->',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="http://www.ustream.tv/embed/16234409"/></iframe>'
			),
			array(
				'vid=40688256',
				'<!-- s9e_MediaBBCodes::renderUstream -->',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="http://www.ustream.tv/embed/recorded/40688256"/></iframe>'
			),
			array(
				'-cEzsCAzTak',
				'<!-- s9e_MediaBBCodes::renderYoutube -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak"/></iframe>'
			),
			array(
				'id=9bZkp7q19f0;t=113',
				'<!-- s9e_MediaBBCodes::renderYoutube -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/9bZkp7q19f0?start=113"/></iframe>'
			),
			array(
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA',
				'<!-- s9e_MediaBBCodes::renderYoutube -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA"/></iframe>'
			),
			array(
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA;t=123',
				'<!-- s9e_MediaBBCodes::renderYoutube -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA&amp;start=123"/></iframe>'
			),
		);
	}
}