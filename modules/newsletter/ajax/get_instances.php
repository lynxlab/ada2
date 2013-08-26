<?php
/**
 * NEWSLETTER MODULE.
 *
 * @package		newsletter module
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			newsletter
 * @version		0.1
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
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_NEWSLETTER_PATH .'/config/config.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retval = array();

if (isset($_GET['selected'])  && intval($_GET['selected'])>0 )
{
	$instancesArr = $dh->course_instance_get_list (array ('title'), intval ($_GET['selected']));

	if (!AMA_DB::isError($instancesArr))
	{
		array_push($retval, array( "label"=>translateFN('Tutte le istanze'), "value"=>0) );
		for ($i=0; $i < count ($instancesArr); $i++)
		{
			array_push($retval, array( "label"=>$instancesArr[$i][1], "value"=>intval($instancesArr[$i][0])  ));
		}				
	}
}
echo json_encode($retval);
?>