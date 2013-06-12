<?php

/**
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
$self= whoami();
/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserLoginForm.inc.php';
require_once ROOT_DIR . '/include/Forms/UserRegistrationForm.inc.php';
require_once ROOT_DIR . '/include/token_classes.inc.php';

$redirectURL = $_SESSION['subscription_page'];
$navigationHistoryObj = $_SESSION['sess_navigation_history'];


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserLoginForm();
    $form->fillWithPostData();
    /*
     *
    $navigationHistoryObj = $_SESSION['sess_navigation_history'];
    $lastModule = $navigationHistoryObj->lastModule();
    print_r($form);
     * 
     */
    if ($form->isValid()) {
        $userObj = MultiPort::loginUser($_POST['username'], $_POST['password']);
        if ((is_object($userObj)) && ($userObj instanceof ADALoggableUser)) {
            $status = $userObj->getStatus();
            if ($status == ADA_STATUS_REGISTERED) {
                $_SESSION['sess_user_language'] = $p_selected_language;
                $_SESSION['sess_id_user'] = $userObj->getId();
                $GLOBALS['sess_id_user'] = $userObj->getId();
                $_SESSION['sess_id_user_type'] = $userObj->getType();
                $GLOBALS['sess_id_user_type'] = $userObj->getType();
                $_SESSION['sess_userObj'] = $userObj;
                $user_default_tester = $userObj->getDefaultTester();
                if ($user_default_tester !== NULL) {
                    $_SESSION['sess_selected_tester'] = $user_default_tester;
                }
//                print_r($_SESSION['subscription_page']);
                if (isset($_SESSION['subscription_page'])) {
                    $redirectURL = $_SESSION['subscription_page'];
                    unset ($_SESSION['subscription_page']);
                    header('Location:' . $redirectURL);
                    exit();
                } else {
                    $lastModule = $navigationHistoryObj->lastModule();
                    header('Location:' . $lastModule);
                    exit();
                }
            }
        } else {
            $data = new CText('Utente non trovato');
        }
    } else {
        $data = new CText('Dati inseriti non validi');
    }
} else {
    $data = new UserLoginForm();
    $registration_action = HTTP_ROOT_DIR . '/browsing/registration.php';
    $cod = FALSE;
    $registration_data = new UserRegistrationForm($cod, $registration_action);
//    $form = new UserRegistrationForm();
//    $data = $form->render();
} 
$help = translateFN('Per poter proseguire, è necessario che tu sia un utente registrato.');
$title = translateFN('Richiesta di autenticazione');

$registrationDataHtml = '';
if (is_object($registration_data)) {
    $registrationDataHtml = $registration_data->getHtml();
}

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDateField();';

$content_dataAr = array(
    'course_title' => $title,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $title,
    'help' => $help,
    'data' => $data->getHtml(),
    'registration_data' => $registrationDataHtml
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);