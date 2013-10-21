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

$php = '<?php

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
';

$dom = new DOMDocument('1.0', 'utf-8');
$addon = $dom->appendChild($dom->createElement('addon'));

$versionId = gmdate('Ymd');

$addon->setAttribute('addon_id',       's9e');
$addon->setAttribute('title',          's9e Media Pack');
$addon->setAttribute('url',            'https://github.com/s9e/XenForoMediaBBCodes');
$addon->setAttribute('version_id',     $versionId);
$addon->setAttribute('version_string', $versionId);

$rows = [];
$rows[] = '<tr>';
$rows[] = '	<th><input type="checkbox" onchange="toggleAll(this)"></th>';
$rows[] = '	<th>Id</th>';
$rows[] = '	<th>Site</th>';
$rows[] = '	<th>Example URLs</th>';
$rows[] = '</tr>';

$sitenames = [];

$parentNode = $addon->appendChild($dom->createElement('bb_code_media_sites'));
foreach ($sites->site as $site)
{
	$template = (string) $configurator->MediaEmbed->add($site['id'])->defaultTemplate;

	$node = $parentNode->appendChild($dom->createElement('site'));
	$node->setAttribute('media_site_id',  $site['id']);
	$node->setAttribute('site_title',     $site->name);
	$node->setAttribute('site_url',       $site->homepage);
	$node->setAttribute('match_is_regex', '1');

	if (strpos($template, 'xsl') === false && !preg_match('#\\{@(?!id)#', $template))
	{
		$html = preg_replace('#(<(iframe|script)[^>]+)/>#', '$1></$2>', $template);
	}
	else
	{
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
		$src = preg_replace("#\\\$node->hasAttribute\\(('[^']+')\\)#", '!empty($m[$1])', $src);
		$src = preg_replace("#\\\$node->getAttribute\\(('[^']+')\\)#", '$m[$1]', $src);
		$src = str_replace('$this->out', '$html', $src);

		if (strpos($src, '->'))
		{
			echo 'Skipping ', $site->name, " (->)\n";
			$node->parentNode->removeChild($node);

			continue;
		}

		$methodName = 'embed' . ucfirst($site['id']);
		$node->setAttribute('embed_html_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('embed_html_callback_method', $methodName);

		$extract = (string) $site->extract;

		$php .= "\n\tpublic static function " . $methodName . '($url, $site)';
		$php .= "\n\t{";
		$php .= "\n\t\tif (!preg_match(" . var_export($extract, true) . ', $url, $m))';
		$php .= "\n\t\t{";
		$php .= "\n\t\t\treturn '<a href=\"' . htmlspecialchars(\$url) . '\">' . htmlspecialchars(\$url) . '</a>';";
		$php .= "\n\t\t}";
		$php .= "\n";
		$php .= "\n\t\t" . $src;
		$php .= "\n";
		$php .= "\n\t\treturn \$html;";
		$php .= "\n\t}";
		$php .= "\n";

		$html = '<!-- s9e_MediaBBCodes::' . $methodName . '() -->';

		// Replace the original extract regexp
		$delim   = $extract[0];
		$extract = preg_replace("#\\(\\?'\\w+'#", '(?:', $extract);
		$extract = $delim . "(?'id'.*" . substr($extract, 1, -1) . '.*)' . $delim;
		$site->extract = $extract;
	}

	$regexps = [];
	foreach ($site->extract as $regexp)
	{
		$regexps[] = (string) $regexp;
	}

	// NOTE: should be changed if there's a site with multiple <scrape>
	if (isset($site->scrape))
	{
		$match   = (string) $site->scrape->match;
		$extract = (string) $site->scrape->extract;

		// Add a fake "id" capture
		$regexps[] = $match[0] . "(?'id')" . substr($match, 1);

		$methodName = 'match' . ucfirst($site['id']);
		$node->setAttribute('match_callback_class',  's9e_MediaBBCodes');
		$node->setAttribute('match_callback_method', $methodName);

		$php .= "\n\tpublic static function " . $methodName . '($url, $id, $site)';
		$php .= "\n\t{";
		$php .= "\n\t\treturn (\$id) ? \$id : self::scrape(\$url, " . var_export($extract, true) . ");";
		$php .= "\n\t}";
		$php .= "\n";
	}
	$node->appendChild($dom->createElement('match_urls', implode("\n", $regexps)));

	$node->appendChild($dom->createElement('embed_html'))
	     ->appendChild($dom->createCDATASection(str_replace('{@id}', '{$id}', $html)));

	// Build the table of sites
	$rows[] = '<tr>';
	$rows[] = '	<td><input type="checkbox" data-id="' . $site['id'] . '"></td>';
	$rows[] = '	<td><code>' . $site['id'] . '</code></td>';
	$rows[] = '	<td>' . $site->name . '</td>';
	$rows[] = '	<td>' . str_replace('&', '&amp;', implode('<br/>', (array) $site->example)) . '</td>';
	$rows[] = '</tr>';

	// Record the name of the site
	$sitenames[] = (string) $site->name;
}

$dom->formatOutput = true;
$xml = $dom->saveXML();

file_put_contents(__DIR__ . '/../build/addon.xml', $xml);

$php .= '}';

// Save the pack
file_put_contents(__DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php', $php);

// Coalesce the table's content
$rows = implode("\n", $rows);

// Update the table used in the configurator
$filepath = __DIR__ . '/../www/configure.html';
file_put_contents(
	$filepath,
	preg_replace(
		'#(?<=var xml = ).*?(?=;\\n\\n)#',
		json_encode($xml),
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

exec('7z a -tzip -mx9 ' . realpath(__DIR__ . '/../releases') . '/XenForoMediaBBCodes-' . $versionId . '.zip' . ' ' . realpath(__DIR__ . '/../build') . '/*');

$readme =
'This pack contains the definitions for ' . count($sitenames) . ' media sites: ' . implode(', ', $sitenames) . '. The complete list with examples of supported URLs can be found on [url=https://github.com/s9e/XenForoMediaBBCodes]its GitHub page[/url].

The BBCodes definitions is based on and compatible with [url=https://github.com/s9e/TextFormatter]the s9e\TextFormatter library[/url], and more specifically its [url=https://github.com/s9e/TextFormatter/tree/master/src/s9e/TextFormatter/Plugins/MediaEmbed]MediaEmbed[/url] plugin. The BBCodes are designed for performance: the media site is only accessed once during posting, and only if absolutely necessary.

This add-on is released under [url=http://en.wikipedia.org/wiki/MIT_License]The MIT License[/url]. Redistribution is allowed and [b]encouraged[/b].

[size=6][font=Arial]Installation[/font][/size]

Unzip the archive, and upload the content of the [font=monospace]upload[/font] folder to your XenForo installation. Then go to your forum\'s Admin CP and install the add-on via the provided [font=monospace]addon.xml[/font] file.

[size=6][font=Arial]Compatibility and customization[/font][/size]

Most of the definitions found in this pack are compatible with existing definitions, but some of them may not. If you only want to install [i]some[/i] of the BBCodes found in this pack, you can try the [url=http://s9e.github.io/XenForoMediaBBCodes/configure.html]experimental configurator interface[/url].

[size=6][font=Arial]Requests[/font][/size]

If there\'s a media site that you would want to see in this pack, you can request it in this thread and it will be considered for inclusion.';

file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $versionId . '.txt',
	$readme
);