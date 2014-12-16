<?php
/**
 * FormControl file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of FormControl
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
abstract class FormControl
{
    public function __construct($controlType, $controlId, $labelText) {
        $this->_controlType = $controlType;
        $this->_controlId = $controlId;
        $this->_labelText = $labelText;
        $this->_controlData = NULL;
        $this->_selected = FALSE;
        $this->_isRequired = FALSE;
        $this->_isMissing = FALSE;
		$this->_hidden = FALSE;

        $this->_validator = FormValidator::DEFAULT_VALIDATOR;
    }
    /**
     * Creates a new FormControl object.
     *
     * @param string $controlType
     * @param string $controlId
     * @param string $labelText
     * @return FormControl
     */
    public static function create($controlType, $controlId, $labelText) {
        switch($controlType) {
            case self::INPUT_CHECKBOX:
            case self::INPUT_RADIO:
                return new FCInputCheckable($controlType,$controlId,$labelText);
            case self::INPUT_HIDDEN:
                return new FCInputHidden($controlType,$controlId,$labelText);
            case self::INPUT_FILE:
            case self::INPUT_TEXT:
            case self::INPUT_PASSWORD:
                return new FCInput($controlType, $controlId, $labelText);
            case self::SELECT:
                return new FCSelect($controlType, $controlId, $labelText);
            case self::OPTION:
                return new FCOption($controlType, $controlId, $labelText);
            case self::TEXTAREA:
                return new FCTextarea($controlType, $controlId, $labelText);
            case self::FIELDSET:
                return new FCFieldset($controlType, $controlId, $labelText);
            case self::INPUT_BUTTON:
            	return new FCButton($controlType, $controlId, $labelText);
            case self::INPUT_IMAGE:            	
            default:
                return new FCNullControl($controlType, $controlId, $labelText);
        }
    }
    /**
     * Returns the label for this form control.
     * 
     * @return string the html for the control's label
     */
    protected function label() {
        $html = '<label for="'.$this->_controlId.'" id="l_'.$this->_controlId .'" class="'.self::DEFAULT_CLASS;
        if($this->_isMissing) {
            $html .= ' error';
        }
        $html .='" >'. $this->_labelText;
        if ($this->_isRequired) {
            $html .= ' (*)';
        }
        $html .= '</label>';
        return $html;
    }

    /**
     * Returns the html for this attributes form control.
     *
     * @return string the html for the control's attributes
     */
    protected function renderAttributes() {
        $htmlAttributes = '';
        foreach ($this->_attributes as $key => $value) {
            $htmlAttributes .= ' ' . $key . '="' . $value . '"';
        }
        return $htmlAttributes;
    }

    /**
     * Sets the data of this form control to $data.
     * 
     * @param mixed $data
     * @return FormControl
     */
    public function withData($data) {
        $this->_controlData = $data;
        return $this;
    }
    /**
     * Sets this form control as selected.
     *
     * @return FormControl
     */
    public function setSelected() {
        $this->_selected = TRUE;
        return $this;
    }
    public function setNotSelected() {
        $this->_selected = FALSE;
        return $this;
    }

    /**
     * Sets this form control as required.
     *
     * @return FormControl
     */
    public function setHidden() {
        $this->_hidden = TRUE;
        return $this;
    }

    /**
     * Sets this form control as required.
     * 
     * @return FormControl
     */
    public function setRequired() {
        $this->_isRequired = TRUE;
        return $this;
    }
    /**
     * Sets this form control as missing.
     * @return FormControl
     */
    public function setIsMissing() {
        $this->_isMissing = TRUE;
        return $this;
    }
    /**
     * Sets this form control as attribute.
     * @return FormControl
     */
    public function setAttribute($attribute,$value) {
		if (isset($this->_attributes['class']) && $attribute == 'class') {
			$value = $this->_attributes['class'].' '.$value;
		}
        $this->_attributes[$attribute]=$value;
//        $this->_isMissing = TRUE;
        return $this;
    }
    /**
     * Returns true if this form control is marked as selected.
     * @return boolean
     */
    public function isHidden() {
        return $this->_hidden;
    }
    /**
     * Returns true if this form control is marked as selected.
     * @return boolean
     */
    public function isSelected() {
        return $this->_selected;
    }
    /**
     * Returns true if this form control was marked as required.
     * 
     * @return boolean
     */
    public function isRequired() {
        return $this->_isRequired;
    }
    /**
     * Returns the id of this form control.
     * 
     * @return string
     */
    public function getId() {
        return $this->_controlId;
    }
    /**
     * Returns the data contained in this form control.
     *
     * @return mixed
     */
    public function getData() {
        return $this->_controlData;
    }
    /**
     * Returns the validator assigned to this form control.
     * 
     * @return integer
     */
    public function getValidator() {
        return $this->_validator;
    }
    /**
     * Sets the validator for this form control.
     *
     * @param integer $validator
     * 
     * @return FormControl
     */
    public function setValidator($validator) {
        $this->_validator = $validator;
        return $this;
    }
    /**
     *
     * @var string
     */
    protected $_controlId;
    /**
     *
     * @var string
     */
    protected $_labelText;
    /**
     *
     * @var string
     */
    protected $_controlType;
    /**
     *
     * @var string
     */
    protected $_controlData;
    /**
     *
     * @var boolean
     */
    protected $_selected;
    /**
     *
     * @var boolean
     */
    protected $_hidden;
    /**
     *
     * @var boolean
     */
    protected $_isRequired;
    /**
     *
     * @var boolean
     */
    protected $_isMissing;
    /**
     *
     * @var integer
     */
    protected $_validator;
    /**
     *
     * @var array
     */
    protected $_attributes = array('class'=>self::DEFAULT_CLASS);


    const INPUT_TEXT = 'text';
    const INPUT_PASSWORD = 'password';
    const INPUT_CHECKBOX = 'checkbox';
    const INPUT_RADIO = 'radio';
    const INPUT_SUBMIT = 'submit';
    const INPUT_IMAGE = 'image';
    const INPUT_RESET = 'reset';
    const INPUT_BUTTON = 'button';
    const INPUT_HIDDEN = 'hidden';
    const INPUT_FILE = 'file';
    const SELECT = 'select';
    const OPTION = 'option';
    const TEXTAREA = 'textarea';
    const FIELDSET = 'fieldset';


	const DEFAULT_CLASS = 'form';
}
/**
 *
 */
