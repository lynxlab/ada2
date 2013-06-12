<?php
/**
 * VIEW.
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
        AMA_TYPE_VISITOR => array('node','layout','course'),
        AMA_TYPE_STUDENT => array('node','layout','tutor','course','course_instance'),
        AMA_TYPE_TUTOR   => array('node','layout','course','course_instance'),
        AMA_TYPE_AUTHOR  => array('node','layout','course')
);

//FIXME: course_instance is needed by videochat BUT not for guest user


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';

include_once 'include/browsing_functions.inc.php';

if($userObj instanceof ADAGuest) {
    $self = 'guest_view';
}
else {
    $self = whoami();
}


require_once 'include/graph_map.inc.php';


$node_map = $nodeObj->graph_indexFN();
$node_title = $nodeObj->name;

$image_map = make_image_mapFN($node_map,$user_level,$id_profile);
$alternate = translateFN("mappa del gruppo");

$_SESSION['sess_user_level'] = $user_level;
$_SESSION['sess_node_map'] = $node_map;
$_SESSION['sess_template'] = $layout_template;

$src_map = "<img src=\"make_map.php\" alt=\"$alternate\"  border=\"0\" usemap=\"#Map\">\n";

$src_map .= $image_map;
$view_back = "<A HREF = \"view.php?id_node=$sess_id_node\">" . translateFN("vedi scheda") . "</A>";

/* Dynamic Links */

// history
switch ($id_profile){
  case AMA_TYPE_STUDENT:
    $go_history = "<a href=\"history.php\">".translateFN("cronologia")."</a>";
    break;
  default:
    $go_history = translateFN("cronologia");
}

// Who's online

// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users
$online_users_listing_mode = 2;
$online_users = ADAGenericUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


/* 2. Operations
 Allowed values: edit, bookmark, print, link, map (=default)
 Implemented: map, print, bookmark
 */

if (!isset($op)) {
  $op='map';
}

switch ($op){
  case 'bookmark':
    $bookmarkObj = new Bookmark();
    $bookmark_error = $bookmarkObj->set_bookmark($sess_id_user, $sess_id_node, $node_title);
    if (!empty($bookmark_error)){
      $user_messages.= $bookmark_error;
    } else {
      $id_bk = $bookmarkObj->id;
      $add_bookmark = "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img src=\"img/check.gif\" alt=\"".translateFN("Vai al segnalibro")."\" border=0></a>";
    }

    $content_dataAr = array(
                   'course_title'=>$course_title,
                   'user_name'=>$user_name,
                   'user_type'=>$user_type,
                   'user_level'=>$user_level,
                   'path'=>$node_path,
                   'map'=>$src_map,
                   'title'=>$node_title,
                   'view'=>$view_back
    );

    break;
  case 'print':
    $layoutObj = read_layout_from_DB($id_profile,$template_family,'print');
    $layout_CSS = $layoutObj->CSS_filename;
    $layout_template = $layoutObj->template;

    $printbutton = "<form>
            <input type=\"button\" value=\"".translateFN("Stampa")."\" name=\"Print\" onClick=\"printit()\">
            </form>";

    $printcode = "<SCRIPT Language=\"Javascript\" type=\"text/javascript\">";
    /* moved to view.js
     $printcode .= "function printit() {
     if (NS) {
     window.print() ;
     } else {
     var WebBrowser = '<OBJECT ID=\"WebBrowser1\" WIDTH=\"0\" HEIGHT=\"0\" CLASSID=\"CLSID:8856F961-340A-11D0-A96B-00C04FD705A2\"></OBJECT>';
     document.body.insertAdjacentHTML('beforeEnd', WebBrowser);
     WebBrowser1.ExecWB(6, 2);
     WebBrowser1.outerHTML = \"\";
     }
     }
     */
    $printcode .= "var NS = (navigator.appName == \"Netscape\");\n  var VERSION = parseInt(navigator.appVersion);\n";
    $printcode .= " </SCRIPT>\n";
    $content_dataAr = array(
                   'printcode'=>$printcode,
                   'printbutton'=>$printbutton,
                   'user_name'=>$user_name,
                   'user_type'=>$user_type,
                   'course_title'=>$course_title,
                   'level'=>$user_level,
                   'map'=>$src_map,
                   'path'=>$node_path,
                   'title'=>$node_title,
                   'author'=>$node_author,
                   'text'=>$data['text']
    );
    break;
  case 'map':
  default:
    // map
    // Add to the History only Student activity

    if   (
    (($id_profile == AMA_TYPE_STUDENT) AND ($reg_enabled))
    OR ($id_profile == AMA_TYPE_TUTOR)
    AND (!empty($sess_id_course_instance))

    ) {
      $dh->add_node_history($sess_id_user, $sess_id_course_instance, $sess_id_node); # Andrebbe aggiunta la modalit di visualizzazione (mappa, testo, etc.)
    }

    if (($id_profile == AMA_TYPE_STUDENT)  OR ($id_profile == AMA_TYPE_TUTOR)) {
      $add_bookmark = "<a href=\"map.php?id_node=" . $sess_id_node . "&op=bookmark\">"  . translateFN("aggiungi al segnalibri") . "</A>";
    } else {
      $add_bookmark = "";
    }


    if (($id_profile == AMA_TYPE_STUDENT)  OR ($id_profile == AMA_TYPE_TUTOR)){
      $chat_link = "<a href=\"$http_root_dir/comunica/ada_chat.php\" target=_blank>".translateFN("chat")."</a>";
    } else {
      $chat_link = "";
    }

    //

 
    $content_dataAr = array(
                   'course_title'=>'<a href="main_index.php">'.$course_title.'</a>',
                   'user_name'=>$user_name,
                   'user_type'=>$user_type,
                   'user_level'=>$user_level,
                   'path'=>$node_path,
                   'map'=>$src_map,
                   'title'=>$node_title,
                   'view'=>$view_back
    );



}
if ($reg_enabled){
  $content_dataAr['go_history']=$go_history;
  $content_dataAr['add_bookmark']=$add_bookmark;
  $content_dataAr['go_bookmarks_1']=$go_bookmarks;
  $content_dataAr['go_bookmarks_2']=$go_bookmarks;
} else {
  $content_dataAr['go_history']=translateFN("cronologia");
  $content_dataAr['add_bookmark']="";
  $content_dataAr['go_bookmarks_1']=translateFN("segnalibri");
  $content_dataAr['go_bookmarks_2']=translateFN("segnalibri");
}

if ($com_enabled){
  $content_dataAr['chat_link']=$chat_link;
  $content_dataAr['messages'] = $user_messages->getHtml();
  $content_dataAr['agenda']= $user_agenda->getHtml();
  $content_dataAr['chat_users']=$online_users;
} else {
  $content_dataAr['chat_link']=translateFN("chat");
  $content_dataAr['messages'] = translateFN("messaggeria non abilitata");
  $content_dataAr['agenda']=translateFN("agenda non abilitata");
  $content_dataAr['chat_users']="";
}

ARE::render($layout_dataAr, $content_dataAr);