<?php
/**
 * AUTHOR.
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
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = author!

include_once 'include/'.$self.'_functions.inc.php';

/*
 * YOUR CODE HERE
 */
$sess_id_user            = isset($_SESSION['sess_id_user']) ? $_SESSION['sess_id_user'] : null;
$sess_id_course          = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
$sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;


if (!isset($msg)) {
  $msg = translateFN("pronto");
}

$help = translateFN("Da qui l'autore pu&ograve; vedere vedere un report generale sui suoi corsi, modificare un corso oppure aggiungerne un nuovo.
");

// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

// FIXME: servono gli utenti online in questo modulo?
//$online_users_listing_mode = 2;
//$online_users = ADAAuthor::get_online_usersFN($id_course_instance,$online_users_listing_mode);

// find all course available

$field_list_ar = array('nome','titolo','data_creazione','media_path','id_nodo_iniziale');
$key = $sess_id_user;
$search_fields_ar = array('id_utente_autore');
$dataHa = $dh->find_courses_list_by_key($field_list_ar, $key, $search_fields_ar);

if (AMA_DataHandler::isError($dataHa)){
  /*
   * Qui, se codice di errore == AMA_ERR_NOT_FOUND, tutto ok, semplicemente non
   * ci sono corsi.
   * Altrimenti ADA_Error
   */
   $err_msg = $dataHa->getMessage();
  //header("Location: $error?err_msg=$msg");
}
else {
  // courses array
  $course_dataHa = array();

  foreach($dataHa as $course){
    // mydebug(__LINE__,__FILE__,array('Course'=>$course[1]));
    $id_course = $course[0];
    $nome = $course[1];
    $titolo = $course[2];
    $data = ts2dFN($course[3]);
    $media_path =  $course[4];
    if (!$media_path) {
      $media_path = translateFN("default");
    }
    $id_nodo_iniziale = $course[5];

    // vito, 8 apr 2009
    $confirm_dialog_message = translateFN('Sei sicuro di voler eliminare questo corso?');
    $onclick = "confirmCriticalOperationBeforeRedirect('$confirm_dialog_message','delete_course.php?id_course=$id_course');";

    $row = array(
      translateFN('Nome')=>$nome,
      translateFN('Titolo')=>$titolo,
      translateFN('Data')=>$data,
      translateFN('Path')=>$media_path,
      translateFN('Naviga')=> "<a href=\"../browsing/view.php?id_course=$id_course&id_node=".$id_course."_".$id_nodo_iniziale."\"><img src=\"img/timon.png\" border=0></a>",
      translateFN('Report')=> "<a href=\"author_report.php?id_course=$id_course\"><img src=\"img/report.png\" border=0></a>",
      translateFN('Aggiungi')=> "<a href=\"addnode.php?id_course=$id_course\"><img src=\"img/_nodo.png\" border=0></a>",
      //translateFN('XML')=> "<a href=\"author_report.php?mode=xml&amp;id_course=$id_course\"><img src=\"img/xml.png\" border=0></a>",
      //translateFN('Elimina')=> "<a href=\"#\" onclick=\"$onclick\"><img src=\"img/delete.png\" border=0></a>"
    );
    if (defined('MODULES_SLIDEIMPORT') && MODULES_SLIDEIMPORT) {
    	$row[translateFN('Importa')] = "<a href=\"".MODULES_SLIDEIMPORT_HTTP."/?id_course=$id_course\"><img src=\"".MODULES_SLIDEIMPORT_HTTP."/layout/img/slideimport.png\" border=0></a>";
    }
    array_push($course_dataHa,$row);
  }
  $caption = translateFN("Corsi inviati e attivi il")." $ymdhms";
  $tObj = BaseHtmlLib::tableElement('id:authorTable, class:doDataTable',array_keys(reset($course_dataHa)),$course_dataHa,null,$caption);
  $tObj->setAttribute('class', 'default_table doDataTable');
  $total_course_data = $tObj->getHtml();
  $optionsAr['onload_func'] = 'initDoc();';
  $layout_dataAr['CSS_filename'] = array (
  		JQUERY_UI_CSS,
  		JQUERY_DATATABLE_CSS,
  );
  $layout_dataAr['JS_filename'] = array(
  		JQUERY,
  		JQUERY_UI,
  		JQUERY_DATATABLE,
  		JQUERY_DATATABLE_DATE,
  		JQUERY_NO_CONFLICT
  );
}

if (isset($err_msg)) {
  $total_course_data = translateFN("Nessun corso assegnato all'autore.");
}
// menu' table


$lObj = new Ilist() ;
$data = array (
    crea_link(translateFN('report'),'author_report.php'),
//    crea_link('crea corso vuoto','add_course.php'),
//    crea_link("crea corso da modello","add_course.php?modello=1"),
    //crea_link("invia corso","../upload_file/upload_xml_file_form.php"),
    //crea_link("nuovo nodo","addnode.php"),
    crea_link(translateFN('modifica il tuo profilo'),"edit_author.php?id=$sess_id_user"),
  );

$lObj->setList($data);
$menu_ha = $lObj->getList();

//$banner = include ("$root_dir/include/banner.inc.php");

$title = translateFN('Home Autore');

//if (empty($user_messages)) {
//  $user_messages = translateFN('Non ci sono nuovi messaggi');
//}
// SERVICE:  BANNER
$banner = include ROOT_DIR.'/include/banner.inc.php';

$content_dataAr = array(
  //        'form'=>$menu_ha,
  'course_title' => translateFN('Lista dei servizi'),
  'banner'       => $banner,
  'menu'         => $menu_ha,
  'status'       => $msg,
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'help'         => $help,
  'form'         => $total_course_data,
  'edit_profile' => $userObj->getEditProfilePage(),
  'agenda'       => $user_agenda->getHtml(),
  'messages'     => $user_messages->getHtml()
);
/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, null, (isset($optionsAr) ? $optionsAr : null));