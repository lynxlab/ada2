<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR   => array('layout'),
		AMA_TYPE_TUTOR    => array('layout'),
		AMA_TYPE_STUDENT  => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);
require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/functions.inc.php';

$data = '';
$error = true;
$retArray = array('title'=>'<i class="basic error icon"></i>'.translateFN('Errore elaborazione file'), 'status'=>'ERROR');

if (isset($_SESSION[$sessionVar]['filename']) && strlen($_SESSION[$sessionVar]['filename'])>0) {
	if (isset($_GET['isPdf']) && intval($_GET['isPdf'])===1) {
		$error = false;
	} else {
		$tmpdir = ADA_UPLOAD_PATH.$userId;
		// do the pdf conversion using libreoffice
		exec('export HOME='.$tmpdir.
			 ' && libreoffice --headless -convert-to pdf --outdir '.
			 $tmpdir.' '.$_SESSION[$sessionVar]['filename']);
		// check if the pdf really exists
		$info = pathinfo($_SESSION[$sessionVar]['filename']);
		$convertedFilename = str_replace('.'.$info['extension'], '.pdf', $_SESSION[$sessionVar]['filename']);
		// remove the uploaded file anyway, we're done with it
		unlink($_SESSION[$sessionVar]['filename']);
		if (is_file($convertedFilename)) {
			$error = false;
			$_SESSION[$sessionVar]['filename'] = $convertedFilename;
		} else {
			$data = translateFN('Errore durante la conversione in PDF');
		}
	}
} else {
	$error = true;
	$data = translateFN('Nessun file da elaborare');
}

if (!$error) {
	$data = getFileData($_SESSION[$sessionVar]['filename']);
	$retArray['data'] = $data;
	$retArray['status'] = 'OK';
	unset($retArray['title']);
} else {
	unlink($_SESSION[$sessionVar]['filename']);
	if (strlen($data)<=0) $data = translateFN('Errore sconosciuto');
	$retArray['msg'] = $data;
}

header('Content-Type: application/json');
echo json_encode($retArray);
?>