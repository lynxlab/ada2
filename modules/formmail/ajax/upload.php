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
require_once ROOT_DIR . '/include/FileUploader.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/include/functions.inc.php';

$fileUploader = new FileUploader(ADA_UPLOAD_PATH.$userId.'/');
$data = '';
$error = true;
$isPdf = false;

if($fileUploader->upload() == false) {
	$data = $fileUploader->getErrorMessage();
} else {
	$error = false;
}

if (!$error) {
	$data = json_encode(array ('attachedfile' => basename($fileUploader->getPathToUploadedFile())));
	header('Content-Type: application/json');
} else {
	header(' ', true, 400);
	unlink($fileUploader->getPathToUploadedFile());
	if (strlen($data)<=0) $data = translateFN('Errore sconosciuto');
}

echo $data;
?>