<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprPolicy;
use Lynxlab\ADA\Module\GDPR\GdprException;

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

try {

	if (!GdprActions::canDo(GdprActions::LIST_POLICIES)) {
		throw new GdprException(translateFN("Solo un utente abilitato può vedere tutte le politiche di privacy"));
	}

	$canEdit = GdprActions::canDo(GdprActions::EDIT_POLICY);

	$tableID = 'list_policies';
	$dataForJS = array('canEdit' => $canEdit);

	$table = BaseHtmlLib::tableElement('id:'.$tableID.',class:hover row-border display '.ADA_SEMANTICUI_TABLECLASS, GdprPolicy::getTableHeader($canEdit), array());
	$data = $table->getHtml();

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

	$optionsAr['onload_func'] = 'initDoc(\''.$tableID.'\','.htmlentities(json_encode($dataForJS, JSON_FORCE_OBJECT), ENT_COMPAT, ADA_CHARSET).');';

} catch (\Exception $e) {
	$message = CDOMElement::create('div','class:ui icon error message');
	$message->addChild(CDOMElement::create('i','class:attention icon'));
	$mcont = CDOMElement::create('div','class:content');
	$mheader = CDOMElement::create('div','class:header');
	$mheader->addChild(new CText(translateFN('Errore politiche privacy GDPR')));
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
	'title' => translateFN('Elenco Politiche Privacy GDPR')
);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
