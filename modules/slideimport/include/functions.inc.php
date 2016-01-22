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

function getFileData($fileName) {
	$filedata = Array();
	$imagick = new Imagick($fileName);
	$filedata['numPages'] = $imagick->getnumberimages();
	$width = $imagick->getimagewidth();
	$height = $imagick->getimageheight();
	$filedata['orientation'] = ($width > $height) ? 'landscape' : 'portrait';
	$filedata['url'] = str_replace(ROOT_DIR, HTTP_ROOT_DIR, $fileName);
	return $filedata;
}

