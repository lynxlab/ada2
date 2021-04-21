<?php

/**
 * @package 	notifications module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\Notifications\AMANotificationsDataHandler;
use Lynxlab\ADA\Module\Notifications\NotificationException;
use Lynxlab\ADA\Module\Notifications\NotificationActions;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(NotificationActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMANotificationsDataHandler $ntDH
 */
$ntDH = AMANotificationsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array('status' => 'ERROR');
session_write_close();

// sanitizie data
$passedData = [];
$needed = [
    [
        'key' => 'notificationType',
        'sanitize' => function ($v) {
            return (in_array($v, \Lynxlab\ADA\Module\Notifications\Notification::types) ? $v : 0);
        },
    ],
    [
        'key' => 'isActive',
        'sanitize' => function ($v) {
            return (bool) $v;
        },
    ],
    [
        'key' => 'instanceId',
        'sanitize' => function ($v) {
            return intval($v)>0 ? intval($v) : null;
        },
    ],
    [
        'key' => 'notificationId',
        'sanitize' => function ($v) {
            return intval($v)>0 ? intval($v) : null;
        },
    ],
    [
        'key' => 'nodeId',
        'sanitize' => function ($v) {
            $v = trim($v);
            return \DataValidator::validate_node_id($v) ? $v : null;
        },
    ],
];

foreach ($needed as $n) {
    if (array_key_exists($n['key'], $_REQUEST)) {
        $passedData[$n['key']] = $n['sanitize']($_REQUEST[$n['key']]);
    } else {
        $passedData[$n['key']] = null;
    }
}

$res = new NotificationException(translateFN('Errore sconosciuto'));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
     * it's a POST, save the passed data
     */
    if (array_key_exists('sess_userObj', $_SESSION)) {
        if ($passedData['notificationType']>0) {
            $res = $ntDH->saveNotification($passedData);
        } else {
            $res = new NotificationException(translateFN('Tipo di notifica non valido'));
        }
    } else {
        $res = new NotificationException(translateFN('Nessun utente in sessione'));
    }
}

if (AMA_DB::isError($res) || $res instanceof NotificationException) {
    // if it's an error display the error message
    $retArray['status'] = "ERROR";
    $retArray['msg'] = $res->getMessage();
} else {
    $retArray['status'] = "OK";
    $retArray['msg'] = translateFN("Preferenze di notifica impostate");
    $retArray['data'] = $res;
}

header('Content-Type: application/json');
echo json_encode($retArray);
