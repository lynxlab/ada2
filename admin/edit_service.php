<?php
/**
 * edit service - this module provides service editing functionality
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
    $form = AdminModuleHtmlLib::getEditServiceForm($testersAr,$service_dataAr,$errorsAr);
  }
  else {
    unset($_POST['submit']);
    $service_dataAr = $_POST;
    $result = $common_dh->set_service($_POST['service_id'], $service_dataAr);
    if(AMA_Common_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
    }
    else {
      header('Location: ' . $userObj->getHomePage());
      exit();
    }
  }
}
else {
  /*
   * Display the add user form
   */
  $id_service = DataValidator::is_uinteger($_GET['id_service']);
  if($id_service !== FALSE) {
    $service_infoAr = $common_dh->get_service_info($id_service);
    if(AMA_Common_DataHandler::isError($service_infoAr)) {
      $errObj = new ADA_Error($service_infoAr);
    }
    else {
      $testersAr = array();
      $service_dataAr = array(
        'service_id'               => $service_infoAr[0],
        'service_name'             => $service_infoAr[1],
        'service_description'      => $service_infoAr[2],
        'service_level'            => $service_infoAr[3],
        'service_duration'         => $service_infoAr[4],
        'service_min_meetings'     => $service_infoAr[5],
        'service_max_meetings'     => $service_infoAr[6],
        'service_meeting_duration' => $service_infoAr[7]
      );
      $form = AdminModuleHtmlLib::getEditServiceForm($testersAr,$service_dataAr);
    }
  }
  else {
    $form = new CText('');
  }
}
$label = translateFN("Modifica servizio");

$help  = translateFN("Da qui l'amministratore puo' apportare modifiche ad un servizio esistente");

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'label'        => $label,
  'help'         => $help,
  'data'         => $form->getHtml(),
  'module'       => $label,
);

ARE::render($layout_dataAr, $content_dataAr);
?>