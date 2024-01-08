<?php

/**
* @copyright Copyright (c) 2013-2024 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

include_once __DIR__ . '/../vendor/autoload.php';

if (!isset($addonId))
{
	$sitesDir    = __DIR__ . '/../vendor/s9e/text-formatter/src/Plugins/MediaEmbed/Configurator/sites';
	$classFile   = __DIR__ . '/../build/upload/library/s9e/MediaBBCodes.php';
	$addonFile   = __DIR__ . '/../build/addon-s9e.xml';
	$addonId     = 's9e';
	$namespace   = 's9e';
	$className   = 's9e_MediaBBCodes';
	$addonTitle  = 's9e Media Pack';
	$addonUrl    = 'https://xenforo.com/community/resources/s9e-media-bbcodes-pack.2476/';
	$linkText    = 'Media embeds by s9e';
	$tagsDescUrl = 'http://s9e.github.io/XenForoMediaBBCodes/tags.html';
	$githubUrl   = 'https://github.com/s9e/XenForoMediaBBCodes/blob/master/docs/';
}

// The version is simply the current UTC day, optionally followed by the first argument given to
// this script
$version   = gmdate('Ymd');
$versionId = $version * 10;
if (isset($_SERVER['argv'][1]))
{
	$version   .= $_SERVER['argv'][1];
	$versionId += ($_SERVER['argv'][1] === '-dev') ? -1 : (ord($_SERVER['argv'][1]) - 97);
}

// Set up the MediaEmbed plugin
$configurator = new s9e\TextFormatter\Configurator;
$configurator->rendering->engine = 'PHP';
$configurator->rendering->engine->enableQuickRenderer = false;
//$configurator->rendering->engine->forceEmptyElements  = false;
//$configurator->rendering->engine->useEmptyElements    = false;
//$configurator->rendering->engine->serializer->branchTableThreshold = PHP_INT_MAX;
$configurator->rendering->engine->serializer->convertor->features['php80'] = false;
//$configurator->MediaEmbed->sitesDir = $sitesDir;

// Load the PHP file and remove the renderers
$php = file_get_contents($classFile);
$php = preg_replace('((.*?)(?:\\s*public static function render.*)?\\}$)s', '$1', $php);

// Redefine the constants here
preg_match_all('(^\\s*const\\s+.*)m', $php, $m);
eval(implode('', $m[0]));

function setAttributes(DOMElement $element, array $attributes)
{
	foreach ($attributes as $attrName => $attrValue)
	{
		$element->setAttribute($attrName, $attrValue);
	}
}

function exportSites(array $sites)
{
	$php = 'array(';
	foreach ($sites as $siteId => $site)
	{
		$php .= "\n\t\t" . var_export($siteId, true) . '=>' . exportConfig($site) . ',';
	}
	$php = substr($php, 0, -1) . "\n\t)";

	return $php;
}

function exportConfig(array $config)
{
	ksort($config);

	// Remove null values at the end of the config
	while (end($config) === null)
	{
		array_pop($config);
	}

	$i = 0;
	$elements = [];
	foreach ($config as $k => $v)
	{
		$php = ($k === $i++) ? '' : var_export($k, true) . '=>';

		if (is_array($v))
		{
			$php .= exportConfig($v);
		}
		elseif (is_null($v))
		{
			// var_export() exports null values as "NULL" in uppercase and uppercase sucks
			$php .= 'null';
		}
		elseif (strpos($v, "\n") !== false)
		{
			$php .= '"' . addcslashes($v, "\\\"\n") . '"';
		}
		else
		{
			$php .= var_export($v, true);
		}

		$elements[] = $php;
	}

	return 'array(' . implode(',', $elements) . ')';
}

// Create the XML document
$dom = new DOMDocument('1.0', 'utf-8');
$addon = $dom->appendChild($dom->createElement('addon'));
$addon->appendChild($dom->createElement('bb_code_media_sites'));

// Set the add-on informations
setAttributes(
	$addon,
	[
		'addon_id'                  => $addonId,
		'title'                     => $addonTitle,
		'url'                       => $addonUrl,
		'version_id'                => $versionId,
		'version_string'            => $version,
		'install_callback_class'    => $className,
		'install_callback_method'   => 'install',
		'uninstall_callback_class'  => $className,
		'uninstall_callback_method' => 'uninstall'
	]
);

$sites       = [];
$sitenames   = [];
$optionNames = [];
$php         = [$php];

foreach (glob($sitesDir . '/*.xml') as $siteFile)
{
	$site   = simplexml_load_file($siteFile);
	$siteId = basename($siteFile, '.xml');

	$configurator->tags->clear();
	$template = (string) $configurator->MediaEmbed->add($siteId)->template;
	$template = preg_replace('( data-s9e-livepreview-[-\\w]+=".*?")', '', $template);

	$config = array(
		KEY_HTML  => null,
		KEY_TAGS  => array(),
		KEY_TITLE => (string) $site['name'],
		KEY_URL   => (string) $site['homepage']
	);

	if (isset($site->tags->tag))
	{
		foreach ($site->tags->tag as $tag)
		{
			$config[KEY_TAGS][(string) $tag] = 1;
		}
	}
	else
	{
		// Untagged sites go into the "misc" category
		$config[KEY_TAGS]['misc'] = 1;
	}

	// Default HTML replacement. Ensure that iframe and script have an end tag
	$html = preg_replace('#(<(iframe|script)[^>]+)/>#', '$1></$2>', $template);

	$useEmbedCallback = false;

	// Test whether the template needs to be rendered in PHP. Some sites don't like URL-encoding so
	// we force them to be rendered in PHP regardless of the template. XenForo encodes ":" to %3A
	// which Comedy Central's servers don't like
	if (strpos($html, '<xsl:') !== false
	 || preg_match('(="[^"]*(?<!\\{)\\{(?!\\{|@id\\}))', $html)
	 || strpos($html, 'media.mtvnservices.com') !== false
	 || $siteId === 'cnn'
	 || $siteId === 'gist'
	 || $siteId === 'imgur'
	 || $siteId === 'mailru'
	 || $siteId === 'telegram'
	 || $siteId === 'theguardian')
	{
		// Capture the PHP source for this template
		$regexp = '(switch\\(\\$node->nodeName\\)\\{[^:]+:(.*?)break;case\'br\')s';

		$configurator->rendering->getRenderer();
		$source = file_get_contents($configurator->rendering->engine->lastFilepath);
		if (!preg_match($regexp, $source, $m))
		{
			die('Could not match ' . $site['name'] . " renderer\n");
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
			echo "$src\n";
			echo 'Cannot convert ', $site['name'], " (->)\n";
			die(1);
		}

		// Collect the name of all vars in use to initialize them with a null value
		$vars = [];
		preg_match_all('(@(\\w+))', $template, $matches);
		foreach ($matches[1] as $varName)
		{
			$defaultValue = 'null';
			if (isset($site->attributes->$varName))
			{
				$attribute = $site->attributes->$varName;
				if (isset($attribute['defaultValue']))
				{
					$defaultValue = $attribute['defaultValue'];
				}
			}
			$vars[$varName] = var_export($varName, true) . ' => ' . $defaultValue;
		}
		ksort($vars);

		$php[] = '';
		$php[] = '	public static function render' . ucfirst($siteId) . '($vars)';
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
		// Replace XSL attributes with XenForo's syntax and unescape brackets
		$config[KEY_HTML] = strtr($html, ['{@' => '{$', '{{' => '{', '}}' => '}']);
	}

	$regexps          = [];
	$extractRegexps   = [];
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
		$extractRegexps[$regexp] = $regexp;
	}

	foreach ($site->scrape as $scrape)
	{
		$entry = [];

		foreach ($scrape->match as $match)
		{
			$entry['match'][] = $regexps[] = (string) $match;
		}

		if (!isset($entry['match']))
		{
			$entry['match'] = ['//'];
		}

		foreach ($scrape->extract as $extract)
		{
			$entry['extract'][] = (string) $extract;
		}

		if (isset($scrape['url']))
		{
			$entry['url'] = (string) $scrape['url'];
		}

		$config[KEY_SCRAPES][] = $entry;
		$useMatchCallback = true;
	}

	if ($siteId === 'livestream')
	{
		$regexps = ['()'];
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
				$regexps[$k] = $regexp[0] . '(?=.*?[./]' . $hostsRegexp . '[:/]).*?' . substr($regexp, 1);

				if (isset($extractRegexps[$regexp]))
				{
					$extractRegexps[$regexp] = $regexps[$k];
				}
			}
		}
	}

	if (isset($site->attributes))
	{
		$filters = array();
		foreach ($site->attributes->children() as $attribute)
		{
			if (isset($attribute['filterChain']))
			{
				$attrName = $attribute->getName();
				$callback = (string) $attribute['filterChain'];
				if ($callback[0] === '#')
				{
					$callback = $className . '::filter' . ucfirst(substr($callback, 1));
				}
				$filters[$attrName] = [$callback];
			}
		}

		if ($filters)
		{
			$config[KEY_FILTERS] = $filters;
			$useMatchCallback = true;
		}
	}

	if ($useMatchCallback)
	{
		$config[KEY_USE_MATCH_CALLBACK] = true;
	}

	// Test whether each regexp has a capture named "id". If not, add an empty capture named "id" to
	// satisfy XenForo's assumptions that every regexp contains one
	foreach ($regexps as &$regexp)
	{
		if (strpos($regexp, "(?'id'") === false)
		{
			$regexp = $regexp[0] . "(?'id')" . substr($regexp, 1);
		}
	}
	unset($regexp);

	$matchUrls = (empty($regexps)) ? "((?'id')" . $hostsRegexp . '/)' : implode("\n", $regexps);
	$config[KEY_MATCH_URLS] = $matchUrls;
	$config[KEY_EXTRACT_REGEXPS] = array_values($extractRegexps);

	// Record the name of the site
	$sitenames[] = (string) $site['name'];

	// Save the site's config
	$sites[$siteId] = $config;
}
$php[] = '}';

// Replace the sites config in the source
$php[0] = preg_replace_callback(
	'((?<=public static \\$sites = ).*?(?=;\\s*/\\*\\*))s',
	function () use ($sites)
	{
		$php = exportSites($sites);

		// Replace (?'id' with (?P<id>
		$php = preg_replace("(\\(\\?\\\\?'(\\w+)\\\\?')", '(?P<$1>', $php);

		return $php;
	},
	$php[0]
);

