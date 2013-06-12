<?php
/**
 * registration.php file
 *
 * This script is responsible for the user registration process.
 *
 * @package		Default
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		registration
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR);
/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_VISITOR      => array('layout')
);
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR.'/include/Forms/UserRegistrationForm.inc.php';
include_once ROOT_DIR.'/include/token_classes.inc.php';
$self =  whoami();
/**
 * Negotiate login page language
 */
if (!isset($_SESSION['sess_user_language'])) {
  Translator::loadSupportedLanguagesInSession();
  $login_page_language_code = Translator::negotiateLoginPageLanguage();
  $_SESSION['sess_user_language'] = $login_page_language_code;
}
$supported_languages = Translator::getSupportedLanguages();


if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    /*
     * The user is associated by default to the public tester.
     */
    $tester = ADA_PUBLIC_TESTER;
    /*
     * Validate the user submitted data and proceed to the user registration.
     */
    $form = new UserRegistrationForm();
    $form->fillWithPostData();
    if($form->isValid()) {
        $user_dataAr = $form->toArray();
        $user_dataAr['username'] = $_POST['email'];
        
        $userObj = new ADAUser($user_dataAr);
        $userObj->setLayout('');
        $userObj->setType(AMA_TYPE_STUDENT);
        $userObj->setStatus(ADA_STATUS_PRESUBSCRIBED);
        // Random password.
        $userObj->setPassword(sha1(time()));
        
        $id_user = Multiport::addUser($userObj,array($tester));
        if($id_user < 0) {
            $message = translateFN('Impossibile procedere. Un utente con questi dati esiste?')
                     . ' ' . $id_user;
            header('Location:'.HTTP_ROOT_DIR.'/index.php?message='.$message);
            exit();
        }
        /**
         * Create a registration token for this user and send it to the user
         * with the confirmation request.
         */
        $tokenObj = TokenManager::createTokenForUserRegistration($userObj);
        if($tokenObj == false) {
            $message = translateFN('An error occurred while performing your request. Pleaser try again later.');
            header('Location:'.HTTP_ROOT_DIR."/index.php?message=$message");
            exit();
        }
        $token = $tokenObj->getTokenString();

        $admtypeAr = array(AMA_TYPE_ADMIN);
        $admList = $common_dh-> get_users_by_type($admtypeAr);
        if (!AMA_DataHandler::isError($admList)){
            $adm_uname = $admList[0]['username'];
        } else {
            $adm_uname = ADA_ADMIN_MAIL_ADDRESS;
        }

        $title = PORTAL_NAME.': ' . translateFN('ti chiediamo di confermare la registrazione.');


        $text = sprintf(translateFN('Gentile %s, ti chiediamo di confermare la registrazione ai %s.'),
                    $userObj->getFullName(), PORTAL_NAME)
              . PHP_EOL . PHP_EOL
              . translateFN('Il tuo nome utente è il seguente:')
              . ' ' . $userObj->getUserName()
              . PHP_EOL . PHP_EOL
              . sprintf(translateFN('Puoi confermare la tua registrazione ai %s seguendo questo link:'),
                        PORTAL_NAME)
              . PHP_EOL
              . ' ' . HTTP_ROOT_DIR."/browsing/confirm.php?uid=$id_user&tok=$token"
              . PHP_EOL . PHP_EOL
              . translateFN('La segreteria dei Corsi ICoN di Lingua italiana');

        $message_ha = array(
            'titolo' => $title,
            'testo' => $text,
            'destinatari' => array($userObj->getUserName()),
            'data_ora' => 'now',
            'tipo' => ADA_MSG_SIMPLE,
            'mittente' => $adm_uname
        );
        $mh = MessageHandler::instance(MultiPort::getDSN($tester));
        /**
         * Send the message as an internal message
         */
        $result = $mh->send_message($message_ha);
        if(AMA_DataHandler::isError($result)) {
        }
        /**
         * Send the message an email message
         */
        $message_ha['tipo'] = ADA_MSG_MAIL;
        $result = $mh->send_message($message_ha);
        if(AMA_DataHandler::isError($result)) {
        }

        /*
         * Redirect the user to the "registration succeeded" page.
         */
        header('Location: ' . HTTP_ROOT_DIR . '/browsing/registration.php?op=success');
        exit();
    }
    else {
        header('Location: ' . HTTP_ROOT_DIR . '/browsing/registration.php');
        exit();
    }

} elseif (isset($_GET['op']) && $_GET['op'] == 'success') {
    /*
     * The user registration was completed with success.
     * Generate a feedback message for the user.
     */
    $help = '';
    $data  = translateFN('Richiesta di registrazione completata.')
              . '<br />'
              . translateFN('You will receive an email with informations on how to login.');
} else {
    /*
     * Display the registration form.
     */
    $help = translateFN('Da questa pagina puoi effettuare la registrazione ad ADA');
    $form = new UserRegistrationForm();
    $data = $form->render();
}

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT		
);

$optionsAr['onload_func'] = 'initDateField();';

$title = translateFN('Informazioni');

$content_dataAr = array(
  'user_name'  => $user_name,
  'home'       => $home,
  'data'       => $data,
  'help'       => $help,
  'menu'       => $menu,
  'message'    => $message,
  'status'     => $status
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);