<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout', 'course', 'course_instance'),
		AMA_TYPE_TUTOR =>    array('layout', 'course', 'course_instance'),
		AMA_TYPE_STUDENT =>  array('layout', 'course', 'course_instance')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/switcher/include/switcher_functions.inc.php');
SwitcherHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_CLASSAGENDA_PATH .'/config/config.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/AMAClassagendaDataHandler.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/management/calendarsManagement.inc.php';

$self = whoami();

if (isset($_GET['type']) && $_GET['type']=='csv') $type='csv';
else $type='pdf';

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$calendarsManager = new calendarsManagement();
$data = $calendarsManager->exportCalendar($courseObj, $courseInstanceObj, $type);

if ($type=='pdf') {
	$content_dataAr = array(
			'coursename' => $courseObj->getTitle(),
			'instancename' => $courseInstanceObj->getTitle(),
			'data' => (!is_null($data) && isset($data['htmlObj'])) ? $data['htmlObj']->getHtml() : translateFN('Nessun evento trovato')
	);
	$GLOBALS['adafooter'] = translateFN(PDF_EXPORT_FOOTER);
	ARE::render($layout_dataAr, $content_dataAr, ARE_PDF_RENDER, array('outputfile'=>$courseInstanceObj->getTitle()) );
} else if ($type=='csv') {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset='.strtolower(ADA_CHARSET));
	header('Content-Disposition: attachment; filename='.$courseInstanceObj->getTitle().'.csv');
	$out = fopen('php://output', 'w');
	foreach ($data as $row) fputcsv($out, $row);
	fclose($out);
}
die();
?>
