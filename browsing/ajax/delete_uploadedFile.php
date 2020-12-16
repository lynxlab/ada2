<?php
/**
 * delete_uploadedFile.php - delete a file from browsing/download page
 *
 * @package
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009-2013, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Do not track this in navigation history
 */
$trackPageToNavigationHistory = false;

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout','node','course','course_instance'),
  AMA_TYPE_STUDENT => array('layout','node','course','course_instance')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

$self =  'download';

include_once '../include/browsing_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
BrowsingHelper::init($neededObjAr);

include_once ROOT_DIR.'/include/upload_funcs.inc.php';
include_once ROOT_DIR.'/include/Course.inc.php';

/*
 * YOUR CODE HERE
*/

$languages = Translator::getLanguagesIdAndName();

$retArray = array();
$title = translateFN('Cancellazione File');
// print_r ($fileName); die();

if (!is_null($fileName) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	$courseObj = $_SESSION['sess_courseObj'];
	if ($courseObj instanceof Course) {
		$author_id = $courseObj->id_autore;
		//il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
		if($courseObj->media_path != "") {
			$media_path = $courseObj->media_path;
		}
		else {
			$media_path = MEDIA_PATH_DEFAULT . $author_id ;
		}
		$download_path = $root_dir . $media_path;

		$success = unlink($download_path. DIRECTORY_SEPARATOR .$fileName);

		if ($success) {
			$retArray = array ("status"=>"OK", "title"=>$title, "msg"=>translateFN('File cancellato'));
			if (defined('MODULES_COLLABORAACL') && MODULES_COLLABORAACL) {
				$aclDH = \Lynxlab\ADA\Module\CollaboraACL\AMACollaboraACLDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
				$filesACL = $aclDH->findBy('FileACL', [ 'filepath' => str_replace(ROOT_DIR . DIRECTORY_SEPARATOR, '', $download_path . DIRECTORY_SEPARATOR . $fileName) ]);
				if (is_array($filesACL) && count($filesACL)>0) {
					foreach($filesACL as $fileACL) {
						$aclDH->deleteFileACL($fileACL->getId());
					}
				}
			}
		} else $retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>"Errore nella cancellazione del file");

	} else {
		$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>"Errore nel caricamento del corso");
	}
} else if (is_null($fileName)) {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Il nome del file da cancellare non può essere vuoto"));
} else {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
?>