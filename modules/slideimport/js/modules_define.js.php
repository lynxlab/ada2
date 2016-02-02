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
require_once MODULES_SLIDEIMPORT_PATH.'/config/config.inc.php';

if (defined('MODULES_SLIDEIMPORT_HTTP')) {
	echo 'var MODULES_SLIDEIMPORT_HTTP=\''.MODULES_SLIDEIMPORT_HTTP.'\';'.PHP_EOL;
}

if (defined('AMA_TYPE_AUTHOR')) {
	echo 'var AMA_TYPE_AUTHOR=' . AMA_TYPE_AUTHOR .';'.PHP_EOL;
} else {
	echo 'var AMA_TYPE_AUTHOR=null;'.PHP_EOL;
}

if (defined('AMA_TYPE_SWITCHER')) {
	echo 'var AMA_TYPE_SWITCHER=' . AMA_TYPE_SWITCHER .';'.PHP_EOL;
} else {
	echo 'var AMA_TYPE_SWITCHER=null;'.PHP_EOL;
}
