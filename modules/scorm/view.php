<?php
/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array ( AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_SUPERTUTOR );

/**
 * Get needed objects
 * This is generated from ADA Eclipse Developer Plugin, use it as an example!
 */
$neededObjAr = array (
		AMA_TYPE_STUDENT =>    array ('layout'),
		AMA_TYPE_TUTOR =>      array ('layout'),
		AMA_TYPE_AUTHOR =>     array ('layout'),
		AMA_TYPE_SWITCHER =>   array ('layout'),
		AMA_TYPE_SUPERTUTOR => array ('layout')
);
/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_SCORM_PATH .'/config/config.inc.php';
require_once MODULES_SCORM_PATH.'/include/AMAScormDataHandler.inc.php';
require_once MODULES_SCORM_PATH.'/include/SCOHelper.class.inc.php';

$self = whoami();

$GLOBALS['dh'] = AMAScormDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

// SCOObject is coming from GET
$isError = false;
$extraContentArr = array();

if (isset($SCOobject) && strlen($SCOobject)>0) {
	if (is_dir(SCO_BASEDIR. DIRECTORY_SEPARATOR . $SCOobject) && is_file(SCO_BASEDIR. DIRECTORY_SEPARATOR . $SCOobject . DIRECTORY_SEPARATOR . SCO_MANIFEST_XML)) {

		$SCOData = SCOHelper::readIMSManifestFile(SCO_BASEDIR. DIRECTORY_SEPARATOR . $SCOobject . DIRECTORY_SEPARATOR . SCO_MANIFEST_XML);

		if (array_key_exists($SCOData['schemaversion'], $GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS'])) {
			$SCOVersion = $GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS'][$SCOData['schemaversion']];
			unset ($SCOData['schemaversion']);

			if (isset($SCOData['organizationTitle']) && strlen($SCOData['organizationTitle'])>0) {
				$extraContentArr['SCOtitle'] = $SCOData['organizationTitle'];
				unset($SCOData['organizationTitle']);
			} else {
				$extraContentArr['SCOtitle'] = translateFN('Seleziona un contenuto da visualizzare');
			}

			if (is_array($SCOData) && count($SCOData)>0) {
				$divList = CDOMElement::create('div','class:ui link selection list,id:SCOlist');
				$baseHREF = MODULES_SCORM_HTTP . '/viewSCO.php?';
				$linkQuery = array ('SCOobject'=>$SCOobject);
				$linkQuery['SCOversion'] = $SCOVersion;
				foreach ($SCOData as $id=>$SCOElement) {
					$linkQuery['SCOid'] = $id;
					$linkQuery['SCOhref'] = (strlen($SCOElement['base'])>0 ? $SCOElement['base'] : '') . $SCOElement['href'];
					if (strlen($SCOElement['parameters'])>0) $linkQuery['SCOparameters'] = $SCOElement['parameters'];
					if (strlen($SCOElement['datafromlms'])>0) $linkQuery['SCOdatafromlms'] = $SCOElement['datafromlms'];
					if (strlen($SCOElement['masteryscore'])>0) $linkQuery['SCOmasteryscore'] = $SCOElement['masteryscore'];

					$divItem = CDOMElement::create('div','class:item');
					$link = BaseHtmlLib::link($baseHREF.http_build_query($linkQuery), $SCOElement['title']);
					$link->setAttribute('class', 'item');
					$link->addChild(CDOMElement::create('i','class:book icon'));
					$divItem->addChild($link);
					$divList->addChild($divItem);
				}
				$extraContentArr['data'] = $divList->getHtml();
			} else {
				$extraContentArr['errorMSG'] = translateFN('Nessuna risorsa da lanciare nello SCO');
				$isError = true;
			}
		} else {

			if (strlen($SCOData['schemaversion'])>0) {
				$errorMSG = translateFN('Questo SCO si identifica come versione').' '.$SCOData['schemaversion'];
			} else {
				$errorMSG = translateFN('Questo SCO non dichiara numero di versione');
			}

			$extraContentArr['errorMSG'] = $errorMSG . ', '.translateFN('le versioni supportate sono').': '.implode(', ', array_keys($GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS']));
			$isError = true;
		}
	} else {
		$extraContentArr['errorMSG'] = sprintf (translateFN('Non trovo il file %s per lo SCO %s'),SCO_MANIFEST_XML, $SCOobject);
		$isError = true;
	}
} else {
	$extraContentArr['errorMSG'] = translateFN('Specificare uno SCO');
	$isError = true;
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => translateFN('scorm')
);

if (count($extraContentArr)>0) $content_dataAr = array_merge($content_dataAr, $extraContentArr);

$optionsAr['onload_func'] = 'initDoc('.intval($isError).');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
