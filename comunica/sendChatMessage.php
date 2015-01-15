<?php
/**
 * send chat message
 *
 * @package		comunica
 * stamos
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
$start_time = time() + microtime();

require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array();

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';
//include_once 'include/MessageHandler.inc.php';
include_once 'include/ChatDataHandler.inc.php';
include_once 'include/ChatRoom.inc.php';
include_once 'include/adaChatUtilities.inc.php';
/*
 * YOUR CODE HERE
 */

/*
 * Check that this script was called with the right arguments.
 * If not, stop script execution and report an error to the caller.
 */
if(!isset($_POST['chatroom']) || !isset($_POST['message_to_send'])) {
  exitWith_JSON_Error(translateFN("Errore: parametri passati allo script PHP non corretti, "));
}
/*
 * Get the chatroom id
 */
$id_chatroom = $_POST['chatroom'];
/*
 * Get from $_POST the text message to send in the chatroom.
 */
$message_to_send = $_POST['message_to_send'];

$mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
if (AMA_DataHandler::isError($mh)) {
  exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto MessageHandler"));
}

$chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
if (AMA_DataHandler::isError($chatroomObj)) {
  exitWith_JSON_Error(translateFN("Errore nella creazione della chatroom"));
}

$testo = $message_to_send;

// Initialize errors array
$errors = array();

// get the status of the user into the current chatroom
$user_status= $chatroomObj->get_user_statusFN($sess_id_user,$id_chatroom);
if (AMA_DataHandler::isError($user_status)) {
  exitWith_JSON_Error(translateFN("Errore nell'ottenimento delle informazioni sullo stato dell'utente nella chatroom"));
}

if($user_status == STATUS_MUTE) {
  exitWith_JSON_Error(translateFN("Non hai il permesso di parlare in questa stanza!"));
}
if($user_status == STATUS_BAN) {
  exitWith_JSON_Error(translateFN("Sei stato allontanato da questa stanza!"), 2);
}
/*
 * Distinguish public from private message
 */

// this is the text including even the command and the receiver name, if it is present
$initial_text = strip_tags($testo,"<B><I><LINK><URL><U>");

// get the length of the text
$lung = strlen($initial_text);

// if no command string is present
if (!is_integer(strpos($initial_text, "/msg "))
and !is_integer(strpos($initial_text, "/to "))
and !is_integer(strpos($initial_text, "/a "))){

  //this is a common chatroom message
  $message_type = ADA_MSG_CHAT;
  $final_text = addslashes($initial_text);
}
else {
  // text including the receiver name and the text to send
  $private_text = strstr($initial_text, " ");

  // the lenght of the text
  $num_char = strlen($private_text);

  // get the text, discard the first space
  $private_text = substr($private_text,1,$num_char);

  // get the position of the first space after the name of the receiver
  $pos = strpos($private_text," ");

  // extract the name of the receiver
  $receiver_name = substr($private_text, 0, $pos);

//  $udh = UserDataHandler::instance(MultiPort::getDSN($sess_selected_tester));
  $udh = UserDataHandler::instance($_SESSION['sess_selected_tester_dsn']);
  if (AMA_DataHandler::isError($udh)) {
    exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto UserDataHandler"));
  }

  // verify that the user typed a correct username
  $res_ar = $udh->find_users_list(array(),"username='$receiver_name'");

  if (AMA_DataHandler::isError($res_ar)) {
    exitWith_JSON_Error(translateFN("Errore nella lettura dello username del destinatario del messaggio privato"));
  }

  // getting only user_id
  $id_receiver= $res_ar[0][0];

  if(empty($id_receiver)) {
    $errors["receiver"] = translateFN("Il Destinatario inserito contiene un nome utente non valido!");
  }

  //extract the text to send
  $private_text = substr($private_text, ($pos+1), $num_char);
  // private chat messagge type
  $message_type = ADA_MSG_PRV_CHAT;
  $final_text = $private_text;
}

// a message can be sent only if no errors are found
if (count($errors) == 0) {
  //prepare message to send
  $message_ha = isset($send_chat_message_form) ? $send_chat_message_form : null;
  $message_ha['tipo'] = $message_type;

  if ($message_ha['tipo']== ADA_MSG_PRV_CHAT) {
    $message_ha['destinatari']= $receiver_name;
  }

  $message_ha['testo'] = $final_text;
  //$id_chatroom = $id_chatroom;
  $message_ha['data_ora'] = "now";
  $message_ha['mittente'] = $user_uname;
  $message_ha['id_group']= $id_chatroom;

  // delegate sending to the message handler
  $res = $mh->send_message($message_ha);
  if (AMA_DataHandler::isError($res)) {
    $code = $res->errorMessage();
    exitWith_JSON_Error(translateFN("Errore nell'invio del messaggio $code"));
  }
  //	log_this("$read_res", 4);
}// end if count

$data =  array('id_chatroom'=> $id_chatroom);

$end_time = time() + microtime();
$total_time = $end_time - $start_time;
/*
 * Send back JSON data to caller
 */
$response = '{"error" : 0, "execution_time" : '.$total_time.'}';
print $response;
?>