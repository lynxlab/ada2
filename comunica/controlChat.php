<?php
/**
 * control chat
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

/**
 * vito, 22 september 2008
 *
 * The original script has been modified in order to work in an AJAX-like
 * environment.
 * Now it performs only the actions needed to obtain:
 * 1. user status in the current chatroom
 * 2. the actions the user is allowed to perform in the current chatroom
 * 3. the list of the users in the current chatroom.
 *
 * Performing the user control action, which was previously a responsibility
 * for this script, is now demanded to controlChatAction.php.
 *
 * Data is returned back to the caller via JSON strings, not XML.
 */

/*
 * Check that this script was called with the right arguments.
 * If not, stop script execution and report an error to the caller.
 */
if ( !isset($_POST['chatroom'])/*!isset($_GET['chatroom'])*/ )
{
    exitWith_JSON_Error(translateFN("Errore: parametri passati allo script PHP non corretti"));
}

$id_chatroom = $_POST['chatroom'];//$_GET['chatroom'];

/*
 * Get an instance of the UserDataHandler class.
 */
//$udh = UserDataHandler::instance(MultiPort::getDSN($sess_selected_tester));
//if (AMA_DataHandler::isError($udh))
//{
//    exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto UserDataHandler"));
//}

/*
 * Get an instance of the MessageHandler class.
 */
//$mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
$mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);

if (AMA_DataHandler::isError($mh))
{
    exitWith_JSON_Error(translateFN("Errore nella creazione dell'oggetto MessageHandler"));
}

/*
 * Get chatroom data
 */
$chatroomObj = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
if (AMA_DataHandler::isError($chatroomObj))
{
    exitWith_JSON_Error(translateFN("Errore nella creazione della chatroom"));
}

$chatroom_ha = $chatroomObj->get_info_chatroomFN($id_chatroom);
if (AMA_DataHandler::isError($chatroom_ha))
{
    exitWith_JSON_Error(translateFN("Errore nell'ottenimento dei dati sulla chatroom"));
}

$actual_time = time();
// time that the chat will be closed
$expiration_time = $chatroom_ha['tempo_fine'];
$chat_type       = $chatroom_ha['tipo_chat'];

// gets an array containing all the ids of the users present in the chat
/* 21 settembre 2008
 * modifica di vito in ChatDataHandler->list_users_chatroom(richiamato da $chatroomObj->list_users_chatroomFN)
 * ora restituisce id utente e username
 */
$userslist_ar = $chatroomObj->list_users_chatroomFN($id_chatroom);

$invited_userslist_ar = $chatroomObj->list_users_invited_to_chatroomFN($id_chatroom);

if (AMA_DataHandler::isError($userslist_ar))
{
    exitWith_JSON_Error(translateFN("Errore nell'ottenimento dei dati relativi agli utenti presenti nella chatroom"));
}

if (AMA_DataHandler::isError($invited_userslist_ar))
{
    exitWith_JSON_Error(translateFN("Errore nell'ottenimento dei dati relativi agli utenti invitati alla chatroom"));
}

/*
 *
 */
if ($expiration_time !=0)
{
	 // calculate the time that remains before the chatroom expires
	 if (($expiration_time - $actual_time)<= TIME_BEFORE_EXPIRATION)
	 {
	    // count the users present into the chatroom
	    $how_many_users = count($userslist_ar);
	    if(($chat_type == CLASS_CHAT) and ($how_many_users >= USERS_REQUESTED_TO_EXTEND))
	    {
    	    // get and set chatroom details
		    $chatroom_new['end_time']= $expiration_time + TIME_TO_EXTEND;
		    $title = $chatroom_ha['titolo_chat'];
		    $chatroom_new['chat_title']= addslashes($title);
    	    $topic = $chatroom_ha['argomento_chat'];
		    $chatroom_new['chat_topic']= addslashes($topic);
		    $welcome_msg = $chatroom_ha['msg_benvenuto'];
		    $chatroom_new['welcome_msg']= addslashes($welcome_msg);

		    //extend the time of this chat session
		    $result = $chatroomObj->set_chatroomFN($id_chatroom,$chatroom_new);
            if (AMA_DataHandler::isError($result))
            {
                exitWith_JSON_Error(translateFN("Errore nel tentativo di estendere la durata della chatroom"));
            }
        }
     }
}

