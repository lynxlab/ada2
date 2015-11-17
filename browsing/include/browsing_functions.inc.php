<?php
/*
 * Created on 23/06/2009
 *
 * Creating courseObj, $courseInstanceObj, nodeObj, userObj, tutorObj objects
 * + layout_dataAr array
 */

/**
 * Needed when obtaining messages for a user.
 */
require_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';
require_once ROOT_DIR.'/comunica/include/UserDataHandler.inc.php';
require_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';
require_once ROOT_DIR.'/include/media_viewing_classes.inc.php';
require_once 'CourseViewer.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';

/*
if ($_REQUEST['id_node']){
  $sess_id_node = $_REQUEST['id_node'];
  $id_node = $_REQUEST['id_node'];
} else {
  $sess_id_node = $_SESSION['sess_id_node'];
}


if ($_REQUEST['id_course']){
  $sess_id_course = $_REQUEST['id_course'];
  $id_course = $sess_id_course;
} else {
  $sess_id_course = $_SESSION['sess_id_course'];
}

if ($_REQUEST['id_course_instance']){
  $_SESSION['sess_id_course_instance'] = $_REQUEST['id_course_instance'];;

  $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
  $id_course_instance = $sess_id_course_instance;
  //  $is_istance_active = 1; ??
} else {
  $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
}
 * 
 */


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

/*
 * set in which way video LIS (or other) is showed
 * mode = LIS
 * mode = changeLIS change the LIS status
 * mode = ...
 */

if (isset($_REQUEST['mode']) && strlen($_REQUEST['mode'])>0) {
    if ($_REQUEST['mode'] == 'changeLIS') {
        if ($_SESSION['mode'] == 'LIS') {
            unset($_SESSION['mode']);
        } else {
            $_SESSION['mode'] = 'LIS';
        }
    } else {
        $_SESSION['mode'] = $_REQUEST['mode'];
    }

} else { //when unset the mode session??
    //$status = $_REQUEST['status'];
}

// $is_istance_active = ... ?;

/**
 * get User object
 */

/**
 * @var Object
 */
  if($_SESSION['sess_userObj'] instanceof ADAGenericUser) {
      $userObj = $_SESSION['sess_userObj'];
  } else {    
    $userObj = read_user($sess_id_user);
    if (ADA_Error::isError($userObj)){
      $userObj->handleError();
    }
  }
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
      // FIXME:rimettere a posto
      //$user_history = $userObj->history();
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = 0;
      break;
    case AMA_TYPE_SWITCHER:
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = ADA_STATUS_VISITOR;
      break;
    case AMA_TYPE_AUTHOR:
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = ADA_STATUS_VISITOR;
      break;
    case AMA_TYPE_ADMIN:
      $user_level = ADA_MAX_USER_LEVEL;
      $user_score = "";
      $user_status = ADA_STATUS_VISITOR;
      break;
        /*
      $homepage = HTTP_ROOT_DIR .'/admin/admin.php';
      $msg =   urlencode(translateFN('Redirezionamento automatico'));
      header("Location: $homepage?err_msg=$msg");
      exit();
      break;
         * 
         */
    default:
      $user_messages = "";
      $user_agenda =  "";
      $user_level = "0";
      $user_score =  "0";
      $user_history = "";
      $user_status = AMA_TYPE_VISITOR;
      break;
  }
  $user_type = $userObj->convertUserTypeFN($id_profile);
  $user_uname =  $userObj->username;
  $user_name =  $userObj->nome;
  $user_surname =  $userObj->cognome;
  $user_family = $userObj->template_family;
  $id_profile = $userObj->getType();
  $user_mail =  $userObj->email;

  $testers_dataAr = MultiPort::getTestersPointersAndIds();

  $user_messagesAr = MultiPort::getUserMessages($userObj);
  $user_messages   = CommunicationModuleHtmlLib::getMessagesAsTable($user_messagesAr, $testers_dataAr);

  $user_agendaAr   = MultiPort::getUserAgenda($userObj);
  $user_agenda     = CommunicationModuleHtmlLib::getAgendaAsTable($user_agendaAr, $testers_dataAr);

  //$user_eventsAr = MultiPort::getUserEvents($userObj);
  //$user_events    = CommunicationModuleHtmlLib::getEventsAsTable($userObj, $user_eventsAr, $testers_dataAr);

  $user_eventsAr = MultiPort::getUserEventsNotRead($userObj);
  $user_events    = CommunicationModuleHtmlLib::getEventsAsTable($userObj, $user_eventsAr, $testers_dataAr);
//}
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
    $course_title = $courseObj->titolo; //title
    $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
    $course_media_path = $courseObj->media_path;
    $course_author_id =$courseObj->id_autore;
    $course_family = $courseObj->template_family;
    $course_static_mode = $courseObj->static_mode;
  }

  if (empty($course_media_path)) {
    $media_path = MEDIA_PATH_DEFAULT.$course_author_id."/";
  } else {
    $media_path = $course_media_path;
  }
}

if (in_array('course_instance',$thisUserNeededObjAr)){
  if (!ADA_Error::isError($courseObj) && !$courseObj->getIsPublic ()) {

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
        // no need to connect to DB ...
        // $cistatus =  $dh->course_instance_status_get($sess_id_course_instance);
        $cistatus = $courseInstanceObj->status;
        if (($cistatus == ADA_COURSEINSTANCE_STATUS_PUBLIC)
        AND (($id_profile == AMA_TYPE_STUDENT) OR ($id_profile == AMA_TYPE_GUEST))){
          $user_status = ADA_STATUS_VISITOR;
        }
      }
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
          if (isset($tutor['username'])) $tutor_uname = $tutor['username'];
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
  $nodeObj = read_node_from_DB(isset($id_node) ? $id_node : null);  
  //  mydebug(__LINE__,__FILE__,$nodeObj);
  if (ADA_Error::isError($nodeObj)){
    $nodeObj->handleError();
  }
}


