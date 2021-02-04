<?php
/**
 * Add tester - this module provides add tester functionality
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
AdminHelper::init($neededObjAr);

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

  if(DataValidator::validate_not_empty_string($_POST['tester_name']) === FALSE) {
    $errorsAr['tester_name'] = true;
  }

  if(DataValidator::validate_string($_POST['tester_rs']) === FALSE) {
    $errorsAr['tester_rs'] = true;
  }

  if(DataValidator::validate_not_empty_string($_POST['tester_address']) === FALSE) {
    $errorsAr['tester_address'] = true;
  }

  if(DataValidator::validate_not_empty_string($_POST['tester_province']) === FALSE) {
    $errorsAr['tester_province'] = true;
  }

  if(DataValidator::validate_not_empty_string($_POST['tester_city']) === FALSE) {
    $errorsAr['tester_city'] = true;
  }

  if(DataValidator::validate_not_empty_string($_POST['tester_country']) === FALSE) {
    $errorsAr['tester_country'] = true;
  }

  if(DataValidator::validate_phone($_POST['tester_phone']) === FALSE) {
    $errorsAr['tester_phone'] = true;
  }

  if(DataValidator::validate_email($_POST['tester_email']) === FALSE) {
    $errorsAr['tester_email'] = true;
  }

  if(DataValidator::validate_string($_POST['tester_desc']) === FALSE) {
    $errorsAr['tester_desc'] = true;
  }

  if(DataValidator::validate_string($_POST['tester_resp']) === FALSE) {
    $errorsAr['tester_resp'] = true;
  }
  // validate_testername valida il puntatore, non il nome del tester.
  if(DataValidator::validate_testername($_POST['tester_pointer'],MULTIPROVIDER) === FALSE) {
    $errorsAr['tester_pointer'] = true;
  }

  if (array_key_exists('tester_iban', $_POST) && strlen(trim($_POST['tester_iban']))>0 && DataValidator::validate_iban(trim($_POST['tester_iban'])) === FALSE) {
    $errorsAr['tester_iban'] = true;
  }


  if(count($errorsAr) > 0) {
    $tester_dataAr = $_POST;
    $testersAr = array();
    $form = AdminModuleHtmlLib::getAddTesterForm($testersAr,$tester_dataAr,$errorsAr);
  }
  else {
    unset($_POST['submit']);
    $tester_dataAr = $_POST;

    $result = $common_dh->add_tester($tester_dataAr);
    if(AMA_Common_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
      $form = new CText('');
    }
    else {
      $adminUsersArr = $common_dh->get_users_by_type(array(AMA_TYPE_ADMIN));
      if (!AMA_DB::isError($adminUsersArr) && is_array($adminUsersArr) && count($adminUsersArr)>0) {
      	foreach ($adminUsersArr as $adminUser) {
      		$adminUserObj = MultiPort::findUserByUsername($adminUser['username']);
		    $common_dh->add_user_to_tester($adminUserObj->getId(),$result);
      	}
      }
      header('Location: ' . $userObj->getHomePage());
      exit();
    }
  }
}
else {
  /*
   * Display the add user form
   */
  $testersAr = array();
  $form = AdminModuleHtmlLib::getAddTesterForm($testersAr);
}
$label = translateFN("Aggiunta tester");
$help  = translateFN("Da qui l'amministratore puo' creare un nuovo tester");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$module = $home_link->getHtml() . ' > ' . $label;

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

ARE::render($layout_dataAr, $content_dataAr);
?>