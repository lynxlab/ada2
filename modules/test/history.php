<?php
/**
 * TEST.
 *
 * @package		test
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		test
 * @version		0.1
 */

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'course', 'course_instance', 'user'),
);

/**
 * Performs basic controls before entering this module
 */
if(isset($_REQUEST['isAjax']) && intval($_REQUEST['isAjax'])===1) $trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');

//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if ($courseInstanceObj instanceof Course_instance) {
    $self_instruction = $courseInstanceObj->getSelfInstruction();
}
if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    $self='tutorSelfInstruction';
}
else
{
$self = 'tutor';
}

if (!isset($course_instanceObj) || !is_a($course_instanceObj,'Course_instance')) {
	$course_instanceObj = read_course_instance_from_DB($_GET['id_course_instance']);
}

require_once(MODULES_TEST_PATH.'/include/management/historyManagementTest.inc.php');
$management = new HistoryManagementTest($_GET['op'],$courseObj,$course_instanceObj,$_SESSION['sess_id_user'],
		isset($_GET['id_test']) ? $_GET['id_test'] : null,
		isset($_GET['id_history_test']) ? $_GET['id_history_test'] : null);
$return = $management->render();
$text = $return['html'];
$title = $return['title'];
$path = $return['path'];
/**
 * if it's an ajax request, put the id_test in session
 * so that it'll be opened on next page load (whenever it
 * will occour), output the html table and die
 */
if(isset($_REQUEST['isAjax']) && intval($_REQUEST['isAjax'])===1) {
	if (isset($_GET['id_test']) && intval($_GET['id_test'])>0 &&
			isset($_GET['id_student']) && intval($_GET['id_student'])>0) {
				$_SESSION['list_history_tests_id'] = intval($_GET['id_test']).'_'.intval($_GET['id_student']);
			}
			die ($text);
}

/*
 * Go back link
 */
$navigation_history = $_SESSION['sess_navigation_history'];
$last_visited_node  = $navigation_history->lastModule();
$go_back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
$go_back_link->addChild(new CText(translateFN('Indietro')));

/*
 * Edit profile
 */

$edit_profile=$userObj->getEditProfilePage();

if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction))
{
    $edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile.'?self_instruction=1');
}
else
{
    $edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
}
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

/*
 * link Naviga
 
$naviga=CDOMElement::create('a','#');
$naviga->setAttribute(onclick, "toggleElementVisibility('menuright', 'right')");
$naviga->setAttribute('class', 'positionNaviga');
$naviga->addChild(new CText(translateFN('Naviga')));
*/
/*
 * Output
 */
$content_dataAr = array(
    'status' => translateFN('Navigazione'),
    'path' => $path,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'user_level' => $user_level,
    'visited' => '-',
    'icon' => isset($icon) ? $icon : '',
    //'navigation_bar' => $navBar->getHtml(),
    'text' =>  $text,
    'go_back' => $go_back_link->getHtml(),
    'title' => $title,
    'author' => isset($author) ? $author : '',
    'node_level' => 'livello nodo',
    'edit_profile'=> $edit_profile_link->getHtml(),
    'naviga'=>$go_back_link->getHtml()
    //'course_title' => '<a href="'.HTTP_ROOT_DIR.'/tutor/tutor.php">'.translateFN('Modulo Tutor').'</a> > ',
    //'media' => 'media',
);

$content_dataAr['notes'] = isset($other_node_data['notes']) ? $other_node_data['notes'] : null;
$content_dataAr['personal'] = isset($other_node_data['private_notes']) ? $other_node_data['private_notes'] : null;


if ($log_enabled)
    $content_dataAr['go_history'] = isset($go_history) ? $go_history : '';
else
    $content_dataAr['go_history'] = translateFN("cronologia");

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = isset($add_bookmark) ? $add_bookmark : '';
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = isset($bookmark) ? $bookmark : "";
$content_dataAr['go_bookmarks_1'] = isset($go_bookmarks) ? $go_bookmarks : "";
$content_dataAr['go_bookmarks_2'] = isset($go_bookmarks) ? $go_bookmarks : "";

