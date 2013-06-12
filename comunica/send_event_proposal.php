<?php
/**
 * SEND EVENT PROPOSAL.
 *
 * @package		comunica
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
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

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_STUDENT => array('layout')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';
/*
 * YOUR CODE HERE
 */
//include_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php'; //incluso da comunica_functions

//$success    = HTTP_ROOT_DIR.'/comunica/list_events.php';
//$error_page = HTTP_ROOT_DIR.'/comunica/send_event.php';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * Controllo validita' sui dati in arrivo dal form
   */

  if(isset($_SESSION['event_msg_id'])) {
    $previous_proposal_msg_id = $_SESSION['event_msg_id'];
    $event_token = '';
  }
  else {
    /*
     * Costruiamo qui l'identificatore della catena di proposte che portano a
     * fissare un appuntamento.
     */
    $event_token = ADAEventProposal::generateEventToken($id_user, $userObj->getId(), $id_course_instance);
  }

  /*
   * Validazione dei dati: le date proposte devono essere valide e non devono essere antecedenti
   * a quella odierna (come timestamp)
   */
  $errors = array();

  if(DataValidator::validate_not_empty_string($subject) === FALSE) {
    $errors['subject'] = ADA_EVENT_PROPOSAL_ERROR_SUBJECT;
  }

  if(($value = ADAEventProposal::canProposeThisDateTime($userObj, $date1, $time1, $sess_selected_tester)) !== TRUE) {
    $errors['date1'] = $value;
  }
  if(($value = ADAEventProposal::canProposeThisDateTime($userObj, $date2, $time2, $sess_selected_tester)) !== TRUE) {
    $errors['date2'] = $value;
  }
  if(($value = ADAEventProposal::canProposeThisDateTime($userObj, $date3, $time3, $sess_selected_tester)) !== TRUE) {
    $errors['date3'] = $value;
  }


  $datetimesAr = array(
    array('date' => $date1, 'time' => $time1),
    array('date' => $date2, 'time' => $time2),
    array('date' => $date3, 'time' => $time3)
  );

  $message_content = ADAEventProposal::generateEventProposalMessageContent($datetimesAr, $id_course_instance, $notes);

  if(count($errors) > 0) {
    $data = array(
      'testo'  => $message_content,
      'titolo' => $subject,
      'flags'  => $type
    );
    $form = CommunicationModuleHtmlLib::getEventProposalForm($id_user, $data, $errors,$sess_selected_tester);
  }
  else {
    /*
	 * If we are ready to send the message, we can safely unset $_SESSION['event_msg_id'])
     */
    unset($_SESSION['event_msg_id']);

    $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));

    $addresseeObj = MultiPort::findUser($id_user);



    $message_ha = array(
      'tipo'        => ADA_MSG_AGENDA,
      'flags'       => ADA_EVENT_PROPOSED|$type,
      'mittente'    => $user_uname,
      'destinatari' => array($addresseeObj->username),
      'data_ora'    => 'now',
      'titolo'      => ADAEventProposal::addEventToken($event_token, $subject),
      'testo'       => $message_content
    );

    $res = $mh->send_message($message_ha);

    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
    }

    /*
     * If there aren't errors, redirect the user to his agenda
     */
      /*
     * SE ABBIAMO INVIATO UNA MODIFICA AD UNA PROPOSTA DI APPUNTAMENTO,
     * LA PROPOSTA PRECEDENTE DEVE ESSERE MARCATA COME CANCELLATA IN
     * DESTINATARI MESSAGGI PER L'UTENTE PRACTITIONER
     */
    if(isset($previous_proposal_msg_id)) {
      MultiPort::removeUserAppointments($userObj, array($previous_proposal_msg_id));
    }

    /*
     * Inviamo una mail all'utente in cui lo informiamo del fatto che il
     * practitioner ha inviato delle nuove proposte
     */
    $admtypeAr = array(AMA_TYPE_ADMIN);
    $admList = $common_dh->get_users_by_type($admtypeAr);
    if (!AMA_DataHandler::isError($admList)){
      $adm_uname = $admList[0]['username'];
    } else {
      $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
    }
    $clean_subject = ADAEventProposal::removeEventToken($subject);
    $message_content = sprintf(translateFN('Dear user, the practitioner %s has sent you new proposal dates for the appointment: %s.'), $userObj->getFullName(), $clean_subject);
    $message_ha = array(
      'tipo'        => ADA_MSG_MAIL,
      'mittente'    => $adm_uname,
      'destinatari' => array($addresseeObj->username),
      'data_ora'    => 'now',
      'titolo'      => 'ADA: ' . translateFN('new event proposal dates'),
      'testo'       => $message_content
    );
    $res = $mh->send_message($message_ha);
    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
    }

    $text = translateFN("La proposta di appuntamento è stata inviata con successo all'utente ") . $addresseeObj->getFullName() . ".";
    $form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($text);
    //header('Location: '.HTTP_ROOT_DIR.'/comunica/list_events.php');
    //exit();
  }
}
else {
  if(isset($msg_id)) {
    $data = MultiPort::getUserAppointment($userObj, $msg_id);
    if($data['flags'] & ADA_EVENT_PROPOSAL_OK) {
      /*
       * The user accepted one of the three proposed dates for the appointment.
       * E' UN CASO CHE NON SI PUO' VERIFICARE, visto che vogliamo che l'appuntamento
       * venga inserito non appena l'utente accetta una data porposta dal practitioner
       */
      $form = CommunicationModuleHtmlLib::getConfirmedEventProposalForm($data);
    }
    else {
      /*
       * The user did not accept the proposed dates for the appointment
       */
      $_SESSION['event_msg_id'] = $msg_id;
      $id_user = $data['id_mittente'];
      $errors = array();
      $form = CommunicationModuleHtmlLib::getEventProposalForm($id_user, $data, $errors, $sess_selected_tester);
    }
  }
  else {
    /*
     * Build the form used to propose an event. Da modificare in modo da passare
     * eventualmente il contenuto dei campi del form nel caso si stia inviando
     * una modifica ad una proposta di appuntamento.
     */
    $errors = array();
    $data = array();
  	$form = CommunicationModuleHtmlLib::getEventProposalForm($sess_id_user, $data, $errors, $sess_selected_tester);
  }
}

$title = translateFN('Invia proposta di appuntamento');

$content_dataAr = array(
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'titolo'         => $titolo,
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $err_msg,
  'data'	   => $form->getHtml(),
  'label'	   => $title
);


ARE::render($layout_dataAr, $content_dataAr);
?>