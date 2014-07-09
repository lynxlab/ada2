<?php
/**
 * Displays information about users
 *
 * @package
 * @author    Stefano Penge <steve@lynxlab.com>
 * @author    Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author    Vito Modena <vito@lynxlab.com>
 * @copyright Copyright (c) 2009, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  'default';//whoami();

include_once '../tutor/include/tutor_functions.inc.php';
include_once '../tutor/include/eguidance_tutor_form_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';

/*
 * deve mostrare
 * 1. il report delle interazioni concluse, con la durata (?) e la tipologia
 * 2. gli appuntamenti ancora da effettuare con l'utente
 * 3. le sue note private sull'utente
 */
if(isset($_GET['op']) && $_GET['op'] == 'csv') {
  $event_token = DataValidator::validate_event_token($_GET['event_token']);
  if($event_token === FALSE) {
    $errObj = new ADA_Error(NULL,
                             translateFN("Dati in input per il modulo user_service_detail non corretti"),
                             NULL, NULL, NULL, $userObj->getHomePage());
  }
/*
 * type_of_guidance
 * user_fullname
 * user_country
 * service_duration
 */

  $eguidance_session_dataAr = $dh->get_eguidance_session_with_event_token($event_token);
  if(AMA_DataHandler::isError($eguidance_session_dataAr)) {
    $errObj = new ADA_Error($eguidance_session_dataAr);
  }
  else {
  //  $tutoredUserObj = MultiPort::findUser($eguidance_session_dataAr['id_utente']);
    $eguidance_session_dataAr['user_fullname'] = $userObj->getFullName();
    $eguidance_session_dataAr['user_country']  = $userObj->getCountry();
    $eguidance_session_dataAr['service_duration'] = '';

    createCSVFileToDownload($eguidance_session_dataAr);
    /*
     * exits here.
     */
  }
}
else {
  // $id_user = DataValidator::is_uinteger($_GET['id_user']);
  // $id_course_instance = DataValidator::is_uinteger($_GET['id_course_instance']);
  $id_user = $_SESSION['sess_id_user'];
  $id_course_instance = $_SESSION['sess_id_course_instance'];
  
  if($id_user === FALSE || $id_course_instance === FALSE) {
    $errObj = new ADA_Error(NULL,
                             translateFN("Dati in input per il modulo user_servide_detail non corretti"),
                             NULL, NULL, NULL, $userObj->getHomePage());
  }

  /*
   * User data to display
   */
  
  // $tutoredUserObj = MultiPort::findUser($id_user);
  $user_data = TutorModuleHtmlLib::getEguidanceSessionUserDataTable($userObj);

  /*
   * Service data to display
   */
  $id_course = $dh->get_course_id_for_course_instance($id_course_instance);
  if(!AMA_DataHandler::isError($id_course)) {
    $service_infoAr = $common_dh->get_service_info_from_course($id_course);
    if(!AMA_Common_DataHandler::isError($service_infoAr)) {
      $service_data = TutorModuleHtmlLib::getServiceDataTable($service_infoAr);
    }
    else {
      $service_data = new CText('');
    }
  }

  /*
   * Eguidance sessions data to display
   */
  $eguidance_sessionsAr = $dh->get_eguidance_sessions($id_course_instance);
  if(AMA_DataHandler::isError($eguidance_sessionsAr) || count($eguidance_sessionsAr) == 0) {
    $eguidance_data = new CText('');
  }
  else {
    $thead_data = array(translateFN('Eguidance sessions conducted'), '', '','');
    $tbody_data = array();
    foreach($eguidance_sessionsAr as $eguidance_sessionAr) {
      $eguidance_date = ts2dFN($eguidance_sessionAr['data_ora']);
      $eguidance_type = EguidanceSession::textForEguidanceType($eguidance_sessionAr['tipo_eguidance']);
      $href = 'eguidance_tutor_form.php?event_token=' . $eguidance_sessionAr['event_token'];
      $eguidance_form = CDOMElement::create('a', "href:$href");
      $eguidance_form->addChild(new CText('edit'));

      $href = 'user_service_detail.php?op=csv&event_token=' . $eguidance_sessionAr['event_token'];
      $download_csv = CDOMElement::create('a', "href:$href");
      $download_csv->addChild(new CText('download csv'));

      $tbody_data[] = array($eguidance_date, $eguidance_type, $eguidance_form, $download_csv);
    }
    $eguidance_data = BaseHtmlLib::tableElement('',$thead_data,$tbody_data);
  }

  /*
   * Future appointments with this user
   *
   *
   * Potremmo avere una classe
   * $agenda = new ADAAgenda($userObj);
   * $appointments = $agenda->futureAppointmentsWithUser($tutoredUserObj->getId());
   *
   */
  $fields_list_Ar = array('data_ora', 'titolo');
  $clause         = ' data_ora > ' . time()
                  . ' AND id_mittente='.$userObj->getId()
                  . ' AND (flags & ' . ADA_EVENT_CONFIRMED .')';

  $sort_field     = ' data_ora desc';

  $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
  $msgs_ha = $mh->find_messages($userObj->getId(),
                                ADA_MSG_AGENDA,
                                $fields_list_Ar,
                                $clause,
                                $sort_field);
  if(AMA_DataHandler::isError($msgs_ha) || count($msgs_ha) == 0) {
    $appointments_data = new CText('');
  }
  else {
    $thead_data = array(translateFN('Date'), translateFN('Appointment type'));
    $tbody_data = array();
    foreach($msgs_ha as $appointment) {
      $tbody_data[] = array(ts2dFN($appointment[0]), ADAEventProposal::removeEventToken($appointment[1]));
    }
    $appointments_data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data);
  }
  $data = $appointments_data->getHtml()
        . $user_data->getHtml()
        . $service_data->getHtml()
        . $eguidance_data->getHtml()
        ;
}

$label = translateFN('user service details');
$help  = translateFN("Details");

$home_link = CDOMElement::create('a','href:tutor.php');
$home_link->addChild(new CText(translateFN("Practitioner's home")));
$module = $home_link->getHtml() . ' > ' . $label;

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'edit_user'=> $edit_profile_link->getHtml(),
  'path'      => $module,
  'label'     => $label,
  'dati'      => $data
);

ARE::render($layout_dataAr, $content_dataAr);
?>