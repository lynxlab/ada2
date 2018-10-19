<?php
/**
 * FORMMAIL MODULE.
 *
 * @package        formmail module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           formmail
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('layout');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_STUDENT, AMA_TYPE_SUPERTUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout'),
		AMA_TYPE_SUPERTUTOR => array('layout')
);


/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_FORMMAIL_PATH . '/include/AMAFormmailDataHandler.inc.php';

$retArray = array('status'=>"ERROR", 'title'=>'<i class="basic error icon"></i>'.translateFN('Errore'), 'msg'=>translateFN("Errore sconosciuto"));

if($_SERVER['REQUEST_METHOD'] == 'POST' &&
	isset($_POST['helpTypeID']) && intval(trim($_POST['helpTypeID']))>0 &&
	isset($_POST['helpType'])   && strlen(trim($_POST['helpType']))>0   &&
	isset($_POST['subject'])    && strlen(trim($_POST['subject']))>0    &&
	isset($_POST['recipient'])  && strlen(trim($_POST['recipient']))>0  &&
	isset($_POST['msgbody'])    && strlen(trim($_POST['msgbody']))>0) {

	$GLOBALS['dh'] = AMAFormmailDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	require_once ROOT_DIR.'/include/phpMailer/class.phpmailer.php';

	$selfSend = isset($_POST['selfSend']) && (intval($_POST['selfSend'])===1);

	/**
	 * Initializre the PHPMailer
	 */
	$phpmailer = new \PHPMailer();
	$phpmailer->CharSet = ADA_CHARSET;
	$phpmailer->IsSendmail();
	$phpmailer->SetFrom($userObj->getEmail(), $userObj->getFullName());
	$phpmailer->AddReplyTo($userObj->getEmail(), $userObj->getFullName());
	$phpmailer->IsHTML(false);
	$phpmailer->Subject = '['.trim($_POST['helpType']).'] - '.trim($_POST['subject']);
	$phpmailer->AddAddress(trim($_POST['recipient']));
	$phpmailer->Body = trim($_POST['msgbody']);
	if ($selfSend) {
		$phpmailer->SingleTo = true;
		$phpmailer->AddAddress($userObj->getEmail(), $userObj->getFullName());
	}

	if (isset($_POST['attachments']) && is_array($_POST['attachments']) && count($_POST['attachments'])>0) {
		foreach ($_POST['attachments'] as $name=>$realfile) {
			$toattach = ADA_UPLOAD_PATH.$userObj->getId().'/'.$realfile;
			if (is_file($toattach) && is_readable($toattach)) {
				$phpmailer->addAttachment($toattach, $name);
			} else {
				unset ($_POST['attachments'][$name]);
			}
		}
		$attachmentStr = serialize($_POST['attachments']);
	} else {
		$attachmentStr = null;
	}

	$sentOK = $phpmailer->send();

	if (!$sentOK) {
		$retArray['msg'] = translateFN('La richiesta non è stata spedita').'<br/>'.
						   translateFN('Possibile causa').': '. $phpmailer->ErrorInfo;
	} else {
		$retArray = array('status' => "OK");
	}

	$GLOBALS['dh']->saveFormMailHistory($userObj->getId(), intval(trim($_POST['helpTypeID'])),
			$phpmailer->Subject, $phpmailer->Body, $attachmentStr, ($selfSend ? 1 :0), ($sentOK ? 1 :0));

} else {
	$retArray['msg'] = translateFN('Numero di parametri non corretto');
}

header('Content-Type: application/json');
echo json_encode ($retArray);
?>