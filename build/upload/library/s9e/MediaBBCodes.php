<?php

/**
* @copyright Copyright (c) 2013-2015 The s9e Authors
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
	* @var array Associative array using site IDs as key, callbacks as values
	*/
	public static $customCallbacks;

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
		'abcnews'=>array('ABC News','http://abcnews.go.com/',array('news'=>1),'!abcnews\\.go\\.com/[^/]+/video/[^/]+-(?\'id\'\\d+)!',array('!abcnews\\.go\\.com/[^/]+/video/[^/]+-(?\'id\'\\d+)!'),7=>'<iframe width="640" height="360" src="//abcnews.go.com/video/embed?id={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'amazon'=>array('Amazon Product','http://affiliate-program.amazon.com/',array('misc'=>1),"#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)[:/]).*?/(?:dp|gp/product)/(?'id'[A-Z0-9]+)#\n#(?'id')(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)[:/]).*?amazon\\.(?:co\\.)?(?'tld'ca|de|fr|it|jp|uk)#",array('#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)[:/]).*?/(?:dp|gp/product)/(?\'id\'[A-Z0-9]+)#','#(?=.*?[./]amazon\\.(?>c(?>a|o(?>m|\\.(?>jp|uk)))|de|fr|it)[:/]).*?amazon\\.(?:co\\.)?(?\'tld\'ca|de|fr|it|jp|uk)#'),true,7=>null,8=>true),
		'audioboom'=>array('audioBoom','https://audioboom.com/',array('podcasts'=>1),'!audioboo(?:\\.f|m\\.co)m/boos/(?\'id\'\\d+)!',array('!audioboo(?:\\.f|m\\.co)m/boos/(?\'id\'\\d+)!'),7=>'<iframe width="100%" height="150" src="//audioboom.com/boos/{$id}/embed/v3" style="max-width:700px" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',8=>true),
		'audiomack'=>array('Audiomack','http://www.audiomack.com/',array('music'=>1),'!audiomack\\.com/(?\'mode\'album|song)/(?\'id\'[-\\w]+/[-\\w]+)!',array('!audiomack\\.com/(?\'mode\'album|song)/(?\'id\'[-\\w]+/[-\\w]+)!'),true,7=>null,8=>true),
		'bandcamp'=>array('Bandcamp','http://bandcamp.com/',array('music'=>1),"!(?'id')bandcamp\\.com/album/.!\n!(?'id')bandcamp\\.com/track/.!",array(),true,array(array('extract'=>array('!/album=(?\'album_id\'\\d+)!'),'match'=>array('!bandcamp\\.com/album/.!')),array('extract'=>array('!"album_id":(?\'album_id\'\\d+)!','!"track_num":(?\'track_num\'\\d+)!','!/track=(?\'track_id\'\\d+)!'),'match'=>array('!bandcamp\\.com/track/.!')))),
		'bbcnews'=>array('BBC News','http://www.bbc.com/news/video_and_audio/',array('news'=>1),'!(?\'id\')bbc\\.com/news/\\w+!',array(),true,array(array('extract'=>array('!meta name="twitter:player".*?playlist=(?\'playlist\'[-/\\w]+)(?:&poster=(?\'poster\'[-/.\\w]+))?(?:&ad_site=(?\'ad_site\'[/\\w]+))?!'),'match'=>array('!bbc\\.com/news/\\w+!')))),
		'bleacherreport'=>array('Bleacher Report videos','http://bleacherreport.com/',array('sports'=>1),'!(?\'id\')(?=.*?[./]bleacherreport\\.com[:/]).*?/articles/.!',array(),true,array(array('extract'=>array('!id="video-(?\'id\'[-\\w]+)!'),'match'=>array('!/articles/.!'))),'<iframe width="640" height="360" src="//bleacherreport.com/video_embed?id={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'blip'=>array('Blip','http://blip.tv/',array('videos'=>1),"!blip\\.tv/play/(?'id'[\\w+%/_]+)!\n!(?'id')blip\\.tv/[^/]+/[^/]+-\\d+$!",array('!blip\\.tv/play/(?\'id\'[\\w+%/_]+)!'),true,array(array('extract'=>array('!blip\\.tv/play/(?\'id\'[\\w%+/_]+)!'),'match'=>array('!blip\\.tv/[^/]+/[^/]+-\\d+$!'))),'<iframe width="560" height="315" src="//blip.tv/play/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'break'=>array('Break','http://www.break.com/',array('entertainment'=>1),'!break\\.com/video/.*-(?\'id\'\\d+)$!',array('!break\\.com/video/.*-(?\'id\'\\d+)$!'),7=>'<iframe width="464" height="280" src="//break.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'cbsnews'=>array('CBS News Video','http://www.cbsnews.com/video/',array('news'=>1),"#cbsnews\\.com/video/watch/\\?id=(?'id'\\d+)#\n#(?'id')cbsnews\\.com/videos/(?!watch/)#",array('#cbsnews\\.com/video/watch/\\?id=(?\'id\'\\d+)#'),true,array(array('extract'=>array('#"pid":"(?\'pid\'\\w+)"#'),'match'=>array('#cbsnews\\.com/videos/(?!watch/)#')))),
		'cnbc'=>array('CNBC','http://www.cnbc.com/',array('news'=>1),'!(?=.*?[./]video\\.cnbc\\.com[:/]).*?cnbc\\.com/gallery/\\?video=(?\'id\'\\d+)!',array('!(?=.*?[./]video\\.cnbc\\.com[:/]).*?cnbc\\.com/gallery/\\?video=(?\'id\'\\d+)!'),7=>'<object type="application/x-shockwave-flash" typemustmatch="" width="400" height="380" data="//plus.cnbc.com/rssvideosearch/action/player/id/{$id}/code/cnbcplayershare"><param name="allowfullscreen" value="true"/><embed type="application/x-shockwave-flash" width="400" height="380" src="//plus.cnbc.com/rssvideosearch/action/player/id/{$id}/code/cnbcplayershare" allowfullscreen=""/></object>'),
		'cnn'=>array('CNN','http://edition.cnn.com/video/',array('news'=>1),'!cnn\\.com/video/data/2\\.0/video/(?\'id\'.*\\.cnn)!',array('!cnn\\.com/video/data/2\\.0/video/(?\'id\'.*\\.cnn)!'),7=>'<iframe width="560" height="315" src="http://edition.cnn.com/video/api/embed.html#/video/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'cnnmoney'=>array('CNNMoney','http://money.cnn.com/video/',array('news'=>1),'!money\\.cnn\\.com/video/(?\'id\'.*\\.cnnmoney)!',array('!money\\.cnn\\.com/video/(?\'id\'.*\\.cnnmoney)!'),7=>'<iframe width="560" height="360" src="http://money.cnn.com/.element/ssi/video/7.0/players/embed.player.html?videoid=video/{$id}&amp;width=560&amp;height=360" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'colbertnation'=>array('Colbert Nation','http://thecolbertreport.cc.com/',array('entertainment'=>1),'!(?\'id\')thecolbertreport\\.cc\\.com/videos/!',array(),true,array(array('extract'=>array('!(?\'id\'mgid:arc:video:colbertnation\\.com:[-0-9a-f]+)!'),'match'=>array('!thecolbertreport\\.cc\\.com/videos/!')))),
		'collegehumor'=>array('CollegeHumor','http://www.collegehumor.com/',array('entertainment'=>1),'!collegehumor\\.com/(?:video|embed)/(?\'id\'\\d+)!',array('!collegehumor\\.com/(?:video|embed)/(?\'id\'\\d+)!'),7=>'<iframe width="600" height="369" src="//www.collegehumor.com/e/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'comedycentral'=>array('Comedy Central','http://www.comedycentral.com/funny-videos',array('entertainment'=>1),'!(?\'id\')c(?:c|omedycentral)\\.com/video-clips/!',array(),true,array(array('extract'=>array('!(?\'id\'mgid:arc:video:[.\\w]+:[-\\w]+)!'),'match'=>array('!c(?:c|omedycentral)\\.com/video-clips/!')))),
		'coub'=>array('Coub','http://coub.com/',array('videos'=>1),'!coub\\.com/view/(?\'id\'\\w+)!',array('!coub\\.com/view/(?\'id\'\\w+)!'),7=>'<iframe width="560" height="315" src="//coub.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'dailymotion'=>array('Dailymotion','http://www.dailymotion.com/',array('videos'=>1),'!dailymotion\\.com/(?:live/|user/[^#]+#video=|video/)(?\'id\'[A-Za-z0-9]+)!',array('!dailymotion\\.com/(?:live/|user/[^#]+#video=|video/)(?\'id\'[A-Za-z0-9]+)!'),7=>'<iframe width="560" height="315" src="//www.dailymotion.com/embed/video/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'dailyshow'=>array('The Daily Show with Jon Stewart','http://www.thedailyshow.com/',array('entertainment'=>1),'!(?\'id\')thedailyshow\\.c(?:c\\.c)?om/(?:collection|extended-interviews|videos|watch)/!',array(),true,array(array('extract'=>array('!(?\'id\'mgid:arc:(?:playlist|video):thedailyshow\\.com:[-0-9a-f]+)!'),'match'=>array('!thedailyshow\\.c(?:c\\.c)?om/(?:collection|extended-interviews|videos|watch)/!')))),
		'dumpert'=>array('dumpert','http://www.dumpert.nl/',array('.nl'=>1,'entertainment'=>1),'!(?\'id\')dumpert\\.nl/mediabase/\\d+/\\w+!',array(),true,array(array('extract'=>array('!data-itemid="(?\'id\'\\w+)!'),'match'=>array('!dumpert\\.nl/mediabase/\\d+/\\w+!')))),
		'eighttracks'=>array('8tracks','http://8tracks.com/',array('music'=>1),"!8tracks\\.com/[-\\w]+/(?'id'\\d+)(?=#|$)!\n!(?'id')8tracks\\.com/[-\\w]+/[-\\w]+!",array('!8tracks\\.com/[-\\w]+/(?\'id\'\\d+)(?=#|$)!'),true,array(array('extract'=>array('!eighttracks://mix/(?\'id\'\\d+)!'),'match'=>array('!8tracks\\.com/[-\\w]+/[-\\w]+!'))),'<iframe width="400" height="400" src="//8tracks.com/mixes/{$id}/player_v3_universal" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'espn'=>array('ESPN','http://espn.go.com/',array('sports'=>1),'#(?=.*?[./]espn\\.go\\.com[:/]).*?(?\'cms\'deportes|espn(?!d)).*(?:clip\\?|video\\?v|clipDeportes\\?)id=(?:\\w+:)?(?\'id\'\\d+)#',array('#(?=.*?[./]espn\\.go\\.com[:/]).*?(?\'cms\'deportes|espn(?!d)).*(?:clip\\?|video\\?v|clipDeportes\\?)id=(?:\\w+:)?(?\'id\'\\d+)#'),true),
		'espndeportes'=>array('ESPN Deportes','http://www.espndeportes.com/',array('sports'=>1),"!espndeportes\\.com/videohub/video/clipDeportes\\?id=(?:deportes:)?(?'id'\\d+)!\n!(?'id')espndeportes\\.com/\\?id=\\w+&topId=\\d+!",array('!espndeportes\\.com/videohub/video/clipDeportes\\?id=(?:deportes:)?(?\'id\'\\d+)!'),true,array(array('extract'=>array('!espn\\.mem\\.video\\((?\'id\'\\d+)!'),'match'=>array('!espndeportes\\.com/\\?id=\\w+&topId=\\d+!'))),'<iframe width="560" height="315" src="https://espn.go.com/video/iframe/twitter/?cms=deportes&amp;id={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'facebook'=>array('Facebook','http://www.facebook.com/',array('social'=>1),'@www\\.facebook\\.com/(?:[\\w/]+/permalink|(?!pages/|groups/).*?)(?:/|fbid=|\\?v=)(?\'id\'\\d+)(?=$|[/?&#])@',array('@www\\.facebook\\.com/(?:[\\w/]+/permalink|(?!pages/|groups/).*?)(?:/|fbid=|\\?v=)(?\'id\'\\d+)(?=$|[/?&#])@'),7=>'<iframe width="560" height="315" src="//s9e.github.io/iframe/facebook.min.html#{$id}" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',8=>true),
		'flickr'=>array('Flickr','https://www.flickr.com/',array('images'=>1),'!flickr\\.com/photos/[^/]+/(?\'id\'\\d+)!',array('!flickr\\.com/photos/[^/]+/(?\'id\'\\d+)!'),7=>'<iframe width="500" height="500" src="https://www.flickr.com/photos/_/{$id}/player/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'foxnews'=>array('Fox News','http://www.foxnews.com/',array('news'=>1),'!video\\.foxnews\\.com/v/(?\'id\'\\d+)!',array('!video\\.foxnews\\.com/v/(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="//video.foxnews.com/v/video-embed.html?video_id={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'funnyordie'=>array('Funny or Die','http://www.funnyordie.com/',array('entertainment'=>1),'!funnyordie\\.com/videos/(?\'id\'[0-9a-f]+)!',array('!funnyordie\\.com/videos/(?\'id\'[0-9a-f]+)!'),7=>'<iframe width="640" height="360" src="http://www.funnyordie.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'gamespot'=>array('Gamespot','http://www.gamespot.com/',array('gaming'=>1),'!gamespot\\.com.*?/(?:events|videos)/.*?-(?\'id\'\\d+)/(?:[#?].*)?$!',array('!gamespot\\.com.*?/(?:events|videos)/.*?-(?\'id\'\\d+)/(?:[#?].*)?$!'),7=>'<iframe width="640" height="400" src="//www.gamespot.com/videos/embed/{$id}/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'gametrailers'=>array('GameTrailers','http://www.gametrailers.com/',array('gaming'=>1),'!(?\'id\')gametrailers\\.com/(?:full-episode|review|video)s/!',array(),true,array(array('extract'=>array('!(?\'id\'mgid:arc:(?:episode|video):gametrailers\\.com:[-\\w]+)!'),'match'=>array('!gametrailers\\.com/(?:full-episode|review|video)s/!')))),
		'getty'=>array('Getty Images','http://www.gettyimages.com/',array('images'=>1),"!gty\\.im/(?'id'\\d+)!\n!(?=.*?[./]g(?:ettyimages\\.(?:c(?:n|o(?:\\.(?>jp|uk)|m(?>\\.au)?))|d[ek]|es|fr|i[et]|nl|pt|[bs]e)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?'id'\\d+)!",array('!gty\\.im/(?\'id\'\\d+)!','!(?=.*?[./]g(?:ettyimages\\.(?:c(?:n|o(?:\\.(?>jp|uk)|m(?>\\.au)?))|d[ek]|es|fr|i[et]|nl|pt|[bs]e)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!"height":[ "]*(?\'height\'\\d+)!','!"width":[ "]*(?\'width\'\\d+)!','!et=(?\'et\'[-=\\w]+)!','!sig=(?\'sig\'[-=\\w]+)!'),'match'=>array('//'),'url'=>'http://embed.gettyimages.com/preview/{@id}'))),
		'gfycat'=>array('Gfycat','http://gfycat.com/',array('images'=>1),'!gfycat\\.com/(?\'id\'\\w+)!',array('!gfycat\\.com/(?\'id\'\\w+)!'),true,array(array('extract'=>array('!property="og:video:height"\\s*content="(?\'height\'\\d+)!','!property="og:video:width"\\s*content="(?\'width\'\\d+)!'),'match'=>array('//'),'url'=>'http://gfycat.com/{@id}'))),
		'gist'=>array('GitHub Gist (via custom iframe)','https://gist.github.com/',array('misc'=>1),'!gist\\.github\\.com/(?\'id\'(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!',array('!gist\\.github\\.com/(?\'id\'(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!'),7=>'<iframe width="100%" height="180" src="//s9e.github.io/iframe/gist.min.html#{$id}" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',8=>true),
		'globalnews'=>array('Global News','http://globalnews.ca/',array('.ca'=>1,'news'=>1),'!globalnews\\.ca/video/(?\'id\'\\d+)!',array('!globalnews\\.ca/video/(?\'id\'\\d+)!'),7=>'<iframe width="560" height="377" src="http://globalnews.ca/video/embed/{$id}/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'gofundme'=>array('GoFundMe','http://www.gofundme.com/',array('fundraising'=>1),'@gofundme\\.com/(?\'id\'\\w+)(?![^#?])@',array('@gofundme\\.com/(?\'id\'\\w+)(?![^#?])@'),7=>'<object type="application/x-shockwave-flash" typemustmatch="" width="258" height="338" data="//funds.gofundme.com/Widgetflex.swf"><param name="allowfullscreen" value="true"/><param name="flashvars" value="page={$id}"/><embed type="application/x-shockwave-flash" width="258" height="338" src="//funds.gofundme.com/Widgetflex.swf" allowfullscreen="" flashvars="page={$id}"/></object>'),
		'googleplus'=>array('Google+','https://plus.google.com/',array('social'=>1),'!(?\'id\')//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?\'name\'\\w+)|(?\'oid\'\\d+))/posts/(?\'pid\'\\w+)!',array('!//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?\'name\'\\w+)|(?\'oid\'\\d+))/posts/(?\'pid\'\\w+)!'),true,7=>null,8=>true),
		'googlesheets'=>array('Google Sheets','http://www.google.com/sheets/about/',array('documents'=>1),'!docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|s/d/)(?\'id\'[-\\w]+)[^#]*(?:#gid=(?\'gid\'\\d+))?!',array('!docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|s/d/)(?\'id\'[-\\w]+)[^#]*(?:#gid=(?\'gid\'\\d+))?!'),true,7=>null,8=>true),
		'grooveshark'=>array('Grooveshark','http://grooveshark.com/',array('music'=>1),"%(?'id')grooveshark\\.com(?:/#!?)?/playlist/[^/]+/(?'playlistid'\\d+)%\n%(?'id')grooveshark\\.com(?:/#!?)?/s/(?'path'[^/]+/.+)%",array('%grooveshark\\.com(?:/#!?)?/playlist/[^/]+/(?\'playlistid\'\\d+)%'),true,array(array('extract'=>array('%songID=(?\'songid\'\\d+)%'),'match'=>array('%grooveshark\\.com(?:/#!?)?/s/(?\'path\'[^/]+/.+)%'),'url'=>'http://grooveshark.com/s/{@path}'))),
		'hudl'=>array('Hudl','http://www.hudl.com/',array('sports'=>1),"!(?'id')hudl\\.com/athlete/(?'athlete'\\d+)/highlights/(?'highlight'\\d+)!\n!(?'id')hudl\\.com/v/!",array('!hudl\\.com/athlete/(?\'athlete\'\\d+)/highlights/(?\'highlight\'\\d+)!'),true,array(array('extract'=>array('!hudl\\.com/athlete/(?\'athlete\'\\d+)/highlights/(?\'highlight\'\\d+)!'),'match'=>array('!hudl\\.com/v/!')))),
		'hulu'=>array('Hulu','http://www.hulu.com/',array('misc'=>1),'!(?\'id\')hulu\\.com/watch/!',array(),true,array(array('extract'=>array('!eid=(?\'id\'[-\\w]+)!'),'match'=>array('!hulu\\.com/watch/!'))),'<iframe width="640" height="360" src="https://secure.hulu.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'humortvnl'=>array('HumorTV','http://humortv.vara.nl/pg.2.pg-home.html',array('.nl'=>1,'entertainment'=>1),'!humortv\\.vara\\.nl/\\w+\\.(?\'id\'[-.\\w]+)\\.html!',array('!humortv\\.vara\\.nl/\\w+\\.(?\'id\'[-.\\w]+)\\.html!'),7=>'<iframe width="560" height="315" src="http://humortv.vara.nl/embed.{$id}.html" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'ign'=>array('IGN','http://www.ign.com/videos/',array('gaming'=>1),'!(?\'id\'https?://.*?ign\\.com/videos/.+)!',array('!(?\'id\'https?://.*?ign\\.com/videos/.+)!'),7=>'<iframe width="468" height="263" src="http://widgets.ign.com/video/embed/content.html?url={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'imdb'=>array('IMDb','http://www.imdb.com/',array('movies'=>1),'!imdb\\.com/video/\\w+/vi(?\'id\'\\d+)!',array('!imdb\\.com/video/\\w+/vi(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="http://www.imdb.com/video/imdb/vi{$id}/imdb/embed?autoplay=false&amp;width=560" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'imgur'=>array('Imgur','http://imgur.com/',array('images'=>1),"!imgur\\.com/(?:gallery/)?(?'id'(?:a/)?\\w+)!\n!(?'id')imgur\\.com/(?:a|gallery)/\\w!\n!(?'id')imgur\\.com/\\w+\\.(?:gifv|mp4)!",array('!imgur\\.com/(?:gallery/)?(?\'id\'(?:a/)?\\w+)!'),true,array(array('extract'=>array('!<div id="(?\'type\'album)-!'),'match'=>array('!imgur\\.com/(?:a|gallery)/\\w!')),array('extract'=>array('!width:\\s*(?\'width\'\\d+)!','!height:\\s*(?\'height\'\\d+)!','!(?\'type\'gifv)!'),'match'=>array('!imgur\\.com/\\w+\\.(?:gifv|mp4)!'),'url'=>'http://i.imgur.com/{@id}.gifv'))),
		'indiegogo'=>array('Indiegogo','http://www.indiegogo.com/',array('fundraising'=>1),"!indiegogo\\.com/projects/(?'id'\\d+)$!\n!(?'id')indiegogo\\.com/projects/.!",array('!indiegogo\\.com/projects/(?\'id\'\\d+)$!'),true,array(array('extract'=>array('!indiegogo\\.com/projects/(?\'id\'\\d+)/!'),'match'=>array('!indiegogo\\.com/projects/.!'))),'<iframe width="224" height="486" src="//www.indiegogo.com/project/{$id}/widget" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'instagram'=>array('Instagram','http://instagram.com/',array('social'=>1),'!instagram\\.com/p/(?\'id\'[-_\\w]+)!',array('!instagram\\.com/p/(?\'id\'[-_\\w]+)!'),7=>'<iframe width="612" height="710" src="//instagram.com/p/{$id}/embed/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'internetarchive'=>array('Internet Archive','https://archive.org/',array('misc'=>1),'!(?\'id\')archive\\.org/details/!',array(),true,array(array('extract'=>array('!meta property="twitter:player" content="https://archive.org/embed/(?\'id\'[^/"]+)!','!meta property="og:video:width" content="(?\'width\'\\d+)!','!meta property="og:video:height" content="(?\'height\'\\d+)!'),'match'=>array('!archive\\.org/details/!')))),
		'izlesene'=>array('İzlesene','http://www.izlesene.com/',array('.tr'=>1),'!izlesene\\.com/video/[-\\w]+/(?\'id\'\\d+)!',array('!izlesene\\.com/video/[-\\w]+/(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="//www.izlesene.com/embedplayer/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'khl'=>array('Kontinental Hockey League (КХЛ)','http://www.khl.ru/',array('.ru'=>1,'sports'=>1),'!(?\'id\')video\\.khl\\.ru/(?:event|quote)s/\\d!',array(),true,array(array('extract'=>array('!//video\\.khl\\.ru/iframe/feed/start/(?\'id\'[\\w/]+)!'),'match'=>array('!video\\.khl\\.ru/(?:event|quote)s/\\d!'))),'<iframe width="560" height="315" src="//video.khl.ru/iframe/feed/start/{$id}?type_id=18&amp;width=560&amp;height=315" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'kickstarter'=>array('Kickstarter','http://www.kickstarter.com/',array('fundraising'=>1),'!kickstarter\\.com/projects/(?\'id\'[^/]+/[^/?]+)(?:/widget/(?:(?\'card\'card)|(?\'video\'video)))?!',array('!kickstarter\\.com/projects/(?\'id\'[^/]+/[^/?]+)(?:/widget/(?:(?\'card\'card)|(?\'video\'video)))?!'),true),
		'liveleak'=>array('LiveLeak','http://www.liveleak.com/',array('videos'=>1),'!liveleak\\.com/view\\?i=(?\'id\'[a-f_0-9]+)!',array('!liveleak\\.com/view\\?i=(?\'id\'[a-f_0-9]+)!'),7=>'<iframe width="640" height="360" src="http://www.liveleak.com/ll_embed?i={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'livestream'=>array('Livestream','http://new.livestream.com/',array('videos'=>1),'((?\'id\')(?=.*?[./]livestream\\.com[:/]).*?)',array('!livestream\\.com/accounts/(?\'account_id\'\\d+)/events/(?\'event_id\'\\d+)!','!/videos/(?\'video_id\'\\d+)!'),true,array(array('extract'=>array('!accounts/(?\'account_id\'\\d+)/events/(?\'event_id\'\\d+)!'),'match'=>array('//')))),
		'mailru'=>array('Mail.Ru','http://my.mail.ru/',array('.ru'=>1),'!(?\'id\')my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!',array(),true,array(array('extract'=>array('!mail\\.ru/videos/embed/(?\'id\'[\\w/]+)\\.html!'),'match'=>array('!my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!')))),
		'medium'=>array('Medium','https://medium.com/',array('blogging'=>1),'!medium\\.com/[^/]*/(?:[-\\w]+-)?(?\'id\'[\\da-f]+)!',array('!medium\\.com/[^/]*/(?:[-\\w]+-)?(?\'id\'[\\da-f]+)!'),7=>'<iframe width="400" height="454" src="https://api.medium.com/embed?type=story&amp;path=//{$id}" style="border:solid 1px;border-color:#eee #ddd #bbb;border-radius:5px;box-shadow:rgba(0,0,0,0.15) 0px 1px 3px" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'metacafe'=>array('Metacafe','http://www.metacafe.com/',array('videos'=>1),'!metacafe\\.com/watch/(?\'id\'\\d+)!',array('!metacafe\\.com/watch/(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="//www.metacafe.com/embed/{$id}/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'mixcloud'=>array('Mixcloud','http://www.mixcloud.com/',array('music'=>1),"@mixcloud\\.com/(?!categories|tag)(?'id'[-\\w]+/[^/&]+)/@\n@(?'id')//i\\.mixcloud\\.com/\\w+$@",array('@mixcloud\\.com/(?!categories|tag)(?\'id\'[-\\w]+/[^/&]+)/@'),true,array(array('extract'=>array('@link rel="canonical" href="https?://[^/]+/(?\'id\'[-\\w]+/[^/&]+)/@'),'match'=>array('@//i\\.mixcloud\\.com/\\w+$@'))),'<iframe width="400" height="400" src="//www.mixcloud.com/widget/iframe/?feed=http%3A%2F%2Fwww.mixcloud.com%2F{$id}%2F&amp;embed_type=widget_standard" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'msnbc'=>array('MSNBC','http://www.msnbc.com/watch',array('news'=>1),"@(?'id')msnbc\\.com/[-\\w]+/watch/@\n@(?'id')on\\.msnbc\\.com/.@",array(),true,array(array('extract'=>array('@guid="?(?\'id\'\\w+)@'),'match'=>array('@msnbc\\.com/[-\\w]+/watch/@','@on\\.msnbc\\.com/.@'))),'<iframe width="635" height="440" src="//player.theplatform.com/p/2E2eJC/EmbeddedOffSite?guid={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'natgeochannel'=>array('National Geographic Channel','http://channel.nationalgeographic.com/',array('misc'=>1),'@channel\\.nationalgeographic\\.com/(?\'id\'[-/\\w]+/videos/[-\\w]+)@',array('@channel\\.nationalgeographic\\.com/(?\'id\'[-/\\w]+/videos/[-\\w]+)@'),7=>'<iframe width="560" height="315" src="//channel.nationalgeographic.com/{$id}/embed/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'natgeovideo'=>array('National Geographic Video','http://video.nationalgeographic.com/',array('documentaries'=>1),'@(?\'id\')video\\.nationalgeographic\\.com/(?:tv|video)/\\w@',array(),true,array(array('extract'=>array('@guid="(?\'id\'[-\\w]+)"@'),'match'=>array('@video\\.nationalgeographic\\.com/(?:tv|video)/\\w@'))),'<iframe width="560" height="315" src="//player.d.nationalgeographic.com/players/ngsvideo/share/?guid={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'nhl'=>array('NHL VideoCenter','http://video.nhl.com/videocenter/',array('sports'=>1),'!nhl\\.com/videocenter/.*?\\Wid=(?\'id\'[-\\w]+)!',array('!nhl\\.com/videocenter/.*?\\Wid=(?\'id\'[-\\w]+)!'),7=>'<iframe width="640" height="395" src="//video.nhl.com/videocenter/embed?playlist={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'npr'=>array('NPR','http://www.npr.org/',array('podcasts'=>1),'!(?\'id\')npr\\.org/[\\w/]+/\\d+!',array(),true,array(array('extract'=>array('!openPlayer\\((?\'i\'\\d+), (?\'m\'\\d+)!'),'match'=>array('!npr\\.org/[\\w/]+/\\d+!')))),
		'nytimes'=>array('The New York Times Video','http://www.nytimes.com/video/',array('news'=>1),"!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?'id'\\d+)!\n!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?'id'\\d+)!",array('!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?\'id\'\\d+)!','!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?\'id\'\\d+)!'),7=>'<iframe width="480" height="373" src="http://graphics8.nytimes.com/bcvideo/1.0/iframe/embed.html?videoId={$id}&amp;playerType=embed" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'pastebin'=>array('Pastebin','http://pastebin.com/',array('misc'=>1),'!pastebin\\.com/(?:\\w+\\.php\\?i=)?(?\'id\'\\w+)!',array('!pastebin\\.com/(?:\\w+\\.php\\?i=)?(?\'id\'\\w+)!'),7=>'<iframe width="100%" height="300" src="//pastebin.com/embed_iframe.php?i={$id}" scrolling="" style="resize:both" allowfullscreen="" frameborder="0" data-s9e=""></iframe>',8=>true),
		'podbean'=>array('Podbean','http://www.podbean.com/',array('podcasts'=>1),"!podbean\\.com/site/player/index/pid/\\d+/eid/(?'id'\\d+)!\n!(?'id')podbean\\.com/e/!",array('!podbean\\.com/site/player/index/pid/\\d+/eid/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!embed/postId/(?\'id\'\\d+)!'),'match'=>array('!podbean\\.com/e/!'))),'<iframe width="100%" height="100" style="max-width:900px" src="//www.podbean.com/media/player/audio/postId/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',true),
		'prezi'=>array('Prezi','http://prezi.com/',array('presentations'=>1),'#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?\'id\'\\w+)/#',array('#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?\'id\'\\w+)/#'),7=>'<iframe width="550" height="400" src="//prezi.com/embed/{$id}/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'rdio'=>array('Rdio','http://www.rdio.com/',array('music'=>1),"!rd\\.io/./(?'id'\\w+)!\n!(?'id')rdio\\.com/.*?(?:playlist|track)!",array('!rd\\.io/./(?\'id\'\\w+)!'),true,array(array('extract'=>array('!rd\\.io/./(?\'id\'\\w+)!'),'match'=>array('!rdio\\.com/.*?(?:playlist|track)!'),'url'=>'http://www.rdio.com/api/oembed/?url={@url}')),'<iframe width="400" height="400" src="https://rd.io/i/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'reddit'=>array('Reddit comment permalink','http://www.reddit.com/',array('social'=>1),'!(?\'id\')(?=.*?[./]reddit\\.com[:/]).*?(?\'path\'/r/\\w+/comments/\\w+/\\w+/\\w+)!',array('!(?=.*?[./]reddit\\.com[:/]).*?(?\'path\'/r/\\w+/comments/\\w+/\\w+/\\w+)!'),true),
		'rutube'=>array('Rutube','http://rutube.ru/',array('.ru'=>1),"!rutube\\.ru/tracks/(?'id'\\d+)!\n!(?'id')rutube\\.ru/video/[0-9a-f]{32}!",array('!rutube\\.ru/tracks/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!rutube\\.ru/play/embed/(?\'id\'\\d+)!'),'match'=>array('!rutube\\.ru/video/[0-9a-f]{32}!'))),'<iframe width="720" height="405" src="//rutube.ru/play/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'scribd'=>array('Scribd','http://www.scribd.com/',array('documents'=>1,'presentations'=>1),'!scribd\\.com/doc/(?\'id\'\\d+)!',array('!scribd\\.com/doc/(?\'id\'\\d+)!'),7=>'<iframe width="100%" height="500" style="resize:both" src="//www.scribd.com/embeds/{$id}/content?view_mode=scroll&amp;show_recommendations=false" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',8=>true),
		'slideshare'=>array('SlideShare','http://www.slideshare.net/',array('presentations'=>1),"!slideshare\\.net/[^/]+/[-\\w]+-(?'id'\\d{6,})$!\n!(?'id')slideshare\\.net/[^/]+/\\w!",array('!slideshare\\.net/[^/]+/[-\\w]+-(?\'id\'\\d{6,})$!'),true,array(array('extract'=>array('!"presentationId":(?\'id\'\\d+)!'),'match'=>array('!slideshare\\.net/[^/]+/\\w!'))),'<iframe width="427" height="356" src="//www.slideshare.net/slideshow/embed_code/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'soundcloud'=>array('SoundCloud','https://soundcloud.com/',array('music'=>1),"@(?'id'https?://(?:(?:api\\.soundcloud\\.com/(?:playlist|track)s/\\d+)|soundcloud\\.com/[^/]+/(?:sets/)?[^/]+)(?:(?:\\?secret_token=|/(?=s-))(?'secret_token'[-\\w]+))?|^[^/]+/[^/]+$)@\n@(?'id')soundcloud\\.com/(?!playlists/|tracks/)[^/]+/(?:sets/)?[^/]+/s-@",array('@(?\'id\'https?://(?:(?:api\\.soundcloud\\.com/(?:playlist|track)s/\\d+)|soundcloud\\.com/[^/]+/(?:sets/)?[^/]+)(?:(?:\\?secret_token=|/(?=s-))(?\'secret_token\'[-\\w]+))?|^[^/]+/[^/]+$)@'),true,array(array('extract'=>array('@playlists/(?\'playlist_id\'\\d+)@','@tracks/(?\'track_id\'\\d+)@'),'match'=>array('@soundcloud\\.com/(?!playlists/|tracks/)[^/]+/(?:sets/)?[^/]+/s-@'),'url'=>'https://api.soundcloud.com/resolve?url={@id}&_status_code_map%5B302%5D=200&_status_format=json&client_id=b45b1aa10f1ac2941910a7f0d10f8e28&app_version=7a35847b')),null,true),
		'sportsnet'=>array('Sportsnet','http://www.sportsnet.ca/',array('.ca'=>1,'sports'=>1),'((?\'id\')sportsnet\\.ca/)',array(),true,array(array('extract'=>array('/vid(?:eoId)?=(?\'id\'\\d+)/','/param name="@videoPlayer" value="(?\'id\'\\d+)"/'),'match'=>array('//'))),'<iframe width="560" height="315" src="https://images.rogersdigitalmedia.com/video_service.php?videoId={$id}&amp;playerKey=AQ~~,AAAAAGWRwLc~,cRCmKE8Utf7OFWP38XQcokFZ80fR-u_y&amp;autoStart=false&amp;width=100%25&amp;height=100%25" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'spotify'=>array('Spotify','https://www.spotify.com/',array('music'=>1),"!(?'id')(?'uri'spotify:(?:album|artist|user|track(?:set)?):[,:\\w]+)!\n!(?'id')(?:open|play)\\.spotify\\.com/(?'path'(?:album|artist|track|user)/[/\\w]+)!",array('!(?\'uri\'spotify:(?:album|artist|user|track(?:set)?):[,:\\w]+)!','!(?:open|play)\\.spotify\\.com/(?\'path\'(?:album|artist|track|user)/[/\\w]+)!'),true),
		'strawpoll'=>array('Straw Poll','http://strawpoll.me/',array('misc'=>1),'!strawpoll\\.me/(?\'id\'\\d+)!',array('!strawpoll\\.me/(?\'id\'\\d+)!'),7=>'<iframe width="600" height="310" src="http://strawpoll.me/embed_1/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'streamable'=>array('Streamable','http://streamable.com/',array('videos'=>1),'!streamable\\.com/(?\'id\'\\w+)!',array('!streamable\\.com/(?\'id\'\\w+)!'),7=>'<iframe width="560" height="315" src="//streamable.com/e/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'teamcoco'=>array('Team Coco','http://teamcoco.com/',array('entertainment'=>1),"!teamcoco\\.com/video/(?'id'\\d+)!\n!(?'id')teamcoco\\.com/video/.!",array('!teamcoco\\.com/video/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!"id":(?\'id\'\\d+)!'),'match'=>array('!teamcoco\\.com/video/.!'))),'<iframe width="640" height="415" src="//teamcoco.com/embed/v/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'ted'=>array('TED Talks','http://www.ted.com/',array('presentations'=>1),'!ted\\.com/(?\'id\'(?:talk|playlist)s/[^\\s"?]+)!i',array('!ted\\.com/(?\'id\'(?:talk|playlist)s/[^\\s"?]+)!i')),
		'theatlantic'=>array('The Atlantic Video','http://www.theatlantic.com/video/',array('news'=>1),'!theatlantic\\.com/video/index/(?\'id\'\\d+)!',array('!theatlantic\\.com/video/index/(?\'id\'\\d+)!'),7=>'<iframe width="640" height="360" src="http://www.theatlantic.com/video/iframe/{$id}/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'theonion'=>array('The Onion','http://www.theonion.com/video/',array('entertainment'=>1),'!theonion\\.com/video/[-\\w]+,(?\'id\'\\d+)!',array('!theonion\\.com/video/[-\\w]+,(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="http://www.theonion.com/video_embed/?id={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'tinypic'=>array('TinyPic videos','http://tinypic.com/',array('images'=>1),'!tinypic\\.com/player\\.php\\?v=(?\'id\'\\w+)&s=(?\'s\'\\d+)!',array('!tinypic\\.com/player\\.php\\?v=(?\'id\'\\w+)&s=(?\'s\'\\d+)!'),true),
		'tmz'=>array('TMZ','http://www.tmz.com/videos',array('gossip'=>1),'@tmz\\.com/videos/(?\'id\'\\w+)@',array('@tmz\\.com/videos/(?\'id\'\\w+)@'),7=>'<iframe width="560" height="315" src="//www.kaltura.com/index.php/kwidget/cache_st/133592691/wid/_591531/partner_id/591531/uiconf_id/9071262/entry_id/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'traileraddict'=>array('Trailer Addict','http://www.traileraddict.com/',array('movies'=>1),'@(?\'id\')traileraddict\\.com/(?!tags/)[^/]+/.@',array(),true,array(array('extract'=>array('@v\\.traileraddict\\.com/(?\'id\'\\d+)@'),'match'=>array('@traileraddict\\.com/(?!tags/)[^/]+/.@'))),'<iframe width="560" height="315" src="//v.traileraddict.com/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'tumblr'=>array('Tumblr','https://www.tumblr.com/',array('social'=>1),'!(?\'name\'[-\\w]+)\\.tumblr\\.com/post/(?\'id\'\\d+)!',array('!(?\'name\'[-\\w]+)\\.tumblr\\.com/post/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!"embed_did":"(?\'did\'[-\\w]+)!','!"embed_key":"(?\'key\'[-\\w]+)!'),'match'=>array('//'),'url'=>'http://{@name}.tumblr.com/post/{@id}/embed'))),
		'twitch'=>array('Twitch','http://www.twitch.tv/',array('gaming'=>1),"#(?'id')twitch\\.tv/(?'channel'(?!m/)\\w+)(?:/b/(?'archive_id'\\d+)|/c/(?'chapter_id'\\d+)|/v/(?'video_id'\\d+))?#\n!(?'id')twitch\\.tv/m/\\d+!",array('#twitch\\.tv/(?\'channel\'(?!m/)\\w+)(?:/b/(?\'archive_id\'\\d+)|/c/(?\'chapter_id\'\\d+)|/v/(?\'video_id\'\\d+))?#'),true,array(array('extract'=>array('!channel=(?\'channel\'\\w+).*?videoId=a(?\'archive_id\'\\d+)!'),'match'=>array('!twitch\\.tv/m/\\d+!')))),
		'twitter'=>array('Twitter (via custom iframe)','https://twitter.com/',array('social'=>1),'@twitter\\.com/(?:#!/)?\\w+/status(?:es)?/(?\'id\'\\d+)@',array('@twitter\\.com/(?:#!/)?\\w+/status(?:es)?/(?\'id\'\\d+)@'),7=>'<iframe width="500" height="186" src="//s9e.github.io/iframe/twitter.min.html#{$id}" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>',8=>true),
		'ustream'=>array('Ustream','http://www.ustream.tv/',array('gaming'=>1),"!(?'id')ustream\\.tv/recorded/(?'vid'\\d+)!\n#(?'id')ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#",array('!ustream\\.tv/recorded/(?\'vid\'\\d+)!'),true,array(array('extract'=>array('!embed/(?\'cid\'\\d+)!'),'match'=>array('#ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#')))),
		'vbox7'=>array('VBOX7','http://vbox7.com/',array('.bg'=>1),'!vbox7\\.com/play:(?\'id\'[\\da-f]+)!',array('!vbox7\\.com/play:(?\'id\'[\\da-f]+)!'),7=>'<iframe width="560" height="315" src="http://vbox7.com/emb/external.php?vid={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'vevo'=>array('VEVO','http://vevo.com/',array('music'=>1),'!vevo\\.com/watch/([-\\w/]+/)?(?\'id\'[A-Z0-9]+)!',array('!vevo\\.com/watch/([-\\w/]+/)?(?\'id\'[A-Z0-9]+)!'),7=>'<iframe width="575" height="324" src="http://cache.vevo.com/m/html/embed.html?video={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'viagame'=>array('Viagame','http://www.viagame.com/',array('gaming'=>1),'!viagame\\.com/channels/[^/]+/(?\'id\'\\d+)!',array('!viagame\\.com/channels/[^/]+/(?\'id\'\\d+)!'),7=>'<iframe width="640" height="392" src="//www.viagame.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'videomega'=>array('Videomega','http://videomega.tv/',array('videos'=>1),'!videomega\\.tv/\\?ref=(?\'id\'\\w+)!',array('!videomega\\.tv/\\?ref=(?\'id\'\\w+)!'),7=>'<iframe width="560" height="315" src="http://videomega.tv/iframe.php?ref={$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'vidme'=>array('vidme','https://vid.me',array('videos'=>1),'!vid\\.me/(?\'id\'\\w+)!',array('!vid\\.me/(?\'id\'\\w+)!'),true,array(array('extract'=>array('!meta property="og:video:type" content="video/\\w+">\\s*<meta property="og:video:height" content="(?\'height\'\\d+)">\\s*<meta property="og:video:width" content="(?\'width\'\\d+)!'),'match'=>array('//')))),
		'vimeo'=>array('Vimeo','http://vimeo.com/',array('videos'=>1),'!vimeo\\.com/(?:channels/[^/]+/)?(?\'id\'\\d+)!',array('!vimeo\\.com/(?:channels/[^/]+/)?(?\'id\'\\d+)!'),7=>'<iframe width="560" height="315" src="//player.vimeo.com/video/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'vine'=>array('Vine','https://vine.co/',array('social'=>1,'videos'=>1),'!vine\\.co/v/(?\'id\'[^/]+)!',array('!vine\\.co/v/(?\'id\'[^/]+)!'),7=>'<iframe width="480" height="480" src="https://vine.co/v/{$id}/embed/simple" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe><script async="" src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>'),
		'vk'=>array('VK','https://vk.com/',array('.ru'=>1),"!(?'id')vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?'oid'-?\\d+)_(?'vid'\\d+)!\n!(?'id')vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?'oid'-?\\d+)&id=(?'vid'\\d+)&hash=(?'hash'[0-9a-f]+)!\n!(?'id')(?=.*?[./]vk(?>\\.com|ontakte\\.ru)[:/]).*?vk.*?video-?\\d+_\\d+!",array('!vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?\'oid\'-?\\d+)_(?\'vid\'\\d+)!','!vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?\'oid\'-?\\d+)&id=(?\'vid\'\\d+)&hash=(?\'hash\'[0-9a-f]+)!'),true,array(array('extract'=>array('!\\\\"hash2\\\\":\\\\"(?\'hash\'[0-9a-f]+)!'),'match'=>array('!vk.*?video-?\\d+_\\d+!'),'url'=>'http://vk.com/video{@oid}_{@vid}'))),
		'vocaroo'=>array('Vocaroo','http://vocaroo.com/',array('misc'=>1),'!vocaroo\\.com/i/(?\'id\'\\w+)!',array('!vocaroo\\.com/i/(?\'id\'\\w+)!'),7=>'<object type="application/x-shockwave-flash" typemustmatch="" width="148" height="44" data="//vocaroo.com/player.swf?playMediaID={$id}&amp;autoplay=0"><param name="allowfullscreen" value="true"/><embed type="application/x-shockwave-flash" width="148" height="44" src="//vocaroo.com/player.swf?playMediaID={$id}&amp;autoplay=0" allowfullscreen=""/></object>'),
		'wshh'=>array('WorldStarHipHop','http://www.worldstarhiphop.com/',array('videos'=>1),"!worldstarhiphop\\.com/featured/(?'id'\\d+)!\n!(?'id')worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!",array('!worldstarhiphop\\.com/featured/(?\'id\'\\d+)!'),true,array(array('extract'=>array('!disqus_identifier[ =\']+(?\'id\'\\d+)!'),'match'=>array('!worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!'))),'<iframe width="640" height="360" src="//www.worldstarhiphop.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'wsj'=>array('The Wall Street Journal Online','http://live.wsj.com/',array('news'=>1),"@live\\.wsj\\.com/[^#]*#!(?'id'[-0-9A-F]{36})@\n@live\\.wsj\\.com/video/[^/]+/(?'id'[-0-9A-F]{36})@",array('@live\\.wsj\\.com/[^#]*#!(?\'id\'[-0-9A-F]{36})@','@live\\.wsj\\.com/video/[^/]+/(?\'id\'[-0-9A-F]{36})@')),
		'xboxclips'=>array('XboxClips','http://xboxclips.com/',array('gaming'=>1),'!xboxclips\\.com/(?\'user\'[^/]+)/(?\'id\'[-0-9a-f]+)!',array('!xboxclips\\.com/(?\'user\'[^/]+)/(?\'id\'[-0-9a-f]+)!'),true),
		'xboxdvr'=>array('Xbox DVR','http://xboxdvr.com/',array('gaming'=>1),'!xboxdvr\\.com/(?\'user\'[^/]+)/(?\'id\'[-0-9a-f]+)!',array('!xboxdvr\\.com/(?\'user\'[^/]+)/(?\'id\'[-0-9a-f]+)!'),true),
		'yahooscreen'=>array('Yahoo! Screen','https://screen.yahoo.com/',array('movies'=>1),'!screen\\.yahoo\\.com/(?:[-\\w]+/)?(?\'id\'[-\\w]+)\\.html!',array('!screen\\.yahoo\\.com/(?:[-\\w]+/)?(?\'id\'[-\\w]+)\\.html!'),7=>'<iframe width="640" height="360" src="https://screen.yahoo.com/{$id}.html?format=embed" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'youku'=>array('Youku','http://www.youku.com/',array('.cn'=>1),'!youku\\.com/v_show/id_(?\'id\'\\w+)!',array('!youku\\.com/v_show/id_(?\'id\'\\w+)!'),7=>'<iframe width="512" height="328" src="http://player.youku.com/embed/{$id}" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>'),
		'youtube'=>array('YouTube','http://www.youtube.com/',array('videos'=>1),"!youtube\\.com/(?:watch.*?v=|v/)(?'id'[-\\w]+)!\n!youtu\\.be/(?'id'[-\\w]+)!\n!(?'id')(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?[#&?]t=(?:(?:(?'h'\\d+)h)?(?'m'\\d+)m(?'s'\\d+)|(?'t'\\d+))!\n!(?'id')(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?&list=(?'list'[-\\w]+)!",array('!youtube\\.com/(?:watch.*?v=|v/)(?\'id\'[-\\w]+)!','!youtu\\.be/(?\'id\'[-\\w]+)!','!(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?[#&?]t=(?:(?:(?\'h\'\\d+)h)?(?\'m\'\\d+)m(?\'s\'\\d+)|(?\'t\'\\d+))!','!(?=.*?[./]youtu(?>\\.be|be\\.com)[:/]).*?&list=(?\'list\'[-\\w]+)!'),true),
		'zippyshare'=>array('Zippyshare audio files','http://www.zippyshare.com/',array('file sharing'=>1),'!(?\'id\')(?=.*?[./]zippyshare\\.com[:/]).*?/v/!',array(),true,array(array('extract'=>array('!file=(?\'file\'\\w+)&amp;server=(?\'server\'\\d+)!'),'match'=>array('!/v/!'))),null,true)
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
		self::upgradeTags();
	}

	/**
	* Upgrade the list of enabled tags
	*
	* Automatically enables new tags
	*
	* @return void
	*/
	protected static function upgradeTags()
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
			$embedHtml = $config[self::KEY_HTML];
			if (self::$maxResponsiveWidth && empty($config[self::KEY_UNRESPONSIVE]))
			{
				$embedHtml = self::customiseDimensions($embedHtml);
			}
		}
		else
		{
			$embedHtml = '<!-- see callback -->';
			$site['embed_html_callback_class']  = __CLASS__;
			$site['embed_html_callback_method'] = 'embed';
		}

		$site->addChild('embed_html', htmlspecialchars($embedHtml));
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
	* Parse a text to capture the list of excluded sites
	*
	* @param  string $text Comma-separated list of excluded sites (ID or name)
	* @return void
	*/
	protected static function parseExcludedSites($text)
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
		$text = implode(',', $siteIds);
		self::$excludedSites = $text;
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

		if (self::$maxResponsiveWidth && empty(self::$sites[$siteId][self::KEY_UNRESPONSIVE]))
		{
			$html = self::customiseDimensions($html);
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

		$page = @file_get_contents(
			'compress.zlib://' . $url,
			false,
			stream_context_create(array(
				'http' => array('header' => 'Accept-Encoding: gzip'),
				'ssl'  => array('verify_peer' => false)
			))
		);

		if ($page && isset($cacheFile))
		{
			file_put_contents($cacheFile, gzencode($page, 9));
		}

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
	* Extract absolute dimensions of from HTML code
	*
	* @param  string  $html Original code
	* @return string        Modified code
	*/
	protected static function customiseDimensions($html)
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

	public static function renderAmazon($vars)
	{
		$vars += array('id' => null, 'tld' => null);

		$html='<iframe width="120" height="240" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//rcm';if(isset($vars['tld'])&&(strpos('cadefritjpuk',$vars['tld'])!==false))$html.='-'.htmlspecialchars($vars['tld'],2);$html.='.amazon.';if($vars['tld']==='jp'||$vars['tld']==='uk')$html.='co.'.htmlspecialchars($vars['tld'],2);elseif(isset($vars['tld'])&&(strpos('cadefrit',$vars['tld'])!==false))$html.=htmlspecialchars($vars['tld'],2);else$html.='com';$html.='/e/cm?lt1=_blank&amp;bc1=FFFFFF&amp;bg1=FFFFFF&amp;fc1=000000&amp;lc1=0000FF&amp;p=8&amp;l=as1&amp;f=ifr&amp;asins='.htmlspecialchars($vars['id'],2).'&amp;o=';if($vars['tld']==='ca')$html.='15';elseif($vars['tld']==='de')$html.='3';elseif($vars['tld']==='fr')$html.='8';elseif($vars['tld']==='it')$html.='29';elseif($vars['tld']==='jp')$html.='9';elseif($vars['tld']==='uk')$html.='2';else$html.='1';$html.='&amp;t=';if(!empty(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG))$html.=htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG,2);else$html.='_';$html.='"></iframe>';

		return $html;
	}

	public static function renderAudiomack($vars)
	{
		$vars += array('id' => null, 'mode' => null);

		$html='<iframe width="100%" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" height="';if($vars['mode']==='album')$html.='340';else$html.='110';$html.='" src="//www.audiomack.com/embed4';if($vars['mode']==='album')$html.='-album';$html.='/'.htmlspecialchars($vars['id'],2).'"></iframe>';

		return $html;
	}

	public static function renderBandcamp($vars)
	{
		$vars += array('album_id' => null, 'track_id' => null, 'track_num' => null);

		$html='<iframe width="400" height="400" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/';if(isset($vars['album_id'])){$html.='album='.htmlspecialchars($vars['album_id'],2);if(isset($vars['track_num']))$html.='/t='.htmlspecialchars($vars['track_num'],2);}else$html.='track='.htmlspecialchars($vars['track_id'],2);$html.='"></iframe>';

		return $html;
	}

	public static function renderBbcnews($vars)
	{
		$vars += array('ad_site' => null, 'playlist' => null, 'poster' => null);

		$html='<iframe width="560" height="315" src="https://ssl.bbc.co.uk/wwscripts/embed_player#playlist='.htmlspecialchars($vars['playlist'],2).'&amp;poster='.htmlspecialchars($vars['poster'],2).'&amp;ad_site='.htmlspecialchars($vars['ad_site'],2).'&amp;ad_keyword=&amp;source=twitter" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderCbsnews($vars)
	{
		$vars += array('id' => null, 'pid' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="425" height="279" data="';if(isset($vars['pid']))$html.='http://www.cbsnews.com/common/video/cbsnews_player.swf';else$html.='http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf';$html.='"><param name="allowfullscreen" value="true"><param name="flashvars" value="';if(isset($vars['pid']))$html.='pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2);else$html.='si=254&amp;contentValue='.htmlspecialchars($vars['id'],2);$html.='"><embed type="application/x-shockwave-flash" width="425" height="279" allowfullscreen="" src="';if(isset($vars['pid']))$html.='http://www.cbsnews.com/common/video/cbsnews_player.swf';else$html.='http://i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf';$html.='" flashvars="';if(isset($vars['pid']))$html.='pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2);else$html.='si=254&amp;contentValue='.htmlspecialchars($vars['id'],2);$html.='"></object>';

		return $html;
	}

	public static function renderColbertnation($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderComedycentral($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderDailyshow($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderDumpert($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="560" height="315" src="http://www.dumpert.nl/embed/'.htmlspecialchars(strtr($vars['id'],'_','/'),2).'/" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderEspn($vars)
	{
		$vars += array('cms' => null, 'id' => null);

		$html='<iframe width="560" height="315" src="https://espn.go.com/video/iframe/twitter/?cms='.htmlspecialchars($vars['cms'],2).'&amp;id='.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderGametrailers($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderGetty($vars)
	{
		$vars += array('et' => null, 'height' => null, 'id' => null, 'sig' => null, 'width' => null);

		$html='<iframe width="'.htmlspecialchars($vars['width'],2).'" height="'.htmlspecialchars(49+$vars['height'],2).'" src="//embed.gettyimages.com/embed/'.htmlspecialchars($vars['id'],2).'?et='.htmlspecialchars($vars['et'],2).'&amp;similar=on&amp;sig='.htmlspecialchars($vars['sig'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderGfycat($vars)
	{
		$vars += array('height' => 315, 'id' => null, 'width' => 560);

		$html='<iframe width="'.htmlspecialchars($vars['width'],2).'" height="'.htmlspecialchars($vars['height'],2).'" src="//gfycat.com/iframe/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderGoogleplus($vars)
	{
		$vars += array('name' => null, 'oid' => null, 'pid' => null);

		$html='<iframe width="450" height="240" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//s9e.github.io/iframe/googleplus.min.html#';if(isset($vars['oid']))$html.=htmlspecialchars($vars['oid'],2);else$html.='+'.htmlspecialchars($vars['name'],2);$html.='/posts/'.htmlspecialchars($vars['pid'],2).'"></iframe>';

		return $html;
	}

	public static function renderGooglesheets($vars)
	{
		$vars += array('gid' => null, 'id' => null);

		$html='<iframe width="100%" height="500" style="resize:both" src="https://docs.google.com/spreadsheet/ccc?key='.htmlspecialchars($vars['id'],2).'&amp;widget=true&amp;headers=false&amp;rm=minimal#gid='.htmlspecialchars($vars['gid'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderGrooveshark($vars)
	{
		$vars += array('playlistid' => null, 'songid' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="400" height="'.htmlspecialchars((isset($vars['songid'])?40:400),2).'" data="//grooveshark.com/'.htmlspecialchars((isset($vars['songid'])?'songW':'w'),2).'idget.swf"><param name="allowfullscreen" value="true"><param name="flashvars" value="playlistID='.htmlspecialchars($vars['playlistid'],2).'&amp;songID='.htmlspecialchars($vars['songid'],2).'"><embed type="application/x-shockwave-flash" width="400" height="'.htmlspecialchars((isset($vars['songid'])?40:400),2).'" src="//grooveshark.com/'.htmlspecialchars((isset($vars['songid'])?'songW':'w'),2).'idget.swf" allowfullscreen="" flashvars="playlistID='.htmlspecialchars($vars['playlistid'],2).'&amp;songID='.htmlspecialchars($vars['songid'],2).'"></object>';

		return $html;
	}

	public static function renderHudl($vars)
	{
		$vars += array('athlete' => null, 'highlight' => null);

		$html='<iframe width="640" height="360" src="//www.hudl.com/embed/athlete/'.htmlspecialchars($vars['athlete'],2).'/highlights/'.htmlspecialchars($vars['highlight'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderImgur($vars)
	{
		$vars += array('height' => null, 'id' => null, 'type' => null, 'width' => null);

		$html='<iframe allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" width="';if($vars['type']==='gifv'&&isset($vars['width']))$html.=htmlspecialchars($vars['width'],2);else$html.='100%';$html.='" height="';if($vars['type']==='gifv'&&isset($vars['height']))$html.=htmlspecialchars($vars['height'],2);else$html.='550';$html.='" src="';if($vars['type']==='gifv')$html.='//i.imgur.com/'.htmlspecialchars($vars['id'],2).'.gifv#embed';elseif((strpos($vars['id'],'a/')===0))$html.='//imgur.com/'.htmlspecialchars($vars['id'],2).'/embed';elseif($vars['type']==='album')$html.='//imgur.com/a/'.htmlspecialchars($vars['id'],2).'/embed';else$html.='//s9e.github.io/iframe/imgur.min.html#'.htmlspecialchars($vars['id'],2);$html.='" onload="';if(!($vars['type']==='gifv'||$vars['type']==='album'))$html.='var id=Math.random();window.addEventListener(\'message\',function(a){a.data.id==id&amp;&amp;(style.height=a.data.height+\'px\',style.width=a.data.width+\'px\')});var origin=src.substr(0,src.indexOf(\'/\',8));contentWindow.postMessage(\'s9e:\'+id,origin);';$html.='"></iframe>';

		return $html;
	}

	public static function renderInternetarchive($vars)
	{
		$vars += array('height' => null, 'id' => null, 'width' => null);

		$html='<iframe width="'.htmlspecialchars($vars['width'],2).'" height="'.htmlspecialchars($vars['height'],2).'" src="https://archive.org/embed/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderKickstarter($vars)
	{
		$vars += array('id' => null, 'video' => null);

		$html='<iframe allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" width="';if(isset($vars['video']))$html.='480';else$html.='220';$html.='" height="';if(isset($vars['video']))$html.='360';else$html.='420';$html.='" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/';if(isset($vars['video']))$html.='video';else$html.='card';$html.='.html"></iframe>';

		return $html;
	}

	public static function renderLivestream($vars)
	{
		$vars += array('account_id' => null, 'event_id' => null, 'video_id' => null);

		$html='<iframe width="640" height="360" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//new.livestream.com/accounts/'.htmlspecialchars($vars['account_id'],2).'/events/'.htmlspecialchars($vars['event_id'],2);if(isset($vars['video_id']))$html.='/videos/'.htmlspecialchars($vars['video_id'],2);$html.='/player?autoPlay=false"></iframe>';

		return $html;
	}

	public static function renderMailru($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="560" height="342" src="http://videoapi.my.mail.ru/videos/embed/'.htmlspecialchars($vars['id'],2).'.html" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderNpr($vars)
	{
		$vars += array('i' => null, 'm' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="400" height="386" data="//www.npr.org/v2/?i='.htmlspecialchars($vars['i'],2).'&amp;m='.htmlspecialchars($vars['m'],2).'" base="//www.npr.org"><param name="allowfullscreen" value="true"><embed type="application/x-shockwave-flash" width="400" height="386" base="//www.npr.org" src="//www.npr.org/v2/?i='.htmlspecialchars($vars['i'],2).'&amp;m='.htmlspecialchars($vars['m'],2).'" allowfullscreen=""></object>';

		return $html;
	}

	public static function renderReddit($vars)
	{
		$vars += array('path' => null);

		$html='<iframe width="800" height="165" src="//s9e.github.io/iframe/reddit.min.html#'.htmlspecialchars($vars['path'],2).'" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderSoundcloud($vars)
	{
		$vars += array('id' => null, 'playlist_id' => null, 'secret_token' => null, 'track_id' => null);

		$html='<iframe width="100%" height="166" style="max-width:900px" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="https://w.soundcloud.com/player/?url=';if(isset($vars['secret_token'])&&isset($vars['playlist_id']))$html.='https://api.soundcloud.com/playlists/'.htmlspecialchars($vars['playlist_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);elseif(isset($vars['secret_token'])&&isset($vars['track_id']))$html.='https://api.soundcloud.com/tracks/'.htmlspecialchars($vars['track_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);else{if((strpos($vars['id'],'://')===false))$html.='https://soundcloud.com/';$html.=htmlspecialchars($vars['id'],2);if(isset($vars['secret_token']))$html.='&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);}$html.='"></iframe>';

		return $html;
	}

	public static function renderSpotify($vars)
	{
		$vars += array('path' => null, 'uri' => null);

		$html='<iframe width="400" height="480" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="https://embed.spotify.com/?view=coverart&amp;uri=';if(isset($vars['uri']))$html.=htmlspecialchars($vars['uri'],2);else$html.='spotify:'.htmlspecialchars(strtr($vars['path'],'/',':'),2);$html.='"></iframe>';

		return $html;
	}

	public static function renderTed($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//embed.ted.com/'.htmlspecialchars($vars['id'],2);if((strpos($vars['id'],'.html')===false))$html.='.html';$html.='"></iframe>';

		return $html;
	}

	public static function renderTinypic($vars)
	{
		$vars += array('id' => null, 's' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="560" height="345" data="http://tinypic.com/player.swf?file='.htmlspecialchars($vars['id'],2).'&amp;s='.htmlspecialchars($vars['s'],2).'"><param name="allowfullscreen" value="true"><embed type="application/x-shockwave-flash" width="560" height="345" src="http://tinypic.com/player.swf?file='.htmlspecialchars($vars['id'],2).'&amp;s='.htmlspecialchars($vars['s'],2).'" allowfullscreen=""></object>';

		return $html;
	}

	public static function renderTumblr($vars)
	{
		$vars += array('id' => null, 'key' => null);

		$html='<iframe width="520" height="180" src="//s9e.github.io/iframe/tumblr.min.html#'.htmlspecialchars($vars['key'],2).'/'.htmlspecialchars($vars['id'],2).'" onload="var a=Math.random();window.addEventListener(\'message\',function(b){if(b.data.id==a)style.height=b.data.height+\'px\'});contentWindow.postMessage(\'s9e:\'+a,src.substr(0,src.indexOf(\'/\',8)))" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderTwitch($vars)
	{
		$vars += array('archive_id' => null, 'channel' => null, 'chapter_id' => null, 'video_id' => null);

		$html='<iframe width="620" height="378" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//s9e.github.io/iframe/twitch.min.html#channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['archive_id']))$html.='&amp;videoId=a'.htmlspecialchars($vars['archive_id'],2);elseif(isset($vars['chapter_id']))$html.='&amp;videoId=c'.htmlspecialchars($vars['chapter_id'],2);elseif(isset($vars['video_id']))$html.='&amp;videoId=v'.htmlspecialchars($vars['video_id'],2);$html.='"></iframe>';

		return $html;
	}

	public static function renderUstream($vars)
	{
		$vars += array('cid' => null, 'vid' => null);

		$html='<iframe width="480" height="302" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//www.ustream.tv/embed/';if(isset($vars['vid']))$html.='recorded/'.htmlspecialchars($vars['vid'],2);else$html.=htmlspecialchars($vars['cid'],2);$html.='"></iframe>';

		return $html;
	}

	public static function renderVidme($vars)
	{
		$vars += array('height' => null, 'id' => null, 'width' => null);

		$html='<iframe width="'.htmlspecialchars($vars['width'],2).'" height="'.htmlspecialchars($vars['height'],2).'" src="https://vid.me/e/'.htmlspecialchars($vars['id'],2).'" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderVk($vars)
	{
		$vars += array('hash' => null, 'oid' => null, 'vid' => null);

		$html='<iframe width="607" height="360" src="//vk.com/video_ext.php?oid='.htmlspecialchars($vars['oid'],2).'&amp;id='.htmlspecialchars($vars['vid'],2).'&amp;hash='.htmlspecialchars($vars['hash'],2).'&amp;hd=1" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderWsj($vars)
	{
		$vars += array('id' => null);

		$html='<iframe width="512" height="288" src="http://live.wsj.com/public/page/embed-'.htmlspecialchars(strtr($vars['id'],'-','_'),2).'.html" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderXboxclips($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<iframe width="560" height="315" src="//xboxclips.com/'.htmlspecialchars($vars['user'],2).'/'.htmlspecialchars($vars['id'],2).'/embed" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderXboxdvr($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<iframe width="560" height="430" src="//xboxdvr.com/'.htmlspecialchars($vars['user'],2).'/'.htmlspecialchars($vars['id'],2).'/embed" allowfullscreen="" frameborder="0" scrolling="no" data-s9e=""></iframe>';

		return $html;
	}

	public static function renderYoutube($vars)
	{
		$vars += array('h' => null, 'id' => null, 'list' => null, 'm' => null, 's' => null, 't' => null);

		$html='<iframe width="560" height="315" allowfullscreen="" frameborder="0" scrolling="no" data-s9e="" src="//www.youtube.com/embed/'.htmlspecialchars($vars['id'],2);if(isset($vars['list']))$html.='?list='.htmlspecialchars($vars['list'],2);if(isset($vars['t'])||isset($vars['m'])){if(isset($vars['list']))$html.='&amp;';else$html.='?';$html.='start=';if(isset($vars['t']))$html.=htmlspecialchars($vars['t'],2);elseif(isset($vars['h']))$html.=htmlspecialchars($vars['h']*3600+$vars['m']*60+$vars['s'],2);else$html.=htmlspecialchars($vars['m']*60+$vars['s'],2);}$html.='"></iframe>';

		return $html;
	}

	public static function renderZippyshare($vars)
	{
		$vars += array('file' => null, 'server' => null);

		$html='<object type="application/x-shockwave-flash" typemustmatch="" width="100%" height="80" data="//api.zippyshare.com/api/player.swf" style="max-width:900px"><param name="allowfullscreen" value="true"><param name="flashvars" value="file='.htmlspecialchars($vars['file'],2).'&amp;server='.htmlspecialchars($vars['server'],2).'&amp;autostart=false"><embed type="application/x-shockwave-flash" width="100%" height="80" style="max-width:900px" src="//api.zippyshare.com/api/player.swf" allowfullscreen="" flashvars="file='.htmlspecialchars($vars['file'],2).'&amp;server='.htmlspecialchars($vars['server'],2).'&amp;autostart=false"></object>';

		return $html;
	}
}