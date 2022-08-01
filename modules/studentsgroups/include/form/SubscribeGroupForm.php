<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\StudentsGroups;

/**
 * Class for the group object form
 *
 * @author giorgio
 */
class SubscribeGroupForm extends StudentsGroupsAbstractForm {

	public function __construct($formName = null, $action = null, array $groups) {
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

		$sel = \CDOMElement::create('select','name:subscribegroup,id:subscribegroup');
		$field->addChild($sel);

		array_map(function($group) use ($sel) {
			$opt = \CDOMElement::create('option', 'value:'.$group->getId());
			$customFStr = [];
			foreach($group->getCustomFields() as $fKey => $fVal) {
				$customFStr[] = Groups::getCustomFieldsVal()[$fKey][$fVal];
			}
			$opt->addChild(new \CText($group->getLabel().' - '.implode(' - ', $customFStr)));
			$sel->addChild($opt);
		} , $groups);
	}
}
