<?php
/**
 * generateClient.php
 *
 * @package        generateClient
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           generateClient
 * @version		   0.1
 */

/**
 * This is called via ajax by the module's index page when the user
 * requests for a client id/client secret pair.
 * It generates a new pair and if there's not an existing one for the
 * passed user id, will save and return it. If there's an existing pair
 * for the user id, just return it.
 */

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

// MODULE's OWN IMPORTS
require_once MODULES_APPS_PATH .'/config/config.inc.php';

$dh = AMAAppsDataHandler::instance();

/**
 * TODO: Your own code here
 */

if (intval($userID)>0)
{

	$clientArray = $dh->saveClientIDAndSecret(generateConsumerIdAndSecret(),$userID);
		
	if (!$isError) print_r($clientArray);
	else print_r($isError);
	
} else echo "userID erorr";
?>