<?php
/**
 * quit chat
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
 * Questo script esegue le operazioni di ada_chat.php, tranne l'inclusione dei vari script che compongono la chat.
 */


//if ( /*!isset($_GET['user']) ||*/ !isset($_GET['chatroom']) /*|| !isset($_GET['course_instance'])*/ )
if(!isset($_POST['chatroom']))
{
  exitWith_JSON_Error(translateFN("Errore: parametri passati allo script PHP non corretti"));
}

$id_user      = $sess_id_user;
$id_chatroom  = $_POST['chatroom'];//$_GET['chatroom'];
if (!isset($exit_reason)) {
  $exit_reason  = EXIT_REASON_QUIT;
}


/*
 * uscita dalla chatroom
 */
switch($exit_reason){
  case EXIT_REASON_QUIT:
    // initialize a new ChatDataHandler object

    $chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
    if (AMA_DataHandler::isError($chatroomObj))
    {
      exitWith_JSON_Error(translateFN("Errore nella creazione della chatroom"));
    }

    //get the type of the chatroom
    $chatroomHa= $chatroomObj->get_info_chatroomFN($id_chatroom);
    if (AMA_DataHandler::isError($chatroomHa))
    {
      exitWith_JSON_Error(translateFN("Errore nell'ottenimento dei dati sulla chatroom"));
    }

    // we have to distinguish the case that the chatroom is a private chatroom
    // in that case we do not have to remove the user, since if we remove him
    // he will not be able to come back once he wants to rejoin the chatroom
    // in the case of private rooms we just set his status to 'E' value
    $chat_type= $chatroomHa['tipo_chat'];
    if ($chat_type==INVITATION_CHAT){
      $user_exits = $chatroomObj->set_user_statusFN($id_user,$id_user,
      $id_chatroom,ACTION_EXIT);
      if (AMA_DataHandler::isError($user_exits))
      {
        exitWith_JSON_Error(translateFN("Errore nell'uscita dell'utente dalla chatroom"));
      }

    }
    else{
      // removes user form database
      $user_exits = $chatroomObj->quit_chatroomFN($id_user,$id_user,$id_chatroom);
      if (AMA_DataHandler::isError($user_exits))
      {
        exitWith_JSON_Error(translateFN("Errore nell'uscita dell'utente dalla chatroom"));
      }
    }


    //		 include_once("include/MessageHandler.inc.php"); // messages functions
    // initialize a new MessageHandler object

    //$mh = new MessageHandler(MultiPort::getDSN($sess_selected_tester));
    $mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
    if (AMA_DataHandler::isError($mh))
    {
      exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto MessageHandler"));
    }
    // send a message to announce the entrance of the user
    $message_ha['tipo']     = ADA_MSG_CHAT;
    $message_ha['data_ora'] = "now";
    $message_ha['mittente'] = $user_uname;//"admin";
    $message_ha['id_group'] = $id_chatroom;
    $message_ha['testo']    = addslashes(sprintf(translateFN("L'utente %s e' uscito dalla stanza!"), $user_uname));
    // delegate sending to the message handler
    $result = $mh->send_message($message_ha);
    if (AMA_DataHandler::isError($result))
    {
      exitWith_JSON_Error(translateFN("Errore nel'invio del messaggio"));
    }
    // message to display while logging out
    $display_message1 = translateFN("Grazie per aver effettuato correttamente il logout.");
    $display_message2 = translateFN("Arrivederci da ADA Chat.");
    break;
    case EXIT_REASON_BANNED:
      // message to display while logging out
      $display_message1 = translateFN("Non puoi partecipare a questa chatroom, accesso negato.");
      $display_message2 = translateFN("Arrivederci da ADA Chat.");
      break;
    case EXIT_REASON_KICKED:
      // message to display while logging out
      $display_message1 = translateFN("Sei stato escluso momentaneamente dalla chatroom.");
      $display_message2 = translateFN("Arrivederci da ADA Chat.");
      break;
    case EXIT_REASON_NOT_EXIST:
      $display_message1 = translateFN("Si e' verificato un errore, non esiste una chatroom con l'ID specificato.<br><br>Impossibile proseguire");
      $display_message2 = translateFN("Arrivederci da ADA Chat.");
      break;
    case EXIT_REASON_NOT_STARTED:
      // initialize a new ChatDataHandler object
      $chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
      //get the type of the chatroom
      // we have to distinguish the case that the chatroom is a private chatroom
      // in that case we do not have to remove the user, since if we remove him
      // he will not be able to come back once he wants to rejoin the chatroom
      // in the case of private rooms we just set his status to 'E' value
      $chatroomHa= $chatroomObj->get_info_chatroomFN($id_chatroom);
      $chat_type= $chatroomHa['tipo_chat'];
      if ($chat_type==INVITATION_CHAT){
        $user_exits = $chatroomObj->set_user_statusFN($id_user,$id_user,
        $id_chatroom,ACTION_EXIT);
      }
      else{
        // removes user form database
        $user_exits = $chatroomObj->quit_chatroomFN($id_user,$id_user,$id_chatroom);
      }
      // message to display while logging out
      $display_message1 = translateFN("La chatroom cui stai provando ad accedere non e' stata ancora avviata! Verifica l'orario di apertura e riprova piu' tardi.");
      $display_message2 = translateFN("Arrivederci da ADA Chat.");
      break;
      case EXIT_REASON_EXPIRED:
        // initialize a new ChatDataHandler object
        $chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
        //get the type of the chatroom
        // we have to distinguish the case that the chatroom is a private chatroom
        // in that case we do not have to remove the user, since if we remove him
        // he will not be able to come back once he wants to rejoin the chatroom
        // in the case of private rooms we just set his status to 'E' value
        $chatroomHa= $chatroomObj->get_info_chatroomFN($id_chatroom);
        $chat_type= $chatroomHa['tipo_chat'];
        if ($chat_type==INVITATION_CHAT){
          $user_exits = $chatroomObj->set_user_statusFN($id_user,$id_user,
          $id_chatroom,ACTION_EXIT);
        }
        else{
          // removes user form database
          $user_exits = $chatroomObj->quit_chatroomFN($id_user,$id_user,$id_chatroom);
        }
        // message to display while logging out
        $display_message1 = translateFN("La chatroom cui stai provando ad accedere e' stata terminata!");
        $display_message2 = translateFN("Arrivederci da ADA Chat.");
        break;
        case EXIT_REASON_WRONG_ROOM:
          // message to display while logging out
          $display_message1 = translateFN("La chatroom cui stai provando ad accedere non appartiene alla tua classe oppure non sei invitato!");
          $display_message2 = translateFN("Arrivederci da ADA Chat.");
          break;
        case EXIT_REASON_FULL_ROOM:
          // initialize a new ChatDataHandler object
          $chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
          //get the type of the chatroom
          $chatroomHa= $chatroomObj->get_info_chatroomFN($id_chatroom);
          $chat_type= $chatroomHa['tipo_chat'];
          if ($chat_type==INVITATION_CHAT){
            $user_exits = $chatroomObj->set_user_statusFN($id_user,$id_user,
            $id_chatroom,ACTION_EXIT);
          }
          else{
            // removes user form database
            $user_exits = $chatroomObj->quit_chatroomFN($id_user,$id_user,$id_chatroom);
          }
          // message to display while logging out
          $display_message1 = translateFN("La chatroom cui stai provando ad accedere ha raggiunto il massimo numero di utenti che pu� ospitare! Riprova pi� tardi!");
          $display_message2 = translateFN("Arrivederci da ADA Chat.");
          break;

        default:
} // switch
/*
 * tracciamento del tempo di esecuzione dello script
 */
//$end_time = time() + microtime();
//$total_time = $end_time - $start_time;

/*
 * invio della risposta JSON al simulatore
 */
$error  = 0;
//$error += ($ERROR_USER)     ?  1 : 0;
//$error += ($ERROR_CHATROOM) ?  2 : 0;

$response = '{"error":'.$error.',"data":[{"time":"","sender":"admin","text":"Sei uscito dalla chat"}]}';

print $response;
?>