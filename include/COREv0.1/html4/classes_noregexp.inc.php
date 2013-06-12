<?php
/**
 * abstract class CBase: defines an abstract method, getHtml()
 * that all of the elements in this hierarchy have to redefine.
 *
 * @author vito
 */
abstract class CBase
{
    abstract public function getHtml();
}
/**
 * abstract class CBaseElement: this class defines base methods common to all
 * of the DOM elements.
 *
 * @author vito
 */
abstract class CBaseElement extends CBase
{
    /**
     * function getAttribute
     *
     * @param string $attribute_name - the name of the attribute
     */
    public function getAttribute($attribute_name)
    {
        if (isset($this->$attribute_name))
        {
            return $this->$attribute_name;
        }

        return NULL;
    }

    public function setAttribute($attribute_name, $attribute_value)
    {
        if (property_exists($this, $attribute_name))
        {
            $this->$attribute_name = $attribute_value;
            return TRUE;
        }
        return FALSE;
    }

    public function setAttributes($a_list_of_attribute_value_pairs)
    {
        // FIXME: verificare bene l'espressione regolare relativa al valore
        $attribute_value_pair = '/\s*([a-z-]+)\s*:\s*([\s\(\)a-zA-Z0-9:;\.\[\]\/=\?\+%&_@-]+)\s*/';
        //$attribute_value_pair = '/\s*([a-z]+)\s*:\s*(.*)\s*/';

        $matches = array();
        preg_match_all($attribute_value_pair, $a_list_of_attribute_value_pairs, $matches);

        $attributes       = array();
        $attributes       = $matches[1];
        $attributes_count = count($attributes);

        $values       = array();
        $values       = $matches[2];
        $values_count = count($values);

        for ($i = 0; $i < $attributes_count; $i++)
        {
            $attribute = str_replace('-','_',$attributes[$i]);
            $this->setAttribute($attribute, $values[$i]);
        }
    }
}
/**
 * abstract class CBaseElement: this class defines base methods common to all
 * of the DOM elements.
 *
 * @author vito
 */
abstract class CBaseAttributesElement extends CBaseElement
{
    protected $id;
    protected $class;

    protected $lang;
    protected $dir;

    protected $title;
    protected $style;

    protected $onclick;
    protected $ondblclick;
    protected $onmousedown;
    protected $onmouseup;
    protected $onmouseover;
    protected $onmousemove;
    protected $onmouseout;
    protected $onkeypress;
    protected $onkeydown;
    protected $onkeyup;

    public function __construct() {

    }
}
/**
 * abstract class CoreAttributesElement: this class defines base methods common to all
 * of the DOM elements.
 *
 * @author vito
 */
abstract class CCoreAttributesElement extends CBaseElement
{
    protected $id;
    protected $class;
    protected $style;
    protected $title;

    protected $_children;
    /**
     * @var $_accept - which elements can be added as children
     */
    protected $_accept;
    /**
     * @var $_reject - which elements cannot be added as children
     */
    protected $_reject;

    public function __construct()
    {
        $this->_children = array();
        $this->_accept   = array();
        $this->_reject   = array();
    }

    public function getHtml()
    {
		$class_vars = get_object_vars($this);

		$tag = constant(get_class($this).'::TAG');

		$html = '<'.$tag.' ';

		foreach ($class_vars as $var=>$value) {

			if (strpos($var, '_') !== false) continue;

			if (is_null($value)) {
				//non stampo niente
			}
			else if (empty($value) && $value !== 0 && $value !== '0') {
				$html.= ' '.$var;
			}
			else {
				// the whitespace at the beginning of the string is needed
				$html.= ' '.$var.'="'.$value.'"';
			}
        }

		if (isset($this->_children)) {
			$html.= '>'."\n";
			foreach($this->_children as $child) {
				$html.= $child->getHtml();
			}
			$html.= '</'.$tag.'>';
		}
		else $html.= ' />';

		$html.= "\n";

        return $html;
    }
}
/**
 * abstract class I18NAttributesElement: this class defines base methods common to all
 * of the DOM elements.
 *
 * @author vito
 */
abstract class CI18NAttributesElement extends CBaseElement
{
    protected $lang;
    protected $dir;
}
/**
 * class CText
 *
 * @author vito
 */
class CText extends CBase
{
    private $t;

    public function __construct($text)
    {
        $this->t = $text;
    }

