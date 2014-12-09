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
				$radios[$classroom['id_classroom']] = array('name'=>$classroom['name'],
															'seats'=>$classroom['seats']);
			}
			reset($radios);
			$htmlElement = CDOMElement::create('div');
			foreach ($radios as $id=>$radio) {
				$radioEL = CDOMElement::create('radio','name:classroomradio,class:classroomradio,value:'.$id.',id:classroom'.$id);
				$labelEL = CDOMElement::create('label','for:classroom'.$id);
				$labelEL->addChild(new CText($radio['name']));
				
				if (strlen($radio['seats'])>0) {
					$labelSPAN = CDOMElement::create('span');
					$labelSPAN->addChild(new CText(' ('.$radio['seats'].' '.translateFN('posti').')'));
					$labelEL->addChild($labelSPAN);
				}
				 
				$htmlElement->addChild($radioEL);
				$htmlElement->addChild($labelEL);
				$htmlElement->addChild(CDOMElement::create('div','class:clearfix'));
			}
			
			/**
			 * add hidden div with id='facilities<classroomid>'
			 * to display classroom facilities as a tooltip
			 */
			reset($result);
			foreach ($result as $classroom) {
				// this will return a div CDOMElement or null
				$facilitiesHTML = $classroomAPI->getFacilitesHTML($classroom);
				if (!is_null($facilitiesHTML)) {
					$facilitiesHTML->setAttribute('id', 'facilities'.$classroom['id_classroom']);
					$facilitiesHTML->setAttribute('style','display:none;');
					$htmlElement->addChild($facilitiesHTML);
				}
			}
			$retVal = $htmlElement->getHtml();
		}
	}
}
die ($retVal);