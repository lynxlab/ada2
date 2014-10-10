<?php
/**
 * Add exercise
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
        AMA_TYPE_AUTHOR => array('layout', 'node', 'course', 'course_instance'),
);

require_once ROOT_DIR.'/include/module_init.inc.php';

//$self =  whoami();
$self = 'form';

require_once(ROOT_DIR.'/services/include/author_functions.inc.php');
$layout_dataAr['node_type'] = $self;

$online_users_listing_mode = 2;
$online_users = ADAGenericUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');
require_once(MODULES_TEST_PATH.'/include/management/managementTest.inc.php');
require_once(MODULES_TEST_PATH.'/include/management/rootManagementTest.inc.php');
//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

/*
 * Generazione dei form per l'inserimento dell'esercizio.
 * 
*/
switch($_GET['mode']) {
	default:
	case 'test':
		require_once(MODULES_TEST_PATH.'/include/management/testManagementTest.inc.php');
		$management = new TestManagementTest($_GET['action'],$_GET['id_test']);
	break;
	case 'survey':
		require_once(MODULES_TEST_PATH.'/include/management/surveyManagementTest.inc.php');
		$management = new SurveyManagementTest($_GET['action'],$_GET['id_test']);
	break;
}

$form_return = $management->run();

// per la visualizzazione del contenuto della pagina
$banner = include ($root_dir.'/include/banner.inc.php');

$content_dataAr = array(
        'head'=>$head_form,
        'banner'=>$banner,
		'path'=>$form_return['path'],
        'form'=>$form_return['html'],
        'status'=>$form_return['status'],
        'user_name'=>$user_name,
        'user_type'=>$user_type,
        'messages'=>$user_messages->getHtml(),
        'agenda'=>$user_agenda->getHtml(),
        'title'=>$node_title,
        'course_title'=>$course_title,
        'back'=>$back
);

$content_dataAr['notes'] = $other_node_data['notes'];
$content_dataAr['personal'] = $other_node_data['private_notes'];

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = $add_bookmark;
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = $bookmark;
$content_dataAr['go_bookmarks_1'] = $go_bookmarks;
$content_dataAr['go_bookmarks_2'] = $go_bookmarks;

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
	JQUERY_NO_CONFLICT
);

ARE::render($layout_dataAr, $content_dataAr);