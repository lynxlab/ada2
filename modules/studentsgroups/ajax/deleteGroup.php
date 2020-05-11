<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\StudentsGroups\AMAStudentsGroupsDataHandler;
use Lynxlab\ADA\Module\StudentsGroups\StudentsGroupsActions;

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
	 * it's a POST, delete the passed group by id
	 */
    $postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	$res = $GLOBALS['dh']->deleteGroup($postParams);

	if (AMA_DB::isError($res) || $res instanceof \Exception) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Gruppo cancellato');
	}
}

header('Content-Type: application/json');
echo json_encode($retArray);
