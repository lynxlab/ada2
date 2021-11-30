<?php
/**
 * Edit author - this module provides edit author functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_AUTHOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/author_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
ServiceHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';

$languages = Translator::getLanguagesIdAndName();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserProfileForm($languages);
    $form->fillWithPostData();
    $password = trim($_POST['password']);
    $passwordcheck = trim($_POST['passwordcheck']);
    if(DataValidator::validate_password_modified($password, $passwordcheck) === FALSE) {
    	$message = translateFN('Le password digitate non corrispondono o contengono caratteri non validi.');
    	header("Location: edit_author.php?message=$message");
    	exit();
    }
    if ($form->isValid()) {
        $userObj->fillWithArrayData($_POST);
		if($password != '') {
			$userObj->setPassword($password);
		}
        if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
			if (array_key_exists('secretquestion', $_POST) &&
				array_key_exists('secretanswer', $_POST) &&
				strlen($_POST['secretquestion'])>0 && strlen($_POST['secretanswer'])>0) {
					/**
					 * Save secret question and answer and set the registration as successful
					 */
					$sqdh = \AMASecretQuestionDataHandler::instance();
					$sqdh->saveUserQandA($userObj->getId(), $_POST['secretquestion'], $_POST['secretanswer']);
				}
		}
        MultiPort::setUser($userObj, array(), true);

        /* unset $_SESSION['service_level'] to reload it with the correct user language translation */
        unset($_SESSION['service_level']);

        $help = translateFN('Dati salvati');
        /*$navigationHistoryObj = $_SESSION['sess_navigation_history'];
        $location = $navigationHistoryObj->lastModule();
        header('Location: ' . $location);
        exit();*/
    }
} else {
    $form = new UserProfileForm($languages);
    $user_dataAr = $userObj->toArray();
    unset($user_dataAr['password']);
    $user_dataAr['email'] = $user_dataAr['e_mail'];
    $user_dataAr['uname'] = $user_dataAr['username'];
    unset($user_dataAr['e_mail']);
    $form->fillWithArrayData($user_dataAr);
    $help = translateFN('Modifica dati utente');
}

$label = translateFN('Modifica dati utente');
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
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
    'course_title' => translateFN('Modifica dati utente'),
    'dati' => $form->getHtml(),
    'help' => $help
);

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
$content_dataAr['user_avatar'] = $avatar->getHtml();

ARE::render($layout_dataAr, $content_dataAr,NULL, $optionsAr);