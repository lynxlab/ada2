<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprException;
use Lynxlab\ADA\Module\GDPR\GdprRequest;
use Ramsey\Uuid\Uuid;

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_GDPR_PATH .'/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(GdprActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

$self = whoami();

$showAll = array_key_exists('showall', $_REQUEST) && intval($_REQUEST['showall'])===1;
$showUUID = array_key_exists('uuid', $_REQUEST);

try {

	if (intval($_SESSION['sess_userObj']->getType()) === AMA_TYPE_VISITOR && $showUUID !== true) {
		throw new GdprException(translateFN("L'utente non registrato può solo vedere il suo numero di pratica"));
	} else if ($showAll === true && !GdprActions::canDo(GdprActions::ACCESS_ALL_REQUESTS)) {
		throw new GdprException(translateFN("Solo un utente abilitato può vedere tutte le richieste"));
	}

	if ($showUUID && !UUid::isValid(trim($_REQUEST['uuid']))) {
		throw new GdprException(translateFN("Numero di pratica non valido"));
	}

	$tableID = 'list_requests';
	$dataForJS = array();
	if ($showAll) $dataForJS['showall'] = intval($showAll);
	if ($showUUID) $dataForJS['uuid'] = trim($_REQUEST['uuid']);

	$layout_dataAr['JS_filename'] = array(
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE
	);

	$layout_dataAr['CSS_filename']= array(
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS,
		SEMANTICUI_DATATABLE_CSS
	);

	$requestClass = 'Lynxlab\ADA\Module\GDPR\GdprRequest';
	if ($showAll) {
		$layout_dataAr['JS_filename'][]  = MODULES_GDPR_PATH .'/js/jeditable-2.0.1/jquery.jeditable.min.js';
	}

	$table = BaseHtmlLib::tableElement('id:'.$tableID.',class:hover row-border display '.ADA_SEMANTICUI_TABLECLASS, $requestClass::getTableHeader($showAll), array());
	$data = $table->getHtml();

	$optionsAr['onload_func'] = 'initDoc(\''.$tableID.'\','.htmlentities(json_encode($dataForJS), ENT_COMPAT, ADA_CHARSET).');';

} catch (\Exception $e) {
	$message = CDOMElement::create('div','class:ui icon error message');
	$message->addChild(CDOMElement::create('i','class:attention icon'));
	$mcont = CDOMElement::create('div','class:content');
	$mheader = CDOMElement::create('div','class:header');
	$mheader->addChild(new CText(translateFN('Errore elenco richieste GDPR')));
	$span = CDOMElement::create('span');
	$span->addChild(new CText($e->getMessage()));
	$mcont->addChild($mheader);
	$mcont->addChild($span);
	$message->addChild($mcont);
	$data = $message->getHtml();
	$optionsAr = null;
}

$content_dataAr = array(
	'user_name' => $userObj->getFirstName(),
	'user_homepage' => $userObj->getHomePage(),
	'user_type' => $user_type,
	'messages' => $user_messages->getHtml(),
	'agenda' => $user_agenda->getHtml(),
	'status' => $status,
	'data' => $data,
	'title' => translateFN('Elenco Richieste GDPR')
);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
