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
include_once 'include/ADAEvent.inc.php';
/*
 * YOUR CODE HERE
 */

if ($id_course) {
  $sess_id_course = $id_course;
}

if ($id_course_instance) {
  $sess_id_course_instance = $id_course_instance;
}

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
$testo= urlencode(trim($message_text));
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

//$go_back = "<a href=\"javascript:self.close()\">".translateFN("chiudi")."</a>";
$go_back = "<a href=\"#\" onclick=\"closeMeAndReloadParent();\">".translateFN("chiudi")."</a>";
$go_map = "<a href = \" map.php?id_node=$sess_id_node\">" . translateFN("Vai alla mappa") . "</a>";
$go_print = "<a href=\" view.php?id_node=" . $sess_id_node . "&op=print\">"  . translateFN("Stampa") . "</a>";
$node_title = ""; // empty
//$menu_01 = "<a href=\"send_event.php\">" . translateFN("Nuovo") . "</a>";
//$menu_02 = "<a href=\"read_event.php?del_msg_id=" . $msg_id . "\">" . translateFN("Cancella") . "</a>";
$menu_03 = ""; //"<a href=\"send_event.php?op=replay_all\">" . translateFN("Rispondi a tutti") . "</A>";

$content_dataAr = array(
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $status,
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'level'          => $user_level,
  'go_back'        => $go_back,
  'go_print'       => $go_print,  // OR ELSE AN ARRAY OF PLACEHOLDERS?
  'mittente'       => $mittente,
  'Data_messaggio' => $Data_messaggio,
  'oggetto'        => $oggetto,
  'destinatario'   => $destinatario,
  'message_text'   => $message_text,
 // 'menu_01'        => $menu_01,
 // 'menu_02'        => $menu_02,
  'menu_03'        => $menu_03
);

ARE::render($layout_dataAr, $content_dataAr);
?>