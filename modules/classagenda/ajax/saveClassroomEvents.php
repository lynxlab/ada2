<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
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

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['instanceID']) && intval($_POST['instanceID'])>0) {
		
		if (isset($_POST['venueID']) && intval($_POST['venueID'])>0) {
			$venueID = intval($venueID);
		} else $venueID = null;

		if (isset($_POST['events']) && is_array($_POST['events']) && count($_POST['events'])>0) {
			$postEvents = $_POST['events'];
		} else $postEvents = null;
		
		$result = $GLOBALS['dh']->saveClassroomEvents(intval($_POST['instanceID']),$venueID,
													  $postEvents);
		
		if (!AMA_DB::isError($result) && $result===true) {
			$retArray = array("status"=>"OK", "msg"=>translateFN("Calendario salvato"));
		} else {
			$retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore nel salvataggio"));
		}
		
	} else {
		$retArray = array("status"=>"ERROR", "msg"=>translateFN("Selezionare un'istanza di corso"));
	}	
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);