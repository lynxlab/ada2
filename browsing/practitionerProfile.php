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
BrowsingHelper::init($neededObjAr);

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