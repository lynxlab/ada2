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

$retVal['isOverlap'] = false;
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($start) && strlen($start)>0 && isset($end) && strlen($end)>0 && isset($tutorID) && intval($tutorID)>0) {
		
		$eventID = (isset($eventID) && intval($eventID)>0) ? intval($eventID) : null;
		
		list ($startDate, $startTime) = explode('T', $start);
		list ($endDate, $endTime) = explode('T', $end);
		
		list ($startYear, $startMonth, $startDay) = explode('-', $startDate);
		list ($endYear, $endMonth, $endDay) = explode('-', $endDate);
		
		$result = $dh->checkTutorOverlap($dh->date_to_ts($startDay.'/'.$startMonth.'/'.$startYear,$startTime),
										 $dh->date_to_ts($endDay.'/'.$endMonth.'/'.$endYear,$endTime),
										 intval($tutorID), $eventID);
		
		if (!AMA_DB::isError($result) && $result!==false && count($result)>0) {
			$retVal['isOverlap'] = true;
			$retVal['data'] = $result;
			$retVal['data']['date'] = ts2dFN($result['start']);
			$retVal['data']['start'] = substr(ts2tmFN($result['start']),0,-3);
			$retVal['data']['end'] = substr(ts2tmFN($result['end']),0,-3);
			
			$courseInstance = $dh->course_instance_get($result['id_istanza_corso']);
			if (!AMA_DB::isError($courseInstance)) {
				$course = $dh->get_course_info_for_course_instance($result['id_istanza_corso']);
				if (!AMA_DB::isError($course)) {
					$retVal['data']['instanceName'] = $course['titolo'].' &gt; '.$courseInstance['title'];
				}
			}
		}
	}
}
die (json_encode($retVal));