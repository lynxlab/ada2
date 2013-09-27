<?php
/**
 * Twitter Timeline reader
 * uses: TwitterAPIExchange.php
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
 * 	name="screen_name"	   mandatory, value: twitter username of the displayed timeline
 *  name="showImage"       optional,  value: shows or hides the user image. values: 0 or nonzero
 *                                           if invalid or omitted, image will be hidden  
 *	name="count"		   optional,  value: how many tweets to display 
 *                                           if invalid or omitted 20 tweets are displayed
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

require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/TwitterAPIExchange.php';
require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/TwitterAccess.inc.php';

if (!isset($screen_name)) die ('no twitter timeline to load');
if (!isset($count) || !is_numeric($count)) $count=20;
if (!isset($showImage) || !is_numeric($showImage)) $showImage = 0;

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name='.$screen_name.'&count='.$count;
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$twDatas =  json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());

$baseUserLink = "https://twitter.com/";
$baseHashTagLink = $baseUserLink."search?q=%23<HASHTAG>&src=hash";


$baseLinkHref = '<a href="<URL>" rel="nofollow" dir="ltr" data-expanded-url="<EXPANDED_URL>" target="_blank" title="<EXPANDED_URL>"><DISPLAY_URL></a>';
$searchForBuildURL = array ('<URL>','<EXPANDED_URL>','<DISPLAY_URL>');

$items = array();
foreach($twDatas as $k=>$twitterAr) {

// 	var_dump ($twitterAr->user->profile_image_url);
// 	if ($k>0) die();
	
	$hashText = array();
	$hashLinks = array();
	
	$searchURLs  = array();
	$replaceURLs = array();
	
	$searchUser = array();
	$replaceUser = array();
	
	$mediaSearchURLs = array();
	$mediaReplaceURLs = array();
	
	// checks if it's retweeted
	if (is_object($twitterAr->retweeted_status))
	{
		$username = $twitterAr->retweeted_status->user->name;
		$screenname = $twitterAr->retweeted_status->user->screen_name;		
		$displayText = $twitterAr->retweeted_status->text;
		$isRetweeded = true;
	}
	else
	{
		$username = $twitterAr->user->name;
		$screenname = $twitterAr->user->screen_name;
		$displayText = $twitterAr->text;
		$isRetweeded = false;
	}
	
	$firstLine = "<a href='".$baseUserLink.$screenname."' target='_blank'><h4 style='display:inline;'>$username</h4> <s>@</s>".$screenname."</a><br/>";
	
	// makes the hashtags links
	$curr=0;
	foreach ($twitterAr->entities->hashtags as $hastag)
	{
		$hashText[++$curr] = $hastag->text;
		$linkHref = str_replace("<HASHTAG>", $hashText[$curr], $baseHashTagLink);	
		$hashLinks[$curr] ="<a href='".$linkHref."' target='_blank'><s>#</s>".$hashText[$curr]."</a>";
		$hashText[$curr] = '#'.$hashText[$curr];		
	}	
	$displayText = str_replace($hashText, $hashLinks, $displayText); 
	
	// makes the link address links
	$curr=0;
	$urlsObj = $isRetweeded ? $twitterAr->retweeted_status->entities->urls : $twitterAr->entities->urls;
	foreach ($urlsObj as $url)
	{
		$searchURLs[++$curr] = $url->url;		
		$replaceForBuildURL = array ($searchURLs[$curr], $url->expanded_url, $url->display_url);		
		$replaceURLs[$curr] = str_replace($searchForBuildURL, $replaceForBuildURL, $baseLinkHref);
	}	
	$displayText = str_replace($searchURLs, $replaceURLs, $displayText);
	
	// makes media links
	$curr=0;
	$mediaObj = $isRetweeded ? $twitterAr->retweeted_status->entities->media : $twitterAr->entities->media;
	foreach ($mediaObj as $aMedia)
	{
		$mediaSearchURLs[++$curr] = $aMedia->url;
		$replaceForBuildURL = array ($mediaSearchURLs[$curr], $aMedia->expanded_url, $aMedia->display_url);
		$mediaReplaceURLs[$curr] = str_replace($searchForBuildURL, $replaceForBuildURL, $baseLinkHref);		 
	}
	$displayText = str_replace($mediaSearchURLs, $mediaReplaceURLs, $displayText);
	
	// makes the username links
	$curr=0;
	foreach ($twitterAr->entities->user_mentions as $user)
	{
		$searchUser[++$curr] = $user->screen_name;
		$replaceUser[$curr] = "<a href='".$baseUserLink.$searchUser[$curr]."' target='_blank'><s>@</s>".$searchUser[$curr]."</a>";
		$searchUser[$curr] = "@".$searchUser[$curr];
	}
	$displayText = str_replace($searchUser, $replaceUser, $displayText);
	
	$twitterDIV = CDOMElement::create('div','class:twitterContainer');
	$twitterText = CDOMElement::create('div','class:twitterText');
	$twitterText->addChild(new CText($firstLine.$displayText));
	
	if ($showImage)
	{
		$imgUrl = ($isRetweeded) ? $twitterAr->retweeted_status->user->profile_image_url :  $twitterAr->user->profile_image_url;
		
		$twitterImage = CDOMElement::create('div','class:twitterImage');
		$twitterImage->setAttribute('style', 'float:left');
		$twitterImage->addChild(CDOMElement::create('img','src:'.$imgUrl));
		$twitterText->setAttribute('style', 'margin-left:55px');
		$twitterDIV->addChild($twitterImage);
	}	
	$twitterDIV->addChild($twitterText);
	
	$items[] = $twitterDIV->getHtml();
}

/**
 * Common output in sync or async mode
 */
 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return implode('<br class="clearfix" />', $items);
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo implode('<br class="clearfix" />', $items);
		
}
?>
