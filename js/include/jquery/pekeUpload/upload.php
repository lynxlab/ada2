<?php
/**
 * EXPORT TEST.
 *
 * @package		export/import course
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			impexport
 * @version		0.1
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_STUDENT => array('layout'),
		AMA_TYPE_SWITCHER => array('layout'),
                AMA_TYPE_AUTHOR =>array('layout'),
                AMA_TYPE_TUTOR =>array('layout'),
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

$fileUploader = new FileUploader(ADA_UPLOAD_PATH.$userId.'/'); // $fieldUploadName);
if($fileUploader->upload(true) == false) {
	$data = $fileUploader->getErrorMessage();
} else {
	 $_SESSION['uploadHelper']['filename'] = $fileUploader->getPathToUploadedFile();
	 $_SESSION['uploadHelper']['fileNameWithoutPath'] = $fileUploader->getFileName();
	 $data = '1'; // '1' means okay
}

echo $data;
?>