// Remove temp files
array_map('unlink', glob(sys_get_temp_dir() . '/Renderer_*'));

// Prepare the phrase group
$phrases = $addon->appendChild($dom->createElement('phrases'));

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', $addonTitle)),
	[
		'title'          => 'option_group_' . $addonId,
		'version_id'     => '1',
		'version_string' => '1'
	]
);

// Add template modifications
$modifications = $addon->appendChild($dom->createElement('public_template_modifications'));
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Fixes responsive embeds in XenForo Media Gallery',
		'enabled'          => 1,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_xmg_fix',
		'template'         => 'xengallery_media_view.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^)'));
$modification->appendChild($dom->createElement(
	'replace',
	".videoContainer>[data-s9e-mediaembed]\n{\n\tmax-width:100%!important;\n}\n\n"
));
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => "Fixes responsive embeds in XenForo Media Gallery's lightbox",
		'enabled'          => 1,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_lightbox_fix',
		'template'         => 'xengallery_media_preview.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^)'));
$modification->appendChild($dom->createElement(
	'replace',
	".mfp-iframe-scaler>span[data-s9e-mediaembed]>span>iframe\n{\n\tposition: fixed !important;\n}\n\n"
));
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Fixes responsive embeds in sonnb XenGallery',
		'enabled'          => 1,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_sonnb_fix',
		'template'         => 'sonnb_xengallery_photo_view.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^.?)s'));
