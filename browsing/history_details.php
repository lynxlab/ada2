<?php
//
// +----------------------------------------------------------------------+
// | ADA version 1.8                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 Lynx                                              |
// +----------------------------------------------------------------------+
// |                                                                      |
// |                          H I S T O R Y                                |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Marco Benini                                                 |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: history.php,v 1.0 2001/06/20
//
//

/* 0.
Initializating variables and including modules

*/


$ada_config_path = realpath(dirname(__FILE__).'/..');

$history = "" ;

include_once('include/includes.inc.php');
$self = "history";


 /*****************************************************************
 // Include moduli di comunicazione
 */
 // include UserDataHandler
include_once("$root_dir/comunica/include/UserDataHandler.inc.php");
 // include Message Handler
include_once("$root_dir/comunica/include/MessageHandler.inc.php");
 /*****************************************************************/



session_controlFN();


// ******************************************************
// Clear node and layout variable
$whatAR = array();
array_push($whatAR, 'node');
array_push($whatAR, 'layout');
array_push($whatAR, 'user');
array_push($whatAR, 'course');
clear_data($whatAR);

$sess_id_course = $_SESSION['sess_id_course'];
$sess_id_course_instance = $_SESSION['sess_id_course_instance'];
$sess_id_user = $_SESSION['sess_id_user'];

//import_request_variables("gP","");
extract($_GET,EXTR_OVERWRITE,ADA_GP_VARIABLES_PREFIX);
extract($_POST,EXTR_OVERWRITE,ADA_GP_VARIABLES_PREFIX);

// ******************************************************
if($sess_id_course){
    // get object course
    $courseObj = read_course_from_DB($sess_id_course);
    if ($dh->isError($courseObj)){
       $errObj = $courseObj;
       $msg =   $errObj->errorMessage();
       header("Location:$error_page?err_msg=$msg");
       exit;
    } else {
        $course_title = $courseObj->titolo; //title
        $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
        $course_family = $courseObj->template_family;
    }    
} else {
     $errObj = new ADA_error(translateFN("Corso non trovato"),translateFN("Impossibile proseguire."));
}




// ******************************************************
// get user object
$userObj = read_user_from_DB($sess_id_user);
if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {

       $id_profile = $userObj->tipo;
       $user_name =  $userObj->username;
       $user_type = $userObj->convertUserTypeFN($id_profile);
       $user_historyObj = $userObj->history;
       $user_level = $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
       $user_family = $userObj->template_family;

}  else {
     $errObj = new ADA_error(translateFN("Utente non trovato"),translateFN("Impossibile proseguire."));
}

// ******************************************************
// LAYOUT

if ((isset($family))  and (!empty($family))){ // from GET parameters
	$template_family = $family; 
} elseif ((isset($node_family))  and (!empty($node_family))){ // from node definition
	$template_family = $node_family; 
} elseif ((isset($course_family))  and (!empty($course_family))){ // from course definition
	$template_family = $course_family; 
} elseif ((isset($user_family)) and (!empty($user_family))) { // from user's profile
    $template_family = $user_family; 
} else {
    $template_family = ADA_TEMPLATE_FAMILY; // default template famliy
}		

$layoutObj = read_layout_from_DB($id_profile,$template_family);
$layout_CSS = $layoutObj->CSS_filename;
$layout_template = $layoutObj->template;

// END LAYOUT
// *****************************************************



/* 1.
Retrieving node's data filtered by user'properties

*/

// lettura dei dati dal database
//$userObj->get_history_dataFN($id_course_instance) ;


if($period!="all"){
    // Nodi visitati negli ultimi n giorni. Periodo in giorni.
    $history .= "<p>";
//    $history .= translateFN("Nodi visitati negli ultimi $period giorni:") ;
    $history .= $user_historyObj->history_nodes_list_filtered_FN($period) ;
    $history .= "</p>";
}else{
// Full history
    $history .= "<p>";
//    $history .= translateFN("Cronologia completa:") ;
    $history .= $user_historyObj->get_historyFN() ;
    $history .= "</p>";
}


// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
$online_users = User::get_online_usersFN($id_course_instance,$online_users_listing_mode);


