<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
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
$allowedUsersAr = array(AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/functions.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/AMASlideimportDataHandler.inc.php';

/**
 * this is called async by the tree view to populate itself
 */

$courseID = (isset ($_GET['courseID']) && (intval($_GET['courseID'])>0) ) ? intval ($_GET['courseID']) : 0;

if ($courseID > 0)
{
	$rootNode = $courseID.AMASlideimportDataHandler::$courseSeparator."0";
	// need a DataHandler
	$dh = AMASlideimportDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	$a = $dh->getAllChildrenArray($rootNode);

	header('Content-Type: application/json');
	echo json_encode (array($a));
}
?>