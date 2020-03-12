<?php
use Lynxlab\ADA\Module\Badges\BadgesActions;
use Lynxlab\ADA\Module\Badges\AMABadgesDataHandler;

/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS
// require_once MODULES_BADGES_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(BadgesActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMABadgesDataHandler $GLOBALS['dh']
 */
$GLOBALS['dh'] = AMABadgesDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));


$retArray = array('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed badge data
	 */
    // try to save it
    $postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	$res = $GLOBALS['dh']->deleteCourseBadge($postParams);

	if (AMA_DB::isError($res) || $res instanceof \Exception) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// redirect to badges page
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Associazione cancellata');
	}
}

header('Content-Type: application/json');
echo json_encode($retArray);
