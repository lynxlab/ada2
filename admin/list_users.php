<?php
/**
 * List users
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
$id_tester = DataValidator::is_uinteger($_GET['id_tester']);
if(!isset($_GET['page']) || DataValidator::is_uinteger($_GET['page']) === FALSE){
  $page = 1;
}
else {
  $page = $_GET['page'];
}
$userTypeToFilter = isset($_GET['user_type']) ? DataValidator::is_uinteger($_GET['user_type']) : false;

$users_per_page = 20;

if($id_tester !== FALSE) {
  $tester_info = $common_dh->get_tester_info_from_id($id_tester);
  $tester_dsn = MultiPort::getDSN($tester_info[10]);
  if($tester_dsn != NULL) {
    $tester_dh = AMA_DataHandler::instance($tester_dsn);

    if($userTypeToFilter !== FALSE) {
        $user_typesAr = array($userTypeToFilter);
    } else {
        $user_typesAr = array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER,AMA_TYPE_ADMIN,AMA_TYPE_SUPERTUTOR);
    }
    $users_count = $tester_dh->count_users_by_type($user_typesAr);
    if(AMA_DataHandler::isError($users_count)) {
      $errObj = new ADA_Error($users_count);
    }
    else {
      $pages = ceil($users_count / $users_per_page);
      if($page > $pages) {
        $page = $pages;
      }
      $start = ($page-1)*$users_per_page;

      $users_dataAr = $tester_dh->get_users_by_type_from_position_to_position($user_typesAr,$start, $users_per_page);
      if (AMA_DataHandler::isError($users_dataAr)) {
            $user_type = ADAGenericUser::convertUserTypeFN($userTypeToFilter);
            $data = CDOMElement::create('div');
            $data->addChild(new CText(translateFN('No user of type ') . $user_type));
//        $errObj = new ADA_Error($users_dataAr);
      }
      else {
        $data = AdminModuleHtmlLib::displayUsersOnThisTester($id_tester,$page,$pages,$users_dataAr);
      }
    }
  }
}
else {
  /*
   * non e' stato passato id_tester
   */
}

$label = translateFN("Lista degli utenti presenti sul tester");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$tester_profile_link = CDOMElement::create('a','href:tester_profile.php?id_tester='.$id_tester);
$tester_profile_link->addChild(new CText(translateFN("Profilo del tester")));
$module = $home_link->getHtml() . ' > ' . $tester_profile_link->getHtml() . ' > ' .$label;

$help  = translateFN("Lista degli utenti presenti sul tester");

//$menu_dataAr = array();
$menu_dataAr = array(
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_ADMIN, 'text' => translateFN('Admins list')),
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_SWITCHER, 'text' => translateFN('Switcher list')),
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_AUTHOR, 'text' => translateFN('Authors list')),
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_TUTOR, 'text' => translateFN('Tutors list')),
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_SUPERTUTOR, 'text' => translateFN('SuperTutors list')),
  array('href' => 'list_users.php?id_tester='. $id_tester.'&user_type='.AMA_TYPE_STUDENT, 'text' => translateFN('Students list')),
  array('href' => 'list_users.php?id_tester='. $id_tester, 'text' => translateFN('All Users list')),
  );

$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $data->getHtml(),
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);
$menuOptions['id_tester'] = $id_tester;
ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
?>