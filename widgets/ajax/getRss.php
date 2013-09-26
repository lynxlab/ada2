<?php
/* RSS */
/* uses: simplepie.inc.php 
 * http://simplepie.org
 * 
 */ 
ini_set('display_errors', '0'); error_reporting(E_ALL);

require_once realpath(dirname(__FILE__)).'/../../config_path.inc.php';
require_once ROOT_DIR.'/widgets/include/widget_includes.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	/**
	 * checks and inits to be done if this has been called in async mode
	 * (i.e. with a get request)
	 */
	if(isset($_SERVER['HTTP_REFERER'])){
		if(preg_match("#^".HTTP_ROOT_DIR."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1){
			die ('Only local execution allowed.');
		}
	}
	
	extract ($_GET);
	
	$var = ${constant(PREFISSO)."url"};

	
	if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;		
} else {
	/**
	 * checks and inits to be done if this has been called in sync mode
	 * (i.e. with a file inclusion)
	 */
	$widgetMode = ADA_WIDGET_SYNC_MODE;
}

// needed for BaseHtmlLib!

require_once ROOT_DIR.'/widgets/ajax/include/simplepie.inc.php';

//  define ('BLOG_URL','http://ecm.lynxlab.com');
//  define ('BLOG_NAME','ecm.lynxlab.com');
//  define ('BLOG_IMG','http://ada.lynxlab.com/ecm/wp-content/uploads/2013/03/ecmNews+subtitle-short.png');

if (!isset($url)) die ('No url  to be loaded.');

$spObj = new simplepie();
$spObj->set_feed_url($url);
$spObj->init();
// $spObj->set_cache_location('./cache')
// $spObj->enable_cache(true);

$rss_items = array();
foreach($spObj->get_items() as $item) {
    $href = $item->get_link() ;
	$title = $item->get_title() ;	
	$rss_link =  BaseHtmlLib::link($href,$title);
	$rss_items[] = 	$rss_link->getHTML();
}
$rss_msg = BaseHtmlLib::plainListElement('',$rss_items,false);

// $href = BLOG_URL;
// $title = "<img src='".BLOG_IMG."' height='50px'alt='".BLOG_NAME."'/>";

// $rss_title =  BaseHtmlLib::link($href,$title);
// $rss_string = $rss_title->getHtml().$rss_msg->getHtml();

$rss_string = $rss_msg->getHtml();

 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return $rss_string;
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo $rss_string;
		
}
?>
