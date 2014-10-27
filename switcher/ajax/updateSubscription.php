<?php
/**
 * updateSubscription.php - update user status in th DB
 *
 * @package
 * @author		sara <sara@lynxlab.com>
 * @copyright           Copyright (c) 2009-2013, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
//require_once 'include/switcher_functions.inc.php';
include_once '../include/Subscription.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $userStatus=$_POST['status'];
    $id_user=$_POST['id_user'];
    $id_instance=$_POST['id_instance'];

    $s = new Subscription($id_user, $id_instance);
    $s->setSubscriptionStatus($userStatus);
    $s->setStartStudentLevel(null); // null means no level update
    $result = Subscription::updateSubscription($s);

    if(AMA_DataHandler::isError($result)) {
        $retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
    }
    else {
        $retArray=array("status"=>"OK","msg"=>  translateFN("Hai aggiornato correttamente lo stato dell'iscrizione"),"text"=>$message,"title"=>  translateFN('Notifica'));
    }

echo json_encode($retArray);
}