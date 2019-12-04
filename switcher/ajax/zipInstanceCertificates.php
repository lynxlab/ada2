<?php
/**
 * build certificates for all students in the passed instance and downloads as a zip file
 *
 * @package
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * Base config file
*/
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
	AMA_TYPE_SWITCHER =>array('layout', 'user','course','course_instance')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once '../include/switcher_functions.inc.php';
require_once '../include/Subscription.inc.php';
SwitcherHelper::init($neededObjAr);

$doDownload = false;
$data = null;
$checkOnly = (array_key_exists('check', $_REQUEST) && intval($_REQUEST['check'])>=0) ? (bool)intval($_REQUEST['check']) : true;

if (isset($_REQUEST['c']) && isset($_REQUEST['t']) && strlen($_REQUEST['c'])>0 && strlen($_REQUEST['t'])>0) {
	// a cookie name and token has been passed, send them back to the server in a cookie
	setcookie($_REQUEST['c'],$_REQUEST['t'],time() + 600, "/"); // expires in 10 minutes
}

if (array_key_exists('id_instance', $_REQUEST) && intval($_REQUEST['id_instance'])>0) {
	$courseInstanceObj = new Course_instance(intval($_REQUEST['id_instance']));

	if ($courseInstanceObj instanceof Course_instance && $courseInstanceObj->full==1) {
		if(!$courseInstanceObj->isTutorCommunity() && defined('ADA_PRINT_CERTIFICATE') && (ADA_PRINT_CERTIFICATE)) {
			$subscriptions = Subscription::findSubscriptionsToClassRoom($courseInstanceObj->getId(), true);
			if (is_array($subscriptions) && count($subscriptions)>0) {
				if (!$checkOnly) {
					$_GET['forcereturn'] = true;
					// These are needed by the Rendering Engine called by the userCertificate inclusion
					$self = 'userCertificate';
					$GLOBALS['self'] = $self;
					$layout_dataAr['module_dir'] = 'browsing/';
					// Prepare ZipArchive
					$file = ADA_UPLOAD_PATH . translateFN('Certificati-classe-').$courseInstanceObj->getId().'.zip';
					$zip = new \ZipArchive();
					$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
					foreach($subscriptions as $subscription) {
						if (ADAUser::Check_Requirements_Certificate($subscription->getSubscriberId(), $subscription->getSubscriptionStatus())) {
							// must set the id_user to be used by userCertificate
							$_GET['id_user'] = $subscription->getSubscriberId();
							$pdfArr = include ROOT_DIR.'/browsing/userCertificate.php';
							if (array_key_exists('content', $pdfArr) && strlen($pdfArr['content'])>0) {
								$zipname = (array_key_exists('filename', $pdfArr) && strlen($pdfArr['filename'])>0) ? $pdfArr['filename'] : translateFN('studente').'-'. $subscription->getSubscriberId() .'.pdf';
								$zip->addFromString($zipname, $pdfArr['content']);
								$doDownload = true;
							}
						}
					}
					$zip->close();
					if ($doDownload) {
						//Set headers
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: public");
						header("Content-Description: File Transfer");
						header("Content-Transfer-Encoding: binary");
						header('Content-Type: application/zip');
						header('Content-Length: ' . filesize($file));
						header('Content-Disposition: attachment; filename="'.basename($file).'"');
						readfile($file);
					} else $data = translateFN('Nessun certificato da scaricare');
					@unlink($file);
				} else $data = 'OK';

			} else $data = translateFN('Nessuno studente iscritto alla classe');
		} else $data = translateFN('Comunità di tutor o certificati disabilitati');
	} else $data = translateFN("Impossibile caricare l'istanza");
} else $data = translateFN('Passare un id istanza valido');

if ($checkOnly && !is_null($data)) {
	header('Content-Type: application/json');
	die (json_encode(array('data'=>$data)));
}
die();
