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
		include __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
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
				'twitch',
				'http://www.twitch.tv/minigolf2000/b/361358487',
				'archive_id=361358487;channel=minigolf2000'
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
				array(
					'embed_html' => '<b>{$id}</b>'
				),
				'<b>foo</b>'
			),
			array(
				'foo&bar',
				array(
					'embed_html' => '<b>{$id}</b>'
				),
				'<b>foo&amp;bar</b>'
			),
			array(
				'foo=bar;baz=quux',
				array(
					'embed_html' => '{$foo} {$baz}'
				),
				'bar quux'
			),
		);
	}
}