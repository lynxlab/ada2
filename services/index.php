<?php
/**
 * Redirect.
 * 
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */

/**
 * Base config file 
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */


$variableToClearAR = array('node','layout', 'user', 'course');
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
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = 'index';

$userObj = read_user($sess_id_user);
if($userObj instanceof ADAGenericUser) {
  $homepage = $userObj->getHomePage();
  header('Location: ' . $homepage);
  exit();  
}

header('Location: '.HTTP_ROOT_DIR);
exit();
?>