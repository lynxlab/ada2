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

		$toggleDIV = \CDOMElement::create('div','class:ui toggle checkbox');
		$checkBox = \CDOMElement::create('checkbox','id:mandatory,type:checkbox,name:mandatory,value:1');
		if ($policy->getMandatory()) {
			$checkBox->setAttribute('checked', 'checked');
		}
		$toggleDIV->addChild($checkBox);
		$label = \CDOMElement::create('label','for:mandatory');
		$label->addChild(new \CText('Accettazione obbligatoria'));
		$toggleDIV->addChild($label);

		$this->addCDOM($toggleDIV);

		$this->addTextArea('content', translateFN('Testo policy'))->setValidator(\FormValidator::MULTILINE_TEXT_VALIDATOR)->setRequired()->withData($policy->getContent());
		$this->addHidden('privacy_content_id')->withData($policy->getPrivacy_content_id());

	}
}
