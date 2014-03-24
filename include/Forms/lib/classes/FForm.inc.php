<?php
/**
 * FForm.inc.php file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'FormControl.inc.php';
require_once 'FormValidator.inc.php';
/**
 * Provides simple form creation methods.
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
abstract class FForm
{
    public function __construct() {
        $this->_action = '';
        $this->_method = 'POST';
        $this->_enctype = '';
        $this->_accept = '';
        $this->_name = '';
        $this->_onSubmit = '';
        $this->_onReset = '';
        $this->_acceptCharset = '';
        $this->_id = '';
        $this->_controls = array();
    }
    /**
     * Given a Request object, uses its contents to fill the controls in the
     * form.
     *
     * @param Request $request
     */
    public final function fillWithRequestData($request) {
        foreach($this->_controls as $control) {
            $control->withData($request->getArgument($control->getId()));
        }
    }
    /**
     * Fills the controls in the form with contents of $_POST.
     */
    public final function fillWithPostData() {
    		$this->fillWithArrayData($_POST);
//         foreach($this->_controls as $control) {
//             if(isset($_POST[$control->getId()]) &&!($control instanceof FCFieldset) ) {
//                 $control->withData($_POST[$control->getId()]);
//             }
//             else if ($control instanceof FCFieldset)
//             {            	
//             	foreach ($control->getControls() as $field)
//             	{            		
//             		if (isset($_POST[$field->getId()]))
//             		{
//             			$field->withData($_POST[$field->getId()]);
//             		}
//             	}
//             }
//         }
    }
    /**
     * Fills the controls in the form with contents of the given array.
     */
    public final function fillWithArrayData($formData = array()) {
        foreach($this->_controls as $control) {
            if(isset($formData[$control->getId()]) && (!($control instanceof FCFieldset)))  
            {
                $control->withData($formData[$control->getId()]);
            }
            else if ($control instanceof FCFieldset)
            {
            	foreach ($control->getControls() as $field)
            	{
            		if (isset($formData[$field->getId()]))
            		{
            			$field->withData($formData[$field->getId()]);
            		}
            	}
            	
            }
        }
    }
    
    /**
     * Iterates over each control in the form and uses FormValidator to validate
     * it. If all the controls in the form are valid, the form is valid.
     *
     * @return boolean
     */
    public final function isValid() {
        $isValid = TRUE;
        $validator = new FormValidator();
        foreach($this->_controls as $control) {
            if($control->isRequired()) {
                if(!$validator->validate($control)) {
                    $control->setIsMissing();
                    $isValid = FALSE;
                }
            }
        }
        return $isValid;
    }

    public function toArray() {
        $formAsArray = array();
        foreach($this->_controls as $control) {
            if (!$control instanceof FCFieldset) {
                $formAsArray[$control->getId()] = $control->getData();
                
            } elseif ($control instanceof FCFieldset) {
                foreach($control->getControls() as $field) {
                    $formAsArray[$field->getId()] = $field->getData();
                }
            }
                
        }
        return $formAsArray;
    }

    protected function setCustomJavascript($js,$append = true) {
		if ($append) {
			$this->_customJavascript .= "\n".$js;
		}
		else {
			$this->_customJavascript = $js;
		}
    }

    protected function setAction($action) {
        $this->_action = $action;
    }

    protected function setMethod($method) {
        $this->_method = $method;
    }

    protected function setEncType($encType) {
        $this->_enctype = $encType;
    }

    protected function setAccept($accept) {
        $this->_accept = $accept;
    }

    protected function setName($name) {
        $this->_name = $name;
    }

    protected function setOnSubmit($onSubmit) {
        $this->_onSubmit = $onSubmit;
    }

    protected function setOnReset($onReset) {
        $this->_onReset = $onReset;
    }

    protected function setAcceptCharset($acceptCharset) {
        $this->_acceptCharset = $acceptCharset;
    }

    protected function setSubmitValue($submitValue) {
        $this->_submitValue = $submitValue;
    }
    
    /**
     * @author giorgio 01/lug/2013
     * 
     * getter for the form name
     * @return string
     */
    public function getName() {
    	return $this->_name;
    }
    /*
     * Form controls creational methods
     */

    /**
     * Adds the given FormControl and returns it so that it is possible to call
     * FormControl's methods after its creation.
     *
     * @param FormControl $control
     * @return FormControl
     */
    public function addControl(FormControl $control) {
        $this->_controls[] = $control;
        return $control;
    }
    /**
     * Adds a new text input.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addTextInput($id, $label) {
        $control = FormControl::create(FormControl::INPUT_TEXT, $id, $label);
        return $this->addControl($control);

    }
    /**
     * Adds a new password input.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addPasswordInput($id, $label) {
        $control = FormControl::create(FormControl::INPUT_PASSWORD, $id, $label);
        return $this->addControl($control);
    }
    /**
     * Adds a new file input.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addFileInput($id, $label) {
        $control = FormControl::create(FormControl::INPUT_FILE, $id, $label);
        return $this->addControl($control);

    }
    /**
     * Adds the given checkboxes.
     *
     * @param string $id
     * @param string $label
     * @param array $data value and label for each checkbox
     * @param mixed $checked an array of values or a single value
     * @return FormControl
     */
    protected final function addCheckboxes($id,$label, $data, $checked) {
		$checkboxButtons = array();
        if(is_array($data) && count($data) > 0) {
            foreach($data as $value => $text) {
                $control = FormControl::create(FormControl::INPUT_CHECKBOX, $id, $text)
                         ->withData($value);
                if((!is_array($checked) && $value == $checked) ||
                   (is_array($checked) && in_array($value, $checked))) {
                    $control->setSelected();
                }
                //$this->addControl($control);
				$checkboxButtons[] = $control;
            }
        }
        return $this->addControl(FormControl::create(FormControl::FIELDSET, $id, $label))
					->withData($checkboxButtons);
    }
    /**
     * Adds the given radio buttons.
     *
     * @param string $id
     * @param string $label
     * @param array $data value and label for each radio button
     * @param string $checked the value of the radio button to be checked
     * @return FormControl
     */
    protected final function addRadios($id,$label, $data, $checked) {
        $radioButtons = array();
        if(is_array($data) && count($data) > 0) {
            foreach($data as $value => $text) {
                $control = FormControl::create(FormControl::INPUT_RADIO, $id, $text)
                         ->withData($value);
                //$this->addControl($control);
                $radioButtons[] = $control;
                if($value == $checked) {
                    $control->setSelected();
                }
            }
        }
        return $this->addControl(FormControl::create(FormControl::FIELDSET, $id, $label))
					->withData($radioButtons);
    }
    /**
     * Adds the given select.
     *
     * @param string $id
     * @param string $label
     * @param array $data value and label for each select option
     * @param string $checked the value of the option to be checked
     * @return FormControl
     */
    protected final function addSelect($id,$label, $data, $checked) {
        if(is_array($data) && count($data) > 0) {
            $control = FormControl::create(FormControl::SELECT, $id, $label)
                     ->withData($data, $checked);
            return $this->addControl($control);
        }
    }
    /**
     * Adds a new textarea.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addTextarea($id, $label) {
        $control = FormControl::create(FormControl::TEXTAREA, $id, $label);
        return $this->addControl($control);
    }
    /**
     * Adds a new button.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */    
     protected final function addButton($id, $label) {
    	$control = FormControl::create(FormControl::INPUT_BUTTON, $id, $label);
    	return $this->addControl($control);
    }
    /**
     * Adds a new hidden input.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addHidden($id) {
        $control = FormControl::create(FormControl::INPUT_HIDDEN, $id, '');
        return $this->addControl($control);
    }
    /**
     * Adds a new fieldset.
     *
     * @param string $id
     * @param string $label
     * @return FormControl
     */
    protected final function addFieldset($label,$id='') {
        $control = FormControl::create(FormControl::FIELDSET, $id, $label);
        return $this->addControl($control);
    }
    protected final function addSubmit($id) {
    }
    protected final function addReset($id) {
    }

    /*
     * Rendering
     */
 /*
  action      %URI;          #REQUIRED -- server-side form handler --
  method      (GET|POST)     GET       -- HTTP method used to submit the form--
  enctype     %ContentType;  "application/x-www-form-urlencoded"
  accept      %ContentTypes; #IMPLIED  -- list of MIME types for file upload --
  name        CDATA          #IMPLIED  -- name of form for scripting --
  onsubmit    %Script;       #IMPLIED  -- the form was submitted --
  onreset     %Script;       #IMPLIED  -- the form was reset --
  accept-charset %Charsets;  #IMPLIED  -- list of supported charsets --
*/


    /**
     * Renders the form and its controls.
     *
     * @return string the html to be rendered
     */
    public function render() {
        $html = '<div class="fform '.FormControl::DEFAULT_CLASS.'">
			<form'.$this->formId().$this->formAction().$this->formMethod().$this->formEncType().$this->formAccept().$this->formName().$this->formOnSubmit().$this->formOnReset().$this->formAcceptCharset().'>
  <fieldset class="'.FormControl::DEFAULT_CLASS.'">
    <ol class="'.FormControl::DEFAULT_CLASS.'">';

        foreach ($this->_controls as $control) {
			$hidden = '';
            if($control->isHidden()) {
                $hidden =' hidden';
            }
            $html .= '<li class="'.FormControl::DEFAULT_CLASS.$hidden.'">'.$control->render().'</li>';
        }
        $html .= '
   </ol>
   </fieldset>
   <div id="error_form_'.$this->_name.'" class="hide_error form">
		'.translateFN('Sono presenti errori nel form, si prega di correggere le voci evidenziate in rosso').'
   </div>
   <p class="'.FormControl::DEFAULT_CLASS.' submit"><input value="'.translateFN('Invia').'" class="'.FormControl::DEFAULT_CLASS.'" type="submit" id="submit_'.$this->_name.'" name="submit_'.$this->_name. '" onClick="return validate_'.$this->_name.'();"'.$this->submitValue().'/></p>
</form>
</div>';

		$html.= $this->addJsValidation()."\n";
		$html.= $this->addCustomJavascript()."\n";

        return $html;
    }


    public final function getHtml() {
        return $this->render();
    }

	/**
	 * Adds the custom javascript specified by user
	 *
	 * @return string the custom javascript specified by user
	 */
	private function addCustomJavascript() {
		if (!is_null($this->_customJavascript)) {
			return '<script type="text/javascript">
				'.$this->_customJavascript.'
			</script>';
		}
	}

    /**
     * Adds the javascript used to validate the form.
     *
     * @return string the javascript used to validate the form.
     */
    private function addJsValidation() {
        $validator = new FormValidator();
		$jsFields = array();
		$jsRegexps = array();


		foreach ($this->_controls as $control) {
			$v = $control->getValidator();
			if (!is_null($v)) {
				if (! $control instanceof FCFieldset) {
					$jsFields[] = $control->getId();
					$jsRegexps[] = $validator->getRegexpForValidator($control->getValidator());
				}
				else {
					foreach ($control->getControls() as $field) {
						$vField = $field->getValidator();
						if ($field->isRequired()) { 
							$jsFields[] = $field->getId();
							$jsRegexps[] = $validator->getRegexpForValidator($vField);
						}
					}
				}
// 				$jsRegexps[] = $validator->getRegexpForValidator($control->getValidator());
// 				$jsRegexps[] = $validator->getRegexpForValidator($v);
			}
        }
        $html = '<script type="text/javascript">
					var validateContentFields_'.$this->_name.' = new Array("'.implode('","',$jsFields).'");
					var validateContentRegexps_'.$this->_name.' = new Array('.implode(',',$jsRegexps).');
					function validate_'.$this->_name.'() {
						return validateContent(validateContentFields_'.$this->_name.',validateContentRegexps_'.$this->_name.' , "'.$this->_name.'");
					}
				</script>';
		
        return $html;
    }
    /**
     * Returns the id attribute for the form element.
     *
     * @return string
     */
    private function formId() {
        if($this->_id != '') {
            return ' id="' . $this->_id . '"';
        }
        return '';
    }
    /**
     * Returns the action attribute for the form element.
     *
     * @return string
     */
    private function formAction() {
        if($this->_action != '') {
            return ' action="' . $this->_action . '"';
        }
        return '';
    }
    /**
     * Returns the method attribute for the form element.
     *
     * @return string
     */
    private function formMethod() {
        if($this->_method != '') {
            return ' method="' . $this->_method . '"';
        }
        return ' method="POST"';
    }
    /**
     * Returns the enctype attribute for the form element.
     *
     * @return string
     */
    private function formEncType() {
        if($this->_enctype != '') {
            return ' enctype="' . $this->_enctype . '"';
        }
        return '';
    }

    /**
     * Returns the accept attribute for the form element.
     *
     * @return string
     */
    private function formAccept() {
        if($this->_accept != '') {
            return ' accept="' . $this->_accept . '"';
        }
        return '';
    }

    /**
     * Returns the name attribute for the form element.
     *
     * @return string
     */
    private function formName() {
        if($this->_name != '') {
            return ' name="' . $this->_name . '"';
        }
        return '';
    }

    /**
     * Returns the onsubmit attribute for the form element.
     *
     * @return string
     */
    private function formOnSubmit() {
        if($this->_onSubmit != '') {
            return ' onsubmit="' . $this->_onSubmit . '"';
        }
        return '';
    }

    /**
     * Returns the onreset attribute for the form element.
     *
     * @return string
     */
    private function formOnReset() {
        if($this->_onReset != '') {
            return ' onreset="' . $this->_onReset . '"';
        }
        return '';
    }

    /**
     * Returns the accept-charset attribute for the form element.
     *
     * @return string
     */
    private function formAcceptCharset() {
        if($this->_acceptCharset != '') {
            return ' accept-charset="' . $this->_acceptCharset . '"';
        }
        return '';
    }

    /**
     * Returns the submit value
     *
     * @return string
     */
    private function submitValue() {
        if($this->_submitValue != '') {
            return ' value="' . $this->_submitValue . '"';
        }
        return '';
    }

    /**
     *
     * @var string
     */
    private $_id;
    /**
     *
     * @var string
     */
    private $_action;
    /**
     *
     * @var string
     */
    private $_method;
    /**
     *
     * @var string
     */
    private $_enctype;
    /**
     *
     * @var string
     */
    private $_accept;
    /**
     *
     * @var string
     */
    private $_name;
    /**
     *
     * @var string
     */
    private $_onSubmit;
    /**
     *
     * @var string
     */
    private $_onReset;
    /**
     *
     * @var string
     */
    private $_acceptCharset;
    /**
     *
     * @var array
     */
    private $_controls;
    /**
     *
     * @var string
     */
    private $_submitValue = '';
    /**
     *
     * @var string
     */
    private $_customJavascript = null;
}