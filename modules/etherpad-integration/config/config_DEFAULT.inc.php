<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

try {
    if (!@include_once(MODULES_ETHERPAD_PATH . '/vendor/autoload.php')) {
        // @ - to suppress warnings,
        throw new Exception(
            json_encode(array(
                'header' => 'Etherpad Integration module will not work because autoload file cannot be found!',
                'message' => 'Please run <code>composer install</code> in the module subdir'
            ))
        );
    } else {
        // MODULE'S OWN DEFINES HERE
        /**
         * constants for etherpad API host, port and apikey
         * In a non multiprovider environment,
         * each provider must define its own somewhere in its config files
         */
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_HOST'))) {
            define('MODULES_ETHERPAD_HOST', getenv('ETHERPAD_HOST') ?: 'http://localhost');
        }
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_PORT'))) {
            // if no port is needed (such as in https) set an empty string
            define('MODULES_ETHERPAD_PORT', getenv('ETHERPAD_PORT') ?: '9001');
        }
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_APIBASEURL'))) {
            define('MODULES_ETHERPAD_APIBASEURL', getenv('ETHERPAD_APIBASEURL') ?: 'api');
        }
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_APIKEY'))) {
            define('MODULES_ETHERPAD_APIKEY', getenv('ETHERPAD_APIKEY') ?: '');
        }
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_INSTANCEPAD'))) {
            define('MODULES_ETHERPAD_INSTANCEPAD', getenv('ETHERPAD_INSTANCEPAD') ?: true);
        }
        if (MULTIPROVIDER || (!MULTIPROVIDER && !defined('MODULES_ETHERPAD_NODEPAD'))) {
            define('MODULES_ETHERPAD_NODEPAD', getenv('ETHERPAD_NODEPAD') ?: false);
        }
        return true;
    }
} catch (Exception $e) {
    $text = json_decode($e->getMessage(), true);
    // populating $_GET['message'] is a dirty hack to force the error message to appear in the home page at least
    if (!isset($_GET['message'])) $_GET['message'] = '';
    $_GET['message'] .= '<div class="ui icon error message"><i class="ban circle icon"></i><div class="content">';
    if (array_key_exists('header', $text) && strlen($text['header']) > 0) {
        $_GET['message'] .= '<div class="header">' . $text['header'] . '</div>';
    }
    if (array_key_exists('message', $text) && strlen($text['message']) > 0) {
        $_GET['message'] .= '<p>' . $text['message'] . '</p>';
    }
    $_GET['message'] .= '</div></div>';
}
return false;
