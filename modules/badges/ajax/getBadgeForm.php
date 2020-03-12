<?php
use Ramsey\Uuid\Uuid;
use Lynxlab\ADA\Module\Badges\BadgesActions;
use Lynxlab\ADA\Module\Badges\AMABadgesDataHandler;
use Lynxlab\ADA\Module\Badges\BadgeForm;
use Lynxlab\ADA\Module\Badges\Badge;

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
	$res = $GLOBALS['dh']->saveBadge($_POST);

	if (AMA_DB::isError($res)) {
		// if it's an error display the error message
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// redirect to classrooms page
		$retArray['status'] = "OK";
		$retArray['msg'] = translateFN('Badge salvato');
	}
} else

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['uuid']) && Uuid::isValid($_GET['uuid'])) {
		// try to load
		$res = $GLOBALS['dh']->findBy('Badge', [ 'uuid' => trim($_GET['uuid']) ]);
		if (!AMA_DB::isError($res) && is_array($res) && count($res)===1) {
			$badge = reset($res);
			// display the form with loaded data
			$form = new BadgeForm('editbadge', null, $badge);
			$retArray['status'] = "OK";
			$retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
			$retArray['dialogTitle'] = translateFN('Modifica Badge');
		} else {
			// if it's an error display the error message without the form
			$retArray['status'] = "ERROR";
			$retArray['msg'] = AMA_DB::isError($res) ? $res->getMessage() : translateFN('Errore caricamento badge');
		}
	} else {
		/**
		 * display the empty form
		 */
		$form = new BadgeForm('editbadge', null, new Badge());
		$retArray['status'] = "OK";
		$retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
		$retArray['dialogTitle'] = translateFN('Nuovo Badge');
	}
}

header('Content-Type: application/json');
echo json_encode($retArray);
