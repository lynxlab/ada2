<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
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
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once ROOT_DIR . '/include/HtmlLibrary/BaseHtmlLib.inc.php';
$retVal = translateFN('Nessuna classe trovata');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM && isset($venueID) && intval($venueID)>0) {
		require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
		$classroomAPI = new classroomAPI();
		$result = $classroomAPI->getClassroomsForVenue(intval($venueID));
		
		if(!AMA_DB::isError($result)) {
			$firstEl = reset($result);
			if (!is_array($firstEl)) $result = array($result);
			foreach ($result as $classroom) {
				$radios[$classroom['id_classroom']] = $classroom['name'].
					' ('.$classroom['seats'].' '.translateFN('posti').')';
			}
			reset($radios);
			$htmlElement = BaseHtmlLib::radioButtons('class:classroomradio',$radios,'classroomradio');
			$retVal = $htmlElement->getHtml();
		}
	}
}
die ($retVal);