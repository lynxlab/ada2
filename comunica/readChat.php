<?php
/**
 * read chat
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

include_once 'include/MessageHandler.inc.php';
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
if (!isset($_POST['chatroom']) || !isset($_POST['lastMsgId'])) {
  exitWith_JSON_Error(translateFN('Errore: parametri passati allo script PHP non corretti'));
}

$id_chatroom  = (int)$_POST['chatroom'];

$lastMsgId = (int)$_POST['lastMsgId'];

/*
 * Get Chatroom
 */
$chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
if (AMA_DataHandler::isError($chatroomObj)) {
  exitWith_JSON_Error(translateFN('Errore nella creazione della chatroom'));
}

/*
 * Get chatroom info
 */
$chatroom_ha= $chatroomObj->get_info_chatroomFN($id_chatroom);
if (AMA_DataHandler::isError($chatroomObj)) {
  exitWith_JSON_Error(translateFN("Errore nell'ottenimento dei dati sulla chatroom"));
}

if (is_array($chatroom_ha)) {
  // get the topic of the chatroom
  $chat_topic = $chatroom_ha['argomento_chat'];
}
else {
  /*
   * Gestire uscita dalla chat
   */
  // close_chat template will be loaded
  $self = 'close_chat';
  // motivate the exit of the user
  $exit_reason = EXIT_REASON_NOT_EXIST;
  // open close_chat.php
}
/*
 * Get user status in the current chatroom
 */

$user_status = $chatroomObj->get_user_statusFN($sess_id_user,$id_chatroom);
if (AMA_DataHandler::isError($user_status)) {
  exitWith_JSON_Error(translateFN("Errore nell'ottenimento dello stato dell'utente nella chatroom"));
}

/*
 * User has been banned from the chatroom
 */
if($user_status==STATUS_BAN) {
  exitWith_JSON_Error(translateFN('Sei stato bannato dalla chatroom'),2);
}

/*
 * User has been kicked from the chatroom
 */
if($user_status==STATUS_EXIT) {
  exitWith_JSON_Error(translateFN("Sei stato cacciato dalla chatroom"),2);
}

/*
 * lettura messaggi
 */

$mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
if (AMA_DataHandler::isError($mh)) {
  exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto MessageHandler"));
}

// set the sorting filter, we will get the list of the messages sorted by this variable
if (!isset($sort_field)) {
  $sort_field = "data_ora asc";
}
elseif($sort_field == "data_ora") {
  $sort_field .= " asc";
}

session_write_close();

$msgs_pub_ha = array();
/*
 * vito, uso $fields_list per indicare quali messaggi ottenere: quelli inviati
 * a partire dall'avvio della chat o quelli inviati a partire dall'ultima lettura
 */
// TODO: usare find_messages al posto di get_messages e passare la clausola corretta
$fields_list = $lastMsgId;
$msgs_pub_ha = $mh->get_messages($sess_id_user, ADA_MSG_CHAT, $fields_list, $sort_field);
if (AMA_DataHandler::isError($msgs_pub_ha)) {
  exitWith_JSON_Error(translateFN('Errore nella lettura dei messaggi dal DB'));
}

$msgs_priv_ha = array();
$msgs_priv_ha = $mh->get_messages($sess_id_user, ADA_MSG_PRV_CHAT,$fields_list,$sort_field);
if(AMA_DataHandler::isError($msgs_priv_ha)) {
  exitWith_JSON_Error(translateFN('Errore nella lettura dei messaggi dal DB'));
}

//merge the two arrays without losing their keys.
//In this case each key is the id of one message that is unique

/*
 * NUOVO CODICE MESSAGGI CHAT
 */
$current_public_message  = 0;
$current_private_message = 0;
$total_private_messages  = count($msgs_priv_ha);
$total_public_messages   = count($msgs_pub_ha);

$msgs_number = $total_public_messages + $total_private_messages;

$messages_display_Ha = array();

//$json_data = "[";

for ( $i = 0; $i <$msgs_number; $i++ )
{
  if(($current_public_message < $total_public_messages)
  && ($current_private_message < $total_private_messages))
  {
    if($msgs_pub_ha[$current_public_message]['id_messaggio']
    < $msgs_priv_ha[$current_private_message]['id_messaggio'])
    {
      //            $json_data .= thisChatMessageToJSON($msg_pub_ha[$current_public_message]);
      $messages_display_Ha[$i] = $msgs_pub_ha[$current_public_message];
      $current_public_message++;
    }
    else
    {
      //            $json_data .= thisChatMessageToJSON($msgs_priv_ha[$current_private_message]);
      $messages_display_Ha[$i] = $msgs_priv_ha[$current_private_message];
      $current_private_message++;
    }
  }
  else if($current_public_message < $total_public_messages)
  {
    //        $json_data .= thisChatMessageToJSON($msg_pub_ha[$current_public_message]);
    $messages_display_Ha[$i] = $msgs_pub_ha[$current_public_message];
    $current_public_message++;
  }
  else if($current_private_message < $total_private_messages)
  {
    //        $json_data .= thisChatMessageToJSON($msgs_priv_ha[$current_private_message]);
    $messages_display_Ha[$i] = $msgs_priv_ha[$current_private_message];
    $current_private_message++;
  }

}

$json_data = array_map(function($message){
  return [
    'id' => $message['id_messaggio'],
    'tipo' => $message['tipo'],
    'time' => ts2tmFN($message['data_ora']),
    'sender' => $message['nome'],
    'text' => stripslashes($message['testo'])
  ];
}, $messages_display_Ha);

/*
 * fine di costruisce la stringa json contenente i messaggi ricevuti
 */

/*
 * invio della risposta JSON al simulatore
 */
$error  = 0;

//$response = '{"error" : '.$error.', "execution_time" : '.$total_time.'}';

header('Content-Type: application/json');
die(json_encode(['error'=>$error, "data" => $json_data ]));