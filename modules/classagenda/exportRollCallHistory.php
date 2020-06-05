<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2020, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classagenda
 * @version			0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('layout',  'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
	AMA_TYPE_SWITCHER => array('layout', 'course', 'course_instance'),
	AMA_TYPE_TUTOR =>    array('layout', 'course', 'course_instance'),
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/switcher/include/switcher_functions.inc.php');
SwitcherHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_CLASSAGENDA_PATH.'/include/AMAClassagendaDataHandler.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/management/rollcallManagement.inc.php';

$self = whoami();
$type='csv';
$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

// $id_course_instance is coming from $_GET
$rollcallManager = new rollcallManagement($id_course_instance);
$expData = $rollcallManager->exportRollCallHistory();

if ($type=='csv') {
	$data = [];
	if (count($expData)>0) {
		if (array_key_exists('header', $expData)) {
			array_push($data, $expData['header']);
		}
		foreach($expData['studentsList'] as $rows) {
			array_push($data, $rows);
		}
	}

	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset='.strtolower(ADA_CHARSET));
	header('Content-Disposition: attachment; filename='.$courseInstanceObj->getTitle().'.csv');
	$out = fopen('php://output', 'w');
	foreach ($data as $row) fputcsv($out, $row);
	fclose($out);
}
die();
?>
