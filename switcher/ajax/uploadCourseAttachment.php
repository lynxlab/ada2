<?php
/**
 * uploads a course attachment file
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
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

$error = true;
$data = '';
if (isset($_FILES) && count($_FILES)>0) {
	$fileUploader = new FileUploader(Course::MEDIA_PATH_DEFAULT.$courseID.'/',  $fieldUploadName);
	// prepare data to be saved
	$extRes = array(
		'nome_file' => $fileUploader->getFileName(),
		'tipo' => array_key_exists($fileUploader->getType(), $GLOBALS['ADA_MIME_TYPE']) ? $GLOBALS['ADA_MIME_TYPE'][$fileUploader->getType()]['type'] : -1,
		'id_nodo' => $courseID,
		'keywords' => isset($filekeywords) ? trim($filekeywords) : null,
		'titolo' => isset($filetitle) ? trim($filetitle) : null,
		'pubblicato' => 1,
		'copyright' => null,
		'lingua' => null,
		'descrizione' => isset($filedescr) ? $filedescr: null,
		'id_utente' => $userID
	);
	// 2nd param forces duplicate filename insertion
	$res = $GLOBALS['dh']->add_risorsa_esterna($extRes, true);
	if (!AMA_DB::isError($res)) {
		if($fileUploader->upload() == false) {
			$GLOBALS['dh']->_del_risorse_nodi($courseID, $extRes);
			$GLOBALS['dh']->remove_risorsa_esterna($extRes);
			$data = $fileUploader->getErrorMessage();
		} else {
			$data = sprintf(translateFN('File %s caricato correttamente'), $fileUploader->getFileName());
			$error = false;
		}
	} else $data = $res->getMessage();

} else $data = translateFN('Array files vuoto');



if ($error) header(' ', true, 500);
header('Content-Type: application/json');
die (json_encode(array('message'=>$data)));
?>