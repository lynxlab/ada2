<?php
/**
 * e-guidance tutor form.
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
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

$sess_navigationHistory = $_SESSION['sess_navigation_history'];
if($sess_navigationHistory->callerModuleWas('quitChatroom')
   || $sess_navigationHistory->callerModuleWas('close_videochat')
   || $sess_navigationHistory->callerModuleWas('list_events')
   || isset($_GET['popup'])
   ) {
  $self = whoami();
  $is_popup = TRUE;
}
else {
    $self =  'tutor';
  $is_popup = FALSE;
}

include_once 'include/tutor_functions.inc.php';
include_once 'include/eguidance_tutor_form_functions.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';
include_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  // Genera CSV a partire da contenuto $_POST
  // e crea CSV forzando il download

  if(isset($_POST['is_popup'])) {
    $href_suffix = '&popup=1';
    unset($_POST['is_popup']);
  }
  else {
    $href_suffix = '';
  }
  $eguidance_dataAr = $_POST;
  $eguidance_dataAr['id_tutor'] = $userObj->getId();

  if(isset($eguidance_dataAr['id_eguidance_session'])) {
    /*
     * Update an existing eguidance session evaluation
     */
    $result = $dh->update_eguidance_session_data($eguidance_dataAr);
    if(AMA_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
    }
  }
  else {
    /*
     * Save a new eguidance session evaluation
     */
    $result = $dh->add_eguidance_session_data($eguidance_dataAr);
    if(AMA_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
    }
  }
  //createCSVFileToDownload($_POST);

  //$text = translateFN('The eguidance session data were correctly saved.');
  //$form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($text);
  /*
   * Redirect the practitioner to user service detail
   */
  $tutored_user_id    = $eguidance_dataAr['id_utente'];
  $id_course_instance = $eguidance_dataAr['id_istanza_corso'];
  header('Location: user_service_detail.php?id_user='.$tutored_user_id.'&id_course_instance='.$id_course_instance.$href_suffix);
  exit();
}
else {

  /*
   * Obtain event_token from $_GET.
   */

  if(isset($_GET['event_token'])) {
    $event_token = DataValidator::validate_event_token($_GET['event_token']);
    if($event_token === FALSE) {
      $errObj = new ADA_Error(NULL,
                         translateFN("Dati in input per il modulo eguidance_tutor_form non corretti"),
                         NULL, NULL, NULL, $userObj->getHomePage());
    }
  }
  else {
    $errObj = new ADA_Error(NULL,
                         translateFN("Dati in input per il modulo eguidance_tutor_form non corretti"),
                         NULL, NULL, NULL, $userObj->getHomePage());
  }

  $id_course_instance = ADAEventProposal::extractCourseInstanceIdFromThisToken($event_token);

  /*
   * Get service info
   */
  $id_course = $dh->get_course_id_for_course_instance($id_course_instance);
  if(AMA_DataHandler::isError($id_course)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento dell'id del servzio"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }

  $service_infoAr = $common_dh->get_service_info_from_course($id_course);
  if(AMA_Common_DataHandler::isError($service_infoAr)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle informazioni sul servizio"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }

  $users_infoAr = $dh->course_instance_students_presubscribe_get_list($id_course_instance);
  if(AMA_DataHandler::isError($users_infoAr)) {
    $errObj = new ADA_Error($users_infoAr,translateFN("Errore nell'ottenimento dei dati dello studente"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }


  /*
   * Get tutored user info
   */
  /*
   * In ADA only a student can be subscribed to a specific course instance
   * if the service has level < 4.
   * TODO: handle form generation for service with level = 4 and multiple users
   * subscribed.
   */
  $user_infoAr = $users_infoAr[0];
  $id_user = $user_infoAr['id_utente_studente'];
  $tutoredUserObj = MultiPort::findUser($id_user);

  $service_infoAr['id_istanza_corso'] = $id_course_instance;
  $service_infoAr['event_token']      = $event_token;

  /*
   * Check if an eguidance session with this event_token exists. In this case,
   * use this data to fill the form.
   */
  $eguidance_session_dataAr = $dh->get_eguidance_session_with_event_token($event_token);
  if(!AMA_DataHandler::isError($eguidance_session_dataAr)) {
    if($is_popup) {
      $eguidance_session_dataAr['is_popup'] = true;
    }
    $form = TutorModuleHtmlLib::getEditEguidanceDataForm($tutoredUserObj, $service_infoAr, $eguidance_session_dataAr);
  }
  else {
    $last_eguidance_session_dataAr = $dh->get_last_eguidance_session($id_course_instance);
    if(AMA_DataHandler::isError($last_eguidance_session_dataAr)) {
      $errObj = new ADA_Error($users_infoAr,translateFN("Errore nell'ottenimento dei dati della precedente sessione di eguidance"),
                               NULL,NULL,NULL,$userObj->getHomePage());
    }

    if($is_popup) {
      $last_eguidance_session_dataAr['is_popup'] = true;
    }
    $form = TutorModuleHtmlLib::getEguidanceTutorForm($tutoredUserObj, $service_infoAr, $last_eguidance_session_dataAr);
  }
}

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'dati'      => $form->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
?>