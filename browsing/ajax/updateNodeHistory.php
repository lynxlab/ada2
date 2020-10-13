<?php
/**
 * closeNodeHistory.php - force the setting of data_uscita to the last history_nodi row of the passed node
 *
 * @package
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018-2020, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_STUDENT => array('layout', 'course')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';
require ROOT_DIR .'/browsing/include/browsing_functions.inc.php';

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

/*
 * YOUR CODE HERE
 */
$retArray = ['status' => 'ERROR', 'title' => whoami(), 'msg'=>translateFN("Errore sconosciuto")];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_SESSION['ada_remote_address'])) {
		$remote_address = $_SESSION['ada_remote_address'];
	} else {
		$remote_address = $_SERVER['REMOTE_ADDR'];
	}

	if (isset($_SESSION['ada_access_from'])) {
		$accessed_from = $_SESSION['ada_access_from'];
	} else {
		$accessed_from = ADA_GENERIC_ACCESS;
	}

	$nodeId = (isset($_POST['nodeId']) && strlen($_POST['nodeId'])>0) ? trim($_POST['nodeId']) : $sess_id_node;
	$instanceId =  (!isset($sess_id_course_instance)  || $courseObj->getIsPublic() ) ? 0 : $sess_id_course_instance;
	$retArray['data'] = [
		'idUser' => $sess_id_user,
		'idInstance' => $instanceId,
		'idNode' => $nodeId
	];

	if (true === $GLOBALS['dh']->add_node_history($sess_id_user, $instanceId, $nodeId, $remote_address, HTTP_ROOT_DIR, $accessed_from, true)) {
		$retArray['status'] = $retArray['msg'] = "OK";
	}
}

header('Content-Type: application/json');
die(json_encode($retArray));
