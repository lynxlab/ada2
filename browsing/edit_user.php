<?php
/**
 * Edit user - this module provides edit user functionality
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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_AUTHOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
$languages = Translator::getLanguagesIdAndName();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserProfileForm($languages);
    $form->fillWithPostData();

    if ($form->isValid()) {
        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
            $user_layout = $_POST['layout'];
        } else {
            $user_layout = '';
        }
        $userObj->setFirstName($_POST['nome']);
        $userObj->setLastName($_POST['cognome']);
        $userObj->setFiscalCode($_POST['codice_fiscale']);
        $userObj->setEmail($_POST['email']);
        if (trim($_POST['password']) != '') {
            $userObj->setPassword($_POST['password']);
        }
        $userObj->setSerialNumber($_POST['matricola']);
        $userObj->setLayout($user_layout);
        $userObj->setAddress($_POST['indirizzo']);
        $userObj->setCity($_POST['citta']);
        $userObj->setProvince($_POST['provincia']);
        $userObj->setCountry($_POST['nazione']);
        $userObj->setBirthDate($_POST['birthdate']);
        $userObj->setGender($_POST['sesso']);
        $userObj->setPhoneNumber($_POST['telefono']);
        $userObj->setLanguage($_POST['lingua']);
        $userObj->setAvatar($_POST['avatar']);
        $userObj->setCap($_POST['cap']);
        
        MultiPort::setUser($userObj, array(), true);
//        $_SESSION['sess_userObj'] = $userObj;

        $navigationHistoryObj = $_SESSION['sess_navigation_history'];
        $location = $navigationHistoryObj->lastModule();
        header('Location: ' . $location);
        exit();
    }
} else {
    $allowEditProfile=false;
    $allowEditConfirm=false;
   
    $form = new UserProfileForm($languages,$allowEditProfile, $allowEditConfirm, $self.'.php');
    $user_dataAr = $userObj->toArray();
    unset($user_dataAr['password']);
    $user_dataAr['email'] = $user_dataAr['e_mail'];
//    $user_dataAr['avatarFileHidden']=$user_dataAr['avatarfile'];
    unset($user_dataAr['e_mail']);
    $form->fillWithArrayData($user_dataAr);
}

$label = translateFN('Modifica dati utente');

$divProgressBar = CDOMElement::create('div','id:progressbar');
$divProgressLabel = CDOMElement::create('div','id:progress-label');			
$divProgressBar->addChild ($divProgressLabel);			


$help = translateFN('Modifica dati utente');

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

//$optionsAr['onload_func'] = 'initDateField();';

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => translateFN('Modifica dati utente'),
    'data' => $form->getHtml(), //.$divProgressBar->getHtml(),
    'help' => $help
);

ARE::render($layout_dataAr, $content_dataAr,NULL, $optionsAr);