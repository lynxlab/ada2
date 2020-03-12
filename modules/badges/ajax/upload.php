<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\Badges\BadgesActions;

/**
 * Base config file
*/

require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
// require_once MODULES_BADGES_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(BadgesActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);
require_once ROOT_DIR . '/include/FileUploader.inc.php';

$fileUploader = new FileUploader(ADA_UPLOAD_PATH.DIRECTORY_SEPARATOR.MODULES_BADGES_NAME.DIRECTORY_SEPARATOR, key($_FILES));
$data = '';
$error = true;

if($fileUploader->upload() == false) {
	$data = $fileUploader->getErrorMessage();
} else {
	$data = json_encode(array('fileName'=>$fileUploader->getFileName()));
	$error = false;
}

if ($error !== false) {
	header(' ', true, 400);
	unlink($fileUploader->getPathToUploadedFile());
	if (strlen($data)<=0) $data = translateFN('Errore sconosciuto');
}

header('Content-Type: application/json');
echo $data;
