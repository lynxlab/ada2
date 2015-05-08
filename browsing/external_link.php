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
    
    /**
     * @author giorgio 07/mag/2015
     *
     * look for possible translations of passed file
     * e.g.: file=privacy.html will look for privacy_en.html, privacy_it.html...
     *       file=guest_it.html will look for
     */
    $foundFile = is_readable(ROOT_DIR . '/docs/' . $filename);
    /**
     * NOTE: it's safe to assume that $filename has a dot, the
     * validate_local_filename would have returned false if it had not
     */
    $exploded_filename = explode('.', $filename);
    
    if (!$foundFile) {
	    $extension = '.'.end($exploded_filename);
	    $underscoreDelimited = explode ('_',reset($exploded_filename));
	    /**
	     * If the last piece of $underscoreDelimited has length 2
	     * it's assumed to be lang part of the file name, remove it
	     */
	    if (strlen(end($underscoreDelimited))===2) {
	    	unset($underscoreDelimited[count($underscoreDelimited)-1]);
	    }
	    /**
	     * build the array of candidate languages
	     */
	    $tryLangs = array($login_page_language_code = Translator::negotiateLoginPageLanguage());
	    if (!in_array(ADA_LOGIN_PAGE_DEFAULT_LANGUAGE, $tryLangs)) $tryLangs[] = ADA_LOGIN_PAGE_DEFAULT_LANGUAGE;
	    /**
	     * loop the array until a file has been found
	     * or end of array has been reached
	     */
	    for ($currentLang = reset($tryLangs); ((current($tryLangs)!==false) && !$foundFile) ; $currentLang = next($tryLangs)) {
	    	$filename = implode('_', $underscoreDelimited).'_'.$currentLang.$extension;
	    	$foundFile = is_file(ROOT_DIR . '/docs/' . $filename) && is_readable(ROOT_DIR . '/docs/' . $filename);
	    }
    }
    
    if($foundFile) {
      $http_path_to_file = HTTP_ROOT_DIR . '/docs/' . $filename;
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