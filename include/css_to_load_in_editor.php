<?php
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * used to load css rules in fck editor
 */
header('Content-type: text/css');
echo '@CHARSET "UTF-8";'.PHP_EOL;
echo <<<CSS
/**
 * OWN CSS RULES HERE
 */


CSS;
