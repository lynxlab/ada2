<?php
/**
 * eguidance sessions summary
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
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();

include_once 'include/tutor_functions.inc.php';

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
TutorHelper::init($neededObjAr);

include_once 'include/eguidance_tutor_form_functions.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';


/*
 * If id course instance is not set or is not valid,
 * return to user's home page
 */
$id_course_instance = DataValidator::is_uinteger($_GET['id_course_instance']);
if($id_course_instance === FALSE) {
  $errObj = new ADA_Error(NULL,translateFN('Impossibile accedere al modulo'),
                           NULL,NULL,NULL,$userObj->getHomePage());
}

/*
 * If id user is not set or is not valid,
 * return to user's home page
 */
$id_user = DataValidator::is_uinteger($_GET['id_user']);
if($id_user === FALSE) {
  $errObj = new ADA_Error(NULL,translateFN('Impossibile accedere al modulo'),
                           NULL,NULL,NULL,$userObj->getHomePage());
}

$page = DataValidator::is_uinteger($_GET['page']);
if($page === FALSE) {
  $page = 1;
}

$tutoredUserObj = MultiPort::findUser($id_user);

/*
 * Obtain service information and eguidance data for the given id_course_instance
 */
$id_course = $dh->get_course_id_for_course_instance($id_course_instance);
if(AMA_DataHandler::isError($id_course)) {
  $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento dell'id del servzio"),
                           NULL,NULL,NULL,$userObj->getHomePage());
}

$service_infoAr = $common_dh->get_service_info_from_course($id_course);
if(AMA_Common_DataHandler::isError($service_infoAr)) {
  $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle informazioni sul servizio"),
                           NULL,NULL,NULL,$userObj->getHomePage());
}

$eguidance_session_datesAr = $dh->get_eguidance_session_dates($id_course_instance);
if(AMA_DataHandler::isError($eguidance_session_datesAr)) {
  $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle informazioni sul servizio"),
                           NULL,NULL,NULL,$userObj->getHomePage());
}

$eguidance_sessions_count = count($eguidance_session_datesAr);
if($page > $eguidance_sessions_count) {
  $page = $eguidance_sessions_count;
}

/*
 * Obtain and display an eguidance session evaluation sheet.
 */
$eguidance_session_dataAr = $dh->get_eguidance_session($id_course_instance, $page-1);
if(AMA_DataHandler::isError($eguidance_session_dataAr)
   && $eguidance_session_dataAr->code != AMA_ERR_GET) {
  $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle informazioni sul servizio"),
                           NULL,NULL,NULL,$userObj->getHomePage());
}
else if(AMA_DataHandler::isError($eguidance_session_dataAr)) {
  // Mostrare messaggio non ci sono dati
  $data = new CText(translateFN("There aren't evaluation sheets available"));
  $htmlData = $data->getHtml();
}
else {
	$base_href = 'eguidance_sessions_summary.php?id_course_instance='
	           . $id_course_instance . '&id_user=' . $id_user;

	$p = 1;
	$page_titles = array();
	foreach($eguidance_session_datesAr as $d) {
	  $page_titles[$p++] = ts2dFN($d['data_ora']);
	}

	$pagination_bar = BaseHtmlLib::getPaginationBar($page,$page_titles, $base_href);

	$data = TutorModuleHtmlLib::displayEguidanceSessionData($tutoredUserObj,$service_infoAr,$eguidance_session_dataAr);

	$htmlData = $pagination_bar->getHtml() . $data->getHtml();
}

/*
 *
 */
$label = translateFN('Eguidance session summary');

$home_link = CDOMElement::create('a','href:tutor.php');
$home_link->addChild(new CText(translateFN("Epractitioner's home")));
$path = $home_link->getHtml() . ' > ' . $label;

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'label'     => $label,
  'dati'      => $htmlData,
  'path'      => $path
);

ARE::render($layout_dataAr, $content_dataAr);
?>