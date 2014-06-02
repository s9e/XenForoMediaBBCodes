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
	public function testMatchCallback($id, $url, $expected, $assertMethod = 'assertSame')
	{
		if (!class_exists('s9e_MediaBBCodes'))
		{
			include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';
		$methodName = 'match' . ucfirst($id);

		$this->$assertMethod($expected, s9e_MediaBBCodes::$methodName($url));
	}

	public function getMatchCallbackTests()
	{
		return array(
			array(
				'amazon',
				'http://www.amazon.ca/gp/product/B00GQT1LNO/',
				'id=B00GQT1LNO;tld=ca'
			),
			array(
				'amazon',
				'http://www.amazon.co.jp/gp/product/B003AKZ6I8/',
				'id=B003AKZ6I8;tld=jp'
			),
			array(
				'amazon',
				'http://www.amazon.co.uk/gp/product/B00BET0NR6/',
				'id=B00BET0NR6;tld=uk'
			),
			array(
				'amazon',
				'http://www.amazon.com/dp/B002MUC0ZY',
				'B002MUC0ZY'
			),
			array(
				'amazon',
				'http://www.amazon.com/The-BeerBelly-200-001-80-Ounce-Belly/dp/B001RB2CXY/',
				'B001RB2CXY'
			),
			array(
				'amazon',
				'http://www.amazon.com/gp/product/B0094H8H7I',
				'B0094H8H7I'
			),
			array(
				'amazon',
				'http://www.amazon.de/Netgear-WN3100RP-100PES-Repeater-integrierte-Steckdose/dp/B00ET2LTE6/',
				'id=B00ET2LTE6;tld=de'
			),
			array(
				'amazon',
				'http://www.amazon.fr/Vans-Authentic-Baskets-mixte-adulte/dp/B005NIKPAY/',
				'id=B005NIKPAY;tld=fr'
			),
			array(
				'amazon',
				'http://www.amazon.it/gp/product/B00JGOMIP6/',
				'id=B00JGOMIP6;tld=it'
			),
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
				'cbsnews',
				'http://www.cbsnews.com/video/watch/?id=50156501n',
				'50156501'
			),
			array(
				'cbsnews',
				'http://www.cbsnews.com/videos/is-the-us-stock-market-rigged',
				'pid=W4MVSOaNEYMq'
			),
			array(
				'colbertnation',
				'http://www.colbertnation.com/the-colbert-report-videos/429637/october-14-2013/5-x-five---colbert-moments--under-the-desk',
				'mgid:arc:video:colbertnation.com:94b7c7d0-dbc8-4e76-8f2b-9dd88b5db1e1'
			),
			array(
				'colbertnation',
				'http://thecolbertreport.cc.com/videos/gh6urb/neil-degrasse-tyson-pt--1',
				'mgid:arc:video:colbertnation.com:676d3a42-4c19-47e0-9509-f333fa76b4eb'
			),
			array(
				'comedycentral',
				'http://www.comedycentral.com/video-clips/uu5qz4/key-and-peele-dueling-hats',
				'mgid:arc:video:comedycentral.com:bc275e2f-48e3-46d9-b095-0254381497ea'
			),
			array(
				'dailyshow',
				'http://www.thedailyshow.com/watch/mon-july-16-2012/louis-c-k-',
				'mgid:arc:video:thedailyshow.com:627cc3c2-4218-4a78-bf1d-c8258f4db2f8'
			),
			array(
				'dailyshow',
				'http://thedailyshow.cc.com/extended-interviews/rpgevm/exclusive-matt-taibbi-extended-interview',
				'mgid:arc:playlist:thedailyshow.com:85ebd39c-9fea-44f3-9da2-f3088cab195d'
			),
			array(
				'ebay',
				'http://www.ebay.com/itm/Converse-All-Star-Chuck-Taylor-Black-Hi-Canvas-M9160-Men-/251053262701',
				'itemid=251053262701'
			),
			array(
				'ebay',
				'http://www.ebay.co.uk/itm/Converse-Classic-Chuck-Taylor-Low-Trainer-Sneaker-All-Star-OX-NEW-sizes-Shoes-/230993099153',
				'itemid=230993099153;lang=en_GB'
			),
			array(
				'espn',
				'http://espn.go.com/video/clip?id=10936987',
				'cms=espn;id=10936987'
			),
			array(
				'espn',
				'http://m.espn.go.com/general/video?vid=10926479',
				'cms=espn;id=10926479'
			),
			array(
				'espn',
				'http://espndeportes.espn.go.com/videohub/video/clipDeportes?id=deportes:2001302',
				'cms=deportes;id=2001302'
			),
			array(
				'espn',
				'http://espndeportes.espn.go.com/videohub/video/clipDeportes?id=2088955&amp;cc=7586',
				'cms=deportes;id=2088955'
			),
			array(
				'facebook',
				'https://www.facebook.com/photo.php?v=10100658170103643',
				'id=10100658170103643;mode=photo'
			),
			array(
				'facebook',
				'https://www.facebook.com/video/video.php?v=10150451523596807',
				'id=10150451523596807;mode=video'
			),
			array(
				'facebook',
				'https://www.facebook.com/FacebookDevelopers/posts/10151471074398553',
				'id=10151471074398553;mode=post'
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
				'getty',
				'http://gty.im/3232182',
				'(et=[-\\w]{22};height=399;id=3232182;sig=[-\\w]{43}%3D;width=594)',
				'assertRegexp'
			),
			array(
				'getty',
				'http://www.gettyimages.co.uk/detail/3232182',
				'(et=[-\\w]{22};height=399;id=3232182;sig=[-\\w]{43}%3D;width=594)',
				'assertRegexp'
			),
			array(
				'gfycat',
				'http://gfycat.com/SereneIllfatedCapybara',
				'height=338;id=SereneIllfatedCapybara;width=600'
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
				'internetarchive',
				'https://archive.org/details/Olympics2002_2',
				'height=240;id=Olympics2002_2;width=320'
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
				'podbean',
				'http://wendyswordsofwisdom.podbean.com/e/tiffany-stevensons-words-of-wisdom/',
				'5168723'
			),
			array(
				'rdio',
				'http://rd.io/x/QcD7oTdeWevg/',
				'QcD7oTdeWevg'
			),
			array(
				'rdio',
				'https://www.rdio.com/artist/Hannibal_Buress/album/Animal_Furnace/track/Hands-Free/',
				'QitDVOn7'
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
				'http://www.traileraddict.com/muppets-most-wanted/super-bowl-tv-spot',
				'86191'
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
				'id=B00GQT1LNO;tld=ca',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-ca.amazon.ca/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00GQT1LNO&amp;o=15&amp;t=_"></iframe>'
			),
			array(
				'id=B003AKZ6I8;tld=jp',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-jp.amazon.co.jp/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B003AKZ6I8&amp;o=9&amp;t=_"></iframe>'
			),
			array(
				'id=B00BET0NR6;tld=uk',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-uk.amazon.co.uk/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00BET0NR6&amp;o=2&amp;t=_"></iframe>'
			),
			array(
				'B002MUC0ZY',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=_"></iframe>'
			),
			array(
				'B002MUC0ZY',
				'<!-- AMAZON_ASSOCIATE_TAG=foo-20 --><!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=foo-20"></iframe>'
			),
			array(
				'id=B00ET2LTE6;tld=de',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-de.amazon.de/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00ET2LTE6&amp;o=3&amp;t=_"></iframe>'
			),
			array(
				'id=B005NIKPAY;tld=fr',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-fr.amazon.fr/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B005NIKPAY&amp;o=8&amp;t=_"></iframe>'
			),
			array(
				'id=B00JGOMIP6;tld=it',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm-it.amazon.it/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00JGOMIP6&amp;o=29&amp;t=_"></iframe>'
			),
			array(
				'id=B002MUC0ZY;tld=com',
				'<!-- s9e_MediaBBCodes::renderAmazon() -->',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="http://rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=_"></iframe>'
			),
			array(
				'album_id=1122163921',
				'<!-- s9e_MediaBBCodes::renderBandcamp() -->',
				'<iframe width="400" height="120" allowfullscreen="" frameborder="0" scrolling="no" src="//bandcamp.com/EmbeddedPlayer/album=1122163921/size=medium"></iframe>'
			),
			array(
				'album_id=1122163921;track_num=7',
				'<!-- s9e_MediaBBCodes::renderBandcamp() -->',
				'<iframe width="400" height="42" allowfullscreen="" frameborder="0" scrolling="no" src="//bandcamp.com/EmbeddedPlayer/album=1122163921/size=small/t=7"></iframe>'
			),
			array(
				'50156501',
				'<!-- s9e_MediaBBCodes::renderCbsnews() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="si=254&amp;contentValue=50156501"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf" flashvars="si=254&amp;contentValue=50156501"></object>'
			),
			array(
				'pid=W4MVSOaNEYMq',
				'<!-- s9e_MediaBBCodes::renderCbsnews() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="http://www.cbsnews.com/common/video/cbsnews_player.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="pType=embed&amp;si=254&amp;pid=W4MVSOaNEYMq"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="http://www.cbsnews.com/common/video/cbsnews_player.swf" flashvars="pType=embed&amp;si=254&amp;pid=W4MVSOaNEYMq"></object>'
			),
			array(
				'itemid=251053262701',
				'<!-- s9e_MediaBBCodes::renderEbay() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="355" height="300" data="http://togo.ebay.com/togo/togo.swf?2008013100"><param name="allowfullscreen" value="true"><param name="flashvars" value="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=251053262701"><embed type="application/x-shockwave-flash" src="http://togo.ebay.com/togo/togo.swf?2008013100" width="355" height="300" allowfullscreen="" flashvars="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=251053262701"></object>'
			),
			array(
				'itemid=251053262701;lang=en_GB',
				'<!-- s9e_MediaBBCodes::renderEbay() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="355" height="300" data="http://togo.ebay.com/togo/togo.swf?2008013100"><param name="allowfullscreen" value="true"><param name="flashvars" value="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=251053262701&amp;lang=en-GB"><embed type="application/x-shockwave-flash" src="http://togo.ebay.com/togo/togo.swf?2008013100" width="355" height="300" allowfullscreen="" flashvars="base=http://togo.ebay.com/togo/&amp;mode=normal&amp;query=server&amp;itemid=251053262701&amp;lang=en-GB"></object>'
			),
			array(
				'id=10100658170103643;mode=photo',
				'<!-- s9e_MediaBBCodes::renderFacebook() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="https://www.facebook.com/video/embed?video_id=10100658170103643"></iframe>'
			),
			array(
				'id=10150451523596807;mode=video',
				'<!-- s9e_MediaBBCodes::renderFacebook() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="https://www.facebook.com/video/embed?video_id=10150451523596807"></iframe>'
			),
			array(
				'10150451523596807',
				'<!-- s9e_MediaBBCodes::renderFacebook() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="https://www.facebook.com/video/embed?video_id=10150451523596807"></iframe>'
			),
			array(
				'id=10151471074398553;mode=post',
				'<!-- s9e_MediaBBCodes::renderFacebook() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//s9e.github.io/iframe/facebook.min.html#10151471074398553" onload="var b=this;window.addEventListener(\'message\',function(a){/^https?:\/\/s9e\.github\.io$/.test(a.origin)&amp;&amp;a.data.url&amp;&amp;a.data.height&amp;&amp;b.src==a.data.url&amp;&amp;(b.style.height=a.data.height+\'px\')});b.contentWindow.postMessage(\'s9e:init\',\'*\')"></iframe>'
			),
			array(
				'et=0KmkT83GTG1ynPe0_63zHg;height=399;id=3232182;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48%3D;width=594',
				'<!-- s9e_MediaBBCodes::renderGetty() -->',
				'<iframe width="594" height="448" src="//embed.gettyimages.com/embed/3232182?et=0KmkT83GTG1ynPe0_63zHg&amp;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48=" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'height=338;id=SereneIllfatedCapybara;width=600',
				'<iframe width="{$width}" height="{$height}" src="http://gfycat.com/iframe/{$id}" allowfullscreen="" frameborder="0" scrolling="no"></iframe>',
				'<iframe width="600" height="338" src="http://gfycat.com/iframe/SereneIllfatedCapybara" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'playlistid=74854761',
				'<!-- s9e_MediaBBCodes::renderGrooveshark() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="250" data="//grooveshark.com/widget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID=74854761&amp;songID="><embed type="application/x-shockwave-flash" src="//grooveshark.com/widget.swf" width="250" height="250" allowfullscreen="" flashvars="playlistID=74854761&amp;songID="></object>'
			),
			array(
				'songid=35292216',
				'<!-- s9e_MediaBBCodes::renderGrooveshark() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="250" height="40" data="//grooveshark.com/songWidget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID=&amp;songID=35292216"><embed type="application/x-shockwave-flash" src="//grooveshark.com/songWidget.swf" width="250" height="40" allowfullscreen="" flashvars="playlistID=&amp;songID=35292216"></object>'
			),
			array(
				'1869987317/wish-i-was-here-1',
				'<!-- s9e_MediaBBCodes::renderKickstarter() -->',
				'<iframe width="220" height="380" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'card=card;id=1869987317%2Fwish-i-was-here-1',
				'<!-- s9e_MediaBBCodes::renderKickstarter() -->',
				'<iframe width="220" height="380" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'id=1869987317%2Fwish-i-was-here-1;video=video',
				'<!-- s9e_MediaBBCodes::renderKickstarter() -->',
				'<iframe width="480" height="360" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/video.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'http://api.soundcloud.com/tracks/98282116',
				'<!-- s9e_MediaBBCodes::renderSoundcloud() -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=http://api.soundcloud.com/tracks/98282116"></iframe>'
			),
			array(
				'id=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F12345%3Fsecret_token%3Ds-foobar;secret_token=s-foobar',
				'<!-- s9e_MediaBBCodes::renderSoundcloud() -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/12345?secret_token=s-foobar&amp;secret_token=s-foobar"></iframe>'
			),
			array(
				'id=https%3A%2F%2Fsoundcloud.com%2Fmatt0753%2Firoh-ii-deep-voice%2Fs-UpqTm;secret_token=s-UpqTm;track_id=51465673',
				'<!-- s9e_MediaBBCodes::renderSoundcloud() -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/51465673&amp;secret_token=s-UpqTm"></iframe>'
			),
			array(
				'nruau/nruau-mix2',
				'<!-- s9e_MediaBBCodes::renderSoundcloud() -->',
				'<iframe width="560" height="166" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://soundcloud.com/nruau/nruau-mix2"></iframe>'
			),
			array(
				'uri=spotify%3Atrack%3A5JunxkcjfCYcY7xJ29tLai',
				'<!-- s9e_MediaBBCodes::renderSpotify() -->',
				'<iframe width="300" height="80" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:track:5JunxkcjfCYcY7xJ29tLai"></iframe>'
			),
			array(
				'uri=spotify%3Atrackset%3APREFEREDTITLE%3A5Z7ygHQo02SUrFmcgpwsKW%2C1x6ACsKV4UdWS2FMuPFUiT%2C4bi73jCM02fMpkI11Lqmfe',
				'<!-- s9e_MediaBBCodes::renderSpotify() -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe"></iframe>'
			),
			array(
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt',
				'<!-- s9e_MediaBBCodes::renderSpotify() -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:user:ozmoetr:playlist:4yRrCWNhWOqWZx5lmFqZvt"></iframe>'
			),
			array(
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz',
				'<!-- s9e_MediaBBCodes::renderSpotify() -->',
				'<iframe width="300" height="380" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?uri=spotify:album:5OSzFvFAYuRh93WDNCTLEz"></iframe>'
			),
			array(
				'talks/eli_pariser_beware_online_filter_bubbles.html',
				'<!-- s9e_MediaBBCodes::renderTed() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="http://embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe>'
			),
			array(
				'talks/eli_pariser_beware_online_filter_bubbles',
				'<!-- s9e_MediaBBCodes::renderTed() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="http://embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe>'
			),
			array(
				'channel=minigolf2000',
				'<!-- s9e_MediaBBCodes::renderTwitch() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/live_embed_player.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="channel=minigolf2000&amp;auto_play=false"><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/live_embed_player.swf" allowfullscreen=""></object>',
			),
			array(
				'archive_id=361358487;channel=minigolf2000',
				'<!-- s9e_MediaBBCodes::renderTwitch() -->',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/archive_embed_player.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="channel=minigolf2000&amp;archive_id=361358487&amp;auto_play=false"><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/archive_embed_player.swf" allowfullscreen=""></object>',
			),
			array(
				'cid=16234409',
				'<!-- s9e_MediaBBCodes::renderUstream() -->',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="http://www.ustream.tv/embed/16234409"></iframe>'
			),
			array(
				'vid=40688256',
				'<!-- s9e_MediaBBCodes::renderUstream() -->',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="http://www.ustream.tv/embed/recorded/40688256"></iframe>'
			),
			array(
				'09FB2B3B-583E-4284-99D8-FEF6C23BE4E2',
				'<!-- s9e_MediaBBCodes::renderWsj() -->',
				'<iframe width="512" height="288" src="http://live.wsj.com/public/page/embed-09FB2B3B_583E_4284_99D8_FEF6C23BE4E2.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'-cEzsCAzTak',
				'<!-- s9e_MediaBBCodes::renderYoutube() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak?controls=2"></iframe>'
			),
			array(
				'id=9bZkp7q19f0;t=113',
				'<!-- s9e_MediaBBCodes::renderYoutube() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/9bZkp7q19f0?controls=2&amp;start=113"></iframe>'
			),
			array(
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA',
				'<!-- s9e_MediaBBCodes::renderYoutube() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?controls=2&amp;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA"></iframe>'
			),
			array(
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA;t=123',
				'<!-- s9e_MediaBBCodes::renderYoutube() -->',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?controls=2&amp;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA&amp;start=123"></iframe>'
			),
			array(
				'-cEzsCAzTak',
				'<div class="responsiveVideoContainer"><!-- s9e_MediaBBCodes::renderYoutube() --></div>',
				'<div class="responsiveVideoContainer"><iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak?controls=2"></iframe></div>'
			),
			array(
				'xyz',
				'<!-- s9e_MediaBBCodes::renderInexistent() -->',
				'<!-- s9e_MediaBBCodes::renderInexistent() -->'
			),
			array(
				'-cEzsCAzTak',
				'<!-- s9e_MediaBBCodes::renderYoutube(1280, 620) -->',
				'<iframe width="1280" height="620" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak?controls=2"></iframe>'
			),
			array(
				'-cEzsCAzTak',
				'<!-- s9e_MediaBBCodes::renderYoutube(1280,620) -->',
				'<iframe width="1280" height="620" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak?controls=2"></iframe>'
			),
		);
	}
}