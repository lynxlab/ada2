<?php
/**
 * ADMIN FUNCTIONS
 *
 * @package
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Needed when obtaining messages for a user.
 */
require_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';
require_once ROOT_DIR.'/comunica/include/UserDataHandler.inc.php';

require_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/AdminModuleHtmlLib.inc.php';

if (isset($_REQUEST['id_course'])){
  $sess_id_course = $_REQUEST['id_course'];
  $id_course = $sess_id_course;
} else {
  $sess_id_course = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
}

if (isset($_REQUEST['id_course_instance'])){
  $sess_id_course_instance = $_REQUEST['id_course_instance'];
  $id_course_instance = $sess_id_course_instance;
  //  $is_istance_active = 1; ??
} else {
  $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
}


if (!isset($_REQUEST['status'])) {
  if (isset($_REQUEST['msg'])){
    $status = $_REQUEST['msg'];
    $msg = $_REQUEST['msg'];
  } else {
    $status = translateFN('navigazione');
  }
} else {
  $status = $_REQUEST['status'];
}

// $is_istance_active = ... ?;

/**
 * get User object
 */
$userObj = read_user($sess_id_user);
if (ADA_Error::isError($userObj)){
  $userObj->handleError();
}

// FIXME: messages and agenda will be handled by class MultiPort
//        $user_messages = $userObj->get_messagesFN($sess_id_user);
//        $user_agenda =  $userObj->get_agendaFN($sess_id_user);
$testers_dataAr = MultiPort::getTestersPointersAndIds();
  
$user_messagesAr = MultiPort::getUserMessages($userObj);
$user_messages   = CommunicationModuleHtmlLib::getMessagesAsTable($user_messagesAr, $testers_dataAr);
  
//$user_agendaAr   = MultiPort::getUserAgenda($userObj);
//$user_agenda     = CommunicationModuleHtmlLib::getAgendaAsTable($user_agendaAr, $testers_dataAr);

$user_level = ADA_MAX_USER_LEVEL;
$user_score = "";
//  $user_status = ADA_STATUS_VISITOR;

$user_uname   = $userObj->username;
$user_name    = $userObj->nome;
$user_surname = $userObj->cognome;
$user_family  = $userObj->template_family;
$id_profile   = $userObj->getType();
$user_type    = $userObj->convertUserTypeFN($id_profile);
$user_mail    = $userObj->email;

/*
 * Get this user needed objects from $neededObjAr based on user tyoe
 */
if(is_array($neededObjAr) && isset($neededObjAr[$id_profile]) && is_array($neededObjAr[$id_profile])) {
  $thisUserNeededObjAr = $neededObjAr[$id_profile];
}
else {
  $thisUserNeededObjAr = array();
}

if (in_array('course',$thisUserNeededObjAr)){
  /**
   *  get Course object
   */

  /**
   * @var Object
   */
  $courseObj = read_course($sess_id_course);

  //mydebug(__LINE__,__FILE__,$courseObj);

  if (ADA_Error::isError($courseObj)){
    $courseObj->handleError();
  }
  else {
    //mydebug(__LINE__,__FILE__,$courseObj);
    $course_title       = $courseObj->titolo; //title
    $id_toc             = $courseObj->id_nodo_toc;  //id_toc_node
    $course_media_path  = $courseObj->media_path;
    $course_author_id   = $courseObj->id_autore;
    $course_family      = $courseObj->template_family;
    $course_static_mode = $courseObj->static_mode;
  }

  if (empty($course_media_path)) {
    $media_path = MEDIA_PATH_DEFAULT.$course_author_id."/";
  } else {
    $media_path = $course_media_path;
  }
}

// FIXME: verificare bene questa parte
$reg_enabled = false; // links to bookmarks disabled
$log_enabled = false; // links to history disabled
$mod_enabled = false; // links to modify nodes  disabled
$com_enabled = false; // links to comunicate among users  disabled

/**
 * Template Family
 */

if ((isset($family))  and (!empty($family))){ // from GET parameters
  $template_family = $family;
} elseif ((isset($node_family))  and (!empty($node_family))){ // from node definition
  $template_family = $node_family;
} elseif ((isset($course_instance_family))  and (!empty($course_instance_family))){ // from course instance definition
  $template_family = $course_instance_family;
} elseif ((isset($course_family))  and (!empty($course_family))){ // from course definition
  $template_family = $course_family;
} elseif ((isset($user_family)) and (!empty($user_family))) { // from user's profile
  $template_family = $user_family;
} else {
  $template_family = ADA_TEMPLATE_FAMILY; // default template famliy
}

$_SESSION['sess_template_family'] = $template_family;

/**
 * Layout data
 */
$layout_dataAr = array(
  'node_type'      => isset($node_type) ? $node_type : null,
  'family'         => isset($template_family) ? $template_family : null,
  'node_author_id' => isset($node_author_id) ? $node_author_id : null,
  'node_course_id' => isset($node_course_id) ? $node_course_id : null,
  'module_dir'     => isset($module_dir) ? $module_dir : null
);
?>