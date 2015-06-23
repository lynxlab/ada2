<?php
/**
 * LOGIN MODULE - config page for ldap login provider
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
require_once MODULES_LOGIN_PATH.'/include/management/ldapManagement.inc.php';

$GLOBALS['dh'] = AMALoginDataHandler::instance();

$retArray = array('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed ldap config data
	 */
	// build a ldapconfig with passed POST data
	$ldapManager = new ldapManagement($_POST);
	// try to save it
	$res = $GLOBALS['dh']->saveLDAP($ldapManager->toArray());

	if (AMA_DB::isError($res)) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// redirect to ldapconfig page
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Fonte salvata');
	}	
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && 
			isset($_GET['id_ldap']) && intval(trim($_GET['id_ldap']))>0) {
	/**
	 * it's a GET with an id_ldap, load it and display
	 */
	$id_ldap = intval(trim($_GET['id_ldap']));
	// try to load it
	$res = $GLOBALS['dh']->getLDAP($id_ldap);
	
	if (AMA_DB::isError($res)) {
		// if it's an error display the error message without the form
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// display the form with loaded data
		$ldapManager = new ldapManagement($res);
		$data = $ldapManager->run(MODULES_LOGIN_EDIT_LDAP);
		
		$retArray['status'] = "OK";
		$retArray['html'] = $data['htmlObj']->getHtml();
		$retArray['dialogTitle'] = translateFN('Modifica Fonte');
	}
} else {
	/**
	 * it's a get without an id_ldap, display the empty form
	 */
	$ldapManager = new ldapManagement();
	$data = $ldapManager->run(MODULES_LOGIN_EDIT_LDAP);
	
	$retArray['status'] = "OK";
	$retArray['html'] = $data['htmlObj']->getHtml();
	$retArray['dialogTitle'] = translateFN('Nuova Fonte');	
}

echo json_encode($retArray);