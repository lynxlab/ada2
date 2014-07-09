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

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

// preparazione output HTML e print dell' output
$title = translateFN('ADA - dati epractitioner');

$content_dataAr = array(
  'menu'      => $menu,
  'banner'    => $banner,
  'dati'      => $data->getHtml(),
  'help'      => $help,
  'status'    => $status,
  'user_name' => $user_name,
  'edit_switcher'=>$edit_profile_link->getHtml(),
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);