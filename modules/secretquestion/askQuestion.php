<?php
/**
 * @package 	secretquestion module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */


ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_SECRETQUESTION_PATH .'/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR);

/**
 * Get needed objects
 */
$neededObjAr = array(AMA_TYPE_VISITOR => array('layout'));

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

$self = whoami();

try {
	$userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_NUMBER_INT);
	if ($userId > 0) {
		$sqdh = AMASecretQuestionDataHandler::instance();
		$question = $sqdh->getUserQuestion($userId);
		if (strlen($question)>0) {
			require_once MODULES_SECRETQUESTION_PATH .'/include/form/SecretQuestionForm.php';
			$form = new SecretQuestionForm(false, true);
			$form->fillWithArrayData(['secretquestion'=>htmlentities($question), 'userId'=>$userId]);
			$data = $form->getHtml();
			$optionsAr['onload_func'] = 'initDoc(\''.$form->getName().'\');';
		} else throw new Exception(translateFN('Impossibile trovare la domanda segreta'));
	} else throw new Exception(translateFN('Utente non valido'));
} catch (\Exception $e) {
	$message = CDOMElement::create('div','class:ui icon error message');
	$message->addChild(CDOMElement::create('i','class:attention icon'));
	$mcont = CDOMElement::create('div','class:content');
	$mheader = CDOMElement::create('div','class:header');
	$mheader->addChild(new CText(translateFN('Errore modulo domanda segreta')));
	$span = CDOMElement::create('span');
	$span->addChild(new CText($e->getMessage()));
	$mcont->addChild($mheader);
	$mcont->addChild($span);
	$message->addChild($mcont);
	$data = $message->getHtml();
	$optionsAr = null;
}

$content_dataAr = array(
	'user_name' => $userObj->getFirstName(),
	'user_homepage' => $userObj->getHomePage(),
	'user_type' => $user_type,
	'messages' => $user_messages->getHtml(),
	'agenda' => $user_agenda->getHtml(),
	'status' => $status,
	'data' => $data,
	'help' => translateFN('Rispondi alla domanda per impostare la nuova password')
);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
