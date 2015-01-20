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

$dh = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($instanceID) && intval($instanceID)>0) {
		$instanceID = intval($instanceID);
	} else $instanceID=null; // null means to get all instances
	
	if (is_null($instanceID) && (isset($activeOnly) && intval($activeOnly)>0)) {
		/**
		 * take care of active only instances only if
		 * we've been asked to get all instances
		 */
		// first of all, get the coure list
		$courseList = $dh->get_courses_list(array());
		// first element of returned array is always the courseId, array is NOT assoc
		if (!AMA_DB::isError($courseList)) {
			// for each course in the list...
			foreach ($courseList as $courseItem) {
				// ... get the subscribeable course instance list...
				$courseInstances = $dh->course_instance_subscribeable_get_list(array('title'), $courseItem[0]);
				// first element of returned array is always the instanceId, array is NOT assoc
				if (!AMA_DB::isError($courseInstances)) {
					// ...and, for each subscribeable instance in the list...
					foreach ($courseInstances as $courseInstanceItem) {
						if (is_null($instanceID)) $instanceID = array();
						// ... put its ID in the instanceID array
						$instanceID[] = $courseInstanceItem[0];
					}
				}
			}
		}
	}

	if (isset($venueID) && intval($venueID)>0) {
		$venueID = intval($venueID);
	} else $venueID=null; // null means to get all classrooms
	
	$result = $GLOBALS['dh']->getClassRoomEventsForCourseInstance($instanceID, $venueID);
	if(!AMA_DB::isError($result)) {
		// convert return array to data structure needed by calendar component
		$i=0;
		foreach ($result as $eventID=>$aResult) {
			$retArray[$i]['id'] = $eventID;
			$retArray[$i]['instanceID'] = (int) $aResult['id_istanza_corso'];
			$retArray[$i]['classroomID'] = (int) $aResult['id_classroom'];
			$retArray[$i]['tutorID'] = (int) $aResult['id_utente_tutor'];
			$retArray[$i]['isSelected'] = false;
			if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true && !is_null($aResult['id_venue'])) {
				$retArray[$i]['venueID'] = (int) $aResult['id_venue'];
			} else $retArray[$i]['venueID'] = null;
			
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