$reg_enabled = true; // links to bookmarks enabled
$log_enabled = true; // links to history enabled
$mod_enabled = true; // links to modify nodes  enabled
$com_enabled = true;  // links to comunicate among users  enabled

if ($id_profile == AMA_TYPE_STUDENT && (
	$user_status == ADA_STATUS_VISITOR || $user_status == ADA_STATUS_TERMINATED || $user_status == ADA_STATUS_COMPLETED)) {
  $reg_enabled = false; // links to bookmarks disabled
  $log_enabled = ($user_status != ADA_STATUS_VISITOR); // links to history disabled
  $mod_enabled = false; // links to modify nodes  disabled
  $com_enabled = false;  // links to comunicate among users  disabled
}

if ($com_enabled){
  // FIXME: messages and agenda will be handled by class MultiPort
  //    $user_messages = $userObj->get_messagesFN($sess_id_user);
  //    $user_agenda =  $userObj->get_agendaFN($sess_id_user);
  $last_access_date = $userObj->get_last_accessFN($sess_id_course_instance,'T');
  if ($last_access_date == translateFN("Nessun'informazione")) {
    $user_name = $userObj->username;
    $destAr =  array($user_name);
    // FIXME: multiportare, ora e' bloccato sul tester selezionato
    $mh =  MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
    $message_ha['destinatari'] = $destAr;
    $message_ha['priorita'] = 1;
    $message_ha['data_ora'] = "now";
    $message_ha['titolo'] = translateFN("Messaggio di benvenuto");
    $welcome_file = "service_".$sess_id_course."_".$sess_id_course_instance."_welcome_$language.txt";
    // es. course_2_12_welcome_italiano.txt
    if (file_exists($welcome_file)){
      $fp = fopen($welcome_file,'r');
      $message_ha['testo'] = fread ($fp, filesize ($welcome_file));
      fclose ($fd);
    } else {
      $message_ha['testo'] = translateFN("Benvenuto in ADA!");
      $message_ha['testo'].= translateFN("Se hai problemi, dubbi o domande, puoi inviare un messaggio al tuo")."<a href=\"$http_root_dir/comunica/send_message.php?destinatari=$tutor_uname\">".translateFN("E-practitioner")."</a>.";
    }
    $message_ha['data_ora'] = "now";
    $message_ha['mittente'] = $tutor_uname;
    // e-mail
    $message_ha['tipo'] = ADA_MSG_MAIL;
    $res = $mh->send_message($message_ha);
    // messaggio interno
    $message_ha['tipo'] = ADA_MSG_SIMPLE;
    //$res = $mh->send_message($message_ha);
    // reload messages to show this one !
    $user_messages = $userObj->get_messagesFN($sess_id_user);
  }
}
if ($id_profile == AMA_TYPE_STUDENT && $log_enabled){
  $user_level = (string) $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
  $user_score =  (string) $userObj->get_student_score($sess_id_user,$sess_id_course_instance);
  $user_history = $userObj->history;
}

/**
 * service completeness
 */
if ($id_profile == AMA_TYPE_STUDENT && defined('MODULES_SERVICECOMPLETE') && MODULES_SERVICECOMPLETE) {
	if (isset($courseInstanceObj) && isset($courseObj) && isset($userObj) &&
		is_object($courseInstanceObj) && is_object($courseObj) && is_object($userObj))
	{		
		if ($user_status!=ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) {

			// need the service-complete module data handler
			require_once MODULES_SERVICECOMPLETE_PATH . '/include/init.inc.php';
			$mydh = AMACompleteDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
			// load the conditionset for this course
			$conditionSet = $mydh->get_linked_conditionset_for_course($courseObj->getId());
			$mydh->disconnect();
			
			if ($conditionSet instanceof CompleteConditionSet) {
				// evaluate the conditionset for this instance ID and course ID
				$is_course_instance_complete = $conditionSet->evaluateSet(array( $courseInstanceObj->getId(), $userObj->getId() ));
			} else {
				$is_course_instance_complete = false;
			}
			
			// if course is complete, save this information to the db
			if ($is_course_instance_complete) {
				require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
				$s = new Subscription($userObj->getId(), $courseInstanceObj->getId());
				$s->setSubscriptionStatus(ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED);
				if (isset($userObj->livello) && intval($userObj->livello)>0) $s->setStartStudentLevel($userObj->livello);
				$subscribedCount = Subscription::updateSubscription($s);
			}
		}
	}
}
/**
 * end service completeness
 */

/**
 * Authors can edit public courses assigned to themselves
 */
if ($id_profile == AMA_TYPE_AUTHOR && isset($courseObj) && $courseObj instanceof Course && $courseObj->getIsPublic()) {
	$mod_enabled = ($userObj->getId() == $courseObj->getAuthorId());
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
$layout_dataAr['node_type'] = isset($node_type) ? $node_type : null;
$layout_dataAr['family'] = isset($template_family) ? $template_family : null;
$layout_dataAr['node_author_id'] = isset($node_author_id) ? $node_author_id : null;
$layout_dataAr['node_course_id'] = isset($node_course_id) ? $node_course_id : null;
$layout_dataAr['module_dir'] = isset($module_dir) ? $module_dir : null;
?>