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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array ('status'=>'ERROR');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	require_once MODULES_CLASSAGENDA_PATH .'/include/form/formEventReminder.php';

	$formName = 'formEventReminder';
	$formAction = 'javascript:saveAndSendReminder();';
	$dataArr = array();
	
	if (isset($_GET['eventID']) && strlen($_GET['eventID'])>0)
		$dataArr['reminderEventID'] = $_GET['eventID'];
	
	$htmlContent = $GLOBALS['dh']->getLastEventReminderHMTL();
	
	if ($htmlContent === false &&
			is_file(MODULES_CLASSAGENDA_REMINDER_HTML) &&
			is_readable(MODULES_CLASSAGENDA_REMINDER_HTML)) {
		$htmlContent = file_get_contents(MODULES_CLASSAGENDA_REMINDER_HTML);
		if ($htmlContent!==false) $dataArr['reminderEventHTML'] = $htmlContent;
	} else {
		$dataArr['reminderEventHTML'] = $htmlContent;
	}
	
	$theForm = new FormEventReminder(count($dataArr) ? $dataArr : null, $formName, $formAction);
	
	if ($theForm->isValid()) {
		$retArray = array ('status'=>'OK', 'html'=>$theForm->getHtml());
	} else $retArray ['html'] = 'ERROR: Invalid form';
	
}
die (json_encode($retArray));