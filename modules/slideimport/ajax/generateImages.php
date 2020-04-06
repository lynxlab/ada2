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
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);
$error=1;

if (isset($_GET['selectedPages']) && is_array($_GET['selectedPages']) && count($_GET['selectedPages'])>0) {
	$error=0;
	$fileName = str_replace(HTTP_ROOT_DIR, ROOT_DIR, trim($_GET['url']));
	if (is_readable($fileName)) {

		$info = pathinfo($fileName);
		$media_path = ROOT_DIR . MEDIA_PATH_DEFAULT . $userObj->getId() . DIRECTORY_SEPARATOR . $info['filename'];
		if (!is_dir($media_path)) {
			if(!mkdir($media_path, 0777, true)) $error = 1;
		}

		if ($error===0) {
			foreach ($_GET['selectedPages'] as $selectedPage) {
				$baseHeight = IMPORT_IMAGE_HEIGHT;
				$imagick = new Imagick();
				$imagick->readimage($fileName.'['.($selectedPage-1).']');
				$width = $imagick->getimagewidth();
				$height = $imagick->getimageheight();
				//$imagick->resizeImage(intval($baseHeight*($width/$height)),$baseHeight,Imagick::FILTER_LANCZOS,1);

				$res = $imagick->getimageresolution();
				$bg = new Imagick();
				$bg->setresolution($res["x"],$res["y"]); //setting the same image resolution
				//create a white background image with the same width and height
				$bg->newimage($imagick->getimagewidth(), $imagick->getimageheight(), 'white');
				$bg->compositeimage($imagick, Imagick::COMPOSITE_OVER, 0, 0); //merging both images
				$bg->resizeImage(intval($baseHeight*($width/$height)),$baseHeight,Imagick::FILTER_TRIANGLE,1);
// 				$imagick->transformImageColorspace(Imagick::COLORSPACE_SRGB);
				//$imagick->setImageFormat('png');
				$bg->setImageFormat(IMAGE_FORMAT);
				$bg->setImageCompressionQuality(IMAGE_COMPRESSION_QUALITY);
//				if ($imagick->writeimage($media_path . DIRECTORY_SEPARATOR . $selectedPage.'.png') !== true) {
				if ($bg->writeimage($media_path . DIRECTORY_SEPARATOR . $selectedPage.'.'.IMAGE_FORMAT) !== true) {
					// delete all files and dir on error
					delTree($media_path);
					$error = 1;
					break;
				}
			}
		}
	}
}
header('Content-Type: application/json');
echo json_encode (array('error'=>$error));