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
	if (isset($GLOBALS['IMPORT_MIME_TYPE'][$fileUploader->getType()]) &&
		$GLOBALS['IMPORT_MIME_TYPE'][$fileUploader->getType()]['permission'] === ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE) {

		$error = false;
		$_SESSION[$sessionVar]['filename'] = $fileUploader->getPathToUploadedFile();
		$isPdf = (stripos($fileUploader->getType(),"pdf")>0);
	} else {
		$error = true;
		$data = translateFN('Caricare solo presentazioni, documenti o PDF').'<br/>'.
				translateFN('Il file caricato si identifica come').': '.$fileUploader->getType();
	}
}

if (!$error) {
	$data = json_encode(array ('isPdf' => $isPdf));
	header('Content-Type: application/json');
} else {
	header(' ', true, 400);
	unlink($fileUploader->getPathToUploadedFile());
	if (strlen($data)<=0) $data = translateFN('Errore sconosciuto');
}

echo $data;
?>