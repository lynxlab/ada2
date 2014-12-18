<?php
/**
 * error.php
 *
 * This page is displayed when a fatal error occurs.
 *
 * PHP version >= 5.0
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009-2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/config_path.inc.php';

$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR,
                        AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN);
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout'),
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_ADMIN => array('layout')
);
/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/index_functions.inc.php';


/*
 * By default, on a fatal error, we redirect the user to the login page.
 */
$homepage = HTTP_ROOT_DIR;
/*
 * Here we check if there's a logged user, with a proper navigation history.
 * If both exist, we check if the module that have raised a fatal error is
 * different from the user's home page.
 * In this case, it is safe to redirect him to his/her homepage.
 * Otherwise he/she will be redirected to the ada login page.
 */
if (isset($_SESSION['sess_userObj'])) {
    $userObj = $_SESSION['sess_userObj'];
    if ($userObj instanceof ADALoggableUser) {
       $user_name = $userObj->getFirstName();
       $user_type = $userObj->getTypeAsString();
       $userHomePage = $userObj->getHomePage();

       if (isset($_SESSION['sess_navigation_history'])) {
            $navigationHistory = $_SESSION['sess_navigation_history'];
            if ($navigationHistory instanceof NavigationHistory) {
                if ($navigationHistory->lastModule() != $userHomePage) {
                    $homepage = $userHomePage;
                }
            }
        }
    }
}

$error_message = translateFN('A fatal error occurred. You can try to enter your home page. If it does not work, please contact the webmaster.');

$error_div = '<div class="unrecoverable">'
           . $error_message
           . '</div>';

$content_dataAr = array(
    'home_link' => $homepage,
    'banner' => isset($banner) ? $banner : '',
    'today' => $ymdhms,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'course_title' => translateFN('Notifica errore'),
    'data' => $error_div,
    'status' => translateFN('Notifica di errore')
);
/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr);