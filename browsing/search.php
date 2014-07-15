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

$variableToClearAR = array('node', 'layout', 'course', 'course_instance','user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_VISITOR      => array('node','layout','course', 'course_instance'),
  AMA_TYPE_STUDENT         => array('node','layout','course', 'course_instance'),
  AMA_TYPE_TUTOR => array('node','layout','course', 'course_instance'),
  AMA_TYPE_AUTHOR       => array('node','layout','course', 'course_instance')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';

include_once 'include/browsing_functions.inc.php';

require_once ROOT_DIR.'/include/HTML_element_classes.inc.php';

require_once ROOT_DIR.'/include/Forms/AdvancedSearchForm.inc.php';

if ($courseInstanceObj instanceof Course_instance) {
    $self_instruction = $courseInstanceObj->getSelfInstruction();
}
if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    $self='searchSelfInstruction';
}
else
{
    $self = whoami();
}

if(isset($_REQUEST['submit']))
{
  $submit='simpleForm';
}
elseif(isset($_REQUEST['submit_advancedForm']))
{
    $submit='advancedForm';
}
else {
    $submit=null;
}
if (!is_null($submit)) {

    $out_fields_ar = array('nome','titolo','testo','tipo','id_utente');
    $clause='';
    $or = ' OR ';
    $and = ' AND ';
    
/*
 * Versione campo unico
 *
 *
 */
    if (!empty($s_UnicNode_text)) {
        $clause = "(";
        $clause = $clause . "nome LIKE '%$s_UnicNode_text%'";
        $clause = $clause . $or. "titolo LIKE '%$s_UnicNode_text%'";
        $clause = $clause . $or. "testo LIKE '%$s_UnicNode_text%'";
        $clause = $clause . ")";
    }
    else {
        $s_UnicNode_text = "";
    }

/*
 * Versione campo diversi
 *
 *
 */

    $resHa=array();
    if($s_UnicNode_text=="")
    {
        $count=0;
        if(isset($_POST['s_node_name']))
        {
            $s_node_name=$_POST['s_node_name'];
        }
        if(isset($_POST['s_node_title']))
        {
            $s_node_title=$_POST['s_node_title'];
        }
        if(isset($_POST['s_node_text']))
        {
            $s_node_text=$_POST['s_node_text'];
        }
        if(empty($s_node_name) && empty($s_node_title)  && empty($s_node_text))
        {
            $clause = '((tipo <> '.ADA_PRIVATE_NOTE_TYPE.') OR (tipo ='.ADA_PRIVATE_NOTE_TYPE.' AND id_utente = '.$sess_id_user.'))';
            $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$_SESSION['sess_id_course']);
        }
        else
        {
            if (!empty($s_node_name)) {
                $count++;
            }
            if (!empty($s_node_title)){ //keywors
                $count++;
            }
            if (!empty($s_node_text)){
                $count++;
            }
            $resHa=$nodeObj->executeSearch($s_node_name,$s_node_title,$s_node_text,$dh,$count,$sess_id_user);
        }

    }
    else if(empty($resHa))
    {
       $clause = '('.$clause.')'.$and.' ((tipo <> '.ADA_PRIVATE_NOTE_TYPE.') OR (tipo ='.ADA_PRIVATE_NOTE_TYPE.' AND id_utente = '.$sess_id_user.'))';
       $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$_SESSION['sess_id_course']);
    }
 
 
    if (!AMA_DataHandler::isError($resHa) and is_array($resHa) and !empty($resHa)){
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
          $temp_results = array(translateFN('Titolo') => $html_for_result,translateFN('keywords') =>$res_course_title);
          array_push ($total_results,$temp_results);
        }
        $thead_data = array(
            translateFN('Titolo'),
            translateFN('Keywords')
       );
        $result_table = BaseHtmlLib::tableElement('id:table_result', $thead_data, $total_results);
        $results=$result_table->getHtml();

       
      }
      else {
    $results=translateFN("Non &egrave; stato trovato nessun nodo.");
  }
}  // end Submit

$menu .= "<p>".translateFN("Scrivi la o le parole che vuoi cercare, poi clicca su Cerca. Il sistema restituir&agrave;");
$menu .= "<br>".translateFN(" una lista con i nodi che contengono TUTTE le parole inserite.");
$menu .= "<br>".translateFN(" Le parole vengono trovate anche all'interno di altre parole e senza");
$menu .= "<br>".translateFN("distinzioni tra maiuscole e minuscole.")."</p>";

/*menù advanced search*/

