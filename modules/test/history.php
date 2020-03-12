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
BrowsingHelper::init($neededObjAr);

// require_once(MODULES_TEST_PATH.'/config/config.inc.php');
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
    'icon' => isset($icon) ? $icon: '',
    //'navigation_bar' => $navBar->getHtml(),
    'text' =>  $text,
    'title' => $title,
    'author' => isset($author) ? $author : '',
    'node_level' => 'livello nodo',
    'edit_profile'=> $userObj->getEditProfilePage()
    //'course_title' => '<a href="'.HTTP_ROOT_DIR.'/tutor/tutor.php">'.translateFN('Modulo Tutor').'</a> > ',
    //'media' => 'media',
);

$content_dataAr['notes'] = isset($other_node_data['notes']) ? $other_node_data['notes'] : null;
$content_dataAr['personal'] = isset($other_node_data['private_notes']) ? $other_node_data['private_notes'] : null;

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = isset($add_bookmark) ? $add_bookmark : "";
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = isset($bookmark) ? $bookmark : "";
$content_dataAr['go_bookmarks_1'] = isset($go_bookmarks) ? $go_bookmarks : "";
$content_dataAr['go_bookmarks_2'] = isset($go_bookmarks) ? $go_bookmarks : "";

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
$menuOptions['self_instruction'] = $self_instruction;
ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
