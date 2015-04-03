<?php
/*
 * Created on 07/08/2009
 *
 * Creating courseObj, $courseInstanceObj, nodeObj, userObj, tutorObj, videoroomObj objects
 * + layout_dataAr array
 */
/**
 * Needed when obtaining messages for a user.
 */
require_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';
require_once ROOT_DIR.'/comunica/include/UserDataHandler.inc.php';
require_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
/**
 * Specific room object .
 */
require_once ROOT_DIR.'/comunica/include/videoroom_classes.inc.php';


if (isset($_REQUEST['id_node'])){
  $sess_id_node = $_REQUEST['id_node'];
  $id_node = $_REQUEST['id_node'];
} else {
  $sess_id_node = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;
}


if (isset($_REQUEST['id_course'])){
  $sess_id_course = intval($_REQUEST['id_course']);
  $id_course = $sess_id_course;
} else {
  $sess_id_course = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
}

if (isset($_REQUEST['id_course_instance'])){
  $sess_id_course_instance = intval($_REQUEST['id_course_instance']);
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
    $status = translateFN("comunicazione");
  }
} else {
  $status = $_REQUEST['status'];
}

if (isset($_REQUEST['id_room'])){
  $sess_id_room = intval($_REQUEST['id_room']);
  $id_room = $sess_id_room;
  $id_chatroom = $sess_id_room;
}


// $is_istance_active = ... ?;


/**
 * get User object
 */

/**
 * @var Object
 */
$userObj = read_user($sess_id_user);
if (ADA_Error::isError($userObj)){
  $userObj->handleError();
}
else {
  $id_profile = $userObj->getType();
  switch ($id_profile){
    case AMA_TYPE_STUDENT:
      $user_messages = "";
      $user_agenda =  "";
      $user_level = "0";
      $user_score =  "0";
      $user_history = "";
      $user_status = $userObj->get_student_status($sess_id_user,$sess_id_course_instance);
      break;
    case AMA_TYPE_TUTOR:
      // FIXME: sistemare userObj->history()
      //$user_history = $userObj->history();
      // FIXME: messages and agenda will be handled by class MultiPort
      //        $user_messages = $userObj->get_messagesFN($sess_id_user);
      //        $user_agenda =  $userObj->get_agendaFN($sess_id_user);
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = 0;
      break;
    case AMA_TYPE_SWITCHER:
      // FIXME: messages and agenda will be handled by class MultiPort
      //        $user_messages = $userObj->get_messagesFN($sess_id_user);
      //        $user_agenda =  $userObj->get_agendaFN($sess_id_user);
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = ADA_STATUS_VISITOR;
      break;
    case AMA_TYPE_AUTHOR:
      // FIXME: messages and agenda will be handled by class MultiPort
      //        $user_messages = $userObj->get_messagesFN($sess_id_user);
      //        $user_agenda =  $userObj->get_agendaFN($sess_id_user);
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = ADA_STATUS_VISITOR;
      break;
    case ADA_TYPE_ADMIN:
      $homepage = "$http_root_dir/admin/admin.php"; // admin.php
      $msg =   urlencode(translateFN("Ridirezionamento automatico"));
      header("Location: $homepage?err_msg=$msg");
      exit;
      break;
  }
  $user_type = $userObj->convertUserTypeFN($id_profile);
  $user_uname =  $userObj->username;
  $user_name =  $userObj->nome;
  $user_surname =  $userObj->cognome;
  $user_family = $userObj->template_family;
  $id_profile = $userObj->getType();
  $user_mail =  $userObj->email;
}

/*
 * Get this user needed objects from $neededObjAr based on user type
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

if (in_array('course_instance',$thisUserNeededObjAr)){



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
      //mydebug(__LINE__,__FILE__,$courseObj);
      $course_instance_family = $courseInstanceObj->template_family;
    }
  }
}


if (in_array('tutor',$thisUserNeededObjAr)){

  /**
   *  get Tutor Object
   */

  if (isset($sess_id_course_instance)){
    if ($user_status <> ADA_STATUS_VISITOR){
      $tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
      if (!empty($tutor_id) && !AMA_dataHandler::isError($tutor_id)){
        /**
         * @var Object
         */
        $tutorObj = $dh->get_tutor($tutor_id);
        if (!AMA_dataHandler::isError($tutorObj)){
          $tutor_uname = $tutorObj['username'];
        }
      }
    }
  }
}

