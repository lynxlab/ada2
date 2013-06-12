<?php
/**
 * index.
 *
 *
 *
 * PHP version >= 5.0
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */

/**
 * Destroy session
 */
session_start();
session_unset();
session_destroy();

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN);
/**
 * Performs basic controls before entering this module
 */

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  index;
include_once 'include/'.$self.'_functions.inc.php';



/**
 * Negotiate login page language
 */
Translator::loadSupportedLanguagesInSession();
$supported_languages = Translator::getSupportedLanguages();
$login_page_language_code = Translator::negotiateLoginPageLanguage();
$_SESSION['sess_user_language'] = $login_page_language_code;

/**
 * Track kiosk accesses
 */
$_SESSION['ada_access_from'] = ADA_KIOSK_ACCESS;
$_SESSION['ada_remote_address'] = $_SERVER['REMOTE_ADDR'];

/*
 * Load news file
 */
  $newsfile = 'news_'.$login_page_language_code.'.txt';
  $infofile = 'info_'.$login_page_language_code.'.txt';
  $helpfile = 'help_'.$login_page_language_code.'.txt';

/*
   $infomsg = '';
   $newsmsg = '';
   $hlpmsg = '';
*/

if ($newsmsg == ''){
   $newsfile = $root_dir."/browsing/".$newsfile; //  txt files in ada browsing directory
   if ($fid = @fopen($newsfile,'r')){
      while (!feof($fid))
        $newsmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $newsmsg = translateFN("File news non trovato");
    }
}

if ($hlpmsg == ''){
   $helpfile = $root_dir."/browsing/".$helpfile;  //  txt files in ada browsing directory
   if ($fid = @fopen($helpfile,'r')){
      while (!feof($fid))
        $hlpmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $hlpmsg = translateFN("File help non trovato");
    }
}

if ($infomsg == ''){
   $infofile = $root_dir."/browsing/".$infofile;  //  txt files in ada browsing directory
   if ($fid = @fopen($infofile,'r')){
      while (!feof($fid))
        $infomsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $infomsg = translateFN("File info non trovato");
    }
}



/**
 * Perform login
 */
if(isset($p_login)) {
  $username = DataValidator::validate_username($p_username);
  $password = DataValidator::validate_password($p_password, $p_password);

  if($username !== FALSE && $password !== FALSE) {
    $userObj = MultiPort::loginUser($username, $password);
    //User has correctly logged in
    if($userObj instanceof ADALoggableUser){

      $_SESSION['sess_user_language'] = $p_selected_language;

      $_SESSION['sess_id_user'] = $userObj->getId();
      $GLOBALS['sess_id_user']  = $userObj->getId();

      $_SESSION['sess_id_user_type'] = $userObj->getType();
      $GLOBALS['sess_id_user_type']  = $userObj->getType();

      $_SESSION['sess_userObj'] = $userObj;

      $user_default_tester = $userObj->getDefaultTester();
      if($user_default_tester !== NULL) {
        $_SESSION['sess_selected_tester'] = $user_default_tester;
      }

      header('Location:'.$userObj->getHomePage());
      exit();
    }
    else {
      // Utente non loggato perché coppia username password non corretta
      $login_error_message = translateFN("Username  e/o password non valide");
    }
  }
  else {
    // Utente non loggato perche' informazioni in username e password non valide
    // es. campi vuoti o contenenti caratteri non consentiti.
    $login_error_message = translateFN("Username  e/o password non valide");
  }
}

/**
 * Show login page
 */
$form_action = HTTP_ROOT_DIR .'/kiosk.php';
$login = UserModuleHtmlLib::loginForm($form_action, $supported_languages,$login_page_language_code, $login_error_message);

$message = CDOMElement::create('div');
if(isset($_GET['message'])) {
  $message->addChild(new CText($_GET['message']));
}

$content_dataAr = array(
  'form' => $login->getHtml(),
  'text' => $newsmsg,
  'help' => $hlpmsg,
  'message' => $message->getHtml()
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr,$content_dataAr);
?>