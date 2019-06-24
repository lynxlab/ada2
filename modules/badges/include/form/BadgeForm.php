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
class BadgeForm extends BadgesAbstractForm {

	public function __construct($formName = null, $action = null, Badge $badge) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);

		// 1st row
		$row = \CDOMElement::create('div'); // ,'class:two fields');
		$this->addCDOM($row);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:name');
		$lbl->addChild(new \CText(translateFN('Nome').' (*)'));
		$input = \CDOMElement::create('text','id:name,name:name');
		$input->setAttribute('value', htmlspecialchars(trim($badge->getName()), ENT_QUOTES, ADA_CHARSET));
		$input->setAttribute('data-notempty','true');
		$field->addChild($lbl);
		$field->addChild($input);

		// 2nd row
		$row = \CDOMElement::create('div'); // ,'class:two fields');
		if (Uuid::isValid($badge->getUuid())) {
			$row->setAttribute('class','two fields');
			$message = 'Trascina qui il file o clicca per sostituirlo';
			$imgdiv = \CDOMElement::create('div','class:field, style:text-align:center');
			$imgdiv->addChild(\CDOMElement::create('img','style:width:160px,src:'.$badge->getImageUrl()));
			$row->addChild($imgdiv);
		} else {
			$message = 'Trascina qui il file o clicca per caricarlo';
		}
		$this->addCDOM($row);
		$field = \CDOMElement::create('div','class:dz container field');
		$row->addChild($field);

		$dz = \CDOMElement::create('div','id:badgefile,class:dropzone');
		$dz->setAttribute('data-type','image');
		$field->addChild($dz);
		$input = \CDOMElement::create('text','name:badgefile');
		$input->setAttribute('style','display:none;');
		$span = \CDOMElement::create('span','class:dz-message');
		$span->addChild(\CDOMElement::create('i','class:photo large icon'));
		$span->addChild(new \CText(translateFN($message)));
		$dz->addChild($input);
		$dz->addChild($span);

		// 3rd row
		$row = \CDOMElement::create('div','class:two fields');
		$this->addCDOM($row);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:description');
		$lbl->addChild(new \CText(translateFN('Descriizone').' (*)'));
		$input = \CDOMElement::create('textarea','id:description,name:description');
		$input->addChild(new \CText(htmlspecialchars(trim($badge->getDescription()), ENT_QUOTES, ADA_CHARSET)));
		$input->setAttribute('data-notempty','true');
		$field->addChild($lbl);
		$field->addChild($input);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:criteria');
		$lbl->addChild(new \CText(translateFN('Criterio').' (*)'));
		$input = \CDOMElement::create('textarea','id:criteria,name:criteria');
		$input->setAttribute('data-notempty','true');
		$input->addChild(new \CText(htmlspecialchars(trim($badge->getCriteria()), ENT_QUOTES, ADA_CHARSET)));
		$field->addChild($lbl);
		$field->addChild($input);

		if (Uuid::isValid($badge->getUuid())) {
			$this->addHidden('badgeuuid')->withData($badge->getUuid());
		}
	}
}
