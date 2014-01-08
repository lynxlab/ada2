<?php
/**
 * EXPORT MODULE.
 *
 * @package		export/import course
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			impexport
 * @version		0.1
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

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
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once dirname(__FILE__).'/config/config.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/exportHelper.class.inc.php';

/**
 * this is called async by the tree view to populate itself
 */

$courseID = (isset ($_GET['courseID']) && (intval($_GET['courseID'])>0) ) ? intval ($_GET['courseID']) : 0;

if ($courseID > 0)
{
	$rootNode = $courseID.exportHelper::$courseSeparator."0";
	// need an Import/Export DataHandler
	$dh =& AMAImpExportDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	
	$exportHelper = new exportHelper($courseID);
	
	$a = $exportHelper->getAllChildrenArray($rootNode, $dh);
	
	echo json_encode (array($a));
} 
?>