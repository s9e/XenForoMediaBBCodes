#!/usr/bin/php
<?php

$url = 'http://www.reddit.com/domain/' . $_SERVER['argv'][1] . '/new/.json?limit=35';

$response = json_decode(file_get_contents($url), true);
foreach ($response['data']['children'] as $child)
{
	$data = $child['data'];

	echo date('Y-m-d', $data['created']), ' ';
	printf('% 3d', $data['score']);
	echo ' ', $data['url'], "\n";
}