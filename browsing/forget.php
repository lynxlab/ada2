<?php
/*
 * Created on 06/ott/2009
 * FORGET
 * @package	browsing
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		    forget
 * @version		0.1
 */

/*
 * First time: we ask for username and repost to self
 * Second time: we get user status, user address and send an email with a token and a link
 * Third time: we verify the token , and allow for resetting password
 * Fourth time: we verify  the user  and change password
 *
 */
// ini_set('display_errors',1);
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
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
require_once ROOT_DIR.'/include/module_init.inc.php';
include_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';

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

include_once ROOT_DIR.'/browsing/include/forget_functions.inc.php';
include_once ROOT_DIR.'/include/form/phpOpenFormGen.inc.php';
include_once ROOT_DIR.'/admin/include/htmladmoutput.inc.php';
include_once ROOT_DIR.'/include/token_classes.inc.php';

$common_dh= $GLOBALS['common_dh'];

//$self =  "guest";
$self =  "registration";
/**
 * Negotiate login page language
 */

$lang_get = isset($_GET['lan']) ? $_GET['lan'] : null;

Translator::loadSupportedLanguagesInSession();
$supported_languages = Translator::getSupportedLanguages();
$login_page_language_code = Translator::negotiateLoginPageLanguage($lang_get);
$_SESSION['sess_user_language'] = $login_page_language_code;



if (isset($_POST['username'])){
  $case = 2;
  $op = "check_username"; // and send email
} elseif ((isset($_POST['user']['password'])) && (isset($_POST['user']['username']))){
  $case = 4;
  $op = "change_password";
} elseif ((isset($_GET['tok'])) && (isset($_GET['uid']))){
  $case = 3;
  $op = "form_password";
} else {
  // first time here
  $case = 1;
  $op  = "insert_username";
}


if (isset($_GET['status'])){
       $status = $_GET['status'];
} else {
      $status = translateFN("New password");
}

