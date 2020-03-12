<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\GdprAPI;
use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprException;
use Lynxlab\ADA\Module\GDPR\GdprRequest;
use Ramsey\Uuid\Uuid;
use Lynxlab\ADA\Module\GDPR\AMAGdprDataHandler;

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
// require_once MODULES_GDPR_PATH . '/config/config.inc.php';

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
BrowsingHelper::init($neededObjAr);

$data = new stdClass();
$data->title = '<i class="basic error icon"></i>'.translateFN('Errore evasione richiesta');
$data->status = 'ERROR';
$data->message = translateFN('Errore sconosciuto');

try {
	$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	$isClose = array_key_exists('isclose', $postParams) && intval($postParams['isclose'])===1;

	if (!array_key_exists('requestuuid', $postParams)) {
		throw new GdprException(translateFN("L'ID pratica non può essere vouto"));
	} else if (array_key_exists('requestuuid', $postParams) && !Uuid::isValid(trim($postParams['requestuuid']))) {
		throw new GdprException(translateFN("L'ID pratica non è valido"));
	}
	// so far so good, load the request
	$gdprAPI = new GdprAPI();
	$tmp = $gdprAPI->findBy($gdprAPI->getObjectClasses()[AMAGdprDataHandler::REQUESTCLASSKEY],array('uuid'=>trim($postParams['requestuuid'])));
	$request = reset($tmp);
	if (!($request instanceof GdprRequest)) {
		throw new GdprException(translateFN("ID pratica non trovato"));
	} else if (($isClose && !GdprActions::canDo(GdprActions::FORCE_CLOSE_REQUEST, $request)) ||
			   (!is_null($request->getType()) && !GdprActions::canDo($request->getType()->getLinkedAction(), $request))) {
		throw new GdprException(translateFN("Utente non abilitato all'azione richiesta"));
	} else {
		if ($isClose) {
			$data = new stdClass();
			$data->reloaddata = true;
			$request->close();
		} else {
			$data = $request->handle();
		}
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
