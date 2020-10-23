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
use Lynxlab\ADA\Module\GDPR\GdprAcceptPoliciesForm;
use Lynxlab\ADA\Module\GDPR\GdprPolicy;

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
BrowsingHelper::init($neededObjAr);
include_once ROOT_DIR . '/include/index_functions.inc.php';
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
    /*
     *
    $navigationHistoryObj = $_SESSION['sess_navigation_history'];
    $lastModule = $navigationHistoryObj->lastModule();
    print_r($form);
     *
     */
    /**
     * Perform login
     */
	if (isset($gdprAccepted) && intval($gdprAccepted)===1 &&  array_key_exists(GdprPolicy::sessionKey, $_SESSION) && array_key_exists('post', $_SESSION[GdprPolicy::sessionKey])) {
		extract($_SESSION[GdprPolicy::sessionKey]['post']);
	}
	unset($_SESSION[GdprPolicy::sessionKey]);
    if(isset($p_login) || (isset($selectedLoginProvider) && strlen($selectedLoginProvider)>0)) {

    	if (isset($p_login)) {
    		$username = DataValidator::validate_username($p_username);
    		$password = DataValidator::validate_password($p_password, $p_password);
    	} else {
    		$username = DataValidator::validate_not_empty_string($p_username);
    		$password = DataValidator::validate_not_empty_string($p_password);
    	}

    	if (!isset($p_remindme)) $p_remindme = false;

    	if (isset($p_login)) {
    		if($username !== FALSE && $password !== FALSE) {
    			//User has correctly inserted un & pw
    			$userObj = MultiPort::loginUser($username, $password);
    			$loginObj = null;
    		} else {
    			// Utente non loggato perche' informazioni in username e password non valide
    			// es. campi vuoti o contenenti caratteri non consentiti.
    			$login_error_message = translateFN("Username  e/o password non valide");
    		}
    	} else if (defined('MODULES_LOGIN') && MODULES_LOGIN &&
    			isset($selectedLoginProvider) && strlen($selectedLoginProvider)>0) {
    				include_once  MODULES_LOGIN_PATH . '/include/'.$selectedLoginProvider.'.class.inc.php';
    				if (class_exists($selectedLoginProvider)) {
    					$loginProviderID = isset($selectedLoginProviderID) ? $selectedLoginProviderID : null;
    					$loginObj = new $selectedLoginProvider($selectedLoginProviderID);
    					$userObj = $loginObj->doLogin($username, $password, $p_remindme, $p_selected_language);
    					if ((is_object($userObj)) && ($userObj instanceof Exception)) {
    						// try the adalogin before giving up the login process
    						$lastTry = MultiPort::loginUser($username, $password);
    						if ((is_object($lastTry)) && ($lastTry instanceof ADALoggableUser)) {
    	  				$loginObj = null;
    	  				$userObj = $lastTry;
    						}
    					}
    				}
    			}

    			if ((is_object($userObj)) && ($userObj instanceof ADALoggableUser)) {
    				if (isset($_SESSION['subscription_page'])) {
    					$redirectURL = $_SESSION['subscription_page'];
    					unset ($_SESSION['subscription_page']);
    				} else {
    					$redirectURL = $navigationHistoryObj->lastModule();
                    }

    				if(!ADALoggableUser::setSessionAndRedirect($userObj, $p_remindme, $p_selected_language, $loginObj,$redirectURL)) {
    					//  Utente non loggato perché stato <> ADA_STATUS_REGISTERED
    					$login_error_message = translateFN("Utente non abilitato");
    				}
    			} else if ((is_object($userObj)) && ($userObj instanceof Exception)) {
    				$login_error_message = $userObj->getMessage();
    				if ($userObj->getCode()!==0) $login_error_message .= ' ('.$userObj->getCode().')';
    			} else {
    				// Utente non loggato perché coppia username password non corretta
    				$login_error_message = translateFN("Username  e/o password non valide");
    			}
    }
    if (isset($login_error_message)) $data = new CText($login_error_message);

}
	/**
	 * Negotiate login page language
	 */
	Translator::loadSupportedLanguagesInSession();
	$supported_languages = Translator::getSupportedLanguages();
	$login_page_language_code = Translator::negotiateLoginPageLanguage($lang_get);
	$_SESSION['sess_user_language'] = $login_page_language_code;

    $form_action = HTTP_ROOT_DIR ;
    $form_action .= '/'.whoami().'.php';
    $data = UserModuleHtmlLib::loginForm($form_action, $supported_languages,$login_page_language_code, $login_error_message);

    $registration_action = HTTP_ROOT_DIR . '/browsing/registration.php';
    $cod = FALSE;
    $registration_data = new UserRegistrationForm($cod, $registration_action);
//    $form = new UserRegistrationForm();
//    $data = $form->render();

$help = translateFN('Per poter proseguire, è necessario che tu sia un utente registrato.');
$title = translateFN('Richiesta di autenticazione');

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename']= array( ROOT_DIR . '/layout/'.$_SESSION['sess_userObj']->template_family.'/css/main/index.css' );
if (defined('MODULES_LOGIN') && MODULES_LOGIN) {
	$layout_dataAr['CSS_filename'] = array_merge($layout_dataAr['CSS_filename'],
			array (
					MODULES_LOGIN_PATH . '/layout/support/login-form.css'
			));
}

$optionsAr['onload_func'] = 'initDateField();';

if (defined('MODULES_GDPR') && MODULES_GDPR === true && isset($registration_data)) {
    $gdprApi = new \Lynxlab\ADA\Module\GDPR\GdprAPI();
    GdprAcceptPoliciesForm::addPolicies($registration_data, array(
    	'policies' => $gdprApi->getPublishedPolicies(),
    	'extraclass' => 'ui form',
    	'isRegistration' => true
    ));

    $layout_dataAr['CSS_filename'][] = MODULES_GDPR_PATH . '/layout/'.ADA_TEMPLATE_FAMILY.'/css/acceptPolicies.css';
    $layout_dataAr['JS_filename'][] =  MODULES_GDPR_PATH . '/js/acceptPolicies.js';
    $layout_dataAr['JS_filename'][] = ROOT_DIR . '/js/browsing/registration.js';
    $optionsAr['onload_func'] .= 'initRegistration();';
}

if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
	$layout_dataAr['JS_filename'][] = MODULES_SECRETQUESTION_PATH . '/js/modules_define.js.php';
}

$registrationDataHtml = '';
if (is_object($registration_data)) {
    $registrationDataHtml = $registration_data->getHtml();
}

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