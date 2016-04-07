<?php
/**
 * client own modules configuration
 *
 * To disable a module that would otherwise be enabled by /config/config_modules.inc.php
 * set to false the proper constant.
 * E.g. to disable MODULES_TEST for this client only, just add the line
 *
 * define('MODULES_TEST', false);
 *
 * If a module is to be enabled for this client only, pls add the proper inclusion code here
 * E.g. to add MODULES_DUMMY for this client only, add the following:
 *
 * if (!defined('MODULES_DUMMY')) {
 * // defines for module dummy
 * define ('MODULES_DUMMY_PATH', MODULES_DIR.'/dummy');
 * if (file_exists(MODULES_DUMMY_PATH.'/config/config.inc.php')) {
 * 		require_once(MODULES_DUMMY_PATH.'/config/config.inc.php');
 * 		define('MODULES_DUMMY', true);
 * 		define('MODULES_DUMMY_HTTP', HTTP_ROOT_DIR.'/modules/dummy');
 * 	} else {
 * 		define('MODULES_DUMMY', false);
 * 	}
 * }
 *
 */
?>
