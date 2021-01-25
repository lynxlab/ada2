<?php

/**
 * @package     impersonate module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

use Lynxlab\ADA\Module\Impersonate\AMAImpersonateDataHandler;
use Lynxlab\ADA\Module\Impersonate\ImpersonateActions;
use Lynxlab\ADA\Module\Impersonate\ImpersonateException;
use Lynxlab\ADA\Module\Impersonate\LinkedUsers;

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
list($allowedUsersAr, $neededObjAr) = array_values(ImpersonateActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMACollaboraACLDataHandler $impDH
 */
$impDH = AMAImpersonateDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array('status' => 'ERROR');
session_write_close();

// sanitizie data
$passedData = [];
$needed = [
    [
        'key' => 'linkedType',
        'sanitize' => function ($v) {
            return intval($v);
        },
    ],
    [
        'key' => 'sourceId',
        'sanitize' => function ($v) {
            return intval($v);
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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
     * it's a POST, save the passed data
     */
    if (array_key_exists('sess_userObj', $_SESSION)) {
        $sourceUser = read_user($passedData['sourceId']);
        if ($passedData['linkedType'] > 0) {
            // if the session user has an inactive link, activate it
            $linkedObj = $impDH->findBy('LinkedUsers', [
                'source_id' => $sourceUser->getId(),
                'source_type' => $sourceUser->getType(),
                'linked_type' => $passedData['linkedType'],
                'is_active' => true,
            ]);
            if (is_array($linkedObj) && count($linkedObj) > 0) {
                $linkedObj = reset($linkedObj);
                $linkedObj->setIs_active(false);
                $linkUpdate = true;
            }

            if (isset($linkedObj) && $linkedObj instanceof LinkedUsers) {
                $res = $impDH->saveLinkedUsers($linkedObj->toArray(), $linkUpdate);
            }

        } else {
            $res = new ImpersonateException(translateFN('Passare il tipo di utente da scollegare'));
        }
    } else {
        $res = new ImpersonateException(translateFN('Nessun utente in sessione'));
    }
}

if (AMA_DB::isError($res) || $res instanceof ImpersonateException) {
    // if it's an error display the error message
    $retArray['status'] = "ERROR";
    $retArray['msg'] = $res->getMessage();
} else {
    $retArray['status'] = "OK";
    $retArray['msg'] = translateFN("Utente scollegato");
    $retArray['reload'] = true;
}

header('Content-Type: application/json');
echo json_encode($retArray);
