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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

$GLOBALS['dh'] = AMAClassagendaDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['reminderEventID']) && intval($_GET['reminderEventID'])>0) {

			$result = $GLOBALS['dh']->getReminderForEvent(intval($_GET['reminderEventID']));

			if (!AMA_DB::isError($result) && $result!==false) {

				$reminderContentDIVId = 'reminderContent';

				$reminderDIV = CDOMElement::create('div','class:reminderDetailsContainer');
				$reminderDIV->setAttribute('style', 'display:none;');

				$reminderSPAN = CDOMElement::create('span','class:reminderDetails');
				$reminderSPAN->addChild(new CText(
						translateFN(MODULES_CLASSAGENDA_EMAIL_REMINDER ? 'Promemoria inviato il': 'Promemoria salvato il').
						' '.$result['date'].' '.translateFN('alle').' '.$result['time']
					)
				);

				$reminderButton = CDOMElement::create('button');
				if (MODULES_CLASSAGENDA_EMAIL_REMINDER) {
					$reminderButton->addChild(new CText(translateFN('Vedi Promemoria')));
					$reminderButton->setAttribute('data-email-reminder', 'true');
					$reminderButton->setAttribute('onclick', 'javascript:openReminder(\'#'.$reminderContentDIVId.'\');');
				} else {
					$reminderButton->addChild(new CText(translateFN('Modifica')));
					$reminderButton->setAttribute('data-email-reminder', 'false');
					$reminderButton->setAttribute('onclick', 'javascript:reminderSelectedEvent($j(this))');
				}

				$reminderDIV->addChild($reminderSPAN);
				$reminderDIV->addChild($reminderButton);

				$reminderContent = CDOMElement::create('div','id:'.$reminderContentDIVId);
				$reminderContent->setAttribute('style', 'display:none');
				$reminderContent->addChild(new CText($result['html']));

				$retArray = array("status"=>"OK", "html"=>$reminderDIV->getHtml(), "content"=>$reminderContent->getHtml());
			} else {
				$retArray = array("status"=>"OK");
			}

	} else {
		$retArray = array("status"=>"ERROR", "msg"=>translateFN("Selezionare un evento"));
	} // if isset eventID
} // if method is GET

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);