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

if (!is_a($course_instanceObj,'Course_instance')) {
	$course_instanceObj = read_course_instance_from_DB($_GET['id_course_instance']);
}

require_once(MODULES_TEST_PATH.'/include/management/historyManagementTest.inc.php');
$management = new HistoryManagementTest($_GET['op'],$courseObj,$course_instanceObj,$_SESSION['sess_id_user'],$_GET['id_test'],$_GET['id_history_test']);
$return = $management->render();
$text = $return['html'];
$title = $return['title'];
$path = $return['path'];

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
    'icon' => $icon,
    //'navigation_bar' => $navBar->getHtml(),
    'text' =>  $text,
    'go_back' => $go_back_link->getHtml(),
    'title' => $title,
    'author' => $author,
    'node_level' => 'livello nodo',
    'edit_profile'=> $edit_profile_link->getHtml(),
    'naviga'=>$go_back_link->getHtml()
    //'course_title' => '<a href="'.HTTP_ROOT_DIR.'/tutor/tutor.php">'.translateFN('Modulo Tutor').'</a> > ',
    //'media' => 'media',
);

$content_dataAr['notes'] = $other_node_data['notes'];
$content_dataAr['personal'] = $other_node_data['private_notes'];


if ($log_enabled)
    $content_dataAr['go_history'] = $go_history;
else
    $content_dataAr['go_history'] = translateFN("cronologia");

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = $add_bookmark;
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = $bookmark;
$content_dataAr['go_bookmarks_1'] = $go_bookmarks;
$content_dataAr['go_bookmarks_2'] = $go_bookmarks;

if ($mod_enabled) {
    $content_dataAr['add_node'] = $add_node;
    $content_dataAr['edit_node'] = $edit_node;
    $content_dataAr['delete_node'] = $delete_node;
    $content_dataAr['send_media'] = $send_media;
    $content_dataAr['add_exercise'] = $add_exercise;
    $content_dataAr['add_note'] = $add_note;
    $content_dataAr['add_private_note'] = $add_private_note;
    $content_dataAr['edit_note'] = $edit_note;
    $content_dataAr['delete_note'] = $delete_note;
    $content_dataAr['import_exercise'] = $import_exercise;
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
    $content_dataAr['ajax_chat_link'] = $ajax_chat_link;
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

$layout_dataAr['JS_filename'] = array(
	JQUERY,
	JQUERY_UI,
	JQUERY_NO_CONFLICT,
	MODULES_TEST_PATH.'/js/dragdrop.js',
	ROOT_DIR.'/js/browsing/virtual_keyboard.js',
);

if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction)) 
     {        
     
    $layout_dataAr['JS_filename'][]=
        ROOT_DIR.'/modules/test/js/tutor.js';   //for tutorSelfInstruction.tpl
     }
     
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS
);

if($userObj->tipo==AMA_TYPE_STUDENT && ($self_instruction)) 
     {        
     
    $layout_dataAr['CSS_filename'][] = 
        ROOT_DIR.'/modules/test/layout/ada_blu/css/tutor.css';   //for tutorSelfInstruction.tpl
     }

ARE::render($layout_dataAr, $content_dataAr);
