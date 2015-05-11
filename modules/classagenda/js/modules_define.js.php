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
require_once MODULES_CLASSBUDGET_PATH.'/config/config.inc.php';

/**
 * export defines for allowed user in calendars.php
 */
if (defined('AMA_TYPE_STUDENT'))  echo 'var AMA_TYPE_STUDENT = ' . AMA_TYPE_STUDENT.';';
if (defined('AMA_TYPE_TUTOR'))    echo 'var AMA_TYPE_TUTOR = ' . AMA_TYPE_TUTOR.';';
if (defined('AMA_TYPE_SWITCHER')) echo 'var AMA_TYPE_SWITCHER = ' . AMA_TYPE_SWITCHER.';';
