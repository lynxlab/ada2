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
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN, AMA_TYPE_SWITCHER);
/**
 * Performs basic controls before entering this module
 */

require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami(); // index
include_once 'include/'.$self.'_functions.inc.php';

// non serve più...
// require_once ROOT_DIR.'/include/aut/login.inc.php';

$lang_get = isset($_GET['lang']) ? $_GET['lang']: null;

/**
 * sets language if it is not multiprovider
 * if commented, then language is handled by ranslator::negotiateLoginPageLanguage
 * that will check if the browser language is supported by ADA and set it accordingly
 */

// if (!MULTIPROVIDER && defined('PROVIDER_LANGUAGE')) $lang_get = PROVIDER_LANGUAGE;


/**
 * Negotiate login page language
 */
Translator::loadSupportedLanguagesInSession();
$supported_languages = Translator::getSupportedLanguages();
$login_page_language_code = Translator::negotiateLoginPageLanguage($lang_get);
$_SESSION['sess_user_language'] = $login_page_language_code;

/**
 *
 */
$_SESSION['ada_remote_address'] = $_SERVER['REMOTE_ADDR'];

/**
 * giorgio 12/ago/2013
 * if it isn't multiprovider, loads proper files into clients directories
 */
if (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) $files_dir = $root_dir.'/clients/'.$GLOBALS['user_provider'];
else $files_dir = $root_dir;

/*
 * Load news file
 */
  $newsfile = 'news_'.$login_page_language_code.'.txt';
  $infofile = 'info_'.$login_page_language_code.'.txt';
  $helpfile = 'help_'.$login_page_language_code.'.txt';

  $infomsg = '';
  $newsmsg = '';
  $hlpmsg  = '';

if ($newsmsg == ''){
   $newsfile = $files_dir.'/docs/news/'.$newsfile; //  txt files in ada browsing directory
   if ($fid = @fopen($newsfile,'r')){
      while (!feof($fid))
        $newsmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $newsmsg = '<p>'.translateFN("File news non trovato").'</p>';
    }
}

if ($hlpmsg == ''){
   $helpfile = $files_dir.'/docs/help/'.$helpfile;  //  txt files in ada browsing directory
   if ($fid = @fopen($helpfile,'r')){
      while (!feof($fid))
        $hlpmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $hlpmsg = '<p>'.translateFN("File help non trovato").'</p>';
    }
}

if ($infomsg == ''){
   $infofile = $files_dir.'/docs/info/'.$infofile;  //  txt files in ada browsing directory
   if ($fid = @fopen($infofile,'r')){
      while (!feof($fid))
        $infomsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $infomsg = '<p>'.translateFN("File info non trovato").'</p>';
    }
}


$login_error_message = '';
/**
 * Perform login
 */
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
  		}
  	}
    
    if ((is_object($userObj)) && ($userObj instanceof ADALoggableUser)) {
		if(!ADALoggableUser::setSessionAndRedirect($userObj, $p_remindme, $p_selected_language)) {
            //  Utente non loggato perché stato <> ADA_STATUS_REGISTERED
	        $login_error_message = translateFN("Utente non abilitato");
	    }
    } else if ((is_object($userObj)) && ($userObj instanceof Exception)) {
    	$login_error_message = $userObj->getMessage();
    } else {
      // Utente non loggato perché coppia username password non corretta
	$login_error_message = translateFN("Username  e/o password non valide");
    }
}

/**
 * Show login page
 */
$form_action = HTTP_ROOT_DIR ;
$form_action .= '/index.php';
$login = UserModuleHtmlLib::loginForm($form_action, $supported_languages,$login_page_language_code, $login_error_message);

