<?php
/**
 * FORMMAIL MODULE.
 *
 * @package        formmail module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           formmail
 * @version		   0.1
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_STUDENT, AMA_TYPE_SUPERTUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout'),
		AMA_TYPE_SUPERTUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_FORMMAIL_PATH .'/config/config.inc.php';
require_once MODULES_FORMMAIL_PATH.'/include/AMAFormmailDataHandler.inc.php';

$self = 'formmail';

$GLOBALS['dh'] = AMAFormmailDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$helpTypes = $GLOBALS['dh']->getHelpTypes($userObj->getType());
$helpTypesHTML = '';

if (!AMA_DB::isError($helpTypes) && is_array($helpTypes) && count($helpTypes)>0) {
	foreach ($helpTypes as $helpType) {
		$helpTypesDOM = CDOMElement::create('div','class:helptype item');
		$helpTypesDOM->setAttribute('data-value', $helpType[AMAFormmailDataHandler::$PREFIX.'helptype_id']);
		$helpTypesDOM->setAttribute('data-email', $helpType['recipient']);
		$helpTypesDOM->addChild(new CText(translateFN($helpType['description'])));
		$helpTypesHTML .= $helpTypesDOM->getHtml();
	}
}


$content_dataAr = array(
	'user_name' => $userObj->getFirstName(),
	'user_homepage' => $userObj->getHomePage(),
	'helptypes' => (strlen($helpTypesHTML)>0 ? $helpTypesHTML : null),
	'user_type' => $user_type,
	'messages' => $user_messages->getHtml(),
	'agenda' => $user_agenda->getHtml(),
	'status' => $status,
	'title' => translateFN('formmail')
);

$layout_dataAr['JS_filename'] = array(
	MODULES_FORMMAIL_PATH . '/js/dropzone.js'
);

$optionsAr['onload_func'] = 'initDoc('.$userObj->getId().');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
