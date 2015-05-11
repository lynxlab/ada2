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

// MODULE's OWN IMPORTS

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
	
	if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
		require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
		$classroomAPI = new classroomAPI($selTester);
		$venues = $classroomAPI->getAllVenues();
		if (!AMA_DB::isError($venues) && is_array($venues) && count($venues)>0) {
			foreach ($venues as $venue) {
				$dataAr[$venue['id_venue']] = $venue['name'];
			}
			reset($dataAr);
				
			/**
			 * venues html select element
			*/
			$venuesSELECT = BaseHtmlLib::selectElement2('id:venuesList,name:venuesList',$dataAr,key($dataAr));
			die($venuesSELECT->getHtml());
		}
	}	
}
