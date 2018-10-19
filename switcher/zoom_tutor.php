<?php
/**
 * ZOOM TUTOR.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER => array('layout')
);


require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  'switcher';  // = switcher!

include_once 'include/'.$self.'_functions.inc.php';

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
SwitcherHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

$tutor_id = DataValidator::is_uinteger($_GET['id']);
if($tutor_id == false) {
  header('Location: ' . $userObj->getHomePage());
  exit();
}

$tutor_ha = $dh->get_tutor($tutor_id);
if(AMA_DataHandler::isError($tutor_ha)) {
  $errObj = new ADA_Error($tutor_ha, translateFN('An error occurred while reading tutor data.'));
}

//$tutored_users_number = $dh->get_number_of_tutored_users($id);
$tutored_user_ids = $dh->get_tutored_user_ids($id);
if(AMA_DataHandler::isError($tutored_user_ids)) {
  $errObj = new ADA_Error($tutored_user_ids, translateFN('An error occurred while reading tutored user ids'));
}

$number_of_active_tutored_users = $common_dh->get_number_of_users_with_status($tutored_user_ids, ADA_STATUS_REGISTERED);
if(AMA_Common_DataHandler::isError($number_of_active_tutored_users)) {
  $errObj = new ADA_Error($number_of_active_tutored_users, translateFN('An error occurred while retrieving the number of active tutored users.'));
}

$tutor_ha['utenti seguiti'] = $number_of_active_tutored_users;

unset($tutor_ha['tipo']);
unset($tutor_ha['layout']);
unset($tutor_ha['tariffa']);
unset($tutor_ha['codice_fiscale']);

$data = BaseHtmlLib::plainListElement('',$tutor_ha);

$banner = include ROOT_DIR.'/include/banner.inc.php';

$status = translateFN('Caratteristiche del practitioner');

// preparazione output HTML e print dell' output
$title = translateFN('ADA - dati epractitioner');

$content_dataAr = array(
  'menu'      => $menu,
  'banner'    => $banner,
  'dati'      => $data->getHtml(),
  'help'      => $help,
  'status'    => $status,
  'user_name' => $user_name,
  'edit_profile'=>$userObj->getEditProfilePage(),
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);