class FCInput extends FormControl {
    public function render() {
		$this->setAttribute('class', 'input_text');

        $html = '<input type="'.$this->_controlType.'" id="'.$this->_controlId.'" name="'.$this->_controlId.'"';
        if($this->_controlData !== NULL) {
            $html .= ' value="' . $this->_controlData .'"';
        }
        $html .= $this->renderAttributes();
        $html .= ' />
			<div class="'.self::DEFAULT_CLASS.' clear"></div>';
        return $this->label() . $html;
    }
}
/**
 * 
 */
class FCInputHidden extends FormControl {
	public function __construct($controlType, $controlId, $labelText) {
		parent::__construct($controlType, $controlId, $labelText);
		$this->setHidden();
	}

    public function render() {
		$this->setAttribute('class', 'input_hidden');
        $html = '<input type="'.$this->_controlType.'" id="'.$this->_controlId.'" name="'.$this->_controlId.'"';
        if($this->_controlData !== NULL) {
            $html .= ' value="' . $this->_controlData .'"';
        }
        $html .= $this->renderAttributes();
        $html .= ' />
			<div class="'.self::DEFAULT_CLASS.' clear"></div>';

        return $html;
    }
}
/**
 * 
 */
class FCInputCheckable extends FormControl {
    public function render() {
		switch($this->_controlType) {
			default:
			case self::INPUT_CHECKBOX:
				$this->setAttribute('class', 'input_checkbox');
			break;
			case self::INPUT_RADIO:
				$this->setAttribute('class', 'input_radio');
			break;
		}

        $html = '<input type="'.$this->_controlType.'" id="'.$this->_controlId.'" name="'.$this->_controlId.'"';
        //$html = '<input type="'.$this->_controlType.'" name="'.$this->_controlId.'"';
        if($this->_controlData !== NULL) {
            $html .= ' value="' . $this->_controlData .'"';
        }
        if($this->_selected !== FALSE) {
            $html .= ' checked';
        }
        $html .= $this->renderAttributes();
        $html .= ' />'
              . '<label for="'.$this->_controlId.'">'.$this->_labelText.'</label>';
        return $html;
    }
    
