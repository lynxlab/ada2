<?php
/**
 * ChatRoom.inc.php
 *
 * @package   default
 * @author    Stamatios Filippis <stamos@tiscali.it>
 * @author    Vito Modena <vito@lynxlab.com>
 * @copyright Copyright (c) 2001-2011, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
 */
require_once 'ChatDataHandler.inc.php';

class ChatRoom {
    /*******************************************************************************
     * @access public
     *
     * @param   $chatroom_ha - contains all data of a chatroom, in order to create one;
     *                        or contains all the data of a chatroom allready existent;
     *                        the parameter is an hash whose keys are:
     *
     *                        id_chatroom
     *                        id_course_instance
     *                        chat_type
     *                        id_chat_owner
     *                        chat_title
     *                        chat_topic
     *                        max_users
     *                        welcome_msg
     *                        start_time
     *                        end_time
     *
     * @return    an AMA_Error object if something goes wrong
     ****************************************************************************** */

    var $id_chatroom = 0;
    var $id_course_instance = 0;
    var $chat_type = "";
    var $id_chat_owner = 0;
    var $chat_title = "";
    var $chat_topic = "";
    var $max_users = 25;
    var $welcome_msg = "";
    var $start_time = 0;
    var $end_time = 0;
    var $error = 1;
    var $error_msg = "";
    var $tester_dsn;

