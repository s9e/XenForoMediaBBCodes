#!/usr/bin/php
<?php

/**
* @copyright Copyright (c) 2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

include __DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/autoloader.php';

$sites = simplexml_load_file(__DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/Plugins/MediaEmbed/Configurator/sites.xml');

$rendererGenerator = new s9e\TextFormatter\Configurator\RendererGenerators\PHP;

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
$addon->setAttribute('version_id',     gmdate('Ymd'));
$addon->setAttribute('version_string', gmdate('Ymd'));

$parentNode = $addon->appendChild($dom->createElement('bb_code_media_sites'));
foreach ($sites->site as $site)
{
	$node = $parentNode->appendChild($dom->createElement('site'));
	$node->setAttribute('media_site_id',  $site['id']);
	$node->setAttribute('site_title',     $site->name);
	$node->setAttribute('site_url',       $site->homepage);
	$node->setAttribute('match_is_regex', '1');

	if (isset($site->iframe))
	{
		$html = '<iframe'
		      . ' src="' . $site->iframe['src'] . '"'
		      . ' width="' . $site->iframe['width'] . '"'
		      . ' height="' . $site->iframe['height'] . '"'
		      . ' allowfullscreen="" frameborder="0" scrolling="no"></iframe>';
	}
	elseif (isset($site->flash))
	{
		$html = '<object type="application/x-shockwave-flash" typemustmatch="" width="' . $site->flash['width'] . '" height="' . $site->flash['height'] . '" data="' . $site->flash['src'] . '"><param name="allowFullScreen" value="true"/>';
		if (isset($site->flash['flashvars']))
		{
			$html .= '<param name="FlashVars" value="' . htmlspecialchars($site->flash['flashvars']) . '"/>';
		}
		$html .= '<embed type="application/x-shockwave-flash" src="' . $site->flash['src'] . '" width="' . $site->flash['width'] . '" height="' . $site->flash['height'] . '" allowfullscreen=""';
		if (isset($site->flash['flashvars']))
		{
			$html .= ' flashvars="' . htmlspecialchars($site->flash['flashvars']) . '"/>';
		}
		$html .= '></embed></object>';
	}
	elseif (strpos($site->template, 'xsl') === false
	     && !preg_match('#\\{@(?!id)#', $site->template))
	{
		$html = preg_replace('#(<(iframe|script)[^>]+)/>#', '$1></$2>', $site->template);
	}
	else
	{
		$xsl = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:template match="X">' . $site->template . '</xsl:template></xsl:stylesheet>';

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
		$extract = preg_replace("#\\(\\?'\\w+'#", '(?:', $extract);
		$extract = "!(?'id'.*" . substr($extract, 1, -1) . '.*)!';
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
}

$dom->formatOutput = true;
$dom->save(__DIR__ . '/../build/addon.xml');

$php .= '}';

file_put_contents(__DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php', $php);

preg_match('#<table>.*</table>#s', file_get_contents(__DIR__ . '/../vendor/s9e/TextFormatter/src/s9e/TextFormatter/Plugins/MediaEmbed/README.md'), $m);

$filepath = __DIR__ . '/../README.md';
file_put_contents($filepath, preg_replace('#<table>.*</table>#s', $m[0], file_get_contents($filepath)));