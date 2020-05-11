<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\StudentsGroups\AMAStudentsGroupsDataHandler;
use Lynxlab\ADA\Module\StudentsGroups\GroupForm;
use Lynxlab\ADA\Module\StudentsGroups\Groups;
use Lynxlab\ADA\Module\StudentsGroups\StudentsGroupsActions;
use Lynxlab\ADA\Module\StudentsGroups\StudentsGroupsException;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(StudentsGroupsActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMAStudentsGroupsDataHandler $GLOBALS['dh']
 */
$GLOBALS['dh'] = AMAStudentsGroupsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed group data
	 */
	// try to save it
	$res = $GLOBALS['dh']->saveGroup($_POST);

	if (AMA_DB::isError($res) || $res instanceof StudentsGroupsException) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// redirect to classrooms page
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Gruppo salvato');
		if (is_array($res) && array_key_exists('importResults', $res)) {
			$retArray['msg'] .= '<br/>'.sprintf("%d studenti totali: %d nuovi, %d esistenti<br/>%d errori (%d password non valide)",
				$res['importResults']['total'], $res['importResults']['registered'], $res['importResults']['duplicates'], $res['importResults']['errors'], $res['importResults']['invalidpasswords']);

		}
	}
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['id']) && intval($_GET['id'])>0) {
		// try to load
		$res = $GLOBALS['dh']->findBy('Groups', [ 'id' => intval(trim($_GET['id'])) ]);
		if (!AMA_DB::isError($res) && is_array($res) && count($res)===1) {
			$group = reset($res);
			// display the form with loaded data
			$form = new GroupForm('editgroup', null, $group);
			$retArray['status'] = "OK";
			$retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
			$retArray['dialogTitle'] = translateFN('Modifica Gruppo');
		} else {
			// if it's an error display the error message without the form
			$retArray['status'] = "ERROR";
			$retArray['msg'] = AMA_DB::isError($res) ? $res->getMessage() : translateFN('Errore caricamento gruppo');
		}
	} else {
		/**
		 * display the empty form
		 */
		$form = new GroupForm('editgroup', null, new Groups());
		$retArray['status'] = "OK";
		$retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
		$retArray['dialogTitle'] = translateFN('Nuovo Gruppo');
	}
}

header('Content-Type: application/json');
echo json_encode($retArray);
