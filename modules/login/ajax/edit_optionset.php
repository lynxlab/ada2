<?php
/**
 * LOGIN MODULE - config page for login provider
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

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
require_once MODULES_LOGIN_PATH .'/config/config.inc.php';

$GLOBALS['dh'] = AMALoginDataHandler::instance();

$retArray = array('status'=>'ERROR');

/**
 * guess which management class is needed by inspecting the passed providerClassName
 */
$optionsClassName = null;
if (isset($_REQUEST['providerClassName']) && strlen($_REQUEST['providerClassName'])>0) {
	$type = trim($_REQUEST['providerClassName']);
	if (in_array($type, abstractLogin::getLoginProviders(true))) {		
		require_once MODULES_LOGIN_PATH . '/include/'.$type.'.class.inc.php';
		$optionsClassName = $type::MANAGEMENT_CLASS;
	}
}

if (!is_null($optionsClassName)) {
	
	require_once MODULES_LOGIN_PATH.'/include/management/'.$optionsClassName.'.inc.php';

	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		/**
		 * it's a POST, save the passed options config data
		 */
		// build an optionManager with passed POST data
		$optionsManager = new $optionsClassName($_POST);
		// try to save it
		if (is_a($optionsManager, 'ldapManagement')) {
			$res = $GLOBALS['dh']->saveOptionSet($optionsManager->toArray());
			$editElement = 'Fonte';  // translatedFN delayed when building msg
		} else {
			/**
			 * check if passed data are OK before saving
			 */
			if (is_null($optionsManager->value)) {
				// user has edited a key
				if ($optionsManager->key === $optionsManager->newkey) die($key);
				else if (strlen($optionsManager->newkey)<=0) {
					$retArray['status'] = "ERROR";
					$retArray['msg'] = translateFN('La chiave non può essere vuota');
					$retArray['displayValue'] = $optionsManager->key;
					die (json_encode($retArray));
				}
			}
			
			$res = $GLOBALS['dh']->saveOptionByKey($optionsManager->toArray());
			$editElement = 'Chiave'; // translatedFN delayed when building msg
		}
	
		if (AMA_DB::isError($res)) {
			// if it's an error display the error message
			$retArray['status'] = "ERROR";
			$retArray['msg'] = $res->getMessage();
		} else {
			// redirect to config page
			$retArray['status'] = "OK";
			$retArray['msg'] = translateFN($editElement.' salvata');
			if (is_string($res) && strlen($res)>0) $retArray['displayValue'] = $res;
		}	
	} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && 
				isset($_GET['option_id']) && intval(trim($_GET['option_id']))>0) {
		/**
		 * it's a GET with an option_id, load it and display
		 */
		$option_id = intval(trim($_GET['option_id']));
		// try to load it
		$res = $GLOBALS['dh']->getOptionSet($option_id);
		
		if (AMA_DB::isError($res)) {
			// if it's an error display the error message without the form
			$retArray['status'] = "ERROR";
			$retArray['msg'] = $res->getMessage();
		} else {
			// display the form with loaded data
			$optionsManager = new $optionsClassName($res);
			$data = $optionsManager->run(MODULES_LOGIN_EDIT_OPTIONSET);
			
			$retArray['status'] = "OK";
			$retArray['html'] = $data['htmlObj']->getHtml();
			$retArray['dialogTitle'] = translateFN('Modifica '.$editElement);
		}
	} else {
		/**
		 * it's a get without an option_id, display the empty form
		 */
		$optionsManager = new $optionsClassName();
		$data = $optionsManager->run(MODULES_LOGIN_EDIT_OPTIONSET);
		
		$retArray['status'] = "OK";
		$retArray['html'] = $data['htmlObj']->getHtml();
		$retArray['dialogTitle'] = translateFN('Nuova '.$editElement);
	}
}
echo json_encode($retArray);