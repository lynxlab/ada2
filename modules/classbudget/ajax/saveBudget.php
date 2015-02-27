<?php
/**
 * CLASSBUDGET MODULE.
 *
 * @package        classbudget module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2015, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classbudget
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
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
require_once MODULES_CLASSBUDGET_PATH . '/include/AMAClassbudgetDataHandler.inc.php';

$GLOBALS['dh'] = AMAClassbudgetDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && 
	isset($_POST['type']) && strlen($_POST['type'])>0) {
	// $data is an array coming from post, $type is a string coming from post
	if (isset($data) && is_array($data) && count($data)>0) {
		$res = $GLOBALS['dh']->saveCosts($data, trim($type));
		if (AMA_DB::isError($res)) {
			$retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore nel salvataggio"));
		} else {
			$retArray = array("status"=>"OK", "msg"=>translateFN("Costi salvati").'<br/><br/>'.translateFN('Attendere il ricaricamento della pagina').'...', "callback"=>"self.document.location.reload();");
		}		
	} else {
		$retArray = array("status"=>"OK", "msg"=>translateFN("Niente da salvare"));
	}
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);