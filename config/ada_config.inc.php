<?php
/**
 * configuration file
 *
 * This is a meta config file,add all constants and global variables to a single
 * file in this folder then add the related line here.
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * constants and global variables
 *
 * DO NOT MODIFY THIS FILE
 */
require_once('config_main.inc.php');

require_once(ROOT_DIR.'/include/ModuleLoaderHelper.inc.php');

/**
 * if it's not a multiprovider environment
 * find out 3rd level domain name and include
 * provider config file accordingly
 */
if (!MULTIPROVIDER)
{
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$servername = $_SERVER['HTTP_X_FORWARDED_HOST'];
		} else if (isset($_SERVER['SERVER_NAME'])) {
			$servername = $_SERVER['SERVER_NAME'];
		}
		list ($client) = explode ('.',preg_replace('/(http[s]?:\/\/)/', '', $servername));
	}

	if (isset($client) && !empty ($client) && is_dir(ROOT_DIR.'/clients/'.$client))
		require_once ROOT_DIR.'/clients/'.$client.'/client_conf.inc.php';
}

/**
 * constants and global variables from installation process
 *
 * YOU NEED TO EDIT THIS FILE WHEN INSTALLING THE SOFTWARE
 */
require_once('config_install.inc.php');

/**
 * constants for error management
 *
 * DO NOT MODIFY THIS FILE
 */
require_once('config_errors.inc.php');

/**
 * constants used by chat
 *
 * DO NOT MODIFY THIS FILE
 */
require_once('config_chat.inc.php');

/**
 * constants used by developers
 *
 * DO NOT MODIFY THIS FILE
 */
require_once('config_dev.inc.php');

/**
 * DO NOT MODIFY THIS FILE
 */
require_once('config_jsgraph.inc.php');

/**
 * if it's not a multiprovider environment
 * include provider config_modules file
 */
if (!MULTIPROVIDER && isset($client) && !empty ($client) && is_readable(ROOT_DIR.'/clients/'.$client.'/config_modules.inc.php')) {
	require_once ROOT_DIR.'/clients/'.$client.'/config_modules.inc.php';
}

/**
 * constants used by modules
 *
 * THIS FILE CONTAINS PATH TO SUPPORTED MODULES
 */
require_once('config_modules.inc.php');

/**
 * if it's not a multiprovider environment
 * include provider smtp setting
 */
if (!MULTIPROVIDER && isset($client) && !empty ($client) && is_readable(ROOT_DIR.'/clients/'.$client.'/config_smtp.inc.php')) {
	require_once ROOT_DIR.'/include/phpMailer/ADAPHPMailer.php';
	require_once ROOT_DIR.'/clients/'.$client.'/config_smtp.inc.php';
	define ('ADA_SMTP', true);
} else if (is_readable(ROOT_DIR . '/config/config_smtp.inc.php')) {
	require_once ROOT_DIR.'/include/phpMailer/ADAPHPMailer.php';
	require_once('config_smtp.inc.php');
	define ('ADA_SMTP', true);
} else {
	define ('ADA_SMTP', false);
}

/**
 * each php script includes this file, set general cookie params here
 */
$pieces = parse_url(HTTP_ROOT_DIR);
$domain = isset($pieces['host']) ? $pieces['host'] : '';
$secure = isset($pieces['scheme']) && ($pieces['scheme'] === 'https');
$path = isset($pieces['path']) ? '/'.trim($pieces['path'],'/') : '/';
if (strlen($domain)>0) {
    session_set_cookie_params(
        0, // lifetime: ends when browser closes
        $path.'; samesite='.($secure ? 'None' : 'Lax'),
        $domain,
        $secure,
        false // http only
    );
}
unset($domain);
unset($pieces);
unset($secure);
