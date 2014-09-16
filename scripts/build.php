<?php

/**
* @copyright Copyright (c) 2013-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

include_once __DIR__ . '/../vendor/s9e/TextFormatter/src/autoloader.php';

if (!isset($addonId))
{
	$sitesDir   = __DIR__ . '/../vendor/s9e/TextFormatter/src/Plugins/MediaEmbed/Configurator/sites';
	$classFile  = __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
	$addonFile  = __DIR__ . '/../build/addon-s9e.xml';
	$addonId    = 's9e';
	$namespace  = 's9e';
	$className  = 's9e_MediaBBCodes';
	$addonTitle = 's9e Media Pack';
	$addonUrl   = 'https://github.com/s9e/XenForoMediaBBCodes';
}

if (!isset($configurator))
{
	$configurator = new s9e\TextFormatter\Configurator;
}
$configurator->rendering->engine = 'PHP';
$configurator->rendering->engine->forceEmptyElements = false;
$configurator->rendering->engine->useEmptyElements   = false;


$php = <<<'NOWDOC'
<?php

/**
* @copyright Copyright (c) 2013-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

class s9e_MediaBBCodes
{
	/**
	* Path to a cache dir, used to cache scraped pages
	*/
	public static $cacheDir;

	public static function install($old, $new, $addon)
	{
		$exclude = XenForo_Application::get('options')->s9e_EXCLUDE_SITES;
		$custom  = class_exists('s9e_Custom');

		if (!$exclude && !$custom)
		{
			return;
		}

		$exclude = array_flip(preg_split('/\\W+/', $exclude, -1, PREG_SPLIT_NO_EMPTY));
		$nodes   = array();

		foreach ($addon->bb_code_media_sites->site as $site)
		{
			$id = (string) $site['media_site_id'];

			if (isset($exclude[$id]))
			{
				$nodes[] = dom_import_simplexml($site);
				continue;
			}

			$callback = 's9e_Custom::' . $id;

			if ($custom && is_callable($callback))
			{
				$site->embed_html = '<!-- ' . $callback . "() -->\n" . $site->embed_html;

				$site['embed_html_callback_class']  = 's9e_MediaBBCodes';
				$site['embed_html_callback_method'] = 'embed';
			}
		}

		foreach ($nodes as $node)
		{
			$node->parentNode->removeChild($node);
		}
	}

	public static function match($url, $regexps, $scrapes, $filters = array())
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
			return false;
		}

		// Apply filters
		foreach ($filters as $varName => $callbacks)
		{
			if (!isset($vars[$varName]))
			{
				continue;
			}

			foreach ($callbacks as $callback)
			{
				$vars[$varName] = $callback($vars[$varName]);
			}
		}

		// If there's only one capture named "id" we store its value as-is
		$keys = array_keys($vars);
		if ($keys === array('id'))
		{
			return $vars['id'];
		}

		// If there's only one capture named "url" and it looks like a URL, we store its value as-is
		if ($keys === array('url') && preg_match('#^\\w+://#', $vars['url']))
		{
			return $vars['url'];
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
		// If the value looks like a URL, we copy its value to the "url" var
		if (preg_match('#^\\w+://#', $mediaKey))
		{
			$vars['url'] = $mediaKey;
		}

		// If the value looks like a series of key=value pairs, add them to $vars
		if (preg_match('(^(\\w+=[^;]*)(?>;(?1))*$)', $mediaKey))
		{
			$vars = array();
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}

		// The value is used as the "id" var if it hasn't been defined already
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

		// Prepare the HTML
		$html = $site['embed_html'];

		// Test whether this particular site has its own renderer
		$html = preg_replace_callback(
			'(<!-- (' . __CLASS__ . '::render\\w+)\\((?:(\\d+), *(\\d+))?\\) -->)',
			function ($m) use ($vars)
			{
				$callback = $m[1];

				if (!is_callable($callback))
				{
					return $m[0];
				}

				$html = call_user_func($callback, $vars);

				if (isset($m[2], $m[3]))
				{
					$html = preg_replace('/( width=")[^"]*/',  '${1}' . $m[2], $html);
					$html = preg_replace('/( height=")[^"]*/', '${1}' . $m[3], $html);
				}

				return $html;
			},
			$html,
			-1,
			$cnt
		);

		// Otherwise use the configured template
		if (!$cnt)
		{
			$html = preg_replace_callback(
				// Interpolate {$id} and other {$vars}
				'(\\{\\$([a-z]+)\\})',
				function ($m) use ($vars)
				{
					return (isset($vars[$m[1]])) ? htmlspecialchars($vars[$m[1]]) : '';
				},
				$site['embed_html']
			);
		}

		// Test for custom modifications
		if (preg_match('(^<!-- (s9e_Custom::\w+)\\(\\) -->\\s*)', $html, $m))
		{
			$html = substr($html, strlen($m[0]));

			if (is_callable($m[1]))
			{
				$html = call_user_func($m[1], $html, $vars);
			}
		}

		return $html;
	}

	public static function wget($url)
	{
		// Return the content from the cache if applicable
		if (isset(self::$cacheDir) && file_exists(self::$cacheDir))
		{
			$cacheFile = self::$cacheDir . '/http.' . crc32($url) . '.gz';

			if (file_exists($cacheFile))
			{
				return file_get_contents('compress.zlib://' . $cacheFile);
			}
		}

		$page = @file_get_contents(
			'compress.zlib://' . $url,
			false,
			stream_context_create(array(
				'http' => array(
					'header' => 'Accept-Encoding: gzip'
				)
			))
		);

		if ($page && isset($cacheFile))
		{
			file_put_contents($cacheFile, gzencode($page, 9));
		}

		return $page;
	}

	protected static function scrape($url, $regexps)
	{
		return self::getNamedCaptures(self::wget($url), $regexps);
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
					// Add named captures to the vars without overwriting existing vars
					if (!is_numeric($k) && !isset($vars[$k]))
					{
						$vars[$k] = $v;
					}
				}
			}
		}

		return $vars;
	}