$modification->appendChild($dom->createElement(
	'replace',
	".video>[data-s9e-mediaembed],.videoHolder>[data-s9e-mediaembed]\n{\n\tmax-width:100%!important;\n}\n\n\$0"
));

$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Adds a scrollbar to the Add Media dialog',
		'enabled'          => 1,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_add_media_dialog',
		'template'         => 'editor_ui.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^.?)s'));
$modification->appendChild($dom->createElement(
	'replace',
	"#redactor_media_link+.listInline\n{\n\tmax-height:40vh;overflow-y:scroll;\n}\n\n\$0"
));

// Prepare the option group
$optiongroups = $addon->appendChild($dom->createElement('optiongroups'));

$group = $optiongroups->appendChild($dom->createElement('group'));
$group->setAttribute('group_id',      $addon->getAttribute('addon_id'));
$group->setAttribute('display_order', 0);
$group->setAttribute('debug_only',    0);
$displayOrder = 0;

// Add the attribution link
if (isset($linkText))
{
	$modification  = $modifications->appendChild($dom->createElement('modification'));
	setAttributes(
		$modification,
		[
			'action'           => 'str_replace',
			'description'      => 'Adds a link back to ' . $addonTitle,
			'enabled'          => 1,
			'execution_order'  => 10,
			'modification_key' => $addonId . '_footer',
			'template'         => 'footer'
		]
	);
	$modification->appendChild($dom->createElement(
		'find',
		'{xen:phrase extra_copyright}'
	));
	$modification->appendChild($dom->createElement(
		'replace',
		' | <a class="concealed" href="' . $addonUrl . '" title="Media BBCodes provided by ' . $addonTitle . ' v' . $version . '">' . $linkText . '</a>$0'
	));

	$option = $optiongroups->appendChild($dom->createElement('option'));
	setAttributes(
		$option,
		[
			'option_id'         => $addonId . '_footer',
			'edit_format'       => 'radio',
			'data_type'         => 'string',
			'can_backup'        => 1,
			'validation_class'  => $className,
			'validation_method' => 'validateFooter'
		]
	);
	$option->appendChild($dom->createElement('default_value', 'show'));
	$option->appendChild($dom->createElement('edit_format_params', "show=I want to display a link to this add-on in the page footer\nhide=I do not want to display a link to this add-on"));
	setAttributes(
		$option->appendChild($dom->createElement('relation')),
		[
			'group_id'      => $addon->getAttribute('addon_id'),
			'display_order' => ++$displayOrder
		]
	);

	setAttributes(
		$phrases->appendChild($dom->createElement('phrase', 'Show your support')),
		[
			'title'          => 'option_' . $addonId . '_footer',
			'version_id'     => '1',
			'version_string' => '1'
		]
	);
	$html = 'You may also choose to support the author directly with a voluntary donation in USD or in EUR.<br><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=ABGFV5AGE98AG"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" alt="Donate in USD" title="Donate in USD"><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6P6985GT2DLGL"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" alt="Donate in EUR" title="Donate in EUR"></a>';
	setAttributes(
		$phrases->appendChild($dom->createElement('phrase', htmlspecialchars($html))),
		[
			'title'          => 'option_' . $addonId . '_footer_explain',
			'version_id'     => '1',
			'version_string' => '1'
		]
	);
}

