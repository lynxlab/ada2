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


$retArray['serviceTypeString'] = translateFN('Tipo di corso sconosciuto');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
	isset($instanceID) && intval($instanceID)>0 && isset($courseID) && intval($courseID)>0) {

	$selTester = null;
	if (isset($_SESSION['sess_selected_tester'])) {
		$selTester = $_SESSION['sess_selected_tester'];
	} else {
		switch ($_SESSION['sess_userObj']->getType()) {
			case AMA_TYPE_STUDENT:
				$selTesterArr = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseID);
				if (!AMA_DB::isError($selTesterArr) && is_array($selTesterArr) && isset($selTesterArr['puntatore'])) {
					$selTester = $selTesterArr['puntatore'];
				}
				break;
			default:
				$selTester = $_SESSION['sess_userObj']->getDefaultTester();
				break;
		}
	}

	$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($selTester));
	$retArray = null;

	$courseInstanceObj = read_course_instance_from_DB($instanceID);

	if (!AMA_DB::isError($courseInstanceObj) && $courseInstanceObj instanceof Course_instance) {
		$retArray['courseID'] = intval($courseID);
		$retArray['duration_hours'] = $courseInstanceObj->getDurationHours();
		$eventsArr = $GLOBALS['dh']->getClassRoomEventsForCourseInstance($instanceID, null);
		$retArray['allocated_hours'] = 0;
		$retArray['lessons_count'] = 0;
		$retArray['endDate'] = $courseInstanceObj->getEndDate();
		$serviceLevel = $courseInstanceObj->getServiceLevel();
		if (is_null($serviceLevel)) $serviceLevel = DEFAULT_SERVICE_TYPE;
		/**
		 * service level online or presence as bool,
		 * $GLOBALS defined in config/config_main.inc.php
		 */
		$retArray['isOnline']   = in_array($serviceLevel, $GLOBALS['onLineServiceTypes']);
		$retArray['isPresence'] = in_array($serviceLevel, $GLOBALS['presenceServiceTypes']);

		/**
		 * service level as a string
		 */
		if(isset($_SESSION['service_level'][$serviceLevel])){
			$retArray['serviceTypeString'] = $_SESSION['service_level'][$serviceLevel];
		} else {
			switch ($serviceLevel) {
				case ADA_SERVICE_ONLINECOURSE:
					$retArray['serviceTypeString'] = translateFN('Corso Online');
					break;
				case ADA_SERVICE_PRESENCECOURSE:
					$retArray['serviceTypeString'] = translateFN('Corso in Presenza');
					break;
				case ADA_SERVICE_MIXEDCOURSE:
					$retArray['serviceTypeString'] = translateFN('Corso misto Online e Presenza');
					break;
			}
		}

		if (!AMA_DB::isError($eventsArr) && is_array($eventsArr) && count($eventsArr)>0) {
			$retArray['lessons_count'] = count($eventsArr);
			foreach ($eventsArr as $event) {
				$retArray['allocated_hours'] += $event['end'] - $event['start'];
			}
			$retArray['allocated_hours'] *= 1000;
		}
	} else {
		$retArray['duration_hours'] = 0;
	}
}
if (!is_null($retArray)) die (json_encode($retArray));