<?php
/**
 * LOGIN MODULE
 *
 * @package     login module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015-2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

use AMA_DB;
use Lynxlab\ADA\Module\Login\AMALoginDataHandler;
use Lynxlab\ADA\Module\Login\loginProviderManagement;

ini_set('display_errors', '0'); error_reporting(E_ALL);
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

$GLOBALS['dh'] = AMALoginDataHandler::instance();

$retArray = array('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed options config data
	 */
	// build an optionManager with passed POST data
	$loginProviderManager = new loginProviderManagement($_POST);
	$res = $GLOBALS['dh']->saveLoginProvider($loginProviderManager->toArray());

	if (AMA_DB::isError($res)) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// redirect to config page
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Login Provider salvato');
	}
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
			isset($_GET['provider_id']) && intval(trim($_GET['provider_id']))>0) {
	/**
	 * it's a GET with an provider_id, load it and display
	 */
	$provider_id = intval(trim($_GET['provider_id']));
	// try to load it
	$res = $GLOBALS['dh']->getLoginProvider($provider_id);

	if (AMA_DB::isError($res)) {
		// if it's an error display the error message without the form
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// display the form with loaded data
		$optionsManager = new loginProviderManagement($res);
		$data = $optionsManager->run(MODULES_LOGIN_EDIT_LOGINPROVIDER);

		$retArray['status'] = "OK";
		$retArray['html'] = $data['htmlObj']->getHtml();
		$retArray['dialogTitle'] = translateFN('Modifica Login Provider');
	}
} else {
	/**
	 * it's a get without a provider_id, display the empty form
	 */
	$optionsManager = new loginProviderManagement();
	$data = $optionsManager->run(MODULES_LOGIN_EDIT_LOGINPROVIDER);

	$retArray['status'] = "OK";
	$retArray['html'] = $data['htmlObj']->getHtml();
	$retArray['dialogTitle'] = translateFN('Nuovo Login Provider');
}

echo json_encode($retArray);
