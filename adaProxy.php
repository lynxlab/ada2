<?php
/**
 * @package		main
 * @author		Giorgio Consorti <g.consorti@lynxlab.com_
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		adaProxy
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER     => array('layout'),
  AMA_TYPE_AUTHOR       => array('layout'),
  AMA_TYPE_ADMIN        => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

if (isset($_REQUEST['q']) && strlen(trim($_REQUEST['q']))>0) {
	$dec = openssl_decrypt($_REQUEST['q'], 'BF-ECB', ADAPROXY_ENC_KEY);
	if (false !== $dec) {
		$_REQUEST['q'] = $dec;
	}
	// fix links starting with a double slash
	$_REQUEST['q'] = trim (trim($_REQUEST['q']), '/');
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	// if q does not start by http[s], add the protocl part. This will work for both http and https
	if (stripos(trim($_REQUEST['q']),'http')!==0) $_REQUEST['q'] = $protocol . trim($_REQUEST['q']);
	die (file_get_contents(trim($_REQUEST['q'])));
}
