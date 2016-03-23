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

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);
/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

echo'
function getDropzonei18n() {
	return {
		dictDefaultMessage: "'.translateFN('Trascina qui un file per l\'upload').'",
		dictFallbackMessage: "'.translateFN('Il tuo browser non supporta l\'upload usando drag\'n\'drop').'",
		dictFallbackText: "'.translateFN('Usa il modulo per fare l\'upload dei files').'",
		dictFileTooBig: "'.translateFN('Il file pesa {{filesize}}MiB ma il massimo consentito è di {{maxFilesize}}MiB').'",
		dictInvalidFileType: "'.translateFN('L\'upload di questo tipo di files non è consentito').'",
		dictResponseError: "'.translateFN('Il server ha risposto con codice {{statusCode}}').'",
		dictCancelUpload: "'.translateFN('Annulla upload').'",
		dictCancelUploadConfirmation: "'.translateFN('Sicuro di voler annullare questo upload?').'",
		dictRemoveFile: "'.translateFN('Elimina file').'",
		dictRemoveFileConfirmation: null,
		dictMaxFilesExceeded: "'.translateFN('Non puoi caricare altri file').'"
	};
}
';
