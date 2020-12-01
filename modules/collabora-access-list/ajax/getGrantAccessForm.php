<?php

/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

use Lynxlab\ADA\Module\CollaboraACL\AMACollaboraACLDataHandler;
use Lynxlab\ADA\Module\CollaboraACL\CollaboraACLActions;
use Lynxlab\ADA\Module\CollaboraACL\CollaboraACLException;
use Lynxlab\ADA\Module\CollaboraACL\FileACL;
use Lynxlab\ADA\Module\CollaboraACL\GrantAccessForm;

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
list($allowedUsersAr, $neededObjAr) = array_values(CollaboraACLActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMACollaboraACLDataHandler $GLOBALS['dh']
 */
$GLOBALS['dh'] = AMACollaboraACLDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array('status' => 'ERROR');
session_write_close();

// sanitizie data
$passedData = [];
$needed = [
    [
        'key' => 'courseId',
        'sanitize' => function ($v) {
            return intval($v);
        },
    ],
    [
        'key' => 'instanceId',
        'sanitize' => function ($v) {
            return intval($v);
        },
    ],
    [
        'key' => 'fileAclId',
        'sanitize' => function ($v) {
            return intval($v);
        },
    ],
    [
        'key' => 'ownerId',
        'sanitize' => function ($v) {
            return intval($v);
        },
    ],
    [
        'key' => 'nodeId',
        'sanitize' => function ($v) {
            return (is_string($v) ? strval(trim($v)) : null);
        },
    ],
    [
        'key' => 'filename',
        'sanitize' => function ($v) {
            return (is_string($v) ? strval(trim($v)) : null);
        },
    ],
    [
        'key' => 'grantedUsers',
        'sanitize' => function($v) {
            if (is_array($v) && count($v)>0) {
                return array_map('intval', $v);
            } else {
                return [];
            }
        }
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
    $res = $GLOBALS['dh']->saveGrantedUsers($passedData);

    if (AMA_DB::isError($res) || $res instanceof CollaboraACLException) {
        // if it's an error display the error message
        $retArray['status'] = "ERROR";
        $retArray['msg'] = $res->getMessage();
    } else {
        $retArray['status'] = "OK";
        $retArray['msg'] = translateFN('Preferenze salvate');
        $retArray['fileAclId'] = $res['fileAclId'];
    }
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    // load data needed by the form
    require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
    $allUsers = Subscription::findSubscriptionsToClassRoom($passedData['instanceId']);
    $acl = $GLOBALS['dh']->findBy('FileACL', ['id' => $passedData['fileAclId']]);
    if (is_array($acl) && count($acl) == 1) {
        $acl = reset($acl);
    } else {
        // make a new, empty access list
        $acl = new FileACL();
    }
    // add granted key to the subscription array
    $allUsers = array_map(
        function ($subscription) use ($acl) {
            $retval = [
                'subscription' => $subscription,
                'granted' => false,
            ];
            foreach ($acl->getAllowedUsers() as $allowed) {
                if ($allowed['utente_id'] == $subscription->getSubscriberId()) {
                    $retval['granted'] = true;
                    break;
                }
            }
            return $retval;
        },
        $allUsers
    );
    // sort by lastname asc
    usort($allUsers, function ($a, $b) {
        return strcasecmp($a['subscription']->getSubscriberLastname(), $b['subscription']->getSubscriberLastname());
    });
    // display the form with loaded data
    $formData = [
        'fileAclId' => $passedData['fileAclId'] > 0 ? $passedData['fileAclId'] : 0,
        'allUsers' => $allUsers,
    ];
    $form = new GrantAccessForm('grantaccess', null, $formData);
    $retArray['status'] = "OK";
    $retArray['html'] = $form->withSubmit()->toSemanticUI()->getHtml();
    $retArray['dialogTitle'] = translateFN('Preferenze condivisione file');
}

header('Content-Type: application/json');
echo json_encode($retArray);
