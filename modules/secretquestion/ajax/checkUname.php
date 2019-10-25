<?php
/**
 * @package 	secretquestion module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
require_once MODULES_SECRETQUESTION_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR);

/**
 * Get needed objects
 */
$neededObjAr = array(AMA_TYPE_VISITOR => array('layout'));

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

$data = new stdClass();
$data->unameok = false;
$data->exception = [];

try {
	$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	if (array_key_exists('uname', $_POST) && strlen(trim($_POST['uname']))>0 && DataValidator::validate_username(trim($_POST['uname']))) {
		$userId = \MultiPort::findUserByUsername(trim($_POST['uname']));
		if (!AMA_DB::isError($userId) && $userId>0) {
			// username exists
			throw new \Exception(translateFN('Username esistente'));
		} else {
			$data->unameok = true;
		}
	} else throw new \Exception(translateFN('Utente non valido'));
} catch (\Exception $e) {
	// header(' ', true, 400);
	$data->exception['code'] = $e->getCode();
	$data->exception['message'] = $e->getMessage();
}

header('Content-Type: application/json');
die (json_encode($data));
