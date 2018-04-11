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
use Ramsey\Uuid\Uuid;
use Lynxlab\ADA\Module\GDPR\GdprRequest;

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

$showAll = null;
$uuid = null;
$data = array();
if (array_key_exists('showall', $_REQUEST)) {
	$showAll = filter_var($_REQUEST['showall'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_SCALAR);
}
if (array_key_exists('uuid', $_REQUEST)) {
	$uuid = filter_var($_REQUEST['uuid'], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR);
}

try {

	if (intval($_SESSION['sess_userObj']->getType()) === AMA_TYPE_VISITOR && strlen($uuid)<=0) {
		throw new GdprException(translateFN("L'utente non registrato può solo vedere il suo numero di pratica"));
	} else if ($showAll === true && intval($_SESSION['sess_userObj']->getType()) !== AMA_TYPE_SWITCHER) {
		throw new GdprException(translateFN("Solo il coordinatore può vedere tutte le richieste"));
	}

	if (strlen($uuid)>0 && !Uuid::isValid($uuid)) {
		throw new GdprException(translateFN("Numero di pratica non valido"));
	}

	$orderby = array('generatedTs' => 'DESC');
	// list user's requests by default
	$where   = array('generatedBy' => $_SESSION['sess_userObj']->getId());

	if ($showAll) {
		// only the swithcer can access all requests
		$where = array();
	}

	if (strlen($uuid)>0) {
		if (intval($_SESSION['sess_userObj']->getType()) === AMA_TYPE_SWITCHER) {
			// the swithcer can view an uuid regardless of the generatedBy value
			$where = array();
		}
		// show only request matching the passed uuid
		$where += array ('uuid' => $uuid);
	}

	$requests = $GLOBALS['dh']->findBy('GdprRequest', $where, $orderby);
	if (count($requests)>0) {
		$data['data'] = array_map(
			/** @var GdprRequest $el */
			function(GdprRequest $el) use ($showAll) {
				$retArr = array();
				$retArr['uuid'] = $el->getUuid();
				if ($showAll) {
					$retArr['generatedBy'] = $el->getGeneratedBy();
				}
				$retArr['generatedDate'] = ts2dFN($el->getGeneratedTs()).' '.ts2tmFN($el->getGeneratedTs());
				$retArr['closedDate'] = is_null($el->getClosedTs()) ? null : ts2dFN($el->getClosedTs()).' '.ts2tmFN($el->getClosedTs());
				$retArr['type'] = $el->getType()->toArray();
				$retArr['content'] = $el->getContent();
				$actions = array();

				if ($showAll) {
					if (is_null($el->getClosedTs()) && GdprActions::canDo(GdprActions::FORCE_CLOSE_REQUEST)) {
						$closeBtn = CDOMElement::create('button','type:button,class:ui tiny button');
						$closeBtn->setAttribute('onclick','clickHandlers.closeRequest($j(this),\''.$el->getUuid().'\');');
						$closeBtn->addChild(new CText(translateFN('chiudi')));
						$actions[] = $closeBtn;
					}
				}

				$retArr['actions'] = array_reduce($actions, function($carry, $item) {
					if (strlen($carry) <= 0) $carry = '';
					$carry .= ($item instanceof \CBase ? $item->getHtml() : '');
					return $carry;
				});

				return $retArr;
			}, $requests);
	} else $data['data'] = array();
} catch (\Exception $e) {
// 	header(' ', true, 400);
	$data['data'] = array();
	$data['data']['error'] = $e->getMessage();
}

header('Content-Type: application/json');
die (json_encode($data, JSON_NUMERIC_CHECK));
