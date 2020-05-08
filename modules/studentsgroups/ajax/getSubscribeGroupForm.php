<?php

/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\StudentsGroups\AMAStudentsGroupsDataHandler;
use Lynxlab\ADA\Module\StudentsGroups\SubscribeGroupForm;
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

$retArray = array('status' => 'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed group data
	 */
	// try to save it
	$res = $GLOBALS['dh']->saveSubscribeGroup($_POST);

	if (AMA_DB::isError($res) || $res instanceof StudentsGroupsException) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Gruppo iscritto alla classe');
		if (is_array($res)) {
			$retArray['msg'] .= '<br/>'.sprintf("%d studenti totali: %d nuove iscrizioni, %d già iscritti",
				$res['alreadySubscribed'] + $res['subscribed'], $res['subscribed'] , $res['alreadySubscribed']);
		}
	}
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	$res = $GLOBALS['dh']->findAll('Groups', ['label' => 'ASC']);
	if (!AMA_DB::isError($res)) {
		if (is_array($res) && count($res) > 0) {
			// display the form with loaded data
			$form = new SubscribeGroupForm('subscribegroups', null, $res);
			$retArray['status'] = "OK";
			$retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
			$retArray['dialogTitle'] = translateFN('Iscrivi Gruppo');
		} else {
			$retArray['status'] = "ERROR";
			$retArray['msg'] = AMA_DB::isError($res) ? $res->getMessage() : translateFN('Nessun gruppo trovato');
		}
	} else {
		// if it's an error display the error message without the form
		$retArray['status'] = "ERROR";
		$retArray['msg'] = AMA_DB::isError($res) ? $res->getMessage() : translateFN('Errore caricamento gruppi');
	}
}

header('Content-Type: application/json');
echo json_encode($retArray);
