<?php
/**
 * list_chatrooms.php
 *
 * @package
 * @author		Stamatios Filippis <st4m0s@gmail.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2001-2011, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout')

);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

require_once 'include/comunica_functions.inc.php';
require_once 'include/ChatRoom.inc.php';
require_once 'include/ChatDataHandler.inc.php';

$help = translateFN("Da qui l'utente puo' vedere la lista di tutte le chatrooms a cui puo' accedere.");
$status = ''; //translateFN('lista delle chatrooms');
$modulo = translateFN('lista delle chatrooms');

// including the banner

$chat_label = translateFN('entra');
$edit_label = translateFN('modifica');
$delete_label = translateFN('cancella');
$add_users_label = translateFN('aggiungi utenti');



switch ($id_profile) {
    // ADMINISTRATOR
    case AMA_TYPE_ADMIN:
    case AMA_TYPE_SWITCHER:
        // gets an array with all the chatrooms
        $all_chatrooms_ar = Chatroom::get_all_chatroomsFN();


        //initialize an array
        $list_chatrooms = array();
        // sort the chatrooms in reverse order, so we can visualize first the most recent chatrooms
        rsort($all_chatrooms_ar);
        $tbody_data = array();
        foreach ($all_chatrooms_ar as $id_chatroom) {
            //initialize a chatroom Object
            $chatroomObj = new ChatRoom($id_chatroom);
            //get the array with all the current info of the chatoorm
            $chatroom_ha = $chatroomObj->get_info_chatroomFN($id_chatroom);
            $id_course_instance = $chatroom_ha['id_istanza_corso'];
            $id_course = $dh->get_course_id_for_course_instance($chatroom_ha['id_istanza_corso']);
            $courseObj = read_course($id_course);
            if ((is_object($courseObj)) && (!AMA_dataHandler::isError($userObj)))  {
                    $course_title = $courseObj->titolo; //title
                    $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
            }

            // get the title of the chatroom
            $chat_title = $chatroom_ha['titolo_chat'];
            // get the type of the chatroom
            $c_type = $chatroom_ha['tipo_chat'];
            switch ($c_type) {
                case PUBLIC_CHAT:
                    $chat_type = translateFN("pubblica");
                    break;
                case CLASS_CHAT:
                    $chat_type = translateFN("classe");
                    break;
                case INVITATION_CHAT:
                    $chat_type = translateFN("privata");
                    break;
                default:
            } // switch $c_type
            // verifiy the status of the chatroom
            $started = $chatroomObj->is_chatroom_startedFN($id_chatroom);
            $running = $chatroomObj->is_chatroom_activeFN($id_chatroom);
            //$not_expired = $chatroomObj->is_chatroom_not_expiredFN($id_chatroom);
            if ($running) {
                $chatroom_status = translateFN('in corso');
                switch ($c_type) {
                    case PUBLIC_CHAT:
                        $enter = "<a href=\"chat.php?id_room=$id_chatroom&id_course=$id_course\" target=\"_blank\"><img src=\"img/_chat.png\" alt=\"$chat_label\" border=\"0\"></a>";
                        break;
                    case CLASS_CHAT:
                        $enter = translateFN("- - - ");
                        break;
                    case INVITATION_CHAT:
                        $present = $chatroomObj->get_user_statusFN($sess_id_user, $id_chatroom);
                        if (($present == STATUS_OPERATOR) or ($present == STATUS_ACTIVE) or
                                ($present == STATUS_MUTE) or ($present == STATUS_BAN)
                                or ($present == STATUS_INVITED) or ($present == STATUS_EXIT)) {
                            $enter = "<a href=\"chat.php?id_room=$id_chatroom\" target=\"_blank\"><img src=\"img/_chat.png\" alt=\"$chat_label\" border=\"0\"></a>";
                        } else {
                            $enter = translateFN("- - - ");
                        }
                        break;
                    default:
                } // switch $c_type
            } elseif (!$started) {
                $chatroom_status = translateFN('non avviata');
                $enter = translateFN("- - - ");
            } else {
                $chatroom_status = translateFN('terminata');
                $enter = translateFN("- - - ");
            }
            if ($c_type == INVITATION_CHAT) {
                $add_users = "<a href=\"add_users_chat.php?id_room=$id_chatroom\"><img src=\"img/add_user.png\" alt=\"$add_users_label\" border=\"0\"></a>";
            } else {
                $add_users = translateFN("- - -");
            }

            // create the entries for the table
            $tbody_data[] = array(
                $course_title,
                $id_course_instance,
                $chat_title,
                $chatroom_status,
                $chat_type,
                $enter,
                "<a href=\"edit_chat.php?id_room=$id_chatroom\"><img src=\"img/edit.png\" alt=\"$edit_label\" border=\"0\"></a>",
                "<a href=\"delete_chat.php?id_room=$id_chatroom\"><img src=\"img/delete.png\" alt=\"$delete_label\" border=\"0\"></a>"
              );
        }

        // initialize a new Table object that will visualize the list of the chatrooms
        $thead_data = array(
                translateFN('corso'),
                translateFN('classe'),
                translateFN('titolo'),
                translateFN('stato'),
                translateFN('tipo'),
                translateFN('entra'),
                translateFN('modifica'),
                translateFN('cancella')
//                translateFN('aggiungi utenti') => $add_users
         );
        $table_room = BaseHtmlLib::tableElement('class:sortable', $thead_data, $tbody_data);
        $list_chatrooms_table = $table_room->getHtml();


        //link for the creation of a chatroom
        $menu_02 = "<a href=" . HTTP_ROOT_DIR . "/comunica/create_chat.php>" . translateFN('crea chatroom') . "</a>";

        break;
    case AMA_TYPE_TUTOR: // TUTOR
        // get the pubblic chatroom
        $public_chatroom = ChatRoom::find_public_chatroomFN();
        // get the instances for which the user is the tutor of the class
        $course_instances_ar = $dh->course_tutor_instance_get($sess_id_user);
        // get only the ids of the courses instances
        foreach ($course_instances_ar as $value) {
            $course_instances_ids_ar[] = $value[0];
        }
        $class_chatrooms_ar = array();
        // get a bidimensional array with all the chatrooms for every course instance
        foreach ($course_instances_ids_ar as $id_course_instance) {
            $class_chatrooms = ChatRoom::get_all_class_chatroomsFN($id_course_instance);
            if (is_array($class_chatrooms)) {
                $class_chatrooms_ar[] = $class_chatrooms;
            }
        }
        $chatrooms_class_ids_ar = array();
        // get only the ids of the chatrooms
        foreach ($class_chatrooms_ar as $value) {
            foreach ($value as $id) {
                $chatrooms_class_ids_ar[] = $id;
            }
        }
        // merge class chatrooms with the public chatroom
        //vito 9gennaio2009
        if (!AMA_DataHandler::isError($public_chatroom)) {
            array_push($chatrooms_class_ids_ar, $public_chatroom);
        }

        // get all the private chatrooms of the user
        $private_chatrooms_ar = ChatRoom::get_all_private_chatroomsFN($sess_id_user);
        if (is_array($private_chatrooms_ar)) {
            $all_chatrooms_ar = array_merge($chatrooms_class_ids_ar, $private_chatrooms_ar);
        } else {
            $all_chatrooms_ar = $chatrooms_class_ids_ar;
        }
        // sort the chatrooms in reverse order, so we can visualize first the most recent chatrooms
        rsort($all_chatrooms_ar);
        //initialize the array of the chatrooms to be displayed on the screen
        $list_chatrooms = array();
        // start the construction of the table contaning all the chatrooms
        $tbody_data[] = array();
        foreach ($all_chatrooms_ar as $id_chatroom) {
            //initialize a chatroom Object
            if (!is_object($id_chatroom)) {
                $chatroomObj = new ChatRoom($id_chatroom, MultiPort::getDSN($sess_selected_tester));
                //get the array with all the current info of the chatoorm
                $chatroom_ha = $chatroomObj->get_info_chatroomFN($id_chatroom);
                $id_course_instance = $chatroom_ha['id_istanza_corso'];
                $id_course = $dh->get_course_id_for_course_instance($chatroom_ha['id_istanza_corso']);
                $courseObj = read_course($id_course);
                if ((is_object($courseObj)) && (!AMA_dataHandler::isError($userObj)))  {
                        $course_title = $courseObj->titolo; //title
                        $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
                }

                // get the owner of the room
                $chat_title = $chatroom_ha['titolo_chat'];
                // get the type of the chatroom
                $c_type = $chatroom_ha['tipo_chat'];
                switch ($c_type) {
                    case PUBLIC_CHAT:
                        $chat_type = translateFN("pubblica");
                        break;
                    case CLASS_CHAT:
                        $chat_type = translateFN("classe");
                        break;
                    case INVITATION_CHAT:
                        $chat_type = translateFN("privata");
                        break;
                    default:
                } // switch $c_type
                // verify the status of the chatroom
                $started = $chatroomObj->is_chatroom_startedFN($id_chatroom);
                $running = $chatroomObj->is_chatroom_activeFN($id_chatroom);
                //$not_expired = $chatroomObj->is_chatroom_not_expiredFN($id_chatroom);
                if ($running) {
                    $chatroom_status = translateFN('in corso');
                    $enter = "<a href=\"chat.php?id_room=$id_chatroom&id_course=$id_course\" target=\"_blank\"><img src=\"img/_chat.png\" alt=\"$chat_label\" border=\"0\"></a>";
                } elseif (!$started) {
                    $chatroom_status = translateFN('non avviata');
                    $enter = translateFN("- - -");
                } else {
                    $chatroom_status = translateFN('terminata');
                    // vito, 22 apr 2009
                    $enter= translateFN("- - -");
                    //$enter = "<a href=\"report_chat.php?id_room=$id_chatroom\" target=\"_self\">" . translateFN('Report') . "</a>";
                }
                $report = "<a href=\"report_chat.php?id_room=$id_chatroom\" target=\"_self\">" . translateFN('Report') . "</a>";
                //check if he is the owner of the chatroom in order to give access for edit and delete
                $id_owner = $chatroom_ha['id_proprietario_chat'];
                // get the title of the chatroom
                if ($id_owner == $sess_id_user) {
                    $edit = "<a href=\"edit_chat.php?id_room=$id_chatroom\"><img src=\"img/edit.png\" alt=\"$edit_label\" border=\"0\"></a>";
                    $delete = "<a href=\"delete_chat.php?id_room=$id_chatroom\"><img src=\"img/delete.png\" alt=\"$delete_label\" border=\"0\"></a>";
                    if ($c_type == INVITATION_CHAT) {
                        $add_users = "<a href=\"add_users_chat.php?id_room=$id_chatroom\"><img src=\"img/add_user.png\" alt=\"$add_users_label\" border=\"0\"></a>";
                    } else {
                        $add_users = translateFN("- - -");
                    }
                } else {
                    $edit = translateFN("- - -");
                    $delete = translateFN("- - -");
                    $add_users = translateFN("- - -");
                }
                // create the entries for the table
                $tbody_data[] = array(
                    $course_title,
                    $id_course_instance,
                    $chat_title,
                    $chatroom_status,
                    $chat_type,
                    $enter,
                    $edit,
                    $report
                  );

            }
        }
        // initialize a new Table object that will visualize the list of the chatrooms
        $thead_data = array(
                translateFN('corso'),
                translateFN('classe'),
                translateFN('titolo'),
                translateFN('stato'),
                translateFN('tipo'),
                translateFN('entra'),
                translateFN('modifica'),
                translateFN('report')
         );
        $table_room = BaseHtmlLib::tableElement('class:sortable', $thead_data, $tbody_data);
        $list_chatrooms_table = $table_room->getHtml();

        //link to create chatroom
        $menu_02 = "<a href=" . $http_root_dir . "/comunica/create_chat.php>" . translateFN("crea chatroom") . "</a>";

        break;

// AUTHOR
    case AMA_TYPE_AUTHOR:
        /*
         * vito, 22 apr 2009:
         * an author can only enter chatrooms he is invited to.
         */

        $available_chatrooms = ChatRoom::get_all_private_chatroomsFN($sess_id_user);
        if (AMA_DataHandler::isError($available_chatrooms)) {
            if ($available_chatrooms->code != AMA_ERR_NOT_FOUND) {
                // there aren't chatrooms available.
                $available_chatrooms = array();
            } else {
                // an error occurred
                // ottenere la pagina da cui l'autore proviene
                // costruire un messaggio da passare a $status
                // redirigere l'autore alla pagina
            }
        }

        $list_chatrooms = array();

        foreach ($available_chatrooms as $id_chatroom) {

            $chatroomObj = new ChatRoom($id_chatroom);

            if (!AMA_DataHandler::isError($chatroomObj)) {

                switch ($chatroomObj->chat_type) {
                    case PUBLIC_CHAT:
                        $chat_type = translateFN('pubblica');
                        break;

                    case INVITATION_CHAT:
                        $chat_type = translateFN('privata');
                        break;

                    case CLASS_CHAT:
                    default:
                }

                // verify the status of the chatroom
                $started = $chatroomObj->is_chatroom_startedFN($id_chatroom);
                $running = $chatroomObj->is_chatroom_activeFN($id_chatroom);

                if ($running) {
                    $chatroom_status = translateFN('in corso');
//      	$enter= "<a href=\"../comunica/adaChat.php?id_chatroom=$id_chatroom&id_course=$id_course\" target=_blank><img src=\"img/_chat.gif\" alt=\"$chat_label\" border=\"0\"></a>";
                    $enter = "<a href=\"chat.php?id_room=$id_chatroom\" target=\"_blank\"><img src=\"img/_chat.png\" alt=\"" . translateFN('Entra nella chat') . "\" border=\"0\"></a>";
                } elseif (!$started) {
                    $chatroom_status = translateFN('non avviata');
                    $enter = translateFN("- - -");
                } else {
                    $chatroom_status = translateFN('terminata');
                    // vito, 22 apr 2009
                    //$enter= translateFN("- - -");
                    $enter = "<a href=\"report_chat.php?id_room=$id_chatroom\" target=\"_self\">" . translateFN('Report') . "</a>";
                }
                // create the entries for the table
                $row = array(
                    translateFN('titolo') => translateFN($chatroomObj->chat_title),
                    translateFN('stato') => $chatroom_status,
                    translateFN('tipo') => $chat_type,
                    translateFN('entra') => $enter
                );
                array_push($list_chatrooms, $row);
            }
        }

        // initialize a new Table object that will visualize the list of the chatrooms
        $tObj = new Table();
        $tObj->initTable('1', 'center', '2', '2', '100%', '', '', '', '', '1', '', '');
        $caption = '<strong>' . translateFN('La lista delle tue chatroom') . '</strong>';
        $summary = translateFN('La lista delle tue chatroom');
        $tObj->setTable($list_chatrooms, $caption, $summary);
        $list_chatrooms_table = $tObj->getTable();

        break;

    case AMA_TYPE_STUDENT: // STUDENT
        // get the public chatroom
        $public_chatroom = ChatRoom::find_public_chatroomFN();

        // get the active classes to which the user is subscribed
        $field_ar = array('id_corso');
        $all_instances = $dh->course_instance_started_get_list($field_ar);
        // get only the ids of the classes
        foreach ($all_instances as $one_instance) {
            $id_course_instance = $one_instance[0];
            $sub_courses = $dh->get_subscription($_SESSION['sess_id_user'], $id_course_instance);
            //print_r($sub_courses);
            if ((is_array($sub_courses)) && ($sub_courses['tipo'] == ADA_STATUS_SUBSCRIBED)) {
                $class_instances_ids_ar[] = $id_course_instance;
            }
        }
        // get the ACTIVE chatroom, if exists, of each class

        $class_chatrooms_ar = array();
        if (is_array($class_instances_ids_ar)) {
            // get a bidimensional array with all the chatrooms for every course instance
            foreach ($class_instances_ids_ar as $id_course_instance) {
                $chatroom_class = ChatRoom::get_class_chatroomFN($id_course_instance);
                //vito 9gennaio2009
                //if(!is_object($chatroom_class)){
                if (!AMA_DataHandler::isError($chatroom_class)) {
                    $class_chatrooms_ar[] = $chatroom_class;
                }
            }
            // merge class chatrooms with the public chatroom
            //vito 9gennaio2009
            if (!AMA_DataHandler::isError($public_chatroom)) {
                array_push($class_chatrooms_ar, $public_chatroom);
            }
        }



        // get all the private chatrooms of the user
        $private_chatrooms_ar = ChatRoom::get_all_private_chatroomsFN($sess_id_user);
        if (is_array($private_chatrooms_ar)) {
            $all_chatrooms_ar = array_merge($class_chatrooms_ar, $private_chatrooms_ar);
        } else {
            $all_chatrooms_ar = $class_chatrooms_ar;
        }
        // sort the chatrooms in reverse order, so we can visualize first the most recent chatrooms
        rsort($all_chatrooms_ar);
        //initialize the array of the chatrooms to be displayed on the screen
        $list_chatrooms = array();
        // start the construction of the table contaning all the chatrooms
        foreach ($all_chatrooms_ar as $id_chatroom) {
            //initialize a chatroom Object
            $chatroomObj = new ChatRoom($id_chatroom);
            //get the array with all the current info of the chatoorm
            $chatroom_ha = $chatroomObj->get_info_chatroomFN($id_chatroom);
            // vito, 16 mar 2009
            $id_course = $dh->get_course_id_for_course_instance($chatroom_ha['id_istanza_corso']);

            // get the owner of the room
            $chat_title = $chatroom_ha['titolo_chat'];
            // get the type of the chatroom
            $c_type = $chatroom_ha['tipo_chat'];
            switch ($c_type) {
                case PUBLIC_CHAT:
                    $chat_type = translateFN('pubblica');
                    break;
                case CLASS_CHAT:
                    $chat_type = translateFN('classe');
                    break;
                case INVITATION_CHAT:
                    $chat_type = translateFN('privata');
                    break;
                default:
            } // switch $c_type
            // verify the status of the chatroom
            $started = $chatroomObj->is_chatroom_startedFN($id_chatroom);
            $running = $chatroomObj->is_chatroom_activeFN($id_chatroom);
            //$not_expired = $chatroomObj->is_chatroom_not_expiredFN($id_chatroom);
            if ($running) {
                $chatroom_status = translateFN('in corso');
                $enter = "<a href=\"chat.php?id_room=$id_chatroom&id_course=$id_course\" target=\"_blank\"><img src=\"img/_chat.png\" alt=\"$chat_label\" border=\"0\"></a>";
            } elseif (!$started) {
                $chatroom_status = translateFN('non avviata');
                $enter = translateFN("- - -");
            } else {
                $chatroom_status = translateFN('terminata');
                $enter = translateFN("- - -");
            }
            // create the entries for the table
            $row = array(
                translateFN('titolo') => translateFN($chat_title),
                translateFN('stato') => $chatroom_status,
                translateFN('tipo') => $chat_type,
                translateFN('entra') => $enter
            );
            array_push($list_chatrooms, $row);
        }
        // initialize a new Table object that will visualize the list of the chatrooms
        $tObj = new Table();
        $tObj->initTable('1', 'center', '2', '2', '100%', '', '', '', '', '1', '', '');
        $caption = "<strong>" . translateFN("La lista delle tue chatroom") . "</strong>";
        $summary = translateFN("La lista delle tue chatroom");
        $tObj->setTable($list_chatrooms, $caption, $summary);
        $list_chatrooms_table = $tObj->getTable();
}



$banner = include ROOT_DIR . '/include/banner.inc.php';
$chatrooms_link = '<a href="'.HTTP_ROOT_DIR . '/comunica/list_chatrooms.php">'. translateFN('Lista chatrooms')
                    . '</a>';
$content_dataAr = array(
  'banner' => $banner,
  'user_name' => $user_name,
  'user_type' => $user_type,
//  'messages'     => $messages->getHtml(),
  'status' => $status,
  'course_title' => $modulo,
  'help' => $help,
  'data' => $list_chatrooms_table,
  'chat_users' => $online_users,
  //'chatrooms'=>$chatrooms_link,
  'edit_profile'=> $userObj->getEditProfilePage(),
  'menu_01' => $menu_01,
  'menu_02' => $menu_02,
  'menu_03' => $menu_03
);

ARE::render($layout_dataAr, $content_dataAr);