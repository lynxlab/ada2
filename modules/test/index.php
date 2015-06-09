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

/**
 * if isset $_GET['unload'] means that the system is closing test, so there
 * is no need to save the page in NavigationHistor and to ask module_init
 * for any needed object.
 * 
 * Note that this MUST be done before the actual module_init inclusion.
 */ 
  
if (isset($_GET['unload'])) {
	$neededObjAr = array();
    $trackPageToNavigationHistory=false;
}
require_once(ROOT_DIR.'/include/module_init.inc.php');

/**
 * @author giorgio 31/ott/2014
 * 
 * index.js will ajax-call this file passing an 'unload' parameter to close
 * the test/activity history.
 * 
 *  In certain cases (e.g. when exiting towards browsing/user.php) could be
 *  that the session no longer contains a selected tester and/or a course
 *  instance ID.
 *  
 *  These two values are therfore saved in two 'close_test_' prefixed session
 *  variables used in this module only instead of the 'sess_' prefixed session
 *  usual variables.
 *  
 *  When doing an unload these variables are unset and the script dies just
 *  after the test/activity logic has run. There's really no need to continue
 *  the computation that is just for proper page rendering purposes.
 *  
 *  Note that also the dataHandler is being disconnected on unload to ensure it
 *  is valid and that this code MUST be just before promoting it to an 
 *  AMATestDataHandler.
 */

// save the selected_tester in close_test_provide
if (isset($_SESSION['sess_selected_tester']) && strlen($_SESSION['sess_selected_tester'])>0) {
	$_SESSION['close_test_provider'] = $_SESSION['sess_selected_tester'];
}
// save the id_course_instance in close_test_instance
if (isset($_SESSION['sess_id_course_instance']) && strlen($_SESSION['sess_id_course_instance'])>0) {
	$_SESSION['close_test_instance'] = $_SESSION['sess_id_course_instance'];
}

require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');

// disconnect the dataHandler on unload
if (isset($_GET['unload']) && isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['close_test_provider']));

//$self = whoami();
if (isset($courseInstanceObj) && $courseInstanceObj instanceof Course_instance) {
    $self_instruction = $courseInstanceObj->getSelfInstruction();
} else $self_instruction = 0;

if ($userObj instanceof ADAGuest  || (isset($courseObj) && $courseObj instanceof Course && $courseObj->getIsPublic() && $userObj->getType()!=AMA_TYPE_AUTHOR)) {
    $self = 'guest_view';
}
elseif(RootTest::isSessionUserAStudent() && ($self_instruction)) {
	$self='indexSelfInstruction';
}
else {
	$self = whoami();
}
/**
 * @author giorgio 08/ott/2013
 * added preview mode by temporarily switching the user type
 * it will be restored just before the call to the rendering engine
 * 
 * additions start here
 */
$isPreview = (isset($action) && trim($action)==='preview');
if ($isPreview)
{
	$savedSessUserType = $_SESSION['sess_id_user_type'];
	$_SESSION['sess_id_user_type'] = AMA_TYPE_STUDENT;
	$id_profile = $_SESSION['sess_id_user_type'];
}
/**
 * @author giorgio 08/ott/2013
 * additions end here
 */
