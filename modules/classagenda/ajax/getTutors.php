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
$retVal = translateFN('Nessun tutor trovato');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($instanceID) && intval($instanceID)>0) {
		$result = $GLOBALS['dh']->course_instance_tutor_get(intval($instanceID),'ALL');
		
		if(!AMA_DB::isError($result) && is_array($result) && count($result)>0) {
			/**
			 * get tutors first and last name
			 */
			$tutorlist = $GLOBALS['dh']->find_tutors_list(array('nome','cognome'),'id_utente_tutor IN('.implode(',',$result).')');
			if (!AMA_DB::isError($tutorlist)) {
				$htmlElement = CDOMElement::create('div');
				foreach ($tutorlist as $aTutor) {
					$radioEL = CDOMElement::create('radio','name:tutorradio,class:tutorradio,value:'.$aTutor[0].',id:tutor'.$aTutor[0]);
					$labelEL = CDOMElement::create('label','class:tutorradiotext,for:tutor'.$aTutor[0]);
					$labelEL->addChild(new CText($aTutor[1].' '.$aTutor[2]));
					
					$htmlElement->addChild($radioEL);
					$htmlElement->addChild($labelEL);
					$htmlElement->addChild(CDOMElement::create('div','class:clearfix'));
				}
				$retVal = $htmlElement->getHtml();
			}
		}
	}
}
die ($retVal);