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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'tutor', 'node', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('layout', 'node', 'course', 'course_instance'),
    AMA_TYPE_AUTHOR => array('layout', 'node', 'course', 'course_instance'),
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

$self = whoami();

$test = NodeTest::readTest($_GET['id_test']);
if (!AMATestDataHandler::isError($test)) {
	$test->run();
	$text = $test->render(true);
	$title = $test->titolo;

	$nodeObj = new Node($test->id_nodo_riferimento); //present in session
	$path = $nodeObj->findPathFN();

	$courseObj = new Course($sess_id_course); //present in session
	$course_title = $courseObj->titolo;

	$courseInstanceObj = new Course_instance($sess_id_course_instance); //present in session
	if (!empty($courseInstanceObj->title)) {
		$course_title.= ' - '.$courseInstanceObj->title;
	}

	if ($id_profile == AMA_TYPE_AUTHOR) {
		if (is_a($test,'TestTest')) {
			$what = translateFN('test');
			$mode = 'test';
		}
		else if (is_a($test,'SurveyTest')) {
			$what = translateFN('sondaggio');
			$mode = 'survey';
		}

		$edit_test_link = CDOMElement::create('a', 'href:edit_test.php?mode='.$mode.'&action=mod&id_test='.$test->id_nodo);
		$edit_test_link->addChild(new CText(sprintf(translateFN('Modifica %s'),$what)));
		$edit_test_link = $edit_test_link->getHtml();

		$delete_test_link = CDOMElement::create('a', 'href:edit_test.php?mode='.$mode.'&action=del&id_test='.$test->id_nodo);
		$delete_test_link->addChild(new CText(sprintf(translateFN('Cancella %s'),$what)));
		$delete_test_link = $delete_test_link->getHtml();
	}
}
else {
	$text = translateFN('Impossibile trovare il test');
	$title = translateFN('Si è verificato un errore');
	$course_title = translateFN('Si è verificato un errore');
}

/*
 * Go back link
 */
$navigation_history = $_SESSION['sess_navigation_history'];
$last_visited_node  = $navigation_history->lastModule();
$go_back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
$go_back_link->addChild(new CText(translateFN('Indietro')));

$back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
$back_link->addChild(new CText(translateFN('Torna')));


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
	'back_link' => $back_link->getHtml(),
    'edit_test' => $edit_test_link,
	'delete_test' => $delete_test_link,
	'add_topic' => $add_topic_link,
	'add_question' => $add_question_link,
    'title' => $title,
    'author' => $author,
    'node_level' => 'livello nodo',
    'course_title' => '<a href="../../browsing/main_index.php">'.$course_title.'</a> > ',
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
	MODULES_TEST_PATH.'/js/variation.js',
	MODULES_TEST_PATH.'/js/erasehighlight.js',
	ROOT_DIR.'/js/browsing/virtual_keyboard.js',
);
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS
);

ARE::render($layout_dataAr, $content_dataAr);