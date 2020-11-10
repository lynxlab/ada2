<?php
/**
 * @package 	zoom integration module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

try {
	if (! @include_once(MODULES_ZOOMCONF_PATH.'/vendor/autoload.php')) {
		// @ - to suppress warnings,
		throw new Exception (
			json_encode(array(
				'header' => 'ZoomConf  module will not work because autoload file cannot be found!',
				'message' => 'Please run <code>composer install</code> in the module subdir'
		)));
	} else {
		// MODULE'S OWN DEFINES HERE
		/**
		 * NOTE
		 * ZOOMCONF_APIKEY and ZOOMCONF_APISECRET must be defined in:
		 * comunica/include/ZoomConf.config.inc.php
		 */
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
