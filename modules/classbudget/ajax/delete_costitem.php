<?php
/**
 * CLASSBUDGET MODULE.
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classbudget
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
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_CLASSBUDGET_PATH .'/include/management/costItemManagement.inc.php';
require_once MODULES_CLASSBUDGET_PATH .'/include/management/costitemBudgetManagement.inc.php';
require_once MODULES_CLASSBUDGET_PATH .'/include/AMAClassbudgetDataHandler.inc.php';

$GLOBALS['dh'] = AMAClassbudgetDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (!isset($_POST['cost_item_id'])) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Non so cosa cancellare"));
	else {
		$costItemManager = new costItemManagement($GLOBALS['dh']->getCostItem($cost_item_id));
		$result = $GLOBALS['dh']->deleteCostItem(intval($_POST['cost_item_id']));

		if (!AMA_DB::isError($result)) {
			$retArray = array ("status"=>"OK", "msg"=>translateFN("Voce cancellata"));
			// get the new item cost table to be displayed
			$costItemBudget = new costitemBudgetManagement($costItemManager->id_istanza_corso);
			$htmlObj = $costItemBudget->run(MODULES_CLASSBUDGET_EDIT);
			$retArray['html'] = $htmlObj->getHtml();
		} else
			$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore di cancellazione") );
	}
} else {
	$retArray = array ("status"=>"ERROR", "msg"=>trasnlateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
?>