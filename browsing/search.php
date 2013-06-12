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
  $self = whoami();
/*} */

// questa versione permette di cercare anche SOLO per nome o per keyword (titolo)
// se è passato il testo, lo cerca ovunque (O nel nome O nelle keywords O nel testo)
// versione con campo unico
  
  
if (isset($_REQUEST['submit'])) {
  $out_fields_ar = array('nome','titolo','testo','tipo');
  $clause='';
  $or = ' OR ';
  $and = ' AND ';

  // casi di ricerca da URL statica
  if (!empty($s_node_name)) {  // (search.php?s_node_name=xyz). Non è usato attualmente
    $clause = "nome LIKE '%$s_node_name%'";
  } elseif  (!empty($s_node_title)){ //  (search.php?s_node_title=xyz). Attenzione: "title" contiene le keywords del nodo
    $clause = "titolo LIKE '%$s_node_title%'";
  }
  // questo è il caso della ricerca da form. La ricerca avviene SU TUTTI I CAMPI se l_search = all
  if (!empty($s_node_text)) { 
    $clause = "(";
    $clause = $clause . "testo LIKE '%$s_node_text%'";
    if ($_REQUEST['l_search'] == 'all'){
		$clause = $clause . $or. "titolo LIKE '%$s_node_text%'";
		$clause = $clause . $or. "nome LIKE '%$s_node_text%'";
	}
    $clause = $clause . ")";
  }
/* versione che permette la ricerca in OR tra le parole

if (isset($submit) && (!empty($s_node_text))) {
  // versione con campo unico
  $out_fields_ar = array('nome','titolo','testo','tipo');
  $clause='';
  $or = ' OR ';
  $and = ' AND ';

  if (!empty($s_node_text)) {

    $regexp  = '/\w{3,}+/';
    preg_match_all($regexp, $s_node_text, $words_to_searchAr);
    //$words_to_search = $words_to_searchAr[1];
    //print_r($words_to_searchAr);
    $num_words = count($words_to_searchAr[0]);
//    print_r($words_to_searchAr);
    if ( $num_words > 1) {
        for ($i = 0; $i < count($words_to_searchAr[0]); $i++) {
            $word = $words_to_searchAr[0][$i];
            if ($i == 0) {
                $clause_nome = "(nome LIKE '%$word%'";
                $clause_titolo = "(titolo LIKE '%$word%'";
                $clause_testo = "(testo LIKE '%$word%'";
            }elseif ($i == $num_words - 1) {
                $clause_nome .= " OR nome LIKE '%$word%')";
                $clause_titolo .= " OR titolo LIKE '%$word%')";
                $clause_testo .= " OR testo LIKE '%$word%')";
            } else {
                $clause_nome .= " OR nome LIKE '%$word%'";
                $clause_titolo .= " OR titolo LIKE '%$word%'";
                $clause_testo .= " OR testo LIKE '%$word%'";
            }
        }
        $clause = $clause_nome . ' OR ' . $clause_titolo . ' OR ' . $clause_testo;
    } else {
        $clause = "(";
        $clause = $clause . "nome LIKE '%$s_node_text%'";
        $clause = $clause . $or. "titolo LIKE '%$s_node_text%'";
        $clause = $clause . $or. "testo LIKE '%$s_node_text%'";
        $clause = $clause . ")";
    }
  }
  */
  
  
  // does ADA care of types?
/*
 *
  if ($op == "lemma") {
    $clause = $clause . $and. " (tipo  = ".ADA_GROUP_WORD_TYPE."  OR tipo  = ".ADA_LEAF_WORD_TYPE.")";
  }else {
    $clause = $clause . $and. " (tipo  = ".ADA_GROUP_TYPE."  OR tipo  = ".ADA_LEAF_TYPE.")";
  }
 */
 


  $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$_SESSION['sess_id_course']);


  if ($resHa){
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
        case ADA_GROUP_WORD_TYPE:
          //$icon = "<img src=\"img/group_ico.png\" border=0>";
          $class_name = 'ADA_GROUP_WORD_TYPE';
          $group_count++;
          break;
      case ADA_LEAF_WORD_TYPE:
          //$icon = "<img src=\"img/node_ico.png\" border=0>";
          $class_name = 'ADA_LEAF_WORD_TYPE';
          $node_count++;
          break;

      }
      $s_node_text_enc = urlencode($s_node_text);
      if( $res_type == ADA_GROUP_TYPE || $res_type == ADA_LEAF_TYPE || ADA_GROUP_WORD_TYPE || $res_type == ADA_LEAF_WORD_TYPE || $res_type == ADA_NOTE_TYPE || $res_type == ADA_PRIVATE_NOTE_TYPE) {
        $html_for_result = "<span class=\"$class_name\"><a href=\"view.php?id_node=$res_id_node&querystring=$s_node_text_enc\">$res_name</a></span>";
      }
      else {
        $html_for_result = "<span class=\"$class_name\"><a href=\"exercise.php?id_node=$res_id_node\">$res_name</a></span>";
      }
      $temp_results = array(translateFN('Titolo') => $html_for_result);
      array_push ($total_results,$temp_results);
    }

    $tObj = new Table();
    $tObj->initTable('0','center','2','1','100%','black','white','black','white');
    $summary = translateFN("Elenco dei nodi che soddisfano la ricerca al ") . $ymdhms;
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
}  // end Submit


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

if ($op == 'lemma') {
    $form_dataHa[] = array (
    'label'=>'',
    'type'=>'hidden',
    'name'=>'op',
    'value'=>$op
    );
}

$fObj = new Form();
$fObj->setForm($form_dataHa);
$search_form = $fObj->getForm();



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
