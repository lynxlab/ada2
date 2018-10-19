<?php
/**
 * SEND EVENT.
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
/*
 * ACTUALLY THIS MODULE IS NOT USED IN ADA.
 * SO WE DO NOT ALLOW USERS HERE.
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR,AMA_TYPE_STUDENT, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout'),
  AMA_TYPE_SWITCHER => array('layout'),
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

if(MultiPort::isUserBrowsingThePublicTester()) {
   $sess_selected_tester = ADA_PUBLIC_TESTER;
}else{
    $sess_selected_tester = $_SESSION['sess_selected_tester'];
}


/*
 * YOUR CODE HERE
 */
include_once 'include/StringValidation.inc.php';
include_once 'include/address_book.inc.php';

if (!isset($op)) {
  $op = 'default';
}

$success    = HTTP_ROOT_DIR.'/comunica/list_events.php';
$error_page = HTTP_ROOT_DIR.'/comunica/send_event.php';


$title = translateFN('Pubblica appuntamento');
//$rubrica_ok = 0; // Address book not loaded yet

// Has the form been posted?
if($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($spedisci)) {
    $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));

    // Initialize errors array
    $errors = array();

    // Trim all submitted data
    $form = $_POST;
    foreach ($form as $key => $value){
      if (!is_array($value)) $$key = trim($value);
    }


    if(!isset($destinatari) || DataValidator::validate_not_empty_string($destinatari) === FALSE) {
        $errors['destinatari'] = ADA_EVENT_PROPOSAL_ERROR_RECIPIENT;
    }

    if(!isset($titolo) || DataValidator::validate_not_empty_string($titolo) === FALSE) {
        $errors['titolo'] = ADA_EVENT_PROPOSAL_ERROR_SUBJECT;
    }

    if(($value = ADAEventProposal::canProposeThisDateTime($userObj, $data_evento, $ora_evento, $sess_selected_tester)) !== TRUE) {
        $errors['$data_evento'] = $value;
    }

        // Check submitted subject
/*      if (!is_clean_text($titolo, 0, 128)){
        $errors["titolo"] = translateFN("L'oggetto dell'appuntamento contiene caratteri non validi");
        $titolo = clean_text($titolo, 0, 128);
      }
      else {
        $ora_evento_a = tm2tsFN($ora_evento);
        $ora_evento_b = ts2tmFN($ora_evento_a);
        $data_ora = sumDateTimeFN(array($data_evento,$ora_evento));

        $sort_field = "data_ora desc";

        $msgs_ha = $mh->get_messages($sess_id_user, ADA_MSG_AGENDA, array("id_mittente", "data_ora", "titolo", "priorita", "read_timestamp"),$sort_field);
        if(AMA_DataHandler::isError($msgs_ha)) {
          $errObj = new ADA_Error($res, translateFN('Errore in ottenimento appuntamenti'),
                         NULL, NULL, NULL,
                         $error_page.'?err_msg='.urlencode(translateFN('Errore in ottenimento appuntamenti'))
                         );
        }

        foreach ($msgs_ha as $msg_id => $msg_ar){
          $date_time = $msg_ar[1];
          if ($date_time==$data_ora) {
            $errors["data"] = translateFN("Esiste gi&agrave; un appuntamento a quell'ora");
          }
        }
      }
 *
 */

    // Actually send event only if no errors were found
    if (count($errors)== 0){

      // $recipients_ar = explode(",", $form["destinatari"]);

      // prepare event to send
      /*
      $offset = 0;
      if ($tester === NULL) {
    	$tester_TimeZone = SERVER_TIMEZONE;
      } else {
      	$tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	$offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
      }
      $data_ora = sumDateTimeFN(array($date,$time)) - $offset;
      */

      $message_ha = $form;

      $offset = 0;
      if ($sess_selected_tester === NULL) {
    	$tester_TimeZone = SERVER_TIMEZONE;
      } else {
      	$tester_TimeZone = MultiPort::getTesterTimeZone($sess_selected_tester);
	$offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
      }
      $data_ora = sumDateTimeFN(array($data_evento,$ora_evento))- $offset;
      $message_ha['data_ora'] = $data_ora; //"now";
      $message_ha['tipo']     = ADA_MSG_AGENDA;
      //$message_ha['mittente'] = $user_name;
      $message_ha['mittente'] = $user_uname;

      // delegate sending to the message handler
      $res = $mh->send_message($message_ha);
      if (AMA_DataHandler::isError($res)){
        $errObj = new ADA_Error($res, translateFN('Errore in inserimento appuntamento'),
                                 NULL, NULL, NULL,
                                 $error_page.'?err_msg='.urlencode(translateFN('Errore in inserimento appuntamento'))
                                 );
      }

      $status = urlencode(translateFN('Appuntamento inserito in agenda'));
      header("Location: $success?status=$status");
      exit();
    } // end if count

    // build up error message
    if (count($errors)) {
        $error_messages = array(
          ADA_EVENT_PROPOSAL_ERROR_DATE_FORMAT      => translateFN('Attenzione: il formato della data non è corretto.'),
          ADA_EVENT_PROPOSAL_ERROR_DATE_IN_THE_PAST => translateFN("Attenzione: la data e l'ora proposte per l'appuntamento sono antecedenti a quelle attuali."),
          ADA_EVENT_PROPOSAL_ERROR_DATE_IN_USE      => translateFN('Attenzione: è già presente un appuntamento in questa data e ora'),
          ADA_EVENT_PROPOSAL_ERROR_RECIPIENT        => translateFN('Bisogna specificare almeno un destinatario'),
          ADA_EVENT_PROPOSAL_ERROR_SUBJECT          => translateFN('The given event subject is not valid.')
        );

      $err_msg = "<strong>";
      foreach ($errors as $err){
        $err_msg .=$error_messages[$err]."<br>";
      }
      $err_msg .= "</strong>";
    }
    // *****
  } //end if Spedisci
  // *****

    $destinatari_Ar =  isset($form["destinatari"]) ? $form["destinatari"] : null;
    $destinatari = '';
    if (is_array($destinatari_Ar) && count($destinatari_Ar)>0) {
    	foreach($destinatari_Ar as $d) {
    		$destinatario = trim($d);
    		$div = CDOMElement::create('div', "id:$destinatario");
    		$checkbox    = CDOMElement::create('checkbox', "name:destinatari[], value:$destinatario, checked: checked");
    		$checkbox->setAttribute('onclick', "remove_addressee('$destinatario');");
    		$div->addChild($checkbox);
    		$div->addChild(new CText($destinatario));
    		$destinatari .= $div->getHtml();
    	}
    }


}

