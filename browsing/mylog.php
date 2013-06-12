<?php
/**
 * mylog - this module provides management of a personal diary
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright		Copyright (c) 2009-2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
    AMA_TYPE_AUTHOR => array('node', 'layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/browsing_functions.inc.php';

$debug = 0; 
$mylog_mode = 0; // default: only one file for user
//$log_extension = ".txt";	
$log_extension = ".htm";	

$self =  whoami();  // = mylog

//$classi_dichiarate = get_declared_classes();
//mydebug(__LINE__,__FILE__,$classi_dichiarate);

$ymdhms = today_dateFN();

import_request_variables("gP","");

// ******************************************************
$reg_enabled = TRUE; // link to edit bookmarks
$log_enabled = TRUE; // link to history 
$mod_enabled = TRUE; // link to modify nod/tes
$com_enabled = TRUE;  // link to comunicate among users
// Get user object
$userObj = read_user_from_DB($sess_id_user);
//print_r($userObj);
if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {
     $id_profile = $userObj->tipo;
       switch ($id_profile){
        case AMA_TYPE_TUTOR:
        case AMA_TYPE_STUDENT:
        case AMA_TYPE_AUTHOR:
           break;
        case AMA_TYPE_ADMIN:
            $homepage = $http_root_dir . "/browsing/student.php"; 
            $msg =   urlencode(translateFN("Ridirezionamento automatico"));
            header("Location: $homepage?err_msg=$msg");
            exit;
            break;
        }
        $user_type = $userObj->convertUserTypeFN($id_profile);
        $user_name =  $userObj->username;
        $user_family = $userObj->template_family; 
} else {
$errObj = new ADA_error(translateFN("Utente non trovato"),translateFN("Impossibile proseguire."));
}

// set the  title:	 
$module_title = translateFN("Diario");

// building file name
// rootdir  + media path + author_id + filename
$public_dir = "/services/media/";
// a public access directory where log files can be written
// building file name

if (isset($sess_id_course) and  (!($sess_id_course==""))) {
// finding course's author
	$course_ha = $dh->get_course($sess_id_course);
	if (AMA_DataHandler::isError($course_ha)){ // not enrolled yet?
        	$msg = $course_ha->getMessage();
        	header("Location: " . $http_root_dir . "/browsing/student.php?status=$msg");
	}
	// look for the author, starting from author's id
	$author_id = $course_ha['id_autore'];
	if ($mylog_mode == 1){
		// a log file for every instance of course in which user is enrolled in:
		// id_course_instance + user_id 
		$name_tmp = 'log_'.$sess_id_course_instance . "_" . $sess_id_user . $log_extension;	
	} else { // default
		// only 1 log file for user:
		$name_tmp = 'log_'.$sess_id_user.$log_extension; 
	}

	$logfile = $root_dir . "/services/media/" . $author_id . "/" . $name_tmp;
} else {
	$logfile = $root_dir . $public_dir . "log".$sess_id_user.$log_extension;
}

if (!file_exists($logfile))
	$fp = fopen($logfile,'w');

//set the  body:

if (isset($_POST['Submit']))
{
    
    if (isset($_POST['log_today']))
    {
       $log = $_POST['log_text']."<br/>".$_POST['log_today'];
       $i = fopen($logfile,'w');
       if (get_magic_quotes_gpc()) {
	       $res = fwrite($i,stripslashes($log));
	}else{
	       $res = fwrite($i,$log);
	}
       $res = fclose($i);
   }
    $status = translateFN("Le informazioni sono state registrate.");
}
// } else {
                
if ($fp = fopen($logfile,'r'))
	$log_text = fread ($fp,16000);
else
	$log_text = "";
fclose($fp);
if (isset($op) && ($op=="export")){
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");                          // HTTP/1.0
    //header("Content-Type: text/plain");
    header("Content-Type: text/html");
    //header("Content-Length: ".filesize($name));
    header("Content-Disposition: attachment; filename=$name_tmp");
    echo $log_text;
    exit;
} else {
    $date = today_dateFN()." ".today_timeFN()."\n";
    $log_form = new Form();
    $log_data= array(
    array(
        'label'=>"",
        'type'=>'textarea',
        'name'=>'log_today',
        'rows'=>'10',
        'cols'=>'80',
        'wrap'=>'virtual',
	'value'=>$date
        ),
    array(
        'label'=>"",
        'type'=>'hidden',
        'name'=>'log_text',
        'value'=>$log_text
        ),
    array(
        'label'=>'',
        'type'=>'submit',
        'name'=>'Submit',
        'value'=>'Salva'
        )
    );

   $log_form->initForm("$http_root_dir/browsing/mylog.php","POST","multipart/form-data");
   $log_form->setForm($log_data);
   $log_data = $log_form->getForm();
   $log_data.= $log_text;
}

$export_log_link = "<a href=$http_root_dir/browsing/mylog.php?op=export>".translateFN("Esporta")."</a><br/>";

// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


/*
 $online_users_listing_mode = 0;

// vito 19 gennaio 2009
//$online_users = User::get_online_usersFN($id_course_instance,$online_users_listing_mode);
if(isset($sess_id_course_instance) && !empty($sess_id_course_instance)) {
  $online_users = User::get_online_usersFN($sess_id_course_instance,$online_users_listing_mode);
}
else {
  $online_users = '';
}
*/

