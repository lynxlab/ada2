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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($instanceID) && intval($instanceID)>0) {
		$instanceID = intval($instanceID);
	} else $instanceID=null; // null means to get all instances

	if (isset($venueID) && intval($venueID)>0) {
		$venueID = intval($venueID);
	} else $venueID=null; // null means to get all classrooms
	
	$result = $GLOBALS['dh']->getClassRoomEventsForCourseInstance($instanceID, $venueID);
	if(!AMA_DB::isError($result)) {
		// convert return array to data structure needed by calendar component
		$i=0;
		foreach ($result as $eventID=>$aResult) {
			$retArray[$i]['id'] = $eventID;
			$retArray[$i]['instanceID'] = $aResult['id_istanza_corso'];
			$retArray[$i]['classroomID'] = $aResult['id_classroom'];
			$retArray[$i]['tutorID'] = $aResult['id_utente_tutor'];
			$retArray[$i]['isSelected'] = boolval(false);
			
			list ($day, $month, $year) = explode ('/',ts2dFN($aResult['start']));
			$retArray[$i]['start'] = $year.'-'.$month.'-'.$day.'T'.ts2tmFN($aResult['start']);
			
			list ($day, $month, $year) = explode ('/',ts2dFN($aResult['end']));
			$retArray[$i]['end'] = $year.'-'.$month.'-'.$day.'T'.ts2tmFN($aResult['end']);
			
			$i++;
		}
		die (json_encode($retArray));
	}
}
die ();