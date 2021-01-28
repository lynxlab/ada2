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
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

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

$impersonateId = -1;

if (isset($_SESSION[MODULES_IMPERSONATE_SESSBACKDATA])) {
    $impersonateObj = $_SESSION[MODULES_IMPERSONATE_SESSBACKDATA];
    unset($_SESSION[MODULES_IMPERSONATE_SESSBACKDATA]);
} else {
    /**
     * @var AMAImpersonateDataHandler $impDH
     */
    $impDH = AMAImpersonateDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
    try {
        $impObj = LinkedUsers::getSessionLinkedUser();
        if (count($impObj) > 0) {
            if (isset($_GET['t']) && intval($_GET['t']) > 0) {
                $t = intval($_GET['t']);
                $impObj = array_filter($impObj, function ($el) use ($t) {
                    return $el->getLinked_type() == $t;
                });
            }
            $impObj = reset($impObj);
            $impersonateId = $impObj->getLinked_id();
            $_SESSION[MODULES_IMPERSONATE_SESSBACKDATA] = $_SESSION['sess_userObj'];
        } else {
            throw new ImpersonateException('Error loading LinkedUsers object');
        }
    } catch (ImpersonateException $e) {
        $impersonateId = -1;
        if (isset($_SESSION[MODULES_IMPERSONATE_SESSBACKDATA])) {
            unset($_SESSION[MODULES_IMPERSONATE_SESSBACKDATA]);
        }
    }
}

if (!isset($impersonateObj)) {
    $impersonateObj = $impersonateId > 0 ? read_user($impersonateId) : $userObj;
}

if ($impersonateObj instanceof \ADALoggableUser) {
    if (isset($_SESSION[MODULES_IMPERSONATE_SESSBACKDATA])) {
        $impersonateObj->setStatus(ADA_STATUS_REGISTERED);
    }
    \ADAUser::setSessionAndRedirect(
        $impersonateObj,
        false,
        $impersonateObj->getLanguage(),
        null,
        $impersonateObj->getHomePage()
    );
}
