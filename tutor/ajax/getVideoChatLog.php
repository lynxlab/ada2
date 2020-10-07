<?php
/**
 * gets videoroom log data
 *
 * @package		tutor
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
	AMA_TYPE_TUTOR => array('layout', 'course', 'course_instance'),
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR. '/include/module_init.inc.php';
require_once ROOT_DIR. '/tutor/include/tutor_functions.inc.php';
require_once ROOT_DIR. '/comunica/include/videoroom.classes.inc.php';

TutorHelper::init($neededObjAr);

$error = true;
$data = null;

$id_user = (array_key_exists('id_user', $_GET) && intval($_GET['id_user'])>0) ? intval($_GET['id_user']) : null;
$id_room = (array_key_exists('id_room', $_GET) && intval($_GET['id_room'])>0) ? intval($_GET['id_room']) : null;

$data = videoroom::getInstanceLog($courseInstanceObj->getId(), $id_room, $id_user);
$data = array_map(function($el) {
    $el['details']['rowId'] = 'row_' . $el['details']['id_room'];
    $el['details']['tipo_videochat_descr'] = videoroom::initialToDescr($el['details']['tipo_videochat']);
    return $el;
}, $data);

$error = !(is_array($data) && count($data)>0);

if ($error !== false) {
    $data = ['data' => []];
}

header('Content-Type: application/json');
die (json_encode([ 'data'=>$data ]));
