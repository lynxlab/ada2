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

$retVal = '<option value=0>'.translateFN('Nessuna istanza trovata').'</option>';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	$filterInstanceState =  (isset($filterInstanceState) && intval($filterInstanceState)>0) ?
		$filterInstanceState : MODULES_CLASSAGENDA_ALL_INSTANCES;
	
	$dh = $GLOBALS['dh'];
	// grab some course and course instance datas to build up the form properly
	$returnHTML = '';
	
	// first of all, get the coure list
	$courseList = $dh->find_courses_list(array('titolo'),'1 ORDER BY titolo ASC');
	// first element of returned array is always the courseId, array is NOT assoc
	if (!AMA_DB::isError($courseList)) {
		// for each course in the list...
		foreach ($courseList as $courseItem) {
			// ... get the subscribeable course instance list...
			if ($filterInstanceState == MODULES_CLASSAGENDA_STARTED_INSTANCES) {
				$whereClause = 'id_corso='.$courseItem[0].' AND data_inizio>0 AND data_fine>='.time().' and durata>0 ORDER BY title ASC';
			}
			else if ($filterInstanceState == MODULES_CLASSAGENDA_NONSTARTED_INSTANCES) {
				$whereClause = 'id_corso='.$courseItem[0].' AND data_inizio<=0 ORDER BY title ASC';
			}
			else if ($filterInstanceState == MODULES_CLASSAGENDA_CLOSED_INSTANCES) {
				$whereClause = 'id_corso='.$courseItem[0].' AND data_fine<'.time().' ORDER BY title ASC';
			}
			else {
				$whereClause = 'id_corso='.$courseItem[0].' ORDER BY title ASC';
			}
			$courseInstances = $dh->course_instance_find_list(array('title'), $whereClause);
			// first element of returned array is always the instanceId, array is NOT assoc
			if (!AMA_DB::isError($courseInstances) && count($courseInstances)>0) {
				// ...and, for each subscribeable instance in the list...
				foreach ($courseInstances as $courseInstanceItem) {
					// ... put its ID and human readble course instance name, course title and course name as an <option> in the <select>
					$returnHTML .= '<option value='.$courseInstanceItem[0].'>'.$courseItem[1] . ' > '.$courseInstanceItem[1].'</option>';
				}
			}
		}
	}
	
	if (strlen($returnHTML)>0) die ($returnHTML);
	
}
die ($retVal);