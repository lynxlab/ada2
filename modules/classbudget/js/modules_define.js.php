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
// require_once MODULES_CLASSBUDGET_PATH.'/config/config.inc.php';

if (defined('ADA_CURRENCY_SYMBOL')) echo 'var ADA_CURRENCY_SYMBOL = \''.ADA_CURRENCY_SYMBOL.'\';'.PHP_EOL;
if (defined('ADA_CURRENCY_DECIMALS')) echo 'var ADA_CURRENCY_DECIMALS = '.ADA_CURRENCY_DECIMALS .';'.PHP_EOL;
if (defined('ADA_CURRENCY_THOUSANDS_SEP')) echo 'var ADA_CURRENCY_THOUSANDS_SEP = \''.ADA_CURRENCY_THOUSANDS_SEP.'\';'.PHP_EOL;
if (defined('ADA_CURRENCY_DECIMAL_POINT')) echo 'var ADA_CURRENCY_DECIMAL_POINT = \''.ADA_CURRENCY_DECIMAL_POINT.'\';'.PHP_EOL;
