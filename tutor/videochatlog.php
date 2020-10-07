<?php

/**
 * TUTOR.
 *
 * @package
 * @author      Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version	    0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user');

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

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once 'include/tutor_functions.inc.php';

$self =  whoami();
TutorHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */

$banner = include ROOT_DIR . '/include/banner.inc.php';

$online_users_listing_mode = 2;
$id_course_instance = $courseInstanceObj->getId();
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance, $online_users_listing_mode);

$content_dataAr = array(
    'course_title' => ucwords(translateFN('Log videochat')) . ' &gt; ' .$courseObj->getTitle() . ' &gt; ' . $courseInstanceObj->getTitle(),
    'banner' => $banner,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'edit_profile' => $userObj->getEditProfilePage(),
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'help'  => isset($help) ? $help : null,
    // 'dati'  => $data,
    'status' => $status,
    'chat_users' => $online_users,
    'chat_link' => isset($chat_link) ? $chat_link : ''
);

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    SEMANTICUI_DATATABLE_CSS,
);
$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    ROOT_DIR . '/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
    JQUERY_NO_CONFLICT
);

$menuOptions = array();
if (isset($id_course))   $menuOptions['id_course'] = $id_course;
if (isset($id_instance)) $menuOptions['id_instance'] = $id_instance;
if (isset($id_instance)) $menuOptions['id_course_instance'] = $id_instance;
if (isset($id_student))  $menuOptions['id_student'] = $id_student;
/**
 * add a define for the supertutor menu item to appear
 */
if ($userObj instanceof ADAPractitioner && $userObj->isSuper()) define('IS_SUPERTUTOR', true);
else define('NOT_SUPERTUTOR', true);

$optionsAr['onload_func'] = 'initDoc(' . $courseObj->getId() .', '.$courseInstanceObj->getId() . ');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr, $menuOptions);