    //*******************************************************************************/
    //main constructor function of the class ChatRoom
    //*******************************************************************************/
    function ChatRoom($id_chatroom, $tester_dsn) {

        //$this->tester_dsn = MultiPort::getDSN($tester);
        $this->tester_dsn = $tester_dsn;

        $dh = $GLOBALS['dh'];
        //get $user_id from the session variables
        $sess_id_user = $_SESSION['sess_id_user'];
        //get $id_course_instance from the session variables
        if (!empty($_SESSION['sess_id_course_instance'])) {
            $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        }

        //case id_chatroom is provided
        if ((isset($id_chatroom)) && (!is_object($id_chatroom))) {
            // search for a chatroom into the DB with such id_chatroom
            $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
//            print_r($chatroom_ha);
            // the chatroom with such id allready exists;
            if (is_array($chatroom_ha)) {

                // check the user status into the chatroom
                $present = $this->find_userFN($sess_id_user, $id_chatroom);
                //get course instance assigned to the chatroom
                $id_course_instance = $chatroom_ha['id_istanza_corso'];

                //check the type of the chatroom
                switch ($chatroom_ha['tipo_chat']) {
                    //class chatroom
                    case CLASS_CHAT :
                        //verify that this chatroom is the correct one for his classroom
                        if (!empty($sess_id_course_instance)) {
                            if ($sess_id_course_instance == $id_course_instance) {
                                switch ($GLOBALS['id_profile']) {
                                    case AMA_TYPE_STUDENT:
                                        switch ($present) {
                                            case STATUS_ACTIVE:
                                            case STATUS_OPERATOR:
                                            case STATUS_MUTE:
                                                $this->error = 0;
                                                break;
                                            case STATUS_BAN:
                                                $this->error = 1;
                                                $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente nella chatroom"); // non presente
                                        }//end switch
                                        break;
                                    case AMA_TYPE_TUTOR:
                                        switch ($present) {
                                            case STATUS_OPERATOR:
                                            case STATUS_ACTIVE:
                                            case STATUS_MUTE:
                                            case STATUS_BAN:
                                            case STATUS_EXIT:
                                            case STATUS_INVITED:
                                                // do nothing
                                                $this->error = 0;
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente"); // non presente
                                        }//end switch
                                        break;
                                    default:
                                        $this->error = 1;
                                        $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                                }// end of $GLOBALS['id_profile']
                            }
                            // sess_id_course_instance != id_course_instance
                            else {
                                // user tries to enter a chat not belonging to his course_instance
                                $this->error = 1;
                                $this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom non  della tua classe. Impossibile proseguire"); // wrong chat
                            }// end of $sess_id_course_instance == $id_course_instance
                        }// end isset($sess_id_course_instance)
                        else {
                            switch ($GLOBALS['id_profile']) {
                                case AMA_TYPE_STUDENT:
                                    //case student: does the student is subscribed into the course?
                                    $res_Ha = $dh->get_subscription($sess_id_user, $id_course_instance);
                                    if ($res_Ha['tipo'] == 2) {
                                        switch ($present) {
                                            case STATUS_ACTIVE:
                                            case STATUS_OPERATOR:
                                            case STATUS_MUTE:
                                                $this->error = 0;
                                                break;
                                            case STATUS_BAN:
                                                $this->error = 1;
                                                $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente"); // non presente
                                        }//end switch
                                    } else {
                                        // user tries to enter a chat not belonging at his course_instance
                                        $this->error = 1;
                                        $this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom  non della tua classe. Impossibile proseguire"); // wrong chat
                                    }// end $res_Ha['tipo']
                                    break;
                                case AMA_TYPE_TUTOR :
                                    //case tutor: does the user is the tutor of the course_instance?
                                    $id_tutor = $dh->course_instance_tutor_get($id_course_instance);
                                    if ($id_tutor == $sess_id_user) {
                                        switch ($present) {
                                            case STATUS_OPERATOR:
                                            case STATUS_ACTIVE:
                                            case STATUS_MUTE:
                                            case STATUS_BAN:
                                            case STATUS_INVITED:
                                            case STATUS_EXIT:
                                                $this->error = 0;
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente"); // non presente
                                        }//end switch
                                    }
                                    break;
                                case AMA_TYPE_AUTHOR:
                                    $this->error = 1;
                                    // vito, 20 apr 2009
                                    //$this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom  non della tua classe. Impossibile proseguire"); // wrong chat
                                    $this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom a cui no sei invitato. Impossibile proseguire"); // wrong chat
//   			                        }// end of if($id_tutor == $sess_id_user)
                                    break;
                                default:
                                    $this->error = 1;
                                    $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                            }
                        }// end classroom chat type
                        break;
                    //chatroom for invited users only
                    case INVITATION_CHAT :
                        if ($present == STATUS_INVITED or
                                $present == STATUS_EXIT) {
                            $this->error = 2; // enter time is to be updated
                        } elseif ($present == STATUS_ACTIVE or
                                $present == STATUS_OPERATOR or
                                $present == STATUS_MUTE) {
                            $this->error = 0; // enter time will not updated
                        } elseif ($present == STATUS_BAN) {
                            $this->error = 1;
                            $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                        } else {
                            // user tries to enter a chat that he is not invited
                            $this->error = 1;
                            $this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom in cui non sei invitato. Impossibile proseguire"); // wrong chat
                        }
                        break;
                    //public chatroom
                    case PUBLIC_CHAT :
                        if ($present == STATUS_ACTIVE or
                                $present == STATUS_OPERATOR or
                                $present == STATUS_MUTE) {
                            $this->error = 0; // enter time will not updated
                        } elseif ($present == STATUS_BAN) {
                            $this->error = 1;
                            $this->error_msg = translateFN("Accesso ristretto");
                        } else {
                            $this->error = 2;
                        }
                        break;
                    default:
                        $this->error = 1;
                        $this->error_msg = translateFN("Accesso ristretto");
                }// end of switch when $chatroom_ha exists
            }// end of case $id_chatroom exist ,$chatroom_ha is_array
            //case $chatroom_ha !is_array
            else {
                //verify that a course_instance exists
                if (!empty($sess_id_course_instance)) {
                    // search into the DB for a chatroom assosciated to the course_instance
                    $id_chatroom = $this->get_class_chatroomFN($sess_id_course_instance);
                    // there is a chatroom for that course_instance
                    if ($id_chatroom) {
                        // search for a chatroom into the DB with such id_chatroom
                        $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
                        // the chatroom with such id allready exists;
                        if (is_array($chatroom_ha)) {
                            // check the user status into the chatroom
                            $present = $this->find_userFN($sess_id_user, $id_chatroom);
                            //get course instance assigned to the chatroom
                            $id_course_instance = $chatroom_ha['id_istanza_corso'];
                            if ($sess_id_course_instance == $id_course_instance) {
                                switch ($GLOBALS['id_profile']) {
                                    case AMA_TYPE_STUDENT:
                                        switch ($present) {
                                            case STATUS_ACTIVE:
                                            case STATUS_OPERATOR:
                                            case STATUS_MUTE:
                                                $this->error = 0;
                                                break;
                                            case STATUS_BAN:
                                                $this->error = 1;
                                                $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente"); // non presente
                                        }//end switch
                                        break;
                                    case AMA_TYPE_TUTOR:
                                        switch ($present) {
                                            case STATUS_OPERATOR:
                                            case STATUS_ACTIVE:
                                            case STATUS_MUTE:
                                            case STATUS_BAN:
                                            case STATUS_INVITED:
                                            case STATUS_EXIT:
                                                $this->error = 0;
                                                break;
                                            default:
                                                $this->error = 2;
                                                $this->error_msg = translateFN("Utente non presente"); // non presente
                                        }//end switch
                                        break;
                                    default:
                                        $this->error = 1;
                                        $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                                }//end switch $GLOBALS['id_profile']
                            }
                            //case $sess_id_course_instance != $id_course_instance
                            else {
                                // user tries to enter a chat not belonging at his course_instance
                                $this->error = 1;
                                $this->error_msg = translateFN("Accesso negato. Stai cercando ad accedere in una chatroom non della tua classe. Impossibile proseguire"); // wrong chat
                            }//end case $sess_id_course_instance == $id_course_instance
                        }
                        //case !is_array($chatroom_ha), meanwhile $id_chatroom exists
                        else {
                            //$id_chatroom exists,$chatroom_ha not an array
                            $this->error = 1;
                            $this->error_msg = translateFN("Errore nella lettura dati. Impossibile proseguire");
                        }//end of (is_array($chatroom_ha))
                    }//end of if ($id_chatroom)
                    else { //error. no chatroom where found for the classroom, but $sess_id_course_instance isset
                        //if does not exist create a new chatroom for the class
                        $id_chatroom = $this->add_chatroomFN($chatroom_ha, $this->tester_dsn);
                        $this->error = 2;
                        $this->error_msg = translateFN("Utente non presente"); // non presente
                    }// end of case that the provided id does not exist
                }// end if (isset($sess_id_course_instance))
                else {
                    // we could decide to give no access to any room
                    // $this->error = 1;
                    // $this->error_msg = translateFN("Chatroom non trovata. Impossibile proseguire");
                    // give access to user for the public chatroom
                    $id_chatroom = $this->find_public_chatroomFN();
                    $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
                    // check the user status into the chatroom
                    $present = $this->find_userFN($sess_id_user, $id_chatroom);
                    switch ($present) {
                        case STATUS_ACTIVE:
                        case STATUS_OPERATOR:
                        case STATUS_MUTE:
                            $this->error = 0;
                            break;
                        case STATUS_BAN:
                            $this->error = 1;
                            $this->error_msg = translateFN("Accesso negato. Impossibile proseguire"); // banned
                            break;
                        default:
                            $this->error = 2;
                            $this->error_msg = translateFN("Utente non presente"); // non presente
                    }
                }// end else (isset($sess_id_course_instance))
            }// end else(is_array($chatroom_ha))
        }
        // no id_chatroom was provided
        else {
            $this->error = 1;
            $this->error_msg = translateFN("Non &egrave; stato passato nessun parametro per l'identificazione di una chatroom. Impossibile proseguire");
        }// end if($id_chatroom)provided exists
        //if no error were found

        switch ($this->error) {
            // no errors were found
            case 0:
            case 2:
                if ($this->error == 2) {
                    // try to add user into that chatroom
                    $add_user = $this->add_user_chatroomFN($sess_id_user, $sess_id_user, $id_chatroom, ACTION_ENTER, STATUS_ACTIVE);
                }
                $this->chat_type = $chatroom_ha['tipo_chat'];
                $this->id_chat_owner = $chatroom_ha['id_proprietario_chat'];
                $this->chat_title = $chatroom_ha['titolo_chat'];
                $this->chat_topic = $chatroom_ha['argomento_chat'];
                $this->max_users = $chatroom_ha['max_utenti'];
                $this->welcome_msg = $chatroom_ha['msg_benvenuto'];
                $this->start_time = $chatroom_ha['tempo_avvio'];
                $this->end_time = $chatroom_ha['tempo_fine'];
                $this->id_course_instance = $chatroom_ha['id_istanza_corso'];
                $this->id_chatroom = $chatroom_ha['id_chatroom'];
                break;
            // error situation
            case 1:
            default:
            // do nothing
        } // switch()
    }

//end of constuction function
    //*******************************************************************************/
    //adds a chatroom into table chatroom
    //*******************************************************************************/
    function add_chatroomFN($chatroom_ha, $tester_dsn) {
        $dh = $GLOBALS['dh'];

        /*
         * Check if was assigned a course instance to this chatroom. If it wasn't,
         * use course instance from request or from session.
         */
        if (!isset($chatroom_ha['id_course_instance'])
                || empty($chatroom_ha['id_course_instance'])) {

            //get $id_course_instance from the REQUEST variables
            if (!empty($_REQUEST['id_course_instance'])) {
                $id_course_instance = $_REQUEST['id_course_instance'];
            }
            //get $id_course_instance from the session variables
            elseif (!empty($_SESSION['sess_id_course_instance'])) {
                $id_course_instance = $_SESSION['sess_id_course_instance'];
            }
            $chatroom_ha['id_course_instance'] = $id_course_instance;
        }


        // get the user_id form the session variables
        $sess_id_user = $_SESSION['sess_id_user'];
        // get the course title form the GLOBAL variables
        $course_title = $GLOBALS['course_title'];

        /*
         * Check if was assigned a chat owner: if it wasn't, get this course's tutor
         * and set him as chat owner
         */
        if (!isset($chatroom_ha['id_chat_owner']) || empty($chatroom_ha['id_chat_owner'])) {
            $id_tutor = $dh->course_instance_tutor_get($chatroom_ha['id_course_instance']);
            if (!AMA_DataHandler::isError($id_tutor)) {
                $chatroom_ha['id_chat_owner'] = $id_tutor;
            }
        }

        if (!empty($chatroom_ha['chat_type'])) {
            $chatroom_ha['chat_type'] = $chatroom_ha['chat_type'];
        } else {
            $chatroom_ha['chat_type'] = CLASS_CHAT;
        }

        if (!empty($chatroom_ha['chat_title'])) {
            $chatroom_ha['chat_title'] = $chatroom_ha['chat_title'];
        } else {
            $chatroom_ha['chat_title'] = addslashes($course_title);
        }

        if (!empty($chatroom_ha['chat_topic'])) {
            $chatroom_ha['chat_topic'] = $chatroom_ha['chat_topic'];
        } else {
            $chatroom_ha['chat_topic'] = addslashes($course_title);
        }
// MOVED TO LINE 446
//      if (isset($chatroom_ha['id_chat_owner'])) {
//        $chatroom_ha['id_chat_owner']=$chatroom_ha['id_chat_owner'];
//      }
//      else {
//        $chatroom_ha['id_chat_owner']= $id_tutor;
//      }

        if (!empty($chatroom_ha['start_time'])) {
            $chatroom_ha['start_time'] = $chatroom_ha['start_time'];
        } else {
            $chatroom_ha['start_time'] = time();
        }

        // check again this field
        if (isset($chatroom_ha['end_time'])) {
            $chatroom_ha['end_time'] = $chatroom_ha['end_time'];
        } else {
            $chatroom_ha['end_time'] = time() + SHUTDOWN_CHAT_TIME;
        }

        if (!empty($chatroom_ha['welcome_msg'])) {
            $chatroom_ha['welcome_msg'] = $chatroom_ha['welcome_msg'];
        } else {
            $chatroom_ha['welcome_msg'] = addslashes(translateFN("Benvenuti nella chat di ADA.Ricordatevi di uscire correttamente dalla chat usando l'apposita funzionalita'"));
        }

        if (!empty($chatroom_ha['max_users'])) {
            $chatroom_ha['max_users'] = $chatroom_ha['max_users'];
        } else {
            $chatroom_ha['max_users'] = DEFAULT_MAX_USERS;
        }
// MOVED TO LINE 422
//      // check again this field
//      if(isset($chatroom_ha['id_course_instance'])) {
//        $chatroom_ha['id_course_instance']=$chatroom_ha['id_course_instance'];
//      }
//      else {
//        $chatroom_ha['id_course_instance']= $id_course_instance;
//      }
        // write to db and returns the id of the chatroom $id_chatroom.
//      if (isset($this)) {
//        $this->chatroom_ha = $chatroom_ha;
//      }

        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->add_chatroom($chatroom_ha);

        return $result;
    }

// 	/*
// 	 * vito, 8 apr 2009
// 	 */
// 	/**
// 	 * function create_public_chatroom
// 	 *
// 	 * @param  $course_instance_ha - an associative array representing the course
// 	 * instance for which we have to create a public chatroom
// 	 *
// 	 * @return
// 	 */
// 	function create_public_chatroom($course_instance_ha, $course_ha) {
//
// 	  $cdh = ChatDataHandler::instance();
// 	  if (ChatDataHandler::isError($cdh)) {
// 	    // gestire errore
// 	  }
//
// 	  /*
// 	   * create the chatroom_ha based on the content of $course_instance_ha
// 	   */
// 	  // get a tutor from this course instance and set him as the chatroom owner.
// 	  $id_chatroom_owner = $cdh->course_tutor_instance_get($course_instance_ha['id_istanza_corso'],1);
// 	  if (AMA_DataHandler::isError($id_chatroom_owner) || $id_chatroom_owner === false) {
//        // passare l'id dell'amministratore?
// 	    $id_chatroom_owner = 0;
// 	  }
//
// 	  $chatroom_ha = array(
// 	    'chat_type'          => PUBLIC_CHAT,
// 	    'chat_title'         => sprintf(translateFN('Chat pubblica per il corso %s'),$course_ha['titolo']),
// 	    'chat_topic'         => translateFN('Chat pubblica'),
// 	    'id_chatroom_owner'  => $id_chatroom_owner,
// 	    'start_time'	     => $course_instance_ha['data_inizio'],
// 	    'end_time'			 => $cdh->add_number_of_days($course_instance_ha['durata'],$course_instance_ha['data_inizio']),
// 	    'welcome_msg'		 => sprintf(translateFN('Benvenuto nella chat pubblica del corso %s'),$course_ha['titolo']),
// 	    'max_users'			 => DEFAULT_MAX_USERS,
// 	    'id_course_instance' => $course_instance_ha['id_istanza_corso']
// 	  );
//
// 	  $result = $cdh->add_chatroom($chatroom_ha);
// 	  return $result;
// 	}
    //*******************************************************************************/
    //removes an unused chatroom, if any integrity violation is presented,
    //the process is aborted.
    //*******************************************************************************/
    function remove_unused_chatroomFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->remove_unused_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //removes all the unused chatrooms, if any integrity violation is presented,
    //the proccess is aborted.
    //*******************************************************************************/
    function remove_all_unused_chatroomsFN() {
        $chatrooms_ha = $this->get_all_chatroomsFN();
        foreach ($chatrooms_ha as $key => $id) {
            $this->remove_unused_chatroomFN($id);
        }
    }