    public function getHtml()
    {
        return $this->t;
    }
}
/**
 * abstract class Element: this class implements the method
 * getHtml, declared as abstract in class Base and defines the
 * method to be called for adding a child to the DOM element.
 *
 * @author vito
 */
abstract class CElement extends CBaseAttributesElement
{
    protected $_children;
    /**
     * @var $_accept - which elements can be added as children
     */
    protected $_accept;
    /**
     * @var $_reject - which elements cannot be added as children
     */
    protected $_reject;

    public function __construct()
    {
        $this->_children = array();
        $this->_accept   = array();
        $this->_reject   = array();
    }

    public function addChild(CBase $child)
    {
        $child_classname = get_class($child);
        if (count($this->_accept) > 0)
        {
            if(isset($this->_accept[$child_classname]))
            {
                array_push($this->_children, $child);
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else if(count($this->_reject) > 0)
        {
            if(!isset($this->_reject[$child_classname]))
            {
                array_push($this->_children, $child);
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            array_push($this->_children, $child);
            return TRUE;
        }
    }

    public function addAccepted($accepted_element_classname)
    {
        $this->_accept[$accepted_element_classname] = TRUE;
    }

    public function addRejected($rejected_element_classname)
    {
        $this->_reject[$rejected_element_classname] = TRUE;
    }

	public function getHtml()
    {
		$class_vars = get_object_vars($this);

		$tag = constant(get_class($this).'::TAG');

		$html = '<'.$tag.' ';

		foreach ($class_vars as $var=>$value) {

			if (strpos($var, '_') !== false) continue;

			if (is_null($value)) {
				//non stampo nulla
			}
			else if (empty($value) && $value !== 0 && $value !== '0') {
				$html.= ' '.$var;
			}
			else {
				// the whitespace at the beginning of the string is needed
				$html.= ' '.$var.'="'.$value.'"';
			}
        }

		if (isset($this->_children)) {
			$html.= '>'."\n";
			foreach($this->_children as $child) {
				$html.= $child->getHtml();
			}
			$html.= '</'.$tag.'>';
		}
		else $html.= ' />';

		$html.= "\n";

        return $html;
    }
}
/**
 *
 * @author vito
 */
abstract class CEmptyElement extends CBaseAttributesElement
{
	public function getHtml()
    {
		$class_vars = get_object_vars($this);

		$tag = constant(get_class($this).'::TAG');

		$html = '<'.$tag.' ';

		foreach ($class_vars as $var=>$value) {

			if (strpos($var, '_') !== false) continue;

			if (is_null($value)) {
				//non stampo niente
			}
			else if (empty($value) && $value !== 0 && $value !== '0') {
				$html.= ' '.$var;
			}
			else {
				// the whitespace at the beginning of the string is needed
				$html.= ' '.$var.'="'.$value.'"';
			}
        }

		$html.= ' />';

		$html.="\n";

        return $html;
    }
}
/**
 * abstract class CoreAttributesElement: this class defines base methods common to all
 * of the DOM elements.
 *
 * @author vito
 */
abstract class CFrameElement extends CCoreAttributesElement
{
    protected $longdesc;
    protected $name;
    protected $src;
    protected $frameborder;
    protected $marginwidth;
    protected $marginheight;
    protected $noresize;
    protected $scrolling;
}
/**
 *
 *@author vito
 */
abstract class CSelectElement extends CElement
{
    protected $disabled;
    protected $label;
}
/**
 *
 *@author vito
 */
abstract class CAlignableElement extends CElement
{
    protected $align;
    protected $char;
    protected $charoff;
    protected $valign;
}
/**
 *
 *@author vito
 */
abstract class CFocusableElement extends CElement
{
    protected $onfocus;
    protected $onblur;
}
/**
 *
 *@author vito
 */
abstract class CAccesskeyElement extends CElement
{
    protected $accesskey;
}
/**
 *
 *@author vito
 */
abstract class CTabindexElement extends CElement
{
    protected $tabindex;
}
/**
 *
 *@author vito
 */
abstract class CAlignableEmptyElement extends CEmptyElement
{
    protected $align;
    protected $char;
    protected $charoff;
    protected $valign;
}
/**
 *
 *@author vito
 */
abstract class CFocusableEmptyElement extends CEmptyElement
{
    protected $onfocus;
    protected $onblur;

}
/**
 *
 *@author vito
 */
class CUl extends CElement
{
	const TAG = 'ul';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CLi');
    }
}
/**
 *
 *@author vito
 */
class COl extends CElement
{
	const TAG = 'ol';

	protected $start;

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CLi');
    }
}
/**
 *
 * @author vito
 */
class CLi extends CElement
{
	const TAG = 'li';

