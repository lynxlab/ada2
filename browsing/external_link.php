<?php
/**
 * External link
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_VISITOR, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_VISITOR      => array('layout'),
  AMA_TYPE_AUTHOR       => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
include_once 'include/browsing_functions.inc.php';
$self =  whoami();
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';

$external_link_id = isset($_GET['id']) ? DataValidator::is_uinteger($_GET['id']) : false;

$filename = isset($_GET['file']) ? DataValidator::validate_local_filename($_GET['file']) : false;

//$url = DataValidator::validate_url($_GET['url']);
$url = isset($_GET['url']) ?  $_GET['url'] : null;

if($external_link_id != false) {
  $external_resourceAr = $dh->get_risorsa_esterna_info($external_link_id);
  if(AMA_DataHandler::isError($external_resourceAr)) {
    $data = '';
  }
  elseif($external_resourceAr['tipo'] != _LINK) {
    $data = '';
  }
  else {
    $address = $external_resourceAr['nome_file'];
    $data = UserModuleHtmlLib::getExternalLinkNavigationFrame($address)->getHtml();
  }
}
elseif ($filename != false) {
  if (basename($filename) == $filename) {
    $address = '';
    $http_path_to_file = HTTP_ROOT_DIR . '/docs/' . $filename;
    if(is_readable(ROOT_DIR . '/docs/' . $filename)) {
      $exploded_filename = explode('.', $filename);
      $pdf_filename = $exploded_filename[0] . '.pdf';
      if (is_readable(ROOT_DIR . '/docs/' . $pdf_filename)) {
        $href = HTTP_ROOT_DIR . '/docs/' . $pdf_filename;
      	$pdf_link = CDOMElement::create('a', "href: $href");
      	$pdf_link->addChild(new CText(translateFN('Download pdf version')));
      }
      else {
        $pdf_link = new CText('');
      }
      $data = $pdf_link->getHtml()
            . UserModuleHtmlLib::getExternalLinkNavigationFrame($http_path_to_file)->getHtml();
    }
    else {
      $data = translateFN('The required resource is currently not available.')
            . '<br />'
            . translateFN('Please try again later.');
    }
  }
  else {
      $data = translateFN('The required resource is not available.');
  }
}elseif ($url != false) {
        $data = UserModuleHtmlLib::getExternalLinkNavigationFrame($url)->getHtml();
}
else {
  $data = '';
}

$title = translateFN('ADA - External link navigation');

$content_dataAr = array(
  'data'      => $data,
  'address'   => $address,
  'status'    => $status,
  'user_name' => $user_name,
  'user_type' => $user_type,
);

ARE::render($layout_dataAr, $content_dataAr);
?>