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
$variableToClearAR = array('node', 'layout');
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

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_CLASSAGENDA_PATH .'/config/config.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/AMAClassagendaDataHandler.inc.php';
require_once MODULES_CLASSAGENDA_PATH.'/include/management/rollcallManagement.inc.php';

$self = 'rollcall';
if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

// $id_course_instance is coming from $_GET
$rollcallManager = new rollcallManagement($id_course_instance);
$data = $rollcallManager->run(MODULES_CLASSAGENDA_DO_ROLLCALLHISTORY);

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

$layout_dataAr ['JS_filename'] =  array ( JQUERY, JQUERY_UI, JQUERY_DATATABLE, SEMANTICUI_DATATABLE );
$layout_dataAr ['CSS_filename'] = array ( JQUERY_UI_CSS, SEMANTICUI_DATATABLE_CSS );

//	$optionsAr ['onload_func'] = 'initDoc(\''.htmlentities(json_encode($datetimesAr)).'\',\''.htmlentities(json_encode($inputProposalNames)).'\','.MAX_PROPOSAL_COUNT.');';

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
