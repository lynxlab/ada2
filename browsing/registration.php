<?php
use Lynxlab\ADA\Module\GDPR\GdprAcceptPoliciesForm;

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
BrowsingHelper::init($neededObjAr);

require_once ROOT_DIR.'/include/Forms/UserRegistrationForm.inc.php';
include_once ROOT_DIR.'/include/token_classes.inc.php';

require_once ROOT_DIR.'/include/phpMailer/ADAPHPMailer.php';

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
     * Validate the user submitted data and proceed to the user registration.
     */
    $form = new UserRegistrationForm();
    $form->fillWithPostData();
    if($form->isValid()) {
        $user_dataAr = $form->toArray();
        if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
        	$user_dataAr['username'] = trim($_POST['uname']);
        	$passw = trim($_POST['upass']);
        	$ustatus = ADA_STATUS_REGISTERED;
        } else {
	        $user_dataAr['username'] = $_POST['email'];
	        // Random password.
	        $passw = sha1(time());
	        $ustatus = ADA_STATUS_PRESUBSCRIBED;
        }

        $userObj = new ADAUser($user_dataAr);
        $userObj->setLayout('');
        $userObj->setType(AMA_TYPE_STUDENT);
        $userObj->setStatus($ustatus);
        $userObj->setPassword($passw);
        $emailed = false;

        /**
		 * giorgio 19/ago/2013
		 *
		 * if it's not multiprovider, must register the user
		 * in the selected tester only.
		 * if it is multiprovider, must register the user
		 * in the public tester only.
         */
        if (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) {
        	$regProvider = array ($GLOBALS['user_provider']);
        } else {
        	$regProvider = array (ADA_PUBLIC_TESTER);
        }

        $id_user = Multiport::addUser($userObj,$regProvider);
        if($id_user < 0) {
            $message = translateFN('Impossibile procedere. Un utente con questi dati esiste?')
                     . ' ' . urlencode(
						(defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) ?
						$userObj->getUserName() :
						$userObj->getEmail());
            header('Location:'.HTTP_ROOT_DIR.'/browsing/registration.php?message='.$message);
            exit();
        }

        /**
         * before doing anything, save the accepted privacy policies here
         */
        try {
	        if (defined('MODULES_GDPR') && MODULES_GDPR === true) {
	        	$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	        	if (array_key_exists('acceptPolicy', $postParams) && is_array($postParams['acceptPolicy']) && count($postParams['acceptPolicy'])>0) {
		        	$postParams['userId'] = $userObj->getId();
					(new Lynxlab\ADA\Module\GDPR\GdprAPI())->saveUserPolicies($postParams);
	        	}
	        }
        } catch (Exception $e) {
        	$message = translateFN('Errore nel salvataggio delle politiche sulla privacy');
            header('Location:'.HTTP_ROOT_DIR.'/browsing/registration.php?message='.$message);
            exit();
        }

        if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
	        /**
	         * Save secret question and answer and set the registration as successful
	         */
			$sqdh = \AMASecretQuestionDataHandler::instance();
			$sqdh->saveUserQandA($id_user, $_POST['secretquestion'], $_POST['secretanswer']);

        } else {
	        /**
	         * Create a registration token for this user and send it to the user
	         * with the confirmation request.
	         */
	        $tokenObj = TokenManager::createTokenForUserRegistration($userObj);
	        if($tokenObj == false) {
	            $message = translateFN('An error occurred while performing your request. Pleaser try again later.');
	            header('Location:'.HTTP_ROOT_DIR."/browsing/registration.php?message=$message");
	            exit();
	        }
	        $token = $tokenObj->getTokenString();

	        $admTypeAr = array(AMA_TYPE_ADMIN);
	        $extended_data = TRUE;
	        $admList = $dh->get_users_by_type($admTypeAr, $extended_data);
	        if (!AMA_DataHandler::isError($admList) && array_key_exists('username',$admList[0]) && $admList[0]['username'] != '' && $admList[0]['username'] != null){
	            $adm_uname = $admList[0]['username'];
	            $adm_email = strlen($admList[0]['e_mail']) ? $admList[0]['e_mail'] : ADA_NOREPLY_MAIL_ADDRESS;
	        } else {
	            $adm_uname = ADA_ADMIN_MAIL_ADDRESS;
	            $adm_email = ADA_ADMIN_MAIL_ADDRESS;
	        }

	        $title = PORTAL_NAME.': ' . translateFN('ti chiediamo di confermare la registrazione.');

	        $confirm_link_html = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR."/browsing/confirm.php?uid=$id_user&tok=$token");
	        $confirm_link_html->addChild(new CText(translateFN('conferma registrazione')));
	        $confirm_link_html_rendered = $confirm_link_html->getHtml();

	        $PLAINText = sprintf(translateFN('Gentile %s, ti chiediamo di confermare la registrazione ai %s.'),
	        		$userObj->getFullName(), PORTAL_NAME)
	        		. PHP_EOL . PHP_EOL
	        		. translateFN('Il tuo nome utente è il seguente:')
	        		. ' ' . $userObj->getUserName()
	        		. PHP_EOL . PHP_EOL
	        		. sprintf(translateFN('Puoi confermare la tua registrazione a %s seguendo questo link:'),
	        				PORTAL_NAME)
	        		. PHP_EOL
	        		. ' ' . HTTP_ROOT_DIR."/browsing/confirm.php?uid=$id_user&tok=$token"
	        		. PHP_EOL . PHP_EOL
	        		. translateFN('La segreteria di') . ' '. PORTAL_NAME;

	        $HTMLText = sprintf(translateFN('Gentile %s, ti chiediamo di confermare la registrazione ai %s.'),
	        		$userObj->getFullName(), PORTAL_NAME)
	        		. '<BR />' . '<BR />'
	        		. translateFN('Il tuo nome utente è il seguente:')
	        		. ' ' . $userObj->getUserName()
	        		. '<BR />' . '<BR />'
	        		. sprintf(translateFN('Puoi confermare la tua registrazione ai %s seguendo questo link:'),
	        				PORTAL_NAME)
	        		. '<BR />'
	        		. $confirm_link_html_rendered
	        		. '<BR />' . '<BR />'
	        		. translateFN('La segreteria di') . ' '. PORTAL_NAME;

	        $message_ha = array(
	            'titolo' => $title,
	            'testo' => $PLAINText,
	            'destinatari' => array($userObj->getUserName()),
	            'data_ora' => 'now',
	            'tipo' => ADA_MSG_SIMPLE,
	            'mittente' => $adm_uname
	        );
	        $tester = isset($tester) ? $tester : null;
	        $mh = MessageHandler::instance(MultiPort::getDSN($tester));
	        /**
	         * Send the message as an internal message
	         */
	        $result = $mh->send_message($message_ha);
	        if(AMA_DataHandler::isError($result)) {
	        }
	        /**
	         * Send the message an email message
	         * via PHPMailer
	         */
	        $phpmailer = new \PHPMailer\PHPMailer\ADAPHPMailer();
	        $phpmailer->CharSet = ADA_CHARSET;
	        $phpmailer->configSend();
	        $phpmailer->SetFrom($adm_email);
	        $phpmailer->AddReplyTo($adm_email);
	        $phpmailer->IsHTML(true);
	        $phpmailer->Subject = $title;
	        $phpmailer->AddAddress($userObj->getEmail(),  $userObj->getFullName());
	        $phpmailer->AddBCC($adm_email);
	        $phpmailer->Body = $HTMLText;
	        $phpmailer->AltBody = $PLAINText;
	        $emailed = $phpmailer->Send();

	        /**
	         * Send the message an email message
	         * via ADA spool
	        $message_ha['tipo'] = ADA_MSG_MAIL;
	        $result = $mh->send_message($message_ha);
	        if(AMA_DataHandler::isError($result)) {
	        }
	         */
        }

		if (defined('ADA_SUBSCRIBE_FROM_LOGINREQUIRED') && (true === ADA_SUBSCRIBE_FROM_LOGINREQUIRED) &&
			isset($_SESSION['subscription_page']) && strlen($_SESSION['subscription_page'])>0) {
			$subUrl = $_SESSION['subscription_page'];
			/**
			 * setSessionAndRedirect wants the user to be in ADA_STATUS_REGISTERED status
			 */
			$oldStatus = $userObj->getStatus();
			$userObj->setStatus(ADA_STATUS_REGISTERED);
			ADALoggableUser::setSessionAndRedirect($userObj, false, $_SESSION['sess_user_language'], null, null, false);
			$userObj->setStatus($oldStatus);
			/**
			 * parse the query string coming from subscription_page and include
			 * info.php to reproduce the subscription sequence
			 */
			$qs = parse_url($subUrl, PHP_URL_QUERY);
			if (strlen($qs) > 0) {
				parse_str($qs, $_GET);
				require_once ROOT_DIR . '/info.php';
			}
			/**
			 * clean unwanted stuff
			 */
			unset($_SESSION['subscription_page']);
			session_destroy();
		}

        /*
         * Redirect the user to the "registration succeeded" page.
         */
        header('Location: ' . HTTP_ROOT_DIR . '/browsing/registration.php?op=success'.($emailed !== false ? '&emailed=1' : '' ));
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
    $data  = translateFN('Richiesta di registrazione completata.');
    if (isset($_GET['emailed']) && intval($_GET['emailed'])===1) {
    	$data .= '<br />'. translateFN('You will receive an email with informations on how to login.');
    }
} else {
	/**
	 * giorgio 21/ago/2013
	 * if it's not a multiprovider environment and the provider is not
	 * selected, must redirect to index
	 */
	if (!MULTIPROVIDER)
	{
		// if provider is not set the redirect
		if (!isset($GLOBALS['user_provider']) || empty($GLOBALS['user_provider']))
		{
			header ('Location: '.HTTP_ROOT_DIR);
			die();
		}
	}

    /*
     * Display the registration form.
     */
    $help = translateFN('Da questa pagina puoi effettuare la registrazione ad ADA');
    if (isset($message) && strlen($message)>0) {
    	$help = $message;
    	unset($message);
    }
    $form = new UserRegistrationForm();

    if (defined('MODULES_GDPR') && MODULES_GDPR === true) {
	    $gdprApi = new \Lynxlab\ADA\Module\GDPR\GdprAPI();
	    GdprAcceptPoliciesForm::addPolicies($form, array(
	    	'policies' => $gdprApi->getPublishedPolicies(),
	    	'extraclass' => 'ui form',
	    	'isRegistration' => true
	    ));

	    $layout_dataAr['CSS_filename'] = array(
	    	MODULES_GDPR_PATH . '/layout/'.ADA_TEMPLATE_FAMILY.'/css/acceptPolicies.css'
	    );
    }

    $data = $form->render();
}

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

if (isset($gdprApi)) {
    $layout_dataAr['JS_filename'][] =  MODULES_GDPR_PATH . '/js/acceptPolicies.js';
}

if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
	$layout_dataAr['JS_filename'][] = MODULES_SECRETQUESTION_PATH . '/js/modules_define.js.php';
}

$optionsAr['onload_func'] = 'initDateField(); initRegistration();';

$title = translateFN('Informazioni');

$content_dataAr = array(
  'user_name'  => isset($user_name) ? $user_name : '',
  'home'       => isset($home) ? $home : '',
  'data'       => isset($data) ? $data : '',
  'help'       => isset($help) ? $help : '',
  'message'    => isset($message) ? $message : '',
  'status'     => isset($status) ? $status : ''
);

 if(isset($msg))
{
    $help=CDOMElement::create('label');
    $help->addChild(new CText(translateFN(ltrim($msg))));
    $divhelp=CDOMElement::create('div');
    $divhelp->setAttribute('id', 'help');
    $divhelp->addChild($help);
    $content_dataAr['help']=$divhelp->getHtml();
}
/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);