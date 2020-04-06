<?php
/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
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
$variableToClearAR = array();
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

$GLOBALS['dh'] = AMAClassroomDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($tableName) && strlen($tableName)>0 && isset($fieldName) && strlen($fieldName)>0) {
		echo json_encode($GLOBALS['dh']->doSearchForAutocomplete ($tableName, $fieldName, trim($term), $primaryKey));
	}
}
