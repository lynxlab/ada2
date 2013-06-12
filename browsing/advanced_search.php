<?php
/**
 * SEARCH.
 *
 * @package		browsing
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		search
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
  AMA_TYPE_VISITOR      => array('layout'),
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_AUTHOR       => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';

include_once 'include/browsing_functions.inc.php';

require_once ROOT_DIR.'/include/HTML_element_classes.inc.php';


/* if($userObj instanceof ADAGuest) {
 $self = 'guest_view'; FIXME: we have to create a guest_search template
}
else { */
//  $self = whoami();
  $self = search;
/*} */


/*
 * Versione campo unico
 *
 *
 */
if (isset($submit)) {  //&& (!empty($s_node_text))) {
  // versione con campo unico
  $out_fields_ar = array('nome','titolo','testo','tipo');
  $clause='';
  $or = ' OR ';
  $and = ' AND ';

  if (!empty($s_node_text)) {
    $clause = "(";
    $clause = $clause . "nome LIKE '%$s_node_text%'";
    $clause = $clause . $or. "titolo LIKE '%$s_node_text%'";
    $clause = $clause . $or. "testo LIKE '%$s_node_text%'";
    $clause = $clause . ")";
  }
  else {
    $s_node_text = ""; 
  }
 

  //versione con campi diversi

  $out_fields_ar = array('nome','titolo','testo','tipo','id_utente');
  $clause='';
  $or = '';
  $and = '';

  if (!empty($s_node_name)) {
    $clause = "nome LIKE '%$s_node_name%'";
  }

  if (!empty($s_node_title)){
    if ($clause) {
      $and = " AND ";
    }
    $clause = $clause . $and. "titolo LIKE '%$s_node_title%'";
  }

  if (!empty($s_node_text)){
    if ($clause) {
      $and = " AND ";
    }
    $clause = $clause . $and. "testo LIKE '%$s_node_text%'";
  }
  else {
    $s_node_text = "";
  }

  // authors

  if (!empty($s_node_author)){
    $s_node_authorAR = explode(' ',$s_node_author);
    $node_author_name = $s_node_authorAR[0];
    $auth_clause = "nome LIKE '$node_author_name' ";
    if (count($s_node_authorAR)>1){ //name & surname
      $node_author_surname = $s_node_authorAR[1];
      $auth_clause .= "AND cognome LIKE '$node_author_surname' ";
    }
    else {
      $auth_clause .= "OR cognome LIKE '$s_node_author' ";
      $auth_clause .= "OR username LIKE '$s_node_author' ";
    }

    $auth_field_list_ar = array('username');
    $authorAR = $dh->find_authors_list($auth_field_list_ar,$auth_clause);
    if (is_object($authorAR) OR (!isset($authorAR)) ) {//error
      $id_author = 'nobody';
    }
    else {
      $id_author =  $authorAR[0][0]; // id
    }
    if ($clause) {
      $and = " AND ";
    }
    $clause = $clause . $and. "id_utente LIKE '$id_author'";
  }

  // node types

  $clause = $clause . $and. " (tipo  = ".ADA_GROUP_TYPE."  OR tipo  = ".ADA_LEAF_TYPE.")";

  if (isset($l_search)){
    switch ($l_search){
      case 'standard_node':  // group OR nodes NOT notes
        if ($clause) {
          $and = " AND ";
        }
        $clause = $clause . $and. " (tipo  = ".ADA_GROUP_TYPE."  OR tipo  = ".ADA_LEAF_TYPE.")";
        $checked_standard = "checked";
        break;

      case 'group':
      case ADA_GROUP_TYPE:
        $type = ADA_GROUP_TYPE;
        if ($clause) {
          $and = " AND ";
        }
        $clause = $clause . $and. "tipo = '$type'";
        $checked_group = "checked";
        break;

      case 'node':
//      case ADA_LEAF_TYPE:
        $type = ADA_LEAF_TYPE;
        if ($clause) {
          $and = " AND ";
        }
        $clause = $clause . $and. "tipo = '$type'";
        $checked_node = "checked";
        break;

      case 'note':
      case ADA_NOTE_TYPE:
        $type = ADA_NOTE_TYPE;
        if ($clause) {
          $and = " AND ";
        }
        $clause = $clause . $and. "tipo = '$type'";
        $checked_note = "checked";
        break;

      case 'private_note':
      case ADA_PRIVATE_NOTE_TYPE:
        $type = ADA_PRIVATE_NOTE_TYPE;
        if ($clause) {
          $and = " AND ";
        }
        // vito, 16 giugno 2009, vogliamo che l'utente veda tra i risultati della
        // ricerca eventualmente solo le SUE note personali e non quelle di
        // altri utenti.

        //$clause = $clause . $and. "tipo = '$type'";
        $clause = $clause . $and . "(tipo = '$type' and id_utente='$sess_id_user')";
        $checked_note = "checked";
        break;

      case '':
      default:
      case 'all': // group OR nodes OR notes
        $checked_all = "checked";
        // vito, 16 giugno 2009, vogliamo che l'utente veda tra i risultati della
        // ricerca eventualmente solo le SUE note personali e non quelle di
        // altri utenti.
        if ($clause) {
          $and = " AND ";
        }
        $clause = $clause.$and.' ((tipo <> '.ADA_PRIVATE_NOTE_TYPE.') OR (tipo ='.ADA_PRIVATE_NOTE_TYPE.' AND id_utente = '.$sess_id_user.'))';
        break;

    }
  }

  /* ricerca su tutti i corsi pubblici
   * if (il tester è quello pubblico){
   *    $resHa = $dh->find_public_course_nodes_list($out_fields_ar, $clause,$sess_id_course);
   * }
   */

  // $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$sess_id_course);
  $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$_SESSION['sess_id_course']);

