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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout', 'course'),
);

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/switcher/include/switcher_functions.inc.php');

require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');

//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$self = whoami();

if (!is_a($courseObj,'Course')) {
	$courseObj = read_course_from_DB($_GET['id_course']);
}

require_once(MODULES_TEST_PATH.'/include/management/switcherManagementTest.inc.php');
$management = new SwitcherManagementTest($courseObj);
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

$content_dataAr = array(
	'path' => $path,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $title,
	'title' => $title,
    'help' => isset($help) ? $help : '',
    'text' => $text,
    'go_back' => $go_back_link->getHtml(),    
    'module' => isset($module) ? $module : '',
    'ajax_chat_link' => '<a href="'.HTTP_ROOT_DIR . '/comunica/list_chatrooms.php">'. translateFN('Lista chatrooms'),
    'messages' => $user_messages->getHtml(),
	'course_title' => '<a href="'.HTTP_ROOT_DIR.'/switcher/switcher.php">'.translateFN('Modulo Switcher').'</a> > ',
	'back_link' => $go_back_link->getHtml(),
);

$content_dataAr['notes'] = isset($other_node_data['notes']) ? $other_node_data['notes'] : '';
$content_dataAr['personal'] = isset($other_node_data['private_notes']) ? $other_node_data['private_notes'] : '';



if ($log_enabled)
    $content_dataAr['go_history'] = isset($go_history) ? $go_history : '';
else
    $content_dataAr['go_history'] = translateFN("cronologia");

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = isset($add_bookmark) ? $add_bookmark : '';
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = isset($bookmark) ? $bookmark : '';
$content_dataAr['go_bookmarks_1'] = isset($go_bookmarks) ? $go_bookmarks : '';
$content_dataAr['go_bookmarks_2'] = isset($go_bookmarks) ? $go_bookmarks : '';

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
	JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS
);

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
$content_dataAr['user_avatar'] = $avatar->getHtml();

ARE::render($layout_dataAr, $content_dataAr);