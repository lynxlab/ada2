<?php
/**
 * ENTER EVENT.
 *
 * @package		comunica
 * @author
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

$variableToClearAR = array('layout','user','course');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array(),
  AMA_TYPE_TUTOR => array()
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

$event = $_GET['event'];

if ($event == ADA_CHAT_EVENT) {
  header('Location: ' . HTTP_ROOT_DIR .'/comunica/chat.php');
  exit();
}
elseif ($event == ADA_VIDEOCHAT_EVENT) {
  header('Location: ' . HTTP_ROOT_DIR .'/comunica/videochat.php');
  exit();
}
else {
  // dovrebbe chiudersi
}

?>