$menuAdvanced_search .= "<p>".translateFN("Scrivi la o le parole che vuoi cercare, poi clicca su Cerca. In questo caso puoi");
$menuAdvanced_search .= "<br>".translateFN(" effettuare la ricerca su più campi contemporaneamente. Il sistema proverà a");
$menuAdvanced_search .= "<br>".translateFN(" restituituire una lista dei nodi contenenti tutte le parole indicate; se ciò");
$menuAdvanced_search .= "<br>".translateFN(" non è possibile restituirà la lista dei nodi che ne contengono almeno una.");
$menuAdvanced_search .= "<br>".translateFN(" Le parole vengono trovate anche all'interno di altre parole e senza");
$menuAdvanced_search .= "<br>".translateFN("distinzioni tra maiuscole e minuscole.")."</p>";
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
    'name'=>'s_UnicNode_text',
    'size'=>'20',
    'maxlength'=>'40',
    'value'=>$s_UnicNode_text
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
$action=whoami().".php";
/*set get method to prevent the confirmation data on back button's browser*/
$fObj->initForm($action, 'GET');
$fObj->setForm($form_dataHa);
$search_form = $fObj->getForm();

if(isset($_GET['s_AdvancedForm']))
{

$form_AdvancedSearch = new AdvancedSearchForm(false,'search.php');

if($form_AdvancedSearch->isValid()) {
    $AdvancedSearchAr = array(
            's_node_name' => ($_GET['s_node_name']),
            's_node_title' => ($_GET['s_node_title']),
            's_node_text' => ($_GET['s_node_text']),
            's_AdvancedForm'=>($_GET['s_AdvancedForm'])
        );
    
    $form_AdvancedSearch->fillWithArrayData($AdvancedSearchAr);
    $advancedSearch_form=$form_AdvancedSearch->getHtml();
    
}
}
else
{
  $form_AdvancedSearch = new AdvancedSearchForm(false,'search.php');  
  $action=whoami().".php";
  $advancedSearch_form = $form_AdvancedSearch->getHtml();
  
}
$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);

// CHAT, BANNER etc
$banner = include (ROOT_DIR."/include/banner.inc.php");
$chat_link = "<a href='../comunica/adaChat.php' target='_blank'>".translateFN("chat")."</a>";

$go_map = "<a href = \" map.php?id_node=$sess_id_node\">" . translateFN("mappa") . "</a>";
$go_print = "<a href=\" view.php?id_node=" . $sess_id_node . "&op=print\" target=\"_blank\">"  . translateFN("stampa") . "</A>";

$Simple_searchLink="<a href='#'onClick=simpleSearch()>Ricerca semplice</a>";
$advanced_searchLink="<a href='#'onClick=advancedSearch()>Ricerca avanzata</a>";
/* 8.
costruzione della pagina HTML
*/

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$labelSimple_search=  translateFN('Ricerca semplice');
$labelAdvanced_search=translateFN('Ricerca avanzata');

if(isset($_GET['s_AdvancedForm']))
{
    $result_AdvancedSearch=$results;
    unset($results);
}
$content_dataAr = array(
  'form'=>$search_form,
  'advancedSearch_form'=>$advancedSearch_form,
  'menuAdvanced_search'=>$menuAdvanced_search,
  'labelSimple_search'=>$labelSimple_search,
  'labelAdvanced_search'=>$labelAdvanced_search,
  //'results'=>$search_results,
  'results'=>$results,
  'result_AdvancedSearch'=>$result_AdvancedSearch,
  'simpleSearchLink'=>$Simple_searchLink,
  'advanced_searchLink'=>$advanced_searchLink,
  'menu'=>$menu,
  'chat_link'=>$chat_link,
  'banner'=> $banner,
  'course_title'=>translateFN(' Ricerca '),
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
  'chat_users'=>$online_users,
  'user_avatar'=>$avatar->getHtml(),
  'user_modprofilelink' => $userObj->getEditProfilePage(),		
);

/**
 * Sends data to the rendering engine
 */
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_UNIFORM,
                JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT,
                JQUERY_MASKEDINPUT
                );

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
                JQUERY_UNIFORM_CSS,
                JQUERY_DATATABLE_CSS
);
if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    array_push ($layout_dataAr['JS_filename'],ROOT_DIR.'/js/browsing/search.js');
}

$options['onload_func'] = 'dataTablesExec()';
//"\$j('input, a.button, button').uniform();"
ARE::render($layout_dataAr,$content_dataAr, NULL, array('onload_func' => "initDoc();") );


?>
