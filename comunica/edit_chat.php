<?php
// +----------------------------------------------------------------------+
// | ADA version 2.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2012 Lynx                                         |
// +----------------------------------------------------------------------+
// |                                                                      |
// |                     C R E A T E    C H A T   	                      |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Stamatios Filippis <st4m0s@gmail.com>
// |                             |
// +----------------------------------------------------------------------+
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
//    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout')

);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
//$self = whoami();
$self = 'list_chatrooms'; // x template

require_once 'include/comunica_functions.inc.php';
require_once 'include/ChatRoom.inc.php';
require_once 'include/ChatDataHandler.inc.php';

require_once ROOT_DIR . '/include/Forms/chatManagementForm.inc.php';

//print_r($GLOBALS);
$common_dh = $GLOBALS['common_dh'];
$dh = $GLOBALS['dh'];


// display message that explains the functionality of the current script
$help = translateFN("Da qui l'utente puo' creare una nuova chatroom inserendo i valori negli appositi campi.
	 <br><br>Attenzione!<br>Per il corretto funzionamento della chat e' importante inserire i valori corretti.");

$star= translateFN("I campi contrassegnati con * sono obbligatori, non possono essere lasciati vuoti!");
$status = translateFN("Modifica di una chatroom");
// different chat type options are available for admins and for tutors
if($id_profile == AMA_TYPE_SWITCHER){
  $options_of_chat_types = array(
        // 'Privata' => 'Privata',
	'Classe' => 'Classe',
	'Pubblica'=>'Pubblica');
}
if($id_profile == AMA_TYPE_TUTOR){
  $options_of_chat_types = array(
//        'Privata' => 'Privata',
	'Classe' => 'Classe',
	'Pubblica'=>'Pubblica');
//  $options_of_chat_types = array('Privata' => 'Privata');
}
//***********************************

 // initialize a new ChatDataHandler object
 $chatroomObj = new ChatRoom($id_room);
// chek to see if the chatromm is started, in that case we disable some fields
 $chatroom_started = $chatroomObj->is_chatroom_startedFN($id_room);
 $id_owner = $chatroomObj->id_chat_owner;

if($chatroom_started){
    $readonly = 'readonly';
 }
 else{
    $readonly = 0;
 }

 // check user type
 // owner can edit the chatroom
 if ($id_owner == $sess_id_user){
        $msg = translateFN("Utente abilitato per questa operazione.");
 }
 // admins can edit the chatroom
 elseif($id_profile == AMA_TYPE_SWITCHER){
        $msg = translateFN("Utente abilitato per questa operazione.");
 }
 // a moderator can edit the chatroom if chatroom is running
 elseif(($chatroom_started)){
    $is_moderator = $chatroomObj->is_user_moderatorFN($sess_id_user, $id_room);
    if($is_moderator){
        $msg = translateFN("Utente abilitato per questa operazione.");
    }
 }
 else{
        $msg = translateFN("Utente non abilitato per questa operazione. Impossibile proseguire");
        $location = $navigationHistoryObj->lastModule();
        header("Location: $location?err_msg=$msg&msg=$msg");
 }

 // display message that explains the functionality of the current script
 $help = translateFN("Da qui l'utente puo' modificare i dati di chatroom esistente inserendo i valori negli appositi campi.
 <br><br>Attenzione!<br>Per il corretto funzionamento della chat e' importante inserire i valori corretti.");
 // title of the script
 $status = translateFN("Modifica di una chatroom");
     // indicates which fields are compulsory
 $star= translateFN("I campi contrassegnati con * sono obbligatori, non possono essere lasciati vuoti!");
     // including the banner
 $banner = include ("$root_dir/include/banner.inc.php");


// Has the form been posted?
if ($_SERVER['REQUEST_METHOD'] == "POST"){
    $form = new ChatManagementForm();
    $form->fillWithPostData();
    if ($form->isValid()) {
            switch($new_chat_type){
                case 'Privata':
                        $chatroom_ha['chat_type']= INVITATION_CHAT;
                        break;
                case 'Classe':
                       $chatroom_ha['chat_type']= CLASS_CHAT;
                        break;
                case 'Pubblica':
                        $chatroom_ha['chat_type']= PUBLIC_CHAT;
                        break;
                case '-- select --':
                		$chatroom_old_ha = $chatroomObj->get_info_chatroomFN($id_room);
                        $chatroom_ha['chat_type']= $chatroom_old_ha['tipo_chat'];
                        break;
                default:
            } // switch
            /*
             *
             * transfrom username's into user's_id
             *
             */
            $id_owner = $common_dh->find_user_from_username($_POST['chat_owner']);
            if (AMA_DataHandler::isError($id_owner) OR $id_owner == '')
                $id_owner = $_POST['id_owner']; // old owner
               // return new AMA_Error(AMA_ERR_READ_MSG);
                // getting only user_id

            // create a unix data date format
            $start_data_array = array ($_POST['start_day'],$_POST['start_time']);
            $start_data= sumDateTimeFN ($start_data_array);
            // create a unix data date format
            $end_data_array = array ($_POST['end_day'],$_POST['end_time']);
            $end_data= sumDateTimeFN ($end_data_array);

            $chatroom_ha['id_chat_owner']= $id_owner;
            $chatroom_ha['chat_title'] = $chat_title;
            $chatroom_ha['chat_topic'] = $chat_topic;
            $chatroom_ha['welcome_msg'] = $welcome_msg;
            $chatroom_ha['max_users']= $max_users;
            $chatroom_ha['start_time']= $start_data;
            $chatroom_ha['end_time']= $end_data;
            $chatroom_ha['id_course_instance']= $id_course_instance;

            // update chatroom_ha to the database
            $chatroom = $chatroomObj->set_chatroomFN($id_room,$chatroom_ha);

            if($chatroom){
                 $err_msg = translateFN("<strong>La chatroom e' stata aggiornata con successo!</strong><br/>");
                 $err_msg.= translateFN("Torna all'elenco delle tue");
                 $err_msg.=" <a href=list_chatrooms.php>".translateFN("chatroom")."</a>";
            } else {
            	if (is_object($chatroom)) {
	                $errorObj = $chatroom->message;
	                if ($errorObj == "errore: record già esistente"){
	                          $err_msg = "<strong>".translateFN("La chatroom &egrave; stata gi&agrave; aggiornata con questi dati.")."</strong>";
	                }
            	}
            }
    }
} else {
     //get the array with all the current info of the chatoorm to be modified
     $chatroom_old_ha = $chatroomObj->get_info_chatroomFN($id_room);
     if (!is_array($chatroom_old_ha)) {
            $msg = translateFN("<b>Non esiste nessuna chatroom con il chatroom ID specificato! Impossibile proseguire</b>");
            header("Location: $error?err_msg=$msg");
     }
     // get the owner of the room
    $chat_room_HA['id_room'] = $id_room;
    $id_owner = $chatroom_old_ha['id_proprietario_chat'];
    $res_ar = $common_dh->get_user_info($id_owner);
    if (AMA_DataHandler::isError($res_ar))
           return new AMA_Error(AMA_ERR_READ_MSG);

    // getting username that is the name of the owner from the array
     //$owner_name = $res_ar['nome']. ' ' . $res_ar['cognome'];
    $owner_name = $res_ar['username'];
    $chat_room_HA['chat_owner'] = $owner_name;

    // get and visualize the actual chatroom type
    switch($chatroom_old_ha['tipo_chat']){
            case PUBLIC_CHAT:
            $old_chat_type = translateFN("pubblica");
            break;
            case CLASS_CHAT:
            $old_chat_type = translateFN("classe");
            break;
            case INVITATION_CHAT:
            $old_chat_type = translateFN("privata");
            break;
            default:
    }// switch
    $chat_room_HA['actual_chat_type'] = $old_chat_type;

     //get time and date and transform it to sting format
     //ts2dFN()
    $old_start_time = AMA_DataHandler::ts_to_date($chatroom_old_ha['tempo_avvio'], "%H:%M:%S");
     $old_start_day = AMA_DataHandler::ts_to_date($chatroom_old_ha['tempo_avvio']);
     $old_end_time = AMA_DataHandler::ts_to_date($chatroom_old_ha['tempo_fine'], "%H:%M:%S");
     $old_end_day = AMA_DataHandler::ts_to_date($chatroom_old_ha['tempo_fine']);
    // different chat type options are available for admins and for tutors
    // admin case
    $chat_room_HA['start_day'] = $old_start_day;
    $chat_room_HA['start_time'] = $old_start_time;
    $chat_room_HA['end_day'] = $old_end_day;
    $chat_room_HA['end_time'] = $old_end_time;

    if($id_profile == AMA_TYPE_SWITCHER){
        $options_of_chat_types = array(
            '-- select --'=>'-- select --',
            'Privata' => 'Privata',
            'Classe' => 'Classe',
            'Pubblica'=>'Pubblica'
        );
     }
 // tutor case
     if($id_profile == AMA_TYPE_TUTOR){
        $options_of_chat_types = array(
            '-- select --'=>'-- select --',
            'Classe' => 'Classe'
        );
     }
    $chat_room_HA['new_chat_type'] = $options_of_chat_types;
    $chat_room_HA['chat_title'] = $chatroom_old_ha['titolo_chat'];
    $chat_room_HA['chat_topic'] = $chatroom_old_ha['argomento_chat'];
    $chat_room_HA['welcome_msg'] = $chatroom_old_ha['msg_benvenuto'];
    $chat_room_HA['max_users'] = $chatroom_old_ha['max_utenti'];
    $chat_room_HA['id_course_instance'] = $chatroom_old_ha['id_istanza_corso'];
    $chat_title = $chat_room_HA['chat_title'];
    $form = new ChatManagementForm();
    //$form->fillWithPostData();
    $form->fillWithArrayData($chat_room_HA);
}

$course_title = $chat_title;
// array with data to be sended to the browser
$data =  array( 'banner'=> $banner,
                'status'=> $status,
                'user_name'=> $user_name,
                'user_type'=> $user_type,
                'help' =>$help,
                'star'=>$star,
                'course_title'=>$course_title,
                'data'=>$form->getHtml(),
                'error'=> isset($err_msg) ? $err_msg : ''
               );


ARE::render($layout_dataAr, $data);

?>
