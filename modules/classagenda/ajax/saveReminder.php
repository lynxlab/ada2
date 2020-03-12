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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

// MODULE's OWN IMPORTS
// require_once MODULES_CLASSAGENDA_PATH.'/config/config.inc.php';

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['reminderEventID']) && intval($_POST['reminderEventID'])>0) {
		if (isset($_POST['reminderEventHTML']) && strlen(trim($_POST['reminderEventHTML']))>0) {

			$result = $GLOBALS['dh']->saveReminderForEvent(intval($_POST['reminderEventID']),trim($_POST['reminderEventHTML']));

			if (!AMA_DB::isError($result) && intval($result)>0) {
				$retArray = array("status"=>"OK", "reminderID"=>$result, "msg"=>translateFN("Promemoria salvato e inviato"));
			} else {
				$retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore nel salvataggio"));
			}

		} else {
			$retArray = array("status"=>"ERROR", "msg"=>translateFN("Testo promemoria vuoto"));
		} // if isset html
	} else {
		$retArray = array("status"=>"ERROR", "msg"=>translateFN("Selezionare un evento"));
	} // if isset eventID
} // if method is POST

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);