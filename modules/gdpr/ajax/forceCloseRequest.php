<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\AMAGdprDataHandler;
use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprException;
use Ramsey\Uuid\Uuid;

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
require_once MODULES_GDPR_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(GdprActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
$GLOBALS['dh'] = AMAGdprDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$data = new stdClass();
$data->title = '<i class="basic error icon"></i>'.translateFN('Errore ricerca richiesta');
$data->status = 'ERROR';
$data->message = translateFN('Errore sconosciuto');

try {
	$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	if (!array_key_exists('requestUUID', $postParams)) {
		throw new GdprException(translateFN("L'ID pratica non può essere vouto"));
	} else if (array_key_exists('requestUUID', $postParams) && !Uuid::isValid(trim($postParams['requestUUID']))) {
		throw new GdprException(translateFN("L'ID pratica non è valido"));
	} else if (!GdprActions::canDo(GdprActions::FORCE_CLOSE_REQUEST)) {
		throw new GdprException(translateFN("Utente non abilitato all'azione richiesta"));
	} else {
		$GLOBALS['dh']->closeRequest(trim($postParams['requestUUID']));
		$data = new stdClass();
		$data->saveResult = true;
		$data->status = 'OK';
	}
} catch (\Exception $e) {
	header(' ', true, 400);
	$data->errorCode = $e->getCode();
// 	$data->title .= ' ('.$e->getCode().')';
	$data->message = $e->getMessage();
	$data->errorMessage = $e->getCode() . PHP_EOL .$e->getMessage();
	if (array_key_exists('debug', $postParams) && intval($postParams['debug'])===1) {
		$data->errorTrace = $e->getTraceAsString();
	}
}

header('Content-Type: application/json');
die (json_encode($data));