//$login = UserModuleHtmlLib::loginForm($supported_languages,$login_page_language_code, $login_error_message);
 /**
 * giorgio 12/ago/2013
 * set up proper link path and tester for getting the news in a multiproivder environment
 */
  if (!MULTIPROVIDER)
  {
  	if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']))
  	{
		$testerName = $GLOBALS['user_provider'];
  	} else {
  		/**
  		 * overwrite $newsmsg with generated available providers listing
  		 */
  		$allTesters = $common_dh->get_all_testers (array('nome'));
  		$addHtml = false;

  		foreach ($allTesters as $aTester)
  		{  			
  			// skip testers having punatore like 'clientXXX'
  			if (!preg_match('/^(?:client)[0-9]{1,2}$/',$aTester['puntatore']) &&
  				is_dir (ROOT_DIR . '/clients/' .$aTester['puntatore'])) {
  				
  				if (!$addHtml) $providerListUL = CDOMElement::create('ol');
  				$addHtml = true;
  				$testerLink = CDOMElement::create('a','href:'.preg_replace("/(http[s]?:\/\/)(\w+)[.]{1}(\w+)/", "$1".$aTester['puntatore'].".$3", HTTP_ROOT_DIR));
  				$testerLink->addChild (new CText($aTester['nome']));

  				$providerListElement = CDOMElement::create('li');
  				$providerListElement->addChild ($testerLink);
  				$providerListUL->addChild ($providerListElement);
  			}
  		}
  		$newsmsg = $addHtml ? $providerListUL->getHtml() : translateFN ('Nessun fornitore di servizi &egrave; stato configurato');
  	}
  } else  {
  	$testers = $_SESSION['sess_userObj']->getTesters();
  	$testerName = (!is_null($testers) && count($testers)>0) ? $testers[0] : null;
  } // end if (!MULTIPROVIDER)

  $forget_div  = CDOMElement::create('div');
  $forget_linkObj = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/forget.php?lan='.$_SESSION['sess_user_language']);
  $forget_linkObj->addChild(new CText(translateFN("Did you forget your password?")));
  $forget_link = $forget_linkObj->getHtml();
//  $status = translateFN('Explore the web site or register and ask for a practitioner');
  $status = "";

$message = CDOMElement::create('div');
if(isset($_GET['message'])) {
  $message->addChild(new CText($_GET['message']));
}

/**
 *  @author giorgio 25/feb/2014
 *  
 *  News from public course indicated in PUBLIC_COURSE_ID_FOR_NEWS
 *  are loaded in the bottomnews template_field with a widget, pls
 *  see widgets/main/index.xml file
 */

$content_dataAr = array(
	'form' => $login->getHtml().$forget_link,
	'newsmsg' => $newsmsg,
	'helpmsg' => $hlpmsg,
    'infomsg' => $infomsg,
	// 'bottomnews' => $bottomnewscontent,
	'status' => $status,
	'message' => $message->getHtml()
);

if (isset($_SESSION['sess_userObj']) && $_SESSION['sess_userObj']-> getType() != AMA_TYPE_VISITOR) {
    $userObj = $_SESSION['sess_userObj'];
    $user_type = $userObj->getTypeAsString();
    $user_name = $userObj->nome;
    $user_full_name = $userObj->getFullName();
	 
    $imgAvatar = $userObj->getAvatar();
    $avatar = CDOMElement::create('img','src:'.$imgAvatar);
    $avatar->setAttribute('class', 'img_user_avatar');

    $content_dataAr['user_modprofilelink'] = $userObj->getHomePage(); //getEditProfilePage();
    $content_dataAr['user_avatar'] = $avatar->getHtml();	  
    $content_dataAr['status'] = translateFN('logged in');
    $content_dataAr['user_name'] = $user_name;
    $content_dataAr['user_full_name'] = $user_full_name;
    $content_dataAr['user_type'] = $user_type;

    unset($content_dataAr['form']);
    $onload_function = 'initDoc(true);';
} else {
    $onload_function = 'initDoc();';
    $content_dataAr['form'] = $login->getHtml().$forget_link;
    unset($content_dataAr['user_modprofilelink']);
    unset($content_dataAr['user_avatar']);	  
    unset($content_dataAr['user_name']);
    unset($content_dataAr['user_type']);
}
/**
 * @author giorgio 26/set/2013
 * 
 * if you have some widget in the page and need to
 * pass some parameter to it, you can do it this way:
 * 
 * $layout_dataAr['widgets']['<template_field_name>'] = array ("<param_name>"=>"<param_value>");
 */

/**
 * Sends data to the rendering engine
 * 
 * @author giorgio 25/set/2013
 * REMEMBER!!!! If there's a widgets/main/index.xml file
 * and the index.tpl has some template_field for the widget
 * it will be AUTOMAGICALLY filled in!!
 */
// ARE::render($layout_dataAr,$content_dataAr);
		$layout_dataAr['JS_filename'] = array(
				JQUERY,
				JQUERY_UI,
				JQUERY_NO_CONFLICT,
				ROOT_DIR . "/js/main/index.js"
		);
/**
 * @author giorgio 
 * include the jQuery and uniform css for proper styling
 */		
		$layout_dataAr['CSS_filename'] = array (
				JQUERY_UI_CSS
		);
if (defined('MODULES_LOGIN') && MODULES_LOGIN) {
	
	$layout_dataAr['CSS_filename'] = array_merge($layout_dataAr['CSS_filename'], 
		array (
			MODULES_LOGIN_PATH . '/layout/support/login-form.css'
	));
}
			
		$optionsAr['onload_func'] = $onload_function;
		
ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL) );
?>