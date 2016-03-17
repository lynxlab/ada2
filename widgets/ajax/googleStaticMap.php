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
 *  name="url"			   mandatory, value: map url to be loaded
 *  please pass your own url built accordingly to:
 *  https://developers.google.com/maps/documentation/static-maps/intro#URL_Parameters
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

require_once ADA_WIDGET_AJAX_ROOTDIR .'/include/GoogleAccess.inc.php';

if (!isset($url)) die ('No url  to be loaded.');
if (!isset($width) || !is_numeric($width)) $width = 360;
if (!isset($height) || !is_numeric($height)) $height = 213;
if (!isset($zoom) || !is_numeric($zoom)) $zoom = 17;


if (isset($settings['staticmapAPI']) && isset($settings['staticmapAPI']['apiKEY'])) {

	$url = $url.'&key='.$settings['staticmapAPI']['apiKEY'];

	//parse the url
	$url = parse_url($url);

	$urlToSign =  $url['path'] . "?" . $url['query'];
	$originalUrl = $url['scheme'] . "://" . $url['host'] . $url['path'] . "?" . $url['query'];

	if (isset($settings['staticmapAPI']['privateKEY']) && strlen($settings['staticmapAPI']['privateKEY'])>0) {
		// Decode the private key into its binary format
		$decodedKey = base64_decode(str_replace(array('-', '_'), array('+', '/'), $settings['staticmapAPI']['privateKEY']));
		// Create a signature using the private key and the URL-encoded
		// string using HMAC SHA1. This signature will be binary.
		$signature = hash_hmac("sha1", $urlToSign, $decodedKey,true);
		//make encode Signature and make it URL Safe
		$encodedSignature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
		if (strlen($encodedSignature)>0) {
			$signedURL = $originalUrl.'&signature='.$encodedSignature;
		} else {
			die ('Something went wrong while signing your URL');
		}
	} else $signedURL = $originalUrl;

	$output = CDOMElement::create('img','src:'.$signedURL)->getHtml();

} else die ('Non static map credentials found.');


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