    function __construct()
    {
        parent::__construct();
    }
}
/**
 *
 *@author vito
 */
class CDl extends CElement
{
	const TAG = 'dl';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CDt');
        $this->addAccepted('CDd');
    }
}

class CDd extends CElement
{
	const TAG = 'dd';

    public function __construct()
    {
        parent::__construct();
    }
}

class CDt extends CElement
{
	const TAG = 'dt';

    public function __construct()
    {
        parent::__construct();
    }
}

class CTable extends CElement
{
	const TAG = 'table';

    protected $summary;
    protected $width;
    protected $border;
    protected $frame;
    protected $rules;
    protected $cellspacing;
    protected $cellpadding;

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CCaption');
        $this->addAccepted('CCol');
        $this->addAccepted('CColgroup');
        $this->addAccepted('CTHead');
        $this->addAccepted('CTFoot');
        $this->addAccepted('CTBody');
    }
}

class CCaption extends CElement
{
	const TAG = 'caption';

    public function __construct()
    {
        parent::__construct();
    }
}

class CFieldset extends CElement
{
	const TAG = 'fieldset';

    public function __construct()
    {
        parent::__construct();
        // TODO: chiamare addAccepted? Legend, %flow
    }
}

class CSpan extends CElement
{
	const TAG = 'span';

    public function __construct()
    {
        parent::__construct();
    }
}


class CDiv extends CElement
{
	const TAG = 'div';

    public function __construct()
    {
        parent::__construct();
    }
}

class CMap extends CElement
{
	const TAG = 'map';

    protected $name;

    public function __construct()
    {
        parent::__construct();
        // TODO: chiamare addAccepted per %block e Area
    }
}

class CForm extends CElement
{
	const TAG = 'form';

    protected $action;
    protected $method;
    protected $enctype;
    protected $accept;
    protected $name;
    protected $onsubmit;
    protected $onreset;
    protected $accept_charset;

    public function __construct()
    {
        parent::__construct();
        // TODO: chiamare addAccepted per %block, Script
        $this->addRejected('CForm');
    }
}

class CLink extends CEmptyElement
{
	const TAG = 'link';

    protected $charset;
    protected $href;
    protected $hreflang;
    protected $type;
    protected $rel;
    protected $rev;
    protected $media;

    public function __construct()
    {
        parent::__construct();
    }
}

class CImg extends CEmptyElement
{
	const TAG = 'img';

    protected $src;
    protected $alt;
    protected $longdesc;
    protected $name;
    protected $height;
    protected $width;
    protected $usemap;
    protected $ismap;

    public function __construct()
    {
        parent::__construct();
    }
}

class CFrame extends CFrameElement
{
	const TAG = 'frame';

	public function __construct() {
		parent::__construct();
	}
}

class CIFrame extends CFrameElement
{
	const TAG = 'iframe';

    protected $align;
    protected $height;
    protected $width;

    public function __construct() {
		parent::__construct();
    }
}

abstract class  CTableCellElement extends CAlignableElement
{
    protected $abbr;
    protected $axis;
    protected $header;
    protected $scope;
    protected $rowspan;
    protected $colspan;
}

abstract class CATFElement extends CFocusableElement
{
    protected $accesskey;
    protected $tabindex;

    public function __construct()
    {
        parent::__construct();
    }
}

abstract class CTFElement extends CFocusableElement
{
    protected $tabindex;
}

abstract class CAFElement extends CFocusableElement
{
    protected $accesskey;
}

abstract class CATFEmptyElement extends CFocusableEmptyElement
{
    protected $accesskey;
    protected $tabindex;


}

class COptgroup extends CSelectElement
{

	const TAG = 'optgroup';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('COption');
    }
}

class COption extends CSelectElement
{
	const TAG = 'option';

	protected $disabled;
    protected $selected;
    protected $value;

    public function __construct()
    {
        parent::__construct();
    }
}

class CTHead extends CAlignableElement
{
	const TAG = 'thead';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CTr');
    }
}

class CTFoot extends CAlignableElement
{
	const TAG = 'tfoot';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CTr');
    }
}

class CTBody extends CAlignableElement
{
	const TAG = 'tbody';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CTr');
    }
}

class CColgroup extends CAlignableElement
{
	const TAG = 'colgroup';