NOWDOC;

function setAttributes(DOMElement $element, array $attributes)
{
	foreach ($attributes as $attrName => $attrValue)
	{
		$element->setAttribute($attrName, $attrValue);
	}
}

if ($addonId !== 's9e')
{
	$php = str_replace('s9e_MediaBBCodes', $className, $php);
	$php = str_replace('s9e_Custom', $namespace . '_Custom', $php);
	$php = str_replace('s9e_', $addonId . '_', $php);
}

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
setAttributes(
	$addon,
	[
		'addon_id'                => $addonId,
		'title'                   => $addonTitle,
		'url'                     => $addonUrl,
		'version_id'              => $versionId,
		'version_string'          => $version,
		'install_callback_class'  => $className,
		'install_callback_method' => 'install'
	]
);

$rows = [];
$rows[] = '<thead>';
$rows[] = '	<tr>';
$rows[] = '		<th><input type="checkbox" onchange="toggleAll(this)"></th>';
$rows[] = '		<th>Id</th>';
$rows[] = '		<th>Site</th>';
$rows[] = '		<th>Example URLs</th>';
$rows[] = '	</tr>';
$rows[] = '</thead>';
$rows[] = '<tbody>';

$sitenames   = [];
$examples    = [];
$optionNames = [];

$parentNode = $addon->appendChild($dom->createElement('bb_code_media_sites'));
foreach (glob($sitesDir . '/*.xml') as $siteFile)
{
	$site   = simplexml_load_file($siteFile);
	$siteId = basename($siteFile, '.xml');

	$configurator->tags->clear();
	$template = (string) $configurator->MediaEmbed->add($siteId)->template;

	$node = $parentNode->appendChild($dom->createElement('site'));
	$node->setAttribute('media_site_id',  $siteId);
	$node->setAttribute('site_title',     $site['name']);
	$node->setAttribute('site_url',       $site['homepage']);
	$node->setAttribute('match_is_regex', '1');
	$node->setAttribute('supported',      '1');

	preg_match_all('/(?<=@)\\w+/', $template, $matches);
	$attrNames = array_unique($matches[0]);

	if (count($attrNames) === 1 && $attrNames !== ['id'])
	{
		echo "Remap $site[id]\n";
	}

	// Default HTML replacement. Ensure that iframe and script have an end tag
	$html = preg_replace('#(<(iframe|script)[^>]+)/>#', '$1></$2>', $template);

	$useEmbedCallback = false;

	// Test whether the template needs to be rendered in PHP
	if (strpos($html, '<xsl:') !== false
	 || preg_match('/="[^"]*(?<!\\{)\\{(?![@{])/', $html))
	{
		$useEmbedCallback = true;

		$methodName = 'render' . ucfirst($siteId);
		$html = '<!-- ' . $className . '::' . $methodName . '() -->';

		$node->setAttribute('embed_html_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('embed_html_callback_method', 'embed');

		// Capture the PHP source for this template
		$regexp = '(if\\(\\$tb===0\\)\\{?(.*?)\\}?elseif\\(\\$tb===)s';

		if (!preg_match($regexp, $configurator->getRenderer()->source, $m))
		{
			echo 'Skipping ', $site['name'], "\n";
			$node->parentNode->removeChild($node);

			continue;
		}

		$src = "\$html='';" . $m[1];
		$src = str_replace("\$html='';\$this->out.=", '$html=', $src);
		$src = preg_replace("#\\\$node->hasAttribute\\(('[^']+')\\)#", 'isset($vars[$1])', $src);
		$src = preg_replace("#\\\$node->getAttribute\\(('[^']+')\\)#", '$vars[$1]', $src);
		$src = str_replace('$this->out', '$html', $src);

		// Replace the template params
		$src = preg_replace_callback(
			"#\\\$this->params\\['([^']+)'\\]#",
			function ($m) use ($addon, &$optionNames)
			{
				$paramName  = $m[1];
				$optionName = $addon->getAttribute('addon_id') . '_' . $m[1];

				$optionNames[$paramName] = $optionName;

				return "XenForo_Application::get('options')->" . $optionName;
			},
			$src
		);

		if (preg_match("((?<!XenForo_Application::get\\('options'\\))->)", $src))
		{
			echo 'Skipping ', $site['name'], " (->)\n";
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
	else
	{
		// Test whether the template contains any variables other than $id and if so, render it in
		// PHP
		if (preg_match('(\\{@(?!id\\}))', $html))
		{
			$useEmbedCallback = true;
		}

		// Replace XSL attributes with XenForo's syntax and unescape brackets
		$html = strtr($html, ['{@' => '{$', '{{' => '{', '}}' => '}']);
	}

	// Workaround for sites that don't like URL-encoding
	if (strpos($html, 'mtvnservices') !== false)
	{
		$useEmbedCallback = true;
	}

	// Set the embed callback if applicable
	if ($useEmbedCallback)
	{
		$node->setAttribute('embed_html_callback_class',  $className);
		$node->setAttribute('embed_html_callback_method', 'embed');
	}

	$regexps          = [];
	$matchRegexps     = [];
	$scrapes          = [];
	$useMatchCallback = false;

	// Collect this site's hosts and build a regexp that matches all of them
	$hosts = [];
	foreach ($site->host as $host)
	{
		$hosts[] = (string) $host;
	}
	$hostsRegexp = s9e\TextFormatter\Configurator\Helpers\RegexpBuilder::fromList($hosts);

	foreach ($site->extract as $regexp)
	{
		$regexp = (string) $regexp;

		// Test whether it captures anything else than "id"
		if (preg_match("(\\(\\?['<](?!id))", $regexp))
		{
			$useMatchCallback = true;
		}

		$regexps[] = $regexp;
		$matchRegexps[$regexp] = var_export($regexp, true);
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

		if (!isset($entry['match']))
		{
			$entry['match'] = ["'//'"];
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

	foreach ($regexps as $k => $regexp)
	{
		// Test whether this regexp contains the name of at least one host. If not, make it match
		// any of the host. (Spotify excluded)
		$matchHost = ($siteId != 'spotify');
		foreach ($hosts as $host)
		{
			if (strpos($regexp, preg_quote($host)) !== false)
			{
				$matchHost = false;
			}
		}

		if ($matchHost)
		{
			if (false === strpos($regexp, $hostsRegexp)
			 && false === strpos($regexp, str_replace('(?>', '(?:', $hostsRegexp)))
			{
				$regexps[$k] = $regexp[0] . '(?=.*?[./]' . $hostsRegexp . ').*?' . substr($regexp, 1);

				if (isset($matchRegexps[$regexp]))
				{
					$matchRegexps[$regexp] = var_export($regexps[$k], true);
				}
			}
		}
	}

	$filters = array();
	if (isset($site->attributes))
	{
		foreach ($site->attributes->children() as $attribute)
		{
			if (isset($attribute['preFilter']))
			{
				$filters[$attribute->getName()][] = (string) $attribute['preFilter'];
			}
			if (isset($attribute['postFilter']))
			{
				$filters[$attribute->getName()][] = (string) $attribute['postFilter'];
			}
		}

		if (!empty($filters))
		{
			$useMatchCallback = true;
		}
	}

	if ($useMatchCallback)
	{
		$methodName = 'match' . ucfirst($siteId);
		$node->setAttribute('match_callback_class',  $className);
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

		if (!empty($filters))
		{
			$php[] = '		$filters = array(';

			foreach ($filters as $varName => $callbacks)
			{
				$str = '			' . var_export($varName, true) . ' => array(';
				foreach ($callbacks as $i => $callback)
				{
					$str .= (($i) ? ', ' : '') . var_export($callback, true);
				}
				$str .= '),';

				$php[] = $str;
			}

			$php[] = '		);';
		}

		$php[] = '';
		$php[] = '		return self::match($url, $regexps, $scrapes' . (($filters) ? ', $filters' : '') . ');';

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

	$matchUrls = (empty($regexps))
	           ? "((?'id')" . $hostsRegexp . '/)'
	           : htmlspecialchars(implode("\n", $regexps));
	$node->appendChild($dom->createElement('match_urls', $matchUrls));

	$node->appendChild($dom->createElement('embed_html'))
	     ->appendChild($dom->createCDATASection($html));

	// Build the table of sites
	$rows[] = '	<tr>';
	$rows[] = '		<td><input type="checkbox" data-id="' . $siteId . '"></td>';
	$rows[] = '		<td><code>' . $siteId . '</code></td>';
	$rows[] = '		<td>' . $site['name'] . '</td>';
	$rows[] = '		<td>' . str_replace('&', '&amp;', implode('<br/>', (array) $site->example)) . '</td>';
	$rows[] = '	</tr>';

	// Record the name of the site
	$sitenames[] = (string) $site['name'];

	// Record the example URLs
	foreach ($site->example as $example)
	{
		$examples[] = (string) $example;
	}
}
$php[] = '}';

// Remove temp files
array_map('unlink', glob(sys_get_temp_dir() . '/Renderer_*'));

// Prepare the option group
$optiongroups = $addon->appendChild($dom->createElement('optiongroups'));

$group = $optiongroups->appendChild($dom->createElement('group'));
$group->setAttribute('group_id',      $addon->getAttribute('addon_id'));
$group->setAttribute('display_order', 1);
$group->setAttribute('debug_only',    0);

// Prepare the phrase nodes
$phrases = $addon->appendChild($dom->createElement('phrases'));

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', $addonTitle)),
	[
		'title'          => 'option_group_' . $addonId,
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Variables used in some embedded media')),
	[
		'title'          => 'option_group_' . $addonId . '_description',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

// Add the blacklist param
$optionNames['EXCLUDE_SITES'] = $addonId . '_EXCLUDE_SITES';

// Add the params as XenForo options
ksort($optionNames);

$i = 0;
foreach ($optionNames as $paramName => $optionName)
{
	$option = $optiongroups->appendChild($dom->createElement('option'));

	setAttributes(
		$option,
		[
			'option_id'   => $optionName,
			'edit_format' => 'textbox',
			'data_type'   => 'string',
			'can_backup'  => '1'
		]
	);

	setAttributes(
		$option->appendChild($dom->createElement('relation')),
		[
			'group_id'      => $addon->getAttribute('addon_id'),
			'display_order' => ++$i
		]
	);

	setAttributes(
		$phrases->appendChild($dom->createElement('phrase', $paramName)),
		[
			'title'          => 'option_' . $optionName,
			'version_id'     => '1',
			'version_string' => '1'
		]
	);

	setAttributes(
		$phrases->appendChild($dom->createElement('phrase')),
		[
			'title'          => 'option_' . $optionName . '_explain',
			'version_id'     => '1',
			'version_string' => '1'
		]
	);
}

// Save the helper class
file_put_contents($classFile, implode("\n", $php));

// Save addon.xml
$dom->formatOutput = true;
$xml = $dom->saveXML($addon);

file_put_contents($addonFile, $xml);

if ($addonId !== 's9e')
{
	return;
}

// Close the table body
$rows[] = '</tbody>';

// Coalesce the table's content
$rows = implode("\n", $rows);

// Update the tables used in the configurator
$filepath = __DIR__ . '/../www/configure.html';
$html     = file_get_contents($filepath);

// Update the sites table
$html = preg_replace_callback(
	'#(?<=var xml = ).*?(?=;\\n\\n)#',
	function () use ($xml)
	{
		return json_encode($xml);
	},
	preg_replace(
		'#(<table id="sites">).*?</table>#s',
		"\$1\n\t\t" . str_replace("\n", "\n\t\t", $rows) . "\n\t</table>",
		$html
	)
);

file_put_contents($filepath, $html);

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

if (!file_exists(__DIR__ . '/../releases') || !empty($_SERVER['TRAVIS']))
{
	return;
}

chdir(__DIR__ . '/../build');
copy('../LICENSE', 'LICENSE');
exec('kzip -r -y ../releases/XenForoMediaBBCodes-' . $version . '.zip addon-s9e.xml LICENSE upload/library/s9e/MediaBBCodes.php');

$readme =
'This pack contains the definitions for [b]' . count($sitenames) . ' media sites[/b]: ' . implode(', ', $sitenames) . '. The complete list with examples of supported URLs can be found on [url="https://github.com/s9e/XenForoMediaBBCodes"]its GitHub page[/url].

The BBCodes definitions are based on [url="https://github.com/s9e/TextFormatter"]the s9e\TextFormatter library[/url], and more specifically its [url="https://github.com/s9e/TextFormatter/tree/master/src/Plugins/MediaEmbed"]MediaEmbed[/url] plugin. The BBCodes are designed for performance: the media site is only accessed once during posting, and only if absolutely necessary.

This add-on is released under [url="http://en.wikipedia.org/wiki/MIT_License"]The MIT License[/url]. Redistribution is allowed and [b]encouraged[/b]. [color=#fff]This add-on is free and available on GitHub. If you paid for it, you\'ve been swindled.[/color]
[url="https://travis-ci.org/s9e/XenForoMediaBBCodes"][img]https://travis-ci.org/s9e/XenForoMediaBBCodes.png?branch=master&' . $version . '[/img][/url] [url="https://coveralls.io/r/s9e/XenForoMediaBBCodes"][img]https://coveralls.io/repos/s9e/XenForoMediaBBCodes/badge.png?' . $version . '[/img][/url]

[size=6][font=Arial][b]Installation[/b][/font][/size]

Unzip the archive, and upload the content of the [font=monospace]upload[/font] folder to your XenForo installation. Then go to your forum\'s Admin CP and install the add-on via the provided [font=monospace]addon.xml[/font] file.

[size=6][font=Arial][b]Compatibility and customization[/b][/font][/size]

Most of the definitions found in this pack are compatible with existing definitions, but some of them may not be. [b]Note that this add-on replaces the default media sites.[/b] If you only want to install [i]some[/i] of the BBCodes found in this pack, you can try the [url="http://s9e.github.io/XenForoMediaBBCodes/configure.html"]online configurator interface[/url].

[size=6][font=Arial][b]How to request a media site[/b][/font][/size]

If there\'s a media site that you would want to see in this pack, you can request it in this thread and it will be considered for inclusion. Selection may depend on the site\'s popularity, Alexa rank and activity on social sites. [b]Please post a few links as examples[/b] of the kind of links that should be supported. You do not need to post the embed code, only links to the content [i]you[/i] want to embed.

[size=6][font=Arial][b]How to support this add-on[/b][/font][/size]

You can donate any amount of your hard earned money in USD or EUR using either of the following links.

[url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ABGFV5AGE98AG"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url][url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6P6985GT2DLGL"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url]';

file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $version . '.txt',
	$readme
);

// Update the test file
file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $version . '-urls.txt',
	implode("\n", $examples)
);