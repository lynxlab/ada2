<?php
/**
 * Esit tester - this module provides tester editing functionality
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

  if(DataValidator::validate_testername($_POST['tester_pointer'],MULTIPROVIDER) === FALSE) {
    $errorsAr['tester_pointer'] = true;
  }

  if(count($errorsAr) > 0) {
    $tester_dataAr = $_POST;
    $form = AdminModuleHtmlLib::getEditTesterForm($testersAr,$tester_dataAr,$errorsAr);
  }
  else {
    unset($_POST['submit']);
    $tester_dataAr = $_POST;

    $result = $common_dh->set_tester($tester_dataAr['tester_id'], $tester_dataAr);
    if(AMA_Common_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
      $form = new CText('');
    }
    else {
      header('Location: ' . HTTP_ROOT_DIR.'/admin/tester_profile.php?id_tester='.$tester_dataAr['tester_id']);
      exit();
    }
  }
}
else {
  /*
   * Display the add user form
   */
  $id_tester = DataValidator::is_uinteger($_GET['id_tester']);
  if($id_tester !== FALSE) {
    $tester_infoAr = $common_dh->get_tester_info_from_id($id_tester);
    if(AMA_Common_DataHandler::isError($tester_infoAr)) {
      $errObj = new ADA_Error($tester_infoAr);
    }
    else {
      $testersAr = array();
      $tester_dataAr = array(
        'tester_id'       => $tester_infoAr[0],
        'tester_name'     => $tester_infoAr[1],
        'tester_rs'       => $tester_infoAr[2],
        'tester_address'  => $tester_infoAr[3],
        'tester_city'     => $tester_infoAr[4],
        'tester_province' => $tester_infoAr[5],
        'tester_country'  => $tester_infoAr[6],
        'tester_phone'    => $tester_infoAr[7],
        'tester_email'    => $tester_infoAr[8],
      	'tester_resp'     => $tester_infoAr[9],
        'tester_pointer'  => $tester_infoAr[10],
        'tester_desc'     => $tester_infoAr[11]
      );

      $form = AdminModuleHtmlLib::getEditTesterForm($testersAr, $tester_dataAr);
    }
  }
  else {
    $form = new CText();
  }

}
$label = translateFN("Modifica tester");

$help  = translateFN("Da qui l'amministratore puo' apportare modifiche ad un tester esistente");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$tester_profile_link = CDOMElement::create('a','href:tester_profile.php?id_tester='.$id_tester);
$tester_profile_link->addChild(new CText(translateFN("Profilo del tester")));
$module = $home_link->getHtml() . ' > ' . $tester_profile_link->getHtml() . ' > ' .$label;

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