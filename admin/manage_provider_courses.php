<?php
/**
 * Manage association of services to the selected tester.
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

/*
 * YOUR CODE HERE
 */
/*
 * 1. dati del tester (con link modifica)
 * 2. elenco servizi erogati dal tester (con link modifica)
 * 3. link a lista utenti presenti sul tester
 */

$id_tester = DataValidator::is_uinteger($_GET['id_tester']);

if($id_tester !== FALSE) {
  $tester_infoAr = $common_dh->get_tester_info_from_id($id_tester);
  if(AMA_Common_DataHandler::isError($tester_infoAr)) {
    $errObj = new ADA_Error($tester_infoAr);
  }
  else {
/*    $testersAr = array();
    $tester_dataAr = array(
      array(translateFN('id')       , $tester_infoAr[0]),
      array(translateFN('Nome')     , $tester_infoAr[1]),
      array(translateFN('Ragione Sociale')       , $tester_infoAr[2]),
      array(translateFN('Indirizzo')  , $tester_infoAr[3]),
      array(translateFN('Provincia') , $tester_infoAr[4]),
      array(translateFN('Citt&agrave;')     , $tester_infoAr[5]),
      array(translateFN('Nazione')  , $tester_infoAr[6]),
      array(translateFN('Telefono')    , $tester_infoAr[7]),
      array(translateFN('E-mail')    , $tester_infoAr[8]),
      array(translateFN('Responsabile')     , $tester_infoAr[9]),
      array(translateFN('Puntatore al database')  , $tester_infoAr[10])
    );
    //$tester_data = BaseHtmlLib::tableElement('',array(),$tester_dataAr);
    
    $tester_data = AdminModuleHtmlLib::displayTesterInfo($tester_dataAr);
    
    $services_dataAr = $common_dh->get_info_for_tester_services($id_tester);
    if(AMA_Common_DataHandler::isError($services_dataAr)) {
      $errObj = new ADA_Error($services_dataAr);        
    }
    else {
      $tester_services = AdminModuleHtmlLib::displayServicesOnThisTester($id_tester, $services_dataAr);
    }
      
    $tester_dsn = MultiPort::getDSN($tester_infoAr[10]);
    if($tester_dsn != NULL) {
      $tester_dh = AMA_DataHandler::instance($tester_dsn);
      $users_on_this_tester = $tester_dh->count_users_by_type(array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER,AMA_TYPE_ADMIN));
      if(AMA_DataHandler::isError($users_on_this_tester)) {
        $errObj = new ADA_Error($users_on_this_tester);        
      }
      else {
        $user_list_link = new CText('Numero di utenti presenti sul tester: '.$users_on_this_tester);
      }
    }
    */
  }
}
else {
  /*
   * non e' stato passato id_tester
   */
}




//$tester_services = new CText('servizi offerti da questo tester<br />');
//$user_list_link  = new CText('numero di utenti presenti sul tester e link alista utenti');



$label = translateFN("Gestisci servizi associati al tester");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$tester_profile_link = CDOMElement::create('a','href:tester_profile.php?id_tester='.$id_tester);
$tester_profile_link->addChild(new CText(translateFN("Profilo del tester")));
$module = $home_link->getHtml() . ' > ' . $tester_profile_link->getHtml() . ' > ' .$label;

$help  = translateFN("Gestisci servizi associati al tester");

$menu_dataAr = array(
  array('href' => 'edit_tester.php?id_tester='.$_GET['id_tester'], 'text' => translateFN('Modifica il profilo del tester'))
);
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $data,
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
?>