$menu = $export_log_link;
// vito 19 gennaio 2009
if(isset($sess_id_course_instance) && !empty($sess_id_course_instance)) {
  $last_visited_node_id = $userObj->get_last_accessFN($sess_id_course_instance,"N");
  $node_path = $nodeObj->findPathFN();
}
else {
  $last_visited_node_id = '';
}
if  (!empty($last_visited_node_id)){
           $last_node = $dh->get_node_info($last_visited_node_id);
           $last_visited_node_name = $last_node['name'];
           $last_node_visited = "<a href=view.php?id_node=$last_visited_node_id>".translateFN("torna")."</a><br>";
} else {
           $last_node_visited = "";
}


$menu.= $last_node_visited;


$help = translateFN("Nel Diario si possono inserire i propri commenti privati, oppure esportarli per conservarli.");
// CHAT, BANNER etc

$banner = include ("$root_dir/include/banner.inc.php");

$chat_link = "<a href=\"$http_root_dir/comunica/ada_chat.php\" target=_blank>".translateFN("chat")."</a>";
//


/* 3.
HTML page building
*/

/*
  'user_name'=>$user_name,
  'user_type'=>$user_type,
  'level'=>$user_level,
  'index'=>$node_index,
  'title'=>$node_title,
  'author'=>$node_author,
  'text'=>$data['text'],
  'link'=>$data['link'],
  'messages'=>$user_messages->getHtml(),
  'agenda'=>$user_agenda->getHtml(),
  'events'=>$user_events->getHtml(),
  'chat_users'=>$online_users

*/

         $body_onload = "includeFCKeditor('log_today');";
         $options = array('onload_func' => $body_onload);

$node_data = array(
                   'banner'=>$banner,
                   'course_title'=>'<a href="main_index.php">'.$course_title.'</a>',
                   'today'=>$ymdhms,
                   'path'=>$node_path,
                   'user_name'=>$user_name,
                   'user_type'=>$user_type,
                   'user_level'=>$user_level,
                   'last_visit'=>$last_access_date,
                   'data'=>$log_data,
		   'menu'=>$menu,
		   'help'=>$help,
                   'bookmarks'=>$user_bookmarks,
                   'profilo'=>$profilo,
                   'myforum'=>$my_forum,
                   'title'=>$node_title
                   //'mylog'=>$mylog,
                  );

                   if ($com_enabled){
                       $node_data['chat_link']=$chat_link;
		       $node_data['mychat'] =$my_chat;
    		       $node_data['myforum'] =$my_forum;
                       $node_data['messages']=$user_messages->getHtml();
                       $node_data['agenda']=$user_agenda->getHtml();
                       $node_data['events']=$user_events->getHtml();
                       $node_data['chat_users']=$online_users;
                   } else {
                       $node_data['chat_link']=translateFN("chat");
 		       $node_data['mychat'] ="";
		       $node_data['myforum'] ="";
                       $node_data['messages'] = translateFN("messaggeria non abilitata");
                       $node_data['agenda']=translateFN("agenda non abilitata");
                       $node_data['chat_users']="";
                   }


ARE::render($layout_dataAr,$node_data, NULL, $options);

/* Versione XML:

 $xmlObj = new XML($layout_template,$layout_CSS,$imgpath);
 $xmlObj->fillin_templateFN($node_data);
 $xmlObj->outputFN('page','XML');

*/

?>