/*
 *
 */
$still_running = $chatroomObj->is_chatroom_not_expiredFN($id_chatroom);
if (AMA_DataHandler::isError($still_running))
{
    exitWith_JSON_Error(translateFN("Errore nella verifica della validit&agrave; della chatroom"));
}

// verify if the closing chatroom time has arrived
if(!$still_running)
{
    // close_chat template will loaded
	$self = close_chat;
	// motivate the exit of the user
	$exit_reason = EXIT_REASON_EXPIRED;
	// open close_chat.php
//	$onload_func =  "top.location.href='close_chat.php?exit_reason=$exit_reason&id_chatroom=$id_chatroom&id_user=$sess_id_user'";
	$data = array ('chat_text'=>$chat_text);
}

/*
 *
 */
$user_status= $chatroomObj->get_user_statusFN($sess_id_user,$id_chatroom);
if (AMA_DataHandler::isError($user_status))
{
    exitWith_JSON_Error(translateFN("Errore nell'ottenimento dello stato dell'utente all'interno della chatroom"));
}

// ******************************************************
// building the available options for the user
// ******************************************************

// we convert in text the status of the user in order to print it on the screen
switch($user_status)
{
    case STATUS_OPERATOR:
        $view_user_status = translateFN("Moderatore");
        break;
    case STATUS_ACTIVE:
        $view_user_status = translateFN("Attivo");
        break;
    case STATUS_MUTE:
        $view_user_status = translateFN("Senza Voce");
        break;
    case STATUS_BAN:
        $view_user_status = translateFN("Accesso Negato");
        break;
    default:
}//end switch

/*
 *
 */
//if (($user_status == STATUS_ACTIVE)or($user_status == STATUS_OPERATOR))
//{
//    /*
//     * Common options for simple user and operators
//     */
//    $json_options_data  = '[';
//    $json_options_data .= '{"value":'.ADA_CHAT_MOOD_TYPE_ASK_FOR_ATTENTION.',"text":"'.translateFN("Chiedi Ascolto").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_MOOD_TYPE_APPLAUSE.',"text":"'.translateFN("Applaudi").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_MOOD_TYPE_DISAGREE.',"text":"'.translateFN("Dissenti").'"}';
//}
//if ($user_status == STATUS_OPERATOR)
//{
//    /*
//     * In case of operator more functions are available
//     */
//    $json_options_data .= ',{"value":'.ADA_CHAT_OPERATOR_ACTION_SET_OPERATOR.',"text":"'.translateFN("Rendi Moderatore").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_UNSET_OPERATOR.',"text":"'.translateFN("Togli Moderatore").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_MUTE_USER.',"text":"'.translateFN("Togli Voce").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_UNMUTE_USER.',"text":"'.translateFN("Dai Voce").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_BAN_USER.',"text":"'.translateFN("Nega Accesso").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_UNBAN_USER.',"text":"'.translateFN("Dai Accesso").'"},';
//    $json_options_data .= '{"value":'.ADA_CHAT_OPERATOR_ACTION_KICK_USER.',"text":"'.translateFN("Espelli").'"}';
//    // edit currenr chatroom properties
//    $edit_chatroom = "<a href=../comunica/edit_chat.php?$session_id_par"."&id_chatroom=$id_chatroom target='_blank' border='0'>Modifica Chatroom</a>";
//}
//$json_options_data .= ']';

	 // $userslist_ar has been retrieved at the start of the script


	 // proceed only if list it is not empty, get the list of the users into the chatroom
if (is_array($userslist_ar))
{
    /*
     * Create the json for the users list
     */
    $json_users_list = '[';

    while (count($userslist_ar) > 1 )
    {
        $user_data        = array_shift($userslist_ar);
        $json_users_list .= '{"id":"'.$user_data['id_utente'].'","username":"'.$user_data['username'].'"},';
    }
    if (count($userslist_ar) == 1)
    {
        $user_data        = array_shift($userslist_ar);
        $json_users_list .= '{"id":"'.$user_data['id_utente'].'","username":"'.$user_data['username'].'"}';
    }
    $json_users_list .= ']';

}// end of users list
else
{
    // Errors on $userslist_ar should have been catched on line 138.
    //  $errObj = new ADA_error(translateFN("Errore durante la lettura del DataBase"),translateFN("Impossibile proseguire."));
}