// Tag checkboxes
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Configure the s9e media sites')),
	[
		'title'          => 'option_group_' . $addonId . '_description',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Categories of media sites to install')),
	[
		'title'          => 'option_' . $addonId . '_media_tags',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

$html = '';
if (isset($tagsDescUrl))
{
	$html = '<a href="' . $tagsDescUrl . '" target="_blank">List of optional sites enabled by each category</a>';
}
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', htmlspecialchars($html))),
	[
		'title'          => 'option_' . $addonId . '_media_tags_explain',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Custom callbacks')),
	[
		'title'          => 'option_' . $addonId . '_custom_callbacks',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

$text = '';
if (isset($githubUrl))
{
	$text = '<a href="' . $githubUrl . 'CustomCallbacks.md" target="_blank" style="cursor:help">Help</a>';
}
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', $text)),
	[
		'title'          => 'option_' . $addonId . '_custom_callbacks_explain',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Custom dimensions')),
	[
		'title'          => 'option_' . $addonId . '_custom_dimensions',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', '<i>Deprecated</i>')),
	[
		'title'          => 'option_' . $addonId . '_custom_dimensions_explain',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Excluded sites')),
	[
		'title'          => 'option_' . $addonId . '_excluded_sites',
		'version_id'     => '1',
		'version_string' => '1'
	]
);
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Comma-separated list of sites not to install')),
	[
		'title'          => 'option_' . $addonId . '_excluded_sites_explain',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

// Add the category checkboxes
$option = $optiongroups->appendChild($dom->createElement('option'));
setAttributes(
	$option,
	[
		'option_id'         => $addonId . '_media_tags',
		'edit_format'       => 'checkbox',
		'data_type'         => 'array',
		'can_backup'        => 1,
		'validation_class'  => $className,
		'validation_method' => 'updateTags'
	]
);

$tags = [];
foreach ($sites as $site)
{
	if (isset($site[KEY_TAGS]))
	{
		$tags += $site[KEY_TAGS];
	}
}
ksort($tags);

$lines = [];
foreach (array_keys($tags) as $tag)
{
	$lines[] = $tag . '=' . ucfirst($tag);
}

$option->appendChild($dom->createElement('default_value', serialize($tags)));
$option->appendChild($dom->createElement('edit_format_params', implode("\n", $lines)));
$option->appendChild($dom->createElement('sub_options', '*'));
setAttributes(
	$option->appendChild($dom->createElement('relation')),
	[
		'group_id'      => $addon->getAttribute('addon_id'),
		'display_order' => ++$displayOrder
	]
);

// Add the custom callbacks list
$option = $optiongroups->appendChild($dom->createElement('option'));
setAttributes(
	$option,
	[
		'option_id'         => $addonId . '_custom_callbacks',
		'edit_format'       => 'textbox',
		'data_type'         => 'string',
		'can_backup'        => 1,
		'validation_class'  => $className,
		'validation_method' => 'validateCustomCallbacks'
	]
);
$option->appendChild($dom->createElement('edit_format_params', 'rows=2'));
setAttributes(
	$option->appendChild($dom->createElement('relation')),
	[
		'group_id'      => $addon->getAttribute('addon_id'),
		'display_order' => ++$displayOrder
	]
);

// Add the custom dimensions list
$option = $optiongroups->appendChild($dom->createElement('option'));
setAttributes(
	$option,
	[
		'option_id'         => $addonId . '_custom_dimensions',
		'edit_format'       => 'textbox',
		'data_type'         => 'string',
		'can_backup'        => 1,
		'validation_class'  => $className,
		'validation_method' => 'validateCustomDimensions'
	]
);
$option->appendChild($dom->createElement('edit_format_params', 'rows=2'));
setAttributes(
	$option->appendChild($dom->createElement('relation')),
	[
		'group_id'      => $addon->getAttribute('addon_id'),
		'display_order' => ++$displayOrder
	]
);

// Add the blacklist param
$option = $optiongroups->appendChild($dom->createElement('option'));
setAttributes(
	$option,
	[
		'option_id'         => $addonId . '_excluded_sites',
		'edit_format'       => 'textbox',
		'data_type'         => 'string',
		'can_backup'        => 1,
		'validation_class'  => $className,
		'validation_method' => 'validateExcludedSites'
	]
);
setAttributes(
	$option->appendChild($dom->createElement('relation')),
	[
		'group_id'      => $addon->getAttribute('addon_id'),
		'display_order' => ++$displayOrder
	]
);

// Add the lazy loading modification and option
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Defers the loading of embedded content',
		'enabled'          => 0,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_lazy_loading',
		'template'         => 'ad_thread_view_below_messages'
	]
);
$modification->appendChild($dom->createElement('find', '(^)'));
$modification->appendChild($dom->createElement(
	'replace',
	htmlspecialchars('<script>(function(){function h(a){a=a.getBoundingClientRect();var b=innerHeight+100;return-50<a.bottom&&a.top<b&&a.width}function k(){e=!0}function l(){for(var a=document.getElementsByTagName("iframe"),b=a.length,e=-1;++e<b;){var c=a[e],d=c;d.hasAttribute("data-lazy")||!d.hasAttribute("data-s9e-mediaembed")&&!d.parentNode.parentNode.hasAttribute("data-s9e-mediaembed")||h(d)||(f.push(c),d=c.getAttribute("onload"),c.hasAttribute("onload")&&0>d.indexOf("data-lazy")&&c.setAttribute("onload","if(!hasAttribute(\'data-lazy\')){"+d+"}"),c.setAttribute("data-lazy",""),c.contentWindow.location.replace("data:text/html,"))}}var f=[],g=!0,e=!1;l();f.length&&(3<f.length&&setInterval(l,6E4),addEventListener("scroll",k),addEventListener("resize",k),addEventListener("click",k),setInterval(function(){if(e)e=!1,g=!0;else if(g){g=!1;for(var a=f.length;0<=--a;){var b=f[a];h(b)&&(b.contentWindow.location.replace(b.src),b.removeAttribute("data-lazy"),f.splice(a,1))}}},100))})()</script>')
));

$option = $optiongroups->appendChild($dom->createElement('option'));
setAttributes(
	$option,
	[
		'option_id'         => $addonId . '_lazy_loading',
		'edit_format'       => 'radio',
		'data_type'         => 'string',
		'can_backup'        => 1,
		'validation_class'  => $className,
		'validation_method' => 'validateLazyLoading'
	]
);
$option->appendChild($dom->createElement('default_value', 'immediate'));
$option->appendChild($dom->createElement('edit_format_params', "immediate=Load embedded content immediately\nlazy=Defer loading embedded content until it's visible (experimental)"));
setAttributes(
	$option->appendChild($dom->createElement('relation')),
	[
		'group_id'      => $addon->getAttribute('addon_id'),
		'display_order' => ++$displayOrder
	]
);
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Performance')),
	[
		'title'          => 'option_' . $addonId . '_lazy_loading',
		'version_id'     => '1',
		'version_string' => '1'
	]
);
setAttributes(
	$phrases->appendChild($dom->createElement('phrase', 'Deferring the loading of embedded content makes pages load faster and use less memory.')),
	[
		'title'          => 'option_' . $addonId . '_lazy_loading_explain',
		'version_id'     => '1',
		'version_string' => '1'
	]
);

// Make the CSS that applies to iframes in quotes apply to their wrapper too
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'str_replace',
		'description'      => 'Makes the CSS that applies to iframes in quote also apply to the responsive embed wrapper',
		'enabled'          => 1,
		'execution_order'  => 10,
		'modification_key' => $addonId . '_quote_css',
		'template'         => 'bb_code.css'
	]
);
$modification->appendChild($dom->createElement('find', '.bbCodeQuote iframe,'));
$modification->appendChild($dom->createElement(
	'replace',
	'.bbCodeQuote iframe, .bbCodeQuote [data-s9e-mediaembed],'
));

// Expand embeds inside of expanded quote blocks
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Expands embeds inside of expanded quote blocks',
		'enabled'          => 1,
		'execution_order'  => 1,
		'modification_key' => $addonId . '_quote_expanded_css',
		'template'         => 'bb_code.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^)'));
