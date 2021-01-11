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
 *  name="circularImage"   optional,  value: use a circular image for post's user.values: 0 or nonzero
 *                                           if invalid or omitted, image will not be circular
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

require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/TwitterAPIExchange.php';
require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/TwitterAccess.inc.php';

if (!isset($screen_name)) die ('no twitter timeline to load');
if (!isset($count) || !is_numeric($count)) $count=20;
if (!isset($showImage) || !is_numeric($showImage)) $showImage = 0;
if (!isset($circularImage) || !is_numeric($circularImage)) $circularImage=0;

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name='.$screen_name.'&count='.$count;
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$twDatas =  json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());

$baseUserLink = "https://twitter.com/";
$baseHashTagLink = $baseUserLink."search?q=%23<HASHTAG>&src=hash";


$baseLinkHref = '<a href="<URL>" rel="nofollow" dir="ltr" data-expanded-url="<EXPANDED_URL>" target="_blank" title="<EXPANDED_URL>"><DISPLAY_URL></a>';
$searchForBuildURL = array ('<URL>','<EXPANDED_URL>','<DISPLAY_URL>');

$twitterMain = CDOMElement::create('div','class:ui basic feed segment');
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
	if (property_exists($twitterAr, 'retweeted_status') && is_object($twitterAr->retweeted_status))
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

	$firstLine = "<a href='".$baseUserLink.$screenname."' target='_blank'>$username <s>@</s>".$screenname."</a>";

	// makes the hashtags links
	$curr=0;
	if (is_object($twitterAr->entities) &&  $twitterAr->entities->hashtags) {
		foreach ($twitterAr->entities->hashtags as $hastag)
		{
			$hashText[++$curr] = $hastag->text;
			$linkHref = str_replace("<HASHTAG>", $hashText[$curr], $baseHashTagLink);
			$hashLinks[$curr] ="<a href='".$linkHref."' target='_blank'><s>#</s>".$hashText[$curr]."</a>";
			$hashText[$curr] = '#'.$hashText[$curr];
		}
	}
	$displayText = str_replace($hashText, $hashLinks, $displayText);

	// makes the link address links
	$curr=0;
	$urlsObj = $isRetweeded ? $twitterAr->retweeted_status->entities->urls : $twitterAr->entities->urls;
	if ($urlsObj) {
		foreach ($urlsObj as $url)
		{
			$searchURLs[++$curr] = $url->url;
			$replaceForBuildURL = array ($searchURLs[$curr], $url->expanded_url, $url->display_url);
			$replaceURLs[$curr] = str_replace($searchForBuildURL, $replaceForBuildURL, $baseLinkHref);
		}
	}
	$displayText = str_replace($searchURLs, $replaceURLs, $displayText);

	// makes media links
	$curr=0;

	$mediaObj = false;
	if ($isRetweeded && property_exists($twitterAr->retweeted_status->entities, 'media')) {
		$mediaObj = $twitterAr->retweeted_status->entities->media;
	} else if (property_exists($twitterAr->entities, 'media')) {
		$mediaObj = $twitterAr->entities->media;
	}

	if ($mediaObj) {
		foreach ($mediaObj as $aMedia)
		{
			$mediaSearchURLs[++$curr] = $aMedia->url;
			$replaceForBuildURL = array ($mediaSearchURLs[$curr], $aMedia->expanded_url, $aMedia->display_url);
			$mediaReplaceURLs[$curr] = str_replace($searchForBuildURL, $replaceForBuildURL, $baseLinkHref);
		}
	}
	$displayText = str_replace($mediaSearchURLs, $mediaReplaceURLs, $displayText);

	// makes the username links
	$curr=0;
	if ($twitterAr->entities->user_mentions) {
		foreach ($twitterAr->entities->user_mentions as $user)
		{
			$searchUser[++$curr] = $user->screen_name;
			$replaceUser[$curr] = "<a href='".$baseUserLink.$searchUser[$curr]."' target='_blank'><s>@</s>".$searchUser[$curr]."</a>";
			$searchUser[$curr] = "@".$searchUser[$curr];
		}
	}
	$displayText = str_replace($searchUser, $replaceUser, $displayText);

	$twitterDIV = CDOMElement::create('div','class:event');
	$twitterText = CDOMElement::create('div','class:content');
	/**
	 * TODO: add date? to twitterText
	 */

	$twitterSummary = CDOMElement::create('div','class:summary');
	$twitterText->addChild($twitterSummary);

	$twitterHeader = CDOMElement::create('div','class:header');
	$twitterHeader->addChild(new CText($firstLine));
	$twitterSummary->addChild($twitterHeader);

	$textEl = CDOMElement::create('div','class:extra text');
	$twitterSummary->addChild($textEl);
	$textEl->addChild(new CText($displayText));

	if ($showImage)
	{
		$imgUrl = ($isRetweeded) ? $twitterAr->retweeted_status->user->profile_image_url :  $twitterAr->user->profile_image_url;
		$imgUrl = preg_replace('#^https?://#', '', rtrim($imgUrl,'/'));
		$protocol = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$protocol .= 's';
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
			$protocol .= 's';
		}
		$protocol .= '://';
		$twitterLabel = CDOMElement::create('div','class:label');
		$twitterImage = CDOMElement::create('img','src:'.$protocol.$imgUrl);
		$twitterImage->setAttribute('class', 'ui mini image'.($circularImage ? ' circular' : ''));

		$twitterLabel->addChild($twitterImage);
		$twitterDIV->addChild($twitterLabel);
	}
	$twitterDIV->addChild($twitterText);

	$twitterMain->addChild($twitterDIV);
}

/**
 * Common output in sync or async mode
 */
 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return $twitterMain->getHtml();
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo $twitterMain->getHtml();

}
?>
