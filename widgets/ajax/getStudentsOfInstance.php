<?php

/**
 * ADA students of instance widget
 *
 * @package		widget
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com>
 *
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *
 *  name="courseId"         mandatory,  value: course instance id from which to load the students list
 *  name="courseInstanceId" mandatory,  value: course instance id from which to load the students list
 *
 *  name="filterStatus"     optional, value: display all subscription statuses, or filter just one
 *  name="styleHeight"      optional, value: container css height property
 *  name="styleOverflow"    optional, value: container css overflow property
 *  name="addHeader"        optional, value: 1 if must add an header element
 *  name="showStatus"       optional, value: 1 if subscrition status must be displayed
 *  name="showEmail"        optional, value: 1 if subscriber email must be displayed
 *  name="emailIsLink"      optional, value: 1 if subscriber email must be a mailto: link
 */

/**
 * Common initializations and include files
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';
require_once ROOT_DIR . '/widgets/include/widget_includes.inc.php';

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_SUPERTUTOR, AMA_TYPE_SWITCHER);
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';
include_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	/**
	 * utility function used in the code below to convert hours and mins
	 * well formatted in an array of 'val' and 'labels' keys
	 *
	 * @param integer $hours
	 * @param integer $mins
	 * @return array
	 */
	function prettyPrintHourMin($hours = 0, $mins = 0)
	{
		$hpref = 'or';
		$mpref = 'minut';
		$timeArr = [];
		if ($hours > 0 || $mins > 0) {
			if ($hours > 0) {
				$timeArr[] = [
					'val' => intval($hours),
					'label' => '<strong>' . translateFN("%d " . $hpref . ($hours > 1 ? 'e' : 'a')) . '</strong>'
				];
				if ($mins > 0) $timeArr[] = ['label' => " e "];
			}
			if ($mins > 0) {
				$timeArr[] = [
					'val' => intval($mins),
					'label' => '<strong>' . translateFN("%d " . $mpref . ($mins > 1 ? 'i' : 'o')) . '</strong>'
				];
			}
		}
		return $timeArr;
	}

	session_write_close();
	extract($_GET);
	if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;

	/**
	 * checks and inits to be done if this has been called in async mode
	 * (i.e. with a get request)
	 */
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (
			$widgetMode != ADA_WIDGET_SYNC_MODE &&
			preg_match("#^" . trim(HTTP_ROOT_DIR, "/") . "($|/.*)#", $_SERVER['HTTP_REFERER']) != 1
		) {
			die('Only local execution allowed.');
		}
	}
}

/**
 * Your code starts here
 */
try {
	if (!isset($courseId)) throw new \Exception(translateFN("Specificare un id di corso"));
	if (!isset($courseInstanceId)) throw new \Exception(translateFN("Specificare un id di istanza corso"));

	/**
	 * get the correct testername
	 */
	if (!MULTIPROVIDER) {
		if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
			$testerName = $GLOBALS['user_provider'];
		} else {
			throw new \Exception(translateFN('Nessun fornitore di servizi &egrave; stato configurato'));
		}
	} else {
		$testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseId);
		if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo['puntatore'])) {
			$testerName = $testerInfo['puntatore'];
		}
	} // end if (!MULTIPROVIDER)

	if (isset($testerName)) {
		$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($testerName));
		// setting of the global is needed to load the course object
		$GLOBALS['dh'] = $tester_dh;
	} else throw new \Exception(translateFN('Spiacente, non so a che fornitore di servizi sei collegato'));

	require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
	$output = '';

	$subscriptions = Subscription::findSubscriptionsToClassRoom($courseInstanceId, true);
	if (is_array($subscriptions) && count($subscriptions) > 0) {
		if (isset($filterStatus)) {
			$subscriptions = array_filter($subscriptions, function ($v) use ($filterStatus) {
				return $filterStatus == $v->getSubscriptionStatus();
			});
		}
		if (count($subscriptions) > 0) {
			usort($subscriptions, function ($a, $b) {
				return strcasecmp($a->getSubscriberFullname(), $b->getSubscriberFullname());
			});
			$outCont = \CDOMElement::create('div', 'class:widget get-students-of-instance');
			$cssString = [];
			if (isset($styleHeight) && strlen($styleHeight) > 0) {
				$cssString[] = 'height:'.$styleHeight;
			}
			if (isset($styleOverflow) && strlen($styleOverflow) > 0) {
				$cssString[] = 'overflow:'.$styleOverflow;
			}
			if (count($cssString) > 0) {
				$outCont->setAttribute('style', implode(' ', $cssString));
			}
			$outDIV = \CDOMElement::create('div', 'class:ui large list');
			foreach ($subscriptions as $s) {
				$sDIV = \CDOMElement::create('div', 'class:item');
				$fns = \CDOMElement::create('div', 'class:header');
				$fns->addChild(new \CText($s->getSubscriberFullname()));
				$sDIV->addChild($fns);
				$extras = [];
				if (isset($showStatus) && $showStatus == 1) {
					if (strlen($s->subscriptionStatusAsString()) > 0) {
						$extras[] = $s->subscriptionStatusAsString();
					}
				}
				if (isset($showEmail) && $showEmail == 1) {
					if (strlen($s->getSubscriberEmail()) > 0) {
						if (isset($emailIsLink) && $emailIsLink == 1) {
							$maillink = \CDOMElement::create('a', 'class:dontcapitalize');
							$maillink->setAttribute('href', 'mailto:' . $s->getSubscriberEmail());
							$maillink->addChild(new \CText($s->getSubscriberEmail()));
							$extras[] = $maillink->getHtml();
						} else {
							$extras[] = $s->getSubscriberEmail();
						}
					}
				}
				if (count($extras) > 0) {
					$sDIV->addChild(new \CText(implode('<br/>', $extras)));
				}
				$outDIV->addChild($sDIV);
			}

			if (isset($addHeader) && $addHeader == 1) {
				$h = \CDOMElement::create('h3', 'class:ui header');
				$h->addChild(new \CText(translateFN('Elenco iscritti al corso')));
				$outCont->addChild($h);
			}
			$outCont->addChild($outDIV);
			$output = $outCont->getHtml();
		}
	}
} catch (\Exception $e) {
	$divClass = 'error';
	$divMessage = basename($_SERVER['PHP_SELF']) . ': ' . $e->getMessage();
	$outDIV = \CDOMElement::create('div', "class:ui $divClass message");
	$closeIcon = \CDOMElement::create('i', 'class:close icon');
	$closeIcon->setAttribute('onclick', 'javascript:$j(this).parents(\'.ui.message\').remove();');
	$outDIV->addChild($closeIcon);
	$errorSpan = \CDOMElement::create('span');
	$errorSpan->addChild(new \CText($divMessage));
	$outDIV->addChild($errorSpan);
	$output = $outDIV->getHtml();
} finally {
	/**
	 * Common output in sync or async mode
	 */
	switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return $output;
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo $output;
	}
}
