<?php
/**
 * Add chat - this module provides add chat functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!
$self = 'list_chatrooms'; // x template
require_once 'include/comunica_functions.inc.php';
require_once ROOT_DIR . '/include/Forms/ChatRemovalForm.inc.php';
require_once 'include/ChatRoom.inc.php';
require_once 'include/ChatDataHandler.inc.php';

/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['delete'] == 0) {
        $list_chatrooms = HTTP_ROOT_DIR . '/comunica/list_chatrooms.php';
//        $msg = translateFN("<b>Non esiste nessuna chatroom con il chatroom ID specificato! Impossibile proseguire</b>");
        header("Location: $list_chatrooms");
        exit();
    }
    $chatId = DataValidator::is_uinteger($_POST['id_room']);
    if($chatId !== false) {
         $chatRoomHa = ChatRoom::get_info_chatroomFN($chatId);
         if (!AMA_DataHandler::isError($chatRoomHa)) {
             // check to see if the chatromm is started, in that case we disable some fields
             // $chatroom_started = $chatroomObj->is_chatroom_startedFN($chatId);
            $classId = $chatRoomHa['id_istanza_corso'];
            $chatTitle = $chatRoomHa['titolo_chat'];
            $chat_deleted = ChatRoom::remove_chatroomFN($chatId);
            if ($chat_deleted) {
                 $data = new CText(translateFN('Chat cancellata'));
            } else {
                 $data = new CText(translateFN('Errore nella cancellazione della Chat'));
            }
         } else {
            $data = new CText(translateFN('Chatroom non trovata'));
         }
    } else {
        $data = new CText(translateFN('Id chat non valido'));
    }
} else {
    $chatId = DataValidator::is_uinteger($_GET['id_room']);
    if($chatId === false) {
        $data = new CText(translateFN('Id chat non valido') . '(1)');
    } else {
//         $chatroomObj = new ChatRoom($chatId);
         $chatRoomHa = ChatRoom::get_info_chatroomFN($chatId);
         if (!AMA_DataHandler::isError($chatRoomHa)) {
            $classId = $chatRoomHa['id_istanza_corso'];
            $chatTitle = $chatRoomHa['titolo_chat'];
            $formData = array(
              'id_room' => $chatId
            );
            $data = new chatRemovalForm();
            $data->fillWithArrayData($formData);
        } else {
            $data = new CText(translateFN('Chatroom non trovata') . '(1)');
        }
    }
}

$label = translateFN('Cancellazione chatroom') .' ' .$chatTitle .', id: ' .$chatId;
$label .= ' - ' . translateFN('Classe') . ': ' . $classId;
$help = translateFN('Da qui il provider admin può cancellare una chat esistente');

$menu_01 = "<a href=" . $http_root_dir . "/comunica/list_chatrooms.php>" . translateFN("lista di chatrooms") . "</a>";
$menu_02 = "<a href=" . $http_root_dir . "/comunica/create_chat.php>" . translateFN("crea chatroom") . "</a>";

/*
 *
$content_dataAr = array(
    'chat_name' => $chat_name,
    'chat_type' => $chat_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module
//    'messages' => $chat_messages->getHtml()
);
 * 
 */

$content_dataAr =  array( 'banner'=> $banner,
                'status'=> $status,
                'user_name'=> $user_name,
                'user_type'=> $user_type,
                'help' =>$help,
//                'label' => $label,
                'course_title'=>$label,
                'data'=>$data->getHtml(),
                'error'=> $err_msg,
                'menu_01'=>$menu_01,
                'menu_02'=>$menu_02
                );

ARE::render($layout_dataAr, $content_dataAr);