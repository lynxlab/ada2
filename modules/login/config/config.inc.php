<?php

/**
 * LOGIN MODULE
 *
 * @package     login module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015-2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

try {
    if (!@include_once(MODULES_LOGIN_PATH . '/vendor/autoload.php')) {
        // @ - to suppress warnings,
        throw new Exception(
            json_encode(array(
                'header' => 'Login module will not work because autoload file cannot be found!',
                'message' => 'Please run <code>composer install</code> in the module subdir'
            ))
        );
    } else {
        // MODULE'S OWN DEFINES HERE
        /**
         * To prevent `module_login_history_login` table to grow up forever
         * limit here how many logins per provider ADA must keep in history
         */
        define('MODULES_LOGIN_HISTORY_LIMIT', 10);

        /**
         * module's action codes
         */
        define('MODULES_LOGIN_EDIT_OPTIONSET',       1);
        define('MODULES_LOGIN_EDIT_LOGINPROVIDER', 2);

        /**
         * default name implementing default login the first entry in login
         * provider of this class cannot be deleted and cannot be disabled if
         * it's the only login provider in the control panel
         */
        define('MODULES_LOGIN_DEFAULT_LOGINPROVIDER', 'adaLogin');

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
