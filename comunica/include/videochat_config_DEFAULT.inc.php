<?php

/**
 * Conference generic configuration file.
 *
 * @author
 * @version
 * @package
 * @license
 * @copyright (c) 2009 Lynx s.r.l.
 */

/**
 * if it's not a multiprovider environment
 * include provider videochat_config file
 */
if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']) && is_readable(ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/videochat_config.inc.php')) {
    require_once ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/videochat_config.inc.php';
}
/**
 * Type of conference integrated
 */
if (!defined('CONFERENCE_TO_INCLUDE')) {
    define('CONFERENCE_TO_INCLUDE', 'OpenMeeting'); // OpenMeeting
    // define ('CONFERENCE_TO_INCLUDE','Jitsi'); // Jitsi
    //define ('CONFERENCE_TO_INCLUDE','AdobeConnect'); //Adobe Connect
}

/**
 * Has to control if appoint is defined
 */
if (!defined('DATE_CONTROL')) {
    define('DATE_CONTROL', FALSE);
}
