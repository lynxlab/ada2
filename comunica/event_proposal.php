<?php
/**
 * EVENT PROPOSAL.
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
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';
include_once 'include/ADAEvent.inc.php';

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';

$error_page = HTTP_ROOT_DIR .'/comunica/event_proposal.php';

$newline = "\r\n";

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * Controllo validita' sui dati in arrivo dal form
   */
  $selected_date         = $_POST['date'];
  $course_instance       = $_POST['id_course_instance'];
  $practitioner_proposal = $_SESSION['practitioner_proposal'];
  $msg_id                = $_SESSION['event_msg_id'];
  unset($_SESSION['practitioner_proposal']);
  unset($_SESSION['event_msg_id']);

  $mittente    = $user_uname;
  $destinatari = array($practitioner_proposal['mittente']);
  $subject     = $practitioner_proposal['titolo'];


  $tutor_id = $common_dh->find_user_from_username($practitioner_proposal['mittente']);
  if(AMA_Common_DataHandler::isError($tutor_id)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento del practitioner"));
  }
  $tutorObj = MultiPort::findUser($tutor_id);

  /*
   * Get the ada admin username, it is needed in order to send email notifications
   * to both the users as ADA.
   */
  $admtypeAr = array(AMA_TYPE_ADMIN);
  $admList = $common_dh->get_users_by_type($admtypeAr);
  if (!AMA_DataHandler::isError($admList)){
    $adm_uname = $admList[0]['username'];
  } else {
    $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
  }

  /*
   * Obtain a messagehandler instance for the correct tester
   */
  if(MultiPort::isUserBrowsingThePublicTester()) {
  /*
   * In base a event_msg_id, ottenere connessione al tester appropriato
   */
    $data_Ar = MultiPort::geTesterAndMessageId($msg_id);
    $tester  = $data_Ar['tester'];
  }
  else {
    /*
     * We are inside a tester
     */
    $tester = $sess_selected_tester;
  }
  $tester_dsn = MultiPort::getDSN($tester);

  $mh = MessageHandler::instance($tester_dsn);


  if($selected_date == 0) {
    /*
     * Nessuna tra le date proposte va bene
     */

    $flags = ADA_EVENT_PROPOSAL_NOT_OK | $practitioner_proposal['flags'];
    $message_content = $practitioner_proposal['testo'];

    $message_ha = array(
      'tipo'        => ADA_MSG_AGENDA,
      'flags'       => $flags,
      'mittente'    => $mittente,
      'destinatari' => $destinatari,
      'data_ora'    => 'now',
      'titolo'      => $subject,
      'testo'       => $message_content
    );

    /*
     * This email message is sent only to the practitioner.
     * Send here.
     */
    $clean_subject = ADAEventProposal::removeEventToken($subject);
    $email_message_ha = array(
      'tipo'        => ADA_MSG_MAIL,
      'mittente'    => $adm_uname,
      'destinatari' => $destinatari,
      'data_ora'    => 'now',
      'titolo'      => 'ADA: ' . translateFN('a user asks for new event proposal dates'),
      'testo'       => sprintf(translateFN('Dear practitioner, the user %s is asking you for new event dates for the appointment %s.\r\nThank you.'), $userObj->getFullName(), $clean_subject)
    );

    /*
     * Send the email message
     */
    $res = $mh->send_message($email_message_ha);
    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio ERR_0')));
    }
    $text = sprintf(translateFN("La richiesta di modifica delle date proposte è stata correttamente inviata all'utente %s."), $tutorObj->getFullName());
  }
  else {

  	/*
     * L'utente ha scelto una data tra quelle proposte, creiamo l'appuntamento
     * e, se di tipo appuntamento in chat, creiamo anche la chatroom.
     */

    $tester_dh = AMA_DataHandler::instance($tester_dsn);

    $id_course = $tester_dh->get_course_id_for_course_instance($course_instance);
    if(AMA_DataHandler::isError($id_course)) {
      $errObj = new ADA_Error($id_chatroom, translateFN("An error occurred."));
    }

    $tester_infoAr = $common_dh->get_tester_info_from_pointer($tester);
    if(AMA_Common_DataHandler::isError($tester_infoAr)) {
      $errObj = new ADA_Error($service_infoAr, translateFN("An error occurred."));
    }
    $tester_name = $tester_infoAr[1];

    $service_infoAr = $common_dh->get_service_info_from_course($id_course);
    if(AMA_Common_DataHandler::isError($service_infoAr)) {
      $errObj = new ADA_Error($service_infoAr, translateFN("An error occurred."));
    }
    $service_name = translateFN($service_infoAr[1]);

    $date_data_Ar = explode('_',$_POST['date']);
    $date = $date_data_Ar[0];
    $time = $date_data_Ar[1];
    $time = "$time:00";

    $offset = 0;
    if ($tester === NULL) {
    	$tester_TimeZone = SERVER_TIMEZONE;
    } else {
      	$tester_TimeZone = MultiPort::getTesterTimeZone($tester);
		$offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
    }
    $data_ora = sumDateTimeFN(array($date,$time)) - $offset;

    $event_token = ADAEventProposal::extractEventToken($subject);

    $event_flag = 0;
    if(ADA_CHAT_EVENT & $practitioner_proposal['flags']) {
      $new_subject    = translateFN('Appuntamento in chat');
      //$url = HTTP_ROOT_DIR.'/comunica/chat.php';
      $event_flag = ADA_CHAT_EVENT;
    }
    else if(ADA_VIDEOCHAT_EVENT & $practitioner_proposal['flags']) {
      $new_subject    = translateFN('Appuntamento in videochat');
      //$url = HTTP_ROOT_DIR.'/comunica/videochat.php';
      $event_flag = ADA_VIDEOCHAT_EVENT;
    }
    else if(ADA_PHONE_EVENT & $practitioner_proposal['flags']) {
      $new_subject    = translateFN('Appuntamento telefonico');
      //$url = NULL;
      $event_flag = ADA_PHONE_EVENT;
    }
    else if(ADA_IN_PLACE_EVENT & $practitioner_proposal['flags']) {
      $new_subject    = translateFN('Appuntamento in presenza');
      //$url = NULL;
      $event_flag = ADA_IN_PLACE_EVENT;
    }

    $message_text  = sprintf(translateFN('Provider: "%s".%sService: "%s".%s'), $tester_name, $newline, $service_name, $newline);
    $message_text .= ' ' .sprintf(translateFN("L'appuntamento, di tipo %s,  si terrà il giorno %s alle ore %s."), $new_subject, $date, $time);

    /**
     * In case the user is confirming a videochat or a chat appointment,
     * we will also add a link to enter the chat or videochat directly from the
     * appointment message.
     */
    if((ADA_CHAT_EVENT & $practitioner_proposal['flags'])
    || (ADA_VIDEOCHAT_EVENT & $practitioner_proposal['flags'])) {


      if(ADA_CHAT_EVENT & $practitioner_proposal['flags']) {
        $event_flag = ADA_CHAT_EVENT;

        $end_time = $data_ora + $service_infoAr[7]; //durata_max_incontro

        $chatroom_ha = array(
          'id_course_instance' => $course_instance,
          'id_chat_owner'      => $practitioner_proposal['id_mittente'], // this is the id of the practitioner
          //'chat_type'      => $chat_type, // di default e' CLASS_CHAT
          'chat_title'       => ADAEventProposal::addEventToken($event_token,$new_subject),
          'chat_topic'       => '',
          'start_time'       => $data_ora, // parte alla stessa ora dell'appuntamento
          'end_time'         => $end_time//$data_ora + 3600,
           //'welcome_msg'        => $welcome_msg,  //usiamo messaggio di benvenuto di default
          //'max_users'          => $max_users     // di default 2 utenti
        );
        require_once 'include/ChatDataHandler.inc.php';
        require_once 'include/ChatRoom.inc.php';

        $id_chatroom = ChatRoom::add_chatroomFN($chatroom_ha, $tester_dsn);
        if(AMA_DataHandler::isError($id_chatroom)) {
          $errObj = new ADA_Error($id_chatroom,
                                   translateFN("Si è verificato un errore nella creazione della chatroom. L'appuntamento non è stato creato."),
                                   NULL, NULL, NULL, $userObj->getHomePage());
        }
      }
      else {
        $event_flag = ADA_VIDEOCHAT_EVENT;
      }
      $message_text .= ADAEvent::generateEventMessageAction($event_flag, $id_course, $course_instance);
    }


    $message_ha = array(
      'tipo'	      => ADA_MSG_AGENDA,
      'flags'       => ADA_EVENT_CONFIRMED | $event_flag,
      'mittente'    => $user_uname,
      'destinatari' => array($user_uname,$practitioner_proposal['mittente']),
      'data_ora'	  => $data_ora,
      'titolo'      => ADAEventProposal::addEventToken($event_token,$new_subject),
      'testo'       => $message_text
    );

    /*
     * Here we send an email message as an appointment reminder.
     * We send it seprately to the user and to the practitioner, since we do not
     * want the user to know the practitioner's email address.
     */

    $appointment_type = $new_subject;
    $appointment_title = ADAEventProposal::removeEventToken($subject);
    $appointment_message = sprintf(translateFN('Provider: "%s".%sService: "%s".%s'), $tester_name, $newline, $service_name, $newline)
                         . ' ' . sprintf(translateFN('This is a reminder for the appointment %s: %s in date %s at time %s'), $appointment_title, $appointment_type, $date, $time);

    $practitioner_email_message_ha = array(
      'tipo'        => ADA_MSG_MAIL,
      'mittente'    => $adm_uname,
      'destinatari' => array($practitioner_proposal['mittente']),
      'data_ora'    => 'now',
      'titolo'      => 'ADA: ' . translateFN('appointment reminder'),
      'testo'       => $appointment_message
    );

    $user_email_message_ha = array(
      'tipo'        => ADA_MSG_MAIL,
      'mittente'    => $adm_uname,
      'destinatari' => array($user_uname),
      'data_ora'    => 'now',
      'titolo'      => 'ADA: ' . translateFN('appointment reminder'),
      'testo'       => $appointment_message
    );

    /*
     * Send the email message to the practitioner
     */
    $res = $mh->send_message($practitioner_email_message_ha);
    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio ERR_1')));
    }

    /*
     * Send the email message to the user
     */
    $res = $mh->send_message($user_email_message_ha);
    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio ERR_2')));
    }

    // TODO: al posto di $practitioner_proposal['mittente'] passare $tutorObj->getFullname()
    $text = sprintf(translateFN('Provider: "%s".%sService: "%s".%s'), $tester_name, $newline, $service_name, $newline)
                        . ' ' . sprintf(translateFN("L'appuntamento con l'utente %s, in data %s alle ore %s, è stato inserito correttamente."),
                    $tutorObj->getFullName(), $date, $time);
  }

  $res = $mh->send_message($message_ha);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
    NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio ERR_3')));
  }


  /*
   * SE NON SI SONO VERIFICATI ERRORI NELL'INVIO DELLA RISPOSTA AL TUTOR,
   * POSSO MARCARE COME ELIMINATO IL MESSAGGIO RELATIVO ALLA PROPOSTA DEL PRACTITIONER
   */

  MultiPort::removeUserAppointments($userObj, array($msg_id));

  $form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($text);
}
elseif(isset($_GET['err_msg'])) {
  $error_message = translateFN('An error occurred while processing your request, please try again later.')
          . '<br />'
          . translateFN('If the problem persists, please contact the administrator.');

  $form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($error_message);
}
else if(isset($msg_id)) {

  $data = MultiPort::getUserAppointment($userObj,$msg_id);
  $_SESSION['practitioner_proposal'] = $data;
  $_SESSION['event_msg_id'] = $msg_id;
  /*
   * Check if the user has already an appointment in one of the proposed dates
   * or if an appointment proposal is in the past.
   */
  $datetimesAr = ADAEventProposal::extractDateTimesFromEventProposalText($data['testo']);
  if($datetimesAr === FALSE) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle date per l'appuntamento"));
  }

  /*
   * Obtain a messagehandler instance for the correct tester
   */
  if(MultiPort::isUserBrowsingThePublicTester()) {
  /*
   * In base a event_msg_id, ottenere connessione al tester appropriato
   */
    $data_Ar = MultiPort::geTesterAndMessageId($msg_id);
    $tester  = $data_Ar['tester'];
  }
  else {
    /*
     * We are inside a tester
     */
    $tester = $sess_selected_tester;
  }

  if(($value = ADAEventProposal::canProposeThisDateTime($userObj,$datetimesAr[0]['date'], $datetimesAr[0]['time'], $tester)) !== TRUE) {
    $errors['date1'] = $value;
  }
  if(($value = ADAEventProposal::canProposeThisDateTime($userObj,$datetimesAr[1]['date'], $datetimesAr[1]['time'], $tester)) !== TRUE) {
    $errors['date2'] = $value;
  }
  if(($value = ADAEventProposal::canProposeThisDateTime($userObj,$datetimesAr[2]['date'], $datetimesAr[2]['time'], $tester)) !== TRUE) {
    $errors['date3'] = $value;
  }

  $form = CommunicationModuleHtmlLib::getProposedEventForm($data, $errors, $tester);
}

$titolo = translateFN('Proposta di appuntamento');

$content_dataAr = array(
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'titolo'         => $titolo,
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $err_msg,
  'data'		   => $form->getHtml(),
  'label'		   => $titolo
);

ARE::render($layout_dataAr, $content_dataAr);
?>