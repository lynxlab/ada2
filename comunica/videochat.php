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
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
array_push($variableToClearAR, 'layout');
array_push($variableToClearAR, 'user');
array_push($variableToClearAR, 'course');
array_push($variableToClearAR, 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout','tutor','course','course_instance', 'videoroom'),
  AMA_TYPE_TUTOR => array('layout','tutor','course','course_instance','videoroom')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

/**
 * Specific Openmeetings config file
 */
require_once 'include/videochat_config.inc.php';

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

/*
 * Redirect to correct home if comunication not enabled
 */
 if ($userObj->getType() == AMA_TYPE_VISITOR){
  $homepage = $userObj->getHomepage();
  $msg =   translateFN("Utente non autorizzato");
  header("Location: $homepage?err_msg=$msg");
  exit;
 }
/*
 * FINE Redirect to correct home if comunication not enabled
 */
$date = date('l jS \of F Y h:i:s A');
$label = "Video Chat on ".$date;
// $content = "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0\" width=\"100%\" height=\"600\">
//                         <param name=movie value=\"$videoroomObj->link_to_room\">
//                         <param name=quality value=high>
//                         <embed src=\"$videoroomObj->link_to_room\" quality=high pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" type=\"application/x-shockwave-flash\" width=\"100%\" height=\"600\">
//                         </embed>
//                         </object>";

$width = FRAME_WIDTH;
$height = FRAME_HEIGHT;
$logEnter = false;

if ($videoroomObj->link_to_room instanceof CBase) {
    $iframe = $videoroomObj->link_to_room->getHtml();
    $className = get_class($videoroomObj);
    if (defined($className.'::onload_js')) {
      $options_Ar = array('onload_func' => constant($className.'::onload_js'));
    }
    $logEnter = true;
} else if (is_string($videoroomObj->link_to_room) && strlen($videoroomObj->link_to_room)>0) {
    $className = get_class($videoroomObj);
    $iframe = "<iframe src='$videoroomObj->link_to_room' width='$width' height = '$height'";
    if (defined($className.'::iframeAttr')) {
      $iframe .= constant($className.'::iframeAttr');
    }
    $iframe .= " data-logout='".urlencode($videoroomObj->getLogoutUrlParams())."'";
    $iframe .= "></iframe>";
    $logEnter = true;
} else {
  $iframe = '';
  $status = addslashes(translateFN("ops, there was a problem!"));
  if (!isset($GLOBALS['options_Ar'])) {
    $options_Ar = array('onload_func' => "close_page('$status');");
  }
}

if ($logEnter) {
  $videoroomObj->logEnter();
}

$menu_01 = "<a href=\"close_videochat.php?id_room=".$videoroomObj->id_room ."&event_token=$event_token\">" . translateFN("Chiudi") . "</a>";
$content_dataAr = array (
//	'data'      => $content,
	'label' => $label,
	'menu_01'   => $menu_01,
	'user_name' =>isset($user_uname) ? $user_uname : '',
	'user_type' =>$user_type,
	'status' => $status,
	'data'      => $iframe
);


ARE::render($layout_dataAr,$content_dataAr,NULL,isset($options_Ar) ? $options_Ar : null);

?>