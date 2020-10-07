<?php

/**
 * ADA course status widget
 *
 * @package		widget
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com>
 *
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *
 *  name="id_course"          mandatory,  value: course instance id from which to load the status
 *  name="id_course_instance" mandatory,  value: course instance id from which to load the status
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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';
include_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	session_write_close();
	extract($_GET);

	if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;

	/**
	 * checks and inits to be done if this has been called in async mode
	 * (i.e. with a get request)
	 */
	if (isset($_SERVER['HTTP_REFERER'])) {
		if ($widgetMode != ADA_WIDGET_SYNC_MODE &&
			preg_match("#^" . trim(HTTP_ROOT_DIR,"/") . "($|/.*)#", $_SERVER['HTTP_REFERER']) != 1) {
			die('Only local execution allowed.');
		}
	}

}

/**
 * Your code starts here
 */
try {

	$isError = false;

	if (!isset($id_course)) throw new \Exception(translateFN("Specificare un id di corso"));
	if (!isset($id_course_instance)) throw new \Exception(translateFN("Specificare un id di istanza corso"));
	if (!defined('MODULES_CLASSAGENDA') || (defined('MODULES_CLASSAGENDA') && true != MODULES_CLASSAGENDA)) {
		throw new \Exception(translateFN("Modulo CLASSAGENDA non trovato o non configurato"));
	}

	require_once MODULES_CLASSAGENDA_PATH.'/include/management/calendarsManagement.inc.php';

	/**
	 * get the correct testername
	 */
	if (!MULTIPROVIDER) {
		if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
			$testerName = $GLOBALS['user_provider'];
		} else {
			throw new \Exception (translateFN('Nessun fornitore di servizi &egrave; stato configurato'));
		}
	} else {
		$testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($id_course);
		if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo['puntatore'])) {
			$testerName = $testerInfo['puntatore'];
		}
	} // end if (!MULTIPROVIDER)

	if (isset($testerName)) {
		$tester_dh = AMAClassagendaDataHandler::instance(MultiPort::getDSN($testerName));
	// setting of the global is needed to load the course object
		$GLOBALS['dh'] = $tester_dh;
	} else throw new \Exception(translateFN('Spiacente, non so a che fornitore di servizi sei collegato'));

	if (array_key_exists('start', $_REQUEST) && intval($_REQUEST['start'])>0) {
		list ($date,$time) = explode('T',$_REQUEST['start']);
		list ($year, $month, $day) = explode('-', $date);
		$start = $tester_dh->date_to_ts($day.'/'.$month.'/'.$year, $time);
		$headerTxt = translateFN('Eventi del').' '.ts2dFN($start);
	} else {
		$start = $tester_dh->date_to_ts($tester_dh::ts_to_date($tester_dh->date_to_ts('now')));
		$headerTxt = translateFN('Eventi di oggi');
	}

	if (array_key_exists('end', $_REQUEST) && intval($_REQUEST['end'])>0) {
		list ($date,$time) = explode('T',$_REQUEST['end']);
		list ($year, $month, $day) = explode('-', $date);
		$end = $tester_dh->date_to_ts($day.'/'.$month.'/'.$year, $time);
	} else {
		$end = $tester_dh->date_to_ts($tester_dh::ts_to_date($tester_dh->add_number_of_days(1, $tester_dh->date_to_ts('now'))));
	}

	$events = $tester_dh->getClassRoomEventsForCourseInstance($id_course_instance, null, $start, $end);
	if (!AMA_DB::isError($events) && is_array($events) && count($events)>0) {
		uasort($events, function($a, $b) {
			return $a['start'] - $b['start'];
		});
		$outDIV = \CDOMElement::create('div','id:instanceReminders,class:ui feed basic segment');
		$header = \CDOMElement::create('h4','class:ui header');
		$header->addChild(new \CText($headerTxt));
		$outDIV->addChild($header);
		foreach($events as $eventID => $event) {
			$evDIV = \CDOMElement::create('div','class:event');
			$evDIV->setAttribute('data-event-id', $eventID);
			$lbl = \CDOMElement::create('div','class:label');
			$lbl->addChild(\CDOMElement::create('i','class:calendar icon'));
			$content = \CDOMElement::create('div','class:content');
			$date = \CDOMElement::create('div','class:date');
			$date->addChild(new \CText(translateFN('Dalle').' '.
				substr(ts2tmFN($event['start']), 0, -3).' '.translateFN('alle').' '.
				substr(ts2tmFN($event['end']), 0, -3)));
			$content->addChild($date);
			$summary = \CDOMElement::create('div','class:summary');

			$htmlContent = $tester_dh->getLastEventReminderHTML($eventID);

			if ($htmlContent === false) {
				$htmlContent = translateFN('Nessun promemoria');
			} else {
				$reminder = $tester_dh->getReminderForEvent($eventID);
				$reminderData = $tester_dh->getReminderDataToEmail(intval($reminder['id']));
				/**
				 * perform general substitutions
				 */
				$searchArray = array();
				$replaceArray = array();
				// fields that must be replaced per user!
				$userDataFields = array ('name','lastname','e-mail');
				foreach (calendarsManagement::reminderPlaceholders() as $placeHolder=>$label) {
					if (!in_array($placeHolder, $userDataFields)) {
						$searchArray[] = '{'.$placeHolder.'}';
						if (isset($reminderData[$placeHolder]) && strlen($reminderData[$placeHolder])>0) {
							$replaceArray[] = $reminderData[$placeHolder];
						} else $replaceArray[] = '';
					}
				}
				$HTMLModelText = str_replace($searchArray,$replaceArray, $htmlContent);
				// perform general substitutions for relative path images
				$HTMLModelText = preg_replace('/(src=[\'"])\/?[^>]*(\/?services\/media\/)/', '$1'.HTTP_ROOT_DIR.'/$2', $HTMLModelText);

				// performs user substitutions
				$htmlContent = str_replace(
					array ('{name}','{lastname}','{e-mail}'),
					array ($_SESSION['sess_userObj']->getFirstName(), $_SESSION['sess_userObj']->getLastName(), $_SESSION['sess_userObj']->getEmail()), $HTMLModelText);
			}

			$summary->addChild(new \CText($htmlContent));
			$content->addChild($summary);
			$evDIV->addChild($lbl);
			$evDIV->addChild($content);
			$outDIV->addChild($evDIV);
		}
		$output = $outDIV->getHtml();
	} else {
		$output = '';
	}
} catch (\Exception $e) {
	$isError = true;
	$divClass = 'error';
	$divMessage = basename($_SERVER['PHP_SELF']) . ': ' . $e->getMessage();
} finally {
	if ($isError) {
		if (!isset($divMessage)) {
		   $divMessage = translateFN('Errore sconosciuto');
		   $divClass = 'error';
	   }
	   $outDIV = \CDOMElement::create('div',"class:ui $divClass message");
	   $closeIcon = \CDOMElement::create('i','class:close icon');
	   $closeIcon->setAttribute('onclick','javascript:$j(this).parents(\'.ui.message\').remove();');
	   $outDIV->addChild($closeIcon);
	   $errorSpan = \CDOMElement::create('span');
	   $errorSpan->addChild(new \CText($divMessage));
	   $outDIV->addChild($errorSpan);
	   $output = $outDIV->getHtml();
	}

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
