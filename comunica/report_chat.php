<?php
/*
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2001-2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/* 0.
Initializating variables and including modules

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
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'), // Access only invitation chat
    AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';

require_once 'include/comunica_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
ComunicaHelper::init($neededObjAr);

require_once 'include/ChatRoom.inc.php';
require_once 'include/ChatDataHandler.inc.php';

$self =  "list_chatrooms"; //whoami();
$log_type = "db";
/* 1. getting data about user

*/
$ymdhms = today_dateFN();


/*
 * vito, 16 mar 2009, when called by tutor.php, receives the course instance id
 * passed by GET in $id_instance.
 */
if (!isset($sess_id_course_instance) && isset($id_instance)) {
  $sess_id_course_instance = $id_instance;
}

$title = translateFN("ADA - Chat Log");

if (!isset($id_chatroom)) $id_chatroom = null;
$chatroomObj = new ChatRoom($id_chatroom);
if (is_object($chatroomObj) && !AMA_DataHandler::isError($chatroomObj)) {
    //get the array with all the current info of the chatoorm
    $id_course_instance = $chatroomObj->id_course_instance;
    $id_course = $dh->get_course_id_for_course_instance($id_course_instance);
    // ******************************************************
    // get  course object
    $courseObj = read_course($id_course);
    if ((is_object($courseObj)) && (!AMA_DB::isError($courseObj)))  {
            $course_title = $courseObj->titolo; //title
            $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
    }
}


if (empty($media_path))
	$media_path = MEDIA_PATH_DEFAULT;

$banner = include ("$root_dir/include/banner.inc.php");

