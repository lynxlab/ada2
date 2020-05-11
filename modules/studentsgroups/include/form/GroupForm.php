<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\StudentsGroups;

use CDOMElement;

/**
 * Class for the group object form
 *
 * @author giorgio
 */
class GroupForm extends StudentsGroupsAbstractForm {

	public function __construct($formName = null, $action = null, Groups $group) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);

		$isUpdate = intval($group->getId())>0;

		// 1st row
		$row = \CDOMElement::create('div'); // ,'class:two fields');
		$this->addCDOM($row);

		$field = \CDOMElement::create('div','class:field');
		$row->addChild($field);
		$lbl = \CDOMElement::create('label','for:grouplbl');
		$lbl->addChild(new \CText(translateFN('Nome').' (*)'));
		$input = \CDOMElement::create('text','id:grouplbl,name:label');
		$input->setAttribute('value', htmlspecialchars(trim($group->getLabel()), ENT_QUOTES, ADA_CHARSET));
		$input->setAttribute('data-notempty','true');
		$field->addChild($lbl);
		$field->addChild($input);

		// make a row with 2 fields for each customField
		$j = 0;
		foreach (Groups::customFieldLbl as $cIndex => $cLbl) {
			if ($j++ % 2 == 0) {
				$row = \CDOMElement::create('div','class:two fields');
				$this->addCDOM($row);
			}

			$field = \CDOMElement::create('div','class:field');

			$dd = CDOMElement::create('div','class:ui fluid selection dropdown');
			$dh = CDOMElement::create('hidden','name:customField'.$cIndex);
			if (!is_null($group->getCustomFields()[$cIndex])) {
				$dh->setAttribute('value', $group->getCustomFields()[$cIndex]);
				$dd->setAttribute('data-selected-value', $group->getCustomFields()[$cIndex]);
			}
			// set hidden value
			$dd->addChild($dh);
			$dt = CDOMElement::create('div','class:default text');
			$dt->addChild(new \CText(translateFN($cLbl)));
			$dd->addChild($dt);
			$dd->addChild(CDOMElement::create('i','class: dropdown icon'));
			$dm = CDOMElement::create('div','class:menu');
			$dd->addChild($dm);
			foreach(Groups::customFieldsVal[$cIndex] as $fieldVal => $fieldLbl) {
				$item = CDOMElement::create('div','class:item');
				$item->setAttribute('data-value', $fieldVal);
				$item->addChild(new \CText($fieldLbl));
				$dm->addChild($item);
			}
			$field->addChild($dd);
			$row->addChild($field);
		}

		// dropzone if not updating
		if (!$isUpdate) {
			$row = \CDOMElement::create('div'); // ,'class:two fields');
			$message = 'Trascina qui il file o clicca per caricarlo';

			$this->addCDOM($row);
			$field = \CDOMElement::create('div','class:dz container field');
			$row->addChild($field);

			$dz = \CDOMElement::create('div','id:studentsgroupsfile,class:dropzone');
			$dz->setAttribute('data-type','file');
			$field->addChild($dz);
			$input = \CDOMElement::create('text','name:studentsgroupsfile');
			$input->setAttribute('style','display:none;');
			$span = \CDOMElement::create('span','class:dz-message');
			$span->addChild(\CDOMElement::create('i','class:doc basic large icon'));
			$span->addChild(new \CText(translateFN($message)));
			$dz->addChild($input);
			$dz->addChild($span);

			$dzlegend = \CDOMElement::create('span','class:small');
			$legend = [
				'File in formato csv',
				'Senza riga di intestazione, sequenza campi: nome, cognome, '.(MODULES_SECRETQUESTION ? 'username' : 'e-mail (che sarà anche l\'username)').', password'
			];
			$dzlegend->addChild(new \CText(implode('<br/>', array_map('translateFN', $legend))));
			$row->addChild($dzlegend);
		}

		if ($isUpdate) {
			$this->addHidden('id')->withData($group->getId());
		}
	}
}