if ($mod_enabled) {
	$content_dataAr['add_node'] = isset($add_node) ? $add_node : '';
	$content_dataAr['edit_node'] = isset($edit_node) ? $edit_node : '';
	$content_dataAr['delete_node'] = isset($delete_node) ? $delete_node : '';
	$content_dataAr['send_media'] = isset($send_media) ? $send_media : '';
	$content_dataAr['add_exercise'] = isset($add_exercise)  ? $add_exercise : '';
	$content_dataAr['add_note'] = isset($add_note) ? $add_note : '';
	$content_dataAr['add_private_note'] = isset($add_private_note) ? $add_private_note : '';
	$content_dataAr['edit_note'] = isset($edit_note) ? $edit_note : '';
	$content_dataAr['delete_note'] = isset($delete_note) ? : '';
	$content_dataAr['import_exercise'] = isset($import_exercise) ? $import_exercise : '';
} else {
    $content_dataAr['add_node'] = '';
    $content_dataAr['edit_node'] = '';
    $content_dataAr['delete_node'] = '';
    $content_dataAr['send_media'] = '';
    $content_dataAr['add_note'] = '';
    $content_dataAr['add_private_note'] = '';
    $content_dataAr['edit_note'] = '';
    $content_dataAr['delete_note'] = '';
}

if ($com_enabled) {
    $content_dataAr['ajax_chat_link'] = isset($ajax_chat_link) ? $ajax_chat_link : "";
    $content_dataAr['messages'] = $user_messages->getHtml();
    $content_dataAr['agenda'] = $user_agenda->getHtml();
    $content_dataAr['events'] = $user_events->getHtml();
    $content_dataAr['chat_users'] = isset($online_users) ? $online_users : "";
} else {
    $content_dataAr['chat_link'] = translateFN("chat non abilitata");
    $content_dataAr['messages'] = translateFN("messaggeria non abilitata");
    $content_dataAr['agenda'] = translateFN("agenda non abilitata");
    $content_dataAr['chat_users'] = "";
}

$layout_dataAr['JS_filename'] = array(
	JQUERY,
	JQUERY_UI,
	MODULES_TEST_PATH.'/js/jquery/jquery.mCustomScrollbar.concat.min.js',
	JQUERY_JPLAYER,	
	JQUERY_DATATABLE,
	ROOT_DIR . '/js/include/jquery/dataTables/dateSortPlugin.js',			
	JQUERY_NO_CONFLICT,
	MODULES_TEST_PATH.'/js/dragdrop.js',
	MODULES_TEST_PATH.'/js/index.js',		
	ROOT_DIR. '/external/mediaplayer/flowplayer-5.4.3/flowplayer.js'		
);

if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction)) 
     {        
     
    $layout_dataAr['JS_filename'][]=
        ROOT_DIR.'/modules/test/js/tutor.js';   //for tutorSelfInstruction.tpl
     }
     
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS,
	JQUERY_JPLAYER_CSS,
	JQUERY_DATATABLE_CSS,		
	ROOT_DIR.'/external/mediaplayer/flowplayer-5.4.3/skin/minimalist.css',
	MODULES_TEST_PATH.'/template/jquery.mCustomScrollbar.css'		
);

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
$content_dataAr['user_avatar'] = $avatar->getHtml();

$optionsAr['onload_func']  = "initDoc(";
if (isset($_SESSION['list_history_tests_id']) && intval($_SESSION['list_history_tests_id'])) {
	$optionsAr['onload_func'] .= '\''.urlencode(json_encode(array('openTestID'=>$_SESSION['list_history_tests_id']))).'\'';
}
$optionsAr['onload_func'] .= ");";

$menuOptions['self_instruction'] = $self_instruction;
ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr,$menuOptions);