if (isset($id_chatroom)) $menuOptions['id_room']=$id_chatroom;
if (isset($id_course)) $menuOptions['id_course']=$id_course;
// $op
if (!isset($op)) $op=null;
switch ($op){
    case 'rooms':
    case '':
        switch ($log_type){
            case 'file':
            // versione che legge da file

            $chat_log_file =  "$root_dir/chat/chat/log/chat_".$stanza.".log";

            if (file_exists($chat_log_file)==1) {
                $chat_dataAr = array();
                $chat_logAr = file($chat_log_file);
                $usersHa = array();
                $chat_msg = 0;
                foreach($chat_logAr as $chat_row_string){
                    $chat_rowAr = explode("|",$chat_row_string);
                    $date =  $chat_rowAr[0];
                    $user =  $chat_rowAr[1];
                    $message =  $chat_rowAr[2];
                    $chat_msg++;
                    $row = array(
                            translateFN('Data e ora')=>ts2dFN($date)." ".ts2tmFN($date),
                            translateFN('Utente')=>$user,
                            translateFN('Messaggio')=>strip_tags($message)
                            );
                    array_push($chat_dataAr,$row);
                    if (in_array($user,array_keys($usersHa))){
                            $n = $usersHa[$user];
                            $usersHa[$user] = $n+1;
                    // echo  $user.":".$usersHa[$user]."<br>";
                    } else {
                            $usersHa[$user]=1;
                    }
                }

                $user_chat_report = translateFN("Totale messaggi:")." ".$chat_msg."<br />";
                $user_chat_report .= translateFN("Ultimo messaggio:")." ". ts2dFN($date)." ".ts2tmFN($date)."<br />";
                $user_chat_report .= translateFN("Utenti / messaggi:")."<br /><br />";

                foreach  ($usersHa as $k=>$v)
                                $user_chat_report.= "$k: $v<br/>\n";
                $tObj = new Table();
                $tObj->initTable('0','right','1','0','90%','','','','','1','0');
                // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
                $caption = translateFN("Resoconto della chat di classe");
                $summary = translateFN("Chat fino al $ymdhms");
                $tObj->setTable($chat_dataAr,$caption,$summary);
                $tabled_chat_dataHa = $tObj->getTable();
                $menuOptions['id_instance']=$sess_id_course_instance;
                $menuOptions['id_course']=$id_course;
                $menuOptions['days']=$days;
                $menuOptions['id_chatroom']=$sess_id_course_instance;
            }
            else {
            }
            $tabled_chat_dataHa = translateFN("Nessuna chat disponibile.");
       break;
    case 'db':
            $chat_report ="";

            if (!isset($id_chatroom)) // ???
                    if (isset($id_instance))
                            $id_chatroom = $id_instance;
                    elseif (isset($sess_id_course_instance))
                            $id_chatroom = $sess_id_course_instance;

            $mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
            if(!isset($sess_user_id)) $sess_user_id = null;
            $chat_data = $mh->find_chat_messages($sess_user_id, ADA_MSG_CHAT, $id_chatroom, $fields_list="", $clause="", $ordering="");
            if (is_array($chat_data)){
                    $chat_dataAr = array();
                    $chat_data_simpleAr = array();
                    $c=0;
                    $tbody_data = array();
                    foreach ($chat_data as $chat_msgAr){
                        if (is_numeric($chat_msgAr[0])) {
                            $sender_dataHa = $dh->_get_user_info($chat_msgAr[0]);

                            $user = $sender_dataHa['nome'] . ' ' . $sender_dataHa['cognome'];
                            $message = $chat_msgAr[1];
                            $data_ora = ts2dFN($chat_msgAr[2])." ".ts2tmFN($chat_msgAr[2]);
                            $tbody_data[] = array(
                                $data_ora,
                                $user,
                                strip_tags($message)
                            );
                            $chat_report.= "$data_ora $user: $message<br/>\n";
                            $c++;
                        }
                    }
                    $user_chat_report = translateFN("Totale messaggi:")." ".$c."<br />";
                    if(isset($data_ora) && strlen($data_ora)>0) {
                    	$user_chat_report .= translateFN("Ultimo messaggio:")." ". $data_ora."<br />";
                    }
                    $user_chat_report .= translateFN("Utenti / messaggi:")."<br /><br />";
                    $user_chat_report .=$chat_report;

                    $thead_data = array(translateFN('Data e ora'), translateFN('Utente'), translateFN('Messaggio'));
                    $table_Mess = BaseHtmlLib::tableElement('class:sortable', $thead_data, $tbody_data);
                    $tabled_chat_dataHa = $table_Mess->getHtml();
                    $menuOptions['id_chatroom'] = $id_chatroom;
                    if (isset($days)) $menuOptions['days'] = $days;

            } else {
                    $tabled_chat_dataHa = translateFN("Nessuna chat disponibile.");
            }
  //      }
        break;
    }
    break;
case 'index':

        $class_chatrooms_ar = array();
        $class_chatrooms = ChatRoom::get_all_class_chatroomsFN($sess_id_course_instance);
        if(is_array($class_chatrooms)){
                $class_chatrooms_ar[] =$class_chatrooms;
        }
        // get only the ids of the chatrooms
        foreach($class_chatrooms_ar as $value){
                foreach ($value as $id){
                        $chatrooms_class_ids_ar[] = $id;
                }
        }
        //initialize the array of the chatrooms to be displayed on the screen
        $list_chatrooms ="";
        // start the construction of the table contaning all the chatrooms
        foreach($chatrooms_class_ids_ar as $id_chatroom){
          // vito, 16 mar 2009
          if (!is_object($id_chatroom)){
            $chatroomObj = new ChatRoom($id_chatroom);
                //get the array with all the current info of the chatoorm
                $chatroom_ha = $chatroomObj->get_info_chatroomFN($id_chatroom);
          }
          $list_chatrooms .="<a href=\"report_chat.php?id_chatroom=$id_chatroom\">{$chatroom_ha['titolo_chat']}</a><br />";
        }
        $tabled_chat_dataHa  = $list_chatrooms;
        break;
case 'export'; //file as TXT :
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");                          // HTTP/1.0
        header("Content-Type: text/plain");
        header("Content-Length: ".filesize($chat_log_file)); //?
        header("Content-Disposition: attachment;
                filename=chat_course_".$id_course."_class_".$id_instance.".html");
        echo $user_chat_report;
//              header ("Connection: close");
        exit;
        break;
case 'exportTable': // XLS-like
            $chat_report ="";

            if (!isset($id_chatroom)) // ???
                    if (isset($id_instance))
                            $id_chatroom = $id_instance;
                    elseif (isset($sess_id_course_instance))
                            $id_chatroom = $sess_id_course_instance;
            $mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
            $chat_data = $mh->find_chat_messages($sess_user_id, ADA_MSG_CHAT, $id_chatroom, $fields_list="", $clause="", $ordering="");
            if (is_array($chat_data)){
                    $chat_dataAr = array();
                    $c=0;
                    $tbody_data = array();
                    $export_log = translateFN('Data e ora') . ';'. translateFN('Utente') .';'. translateFN('Messaggio') . PHP_EOL;
                    foreach ($chat_data as $chat_msgAr){
                        if (is_numeric($chat_msgAr[0])) {
                            $sender_dataHa = $dh->_get_user_info($chat_msgAr[0]);

                            $user = $sender_dataHa['nome'] . ' ' . $sender_dataHa['cognome'];
                            $message = $chat_msgAr[1];
                            $data_ora = ts2dFN($chat_msgAr[2])." ".ts2tmFN($chat_msgAr[2]);
                            /*
                             *
                            $row = array(
                            translateFN('Data e ora')=>$data_ora,
                            translateFN('Utente')=>$user,
                            translateFN('Messaggio')=>strip_tags($message)
                            );
                             */
                            $export_log .= $data_ora . ';'. $user .';'. strip_tags($message) . PHP_EOL;
                            //array_push($chat_dataAr,$row);
                            $chat_report.= "$data_ora $user: $message<br/>PHP_EOL";
                            $c++;
                        }
                    }
            //}
                    $user_chat_report = translateFN("Totale messaggi:")." ".$c."<br />";
                    $user_chat_report .= translateFN("Ultimo messaggio:")." ". $data_ora."<br />";
                    $user_chat_report .= translateFN("Utenti / messaggi:")."<br /><br />";
                    $user_chat_report .=$chat_report;

            } else {
                    $export_log = translateFN("Nessuna chat disponibile.");
            }

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");                          // HTTP/1.0
        header("Content-Type: text/plain");
//        header("Content-Type: application/vnd.ms-excel");
//              header("Content-Length: ".filesize($chat_log_file)); //?
        $course_title .= ' - '. translateFN('id classe') .': '. $id_course_instance;
        header("Content-Disposition: attachment; filename=class_".$id_course_instance.'_chat_'.$id_chatroom.".csv");
        echo $export_log;
//              header ("Connection: close");
        exit;
        break;
default:
}
$help = translateFN("Questa &egrave; il report della chat di classe");


  if (!isset($course_title)) {
      $course_title = "";
  } else {
      $course_title .= ' - '. translateFN('id classe') .': '. $id_course_instance;
  }
  if (!isset($status))
       $status = "";

$chatrooms_link = '<a href="'.HTTP_ROOT_DIR . '/comunica/list_chatrooms.php">'. translateFN('Lista chatrooms');

  $content_dataAr = array(
                 'banner'=> $banner,
                 'course_title'=>  translateFN('Report della chat'). ' - ' . translateFN('Corso') .': '.$course_title,
                 'home'=> isset($homepage) ? "<a href=\"$homepage\">home</a>" : '',
                 'user_name'=>$user_name,
                 'user_type'=>$user_type,
                 'level'=>$user_level,
                 'help'=>$help,
                 'data'=>$tabled_chat_dataHa,
                 'status'=>$status,
                 'chatrooms'=>$chatrooms_link,
                 'chat_users'=>isset($online_users) ? $online_users : '',
                 'messages'=>isset($user_messages) ? $user_messages : '',
                 'agenda'=>isset($user_agenda) ? $user_agenda : ''
                );

ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,isset($menuOptions) ? $menuOptions : null);
