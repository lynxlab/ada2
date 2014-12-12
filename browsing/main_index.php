<?php
/**
 * MAIN INDEX.
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
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
  AMA_TYPE_VISITOR => array('layout','course'),
  AMA_TYPE_STUDENT => array('layout','tutor','course','course_instance'),
  AMA_TYPE_TUTOR   => array('layout','course','course_instance'),
  AMA_TYPE_AUTHOR  => array('layout','course')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
//$self = 'index';

include_once 'include/browsing_functions.inc.php';
include_once 'include/cache_manager.inc.php';


include_once CORE_LIBRARY_PATH.'/includes.inc.php';
include_once ROOT_DIR.'/include/bookmark_class.inc.php';

if ($courseInstanceObj instanceof Course_instance) {
    $self_instruction = $courseInstanceObj->getSelfInstruction();
}
if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    $self='defaultSelfInstruction';
}
else
{
    $self = 'index';
}

if (!isset($hide_visits)) {
  $hide_visits = 1; // default: no visits countg
}

 if (!isset($order)) {
  $order = 'struct'; // default
 }
 
 if (!isset($op)) {
 	$op = null;
 }

if (!isset($expand)) {
    if($op == 'forum') {
        $expand = 1;
    } elseif ($op == 'glossary') {
        $expand = 2; // default: 1 level of nodes
    } else {
        $expand = 3; // default: 1 level of nodes
    }
}
$with_icons = 1; // 0 or 1; valid only for forum display

// FIXME: verificare se servono.
//if (isset($id_course)){
//  $_SESSION['sess_id_course'] = $id_course;
//  $sess_id_course = $id_course;
//}
//
//if (isset($id_course_instance)){
//  $_SESSION['sess_id_course_instance'] = $id_course_instance;
//  $sess_id_course_instance = $id_course_instance;
//}
// ******************************************************
// get user object
$userObj = read_user($sess_id_user);
if (is_object($userObj) && (!AMA_DataHandler::isError($userObj))) {
  if (isset($_POST['s_node_name'])){
    header("Location: search.php?submit=1&s_node_text=$s_node_name&l_search=$l_search");
    exit;
  }
  else {
    // FIXME: verificare se compare in browsing_init.inc.php
//    if ($id_profile == AMA_TYPE_STUDENT_STUDENT) {
//      $user_level = $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
//    }
//    else {
//      $user_level = ADA_MAX_USER_LEVEL;
//    }



      /* Static mode */
     $cacheObj = New CacheManager($id_profile);
     $cacheObj->checkCache($id_profile);
     if ($cacheObj->getCachedData()){
         exit();
     }

    // dynamic mode:
    // ******************************************************

		/*
      $exp_link = translateFN("Profondit&agrave;");
      for ($e=1;$e<11;$e++){
        if ((isset($expand)) AND ($e == $expand))
			$label_exp = "<strong>$e</strong>";
        else
			$label_exp = $e;
        $exp_link .= "<a href=\"main_index.php?op=$op&amp;order=struct&amp;hide_visits=$hide_visits&amp;expand=$e\">$label_exp</a> |";
      }
      $exp_link .="<br>\n";
      */

		$div_link = CDOMElement::create('div');
		$link_expand = CDOMElement::create('a');
		$link_expand->setAttribute('id','expandNodes');
		$link_expand->setAttribute('href','javascript:void(0);');
		$link_expand->setAttribute('onclick',"toggleVisibilityByDiv('structIndex','show');");
		$link_expand->addChild(new CText(translateFN('Apri Nodi')));
		$link_collapse = CDOMElement::create('a');
		$link_collapse->setAttribute('href','javascript:void(0);');
		$link_collapse->setAttribute('onclick',"toggleVisibilityByDiv('structIndex','hide');");
		$link_collapse->addChild(new CText(translateFN('Chiudi Nodi')));

		$div_link->addChild($link_expand);
		$div_link->addChild(new CText(' | '));
		$div_link->addChild($link_collapse);

		$exp_link = $div_link->getHtml();


      if ((isset($op)) && (($op=='forum') || ($op=='diary'))){
        /* listing mode:
         *  standard o null
         *  export
         *  export_single
         */

        // template for forum index

        //$self = 'forum_index';

        if (!isset($list_mode) OR ($list_mode=="")){
          $list_mode = "standard";
        } else { // export_all , export_single
          $node_index = $course_instance_Obj->forum_main_indexFN('',1,$id_profile,$order,$id_student,$list_mode);
          $node_index = strip_tags($node_index);

          //  $node_index = unhtmlentities($node_index);
          header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
          header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
          // always modified
          header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
          header("Cache-Control: post-check=0, pre-check=0", false);
          header("Pragma: no-cache");                          // HTTP/1.0
          //header("Content-Type: text/plain");
          header("Content-Type: application/rtf");

          //header("Content-Length: ".filesize($name));
          header("Content-Disposition: attachment; filename=".$op."_".$id_course."_class_".$sess_id_course_instance.".rtf");
          echo $node_index;
          exit;
        }
        $legenda = CDOMElement::create('div','id:legenda');
        $label   = CDOMElement::create('span','class:text');
        $label->addChild(new CText(translateFN('Legenda:')));
        $legenda->addChild($label);
        
        $group_item = CDOMElement::create('span','class:ADA_GROUP_TYPE');
        $group_item->addChild(new CText(translateFN('gruppo')));
        $legenda->addChild($group_item);
        
        $note_item = CDOMElement::create('span','class:ADA_NOTE_TYPE');
        $note_item->addChild(new CText(translateFN('nota di classe di un altro studente')));
        $legenda->addChild($note_item);
        $tutor_note_item = CDOMElement::create('span');
        $tutor_note_item->setAttribute('class','ADA_NOTE_TYPE TUTOR_NOTE');
        $tutor_note_item->addChild(new CText(translateFN('nota di classe del tutor')));
        $legenda->addChild($tutor_note_item);
        $your_note_item = CDOMElement::create('span');
        $your_note_item->setAttribute('class','ADA_NOTE_TYPE YOUR_NOTE');
        $your_note_item->addChild(new CText(translateFN('tua nota di classe')));
        $legenda->addChild($your_note_item);
        $private_note_item = CDOMElement::create('span');
        $private_note_item->setAttribute('class','ADA_NOTE_TYPE YOUR_NOTE ADA_PRIVATE_NOTE_TYPE');
        $private_note_item->addChild(new CText(translateFN('nota personale')));
        $legenda->addChild($private_note_item);

        $order_div = CDOMElement::create('div','id:ordering');
        if (isset($order) && $order == 'chrono') {
          $alfa = CDOMElement::create('span','class:selected');
          $alfa->addChild(new CText(translateFN('Ordina per data')));
          $order_div->addChild($alfa);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=struct&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per struttura')));
          $struct->addChild($link);
          $order_div->addChild($struct);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=alfa&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per titolo')));
          $struct->addChild($link);
          $order_div->addChild($struct);
          $expand_nodes = false;
        }
        else if (isset($order) && $order == 'alfa') {
          $order = 'alfa';
          $alfa = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=chrono&expand=$expand");
          // $link->addChild(new CText(translateFN('Ordina per titolo')));
		  $link->addChild(new CText(translateFN('Ordina per data')));
          $alfa->addChild($link);
          $order_div->addChild($alfa);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=struct&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per struttura')));
          $struct->addChild($link);
          $order_div->addChild($struct);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:selected');
          $struct->addChild(new CText(translateFN('Ordina per titolo')));
          $order_div->addChild($struct);
          $expand_nodes = true;
        }
        else {
          $order = 'struct';
          $alfa = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=chrono&expand=$expand");
          // $link->addChild(new CText(translateFN('Ordina per titolo')));
		$link->addChild(new CText(translateFN('Ordina per data')));
          $alfa->addChild($link);
          $order_div->addChild($alfa);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:selected');
          $struct->addChild(new CText(translateFN('Ordina per struttura')));
          $order_div->addChild($struct);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?op=$op&order=alfa&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per titolo')));
          $struct->addChild($link);
          $order_div->addChild($struct);
          $expand_nodes = true;
        }


        switch($hide_visits) {
          case 0:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:selected');
            $span->addChild(new CText(translateFN('mostra anche le visite')));
            $order_div->addChild($span);
            break;
          case 1:
          default:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:not_selected');
            $link = CDOMElement::create('a',"href:main_index.php?op=$op&order=$order&hide_visits=0&expand=$expand");
            $link->addChild(new CText(translateFN('mostra anche le visite')));
            $span->addChild($link);
            $order_div->addChild($span);
            break;
        }


        $index_link = $order_div->getHtml();

		if ($expand_nodes) {
        $node_index  = $exp_link;
		}

        //$menu = "<a href=\"main_index.php?op=$op&amp;order=chrono&amp;list_mode=export_all\">".translateFN("Esporta")."</a>";
        //vito, 8 giugno 2009
        $menu = CourseViewer::displayForumMenu($op, $userObj);
        //$node_index .= CourseViewer::displayForumIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance, $with_icons, 'structIndex');
        // vito, 26 nov 2008: $forum_index is a CORE object.


        $forum_index = CourseViewer::displayForumIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance, $with_icons, 'structIndex');

        $node_index .= $forum_index->getHtml();

        $node_index .= $legenda->getHtml();
        // NODES & GROUPS INDEX
      }
      elseif ((isset($op)) && ($op=='glossary')) { // glossary index
        $legenda = CDOMElement::create('div','id:legenda');
        $label   = CDOMElement::create('span','class:text');
        $label->addChild(new CText(translateFN('Legenda:')));
        $legenda->addChild($label);
        $node_item = CDOMElement::create('span','class:ADA_LEAF_WORD_TYPE');
        $node_item->addChild(new CText(translateFN('nodo')));
        $legenda->addChild($node_item);
        $group_item = CDOMElement::create('span','class:ADA_GROUP_WORD_TYPE');
        $group_item->addChild(new CText(translateFN('gruppo')));
        $legenda->addChild($group_item);
        $unreachable_item = CDOMElement::create('span','class:NODE_NOT_VIEWABLE');
        $unreachable_item->addChild(new CText(translateFN('nodo non raggiungibile')));
        $legenda->addChild($unreachable_item);
        //$exercise_item = CDOMElement::create('span','class:ADA_LEAF_TYPE');
        //$exercise_item->addChild(new CText(translateFN('esercizio')));
        //$legenda->addChild($exercise_item);
        //$executed_exercise_item = CDOMElement::create('span','class:ADA_LEAF_TYPE');
        //$executed_exercise_item->addChild(new CText(translateFN('esercizio gi&agrave; eseguito')));
        //$legenda->addChild($executed_exercise_item);

        $order_div = CDOMElement::create('div','id:ordering');
        if (isset($order) && $order == 'alfa') {
          $alfa = CDOMElement::create('span','class:selected');
          $alfa->addChild(new CText(translateFN('Ordina per titolo')));
          $order_div->addChild($alfa);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?order=struct&expand=$expand&op=$op");
          $link->addChild(new CText(translateFN('Ordina per struttura')));
          $struct->addChild($link);
          $order_div->addChild($struct);
        }
        else {
          $order = 'struct';
          $alfa = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?order=alfa&expand=$expand&op=$op");
          $link->addChild(new CText(translateFN('Ordina per titolo')));
          $alfa->addChild($link);
          $order_div->addChild($alfa);
          $order_div->addChild(new CText('|'));
          $struct = CDOMElement::create('span','class:selected');
          $struct->addChild(new CText(translateFN('Ordina per struttura')));
          $order_div->addChild($struct);
        }


        switch($hide_visits) {
          case 0:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:selected');
            $span->addChild(new CText(translateFN('mostra anche le visite')));
            $order_div->addChild($span);
            break;
          case 1:
          default:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:not_selected');
            $link = CDOMElement::create('a',"href:main_index.php?order=$order&hide_visits=0&expand=$expand&op=$op");
            $link->addChild(new CText(translateFN('mostra anche le visite')));
            $span->addChild($link);
            $order_div->addChild($span);
            break;
        }

        $index_link = $order_div->getHtml();

        $search_label = translateFN('Cerca nell\'Indice:');
        $node_type = 'standard_node';
        $node_index  = $exp_link;
        $glossary_index = CourseViewer::displayGlossaryIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance,'courseIndex');
        if(!AMA_DataHandler::isError($glossary_index)) {
           $node_index .= $glossary_index->getHtml();
        }

        //vito 26 gennaio 2009
        //$node_index .= $legenda;
        $node_index .= $legenda->getHtml();

      } else { //normal index
        $legenda = CDOMElement::create('div','id:legenda');
        $label   = CDOMElement::create('span','class:text');
        $label->addChild(new CText(translateFN('Legenda:')));
        $legenda->addChild($label);
        $node_item = CDOMElement::create('span','class:ADA_LEAF_TYPE');
        $node_item->addChild(new CText(translateFN('nodo')));
        $legenda->addChild($node_item);
        $group_item = CDOMElement::create('span','class:ADA_GROUP_TYPE');
        $group_item->addChild(new CText(translateFN('gruppo')));
        $legenda->addChild($group_item);
        $unreachable_item = CDOMElement::create('span','class:NODE_NOT_VIEWABLE');
        $unreachable_item->addChild(new CText(translateFN('nodo non raggiungibile')));
        $legenda->addChild($unreachable_item);
        //$exercise_item = CDOMElement::create('span','class:ADA_LEAF_TYPE');
        //$exercise_item->addChild(new CText(translateFN('esercizio')));
        //$legenda->addChild($exercise_item);
        //$executed_exercise_item = CDOMElement::create('span','class:ADA_LEAF_TYPE');
        //$executed_exercise_item->addChild(new CText(translateFN('esercizio gi&agrave; eseguito')));
        //$legenda->addChild($executed_exercise_item);

        $order_div = CDOMElement::create('div','id:ordering');
        if (isset($order) && $order == 'alfa') {
          $struct = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?order=struct&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per struttura')));
          $struct->addChild($link);
          $order_div->addChild($struct);
          $order_div->addChild(new CText('|'));
          $alfa = CDOMElement::create('span','class:selected');
          $alfa->addChild(new CText(translateFN('Ordina per titolo')));
          $order_div->addChild($alfa);
          $expand_nodes = false;
        }
        else {
          $order = 'struct';
          $struct = CDOMElement::create('span','class:selected');
          $struct->addChild(new CText(translateFN('Ordina per struttura')));
          $order_div->addChild($struct);
          $order_div->addChild(new CText('|'));
          $alfa = CDOMElement::create('span','class:not_selected');
          $link = CDOMElement::create('a', "href:main_index.php?order=alfa&expand=$expand");
          $link->addChild(new CText(translateFN('Ordina per titolo')));
          $alfa->addChild($link);
          $order_div->addChild($alfa);
          $expand_nodes = true;
        }


        switch($hide_visits) {
          case 0:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:selected');
            $span->addChild(new CText(translateFN('mostra anche le visite')));
            $order_div->addChild($span);
            break;
          case 1:
          default:
            $order_div->addChild(new CText('|'));
            $span = CDOMElement::create('span','class:not_selected');
            $link = CDOMElement::create('a',"href:main_index.php?order=$order&hide_visits=0&expand=$expand");
            $link->addChild(new CText(translateFN('mostra anche le visite')));
            $span->addChild($link);
            $order_div->addChild($span);
            break;
        }

        $index_link = $order_div->getHtml();

        $search_label = translateFN('Cerca nell\'Indice:');
        $node_type = 'standard_node';
        /*
         * vito, 23 luglio 2008
         */
		if ($expand_nodes) {
        $node_index  = $exp_link;
		}


        //$node_index .= CourseViewer::displayMainIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance,'structIndex');

        // vito, 26 nov 2008: $main_index is a CORE object

         $main_index = CourseViewer::displayMainIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance,'structIndex');
         if(!AMA_DataHandler::isError($main_index)) {
           $node_index .= $main_index->getHtml();
         }

        //vito 26 gennaio 2009
        //$node_index .= $legenda;
        $node_index .= $legenda->getHtml();
      }


    /* 2.
     getting todate-information on user
     MESSAGES adn EVENTS
     */
