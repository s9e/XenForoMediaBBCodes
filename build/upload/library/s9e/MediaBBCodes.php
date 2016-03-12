<?php

/**
* @copyright Copyright (c) 2013-2016 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

class s9e_MediaBBCodes
{
	/**
	* Index at which the regexps used to extract vars from the URL are stored in the sites config
	*/
	const KEY_EXTRACT_REGEXPS = 4;

	/**
	* Index at which attribute filters are stored in the sites config
	*/
	const KEY_FILTERS = 9;

	/**
	* Index at which a site's embed HTML is stored in the sites config
	*/
	const KEY_HTML = 7;

	/**
	* Index at which the "match_urls" value for the media site is stored
	*/
	const KEY_MATCH_URLS = 3;

	/**
	* Index at which a site's scrape config is stored in the sites config
	*/
	const KEY_SCRAPES = 6;

	/**
	* Index at which a site's tags are stored in the sites config
	*/
	const KEY_TAGS = 2;

	/**
	* Index at which a site's title is stored in the sites config
	*/
	const KEY_TITLE = 0;

	/**
	* Index at which the unresponsive site flag stored in the sites config
	*/
	const KEY_UNRESPONSIVE = 8;

	/**
	* Index at which a site's homepage URL is stored in the sites config
	*/
	const KEY_URL = 1;

	/**
	* Index at which the flag that indicates whether the site has a match callback is stored
	*/
	const KEY_USE_MATCH_CALLBACK = 5;

	/**
	* @var string Path to a cache dir, used to cache scraped pages
	*/
	public static $cacheDir;

	/**
	* @var array Associative array using site IDs as keys, callbacks as values
	*/
	public static $customCallbacks;

	/**
	* @var array Associative array using site IDs as keys, [width, height] as values
	*/
	public static $customDimensions;

	/**
	* @var string Comma-separated list of sites that should not be installed
	*/
	public static $excludedSites;

	/**
	* @var integer Maximum width for responsive embeds
	*/
	public static $maxResponsiveWidth;

	/**
	* @var array Associative array using site IDs as keys, sites' config arrays as values
	*/
	public static $sites = array(
		'abcnews'=>array('ABC News','http://abcnews.go.com/',array('news'=>1),'!abcnews\\.go\\.com/[^/]+/video/[^/]+-(?P<id>\\d+)!',array('!abcnews\\.go\\.com/[^/]+/video/[^/]+-(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="abcnews" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//abcnews.go.com/video/embed?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'amazon'=>array('Amazon Product','http://affiliate-program.amazon.com/',array('misc'=>1),"#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|es|fr|it)[:/]).*?/(?:dp|gp/product)/(?P<id>[A-Z0-9]+)#\n#(?P<id>)(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|es|fr|it)[:/]).*?amazon\\.(?:co\\.)?(?P<tld>ca|de|es|fr|it|jp|uk)#",array('#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|es|fr|it)[:/]).*?/(?:dp|gp/product)/(?P<id>[A-Z0-9]+)#','#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|es|fr|it)[:/]).*?amazon\\.(?:co\\.)?(?P<tld>ca|de|es|fr|it|jp|uk)#'),true),
		'audioboom'=>array('audioBoom','https://audioboom.com/',array('podcasts'=>1),'!audioboo(?:\\.f|m\\.co)m/boos/(?P<id>\\d+)!',array('!audioboo(?:\\.f|m\\.co)m/boos/(?P<id>\\d+)!'),7=>'<iframe data-s9e-mediaembed="audioboom" allowfullscreen="" scrolling="no" src="//audioboom.com/boos/{$id}/embed/v3" style="border:0;height:150px;max-width:700px;width:100%"></iframe>'),
		'audiomack'=>array('Audiomack','http://www.audiomack.com/',array('music'=>1),'!audiomack\\.com/(?P<mode>album|song)/(?P<id>[-\\w]+/[-\\w]+)!',array('!audiomack\\.com/(?P<mode>album|song)/(?P<id>[-\\w]+/[-\\w]+)!'),true),
		'bandcamp'=>array('Bandcamp','http://bandcamp.com/',array('music'=>1),"!(?P<id>)bandcamp\\.com/album/.!\n!(?P<id>)bandcamp\\.com/track/.!",array(),true,array(array('extract'=>array('!/album=(?P<album_id>\\d+)!'),'match'=>array('!bandcamp\\.com/album/.!')),array('extract'=>array('!"album_id":(?P<album_id>\\d+)!','!"track_num":(?P<track_num>\\d+)!','!/track=(?P<track_id>\\d+)!'),'match'=>array('!bandcamp\\.com/track/.!')))),
		'bbcnews'=>array('BBC News','http://www.bbc.com/news/video_and_audio/',array('news'=>1),'!(?P<id>)bbc\\.com/news/\\w+!',array(),true,array(array('extract'=>array('!meta name="twitter:player".*?playlist=(?P<playlist>[-/\\w]+)(?:&poster=(?P<poster>[-/.\\w]+))?(?:&ad_site=(?P<ad_site>[/\\w]+))?!'),'match'=>array('!bbc\\.com/news/\\w+!')))),
		'blab'=>array('Blab','https://blab.im/',array('social'=>1),'#blab\\.im/(?!about$|live$|replay$|scheduled$|search\\?)(?P<id>[-\\w]+)#',array('#blab\\.im/(?!about$|live$|replay$|scheduled$|search\\?)(?P<id>[-\\w]+)#'),7=>'<div data-s9e-mediaembed="blab" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="https://blab.im/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'bleacherreport'=>array('Bleacher Report videos','http://bleacherreport.com/',array('sports'=>1),'!(?P<id>)(?=.*?[./]bleacherreport\\.com[:/]).*?/articles/.!',array(),true,array(array('extract'=>array('!id="video-(?P<id>[-\\w]+)!'),'match'=>array('!/articles/.!'))),'<div data-s9e-mediaembed="bleacherreport" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//bleacherreport.com/video_embed?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'break'=>array('Break','http://www.break.com/',array('entertainment'=>1),'!break\\.com/video/.*-(?P<id>\\d+)$!',array('!break\\.com/video/.*-(?P<id>\\d+)$!'),7=>'<div data-s9e-mediaembed="break" style="display:inline-block;width:100%;max-width:464px"><div style="overflow:hidden;position:relative;padding-bottom:60.344827586207%"><iframe allowfullscreen="" scrolling="no" src="//break.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'brightcove'=>array('Brightcove','https://www.brightcove.com/',array('videos'=>1),'!(?P<id>)link\\.brightcove\\.com/services/player/!',array(),true,array(array('extract'=>array('!meta name="twitter:player" content=".*?bcpid(?P<bcpid>\\d+).*?bckey=(?P<bckey>[-,~\\w]+).*?bctid=(?P<bctid>\\d+)!'),'match'=>array('!link\\.brightcove\\.com/services/player/!')))),
		'cbsnews'=>array('CBS News Video','http://www.cbsnews.com/video/',array('news'=>1),"#cbsnews\\.com/video/watch/\\?id=(?P<id>\\d+)#\n#(?P<id>)cbsnews\\.com/videos/(?!watch/)#",array('#cbsnews\\.com/video/watch/\\?id=(?P<id>\\d+)#'),true,array(array('extract'=>array('#"pid":"(?P<pid>\\w+)"#'),'match'=>array('#cbsnews\\.com/videos/(?!watch/)#')))),
		'cnbc'=>array('CNBC','http://www.cnbc.com/',array('news'=>1),'!(?=.*?[./]video\\.cnbc\\.com[:/]).*?cnbc\\.com/gallery/\\?video=(?P<id>\\d+)!',array('!(?=.*?[./]video\\.cnbc\\.com[:/]).*?cnbc\\.com/gallery/\\?video=(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="cnbc" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:95%"><object data="//plus.cnbc.com/rssvideosearch/action/player/id/{$id}/code/cnbcplayershare" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"/></object></div></div>'),
		'cnn'=>array('CNN','http://edition.cnn.com/video/',array('news'=>1),"!(?=.*?[./]cnn\\.com[:/]).*?cnn.com/videos/(?P<id>.*\\.cnn)!\n!cnn\\.com/video/data/2\\.0/video/(?P<id>.*\\.cnn)!",array('!(?=.*?[./]cnn\\.com[:/]).*?cnn.com/videos/(?P<id>.*\\.cnn)!','!cnn\\.com/video/data/2\\.0/video/(?P<id>.*\\.cnn)!')),
		'cnnmoney'=>array('CNNMoney','http://money.cnn.com/video/',array('news'=>1),'!money\\.cnn\\.com/video/(?P<id>.*\\.cnnmoney)!',array('!money\\.cnn\\.com/video/(?P<id>.*\\.cnnmoney)!'),7=>'<div data-s9e-mediaembed="cnnmoney" style="display:inline-block;width:100%;max-width:560px"><div style="overflow:hidden;position:relative;padding-bottom:64.285714285714%"><iframe allowfullscreen="" scrolling="no" src="http://money.cnn.com/.element/ssi/video/7.0/players/embed.player.html?videoid=video/{$id}&amp;width=560&amp;height=360" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'collegehumor'=>array('CollegeHumor','http://www.collegehumor.com/',array('entertainment'=>1),'!collegehumor\\.com/(?:video|embed)/(?P<id>\\d+)!',array('!collegehumor\\.com/(?:video|embed)/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="collegehumor" style="display:inline-block;width:100%;max-width:600px"><div style="overflow:hidden;position:relative;padding-bottom:61.5%"><iframe allowfullscreen="" scrolling="no" src="//www.collegehumor.com/e/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'comedycentral'=>array('Comedy Central','http://www.comedycentral.com/funny-videos',array('entertainment'=>1),'!(?P<id>)c(?:c|omedycentral)\\.com/(?:full-episode|video-clip)s/!',array(),true,array(array('extract'=>array('!(?P<id>mgid:arc:(?:episode|video):[.\\w]+:[-\\w]+)!'),'match'=>array('!c(?:c|omedycentral)\\.com/(?:full-episode|video-clip)s/!')))),
		'coub'=>array('Coub','http://coub.com/',array('videos'=>1),'!coub\\.com/view/(?P<id>\\w+)!',array('!coub\\.com/view/(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="coub" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//coub.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'dailymotion'=>array('Dailymotion','http://www.dailymotion.com/',array('videos'=>1),'!dailymotion\\.com/(?:live/|swf/|user/[^#]+#video=|(?:related/\\d+/)?video/)(?P<id>[A-Za-z0-9]+)!',array('!dailymotion\\.com/(?:live/|swf/|user/[^#]+#video=|(?:related/\\d+/)?video/)(?P<id>[A-Za-z0-9]+)!'),7=>'<div data-s9e-mediaembed="dailymotion" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.dailymotion.com/embed/video/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'democracynow'=>array('Democracy Now!','http://www.democracynow.org/',array('misc'=>1),"!(?=.*?[./]democracynow\\.org[:/]).*?democracynow.org/(?:embed/)?(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)!\n!(?P<id>)m\\.democracynow\\.org/stories/\\d!",array('!(?=.*?[./]democracynow\\.org[:/]).*?democracynow.org/(?:embed/)?(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)!'),true,array(array('extract'=>array('!democracynow\\.org/(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)\' rel=\'canonical!'),'match'=>array('!m\\.democracynow\\.org/stories/\\d!')))),
		'dumpert'=>array('dumpert','http://www.dumpert.nl/',array('.nl'=>1,'entertainment'=>1),'!(?P<id>)dumpert\\.nl/mediabase/\\d+/\\w+!',array(),true,array(array('extract'=>array('!data-itemid="(?P<id>\\w+)!'),'match'=>array('!dumpert\\.nl/mediabase/\\d+/\\w+!')))),
		'eighttracks'=>array('8tracks','http://8tracks.com/',array('music'=>1),"!8tracks\\.com/[-\\w]+/(?P<id>\\d+)(?=#|$)!\n!(?P<id>)8tracks\\.com/[-\\w]+/[-\\w]+!",array('!8tracks\\.com/[-\\w]+/(?P<id>\\d+)(?=#|$)!'),true,array(array('extract'=>array('!eighttracks://mix/(?P<id>\\d+)!'),'match'=>array('!8tracks\\.com/[-\\w]+/[-\\w]+!'))),'<div data-s9e-mediaembed="eighttracks" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//8tracks.com/mixes/{$id}/player_v3_universal" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'espn'=>array('ESPN','http://espn.go.com/',array('sports'=>1),'#(?=.*?[./]espn\\.go\\.com[:/]).*?(?P<cms>deportes|espn(?!d)).*(?:clip\\?|video\\?v|clipDeportes\\?)id=(?:\\w+:)?(?P<id>\\d+)#',array('#(?=.*?[./]espn\\.go\\.com[:/]).*?(?P<cms>deportes|espn(?!d)).*(?:clip\\?|video\\?v|clipDeportes\\?)id=(?:\\w+:)?(?P<id>\\d+)#'),true),
		'facebook'=>array('Facebook','http://www.facebook.com/',array('social'=>1),'@/(?!(?:apps|developers|graph)\\.)[-\\w.]*facebook\\.com/(?:[/\\w]+/permalink|(?!pages/|groups/).*?)(?:/|fbid=|\\?v=)(?P<id>\\d+)(?=$|[/?&#])@',array('@/(?!(?:apps|developers|graph)\\.)[-\\w.]*facebook\\.com/(?:[/\\w]+/permalink|(?!pages/|groups/).*?)(?:/|fbid=|\\?v=)(?P<id>\\d+)(?=$|[/?&#])@'),7=>'<iframe data-s9e-mediaembed="facebook" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/facebook.min.html#{$id}" style="border:0;height:360px;max-width:640px;width:100%"></iframe>'),
		'flickr'=>array('Flickr','https://www.flickr.com/',array('images'=>1),'!flickr\\.com/photos/[^/]+/(?P<id>\\d+)!',array('!flickr\\.com/photos/[^/]+/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="flickr" style="display:inline-block;width:100%;max-width:500px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="https://www.flickr.com/photos/_/{$id}/player/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'foratv'=>array('FORA.tv','http://fora.tv/',array('misc'=>1),'!(?P<id>)fora\\.tv/\\d+/\\d+/\\d+/.!',array(),true,array(array('extract'=>array('!embed\\?id=(?P<id>\\d+)!'),'match'=>array('!fora\\.tv/\\d+/\\d+/\\d+/.!'))),'<div data-s9e-mediaembed="foratv" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//library.fora.tv/embed?id={$id}&amp;type=c" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'foxnews'=>array('Fox News','http://www.foxnews.com/',array('news'=>1),'!video\\.foxnews\\.com/v/(?P<id>\\d+)!',array('!video\\.foxnews\\.com/v/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="foxnews" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video.foxnews.com/v/video-embed.html?video_id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'funnyordie'=>array('Funny or Die','http://www.funnyordie.com/',array('entertainment'=>1),'!funnyordie\\.com/videos/(?P<id>[0-9a-f]+)!',array('!funnyordie\\.com/videos/(?P<id>[0-9a-f]+)!'),7=>'<div data-s9e-mediaembed="funnyordie" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://www.funnyordie.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'gamespot'=>array('Gamespot','http://www.gamespot.com/',array('gaming'=>1),'!gamespot\\.com.*?/(?:events|videos)/.*?-(?P<id>\\d+)/(?:[#?].*)?$!',array('!gamespot\\.com.*?/(?:events|videos)/.*?-(?P<id>\\d+)/(?:[#?].*)?$!'),7=>'<div data-s9e-mediaembed="gamespot" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:62.5%"><iframe allowfullscreen="" scrolling="no" src="//www.gamespot.com/videos/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'gametrailers'=>array('GameTrailers','http://www.gametrailers.com/',array('gaming'=>1),'!(?P<id>)gametrailers\\.com/(?:full-episode|review|video)s/!',array(),true,array(array('extract'=>array('!embed/(?P<id>\\d+)!'),'match'=>array('!gametrailers\\.com/(?:full-episode|review|video)s/!')))),
		'getty'=>array('Getty Images','http://www.gettyimages.com/',array('images'=>1),"!gty\\.im/(?P<id>\\d+)!\n!(?=.*?[./]g(?:ettyimages\\.(?:c(?:n|o(?:\\.(?>jp|uk)|m(?>\\.au)?))|d[ek]|es|fr|i[et]|nl|pt|[bs]e)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?P<id>\\d+)!",array('!gty\\.im/(?P<id>\\d+)!','!(?=.*?[./]g(?:ettyimages\\.(?:c(?:n|o(?:\\.(?>jp|uk)|m(?>\\.au)?))|d[ek]|es|fr|i[et]|nl|pt|[bs]e)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?P<id>\\d+)!'),true,array(array('extract'=>array('!"height":[ "]*(?P<height>\\d+)!','!"width":[ "]*(?P<width>\\d+)!','!et=(?P<et>[-=\\w]+)!','!sig=(?P<sig>[-=\\w]+)!'),'match'=>array('//'),'url'=>'http://embed.gettyimages.com/preview/{@id}'))),
		'gfycat'=>array('Gfycat','http://gfycat.com/',array('images'=>1),'!gfycat\\.com/(?P<id>\\w+)!',array('!gfycat\\.com/(?P<id>\\w+)!'),true,array(array('extract'=>array('!gfyHeight\\s*=\\s*"?(?P<height>\\d+)!','!gfyWidth\\s*=\\s*"?(?P<width>\\d+)!'),'match'=>array('//'),'url'=>'http://gfycat.com/{@id}'))),
		'gist'=>array('GitHub Gist (via custom iframe)','https://gist.github.com/',array('misc'=>1),'!gist\\.github\\.com/(?P<id>(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!',array('!gist\\.github\\.com/(?P<id>(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!'),7=>'<iframe data-s9e-mediaembed="gist" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/gist.min.html#{$id}" style="border:0;height:180px;width:100%"></iframe>'),
		'globalnews'=>array('Global News','http://globalnews.ca/',array('.ca'=>1,'news'=>1),'!globalnews\\.ca/video/(?P<id>\\d+)!',array('!globalnews\\.ca/video/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="globalnews" style="display:inline-block;width:100%;max-width:560px"><div style="overflow:hidden;position:relative;padding-bottom:67.321428571429%"><iframe allowfullscreen="" scrolling="no" src="http://globalnews.ca/video/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'gofundme'=>array('GoFundMe','http://www.gofundme.com/',array('fundraising'=>1),'@gofundme\\.com/(?P<id>\\w+)(?![^#?])@',array('@gofundme\\.com/(?P<id>\\w+)(?![^#?])@'),7=>'<div data-s9e-mediaembed="gofundme" style="display:inline-block;width:100%;max-width:258px"><div style="overflow:hidden;position:relative;padding-bottom:131.00775193798%"><object data="//funds.gofundme.com/Widgetflex.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"/><param name="flashvars" value="page={$id}"/></object></div></div>'),
		'googledrive'=>array('Google Drive','https://drive.google.com',array('documents'=>1,'images'=>1,'videos'=>1),'!drive\\.google\\.com/file/d/(?P<id>[-\\w]+)!',array('!drive\\.google\\.com/file/d/(?P<id>[-\\w]+)!'),7=>'<div data-s9e-mediaembed="googledrive" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:75%"><iframe allowfullscreen="" scrolling="no" src="//drive.google.com/file/d/{$id}/preview" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'googleplus'=>array('Google+','https://plus.google.com/',array('social'=>1),'!(?P<id>)//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?P<name>\\w+)|(?P<oid>\\d+))/posts/(?P<pid>\\w+)!',array('!//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?P<name>\\w+)|(?P<oid>\\d+))/posts/(?P<pid>\\w+)!'),true),
		'googlesheets'=>array('Google Sheets','http://www.google.com/sheets/about/',array('documents'=>1),'!docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|s/d/)(?P<id>[-\\w]+)[^#]*(?:#gid=(?P<gid>\\d+))?!',array('!docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|s/d/)(?P<id>[-\\w]+)[^#]*(?:#gid=(?P<gid>\\d+))?!'),true),
		'healthguru'=>array('Healthguru','http://www.healthguru.com/',array('health'=>1),'!(?P<id>)healthguru\\.com/(?:content/)?video/.!',array(),true,array(array('extract'=>array('!healthguru\\.com/embed/(?P<id>\\w+)!'),'match'=>array('!healthguru\\.com/(?:content/)?video/.!'))),'<div data-s9e-mediaembed="healthguru" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.healthguru.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'hudl'=>array('Hudl','http://www.hudl.com/',array('sports'=>1),"!(?P<id>)hudl\\.com/athlete/(?P<athlete>\\d+)/highlights/(?P<highlight>\\d+)!\n!(?P<id>)hudl\\.com/v/!",array('!hudl\\.com/athlete/(?P<athlete>\\d+)/highlights/(?P<highlight>\\d+)!'),true,array(array('extract'=>array('!hudl\\.com/athlete/(?P<athlete>\\d+)/highlights/(?P<highlight>\\d+)!'),'match'=>array('!hudl\\.com/v/!')))),
		'hulu'=>array('Hulu','http://www.hulu.com/',array('misc'=>1),'!(?P<id>)hulu\\.com/watch/!',array(),true,array(array('extract'=>array('!eid=(?P<id>[-\\w]+)!'),'match'=>array('!hulu\\.com/watch/!'))),'<div data-s9e-mediaembed="hulu" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://secure.hulu.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'humortvnl'=>array('HumorTV','http://humortv.vara.nl/pg.2.pg-home.html',array('.nl'=>1,'entertainment'=>1),'!humortv\\.vara\\.nl/\\w+\\.(?P<id>[-.\\w]+)\\.html!',array('!humortv\\.vara\\.nl/\\w+\\.(?P<id>[-.\\w]+)\\.html!'),7=>'<div data-s9e-mediaembed="humortvnl" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://humortv.vara.nl/embed.{$id}.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'ign'=>array('IGN','http://www.ign.com/videos/',array('gaming'=>1),'!(?P<id>https?://.*?ign\\.com/videos/.+)!',array('!(?P<id>https?://.*?ign\\.com/videos/.+)!'),7=>'<div data-s9e-mediaembed="ign" style="display:inline-block;width:100%;max-width:468px"><div style="overflow:hidden;position:relative;padding-bottom:56.196581196581%"><iframe allowfullscreen="" scrolling="no" src="http://widgets.ign.com/video/embed/content.html?url={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'imdb'=>array('IMDb','http://www.imdb.com/',array('movies'=>1),'!imdb\\.com/video/\\w+/vi(?P<id>\\d+)!',array('!imdb\\.com/video/\\w+/vi(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="imdb" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.imdb.com/video/imdb/vi{$id}/imdb/embed?autoplay=false&amp;width=560" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'imgur'=>array('Imgur','http://imgur.com/',array('images'=>1),"@imgur\\.com/(?!r/|user/)(?:gallery/)?(?P<id>(?:a/)?\\w+)(?!\\w|\\.(?:pn|jp)g)@\n@(?P<id>)imgur\\.com/(?!r/|user/)(?:a|gallery)/\\w@\n!(?P<id>)imgur\\.com/\\w+\\.(?:gifv|mp4|webm)!",array('@imgur\\.com/(?!r/|user/)(?:gallery/)?(?P<id>(?:a/)?\\w+)(?!\\w|\\.(?:pn|jp)g)@'),true,array(array('extract'=>array('!image\\s*:\\s*.*?"is_(?P<type>album)":true!','!<div id="(?P<type>album)-!','!class="(?P<type>album)-image!'),'match'=>array('@imgur\\.com/(?!r/|user/)(?:a|gallery)/\\w@')),array('extract'=>array('!width:\\s*(?P<width>\\d+)!','!height:\\s*(?P<height>\\d+)!','!(?P<type>gifv)!'),'match'=>array('!imgur\\.com/\\w+\\.(?:gifv|mp4|webm)!'),'url'=>'http://i.imgur.com/{@id}.gifv'))),
		'indiegogo'=>array('Indiegogo','http://www.indiegogo.com/',array('fundraising'=>1),'!indiegogo\\.com/projects/(?P<id>[-\\w]+)!',array('!indiegogo\\.com/projects/(?P<id>[-\\w]+)!'),7=>'<div data-s9e-mediaembed="indiegogo" style="display:inline-block;width:100%;max-width:222px"><div style="overflow:hidden;position:relative;padding-bottom:200.45045045045%"><iframe allowfullscreen="" scrolling="no" src="//www.indiegogo.com/project/{$id}/embedded" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'instagram'=>array('Instagram','http://instagram.com/',array('social'=>1),'!instagram\\.com/p/(?P<id>[-\\w]+)!',array('!instagram\\.com/p/(?P<id>[-\\w]+)!'),7=>'<iframe data-s9e-mediaembed="instagram" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/instagram.min.html#{$id}" style="border:0;height:640px;max-width:640px;width:100%"></iframe>'),
		'internetarchive'=>array('Internet Archive','https://archive.org/',array('misc'=>1),'!(?P<id>)archive\\.org/details/!',array(),true,array(array('extract'=>array('!meta property="twitter:player" content="https://archive.org/embed/(?P<id>[^/"]+)!','!meta property="og:video:width" content="(?P<width>\\d+)!','!meta property="og:video:height" content="(?P<height>\\d+)!'),'match'=>array('!archive\\.org/details/!')))),
		'izlesene'=>array('İzlesene','http://www.izlesene.com/',array('.tr'=>1),'!izlesene\\.com/video/[-\\w]+/(?P<id>\\d+)!',array('!izlesene\\.com/video/[-\\w]+/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="izlesene" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.izlesene.com/embedplayer/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'khl'=>array('Kontinental Hockey League (КХЛ)','http://www.khl.ru/',array('.ru'=>1,'sports'=>1),'!(?P<id>)video\\.khl\\.ru/(?:event|quote)s/\\d!',array(),true,array(array('extract'=>array('!/feed/start/(?P<id>[/\\w]+)!'),'match'=>array('!video\\.khl\\.ru/(?:event|quote)s/\\d!'))),'<div data-s9e-mediaembed="khl" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video.khl.ru/iframe/feed/start/{$id}?type_id=18&amp;width=560&amp;height=315" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'kickstarter'=>array('Kickstarter','http://www.kickstarter.com/',array('fundraising'=>1),'!kickstarter\\.com/projects/(?P<id>[^/]+/[^/?]+)(?:/widget/(?:(?P<card>card)|(?P<video>video)))?!',array('!kickstarter\\.com/projects/(?P<id>[^/]+/[^/?]+)(?:/widget/(?:(?P<card>card)|(?P<video>video)))?!'),true),
		'kissvideo'=>array('Kiss Video','http://www.kissvideo.click/index.html',array('videos'=>1),'!kissvideo\\.click/[^_]*_(?P<id>[0-9a-f]+)!',array('!kissvideo\\.click/[^_]*_(?P<id>[0-9a-f]+)!'),7=>'<div data-s9e-mediaembed="kissvideo" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.kissvideo.click/embed.php?vid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'libsyn'=>array('Libsyn','http://www.libsyn.com/',array('podcasts'=>1),'@(?P<id>)(?=.*?[./]libsyn\\.com[:/]).*?(?!\\.mp3)....$@',array(),true,array(array('extract'=>array('!embed/episode/id/(?P<id>\\d+)!'),'match'=>array('@(?!\\.mp3)....$@'))),'<iframe data-s9e-mediaembed="libsyn" allowfullscreen="" scrolling="no" src="//html5-player.libsyn.com/embed/episode/id/{$id}/height/45/width/900/theme/standard/direction/no/autoplay/no/autonext/no/thumbnail/no/preload/no/no_addthis/no/" style="border:0;height:45px;max-width:900px;width:100%"></iframe>'),
		'livecap'=>array('LiveCap','https://www.livecap.tv/',array('gaming'=>1),'!(?=.*?[./]livecap\\.tv[:/]).*?livecap.tv/[st]/(?P<channel>\\w+)/(?P<id>\\w+)!',array('!(?=.*?[./]livecap\\.tv[:/]).*?livecap.tv/[st]/(?P<channel>\\w+)/(?P<id>\\w+)!'),true),
		'liveleak'=>array('LiveLeak','http://www.liveleak.com/',array('videos'=>1),'!liveleak\\.com/view\\?i=(?P<id>[a-f_0-9]+)!',array('!liveleak\\.com/view\\?i=(?P<id>[a-f_0-9]+)!'),7=>'<div data-s9e-mediaembed="liveleak" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://www.liveleak.com/ll_embed?i={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'livestream'=>array('Livestream','http://new.livestream.com/',array('videos'=>1),'((?P<id>)(?=.*?[./]livestre(?>\\.a|am\\.co)m[:/]).*?)',array('!livestream\\.com/accounts/(?P<account_id>\\d+)/events/(?P<event_id>\\d+)!','!/videos/(?P<video_id>\\d+)!','!original\\.livestream\\.com/(?P<channel>\\w+)/video\\?clipId=(?P<clip_id>[-\\w]+)!'),true,array(array('extract'=>array('!accounts/(?P<account_id>\\d+)/events/(?P<event_id>\\d+)!'),'match'=>array('//')),array('extract'=>array('!//original\\.livestream\\.com/(?P<channel>\\w+)/video/(?P<clip_id>[-\\w]+)!'),'match'=>array('!livestre.am!')))),
		'mailru'=>array('Mail.Ru','http://my.mail.ru/',array('.ru'=>1),'!(?P<id>)my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!',array(),true,array(array('extract'=>array('!mail\\.ru/video/(?P<id>[/\\w]+)\\.html!'),'match'=>array('!my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!')))),
		'medium'=>array('Medium','https://medium.com/',array('blogging'=>1),'!medium\\.com/[^/]*/(?:[-\\w]+-)?(?P<id>[\\da-f]+)!',array('!medium\\.com/[^/]*/(?:[-\\w]+-)?(?P<id>[\\da-f]+)!')),
		'metacafe'=>array('Metacafe','http://www.metacafe.com/',array('videos'=>1),'!metacafe\\.com/watch/(?P<id>\\d+)!',array('!metacafe\\.com/watch/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="metacafe" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.metacafe.com/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'mixcloud'=>array('Mixcloud','http://www.mixcloud.com/',array('music'=>1),'@mixcloud\\.com/(?!categories|tag)(?P<id>[-\\w]+/[^/&]+)/@',array('@mixcloud\\.com/(?!categories|tag)(?P<id>[-\\w]+/[^/&]+)/@'),7=>'<div data-s9e-mediaembed="mixcloud" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//www.mixcloud.com/widget/iframe/?feed=http%3A%2F%2Fwww.mixcloud.com%2F{$id}%2F&amp;embed_type=widget_standard" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'mrctv'=>array('MRCTV','http://www.mrctv.org/',array('misc'=>1),'!(?P<id>)mrctv\\.org/videos/.!',array(),true,array(array('extract'=>array('!mrctv\\.org/embed/(?P<id>\\d+)!'),'match'=>array('!mrctv\\.org/videos/.!'))),'<div data-s9e-mediaembed="mrctv" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.mrctv.org/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'msnbc'=>array('MSNBC','http://www.msnbc.com/watch',array('news'=>1),"@(?P<id>)msnbc\\.com/[-\\w]+/watch/@\n@(?P<id>)on\\.msnbc\\.com/.@",array(),true,array(array('extract'=>array('@guid="?(?P<id>\\w+)@'),'match'=>array('@msnbc\\.com/[-\\w]+/watch/@','@on\\.msnbc\\.com/.@'))),'<div data-s9e-mediaembed="msnbc" style="display:inline-block;width:100%;max-width:635px"><div style="overflow:hidden;position:relative;padding-bottom:69.291338582677%"><iframe allowfullscreen="" scrolling="no" src="//player.theplatform.com/p/2E2eJC/EmbeddedOffSite?guid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'natgeochannel'=>array('National Geographic Channel','http://channel.nationalgeographic.com/',array('misc'=>1),'@channel\\.nationalgeographic\\.com/(?P<id>[-/\\w]+/videos/[-\\w]+)@',array('@channel\\.nationalgeographic\\.com/(?P<id>[-/\\w]+/videos/[-\\w]+)@'),7=>'<div data-s9e-mediaembed="natgeochannel" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//channel.nationalgeographic.com/{$id}/embed/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'natgeovideo'=>array('National Geographic Video','http://video.nationalgeographic.com/',array('documentaries'=>1),'@(?P<id>)video\\.nationalgeographic\\.com/(?:tv|video)/\\w@',array(),true,array(array('extract'=>array('@guid="(?P<id>[-\\w]+)"@'),'match'=>array('@video\\.nationalgeographic\\.com/(?:tv|video)/\\w@'))),'<div data-s9e-mediaembed="natgeovideo" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.d.nationalgeographic.com/players/ngsvideo/share/?guid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'nhl'=>array('NHL VideoCenter','http://video.nhl.com/videocenter/',array('sports'=>1),'!nhl\\.com/videocenter/.*?\\Wid=(?P<id>[-\\w]+)!',array('!nhl\\.com/videocenter/.*?\\Wid=(?P<id>[-\\w]+)!'),7=>'<div data-s9e-mediaembed="nhl" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:61.71875%"><iframe allowfullscreen="" scrolling="no" src="//video.nhl.com/videocenter/embed?playlist={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'npr'=>array('NPR','http://www.npr.org/',array('podcasts'=>1),"!(?P<id>)npr\\.org/[/\\w]+/\\d+!\n!(?P<id>)n\\.pr/\\w!",array(),true,array(array('extract'=>array('!player/embed/(?P<i>\\d+)/(?P<m>\\d+)!'),'match'=>array('!npr\\.org/[/\\w]+/\\d+!','!n\\.pr/\\w!')))),
		'nytimes'=>array('The New York Times Video','http://www.nytimes.com/video/',array('movies'=>1,'news'=>1),"!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?P<id>\\d+)!\n!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?P<id>\\d+)!\n!(?P<id>)nytimes\\.com/movie(?:s/movie)?/(?P<playlist>\\d+)/[-\\w]+/trailers!",array('!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?P<id>\\d+)!','!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?P<id>\\d+)!'),true,array(array('extract'=>array('!/video/movies/(?P<id>\\d+)!'),'match'=>array('!nytimes\\.com/movie(?:s/movie)?/(?P<playlist>\\d+)/[-\\w]+/trailers!'),'url'=>'http://www.nytimes.com/svc/video/api/playlist/{@playlist}?externalId=true')),'<div data-s9e-mediaembed="nytimes" style="display:inline-block;width:100%;max-width:585px"><div style="overflow:hidden;position:relative;padding-bottom:68.376068376068%"><iframe allowfullscreen="" scrolling="no" src="http://graphics8.nytimes.com/video/players/offsite/index.html?videoId={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'oddshot'=>array('Oddshot','http://oddshot.tv/',array('gaming'=>1),'!(?=.*?[./]oddshot\\.tv[:/]).*?oddshot.tv/shot/(?P<id>[-\\w]+)!',array('!(?=.*?[./]oddshot\\.tv[:/]).*?oddshot.tv/shot/(?P<id>[-\\w]+)!'),7=>'<div data-s9e-mediaembed="oddshot" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//oddshot.tv/shot/{$id}/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'pastebin'=>array('Pastebin','http://pastebin.com/',array('misc'=>1),'@pastebin\\.com/(?!u/)(?:\\w+\\.php\\?i=)?(?P<id>\\w+)@',array('@pastebin\\.com/(?!u/)(?:\\w+\\.php\\?i=)?(?P<id>\\w+)@'),7=>'<iframe data-s9e-mediaembed="pastebin" allowfullscreen="" scrolling="" src="//pastebin.com/embed_iframe.php?i={$id}" style="border:0;height:300px;resize:vertical;width:100%"></iframe>'),
		'playstv'=>array('Plays.tv','http://plays.tv/',array('gaming'=>1),'!(?=.*?[./]plays\\.tv[:/]).*?plays.tv/video/(?P<id>\\w+)!',array('!(?=.*?[./]plays\\.tv[:/]).*?plays.tv/video/(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="playstv" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//plays.tv/embeds/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'podbean'=>array('Podbean','http://www.podbean.com/',array('podcasts'=>1),"!podbean\\.com/site/player/index/pid/\\d+/eid/(?P<id>\\d+)!\n!(?P<id>)podbean\\.com/e/!",array('!podbean\\.com/site/player/index/pid/\\d+/eid/(?P<id>\\d+)!'),true,array(array('extract'=>array('!embed/postId/(?P<id>\\d+)!'),'match'=>array('!podbean\\.com/e/!'))),'<iframe data-s9e-mediaembed="podbean" allowfullscreen="" scrolling="no" src="//www.podbean.com/media/player/audio/postId/{$id}" style="border:0;height:100px;max-width:900px;width:100%"></iframe>'),
		'prezi'=>array('Prezi','http://prezi.com/',array('presentations'=>1),'#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?P<id>\\w+)/#',array('#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?P<id>\\w+)/#'),7=>'<div data-s9e-mediaembed="prezi" style="display:inline-block;width:100%;max-width:550px"><div style="overflow:hidden;position:relative;padding-bottom:72.727272727273%"><iframe allowfullscreen="" scrolling="no" src="//prezi.com/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'reddit'=>array('Reddit comment permalink','http://www.reddit.com/',array('social'=>1),'!(?P<id>)(?=.*?[./]reddit\\.com[:/]).*?(?P<path>/r/\\w+/comments/\\w+/\\w+/\\w+)!',array('!(?=.*?[./]reddit\\.com[:/]).*?(?P<path>/r/\\w+/comments/\\w+/\\w+/\\w+)!'),true),
		'rutube'=>array('Rutube','http://rutube.ru/',array('.ru'=>1),"!rutube\\.ru/tracks/(?P<id>\\d+)!\n!(?P<id>)rutube\\.ru/video/[0-9a-f]{32}!",array('!rutube\\.ru/tracks/(?P<id>\\d+)!'),true,array(array('extract'=>array('!rutube\\.ru/play/embed/(?P<id>\\d+)!'),'match'=>array('!rutube\\.ru/video/[0-9a-f]{32}!'))),'<div data-s9e-mediaembed="rutube" style="display:inline-block;width:100%;max-width:720px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//rutube.ru/play/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'scribd'=>array('Scribd','http://www.scribd.com/',array('documents'=>1,'presentations'=>1),'!scribd\\.com/doc/(?P<id>\\d+)!',array('!scribd\\.com/doc/(?P<id>\\d+)!'),7=>'<iframe data-s9e-mediaembed="scribd" allowfullscreen="" scrolling="no" src="//www.scribd.com/embeds/{$id}/content?view_mode=scroll&amp;show_recommendations=false" style="border:0;height:500px;resize:vertical;width:100%"></iframe>'),
		'slideshare'=>array('SlideShare','http://www.slideshare.net/',array('presentations'=>1),"!slideshare\\.net/[^/]+/[-\\w]+-(?P<id>\\d{6,})$!\n!(?P<id>)slideshare\\.net/[^/]+/\\w!",array('!slideshare\\.net/[^/]+/[-\\w]+-(?P<id>\\d{6,})$!'),true,array(array('extract'=>array('!"presentationId":(?P<id>\\d+)!'),'match'=>array('!slideshare\\.net/[^/]+/\\w!'))),'<div data-s9e-mediaembed="slideshare" style="display:inline-block;width:100%;max-width:427px"><div style="overflow:hidden;position:relative;padding-bottom:83.372365339578%"><iframe allowfullscreen="" scrolling="no" src="//www.slideshare.net/slideshow/embed_code/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'soundcloud'=>array('SoundCloud','https://soundcloud.com/',array('music'=>1),"@(?P<id>https?://(?:api\\.)?soundcloud\\.com/(?!pages/)[-/\\w]+/[-/\\w]+|^[^/]+/[^/]+$)@\n@(?P<id>)(?=.*?[./]soundcloud\\.com[:/]).*?api.soundcloud.com/playlists/(?P<playlist_id>\\d+)@\n@(?P<id>)(?=.*?[./]soundcloud\\.com[:/]).*?api.soundcloud.com/tracks/(?P<track_id>\\d+)(?:\\?secret_token=(?P<secret_token>[-\\w]+))?@\n@(?P<id>)soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/(?=s-)(?P<secret_token>[-\\w]+)@\n@(?P<id>)soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/s-@",array('@(?P<id>https?://(?:api\\.)?soundcloud\\.com/(?!pages/)[-/\\w]+/[-/\\w]+|^[^/]+/[^/]+$)@','@(?=.*?[./]soundcloud\\.com[:/]).*?api.soundcloud.com/playlists/(?P<playlist_id>\\d+)@','@(?=.*?[./]soundcloud\\.com[:/]).*?api.soundcloud.com/tracks/(?P<track_id>\\d+)(?:\\?secret_token=(?P<secret_token>[-\\w]+))?@','@soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/(?=s-)(?P<secret_token>[-\\w]+)@'),true,array(array('extract'=>array('@soundcloud:tracks:(?P<track_id>\\d+)@'),'match'=>array('@soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/s-@')))),
		'sportsnet'=>array('Sportsnet','http://www.sportsnet.ca/',array('.ca'=>1,'sports'=>1),'((?P<id>)sportsnet\\.ca/)',array(),true,array(array('extract'=>array('/vid(?:eoId)?=(?P<id>\\d+)/','/param name="@videoPlayer" value="(?P<id>\\d+)"/'),'match'=>array('//'))),'<div data-s9e-mediaembed="sportsnet" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://images.rogersdigitalmedia.com/video_service.php?videoId={$id}&amp;playerKey=AQ~~,AAAAAGWRwLc~,cRCmKE8Utf7OFWP38XQcokFZ80fR-u_y&amp;autoStart=false&amp;width=100%25&amp;height=100%25" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'spotify'=>array('Spotify','https://www.spotify.com/',array('music'=>1),"!(?P<id>)(?P<uri>spotify:(?:album|artist|user|track(?:set)?):[,:\\w]+)!\n!(?P<id>)(?:open|play)\\.spotify\\.com/(?P<path>(?:album|artist|track|user)/[/\\w]+)!",array('!(?P<uri>spotify:(?:album|artist|user|track(?:set)?):[,:\\w]+)!','!(?:open|play)\\.spotify\\.com/(?P<path>(?:album|artist|track|user)/[/\\w]+)!'),true),
		'stitcher'=>array('Stitcher','http://www.stitcher.com/',array('podcasts'=>1),'!(?P<id>)(?=.*?[./]stitcher\\.com[:/]).*?/podcast/!',array(),true,array(array('extract'=>array('!data-eid="(?P<eid>\\d+)!','!data-fid="(?P<fid>\\d+)!'),'match'=>array('!/podcast/!')))),
		'strawpoll'=>array('Straw Poll','http://strawpoll.me/',array('misc'=>1),'!strawpoll\\.me/(?P<id>\\d+)!',array('!strawpoll\\.me/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="strawpoll" style="display:inline-block;width:100%;max-width:600px"><div style="overflow:hidden;position:relative;padding-bottom:51.666666666667%"><iframe allowfullscreen="" scrolling="" src="//strawpoll.me/embed_1/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'streamable'=>array('Streamable','http://streamable.com/',array('videos'=>1),'!streamable\\.com/(?P<id>\\w+)!',array('!streamable\\.com/(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="streamable" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//streamable.com/e/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'teamcoco'=>array('Team Coco','http://teamcoco.com/',array('entertainment'=>1),"!teamcoco\\.com/video/(?P<id>\\d+)!\n!(?P<id>)teamcoco\\.com/video/.!",array('!teamcoco\\.com/video/(?P<id>\\d+)!'),true,array(array('extract'=>array('!"id":(?P<id>\\d+)!'),'match'=>array('!teamcoco\\.com/video/.!'))),'<div data-s9e-mediaembed="teamcoco" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:64.84375%"><iframe allowfullscreen="" scrolling="no" src="//teamcoco.com/embed/v/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'ted'=>array('TED Talks','http://www.ted.com/',array('presentations'=>1),'!ted\\.com/(?P<id>(?:talk|playlist)s/[^\\s"?]+)!i',array('!ted\\.com/(?P<id>(?:talk|playlist)s/[^\\s"?]+)!i')),
		'theatlantic'=>array('The Atlantic Video','http://www.theatlantic.com/video/',array('news'=>1),'!theatlantic\\.com/video/index/(?P<id>\\d+)!',array('!theatlantic\\.com/video/index/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="theatlantic" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://www.theatlantic.com/video/iframe/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'theonion'=>array('The Onion','http://www.theonion.com/video/',array('entertainment'=>1),'!theonion\\.com/video/[-\\w]+[-,](?P<id>\\d+)!',array('!theonion\\.com/video/[-\\w]+[-,](?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="theonion" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://www.theonion.com/video_embed/?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'tinypic'=>array('TinyPic videos','http://tinypic.com/',array('images'=>1),'!tinypic\\.com/player\\.php\\?v=(?P<id>\\w+)&s=(?P<s>\\d+)!',array('!tinypic\\.com/player\\.php\\?v=(?P<id>\\w+)&s=(?P<s>\\d+)!'),true),
		'tmz'=>array('TMZ','http://www.tmz.com/videos',array('gossip'=>1),'@tmz\\.com/videos/(?P<id>\\w+)@',array('@tmz\\.com/videos/(?P<id>\\w+)@'),7=>'<div data-s9e-mediaembed="tmz" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.kaltura.com/index.php/kwidget/cache_st/133592691/wid/_591531/partner_id/591531/uiconf_id/9071262/entry_id/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'traileraddict'=>array('Trailer Addict','http://www.traileraddict.com/',array('movies'=>1),'@(?P<id>)traileraddict\\.com/(?!tags/)[^/]+/.@',array(),true,array(array('extract'=>array('@v\\.traileraddict\\.com/(?P<id>\\d+)@'),'match'=>array('@traileraddict\\.com/(?!tags/)[^/]+/.@'))),'<div data-s9e-mediaembed="traileraddict" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//v.traileraddict.com/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'tumblr'=>array('Tumblr','https://www.tumblr.com/',array('social'=>1),"!(?P<name>[-\\w]+)\\.tumblr\\.com/post/(?P<id>\\d+)!\n!(?P<id>)\\w\\.tumblr\\.com/post/\\d!",array('!(?P<name>[-\\w]+)\\.tumblr\\.com/post/(?P<id>\\d+)!'),true,array(array('extract'=>array('!did=\\\\u0022(?P<did>[-\\w]+)!','!embed\\\\/post\\\\/(?P<key>[-\\w]+)!'),'match'=>array('!\\w\\.tumblr\\.com/post/\\d!'),'url'=>'http://www.tumblr.com/oembed/1.0?url=http://{@name}.tumblr.com/post/{@id}'))),
		'twitch'=>array('Twitch','http://www.twitch.tv/',array('gaming'=>1),"#(?P<id>)twitch\\.tv/(?P<channel>\\w+)(?:/b/(?P<archive_id>\\d+)|/c/(?P<chapter_id>\\d+)|/v/(?P<video_id>\\d+))?#\n#(?P<id>)(?=.*?[./]twitch\\.tv[:/]).*?t=(?P<t>(?:(?:\\d+h)?\\d+m)?\\d+s)#",array('#twitch\\.tv/(?P<channel>\\w+)(?:/b/(?P<archive_id>\\d+)|/c/(?P<chapter_id>\\d+)|/v/(?P<video_id>\\d+))?#','#(?=.*?[./]twitch\\.tv[:/]).*?t=(?P<t>(?:(?:\\d+h)?\\d+m)?\\d+s)#'),true),
		'twitter'=>array('Twitter','https://twitter.com/',array('social'=>1),'@twitter\\.com/(?:#!/)?\\w+/status(?:es)?/(?P<id>\\d+)@',array('@twitter\\.com/(?:#!/)?\\w+/status(?:es)?/(?P<id>\\d+)@'),7=>'<iframe data-s9e-mediaembed="twitter" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/twitter.min.html#{$id}" style="border:0;height:186px;max-width:500px;width:100%"></iframe>'),
		'ustream'=>array('Ustream','http://www.ustream.tv/',array('gaming'=>1),"!(?P<id>)ustream\\.tv/recorded/(?P<vid>\\d+)!\n#(?P<id>)ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#",array('!ustream\\.tv/recorded/(?P<vid>\\d+)!'),true,array(array('extract'=>array('!embed/(?P<cid>\\d+)!'),'match'=>array('#ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#')))),
		'vbox7'=>array('VBOX7','http://vbox7.com/',array('.bg'=>1),'!vbox7\\.com/play:(?P<id>[\\da-f]+)!',array('!vbox7\\.com/play:(?P<id>[\\da-f]+)!'),7=>'<div data-s9e-mediaembed="vbox7" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://vbox7.com/emb/external.php?vid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'vevo'=>array('VEVO','http://vevo.com/',array('music'=>1),'!vevo\\.com/watch/([-/\\w]+/)?(?P<id>[A-Z0-9]+)!',array('!vevo\\.com/watch/([-/\\w]+/)?(?P<id>[A-Z0-9]+)!'),7=>'<div data-s9e-mediaembed="vevo" style="display:inline-block;width:100%;max-width:575px"><div style="overflow:hidden;position:relative;padding-bottom:56.347826086957%"><iframe allowfullscreen="" scrolling="no" src="http://cache.vevo.com/m/html/embed.html?video={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'viagame'=>array('Viagame','http://www.viagame.com/',array('gaming'=>1),'!viagame\\.com/channels/[^/]+/(?P<id>\\d+)!',array('!viagame\\.com/channels/[^/]+/(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="viagame" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:61.25%"><iframe allowfullscreen="" scrolling="no" src="//www.viagame.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'videodetective'=>array('Video Detective','http://www.videodetective.com/',array('misc'=>1),'!videodetective\\.com/\\w+/[-\\w]+/(?:trailer/P0*)?(?P<id>\\d+)!',array('!videodetective\\.com/\\w+/[-\\w]+/(?:trailer/P0*)?(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="videodetective" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.videodetective.com/embed/video/?options=false&amp;autostart=false&amp;playlist=none&amp;publishedid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'videomega'=>array('Videomega','http://videomega.tv/',array('videos'=>1),'!videomega\\.tv/\\?ref=(?P<id>\\w+)!',array('!videomega\\.tv/\\?ref=(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="videomega" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://videomega.tv/iframe.php?ref={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'vidme'=>array('vidme','https://vid.me',array('videos'=>1),'!vid\\.me/(?P<id>\\w+)!',array('!vid\\.me/(?P<id>\\w+)!'),true,array(array('extract'=>array('!meta property="og:video:type" content="video/\\w+">\\s*<meta property="og:video:height" content="(?P<height>\\d+)">\\s*<meta property="og:video:width" content="(?P<width>\\d+)!'),'match'=>array('//')))),
		'vimeo'=>array('Vimeo','http://vimeo.com/',array('videos'=>1),'!vimeo\\.com/(?:channels/[^/]+/|video/)?(?P<id>\\d+)!',array('!vimeo\\.com/(?:channels/[^/]+/|video/)?(?P<id>\\d+)!'),7=>'<div data-s9e-mediaembed="vimeo" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.vimeo.com/video/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'vine'=>array('Vine','https://vine.co/',array('social'=>1,'videos'=>1),'!vine\\.co/v/(?P<id>[^/]+)!',array('!vine\\.co/v/(?P<id>[^/]+)!'),7=>'<div data-s9e-mediaembed="vine" style="display:inline-block;width:100%;max-width:480px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="https://vine.co/v/{$id}/embed/simple?audio=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'vk'=>array('VK','https://vk.com/',array('.ru'=>1),"!(?P<id>)vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?P<oid>-?\\d+)_(?P<vid>\\d+)!\n!(?P<id>)vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?P<oid>-?\\d+)&id=(?P<vid>\\d+)&hash=(?P<hash>[0-9a-f]+)!\n!(?P<id>)(?=.*?[./]vk(?>\\.com|ontakte\\.ru)[:/]).*?vk.*?video-?\\d+_\\d+!",array('!vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?P<oid>-?\\d+)_(?P<vid>\\d+)!','!vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?P<oid>-?\\d+)&id=(?P<vid>\\d+)&hash=(?P<hash>[0-9a-f]+)!'),true,array(array('extract'=>array('!\\\\"hash2\\\\":\\\\"(?P<hash>[0-9a-f]+)!'),'match'=>array('!vk.*?video-?\\d+_\\d+!'),'url'=>'http://vk.com/video{@oid}_{@vid}'))),
		'vocaroo'=>array('Vocaroo','http://vocaroo.com/',array('misc'=>1),'!vocaroo\\.com/i/(?P<id>\\w+)!',array('!vocaroo\\.com/i/(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="vocaroo" style="display:inline-block;width:100%;max-width:148px"><div style="overflow:hidden;position:relative;padding-bottom:29.72972972973%"><object data="//vocaroo.com/player.swf?playMediaID={$id}&amp;autoplay=0" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"/></object></div></div>'),
		'vox'=>array('Vox','http://www.vox.com/',array('misc'=>1),'!(?=.*?[./]vox\\.com[:/]).*?vox.com/.*#ooid=(?P<id>[-\\w]+)!',array('!(?=.*?[./]vox\\.com[:/]).*?vox.com/.*#ooid=(?P<id>[-\\w]+)!'),7=>'<div data-s9e-mediaembed="vox" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.ooyala.com/iframe.html#pbid=a637d53c5c0a43c7bf4e342886b9d8b0&amp;ec={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'wshh'=>array('WorldStarHipHop','http://www.worldstarhiphop.com/',array('videos'=>1),"!worldstarhiphop\\.com/featured/(?P<id>\\d+)!\n!(?P<id>)worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!",array('!worldstarhiphop\\.com/featured/(?P<id>\\d+)!'),true,array(array('extract'=>array('!disqus_identifier[ =\']+(?P<id>\\d+)!'),'match'=>array('!worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!'))),'<div data-s9e-mediaembed="wshh" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.worldstarhiphop.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'wsj'=>array('The Wall Street Journal Online','http://www.wsj.com/video/',array('news'=>1),"@wsj\\.com/[^#]*#!(?P<id>[-0-9A-F]{36})@\n@wsj\\.com/video/[^/]+/(?P<id>[-0-9A-F]{36})@\n@(?P<id>)on\\.wsj\\.com/\\w@",array('@wsj\\.com/[^#]*#!(?P<id>[-0-9A-F]{36})@','@wsj\\.com/video/[^/]+/(?P<id>[-0-9A-F]{36})@'),true,array(array('extract'=>array('@guid=(?P<id>[-0-9A-F]{36})@'),'match'=>array('@on\\.wsj\\.com/\\w@'))),'<div data-s9e-mediaembed="wsj" style="display:inline-block;width:100%;max-width:512px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video-api.wsj.com/api-video/player/iframe.html?guid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'xboxclips'=>array('XboxClips','http://xboxclips.com/',array('gaming'=>1),'!xboxclips\\.com/(?P<user>[^/]+)/(?P<id>[-0-9a-f]+)!',array('!xboxclips\\.com/(?P<user>[^/]+)/(?P<id>[-0-9a-f]+)!'),true),
		'xboxdvr'=>array('Xbox DVR','http://xboxdvr.com/',array('gaming'=>1),'!xboxdvr\\.com/gamer/(?P<user>[^/]+)/video/(?P<id>\\d+)!',array('!xboxdvr\\.com/gamer/(?P<user>[^/]+)/video/(?P<id>\\d+)!'),true),
		'yahooscreen'=>array('Yahoo! Screen','https://screen.yahoo.com/',array('movies'=>1),'!screen\\.yahoo\\.com/(?:[-\\w]+/)?(?P<id>[-\\w]+)\\.html!',array('!screen\\.yahoo\\.com/(?:[-\\w]+/)?(?P<id>[-\\w]+)\\.html!'),7=>'<div data-s9e-mediaembed="yahooscreen" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://screen.yahoo.com/{$id}.html?format=embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'youku'=>array('Youku','http://www.youku.com/',array('.cn'=>1),'!youku\\.com/v_show/id_(?P<id>\\w+)!',array('!youku\\.com/v_show/id_(?P<id>\\w+)!'),7=>'<div data-s9e-mediaembed="youku" style="display:inline-block;width:100%;max-width:512px"><div style="overflow:hidden;position:relative;padding-bottom:64.0625%"><iframe allowfullscreen="" scrolling="no" src="http://player.youku.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>'),
		'youtube'=>array('YouTube','http://www.youtube.com/',array('videos'=>1),"!youtube\\.com/(?:watch.*?v=|v/)(?P<id>[-\\w]+)!\n!youtu\\.be/(?P<id>[-\\w]+)!\n!(?P<id>)(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?[#&?]t=(?:(?:(?P<h>\\d+)h)?(?P<m>\\d+)m(?P<s>\\d+)|(?P<t>\\d+))!\n!(?P<id>)(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?&list=(?P<list>[-\\w]+)!",array('!youtube\\.com/(?:watch.*?v=|v/)(?P<id>[-\\w]+)!','!youtu\\.be/(?P<id>[-\\w]+)!','!(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?[#&?]t=(?:(?:(?P<h>\\d+)h)?(?P<m>\\d+)m(?P<s>\\d+)|(?P<t>\\d+))!','!(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?&list=(?P<list>[-\\w]+)!'),true),
		'zippyshare'=>array('Zippyshare audio files','http://www.zippyshare.com/',array('file sharing'=>1),'!(?P<id>)(?=.*?[./]zippyshare\\.com[:/]).*?/v/!',array(),true,array(array('extract'=>array('!file=(?P<file>\\w+)&amp;server=(?P<server>\\d+)!'),'match'=>array('!/v/!'))))
	);

	/**
	* @var array Associative array using enabled tags as keys
	*/
	public static $tags;

	/**
	* Installer
	*
	* @param  array|bool       $old   Either the add-on's old config on update, or FALSE on install
	* @param  array            $new   The add-on's new config
	* @param  SimpleXMLElement $addon The add-on's XML document
	* @return void
	*/
	public static function install($old, array $new, SimpleXMLElement $addon)
	{
		self::loadSettings();
		if (isset($old['version_id']))
		{
			self::upgrade($old['version_id']);
		}
		foreach (self::getFilteredSites() as $siteId => $site)
		{
			self::addSite($addon->bb_code_media_sites, $siteId, $site);
		}

		// Sniff s9e_Custom callbacks for backward compatibility
		if (class_exists('s9e_Custom'))
		{
			$value = '';
			foreach ($addon->bb_code_media_sites->site as $site)
			{
				$siteId   = (string) $site['media_site_id'];
				$callback = 's9e_Custom::' . $siteId;
				if (!isset(self::$customCallbacks[$siteId]) && is_callable($callback))
				{
					$value .= $siteId . '=' . $callback . "\n";
				}
			}

			if ($value)
			{
				$options = $addon->xpath('//option[@option_id="s9e_custom_callbacks"]');
				$options[0]->default_value = $value;
			}
		}

		// Overwrite the default value of s9e_excluded_sites to maintain backward compatibility with
		// the old s9e_EXCLUDE_SITES
		$options = XenForo_Application::get('options');
		if (empty($options->s9e_excluded_sites) && !empty(self::$excludedSites))
		{
			$options = $addon->xpath('//option[@option_id="s9e_excluded_sites"]');
			$options[0]->default_value = self::$excludedSites;
		}
	}

	/**
	* Upgrade this add-on's options
	*
	* @param  integer $versionId Old version ID
	* @return void
	*/
	protected static function upgrade($versionId)
	{
		self::upgradeTags($versionId);
	}

	/**
	* Upgrade the list of enabled tags
	*
	* Automatically enables new tags
	*
	* @param  integer $versionId Old version ID
	* @return void
	*/
	protected static function upgradeTags($versionId)
	{
		if (!isset(XenForo_Application::get('options')->s9e_media_tags))
		{
			return;
		}
		$save = false;
		$tags = array(
			'videos' => 201502120
		);
		foreach ($tags as $tag => $tagVersionId)
		{
			if ($versionId < $tagVersionId)
			{
				self::$tags[$tag] = 1;
				$save = true;
			}
		}
		if ($save)
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
			$dw->setExistingData('s9e_media_tags');
			$dw->set('option_value', self::$tags);
			$dw->save();
		}
	}

	/**
	* Uninstaller
	*
	* Will unconditionally reinstall the default media sites
	*
	* @return void
	*/
	public static function uninstall()
	{
		try
		{
			$defaultData = XenForo_Install_Data_MySql::getData();
			$query = preg_replace('(^(\\s*)INSERT)', '$1REPLACE', $defaultData['xf_bb_code_media_site']);
			XenForo_Application::getDb()->query($query);
		}
		catch (Exception $e)
		{
			// Nothing to do here
		}
	}

	/**
	* Return an array of all the tags (used as keys)
	*
	* @return array
	*/
	protected static function getAllTags()
	{
		$tags = array();
		foreach (self::$sites as $site)
		{
			$tags += $site[self::KEY_TAGS];
		}
		ksort($tags);

		return $tags;
	}

	/**
	* Load the settings from XenForo's options
	*
	* @return void
	*/
	protected static function loadSettings()
	{
		$options = XenForo_Application::get('options');
		if (!isset(self::$customCallbacks))
		{
			self::parseCustomCallbacks($options->s9e_custom_callbacks);
		}
		if (!isset(self::$customDimensions))
		{
			self::parseCustomDimensions($options->s9e_custom_dimensions);
		}
		if (!isset(self::$excludedSites))
		{
			self::$excludedSites = $options->s9e_excluded_sites ?: $options->s9e_EXCLUDE_SITES ?: '';
		}
		if (!isset(self::$maxResponsiveWidth))
		{
			self::$maxResponsiveWidth = (int) $options->s9e_max_responsive_width;
		}
		if (!isset(self::$tags))
		{
			self::$tags = $options->s9e_media_tags ?: self::getAllTags();
		}
	}

	/**
	* Add a site definition to the add-on's XML
	*
	* @param  SimpleXMLElement $parent Parent node
	* @param  string           $siteId Site's ID
	* @param  array            $config Site's config
	* @return void
	*/
	protected static function addSite(SimpleXMLElement $parent, $siteId, array $config)
	{
		$site = $parent->addChild('site');

		$site['media_site_id']  = $siteId;
		$site['site_title']     = $config[self::KEY_TITLE];
		$site['site_url']       = $config[self::KEY_URL];
		$site['match_is_regex'] = 1;
		$site['supported']      = 1;

		if (!empty($config[self::KEY_USE_MATCH_CALLBACK]))
		{
			$site['match_callback_class']  = __CLASS__;
			$site['match_callback_method'] = 'match';
		}

		if (isset($config[self::KEY_HTML]))
		{
			$html = $config[self::KEY_HTML];
			if (isset(self::$customDimensions[$siteId]))
			{
				$html = self::setCustomDimensions($html, self::$customDimensions[$siteId]);
			}
			if (self::$maxResponsiveWidth && empty($config[self::KEY_UNRESPONSIVE]))
			{
				$html = self::addResponsiveWrapper($html);
			}
		}
		else
		{
			$html = '<!-- see callback -->';
			$site['embed_html_callback_class']  = __CLASS__;
			$site['embed_html_callback_method'] = 'embed';
		}

		$site->addChild('embed_html', htmlspecialchars($html));
		$site->addChild('match_urls', htmlspecialchars($config[self::KEY_MATCH_URLS]));
	}

	/**
	* Parse a text to capture the list of custom callbacks
	*
	* @param  string $text List of callbacks as text, one per line as "site=callback"
	* @return void
	*/
	protected static function parseCustomCallbacks($text)
	{
		$regexp = '(^\\s*(\\w+)\\s*=\\s*(\\w+(?:\\s*::\\s*\\w+)?)\\s*$)m';
		preg_match_all($regexp, $text, $matches, PREG_SET_ORDER);

		self::$customCallbacks = array();
		foreach ($matches as $m)
		{
			$siteId   = strtolower($m[1]);
			$callback = preg_replace('(\\s+)', '', $m[2]);

			self::$customCallbacks[$siteId] = $callback;
		}
		ksort(self::$customCallbacks);
	}

	/**
	* Parse a text to capture the list of custom dimensions
	*
	* @param  string $text List of dimensions as text, one per line as "site=width,height"
	* @return void
	*/
	protected static function parseCustomDimensions($text)
	{
		preg_match_all('((\\w+)\\s*=\\s*(\\d+)\\s*,\\s*(\\d+))', $text, $matches, PREG_SET_ORDER);

		self::$customDimensions = array();
		foreach ($matches as $match)
		{
			list(, $siteId, $width, $height) = $match;
			foreach (self::parseSiteIds($siteId) as $siteId)
			{
				self::$customDimensions[$siteId] = array((int) $width, (int) $height);
			}
		}
		ksort(self::$customDimensions);
	}

	/**
	* Parse a text to capture the list of excluded sites
	*
	* @param  string $text Comma-separated list of excluded sites (ID or name)
	* @return void
	*/
	protected static function parseExcludedSites($text)
	{
		$siteIds = self::parseSiteIds($text);
		$text = implode(',', $siteIds);
		self::$excludedSites = $text;
	}

	/**
	* Parsed a list of sites into an array of siteId
	*
	* @param  string   Comma-separated list of sites
	* @return string[]
	*/
	protected static function parseSiteIds($text)
	{
		$siteIds = array();
		foreach (preg_split('(\\s*,\\s*)', strtolower(trim($text))) as $name)
		{
			if (isset(self::$sites[$name]))
			{
				$siteIds[] = $name;
			}
			else
			{
				foreach (self::$sites as $siteId => $site)
				{
					if (strtolower($site[self::KEY_TITLE]) === $name)
					{
						$siteIds[] = $siteId;
					}
				}
			}
		}
		sort($siteIds);

		return $siteIds;
	}

	/**
	* Validate a list of custom callbacks and trigger the reinstallation
	*
	* @param  string &$text List of callbacks as text, one per line as "site=callback"
	* @return bool          Always TRUE
	*/
	public static function validateCustomCallbacks(&$text)
	{
		self::parseCustomCallbacks($text);

		// Rebuild the list to remove malformed entries
		$text = '';
		foreach (self::$customCallbacks as $siteId => $callback)
		{
			$text .= $siteId . '=' . $callback . "\n";
		}

		self::reinstall();

		return true;
	}

	/**
	* Validate a list of custom dimensions and trigger the reinstallation
	*
	* @param  string &$text List of dimensions as text, one per line as "site=width,height"
	* @return bool          Always TRUE
	*/
	public static function validateCustomDimensions(&$text)
	{
		self::parseCustomDimensions($text);

		// Rebuild the list to remove malformed entries
		$text = '';
		foreach (self::$customDimensions as $siteId => $dimensions)
		{
			list($width, $height) = $dimensions;
			$text .= $siteId . '=' . $width . ',' . $height . "\n";
		}

		self::reinstall();

		return true;
	}

	/**
	* Validate a comma-separated list of sites not to install
	*
	* @param  string &$text Comma-separated IDs, e.g. "foo,bar"
	* @return bool          Always TRUE
	*/
	public static function validateExcludedSites(&$text)
	{
		self::parseExcludedSites($text);
		$text = self::$excludedSites;
		self::reinstall();

		return true;
	}

	/**
	* Callback used to toggle the link in the footer
	*
	* @param  string $value Either "show" or "hide"
	* @return string        Original value, returned as-is
	*/
	public static function validateFooter($value)
	{
		$model = XenForo_Model::create('XenForo_Model_TemplateModification');
		$modification = $model->getModificationByKey('s9e_footer');

		if ($modification)
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_TemplateModification');
			$dw->setExistingData($modification);
			$dw->set('enabled', (int) ($value === 'show'));
			$dw->save();
		}

		return $value;
	}

	/**
	* Callback used to toggle lazy loading
	*
	* @param  string $value Either "immediate" or "lazy"
	* @return string        Original value, returned as-is
	*/
	public static function validateLazyLoading($value)
	{
		$model = XenForo_Model::create('XenForo_Model_TemplateModification');
		$modification = $model->getModificationByKey('s9e_lazy_loading');

		if ($modification)
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_TemplateModification');
			$dw->setExistingData($modification);
			$dw->set('enabled', (int) ($value === 'lazy'));
			$dw->save();
		}

		return $value;
	}

	/**
	* Trigger the reinstallation when the max responsive width is toggled
	*
	* @param  string &$text Max responsive width, as text
	* @return bool          Always TRUE
	*/
	public static function validateMaxResponsiveWidth(&$text)
	{
		self::$maxResponsiveWidth = (int) $text;
		self::reinstall();

		return true;
	}

	/**
	* Reinstall media sites based on given list of tags
	*
	* @param  array $tags Associative array using site IDs as keys
	* @return bool        Always TRUE
	*/
	public static function updateTags(array $tags)
	{
		self::$tags = $tags;
		self::reinstall();

		return true;
	}

	/**
	* Add the responsive wrapper around given HTML
	*
	* @param  string  $html Original code
	* @return string        Modified code
	*/
	protected static function addResponsiveWrapper($html)
	{
		$ratio = self::getEmbedRatio($html);
		if (!$ratio)
		{
			return $html;
		}

		$css  = 'position:absolute;top:0;left:0;width:100%;height:100%';
		$html = preg_replace('( style="[^"]*)', '$0;' . $css, $html, -1, $cnt);
		if (!$cnt)
		{
			$html = preg_replace('(>)', ' style="' . $css . '">', $html, 1);
		}

		return '<div data-s9e="wrapper" style="display:inline-block;width:100%;max-width:' . self::$maxResponsiveWidth . 'px;overflow:hidden"><div style="position:relative;padding-top:' . round(100 * $ratio, 2) . '%">' . $html . '</div></div>';
	}

	/**
	* Return all of the sites, filtered to fit the user's preferences
	*
	* @return array Site IDs as keys, config arrays as values
	*/
	protected static function getFilteredSites()
	{
		self::loadSettings();

		$sites = array();
		foreach (self::$sites as $siteId => $site)
		{
			if (!array_intersect_key($site[self::KEY_TAGS], self::$tags))
			{
				continue;
			}

			$sites[$siteId] = $site;
		}

		if (self::$excludedSites)
		{
			$excludedSites = array_flip(preg_split('/\\W+/', self::$excludedSites, -1, PREG_SPLIT_NO_EMPTY));
			$sites = array_diff_key($sites, $excludedSites);
		}

		return $sites;
	}

	/**
	* Reinstall all enabled sites
	*
	* @return void
	*/
	protected static function reinstall()
	{
		self::loadSettings();
		$sites = simplexml_load_string('<sites/>');
		foreach (self::getFilteredSites() as $siteId => $site)
		{
			self::addSite($sites, $siteId, $site);
		}

		$model = XenForo_Model::create('XenForo_Model_BbCode');
		$model->importBbCodeMediaSitesAddOnXml($sites, 's9e');
		$model->rebuildBbCodeCache();
	}

	/**
	* Match given URL and return an ID or all extracted vars serialized
	*
	* Vars are URLencoded and separated with semi-colons
	*
	* @param  string $url       Original URL
	* @param  string $matchedId Unused
	* @param  array  $site      Unused
	* @param  string $siteId    Site's ID
	* @return string
	*/
	public static function match($url, $matchedId, array $site, $siteId = null)
	{
		if (!isset(self::$sites[$siteId]))
		{
			return false;
		}

		$config = self::$sites[$siteId];
		$vars = array();

		if (!empty($config[self::KEY_EXTRACT_REGEXPS]))
		{
			$vars = self::getNamedCaptures($url, $config[self::KEY_EXTRACT_REGEXPS]);
		}

		if (!empty($config[self::KEY_SCRAPES]))
		{
			foreach ($config[self::KEY_SCRAPES] as $scrape)
			{
				// Overwrite vars extracted from URL with vars extracted from content
				$vars = self::scrape($url, $scrape, $vars) + $vars;
			}
		}

		// No vars = no match
		if (empty($vars))
		{
			return false;
		}

		// Apply filters
		self::applyFilters($siteId, $vars);

		// If there's only one capture named "id" we store its value as-is
		$keys = array_keys($vars);
		if ($keys === array('id'))
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

	/**
	* Generate the HTML code for a site
	*
	* @param  string $mediaKey XenForo's "media key" (same format as match())
	* @param  array  $site     Site's config. We're only interested in the embed_html element
	* @param  string $siteId   Site's ID
	* @return string           Embed code
	*/
	public static function embed($mediaKey, array $site, $siteId = null)
	{
		if (!isset($siteId))
		{
			return '<div style="background:#d00;color:#fff;font-weight:bold;text-align:center">Cannot render media site: you may be running an outdated version of XenForo</div>';
		}

		self::loadSettings();
		$vars = array('id' => $mediaKey);

		// If the value looks like a URL, we copy its value to the "url" var
		if (preg_match('#^\\w+://#', $mediaKey))
		{
			$vars['url'] = $mediaKey;
		}

		// If the value looks like a series of key=value pairs, add them to $vars
		if (preg_match('(^(\\w+=[^;]*)(?>;(?1))*$)', $mediaKey))
		{
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}

		// Prepare the HTML
		$methodName = 'render' . ucfirst($siteId);
		if (method_exists(__CLASS__, $methodName))
		{
			$html = call_user_func(__CLASS__ . '::' . $methodName, $vars);
		}
		else
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

		if (isset(self::$customDimensions[$siteId]))
		{
			$html = self::setCustomDimensions($html, self::$customDimensions[$siteId]);
		}
		if (self::$maxResponsiveWidth && empty(self::$sites[$siteId][self::KEY_UNRESPONSIVE]))
		{
			$html = self::addResponsiveWrapper($html);
		}

		// Test for custom callbacks
		if (isset(self::$customCallbacks[$siteId]) && is_callable(self::$customCallbacks[$siteId]))
		{
			$html = call_user_func(self::$customCallbacks[$siteId], $html, $vars);
		}

		return $html;
	}

	/**
	* Retrieve content from given URL
	*
	* @param  string $url Target URL
	* @return string      Response body
	*/
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

		$page = (extension_loaded('curl') && !ini_get('open_basedir') && !ini_get('safe_mode')) ? self::wgetCurl($url) : self::wgetNative($url);
		if ($page && isset($cacheFile))
		{
			file_put_contents($cacheFile, gzencode($page, 9));
		}

		return $page;
	}

	/**
	* Retrieve content from given URL via cURL
	*
	* @param  string $url Target URL
	* @return string      Response body
	*/
	protected static function wgetCurl($url)
	{
		static $curl;
		if (!isset($curl))
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_ENCODING,       '');
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_USERAGENT,      'PHP (not Mozilla)');
		}
		curl_setopt($curl, CURLOPT_URL, $url);

		return curl_exec($curl);
	}

	/**
	* Retrieve content from given URL via native PHP stream
	*
	* @param  string $url Target URL
	* @return string      Response body
	*/
	protected static function wgetNative($url)
	{
		return @file_get_contents(
			'compress.zlib://' . $url,
			false,
			stream_context_create(array(
				'http' => array(
					'header'     => 'Accept-Encoding: gzip',
					'user_agent' => 'PHP (not Mozilla)'
				),
				'ssl'  => array('verify_peer' => false)
			))
		);

		return $page;
	}

	/**
	* Apply the var filters associated with given site
	*
	* @param  string  $siteId
	* @param  array  &$vars
	* @return void
	*/
	protected static function applyFilters($siteId, array &$vars)
	{
		if (!isset(self::$sites[$siteId][self::KEY_FILTERS]))
		{
			return;
		}

		foreach (self::$sites[$siteId][self::KEY_FILTERS] as $varName => $callbacks)
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
	}

	/**
	* Scrape vars off given URL
	*
	* @param  string   $url
	* @param  array    $scrape
	* @param  string[] $vars
	* @return array
	*/
	protected static function scrape($url, array $scrape, array $vars)
	{
		$scrapeVars = array();

		$match = false;
		foreach ($scrape['match'] as $regexp)
		{
			if (preg_match($regexp, $url, $m))
			{
				// Add the named captures to the available vars
				$scrapeVars += $m;

				$match = true;
			}
		}

		if (!$match)
		{
			return $scrapeVars;
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

		return self::getNamedCaptures(self::wget($scrapeUrl), $scrape['extract']);
	}

	/**
	* Capture substrings from a string using a set of regular expressions
	*
	* @param  string   $string
	* @param  string[] $regexps
	* @return array
	*/
	protected static function getNamedCaptures($string, array $regexps)
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

	/**
	* Compute the height:width ratio of given embed code
	*
	* @param  string      $html Original code
	* @return float|false Height:width ratio or FALSE
	*/
	protected static function getEmbedRatio($html)
	{
		// Don't try to compute the ratio if the height is set dynamically in an onload event
		if (preg_match('(onload="[^"]*height)', $html))
		{
			return false;
		}

		if (preg_match('(height="(\\d+)")', $html, $height)
		 && preg_match('(width="(\\d+)")', $html, $width))
		{
			return $height[1] / $width[1];
		}

		return false;
	}

	/**
	* Replace dimensions in given HTML
	*
	* @param  string    $html       Original HTML
	* @param  integer[] $dimensions [width, height]
	* @return string                Modified HTML
	*/
	protected static function setCustomDimensions($html, array $dimensions)
	{
		list($width, $height) = $dimensions;
		$match = $replace = array();

		$match[]   = '(width="\\d+")';
		$replace[] = 'width="' . $width . '"';

		$match[]   = '(height="\\d+")';
		$replace[] = 'height="' . $height . '"';

		$match[]   = '(width:\\s*\\d+\\s*px)';
		$replace[] = 'width:' . $width . 'px';

		$match[]   = '(height:\\s*\\d+\\s*px)';
		$replace[] = 'height:' . $height . 'px';

		return preg_replace($match, $replace, $html);
	}

	public static function renderAmazon($vars)
	{
		$vars += array('id' => null, 'tld' => null);

		$html='<div data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><div style="overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//rcm-';if($vars['tld']==='jp')$html.='fe';elseif(isset($vars['tld'])&&(strpos('desfrituk',$vars['tld'])!==false))$html.='eu';else$html.='na';$html.='.amazon-adsystem.com/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins='.htmlspecialchars($vars['id'],2).'&amp;o=';if($vars['tld']==='ca')$html.='15';elseif($vars['tld']==='de')$html.='3';elseif($vars['tld']==='es')$html.='30';elseif($vars['tld']==='fr')$html.='8';elseif($vars['tld']==='it')$html.='29';elseif($vars['tld']==='jp')$html.='9';elseif($vars['tld']==='uk')$html.='2';else$html.='1';$html.='&amp;t=';if($vars['tld']==='ca'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_CA))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_CA,2);elseif($vars['tld']==='de'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_DE))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_DE,2);elseif($vars['tld']==='es'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_ES))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_ES,2);elseif($vars['tld']==='fr'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_FR))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_FR,2);elseif($vars['tld']==='it'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_IT))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_IT,2);elseif($vars['tld']==='jp'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_JP))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_JP,2);elseif($vars['tld']==='uk'&&!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_UK))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_UK,2);elseif(!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG,2);else$html.='_';$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderAudiomack($vars)
	{
		$vars += array('id' => null, 'mode' => null);

		$html='';if($vars['mode']==='album')$html.='<iframe data-s9e-mediaembed="audiomack" allowfullscreen="" scrolling="no" src="//www.audiomack.com/embed4-album/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:340px;max-width:900px;width:100%"></iframe>';else$html.='<iframe data-s9e-mediaembed="audiomack" allowfullscreen="" scrolling="no" src="//www.audiomack.com/embed4/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:110px;max-width:900px;width:100%"></iframe>';

		return $html;
	}

	public static function renderBandcamp($vars)
	{
		$vars += array('album_id' => null, 'track_id' => null, 'track_num' => null);

		$html='<div data-s9e-mediaembed="bandcamp" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/';if(isset($vars['album_id'])){$html.='album='.htmlspecialchars($vars['album_id'],2);if(isset($vars['track_num']))$html.='/t='.htmlspecialchars($vars['track_num'],2);}else$html.='track='.htmlspecialchars($vars['track_id'],2);$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderBbcnews($vars)
	{
		$vars += array('ad_site' => null, 'playlist' => null, 'poster' => null);

		$html='<div data-s9e-mediaembed="bbcnews" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://ssl.bbc.co.uk/wwscripts/embed_player#playlist='.htmlspecialchars($vars['playlist'],2).'&amp;poster='.htmlspecialchars($vars['poster'],2).'&amp;ad_site='.htmlspecialchars($vars['ad_site'],2).'&amp;ad_keyword=&amp;source=twitter" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderBrightcove($vars)
	{
		$vars += array('bckey' => null, 'bcpid' => null, 'bctid' => null);

		$html='<div data-s9e-mediaembed="brightcove" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://link.brightcove.com/services/player/bcpid'.htmlspecialchars($vars['bcpid'],2).'?bckey='.htmlspecialchars($vars['bckey'],2).'&amp;bctid='.htmlspecialchars($vars['bctid'],2).'&amp;secureConnections=true&amp;secureHTMLConnections=true&amp;autoStart=false&amp;height=100%25&amp;width=100%25" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderCbsnews($vars)
	{
		$vars += array('id' => null, 'pid' => null);

		$html='<div data-s9e-mediaembed="cbsnews" style="display:inline-block;width:100%;max-width:640px">';if(isset($vars['pid']))$html.='<div style="overflow:hidden;position:relative;padding-bottom:62.1875%;padding-bottom:calc(56.25% + 38px)"><object data="//www.cbsnews.com/common/video/cbsnews_player.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2);else$html.='<div style="overflow:hidden;position:relative;padding-bottom:62.5%;padding-bottom:calc(56.25% + 40px)"><object data="//i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="si=254&amp;contentValue='.htmlspecialchars($vars['id'],2);$html.='"></object></div>';$html.='</div>';

		return $html;
	}

	public static function renderCnn($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="cnn" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://edition.cnn.com/video/api/embed.html#/video/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderComedycentral($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="comedycentral" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderDemocracynow($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/';if((strpos($vars['id'],'/headlines')!==false))$html.='headlines/'.htmlspecialchars(strstr($vars['id'],'/headlines',true),2);elseif((strpos($vars['id'],'2')===0))$html.='story/'.htmlspecialchars($vars['id'],2);elseif((strpos($vars['id'],'shows/')===0))$html.='show/'.htmlspecialchars(substr(strstr($vars['id'],'/'),1),2);else$html.=htmlspecialchars($vars['id'],2);$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderDumpert($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="dumpert" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="http://www.dumpert.nl/embed/'.htmlspecialchars(strtr($vars['id'],'_','/'),2).'/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderEspn($vars)
	{
		$vars += array('cms' => null, 'id' => null);

		$html='<div data-s9e-mediaembed="espn" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://espn.go.com/video/iframe/twitter/?cms='.htmlspecialchars($vars['cms'],2).'&amp;id='.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderGametrailers($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="gametrailers" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="';if((strpos($vars['id'],'mgid:')===0))$html.='//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2);else$html.='//embed.gametrailers.com/embed/'.htmlspecialchars($vars['id'],2).'?embed=1&amp;suppressBumper=1';$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderGetty($vars)
	{
		$vars += array('et' => null, 'height' => null, 'id' => null, 'sig' => null, 'width' => null);

		$html='<div data-s9e-mediaembed="getty" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><div style="overflow:hidden;position:relative;padding-bottom:'.htmlspecialchars(100*($vars['height']+49)/$vars['width'],2).'%;padding-bottom:calc('.htmlspecialchars(100*$vars['height']/$vars['width'],2).'% + 49px)"><iframe allowfullscreen="" scrolling="no" src="//embed.gettyimages.com/embed/'.htmlspecialchars($vars['id'],2).'?et='.htmlspecialchars($vars['et'],2).'&amp;sig='.htmlspecialchars($vars['sig'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderGfycat($vars)
	{
		$vars += array('height' => 315, 'id' => null, 'width' => 560);

		$html='<div data-s9e-mediaembed="gfycat" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><div style="overflow:hidden;position:relative;padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%"><iframe allowfullscreen="" scrolling="no" src="//gfycat.com/iframe/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderGoogleplus($vars)
	{
		$vars += array('name' => null, 'oid' => null, 'pid' => null);

		$html='<iframe data-s9e-mediaembed="googleplus" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" style="border:0;height:240px;max-width:450px;width:100%" src="https://s9e.github.io/iframe/googleplus.min.html#';if(isset($vars['oid']))$html.=htmlspecialchars($vars['oid'],2);else$html.='+'.htmlspecialchars($vars['name'],2);$html.='/posts/'.htmlspecialchars($vars['pid'],2).'"></iframe>';

		return $html;
	}

	public static function renderGooglesheets($vars)
	{
		$vars += array('gid' => null, 'id' => null);

		$html='<iframe data-s9e-mediaembed="googlesheets" allowfullscreen="" scrolling="no" src="https://docs.google.com/spreadsheet/ccc?key='.htmlspecialchars($vars['id'],2).'&amp;widget=true&amp;headers=false&amp;rm=minimal#gid='.htmlspecialchars($vars['gid'],2).'" style="border:0;height:500px;resize:vertical;width:100%"></iframe>';

		return $html;
	}

	public static function renderHudl($vars)
	{
		$vars += array('athlete' => null, 'highlight' => null);

		$html='<div data-s9e-mediaembed="hudl" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.hudl.com/embed/athlete/'.htmlspecialchars($vars['athlete'],2).'/highlights/'.htmlspecialchars($vars['highlight'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderImgur($vars)
	{
		$vars += array('id' => null, 'type' => null);

		$html='';if((strpos($vars['id'],'a/')===0))$html.='<iframe data-s9e-mediaembed="imgur" allowfullscreen="" scrolling="no" src="//imgur.com/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:550px;width:100%"></iframe>';elseif($vars['type']==='album')$html.='<iframe data-s9e-mediaembed="imgur" allowfullscreen="" scrolling="no" src="//imgur.com/a/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:550px;width:100%"></iframe>';else$html.='<iframe data-s9e-mediaembed="imgur" allowfullscreen="" onload="var b=Math.random();window.addEventListener(\'message\',function(a){a.data.id==b&amp;&amp;(style.height=a.data.height+\'px\',style.width=a.data.width+\'px\')});contentWindow.postMessage(\'s9e:\'+b,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/imgur.min.html#'.htmlspecialchars($vars['id'],2).'" style="border:0;height:550px;width:100%"></iframe>';

		return $html;
	}

	public static function renderInternetarchive($vars)
	{
		$vars += array('height' => null, 'id' => null, 'width' => null);

		$html='<div data-s9e-mediaembed="internetarchive" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><div style="overflow:hidden;position:relative;padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%"><iframe allowfullscreen="" scrolling="no" src="https://archive.org/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderKickstarter($vars)
	{
		$vars += array('id' => null, 'video' => null);

		$html='';if(isset($vars['video']))$html.='<div data-s9e-mediaembed="kickstarter" style="display:inline-block;width:100%;max-width:480px"><div style="overflow:hidden;position:relative;padding-bottom:75%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/video.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';else$html.='<div data-s9e-mediaembed="kickstarter" style="display:inline-block;width:100%;max-width:220px"><div style="overflow:hidden;position:relative;padding-bottom:190.90909090909%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/card.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderLivecap($vars)
	{
		$vars += array('channel' => null, 'id' => null);

		$html='<div data-s9e-mediaembed="livecap" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.livecap.tv/s/embed/'.htmlspecialchars($vars['channel'],2).'/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderLivestream($vars)
	{
		$vars += array('account_id' => null, 'channel' => null, 'clip_id' => null, 'event_id' => null, 'video_id' => null);

		$html='<div data-s9e-mediaembed="livestream" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="';if(isset($vars['clip_id']))$html.='//cdn.livestream.com/embed/'.htmlspecialchars($vars['channel'],2).'?layout=4&amp;autoplay=false&amp;clip='.htmlspecialchars($vars['clip_id'],2);else{$html.='//livestream.com/accounts/'.htmlspecialchars($vars['account_id'],2).'/events/'.htmlspecialchars($vars['event_id'],2);if(isset($vars['video_id']))$html.='/videos/'.htmlspecialchars($vars['video_id'],2);$html.='/player?autoPlay=false';}$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderMailru($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="mailru" style="display:inline-block;width:100%;max-width:560px"><div style="overflow:hidden;position:relative;padding-bottom:61.071428571429%"><iframe allowfullscreen="" scrolling="no" src="http://videoapi.my.mail.ru/videos/embed/'.htmlspecialchars($vars['id'],2).'.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderMedium($vars)
	{
		$vars += array('id' => null);

		$html='<iframe data-s9e-mediaembed="medium" allowfullscreen="" onload="window.addEventListener(\'message\',function(a){a=a.data.split(\'::\');\'m\'===a[0]&amp;&amp;0&lt;src.indexOf(a[1])&amp;&amp;a[2]&amp;&amp;(style.height=a[2]+\'px\')})" scrolling="no" src="https://api.medium.com/embed?type=story&amp;path=%2F%2F'.htmlspecialchars($vars['id'],2).'&amp;id='.htmlspecialchars(strtr($vars['id'],'abcdef','111111'),2).'" style="border:1px solid;border-color:#eee #ddd #bbb;border-radius:5px;box-shadow:rgba(0,0,0,.15) 0 1px 3px;height:400px;max-width:400px;width:100%"></iframe>';

		return $html;
	}

	public static function renderNpr($vars)
	{
		$vars += array('i' => null, 'm' => null);

		$html='<iframe data-s9e-mediaembed="npr" allowfullscreen="" scrolling="no" src="//www.npr.org/player/embed/'.htmlspecialchars($vars['i'],2).'/'.htmlspecialchars($vars['m'],2).'" style="border:0;height:228px;max-width:800px;width:100%"></iframe>';

		return $html;
	}

	public static function renderReddit($vars)
	{
		$vars += array('path' => null);

		$html='<iframe data-s9e-mediaembed="reddit" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/reddit.min.html#'.htmlspecialchars($vars['path'],2).'" style="border:0;height:165px;max-width:800px;width:100%"></iframe>';

		return $html;
	}

	public static function renderSoundcloud($vars)
	{
		$vars += array('id' => null, 'playlist_id' => null, 'secret_token' => null, 'track_id' => null);

		$html='';if(isset($vars['playlist_id']))$html.='<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/playlists/'.htmlspecialchars($vars['playlist_id'],2).'" style="border:0;height:450px;max-width:900px;width:100%"></iframe>';elseif(isset($vars['track_id']))$html.='<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/'.htmlspecialchars($vars['track_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2).'" style="border:0;height:166px;max-width:900px;width:100%"></iframe>';elseif((strpos($vars['id'],'://')===false))$html.='<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=https%3A//soundcloud.com/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:166px;max-width:900px;width:100%"></iframe>';else$html.='<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url='.htmlspecialchars($vars['id'],2).'" style="border:0;height:166px;max-width:900px;width:100%"></iframe>';

		return $html;
	}

	public static function renderSpotify($vars)
	{
		$vars += array('path' => null, 'uri' => null);

		$html='<div data-s9e-mediaembed="spotify" style="display:inline-block;width:100%;max-width:400px"><div style="overflow:hidden;position:relative;padding-bottom:120%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="https://embed.spotify.com/?view=coverart&amp;uri=';if(isset($vars['uri']))$html.=htmlspecialchars($vars['uri'],2);else$html.='spotify:'.htmlspecialchars(strtr($vars['path'],'/',':'),2);$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderStitcher($vars)
	{
		$vars += array('eid' => null, 'fid' => null);

		$html='<iframe data-s9e-mediaembed="stitcher" allowfullscreen="" scrolling="no" src="//app.stitcher.com/splayer/f/'.htmlspecialchars($vars['fid'],2).'/'.htmlspecialchars($vars['eid'],2).'" style="border:0;height:150px;max-width:900px;width:100%"></iframe>';

		return $html;
	}

	public static function renderTed($vars)
	{
		$vars += array('id' => null);

		$html='<div data-s9e-mediaembed="ted" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//embed.ted.com/'.htmlspecialchars($vars['id'],2);if((strpos($vars['id'],'.html')===false))$html.='.html';$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderTinypic($vars)
	{
		$vars += array('id' => null, 's' => null);

		$html='<div data-s9e-mediaembed="tinypic" style="display:inline-block;width:100%;max-width:560px"><div style="overflow:hidden;position:relative;padding-bottom:61.607142857143%"><object data="http://tinypic.com/player.swf?file='.htmlspecialchars($vars['id'],2).'&amp;s='.htmlspecialchars($vars['s'],2).'" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"></object></div></div>';

		return $html;
	}

	public static function renderTumblr($vars)
	{
		$vars += array('id' => null, 'key' => null);

		$html='<iframe data-s9e-mediaembed="tumblr" allowfullscreen="" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,\'https://s9e.github.io\')" scrolling="no" src="https://s9e.github.io/iframe/tumblr.min.html#'.htmlspecialchars($vars['key'],2).'/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:180px;max-width:520px;width:100%"></iframe>';

		return $html;
	}

	public static function renderTwitch($vars)
	{
		$vars += array('archive_id' => null, 'channel' => null, 'chapter_id' => null, 't' => null, 'video_id' => null);

		$html='<div data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//player.twitch.tv/?autoplay=false&amp;';if(isset($vars['archive_id']))$html.='video=a'.htmlspecialchars($vars['archive_id'],2);elseif(isset($vars['chapter_id']))$html.='video=c'.htmlspecialchars($vars['chapter_id'],2);elseif(isset($vars['video_id']))$html.='video=v'.htmlspecialchars($vars['video_id'],2);else$html.='channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['t']))$html.='&amp;time='.htmlspecialchars($vars['t'],2);$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderUstream($vars)
	{
		$vars += array('cid' => null, 'vid' => null);

		$html='<div data-s9e-mediaembed="ustream" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%">';if(isset($vars['vid']))$html.='<iframe allowfullscreen="" scrolling="no" src="//www.ustream.tv/embed/recorded/'.htmlspecialchars($vars['vid'],2);else$html.='<iframe allowfullscreen="" scrolling="no" src="//www.ustream.tv/embed/'.htmlspecialchars($vars['cid'],2);$html.='?html5ui" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe>';$html.='</div></div>';

		return $html;
	}

	public static function renderVidme($vars)
	{
		$vars += array('height' => 360, 'id' => null, 'width' => 640);

		$html='<div data-s9e-mediaembed="vidme" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><div style="overflow:hidden;position:relative;padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%"><iframe allowfullscreen="" scrolling="no" src="https://vid.me/e/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderVk($vars)
	{
		$vars += array('hash' => null, 'oid' => null, 'vid' => null);

		$html='<div data-s9e-mediaembed="vk" style="display:inline-block;width:100%;max-width:607px"><div style="overflow:hidden;position:relative;padding-bottom:59.308072487644%"><iframe allowfullscreen="" scrolling="no" src="//vk.com/video_ext.php?oid='.htmlspecialchars($vars['oid'],2).'&amp;id='.htmlspecialchars($vars['vid'],2).'&amp;hash='.htmlspecialchars($vars['hash'],2).'&amp;hd=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderXboxclips($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<div data-s9e-mediaembed="xboxclips" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//xboxclips.com/'.htmlspecialchars($vars['user'],2).'/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderXboxdvr($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<div data-s9e-mediaembed="xboxdvr" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//xboxdvr.com/gamer/'.htmlspecialchars($vars['user'],2).'/video/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></div></div>';

		return $html;
	}

	public static function renderYoutube($vars)
	{
		$vars += array('h' => null, 'id' => null, 'list' => null, 'm' => null, 's' => null, 't' => null);

		$html='<div data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.youtube.com/embed/'.htmlspecialchars($vars['id'],2);if(isset($vars['list']))$html.='?list='.htmlspecialchars($vars['list'],2);if(isset($vars['t'])||isset($vars['m'])){if(isset($vars['list']))$html.='&amp;';else$html.='?';$html.='start=';if(isset($vars['t']))$html.=htmlspecialchars($vars['t'],2);elseif(isset($vars['h']))$html.=htmlspecialchars($vars['h']*3600+$vars['m']*60+$vars['s'],2);else$html.=htmlspecialchars($vars['m']*60+$vars['s'],2);}$html.='"></iframe></div></div>';

		return $html;
	}

	public static function renderZippyshare($vars)
	{
		$vars += array('file' => null, 'server' => null);

		$html='<object data-s9e-mediaembed="zippyshare" data="//api.zippyshare.com/api/player.swf" style="height:80px;max-width:900px;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="file='.htmlspecialchars($vars['file'],2).'&amp;server='.htmlspecialchars($vars['server'],2).'&amp;autostart=false"></object>';

		return $html;
	}
}