$modification->appendChild($dom->createElement(
	'replace',
	'.quoteContainer.expanded iframe[data-s9e-mediaembed],
	.quoteContainer.expanded [data-s9e-mediaembed] iframe
	{
		max-height: none;
		max-width:  none;
	}

'
));

// Un-position iframes inside of collapsed quote blocks
$modification  = $modifications->appendChild($dom->createElement('modification'));
setAttributes(
	$modification,
	[
		'action'           => 'preg_replace',
		'description'      => 'Un-positions iframes inside of collapsed quote blocks',
		'enabled'          => 1,
		'execution_order'  => 1,
		'modification_key' => $addonId . '_quote_collapsed_css',
		'template'         => 'bb_code.css'
	]
);
$modification->appendChild($dom->createElement('find', '(^)'));
$modification->appendChild($dom->createElement(
	'replace',
	'.quoteContainer:not(.expanded) [data-s9e-mediaembed] iframe
	{
		position: unset !important;
	}

'
));

// Add the params as XenForo options
ksort($optionNames);

$displayOrder = 100;
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
			'display_order' => ++$displayOrder
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
$php = implode("\n", $php);
if (file_get_contents($classFile) !== $php)
{
	file_put_contents($classFile, $php);
}

// Save addon.xml
$dom->formatOutput = true;
$xml = $dom->saveXML($addon);
if (file_get_contents($addonFile) !== $xml)
{
	file_put_contents($addonFile, $xml);
}

