#!/usr/bin/php
<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

$sites = simplexml_load_file(__DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/Plugins/MediaEmbed/Configurator/sites.xml');

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

$addon->setAttribute('addon_id',       's9e');
$addon->setAttribute('title',          's9e Media Pack');
$addon->setAttribute('url',            'https://github.com/s9e/XenForoMediaBBCodes');
$addon->setAttribute('version_id',     '1');
$addon->setAttribute('version_string', '0.1');

$parentNode = $addon->appendChild($dom->createElement('bb_code_media_sites'));
foreach ($sites->site as $site)
{
	if (isset($site->iframe))
	{
		$html = '<iframe'
		      . ' src="' . str_replace('@', '$', $site->iframe['src']) . '"'
		      . ' width="' . $site->iframe['width'] . '"'
		      . ' height="' . $site->iframe['height'] . '"'
		      . ' allowfullscreen="" frameborder="0" scrolling="no"></iframe>';
	}
	elseif (isset($site->flash))
	{
		$html = '<object type="application/x-shockwave-flash" typemustmatch="" width="' . $site->flash['width'] . '" height="' . $site->flash['height'] . '" data="' . str_replace('@', '$', $site->flash['src']) . '"><param name="allowFullScreen" value="true"/><embed type="application/x-shockwave-flash" src="' . str_replace('@', '$', $site->flash['src']) . '" width="' . $site->flash['width'] . '" height="' . $site->flash['height'] . '" allowfullscreen=""></embed></object>';
	}
	else
	{
		echo 'Skipping ', $site->name, "\n";

		continue;
	}

	$node = $parentNode->appendChild($dom->createElement('site'));
	$node->setAttribute('media_site_id',  $site['id']);
	$node->setAttribute('site_title',     $site->name);
	$node->setAttribute('site_url',       $site->homepage);
	$node->setAttribute('match_is_regex', '1');

	if (isset($site->scrape))
	{
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
	     ->appendChild($dom->createCDATASection($html));
}

$dom->formatOutput = true;
$dom->save(__DIR__ . '/../build/addon.xml');

$php .= '}';

file_put_contents(__DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php', $php);