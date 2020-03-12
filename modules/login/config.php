<?php
/**
 * LOGIN MODULE - config page for option sets of the provider type
 *
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);
/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);
/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);
// MODULE's OWN IMPORTS
// require_once MODULES_LOGIN_PATH .'/config/config.inc.php';
$self = whoami();

foreach (abstractLogin::getLoginProviders(null) as $id=>$className) {
	if (intval($id)===intval($_GET['id'])) {
		$providerClassName = $className;
		require_once MODULES_LOGIN_PATH . '/include/'.$providerClassName.'.class.inc.php';
		$loginObj = new $providerClassName($id);
		break;
	}
}

if (isset($loginObj) && is_object($loginObj) && is_a($loginObj, 'abstractLogin')) {
	$data = $loginObj->generateConfigPage()->getHtml();
	$title = translateFN('Configurazioni '.ucfirst(strtolower($loginObj->loadProviderName())));
	$optionsAr['onload_func'] = 'initDoc(\''.$providerClassName.'\');';
} else {
	$data = translateFN('Impossibile caricare i dati').'. '.translateFN('Login provider ID non riconosciuto').'.';
	$title = translateFN('Erorre login provider');
	$optionsAr = null;
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => $title,
		'data' => $data,
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		MODULES_LOGIN_PATH . '/js/jquery.jeditable.mini.js',
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_REDRAW,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS,
		MODULES_LOGIN_PATH.'/layout/tooltips.css'
);
ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>