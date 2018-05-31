<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\GdprAPI;
use Lynxlab\ADA\Module\GDPR\GdprAcceptPoliciesForm;
use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprLoginRepeaterForm;
use Lynxlab\ADA\Module\GDPR\GdprPolicy;

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

$self = whoami();

try {
	$dataAr = array();
	$submitTo = HTTP_ROOT_DIR .'/index.php';
	if (array_key_exists(GdprPolicy::sessionKey, $_SESSION)) {
		if (array_key_exists('redirectURL', $_SESSION[GdprPolicy::sessionKey])) {
			$dataAr['redirectURL'] = $_SESSION[GdprPolicy::sessionKey]['redirectURL'];
			unset($_SESSION[GdprPolicy::sessionKey]['redirectURL']);
		}
		if (array_key_exists('loginRepeaterSubmit', $_SESSION[GdprPolicy::sessionKey])) {
			$submitTo = HTTP_ROOT_DIR . $_SESSION[GdprPolicy::sessionKey]['loginRepeaterSubmit'];
			unset($_SESSION[GdprPolicy::sessionKey]['loginRepeaterSubmit']);
		}
	}
	$gdprApi = new GdprAPI();

	$loginForm = new GdprLoginRepeaterForm('loginRepeater', $submitTo, $dataAr);
	$policiesForm = new GdprAcceptPoliciesForm('acceptPolicies', null, array(
		'policies' => $gdprApi->getPublishedPolicies(),
		'userAccepted' => $gdprApi->getUserAcceptedPolicies($_SESSION[GdprPolicy::sessionKey]['userId']),
		'userId' => $_SESSION[GdprPolicy::sessionKey]['userId']
	));
	$optionsAr['onload_func'] = 'initDoc(\'loginRepeater\',\'acceptPolicies\');';

	$data = $loginForm->getHtml().$policiesForm->toSemanticUI()->getHtml();

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
	'data' => $data
);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