/*
 * Non dovrebbe servire
 */
//    if (empty($userObj->error_msg)){
//      // FIXME: MULTIPORTARE
//      //$user_messages = $userObj->get_messagesFN($sess_id_user);
//      //$user_agenda =  $userObj->get_agendaFN($sess_id_user);
//    } else {
//      $user_messages =  $userObj->error_msg;
//      $user_agenda   = translateFN("Nessun'informazione");
//
//    }
  }
}
else {
//  $user_messages = $userObj;
//  $user_agenda = "";
  $errObj = new ADA_error($userObj, translateFN('Utente non trovato, impossibile proseguire'));
}

/*
 *  Who's online
 */
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users
$online_users_listing_mode = 2;
$id_course_instance = isset($id_course_instance) ? $id_course_instance : null;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);

/*
 * Search form (redirects to search.php)
 */
$search_data = array(
  array(
    'label'     => isset($search_label) ? $search_label : null,
    'type'      => 'text',
    'name'      => 's_node_name',
    'size'      => '20',
    'maxlength' => '40',
    'value'     => ""
  ),
  array(
    'label' => '',
    'type'  => 'submit',
    'name'  => 'Submit',
    'value' => translateFN('Cerca')
  ),
  array(
    'label'    => '',
    'type'     => 'hidden',
    'name'     => 'l_search',
    'size'     => '20',
    'maxlength'=> '40',
    'value'    => isset($node_type) ? $node_type : null 
  )
);
$fObj = new Form();
$fObj->setForm($search_data);
$search_form = $fObj->getForm();