switch ($op){

  case "check_username":
    $username = $_POST['username'];
    if ($username != NULL){
      $user_id = $common_dh->find_user_from_username($username);
      if(AMA_Common_DataHandler::isError($user_id)) {
        // Utente non esistente o non loggable
        /*
         * Verifico se esiste un utente che ha come email  il contenuto del
         * campo $username
         */
//        $user_id = $common_dh->find_user_from_email($username);
//        if(AMA_Common_DataHandler::isError($user_id)) {
          $message = translateFN("Username is not valid");
          $redirect_to = HTTP_ROOT_DIR . "/browsing/forget.php?message=$message";
          header('Location:'.$redirect_to);
          exit();
//        }
      }
      $userObj =   MultiPort::findUser($user_id);//read_user($user_id);  // ?
      if ((is_object($userObj)) && ($userObj instanceof ADALoggableUser)){
        // user is recognized as loggable
        $userStatus = $userObj->getStatus();
        /*
        if ($userStatus == ADA_STATUS_REGISTERED)  // FIXME: practitioner and others?
        {
          // user is a ADAuser with status set to 0 OR
          // user is admin, author or switcher whose status is by default = 0

          $_SESSION['sess_user_language'] = $p_selected_language;
          $_SESSION['sess_id_user'] = $userObj->getId();
          $_SESSION['sess_id_user_type'] = $userObj->getType();
          $_SESSION['sess_userObj'] = $userObj;
          $GLOBALS['sess_id_user']  = $_SESSION['sess_id_user'];
          $GLOBALS['sess_id_user_type']  = $_SESSION['sess_id_user_type'];

          $user_default_tester = $userObj->getDefaultTester();
          if($user_default_tester !== NULL) {
            $_SESSION['sess_selected_tester'] = $user_default_tester;
          }
        }
         *
         */
      } else {
        // Utente non esistente o non loggable
        $message = translateFN("Username is not valid");
        $redirect_to = HTTP_ROOT_DIR. "/browsing/forget.php?message=$message";
        header('Location:'.$redirect_to);
        exit();
      }
    } else {
      // vuoto
      $message = translateFN("Username cannot be empty");
      $redirect_to = HTTP_ROOT_DIR."/browsing/forget.php?message=$message";
      header('Location:'.$redirect_to);
      exit();
    }

    if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
      /**
       * MODULES_SECRETQUESTION will handle questioning and answer check
       */
      redirect(MODULES_SECRETQUESTION_HTTP . '/askQuestion.php?userId='.$user_id);
    } else {
      $admtypeAr = array(AMA_TYPE_ADMIN);
      $admList = $common_dh-> get_users_by_type($admtypeAr);
      // $admList = $tester_dh-> get_users_by_type($admtypeAr); ???

      if (!AMA_DataHandler::isError($admList)){
        $adm_uname = $admList[0]['username'];
      } else {
        $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
      }
      /*
       * Create a token to authorize this user to change his/her password
       */
      $tokenObj = TokenManager::createTokenForPasswordChange($userObj);
      if($tokenObj == false) {
        $message = translateFN('An error occurred while performing your request. Please try again later.');
        header('Location:'.HTTP_ROOT_DIR."/browsing/forget.php?message=$message");
        exit();
      }
      $token    = $tokenObj->getTokenString();

      $titolo = translateFN("Richiesta cambio password");
      $testo = sprintf(translateFN("Abbiamo ricevuto una richiesta di cambio password per l'utente %s della piattaforma %s."), $username, PORTAL_NAME);
      $testo .= '<br/><br/>';
      $link = HTTP_ROOT_DIR."/browsing/forget.php?uid=$user_id&tok=$token";
      $testo.= sprintf(translateFN("Se hai fatto tu la richiesta, apri questo link %s"), BaseHtmlLib::link($link, $link)->getHtml());

      // $mh = MessageHandler::instance(MultiPort::getDSN($tester)); /* FIXME */
      // should we user common DB?
      $common_db_dsn = ADA_COMMON_DB_TYPE.'://'.ADA_COMMON_DB_USER.':'
      .ADA_COMMON_DB_PASS.'@'.ADA_COMMON_DB_HOST.'/'
      .ADA_COMMON_DB_NAME;
      $mh = MessageHandler::instance($common_db_dsn);

      // prepare message to send
      $message_ha = array();
      $message_ha['titolo'] = $titolo;
      $message_ha['testo'] = $testo;
      $message_ha['destinatari'] = array($username);
      $message_ha['data_ora'] = "now";
      $message_ha['tipo'] = ADA_MSG_MAIL;
      $message_ha['mittente'] = $adm_uname;

      // delegate sending to the message handler
      $res = $mh->send_message($message_ha);

      if (AMA_DataHandler::isError($res)){
        //	  $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
        //	  NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
      }
      //	} else {
      $message = translateFN("A message has been sent to  you with informations on how to change your password.");
      $redirect_to = HTTP_ROOT_DIR . "/browsing/forget.php?message=$message";
      header('Location:'.$redirect_to);
      exit();
    }
    //	}
    break;
  case "change_password":
    /*
     * Third time here.
     * After filling the change password form.
     */

    $userid   = $_POST['user']['uid'];
    $username = $_POST['user']['username'];
    $token    = $_POST['token'];

    $tokenObj = TokenFinder::findTokenForPasswordChange($userid, $token);
    if($tokenObj == false) {
      $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
      $errObj = new ADA_Error($userType,translateFN('It was impossible to confirm the password change: token not valid'),
      NULL,NULL,NULL,
      $error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change: token not valid')));
      exit();
    }

    $userObj = MultiPort::findUser($userid);
    $userStatus = $userObj->getStatus();
    if (AMA_DataHandler::isError($userObj)){
      $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
      $errObj = new ADA_Error($userType,translateFN('It was impossible to confirm the password change: user unknown'),
      NULL,NULL,NULL,$error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change: user unknown')));
      exit();
    }
    else {
      $message = '';
      // cut off extra spaces
      $password      = trim($_POST['user']['password']);
      $passwordcheck = trim($_POST['user']['passwordcheck']);

      /**
       * Check that the user entered a valid password and confirmed it correctly
       */
      if(DataValidator::validate_password($password, $passwordcheck) === FALSE) {
        $errors = TRUE;
        $message .= translateFN('Le password digitate non corrispondo o contengono caratteri non validi.').'<br />';
        header("Location: ".HTTP_ROOT_DIR ."/browsing/forget.php?message=$message&uid=$userid&tok=$token");
        exit();
      }
      else {
        $userObj->setPassword($password);

        $new_testers = array();
        $resPass = MultiPort::setUser($userObj,$new_testers,TRUE); // TRUE to modify user data

        if (AMA_DataHandler::isError($resPass)){
          $msg = $result->getMessage();
          $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
          $errObj = new ADA_Error($requestInfo,translateFN('It was impossible to confirm the password change'),
          NULL,NULL,NULL,$error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change')));
          exit();
        }
        else {
          // change status of user ON Common AND ON  TESTER ?
          switch ($userStatus){
            case ADA_STATUS_PRESUBSCRIBED:
              $userObj->setStatus(ADA_STATUS_REGISTERED);

              $resSet = MultiPort::setUser($userObj,$new_testers,true);
              /*
               $adh->set_user_status(ADA_STATUS_REGISTERED);
               $common_dh->set_user_status(ADA_STATUS_REGISTERED);
               */
              break;
            case ADA_STATUS_REGISTERED:
              break;
            case ADA_STATUS_REMOVED:
            default:
              $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
              $errObj = new ADA_Error($requestInfo,translateFN('It was impossible to confirm the password change: user unknown'),
              NULL,NULL,NULL,$error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change: user unknown')));
              exit();
          }


          $message = translateFN("Password cambiata con successo.");
          // FIXME: add a get parameter to help user to login ??
          //header('Location: '.$redirectPage."?message=$message&user=$username");

          $tokenObj->markAsUsed();
          TokenManager::updateToken($tokenObj);

          header('Location: '.HTTP_ROOT_DIR."/browsing/forget.php?message=$message");
          exit();
        }
      }
    }
    break;

  case "form_password":
    /*
     * Second time here.
     * Show the password change form.
     */

    $token  = DataValidator::validate_action_token($_GET['tok']);
    $userid = DataValidator::is_uinteger($_GET['uid']);

    if($token == false || $userid == false) {
      /*
       * Invalid data in input
       */
      $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
      $errObj = new ADA_Error($requestInfo,translateFN('It was impossible to confirm the password change'),
      NULL,NULL,NULL,
      $error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change')));
      exit();
    }

    $tokenObj = TokenFinder::findTokenForPasswordChange($userid, $token);
    if($tokenObj === false) {
      /*
       * There isn't a token corresponding to input data, do not proceed.
       */
      $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
      $errObj = new ADA_Error($requestInfo,translateFN('It was impossible to confirm the password change'),
      NULL,NULL,NULL,
      $error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change')));
      exit();
    }

    $userObj = MultiPort::findUser($userid);
    if (AMA_DataHandler::isError($userObj)){
      $error_page = HTTP_ROOT_DIR."/browsing/forget.php";
      $errObj = new ADA_Error($userType,translateFN('It was impossible to confirm the password change: user unknown'),
      NULL,NULL,NULL,$error_page.'?message='.urlencode(translateFN('It was impossible to confirm the password change: user unknown')));
      exit();
    }

    if($tokenObj->isValid()) {

      if (!isset($username)) $usernameStr = '';
      else $usernameStr = ', '.$username;
      $help  = translateFN('Per favore inserisci la tua password:');
     // $status = translateFN("Modifica password utente");
      $welcome ="<br />". translateFN('Benvenuto').$usernameStr."<br />";
      $welcome.= translateFN('Ora devi cambiare la tua password. Puoi usare lettere, numeri e trattini. Lunghezza minima 8 lettere')."<br />";
      $home = 'user.php';
      $menu = '';

      $op   = new htmladmoutput();

      $dati = $op->form_confirmpassword('forget.php',$home,$username,$userid,isset($id_course) ? $id_course : null,$token);
      $dati = $welcome.$dati;
      $title = translateFN('ADA - Modifica Dati Utente');
    }
    else {
      /*
       * Informiamo l'utente che il token per il cambio password è scaduto e che
       * deve richiedere nuovamente di cambiare la password
       */
      $title = translateFN('');
      $dati  = sprintf(translateFN("Dear user %s, the web address you have clicked to change your password has expired. You have to require a new one by clicking on the following link. "), $userObj->getUserName());
      $forget_linkObj = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/forget.php?lan='.$_SESSION['sess_user_language']);
      $forget_linkObj->addChild(new CText(translateFN("Did you forget your password?")));
      $dati .= $forget_linkObj->getHtml();
    }
    break;

  case "insert_username":
  default:
    // first time here
    $help  = translateFN('Did you forget your password?');

    $welcome ="<br />". translateFN('Welcome, user')."<br />";
    $welcome.= translateFN('If you forgot your password, please insert your username. We will send you a message with instructions
     to change your password');
    if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
      $welcome .= ' '.translateFN('or ask your secret question');
    }
    $welcome .= ".<br />";

    $home = $userObj->getHomepage();
    $menu = '';
    $op   = new htmladmoutput();

    $dati = $op->form_getUsername('forget.php');
    $dati = $welcome.$dati;
    $title = translateFN('ADA - Changing password');
    break;

} // end switch

if (isset($message) && strlen($message)>0) {
	$help = $message;
	unset($message);
}

$content_dataAr = array(
  'title'     => $title,
  'menu'      => $menu,
  'data'      => $dati, // FIXME: move to message field
  'help'      => $help,
  'user_type' => isset($userType) ? $userType : null,
  // 'message'   => $message // FIXME: not visible !
);

ARE::render($layout_dataAr, $content_dataAr);