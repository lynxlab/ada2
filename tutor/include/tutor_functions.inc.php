<?php
/**
 * TUTOR FUNCTIONS
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
require_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';

require_once ROOT_DIR.'/include/HTML_element_classes.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';


if (isset($_REQUEST['id_node'])){
  $sess_id_node = $_REQUEST['id_node'];
  $id_node = $_REQUEST['id_node'];
} else {
  $sess_id_node = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;
}


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
    $status = translateFN("navigazione");
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

$user_agendaAr   = MultiPort::getUserAgenda($userObj);
$user_agenda     = CommunicationModuleHtmlLib::getAgendaAsTable($user_agendaAr, $testers_dataAr);

  $user_eventsAr = MultiPort::getUserEventsNotRead($userObj);
  $user_events    = CommunicationModuleHtmlLib::getEventsAsTable($userObj, $user_eventsAr, $testers_dataAr);

/*$user_eventsAr = MultiPort::getUserEvents($userObj);
$user_events    = CommunicationModuleHtmlLib::getEventsAsTable($userObj, $user_eventsAr, $testers_dataAr);
 * 
 */

$user_level = ADA_MAX_USER_LEVEL;
$user_score = "";
//  $user_status = ADA_STATUS_VISITOR;

$user_uname   = $userObj->username;
$user_name    = $userObj->nome;
$user_surname = $userObj->cognome;
$user_family  = $userObj->template_family;
$id_profile   = $userObj->getType();
$user_type    = $userObj->getTypeAsString();
$user_mail    = $userObj->email;

/*
 * Get this user needed objects from $neededObjAr based on user tyoe
 */
if(is_array($neededObjAr) && is_array($neededObjAr[$id_profile])) {
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

if (in_array('course_instance',$thisUserNeededObjAr)){

  //if(!MultiPort::isUserBrowsingThePublicTester()) {

    /**
     *  get Course_Instance object
     */

    if (($id_profile== AMA_TYPE_STUDENT) OR ($id_profile== AMA_TYPE_TUTOR)){
      /**
       * 	@var Object
       */
      $courseInstanceObj = read_course_instance_from_DB($sess_id_course_instance);
      if (ADA_Error::isError($courseInstanceObj)){
        $courseInstanceObj->handleError();
      }
      else {
        
        $course_instance_family = $courseInstanceObj->template_family;
        // no need to connect to DB ...
        // $cistatus =  $dh->course_instance_status_get($sess_id_course_instance);
        $cistatus = $courseInstanceObj->status;
        if (($cistatus == ADA_COURSEINSTANCE_STATUS_PUBLIC)
        AND (($id_profile == AMA_TYPE_STUDENT) OR ($id_profile == AMA_TYPE_GUEST))){
          $user_status = ADA_STATUS_VISITOR;
        }
      }
    }
  //}
}


if (in_array('node',$thisUserNeededObjAr)){
  /**
   *  get Node Object
   */
  $nodeObj = read_node_from_DB($id_node);
  if (ADA_Error::isError($nodeObj)){
    $nodeObj->handleError();
  }

  $node_family    = $nodeObj->template_family;
  $node_author_id = $nodeObj->author;
  $node_type      = $nodeObj->type;
}

// FIXME: verificare bene questa parte
$reg_enabled = false; // links to bookmarks disabled
$log_enabled = false; // links to history disabled
$mod_enabled = false; // links to modify nodes  disabled
$com_enabled = false; // links to comunicate among users  disabled

/*
 * Non dovrebbe servire
 */
//if ($com_enabled){
//  // FIXME: messages and agenda will be handled by class MultiPort
//  //    $user_messages = $userObj->get_messagesFN($sess_id_user);
//  //    $user_agenda =  $userObj->get_agendaFN($sess_id_user);
//
//  $last_access_date = $userObj->get_last_accessFN($sess_id_course_instance,'T');
//  if ($last_access_date == translateFN("Nessun'informazione")) {
//    $user_name = $userObj->username;
//    $destAr =  array($user_name);
//    $mh =  MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
//    $message_ha['destinatari'] = $destAr;
//    $message_ha['priorita'] = 1;
//    $message_ha['data_ora'] = "now";
//    $message_ha['titolo'] = translateFN("Messaggio di benvenuto");
//    $welcome_file = "service_".$sess_id_course."_".$sess_id_course_instance."_welcome_$language.txt";
//    // es. course_2_12_welcome_italiano.txt
//    if (file_exists($welcome_file)){
//      $fp = fopen($welcome_file,'r');
//      $message_ha['testo'] = fread ($fp, filesize ($welcome_file));
//      fclose ($fd);
//    } else {
//      $message_ha['testo'] = translateFN("Benvenuto in ADA!");
//      $message_ha['testo'].= translateFN("Se hai problemi, dubbi o domande, puoi inviare un messaggio al tuo")."<a href=\"$http_root_dir/comunica/send_message.php?destinatari=$tutor_uname\">".translateFN("E-practitioner")."</a>.";
//    }
//    $message_ha['data_ora'] = "now";
//    $message_ha['mittente'] = $tutor_uname;
//    // e-mail
//    $message_ha['tipo'] = ADA_MSG_MAIL;
//    $res = $mh->send_message($message_ha);
//    // messaggio interno
//    $message_ha['tipo'] = ADA_MSG_SIMPLE;
//    $res = $mh->send_message($message_ha);
//    // reload messages to show this one !
//    $user_messages = $userObj->get_messagesFN($sess_id_user);
//  }
//}

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
  'node_type'      => isset($node_type) ? $node_type : '',
  'family'         => isset($template_family) ? $template_family : '',
  'node_author_id' => isset($node_author_id) ? $node_author_id : '',
  'node_course_id' => isset($node_course_id) ? $node_course_id : '',
  'module_dir'     => isset($module_dir) ? $module_dir : ''
);
?>