    protected $span;
    protected $width;

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CCol');
    }
}

class CTr extends CAlignableElement
{
	const TAG = 'tr';

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('CTh');
        $this->addAccepted('CTd');
    }
}

class CLegend extends CAccesskeyElement
{
	const TAG = 'legend';

    public function __construct()
    {
        parent::__construct();
    }
}

class CTObject extends CTabindexElement
{
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

    public function __construct()
    {
        parent::__construct();
    }
}

class CCol extends CAlignableEmptyElement
{
    protected $span;
    protected $width;

    public function __construct()
    {
        parent::__construct();

    }
}

abstract class CInputElement extends CATFEmptyElement
{
	const TAG = 'input';

    protected $name;
    protected $type;
    protected $disabled;
    protected $onselect;
    protected $size;
    protected $usemap;
    protected $ismap;
    protected $src;
    protected $alt;
    protected $onchange;
    protected $value;

}

class CTd extends CTableCellElement
{
	const TAG = 'td';

    public function __construct()
    {
        parent::__construct();
    }
}

class CTh extends CTableCellElement
{
	const TAG = 'th';

	public function __construct()
    {
        parent::__construct();
    }
}

class CA extends CATFElement
{
	const TAG = 'a';

    protected $charset;
    protected $type;
    protected $name;
    protected $href;
    protected $hreflang;
    protected $rel;
    protected $rev;
    protected $shape;
    protected $coords;
    protected $target;

    public function __construct()
    {
        parent::__construct();
        $this->addRejected('CA');
    }
}

class CTextarea extends CATFElement
{
	const TAG = 'textarea';

    protected $name;
    protected $cols;
    protected $rows;
    protected $disabled;
    protected $readonly;
    protected $onselect;
    protected $onchange;

    public function __construct()
    {
        parent::__construct();

    }
}

class CButton extends CATFElement
{
	const TAG = 'button';

    protected $name;
    protected $value;
    protected $type;
    protected $disabled;

    public function __construct()
    {
        parent::__construct();
        $this->addRejected('CA');
        $this->addRejected('CInput');
        $this->addRejected('CSelect');
        $this->addRejected('CTextarea');
        $this->addRejected('CLabel');
        $this->addRejected('CButton');
        $this->addRejected('CForm');
        $this->addRejected('CFieldset');
    }
}

class CSelect extends CTFElement
{
	const TAG = 'select';

    protected $size;
    protected $name;
    protected $multiple;
    protected $disabled;
    protected $onchange;

    public function __construct()
    {
        parent::__construct();
        $this->addAccepted('COptgroup');
        $this->addAccepted('COption');
    }
}

class CLabel extends CAFElement
{
	const TAG = 'label';

    protected $for;

    function __construct()
    {
        parent::__construct();
        $this->addRejected('CLabel');
    }
}

class CArea extends CATFEmptyElement
{
	const TAG = 'area';

    protected $shape;
    protected $coords;
    protected $href;
    protected $nohref;
    protected $alt;

    public function __construct()
    {
        parent::__construct();
    }
}

abstract class CCheckableInput extends CInputElement
{
    protected $checked;
    protected $value;
}

abstract class CTextInput extends CInputElement
{
    protected $maxlength;
}

class CFileInput extends CInputElement
{
    protected $accept;

    public function __construct()
    {
        $this->setAttribute('type', 'file');
    }
}

class CImageInput extends CInputElement
{
    public function __construct()
    {
        parent::__construct();
    }
}

class CSubmitInput extends CInputElement
{
    public function __construct()
    {
        $this->setAttribute('type', 'submit');
    }
}

class CResetInput extends CInputElement
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('type', 'reset');
    }
}

class CButtonInput extends CInputElement
{
    public function __construct()
    {
       $this->setAttribute('type', 'button');
    }
}

class CHiddenInput extends CInputElement
{
    public function __construct()
    {
        $this->setAttribute('type', 'hidden');
    }
}

abstract class CReadonlyTextInput extends CTextInput
{
    protected $readonly;
}

class CCheckbox extends CCheckableInput
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('type','checkbox');
    }
}

class CRadio extends CCheckableInput
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('type','radio');

    }
}

class CInputText extends CReadonlyTextInput
{
    public function __construct()
    {
        $this->setAttribute('type','text');
    }
}

class CInputPassword extends CReadonlyTextInput
{
    public function __construct()
    {
        $this->setAttribute('type','password');
    }
}