$banner = include ROOT_DIR.'/include/banner.inc.php';

//show course istance name if isn't empty - valerio
if (!empty($courseInstanceObj->title)) {
	$course_title .= ' - '.$courseInstanceObj->title;
}

if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    $user_type=$user_type.' livello '.$user_level;
    $user_level='';
    $layout_dataAr['JS_filename']=array(ROOT_DIR.'/js/include/menu_functions.js'); 
    
}

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

$title = '';
if (isset($index_link)) $title .= $index_link; 
if (isset($index_no_visits_link)) $title .= $index_no_visits_link;

$content_dataAr = array(
  'banner'       => $banner,
  'course_title' => "<a href='main_index.php'>".$course_title."</a>",
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'user_level'   => $user_level,
  'last_visit' => $last_access,
  'status'       => $status,
  'title'        => $title,
  'index'        => $node_index,
  'search_form'  => $search_form,//."<br>".$menu,
  'forum_menu'   => isset($menu) ? $menu: '',
  'messages'     => $user_messages->getHtml(),
  'agenda'       => $user_agenda->getHtml(),
  'events'		 => $user_events->getHtml(),
  'edit_profile'=> $userObj->getEditProfilePage(),
  'chat_users'   => $online_users
 );

ARE::render($layout_dataAr, $content_dataAr);

/**
 * preparing for static mode
 *

  if (($static_mode > ADA_READONLY_CACHE) OR ($cache_mode == 'cache') OR ($cache_mode == 'updatecache')){ // we have to (re)write the cache file
   if($id_profile == AMA_TYPE_VISITOR) {
      $static_optionsAr = array('static_dir' => $static_dir);
      ARE::render($layout_dataAR,$content_dataAr,ARE_FILE_RENDER,$static_optionsAr);
   }
}
 *
 * now managed by the class Cache Manager
 * */

$cacheObj->writeCachedData($id_profile,$layout_dataAr,$content_dataAr);
