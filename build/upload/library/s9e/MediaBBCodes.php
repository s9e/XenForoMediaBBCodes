<?php

/**
* @copyright Copyright (c) 2013-2019 The s9e Authors
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
	const KEY_FILTERS = 8;

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
	* @var array Associative array using site IDs as keys, sites' config arrays as values
	*/
	public static $sites = array(
		'abcnews'=>array('ABC News','https://abcnews.go.com/',array('news'=>1),'!abcnews\\.go\\.com/(?:video/embed\\?id=|[^/]+/video/[^/]+-)(?P<id>\\d+)!',array('!abcnews\\.go\\.com/(?:video/embed\\?id=|[^/]+/video/[^/]+-)(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="abcnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//abcnews.go.com/video/embed?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'amazon'=>array('Amazon Product','https://affiliate-program.amazon.com/',array('misc'=>1),"#(?=.*?[./]amazon\\.(?:c(?:a|o(?:\\.(?:jp|uk)|m))|de|es|fr|i[nt])[:/]).*?/(?:dp|gp/product)/(?P<id>[A-Z0-9]+)#\n#(?P<id>)(?=.*?[./]amazon\\.(?:c(?:a|o(?:\\.(?:jp|uk)|m))|de|es|fr|i[nt])[:/]).*?amazon\\.(?:co\\.)?(?P<tld>ca|de|es|fr|in|it|jp|uk)#",array('#(?=.*?[./]amazon\\.(?:c(?:a|o(?:\\.(?:jp|uk)|m))|de|es|fr|i[nt])[:/]).*?/(?:dp|gp/product)/(?P<id>[A-Z0-9]+)#','#(?=.*?[./]amazon\\.(?:c(?:a|o(?:\\.(?:jp|uk)|m))|de|es|fr|i[nt])[:/]).*?amazon\\.(?:co\\.)?(?P<tld>ca|de|es|fr|in|it|jp|uk)#'),true),
		'anchor'=>array('Anchor','https://anchor.fm/',array('podcasts'=>1),'@(?=.*?[./]anchor\\.fm[:/]).*?anchor.fm/[-\\w]+/episodes/(?:[-\\w]+-)(?P<id>\\w+)(?![-\\w])@',array('@(?=.*?[./]anchor\\.fm[:/]).*?anchor.fm/[-\\w]+/episodes/(?:[-\\w]+-)(?P<id>\\w+)(?![-\\w])@'),7=>'<iframe data-s9e-mediaembed="anchor" allowfullscreen="" scrolling="no" src="//anchor.fm/embed/episodes/x-{$id}" style="border:0;height:102px;max-width:700px;width:100%"></iframe>'),
		'audioboom'=>array('Audioboom','https://audioboom.com/',array('podcasts'=>1),'!audioboo(?:\\.f|m\\.co)m/(?:boo|post)s/(?P<id>\\d+)!',array('!audioboo(?:\\.f|m\\.co)m/(?:boo|post)s/(?P<id>\\d+)!'),7=>'<iframe data-s9e-mediaembed="audioboom" allowfullscreen="" scrolling="no" src="//audioboom.com/posts/{$id}/embed/v3" style="border:0;height:150px;max-width:700px;width:100%"></iframe>'),
		'audiomack'=>array('Audiomack','https://www.audiomack.com/',array('music'=>1),'!audiomack\\.com/(?P<mode>album|song)/(?P<id>[-\\w]+/[-\\w]+)!',array('!audiomack\\.com/(?P<mode>album|song)/(?P<id>[-\\w]+/[-\\w]+)!'),true),
		'bandcamp'=>array('Bandcamp','https://bandcamp.com/',array('music'=>1),"!(?P<id>)bandcamp\\.com/album/.!\n!(?P<id>)bandcamp\\.com/track/.!",array(),true,array(array('extract'=>array('!/album=(?P<album_id>\\d+)!'),'match'=>array('!bandcamp\\.com/album/.!')),array('extract'=>array('!"album_id":(?P<album_id>\\d+)!','!"track_num":(?P<track_num>\\d+)!','!/track=(?P<track_id>\\d+)!'),'match'=>array('!bandcamp\\.com/track/.!')))),
		'bbcnews'=>array('BBC News','https://www.bbc.com/news/video_and_audio/headlines/',array('news'=>1),"@(?=.*?[./]bbc\\.co(?:\\.uk|m)[:/]).*?bbc\\.co(?:m|\\.uk)/news/av/embed/(?P<id>[-\\\\\\w/]+)@\n@(?P<id>)(?=.*?[./]bbc\\.co(?:\\.uk|m)[:/]).*?bbc\\.co(?:m|\\.uk)/news/(?:av(?!/embed)|video_and_audio)/\\w@",array('@(?=.*?[./]bbc\\.co(?:\\.uk|m)[:/]).*?bbc\\.co(?:m|\\.uk)/news/av/embed/(?P<id>[-\\\\\\w/]+)@'),true,array(array('extract'=>array('@bbc\\.co(?:m|\\.uk)\\\\?/news\\\\?/av\\\\?/embed\\\\?/(?P<id>[-\\\\\\w/]+)@'),'match'=>array('@bbc\\.co(?:m|\\.uk)/news/(?:av(?!/embed)|video_and_audio)/\\w@'))),null,array('id'=>array('stripslashes'))),
		'bitchute'=>array('BitChute','https://www.bitchute.com/',array('videos'=>1),'@bitchute\\.com/video/(?P<id>\\w+)@',array('@bitchute\\.com/video/(?P<id>\\w+)@'),7=>'<span data-s9e-mediaembed="bitchute" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.bitchute.com/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'bleacherreport'=>array('Bleacher Report videos','https://bleacherreport.com/videos',array('sports'=>1),'!(?P<id>)(?=.*?[./]bleacherreport\\.com[:/]).*?/articles/.!',array(),true,array(array('extract'=>array('!id="video-(?P<id>[-\\w]+)!','!video_embed\\?id=(?P<id>[-\\w]+)!'),'match'=>array('!/articles/.!'))),'<span data-s9e-mediaembed="bleacherreport" style="display:inline-block;width:100%;max-width:320px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//bleacherreport.com/video_embed?id={$id}&amp;library=video-cms" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'break'=>array('Break','https://www.break.com/',array('entertainment'=>1),'!break\\.com/video/.*-(?P<id>\\d+)$!',array('!break\\.com/video/.*-(?P<id>\\d+)$!'),7=>'<span data-s9e-mediaembed="break" style="display:inline-block;width:100%;max-width:464px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:60.344828%"><iframe allowfullscreen="" scrolling="no" src="//break.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'brightcove'=>array('Brightcove','https://www.brightcove.com/',array('videos'=>1),"@(?P<id>)link\\.brightcove\\.com/services/player/bcpid(?P<bcpid>\\d+).*?bckey=(?P<bckey>[-,~\\w]+).*?bctid=(?P<bctid>\\d+)@\n@(?P<id>)players\\.brightcove\\.net/(?P<bcpid>\\d+)/.*?videoId=(?P<bctid>\\d+)@",array('@link\\.brightcove\\.com/services/player/bcpid(?P<bcpid>\\d+).*?bckey=(?P<bckey>[-,~\\w]+).*?bctid=(?P<bctid>\\d+)@','@players\\.brightcove\\.net/(?P<bcpid>\\d+)/.*?videoId=(?P<bctid>\\d+)@'),true),
		'cbsnews'=>array('CBS News Video','https://www.cbsnews.com/video/',array('news'=>1),"#cbsnews\\.com/videos?/(?!watch/)(?P<id>[-\\w]+)#\n#cbsnews\\.com/video/watch/\\?id=(?P<id>\\d+)#",array('#cbsnews\\.com/videos?/(?!watch/)(?P<id>[-\\w]+)#','#cbsnews\\.com/video/watch/\\?id=(?P<id>\\d+)#')),
		'cnbc'=>array('CNBC','https://www.cnbc.com/video/',array('news'=>1),"!cnbc\\.com/gallery/\\?video=(?P<id>\\d+)!\n!(?P<id>)cnbc\\.com/video/20\\d\\d/\\d\\d/\\d\\d/\\w!",array('!cnbc\\.com/gallery/\\?video=(?P<id>\\d+)!'),true,array(array('extract'=>array('!byGuid=(?P<id>\\d+)!'),'match'=>array('!cnbc\\.com/video/20\\d\\d/\\d\\d/\\d\\d/\\w!'))),'<span data-s9e-mediaembed="cnbc" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://player.cnbc.com/p/gZWlPC/cnbc_global?playertype=synd&amp;byGuid={$id}&amp;size=640_360" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'cnn'=>array('CNN','https://edition.cnn.com/video/',array('news'=>1),"!(?=.*?[./]cnn\\.com[:/]).*?cnn.com/videos/(?P<id>.*\\.cnn)!\n!cnn\\.com/video/data/2\\.0/video/(?P<id>.*\\.cnn)!",array('!(?=.*?[./]cnn\\.com[:/]).*?cnn.com/videos/(?P<id>.*\\.cnn)!','!cnn\\.com/video/data/2\\.0/video/(?P<id>.*\\.cnn)!')),
		'cnnmoney'=>array('CNNMoney','https://money.cnn.com/video/',array('news'=>1),'!money\\.cnn\\.com/video/(?P<id>.*\\.cnnmoney)!',array('!money\\.cnn\\.com/video/(?P<id>.*\\.cnnmoney)!'),7=>'<span data-s9e-mediaembed="cnnmoney" style="display:inline-block;width:100%;max-width:560px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:64.285714%"><iframe allowfullscreen="" scrolling="no" src="//money.cnn.com/.element/ssi/video/7.0/players/embed.player.html?videoid=video/{$id}&amp;width=560&amp;height=360" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'comedycentral'=>array('Comedy Central','https://www.cc.com/',array('entertainment'=>1),'!(?P<id>)c(?:c|omedycentral)\\.com/(?:full-episode|video-clip)s/!',array(),true,array(array('extract'=>array('!(?P<id>mgid:arc:(?:episode|video):[.\\w]+:[-\\w]+)!'),'match'=>array('!c(?:c|omedycentral)\\.com/(?:full-episode|video-clip)s/!')))),
		'coub'=>array('Coub','https://coub.com/',array('videos'=>1),'!coub\\.com/view/(?P<id>\\w+)!',array('!coub\\.com/view/(?P<id>\\w+)!'),7=>'<span data-s9e-mediaembed="coub" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//coub.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'dailymotion'=>array('Dailymotion','https://www.dailymotion.com/',array('videos'=>1),"!dai\\.ly/(?P<id>[a-z0-9]+)!i\n!dailymotion\\.com/(?:live/|swf/|user/[^#]+#video=|(?:related/\\d+/)?video/)(?P<id>[a-z0-9]+)!i\n!(?P<id>)(?=.*?[./]dai(?:\\.ly|lymotion\\.com)[:/]).*?start=(?P<t>\\d+)!",array('!dai\\.ly/(?P<id>[a-z0-9]+)!i','!dailymotion\\.com/(?:live/|swf/|user/[^#]+#video=|(?:related/\\d+/)?video/)(?P<id>[a-z0-9]+)!i','!(?=.*?[./]dai(?:\\.ly|lymotion\\.com)[:/]).*?start=(?P<t>\\d+)!'),true),
		'democracynow'=>array('Democracy Now!','https://www.democracynow.org/',array('misc'=>1),"!(?=.*?[./]democracynow\\.org[:/]).*?democracynow.org/(?:embed/)?(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)!\n!(?P<id>)m\\.democracynow\\.org/stories/\\d!",array('!(?=.*?[./]democracynow\\.org[:/]).*?democracynow.org/(?:embed/)?(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)!'),true,array(array('extract'=>array('!democracynow\\.org/(?P<id>(?:\\w+/)?\\d+/\\d+/\\d+(?:/\\w+)?)\' rel=\'canonical!'),'match'=>array('!m\\.democracynow\\.org/stories/\\d!')))),
		'dumpert'=>array('dumpert','https://www.dumpert.nl/',array('.nl'=>1,'entertainment'=>1),'!dumpert\\.nl/mediabase/(?P<id>\\d+[/_]\\w+)!',array('!dumpert\\.nl/mediabase/(?P<id>\\d+[/_]\\w+)!')),
		'eighttracks'=>array('8tracks','https://8tracks.com/',array('music'=>1),"!8tracks\\.com/[-\\w]+/(?P<id>\\d+)(?=#|$)!\n!(?P<id>)8tracks\\.com/[-\\w]+/\\D!",array('!8tracks\\.com/[-\\w]+/(?P<id>\\d+)(?=#|$)!'),true,array(array('extract'=>array('!eighttracks://mix/(?P<id>\\d+)!'),'match'=>array('!8tracks\\.com/[-\\w]+/\\D!'))),'<span data-s9e-mediaembed="eighttracks" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//8tracks.com/mixes/{$id}/player_v3_universal" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'espn'=>array('ESPN','https://www.espn.com/',array('sports'=>1),'#(?=.*?[./]espn\\.(?:go\\.)?com[:/]).*?video/(?:clip(?:\\?id=|/_/id/))?(?P<id>\\d+)#',array('#(?=.*?[./]espn\\.(?:go\\.)?com[:/]).*?video/(?:clip(?:\\?id=|/_/id/))?(?P<id>\\d+)#'),7=>'<span data-s9e-mediaembed="espn" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.espn.com/core/video/iframe?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'facebook'=>array('Facebook','https://www.facebook.com/',array('social'=>1),"@/(?!(?:apps|developers|graph)\\.)[-\\w.]*facebook\\.com/(?:[/\\w]+/permalink|(?!marketplace/|pages/|groups/).*?)(?:/|fbid=|\\?v=)(?P<id>\\d+)(?=$|[/?&#])@\n@(?P<id>)facebook\\.com/(?P<user>[.\\w]+)/(?P<type>post|video)s?/@\n@(?P<id>)facebook\\.com/video/(?P<type>post|video)\\.php@",array('@/(?!(?:apps|developers|graph)\\.)[-\\w.]*facebook\\.com/(?:[/\\w]+/permalink|(?!marketplace/|pages/|groups/).*?)(?:/|fbid=|\\?v=)(?P<id>\\d+)(?=$|[/?&#])@','@facebook\\.com/(?P<user>[.\\w]+)/(?P<type>post|video)s?/@','@facebook\\.com/video/(?P<type>post|video)\\.php@'),true),
		'flickr'=>array('Flickr','https://www.flickr.com/',array('images'=>1),"@flickr\\.com/photos/[^/]+/(?P<id>\\d+)@\n@flic\\.kr/(?!p/)[^/]+/(?P<id>\\d+)@\n@(?P<id>)flic\\.kr/p/(?P<short>\\w+)@",array('@flickr\\.com/photos/[^/]+/(?P<id>\\d+)@','@flic\\.kr/(?!p/)[^/]+/(?P<id>\\d+)@'),true,array(array('extract'=>array('@flickr\\.com/photos/[^/]+/(?P<id>\\d+)@'),'match'=>array('@flic\\.kr/p/(?P<short>\\w+)@'),'url'=>'https://www.flickr.com/photo.gne?rb=1&short={@short}')),'<span data-s9e-mediaembed="flickr" style="display:inline-block;width:100%;max-width:500px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="https://www.flickr.com/photos/_/{$id}/player/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'foxnews'=>array('Fox News','https://www.foxnews.com/',array('news'=>1),'!video\\.foxnews\\.com/v/(?P<id>\\d+)!',array('!video\\.foxnews\\.com/v/(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="foxnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video.foxnews.com/v/video-embed.html?video_id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'foxsports'=>array('FOX Sports','https://www.foxsports.com/video',array('sports'=>1),'@(?P<id>)(?=.*?[./]foxsports\\.com[:/]).*?/video/\\d@',array(),true,array(array('extract'=>array('@BKQ29B/(?P<id>\\w+)@'),'match'=>array('@/video/\\d@'))),'<span data-s9e-mediaembed="foxsports" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.foxfdm.com/sports/embed-iframe.html?videourl=http://link.theplatform.com/s/BKQ29B/{$id}?mbr=true&amp;assetTypes=Video&amp;feed=Fox%20Sports%20All&amp;autoplay=false&amp;policy=68249219&amp;sdk=PDK%205.7.15" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'funnyordie'=>array('Funny or Die','https://www.funnyordie.com/',array('entertainment'=>1),'!funnyordie\\.com/videos/(?P<id>[0-9a-f]+)!',array('!funnyordie\\.com/videos/(?P<id>[0-9a-f]+)!'),7=>'<span data-s9e-mediaembed="funnyordie" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.funnyordie.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'gamespot'=>array('Gamespot','https://www.gamespot.com/',array('gaming'=>1),'!gamespot\\.com.*?/(?:events|videos)/.*?-(?P<id>\\d+)/(?:[#?].*)?$!',array('!gamespot\\.com.*?/(?:events|videos)/.*?-(?P<id>\\d+)/(?:[#?].*)?$!'),7=>'<span data-s9e-mediaembed="gamespot" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.gamespot.com/videos/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'gametrailers'=>array('GameTrailers','https://www.gametrailers.com/',array('gaming'=>1),'!(?P<id>)gametrailers\\.com/(?:full-episode|review|video)s/!',array(),true,array(array('extract'=>array('!embed/(?P<id>\\d+)!'),'match'=>array('!gametrailers\\.com/(?:full-episode|review|video)s/!')))),
		'getty'=>array('Getty Images','https://www.gettyimages.com/',array('images'=>1),"!gty\\.im/(?P<id>\\d+)!\n!(?=.*?[./]g(?:ettyimages\\.(?:[bs]e|c(?:n|o(?:\\.(?:jp|uk)|m(?:\\.au)?))|d[ek]|es|fr|i[et]|nl|pt)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?P<id>\\d+)!\n!(?=.*?[./]g(?:ettyimages\\.(?:[bs]e|c(?:n|o(?:\\.(?:jp|uk)|m(?:\\.au)?))|d[ek]|es|fr|i[et]|nl|pt)|ty\\.im)[:/]).*?#[-\\w]*picture-id(?P<id>\\d+)$!",array('!gty\\.im/(?P<id>\\d+)!','!(?=.*?[./]g(?:ettyimages\\.(?:[bs]e|c(?:n|o(?:\\.(?:jp|uk)|m(?:\\.au)?))|d[ek]|es|fr|i[et]|nl|pt)|ty\\.im)[:/]).*?gettyimages\\.[.\\w]+/detail(?=/).*?/(?P<id>\\d+)!','!(?=.*?[./]g(?:ettyimages\\.(?:[bs]e|c(?:n|o(?:\\.(?:jp|uk)|m(?:\\.au)?))|d[ek]|es|fr|i[et]|nl|pt)|ty\\.im)[:/]).*?#[-\\w]*picture-id(?P<id>\\d+)$!'),true,array(array('extract'=>array('!"height":[ "]*(?P<height>\\d+)!','!"width":[ "]*(?P<width>\\d+)!','!\\bid[=:][\'"]?(?P<et>[-=\\w]+)!','!\\bsig[=:][\'"]?(?P<sig>[-=\\w]+)!'),'match'=>array('//'),'url'=>'http://embed.gettyimages.com/preview/{@id}')),null,array('height'=>array('s9e_MediaBBCodes::filterUint'),'width'=>array('s9e_MediaBBCodes::filterUint'))),
		'gfycat'=>array('Gfycat','https://gfycat.com/',array('images'=>1),"#gfycat\\.com/(?!gaming|reactions|stickers|gifs/tag)(?:gifs/detail/|ifr(?:ame)?/)?(?P<id>\\w+)#\n#(?P<id>)gfycat\\.com/(?!gaming|reactions|stickers|gifs/tag)(?:gifs/detail/|ifr(?:ame)?/)?[a-z]#",array('#gfycat\\.com/(?!gaming|reactions|stickers|gifs/tag)(?:gifs/detail/|ifr(?:ame)?/)?(?P<id>\\w+)#'),true,array(array('extract'=>array('!/ifr/(?P<id>\\w+)!'),'match'=>array('#gfycat\\.com/(?!gaming|reactions|stickers|gifs/tag)(?:gifs/detail/|ifr(?:ame)?/)?[a-z]#'),'url'=>'https://gfycat.com/ifr/{@id}'),array('extract'=>array('!"height":(?P<height>\\d+)!','!"width":(?P<width>\\d+)!'),'match'=>array('//'),'url'=>'https://api.gfycat.com/v1/oembed?url=https://gfycat.com/{@id}')),null,array('height'=>array('s9e_MediaBBCodes::filterUint'),'width'=>array('s9e_MediaBBCodes::filterUint'))),
		'gifs'=>array('Gifs.com','https://gifs.com/',array('images'=>1),'!gifs\\.com/(?:gif/)?(?P<id>\\w+)!',array('!gifs\\.com/(?:gif/)?(?P<id>\\w+)!'),true,array(array('extract'=>array('!meta property="og:image:width" content="(?P<width>\\d+)!','!meta property="og:image:height" content="(?P<height>\\d+)!'),'match'=>array('//'),'url'=>'https://gifs.com/gif/{@id}')),null,array('height'=>array('s9e_MediaBBCodes::filterUint'),'width'=>array('s9e_MediaBBCodes::filterUint'))),
		'gist'=>array('GitHub Gist','https://gist.github.com/',array('misc'=>1),'!gist\\.github\\.com/(?P<id>(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!',array('!gist\\.github\\.com/(?P<id>(?:\\w+/)?[\\da-f]+(?:/[\\da-f]+)?)!')),
		'globalnews'=>array('Global News','https://globalnews.ca/',array('.ca'=>1,'news'=>1),"!globalnews\\.ca/video/(?P<id>\\d+)!\n!(?P<id>)globalnews\\.ca/video/rd/!",array('!globalnews\\.ca/video/(?P<id>\\d+)!'),true,array(array('extract'=>array('!globalnews\\.ca/video/(?P<id>\\d+)!'),'match'=>array('!globalnews\\.ca/video/rd/!'))),'<span data-s9e-mediaembed="globalnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//globalnews.ca/video/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'gofundme'=>array('GoFundMe','https://www.gofundme.com/',array('fundraising'=>1),'@gofundme\\.com/(?P<id>\\w+)(?![^#?])@',array('@gofundme\\.com/(?P<id>\\w+)(?![^#?])@'),7=>'<span data-s9e-mediaembed="gofundme" style="display:inline-block;width:100%;max-width:349px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:118.911175%;padding-bottom:calc(59.312321% + 208px)"><iframe allowfullscreen="" scrolling="no" src="//www.gofundme.com/mvc.php?route=widgets/mediawidget&amp;fund={$id}&amp;image=1&amp;coinfo=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'googledrive'=>array('Google Drive','https://drive.google.com',array('documents'=>1,'images'=>1,'videos'=>1),'!drive\\.google\\.com/.*?(?:file/d/|id=)(?P<id>[-\\w]+)!',array('!drive\\.google\\.com/.*?(?:file/d/|id=)(?P<id>[-\\w]+)!'),7=>'<span data-s9e-mediaembed="googledrive" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:75%"><iframe allowfullscreen="" scrolling="no" src="//drive.google.com/file/d/{$id}/preview" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'googleplus'=>array('Google+','https://plus.google.com/',array('social'=>1),'!(?P<id>)//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?P<name>[^/]+)|(?P<oid>\\d+))/posts/(?P<pid>\\w+)!',array('!//plus\\.google\\.com/(?:u/\\d+/)?(?:\\+(?P<name>[^/]+)|(?P<oid>\\d+))/posts/(?P<pid>\\w+)!'),true,7=>null,8=>array('name'=>array('urldecode'))),
		'googlesheets'=>array('Google Sheets','https://www.google.com/sheets/about/',array('documents'=>1),"@docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|(?:[^e]/)+)(?P<id>(?:e/)?[-\\w]+)@\n@(?P<id>)(?=.*?[./]docs\\.google\\.com[:/]).*?oid=(?P<oid>\\d+)@\n@(?P<id>)(?=.*?[./]docs\\.google\\.com[:/]).*?#gid=(?P<gid>\\d+)@\n@(?P<id>)(?=.*?[./]docs\\.google\\.com[:/]).*?/pub(?P<type>chart)@",array('@docs\\.google\\.com/spreadsheet(?:/ccc\\?key=|(?:[^e]/)+)(?P<id>(?:e/)?[-\\w]+)@','@(?=.*?[./]docs\\.google\\.com[:/]).*?oid=(?P<oid>\\d+)@','@(?=.*?[./]docs\\.google\\.com[:/]).*?#gid=(?P<gid>\\d+)@','@(?=.*?[./]docs\\.google\\.com[:/]).*?/pub(?P<type>chart)@'),true),
		'hudl'=>array('Hudl','https://www.hudl.com/',array('sports'=>1),"!(?P<id>)hudl\\.com/athlete/(?P<athlete>\\d+)/highlights/(?P<highlight>[\\da-f]+)!\n!(?P<id>)hudl\\.com/video/\\d+/(?P<athlete>\\d+)/(?P<highlight>[\\da-f]+)!\n@hudl\\.com/video/(?P<id>\\w+)(?![\\w/])@\n!(?P<id>)hudl\\.com/v/!",array('!hudl\\.com/athlete/(?P<athlete>\\d+)/highlights/(?P<highlight>[\\da-f]+)!','!hudl\\.com/video/\\d+/(?P<athlete>\\d+)/(?P<highlight>[\\da-f]+)!','@hudl\\.com/video/(?P<id>\\w+)(?![\\w/])@'),true,array(array('extract'=>array('!hudl\\.com/video/\\d+/(?P<athlete>\\d+)/(?P<highlight>[\\da-f]+)!','@hudl\\.com/video/(?P<id>\\w+)(?![\\w/])@'),'match'=>array('!hudl\\.com/v/!')))),
		'hulu'=>array('Hulu','https://www.hulu.com/',array('misc'=>1),'!(?P<id>)hulu\\.com/watch/!',array(),true,array(array('extract'=>array('!eid=(?P<id>[-\\w]+)!'),'match'=>array('!hulu\\.com/watch/!'))),'<span data-s9e-mediaembed="hulu" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://secure.hulu.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'ign'=>array('IGN','https://www.ign.com/videos/',array('gaming'=>1),'!(?P<id>https?://.*?ign\\.com/videos/.+)!i',array('!(?P<id>https?://.*?ign\\.com/videos/.+)!i'),7=>'<span data-s9e-mediaembed="ign" style="display:inline-block;width:100%;max-width:468px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.196581%"><iframe allowfullscreen="" scrolling="no" src="//widgets.ign.com/video/embed/content.html?url={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'imdb'=>array('IMDb','https://www.imdb.com/',array('movies'=>1),'!imdb\\.com/[/\\w]+/vi(?P<id>\\d+)!',array('!imdb\\.com/[/\\w]+/vi(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="imdb" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.imdb.com/video/imdb/vi{$id}/imdb/embed?autoplay=false&amp;width=640" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'imgur'=>array('Imgur','https://imgur.com/',array('images'=>1),"@imgur\\.com/(?P<id>a/\\w+)@\n@i\\.imgur\\.com/(?P<id>\\w{5,7})[lms]?\\.@\n@imgur\\.com/(?P<id>\\w+)(?![\\w./])@\n@(?P<id>)imgur\\.com/(?![art]/|user/)(?P<path>(?:gallery/)?\\w+)(?![\\w.])@",array('@imgur\\.com/(?P<id>a/\\w+)@','@i\\.imgur\\.com/(?P<id>\\w{5,7})[lms]?\\.@','@imgur\\.com/(?P<id>\\w+)(?![\\w./])@'),true,array(array('extract'=>array('@data-id="(?P<id>[\\w/]+)"@'),'match'=>array('@imgur\\.com/(?![art]/|user/)(?P<path>(?:gallery/)?\\w+)(?![\\w.])@'),'url'=>'https://api.imgur.com/oembed.xml?url=/{@path}'))),
		'indiegogo'=>array('Indiegogo','https://www.indiegogo.com/',array('fundraising'=>1),'!indiegogo\\.com/projects/(?P<id>[-\\w]+)!',array('!indiegogo\\.com/projects/(?P<id>[-\\w]+)!'),7=>'<span data-s9e-mediaembed="indiegogo" style="display:inline-block;width:100%;max-width:222px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200.45045%"><iframe allowfullscreen="" scrolling="no" src="//www.indiegogo.com/project/{$id}/embedded" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'instagram'=>array('Instagram','https://instagram.com/',array('social'=>1),'!instagram\\.com/(?:p|tv)/(?P<id>[-\\w]+)!',array('!instagram\\.com/(?:p|tv)/(?P<id>[-\\w]+)!'),7=>'<iframe data-s9e-mediaembed="instagram" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/instagram.min.html#{$id}" style="background:url(https://www.instagram.com/static/images/ico/favicon.svg/fc72dd4bfde8.svg) no-repeat 50% 50% / 50%;border:0;height:540px;max-width:540px;width:100%"></iframe>'),
		'internetarchive'=>array('Internet Archive','https://archive.org/',array('misc'=>1),'!(?P<id>)archive\\.org/(?:details|embed)/!',array(),true,array(array('extract'=>array('!meta property="twitter:player" content="https://archive.org/embed/(?P<id>[^/"]+)!','!meta property="og:video:width" content="(?P<width>\\d+)!','!meta property="og:video:height" content="(?P<height>\\d+)!'),'match'=>array('!archive\\.org/(?:details|embed)/!'))),null,array('height'=>array('s9e_MediaBBCodes::filterUint'),'id'=>array('htmlspecialchars_decode'),'width'=>array('s9e_MediaBBCodes::filterUint'))),
		'izlesene'=>array('İzlesene','https://www.izlesene.com/',array('.tr'=>1),'!izlesene\\.com/video/[-\\w]+/(?P<id>\\d+)!',array('!izlesene\\.com/video/[-\\w]+/(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="izlesene" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.izlesene.com/embedplayer/{$id}?autoplay=0" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'jwplatform'=>array('JW Platform','https://www.jwplayer.com/products/jwplatform/',array('videos'=>1),'!jwplatform\\.com/\\w+/(?P<id>[-\\w]+)!',array('!jwplatform\\.com/\\w+/(?P<id>[-\\w]+)!'),7=>'<span data-s9e-mediaembed="jwplatform" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//content.jwplatform.com/players/{$id}.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'khl'=>array('Kontinental Hockey League (КХЛ)','https://www.khl.ru/',array('.ru'=>1,'sports'=>1),'!(?P<id>)video\\.khl\\.ru/(?:event|quote)s/\\d!',array(),true,array(array('extract'=>array('!/feed/start/(?P<id>[/\\w]+)!'),'match'=>array('!video\\.khl\\.ru/(?:event|quote)s/\\d!'))),'<span data-s9e-mediaembed="khl" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video.khl.ru/iframe/feed/start/{$id}?type_id=18&amp;width=560&amp;height=315" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'kickstarter'=>array('Kickstarter','https://www.kickstarter.com/',array('fundraising'=>1),'!kickstarter\\.com/projects/(?P<id>[^/]+/[^/?]+)(?:/widget/(?:(?P<card>card)|(?P<video>video)))?!',array('!kickstarter\\.com/projects/(?P<id>[^/]+/[^/?]+)(?:/widget/(?:(?P<card>card)|(?P<video>video)))?!'),true),
		'libsyn'=>array('Libsyn','https://www.libsyn.com/',array('podcasts'=>1),'@(?P<id>)(?=.*?[./]libsyn\\.com[:/]).*?(?!\\.mp3)....$@',array(),true,array(array('extract'=>array('!embed/episode/id/(?P<id>\\d+)!'),'match'=>array('@(?!\\.mp3)....$@'))),'<iframe data-s9e-mediaembed="libsyn" allowfullscreen="" scrolling="no" src="//html5-player.libsyn.com/embed/episode/id/{$id}/height/45/width/900/theme/standard/direction/no/autoplay/no/autonext/no/thumbnail/no/preload/no/no_addthis/no/" style="border:0;height:45px;max-width:900px;width:100%"></iframe>'),
		'liveleak'=>array('Liveleak','https://www.liveleak.com/',array('videos'=>1),"!liveleak\\.com/(?:e/|view\\?i=)(?P<id>\\w+)!\n!(?P<id>)liveleak\\.com/view\\?t=!",array('!liveleak\\.com/(?:e/|view\\?i=)(?P<id>\\w+)!'),true,array(array('extract'=>array('!liveleak\\.com/e/(?P<id>\\w+)!'),'match'=>array('!liveleak\\.com/view\\?t=!'))),'<span data-s9e-mediaembed="liveleak" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.liveleak.com/e/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'livestream'=>array('Livestream','https://new.livestream.com/',array('livestreaming'=>1,'videos'=>1),'((?P<id>)(?=.*?[./]livestre(?:\\.a|am\\.co)m[:/]).*?)',array('!livestream\\.com/accounts/(?P<account_id>\\d+)/events/(?P<event_id>\\d+)!','!/videos/(?P<video_id>\\d+)!','!original\\.livestream\\.com/(?P<channel>\\w+)/video\\?clipId=(?P<clip_id>[-\\w]+)!'),true,array(array('extract'=>array('!accounts/(?P<account_id>\\d+)/events/(?P<event_id>\\d+)!'),'match'=>array('@livestream\\.com/(?!accounts/\\d+/events/\\d)@')),array('extract'=>array('!//original\\.livestream\\.com/(?P<channel>\\w+)/video/(?P<clip_id>[-\\w]+)!'),'match'=>array('!livestre.am!')))),
		'mailru'=>array('Mail.Ru','https://my.mail.ru/',array('.ru'=>1),'!(?P<id>)my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!',array(),true,array(array('extract'=>array('!"itemId": ?"?(?P<id>\\d+)!'),'match'=>array('!my\\.mail\\.ru/\\w+/\\w+/video/\\w+/\\d!')))),
		'medium'=>array('Medium','https://medium.com/',array('blogging'=>1),'!medium\\.com/(?:s/)?[^/]*/(?:[-\\w]+-)?(?P<id>[\\da-f]+)!',array('!medium\\.com/(?:s/)?[^/]*/(?:[-\\w]+-)?(?P<id>[\\da-f]+)!'),7=>'<iframe data-s9e-mediaembed="medium" allowfullscreen="" scrolling="no" src="https://api.medium.com/embed?type=story&amp;path=%2F%2F{$id}" style="border:1px solid;border-color:#eee #ddd #bbb;border-radius:5px;box-shadow:rgba(0,0,0,.15) 0 1px 3px;height:313px;max-width:900px;width:100%"></iframe>'),
		'megaphone'=>array('Megaphone','https://megaphone.fm/',array('podcasts'=>1),"@megaphone\\.fm/.*?\\?(?:e|selected)=(?P<id>\\w+)@\n@(?:dcs|player|traffic)\\.megaphone\\.fm/(?P<id>\\w+)@\n@megaphone\\.link/(?P<id>\\w+)@",array('@megaphone\\.fm/.*?\\?(?:e|selected)=(?P<id>\\w+)@','@(?:dcs|player|traffic)\\.megaphone\\.fm/(?P<id>\\w+)@','@megaphone\\.link/(?P<id>\\w+)@'),7=>'<iframe data-s9e-mediaembed="megaphone" allowfullscreen="" scrolling="no" src="https://player.megaphone.fm/{$id}" style="border:0;height:200px;max-width:900px;width:100%"></iframe>'),
		'metacafe'=>array('Metacafe','https://www.metacafe.com/',array('videos'=>1),'!metacafe\\.com/watch/(?P<id>\\d+)!',array('!metacafe\\.com/watch/(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="metacafe" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.metacafe.com/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'mixcloud'=>array('Mixcloud','https://www.mixcloud.com/',array('music'=>1),'@mixcloud\\.com/(?!categories|tag)(?P<id>[-\\w]+/[^/&]+)/@',array('@mixcloud\\.com/(?!categories|tag)(?P<id>[-\\w]+/[^/&]+)/@'),7=>'<span data-s9e-mediaembed="mixcloud" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="//www.mixcloud.com/widget/iframe/?feed=http%3A%2F%2Fwww.mixcloud.com%2F{$id}%2F&amp;embed_type=widget_standard" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'mixer'=>array('Mixer','https://mixer.com/',array('gaming'=>1,'livestreaming'=>1),'#(?P<id>)(?=.*?[./]mixer\\.com[:/]).*?mixer.com/(?!browse/)(?P<channel>\\w+)(?!\\?clip|\\w)(?:\\?vod=(?P<vod>[-\\w]+))?#',array('#(?=.*?[./]mixer\\.com[:/]).*?mixer.com/(?!browse/)(?P<channel>\\w+)(?!\\?clip|\\w)(?:\\?vod=(?P<vod>[-\\w]+))?#'),true),
		'mlb'=>array('MLB','https://mlb.com/video/',array('sports'=>1),'#mlb\\.com/video/(?:[-\\w/]+/)?(?:c-|v|[-\\w]+-c)(?P<id>\\d+)#',array('#mlb\\.com/video/(?:[-\\w/]+/)?(?:c-|v|[-\\w]+-c)(?P<id>\\d+)#'),7=>'<span data-s9e-mediaembed="mlb" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.mlb.com/video/share/c-{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'mrctv'=>array('MRCTV','https://www.mrctv.org/',array('misc'=>1),'!(?P<id>)mrctv\\.org/videos/.!',array(),true,array(array('extract'=>array('!mrctv\\.org/embed/(?P<id>\\d+)!'),'match'=>array('!mrctv\\.org/videos/.!'))),'<span data-s9e-mediaembed="mrctv" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.mrctv.org/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'msnbc'=>array('MSNBC','https://www.msnbc.com/watch',array('news'=>1),"@(?P<id>)msnbc\\.com/[-\\w]+/watch/@\n@(?P<id>)on\\.msnbc\\.com/.@",array(),true,array(array('extract'=>array('@embedded-video/(?P<id>\\w+)@'),'match'=>array('@msnbc\\.com/[-\\w]+/watch/@','@on\\.msnbc\\.com/.@')))),
		'natgeochannel'=>array('National Geographic Channel','https://channel.nationalgeographic.com/',array('misc'=>1),'@channel\\.nationalgeographic\\.com/(?P<id>[-/\\w]+/videos/[-\\w]+)@',array('@channel\\.nationalgeographic\\.com/(?P<id>[-/\\w]+/videos/[-\\w]+)@'),7=>'<span data-s9e-mediaembed="natgeochannel" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//channel.nationalgeographic.com/{$id}/embed/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'natgeovideo'=>array('National Geographic Video','https://video.nationalgeographic.com/',array('documentaries'=>1),'@(?P<id>)video\\.nationalgeographic\\.com/(?:tv|video)/\\w@',array(),true,array(array('extract'=>array('@guid="(?P<id>[-\\w]+)"@'),'match'=>array('@video\\.nationalgeographic\\.com/(?:tv|video)/\\w@'))),'<span data-s9e-mediaembed="natgeovideo" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.d.nationalgeographic.com/players/ngsvideo/share/?guid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'nbcnews'=>array('NBC News','https://www.nbcnews.com/video/',array('news'=>1),'!nbcnews\\.com/(?:widget/video-embed/|video/[-\\w]+?-)(?P<id>\\d+)!',array('!nbcnews\\.com/(?:widget/video-embed/|video/[-\\w]+?-)(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="nbcnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.nbcnews.com/widget/video-embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'nbcsports'=>array('NBC Sports','https://www.nbcsports.com/video',array('sports'=>1),'!(?P<id>)nbcsports\\.com/video/.!',array(),true,array(array('extract'=>array('!select/media/(?P<id>\\w+)!'),'match'=>array('!nbcsports\\.com/video/.!'))),'<span data-s9e-mediaembed="nbcsports" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//vplayer.nbcsports.com/p/BxmELC/nbcsports_embed/select/media/{$id}?parentUrl=" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'nhl'=>array('NHL Videos and Highlights','https://www.nhl.com/video',array('sports'=>1),'#(?P<id>)nhl\\.com/(?:\\w+/)?video(?:/(?![ct]-)[-\\w]+)?(?:/t-(?P<t>\\d+))?(?:/c-(?P<c>\\d+))?#',array('#nhl\\.com/(?:\\w+/)?video(?:/(?![ct]-)[-\\w]+)?(?:/t-(?P<t>\\d+))?(?:/c-(?P<c>\\d+))?#'),true),
		'npr'=>array('NPR','https://www.npr.org/',array('podcasts'=>1),"!(?P<id>)npr\\.org/[/\\w]+/\\d+!\n!(?P<id>)n\\.pr/\\w!",array(),true,array(array('extract'=>array('!player/embed/(?P<i>\\d+)/(?P<m>\\d+)!'),'match'=>array('!npr\\.org/[/\\w]+/\\d+!','!n\\.pr/\\w!')))),
		'nytimes'=>array('The New York Times Video','https://www.nytimes.com/video/',array('movies'=>1,'news'=>1),"!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?P<id>\\d+)!\n!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?P<id>\\d+)!\n!(?P<id>)nytimes\\.com/movie(?:s/movie)?/(?P<playlist>\\d+)/[-\\w]+/trailers!",array('!nytimes\\.com/video/[a-z]+/(?:[a-z]+/)?(?P<id>\\d+)!','!nytimes\\.com/video/\\d+/\\d+/\\d+/[a-z]+/(?P<id>\\d+)!'),true,array(array('extract'=>array('!/video/movies/(?P<id>\\d+)!'),'match'=>array('!nytimes\\.com/movie(?:s/movie)?/(?P<playlist>\\d+)/[-\\w]+/trailers!'),'url'=>'http://www.nytimes.com/svc/video/api/playlist/{@playlist}?externalId=true')),'<span data-s9e-mediaembed="nytimes" style="display:inline-block;width:100%;max-width:585px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:68.376068%"><iframe allowfullscreen="" scrolling="no" src="//graphics8.nytimes.com/video/players/offsite/index.html?videoId={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'orfium'=>array('Orfium','https://www.orfium.com/',array('music'=>1),"@(?P<id>)(?=.*?[./]orfium\\.com[:/]).*?album/(?P<album_id>\\d+)@\n@(?P<id>)(?=.*?[./]orfium\\.com[:/]).*?playlist/(?P<playlist_id>\\d+)@\n@(?P<id>)(?=.*?[./]orfium\\.com[:/]).*?live-set/(?P<set_id>\\d+)@\n@(?P<id>)(?=.*?[./]orfium\\.com[:/]).*?track/(?P<track_id>\\d+)@",array('@(?=.*?[./]orfium\\.com[:/]).*?album/(?P<album_id>\\d+)@','@(?=.*?[./]orfium\\.com[:/]).*?playlist/(?P<playlist_id>\\d+)@','@(?=.*?[./]orfium\\.com[:/]).*?live-set/(?P<set_id>\\d+)@','@(?=.*?[./]orfium\\.com[:/]).*?track/(?P<track_id>\\d+)@'),true),
		'pastebin'=>array('Pastebin','https://pastebin.com/',array('misc'=>1),'@pastebin\\.com/(?!u/)(?:\\w+(?:\\.php\\?i=|/))?(?P<id>\\w+)@',array('@pastebin\\.com/(?!u/)(?:\\w+(?:\\.php\\?i=|/))?(?P<id>\\w+)@'),7=>'<iframe data-s9e-mediaembed="pastebin" allowfullscreen="" scrolling="" src="//pastebin.com/embed_iframe.php?i={$id}" style="border:0;height:300px;resize:vertical;width:100%"></iframe>'),
		'pinterest'=>array('Pinterest','https://www.pinterest.com/',array('social'=>1),"@(?=.*?[./]pinterest\\.com[:/]).*?pinterest.com/pin/(?P<id>\\d+)@\n@(?=.*?[./]pinterest\\.com[:/]).*?pinterest.com/(?!_/|discover/|explore/|news_hub/|pin/|search/)(?P<id>[-\\w]+/[-\\w]+)@",array('@(?=.*?[./]pinterest\\.com[:/]).*?pinterest.com/pin/(?P<id>\\d+)@','@(?=.*?[./]pinterest\\.com[:/]).*?pinterest.com/(?!_/|discover/|explore/|news_hub/|pin/|search/)(?P<id>[-\\w]+/[-\\w]+)@')),
		'podbean'=>array('Podbean','https://www.podbean.com/',array('podcasts'=>1),"!podbean\\.com/media/(?:player/|share/pb-)(?P<id>[-\\w]+)!\n@(?P<id>)podbean\\.com/(?:media/shar)?e/(?!pb-)@",array('!podbean\\.com/media/(?:player/|share/pb-)(?P<id>[-\\w]+)!'),true,array(array('extract'=>array('!podbean\\.com/media/player/(?P<id>[-\\w]+)!'),'match'=>array('@podbean\\.com/(?:media/shar)?e/(?!pb-)@'))),'<iframe data-s9e-mediaembed="podbean" allowfullscreen="" scrolling="no" src="//www.podbean.com/media/player/{$id}" style="border:0;height:100px;max-width:900px;width:100%"></iframe>'),
		'prezi'=>array('Prezi','https://prezi.com/',array('presentations'=>1),'#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?P<id>\\w+)/#',array('#//prezi\\.com/(?!(?:a(?:bout|mbassadors)|c(?:o(?:llaborate|mmunity|ntact)|reate)|exp(?:erts|lore)|ip(?:ad|hone)|jobs|l(?:ear|ogi)n|m(?:ac|obility)|pr(?:es(?:s|ent)|icing)|recommend|support|user|windows|your)/)(?P<id>\\w+)/#'),7=>'<span data-s9e-mediaembed="prezi" style="display:inline-block;width:100%;max-width:550px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:72.727273%"><iframe allowfullscreen="" scrolling="no" src="//prezi.com/embed/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'reddit'=>array('Reddit threads and comments','https://www.reddit.com/',array('social'=>1),'!(?=.*?[./]reddit\\.com[:/]).*?(?P<id>\\w+/comments/\\w+(?:/\\w+/\\w+)?)!',array('!(?=.*?[./]reddit\\.com[:/]).*?(?P<id>\\w+/comments/\\w+(?:/\\w+/\\w+)?)!')),
		'rutube'=>array('Rutube','https://rutube.ru/',array('.ru'=>1),"!rutube\\.ru/tracks/(?P<id>\\d+)!\n!(?P<id>)rutube\\.ru/video/(?P<vid>[0-9a-f]{32})!",array('!rutube\\.ru/tracks/(?P<id>\\d+)!'),true,array(array('extract'=>array('!rutube\\.ru/play/embed/(?P<id>\\d+)!'),'match'=>array('!rutube\\.ru/video/(?P<vid>[0-9a-f]{32})!'),'url'=>'http://rutube.ru/api/oembed/?url=https://rutube.ru/video/{@vid}/')),'<span data-s9e-mediaembed="rutube" style="display:inline-block;width:100%;max-width:720px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//rutube.ru/play/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'scribd'=>array('Scribd','https://www.scribd.com/',array('documents'=>1,'presentations'=>1),'!scribd\\.com/(?:mobile/)?(?:doc(?:ument)?|presentation)/(?P<id>\\d+)!',array('!scribd\\.com/(?:mobile/)?(?:doc(?:ument)?|presentation)/(?P<id>\\d+)!'),7=>'<iframe data-s9e-mediaembed="scribd" allowfullscreen="" scrolling="no" src="https://www.scribd.com/embeds/{$id}/content?view_mode=scroll&amp;show_recommendations=false" style="border:0;height:500px;resize:vertical;width:100%"></iframe>'),
		'sendvid'=>array('Sendvid','https://www.sendvid.com/',array('videos'=>1),'!sendvid\\.com/(?P<id>\\w+)!',array('!sendvid\\.com/(?P<id>\\w+)!'),7=>'<span data-s9e-mediaembed="sendvid" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//sendvid.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'slideshare'=>array('SlideShare','https://www.slideshare.net/',array('presentations'=>1),"!slideshare\\.net/[^/]+/[-\\w]+-(?P<id>\\d{6,})$!\n@(?P<id>)slideshare\\.net/[^/]+/\\w(?![-\\w]+-\\d{6,}$)@",array('!slideshare\\.net/[^/]+/[-\\w]+-(?P<id>\\d{6,})$!'),true,array(array('extract'=>array('!"presentationId":(?P<id>\\d+)!'),'match'=>array('@slideshare\\.net/[^/]+/\\w(?![-\\w]+-\\d{6,}$)@'))),'<span data-s9e-mediaembed="slideshare" style="display:inline-block;width:100%;max-width:427px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:83.372365%"><iframe allowfullscreen="" scrolling="no" src="//www.slideshare.net/slideshow/embed_code/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'soundcloud'=>array('SoundCloud','https://soundcloud.com/',array('music'=>1),"@https?://(?:api\\.)?soundcloud\\.com/(?!pages/)(?P<id>[-/\\w]+/[-/\\w]+|^[^/]+/[^/]+$)@i\n@(?P<id>)api\\.soundcloud\\.com/playlists/(?P<playlist_id>\\d+)@\n@(?P<id>)api\\.soundcloud\\.com/tracks/(?P<track_id>\\d+)(?:\\?secret_token=(?P<secret_token>[-\\w]+))?@\n@(?P<id>)soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/(?=s-)(?P<secret_token>[-\\w]+)@\n@(?P<id>)soundcloud\\.com/(?!playlists/\\d|tracks/\\d)[-\\w]+/[-\\w]@\n@(?P<id>)soundcloud\\.com/\\w+/sets/@",array('@https?://(?:api\\.)?soundcloud\\.com/(?!pages/)(?P<id>[-/\\w]+/[-/\\w]+|^[^/]+/[^/]+$)@i','@api\\.soundcloud\\.com/playlists/(?P<playlist_id>\\d+)@','@api\\.soundcloud\\.com/tracks/(?P<track_id>\\d+)(?:\\?secret_token=(?P<secret_token>[-\\w]+))?@','@soundcloud\\.com/(?!playlists|tracks)[-\\w]+/[-\\w]+/(?=s-)(?P<secret_token>[-\\w]+)@'),true,array(array('extract'=>array('@soundcloud:tracks:(?P<track_id>\\d+)@'),'match'=>array('@soundcloud\\.com/(?!playlists/\\d|tracks/\\d)[-\\w]+/[-\\w]@')),array('extract'=>array('@soundcloud://playlists:(?P<playlist_id>\\d+)@'),'match'=>array('@soundcloud\\.com/\\w+/sets/@')))),
		'sporcle'=>array('Sporcle','https://www.sporcle.com/',array('entertainment'=>1),"#(?=.*?[./]sporcle\\.com[:/]).*?sporcle.com/framed/.*?gid=(?P<id>\\w+)#\n#(?P<id>)sporcle\\.com/games/(?!\\w*category/)[-\\w]+/[-\\w]#",array('#(?=.*?[./]sporcle\\.com[:/]).*?sporcle.com/framed/.*?gid=(?P<id>\\w+)#'),true,array(array('extract'=>array('#encodedGameID\\W+(?P<id>\\w+)#'),'match'=>array('#sporcle\\.com/games/(?!\\w*category/)[-\\w]+/[-\\w]#'))),'<iframe data-s9e-mediaembed="sporcle" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'*\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/sporcle.min.html#{$id}" style="border:0;height:535px;max-width:820px;width:100%"></iframe>'),
		'sportsnet'=>array('Sportsnet','https://www.sportsnet.ca/',array('.ca'=>1,'sports'=>1),'((?P<id>)sportsnet\\.ca/)',array(),true,array(array('extract'=>array('/vid(?:eoId)?=(?P<id>\\d+)/','/param name="@videoPlayer" value="(?P<id>\\d+)"/'),'match'=>array('//'))),'<span data-s9e-mediaembed="sportsnet" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://images.rogersdigitalmedia.com/video_service.php?videoId={$id}&amp;playerKey=AQ~~,AAAAAGWRwLc~,cRCmKE8Utf7OFWP38XQcokFZ80fR-u_y&amp;autoStart=false&amp;width=100%25&amp;height=100%25" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'spotify'=>array('Spotify','https://www.spotify.com/',array('music'=>1,'podcast'=>1),'!(?:open|play)\\.spotify\\.com/(?P<id>(?:user/[-.\\w]+/)?(?:album|artist|episode|playlist|show|track)(?:[:/][-.\\w]+)+)!',array('!(?:open|play)\\.spotify\\.com/(?P<id>(?:user/[-.\\w]+/)?(?:album|artist|episode|playlist|show|track)(?:[:/][-.\\w]+)+)!')),
		'spreaker'=>array('Spreaker','https://www.spreaker.com',array('podcasts'=>1),"!(?P<id>)spreaker\\.com/episode/(?P<episode_id>\\d+)!\n!(?P<id>)(?=.*?[./]spreaker\\.com[:/]).*?(?P<url>.+/(?:show/|user/.+/).+)!",array('!spreaker\\.com/episode/(?P<episode_id>\\d+)!'),true,array(array('extract'=>array('!episode_id=(?P<episode_id>\\d+)!','!show_id=(?P<show_id>\\d+)!'),'match'=>array('!(?P<url>.+/(?:show/|user/.+/).+)!'),'url'=>'https://api.spreaker.com/oembed?format=json&url={@url}'))),
		'steamstore'=>array('Steam store','https://store.steampowered.com/',array('gaming'=>1),'!(?=.*?[./]store\\.steampowered\\.com[:/]).*?store.steampowered.com/app/(?P<id>\\d+)!',array('!(?=.*?[./]store\\.steampowered\\.com[:/]).*?store.steampowered.com/app/(?P<id>\\d+)!'),7=>'<iframe data-s9e-mediaembed="steamstore" allowfullscreen="" scrolling="no" src="//store.steampowered.com/widget/{$id}" style="border:0;height:190px;max-width:900px;width:100%"></iframe>'),
		'stitcher'=>array('Stitcher','https://www.stitcher.com/',array('podcasts'=>1),'!(?P<id>)(?=.*?[./]stitcher\\.com[:/]).*?/podcast/!',array(),true,array(array('extract'=>array('!data-eid="(?P<eid>\\d+)!','!data-fid="(?P<fid>\\d+)!'),'match'=>array('!/podcast/!')))),
		'strawpoll'=>array('Straw Poll','https://strawpoll.me/',array('misc'=>1),'!strawpoll\\.me/(?P<id>\\d+)!',array('!strawpoll\\.me/(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="strawpoll" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="" src="//www.strawpoll.me/embed_1/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'streamable'=>array('Streamable','https://streamable.com/',array('videos'=>1),'!streamable\\.com/(?P<id>\\w+)!',array('!streamable\\.com/(?P<id>\\w+)!'),7=>'<span data-s9e-mediaembed="streamable" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//streamable.com/e/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'streamja'=>array('Streamja','https://streamja.com',array('sports'=>1),'@streamja\\.com/(?!login|signup|terms|videos)(?P<id>\\w+)@',array('@streamja\\.com/(?!login|signup|terms|videos)(?P<id>\\w+)@'),7=>'<span data-s9e-mediaembed="streamja" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://streamja.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'teamcoco'=>array('Team Coco','https://teamcoco.com/',array('entertainment'=>1),"!teamcoco\\.com/video/(?P<id>\\d+)!\n!(?P<id>)teamcoco\\.com/video/\\D!",array('!teamcoco\\.com/video/(?P<id>\\d+)!'),true,array(array('extract'=>array('!embed/v/(?P<id>\\d+)!'),'match'=>array('!teamcoco\\.com/video/\\D!'))),'<span data-s9e-mediaembed="teamcoco" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//teamcoco.com/embed/v/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'ted'=>array('TED Talks','https://www.ted.com/',array('presentations'=>1),'#ted\\.com/(?P<id>(?:talk|playlist)s/[-\\w]+(?:\\.html)?)(?![-\\w]|/transcript)#i',array('#ted\\.com/(?P<id>(?:talk|playlist)s/[-\\w]+(?:\\.html)?)(?![-\\w]|/transcript)#i')),
		'telegram'=>array('Telegram','https://telegram.org/',array('social'=>1),'@(?=.*?[./]t\\.me[:/]).*?//t.me/(?!addstickers/|joinchat/)(?P<id>\\w+/\\d+)@',array('@(?=.*?[./]t\\.me[:/]).*?//t.me/(?!addstickers/|joinchat/)(?P<id>\\w+/\\d+)@')),
		'theatlantic'=>array('The Atlantic Video','https://www.theatlantic.com/video/',array('news'=>1),'!theatlantic\\.com/video/index/(?P<id>\\d+)!',array('!theatlantic\\.com/video/index/(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="theatlantic" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.theatlantic.com/video/iframe/{$id}/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'theguardian'=>array('The Guardian (obsolete)','https://www.theguardian.com/video',array('news'=>1),'!theguardian\\.com/(?P<id>\\w+/video/20(?:0[0-9]|1[0-7])[-/\\w]+)!',array('!theguardian\\.com/(?P<id>\\w+/video/20(?:0[0-9]|1[0-7])[-/\\w]+)!')),
		'theonion'=>array('The Onion','https://www.theonion.com/video/',array('entertainment'=>1),'!theonion\\.com/video/[-\\w]+[-,](?P<id>\\d+)!',array('!theonion\\.com/video/[-\\w]+[-,](?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="theonion" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.theonion.com/video_embed/?id={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'tiktok'=>array('TikTok','https://www.tiktok.com',array('videos'=>1),"#tiktok\\.com/(?:@[.\\w]+/video|v|i18n/share/video)/(?P<id>\\d+)#\n#(?P<id>)//vm\\.tiktok\\.com/.#",array('#tiktok\\.com/(?:@[.\\w]+/video|v|i18n/share/video)/(?P<id>\\d+)#'),true,array(array('extract'=>array('#tiktok\\.com/@[.\\w]+/video/(?P<id>\\d+)#'),'match'=>array('#//vm\\.tiktok\\.com/.#'))),'<span data-s9e-mediaembed="tiktok" style="display:inline-block;width:100%;max-width:340px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:205.882353%"><iframe allowfullscreen="" scrolling="no" src="//www.tiktok.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'tmz'=>array('TMZ','https://www.tmz.com/videos',array('gossip'=>1),'@tmz\\.com/videos/(?P<id>\\w+)@',array('@tmz\\.com/videos/(?P<id>\\w+)@'),7=>'<span data-s9e-mediaembed="tmz" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.kaltura.com/index.php/kwidget/cache_st/133592691/wid/_591531/partner_id/591531/uiconf_id/9071262/entry_id/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'traileraddict'=>array('Trailer Addict','https://www.traileraddict.com/',array('movies'=>1),'@(?P<id>)traileraddict\\.com/(?!tags/)[^/]+/.@',array(),true,array(array('extract'=>array('@v\\.traileraddict\\.com/(?P<id>\\d+)@'),'match'=>array('@traileraddict\\.com/(?!tags/)[^/]+/.@'))),'<span data-s9e-mediaembed="traileraddict" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//v.traileraddict.com/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'trendingviews'=>array('Trending Views','https://trendingviews.co/',array('videos'=>1),'!(?=.*?[./]trendingviews\\.co[:/]).*?trendingviews.co/(?:video|embed)/(?:[^/]+-)?(?P<id>\\d+)!',array('!(?=.*?[./]trendingviews\\.co[:/]).*?trendingviews.co/(?:video|embed)/(?:[^/]+-)?(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="trendingviews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://trendingviews.co/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'tumblr'=>array('Tumblr','https://www.tumblr.com/',array('social'=>1),"!(?P<name>[-\\w]+)\\.tumblr\\.com/post/(?P<id>\\d+)!\n!(?P<id>)\\w\\.tumblr\\.com/post/\\d!",array('!(?P<name>[-\\w]+)\\.tumblr\\.com/post/(?P<id>\\d+)!'),true,array(array('extract'=>array('!did=\\\\u0022(?P<did>[-\\w]+)!','!embed\\\\/post\\\\/(?P<key>[-\\w]+)!'),'match'=>array('!\\w\\.tumblr\\.com/post/\\d!'),'url'=>'https://www.tumblr.com/oembed/1.0?url=https://{@name}.tumblr.com/post/{@id}'))),
		'twentyfoursevensports'=>array('247Sports','https://247sports.com/',array('sports'=>1),"!(?P<id>)247sports\\.com/PlayerSport/[-\\w]*?(?P<player_id>\\d+)/Embed!\n!(?P<id>)247sports\\.com/Player/[-\\w]*?\\d!\n!(?P<id>)247sports\\.com/Video/.!",array('!247sports\\.com/PlayerSport/[-\\w]*?(?P<player_id>\\d+)/Embed!'),true,array(array('extract'=>array('!247sports\\.com/PlayerSport/[-\\w]*?(?P<player_id>\\d+)/Embed!'),'match'=>array('!247sports\\.com/Player/[-\\w]*?\\d!')),array('extract'=>array('!player_id%3D(?P<video_id>\\d+)!'),'match'=>array('!247sports\\.com/Video/.!')))),
		'twitch'=>array('Twitch','https://www.twitch.tv/',array('gaming'=>1,'livestreaming'=>1),"#(?P<id>)twitch\\.tv/(?:videos|\\w+/v)/(?P<video_id>\\d+)?#\n#(?P<id>)www\\.twitch\\.tv/(?!videos/)(?P<channel>\\w+)#\n#(?P<id>)(?=.*?[./]twitch\\.tv[:/]).*?t=(?P<t>(?:(?:\\d+h)?\\d+m)?\\d+s)#\n#(?P<id>)clips\\.twitch\\.tv/(?:(?P<channel>\\w+)/)?(?P<clip_id>\\w+)#",array('#twitch\\.tv/(?:videos|\\w+/v)/(?P<video_id>\\d+)?#','#www\\.twitch\\.tv/(?!videos/)(?P<channel>\\w+)#','#(?=.*?[./]twitch\\.tv[:/]).*?t=(?P<t>(?:(?:\\d+h)?\\d+m)?\\d+s)#','#clips\\.twitch\\.tv/(?:(?P<channel>\\w+)/)?(?P<clip_id>\\w+)#'),true),
		'twitter'=>array('Twitter','https://twitter.com/',array('social'=>1),'@twitter\\.com/(?:#!/|i/)?\\w+/(?:status(?:es)?|tweet)/(?P<id>\\d+)@',array('@twitter\\.com/(?:#!/|i/)?\\w+/(?:status(?:es)?|tweet)/(?P<id>\\d+)@'),7=>'<iframe data-s9e-mediaembed="twitter" allow="autoplay *" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/twitter.min.html#{$id}" style="background:url(https://abs.twimg.com/favicons/favicon.ico) no-repeat 50% 50%;border:0;height:250px;max-width:500px;width:100%"></iframe>'),
		'ustream'=>array('Ustream','https://www.ustream.tv/',array('gaming'=>1),"!(?P<id>)ustream\\.tv/recorded/(?P<vid>\\d+)!\n#(?P<id>)ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#",array('!ustream\\.tv/recorded/(?P<vid>\\d+)!'),true,array(array('extract'=>array('!embed/(?P<cid>\\d+)!'),'match'=>array('#ustream\\.tv/(?!explore/|platform/|recorded/|search\\?|upcoming$|user/)(?:channel/)?[-\\w]+#')))),
		'vbox7'=>array('VBOX7','https://vbox7.com/',array('.bg'=>1),'!vbox7\\.com/play:(?P<id>[\\da-f]+)!',array('!vbox7\\.com/play:(?P<id>[\\da-f]+)!'),7=>'<span data-s9e-mediaembed="vbox7" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//vbox7.com/emb/external.php?vid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'veoh'=>array('Veoh','https://www.veoh.com/',array('videos'=>1),'!veoh\\.com/(?:m/watch\\.php\\?v=|watch/)v(?P<id>\\w+)!',array('!veoh\\.com/(?:m/watch\\.php\\?v=|watch/)v(?P<id>\\w+)!'),7=>'<span data-s9e-mediaembed="veoh" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:62.5%;padding-bottom:calc(56.25% + 40px)"><object data="//www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1509&amp;permalinkId=v{$id}&amp;player=videodetailsembedded&amp;videoAutoPlay=0&amp;id=anonymous" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"/></object></span></span>'),
		'vevo'=>array('Vevo','https://vevo.com/',array('music'=>1),'!vevo\\.com/watch/(.*?/)?(?P<id>[A-Z]+\\d+)!',array('!vevo\\.com/watch/(.*?/)?(?P<id>[A-Z]+\\d+)!'),7=>'<span data-s9e-mediaembed="vevo" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://embed.vevo.com/?isrc={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'videodetective'=>array('Video Detective','https://www.videodetective.com/',array('misc'=>1),'!videodetective\\.com/\\w+/[-\\w]+/(?:trailer/P0*)?(?P<id>\\d+)!',array('!videodetective\\.com/\\w+/[-\\w]+/(?:trailer/P0*)?(?P<id>\\d+)!'),7=>'<span data-s9e-mediaembed="videodetective" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.videodetective.com/embed/video/?options=false&amp;autostart=false&amp;playlist=none&amp;publishedid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'vimeo'=>array('Vimeo','https://vimeo.com/',array('videos'=>1),"!vimeo\\.com/(?:channels/[^/]+/|video/)?(?P<id>\\d+)!\n!(?P<id>)(?=.*?[./]vimeo\\.com[:/]).*?#t=(?P<t>[\\dhms]+)!",array('!vimeo\\.com/(?:channels/[^/]+/|video/)?(?P<id>\\d+)!','!(?=.*?[./]vimeo\\.com[:/]).*?#t=(?P<t>[\\dhms]+)!'),true,7=>null,8=>array('t'=>array('s9e_MediaBBCodes::filterTimestamp'))),
		'vine'=>array('Vine','https://vine.co/',array('social'=>1,'videos'=>1),'!vine\\.co/v/(?P<id>[^/]+)!',array('!vine\\.co/v/(?P<id>[^/]+)!'),7=>'<span data-s9e-mediaembed="vine" style="display:inline-block;width:100%;max-width:480px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" src="https://vine.co/v/{$id}/embed/simple?audio=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'vk'=>array('VK','https://vk.com/',array('.ru'=>1),"!(?P<id>)vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?P<oid>-?\\d+)_(?P<vid>\\d+).*?hash=(?P<hash>[0-9a-f]+)!\n!(?P<id>)vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?P<oid>-?\\d+)&id=(?P<vid>\\d+)&hash=(?P<hash>[0-9a-f]+)!",array('!vk(?:\\.com|ontakte\\.ru)/(?:[\\w.]+\\?z=)?video(?P<oid>-?\\d+)_(?P<vid>\\d+).*?hash=(?P<hash>[0-9a-f]+)!','!vk(?:\\.com|ontakte\\.ru)/video_ext\\.php\\?oid=(?P<oid>-?\\d+)&id=(?P<vid>\\d+)&hash=(?P<hash>[0-9a-f]+)!'),true),
		'vocaroo'=>array('Vocaroo','https://vocaroo.com/',array('misc'=>1),'!voca(?:\\.ro|roo\\.com)/(?:i/)?(?P<id>\\w+)!',array('!voca(?:\\.ro|roo\\.com)/(?:i/)?(?P<id>\\w+)!')),
		'vox'=>array('Vox','https://www.vox.com/',array('misc'=>1),'!(?=.*?[./]vox\\.com[:/]).*?vox.com/.*#ooid=(?P<id>[-\\w]+)!',array('!(?=.*?[./]vox\\.com[:/]).*?vox.com/.*#ooid=(?P<id>[-\\w]+)!'),7=>'<span data-s9e-mediaembed="vox" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.ooyala.com/iframe.html#pbid=a637d53c5c0a43c7bf4e342886b9d8b0&amp;ec={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'washingtonpost'=>array('Washington Post Video','https://www.washingtonpost.com/video/',array('news'=>1),"#washingtonpost\\.com/video/c/\\w+/(?P<id>[-0-9a-f]+)#\n#washingtonpost\\.com/video/[-/\\w]+/(?P<id>[-0-9a-f]+)_video\\.html#",array('#washingtonpost\\.com/video/c/\\w+/(?P<id>[-0-9a-f]+)#','#washingtonpost\\.com/video/[-/\\w]+/(?P<id>[-0-9a-f]+)_video\\.html#'),7=>'<span data-s9e-mediaembed="washingtonpost" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.washingtonpost.com/video/c/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'wshh'=>array('WorldStarHipHop','https://www.worldstarhiphop.com/',array('videos'=>1),"!worldstarhiphop\\.com/featured/(?P<id>\\d+)!\n!(?P<id>)worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!",array('!worldstarhiphop\\.com/featured/(?P<id>\\d+)!'),true,array(array('extract'=>array('!v: ?"?(?P<id>\\d+)!'),'match'=>array('!worldstarhiphop\\.com/(?:\\w+/)?video\\.php\\?v=\\w+!'))),'<span data-s9e-mediaembed="wshh" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.worldstarhiphop.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'wsj'=>array('The Wall Street Journal Online','https://www.wsj.com/video/',array('news'=>1),"@wsj\\.com/[^#]*#!(?P<id>[-0-9A-F]{36})@\n@wsj\\.com/video/[^/]+/(?P<id>[-0-9A-F]{36})@\n@(?P<id>)on\\.wsj\\.com/\\w@",array('@wsj\\.com/[^#]*#!(?P<id>[-0-9A-F]{36})@','@wsj\\.com/video/[^/]+/(?P<id>[-0-9A-F]{36})@'),true,array(array('extract'=>array('@guid=(?P<id>[-0-9A-F]{36})@'),'match'=>array('@on\\.wsj\\.com/\\w@'))),'<span data-s9e-mediaembed="wsj" style="display:inline-block;width:100%;max-width:512px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//video-api.wsj.com/api-video/player/iframe.html?guid={$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'xboxclips'=>array('GameClips.io','https://gameclips.io/',array('gaming'=>1),'@(?:gameclips\\.io|xboxclips\\.com)/(?!game/)(?P<user>[^/]+)/(?!screenshots/)(?P<id>[-0-9a-f]+)@',array('@(?:gameclips\\.io|xboxclips\\.com)/(?!game/)(?P<user>[^/]+)/(?!screenshots/)(?P<id>[-0-9a-f]+)@'),true),
		'xboxdvr'=>array('Gamer DVR','https://gamerdvr.com/',array('gaming'=>1),'!(?:gamer|xbox)dvr\\.com/gamer/(?P<user>[^/]+)/video/(?P<id>\\d+)!',array('!(?:gamer|xbox)dvr\\.com/gamer/(?P<user>[^/]+)/video/(?P<id>\\d+)!'),true),
		'youku'=>array('Youku','https://www.youku.com/',array('.cn'=>1),'!youku\\.com/v(?:_show|ideo)/id_(?P<id>\\w+=*)!',array('!youku\\.com/v(?:_show|ideo)/id_(?P<id>\\w+=*)!'),7=>'<span data-s9e-mediaembed="youku" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//player.youku.com/embed/{$id}" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>'),
		'youtube'=>array('YouTube','https://www.youtube.com/',array('livestreaming'=>1,'videos'=>1),"!youtube\\.com/(?:watch.*?v=|v/|attribution_link.*?v%3D)(?P<id>[-\\w]+)!\n!youtu\\.be/(?P<id>[-\\w]+)!\n@(?P<id>)(?=.*?[./]youtu(?:\\.be|be\\.com)[:/]).*?[#&?]t=(?P<t>\\d[\\dhms]*)@\n!(?P<id>)(?=.*?[./]youtu(?:\\.be|be\\.com)[:/]).*?[&?]list=(?P<list>[-\\w]+)!\n!(?P<id>)(?=.*?[./]youtu(?:\\.be|be\\.com)[:/]).*?/shared\\?ci=!",array('!youtube\\.com/(?:watch.*?v=|v/|attribution_link.*?v%3D)(?P<id>[-\\w]+)!','!youtu\\.be/(?P<id>[-\\w]+)!','@(?=.*?[./]youtu(?:\\.be|be\\.com)[:/]).*?[#&?]t=(?P<t>\\d[\\dhms]*)@','!(?=.*?[./]youtu(?:\\.be|be\\.com)[:/]).*?[&?]list=(?P<list>[-\\w]+)!'),true,array(array('extract'=>array('!/vi/(?P<id>[-\\w]+)!'),'match'=>array('!/shared\\?ci=!'))),null,array('id'=>array('s9e_MediaBBCodes::filterIdentifier'),'t'=>array('s9e_MediaBBCodes::filterTimestamp')))
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
	* Replace iframe src attributes in given HTML
	*
	* @param  string  $templateName
	* @param  string &$content
	* @return void
	*/
	public static function replaceIframeSrc($templateName, &$content)
	{
		if (strpos($content, 'data-s9e-mediaembed="') === false)
		{
			return;
		}

		$content = preg_replace_callback(
			'((<(?:span data-s9e-mediaembed="[^>]++><span[^>]++><iframe|iframe data-s9e-mediaembed=")[^>]+? src=")([^>]++))S',
			function ($m)
			{
				$html = $m[1] . 'data:text/html," data-s9e-lazyload-src="' . $m[2];
				if (strpos($html, ' onload="') !== false)
				{
					$html = preg_replace(
						'( onload="([^"]++)")',
						' onload="if(!hasAttribute(\'data-s9e-lazyload-src\')){$1}"',
						$html
					);
				}

				return $html;
			},
			$content
		);

		$content .= '<script>(function(d){function f(b){b("click",e);b("resize",e);b("scroll",e)}function e(){clearTimeout(g);g=setTimeout(h,32)}function h(){k=innerHeight+600;var b=[];a.forEach(function(c){var a=c.getBoundingClientRect();-200<a.bottom&&a.top<k&&a.width?(c.contentWindow.location.replace(c.getAttribute(d)),c.removeAttribute(d)):b.push(c)});a=b;a.length||f(removeEventListener)}for(var l=document.querySelectorAll("iframe["+d+"]"),m=l.length,a=[],k=0,g=0;0<=--m;)a.push(l[m]);f(addEventListener);h()})("data-s9e-lazyload-src")</script>';
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

		// This should be extended to any site that has an "id" capture plus other regexps that
		// can match without capturing a value for "id"
		if ($siteId === 'amazon' && !isset($vars['id']))
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
			unset($vars['id']);
			foreach (explode(';', $mediaKey) as $pair)
			{
				list($k, $v) = explode('=', $pair);
				$vars[urldecode($k)] = urldecode($v);
			}
		}

		// Live-patch the old h/m/s for backward compatibility
		if ($siteId === 'youtube' && !isset($vars['t']))
		{
			$vars += ['h' => 0, 'm' => 0, 's' => 0];
			$vars['t'] = intval($vars['h']) * 3600 + intval($vars['m']) * 60 + intval($vars['s']);
			if (!$vars['t'])
			{
				unset($vars['t']);
			}
		}

		// Re-apply filters
		self::applyFilters($siteId, $vars);

		// Prepare the HTML
		$methodName = 'render' . ucfirst($siteId);
		if (method_exists(__CLASS__, $methodName))
		{
			$html = @call_user_func(__CLASS__ . '::' . $methodName, $vars);
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
		$url = preg_replace('(#.*)s', '', $url);

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
			curl_setopt($curl, CURLOPT_USERAGENT,      'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');
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
					'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0'
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
				$vars[$varName] = call_user_func($callback, $vars[$varName]);
				if ($vars[$varName] === false)
				{
					unset($vars[$varName]);
					break;
				}
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
					if (!is_numeric($k) && !isset($vars[$k]) && $v !== '')
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

	/**
	* Filter an alnum value
	*
	* @param  string $attrValue
	* @return string
	*/
	protected static function filterAlnum($attrValue)
	{
		return (preg_match('(^[a-z0-9]+$)Di', $attrValue)) ? $attrValue : '';
	}

	/**
	* Filter an identifier value
	*
	* @param  string $attrValue
	* @return mixed
	*/
	protected static function filterIdentifier($attrValue)
	{
		return (preg_match('/^[-0-9A-Za-z_]+$/D', $attrValue)) ? $attrValue : false;
	}

	/**
	* Filter a uint value
	*
	* @param  string $attrValue
	* @return mixed
	*/
	protected static function filterUint($attrValue)
	{
		return filter_var($attrValue, FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 0]
		]);
	}

	/**
	* Filter a timestamp value
	*
	* @param  string $attrValue
	* @return string
	*/
	protected static function filterTimestamp($attrValue)
	{
		if (preg_match('/^(?=\\d)(?:(\\d+)h)?(?:(\\d+)m)?(?:(\\d+)s)?$/D', $attrValue, $m))
		{
			$m += [0, 0, 0, 0];

			return intval($m[1]) * 3600 + intval($m[2]) * 60 + intval($m[3]);
		}

		return (is_numeric($attrValue)) ? $attrValue : '';
	}

	public static function renderAmazon($vars)
	{
		$vars += array('id' => null, 'tld' => null);

		$html='<span data-s9e-mediaembed="amazon" style="display:inline-block;width:100%;max-width:120px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:200%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://www.amazon.com/favicon.ico) center no-repeat;border:0;height:100%;left:0;position:absolute;width:100%" src="//ws-';if($vars['tld']==='jp')$html.='fe';elseif(isset($vars['tld'])&&(strpos('desfrinituk',$vars['tld'])!==false))$html.='eu';else$html.='na';$html.='.assoc-amazon.com/widgets/cm?l=as1&amp;f=ifr&amp;o=';switch($vars['tld']){case'ca':$html.='15&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_CA,2);break;case'de':$html.='3&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_DE,2);break;case'es':$html.='30&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_ES,2);break;case'fr':$html.='8&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_FR,2);break;case'in':$html.='31&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_IN,2);break;case'it':$html.='29&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_IT,2);break;case'jp':$html.='9&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_JP,2);break;case'uk':$html.='2&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG_UK,2);break;default:$html.='1&amp;t='.htmlspecialchars(XenForo_Application::get('options')->s9e_AMAZON_ASSOCIATE_TAG,2);}$html.='&amp;asins='.htmlspecialchars($vars['id'],2).'"></iframe></span></span>';

		return $html;
	}

	public static function renderAudiomack($vars)
	{
		$vars += array('id' => null, 'mode' => null);

		$html='<iframe data-s9e-mediaembed="audiomack" allowfullscreen="" scrolling="no"';if($vars['mode']==='album')$html.=' src="https://www.audiomack.com/embed/album/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:400px;max-width:900px;width:100%"';else$html.=' src="https://www.audiomack.com/embed/song/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:252px;max-width:900px;width:100%"';$html.='></iframe>';

		return $html;
	}

	public static function renderBandcamp($vars)
	{
		$vars += array('album_id' => null, 'track_id' => null, 'track_num' => null);

		$html='<span data-s9e-mediaembed="bandcamp" style="display:inline-block;width:100%;max-width:400px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:100%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//bandcamp.com/EmbeddedPlayer/size=large/minimal=true/';if(isset($vars['album_id'])){$html.='album='.htmlspecialchars($vars['album_id'],2);if(isset($vars['track_num']))$html.='/t='.htmlspecialchars($vars['track_num'],2);}else$html.='track='.htmlspecialchars($vars['track_id'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderBbcnews($vars)
	{
		$vars += array('id' => null, 'playlist' => null);

		$html='<span data-s9e-mediaembed="bbcnews" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.bbc.com';if((strpos($vars['id'],'av/')===0))$html.='/news/'.htmlspecialchars($vars['id'],2).'/embed';elseif((strpos($vars['playlist'],'/news/')===0)&&(strpos($vars['playlist'],'A')!==false))$html.=htmlspecialchars(strstr($vars['playlist'],'A',true),2).'/embed';else$html.='/news/av/embed/'.htmlspecialchars($vars['id'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderBrightcove($vars)
	{
		$vars += array('bckey' => null, 'bcpid' => null, 'bctid' => null);

		$html='<span data-s9e-mediaembed="brightcove" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="https://';if(isset($vars['bckey']))$html.='link.brightcove.com/services/player/bcpid'.htmlspecialchars($vars['bcpid'],2).'?bckey='.htmlspecialchars($vars['bckey'],2).'&amp;bctid='.htmlspecialchars($vars['bctid'],2).'&amp;secureConnections=true&amp;secureHTMLConnections=true&amp;autoStart=false&amp;height=360&amp;width=640';else$html.='players.brightcove.net/'.htmlspecialchars($vars['bcpid'],2).'/default_default/index.html?videoId='.htmlspecialchars($vars['bctid'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderCbsnews($vars)
	{
		$vars += array('id' => null, 'pid' => null);

		$html='<span data-s9e-mediaembed="cbsnews" style="display:inline-block;width:100%;max-width:640px"><span';if((strpos($vars['id'],'-')!==false))$html.=' style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.cbsnews.com/embed/videos/'.htmlspecialchars($vars['id'],2).'/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe>';elseif(isset($vars['pid']))$html.=' style="display:block;overflow:hidden;position:relative;padding-bottom:62.1875%;padding-bottom:calc(56.25% + 38px)"><object data="//www.cbsnews.com/common/video/cbsnews_player.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="pType=embed&amp;si=254&amp;pid='.htmlspecialchars($vars['pid'],2).'"></object>';else$html.=' style="display:block;overflow:hidden;position:relative;padding-bottom:62.5%;padding-bottom:calc(56.25% + 40px)"><object data="//i.i.cbsi.com/cnwk.1d/av/video/cbsnews/atlantis2/cbsnews_player_embed.swf" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"><param name="flashvars" value="si=254&amp;contentValue='.htmlspecialchars($vars['id'],2).'"></object>';$html.='</span></span>';

		return $html;
	}

	public static function renderCnn($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="cnn" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//fave.api.cnn.io/v1/fav/?video='.htmlspecialchars($vars['id'],2).'&amp;customer=cnn&amp;edition=international&amp;env=prod" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderComedycentral($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="comedycentral" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderDailymotion($vars)
	{
		$vars += array('id' => null, 't' => null);

		$html='<span data-s9e-mediaembed="dailymotion" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.dailymotion.com/embed/video/'.htmlspecialchars($vars['id'],2);if(isset($vars['t']))$html.='?start='.htmlspecialchars($vars['t'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderDemocracynow($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="democracynow" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.democracynow.org/embed/';if((strpos($vars['id'],'/headlines')!==false))$html.='headlines/'.htmlspecialchars(strstr($vars['id'],'/headlines',true),2);elseif((strpos($vars['id'],'2')===0))$html.='story/'.htmlspecialchars($vars['id'],2);elseif((strpos($vars['id'],'shows/')===0))$html.='show/'.htmlspecialchars(substr(strstr($vars['id'],'/'),1),2);else$html.=htmlspecialchars($vars['id'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderDumpert($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="dumpert" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.dumpert.nl/embed/'.htmlspecialchars(strtr($vars['id'],'_','/'),2).'/" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderFacebook($vars)
	{
		$vars += array('id' => null, 'type' => null);

		$html='<iframe data-s9e-mediaembed="facebook" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/facebook.min.html#'.htmlspecialchars($vars['type'].$vars['id'],2).'" style="border:0;height:360px;max-width:640px;width:100%"></iframe>';

		return $html;
	}

	public static function renderGametrailers($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="gametrailers" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//';if((strpos($vars['id'],'mgid:')===0))$html.='media.mtvnservices.com/embed/'.htmlspecialchars($vars['id'],2);else$html.='embed.gametrailers.com/embed/'.htmlspecialchars($vars['id'],2).'?embed=1&amp;suppressBumper=1';$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderGetty($vars)
	{
		$vars += array('et' => null, 'height' => 360, 'id' => null, 'sig' => null, 'width' => 640);

		$html='<span data-s9e-mediaembed="getty" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><span style="display:block;overflow:hidden;position:relative;';if($vars['width']>0)$html.='padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%';$html.='"><iframe allowfullscreen="" scrolling="no" src="//embed.gettyimages.com/embed/'.htmlspecialchars($vars['id'],2).'?et='.htmlspecialchars($vars['et'],2).'&amp;tld=com&amp;sig='.htmlspecialchars($vars['sig'],2).'&amp;caption=false&amp;ver=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderGfycat($vars)
	{
		$vars += array('height' => 360, 'id' => null, 'width' => 640);

		$html='<span data-s9e-mediaembed="gfycat" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><span style="display:block;overflow:hidden;position:relative;';if($vars['width']>0)$html.='padding-bottom:'.htmlspecialchars(100*($vars['height']+44)/$vars['width'],2).'%;padding-bottom:calc('.htmlspecialchars(100*$vars['height']/$vars['width'],2).'% + 44px)';$html.='"><iframe allowfullscreen="" scrolling="no" src="//gfycat.com/ifr/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderGifs($vars)
	{
		$vars += array('height' => 360, 'id' => null, 'width' => 640);

		$html='<span data-s9e-mediaembed="gifs" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><span style="display:block;overflow:hidden;position:relative;';if($vars['width']>0)$html.='padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%';$html.='"><iframe allowfullscreen="" scrolling="no" src="//gifs.com/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderGist($vars)
	{
		$vars += array('id' => null);

		$html='<iframe data-s9e-mediaembed="gist" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="" src="https://s9e.github.io/iframe/2/gist.min.html#'.htmlspecialchars($vars['id'],2).'" style="border:0;height:180px;width:100%"></iframe>';

		return $html;
	}

	public static function renderGoogleplus($vars)
	{
		$vars += array('name' => null, 'oid' => null, 'pid' => null);

		$html='<iframe data-s9e-mediaembed="googleplus" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" style="border:0;height:240px;max-width:450px;width:100%" src="https://s9e.github.io/iframe/2/googleplus.min.html#';if(isset($vars['oid']))$html.=htmlspecialchars($vars['oid'],2);else$html.='+'.htmlspecialchars($vars['name'],2);$html.='/posts/'.htmlspecialchars($vars['pid'],2).'"></iframe>';

		return $html;
	}

	public static function renderGooglesheets($vars)
	{
		$vars += array('gid' => null, 'id' => null, 'oid' => null, 'type' => null);

		$html='';if($vars['type']==='chart')$html.='<span data-s9e-mediaembed="googlesheets" style="display:inline-block;width:100%;max-width:600px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:62%"><iframe allowfullscreen="" scrolling="no" src="https://docs.google.com/spreadsheets/d/'.htmlspecialchars($vars['id'],2).'/pubchart?oid='.htmlspecialchars($vars['oid'],2).'&amp;format=interactive" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';else{$html.='<iframe data-s9e-mediaembed="googlesheets" allowfullscreen="" scrolling="no" style="border:0;height:500px;resize:vertical;width:100%" src="https://docs.google.com/spreadsheets/d/'.htmlspecialchars($vars['id'],2).'/p';if((strpos($vars['id'],'e/')===0))$html.='ubhtml?widget=true&amp;headers=false';else$html.='review';$html.='#gid='.htmlspecialchars($vars['gid'],2).'"></iframe>';}

		return $html;
	}

	public static function renderHudl($vars)
	{
		$vars += array('athlete' => null, 'highlight' => null, 'id' => null);

		$html='<span data-s9e-mediaembed="hudl" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.hudl.com/embed/';if(isset($vars['id']))$html.='video/'.htmlspecialchars($vars['id'],2);else$html.='athlete/'.htmlspecialchars($vars['athlete'],2).'/highlights/'.htmlspecialchars($vars['highlight'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderImgur($vars)
	{
		$vars += array('id' => null, 'type' => null);

		$html='<iframe data-s9e-mediaembed="imgur" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){var d=e.data.split(\' \');style.height=d[0]+\'px\';style.width=d[1]+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" style="border:0;height:400px;max-width:100%;width:542px" src="https://s9e.github.io/iframe/2/imgur.min.html#';if($vars['type']==='album')$html.='a/';$html.=htmlspecialchars($vars['id'],2).'"></iframe>';

		return $html;
	}

	public static function renderInternetarchive($vars)
	{
		$vars += array('height' => 360, 'id' => null, 'width' => 640);

		$html='';if((strpos($vars['id'],'playlist=1')!==false))$html.='<iframe data-s9e-mediaembed="internetarchive" allowfullscreen="" scrolling="no" src="https://archive.org/embed/'.htmlspecialchars($vars['id'],2).'&amp;list_height=150" style="border:0;height:170px;max-width:640px;width:100%"></iframe>';else{$html.='<span data-s9e-mediaembed="internetarchive" style="display:inline-block;width:100%;max-width:'.htmlspecialchars($vars['width'],2).'px"><span style="display:block;overflow:hidden;position:relative;';if($vars['width']>0)$html.='padding-bottom:'.htmlspecialchars(100*$vars['height']/$vars['width'],2).'%';$html.='"><iframe allowfullscreen="" scrolling="no" src="https://archive.org/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';}

		return $html;
	}

	public static function renderKickstarter($vars)
	{
		$vars += array('id' => null, 'video' => null);

		$html='<span data-s9e-mediaembed="kickstarter"';if(isset($vars['video']))$html.=' style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/video.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span>';else$html.=' style="display:inline-block;width:100%;max-width:220px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:190.909091%"><iframe allowfullscreen="" scrolling="no" src="//www.kickstarter.com/projects/'.htmlspecialchars($vars['id'],2).'/widget/card.html" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span>';$html.='</span>';

		return $html;
	}

	public static function renderLivestream($vars)
	{
		$vars += array('account_id' => null, 'channel' => null, 'clip_id' => null, 'event_id' => null, 'video_id' => null);

		$html='<span data-s9e-mediaembed="livestream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//';if(isset($vars['clip_id']))$html.='cdn.livestream.com/embed/'.htmlspecialchars($vars['channel'],2).'?layout=4&amp;autoplay=false&amp;clip='.htmlspecialchars($vars['clip_id'],2);else{$html.='livestream.com/accounts/'.htmlspecialchars($vars['account_id'],2).'/events/'.htmlspecialchars($vars['event_id'],2);if(isset($vars['video_id']))$html.='/videos/'.htmlspecialchars($vars['video_id'],2);$html.='/player?autoPlay=false';}$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderMailru($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="mailru" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://my.mail.ru/video/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderMixer($vars)
	{
		$vars += array('channel' => null, 'vod' => null);

		$html='<span data-s9e-mediaembed="mixer" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="https://mixer.com/embed/player/'.htmlspecialchars($vars['channel'],2);if(isset($vars['vod']))$html.='?vod='.htmlspecialchars($vars['vod'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderMsnbc($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="msnbc" style="display:inline-block;width:100%;max-width:640px"><span';if((strpos($vars['id'],'_')!==false))$html.=' style="display:block;overflow:hidden;position:relative;padding-bottom:68.75%;padding-bottom:calc(56.25% + 80px)"><iframe allowfullscreen="" scrolling="no" src="//player.theplatform.com/p/7wvmTC/MSNBCEmbeddedOffSite?guid=';else$html.=' style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.msnbc.com/msnbc/embedded-video/';$html.=htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe>';$html.='</span></span>';

		return $html;
	}

	public static function renderNhl($vars)
	{
		$vars += array('c' => null, 't' => null);

		$html='<span data-s9e-mediaembed="nhl" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.nhl.com/video/embed';if(isset($vars['t']))$html.='/t-'.htmlspecialchars($vars['t'],2);if(isset($vars['c']))$html.='/c-'.htmlspecialchars($vars['c'],2);$html.='?autostart=false"></iframe></span></span>';

		return $html;
	}

	public static function renderNpr($vars)
	{
		$vars += array('i' => null, 'm' => null);

		$html='<iframe data-s9e-mediaembed="npr" allowfullscreen="" scrolling="no" src="//www.npr.org/player/embed/'.htmlspecialchars($vars['i'],2).'/'.htmlspecialchars($vars['m'],2).'" style="border:0;height:228px;max-width:800px;width:100%"></iframe>';

		return $html;
	}

	public static function renderOrfium($vars)
	{
		$vars += array('album_id' => null, 'playlist_id' => null, 'set_id' => null, 'track_id' => null);

		$html='<iframe data-s9e-mediaembed="orfium" allowfullscreen="" scrolling="no" src="https://www.orfium.com/embedded/';if(isset($vars['album_id']))$html.='album/'.htmlspecialchars($vars['album_id'],2);elseif(isset($vars['playlist_id']))$html.='playlist/'.htmlspecialchars($vars['playlist_id'],2);elseif(isset($vars['set_id']))$html.='live-set/'.htmlspecialchars($vars['set_id'],2);else$html.='track/'.htmlspecialchars($vars['track_id'],2);$html.='" style="border:0;height:';if(isset($vars['album_id']))$html.='550';else$html.='275';$html.='px;max-width:900px;width:100%"></iframe>';

		return $html;
	}

	public static function renderPinterest($vars)
	{
		$vars += array('id' => null);

		$html='<iframe data-s9e-mediaembed="pinterest" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/pinterest.min.html#'.htmlspecialchars($vars['id'],2).'" style="border:0;height:360px;max-width:';if((strpos($vars['id'],'/')!==false))$html.='730';else$html.='345';$html.='px;width:100%"></iframe>';

		return $html;
	}

	public static function renderReddit($vars)
	{
		$vars += array('id' => null, 'path' => null);

		$html='<iframe data-s9e-mediaembed="reddit" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/reddit.min.html#'.htmlspecialchars($vars['id'].$vars['path'],2).'" style="border:0;height:165px;max-width:800px;width:100%"></iframe>';

		return $html;
	}

	public static function renderSoundcloud($vars)
	{
		$vars += array('id' => null, 'playlist_id' => null, 'secret_token' => null, 'track_id' => null);

		$html='<iframe data-s9e-mediaembed="soundcloud" allowfullscreen="" scrolling="no" src="https://w.soundcloud.com/player/?url=';if(isset($vars['playlist_id']))$html.='https%3A//api.soundcloud.com/playlists/'.htmlspecialchars($vars['playlist_id'],2);elseif(isset($vars['track_id']))$html.='https%3A//api.soundcloud.com/tracks/'.htmlspecialchars($vars['track_id'],2).'&amp;secret_token='.htmlspecialchars($vars['secret_token'],2);else{if((strpos($vars['id'],'://')===false))$html.='https%3A//soundcloud.com/';$html.=htmlspecialchars($vars['id'],2);}$html.='" style="border:0;height:';if(isset($vars['playlist_id'])||(strpos($vars['id'],'/sets/')!==false))$html.='450';else$html.='166';$html.='px;max-width:900px;width:100%"></iframe>';

		return $html;
	}

	public static function renderSpotify($vars)
	{
		$vars += array('id' => null, 'path' => null);

		$html='';if((strpos($vars['id'],'episode/')===0)||(strpos($vars['id'],'show/')===0))$html.='<iframe data-s9e-mediaembed="spotify" allow="encrypted-media" allowfullscreen="" scrolling="no" src="https://open.spotify.com/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:152px;max-width:900px;width:100%"></iframe>';else$html.='<span data-s9e-mediaembed="spotify" style="display:inline-block;width:100%;max-width:320px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:125%;padding-bottom:calc(100% + 80px)"><iframe allow="encrypted-media" allowfullscreen="" scrolling="no" src="https://open.spotify.com/embed/'.htmlspecialchars(strtr($vars['id'],':','/').$vars['path'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderSpreaker($vars)
	{
		$vars += array('episode_id' => null, 'show_id' => null);

		$html='<iframe data-s9e-mediaembed="spreaker" allowfullscreen="" scrolling="no" src="https://widget.spreaker.com/player?episode_id='.htmlspecialchars($vars['episode_id'],2).'&amp;show_id='.htmlspecialchars($vars['show_id'],2).'" style="border:0;height:'.htmlspecialchars(400-200*isset($vars['episode_id']),2).'px;max-width:900px;width:100%"></iframe>';

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

		$html='<span data-s9e-mediaembed="ted" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//embed.ted.com/'.htmlspecialchars($vars['id'],2);if((strpos($vars['id'],'.html')===false))$html.='.html';$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderTelegram($vars)
	{
		$vars += array('id' => null);

		$html='<iframe data-s9e-mediaembed="telegram" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/telegram.min.html#'.htmlspecialchars($vars['id'],2).'" style="border:0;height:96px;max-width:500px;width:100%"></iframe>';

		return $html;
	}

	public static function renderTheguardian($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="theguardian" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//embed.theguardian.com/embed/video/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderTumblr($vars)
	{
		$vars += array('id' => null, 'key' => null);

		$html='<iframe data-s9e-mediaembed="tumblr" allowfullscreen="" onload="var c=new MessageChannel;c.port1.onmessage=function(e){style.height=e.data+\'px\'};contentWindow.postMessage(\'s9e:init\',\'https://s9e.github.io\',[c.port2])" scrolling="no" src="https://s9e.github.io/iframe/2/tumblr.min.html#'.htmlspecialchars($vars['key'],2).'/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:300px;max-width:520px;width:100%"></iframe>';

		return $html;
	}

	public static function renderTwentyfoursevensports($vars)
	{
		$vars += array('player_id' => null, 'video_id' => null);

		$html='';if(isset($vars['video_id']))$html.='<span data-s9e-mediaembed="twentyfoursevensports" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="https://www.cbssports.com/video/player/embed?args=player_id%3D'.htmlspecialchars($vars['video_id'],2).'%26channel%3Dmobilevideo%26pcid%3D'.htmlspecialchars($vars['video_id'],2).'%26width%3D640%26height%3D360%26autoplay%3Dfalse%26comp_ads_enabled%3Dfalse%26uvpc%3Dhttp%3A%2F%2Fsports.cbsimg.net%2Fvideo%2Fuvp%2Fconfig%2Fv4%2Fuvp_247sports.xml%26uvpc_m%3Dhttp%3A%2F%2Fsports.cbsimg.net%2Fvideo%2Fuvp%2Fconfig%2Fv4%2Fuvp_247sports_m.xml%26partner%3D247%26partner_m%3D247_mobile%26utag%3D247sportssite%26resizable%3Dtrue" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';else$html.='<iframe data-s9e-mediaembed="twentyfoursevensports" allowfullscreen="" onload="window.addEventListener(\'message\',function(e){if(e.source===this.contentWindow&amp;&amp;e.data.height)this.style.height=e.data.height+\'px\'})" scrolling="no" src="https://247sports.com/PlayerSport/'.htmlspecialchars($vars['player_id'],2).'/Embed/" style="border:0;height:200px;max-width:600px;width:100%"></iframe>';

		return $html;
	}

	public static function renderTwitch($vars)
	{
		$vars += array('channel' => null, 'clip_id' => null, 't' => null, 'video_id' => null);

		$html='<span data-s9e-mediaembed="twitch" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//';if(isset($vars['clip_id'])){$html.='clips.twitch.tv/embed?autoplay=false&amp;clip=';if(isset($vars['channel']))$html.=htmlspecialchars($vars['channel'],2).'/';$html.=htmlspecialchars($vars['clip_id'],2);}else{$html.='player.twitch.tv/?autoplay=false&amp;';if(isset($vars['video_id']))$html.='video=v'.htmlspecialchars($vars['video_id'],2);else$html.='channel='.htmlspecialchars($vars['channel'],2);if(isset($vars['t']))$html.='&amp;time='.htmlspecialchars($vars['t'],2);}$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderUstream($vars)
	{
		$vars += array('cid' => null, 'vid' => null);

		$html='<span data-s9e-mediaembed="ustream" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.ustream.tv/embed/';if(isset($vars['vid']))$html.='recorded/'.htmlspecialchars($vars['vid'],2);else$html.=htmlspecialchars($vars['cid'],2);$html.='?html5ui"></iframe></span></span>';

		return $html;
	}

	public static function renderVimeo($vars)
	{
		$vars += array('id' => null, 't' => null);

		$html='<span data-s9e-mediaembed="vimeo" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//player.vimeo.com/video/'.htmlspecialchars($vars['id'],2);if(isset($vars['t']))$html.='#t='.htmlspecialchars($vars['t'],2);$html.='"></iframe></span></span>';

		return $html;
	}

	public static function renderVk($vars)
	{
		$vars += array('hash' => null, 'oid' => null, 'vid' => null);

		$html='<span data-s9e-mediaembed="vk" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//vk.com/video_ext.php?oid='.htmlspecialchars($vars['oid'],2).'&amp;id='.htmlspecialchars($vars['vid'],2).'&amp;hash='.htmlspecialchars($vars['hash'],2).'&amp;hd=1" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderVocaroo($vars)
	{
		$vars += array('id' => null);

		$html='<span data-s9e-mediaembed="vocaroo"';if((strpos($vars['id'],'s0')===0)||(strpos($vars['id'],'s1')===0))$html.=' style="display:inline-block;width:100%;max-width:148px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:29.72973%"><object data="//vocaroo.com/player.swf?playMediaID='.htmlspecialchars($vars['id'],2).'&amp;autoplay=0" style="height:100%;left:0;position:absolute;width:100%" type="application/x-shockwave-flash" typemustmatch=""><param name="allowfullscreen" value="true"></object></span>';else$html.=' style="display:inline-block;width:100%;max-width:300px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:20%"><iframe allowfullscreen="" scrolling="no" src="https://vocaroo.com/embed/'.htmlspecialchars($vars['id'],2).'" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span>';$html.='</span>';

		return $html;
	}

	public static function renderXboxclips($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<span data-s9e-mediaembed="xboxclips" style="display:inline-block;width:100%;max-width:560px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//gameclips.io/'.htmlspecialchars($vars['user'],2).'/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderXboxdvr($vars)
	{
		$vars += array('id' => null, 'user' => null);

		$html='<span data-s9e-mediaembed="xboxdvr" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" src="//gamerdvr.com/gamer/'.htmlspecialchars($vars['user'],2).'/video/'.htmlspecialchars($vars['id'],2).'/embed" style="border:0;height:100%;left:0;position:absolute;width:100%"></iframe></span></span>';

		return $html;
	}

	public static function renderYoutube($vars)
	{
		$vars += array('id' => null, 'list' => null, 't' => null);

		$html='<span data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><span style="display:block;overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="background:url(https://i.ytimg.com/vi/'.htmlspecialchars($vars['id'],2).'/hqdefault.jpg) 50% 50% / cover;border:0;height:100%;left:0;position:absolute;width:100%" src="https://www.youtube.com/embed/'.htmlspecialchars($vars['id'],2);if(isset($vars['list']))$html.='?list='.htmlspecialchars($vars['list'],2);if(isset($vars['t'])){if(isset($vars['list']))$html.='&amp;';else$html.='?';$html.='start='.htmlspecialchars($vars['t'],2);}$html.='"></iframe></span></span>';

		return $html;
	}
}