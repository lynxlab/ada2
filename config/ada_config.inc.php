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