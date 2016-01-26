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

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function getNameFromFileName($complete_file_name) {
	$pos = strpos($complete_file_name, '_');
	if ($pos !== false) {
		return trim(str_replace('_', ' ', substr($complete_file_name, $pos)));
	}
	return $complete_file_name;
}

