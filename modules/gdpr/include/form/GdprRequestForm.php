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
 * Class for the gpdr request form
 *
 * @author giorgio
 */
class GdprRequestForm extends GdprAbstractForm {

	public function __construct($formName=null, $action=null, $selectOptions = array()) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);

		if (count($selectOptions)>0) {
			$select = \CDOMElement::create('span','id:selectRequestTypeContainer');

			$label = \CDOMElement::create('label', 'for:requestType');
			$label->addChild(new \CText('Tipo di richiesta'));
			$select->addChild($label);

			$selectObj = \CDOMElement::create('select','id:requestType,name:requestType');
			foreach ($selectOptions as $reqType) {
				$opt = \CDOMElement::create('option','value:'.$reqType->getId());
				$opt->addChild(new \CText(ucfirst(strtolower(translateFN($reqType->getDescription())))));
				if (!is_null($reqType->getExtra()) && count($reqType->getExtra())>0) {
					foreach ($reqType->getExtra() as $key => $val) {
						$opt->setAttribute('data-'.$key, $val);
					}
				}
				$selectObj->addChild($opt);
			}
			$select->addChild($selectObj);
			$this->addCDOM($select);
		}

		$this->addTextArea('requestContent', translateFN('Testo richiesta'));
		$this->addHidden('generatedBy');
		$this->addHidden('selfOpened');
		$this->addHidden('dontConfirm');
		foreach (array_keys(AMAGdprDataHandler::getObjectClasses()) as $key) {
			$this->addHidden($key);
		}

	}
}
