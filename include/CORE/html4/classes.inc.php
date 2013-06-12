<?php
/**
 * classes.inc.php, all the classes needed to render HTML4 valid elements.
 *
 * PHP version >= 5.2.2
 * 
 * @package		ARE
 * @subpackage  CORE
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		core_classes			
 * @version		0.2
 */
abstract class CORERenderableElement
{
  /**
   * 
   * @return string
   */
  abstract public function getHtml();
}

class COREText extends CORERenderableElement
{
  /**
   * 
   * @var string
   */
  protected $text;
  
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public function __construct($text) {
    $this->text = $text;    
  }
  
  /**
   * (non-PHPdoc)
   * @see CORE/html4/CORERenderableElement#getHtml()
   */
  public function getHtml() {
    CORELogger::Log('Getting text: ' . $this->text);
    return $this->text;
  }
}

class CText extends CORERenderableElement
{
  /**
   * 
   * @var string
   */
  protected $text;
  
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public function __construct($text) {
    $this->text = $text;    
  }
  
  /**
   * (non-PHPdoc)
   * @see CORE/html4/CORERenderableElement#getHtml()
   */
  public function getHtml() {
    CORELogger::Log('Getting text: ' . $this->text);
    return $this->text;
  }
}


class CORELocalizedText extends COREText
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public function __construct($text) {
    /*
     * Call here your text translation function.
     * e.g. $this->text = translateFN($text);
     */
    $this->text = $text;
  }
}

class CLocalizedText extends CText
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public function __construct($text) {
    /*
     * Call here your text translation function.
     * e.g. $this->text = translateFN($text);
     */
    $this->text = $text;
  }
}

abstract class COREHtmlElement extends CORERenderableElement
{

  protected $__isEmpty;
  protected $__children;
  protected $__accepted;
  protected $__rejected;
  
  public function addChild(CORERenderableElement $e) {
    if($this->notEmptyElement()) {
      CORELogger::Log('Adding child ' . get_class($e) 
                       . ' to element ' . get_class($this));
      $this->__children[] = $e;
    }
  }
    
  public function setAttribute($attribute, $value) {
    
    if(property_exists($this,$attribute)) {
      CORELogger::Log('Setting attribute: ' . $attribute . ' to value: ' . $value);  
      
      $this->$attribute = $value;
    }
    else {
      CORELogger::Log('Cannot set attribute ' . $attribute 
                      .' for class ' . get_class($this) 
                      . ', it does not exist. Check you code.');  
    }
  }
  
  public function getAttribute($attribute) {
    
    if(property_exists($this,$attribute) && isset($attibute)) {
      CORELogger::Log('Getting attribute: ' . $attribute . ' with value: ' . $this->$attribute);  
    
      return $this->$attribute;
    }
    else {
      CORELogger::Log('Cannot get attribute ' . $attribute 
                      .' for class ' . get_class($this) 
                      . ', it does not exist or it is not set.');  
    }
  }
  
  public function setAttributes($attibutes) {
      CORELogger::Log('Setting multiple attributes for class ' 
                      . get_class($this));    
  }
  
  public function getHtml() {
    CORELogger::Log('Getting html for ' .get_class($this));
  /*  
    if($this->notEmptyElement()) {
     foreach($this->__children as $child) {
       $child->getHtml();
     }
    }
*/
   /*
    * copiato da CORE_v0.1
    */
    // FIXME: sistemare il codice di getHtml() importato da CORE_v0.1
    $matches   = array();
    $pattern   = array();
    $attribute = array();
    
    $html_element = get_class($this);
    $template     = CDOMElement::getElementTemplate($html_element);
    
    $search_attributes = '/%([a-z\-]+)%/';
    preg_match_all($search_attributes, $template, $matches);
    

    foreach($matches[1] as $match=>$text) {
      // FIXME: avoid skipping newline
      $pattern[$match] = "/\s*%$text%\s*/";
       
      if ($text == 'children' && $this->notEmptyElement()) {
        foreach($this->__children as $child) {
          $attribute[$match] .= $child->getHtml();
        }
      }
      else {
        if ($this->$text === NULL) {
          $attribute[$match] = "";
        }
        else {
          // the whitespace at the beginning of the string is needed
          $attribute[$match] = " $text=\"{$this->$text}\"";
        }
      }
    }

    $html = preg_replace($pattern, $attribute, $template);
    return $html;
  }
  
  protected function setEmptyElement()
  {
    $this->__isEmpty  = YES;
    $this->__children = NULL;
    $this->__accepted = NULL;
    $this->__rejected = NULL;
  }
  
  protected function setNotEmptyElement()
  {
    $this->__isEmpty  = NO;
    $this->__children = array();
    $this->__accepted = array();
    $this->__rejected = array();        
  }
  
  protected function emptyElement() {
    return $this->__isEmpty == YES;
  }
  
  protected function notEmptyElement() {
    return $this->__isEmpty == NO;
  }
}

abstract class COREAttrsElement extends COREHtmlElement 
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
}

abstract class COREi18nElement extends COREHtmlElement 
{
  protected $lang;
  protected $dir;
}

abstract class COREFlowElement extends COREHtmlElement 
{
}