if (!AMA_DataHandler::isError($resHa) and is_array($resHa)) {
//    if ($resHa){
    $total_results = array();
    $group_count=0;
    $node_count=0;
    $note_count=0;
    $exer_count=0;

    foreach ($resHa as $row){
      $res_id_node = $row[0];
      $res_name = $row[1];
      $res_course_title = $row[2];
      $res_text = $row[3];

      $res_type =  $row[4];

      switch ($res_type){
        case ADA_GROUP_TYPE:
          //$icon = "<img src=\"img/group_ico.png\" border=0>";
          $class_name = 'ADA_GROUP_TYPE';
          $group_count++;
          break;
      case ADA_LEAF_TYPE:
          //$icon = "<img src=\"img/node_ico.png\" border=0>";
          $class_name = 'ADA_LEAF_TYPE';
          $node_count++;
          break;

        case ADA_NOTE_TYPE:
          //$icon = "<img src=\"img/note_ico.png\" border=0>";
          $class_name = 'ADA_NOTE_TYPE';
          $note_count++;
          break;

        case ADA_PRIVATE_NOTE_TYPE:
          //$icon = "<img src=\"img/_nota_pers.png\" border=0>";
          $class_name = 'ADA_PRIVATE_NOTE_TYPE';
          $note_count++;
          break;


        case ADA_STANDARD_EXERCISE_TYPE:
        default:
          $class_name = 'ADA_STANDARD_EXERCISE_TYPE';
          //$icon = "<img src=\"img/exer_ico.png\" border=0>";
          $exer_count++;
      }
      //$temp_results = array(translateFN("Titolo")=>"<a href=view.php?id_node=$res_id_node&querystring=$s_node_text>$icon $res_name</a>");

      if( $res_type == ADA_GROUP_TYPE || $res_type == ADA_LEAF_TYPE || $res_type == ADA_NOTE_TYPE || $res_type == ADA_PRIVATE_NOTE_TYPE) {
        $html_for_result = "<span class=\"$class_name\"><a href=\"view.php?id_node=$res_id_node&querystring=$s_node_text\">$res_name</a></span>";
      }
      else {
        $html_for_result = "<span class=\"$class_name\"><a href=\"exercise.php?id_node=$res_id_node\">$res_name</a></span>";
      }
      $temp_results = array(translateFN('Titolo') => $html_for_result);
      //           $temp_results = array(translateFN("Titolo")=>$title,translateFN("Testo")=>$res_text);
      array_push ($total_results,$temp_results);
    }

    $tObj = new Table();
    $tObj->initTable('0','center','2','1','100%','black','white','black','white');
    $summary = translateFN("Elenco dei nodi che soddisfano la ricerca al ") . $ymdhms;
 //   $caption = translateFN("Sono stati trovati")." $group_count ".translateFN("gruppi").", $node_count ".translateFN("nodi").", $exer_count ".translateFN("esercizi").",  $note_count ".translateFN("note.");
   $caption = translateFN("Sono stati trovati")." $group_count ".translateFN("gruppi").", $node_count ".translateFN("nodi");
    $tObj->setTable($total_results,$caption,$summary);
    $search_results = $tObj->getTable();
    //
    // diretto:
    //  header("Location: view.php?id_node=$res_id_node");
  }
  else {
    $search_results = translateFN("Non &egrave; stato trovato nessun nodo.");
  }
}

$menu = "<p>".translateFN("Scrivi la o le parole che vuoi cercare, scegli quali oggetti cercare, e poi clicca su Cerca.");
$menu .= "<br>".translateFN("ADA restituir&agrave; una lista con i nodi che contengono TUTTE le parole inserite.");
$menu .= "<br>".translateFN("Le parole vengono trovate anche all'interno di altre parole, e senza distinzioni tra maiuscole e minuscole.")."</p>";
// $menu .= "<br>".translateFN("Se vuoi cercare tra i media collegati (immagini, suoni, siti) usa la ")."<a href=search_media.php>".translateFN("Ricerca sui Media")."</a></p>";
// $menu .= "<br>".translateFN("Se non sai esattamente cosa cercare, prova a consultare il ")."<a href=lemming.php>".translateFN("Lessico")."</a></p>";



