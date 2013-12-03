#!/usr/bin/php
<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

include __DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/autoloader.php';

$sites = simplexml_load_file(__DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/Plugins/MediaEmbed/Configurator/sites.xml');

$configurator = new s9e\TextFormatter\Configurator;
$rendererGenerator = $configurator->setRendererGenerator('PHP');
$rendererGenerator->forceEmptyElements = false;
$rendererGenerator->useEmptyElements   = false;

$php = <<<'NOWDOC'
<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
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
		if (preg_match('(^https?://)', $mediaKey))
		{
			$regexps = (isset($site['regexes']) && is_array($site['regexes']))
			         ? $site['regexes']
			         : array();

			// If the URL is stored in the media site, reparse it and store the captures
			$vars = self::getNamedCaptures($mediaKey, $regexps);
		}
		elseif (preg_match('(^(\\w+=[^;]*)(?>;(?1))*$)', $mediaKey))
		{
			// If the URL looks like a series of key=value pairs, add them to $vars
			$vars = array();
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}

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
			$page = file_get_contents(
				'compress.zlib://' . $url,
				false,
				stream_context_create(array(
					'http' => array(
						'header' => 'Accept-Encoding: gzip'
					)
				))
			);

			if (isset($cacheFile))
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
NOWDOC;

$php = explode("\n", $php);

$dom = new DOMDocument('1.0', 'utf-8');
$addon = $dom->appendChild($dom->createElement('addon'));

// The version is simply the current UTC day, optionally followed by the first argument given to
// this script
$version   = gmdate('Ymd');
$versionId = $version * 10;
if (isset($_SERVER['argv'][1]))
{
	$version   .= $_SERVER['argv'][1];
	$versionId += ord($_SERVER['argv'][1]) - 97;
}

// Set the add-on informations
$attributes = [
	'addon_id'                => 's9e',
	'title'                   => 's9e Media Pack',
	'url'                     => 'https://github.com/s9e/XenForoMediaBBCodes',
	'version_id'              => $version,
	'version_string'          => $versionId,
	'install_callback_class'  => 's9e_MediaBBCodes',
	'install_callback_method' => 'install'
];
foreach ($attributes as $attrName => $attrValue)
{
	$addon->setAttribute($attrName, $attrValue);
}

$rows = [];
$rows[] = '<tr>';
$rows[] = '	<th><input type="checkbox" onchange="toggleAll(this)"></th>';
$rows[] = '	<th>Id</th>';
$rows[] = '	<th>Site</th>';
$rows[] = '	<th>Example URLs</th>';
$rows[] = '</tr>';

$sitenames = [];
$examples  = [];

$parentNode = $addon->appendChild($dom->createElement('bb_code_media_sites'));
foreach ($sites->site as $site)
{
	$template = (string) $configurator->MediaEmbed->add($site['id'])->defaultTemplate;

	$node = $parentNode->appendChild($dom->createElement('site'));
	$node->setAttribute('media_site_id',  $site['id']);
	$node->setAttribute('site_title',     $site->name);
	$node->setAttribute('site_url',       $site->homepage);
	$node->setAttribute('match_is_regex', '1');
	$node->setAttribute('supported',      '1');

	preg_match_all('/(?<=@)\\w+/', $template, $matches);
	$attrNames = array_unique($matches[0]);

	if (count($attrNames) === 1 && $attrNames !== ['id'])
	{
		die("Remap $site[id]\n");
	}

	// Default HTML replacement. Ensure that iframe and script have an end tag
	$html = preg_replace('#(<(iframe|script)[^>]+)/>#', '$1></$2>', $template);

	// Replace XSL attributes with XenForo's syntax
	$html = str_replace('{@', '{$', $html);

	// Temp fix for WorldStarHipHop
	if ($site['id'] == 'wshh')
	{
		$html = str_replace(' type="application/x-shockwave-flash" typemustmatch=""', '', $html);
	}

	// Test whether the template needs to be rendered in PHP
	if (strpos($html, '<xsl:') !== false
	 || strpos($html, '{substring') !== false)
	{
		$methodName = 'render' . ucfirst($site['id']);
		$html = '<!-- s9e_MediaBBCodes::' . $methodName . '() -->';

		$node->setAttribute('embed_html_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('embed_html_callback_method', 'embed');

		// Load the template in an XSL stylesheet
		$xsl = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:template match="X">' . $template . '</xsl:template></xsl:stylesheet>';

		// Normalize whitespace
		$tmp = new DOMDocument;
		$tmp->preserveWhiteSpace = false;
		$tmp->loadXML($xsl);
		$xsl = $tmp->saveXML();

		// Capture the PHP source for this template
		$regexp = '(' . preg_quote("if(\$nodeName==='X'){") . '(.*)' . preg_quote('}else $this->at($node);') . ')s';
		if (!preg_match($regexp, $rendererGenerator->generate($xsl), $m))
		{
			echo 'Skipping ', $site->name, "\n";
			$node->parentNode->removeChild($node);

			continue;
		}

		$src = "\$html='';" . $m[1];
		$src = str_replace("\$html='';\$this->out.=", '$html=', $src);
		$src = preg_replace("#\\\$node->hasAttribute\\(('[^']+')\\)#", 'isset($vars[$1])', $src);
		$src = preg_replace("#\\\$node->getAttribute\\(('[^']+')\\)#", '$vars[$1]', $src);
		$src = str_replace('$this->out', '$html', $src);

		if (strpos($src, '->'))
		{
			echo 'Skipping ', $site->name, " (->)\n";
			$node->parentNode->removeChild($node);

			continue;
		}

		// Collect the name of all vars in use to initialize them with a null value
		$vars = [];
		preg_match_all('(@(\\w+))', $template, $matches);
		foreach ($matches[1] as $varName)
		{
			$vars[$varName] = var_export($varName, true) . ' => null';
		}
		ksort($vars);

		$php[] = '';
		$php[] = '	public static function ' . $methodName . '($vars)';
		$php[] = '	{';

		if (!empty($vars))
		{
			$php[] = '		$vars += array(' . implode(', ', $vars) . ');';
			$php[] = '';
		}

		$php[] = '		' . $src;
		$php[] = '';
		$php[] = '		return $html;';
		$php[] = '	}';
	}

	// Test whether the template contains any variables other than $id and if so, render it in PHP
	if (preg_match('(\\{\\$(?!id\\}))', $html))
	{
		$node->setAttribute('embed_html_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('embed_html_callback_method', 'embed');
	}

	// Workaround for sites that don't like URL-encoding
	if (preg_match('(gist|mtvnservices)', $html))
	{
		$node->setAttribute('embed_html_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('embed_html_callback_method', 'embed');
	}

	$regexps          = [];
	$matchRegexps     = [];
	$scrapes          = [];
	$useMatchCallback = false;

	foreach ($site->extract as $regexp)
	{
		$regexp = (string) $regexp;

		// Test whether it captures anything else than "id"
		if (preg_match("(\\(\\?['<](?!id))", $regexp))
		{
			$useMatchCallback = true;
		}

		// Test whether this regexp contains the name of at least one host. If not, make it match
		// any of the host. (CNN and Spotify excluded)
		$hosts     = [];
		$matchHost = ($site['id'] != 'cnn' && $site['id'] != 'spotify');
		foreach ($site->host as $host)
		{
			$hosts[] = (string) $host;

			if (strpos($regexp, preg_quote($host)) !== false)
			{
				$matchHost = false;
			}
		}
		if ($matchHost)
		{
			$regexp = $regexp[0]
			        . s9e\TextFormatter\Configurator\Helpers\RegexpBuilder::fromList($hosts)
			        . '.*?'
			        . substr($regexp, 1);
		}

		$regexps[] = $regexp;
		$matchRegexps[] = var_export($regexp, true);
	}

	foreach ($site->scrape as $scrape)
	{
		$entry = [];

		foreach ($scrape->match as $match)
		{
			$match     = (string) $match;
			$regexps[] = $match;

			$entry['match'][] = var_export($match, true);
		}

		foreach ($scrape->extract as $extract)
		{
			$entry['extract'][] = var_export((string) $extract, true);
		}

		if (isset($scrape['url']))
		{
			$entry['url'] = (string) $scrape['url'];
		}

		$scrapes[] = $entry;
		$useMatchCallback = true;
	}

	if ($useMatchCallback)
	{
		$methodName = 'match' . ucfirst($site['id']);
		$node->setAttribute('match_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('match_callback_method', $methodName);

		$php[] = '';
		$php[] = '	public static function ' . $methodName . '($url)';
		$php[] = '	{';
		$php[] = '		$regexps = array(' . implode(', ', $matchRegexps) . ');';

		if (empty($scrapes))
		{
			$php[] = '		$scrapes = array();';
		}
		else
		{
			$php[] = '		$scrapes = array(';

			foreach ($scrapes as $k => $scrape)
			{
				$php[] = '			array(';

				if (isset($scrape['url']))
				{
					$php[] = "				'url'     => " . var_export($scrape['url'], true) . ',';
				}

				$php[] = "				'match'   => array(" . implode(', ', $scrape['match']) . '),';
				$php[] = "				'extract' => array(" . implode(', ', $scrape['extract']) . ')';
				$php[] = '			)' . ((isset($scrapes[++$k])) ? ',' : '');
			}

			$php[] = '		);';
		}

		$php[] = '';
		$php[] = '		return self::match($url, $regexps, $scrapes);';
		$php[] = '	}';
	}

	// Test whether each regexp has a capture named "id". If not, add an empty capture named "id" to
	// satisfy XenForo's assumptions that every regexp contains one
	foreach ($regexps as &$regexp)
	{
		if (!preg_match("(\\(\\?['<]id['<])", $regexp))
		{
			$regexp = $regexp[0] . "(?'id')" . substr($regexp, 1);
		}
	}
	unset($regexp);

	$node->appendChild($dom->createElement('match_urls', htmlspecialchars(implode("\n", $regexps))));

	$node->appendChild($dom->createElement('embed_html'))
	     ->appendChild($dom->createCDATASection($html));

	// Build the table of sites
	$rows[] = '<tr>';
	$rows[] = '	<td><input type="checkbox" data-id="' . $site['id'] . '"></td>';
	$rows[] = '	<td><code>' . $site['id'] . '</code></td>';
	$rows[] = '	<td>' . $site->name . '</td>';
	$rows[] = '	<td>' . str_replace('&', '&amp;', implode('<br/>', (array) $site->example)) . '</td>';
	$rows[] = '</tr>';

	// Record the name of the site
	$sitenames[] = (string) $site->name;

	// Record the example URLs
	foreach ($site->example as $example)
	{
		$examples[] = (string) $example;
	}
}

$php[] = '}';

// Save the helper class
file_put_contents(__DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php', implode("\n", $php));

// Save addon.xml
$dom->formatOutput = true;
$xml = $dom->saveXML();

file_put_contents(__DIR__ . '/../build/addon.xml', $xml);

// Coalesce the table's content
$rows = implode("\n", $rows);

// Update the table used in the configurator
$filepath = __DIR__ . '/../www/configure.html';
file_put_contents(
	$filepath,
	preg_replace_callback(
		'#(?<=var xml = ).*?(?=;\\n\\n)#',
		function () use ($xml)
		{
			return json_encode($xml);
		},
		preg_replace(
			'#(<table[^>]*>).*</table>#s',
			"\$1\n\t\t" . str_replace("\n", "\n\t\t", $rows) . "\n\t</table>",
			file_get_contents($filepath)
		)
	)
);

// Remove the buttons from the table used in README
$rows = preg_replace('#\\s*<td><input.*</td>#', '', $rows);
$rows = preg_replace('#\\s*<th>.*</th>#',       '', $rows, 1);

// Update the README
$filepath = __DIR__ . '/../README.md';
file_put_contents(
	$filepath,
	preg_replace(
		'#(<table[^>]*>).*</table>#s',
		"\$1\n\t" . str_replace("\n", "\n\t", $rows) . "\n</table>",
		file_get_contents($filepath)
	)
);

copy(__DIR__ . '/../LICENSE', __DIR__ . '/../build/LICENSE');
exec('7z a -tzip -mx9 ' . realpath(__DIR__ . '/../releases') . '/XenForoMediaBBCodes-' . $version . '.zip' . ' ' . realpath(__DIR__ . '/../build') . '/* 2> /dev/null');

$readme =
'[url=https://travis-ci.org/s9e/XenForoMediaBBCodes][img]https://travis-ci.org/s9e/XenForoMediaBBCodes.png?branch=master[/img][/url] [url=https://coveralls.io/r/s9e/XenForoMediaBBCodes][img]https://coveralls.io/repos/s9e/XenForoMediaBBCodes/badge.png[/img][/url]

This pack contains the definitions for ' . count($sitenames) . ' media sites: ' . implode(', ', $sitenames) . '. The complete list with examples of supported URLs can be found on [url=https://github.com/s9e/XenForoMediaBBCodes]its GitHub page[/url].

The BBCodes definitions are based on [url=https://github.com/s9e/TextFormatter]the s9e\TextFormatter library[/url], and more specifically its [url=https://github.com/s9e/TextFormatter/tree/master/src/s9e/TextFormatter/Plugins/MediaEmbed]MediaEmbed[/url] plugin. The BBCodes are designed for performance: the media site is only accessed once during posting, and only if absolutely necessary.

This add-on is released under [url=http://en.wikipedia.org/wiki/MIT_License]The MIT License[/url]. Redistribution is allowed and [b]encouraged[/b].

[size=6][font=Arial]Installation[/font][/size]

Unzip the archive, and upload the content of the [font=monospace]upload[/font] folder to your XenForo installation. Then go to your forum\'s Admin CP and install the add-on via the provided [font=monospace]addon.xml[/font] file.

[size=6][font=Arial]Compatibility and customization[/font][/size]

Most of the definitions found in this pack are compatible with existing definitions, but some of them may not be. [b]Note that this add-on replaces the default media sites.[/b] If you only want to install only [i]some[/i] of the BBCodes found in this pack, you can try the [url=http://s9e.github.io/XenForoMediaBBCodes/configure.html]experimental configurator interface[/url].

[size=6][font=Arial]Requests[/font][/size]

If there\'s a media site that you would want to see in this pack, you can request it in this thread and it will be considered for inclusion. Selection may depend on the site\'s popularity and Alexa rank. Please post a few links as examples of the kind of links that should be supported. Only links to legal stuff, thanks.';

file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $version . '.txt',
	$readme
);

// Update the test file
file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $version . '-urls.txt',
	implode("\n", $examples)
);