    //*******************************************************************************/
    //removes a chatroom even if the chatroom is still running.
    //users will be removed automatically from the table utente_chatroom
    //messages also will be removed automatically from the table messaggi
    //*******************************************************************************/
    function remove_chatroomFN($id_chatroom) {
        if (!self::isInStaticContext()) {
            $this->id_chatroom = $id_chatroom;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->remove_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //removes all the unused chatrooms
    //*******************************************************************************/
    function remove_all_chatroomsFN() {
        $chatrooms_ha = $this->get_all_chatroomsFN();
        foreach ($chatrooms_ha as $key => $id) {
            $this->remove_chatroomFN($id);
        }
    }

    //*******************************************************************************/
    //gets all the information about a specific chatroom
    //*******************************************************************************/
    function get_info_chatroomFN($id_chatroom) {
        if (!self::isInStaticContext()) {
            $this->id_chatroom = $id_chatroom;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_info_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //gets the list of all the active chatrooms
    //*******************************************************************************/
    function get_all_chatroomsFN() {
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_all_chatrooms();
        return $result;
    }

    //*******************************************************************************/
    //gets the id of the active public chatroom
    //*******************************************************************************/
    function find_public_chatroomFN() {
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->find_public_chatroom();
        return $result;
    }

    //*******************************************************************************/
    //gets the id of the active chatroom, relative to a specific class
    //*******************************************************************************/
    function get_class_chatroomFN($id_course_instance) {
        if (!self::isInStaticContext()) {
            $this->id_course_instance = $id_course_instance;
        }
        $actual_time = time();
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_class_chatroom($id_course_instance, $actual_time);
        return $result;
    }

/* function create_public_chatroom
/*
/* @param  $course_instance_ha - an associative array representing the course
/* instance for which we have to create a public chatroom
/*
/* @return
 */
    function get_class_chatroom_for_instance($id_course_instance, $type) {
        if (!self::isInStaticContext()) {
            $this->id_course_instance = $id_course_instance;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_class_chatroom_for_instance($id_course_instance, $type);
        return $result;
    }

    // vito, 20 apr 2009
    function get_class_chatroom_with_durationFN($id_course_instance, $start_time, $end_time) {
        if (!self::isInStaticContext()) {
            $this->id_course_instance = $id_course_instance;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_class_chatroom_with_durationFN($id_course_instance, $start_time, $end_time);
        return $result;
    }

    //*******************************************************************************/
    // returns an array contaning the ids of the chatrooms relative to a specific class
    //*******************************************************************************/
    function get_all_class_chatroomsFN($id_course_instance) {
        if (!self::isInStaticContext()) {
            $this->id_course_instance = $id_course_instance;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_all_class_chatrooms($id_course_instance);
        return $result;
    }

    //*******************************************************************************/
    //gets all the private chatrooms taht a user could have access
    //*******************************************************************************/
    function get_all_private_chatroomsFN($user_id) {
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_all_private_chatrooms($user_id);
        return $result;
    }

    //*******************************************************************************/
    //adds a user into the chosen chatroom
    // sets the user_status by default as active
    // sets the entrance_time equal to the actual time
    //*******************************************************************************/
    function add_user_chatroomFN($operator_id, $user_id, $id_chatroom, $action, $status) {
        if (!self::isInStaticContext()) {
            $this->operator_id = $operator_id;
            $this->user_id = $user_id;
            $this->id_chatroom = $id_chatroom;
            $this->status = $status;
            $this->action = $action;
        }
        $entrance_time = time();
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->add_user_chatroom($operator_id, $user_id, $id_chatroom, $entrance_time, $action, $status);
        return $result;
    }

    //*******************************************************************************/
    //deletes permanently a row of the table utente_chatroom
    //where id_utente=$user_id and id_chatroom=$id_chatroom
    //*******************************************************************************/
    function remove_user_chatroomFN($user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->remove_user_chatroom($user_id, $id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //deletes permanently all the rows of the table utente_chatroom
    //where id_chatroom=$id_chatroom
    //*******************************************************************************/
    function remove_allusers_chatroomFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->remove_allusers_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    // the users quits the chatroom, do not appears anymore into the users_list
    // the row inclunding his id_user will be removed from the table utente_chatroom
    //*******************************************************************************/
    function quit_chatroomFN($operator_id, $user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $exit_time = time();
        $action = ACTION_EXIT;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->quit_chatroom($operator_id, $user_id, $id_chatroom, $exit_time, $action);
        return $result;
    }

    //*******************************************************************************/
    // sets the status of the user in a specific chatroom
    //*******************************************************************************/
    function set_user_statusFN($operator_id, $user_id, $id_chatroom, $action) {
        $this->operator_id = $operator_id;
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $this->action = $action;
        $time = time();

        switch ($action) {
            /*
             * vito, 26 settembre 2008
             */
            case ACTION_SET_OPERATOR:
            case ADA_CHAT_OPERATOR_ACTION_SET_OPERATOR:
                $status = STATUS_OPERATOR;
                break;
            case ACTION_UNSET_OPERATOR:
            case ADA_CHAT_OPERATOR_ACTION_UNSET_OPERATOR:
                $status = STATUS_ACTIVE;
                break;
            case ACTION_MUTE:
            case ADA_CHAT_OPERATOR_ACTION_MUTE_USER:
                $status = STATUS_MUTE;
                break;
            case ACTION_UNMUTE:
            case ADA_CHAT_OPERATOR_ACTION_UNMUTE_USER:
                $status = STATUS_ACTIVE;
                break;
            case ACTION_BAN:
            case ADA_CHAT_OPERATOR_ACTION_BAN_USER:
                $status = STATUS_BAN;
                break;
            case ACTION_UNBAN:
            case ADA_CHAT_OPERATOR_ACTION_UNBAN_USER:
                $status = STATUS_ACTIVE;
                break;
            // vito, 26 settembre 2008
            //default:
            case ACTION_EXIT:
            case ACTION_KICK:
            case ADA_CHAT_OPERATOR_ACTION_KICK_USER:
                $status = STATUS_EXIT;
                break;
                //case ACTION_INVITE:
                $status = STATUS_INVITED;
                break;
            //default:
        } // switch
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->set_user_status($operator_id, $user_id, $id_chatroom, $action, $status, $time);
        return $result;
    }

    //*******************************************************************************/
    //fuction in order to check the user status(es. active, kicked, mute ecc)
    //*******************************************************************************/
    function get_user_statusFN($user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_user_status($user_id, $id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //fuction in order to find a user connected to the chatroom
    //*******************************************************************************/
    function find_userFN($user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->get_user_status($user_id, $id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //gets the list of the users, present into the chosen chatroom
    //*******************************************************************************/
    function list_users_chatroomFN($id_chatroom) {
        //returns an array contaning the id of the users connected
        if (!self::isInStaticContext()) {
            $this->id_chatroom = $id_chatroom;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->list_users_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //gets the list of the users, invited but not present into the chosen chatroom
    //*******************************************************************************/
    function list_users_invited_to_chatroomFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $cdh = self::obtainChatDataHandlerInstance();

        $result = $cdh->list_users_invited_to_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //gets the list of the users, been banned into the chosen chatroom
    //*******************************************************************************/
    function list_banned_users_chatroomFN($id_chatroom) {
        //returns an array contaning the id of the users connected
        $this->id_chatroom = $id_chatroom;

        $cdh = self::obtainChatDataHandlerInstance();

        $result = $cdh->list_banned_users_chatroom($id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    //gets the list of all the chatrooms that a user could have access or
    // he is allready present
    //*******************************************************************************/
    function list_chatrooms_userFN($user_id) {
        // vito, 22 apr 2009
        if (!self::isInStaticContext()) {
            $this->user_id = $user_id;
        }
        $cdh = self::obtainChatDataHandlerInstance();
        $result = $cdh->list_chatrooms_user($user_id);
        return $result;
    }

    //*******************************************************************************/
    //updates all or some information relative to the chosen chatroom
    //*******************************************************************************/
    function set_chatroomFN($id_chatroom, $chatroom_ha) {
        //first we read and get all the information from the DB, relative to the selected
        // chatroom, assining all the values into the variable $old_chatroom_ha
        $old_chatroom_ha = $this->get_info_chatroomFN($id_chatroom);

        if (!empty($chatroom_ha['chat_type']))
            $chatroom_ha['chat_type'] = $chatroom_ha['chat_type'];
        else
            $chatroom_ha['chat_type'] = $old_chatroom_ha['tipo_chat'];

        if (!empty($chatroom_ha['chat_title']))
            $chatroom_ha['chat_title'] = $chatroom_ha['chat_title'];
        else
            $chatroom_ha['chat_title'] = $old_chatroom_ha['titolo_chat'];

        if (!empty($chatroom_ha['chat_topic']))
            $chatroom_ha['chat_topic'] = $chatroom_ha['chat_topic'];
        else
            $chatroom_ha['chat_topic'] = $old_chatroom_ha['argomento_chat'];

        if (!empty($chatroom_ha['id_chat_owner']))
            $chatroom_ha['id_chat_owner'] = $chatroom_ha['id_chat_owner'];
        else
            $chatroom_ha['id_chat_owner'] = $old_chatroom_ha['id_proprietario_chat'];

        if (!empty($chatroom_ha['start_time']))
            $chatroom_ha['start_time'] = $chatroom_ha['start_time'];
        else
            $chatroom_ha['start_time'] = $old_chatroom_ha['tempo_avvio'];

        if (!empty($chatroom_ha['end_time']))
            $chatroom_ha['end_time'] = $chatroom_ha['end_time'];
        else
            $chatroom_ha['end_time'] = $old_chatroom_ha['tempo_fine'];

        if (!empty($chatroom_ha['welcome_msg']))
            $chatroom_ha['welcome_msg'] = $chatroom_ha['welcome_msg'];
        else
            $chatroom_ha['welcome_msg'] = $old_chatroom_ha['msg_benvenuto'];

        if (!empty($chatroom_ha['max_users']))
            $chatroom_ha['max_users'] = $chatroom_ha['max_users'];
        else
            $chatroom_ha['max_users'] = $old_chatroom_ha['max_utenti'];

        if (!empty($chatroom_ha['id_course_instance']))
            $chatroom_ha['id_course_instance'] = $chatroom_ha['id_course_instance'];
        else
            $chatroom_ha['id_course_instance'] = $old_chatroom_ha['id_istanza_corso'];

        $this->id_chatroom = $id_chatroom;
        $this->chatroom_ha = $chatroom_ha;

        $cdh = self::obtainChatDataHandlerInstance();

        $result = $cdh->set_chatroom($id_chatroom, $chatroom_ha);

        return $result;
    }

    //*******************************************************************************/
    // inserts the time of the last event done by the user on a specific chatroom
    //*******************************************************************************/
    function set_last_event_timeFN($user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;
        $last_event_time = time();

        $cdh = self::obtainChatDataHandlerInstance();

        $result = $cdh->set_last_event_time($user_id, $id_chatroom, $last_event_time);
        return $result;
    }

    //*******************************************************************************/
    // gets the time of the last event done by the user on a specific chatroom
    //*******************************************************************************/
    function get_last_event_timeFN($user_id, $id_chatroom) {
        $this->user_id = $user_id;
        $this->id_chatroom = $id_chatroom;

        $cdh = self::obtainChatDataHandlerInstance();

        $result = $cdh->get_last_event_time($user_id, $id_chatroom);
        return $result;
    }

    //*******************************************************************************/
    // verify if a chatroom is still active
    //*******************************************************************************/
    function is_chatroom_activeFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $actual_time = time();
        $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
        $starting_time = $chatroom_ha ['tempo_avvio'];
        $expiration_time = $chatroom_ha ['tempo_fine'];
        if (($actual_time >= $starting_time) and (($expiration_time == 0) or ($actual_time < $expiration_time))) {
            return true;
        } else {
            return false;
        }
    }

    //*******************************************************************************/
    // verify if a chatroom is started or not
    //*******************************************************************************/
    function is_chatroom_startedFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $actual_time = time();
        $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
        if (is_array($chatroom_ha)) {
            $starting_time = $chatroom_ha ['tempo_avvio'];
            if ($actual_time >= $starting_time) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //*******************************************************************************/
    // verify if a chatroom is expired or not
    //*******************************************************************************/
    function is_chatroom_not_expiredFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $actual_time = time();
        $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
        if (is_array($chatroom_ha)) {
            $expiration_time = $chatroom_ha ['tempo_fine'];
            if (($expiration_time == 0) or ($actual_time < $expiration_time)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //*******************************************************************************/
    // verify if a user is a moderator or not
    //*******************************************************************************/
    function is_user_moderatorFN($user_id, $id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $this->user_id = $user_id;
        $status = $this->get_user_statusFN($user_id, $id_chatroom);
        if ($status == STATUS_OPERATOR) {
            return true;
        } else {
            return false;
        }
    }

    //*******************************************************************************/
    // verify if a chatroom is full
    //*******************************************************************************/
    function is_chatroom_fullFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        $chatroom_ha = $this->get_info_chatroomFN($id_chatroom);
        if (is_array($chatroom_ha)) {
            $max_users = $chatroom_ha ['max_utenti'];
            $list = $this->list_users_chatroomFN($id_chatroom);
            $how_many = count($list);
            if ($how_many > $max_users) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //*******************************************************************************/
    // removes all the users from the chatroom that they are idle for a certain period
    //*******************************************************************************/
    function remove_inactive_usersFN($id_chatroom) {
        $this->id_chatroom = $id_chatroom;
        //get the actual time
        $actual_time = time();
        // get the list of the users into the chatroom
        $users_list_ar = $this->list_users_chatroomFN($id_chatroom);

        foreach ($users_list_ar as $key => $id) {
            $this->id = $id;
            //check user status
            $user_status = $this->get_user_statusFN($id, $id_chatroom);
            //get last event time of user
            $user_last_event = $this->get_last_event_timeFN($id, $id_chatroom);

            if (($user_status != STATUS_BAN) and ($actual_time - $user_last_event > MAX_INACTIVE_TIME)) {
                $this->remove_user_chatroomFN($id, $id_chatroom);
            }
        }
    }
    /**
     * Checks if the class is running in a static context.
     *
     * @return bool true if the method invocation happened in a static context
     */
    private static function isInStaticContext()
    {
         return !(isset($this) && get_class($this) == __CLASS__);
    }
    /**
     * Obtains a ChatDataHandler instance.
     *
     * @return ChatDataHandler instance
     */
    private static function obtainChatDataHandlerInstance()
    {
        if (!self::isInStaticContext()) {
            $cdh = ChatDataHandler::instance($this->tester_dsn);
        } else {
            $tester_dsn = $_SESSION['sess_selected_tester_dsn'];
            $cdh = ChatDataHandler::instance($tester_dsn);
        }
        return $cdh;
    }
}