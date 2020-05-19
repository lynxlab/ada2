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

/**
 * export defines for allowed user in calendars.php
 */
if (defined('AMA_TYPE_STUDENT'))  echo 'const AMA_TYPE_STUDENT = ' . AMA_TYPE_STUDENT.';' .PHP_EOL;
if (defined('AMA_TYPE_TUTOR'))    echo 'const AMA_TYPE_TUTOR = ' . AMA_TYPE_TUTOR.';' .PHP_EOL;
if (defined('AMA_TYPE_SWITCHER')) echo 'const AMA_TYPE_SWITCHER = ' . AMA_TYPE_SWITCHER.';' .PHP_EOL;
echo 'const MODULES_CLASSAGENDA_EMAIL_REMINDER=' . (defined('MODULES_CLASSAGENDA_EMAIL_REMINDER') && MODULES_CLASSAGENDA_EMAIL_REMINDER ? 'true;' : 'false;');
echo  PHP_EOL;
