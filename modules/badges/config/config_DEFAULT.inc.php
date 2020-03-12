<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

try {
	if (! @include_once(MODULES_BADGES_PATH.'/vendor/autoload.php')) {
		// @ - to suppress warnings,
		throw new Exception (
			json_encode(array(
				'header' => 'Badges module will not work because autoload file cannot be found!',
				'message' => 'Please run <code>composer install</code> in the module subdir'
		)));
	} else if (!defined('MODULES_SERVICECOMPLETE') || (defined('MODULES_SERVICECOMPLETE') && MODULES_SERVICECOMPLETE !== true)) {
        throw new Exception (
			json_encode(array(
				'header' => 'Badges module will not work because the service complete module cannot be found!',
				'message' => 'Please install the <code>SERVICE COMPLETE</code> module'
		)));
	} else {
		// MODULE'S OWN DEFINES HERE
		define ('MODULES_BADGES_MEDIAPATH', ROOT_DIR . MEDIA_PATH_DEFAULT . MODULES_BADGES_NAME . DIRECTORY_SEPARATOR);
		return true;
	}
} catch(Exception $e) {
	$text = json_decode($e->getMessage(), true);
	// populating $_GET['message'] is a dirty hack to force the error message to appear in the home page at least
	if (!isset($_GET['message'])) $_GET['message'] = '';
	$_GET['message'] .= '<div class="ui icon error message"><i class="ban circle icon"></i><div class="content">';
	if (array_key_exists('header', $text) && strlen($text['header'])>0) {
		$_GET['message'] .= '<div class="header">'.$text['header'].'</div>';
	}
	if (array_key_exists('message', $text) && strlen($text['message'])>0) {
		$_GET['message'] .= '<p>'.$text['message'].'</p>';
	}
	$_GET['message'] .= '</div></div>';
}
return false;