$test = NodeTest::readTest($_GET['id_test']);
if (!AMATestDataHandler::isError($test)) {

	/**
	 * If user has completed or has a terminated status for the instance,
	 * redirect to $test->id_nodo_riferimento or its parent depending on 
	 * ADA_REDIRECT_TO_TEST being true or false
	 */
	if (is_a($test,'TestTest') && !is_a($test,'ActivityTest') && isset($sess_id_course_instance) &&
		intval($sess_id_course_instance)>0 && RootTest::isSessionUserAStudent($sess_id_course_instance) && 
		in_array($userObj->get_student_status($userObj->getId(),$sess_id_course_instance), 
			array(ADA_STATUS_COMPLETED, ADA_STATUS_TERMINATED)) ) {
				/**
				 * @author giorgio 07/apr/2015
				 *
				 * if user has the terminated status for the course instance, redirect to view
				 */
				$id_node = $sess_id_course.'_0'; // if nothing better is found, redirect to course root node
				if (!ADA_REDIRECT_TO_TEST) {
					/**
					 * if !ADA_REDIRECT_TO_TEST, redirecting to $test->id_nodo_riferimento
					 * shall not cause a redirection loop 
					 */
					$id_node = $test->id_nodo_riferimento;
				} else {
					/**
					 * else redirect to the parent of $test->id_nodo_riferimento
					 */
					$nodeInfo = $GLOBALS['dh']->get_node_info($test->id_nodo_riferimento);
					if (!AMA_DB::isError($nodeInfo) && isset($nodeInfo['parent_id']) && strlen($nodeInfo['parent_id'])>0) {
						$id_node = $nodeInfo['parent_id'];
					}
				}
				redirect(HTTP_ROOT_DIR . '/browsing/view.php?id_node='.$id_node.'&id_course='.$sess_id_course.
						'&id_course_instance='.$sess_id_course_instance);
	}
	
	/**
	 * @author giorgio 10/ott/2013
	 * set the preview mode where appropriate
	 */
	if ($isPreview && is_a($test,'TestTest')) $test->setPreview($isPreview);
	$test->run();
	$text = $test->render(true);
	$title = $test->titolo;
	
	/**
	 * as stated above, if it's an unload unset session vars and die
	 */
	if (isset($_GET['unload'])) {
		if (trim($_GET['unload'])==='force') {
			unset($_SESSION['close_test_provider']);
			unset($_SESSION['close_test_instance']);
		}
		die();
	}
        
	$nodeObj = new Node($test->id_nodo_riferimento); //present in session
	$path = $nodeObj->findPathFN();

	$courseObj = new Course($sess_id_course); //present in session
	$course_title = $courseObj->titolo;
        
	$courseInstanceObj = new Course_instance($sess_id_course_instance); //present in session
	if (!empty($courseInstanceObj->title)) {
		$course_title.= ' - '.$courseInstanceObj->title;
	}

	if ($id_profile == AMA_TYPE_AUTHOR) {
		if (is_a($test,'TestTest') && !is_a($test,'ActivityTest')) {
			$what = translateFN('test');
			$mode = 'test';
		}
		/**
		 * @author giorgio 24/ott/2013
		 * added else if for activity
		 */		
		else if (is_a($test,'ActivityTest')) {
			$what = translateFN('attività');
			$mode = 'activity';
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
	/**
	 * giorgio, add the navigation bar, this should
	 * only work in ICoN installation that has the
	 * proper template_field 
	 */
	if (is_a($test,'ActivityTest')) {
		require_once MODULES_TEST_PATH . '/include/DFSTestNavigationBar.inc.php';
		$navBar = new DFSTestNavigationBar( $test, array ('userLevel' => $user_level) );
   } else $navBar = false;
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

/**
 * Users (types) allowed to access forum and timeline link.
 */
$allowedUsersForumAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);


/*
 * timeline and forum link
 */
if(in_array($userObj->getType(), $allowedUsersForumAr)&& (!$courseInstanceObj->getSelfInstruction()))
{
    $timeline_link = '<a href="' . HTTP_ROOT_DIR . '/browsing/timeline.php?id_course=' . $courseObj->getId() . '&id_course_instance=' . $courseInstanceObj->getId() .'">'.translateFN('timeline').'</a>';
    
    $go_download = '<a href="' . HTTP_ROOT_DIR . '/browsing/download.php">' . translateFN('vai ai File della tua classe') . '</a>';
    $send_media = '<a href="' . HTTP_ROOT_DIR . '/services/upload.php">' . translateFN('invia un File alla tua classe') . '</a>';
    $li_forum=CDOMElement::create('li','id:com');
    $li_forum->setAttribute('class', 'unselectedcom');
    $li_forum->setAttribute('onclick',"toggleElementVisibility('submenu_com','up')");
    $forum=CDOMElement::create('a','href:#');
    $forum->addChild(new CText(translateFN('forum')));
    $li_forum->addChild($forum);
    $forum_link=$li_forum->getHtml();
}
else
{
    $li_forum=CDOMElement::create('li','id:com');
    $li_forum->setAttribute('class', 'disable');
    $li_forum->setAttribute('onclick',"toggleElementVisibility('submenu_com','up')");
    $forum=CDOMElement::create('a','href:#');
    $forum->addChild(new CText(translateFN('forum')));
    $li_forum->addChild($forum);
    $forum_link=$li_forum->getHtml();
}
$back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
$back_link->addChild(new CText(translateFN('Torna')));

/*
 * Add note link
 */
if(($userObj->getType()==AMA_TYPE_STUDENT || $userObj->getType()==AMA_TYPE_TUTOR) && (!$courseInstanceObj->getSelfInstruction()))
{
    $add_note = "<a href=\"$http_root_dir/services/addnode.php?id_parent=$test->id_nodo_riferimento&id_course=$sess_id_course&type=NOTE\">" .translateFN('scrivi una nota nel Forum') . '</a>';
    $add_private_note = "<a href=\"$http_root_dir/services/addnode.php?id_parent=$test->id_nodo_riferimento&id_course=$sess_id_course&type=PRIVATE_NOTE\">" .translateFN('scrivi una nota personale') . '</a>';
}

/**
 * @author giorgio 08/ott/2013
 * if in preview mode, adds a div to notify the user
 * and the javascript will use it as well as a condition
 * to hide the navigation bar and the form buttons
 *
 * additions start here
 */
if ($isPreview)
{
	$previewDIV = CDOMElement::create('div','id:preview');
	$previewDIV->addChild (new CText(translateFN('Anteprima')));
	$text .= $previewDIV->getHtml();
}
/**
 * @author giorgio 08/ott/2013
 * additions end here
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
    'navigation_bar_top' => ($navBar!==false) ? $navBar->getHtml('top') : '',
	'navigation_bar_bottom' => ($navBar!==false) ? $navBar->getHtml('bottom') : '',
    'text' =>  $text,
    'go_back' => $go_back_link->getHtml(),
    'back_link' => $back_link->getHtml(),
    'go_download'=>isset($go_download) ? $go_download : '',
    'edit_test' => isset($edit_test_link) ? $edit_test_link : '',
    'delete_test' => isset($delete_test_link) ? $delete_test_link : '',
    'add_topic' => isset($add_topic_link) ? $add_topic_link : '',
    'add_question' => isset($add_question_link) ? $add_question_link : '',
    // giorgio 14/ott/2013 , substitute title with course title + class name 
    // 'title' => $title,
    'title'=>$course_title,
    'author' => isset($author) ? $author : '',
    'node_level' => 'livello nodo',
    'course_title' => '<a href="../../browsing/main_index.php">'.$course_title.'</a> > ',
    'timeline' => '<li>'.(isset($timeline_link) ? $timeline_link : '' ).'</li>',
    'forum'=>$forum_link
    //'media' => 'media',
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
    $content_dataAr['ajax_chat_link'] = isset($ajax_chat_link) ? $ajax_chat_link : '';
    $content_dataAr['messages'] = $user_messages->getHtml();
    $content_dataAr['agenda'] = $user_agenda->getHtml();
    $content_dataAr['events'] = $user_events->getHtml();
    $content_dataAr['chat_users'] = isset($online_users) ? $online_users : '';
} else {
    $content_dataAr['chat_link'] = translateFN("chat non abilitata");
    $content_dataAr['messages'] = translateFN("messaggeria non abilitata");
    $content_dataAr['agenda'] = translateFN("agenda non abilitata");
    $content_dataAr['chat_users'] = "";
}
 if ($id_profile == AMA_TYPE_STUDENT)
 {
    $content_dataAr['exercise_history'] = '<a href="../../browsing/exercise_history.php?id_course_instance='.$sess_id_course_instance.'">'.translateFN('Cronologia Esercizi A').'</a>';
    if (MODULES_TEST) {
         $content_dataAr['test_history'] = 'op=test&id_course_instance='.$sess_id_course_instance.'&id_course='.$sess_id_course;
        //$content_dataAr['survey_history'] = '<a href="'.MODULES_TEST_HTTP.'/history.php?op=survey&id_course_instance='.$sess_id_course_instance.'&id_course='.$sess_id_course.'">'.translateFN('Storico Sondaggi').'</a>';
    }
                        
}


$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		MODULES_TEST_PATH.'/js/jquery/jquery.mCustomScrollbar.concat.min.js',
// 		ROOT_DIR.'/js//include/jquery/ui/jquery.ui.touch-punch.min.js',
// 		ROOT_DIR.'/js//include/jquery/ui/jquery.hammer-full.js',
		JQUERY_JPLAYER,
		JQUERY_NO_CONFLICT,
		MODULES_TEST_PATH.'/js/dragdrop.js',
		MODULES_TEST_PATH.'/js/variation.js',
		MODULES_TEST_PATH.'/js/erasehighlight.js',
// 		ROOT_DIR. '/external/mediaplayer/flowplayer-5.4.3/flowplayer.js'
);

$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS,
	JQUERY_JPLAYER_CSS,
// 	ROOT_DIR.'/external/mediaplayer/flowplayer-5.4.3/skin/minimalist.css',
	MODULES_TEST_PATH.'/template/jquery.mCustomScrollbar.css'
);

/**
 * @author giorgio 29/mag/2014
 *
 * add proper css and js for mobile devices
 */
if (isset($_SESSION['mobile-detect']) && $_SESSION['mobile-detect']->isMobile()) {
	$userTemplateFamily = (!is_null($userObj->template_family) && (strlen($userObj->template_family)>0)) ? $userObj->template_family : ADA_TEMPLATE_FAMILY ;
	array_push ($layout_dataAr['CSS_filename'], MODULES_TEST_PATH.'/layout/'.$userTemplateFamily.'/css/'.$self.'-mobile.css');
	/**
     * add the jquery.ui.touch-punch.min.js just before 
     * JQUERY_NO_CONFLICT in the $layout_dataAr['JS_filename'] array
	 */
	$noConflictKey = array_search(JQUERY_NO_CONFLICT, $layout_dataAr['JS_filename']);
	array_splice($layout_dataAr['JS_filename'], $noConflictKey, 0, ROOT_DIR.'/js//include/jquery/ui/jquery.ui.touch-punch.min.js');
}

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
$content_dataAr['user_avatar'] = $avatar->getHtml();

/**
 * added here to test new menu
 */
if ($id_profile == AMA_TYPE_AUTHOR) {
	$content_dataAr['edit_test'] = 'mode='.$mode.'&action=mod&id_test='.$test->id_nodo;
	$content_dataAr['delete_test'] = 'mode='.$mode.'&action=del&id_test='.$test->id_nodo;
}
$content_dataAr['go_back'] = $last_visited_node;
$content_dataAr['what'] = isset($what) ? $what : '';
$menuOptions['id_course_instance'] = $courseInstanceObj->getId();
$menuOptions['id_course'] = $courseObj->getId();
$menuOptions['id_parent'] = $test->id_nodo_riferimento;
$menuOptions['self_instruction'] = (isset($courseInstanceObj) &&
								    $courseInstanceObj instanceof Course_instance &&
									$courseInstanceObj->getSelfInstruction());
/**
 * @author giorgio 08/ott/2013
 * restore user type
 * additions start here
 */
if ($isPreview) $_SESSION['sess_id_user_type'] = $savedSessUserType;
/**
 * @author giorgio 08/ott/2013
 * additions end here
 */
ARE::render($layout_dataAr, $content_dataAr, null, null, $menuOptions);
