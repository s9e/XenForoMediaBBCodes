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
			s9e_MediaBBCodes::$customDimensions   = null;
			s9e_MediaBBCodes::$excludedSites      = null;
			s9e_MediaBBCodes::$tags               = null;
		}

		XenForo_Application::$options = array(
			's9e_EXCLUDE_SITES'        => null,
			's9e_excluded_sites'       => null,
			's9e_custom_callbacks'     => null,
			's9e_custom_dimensions'    => null,
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
	* @requires PHP 7.1
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

	public function testValidateCustomDimensionsValue()
	{
		$text = "
			YouTube = 123,456
			Twitter = 222,444
		";
		s9e_MediaBBCodes::validateCustomDimensions($text);

		$this->assertSame("twitter=222,444\nyoutube=123,456\n", $text);
	}

	public function testValidateCustomDimensionsReinstall()
	{
		$text = '';
		s9e_MediaBBCodes::validateCustomDimensions($text);
		$this->assertReinstallWasCalled();
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

	public function testMatchCallbackReturnsFalseIfSiteIdIsMissing()
	{
		$this->assertFalse(s9e_MediaBBCodes::match('http://www', '', array()));
	}

	public function testEmbedCallbackReturnsAnHtmlErrorMessageIfSiteIdIsMissing()
	{
		$this->assertContains('outdated version', s9e_MediaBBCodes::embed('foo', array()));
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
				'amazon',
				'http://www.amazon.it/foo/',
				false
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
//			array(
//				'bbcnews',
//				'http://www.bbc.com/news/business-29149086',
//				'av/business-29149086/alibaba-and-jack-ma-in-60-seconds'
//			),
			array(
				'blab',
				'https://blab.im/05b6ce88279f40798069bb6227a04fce',
				'05b6ce88279f40798069bb6227a04fce'
			),
			array(
				'blab',
				'https://blab.im/about',
				false
			),
//			array(
//				'bleacherreport',
//				'http://bleacherreport.com/articles/2415420-creating-a-starting-xi-of-the-most-overrated-players-in-world-football',
//				'dtYjVhdDr5492cyQTjVPDcM--Mg2rJj5'
//			),
			array(
				'cbsnews',
				'http://www.cbsnews.com/video/watch/?id=50156501n',
				'50156501'
			),
			array(
				'cbsnews',
				'http://www.cbsnews.com/videos/is-carbonated-water-a-healthy-option/',
				'is-carbonated-water-a-healthy-option'
			),
//			array(
//				'comedycentral',
//				'http://www.comedycentral.com/video-clips/uu5qz4/key-and-peele-dueling-hats',
//				'mgid:arc:video:comedycentral.com:bc275e2f-48e3-46d9-b095-0254381497ea'
//			),
			array(
				'dumpert',
				'http://www.dumpert.nl/mediabase/6622635/f6d1e0fd/lompe_boer_op_zuidlaardermarkt_doet_shetlandpony_pijn.html',
				'6622635/f6d1e0fd'
			),
			array(
				'eighttracks',
				'http://8tracks.com/mc_raw/canadian-flavored-indie-rock-grilled-cheese',
				'1007987'
			),
			array(
				'espn',
				'http://espn.go.com/video/clip?id=10936987',
				'10936987'
			),
			array(
				'espn',
				'http://www.espn.com/video/clip?id=17474659',
				'17474659'
			),
			array(
				'espn',
				'http://www.espn.com/video/clip/_/id/17474659/categoryid/2564308',
				'17474659'
			),
			array(
				'espn',
				'http://espn.go.com/new-york/nba/story/_/id/11196159/carmelo-anthony-agent-says-made-decision',
				false
			),
			array(
				'facebook',
				'https://www.facebook.com/FacebookDevelopers/posts/10151471074398553',
				'id=10151471074398553;type=post;user=FacebookDevelopers'
			),
			array(
				'facebook',
				'https://www.facebook.com/ign/videos/10153762113196633/',
				'id=10153762113196633;type=video;user=ign'
			),
			array(
				'foratv',
				'http://fora.tv/2009/07/30/Marijuana_Economics',
				'9677'
			),
//			array(
//				'gametrailers',
//				'http://www.gametrailers.com/videos/view/pop-fiction/102300-Metal-Gear-Solid-3-Still-in-a-Dream',
//				'2954127'
//			),
//			array(
//				'getty',
//				'http://gty.im/3232182',
//				'(et=[-\\w]{22};height=399;id=3232182;sig=[-\\w]{43}%3D;width=594)',
//				'assertRegexp'
//			),
//			array(
//				'getty',
//				'http://www.gettyimages.co.uk/detail/3232182',
//				'(et=[-\\w]{22};height=399;id=3232182;sig=[-\\w]{43}%3D;width=594)',
//				'assertRegexp'
//			),
			array(
				'gfycat',
				'http://gfycat.com/SereneIllfatedCapybara',
				'height=338;id=SereneIllfatedCapybara;width=600'
			),
			array(
				'gifs',
				'https://j.gifs.com/Y6YZoO.gif',
				'height=200;id=Y6YZoO;width=200'
			),
			array(
				'googleplus',
				'https://plus.google.com/+TonyHawk/posts/C5TMsDZJWBd',
				'name=TonyHawk;pid=C5TMsDZJWBd'
			),
			array(
				'googleplus',
				'https://plus.google.com/+JacekMiłaszewski/posts/HJEFk3SX1sL',
				'name=JacekMi%C5%82aszewski;pid=HJEFk3SX1sL'
			),
			array(
				'googleplus',
				'https://plus.google.com/+JacekMi%C5%82aszewski/posts/HJEFk3SX1sL',
				'name=JacekMi%C5%82aszewski;pid=HJEFk3SX1sL'
			),
			array(
				'googleplus',
				'https://plus.google.com/106189723444098348646/posts/V8AojCoTzxV',
				'oid=106189723444098348646;pid=V8AojCoTzxV'
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
//			array(
//				'healthguru',
//				'http://college.healthguru.com/video/handling-heartache',
//				'ZX'
//			),
//			array(
//				'healthguru',
//				'http://college.healthguru.com/content/video/watch/100502/handling-heartache',
//				'RX'
//			),
			array(
				'hudl',
				'http://www.hudl.com/athlete/2067184/highlights/163744377',
				'athlete=2067184;highlight=163744377'
			),
			array(
				'hudl',
				'http://www.hudl.com/v/CVmja',
				'athlete=2122944;highlight=5721c090dfe23b2d68a2283b'
			),
			array(
				'hulu',
				'http://www.hulu.com/watch/484180',
				'zPFCgxncn97IFkqEnZ-kRA'
			),
			array(
				'imgur',
				'http://imgur.com/a/9UGCL',
				'a/9UGCL'
			),
			array(
				'imgur',
				'http://i.imgur.com/u7Yo0Vy.gifv',
				'u7Yo0Vy'
			),
			array(
				'imgur',
				'https://imgur.com/AsQ0K3P',
				'AsQ0K3P'
			),
			array(
				'imgur',
				'http://imgur.com/r/animals',
				false
			),
			array(
				'imgur',
				'http://imgur.com/user/name',
				false
			),
			array(
				'indiegogo',
				'http://www.indiegogo.com/projects/gameheart-redesigned',
				'gameheart-redesigned'
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
				'libsyn',
				'http://bunkerbuddies.libsyn.com/interstellar-w-brandie-posey',
				'3521244'
			),
			array(
				'livecap',
				'https://www.livecap.tv/s/esl_sc2/uZoEz6RR1eA',
				'channel=esl_sc2;id=uZoEz6RR1eA'
			),
			array(
				'livecap',
				'https://www.livecap.tv/t/riotgames/uLxUzBTBs7u',
				'channel=riotgames;id=uLxUzBTBs7u'
			),
//			array(
//				'livestream',
//				'http://livestream.com/ccscsl/USChessChampionships/videos/83267610',
//				'account_id=3913412;event_id=3933674;video_id=83267610'
//			),
//			array(
//				'livestream',
//				'http://livestre.am/1aHRU',
//				'channel=maps_cp;clip_id=pla_d1501f90-438c-401d-98ae-e96ab34a09ae'
//			),
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
				'nhl',
				'https://www.nhl.com/video/recap-min-2-ott-1-fot/t-277753022/c-46330703',
				'c=46330703;t=277753022'
			),
			array(
				'nytimes',
				'https://www.nytimes.com/video/movies/100000005543607/trailer-coco.html',
				'100000005543607'
			),
			array(
				'orfium',
				'https://www.orfium.com/album/24371/everybody-loves-kanye-totom/',
				'album_id=24371'
			),
			array(
				'orfium',
				'https://www.orfium.com/live-set/614763/foof-no-lights-5-foof/',
				'set_id=614763'
			),
			array(
				'orfium',
				'https://www.orfium.com/playlist/511651/electronic-live-sessions-creamtronic/',
				'playlist_id=511651'
			),
			array(
				'orfium',
				'https://www.orfium.com/track/625367/the-ambience-of-the-goss-vistas/',
				'track_id=625367'
			),
			array(
				'podbean',
				'http://wendyswordsofwisdom.podbean.com/e/tiffany-stevensons-words-of-wisdom/',
				'dbn9j-4ede53'
			),
			array(
				'reddit',
				'http://www.reddit.com/r/xenforo/comments/2synou/xenforo_144_released/cnua3uz',
				'xenforo/comments/2synou/xenforo_144_released/cnua3uz'
			),
			array(
				'soundcloud',
				'http://api.soundcloud.com/tracks/98282116',
				'id=tracks%2F98282116;track_id=98282116'
			),
//			array(
//				'soundcloud',
//				'https://soundcloud.com/andrewbird/three-white-horses',
//				'id=andrewbird%2Fthree-white-horses;track_id=59509713'
//			),
			array(
				'soundcloud',
				'https://api.soundcloud.com/tracks/168988860?secret_token=s-GT9Cd',
				'id=tracks%2F168988860;secret_token=s-GT9Cd;track_id=168988860'
			),
			array(
				'sportsnet',
				'http://www.sportsnet.ca/videos/shows/tim-and-sid-video/',
				'(\\d+001)',
				'assertRegexp'
			),
			array(
				'spotify',
				'http://open.spotify.com/user/ozmoetr/playlist/4yRrCWNhWOqWZx5lmFqZvt',
				'user/ozmoetr/playlist/4yRrCWNhWOqWZx5lmFqZvt'
			),
			array(
				'spotify',
				'https://play.spotify.com/album/5OSzFvFAYuRh93WDNCTLEz',
				'album/5OSzFvFAYuRh93WDNCTLEz'
			),
			array(
				'stitcher',
				'http://www.stitcher.com/podcast/panoply/wall-street-journal-tech-news-briefing-51309/e/50838946',
				'eid=50838946;fid=5446'
			),
			array(
				'tinypic',
				'http://tinypic.com/player.php?v=29x86j9&s=8',
				'id=29x86j9;s=8'
			),
//			array(
//				'traileraddict',
//				'http://www.traileraddict.com/muppets-most-wanted/super-bowl-tv-spot',
//				'86191'
//			),
			array(
				'tumblr',
				'http://mrbenvey.tumblr.com/post/104191225637',
				'did=5f3b4bc6718317df9c2b1e77c20839ab94f949cd;id=104191225637;key=uFhWDPKj-bGU0ZlDAnUyxg;name=mrbenvey'
			),
			array(
				'twitch',
				'http://www.twitch.tv/twitch/v/29415830?t=17m17s',
				'channel=twitch;t=17m17s;video_id=29415830'
			),
			array(
				'twitch',
				'https://clips.twitch.tv/twitch/HorribleWoodpeckerHassanChop',
				'channel=twitch;clip_id=HorribleWoodpeckerHassanChop'
			),
			array(
				'twitch',
				'https://clips.twitch.tv/AcceptableCooperativeYogurtTwitchRPG',
				'clip_id=AcceptableCooperativeYogurtTwitchRPG'
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
				'vox',
				'http://www.vox.com/2015/7/21/9005857/ant-man-marvel-apology-review#ooid=ltbzJkdTpKpE-O6hOfD3YJew3t3MppXb',
				'ltbzJkdTpKpE-O6hOfD3YJew3t3MppXb'
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
				'http://vk.com/video_ext.php?oid=121599878&id=165723901&hash=e06b0878046e1d32',
				'hash=e06b0878046e1d32;oid=121599878;vid=165723901'
			),
			array(
				'xboxclips',
				'http://xboxclips.com/Soulshifted/508269a4-6f05-4b3e-a66a-fe4e91e92000',
				'id=508269a4-6f05-4b3e-a66a-fe4e91e92000;user=Soulshifted'
			),
			array(
				'xboxdvr',
				'http://xboxdvr.com/gamer/LOXITANE/video/12720583',
				'id=12720583;user=LOXITANE'
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
				'id=wZZ7oFKsKzY;t=5025'
			),
			array(
				'youtube',
				'http://www.youtube.com/watch?v=wZZ7oFKsKzY&t=1234',
				'id=wZZ7oFKsKzY;t=1234'
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
				'amazon',
				'id=B00GQT1LNO;tld=ca',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=CA&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=CA&amp;asins=B00GQT1LNO&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'id=B003AKZ6I8;tld=jp',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-fe.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=JP&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=JP&amp;asins=B003AKZ6I8&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'id=B00BET0NR6;tld=uk',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=GB&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=GB&amp;asins=B00BET0NR6&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'B002MUC0ZY',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=US&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=US&amp;asins=B002MUC0ZY&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'B002MUC0ZY',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=US&amp;ad_type=product_link&amp;tracking_id=foo-20&amp;marketplace=amazon&amp;region=US&amp;asins=B002MUC0ZY&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>',
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
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=DE&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=DE&amp;asins=B00ET2LTE6&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'id=B005NIKPAY;tld=fr',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=FR&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=FR&amp;asins=B005NIKPAY&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'amazon',
				'id=B00JGOMIP6;tld=it',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//rcm-eu.amazon-adsystem.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins=B00JGOMIP6&amp;o=29&amp;t="></iframe></span></span>'
			),
			array(
				'amazon',
				'id=B002MUC0ZY;tld=com',
				'',
				'<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&amp;OneJS=1&amp;Operation=GetAdHtml&amp;MarketPlace=Com&amp;ad_type=product_link&amp;tracking_id=&amp;marketplace=amazon&amp;region=Com&amp;asins=B002MUC0ZY&amp;show_border=true&amp;link_opens_in_new_window=true"></iframe></span></span>'
			),
			array(
				'audiomack',
				'id=hz-global/double-a-side-vol3;mode=album',
				'',
				'<iframe data-s9e-mediaembed="audiomack" allowfullscreen="" scrolling="no" src="https://www.audiomack.com/embed/album/hz-global/double-a-side-vol3" style="border:0;height:400px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'audiomack',
				'id=random-2/buy-the-world-final-1;mode=song',
				'',
				'<iframe data-s9e-mediaembed="audiomack" allowfullscreen="" scrolling="no" src="https://www.audiomack.com/embed/song/random-2/buy-the-world-final-1" style="border:0;height:252px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'bandcamp',
				'album_id=1122163921',
				'',
				'<span data-s9e-mediaembed="bandcamp" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/album=1122163921"></iframe></span></span>'
			),
			array(
				'bandcamp',
				'album_id=1122163921;track_num=7',
				'',
				'<span data-s9e-mediaembed="bandcamp" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/album=1122163921/t=7"></iframe></span></span>'
			),
			array(
				'brightcove',
				'bckey=AQ%7E%7E%2CAAAABvb_NGE%7E%2CDMkZt2E6wO3dFlbHM7HTX1y1bVRDHLp_;bcpid=1065729157001;bctid=624233815001',
				'',
				'<span data-s9e-mediaembed="brightcove" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://link.brightcove.com/services/player/bcpid1065729157001?bckey=AQ~~,AAAABvb_NGE~,DMkZt2E6wO3dFlbHM7HTX1y1bVRDHLp_&amp;bctid=624233815001&amp;secureConnections=true&amp;secureHTMLConnections=true&amp;autoStart=false&amp;height=100%25&amp;width=100%25" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'cbsnews',
				'50156501',
				'',
				'<span data-s9e-mediaembed="cbsnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:62.5%;padding-bottom:calc(56.25% + 40px)"><object data="//i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="si=254&amp;contentValue=50156501"></object></span></span>'
			),
			array(
				'cbsnews',
				'pid=B2AtjLUWB4Vj',
				'',
				'<span data-s9e-mediaembed="cbsnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:62.1875%;padding-bottom:calc(56.25% + 38px)"><object data="//www.cbsnews.com/common/video/cbsnews_player.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="pType=embed&amp;si=254&amp;pid=B2AtjLUWB4Vj"></object></span></span>'
			),
			array(
				'cnn',
				'tv/2015/06/09/airplane-yoga-rachel-crane-ts-orig.cnn',
				'',
				'<span data-s9e-mediaembed="cnn" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//edition.cnn.com/video/api/embed.html#/video/tv/2015/06/09/airplane-yoga-rachel-crane-ts-orig.cnn" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'democracynow',
				'2014/7/2/dn_at_almedalen_week_at_swedens',
				'',
				'<span data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/story/2014/7/2/dn_at_almedalen_week_at_swedens"></iframe></span></span>'
			),
			array(
				'democracynow',
				'blog/2015/3/13/part_2_bruce_schneier_on_the',
				'',
				'<span data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/blog/2015/3/13/part_2_bruce_schneier_on_the"></iframe></span></span>'
			),
			array(
				'democracynow',
				'shows/2006/2/20',
				'',
				'<span data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/show/2006/2/20"></iframe></span></span>'
			),
			array(
				'democracynow',
				'2015/5/21/headlines',
				'',
				'<span data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/headlines/2015/5/21"></iframe></span></span>'
			),
			array(
				'dumpert',
				'6622635_f6d1e0fd',
				'',
				'<span data-s9e-mediaembed="dumpert" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.dumpert.nl/embed/6622635/f6d1e0fd/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'dumpert',
				'6622635/f6d1e0fd',
				'',
				'<span data-s9e-mediaembed="dumpert" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.dumpert.nl/embed/6622635/f6d1e0fd/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'facebook',
				'10151471074398553',
				'',
				'<iframe data-s9e-mediaembed="facebook" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/facebook.min.html#10151471074398553" style="border:0;height:360px;max-width:640px;width:100%"></iframe>'
			),
			array(
				'facebook',
				'id=10153762113196633;type=video',
				'',
				'<iframe data-s9e-mediaembed="facebook" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/facebook.min.html#video10153762113196633" style="border:0;height:360px;max-width:640px;width:100%"></iframe>'
			),
			array(
				'getty',
				'et=0KmkT83GTG1ynPe0_63zHg;height=399;id=3232182;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48%3D;width=594',
				'',
				'<span data-s9e-mediaembed="getty" style="display:inline-block;width:100%;max-width:594px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:75.420875420875%;padding-bottom:calc(67.171717171717% + 49px)"><iframe allowfullscreen="" scrolling="no" src="//embed.gettyimages.com/embed/3232182?et=0KmkT83GTG1ynPe0_63zHg&amp;sig=adwXi8c671w6BF-VxLAckfZZa3teIln3t9BDYiCil48=" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'gfycat',
				'height=338;id=SereneIllfatedCapybara;width=600',
				'',
				'<span data-s9e-mediaembed="gfycat" style="display:inline-block;width:100%;max-width:600px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.333333333333%"><iframe allowfullscreen="" scrolling="no" src="//gfycat.com/iframe/SereneIllfatedCapybara" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'gfycat',
				'id=SereneIllfatedCapybara',
				'',
				'<span data-s9e-mediaembed="gfycat" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//gfycat.com/iframe/SereneIllfatedCapybara" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'gifs',
				'height=360;id=Y6YZoO;width=360',
				'',
				'<span data-s9e-mediaembed="gifs" style="display:inline-block;width:100%;max-width:360px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//gifs.com/embed/Y6YZoO" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'googleplus',
				'name=TonyHawk;pid=C5TMsDZJWBd',
				'',
				'<iframe data-s9e-mediaembed="googleplus" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" style="border:0;height:240px;max-width:450px;width:100%" src="https://s9e.github.io/iframe/googleplus.min.html#+TonyHawk/posts/C5TMsDZJWBd"></iframe>'
			),
			array(
				'googleplus',
				'oid=106189723444098348646;pid=V8AojCoTzxV',
				'',
				'<iframe data-s9e-mediaembed="googleplus" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" style="border:0;height:240px;max-width:450px;width:100%" src="https://s9e.github.io/iframe/googleplus.min.html#106189723444098348646/posts/V8AojCoTzxV"></iframe>'
			),
			array(
				'gist',
				'foo/123',
				'',
				'<iframe data-s9e-mediaembed="gist" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="" src="https://s9e.github.io/iframe/gist.min.html#foo/123" style="border:0;height:180px;width:100%"></iframe>'
			),
			array(
				'hudl',
				'athlete=2067184;highlight=5721c090dfe23b2d68a2283b',
				'',
				'<span data-s9e-mediaembed="hudl" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.hudl.com/embed/athlete/2067184/highlights/5721c090dfe23b2d68a2283b" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'imgur',
				'id=jhEHi;type=album',
				'',
				'<iframe data-s9e-mediaembed="imgur" allowfullscreen="" onload="var b=Math.random();window.addEventListener(\'message\',function(a){a.data.id==b&amp;&amp;(style.height=a.data.height+\'px\',style.width=a.data.width+\'px\')});contentWindow.postMessage(\'s9e:\'+b,\'https://s9e.github.io\')" scrolling="no" style="border:0;height:450px;max-width:100%;width:568px" src="https://s9e.github.io/iframe/imgur.min.html#a/jhEHi"></iframe>'
			),
			array(
				'kickstarter',
				'1869987317/wish-i-was-here-1',
				'',
				'<span data-s9e-mediaembed="kickstarter" style="display:inline-block;width:100%;max-width:220px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:190.909091%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'kickstarter',
				'card=card;id=1869987317%2Fwish-i-was-here-1',
				'',
				'<span data-s9e-mediaembed="kickstarter" style="display:inline-block;width:100%;max-width:220px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:190.909091%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/card.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'kickstarter',
				'id=1869987317%2Fwish-i-was-here-1;video=video',
				'',
				'<span data-s9e-mediaembed="kickstarter" style="display:inline-block;width:100%;max-width:480px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:75%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/1869987317/wish-i-was-here-1/widget/video.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'livecap',
				'channel=esl_sc2;id=uZoEz6RR1eA',
				'',
				'<span data-s9e-mediaembed="livecap" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.livecap.tv/s/embed/esl_sc2/uZoEz6RR1eA" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'livestream',
				'account_id=12014794;event_id=3788876;video_id=77576437',
				'',
				'<span data-s9e-mediaembed="livestream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//livestream.com/accounts/12014794/events/3788876/videos/77576437/player?autoPlay=false"></iframe></span></span>'
			),
			array(
				'livestream',
				'channel=maps_cp;clip_id=pla_d1501f90-438c-401d-98ae-e96ab34a09ae',
				'',
				'<span data-s9e-mediaembed="livestream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//cdn.livestream.com/embed/maps_cp?layout=4&amp;autoplay=false&amp;clip=pla_d1501f90-438c-401d-98ae-e96ab34a09ae"></iframe></span></span>'
			),
			array(
				'nhl',
				'c=46330703;t=277753022',
				'',
				'<span data-s9e-mediaembed="nhl" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.nhl.com/video/embed/t-277753022/c-46330703?autostart=false"></iframe></span></span>'
			),
			array(
				'orfium',
				'album_id=24371',
				'',
				'<iframe data-s9e-mediaembed="orfium" allowfullscreen="" scrolling="no" src="https://www.orfium.com/embedded/album/24371" style="border:0;height:550px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'orfium',
				'set_id=614763',
				'',
				'<iframe data-s9e-mediaembed="orfium" allowfullscreen="" scrolling="no" src="https://www.orfium.com/embedded/live-set/614763" style="border:0;height:275px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'orfium',
				'playlist_id=511651',
				'',
				'<iframe data-s9e-mediaembed="orfium" allowfullscreen="" scrolling="no" src="https://www.orfium.com/embedded/playlist/511651" style="border:0;height:275px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'orfium',
				'track_id=625367',
				'',
				'<iframe data-s9e-mediaembed="orfium" allowfullscreen="" scrolling="no" src="https://www.orfium.com/embedded/track/625367" style="border:0;height:275px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'pinterest',
				'340444053067283362',
				'',
				'<iframe data-s9e-mediaembed="pinterest" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/pinterest.min.html#340444053067283362" style="border:0;height:360px;max-width:345px;width:100%"></iframe>'
			),
			array(
				'pinterest',
				'felipeandresdp/men-watches',
				'',
				'<iframe data-s9e-mediaembed="pinterest" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/pinterest.min.html#felipeandresdp/men-watches" style="border:0;height:360px;max-width:730px;width:100%"></iframe>'
			),
			array(
				'soundcloud',
				'http://api.soundcloud.com/tracks/98282116',
				'',
				'<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=http://api.soundcloud.com/tracks/98282116" style="border:0;height:166px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'soundcloud',
				'nruau/nruau-mix2',
				'',
				'<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=https%3A//soundcloud.com/nruau/nruau-mix2" style="border:0;height:166px;max-width:900px;width:100%"></iframe>'
			),
			array(
				'spotify',
				'path=user%2Fozmoetr%2Fplaylist%2F4yRrCWNhWOqWZx5lmFqZvt',
				'',
				'<span data-s9e-mediaembed="spotify" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allow="encrypted-media" allowfullscreen="" scrolling="no" src="https://open.spotify.com/embed/user/ozmoetr/playlist/4yRrCWNhWOqWZx5lmFqZvt" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'spotify',
				'path=album%2F5OSzFvFAYuRh93WDNCTLEz',
				'',
				'<span data-s9e-mediaembed="spotify" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allow="encrypted-media" allowfullscreen="" scrolling="no" src="https://open.spotify.com/embed/album/5OSzFvFAYuRh93WDNCTLEz" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'ted',
				'talks/eli_pariser_beware_online_filter_bubbles.html',
				'',
				'<span data-s9e-mediaembed="ted" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe></span></span>'
			),
			array(
				'ted',
				'talks/eli_pariser_beware_online_filter_bubbles',
				'',
				'<span data-s9e-mediaembed="ted" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//embed.ted.com/talks/eli_pariser_beware_online_filter_bubbles.html"></iframe></span></span>'
			),
			array(
				'theguardian',
				'commentisfree/video/2016/jun/22/eu-referendum-welcome-to-the-divided-angry-kingdom-video',
				'',
				'<span data-s9e-mediaembed="theguardian" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//embed.theguardian.com/embed/video/commentisfree/video/2016/jun/22/eu-referendum-welcome-to-the-divided-angry-kingdom-video" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'twitch',
				'channel=minigolf2000',
				'',
				'<span data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//player.twitch.tv/?autoplay=false&amp;channel=minigolf2000"></iframe></span></span>',
			),
			array(
				'twitch',
				'channel=twitch;t=17m17s;video_id=29415830',
				'',
				'<span data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//player.twitch.tv/?autoplay=false&amp;video=v29415830&amp;time=17m17s"></iframe></span></span>'
			),
			array(
				'twitch',
				'channel=twitch;clip_id=HorribleWoodpeckerHassanChop',
				'',
				'<span data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//clips.twitch.tv/embed?autoplay=false&amp;clip=twitch/HorribleWoodpeckerHassanChop"></iframe></span></span>'
			),
			array(
				'twitch',
				'clip_id=AcceptableCooperativeYogurtTwitchRPG',
				'',
				'<span data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//clips.twitch.tv/embed?autoplay=false&amp;clip=AcceptableCooperativeYogurtTwitchRPG"></iframe></span></span>'
			),
			array(
				'ustream',
				'cid=16234409',
				'',
				'<span data-s9e-mediaembed="ustream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.ustream.tv/embed/16234409?html5ui"></iframe></span></span>'
			),
			array(
				'ustream',
				'vid=40688256',
				'',
				'<span data-s9e-mediaembed="ustream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.ustream.tv/embed/recorded/40688256?html5ui"></iframe></span></span>'
			),
			array(
				'xboxclips',
				'id=508269a4-6f05-4b3e-a66a-fe4e91e92000;user=Soulshifted',
				'',
				'<span data-s9e-mediaembed="xboxclips" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//xboxclips.com/Soulshifted/508269a4-6f05-4b3e-a66a-fe4e91e92000/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'xboxdvr',
				'id=12720583;user=LOXITANE',
				'',
				'<span data-s9e-mediaembed="xboxdvr" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//xboxdvr.com/gamer/LOXITANE/video/12720583/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'
			),
			array(
				'youtube',
				'-cEzsCAzTak',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/-cEzsCAzTak/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/-cEzsCAzTak"></iframe></span></span>'
			),
			array(
				'youtube',
				'id=9bZkp7q19f0;t=113',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/9bZkp7q19f0/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/9bZkp7q19f0?start=113"></iframe></span></span>'
			),
			array(
				'youtube',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/pC35x6iIPmo/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA"></iframe></span></span>'
			),
			array(
				'youtube',
				'id=pC35x6iIPmo;list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA;t=123',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/pC35x6iIPmo/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/pC35x6iIPmo?list=PLOU2XLYxmsIIxJrlMIY5vYXAFcO5g83gA&amp;start=123"></iframe></span></span>'
			),
			array(
				'youtube',
				'h=1;id=wZZ7oFKsKzY;m=23;s=45',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/wZZ7oFKsKzY/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/wZZ7oFKsKzY?start=5025"></iframe></span></span>'
			),
			array(
				'youtube',
				'id=wZZ7oFKsKzY;m=23;s=45',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/wZZ7oFKsKzY/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/wZZ7oFKsKzY?start=1425"></iframe></span></span>'
			),
			array(
				'youtube',
				'id=6GMOTrIjVao;m=1;s=',
				'',
				'<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/6GMOTrIjVao/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/6GMOTrIjVao?start=60"></iframe></span></span>'
			),
		);
	}
}