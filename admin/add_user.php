<?php
/**
 * Add user - this module provides add user functionality
 * 
 * 
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */

/**
 * Base config file 
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_ADMIN);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_ADMIN => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = admin!

include_once 'include/admin_functions.inc.php';
include_once 'include/AdminUtils.inc.php';
/*
 * YOUR CODE HERE
 */

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * Handle data from $_POST:
   * 1. validate user submitted data
   * 2. if there are errors, display the add user form updated with error messages
   * 3. if there aren't errors, add this user to the common database and to
   *    the tester databases associated with this user.
   */

    
  /*
   * Validazione dati
   */
  $errorsAr = array();
  
  if($_POST['user_tester'] == 'none') {
    $errorsAr['user_tester'] = true;
  }
  
  if(DataValidator::is_uinteger($_POST['user_type']) === FALSE) {
    $errorsAr['user_type'] = true;
  }
  
  if(DataValidator::validate_firstname($_POST['user_firstname']) === FALSE) {
    $errorsAr['user_firstname'] = true;
  }
  
  if(DataValidator::validate_lastname($_POST['user_lastname']) === FALSE) {
    $errorsAr['user_lastname'] = true;
  }
  
  if(DataValidator::validate_email($_POST['user_email']) === FALSE) {
    $errorsAr['user_email'] = true;
  }

  if(DataValidator::validate_username($_POST['user_username']) === FALSE) {
    $errorsAr['user_username'] = true;
  }
  
  if(DataValidator::validate_password($_POST['user_password'], $_POST['user_passwordcheck']) === FALSE) {
    $errorsAr['user_password'] = true;
  }
  
  if(DataValidator::validate_string($_POST['user_address'])=== FALSE) {
    $errorsAr['user_address'] = true;
  }

  if(DataValidator::validate_string($_POST['user_city'])=== FALSE) {
    $errorsAr['user_city'] = true;
  }
  
  if(DataValidator::validate_string($_POST['user_province'])=== FALSE) {
    $errorsAr['user_province'] = true;
  }
  
  if(DataValidator::validate_string($_POST['user_country'])=== FALSE) {
    $errorsAr['user_country'] = true;
  }
  
  if(DataValidator::validate_string($_POST['user_fiscal_code'])=== FALSE) {
    $errorsAr['user_fiscal_code'] = true;
  }
  
  if(DataValidator::validate_birthdate($_POST['user_birthdate'])=== FALSE) {
    $errorsAr['user_birthdate'] = true;
  }

  if(DataValidator::validate_string($_POST['user_sex'])=== FALSE) {
    $errorsAr['user_sex'] = true;
  }
  
  if(DataValidator::validate_phone($_POST['user_phone']) === FALSE) {
    $errorsAr['user_phone'] = true;
  }

  
  if(count($errorsAr) > 0) {
    unset($_POST['submit']);
    $user_dataAr = $_POST;
    $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));

    if(AMA_Common_DataHandler::isError($testers_dataAr)) {
      $errObj = new ADA_Error($testersAr,translateFN("Errore nell'ottenimento delle informazioni sui tester"));
    }
    else {
      $testersAr = array();
      foreach($testers_dataAr as $tester_dataAr) {
        $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
      }
      $form = AdminModuleHtmlLib::getAddUserForm($testersAr,$user_dataAr,$errorsAr);
    }
  }
  else {
    
    if($_POST['user_layout'] == 'none') {
      $user_layout = '';
    }
    else {
      $user_layout = $_POST['user_layout'];
    }
    
    $user_dataAr = array(
 	  'nome'      => $_POST['user_firstname'],
	  'cognome'   => $_POST['user_lastname'],
	  'tipo'      => $_POST['user_type'], 
      'email'     => $_POST['user_email'],
	  'username'  => $_POST['user_username'],
	  'layout'    => $user_layout,
      'indirizzo' => $_POST['user_address'],
	  'citta'     => $_POST['user_city'],
	  'provincia' => $_POST['user_province'],
	  'nazione'   => $_POST['user_country'],
	  'codice_fiscale' => $_POST['user_fiscal_code'],
      'datanascita'    => $_POST['user_birthdate'],
      'sesso'          => $_POST['user_sex'],
      'telefono'               => $_POST['user_phone'],
	  'stato'                  => 0,//DataValidator::validate_string($_POST['user_status'])
    );
    
    switch($_POST['user_type']) {
      case AMA_TYPE_STUDENT:
        $userObj = new ADAUser($user_dataAr);
        break;
      case AMA_TYPE_AUTHOR:
        $userObj = new ADAAuthor($user_dataAr);
        break;
      case AMA_TYPE_TUTOR:
        $userObj = new ADAPractitioner($user_dataAr);
        break;        
      case AMA_TYPE_SWITCHER:
        $userObj = new ADASwitcher($user_dataAr);
        break;
      case AMA_TYPE_ADMIN:
        $userObj = new ADAAdmin($user_dataAr);
        break;                        
    }
    $userObj->setPassword($_POST['user_password']);
    $result = MultiPort::addUser($userObj, array($_POST['user_tester']));
    if($result > 0) {
      if($userObj instanceof ADAAuthor ) {
          AdminUtils::performCreateAuthorAdditionalSteps($userObj->getId());
      } elseif ($userObj instanceof ADASwitcher || $userObj instanceof ADAPractitioner) {
          AdminUtils::createUploadDirForUser($userObj->getId());
      }
      $message = translateFN("Utente aggiunto con successo");
      header('Location: ' . $userObj->getHomePage($message));
      exit();
    }
    else {
      /*
       * Qui bisogna ricreare il form per la registrazione passando in $errorsAr['registration_error'] 
       * $result e portando li' dentro lo switch su $result
       */
      $errorsAr['registration_error'] = $result;
      
      unset($_POST['submit']);
      $user_dataAr = $_POST;
      $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));
        
      if(AMA_Common_DataHandler::isError($testers_dataAr)) {
        $errObj = new ADA_Error($testersAr,translateFN("Errore nell'ottenimento delle informazioni sui tester"));
      }
      else {
        $testersAr = array();
        foreach($testers_dataAr as $tester_dataAr) {
          $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
        }
        $form = AdminModuleHtmlLib::getAddUserForm($testersAr,$user_dataAr,$errorsAr);
      }
    }
  }
}
else {
  /*
   * Display the add user form
   */
  $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));

  if(AMA_Common_DataHandler::isError($testers_dataAr)) {

    $errObj = new ADA_Error($testersAr,translateFN("Errore nell'ottenimento delle informazioni sui tester"));
  }
  else {
    $testersAr = array();
    foreach($testers_dataAr as $tester_dataAr) {
      $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
    }
    $form = AdminModuleHtmlLib::getAddUserForm($testersAr);
  }
}
$label = translateFN("Aggiunta utente");
$help  = translateFN("Da qui l'amministratore puo' creare un nuovo utente");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$module = $home_link->getHtml() . ' > ' . $label;

$menu_dataAr = array();
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDateField();';

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $form->getHtml(),
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);
?>