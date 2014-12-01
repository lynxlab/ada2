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
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('node', 'layout', 'course'),
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
    AMA_TYPE_AUTHOR => array('node', 'layout', 'course')
);

//FIXME: course_instance is needed by videochat BUT not for guest user


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';

include_once 'include/browsing_functions.inc.php';
include_once 'include/cache_manager.inc.php';

/* Static mode */

$cacheObj = New CacheManager($id_profile);
$cacheObj->checkCache($id_profile);
if ($cacheObj->getCachedData){
	exit();
}


/** DYNAMIC mode
 *
 */
if ($courseInstanceObj instanceof Course_instance) {
    $self_instruction = $courseInstanceObj->getSelfInstruction();
}
if ($userObj instanceof ADAGuest  || ($courseObj->getIsPublic() && $userObj->getType()!=AMA_TYPE_AUTHOR)) {
    $self = 'guest_view';
}
 elseif($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction)) {                    
    $self='viewSelfInstruction';
    // $self='tutorSelfInstruction';
}
else {
    $self = whoami();
}

if ($nodeObj->type != ADA_NOTE_TYPE && $nodeObj->type != ADA_PRIVATE_NOTE_TYPE)
{
	require_once 'include/DFSNavigationBar.inc.php';
	$navBar = new DFSNavigationBar($nodeObj, array(
			'prevId' => $_GET['prevId'], 
			'nextId' => $_GET['nextId'],
			'userLevel' => $user_level));
}

if (MODULES_TEST && strpos($nodeObj->type,(string) constant('ADA_PERSONAL_EXERCISE_TYPE')) === 0 && ADA_REDIRECT_TO_TEST && $_SESSION['sess_id_user_type'] != AMA_TYPE_AUTHOR) {
        $test_db = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $res = $test_db->test_getNodes(array('id_nodo_riferimento'=>$nodeObj->id));
        if (!empty($res) && count($res) == 1 && !AMA_DataHandler::isError($res)) {
                $node = array_shift($res);
                /*Remove the last item to NavigationHistory to increase the value of back button correctly*/ 
                $_SESSION['sess_navigation_history']->removeLastItem();
                header('Location: '.MODULES_TEST_HTTP.'/index.php?id_test='.$node['id_nodo']);
                exit();
        }
}

// search
// versione con campo UNICO

$l_search = 'standard_node';

$form_dataHa = array(
    // SEARCH FIELDS
    array(
        'label' => translateFN('Parola') . "<br>",
        'type' => 'text',
        'name' => 's_node_text',
        'size' => '20',
        'maxlength' => '40',
        'value' => $s_node_text
    ),
    array(
        'label' => '',
        'type' => 'hidden',
        'name' => 'l_search',
        'value' => $l_search
    ),
    array(
        'label' => '',
        'type' => 'submit',
        'name' => 'submit',
        'value' => translateFN('Cerca')
    )
);

$fObj = new Form();
$fObj->initForm("search.php?op=lemma", "POST");
$fObj->setForm($form_dataHa);
$search_form = $fObj->getForm();

/**
 * Backurl: if user bookmarked an address and tried to get it directly...
 *
  if (isset($_SESSION['sess_backurl'])) {
  unset($_SESSION['sess_backurl']);
  }

  if (!isset($_SESSION['sess_id_user'])) {
  $_SESSION['sess_backurl'] = $_SERVER['REQUEST_URI'];
  header("Location: $http_root_dir"); // to login page
  exit();
  }
 */


/**
 * ANONYM Browsing
 *
 * if status of course_instance is ADA_STATUS_PUBLIC
 * user can visit the node but:
 * - no history
 * - no messagery
 * - no logging
 */


/**
 * Guided browsing:
 * if a guide is selected, the node id selected by the  student is overrided
 * $guide_user_id = id number of tutor
 */
if ($id_profile == AMA_TYPE_STUDENT) {
    if ($user_status <> ADA_STATUS_VISITOR) {
//...... do we need it in ADA?
    }
}


// querystring

if (!isset($_REQUEST['querystring'])) {  // word to be enlighten
	$querystring = "";
}
else {
	$querystring = urldecode($_REQUEST['querystring']);
}

