#!/usr/bin/php
<?php

include_once __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';

$sitesPerTag = [];
foreach (s9e_MediaBBCodes::$sites as $siteId => $site)
{
	if (!isset($site[s9e_MediaBBCodes::KEY_TAGS]))
	{
		continue;
	}

	foreach ($site[s9e_MediaBBCodes::KEY_TAGS] as $tag => $void)
	{
		$sitesPerTag[$tag][$siteId] = $site;
	}
}

ksort($sitesPerTag);
foreach ($sitesPerTag as $sites)
{
	asort($sites);
}

$html = '<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="noindex,nofollow">
	<title>s9e Media Sites</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
</head>
<body>
	<dl class="dl-horizontal">';

foreach ($sitesPerTag as $tag => $sites)
{
	$html .= "\n\t\t<dt>$tag</dt>";

	foreach ($sites as $site)
	{
		$html .= "\n\t\t<dd><a href='" . $site[s9e_MediaBBCodes::KEY_URL] . "'>" . $site[s9e_MediaBBCodes::KEY_TITLE] . '</a></dd>';
	}
}

$html .= '
	</dl>
</body>
</html>';

file_put_contents(__DIR__ . '/../www/tags.html', $html);