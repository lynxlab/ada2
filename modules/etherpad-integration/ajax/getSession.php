<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

use Lynxlab\ADA\Module\EtherpadIntegration\AMAEtherpadDataHandler;
use Lynxlab\ADA\Module\EtherpadIntegration\EtherpadActions;
use Lynxlab\ADA\Module\EtherpadIntegration\EtherpadClient;
use Lynxlab\ADA\Module\EtherpadIntegration\EtherpadException;
use Lynxlab\ADA\Module\EtherpadIntegration\Session;
use Lynxlab\ADA\Module\EtherpadIntegration\Utils;

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
list($allowedUsersAr, $neededObjAr) = array_values(EtherpadActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMAEtherpadDataHandler $etDH
 */
$etDH = AMAEtherpadDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array('status' => 'ERROR');
session_write_close();

// sanitizie data
$passedData = [];
$needed = [
    [
        'key' => 'groupId',
        'sanitize' => function ($v) {
            return strlen(trim($v))>0 ? trim($v) : null;
        },
    ],
    [
        'key' => 'authorId',
        'sanitize' => function ($v) {
            return strlen(trim($v))>0 ? trim($v) : null;
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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        if (!EtherpadActions::canDo(EtherpadActions::CREATE_SESSION)) {
            throw new EtherpadException(translateFN('Utente non autorizzato'));
        }

        if (is_null($passedData['groupId'])) {
            throw new EtherpadException(translateFN('Passare un id gruppo'));
        } else {
            // check that passed group exists
            $groupObj = $etDH->findOneBy('Groups',[
                'groupId' => $passedData['groupId'],
                'isActive' => true,
            ]);
            if (is_null($groupObj)) {
                throw new EtherpadException(sprintf(translateFN('Il gruppo %s non esiste'), $passedData['groupId']));
            }
        }
        if (is_null($passedData['authorId'])) {
            throw new EtherpadException(translateFN('Passare un id autore'));
        } else {
            // check that passed group exists
            $authorObj = $etDH->findOneBy('Authors',[
                'authorId' => $passedData['authorId'],
                'isActive' => true,
            ]);
            if (is_null($authorObj)) {
                throw new EtherpadException(sprintf(translateFN("L'autore %s non esiste"), $passedData['authorId']));
            }
        }

        $whereArr = [
            'groupId' => $passedData['groupId'],
            'authorId' => $passedData['authorId'],
            'validUntil' => [
                'op' => '>',
                'value' => time(),
            ],
        ];

        $res = $etDH->findOneBy('Session',$whereArr);
        $sessionId = null;
        if ($res instanceof Session) {
            $sessionId = $res->getSessionId();
        } else {
            if (EtherpadActions::canDo(EtherpadActions::CREATE_SESSION)) {
                // create an etherpad group and save its id locally
                $ethClient = new EtherpadClient(MODULES_ETHERPAD_APIKEY, Utils::getEtherpadURL());
                $rawSession = $ethClient->createSession($groupObj->getGroupId(), $authorObj->getAuthorId(), time() + Session::sessionDuration);
                if (property_exists($rawSession, 'sessionID') && strlen($rawSession->sessionID)>0) {
                    if ($etDH->saveSession([
                        'groupId' => $groupObj->getGroupId(),
                        'authorId' => $authorObj->getAuthorId(),
                        'sessionId' => $rawSession->sessionID,
                        'validUntil' => time() + Session::sessionDuration,
                    ])) {
                        $sessionId = $rawSession->sessionID;
                    } else {
                        // delete the remote session if something went wrong while saving locally
                        $ethClient->deleteSession($rawSession->sessionID);
                        throw new EtherpadException(translateFN('Errore nel salvataggio dei dati, sessione non creata'));
                    }
                } else {
                    throw new EtherpadException(translateFN('Impossibile creare la sessione'));
                }
            } else {
                throw new EtherpadException(translateFN('Utente non abilitato a creare sessioni'));
            }
        }
    } catch (\Exception $e) {
        $res = $e;
    }

    if (AMA_DB::isError($res) || $res instanceof \Exception) {
        // if it's an error display the error message
        $retArray['status'] = "ERROR";
        $retArray['msg'] = $res->getMessage();
        $retArray['sessionId'] = null;
    } else {
        $retArray['status'] = "OK";
        $retArray['msg'] = null;
        $retArray['sessionId'] = $sessionId;
    }

    header('Content-Type: application/json');
    echo json_encode($retArray);
}
die();
