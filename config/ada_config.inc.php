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
 * constants used by modules
 *
 * THIS FILE CONTAINS PATH TO SUPPORTED MODULES
 */
require_once('config_modules.inc.php');