if ($addonId !== 's9e')
{
	return;
}

if (!file_exists(__DIR__ . '/../releases') || !empty($_SERVER['TRAVIS']))
{
	return;
}

// @codeCoverageIgnoreStart
chdir(__DIR__ . '/../build');
copy('../LICENSE', 'LICENSE');
exec('kzip -r -y ../releases/XenForoMediaBBCodes-' . $version . '.zip addon-s9e.xml LICENSE upload/library/s9e/MediaBBCodes.php');
exec('advzip -z4 ../releases/XenForoMediaBBCodes-' . $version . '.zip');

$readme =
'[B]This add-on is for XenForo 1.x. For XenForo 2.x, install [URL=https://xenforo.com/community/resources/s9e-media-sites.5973/]the s9e/MediaSites add-on[/URL].[/B]

[center][url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6P6985GT2DLGL"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url][url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ABGFV5AGE98AG"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url][/center]

This pack contains the definitions for [b]' . count($sitenames) . ' media sites[/b]: ' . implode(', ', $sitenames) . '. The complete list with examples of supported URLs can be found on [url="http://s9etextformatter.readthedocs.io/Plugins/MediaEmbed/Sites/"]this page[/url].

The BBCodes definitions are based on [url="https://github.com/s9e/TextFormatter"]the s9e\TextFormatter library[/url], and more specifically its [url="https://github.com/s9e/TextFormatter/tree/master/src/Plugins/MediaEmbed"]MediaEmbed[/url] plugin. The BBCodes are designed for performance: the media site is only accessed once during posting, and only if absolutely necessary.