//if (is_array($invited_userslist_ar))
//{
//    /*
//     * Create the json for the users list
//     */
//    $json_invited_users_list = '[';
//
//    while (count($invited_userslist_ar) > 1 )
//    {
//        $user_data        = array_shift($invited_userslist_ar);
//        $json_invited_users_list .= '{"id":"'.$user_data['id_utente'].'","username":"'.$user_data['username'].'"},';
//    }
//    if (count($invited_userslist_ar) == 1)
//    {
//        $user_data        = array_shift($invited_userslist_ar);
//        $json_invited_users_list .= '{"id":"'.$user_data['id_utente'].'","username":"'.$user_data['username'].'"}';
//    }
//    $json_invited_users_list .= ']';
//
//}// end of users list
//else
//{
//    $json_invited_users_list = '[]';
//    // Errors on $userslist_ar should have been catched on line 138.
//    //  $errObj = new ADA_error(translateFN("Errore durante la lettura del DataBase"),translateFN("Impossibile proseguire."));
//}


/*
 * If the user is an operator we get also the banned users list
 */
//if ($user_status == STATUS_OPERATOR)
//{
//    // get's an array containing all the ids of the banned users into that chatroom
//    // vito, 21 settembre 2008, modifica a ChatDataHandler->list_banned_users: ottiene anche lo username , oltre allo user id
//    $bannedusers_ar = $chatroomObj->list_banned_users_chatroomFN($id_chatroom);
//    if (AMA_DataHandler::isError($bannedusers_ar))
//    {
//        exitWith_JSON_Error(translateFN("Errore nell'ottenimento della lista degli utenti bannati"));
//    }
//
//	// we will tranform ids in usernames only in the case that the array it is not empty
//	$json_banned_users_list = '[';
//    if (is_array($bannedusers_ar))
//	{
//	    $users_names_ha = array();
//
//	    /*
//     	 * Create the json for the banned users list
//         */
//        while (count($bannedusers_ar) > 1 )
//        {
//            $user_data               = array_shift($bannedusers_ar);
//            $json_banned_users_list .= '{"username":"'.$user_data['username'].'"},';
//        }
//        if (count($bannedusers_ar) == 1)
//        {
//            $user_data               = array_shift($bannedusers_ar);
//            $json_banned_users_list .= '{"username":"'.$user_data['username'].'"}';
//        }
//	}//end of if($bannedusers_ar)
//    $json_banned_users_list .= ']';
//}// end of banned list

// write the time of the event into the utente_chatroom table
$last_event= $chatroomObj->set_last_event_timeFN($sess_id_user,$id_chatroom);
if (isset($bannedusers_ar) && AMA_DataHandler::isError($bannedusers_ar))
{
    exitWith_JSON_Error(translateFN("Errore nell'aggiornamento del tempo relativo all'utlimo evento"));
}
/*
 * Optionally, track this script execution time.
 */
if (defined('ADA_AJAX_CHAT_SCRIPT_TIMING'))
{
    $end_time = time() + microtime();
    $total_time = $end_time - $start_time;
}

/*
 * Sending back data to the caller.
 */
$error  = 0;

/*
 * Get UI labels in the user's language.
 */
$user_status_label  = translateFN("Stato Utente");
$options_list_label = translateFN("Opzioni Utente");
$users_list_label   = translateFN("Utenti nella Chatroom");

/*
 * Li passiamo vuoit perche' in ADA non dovrebbe servire questo tipo di
 * informazioni
 */
$json_banned_users_list  = '[]';
$json_invited_users_list = '[]';
$json_options_data       = '[]';

$response = '{"error":'.$error.',"data":{"user_status_label":"'.$user_status_label.'","user_status":"'.$view_user_status.'","options_list_label":"'.$options_list_label.'","options_list":'.$json_options_data.',"users_list_label":"'.$users_list_label.'","users_list":'.$json_users_list.',"invited_users_list":'.$json_invited_users_list.'}}';

print $response;
?>