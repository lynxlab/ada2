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
 *  Specifiy either:
 *  name="fbname"          mandatory, value: facebook user or pagename owning the posts to be loaded
 *  or:
 *  name="id"			   mandatory, value: facebook user id of the displayed timeline
 *
 *  If both passed, id will be used
 *
 *  name="showDescription" optional,  value: shows or hides the post description. values: 0 or nonzero
 *                                           if invalid or omitted, description will be hidden
 *  name="showImage"       optional,  value: shows or hides the post's user image.values: 0 or nonzero
 *                                           if invalid or omitted, image will be hidden
 *  name="circularImage"   optional,  value: use a circular image for post's user.values: 0 or nonzero
 *                                           if invalid or omitted, image will not be circular
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
define ('FACEBOOK_CONFIG_FILE', 'include/FacebookAccess.inc.php');
require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/facebook-php-graph-sdk-5.x/autoload.php';
include_once ADA_WIDGET_AJAX_ROOTDIR . DIRECTORY_SEPARATOR . FACEBOOK_CONFIG_FILE ;

if (!isset($settings) || (isset($settings) && (!isset($settings['app_id']) || !isset($settings['app_secret'])))) {
	die ('please configure Facebook access in ' . FACEBOOK_CONFIG_FILE);
}

$fb = new Facebook\Facebook($settings);
$fb->setDefaultAccessToken($fb->getApp()->getAccessToken());
$output = '';

try {

	if (isset($fbname) && !isset($id)) {
		$queryParam = $fbname;
	} else if (isset($id)) {
		$queryParam = $id;
	}

	if (isset($queryParam)) {
		// retreive the id from Facebook
		$tmpArr = $fb->get($queryParam.'/')->getDecodedBody();
		if (isset($tmpArr['id']) && strlen($tmpArr['id'])>0) {
			$id = $tmpArr['id'];
		}
	}

	if (!isset($id)) $output = 'no facebook id to load post found';
	else {
		if (isset($tmpArr['name']) && strlen($tmpArr['name'])>0) {
			$facebookName = $tmpArr['name'];
		} else {
			$facebookName = 'UNKNOWN';
		}

		$fbBaseUrl = 'https://www.facebook.com/'.(isset($fbname)?$fbname:$id);

		// URL Regular Expression
		$reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

		if (!isset($showDescription) || !is_numeric($showDescription)) $showDescription=0;
		if (!isset($count) || !is_numeric($count)) $count=20;
		if (!isset($showImage) || !is_numeric($showImage)) $showImage=0;
		if (!isset($circularImage) || !is_numeric($circularImage)) $circularImage=0;

		// get posts for $id
		$tmpArr = $fb->get($id.'/posts')->getDecodedBody();
		if (is_array($tmpArr['data']) && count($tmpArr['data'])>0) {
			$items = array_filter($tmpArr['data'], function($item) {
				/**
				 * items to display must not have a story (that is a shared post)
				 * and must have a message (a text to display)
				 */
				return !isset($item['story']) && isset($item['message']) && strlen($item['message'])>0;
			});
		} else $items = array();

		/**
		 * Get user image real url if needed
		 */
		if ($showImage) {
			/**
			 * Getting the picture endpoint with facebook SDK is resulting
			 * in an OAuth invalid token, let's do this old style
			 */
			$tmpArr = json_decode(file_get_contents('http://graph.facebook.com/'.$id.'/picture?redirect=false'),true);

			if (is_array($tmpArr['data']) && isset($tmpArr['data']['url']) && strlen($tmpArr['data']['url'])>0) {
				// build a CDOMElement for later use
				$userImage = CDOMElement::create('img','src:'.$tmpArr['data']['url']);
				$userImage->setAttribute('class', 'ui image'.($circularImage ? ' circular' : ''));
			} else $showImage = 0;
		}

		if (count($items)>0) {
			$listDIV = CDOMElement::create('div','class:ui basic feed segment');
			foreach ($items as $item) {
				$itemDIV = CDOMElement::create('div','class:event"');
				if ($showImage) {
					$labelDIV = CDOMElement::create('div','class:label');
					$labelDIV->addChild($userImage);
					$itemDIV->addChild($labelDIV);
				}
				$contentDIV = CDOMElement::create('div','class:content');
				$itemDIV->addChild($contentDIV);
				$listDIV->addChild($itemDIV);

				if (isset($item['created_time']) && strlen($item['created_time'])>0) {
					// Facebook outputs dates is ISO8601 format - e.g.: 2011-09-02T18:00:00
					$dateDIV = CDOMElement::create('div','class:date');
					$contentDIV->addChild($dateDIV);
					$dateDIV->addChild(new CText(ts2dFN(strtotime($item['created_time']))));
				}

				$summaryDIV = CDOMElement::create('div','class:summary');
				$contentDIV->addChild($summaryDIV);

				if (isset($item['id']) && strlen($item['id'])>0) {
					list ($userid, $postid) = explode('_', $item['id']);
					if (strlen($postid)>0) {
						$headerDIV = CDOMElement::create('div','class:header');
						$summaryDIV->addChild($headerDIV);
						$link = BaseHtmlLib::link($fbBaseUrl.'/posts/'.$postid, $facebookName);
						$link->setAttribute('target', '_blank');
						$headerDIV->addChild($link);
					}
				}

				if ($showDescription && isset($item['message']) && strlen($item['message'])>0) {
					// Check if there is a url in the message
					if(preg_match($reg_exUrl, $item['message'], $url)) {
						// make the urls hyper links
						$item['message'] = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a> ', $item['message']);
					}
					$textEl = CDOMElement::create('div','class:extra text');
					$summaryDIV->addChild($textEl);
					$textEl->addChild(new CText($item['message']));
				}

				// if items to output falls below 0, break the loop
				if (--$count===0) break;
			}
			$output = $listDIV->getHtml();
		} else {
			$output = 'No post found';
		}
	}
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  $output = 'Facebook SDK returned an error: ' . $e->getMessage();
}

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