    public function withData($data) {
        if (is_null($this->_controlData)) parent::withData($data);
        else if ($this->getData() == $data) $this->setSelected ();
        else $this->setNotSelected ();
        return $this;
    }
}
/**
 * 
 */
class FCSelect extends FormControl {
    public function withData($options, $checked='') {
        if(is_array($this->_options) && count($this->_options) > 0) {
            return $this->setSelectedOption($options);
        }
        if(is_array($options) && count($options) > 0) {            
            foreach($options as $value => $text) {
                $control =  FormControl::create(FormControl::OPTION, '', $text);
                $control->withData($value);
                if($value == $checked) {
                    $control->setSelected();
                }
                $this->_options[] = $control;
            }
        }
        return $this;
    }

    public function getData() {
        if(is_array($this->_options) && count($this->_options) > 0) {
            foreach($this->_options as $control) {
                if($control->isSelected()) {
                    return $control->getData();
                }
            }
        }
    }

    private function setSelectedOption($value) {
        foreach ($this->_options as $control) {
            if($control->isSelected()) {
                $control->setNotSelected();
            }
            if(is_numeric($value) && ((int)$control->getData() == (int)$value)) {
                $control->setSelected();
            }
            else if($control->getData() === $value) {
                $control->setSelected();
            }
        }
        return $this;
    }
    
    public function render() {
        $html = '<select id="' . $this->_controlId .'" name="' . $this->_controlId . '"';
        $html .= $this->renderAttributes();
        $html .=  ' >';
        foreach($this->_options as $option) {
            $html .= $option->render();
        }
        $html .= '</select>
			<div class="'.self::DEFAULT_CLASS.' clear"></div>';
        return $this->label() . $html;
    }

    private $_options = array();
}
/**
 * 
 */
class FCOption extends FormControl {
    public function render() {
        $html = '<option';
		$html .= $this->renderAttributes();
        if($this->_controlData !== NULL) {
            $html .= ' value="' . $this->_controlData .'"';
        }
        if($this->_selected !== FALSE) {
            $html .= ' selected';
        }
        $html .= '>' . $this->_labelText . '</option>';
        return $html;
    }
}
/**
 * 
 */
class FCTextarea extends FormControl {
    public function render() {
        $html = '<textarea id="'.$this->_controlId.'" name="'.$this->_controlId.'"'.$this->renderAttributes().' >'.$this->_controlData.'</textarea><div class="'.self::DEFAULT_CLASS.' clear"></div>';
        return $this->label() . $html;
    }
}
/**
 * 
 */
class FCFieldset extends FormControl {
    public function withData($data) {   	    	
        if(empty ($this->_controls) && is_array($data) && count($data) > 0) {
            $this->_controls = $data;
        } else if(is_array($this->_controls)) {		
            foreach($this->_controls as $control) {
                if($control->getData() === $data) {
                    $control->setSelected();
                } else if($control->isSelected()){
                    $control->setNotSelected();
                } else {
                	$control->withData($data);
                }                
            }
        }
        return $this;
    }
    
    public function getControls()
    {
    	return $this->_controls;
    }

    public function render() {
        $html = $this->label().
				'<fieldset id="'.$this->_controlId.'" class="'.self::DEFAULT_CLASS.'"><ol class="'.self::DEFAULT_CLASS.'">';
				foreach ($this->_controls as $control) {
					$html .= '<li class="'.self::DEFAULT_CLASS.'">' . $control->render() .'</li>';
				}
        $html .= '</ol></fieldset>';
        return $html;
    }

    private $_controls = array();
}

/**
 * class for html button
 * @author giorgio
 */
class FCButton extends FormControl {
	public function render() {
		$html = '<button id="'.$this->_controlId.'" type="button" name="'.$this->_controlId.'"'.$this->renderAttributes().'>'.$this->_labelText.'</button>';
		return $html;
	}
}

/**
 * 
 */
class FCNullControl extends FormControl {
    public function render() {
        return '';
    }
}