// node
	$id_node = $nodeObj->id;
	$node_type = $nodeObj->type;
	$node_title = $nodeObj->name;
	$node_keywords = ltrim($nodeObj->title);
	$node_level = $nodeObj->level;
	$node_date = $nodeObj->creation_date;
	$node_icon = $nodeObj->icon;
	$node_version = $nodeObj->version;
	if (is_array($nodeObj->author)) {
		$authorHa = $nodeObj->author;
		$node_author_id = $authorHa['id'];
		$node_author = $authorHa['nome'] . " " . $authorHa['cognome'];
		$author_uname = $authorHa['username'];
	} else {
		$node_author = "";
		$author_uname = "";
	}
	$node_parent = $nodeObj->parent_id;
	$node_path = $nodeObj->findPathFN();
	$node_index = $nodeObj->indexFN('', 1, $user_level, $user_history, $id_profile);
	$node_family = $nodeObj->template_family;
	$next_node_id = $nodeObj->next_id;
	$sess_id_node = $id_node;
	$data = $nodeObj->filter_nodeFN($user_level, $user_history, $id_profile, $querystring);



// history:
	if ((( $id_profile == AMA_TYPE_STUDENT ) || ($id_profile == AMA_TYPE_VISITOR)
		|| ($id_profile == AMA_TYPE_TUTOR)
		)
		/* && (!empty($sess_id_course_instance)) */
		/* && ($is_istance_active) */
		&& ($node_type != ADA_PRIVATE_NOTE_TYPE)) {
	/*
	 * We need to save visits made by guest users (i.e. users not logged in)
	 */
	if (isset($_SESSION['ada_remote_address'])) {
		$remote_address = $_SESSION['ada_remote_address'];
	} else {
		$remote_address = $_SERVER['REMOTE_ADDR'];
		$_SESSION['ada_remote_address'] = $remote_address;
	}

	if (isset($_SESSION['ada_access_from'])) {
		$accessed_from = $_SESSION['ada_access_from'];
	} else {
		$accessed_from = ADA_GENERIC_ACCESS;
	}
	if (!isset($sess_id_course_instance)  || $courseObj->getIsPublic() ) {
		$dh->add_node_history($sess_id_user, 0, $sess_id_node, $remote_address, HTTP_ROOT_DIR, $accessed_from);
	} else {
		$dh->add_node_history($sess_id_user, $sess_id_course_instance, $sess_id_node, $remote_address, HTTP_ROOT_DIR, $accessed_from);
	}
}

// info on author and tutor
$tutor_info_link = "<a href=\"$http_root_dir/admin/zoom_user.php?id=$tutor_id\">$tutor_uname</a>";
$author_info_link = "<a href=\"$http_root_dir/admin/zoom_user.php?id=$node_author_id\">$node_author</a>";

// link for writing to tutor and author
$write_to_tutor_link = "<a href=\"$http_root_dir/comunica/send_message.php?destinatari=$tutor_uname\">$tutor_uname</a>";
$write_to_author_link = "<a href=\"$http_root_dir/comunica/send_message.php?destinatari=$author_uname\">$node_author</a>";

//next node
$next_node_link = '';
if (!empty($next_node_id)){
	$nextNodeAr =  $dh->get_node_info($next_node_id);
	// level test
	$next_node_level = $nextNodeAr['level'];
	if ($user_level>= $next_node_level){
		// exercise test
		$next_node_type =  $nextNodeAr['type'];
                
		if (Node::isNodeExercise($next_node_type)){
			$next_node_link = "<a href=exercise.php?id_node=$next_node_id>" . translateFN("Continua...") . "</a>";
                       
		}
		else {
			$next_node_link = "<a href=view.php?id_node=$next_node_id>" . translateFN("Continua...") . "</a>";
		}
                
	}
}
// E-portal
$eportal = PORTAL_NAME;

// banner
// $banner = include_once("../include/banner.inc.php"); TO BE COMPLETED
$banner = "";

if ($id_profile == AMA_TYPE_AUTHOR) {
	
	$edit_node = "<a href=\"$http_root_dir/services/edit_node.php?op=edit&id_node=$sess_id_node&id_course=$sess_id_course&type=$node_type\">" .
			translateFN('modifica nodo') . "</a>";

	$delete_node = "<a href=\"$http_root_dir/services/edit_node.php?op=delete&id_node=$sess_id_node&id_course=$sess_id_course&type=$node_type\">" .
			translateFN('elimina nodo') . "</a>";

	$add_exercise = "<a href=\"$http_root_dir/services/add_exercise.php?id_node=$sess_id_node\">" .
			translateFN('aggiungi esercizio') . "</a>";

	$mod_enabled = TRUE;
}

