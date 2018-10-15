<?php
/**
 * VIDEOCHAT.
 *
 * @package		videochat
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Specific Openmeetings config file
 */
require_once 'include/videochat_config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
ComunicaHelper::init($neededObjAr);

/**
 * Specific room object .
 */

//require_once ROOT_DIR.'/comunica/include/videoroom_classes.inc.php';

if ($_REQUEST['id_room']){
  $id_room = $_REQUEST['id_room'];
}
/*
$id_profile = $userObj->getType();
  if ($id_profile==AMA_TYPE_TUTOR){
	$videoroomObj = new videoroom();
	//$videoroomObj->videoroom_info($sess_id_course_instance);
	//if ($videoroomObj->full) {
	//	$id_room = $videoroomObj->id_room;
	$videoroomObj->server_login();
	$videoroomObj->delete_room($id_room);
  	header('Location:'. HTTP_ROOT_DIR . '/tutor/eguidance_tutor_form.php?event_token='.$_GET['event_token']);
  	exit();
  } else {
	$options_Ar = array('onload_func'=>"close_page('Good_bye');");
	$content = "";
	$content_dataAr = array (
		'data'      => $content
	);
	ARE::render($layout_dataAr,$content_dataAr,NULL,$options_Ar);
  }
 *
 */
	$options_Ar = array('onload_func'=>"close_page('Good_bye');");
	$content = "";
	$content_dataAr = array (
		'data'      => $content
	);
	ARE::render($layout_dataAr,$content_dataAr,NULL,$options_Ar);

?>