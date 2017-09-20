<?php
/**
 * delete a course attachment
 *
 * @package		edit course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2017, Lynx s.r.l.
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
	AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

$error = true;
$data = null;
if (array_key_exists('resourceID', $_POST) && intval($_POST['resourceID'])>0) {
	if (array_key_exists('courseID', $_POST) && intval($_POST['courseID'])>0) {
		$resourceID = intval($_POST['resourceID']);
		$courseID = intval($_POST['courseID']);
		$resInfo = $GLOBALS['dh']->get_risorsa_esterna_info($resourceID);
		$res = $GLOBALS['dh']->_del_risorse_nodi($courseID, $resourceID);
		if (!AMA_DB::isError($res)) {
			$res = $GLOBALS['dh']->remove_risorsa_esterna($resourceID);
			if (!AMA_DB::isError($res) && !AMA_DB::isError($resInfo) && array_key_exists('nome_file', $resInfo)) {
				unlink (Course::MEDIA_PATH_DEFAULT.$courseID.'/'. str_replace(' ', '_', $resInfo['nome_file']));
				// this will remove the courseID dir only if it's empty
				@rmdir(Course::MEDIA_PATH_DEFAULT.$courseID);
				$error = false;
				$data = translateFN('Risorsa cancellata');
			} else $data = $res->getMessage();
		} else $data = $res->getMessage();
	} else {
		$data = translateFN('Passare un id corso valido');
	}
} else {
	$data = translateFN('Passare un id risorsa valido');
}

if ($error) header(' ', true, 500);
header('Content-Type: application/json');
die (json_encode(array('message'=>$data)));
?>