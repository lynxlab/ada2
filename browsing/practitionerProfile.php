<?php
/**
 * Practitioner's profile
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
include_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
$self =  whoami();

if(isset($_GET['id']) && DataValidator::is_uinteger($_GET['id'])) {
  $tutorObj = MultiPort::findUser($_GET['id']);
  if ($tutorObj instanceof ADAPractitioner) {
    $dati = CDOMElement::create('div');
    $fullname = CDOMElement::create('div');
    $fullname->addChild(new CText(translateFN('User: ') . ' ' . $tutorObj->getFullName()));
    $username = CDOMElement::create('div');
    $username->addChild(new CText(translateFN('Username: ') . ' ' . $tutorObj->getUserName()));
    $tutorProfile = $tutorObj->getProfile();
    if($tutorProfile == 'NULL') {
      $tutorProfile = '';
    }
    $profile = CDOMElement::create('div');
    $profile->addChild(new CText(translateFN('Profile: ') . ' ' . $tutorProfile));
    $dati->addChild($fullname);
    $dati->addChild($username);
    $dati->addChild($profile);
  }
  else {
    header('Location: ' . $userObj->getHomePage());
  }
}
else {
  header('Location: ' . $userObj->getHomePage());
}

$help   = '';
$status = '';

$menu = '';

$label = translateFN("practitioner's profile");

$home_link = CDOMElement::create('a','href:user.php');
$home_link->addChild(new CText(translateFN("Home dell'Utente")));
$module = $home_link->getHtml() . ' > ' . $label;

$title = translateFN("ADA - practitioner's profile");

$content_dataAr = array(
  'menu'      => $menu,
  'banner'    => $banner,
  'iscrivi'   => $dati->getHtml(),
  'help'      => $help,
  'status'    => $status,
  'label'	  => $label,
  'course_title' => $module,
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml(),
  'events'    => $user_events->getHtml(),
);

ARE::render($layout_dataAr, $content_dataAr);
?>