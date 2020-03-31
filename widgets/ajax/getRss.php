<?php
/**
 * RSS Feed reader
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
 *  name="url"			   mandatory, value: rss url to be loaded
 *  name="showDescription" optional,  value: shows or hides the post description. values: 0 or nonzero
 *                                           if invalid or omitted, description will be hidden
 *	name="count"		   optional,  value: how many posts to display
 *                                           if invalid or omitted all returned entries are displayed
 *  name="headerLink"      optional,  value: if set, a link with passed url will be built and put as
 *                                           RSS listing header
 *  name="headerTitle"     optional,  value: if the link will be generated, it will take the passed title
 *                                           and as link text
 *                                           if omitted, headerLink will be used
 *  name="headerImage"     optional,  value: if the link will be generated, it will have the passed image
 *                                           as a clickable element.
 *                                           if omitted, headerTitle will be used
 *
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
		if(preg_match("#^".trim(HTTP_ROOT_DIR,"/")."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1){
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

if (!isset($url)) die ('No url  to be loaded.');
if (!isset($showDescription) || !is_numeric($showDescription)) $showDescription=0;
if (!isset($count) || !is_numeric($count)) $count=PHP_INT_MAX;
if (!isset($headerImage)) $headerImage = false;
if (!isset($headerTitle)) $headerTitle = false;
if (!isset($headerLink))  $headerLink  = false;

$spObj = new simplepie();
$spObj->set_feed_url($url);
$spObj->init();
// $spObj->set_cache_location('./cache')
// $spObj->enable_cache(true);

// $rssDIV = CDOMElement::create('div','class:RSSContainer');

if ($headerLink) {

	$headerHREF = CDOMElement::create('a','href:'.$headerLink);
	$headerHREF->setAttribute('target', '_blank');
	$headerHREF->setAttribute('class', 'RSSheader');
	if ($headerTitle) $headerHREF->setAttribute('title', $headerTitle);

	if ($headerImage)
	{
		$headerIMG = CDOMElement::create('img','src:'.$headerImage);
		$headerIMG->setAttribute('class', 'RSSheader');
		$headerIMG->setAttribute('height', '50px');
		if ($headerTitle) $headerIMG->setAttribute('alt', $headerTitle);
		$headerHREF->addChild($headerIMG);
	} else {
		$headerHREF->addChild(new CText($headerTitle ? $headerTitle : $headerLink));
	}
// 	$rssDIV->addChild ($headerHREF);
}

$rss_items = array();
$i=0;
$clearfix = CDOMElement::create('div','class:clearfix')->getHtml();

foreach($spObj->get_items() as $item) {
	$title = $item->get_title() ;
	if ($title=='' && !$showDescription) continue;

	$rssCONTENT = CDOMElement::create('div','class:RSSContent');
		$rssLINK = CDOMElement::create('a','href:'.$item->get_link());
		$rssLINK->setAttribute('target', '_blank');
		$rssLINK->addChild(new CText($title));
	$rssCONTENT->addChild($rssLINK);

	if ($showDescription)
	{
		$rssCONTENT->addChild(new CText($clearfix));
		$rssCONTENT->addChild(new CText($item->get_description()));
	}

// 	$rssDIV->addChild($rssCONTENT);
// 	$rssDIV->addChild(new CText('<br class="clearfix" />'));
	$rss_items[] = $rssCONTENT->getHtml();
	if (++$i>=$count) break;
}

$output = '';

if ($headerLink) $output = $headerHREF->getHtml().$clearfix;

$output .= implode($clearfix, $rss_items);

/**
 * Common output in sync or async mode
 */
 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return $output;
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo $output;

}
?>
