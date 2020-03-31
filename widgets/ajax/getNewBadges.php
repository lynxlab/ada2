<?php
use Lynxlab\ADA\Module\Badges\RewardedBadge;
use Lynxlab\ADA\Module\Badges\Badge;

/**
 * ADA notify badges widget
 *
 * @package		widget
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com>
 *
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *
 *  name="courseId"         optional,  value: course id from which to load the badges
 *  name="courseInstanceId" optional,  value: course instance id from which to load the badges
 *	name="userId"		    mandatory, value: user id from which to load the badges
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
$allowedUsersAr = array(AMA_TYPE_STUDENT);
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
	function prettyPrintHourMin ($hours = 0, $mins = 0) {
		$hpref = 'or';
		$mpref = 'minut';
		$timeArr = [];
		if ($hours>0 || $mins>0) {
			if ($hours>0) {
				$timeArr[] = [
					'val' => intval($hours),
					'label' => '<strong>'.translateFN("%d ".$hpref.($hours > 1 ? 'e' : 'a')).'</strong>'
				];
				if ($mins>0) $timeArr[] = ['label' => " e "];
			}
			if ($mins>0) {
				$timeArr[] = [
					'val' => intval($mins),
					'label' => '<strong>'.translateFN("%d ".$mpref.($mins>1 ? 'i' : 'o')).'</strong>'
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
	if (!isset($userId)) throw new \Exception(translateFN("Specificare un id studente"));
	/**
	 * get the correct testername
	 */
	if (!MULTIPROVIDER) {
		if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
			$testerName = $GLOBALS['user_provider'];
		} else {
			throw new \Exception (translateFN('Nessun fornitore di servizi &egrave; stato configurato'));
		}
	} else if (isset($courseId)) {
		$testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseId);
		if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo['puntatore'])) {
			$testerName = $testerInfo['puntatore'];
		}
	} // end if (!MULTIPROVIDER)

	if (!isset($testerName)) throw new \Exception(translateFN('Spiacente, non so a che fornitore di servizi sei collegato'));

	if ($userObj->getType() == AMA_TYPE_STUDENT && defined('MODULES_BADGES') && MODULES_BADGES) {
		require_once MODULES_BADGES_PATH . '/config/config.inc.php';
		$bdh = \Lynxlab\ADA\Module\Badges\AMABadgesDataHandler::instance(\MultiPort::getDSN($testerName));
		$findByArr['id_utente'] = $userObj->getId();
		$findByArr['notified'] = 0;
		$findByArr['approved'] = 1;
		if (isset($courseId)) $findByArr['id_corso'] = $courseId;
		if (isset($courseInstanceId)) $findByArr['id_istanza_corso'] = $courseInstanceId;

		$rewardsList = $bdh->findBy('RewardedBadge', $findByArr);
		$outputArr = [];
		if (!\AMA_DB::isError($rewardsList) && is_array($rewardsList) && count($rewardsList)>0) {
			/** @var RewardedBadge  $reward */
			foreach($rewardsList as $reward) {
				$badge = $bdh->findBy('Badge', [ 'uuid' => $reward->getBadge_uuid() ] );
				if (!AMA_DB::isError($badge) && is_array($badge) && count($badge)===1) {
					/** @var Badge $badge */
					$badge = reset($badge);
					$div = CDOMElement::create('div','class:ui blue icon floating message,id:'.$reward->getUuid());
					$div->setAttribute('data-badge', $badge->getUuid());
					$closeIcon = \CDOMElement::create('i','class:close icon');
					$closeIcon->setAttribute('onclick','javascript:$j(this).parents(\'.ui.message\').fadeOut(function(){ $j(this).remove(); });');
					$div->addChild($closeIcon);

					$div->addChild(CDOMElement::create('img','class:ui small left floated image,style:margin-bottom:0,src:'.$badge->getImageUrl()));

					$headerMSG = translateFN('Congratulazioni!').' '.translateFN('Hai ottenuto il badge').': '.$badge->getName();
					$header = CDOMElement::create('div','class:header');
					$header->addChild(new \CText($headerMSG));
					$div->addChild($header);

					if (strlen($badge->getDescription())>0) {
						$div->addChild(new CText('<p style="margin-top_0.8em; font-weight:700;">'.nl2br($badge->getDescription()).'</p>'));
					}

					array_push($outputArr, $div);
				}
				// set the notified flag to false for the reward
				$reward->setNotified(true);
				$bdh->saveRewardedBadge($reward->toArray());
			}
		}
		$output = implode(PHP_EOL, array_map (function($el) { return $el->getHtml(); }, $outputArr ));
	}

} catch (\Exception $e) {
	$divClass = 'error';
	$divMessage = basename($_SERVER['PHP_SELF']) . ': ' . $e->getMessage();
	$outDIV = \CDOMElement::create('div',"class:ui $divClass message");
	$closeIcon = \CDOMElement::create('i','class:close icon');
	$closeIcon->setAttribute('onclick','javascript:$j(this).parents(\'.ui.message\').remove();');
	$outDIV->addChild($closeIcon);
	$errorSpan = \CDOMElement::create('span');
	$errorSpan->addChild(new \CText($divMessage));
	$outDIV->addChild($errorSpan);
	$output = $outDIV->getHtml();
}

if (!isset($output)) $output='';
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