if (in_array('node',$thisUserNeededObjAr)){


  /**
   *  get Node Object
   */

  /**
   * @var Object
   */
  $nodeObj = read_node_from_DB($id_node);
  if (ADA_Error::isError($nodeObj)){
    $nodeObj->handleError();
  }
}


if (in_array('videoroom',$thisUserNeededObjAr)) {

  /*
   * Check if the user has an appointment
   */

  //	$tester_dh = AMA_DataHandler::instance(self::getDSN('PIPPO'));
  /*
  $dh = $GLOBALS['dh'];
  $sess_selected_tester = $_SESSION['sess_selected_tester'];
  //	$user_agenda =  $userObj->get_agendaFN($sess_id_user);
  //	$mh =  MessageHandler::instance($dh);
  $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
  $sort_field = "data_ora desc";
  $res_agenda_ha = $mh->get_messages($sess_id_user, WISP_MSG_AGENDA,
  array("id_mittente", "data_ora", "titolo", "priorita", "read_timestamp"),
  $sort_field);
  if (AMA_DataHandler::isError($res_agenda_ha) || (count($res_agenda_ha) < 1)){
    $err_code = $msgs_ha->code;
    $status = addslashes(translateFN("Non ci sono appuntamenti"));
//  	$options_Ar = array('onload_func'=>'close_page("NO_appuntamenti");');
  	$options_Ar = array('onload_func' => "close_page('$status');");
  }
*/
  /*
   * Check if the user has an appointment today at actual time
   */

  $user_has_app = false;
  if (defined('DATE_CONTROL') AND (DATE_CONTROL == FALSE)) {
	  $user_has_app = true;
  }else  {
  	$user_has_app = MultiPort::hasThisUserAVideochatAppointment($userObj);
  }

  /*
  $today_time = today_timeFN();
  $today_date = today_dateFN();
  $today_time_date = date(WISP_DATE_FORMAT);
  foreach ($res_agenda_ha as $one_date) {
    $time_2_add = 30*60; // 30 minuti di arrotondamento.
    $unix_date_app_rounded = $one_date[1] + $time_2_add;
    $udate_now = time();
    if ($udate_now >= $one_date[1] and $udate_now <= $unix_date_app_rounded) {
      $user_has_app = true;
      $event_token = WISPEventProposal::extractEventToken($one_date[2]);
      break;
    }
  }
*/
//  if (($user_has_app = MultiPort::hasThisUserAVideochatAppointment($userObj)) !== FALSE) {
  if ($user_has_app) {
    $event_token = $user_has_app;
    $id_profile = $userObj->getType();
    switch ($id_profile){
      case AMA_TYPE_STUDENT:
        /**
         * get videoroom Obj
         */
        $videoroomObj = new videoroom();
	$tempo_attuale = time();
        $videoroomObj->videoroom_info($sess_id_course_instance, $tempo_attuale);

        //$videoroomObj->videoroom_info($sess_id_course_instance);

        if ($videoroomObj->full) {
          $videoroomObj->server_login();
          if ($videoroomObj->login->return >=0) {
            $videoroomObj->room_access($user_uname,$user_name,$user_surname,$user_mail,$sess_id_user,$id_profile);
//            $videoroomObj->list_rooms();
          }
        }else
        {
        	//$status = addslashes(translateFN("Practitioner not yet present"));
        	$status = addslashes(translateFN("Room not yet opened"));
  		//	$options_Ar = array('onload_func'=>'close_page("No_practitioner");');
		  	$options_Ar = array('onload_func' => "close_page('$status');");
        }
        break;
      case AMA_TYPE_TUTOR:
        $videoroomObj = new videoroom();
	$tempo_attuale = time();
        $creationDate = Abstract_AMA_DataHandler::ts_to_date($tempo_attuale);
        $videoroomObj->videoroom_info($sess_id_course_instance, $tempo_attuale);
        $videoroomObj->server_login();
        if ($videoroomObj->full) {
          if ($videoroomObj->login->return >=0) {
            $videoroomObj->room_access($user_uname,$user_name,$user_surname,$user_mail,$sess_id_user,$id_profile);
//            $videoroomObj->list_rooms();
          }
        } else {

	        $room_name = translateFN("course: ") . $course_title . ", Tutor " . $user_uname . " date: " .$creationDate;
	        $id_room = $videoroomObj->add_openmeetings_room($room_name, $sess_id_course_instance, $sess_id_user);
	        if ($videoroomObj->login->return >=0) {
	          $videoroomObj->room_access($user_uname,$user_name,$user_surname,$user_mail,$sess_id_user,$id_profile);
//	          $videoroomObj->list_rooms();
	      	}
        }
        break;
    }

  }
  else 	{
//    $status = translateFN("Non hai appuntamenti");
//  	$options_Ar = array('onload_func'=>'close_page("NO_appuntamenti");');
  	$close_page_message = addslashes(translateFN("You don't have a videochat appointment at this time."));
  	$options_Ar = array('onload_func' => "close_page('$close_page_message');");
  }

}

