<?php
/**
 * Edit user - this module provides edit user functionality
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
$self = 'default';// whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

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
  
  if(isset($_POST['user_tester']) && $_POST['user_tester'] == 'none') {
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
  
  if(trim($_POST['user_password']) != '') {
    if(DataValidator::validate_password($_POST['user_password'], $_POST['user_passwordcheck']) === FALSE) {
      $errorsAr['user_password'] = true;
    }
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
  
  if(DataValidator::validate_not_empty_string($_POST['user_birthcity'])=== FALSE) {
  	$errorsAr['user_birthcity'] = true;
  }
  
  if(DataValidator::validate_string($_POST['user_birthprovince'])=== FALSE) {
  	$errorsAr['user_birthprovince'] = true;
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
      $form = AdminModuleHtmlLib::getEditUserForm($testersAr,$user_dataAr,$errorsAr);
    }
  }
  else {
    
    if($_POST['user_layout'] == 'none') {
      $user_layout = '';
    }
    else {
      $user_layout = $_POST['user_layout'];
    }
    
   $userToEditObj = MultiPort::findUser($_POST['user_id']);
   
   /*
    * Update user fields
    */
   
   $userToEditObj->setFirstName($_POST['user_firstname']);
   $userToEditObj->setLastName($_POST['user_lastname']);
   $userToEditObj->setEmail($_POST['user_email']);
   //$userToEditObj->setUsername($_POST['user_username']);
   if(trim($_POST['user_password']) != '') {
     $userToEditObj->setPassword($_POST['user_password']);
   }
   $userToEditObj->setLayout($user_layout);
   $userToEditObj->setAddress($_POST['user_address']);
   $userToEditObj->setCity($_POST['user_city']);
   $userToEditObj->setProvince($_POST['user_province']);
   $userToEditObj->setCountry($_POST['user_country']);
   $userToEditObj->setFiscalCode($_POST['user_fiscal_code']);
   $userToEditObj->setBirthDate($_POST['user_birthdate']);
   $userToEditObj->setGender($_POST['user_sex']);
   $userToEditObj->setPhoneNumber($_POST['user_phone']);
   $userToEditObj->setBirthCity($_POST['user_birthcity']);
   $userToEditObj->setBirthProvince($_POST['user_birthprovince']);

   if($userToEditObj instanceof ADAPractitioner) {
     $userToEditObj->setProfile($_POST['user_profile']);
   }

   
   MultiPort::setUser($userToEditObj,array(),true);    
   
   $navigationHistoryObj = $_SESSION['sess_navigation_history'];
   $location = $navigationHistoryObj->lastModule();
   header('Location: ' . $location);
   exit();
  }
}
else {
  /*
   * Display the add user form
   */
  
  if(DataValidator::is_uinteger($_GET['id_user']) === FALSE) {
    $form = new CText('');
  }
  else {
    
    $userToEditObj = MultiPort::findUser($_GET['id_user']);
    
    $user_dataAr = $userToEditObj->toArray();
    
    $testers_for_userAr = $common_dh->get_testers_for_user($_GET['id_user']);
    /*
     * FIXME: selects just one tester. if the user is of type ADAUser
     * we have to display all the associated testers.
     */
    if(!AMA_Common_DataHandler::isError($testers_for_userAr) && count($testers_for_userAr) > 0) {
      $tester = $testers_for_userAr[0];
    }
    else {
      $tester = NULL;
    }
    
    $dataAr = array(
   'user_id' => $user_dataAr['id_utente'], 
   'user_firstname'=> $user_dataAr['nome'],
   'user_lastname'=> $user_dataAr['cognome'],
   'user_type'=> $user_dataAr['tipo'], 
   'user_email'=> $user_dataAr['e_mail'],
   'user_username'=> $user_dataAr['username'],
   'user_layout'=> $user_dataAr['layout'],
   'user_address'=> $user_dataAr['indirizzo'],
   'user_city'=> $user_dataAr['citta'],
   'user_province'=> $user_dataAr['provincia'],
   'user_country'=> $user_dataAr['nazione'],
   'user_fiscal_code'=> $user_dataAr['codice_fiscale'],
   'user_birthdate'=> $user_dataAr['birthdate'],
   'user_sex'=> $user_dataAr['sesso'],
   'user_phone'=> $user_dataAr['telefono'],
   //'user_status'=> $user_dataAr['stato']
    'user_tester' => $tester,
    'user_profile' => isset($user_dataAr['profilo']) ? $user_dataAr['profilo'] : null,
    'user_birthcity' => $user_dataAr['birthcity'],
    'user_birthprovince' => $user_dataAr['birthprovince']
    );
    
    
    $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));
  
    if(AMA_Common_DataHandler::isError($testers_dataAr)) {
  
      $errObj = new ADA_Error($testersAr,translateFN("Errore nell'ottenimento delle informazioni sui tester"));
    }
    else {
      $testersAr = array();
      foreach($testers_dataAr as $tester_dataAr) {
        $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
      }
      
      
      
      
      $form = AdminModuleHtmlLib::getEditUserForm($testersAr,$dataAr);
    }
  }
}
$label = translateFN("Modifica dati utente");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));


if (isset($id_tester)) {
	$tester_profile_link = CDOMElement::create('a','href:tester_profile.php?id_tester='.$id_tester);
	$tester_profile_link->addChild(new CText(translateFN("Profilo del tester")));
	$list_users_link = CDOMElement::create('a','href:list_users.php?id_tester='.$id_tester.'&page='.$page);
	$list_users_link->addChild(new CText(translateFN("Lista utenti")));
	
}

$module = $home_link->getHtml();
if (isset($tester_profile_link)) $module .= ' > ' . $tester_profile_link->getHtml();
if (isset($list_users_link)) $module .= ' > ' .$list_users_link->getHtml();
$module .= ' > ' . $label;

$help  = translateFN("Lista degli utenti presenti sul tester");
$menu_dataAr = array();
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

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

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDateField();';

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);
?>