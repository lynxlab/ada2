<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * To prevent `module_login_history_login` table to grow up forever
 * limit here how many logins per provider ADA must keep in history
 */
define ('MODULES_LOGIN_HISTORY_LIMIT', 10);

require_once MODULES_LOGIN_PATH . '/include/abstractLogin.class.inc.php'; 
require_once MODULES_LOGIN_PATH . '/include/AMALoginDataHandler.inc.php';
?>