/* 5.
search form

*/

// versione con campo UNICO

 $l_search = 'standard_node';
 $form_dataHa = array(
  // SEARCH FIELDS
  array(
    'label'=>translateFN('Parola')."<br>",
    'type'=>'text',
    'name'=>'s_node_text',
    'size'=>'20',
    'maxlength'=>'40',
    'value'=>$s_node_text
  ),
   array(
    'label'=>'',
    'type'=>'hidden',
    'name'=>'l_search',
    'value'=>$l_search
  ),
   array(
    'label'=>'',
    'type'=>'submit',
    'name'=>'submit',
    'value'=>translateFN('Cerca')
  )
);


// versione con ricerca sui campi specifici:
if (!isset($s_node_name)) {
  $s_node_name = "";
}
if (!isset($s_node_title)) {
  $s_node_title = "";
}
if (!isset($s_node_author)) {
  $s_node_author = "";
}
//   if (!isset($s_node_media))
//        $s_node_media = "";
if (!isset($s_node_text)) {
  $s_node_text = "";
}
if (!isset($checked_standard)) {
 $checked_standard = "";
}
if (!isset($checked_note)) {
 $checked_note = "";
}
if (!isset($checked_all)) {
  $checked_all = "";
}

// vito, 10 june 2009

if ($checked_standard == "" && $checked_note == "" && $checked_all == "") {
 $checked_all = 'checked';
}

$form_dataHa = array(
  // SEARCH FIELDS
  array(
    'label'=>translateFN('Nome')."<br>",
    'type'=>'text',
    'name'=>'s_node_name',
    'size'=>'20',
    'maxlength'=>'40',
    'value'=>$s_node_name
  ),
  array(
    'label'=>translateFN('Keywords')."<br>",
    'type'=>'text',
    'name'=>'s_node_title',
    'size'=>'20',
    'maxlength'=>'40',
    'value'=>$s_node_title
  ),
 array(
    'label'=>translateFN('Autore')."<br>",
    'type'=>'text',
    'name'=>'s_node_author',
    'size'=>'20',
    'maxlength'=>'40',
    'value'=>$s_node_author
  ),
   array(
   'label'=>translateFN('Media')."<br>",
   'type'=>'text',
   'name'=>'s_node_media',
   'size'=>'20',
   'maxlength'=>'40',
   'value'=>$s_node_media
   ),
  array(
    'label'=>translateFN('Testo')."<br>",
    'type'=>'textarea',
    'name'=>'s_node_text',
    'size'=>'40',
    'maxlength'=>'80',
    'value'=>$s_node_text
  ),
  // SEARCH OPTIONS
  array(
    'label'=>translateFN('Nei gruppi e nei nodi'),
    'type'=>'radio',
    'checked'=>$checked_standard,
    'name'=>'l_search',
    'value'=>'standard_node'
  ),
  array(
    'label'=>translateFN('Nel forum'),
    'type'=>'radio',
    'checked'=>$checked_note,
    'name'=>'l_search',
    'value'=>'note'
  ),
  array(
    'label'=>translateFN('In tutti gli oggetti'),
    'type'=>'radio',
    'checked'=>$checked_all,
    'name'=>'l_search',
    'value'=>'all',
    'selected' => 'selected'
  ),
  array(
    'label'=>'',
    'type'=>'submit',
    'name'=>'submit',
    'value'=>translateFN('Cerca')
  )
);

$fObj = new Form();
$fObj->setForm($form_dataHa);
$search_form = $fObj->getForm();


/* 6.
recupero informazioni aggiornate relative all'utente
ymdhms: giorno e ora attuali
*/

/*

if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {
  if (empty($userObj->error_msg)){
    $user_messages = $userObj->get_messagesFN($sess_id_user);
    $user_agenda =  $userObj->get_agendaFN($sess_id_user);
  }
  else {
    $user_messages =  $userObj->error_msg;
    $user_agenda = translateFN("Nessun'informazione");
  }
}
else {
  $user_messages = $userObj;
  $user_agenda = "";
}
*/

// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);

// CHAT, BANNER etc
$banner = include (ROOT_DIR."/include/banner.inc.php");
$chat_link = "<a href='../comunica/adaChat.php' target='_blank'>".translateFN("chat")."</a>";

$go_map = "<a href = \" map.php?id_node=$sess_id_node\">" . translateFN("mappa") . "</a>";
$go_print = "<a href=\" view.php?id_node=" . $sess_id_node . "&op=print\" target=\"_blank\">"  . translateFN("stampa") . "</A>";

/* 8.
costruzione della pagina HTML
*/


$content_dataAr = array(
  'form'=>$search_form,
  'results'=>$search_results,
  'menu'=>$menu,
  'chat_link'=>$chat_link,
  'banner'=> $banner,
  'course_title'=>'<a href="main_index.php">'.$course_title.'</a>',
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
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr,$content_dataAr);


?>