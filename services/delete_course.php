<?php
/**
 * 
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
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
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = author!

include_once 'include/author_functions.inc.php';

$self =  whoami();

/*
 * YOUR CODE HERE
 */

$success = 'author.php';
$menu = 'author.php';
$error = 'author.php';

$course_has_istance = $dh->course_has_instances($id_course);
if(!$course_has_istance) {
  $res = $dh->remove_course($id_course);
  if (AMA_DataHandler::isError($res)) {
    $msg = $res->getMessage();
  }
  else {
    $msg = translateFN('Cancellazione modello corso riuscita');
  }
  header("Location: $menu?msg=$msg");
  exit();
} else {
  $msg = translateFN('Cancellazione del corso non riuscita. Il corso ha istanze.');
  header("Location: $error?msg=$msg");
  exit();
}