if (!isset($titolo)) {
  $titolo = "";
}
if (!isset($destinatari)) {
  $destinatari = "";
}
if (!isset($course_title)) {
  $course_title = "";
}
//
if ((empty($err_msg)) or (!isset($err_msg))){
  $err_msg = translateFN('Inserimento appuntamento');
}

if (!isset($ora_evento)) {
  $event_time = today_timeFN();
}else{
  $event_time = $ora_evento;
}
if (!isset($data_evento)) {
  $event_date = today_dateFN();
}else{
  $event_date = $data_evento;
}
/*
$event_time = today_timeFN();
$event_date = today_dateFN();
*/

$ada_address_book = EventsAddressBook::create($userObj);

$tester_TimeZone = MultiPort::getTesterTimeZone($sess_selected_tester);
$time = time() + get_timezone_offset($tester_TimeZone, SERVER_TIMEZONE);

/*
* Last access link
*/

if(isset($_SESSION['sess_id_course_instance'])){
        $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
  else {
        $last_access=$userObj->get_last_accessFN(null,"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }

 if($last_access=='' || is_null($last_access)){
    $last_access='-';
}

$content_dataAr = array(
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'user_level'   => $user_level,
  'titolo'         => $titolo,
  'testo'          => isset($testo) ? trim($testo) : '',
  'destinatari'    => isset($destinatari) ? trim($destinatari) : '',
  //'student_button' => $student_button,
  //'tutor_button'   => $tutor_button,
  //'author_button'  => $author_button,
  //'admin_button'   => $admin_button,
  //'indirizzi'      => $indirizzi,
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $err_msg,
  'timezone'       => $tester_TimeZone,
  'event_time'     => $event_time,
  'event_date'     => $event_date,
  'last_visit' => $last_access,
  'rubrica'        => $ada_address_book->getHtml(), //$rubrica,
  'status'         => $err_msg
);

$options_Ar = array('onload_func' => "load_addressbook();updateClock($time);");
//$options_Ar .= array('onload_func' => "updateClock($time);");
ARE::render($layout_dataAr, $content_dataAr, NULL, $options_Ar);
?>