abstract class COREInlineElement extends COREFlowElement 
{
}

abstract class COREBlockElement extends COREFlowElement 
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
}

abstract class COREFontstyle extends COREInlineElement 
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
}

abstract class COREPhrase extends COREInlineElement 
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
}

abstract class CORESpecial extends COREInlineElement 
{
}

abstract class COREFormctrl extends COREInlineElement 
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
}

abstract class COREList extends COREBlockElement
{}

abstract class COREHeading extends COREBlockElement
{}

abstract class COREPreformatted extends COREBlockElement
{}

class COREbody extends COREAttrsElement
{
  protected $onload;
  protected $onunload;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREBlockElement, COREins, COREdel, SCRIPT. Sicuro?
  }
}

class COREarea extends COREAttrsElement
{
  protected $shape;
  protected $coords;
  protected $href;
  protected $nohref;
  protected $alt;
  protected $tabindex;
  protected $accesskey;
  protected $onfocus;
  protected $onblur;
  
  public function __construct() {   
    $this->setEmptyElement();
  }
}

class CORElink extends COREAttrsElement
{
  protected $charset;
  protected $href;
  protected $hreflang;
  protected $type;
  protected $rel;
  protected $rev;
  protected $media;
  
  public function __construct() {   
    $this->setEmptyElement();
  }
}

class COREins extends COREAttrsElement
{
  protected $cite;
  protected $datetime;

  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREFlowElement
  }
}

class COREdel extends COREAttrsElement
{
  protected $cite;
  protected $datetime;

  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREFlowElement   
  }
}

class COREli extends COREAttrsElement
{
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREFlowElement
  }
}

class COREoptgroup extends COREAttrsElement
{
  protected $disabled;
  protected $label;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREoption
  }
}

class COREoption extends COREAttrsElement
{
  protected $selected;
  protected $disabled;
  protected $label;
  protected $value;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only #PCDATA
  }
}

class CORElegend extends COREAttrsElement
{
  protected $accesskey;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREcaption extends COREAttrsElement
{
  public function __construct() {   
  }
}

class COREthead extends COREAttrsElement
{
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREtr
  }
}

class COREtfoot extends COREAttrsElement
{
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREtr
  }
}

class COREtbody extends COREAttrsElement
{
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREtr
  }
}

class COREcolgroup extends COREAttrsElement
{
  protected $span;
  protected $width;
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREcol
  }
}

class COREcol extends COREAttrsElement
{
  protected $span;
  protected $width;
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setEmptyElement();
  }
}

class COREtr extends COREAttrsElement
{
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
   
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREth, COREtd
  }
}

class COREth extends COREAttrsElement
{
  protected $abbr;
  protected $axis;
  protected $headers;
  protected $scope;
  protected $rowspan;
  protected $colspan;
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREFlowElement
  }
}

class COREtd extends COREAttrsElement
{
  protected $abbr;
  protected $axis;
  protected $headers;
  protected $scope;
  protected $rowspan;
  protected $colspan;
  // %cellhalign
  protected $align;
  protected $char;
  protected $charoff;
  // %cellvalign
  protected $valign;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREFlowElement
  }
}

class COREhead extends COREi18nElement
{
  protected $profile;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    //FIXME: verificare %head.content
    // accepts only %head.content, COREscript, COREstyle, COREmeta, CORElink
    // COREobject
  }
}

class COREtitle extends COREi18nElement
{
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only #PCDATA, rejects COREscript, COREstyle, COREmeta, CORElink
    // COREobject
  }
}

class COREmeta extends COREi18nElement
{
  protected $http_equiv; //http-equiv
  protected $name;
  protected $content;
  protected $scheme;
  
  public function __construct() {   
    $this->setEmptyElement();
  }
}

class COREstyle extends COREi18nElement
{
  protected $type;
  protected $media;
  protected $title;
  
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only %Stylesheet
  }
}

class COREhtml extends COREi18nElement
{
  public function __construct() {   
    $this->setNotEmptyElement();
    // accepts only COREhead, COREbody
  }
}

class COREbase extends COREHtmlElement
{  
  protected $href;
  
  public function __construct() {
    $this->setEmptyElement();
  }
}

class COREparam extends COREHtmlElement
{  
  protected $id;
  protected $name;
  protected $value;
  protected $valuetype;
  protected $type;
  
  public function __construct() {
    $this->setEmptyElement();
  }
}

class COREtt extends COREFontstyle 
{  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }
}

class COREi extends COREFontstyle 
{  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }
}

class COREb extends COREFontstyle 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREbig extends COREFontstyle 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREsmall extends COREFontstyle 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREem extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREstrong extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREdfn extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREcode extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREsamp extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREkbd extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREvar extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREcite extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREabbr extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREacronym extends COREPhrase 
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREa extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  protected $charset;
  protected $type;
  protected $href;
  protected $hreflang;
  protected $rel;
  protected $rev;
  protected $accesskey;
  protected $shape;
  protected $coords;
  protected $tabindex;
  protected $onfocus;
  protected $onblur;
    
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement  
    // rejects only COREa
  }  
   
}

