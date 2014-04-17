<?php
/**
 * GET UNREAD MESSAGES COUNT FOR SESSION USER.
 *
 * @package		comunica
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
// ini_set('display_errors', '0'); error_reporting(E_ALL);
require_once realpath ( dirname ( __FILE__ ) ) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array ();

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);

/**
 * Get needed objects
 */
$neededObjAr = array ();

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';

if (isset ( $_SERVER ['REQUEST_METHOD'] ) && $_SERVER ['REQUEST_METHOD'] == 'GET') {
	if (isset($_SESSION['sess_userObj'])) { 
		echo json_encode(array('value'=>$_SESSION['sess_userObj']->getUnreadMessagesCount()));
		die();
	}
}

echo json_encode(array('value'=>'ERROR'));