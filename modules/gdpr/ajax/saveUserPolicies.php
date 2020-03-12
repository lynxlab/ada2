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
$data->title = '<i class="basic error icon"></i>'.translateFN('Errore salvataggio');
$data->status = 'ERROR';
$data->message = translateFN('Errore nel salvataggio delle preferenze');

try {
	$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	if (!array_key_exists('userId', $postParams) || (array_key_exists('userId', $postParams) && intval($postParams['userId'])<=0)) {
		throw new GdprException(translateFN("Impossibile determinare l'utente per il salvataggio"));
	}

	if (array_key_exists('acceptPolicy', $postParams) && is_array($postParams['acceptPolicy']) && count($postParams['acceptPolicy'])>0) {
		$result = (new GdprAPI())->saveUserPolicies($postParams);
		$data->title = '<i class="info icon"></i>'.translateFN('Preferenze salvate');
		$data->status = 'OK';
		$data->message = translateFN('Le preferenze sono salvate correttamente');
		if (property_exists($result, 'redirecturl')) {
			$data->saveResult['redirecturl'] = $result->redirecturl;
		} else if (property_exists($result, 'submit')) {
			$data->saveResult['submit'] = $result->submit;
		}
	} else {
		throw new GdprException(ucfirst(strtolower(translateFN('Niente da salvare'))));
	}
} catch (\Exception $e) {
	header(' ', true, 400);
	$data->title .= ' ('.$e->getCode().')';
	$data->message = $e->getMessage();
	$data->errorMessage = $e->getCode() . PHP_EOL .$e->getMessage();
	if (array_key_exists('debugForm', $postParams) && intval($postParams['debugForm'])===1) {
		$data->errorTrace = $e->getTraceAsString();
	}
}

header('Content-Type: application/json');
die (json_encode($data));
