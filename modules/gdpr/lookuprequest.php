<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprLookupRequestForm;

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

/**
 * sample valid but not found uuid is: Uuid:NIL (00000000-0000-0000-0000-000000000000)
 */

$form = new GdprLookupRequestForm('gdprrequestlookup');
$data = $form->withSubmit()->toSemanticUI()->getHtml();
$optionsAr['onload_func'] = 'initDoc(\''.$form->getName().'\',\'checkimg\');';


$content_dataAr = array(
	'user_name' => $userObj->getFirstName(),
	'user_homepage' => $userObj->getHomePage(),
	'user_type' => $user_type,
	'messages' => $user_messages->getHtml(),
	'agenda' => $user_agenda->getHtml(),
	'status' => $status,
	'data' => $data,
	'title' => translateFN('Cerca Richiesta GDPR')
);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
