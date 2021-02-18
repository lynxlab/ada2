<?php

/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2021, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version        0.1
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);
/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_SERVICECOMPLETE_PATH . '/include/init.inc.php';

$GLOBALS['dh'] = AMACompleteDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $courseId = isset($_GET['courseId']) ? (int) $_GET['courseId'] : 0;
    $courseInstanceId = isset($_GET['instanceId']) ? (int) $_GET['instanceId'] : 0;
    $studentId = isset($_GET['studentId']) ? (int) $_GET['studentId'] : 0;

    $modal = \CDOMElement::create('div', 'class:ui small modal');

    $header = \CDOMElement::create('div', 'class:header');
    $header->addChild(new \CText(translateFN('Resoconto condizioni di completamento')));

    $contentDIV = \CDOMElement::create('div', 'class:content');
    $content = new \CText(translateFN('Erroe nella generazione del resoconto'));

    $actions = \CDOMElement::create('div', 'class:actions');
    $okbtn = \CDOMElement::create('div', 'class:ui green button');
    $okbtn->addChild(new \CText(translateFN('OK')));
    $actions->addChild($okbtn);

    $modal->addChild($header);
    $modal->addChild($contentDIV);
    $modal->addChild($actions);

    if ($courseId>0 && $instanceId>0 && $studentId>0) {
		// load the conditionset for this course
		$conditionSet = $GLOBALS['dh']->get_linked_conditionset_for_course($courseId);
		if ($conditionSet instanceof CompleteConditionSet) {
            $condString = $conditionSet->toString();
            if (strlen($condString)>0) {

                $codeCont = \CDOMElement::create('div', 'id:conditionCodeCont');
                $codeBtn = \CDOMElement::create('button','class:ui icon blue small right floated button');
                $codeBtn->setAttribute('onclick','javascript:$j(\'#conditionCode\').slideToggle();');
                $codeBtn->addChild(\CDOMElement::create('i','class:code icon'));
                $codeCont->addChild($codeBtn);
                $codeCont->addChild(\CDOMElement::create('div','class:clearfix'));

                $codeDIV = \CDOMElement::create('div','id:conditionCode');
                $h3 = \CDOMElement::create('h3');
                $h3->addChild(new \CText(translateFN('Codice della condizione')));
                $pre = \CDOMElement::create('span','class:pre');
                $pre->addChild(new \CText(str_replace('::buildAndCheck','', $condString)));
                $codeDIV->addChild($h3);
                $codeDIV->addChild($pre);
                $codeCont->addChild($codeDIV);
                $contentDIV->addChild($codeCont);
            }
			// evaluate the conditionset for this instance ID and course ID
			$summary = $conditionSet->buildSummary(array($courseInstanceId, $studentId));
			if (is_array($summary) && count($summary)>0) {
				$content = \CDOMElement::create('div','class:ui large list');
				foreach($summary as $condition=>$condData) {
					$content->addChild($condition::getCDOMSummary($condData));
				}
			}
		}

    }

    $contentDIV->addChild($content);
    die($modal->getHtml());
}
die();
