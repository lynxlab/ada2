<?php
/**
 * ADMIN.
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
    $testersAr = array();
    $tester_dataAr = array(
      array(translateFN('id')       , $tester_infoAr[0]),
      array(translateFN('Nome')     , $tester_infoAr[1]),
      array(translateFN('Ragione Sociale')       , $tester_infoAr[2]),
      array(translateFN('Indirizzo')  , $tester_infoAr[3]),
      array(translateFN('Citt&agrave')     , $tester_infoAr[4]),
      array(translateFN('Provincia') , $tester_infoAr[5]),
      array(translateFN('Nazione')  , $tester_infoAr[6]),
      array(translateFN('Telefono')    , $tester_infoAr[7]),
      array(translateFN('E-mail')    , $tester_infoAr[8]),
      array(translateFN('Descrizione')     , $tester_infoAr[11]),
      array(translateFN('Responsabile')     , $tester_infoAr[9]),
      array(translateFN('IBAN')     , $tester_infoAr[12]),
      array(translateFN('Puntatore al database')  , $tester_infoAr[10])
      );
    //$tester_data = BaseHtmlLib::tableElement('',array(),$tester_dataAr);



    /*
    $services_dataAr = $common_dh->get_info_for_tester_services($id_tester);
    if(AMA_Common_DataHandler::isError($services_dataAr)) {
      $errObj = new ADA_Error($services_dataAr);
    }
    else {
      $tester_services = AdminModuleHtmlLib::displayServicesOnThisTester($id_tester, $services_dataAr);
    }
    */

    $tester_dsn = MultiPort::getDSN($tester_infoAr[10]);
    if($tester_dsn != NULL) {
      $tester_dh = AMA_DataHandler::instance($tester_dsn);
      $users_on_this_tester = $tester_dh->count_users_by_type(array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER,AMA_TYPE_ADMIN));
      if(AMA_DataHandler::isError($users_on_this_tester)) {
        $errObj = new ADA_Error($users_on_this_tester);
      }
      else {
        // $users_list_link = CDOMElement::create('div','id:tester_users');
        $tester_dataAr[] = [translateFN('Numero di utenti presenti sul provider: '),  $users_on_this_tester];
      }
    }
    $tester_data = AdminModuleHtmlLib::displayTesterInfo($id_tester, $tester_dataAr);
    if (isset($users_on_this_tester) && intval($users_on_this_tester)>0) {
      $link = CDOMElement::create('a','class:ui button,href:list_users.php?id_tester='.$id_tester);
      $link->addChild(new CText(translateFN('Lista utenti')));
      $tester_data->addChild($link);
    }
  }
}
else {
  /*
   * non e' stato passato id_tester
   */
}




//$tester_services = new CText('servizi offerti da questo provider<br />');
//$user_list_link  = new CText('numero di utenti presenti sul provider e link alista utenti');



$label = translateFN("Profilo del provider");

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$module = $home_link->getHtml() . ' > ' . $label;

// $help  = translateFN("Profilo del provider");

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'label'        => $label,
  // 'help'         => $help,
  'data'         => $tester_data->getHtml(),
                    // $tester_services->getHtml() .
                    // $users_list_link->getHtml(),
  'module'       => $module,
);


$menuOptions['id_tester'] = $_GET['id_tester'];

ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
?>