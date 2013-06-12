<?php
/**
 * index.php
 * 
 * PHP version >= 5.2.2
 * 
 * @package		ARE
 * @subpackage  CORE
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		example			
 * @version		0.2
 */

require_once '../includes.inc.php';

//$element = new COREbody();
$html = CDOMElement::create('html');
$body = CDOMElement::create('body');

$body->addChild(new CORELocalizedText('testo'));
$html->addChild($body);

echo $html->getHtml();


?>