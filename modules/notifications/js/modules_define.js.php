<?php
/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");
/**
 * Module config file
 */

if (defined('MODULES_NOTIFICATIONS_HTTP')) {
	echo 'const MODULES_NOTIFICATIONS_HTTP=\''.MODULES_NOTIFICATIONS_HTTP.'\';'.PHP_EOL;
}

foreach(\Lynxlab\ADA\Module\Notifications\Notification::types as $key => $val) {
	echo sprintf("const MODULES_NOTIFICATIONS_TYPES_%s=%d;".PHP_EOL, $key, $val);
}
