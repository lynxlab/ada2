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
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LOGIN_PATH .'/config/config.inc.php';

$GLOBALS['dh'] = AMALoginDataHandler::instance();

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (!isset($_POST['id_ldap'])) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Non so cosa cancellare"));
	else
	{
		$result = $GLOBALS['dh']->deleteLDAP (intval($_POST['id_ldap']));
		
		if (!AMA_DB::isError($result))
		{		
			$retArray = array ("status"=>"OK", "msg"=>translateFN("Fonte cancellata"));
		}
		else
			$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore di cancellazione") );
	}
}
else {
	$retArray = array ("status"=>"ERROR", "msg"=>trasnlateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
?>