<?php
/**
 * Add service - this module provides add service functionality
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

  if(DataValidator::validate_not_empty_string($_POST['service_name']) === FALSE) {
    $errorsAr['service_name'] = true;
  }

  if(DataValidator::validate_not_empty_string($_POST['service_description']) === FALSE) {
    $errorsAr['service_description'] = true;
  }

  if(DataValidator::is_uinteger($_POST['service_level']) === FALSE) {
    $errorsAr['service_level'] = true;
  }

  if(DataValidator::is_uinteger($_POST['service_duration']) === FALSE) {
    $errorsAr['service_duration'] = true;
  }

  if(DataValidator::is_uinteger($_POST['service_min_meetings']) === FALSE) {
    $errorsAr['service_min_meetings'] = true;
  }

  if(DataValidator::is_uinteger($_POST['service_max_meetings']) === FALSE) {
    $errorsAr['service_max_meetings'] = true;
  }

  if(DataValidator::is_uinteger($_POST['service_meeting_duration']) === FALSE) {
    $errorsAr['service_meeting_duration'] = true;
  }

  if(count($errorsAr) > 0) {
    $service_dataAr = $_POST;
    $form = AdminModuleHtmlLib::getAddServiceForm($testersAr,$service_dataAr,$errorsAr);
  }
  else {
    unset($_POST['submit']);
    $service_dataAr = $_POST;

    if($common_dh->add_service($service_dataAr)) {
      header('Location: ' . $userObj->getHomePage());
      exit();
    }
    else {
      $errObj = new ADA_Error();
    }
  }
}
else {
  /*
   * Display the add user form
   */
//  $testersAr = $common_dh->get_all_testers(array('nome'));
//  if(AMA_Common_DataHandler::isError($testersAr)) {
//    $errObj = new ADA_Error($testersAr);
//  }
//  else {
    $testersAr = array();
    $form = AdminModuleHtmlLib::getAddServiceForm($testersAr);
//  }
}

$label = translateFN("Aggiunta di un servizio");
$help  = translateFN("Da qui l'amministratore puo' creare un nuovo servizio");

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