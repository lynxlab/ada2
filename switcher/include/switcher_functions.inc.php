<?php

/**
 * Switcher functions
 * 
 * @package		
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.2
 */
/**
 * Needed when obtaining messages for a user.
 */
require_once ROOT_DIR . '/comunica/include/MessageHandler.inc.php';
require_once ROOT_DIR . '/comunica/include/UserDataHandler.inc.php';
require_once ROOT_DIR . '/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
///*
// * Breadcrumbs
// */
//if(!isset($_SESSION['sess_breadcrumb'])) {
//    $breadcrumbObj = new Breadcrumb();
//    $_SESSION['sess_breadcrumb'] = $breadcrumbObj;
//}

if ($_REQUEST['id_node']) {
    $sess_id_node = $_REQUEST['id_node'];
    $id_node = $_REQUEST['id_node'];
} else {
    $sess_id_node = $_SESSION['sess_id_node'];
}


if ($_REQUEST['id_course']) {
    $sess_id_course = $_REQUEST['id_course'];
    $id_course = $sess_id_course;
} else {
    $sess_id_course = $_SESSION['sess_id_course'];
}

if ($_REQUEST['id_course_instance']) {
    $sess_id_course_instance = $_REQUEST['id_course_instance'];
    $id_course_instance = $sess_id_course_instance;
    //  $is_istance_active = 1; ??
} else {
    $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
}


if (!isset($_REQUEST['status'])) {
    if (isset($_REQUEST['msg'])) {
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
if (ADA_Error::isError($userObj)) {
    $userObj->handleError();
}


$testers_dataAr = MultiPort::getTestersPointersAndIds();

$user_messagesAr = MultiPort::getUserMessages($userObj);
$user_messages = CommunicationModuleHtmlLib::getMessagesAsTable($user_messagesAr, $testers_dataAr);

$user_agendaAr = MultiPort::getUserAgenda($userObj);
$user_agenda = CommunicationModuleHtmlLib::getAgendaAsTable($user_agendaAr, $testers_dataAr);


$user_level = ADA_MAX_USER_LEVEL;
$user_score = '';

$id_profile = $userObj->getType();
$user_type = $userObj->convertUserTypeFN($id_profile);
$user_uname = $userObj->username;
$user_name = $userObj->nome;
$user_surname = $userObj->cognome;
$user_family = $userObj->template_family;
$user_mail = $userObj->email;

/*
 * Get this user needed objects from $neededObjAr based on user tyoe
 */
if (is_array($neededObjAr) && is_array($neededObjAr[$id_profile])) {
    $thisUserNeededObjAr = $neededObjAr[$id_profile];
} else {
    $thisUserNeededObjAr = array();
}

if (in_array('course', $thisUserNeededObjAr)) {
    /**
     *  get Course object
     */
    /**
     * @var Object
     */
    $courseObj = read_course($sess_id_course);
    //mydebug(__LINE__,__FILE__,$courseObj);

    if (ADA_Error::isError($courseObj)) {
        $courseObj->handleError();
    } else {
        $course_family = $courseObj->getTemplateFamily();
    }
}

if (in_array('course_instance',$thisUserNeededObjAr)){

  //if(!MultiPort::isUserBrowsingThePublicTester()) {

    /**
     *  get Course_Instance object
     */

    if (($id_profile== AMA_TYPE_STUDENT) || ($id_profile == AMA_TYPE_TUTOR) ||
        ($id_profile== AMA_TYPE_SWITCHER)){
     /**
       * 	@var Object
       */
      $courseInstanceObj = read_course_instance_from_DB($sess_id_course_instance);
      if (ADA_Error::isError($courseInstanceObj)){
        $courseInstanceObj->handleError();
      }
      else {
/*
 * Verificare se servono
 */
//        $course_instance_family = $courseInstanceObj->template_family;
//        // no need to connect to DB ...
//        // $cistatus =  $dh->course_instance_status_get($sess_id_course_instance);
//        $cistatus = $courseInstanceObj->status;
//        if (($cistatus == ADA_COURSEINSTANCE_STATUS_PUBLIC)
//            AND (($id_profile == AMA_TYPE_STUDENT) OR ($id_profile == AMA_TYPE_GUEST))){
//          $user_status = ADA_STATUS_VISITOR;
//        }
      }
    }
  //}
}

if (in_array('node', $thisUserNeededObjAr)) {
    /**
     *  get Node Object
     */
    $nodeObj = read_node_from_DB($id_node);
    if (ADA_Error::isError($nodeObj)) {
        $nodeObj->handleError();
    }

    $node_family = $nodeObj->template_family;
    $node_author_id = $nodeObj->author;
    $node_type = $nodeObj->type;
}





// FIXME: verificare bene questa parte
$reg_enabled = false; // links to bookmarks disabled
$log_enabled = false; // links to history disabled
$mod_enabled = false; // links to modify nodes  disabled
$com_enabled = false; // links to comunicate among users  disabled

/**
 * Template Family
 */
if ((isset($family)) and (!empty($family))) { // from GET parameters
    $template_family = $family;
} elseif ((isset($node_family)) and (!empty($node_family))) { // from node definition
    $template_family = $node_family;
} elseif ((isset($course_instance_family)) and (!empty($course_instance_family))) { // from course instance definition
    $template_family = $course_instance_family;
} elseif ((isset($course_family)) and (!empty($course_family))) { // from course definition
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
    'node_type' => $node_type,
    'family' => $template_family,
    'node_author_id' => $node_author_id,
    'node_course_id' => $node_course_id,
    'module_dir' => $module_dir
);