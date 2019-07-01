<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Badges;

use Ramsey\Uuid\Uuid;

/**
 * Class for the badge object form
 *
 * @author giorgio
 */
class CourseBadgeForm extends BadgesAbstractForm {

	public function __construct($formName = null, $action = null, array $badges, array $conditionSets, $courseId = null) {

		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}

		$l = \CDOMElement::create('legend','class:ui top attached small header');
		$l->addChild(new \CText('Nuova associazione'));
		$this->addCDOM($l);

		if (!is_null($courseId)) $this->addHidden('id_corso')->withData($courseId);

		$row = \CDOMElement::create('div','class:two fields');
		$this->addCDOM($row);

		$field = \CDOMElement::create('div','class:label field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:badges');
		$lbl->addChild(new \CText(translateFN('Badge').' (*)'));
		$field->addChild($lbl);

		$field = \CDOMElement::create('div','class:label field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:conditionset');
		$lbl->addChild(new \CText(translateFN('Condizione').' (*)'));
		$field->addChild($lbl);

		$row = \CDOMElement::create('div','class:two fields');
		$this->addCDOM($row);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$input = \CDOMElement::create('select','id:badges,name:badge_uuid');
		/** @var Badge $badge */
		foreach ($badges as $badge) {
			$option = \CDOMElement::create('option','value:'.$badge->getUuid());
			$option->addChild(new \CText($badge->getName()));
			$input->addChild($option);
		}
		$field->addChild($input);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$input = \CDOMElement::create('select','id:conditionset,name:id_conditionset');
		foreach ($conditionSets as $conditionSet) {
			$option = \CDOMElement::create('option','value:'.$conditionSet['id']);
			$option->addChild(new \CText($conditionSet['descrizione']));
			$input->addChild($option);
		}
		$field->addChild($input);

		$button = \CDOMElement::create('button','type:button,class:ui small green button');
		$button->setAttribute('onclick','javascript:ajaxSubmitBadgeForm($j(this));');
		$button->addChild(new \CText('salva'));
		$this->addCDOM($button);

		if (!is_null($action)) $this->setAction($action);
	}
}
