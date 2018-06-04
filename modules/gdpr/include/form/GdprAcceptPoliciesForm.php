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
 * Class for the gpdr accept policies form
 *
 * @author giorgio
 */
class GdprAcceptPoliciesForm extends GdprAbstractForm {

	public function __construct($formName=null, $action=null, $dataAr = array()) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);
		self::addPolicies($this, $dataAr);
		$this->withSaveButton(translateFN('Salva'));
	}

	/**
	 * Add policies contents and radio buttons to the passed form
	 *
	 * @param \FForm $formObj
	 * @param array $dataAr
	 * @return \FForm
	 */
	public static function addPolicies($formObj, $dataAr) {
		if (is_array($dataAr) && count($dataAr)>0) {
			if (array_key_exists('userId', $dataAr)) $formObj->addHidden('userId')->withData($dataAr['userId']);
			if (!array_key_exists('userAccepted', $dataAr)) $dataAr['userAccepted'] = array();
			$isRegistration = array_key_exists('isRegistration', $dataAr) && $dataAr['isRegistration'] === true;
			if (array_key_exists('policies', $dataAr)) {
				$firstElClass = 'active';
				/** @var GdprPolicy $policy */
				$accordion = \CDOMElement::create('div', 'class:ui fluid accordion');
				if (array_key_exists('extraclass', $dataAr)) {
					$accordion->setAttribute('class', $accordion->getAttribute('class').' '.$dataAr['extraclass']);
				}
				foreach ($dataAr['policies'] as $i=>$policy) {
					$title = \CDOMElement::create('div', 'class:'.(($i==0) ? $firstElClass.' ':'').'title');
					$title->addChild(\CDOMElement::create('i','class:dropdown icon'));
					// policy title, left side
					$spanTitle = \CDOMElement::create('span','class:policy title');
					$spanTitle->addChild(new \CText($policy->getTitle()));
					$title->addChild($spanTitle);
					// policy accepted label, right side
					$labelColor = 'black';
					$labelTitle = '';
					if ($policy->getMandatory()) {
						if (array_key_exists($policy->getPolicy_content_id(), $dataAr['userAccepted'])) {
							$labelTitle = sprintf(translateFN('Accettata in versione %d il %s, %s'),
								$dataAr['userAccepted'][$policy->getPolicy_content_id()]['acceptedVersion'],
								ts2dFN($dataAr['userAccepted'][$policy->getPolicy_content_id()]['acceptedTS']),
								ts2tmFN($dataAr['userAccepted'][$policy->getPolicy_content_id()]['acceptedTS'])
							);
							if ($dataAr['userAccepted'][$policy->getPolicy_content_id()]['acceptedVersion'] == $policy->getVersion()) {
								$status = "ACCETTATA";
								$labelColor = 'green';
							} else {
								$status = "NUOVA VERSIONE";
								$labelColor = 'blue';
							}
						} else {
							$status = $isRegistration ? "PRESTARE CONSENSO" : "NON ACCETTATA";
							$labelColor = 'red';
						}
					} else if(!$isRegistration) {
						$status = "FACOLTATIVA";
						$labelColor = 'orange';
					}
					if (isset($status)) {
						$spanTitle = \CDOMElement::create('span','class:policy status ui '.$labelColor.' label');
						if (isset($labelTitle) && strlen($labelTitle)>0) {
							$spanTitle->setAttribute('title', $labelTitle);
						}
						$spanTitle->addChild(new \CText(translateFN($status)));
						unset($status);
						$title->addChild($spanTitle);
					}
					// policy content
					$content = \CDOMElement::create('div','class:'.(($i==0) ? $firstElClass.' ':'').'content');
					$textdiv = \CDOMElement::create('div','class:policy text');
					$textdiv->addChild(new \CText($policy->getContent()));
					$content->addChild($textdiv);

					// accept and deny radio buttons
					if ($policy->getMandatory()) {
						$spanTitle->setAttribute('data-mandatory-policy', '1');
						$fieldsContainer = \CDOMElement::create('div','class:inline fields');
						$radios = array(
							1 => array('type' => 'accept', 'label' => 'Presto il consenso'),
							0 => array('type' => 'deny', 'label' => 'Nego il consenso')
						);
						foreach ($radios as $value => $rData) {
							$radioContainer = \CDOMElement::create('div','class:field');
							$radio = \CDOMElement::create('radio','value:'.$value.',name:acceptPolicy['.$policy->getPolicy_content_id().'],id:'.$rData['type'].'_'.$policy->getPolicy_content_id());
							$label = \CDOMElement::create('label','class:'.$rData['type'].',for:'.$rData['type'].'_'.$policy->getPolicy_content_id());
							$label->addChild(new \CText(translateFN($rData['label'])));
							$radioContainer->addChild($radio);
							$radioContainer->addChild($label);
							$fieldsContainer->addChild($radioContainer);
						}
						$content->addChild($fieldsContainer);
					}
					$accordion->addChild($title);
					$accordion->addChild($content);
				}
				$formObj->addCDOM($accordion);

				$alert = \CDOMElement::create('div','class:ui small modal,id:acceptPoliciesMSG');
				$aHeader = \CDOMElement::create('div','class:header');
				$aHeader->addChild(new \CText(translateFN('Attenzione')));
				$aContent = \CDOMElement::create('div','class:content');
				$aContent->addChild(new \CText('<i class="large warning icon"></i>'.translateFN('Per '.($isRegistration ? 'registrarsi': 'continuare' ).', è necessario prestare il consenso a tutte le politiche di gestione dei dati personali')));

				$aActions = \CDOMElement::create('div','class:actions');
				$button = \CDOMElement::create('div','class:ui red button');
				$button->addChild(new \CText(translateFN('OK')));
				$aActions->addChild($button);

				$alert->addChild($aHeader);
				$alert->addChild($aContent);
				$alert->addChild($aActions);

				$formObj->addCDOM($alert);
			}
		}
		return $formObj;
	}

	/**
	 * Adds a save button with the passed label
	 *
	 * @param string $label
	 * @return \Lynxlab\ADA\Module\GDPR\GdprAcceptPoliciesForm
	 */
	public function withSaveButton($label) {
		// save button
		$saveBtn = $this->addButton('savePolicies', $label);
		$saveBtn->setAttribute('class', 'ui large green button');
		return $this;
	}
}
