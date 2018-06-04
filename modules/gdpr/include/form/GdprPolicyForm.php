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
 * Class for the gpdr privacy policy form
 *
 * @author giorgio
 */
class GdprPolicyForm extends GdprAbstractForm {

	public function __construct($formName=null, $action=null, GdprPolicy $policy) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);

		$this->addTextInput('title', translateFN('Titolo policy'))->setValidator(\FormValidator::NOT_EMPTY_STRING_VALIDATOR)->setRequired()->withData($policy->getTitle());

		$cbContainer = \CDOMElement::create('div','class:checkbox container');
		$toggleDIV = \CDOMElement::create('div','class:ui toggle checkbox');
		$checkBox = \CDOMElement::create('checkbox','id:mandatory,type:checkbox,name:mandatory,value:1');
		if ($policy->getMandatory()) {
			$checkBox->setAttribute('checked', 'checked');
		}
		$toggleDIV->addChild($checkBox);
		$label = \CDOMElement::create('label','for:mandatory');
		$label->addChild(new \CText(translateFN('Accettazione obbligatoria')));
		$toggleDIV->addChild($label);
		$cbContainer->addChild($toggleDIV);

		$toggleDIV = \CDOMElement::create('div','class:ui toggle checkbox');
		$checkBox = \CDOMElement::create('checkbox','id:isPublished,type:checkbox,name:isPublished,value:1');
		if ($policy->getIsPublished()) {
			$checkBox->setAttribute('checked', 'checked');
		}
		$toggleDIV->addChild($checkBox);
		$label = \CDOMElement::create('label','for:isPublished');
		$label->addChild(new \CText(translateFN('Pubblicato')));
		$toggleDIV->addChild($label);
		$cbContainer->addChild($toggleDIV);

		if (!is_null($policy->getPolicy_content_id())) {
			$toggleDIV = \CDOMElement::create('div','class:ui toggle checkbox');
			$checkBox = \CDOMElement::create('checkbox','id:newVersion,type:checkbox,name:newVersion,value:1');
			$toggleDIV->addChild($checkBox);
			$label = \CDOMElement::create('label','for:newVersion');
			$label->addChild(new \CText(translateFN('Nuova versione')));
			$toggleDIV->addChild($label);
			$cbContainer->addChild($toggleDIV);
		}
		$this->addCDOM($cbContainer);

		$this->addTextArea('content', translateFN('Testo policy'))->setValidator(\FormValidator::MULTILINE_TEXT_VALIDATOR)->setRequired()->withData($policy->getContent());

		if (!is_null($policy->getVersion())) {
			$text = sprintf(translateFN("Versione %d del %s alle ore %s"), $policy->getVersion(), ts2dFN($policy->getLastEditTS()), ts2tmFN($policy->getLastEditTS()));
			$vDIV = \CDOMElement::create('div','class:version container');
			$vSPAN = \CDOMElement::create('span','class:version content');
			$vSPAN->addChild(new \CText($text));
			$vDIV->addChild($vSPAN);
			$this->addCDOM($vDIV);
		}

		$this->addHidden('policy_content_id')->withData($policy->getPolicy_content_id());

	}
}