This add-on is released under [url="http://en.wikipedia.org/wiki/MIT_License"]The MIT License[/url]. Redistribution is allowed as per the license terms. [color=#fff]This add-on is free, open source and available on GitHub. If you paid for it, you\'ve been swindled.[/color]
[url="https://travis-ci.org/s9e/XenForoMediaBBCodes"][img]https://travis-ci.org/s9e/XenForoMediaBBCodes.png?branch=master&' . $version . '[/img][/url] [url="https://coveralls.io/r/s9e/XenForoMediaBBCodes"][img]https://coveralls.io/repos/s9e/XenForoMediaBBCodes/badge.png?' . $version . '[/img][/url]

[SIZE=6][FONT=Arial][B]How to remove the branding[/B][/FONT][/SIZE]

The add-on displays a small notice in the page footer, alongside XenForo\'s copyright. You can disable this notice from the Admin CP by going to Home > Options > s9e Media Pack. If you do, please consider making a voluntary donation using the PayPal buttons below.

[size=6][font=Arial][b]Installation / Upgrade[/b][/font][/size]

Unzip the archive and upload the content of the [font=monospace]upload[/font] folder to your XenForo installation. Then go to your forum\'s Admin CP and install/upgrade the add-on via the provided [font=monospace]addon-s9e.xml[/font] file.

[size=6][font=Arial][b]Configuration[/b][/font][/size]

You can configure which categories of media sites you want installed in the Admin CP by going to Home > Options > s9e Media Pack. By default, all the media sites are installed. You do not need to reinstall after changing the configuration.

[size=6][font=Arial][b]How to request a media site[/b][/font][/size]

If there is a media site that you would want to see in this pack, you can request it in this thread and it will be considered for inclusion. Selection may depend on the site\'s popularity, Alexa rank and activity on social sites. [b]Please post a few links as examples[/b] of the kind of links that should be supported. You do not need to post the embed code, only links to the content [i]you[/i] want to embed.

[size=6][font=Arial][b]How to support this add-on[/b][/font][/size]

You can donate any amount of your hard earned money in EUR or USD using either of the following links. Reviewing and rating the add-on helps improve its visibility too.

[center][url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6P6985GT2DLGL"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url][url="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ABGFV5AGE98AG"][img]https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif[/img][/url][/center]';

file_put_contents(
	__DIR__ . '/../releases/XenForoMediaBBCodes-' . $version . '.txt',
	$readme
);
// @codeCoverageIgnoreEnd