if (in_array('chatroom',$thisUserNeededObjAr)) {
  require_once 'ChatRoom.inc.php';
  require_once 'ChatDataHandler.inc.php';

  /*
   * Check if the user has an appointment
   */
  $exit_reason = NO_EXIT_REASON;
  $event_token = '';

//  if (($id_chatroom = MultiPort::hasThisUserAChatAppointment($userObj)) !== FALSE) {

//  $chatroomHA = ChatRoom::get_info_chatroomFN($id_chatroom);
  if (!isset($id_chatroom) && isset($_SESSION['sess_id_course_instance'])) {
      $id_chatroom = ChatRoom::get_class_chatroomFN($_SESSION['sess_id_course_instance']);
        if(AMA_DataHandler::isError($id_chatroom)) {
            $id_chatroom = 0;
        }
  } else {
      
  }
    $chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
    if($chatroomObj->error == 1) {
      $exit_reason = EXIT_REASON_WRONG_ROOM;
    }
//    $event_token = ADAEventProposal::extractEventToken($chatroomObj->chat_title);
//  }
//  else {
//    $exit_reason = EXIT_REASON_WRONG_ROOM;
//  }
}



$reg_enabled = true; // links to bookmarks enabled
$log_enabled = true; // links to history enabled
$mod_enabled = true; // links to modify nodes  enabled
$com_enabled = true;  // links to comunicate among users  enabled

if ($user_status == ADA_STATUS_VISITOR || $user_status == ADA_STATUS_TERMINATED) {
  $reg_enabled = false; // links to bookmarks disabled
  $log_enabled = false; // links to history disabled
  $mod_enabled = false; // links to modify nodes  disabled
  $com_enabled = false;  // links to comunicate among users  disabled
}

if ($id_profile == AMA_TYPE_STUDENT && $log_enabled){
  $user_level = (string) $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
  $user_score =  (string) $userObj->get_student_score($sess_id_user,$sess_id_course_instance);
  $user_history = $userObj->history;
}


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
 * LAYOUT
 */

/**
 * @var Array
 */
$layout_dataAr = array();
if(isset($node_type))       $layout_dataAr['node_type'] = $node_type;
if(isset($template_family)) $layout_dataAr['family'] = $template_family;
if(isset($node_author_id))  $layout_dataAr['node_author_id'] = $node_author_id;
if(isset($node_course_id))  $layout_dataAr['node_course_id'] = $node_course_id;
if(isset($module_dir))      $layout_dataAr['module_dir'] = $module_dir;