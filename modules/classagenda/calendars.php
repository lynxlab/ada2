<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classagenda
 * @version			0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_CLASSAGENDA_PATH.'/include/AMAClassagendaDataHandler.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/management/calendarsManagement.inc.php';

$self = 'calendars';

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$calendarsManager = new calendarsManagement();
$data = $calendarsManager->run(MODULES_CLASSAGENDA_EDIT_CAL);

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'help' => $data['help'],
		'title' => $data['title'],
		'data' => $data['htmlObj']->getHtml()
);

$layout_dataAr ['JS_filename'] =  array ( JQUERY, JQUERY_UI );
$layout_dataAr ['CSS_filename'] = array ( JQUERY_UI_CSS );

// NOTE: if i18n file is not found it'll be discarded by the rendering engine
array_push($layout_dataAr['JS_filename'], MODULES_CLASSAGENDA_PATH . '/js/fullcalendar/moment.min.js');
array_push($layout_dataAr['JS_filename'], MODULES_CLASSAGENDA_PATH . '/js/fullcalendar/fullcalendar.min.js');
array_push($layout_dataAr['JS_filename'], MODULES_CLASSAGENDA_PATH . '/js/fullcalendar/lang/' . $_SESSION ['sess_user_language'] . '.js');
array_push($layout_dataAr['JS_filename'], MODULES_CLASSAGENDA_PATH . '/js/fullcalendar/gcal.js');

array_push($layout_dataAr['CSS_filename'], MODULES_CLASSAGENDA_PATH . '/js/fullcalendar/fullcalendar.css' );

//	$optionsAr ['onload_func'] = 'initDoc(\''.htmlentities(json_encode($datetimesAr)).'\',\''.htmlentities(json_encode($inputProposalNames)).'\','.MAX_PROPOSAL_COUNT.');';

$optionsAr['onload_func'] = 'initDoc('.$userObj->getType().');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
