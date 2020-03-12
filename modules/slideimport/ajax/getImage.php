<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR   => array('layout'),
		AMA_TYPE_TUTOR    => array('layout'),
		AMA_TYPE_STUDENT  => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
// require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';

if (isset($_GET['url']) && strlen(trim($_GET['url']))>0 &&
	isset($_GET['pageNum']) && intval($_GET['pageNum'])>0) {

	$fileName = str_replace(HTTP_ROOT_DIR, ROOT_DIR, trim($_GET['url']));
	// first page is zero
	$pageNum = intval($_GET['pageNum'])-1;
	$baseHeight = IMPORT_PREVIEW_HEIGHT;

	if (is_readable($fileName)) {
		$imagick = new Imagick();
		$imagick->readimage($fileName.'['.$pageNum.']');
		$width = $imagick->getimagewidth();
		$height = $imagick->getimageheight();

		$res = $imagick->getimageresolution();
		$bg = new Imagick();
		$bg->setresolution($res["x"],$res["y"]); //setting the same image resolution
		//create a white background image with the same width and height
		$bg->newimage($imagick->getimagewidth(), $imagick->getimageheight(), 'white');
		$bg->compositeimage($imagick, Imagick::COMPOSITE_OVER, 0, 0); //merging both images
		$bg->setimageformat(IMAGE_FORMAT);
		header('Content-type: '.IMAGE_HEADER_PREVIEW);
		echo $bg->getimageblob();
	}
}