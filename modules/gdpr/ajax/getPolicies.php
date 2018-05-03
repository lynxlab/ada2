<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\GDPR\AMAGdprDataHandler;
use Lynxlab\ADA\Module\GDPR\GdprActions;
use Lynxlab\ADA\Module\GDPR\GdprException;
use Lynxlab\ADA\Module\GDPR\GdprPolicy;

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
require_once MODULES_GDPR_PATH . '/config/config.inc.php';

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
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
$GLOBALS['dh'] = AMAGdprDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$data = array();

try {
	if (intval($_SESSION['sess_userObj']->getType()) !== AMA_TYPE_SWITCHER) {
		throw new GdprException(translateFN("Solo il coordinatore può vedere tutte le le politiche di privacy"));
	}

	$orderby = array('lastEditTS' => 'DESC');
	$policies = $GLOBALS['dh']->findBy('GdprPolicy', array(), $orderby, $GLOBALS['dh']::getPoliciesDB());

	if (count($policies)>0) {
		$data['data'] = array_map(
			/** @var GdprPolicy $el */
			function(GdprPolicy $el) {
				$retArr = array();
				$retArr['id'] = $el->getPrivacy_content_id();
				$retArr['title'] = $el->getTitle();
				$retArr['lastEditTS'] = is_null($el->getLastEditTS()) ? null : ts2dFN($el->getLastEditTS()).' '.ts2tmFN($el->getLastEditTS());
				$retArr['mandatory'] = $el->getMandatory() ? true: false;
				$actions = array();

				if (GdprActions::canDo(GdprActions::EDIT_POLICY, $el)) {
					$actions[] = $el->getActionButton();
				}

				if (count($actions)>0) {
					$retArr['actions'] = array_reduce($actions, function($carry, $item) {
						if (strlen($carry) <= 0) $carry = '';
						$carry .= ($item instanceof \CBase ? $item->getHtml() : '');
						return $carry;
					});
				}

				return $retArr;
			}, $policies);
	} else $data['data'] = array();
} catch (\Exception $e) {
// 	header(' ', true, 400);
	$data['data'] = array();
	$data['data']['error'] = $e->getMessage();
}

header('Content-Type: application/json');
die (json_encode($data, JSON_NUMERIC_CHECK));
