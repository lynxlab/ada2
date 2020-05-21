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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	$selTester = null;
	if (isset($_SESSION['sess_selected_tester'])) {
		$selTester = $_SESSION['sess_selected_tester'];
	} else {
		switch ($_SESSION['sess_userObj']->getType()) {
			case AMA_TYPE_STUDENT:
				if (isset($courseID) && intval($courseID)>0) {
					$selTesterArr = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseID);
					if (!AMA_DB::isError($selTesterArr) && is_array($selTesterArr) && isset($selTesterArr['puntatore'])) {
						$selTester = $selTesterArr['puntatore'];
					}
				}
				break;
			default:
				$selTester = $_SESSION['sess_userObj']->getDefaultTester();
				break;
		}
	}

	$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($selTester));
	$dh = $GLOBALS['dh'];

	if (isset($instanceID) && intval($instanceID)>0) {
		$instanceID = intval($instanceID);
	} else $instanceID=null; // null means to get all instances

	$filterInstanceState =  (isset($filterInstanceState) && intval($filterInstanceState)>0) ?
		$filterInstanceState : MODULES_CLASSAGENDA_ALL_INSTANCES;

	if (is_null($instanceID)) {
		/**
		 * take care of active only instances only if
		 * we've been asked to get all instances
		 */
		// first of all, get the coure list
		$courseList = $dh->get_courses_list(array('id_corso'));
		// first element of returned array is always the courseId, array is NOT assoc
		if (!AMA_DB::isError($courseList)) {
			// for each course in the list...
			foreach ($courseList as $courseItem) {
				// ... get the subscribeable course instance list...
				if ($filterInstanceState == MODULES_CLASSAGENDA_STARTED_INSTANCES) {
					$courseInstances = $dh->course_instance_find_list(array('title'), 'id_corso='.$courseItem[0].
							' AND data_inizio>0 and durata>0');
				}
				else if ($filterInstanceState == MODULES_CLASSAGENDA_NONSTARTED_INSTANCES) {
					$courseInstances = $dh->course_instance_find_list(array('title'), 'id_corso='.$courseItem[0].
							' AND data_inizio<=0');
				}
				else {
					$courseInstances = $dh->course_instance_get_list(array('title'), $courseItem[0]);
				}
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

	$start = (isset($_REQUEST['start']) && intval($_REQUEST['start'])>0) ? intval($_REQUEST['start']) :0;
	$end = (isset($_REQUEST['end']) && intval($_REQUEST['end'])>0) ? intval($_REQUEST['end']) :0;

	$result = $GLOBALS['dh']->getClassRoomEventsForCourseInstance($instanceID, $venueID, $start, $end);
	if(!AMA_DB::isError($result)) {
		// convert return array to data structure needed by calendar component
		$i=0;
		$retArray = array();
		foreach ($result as $eventID=>$aResult) {
			$retArray[$i]['id'] = $eventID;
			$retArray[$i]['instanceID'] = (int) $aResult['id_istanza_corso'];
			$retArray[$i]['classroomID'] = ((int) $aResult['id_classroom'] > 0) ? (int) $aResult['id_classroom'] : null;
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