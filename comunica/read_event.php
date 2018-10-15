<?php
/**
 * READ EVENT.
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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
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

include_once 'include/ADAEvent.inc.php';
/*
 * YOUR CODE HERE
 */

if ($id_course) {
  $sess_id_course = $id_course;
}

if (isset($id_course_instance)) {
  $sess_id_course_instance = $id_course_instance;
} else $sess_id_course_instance = null;

if (isset($del_msg_id) and !empty($del_msg_id)){

  $res = MultiPort::removeUserAppointments($userObj, array($del_msg_id));
  if (AMA_DataHandler::isError($res)) {
    $errObj = new ADA_Error($res,  translateFN('Errore durante la cancellazione di un evento'),
                             NULL, NULL, NULL,
                             'comunica/list_events.php?status='.urlencode(translateFN('Errore durante la cancellazione'))
    );
  }
  else {
    $status = translateFN('Cancellazione eseguita');
    header("Location: list_events.php?status=$status");
    exit();
  }
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

/*
 * Find the appointment
 */
$msg_ha = MultiPort::getUserAppointment($userObj, $msg_id);
if (AMA_DataHandler::isError($msg_ha)){
  $errObj = new ADA_Error($msg_ha,  translateFN('Errore durante la lettura di un evento'),
                           NULL, NULL, NULL,
                           'comunica/list_events.php?status='.urlencode(translateFN('Errore durante la lettura'))
  );
}


/**
 * Conversione Time Zone
 */
$tester_TimeZone = MultiPort::getTesterTimeZone($tester);
$offset          = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
$date_time       = $msg_ha['data_ora'];
$date_time_zone  = $date_time + $offset;
$zone 			 = translateFN("Time zone:") . " " . $tester_TimeZone;
$Data_messaggio  = AMA_DataHandler::ts_to_date($date_time_zone, "%d/%m/%Y - %H:%M:%S") ." " . $zone;
//$Data_messaggio = AMA_DataHandler::ts_to_date($msg_ha['data_ora'], "%d/%m/%Y - %H:%M:%S");

/*
 * Check if the subject has an internal identifier and remove it
 */
$oggetto = ADAEventProposal::removeEventToken($msg_ha['titolo']);


$mittente = $msg_ha['mittente'];

$destinatario = str_replace (",", ", ", $msg_ha['destinatari']);
// $destinatario = $msg_ha['destinatari'];


$dest_encode = urlencode($mittente);
if (isset($message_text) && strlen($message_text)>0) {
	$testo= urlencode(trim($message_text));
} else {
	$message_text='';
	$testo='';
}
$oggetto_url=urlencode(trim($oggetto));

// Registrazione variabili per replay
$destinatari_replay = $mittente; //
$_SESSION['destinatari_replay'] = $destinatari_replay;
$testo_replay = trim($message_text);
$_SESSION['testo_replay'] = $testo_replay;
$titolo_replay = trim($oggetto);
$_SESSION['titolo_replay'] = $titolo_replay;
$destinatari_replay_all = $mittente . "," . $destinatario;
$_SESSION['destinatari_replay_all'] = $destinatari_replay_all;

$message_text = ADAEvent::parseMessageText($msg_ha);

if ((empty($status)) or (!isset($status))) {
  $status = translateFN("Lettura appuntamento");
}
$node_title = ""; // empty

$content_dataAr = array(
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $status,
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'level'          => $user_level,
  'mittente'       => $mittente,
  'Data_messaggio' => $Data_messaggio,
  'oggetto'        => $oggetto,
  'destinatario'   => $destinatario,
  'message_text'   => $message_text
);

ARE::render($layout_dataAr, $content_dataAr);
?>