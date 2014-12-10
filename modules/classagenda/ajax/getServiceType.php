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

$retVal = translateFN('Tipo di corso sconosciuto');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && 
	isset($instanceID) && intval($instanceID)>0) {
	
	$retArray = null;
	
	// first of all, get the course
	$courseID = $GLOBALS['dh']->get_course_id_for_course_instance(intval($instanceID));	
	if (!AMA_DB::isError($courseID) && intval($courseID)>0) {
		$serviceArr = $GLOBALS['common_dh']->get_service_info_from_course(intval($courseID));
		if (!AMA_DB::isError($serviceArr)) {
			// 3 is service level, get it as int and string
			
			$retArray['isOnline'] = $serviceArr[3]==ADA_SERVICE_ONLINECOURSE;
			switch ($serviceArr[3]) {
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
	}
}
if (!is_null($retArray)) die (json_encode($retArray));