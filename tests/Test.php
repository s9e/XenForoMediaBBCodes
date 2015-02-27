<?php

namespace s9e\XenForoMediaBBCodes\Tests;

use DOMDocument;
use PHPUnit_Framework_TestCase;
use s9e_MediaBBCodes;
use XenForo_Application;
use XenForo_DataWriter;
use XenForo_DataWriter_TemplateModification;
use XenForo_Model;
use XenForo_Model_BbCode;
use XenForo_Model_TemplateModification;

include __DIR__ . '/Dummy.php';
include __DIR__ . '/XenForo_Application.php';
include __DIR__ . '/XenForo_DataWriter.php';
include __DIR__ . '/XenForo_DataWriter_TemplateModification.php';
include __DIR__ . '/XenForo_Model.php';
include __DIR__ . '/XenForo_Model_BbCode.php';
include __DIR__ . '/XenForo_Model_TemplateModification.php';
include __DIR__ . '/s9e_Custom.php';

class Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		spl_autoload_register(
			function ($className)
			{
				$filepath = __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
				if ($className === 's9e_MediaBBCodes' && file_exists($filepath))
				{
					include $filepath;
				}
			}
		);
	}

	public function setUp()
	{
		// Reset the s9e_MediaBBCodes vars only if the class already exists. This is to ensure that
		// the class isn't loaded before the first test is run
		if (class_exists('s9e_MediaBBCodes', false))
		{
			s9e_MediaBBCodes::$customCallbacks    = null;
			s9e_MediaBBCodes::$excludedSites      = null;
			s9e_MediaBBCodes::$maxResponsiveWidth = null;
			s9e_MediaBBCodes::$tags               = null;
		}

		XenForo_Application::$options = array(
			's9e_EXCLUDE_SITES'        => null,
			's9e_excluded_sites'       => null,
			's9e_custom_callbacks'     => null,
			's9e_max_responsive_width' => null,
			's9e_media_tags'           => null
		);
	}

	public function getAddon($sitesXml = '<bb_code_media_sites/>')
	{
		$sites = new DOMDocument;
		$sites->loadXML($sitesXml);

		$addon = new DOMDocument;
		$addon->load(__DIR__ . '/../build/addon-s9e.xml');

		$node = $addon->getElementsByTagName('bb_code_media_sites')->item(0);
		$node->parentNode->replaceChild($addon->importNode($sites->documentElement, true), $node);

		return simplexml_import_dom($addon);
	}

	public function assertAddonHasSite($addon, $siteId)
	{
		return (bool) count($addon->xpath('//site[@media_site_id="' . $siteId . '"]'));
	}

	public function assertAddonNotHasSite($addon, $siteId)
	{
		return !$this->assertAddonHasSite($addon, $siteId);
	}

	public function assertReinstallWasCalled()
	{
		$this->assertArrayHasKey(0, XenForo_Model_BbCode::$loggedCalls);
		$this->assertSame(
			'importBbCodeMediaSitesAddOnXml',
			XenForo_Model_BbCode::$loggedCalls[0][0]
		);

		$this->assertArrayHasKey(1, XenForo_Model_BbCode::$loggedCalls);
		$this->assertSame(
			'rebuildBbCodeCache',
			XenForo_Model_BbCode::$loggedCalls[1][0]
		);
	}

	/**
	* @requires PHP 5.6
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

	public function testBlacklist()
	{
		XenForo_Application::$options['s9e_excluded_sites'] = 'two, three, five';

		$addon = $this->getAddon(
			'<bb_code_media_sites>
				<site media_site_id="one"/>
				<site media_site_id="two"/>
				<site media_site_id="three"/>
				<site media_site_id="four"/>
				<site media_site_id="five"/>
				<site media_site_id="six"/>
			</bb_code_media_sites>'
		);

		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertAddonHasSite($addon, 'one');
		$this->assertAddonHasSite($addon, 'four');
		$this->assertAddonHasSite($addon, 'six');
		$this->assertAddonNotHasSite($addon, 'two');
		$this->assertAddonNotHasSite($addon, 'three');
		$this->assertAddonNotHasSite($addon, 'five');
	}

	public function testBlacklistNoEmpty()
	{
		XenForo_Application::$options['s9e_excluded_sites'] = '';

		$addon = $this->getAddon(
			'<bb_code_media_sites>
				<site media_site_id="one"/>
				<site media_site_id="two"/>
				<site media_site_id="three"/>
			</bb_code_media_sites>'
		);

		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertAddonHasSite($addon, 'one');
		$this->assertAddonHasSite($addon, 'two');
		$this->assertAddonHasSite($addon, 'three');
	}

	public function testBlacklistWithSurroundingSpace()
	{
		XenForo_Application::$options['s9e_excluded_sites'] = ' two ';

		$addon = $this->getAddon(
			'<bb_code_media_sites>
				<site media_site_id="one"/>
				<site media_site_id="two"/>
				<site media_site_id="three"/>
			</bb_code_media_sites>'
		);

		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertAddonHasSite($addon, 'one');
		$this->assertAddonNotHasSite($addon, 'two');
		$this->assertAddonHasSite($addon, 'three');
	}

	public function testBlacklistLegacy()
	{
		XenForo_Application::$options['s9e_EXCLUDE_SITES'] = 'two, three, five';

		$addon = $this->getAddon(
			'<bb_code_media_sites>
				<site media_site_id="one"/>
				<site media_site_id="two"/>
				<site media_site_id="three"/>
				<site media_site_id="four"/>
				<site media_site_id="five"/>
				<site media_site_id="six"/>
			</bb_code_media_sites>'
		);

		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertAddonHasSite($addon, 'one');
		$this->assertAddonHasSite($addon, 'four');
		$this->assertAddonHasSite($addon, 'six');
		$this->assertAddonNotHasSite($addon, 'two');
		$this->assertAddonNotHasSite($addon, 'three');
		$this->assertAddonNotHasSite($addon, 'five');
	}

	public function testBlacklistLegacyInstall()
	{
		XenForo_Application::$options['s9e_EXCLUDE_SITES'] = 'exfoo';

		$addon = $this->getAddon();
		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertContains(
			'<default_value>exfoo</default_value></option>',
			$addon->asXML()
		);
	}

	public function testDefaultInstall()
	{
		$addon = $this->getAddon();
		s9e_MediaBBCodes::install(null, array(), $addon);

		// Test a couple of sites
		$this->assertAddonHasSite($addon, 'youku');
		$this->assertAddonHasSite($addon, 'youtube');
	}

	public function testMediaTags()
	{
		XenForo_Application::$options['s9e_media_tags'] = array('social' => 1);

		$addon = $this->getAddon();
		s9e_MediaBBCodes::install(null, array(), $addon);

		// Test a couple of sites
		$this->assertAddonHasSite($addon, 'twitter');
		$this->assertAddonNotHasSite($addon, 'youku');
	}

	public function testUpdateTags()
	{
		$tags = array('new' => 1);

		$this->assertTrue(s9e_MediaBBCodes::updateTags($tags));
		$this->assertSame($tags, s9e_MediaBBCodes::$tags);
		$this->assertReinstallWasCalled();
	}

	public function testValidateCustomCallbacksValue()
	{
		$text = "
			youtube = foo :: bar

			twitter = bar :: baz
		";
		s9e_MediaBBCodes::validateCustomCallbacks($text);

		$this->assertSame("twitter=bar::baz\nyoutube=foo::bar\n", $text);
	}

	public function testValidateCustomCallbacksReinstall()
	{
		$text = '';
		s9e_MediaBBCodes::validateCustomCallbacks($text);
		$this->assertReinstallWasCalled();
	}

	public function testCustomCallbacksInstall()
	{
		$addon = $this->getAddon(
			'<bb_code_media_sites>
				<site media_site_id="custom">
					<embed_html>x</embed_html>
				</site>
			</bb_code_media_sites>'
		);

		s9e_MediaBBCodes::install(null, array(), $addon);

		$this->assertContains(
			"<default_value>custom=s9e_Custom::custom\n</default_value></option>",
			$addon->asXML()
		);
	}

	public function testValidateExcludedSitesValue()
	{
		$text = ' youtube , twitter ';
		s9e_MediaBBCodes::validateExcludedSites($text);

		$this->assertSame('twitter,youtube', $text);
	}

	public function testValidateExcludedSitesValueLowercased()
	{
		$text = ' YouTube , Twitter ';
		s9e_MediaBBCodes::validateExcludedSites($text);

		$this->assertSame('twitter,youtube', $text);
	}

	public function testValidateExcludedSitesByName()
	{
		$text = 'YouTube, 8tracks, Internet Archive';
		s9e_MediaBBCodes::validateExcludedSites($text);

		$this->assertSame('eighttracks,internetarchive,youtube', $text);
	}

	public function testValidateExcludedSitesReinstall()
	{
		$text = '';
		s9e_MediaBBCodes::validateExcludedSites($text);
		$this->assertReinstallWasCalled();
	}

	public function testValidateMaxResponsiveWidthReinstall()
	{
		$text = '';
		s9e_MediaBBCodes::validateMaxResponsiveWidth($text);
		$this->assertReinstallWasCalled();
	}

	public function testValidateMaxResponsiveWidthUpdated()
	{
		$text = '123';
		s9e_MediaBBCodes::validateMaxResponsiveWidth($text);
		$this->assertEquals(123, s9e_MediaBBCodes::$maxResponsiveWidth);
	}

	public function testFooterCallbackNoModification()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = false;

		$this->assertSame('show', s9e_MediaBBCodes::validateFooter('show'));
		$this->assertEmpty(XenForo_DataWriter_TemplateModification::$loggedCalls);
	}

	public function testFooterCallbackOnUpdateShow()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = array('enabled' => 0);

		$this->assertSame('show', s9e_MediaBBCodes::validateFooter('show'));
		$this->assertSame(
			array(
				array('setExistingData', array(array('enabled' => 0))),
				array('set',             array('enabled', 1)),
				array('save',            array())
			),
			XenForo_DataWriter_TemplateModification::$loggedCalls
		);
	}

	public function testFooterCallbackOnUpdateHide()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = array('enabled' => 1);

		$this->assertSame('hide', s9e_MediaBBCodes::validateFooter('hide'));
		$this->assertSame(
			array(
				array('setExistingData', array(array('enabled' => 1))),
				array('set',             array('enabled', 0)),
				array('save',            array())
			),
			XenForo_DataWriter_TemplateModification::$loggedCalls
		);
	}

	public function testLazyLoadingCallbackNoModification()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = false;

		$this->assertSame('immediate', s9e_MediaBBCodes::validateLazyLoading('immediate'));
		$this->assertEmpty(XenForo_DataWriter_TemplateModification::$loggedCalls);
	}

	public function testLazyLoadingCallbackOnUpdateImmediate()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = array('enabled' => 1);

		$this->assertSame('immediate', s9e_MediaBBCodes::validateLazyLoading('immediate'));
		$this->assertSame(
			array(
				array('setExistingData', array(array('enabled' => 1))),
				array('set',             array('enabled', 0)),
				array('save',            array())
			),
			XenForo_DataWriter_TemplateModification::$loggedCalls
		);
	}

	public function testLazyLoadingCallbackOnUpdateLazy()
	{
		XenForo_DataWriter_TemplateModification::$loggedCalls = array();
		XenForo_Model_TemplateModification::$modification = array('enabled' => 0);

		$this->assertSame('lazy', s9e_MediaBBCodes::validateLazyLoading('lazy'));
		$this->assertSame(
			array(
				array('setExistingData', array(array('enabled' => 0))),
				array('set',             array('enabled', 1)),
				array('save',            array())
			),
			XenForo_DataWriter_TemplateModification::$loggedCalls
		);
	}

	/**
	* @dataProvider getMatchCallbackTests
	*/
	public function testMatchCallback($siteId, $url, $expected, $assertMethod = 'assertSame', $setup = null)
	{
		if (isset($setup))
		{
			$setup();
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';

		$this->$assertMethod($expected, s9e_MediaBBCodes::match($url, null, array(), $siteId));
	}

	public function getMatchCallbackTests()
	{
		return array(
			array(
				'unknown',
				'123',
				false
			),
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
				'bbcnews',
				'http://www.bbc.com/news/business-29149086',
				'ad_site=%2Fnews%2Fbusiness%2F;playlist=%2Fnews%2Fbusiness-29149086A;poster=%2Fmedia%2Fimages%2F77537000%2Fjpg%2F_77537408_mapopgetty.jpg'
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
				'dumpert',
				'http://www.dumpert.nl/mediabase/6622635/f6d1e0fd/lompe_boer_op_zuidlaardermarkt_doet_shetlandpony_pijn.html',
				'6622635_f6d1e0fd'
			),
			array(
				'eighttracks',
				'http://8tracks.com/mc_raw/canadian-flavored-indie-rock-grilled-cheese',
				'1007987'
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
				'espn',
				'http://espn.go.com/new-york/nba/story/_/id/11196159/carmelo-anthony-agent-says-made-decision',
				false
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
				'googlesheets',
				'https://docs.google.com/spreadsheets/d/1f988o68HDvk335xXllJD16vxLBuRcmm3vg6U9lVaYpA',
				'1f988o68HDvk335xXllJD16vxLBuRcmm3vg6U9lVaYpA'
			),
			array(
				'googlesheets',
				'https://docs.google.com/spreadsheet/ccc?key=0An1aCHqyU7FqdGtBUDc1S1NNSWhqY3NidndIa1JuQWc#gid=70',
				'gid=70;id=0An1aCHqyU7FqdGtBUDc1S1NNSWhqY3NidndIa1JuQWc'
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
				'imgur',
				'http://imgur.com/a/9UGCL',
				'id=9UGCL;type=album'
			),
			array(
				'imgur',
				'http://i.imgur.com/u7Yo0Vy.gifv',
				'height=389;id=u7Yo0Vy;type=gifv;width=915'
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
				'khl',
				'http://video.khl.ru/quotes/251257',
				'(^free_\\w+_hd/q251257/\\w+/\\d+$)',
				'assertRegexp'
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
				'livestream',
				'http://new.livestream.com/ShawConferenceCentre/CrashedIce/videos/77576437',
				'account_id=12014794;event_id=3788876;video_id=77576437'
			),
			array(
				'msnbc',
				'http://www.msnbc.com/ronan-farrow-daily/watch/thats-no-moon--300512323725',
				'n_farrow_moon_140709_257794'
			),
			array(
				'natgeovideo',
				'http://video.nationalgeographic.com/tv/changing-earth',
				'ngc-4MlzV_K8XoTPdXPLx2NOWq2IH410IzpO'
			),
			array(
				'natgeovideo',
				'http://video.nationalgeographic.com/video/news/140916-bison-smithsonian-zoo-vin?source=featuredvideo',
				'00000148-7a7d-d0bf-a3ff-7f7d480e0001'
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
				'sportsnet',
				'http://www.sportsnet.ca/videos/shows/tim-and-sid-video/',
				'(4\\d+001)',
				'assertRegexp'
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
				'tinypic',
				'http://tinypic.com/player.php?v=29x86j9&s=8',
				'id=29x86j9;s=8'
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
				'vidme',
				'https://vid.me/Ogt',
				'height=1280;id=Ogt;width=720'
			),
			array(
				'vk',
				'http://vkontakte.ru/video-7016284_163645555',
				'hash=eb5d7a5e6e1d8b71;oid=-7016284;vid=163645555'
			),
			array(
				'vk',
				'http://vk.com/video226156999_168963041',
				'hash=9050a9cce6465c9e;oid=226156999;vid=168963041'
			),
			array(
				'vk',
				'http://vk.com/newmusicvideos?z=video-13895667_161988074',
				'hash=de860a8e4fbe45c9;oid=-13895667;vid=161988074'
			),
			array(
				'vk',
				'http://vk.com/video_ext.php?oid=121599878&id=165723901&hash=e06b0878046e1d32',
				'hash=e06b0878046e1d32;oid=121599878;vid=165723901'
			),
			array(
				'wshh',
				'http://m.worldstarhiphop.com/video.php?v=wshh2SXFFe7W14DqQx61',
				'63175'
			),
			array(
				'xboxclips',
				'http://xboxclips.com/Soulshifted/508269a4-6f05-4b3e-a66a-fe4e91e92000',
				'id=508269a4-6f05-4b3e-a66a-fe4e91e92000;user=Soulshifted'
			),
			array(
				'xboxdvr',
				'http://xboxdvr.com/Alternat/33fb93ca-5dee-44d8-bcb2-9f2fc0994868',
				'id=33fb93ca-5dee-44d8-bcb2-9f2fc0994868;user=Alternat'
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
			array(
				'youtube',
				'http://www.youtube.com/watch?v=wZZ7oFKsKzY&t=1h23m45s',
				'h=1;id=wZZ7oFKsKzY;m=23;s=45'
			),
		);
	}

	/**
	* @dataProvider getEmbedCallbackTests
	*/
	public function testEmbedCallback($siteId, $mediaKey, $template, $expected, $assertMethod = 'assertSame', $setup = null)
	{
		if (isset($setup))
		{
			$setup();
		}

		s9e_MediaBBCodes::$cacheDir = __DIR__ . '/.cache';

		$site = array('embed_html' => $template);
		$html = s9e_MediaBBCodes::embed($mediaKey, $site, $siteId);
		$html = str_replace(' data-s9e=""', '', $html);
		$this->$assertMethod($expected, $html);
	}

	public function getEmbedCallbackTests()
	{
		return array(
			array(
				'foo',
				'foo',
				'<b>{$id}</b>',
				'<b>foo</b>'
			),
			array(
				'foo',
				'foo&bar',
				'<b>{$id}</b>',
				'<b>foo&amp;bar</b>'
			),
			array(
				'foo',
				'foo=bar;baz=quux',
				'{$foo} {$baz}',
				'bar quux'
			),
			array(
				'foo',
				'123',
				'<iframe width="560" height="315"></iframe>',
				'<div style="display:inline-block;width:100%;max-width:800px;overflow:hidden"><div style="position:relative;padding-top:56.25%"><iframe width="560" height="315" style="position:absolute;top:0;left:0;width:100%;height:100%"></iframe></div></div>',
				'assertSame',
				function ()
				{
					s9e_MediaBBCodes::$maxResponsiveWidth = 800;
				}
			),
			array(
				'foo',
				'123',
				'<iframe width="560" height="315" style="border:1px"></iframe>',
				'<div style="display:inline-block;width:100%;max-width:800px;overflow:hidden"><div style="position:relative;padding-top:56.25%"><iframe width="560" height="315" style="border:1px;position:absolute;top:0;left:0;width:100%;height:100%"></iframe></div></div>',
				'assertSame',
				function ()
				{
					s9e_MediaBBCodes::$maxResponsiveWidth = 800;
				}
			),
			array(
				'foo',
				'123',
				'<iframe width="100%" height="315"></iframe>',
				'<iframe width="100%" height="315"></iframe>',
				'assertSame',
				function ()
				{
					s9e_MediaBBCodes::$maxResponsiveWidth = 800;
				}
			),
			array(
				'foo',
				'123',
				'<iframe width="400" height="150" onload="this.height=123"></iframe>',
				'<iframe width="400" height="150" onload="this.height=123"></iframe>',
				'assertSame',
				function ()
				{
					s9e_MediaBBCodes::$maxResponsiveWidth = 800;
				}
			),
			array(
				'amazon',
				'id=B00GQT1LNO;tld=ca',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-ca.amazon.ca/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00GQT1LNO&amp;o=15&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'id=B003AKZ6I8;tld=jp',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-jp.amazon.co.jp/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B003AKZ6I8&amp;o=9&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'id=B00BET0NR6;tld=uk',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-uk.amazon.co.uk/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00BET0NR6&amp;o=2&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'B002MUC0ZY',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'B002MUC0ZY',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=foo-20"></iframe>',
				'assertSame',
				function ()
				{
					XenForo_Application::$options['s9e_AMAZON_ASSOCIATE_TAG'] = 'foo-20';
				}
			),
			array(
				'amazon',
				'id=B00ET2LTE6;tld=de',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-de.amazon.de/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00ET2LTE6&amp;o=3&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'id=B005NIKPAY;tld=fr',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-fr.amazon.fr/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B005NIKPAY&amp;o=8&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'id=B00JGOMIP6;tld=it',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm-it.amazon.it/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00JGOMIP6&amp;o=29&amp;t=_"></iframe>'
			),
			array(
				'amazon',
				'id=B002MUC0ZY;tld=com',
				'',
				'<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" src="//rcm.amazon.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B002MUC0ZY&amp;o=1&amp;t=_"></iframe>'
			),
			array(
				'audiomack',
				'id=hz-global/double-a-side-vol3;mode=album',
				'',
				'<iframe width="100%" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" height="352" src="//www.audiomack.com/embed3-album/hz-global/double-a-side-vol3"></iframe>'
			),
			array(
				'audiomack',
				'id=random-2/buy-the-world-final-1;mode=song',
				'',
				'<iframe width="100%" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" height="144" src="//www.audiomack.com/embed3/random-2/buy-the-world-final-1"></iframe>'
			),
			array(
				'bandcamp',
				'album_id=1122163921',
				'',
				'<iframe width="400" height="400" allowfullscreen="" frameborder="0" scrolling="no" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/album=1122163921"></iframe>'
			),
			array(
				'bandcamp',
				'album_id=1122163921;track_num=7',
				'',
				'<iframe width="400" height="400" allowfullscreen="" frameborder="0" scrolling="no" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/album=1122163921/t=7"></iframe>'
			),
			array(
				'cbsnews',
				'50156501',
				'',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="si=254&amp;contentValue=50156501"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf" flashvars="si=254&amp;contentValue=50156501"></object>'
			),
			array(
				'cbsnews',
				'pid=W4MVSOaNEYMq',
				'',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="http://www.cbsnews.com/common/video/cbsnews_player.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="pType=embed&amp;si=254&amp;pid=W4MVSOaNEYMq"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="http://www.cbsnews.com/common/video/cbsnews_player.swf" flashvars="pType=embed&amp;si=254&amp;pid=W4MVSOaNEYMq"></object>'
			),
			array(
				'dumpert',
				'6622635_f6d1e0fd',
				'',
				'<iframe width="560" height="315" src="http://www.dumpert.nl/embed/6622635/f6d1e0fd/" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'getty',
				'et=0KmkT83GTG1ynPe0_63zHg;height=399;id=3232182;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48%3D;width=594',
				'',
				'<iframe width="594" height="448" src="//embed.gettyimages.com/embed/3232182?et=0KmkT83GTG1ynPe0_63zHg&amp;similar=on&amp;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48=" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'gfycat',
				'height=338;id=SereneIllfatedCapybara;width=600',
				'',
				'<iframe width="600" height="338" src="//gfycat.com/iframe/SereneIllfatedCapybara" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'gfycat',
				'id=SereneIllfatedCapybara',
				'',
				'<iframe width="560" height="315" src="//gfycat.com/iframe/SereneIllfatedCapybara" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'grooveshark',
				'playlistid=74854761',
				'',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="400" height="400" data="//grooveshark.com/widget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID=74854761&amp;songID="><embed type="application/x-shockwave-flash" width="400" height="400" src="//grooveshark.com/widget.swf" allowfullscreen="" flashvars="playlistID=74854761&amp;songID="></object>'
			),
			array(
				'grooveshark',
				'songid=35292216',
				'',
				'<object type="application/x-shockwave-flash" typemustmatch="" width="400" height="40" data="//grooveshark.com/songWidget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID=&amp;songID=35292216"><embed type="application/x-shockwave-flash" width="400" height="40" src="//grooveshark.com/songWidget.swf" allowfullscreen="" flashvars="playlistID=&amp;songID=35292216"></object>'
			),
			array(
				'imgur',
				'9UGCL',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="100%" height="550" src="//imgur.com/a/9UGCL/embed"></iframe>'
			),
			array(
				'imgur',
				'id=9UGCL;type=album',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="100%" height="550" src="//imgur.com/a/9UGCL/embed"></iframe>'
			),
			array(
				'imgur',
				'height=389;id=u7Yo0Vy;type=gifv;width=915',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="915" height="389" src="//i.imgur.com/u7Yo0Vy.gifv#embed"></iframe>'
			),
			array(
				'kickstarter',
				'1869987317/wish-i-was-here-1',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="220" height="380" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html"></iframe>'
			),
			array(
				'kickstarter',
				'card=card;id=1869987317%2Fwish-i-was-here-1',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="220" height="380" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html"></iframe>'
			),
			array(
				'kickstarter',
				'id=1869987317%2Fwish-i-was-here-1;video=video',
				'',
				'<iframe allowfullscreen="" frameborder="0" scrolling="no" width="480" height="360" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/video.html"></iframe>'
			),
			array(
				'livestream',
				'account_id=12014794;event_id=3788876;video_id=77576437',
				'',
				'<iframe width="640" height="360" allowfullscreen="" frameborder="0" scrolling="no" src="//new.livestream.com/accounts/12014794/events/3788876/videos/77576437/player?autoPlay=false"></iframe>'
			),
			array(
				'soundcloud',
				'http://api.soundcloud.com/tracks/98282116',
				'',
				'<iframe width="100%" height="166" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=http://api.soundcloud.com/tracks/98282116"></iframe>'
			),
			array(
				'soundcloud',
				'id=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F12345%3Fsecret_token%3Ds-foobar;secret_token=s-foobar',
				'',
				'<iframe width="100%" height="166" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/12345?secret_token=s-foobar&amp;secret_token=s-foobar"></iframe>'
			),
			array(
				'soundcloud',
				'id=https%3A%2F%2Fsoundcloud.com%2Fmatt0753%2Firoh-ii-deep-voice%2Fs-UpqTm;secret_token=s-UpqTm;track_id=51465673',
				'',
				'<iframe width="100%" height="166" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/51465673&amp;secret_token=s-UpqTm"></iframe>'
			),
			array(
				'soundcloud',
				'nruau/nruau-mix2',
				'',
				'<iframe width="100%" height="166" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" src="https://w.soundcloud.com/player/?url=https://soundcloud.com/nruau/nruau-mix2"></iframe>'
			),
			array(
				'spotify',
				'uri=spotify%3Atrack%3A5JunxkcjfCYcY7xJ29tLai',
				'',
				'<iframe width="400" height="480" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?view=coverart&amp;uri=spotify:track:5JunxkcjfCYcY7xJ29tLai"></iframe>'
			),
			array(
				'spotify',
				'uri=spotify%3Atrackset%3APREFEREDTITLE%3A5Z7ygHQo02SUrFmcgpwsKW%2C1x6ACsKV4UdWS2FMuPFUiT%2C4bi73jCM02fMpkI11Lqmfe',
				'',
				'<iframe width="400" height="480" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?view=coverart&amp;uri=spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe"></iframe>'
			),
			array(
				'spotify',
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt',
				'',
				'<iframe width="400" height="480" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?view=coverart&amp;uri=spotify:user:ozmoetr:playlist:4yRrCWNhWOqWZx5lmFqZvt"></iframe>'
			),
			array(
				'spotify',
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz',
				'',
				'<iframe width="400" height="480" allowfullscreen="" frameborder="0" scrolling="no" src="https://embed.spotify.com/?view=coverart&amp;uri=spotify:album:5OSzFvFAYuRh93WDNCTLEz"></iframe>'
			),
			array(
				'ted',
				'talks/eli_pariser_beware_online_filter_bubbles.html',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe>'
			),
			array(
				'ted',
				'talks/eli_pariser_beware_online_filter_bubbles',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe>'
			),
			array(
				'twitch',
				'channel=minigolf2000',
				'',
				'<iframe width="620" height="378" allowfullscreen="" frameborder="0" scrolling="no" src="//s9e.github.io/iframe/twitch.min.html#channel=minigolf2000"></iframe>',
			),
			array(
				'twitch',
				'archive_id=361358487;channel=minigolf2000',
				'',
				'<iframe width="620" height="378" allowfullscreen="" frameborder="0" scrolling="no" src="//s9e.github.io/iframe/twitch.min.html#channel=minigolf2000&amp;videoId=a361358487"></iframe>',
			),
			array(
				'ustream',
				'cid=16234409',
				'',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="//www.ustream.tv/embed/16234409"></iframe>'
			),
			array(
				'ustream',
				'vid=40688256',
				'',
				'<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" src="//www.ustream.tv/embed/recorded/40688256"></iframe>'
			),
			array(
				'wsj',
				'09FB2B3B-583E-4284-99D8-FEF6C23BE4E2',
				'',
				'<iframe width="512" height="288" src="http://live.wsj.com/public/page/embed-09FB2B3B_583E_4284_99D8_FEF6C23BE4E2.html" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'xboxclips',
				'id=508269a4-6f05-4b3e-a66a-fe4e91e92000;user=Soulshifted',
				'',
				'<iframe width="560" height="315" src="//xboxclips.com/Soulshifted/508269a4-6f05-4b3e-a66a-fe4e91e92000/embed" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'xboxdvr',
				'id=33fb93ca-5dee-44d8-bcb2-9f2fc0994868;user=Alternat',
				'',
				'<iframe width="560" height="430" src="//xboxdvr.com/Alternat/33fb93ca-5dee-44d8-bcb2-9f2fc0994868/embed" allowfullscreen="" frameborder="0" scrolling="no"></iframe>'
			),
			array(
				'youtube',
				'-cEzsCAzTak',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/-cEzsCAzTak"></iframe>'
			),
			array(
				'youtube',
				'id=9bZkp7q19f0;t=113',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/9bZkp7q19f0?start=113"></iframe>'
			),
			array(
				'youtube',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA"></iframe>'
			),
			array(
				'youtube',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA;t=123',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA&amp;start=123"></iframe>'
			),
			array(
				'youtube',
				'h=1;id=wZZ7oFKsKzY;m=23;s=45',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/wZZ7oFKsKzY?start=5025"></iframe>'
			),
			array(
				'youtube',
				'id=wZZ7oFKsKzY;m=23;s=45',
				'',
				'<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" src="//www.youtube.com/embed/wZZ7oFKsKzY?start=1425"></iframe>'
			),
		);
	}
}