if ($node_type == ADA_GROUP_TYPE)  {
	$go_map = '<a href="map.php?id_node=' . $sess_id_node . '">'
			. translateFN('mappa') . '</a>';
} elseif ($node_type == ADA_GROUP_WORD_TYPE) {
	$go_map = '<a href="map.php?id_node=' . $sess_id_node . '&map_type=lemma">'
			. translateFN('mappa') . '</a>';
}else {
				$go_map = '';
}

switch($id_profile) {
	case AMA_TYPE_STUDENT:
	case AMA_TYPE_TUTOR:
		$add_note = "<a href=\"$http_root_dir/services/addnode.php?id_parent=$sess_id_node&id_course=$sess_id_course&id_course_instance=$sess_id_course_instance&type=NOTE\">" .
		translateFN('aggiungi nota di classe') . '</a>';
		$add_private_note = "<a href=\"$http_root_dir/services/addnode.php?id_parent=$sess_id_node&id_course=$sess_id_course&id_course_instance=$sess_id_course_instance&type=PRIVATE_NOTE\">" .
		translateFN('aggiungi nota personale') . '</a>';

		if ($nodeObj->type == ADA_PRIVATE_NOTE_TYPE || $nodeObj->type == ADA_NOTE_TYPE) {
			// if it's a note
			if (($node_author_id == $userObj->getId() && $id_profile==AMA_TYPE_STUDENT) ||
			     $id_profile==AMA_TYPE_TUTOR) {
			     	$edit_note = "<a href=\"". $http_root_dir . "/services/edit_node.php?op=edit&id_node=" . $sess_id_node ."&id_course=" . $sess_id_course . "&id_course_instance=" . $sess_id_course_instance ."&type=".$node_type."\">"
			     			. translateFN('modifica nota') . "</a>";
			     	$delete_note = "<a href=\"". $http_root_dir . "/services/edit_node.php?op=delete&id_node=" . $sess_id_node ."&id_course=" . $sess_id_course ."&id_course_instance=" . $sess_id_course_instance."&type=".$node_type."\">"
			     			. translateFN('elimina nota') . "</a>";
			     	/**
			     	 * student can promote only PRIVATE_NOTE to NOTE
			     	 * tutor can do everything
			     	 */
			     	if (($nodeObj->type == ADA_PRIVATE_NOTE_TYPE && $id_profile==AMA_TYPE_STUDENT) ||
			     			($id_profile==AMA_TYPE_TUTOR)) {
			     				$publish_note.= "<a href=\"". $http_root_dir . "/services/edit_node.php?".
			     						"op=publish".
			     						"&id_node=" . $sess_id_node .
			     						"&id_course=" . $sess_id_course .
			     						"&id_course_instance=" . $sess_id_course_instance.
			     						"&type=".$node_type."\">"  .
			     						translateFN("pubblica nota") . "</a>";
			     			}
			     }
		}

		break;
   default:
	   $add_note = '';
	   $add_private_note = '';
	   $edit_note = '';
	   $delete_note = '';
	   break;

}
/*  gli studenti dei corsi in autoistruzione non devono poter inviare media etc etc
		 *  TODO: va riportata la modifica fatta per ADA Icon
		 * */
		if ($id_profile == AMA_TYPE_STUDENT && $courseInstanceObj instanceof Course_instance && $courseInstanceObj->getSelfInstruction()){
		  $mod_enabled = FALSE;
		  $com_enabled = FALSE;
		} 

		//show course istance name if isn't empty - valerio
		if (!empty($courseInstanceObj->title)) {
			$course_title .= ' - '.$courseInstanceObj->title;
		}
// keywords linked to search separately
$linksAr  = array();
$keyAr = explode(',',$node_keywords); // or space?
foreach ($keyAr as $keyword){
	$linksAr [] = "<a href=\"search.php?s_node_title=$keyword&submit=cerca&l_search=all\">$keyword</a>";
}

$linked_node_keywords = implode(',',$linksAr);                

/**
 * content_data
 * @var array
 */
