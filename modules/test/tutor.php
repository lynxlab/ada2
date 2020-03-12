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
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_TUTOR => array('layout', 'course', 'course_instance'),
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

$self = whoami();

if (!isset($course_instanceObj) || !is_a($course_instanceObj,'Course_instance')) {
	$course_instanceObj = read_course_instance_from_DB($_GET['id_course_instance']);
}

require_once(MODULES_TEST_PATH.'/include/management/tutorManagementTest.inc.php');
$management = new TutorManagementTest($_GET['op'],$courseObj,$course_instanceObj,
		isset($_GET['id_student']) ? $_GET['id_student'] : null,
		isset($_GET['id_test']) ? $_GET['id_test'] : null,
		isset($_GET['id_history_test']) ? $_GET['id_history_test'] : null);
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
    'course_title' => '<a href="'.HTTP_ROOT_DIR.'/tutor/tutor.php">'.translateFN('Modulo Tutor').'</a> > ',
    //'media' => 'media',
);

if (isset($other_node_data['notes'])) $content_dataAr['notes'] = $other_node_data['notes'];
if (isset($other_node_data['private_notes'])) $content_dataAr['personal'] = $other_node_data['private_notes'];

if ($reg_enabled && isset($add_bookmark)) {
    $content_dataAr['add_bookmark'] = $add_bookmark;
} else {
    $content_dataAr['add_bookmark'] = "";
}

if (isset($bookmark)) $content_dataAr['bookmark'] = $bookmark;
if (isset($go_bookmarks)) $content_dataAr['go_bookmarks_1'] = $go_bookmarks;
if (isset($go_bookmarks)) $content_dataAr['go_bookmarks_2'] = $go_bookmarks;

if ($com_enabled) {
    if (isset($ajax_chat_link)) $content_dataAr['ajax_chat_link'] = $ajax_chat_link;
    $content_dataAr['messages'] = $user_messages->getHtml();
    $content_dataAr['agenda'] = $user_agenda->getHtml();
    $content_dataAr['events'] = $user_events->getHtml();
    if (isset($online_users)) $content_dataAr['chat_users'] = $online_users;
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
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS
);

ARE::render($layout_dataAr, $content_dataAr);
