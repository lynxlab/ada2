<?php
/**
 * adachat
 *
 * @package		comunica
 * @author              Stamatios Filippis <st4m0s@gmail.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * vito, 24/09/2008
 *
 * The original script has been modified in order to work in an AJAX-like environment.
 * This script is now responsible for:
 * 1. user check
 * 2. course check
 * 3. obtaining page layout
 * 4. obtaining a chatroom
 * 5. do some checking on the obtained chatroom
 * 6. checking the user status in the obtained chatroom
 *
 * The javascript file used to handle AJAX interactions with ADA chat PHP scripts
 * is ada_chat_includes.js, which is included by adaChat.js.
 *
 * The PHP scripts used to implement the AJAX chat are:
 * 1. controlChat.php		- called to obtain informations about the users in the chatroom
 * 2. controlChatAction.php - called to execute a control action selected by the user
 * 3. readChat.php          - called to read the new messages in the chat
 * 4. sendChatMessage.php   - called to send a message in the chatroom
 * 5. topChat.php           - called to obtain the top chat
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
$neededObjAr = array(
  AMA_TYPE_STUDENT=> array('chatroom','layout'),
  AMA_TYPE_AUTHOR=> array('chatroom','layout'),
  AMA_TYPE_TUTOR => array('chatroom','layout')
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

/*
 *   session variables when coming from a course node.
 *
 *	 [sess_id_user]
 *   [sess_id_user_type]
 *   [sess_user_language]
 *   [sess_id_course]
 *   [sess_id_node]
 *   [sess_id_course_instance]
 */
if($exit_reason == NO_EXIT_REASON) {
  $chatroom_ha= $chatroomObj->get_info_chatroomFN($id_chatroom);
  // CONTROLLARE EVENTUALE ERRORE

  if (is_array($chatroom_ha)) {
    // get the id of the owner of the chatroom
    $id_owner = $chatroom_ha['id_proprietario_chat'];
    // check if the current user is the owner of the room
    if ($id_owner == $sess_id_user || $userObj->getType() == AMA_TYPE_TUTOR)
    {
        // gives him moderator access
        $operator = $chatroomObj->set_user_statusFN($sess_id_user,$sess_id_user,$id_chatroom,ACTION_SET_OPERATOR);
        // restituire l'errore via JSON
    }
    $started = $chatroomObj->is_chatroom_startedFN($id_chatroom);
    // restituire l'errore via JSON

    $still_running = $chatroomObj->is_chatroom_not_expiredFN($id_chatroom);
    // restituire l'errore via JSON

    $status = $chatroomObj->get_user_statusFN($sess_id_user,$id_chatroom);
    // restituire l'errore via JSON

    $complete= $chatroomObj->is_chatroom_fullFN($id_chatroom);

    $exit_reason = NO_EXIT_REASON;

    if(($status != STATUS_BAN)and($chatroomObj->error==1)) {
        $exit_reason = EXIT_REASON_WRONG_ROOM;
    }
    // user is banned from chatroom
    elseif($status == STATUS_BAN) {
        $exit_reason = EXIT_REASON_BANNED;
    }
    // chatroom session not started yet
    elseif (!$started) {
        $exit_reason = EXIT_REASON_NOT_STARTED;
    }
    // chatroom session terminated
    elseif(!$still_running) {
        $exit_reason = EXIT_REASON_EXPIRED;
    }
    // chatroom session terminated
    elseif($complete) {
        $exit_reason = EXIT_REASON_FULL_ROOM;
    }
    // everything is ok, enter into the chat
    else {
      //$mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
      $mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
      // send a message to announce the entrance of the user
      $message_ha['tipo']     = ADA_MSG_CHAT;
      $message_ha['data_ora'] = "now";
      $message_ha['mittente'] = "admin";
      $message_ha['id_group'] = $id_chatroom;
      $message_ha['testo']    = "<span class=user_name>$user_name</span> " .translateFN("&egrave; entrato nella stanza");

      $result = $mh->send_message($message_ha);
      // GESTIONE ERRORE
    }
  }
}

if ($exit_reason != NO_EXIT_REASON) {
  $chat = new CText('');
  $offset = 0;
  if ($_SESSION['sess_selected_tester'] === NULL) {
    $tester_TimeZone = SERVER_TIMEZONE;
  } else {
    $tester_TimeZone = MultiPort::getTesterTimeZone($_SESSION['sess_selected_tester']);
    $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
  }
  $current_time = ts2tmFN(time() + $offset);

  $close_page_message = addslashes(translateFN("You don't have a chat appointment at this time.")) . " ($current_time)";
  $optionsAr = array('onload_func' => "close_page('$close_page_message');");
}
else {
  //$event_token = $chatroomObj->get_event_token();
  $request_arguments = "chatroom=$id_chatroom";
  $chat = CommunicationModuleHtmlLib::getChat($request_arguments, $userObj, $event_token);
  $optionsAr = array('onload_func' => 'startChat();');
}
$banner = include ROOT_DIR.'/include/banner.inc.php';
/*
 * Create here the close link.
 */
$exit_chat = CDOMElement::create('a');
$exit_chat->addChild(new CText(translateFN('Chiudi')));
if($userObj instanceof ADAPractitioner) {
  // pass 1 to redirect the practitioner to the eguidance session evaluation form
  if(!empty($event_token)) {
    $_SESSION['sess_event_token'] = $event_token;
    $onclick = "exitChat(1,'event_token=$event_token');";
  }
  else {
    $onclick = 'exitChat(0,0);';
  }
  $exit_chat->setAttribute('onclick',$onclick);
}
else {
  // pass 0 to close the chat window
  $exit_chat->setAttribute('onclick','exitChat(0,0);');
  $onclick = 'exitChat(0,0);';
}

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
  'chat'      => $chat->getHtml(),
  'exit_chat' => $exit_chat->getHtml(),
  'user_name' => $user_name,
  'user_type' => $user_type,
  'user_level'   => $user_level,
  'onclick'=>$onclick,
  'last_visit' => $last_access,
  'status' => translateFN('Chatroom')
);


ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>