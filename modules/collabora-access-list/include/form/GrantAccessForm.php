<?php

/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\CollaboraACL;

use CDOMElement;

/**
 * Class for the group object form
 *
 * @author giorgio
 */
class GrantAccessForm extends CollaboraACLAbstractForm
{

    public function __construct($formName = null, $action = null, $params = null)
    {
        parent::__construct($formName, $action);
        if (!is_null($formName)) {
            $this->setId($formName);
            $this->setName($formName);
        }
        if (!is_null($action)) $this->setAction($action);

        $isUpdate = array_key_exists('fileAclId', $params) && $params['fileAclId'] > 0;

        $grid = \CDOMElement::create('div','class:three column stackable ui grid');
        $this->addCDOM($grid);

        // 1st col
        $col = \CDOMElement::create('div','id:grantAccessLeft,class:seven wide column');
        $grid->addChild($col);

        $field = \CDOMElement::create('div', 'class:field');
        $col->addChild($field);
        $lbl = \CDOMElement::create('label', 'for:grouplbl');
        $lbl->addChild(new \CText(translateFN('Studenti')));
        $select = \CDOMElement::create('select', 'id:users');
        $select->setAttribute('multiple', 'multiple');
        $select->setAttribute('size', '8');
        $select->setAttribute('data-right','#grantedUsers');
        if (array_key_exists('allUsers', $params) && is_array($params['allUsers']) && count($params['allUsers'])>0) {
            foreach($params['allUsers'] as $u) {
                $opt = \CDOMElement::create('option','value:'.$u['subscription']->getSubscriberId());
                $opt->addChild(new \CText($u['subscription']->getSubscriberFullName()));
                if ($u['granted']) {
                    $opt->setAttribute('selected', 'selected');
                }
                $select->addChild($opt);
            }
        }
        $field->addChild($lbl);
        $field->addChild($select);

        // 2nd col
        $col = \CDOMElement::create('div','id:grantAccessButtons,class:two wide column');
        $buttons = [
            [
                'name' => 'rightAll',
                'icon' => 'double angle right',
                'select-prop' => 'right-all',
            ],
            [
                'name' => 'rightSelected',
                'icon' => 'angle right',
                'select-prop' => 'right-selected',
            ],
            [
                'name' => 'leftSelected',
                'icon' => 'angle left',
                'select-prop' => 'left-selected',
            ],
            [
                'name' => 'leftAll',
                'icon' => 'double angle left',
                'select-prop' => 'left-all',
            ],

        ];
        foreach($buttons as $button) {
            $btn = \CDOMElement::create('button','type:button,class:fluid ui icon button,id:multiselect_'.$button['name']);
            $btn->addChild(\CDOMElement::create('i','class:ui icon '.$button['icon']));
            $select->setAttribute('data-'.$button['select-prop'], '#multiselect_'.$button['name']);
            $col->addChild($btn);
        }

        $grid->addChild($col);

        // 3rd col
        $col = \CDOMElement::create('div','id:grantAccessRight,class:seven wide column');
        $grid->addChild($col);
        $field = \CDOMElement::create('div', 'class:field');
        $col->addChild($field);
        $lbl = \CDOMElement::create('label', 'for:grouplbl');
        $lbl->addChild(new \CText(translateFN('Studenti con accesso al file')));
        $select = \CDOMElement::create('select', 'id:grantedUsers,name:grantedUsers[]');
        $select->setAttribute('multiple', 'multiple');
        $select->setAttribute('size', '8');
        $field->addChild($lbl);
        $field->addChild($select);

        // note: there's no need for hiddens, collaboraaclAPI.js has all that it needs
        // if ($isUpdate) {
        //     $this->addHidden('fileAclId')->withData($params['fileAclId']);
        // }
    }
}
