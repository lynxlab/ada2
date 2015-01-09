<?php
/**
 * RSS Feed reader for Facebook
 * uses: simplepie.inc.php
 *
 * @package		widget
 * @author		Stefano Penge <steve@lynxlab.com> 
 * @author		giorgio <g.consorti@lynxlab.com>
 * 
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *   
 *  name="id"			   mandatory, value: facebook user id of the displayed timeline
 *  name="format"		   optional,  value: feed format to be loaded, possible values: "atom10" or "rss20"
 *                                           if invalid or omitted, atom10 will be used
 *  name="showDescription" optional,  value: shows or hides the post description. values: 0 or nonzero
 *                                           if invalid or omitted, description will be hidden
 *	name="count"		   optional,  value: how many posts to display
 *                                           if invalid or omitted 20 posts are displayed 
 */

/**
 * Common initializations and include files
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
	if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;
} else {
	/**
	 * checks and inits to be done if this has been called in sync mode
	 * (i.e. with a file inclusion)
	 */
	$widgetMode = ADA_WIDGET_SYNC_MODE;
}

/**
 * Your code starts here
 */

require_once ROOT_DIR.'/widgets/ajax/include/simplepie.inc.php';

if (!isset($id)) die ('na facebook feed to load');
if (!isset($format)) $format = "atom10";
else if (!in_array($format, array('atom10','rss20'))) $format = "atom10";
if (!isset($showDescription) || !is_numeric($showDescription)) $showDescription=0;
if (!isset($count) || !is_numeric($count)) $count=20;

$url = "http://www.facebook.com/feeds/page.php?format=$format&id=$id";

$spObj = new simplepie();
$spObj->set_feed_url($url);
$spObj->init();
// $spObj->set_cache_location('./cache')
// $spObj->enable_cache(true);
$rss_items = array();
$i = 0;
foreach($spObj->get_items() as $item) {
	$title = html_entity_decode($item->get_title() );
	if ($title=='' && !$showDescription) continue;
	
	$facebookDIV = CDOMElement::create('div','class:facebookContainer');
		$facebookLINK = CDOMElement::create('a','href:'.$item->get_link());
		$facebookLINK->setAttribute('target', '_blank');
		$facebookLINK->addChild(new CText($title));
	$facebookDIV->addChild($facebookLINK);
	
	if ($showDescription)
	{
		$facebookDIV->addChild(new CText('<br class="clearfix" />'));
		$facebookDIV->addChild(new CText($item->get_description()));
	}
	
	$rss_items[] = 	$facebookDIV->getHtml();
	if (++$i>=$count) break;
}

/**
 * Common output in sync or async mode
 */
 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return implode('<br class="clearfix" />', $rss_items);
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo implode('<br class="clearfix" />', $rss_items);
		
}
?>
