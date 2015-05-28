<?php
/**
 * CDOMElement.inc.php, used to create CORERenderable objects.
 * 
 * PHP version >= 5.2.2
 * 
 * @package		ARE
 * @subpackage  CORE
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		cdomelement			
 * @version		0.2
 */
class CDOMElement {
  
  public static function create($element_name, $attributes=null) {
    $classname = 'CORE'.$element_name;
    if(class_exists($classname)) {
      $element = new $classname;
      if($attributes !== null && is_string($attributes)) {
        $element->setAttributes($attributes);
      }
      return $element;
    }
    return null;
  }
  
  public static function getElementTemplate($element_class) {
    $core_attributes  = '%id% %class% %style% %title%';
    $i18n_attributes  = '%lang% %dir%';
    $event_attributes = '%onclick% %ondblclick% %onmousedown% %onmouseup% %onmouseover% %onmousemove% %onmouseout% %onkeypress% %onkeydown% %onkeyup%';

    $attrs = "$core_attributes $i18n_attributes $event_attributes"; 
    
    switch($element_class)
    {
      case 'COREhtml':
        return "<HTML $i18n_attributes>\n%children%\n</HTML>\n";
        
      case 'COREbody':
        return "<BODY $attrs %onload% %onunload%>\n%children%\n</BODY>\n";

      default:
        return '';
    }
  }
}
?>