$content_dataAr = array(
	'banner' => $banner,
	'eportal' => $eportal,
	'course_title' => "<a href='main_index.php'>" . $course_title . "</a>",
	'main_index' => "<a href='main_index.php?op=glossary'>" . translateFN('Indice delle parole') . "</a>",
	'main_index_text' => "<a href='main_index.php'>" . translateFN('Indice dei testi') . "</a>",
	'user_name' => $user_name,
	'user_type' => $user_type,
	'user_level' => $user_level,
	'user_score' => $user_score,
	'status' => $status,
        'node_level' => $node_level,
	'visited' => $visited,
	'path' => $node_path,
	'title' => $node_title,
	'version' => $node_version,
	'date' => $node_date,
	// FIXME: non esiste ancora...??
	//	 'icon' => CourseViewer::getClassNameForNodeType($node_type),
	'icon' => $node_icon,
	// 'keywords' => "<a href=\"search.php?s_node_title=$node_keywords&submit=cerca&l_search=all\">$node_keywords</a>",
        'keywords' => $linked_node_keywords,
	'author' => $author_info_link, //'author'=>$node_author,
	'tutor' => $tutor_info_link, //'tutor'=>$tutor_uname,
	'search_form' => $search_form,
	'index' => $node_index,
	'go_map' => $go_map,
	'go_next' => $next_node_link,
	'edit_profile'=> $userObj->getEditProfilePage()
		//        'messages' => $user_messages,
		//        'agenda' => $user_agenda
);

//dynamic data from $nodeObj->filter_nodeFN

$content_dataAr['text'] = $data['text'];
$content_dataAr['link'] = $data['link'];
$content_dataAr['media'] = $data['media'];
$content_dataAr['user_media'] = $data['user_media'];
$content_dataAr['exercises'] = $data['exercises'];
$content_dataAr['notes'] = $data['notes'];
$content_dataAr['personal'] = $data['private_notes'];

if ($node_type == ADA_GROUP_WORD_TYPE OR $node_type == ADA_LEAF_WORD_TYPE) {
	$content_dataAr['text'] .= $data['extended_node'];
	/*
	 * generate dattilo images DISABLED IN ADA

	$img_dir = $root_dir.'/browsing/dattilo/img';
	$url_dir = $http_root_dir.'/browsing/dattilo/img';
	if (file_exists($img_dir.'/a.jpg')) {
		$dattilo = converti_dattiloFN($node_title,$url_dir);
		$content_dataAr['dattilo'] = $dattilo;
	}
	* */
}