class COREimg extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  protected $src;
  protected $alt;
  protected $longdesc;
  protected $name;
  protected $height;
  protected $width;
  protected $usemap;
  protected $ismap;
  
  public function __construct() {
    $this->setEmptyElement();
  }  
}
class COREobject extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  protected $declare;
  protected $classid;
  protected $codebase;
  protected $data;
  protected $type;
  protected $codetype;
  protected $archive;
  protected $standby;
  protected $height;
  protected $width;
  protected $usemap;
  protected $name;
  protected $tabindex;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREparam, COREFlowElement
  }  
}

class COREbr extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  public function __construct() {
    $this->setEmptyElement();  
  }  
}

class COREscript extends CORESpecial
{
  protected $charset;
  protected $type;
  protected $src;
  protected $defer;
  protected $event;
  protected $for;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only %Script
  }  
}

class COREmap extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;

  protected $name;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREBlockElement, COREarea  
  }  
}

class COREq extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  protected $cite;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREBlockElement, COREarea      
  }  
}

class COREsub extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREsup extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement
  }  
}

class COREspan extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  // i18n
  protected $lang;
  protected $dir;
  
  // events
  protected $onclick;
  protected $ondblclick;
  protected $onmousedown;
  protected $onmouseup;
  protected $onmouseover;
  protected $onmousemove;
  protected $onkeypress;
  protected $onkeydown;
  protected $onkeyup;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement  
  }  
}

class COREbdo extends CORESpecial
{
  // coreattrs
  protected $id;
  protected $class;
  protected $style;
  protected $title;
  
  protected $lang;
  protected $dir;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, COREInlineElement  
  }  
}

class COREinput extends COREFormctrl
{
  protected $type;
  protected $name;
  protected $value;
  protected $checked;
  protected $disabled;
  protected $readonly;
  protected $size;
  protected $maxlength;
  protected $src;
  protected $alt;
  protected $usemap;
  protected $ismap;
  protected $tabindex;
  protected $acceskey;
  protected $onfocus;
  protected $onblur;
  protected $onselect;
  protected $onchange;
  protected $accept;
  
  public function __construct() {
    $this->setEmptyElement();
  }  
}

class COREselect extends COREFormctrl
{
  protected $name;
  protected $size;
  protected $multiple;
  protected $disabled;
  protected $tabindex;
  protected $onfocus;
  protected $onblur;
  protected $onchange;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only  COREoptgroup, COREoption
  }  
}

class COREtextarea extends COREFormctrl
{
  protected $name;
  protected $rows;
  protected $cols;
  protected $disabled;
  protected $readonly;
  protected $tabindex;
  protected $accesskey;
  protected $onfocus;
  protected $onblur;
  protected $onselect;
  protected $onchange;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA
  }  
}

class CORElabel extends COREFormctrl
{
  protected $for;
  protected $accesskey;
  protected $onfocus;
  protected $onblur;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
    // rejects only CORElabel  
  }  
}

class COREbutton extends COREFormctrl
{
  protected $name;
  protected $value;
  protected $type;
  protected $disabled;
  protected $tabindex;
  protected $accesskey;
  protected $onfocus;
  protected $onblur;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREFlowElement
    // rejects only COREa, COREFormctrl, COREform, COREfieldset
  }  
}

class COREol extends COREList
{
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREli
  }
}

class COREul extends COREList
{
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREli
  }
}

class COREh1 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREh2 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREh3 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREh4 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREh5 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREh6 extends COREHeading
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREpre extends COREPreformatted
{
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
    // rejects only COREimg, COREobject, COREbig, COREsmall, COREsub, COREsup 
  }
}

class COREp extends COREBlockElement
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement
  }
}

class COREdl extends COREBlockElement
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREdt, COREdd
  }
}

class COREdiv extends COREBlockElement
{ 
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREFlowElement    
  }
}

class COREnoscript extends COREBlockElement
{
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREBlockElement  
  }
}

class COREblockquote extends COREBlockElement
{
  protected $cite;
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREBlockElement, COREscript    
  }
}

class COREform extends COREBlockElement
{
  protected $action;
  protected $method;
  protected $enctype;
  protected $accept;
  protected $name;
  protected $onsubmit;
  protected $onreset;
  protected $accept_charset; //accept-charset
  
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREBlockElement, COREscript
    // rejects only COREform
  }
}

class COREhr extends COREBlockElement
{
  
  public function __construct() {
    $this->setEmptyElement();
  }
}

class COREtable extends COREBlockElement
{
  protected $summary;
  protected $width;
  protected $border;
  protected $frame;
  protected $rules;
  protected $cellspacing;
  protected $cellpadding;
  protected $datapagesize; //riservato per usi futuri
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only  COREcaption, COREcol, COREcolgroup
    // COREthead, COREtfoot, COREtbody
  }
}

class COREfieldset extends COREBlockElement
{
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only #PCDATA, CORElegend, COREFlowElement  
  }
}

class COREaddress extends COREBlockElement
{
  
  public function __construct() {
    $this->setNotEmptyElement();
    // accepts only COREInlineElement  
  }
}
?>