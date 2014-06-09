<?php
/**
 * Edit tutor - this module provides edit tutor functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009-2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/tutor_functions.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';

$languages = Translator::getLanguagesIdAndName();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserProfileForm($languages, true);
    $form->fillWithPostData();
    $password = trim($_POST['password']);
    $passwordcheck = trim($_POST['passwordcheck']);
    if(DataValidator::validate_password_modified($password, $passwordcheck) === FALSE) {
    	$message = translateFN('Le password digitate non corrispondono o contengono caratteri non validi.');
    	header("Location: edit_tutor.php?message=$message");
    	exit();
    }
    if ($form->isValid()) {
		$userObj->fillWithArrayData($_POST);
        MultiPort::setUser($userObj, array(), true);
        $help = translateFN('Dati salvati');
        /*$navigationHistoryObj = $_SESSION['sess_navigation_history'];
        $location = $navigationHistoryObj->lastModule();
        header('Location: ' . $location.'?saveData');
        exit();*/
    }
} else {
    $form = new UserProfileForm($languages, true);
    $user_dataAr = $userObj->toArray();
    unset($user_dataAr['password']);
    $user_dataAr['email'] = $user_dataAr['e_mail'];
    unset($user_dataAr['e_mail']);
    $form->fillWithArrayData($user_dataAr);
    $help = translateFN('Modifica dati utente');
}

$label = translateFN('Modifica dati utente');


$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.js'		
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.css'
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));

$optionsAr['onload_func'] = 'initDoc('.$maxFileSize.','. $userObj->getId().');';

// $optionsAr['onload_func'] = 'initDateField();';

/*
 * Display error message  if the password is incorrect
 */
if(isset($_GET['message']))
{
	$help= $_GET['message'];

}


$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => translateFN('Modifica dati utente'),
    'data' => $form->getHtml(),
    'help' => $help
);

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);