if ($reg_enabled) {
	$content_dataAr['add_bookmark'] = $add_bookmark;
} else {
	$content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = $bookmark;
$content_dataAr['go_bookmarks_1'] = $go_bookmarks;
$content_dataAr['go_bookmarks_2'] = $go_bookmarks;

if ($mod_enabled) {
	
	$content_dataAr['edit_node'] = $edit_node;
	$content_dataAr['delete_node'] = $delete_node;
	$content_dataAr['add_exercise'] = $add_exercise;
	$content_dataAr['add_note'] = $add_note;
	$content_dataAr['add_private_note'] = $add_private_note;
	$content_dataAr['edit_note'] = $edit_note;
	$content_dataAr['delete_note'] = $delete_note;
	$content_dataAr['publish_note'] = $publish_note;
} else {
	
	$content_dataAr['edit_node'] = '';
	$content_dataAr['delete_node'] = '';
	$content_dataAr['add_note'] = '';
	$content_dataAr['add_private_note'] = '';
	$content_dataAr['edit_note'] = '';
	$content_dataAr['delete_note'] = '';
}

if ($com_enabled) {
	$online_users_listing_mode = 2;
	$online_users = ADALoggableUser::get_online_usersFN($sess_id_course_instance,$online_users_listing_mode);

	$content_dataAr['messages'] = $user_messages->getHtml();
	$content_dataAr['agenda'] = $user_agenda->getHtml();
	$content_dataAr['events'] = $user_events->getHtml();
	$content_dataAr['chat_users'] = $online_users;
} else {
	$content_dataAr['chat_link'] = translateFN("chat non abilitata");
	$content_dataAr['messages'] = translateFN("messaggeria non abilitata");
	$content_dataAr['agenda'] = translateFN("agenda non abilitata");
	$content_dataAr['chat_users'] = "";
}
if ($id_profile == AMA_TYPE_STUDENT)
{
	$content_dataAr['exercise_history'] = '<a href="exercise_history.php?id_course_instance='.$sess_id_course_instance.'">'.translateFN('storico esercizi').'</a>';
}


switch ($op){

	case 'viewXML':
		$XML_optionsAr = array(
			'id'=>$id_node,
			'url'=>$_SERVER['URI'],
			'course_title' => strip_tags($content_dataAr['course_title']),
			'portal' => $eportal
			);
		ARE::render($layout_dataAR,$content_dataAr,ARE_XML_RENDER,$XML_optionsAr);
		break;
            
        case 'print':
            $PRINT_optionsAr = array(
			'id'=>$id_node,
			'url'=>$_SERVER['URI'],
			'course_title' => strip_tags($content_dataAr['course_title']),
			'portal' => $eportal
			);
            ARE::render($layout_dataAR,$content_dataAr, ARE_PRINT_RENDER, $PRINT_optionsAr);
            break;    
	case 'exe':
		// execute the code (!!!)
		//  $content_dataAr['text'] = eval($data['text']); DISABLED IN ADA
		//eval($data['text']);
		// Sends data to the rendering engine
		ARE::render($layout_dataAR,$content_dataAr, null, null);
		break;
	case 'view':
	default:
		// Sends data to the rendering engine
		
		// giorgio 06/set/2013, jquery and flowplayer inclusion
		
		$layout_dataAR['JS_filename'] = array(
				JQUERY,
				JQUERY_UI,
				JQUERY_NIVOSLIDER,
				JQUERY_NO_CONFLICT,
				ROOT_DIR. '/external/mediaplayer/flowplayer-5.4.3/flowplayer.js'
		);		
              if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction)) {                    
                //$self='viewSelfInstruction';
                $layout_dataAR['JS_filename'][] = ROOT_DIR.'/js/browsing/view.js';
              }
		/**
		 * if the jquery-ui theme directory is there in the template family,
		 * do not include the default jquery-ui theme but use the one imported
		 * in the .css file instead
		*/
		if (!isset($userObj->template_family) || $userObj->template_family=='') $userObj->template_family = ADA_TEMPLATE_FAMILY;

		if (!is_dir(ROOT_DIR.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
		{
			$layout_dataAR['CSS_filename'] = array(
					JQUERY_UI_CSS
			);
		} else $layout_dataAR['CSS_filename'] = array();

		array_push ($layout_dataAR['CSS_filename'],ROOT_DIR.'/external/mediaplayer/flowplayer-5.4.3/skin/minimalist.css');
		array_push ($layout_dataAR['CSS_filename'], JQUERY_NIVOSLIDER_CSS);
		array_push ($layout_dataAR['CSS_filename'],ROOT_DIR.'/js/include/jquery/nivo-slider/themes/default/default.css');
		
		$optionsAr['onload_func'] = 'initDoc();';
		
                if (is_object($navBar)) {
                    $content_dataAr['go_prev'] = $navBar->getHtml('prev'); // can pass href text as second param
                    $content_dataAr['go_next'] = $navBar->getHtml('next'); // can pass href text as second param
                }
        
        if(isset($msg))
        {
            $help=CDOMElement::create('label');
            $help->addChild(new CText(translateFN(ltrim($msg))));
            $divhelp=CDOMElement::create('div');
            $divhelp->setAttribute('id', 'help');
            $divhelp->addChild($help);
            $content_dataAr['help']=$divhelp->getHtml();
        }
        $menuOptions['self_instruction'] = $self_instruction;
        $menuOptions['id_course'] = $sess_id_course;
        $menuOptions['id_course_instance'] = $sess_id_course_instance;
        $menuOptions['id_node'] = $sess_id_node;
        $menuOptions['id_parent'] = $sess_id_node;
        
        /**
         * this is modified here to test parameters passing on new menu
         */
        $content_dataAr['test_history'] = 'op=test&id_course_instance='.$sess_id_course_instance.'&id_course='.$sess_id_course;
        
		ARE::render($layout_dataAR,$content_dataAr, null,$optionsAr,$menuOptions);

}


/**
 * preparing for static mode
 *
 * now managed by the class Cache Manager
 *
 */

$cacheObj->writeCachedData($id_profile,$layout_dataAR,$content_dataAr);
