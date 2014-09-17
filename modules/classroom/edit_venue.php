<?php
/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

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
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_CLASSROOM_PATH .'/config/config.inc.php';
require_once MODULES_CLASSROOM_PATH.'/include/management/venuesManagement.inc.php';

$self = 'venues';

$GLOBALS['dh'] = AMAClassroomDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	/**
	 * it's a POST, save the passed venue data
	 */
	// build a venue with passed POST data
	$venuesManager = new venuesManagement($_POST);
	// try to save it
	$res = $GLOBALS['dh']->classroom_saveVenue($venuesManager->toArray());
	
	if (AMA_DB::isError($res)) {
		// if it's an error display the form with the error message
		$data = $venuesManager->run(MODULES_CLASSROOM_EDIT_VENUE);
		$data['help'] = $res->getMessage();
	} else {
		// redirect to venues page
		redirect(MODULES_CLASSROOM_HTTP.'/venues.php');
	}	
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && 
			isset($_GET['id_venue']) && intval(trim($_GET['id_venue']))>0) {
	/**
	 * it's a GET with an id_venue, load it and display
	 */
	$id_venue = intval(trim($_GET['id_venue']));
	// try to load it
	$res = $GLOBALS['dh']->classroom_getVenue($id_venue);
	
	if (AMA_DB::isError($res)) {
		// if it's an error display the error message without the form
		$data['help'] = $res->getMessage();
	} else {
		// display the form with loaded data
		$venuesManager = new venuesManagement($res);
		$data = $venuesManager->run(MODULES_CLASSROOM_EDIT_VENUE);
	}
} else {
	/**
	 * it's a get without an id_venue, display the empty form
	 */
	$venuesManager = new venuesManagement();
	$data = $venuesManager->run(MODULES_CLASSROOM_EDIT_VENUE);
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'status' => $status,
		'help'  =>  $data['help'],
		'title' =>  $data['title'],
		'data'  =>  (!is_null($data['htmlObj'])) ? $data['htmlObj']->getHtml() : ''
);

ARE::render($layout_dataAr, $content_dataAr);
?>