$last_visited_node_id = $userObj->get_last_accessFN($sess_id_course_instance,N);
if  (!empty($last_visited_node_id)){
           $last_node = $dh->get_node_info($last_visited_node_id);
           $last_visited_node_name = $last_node['name'];
           $last_node_visited = "<a href=view.php?id_node=$last_visited_node_id>".translateFN("torna")."</a>";
} else {
           $last_node_visited = "";
}

// Menu nodi visitati per periodo
$menu = menu_detailsFN();
$menu .= "<a href=history.php>".translateFN("cronologia")."</a><br>";
$menu.= $last_node_visited;


/* 2.
getting todate-information on user
MESSAGES adn EVENTS
*/

if (is_object($userObj)){
    if (empty($userObj->error_msg)){
        $user_messages = $userObj->get_messagesFN($sess_id_user);
        $user_agenda =  $userObj->get_agendaFN($sess_id_user);
    } else {
        $user_messages =  $userObj->error_msg;
        $user_agenda = translateFN("Nessun'informazione");

    }
} else {
  $user_messages = $userObj;
  $user_agenda = "";
}


// CHAT, BANNER etc

$banner = include ("$root_dir/include/banner.inc.php");
// $chat_link = "<a href=\"$http_root_dir/chat/chat/index.php3?L=italian&Ver=H&U=" . $user_name . "&PWD_Hash=d41d8cd98f00b204e9800998ecf8427e&R='$course_title'&T=2&D=5&N=20&Reload=NNResize&frameset=fol\" target=_blank>".translateFN("chat")."</a>";

// Costruzione del link per la chat.
// per la creazione della stanza prende solo la prima parola del corso (se piu' breve di 24 caratteri)
// e ci aggiunge l'id dell'istanza corso
$char_num = strpos(trim($course_title), " ");
if ($char_num > 24) $char_num = 24;
$tmp = substr(trim($course_title), 0, $char_num);
$stanza = urlencode(trim($tmp) . "_" . $sess_id_course_instance);
$chat_link = "<a href=\"$http_root_dir/chat/chat/index.php3?L=italian&Ver=H&U=" . $user_name . "&PWD_Hash=d41de&R=$stanza&T=2&D=5&N=20&Reload=NNResize&frameset=fol\" target=_blank>".translateFN("chat")."</a>";

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
  

/* 3.
HTML page building
*/


$htmlObj = new HTML($layout_template,$layout_CSS,$course_title,$node_title);

$node_data = array(


                   'chat_link'=>$chat_link,
                   'banner'=> $banner,
                   'course_title'=>'<a href="main_index.php">'.$course_title.'</a>',
                   'menu'=>$menu,
                   'user_name'=>$user_name,
                   'user_type'=>$user_type,
                   'status'=>$status,
                   'level'=>$user_level,
                   'path'=>$node_path,
                   'history'=>$history,
                   'last_visit' => $last_access,
                   'messages'=>$user_messages,
                   'agenda'=>$user_agenda,
                   'chat_users'=>$online_users
                  );


$htmlObj->fillin_templateFN($node_data);

$imgpath = (dirname($layout_template));
$htmlObj-> resetImgSrcFN($imgpath);

$htmlObj->apply_CSSFN();



/* 5.
sending all the stuff to the  browser
*/

$htmlObj->outputFN('page');

/* FUNCTIONS */

function menu_detailsFN(){
$menu_history = translateFN("Nodi visitati recentemente:")."<br>\n";
$menu_history .= "<a href=\"history_details.php?period=1\">".translateFN("1 giorno")."</a><br>\n";
$menu_history .= "<a href=\"history_details.php?period=5\">".translateFN("5 giorni")."</a><br>\n";
$menu_history .= "<a href=\"history_details.php?period=15\">".translateFN("15 giorni")."</a><br>\n";
$menu_history .= "<a href=\"history_details.php?period=30\">".translateFN("30 giorni")."</a><br>\n";
$menu_history .= "<a href=\"history_details.php?period=all\">".translateFN("tutti")."</a><br>\n";
$menu_history .="<br>";
return $menu_history;
}


?>