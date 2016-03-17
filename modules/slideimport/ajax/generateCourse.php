<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';

// MODULE's OWN IMPORTS
require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/functions.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/AMASlideimportDataHandler.inc.php';

$courseID = -1;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['courseName']) && strlen(trim($_POST['courseName']))>0){

	$courseArr = array(
			'nome' => generateRandomString(8),
			'titolo' => trim($_POST['courseName']),
			'descr' => '',
			'd_create' => ts2dFN(time()),
			'd_publish' => null,
			'id_autore' => $userObj->getId(),
			'id_nodo_toc' => 0,
			'id_nodo_iniziale' => 0,
			'media_path' => null,
			'id_lingua' => $userObj->getLanguage(),
			'static_mode' => 0,
			'crediti' => 0,
			'duration_hours' => 0,
			'service_level' => DEFAULT_SERVICE_TYPE
	);

	$rename_count = 3;
	do {
		$courseNewID = $GLOBALS['dh']->add_course($courseArr);
		if (AMA_DB::isError($courseNewID))
		{
			$courseArr['nome'] = generateRandomString(8);
			$rename_count--;
		}
	} while (AMA_DB::isError($courseNewID) && $rename_count>=0);

	if (!AMA_DB::isError($courseNewID)) {
		// add a row in common.servizio
		$service_dataAr = array(
				'service_name' => trim($_POST['courseName']),
				'service_description' => '',
				'service_level' => DEFAULT_SERVICE_TYPE,
				'service_duration'=> 0,
				'service_min_meetings' => 0,
				'service_max_meetings' => 0,
				'service_meeting_duration' => 0
		);
	}

	$id_service = $GLOBALS['common_dh']->add_service($service_dataAr);
	if(!AMA_DB::isError($id_service)) {
		$tester_infoAr = $GLOBALS['common_dh']->get_tester_info_from_pointer($_SESSION['sess_selected_tester']);
		if(!AMA_DB::isError($tester_infoAr)) {
			$id_tester = $tester_infoAr[0];
			$result = $common_dh->link_service_to_course($id_tester, $id_service, $courseNewID);
			if(AMA_DB::isError($result)) {
				$courseNewID = -1;
			}
		}
	}
}
header('Content-Type: application/json');
echo json_encode (array('courseID'=>$courseNewID));
?>