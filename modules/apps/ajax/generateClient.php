<?php
/**
 * generateClient.php
 *
 * @package        generateClient
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           generateClient
 * @version		   0.1
 */

/**
 * This is called via ajax by the module's index page when the user
 * requests for a client id/client secret pair.
 * It generates a new pair and if there's not an existing one for the
 * passed user id, will save and return it. If there's an existing pair
 * for the user id, just return it.
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');

// MODULE's OWN IMPORTS

$dh = AMAAppsDataHandler::instance();

/**
 * TODO: Your own code here
 */

/**
 * Check if passed user is a real swithcer
 */
$userArr = $dh->get_user_info(intval($userID));

if (!AMA_DB::isError($userArr) && $userArr['tipo']==AMA_TYPE_SWITCHER)
{
	$clientArray = $dh->saveClientIDAndSecret(generateConsumerIdAndSecret(),intval($userArr['id']));

	if (!AMA_DB::isError($clientArray)) {
		$output = CDOMElement::create('div','class:appsecret');
			$span = CDOMElement::create('span','class:clientIDLabel');
			$span->addChild (new CText('clientID: '));
		$output->addChild ($span);
			$span = CDOMElement::create('span','class:clientID');
			$span->addChild (new CText($clientArray['client_id']));
		$output->addChild ($span);
		$output->addChild (new CText(' - '));
			$span = CDOMElement::create('span','class:clientSecretLabel');
			$span->addChild (new CText('clientSecret: '));
		$output->addChild ($span);
			$span = CDOMElement::create('span','class:clientSecret');
			$span->addChild (new CText($clientArray['client_secret']));
		$output->addChild ($span);
		echo $output->getHtml();
	}
	else print_r($clientArray);

} else {
	$output = CDOMElement::create('div','class:appsecreterror');
	$output->addChild (new CText(translateFN('Passed user does not look like a valid Swithcer')));
	echo $output->getHtml();
}
?>