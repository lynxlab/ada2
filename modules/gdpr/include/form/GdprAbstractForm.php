<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

/**
 * class for handling all application forms
 *
 * @author giorgio
 *
 */
require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

abstract class GdprAbstractForm extends \FForm {

	private $withSubmit;
	private $isReadOnly;

	protected $doNotSemanticUI = false;
	protected $maxlength = 255;

	public function __construct($formName=null, $action=null) {
		parent::__construct();
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);

		$this->withSubmit = false;
	}

	public function addCDOM(\CBaseAttributesElement $element) {
		$this->_controls[] = $element;
		return $this;
	}

	public function getHtml() {
		if (strlen($this->getName())<=0) $this->setName($this->_id);
		if ($this->withSubmit === false) {
			$this->removeSubmit();
		}
		return parent::getHtml();
	}

	public function withSubmit() {
		$this->withSubmit = true;
		return $this;
	}

	public function toSemanticUI() {
		if (!$this->doNotSemanticUI) {
			$this->setCustomJavascript('
					$j("#'.$this->_id.' select").addClass("ui form input");
					$j("#'.$this->_id.'").parents("div.fform").addClass("ui");
					$j("#error_form_'.$this->_id.'").addClass("ui red message");', true);
			if ($this->withSubmit) {
				$this->setCustomJavascript('
					$j("#submit_'.$this->_id.'").addClass("ui button");',true);
			}
		}
		return $this;
	}

	public function addJSDataProperty($key, $value) {
		if (is_string($value)) $value = '"'.$value.'"';
		else if (is_bool($value)) $value = ($value ? 'true' : 'false');
		$this->setCustomJavascript('$j("#'.$this->_id.'").data("'.$key.'",'.$value.');', true);
	}

	public function withUIClassOnLi() {
		$this->setCustomJavascript('$j("#'.$this->_id.' ol.form>li.form").addClass("ui field");', true);
		return $this;
	}

	private function removeSubmit() {
		$this->setCustomJavascript('$j("#'.$this->_id.' >p.submit").remove();');
	}

	/**
	 * Get isReadOnly
	 *
	 * @return boolean
	 */
	public function getIsReadOnly() {
		return $this->isReadOnly;
	}

	/**
	 * Set isReadOnly
	 * @param boolean $isReadOnly
	 *
	 * @return GdprAbstractForm
	 */
	protected function setIsReadOnly($isReadOnly) {
		$this->isReadOnly = $isReadOnly;
		return $this;
	}

} // class ends here
