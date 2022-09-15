<?php

/**
 * @package 	cloneinstance module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\CloneInstance;

/**
 * Class for the group object form
 *
 * @author giorgio
 */
class CloneInstanceForm extends CloneInstanceAbstractForm
{

    public function __construct($formName = null, $action = null, array $courses, \Course_instance $instance)
    {
        parent::__construct($formName, $action);
        if (!is_null($formName)) {
            $this->setId($formName);
            $this->setName($formName);
        }
        if (!is_null($action)) $this->setAction($action);

        if (!is_null($instance) && !empty($instance->getId())) {
            $this->addHidden('id_course_instance')->withData($instance->getId());
        }

        $row = \CDOMElement::create('div');
        $this->addCDOM($row);

        $sel = \CDOMElement::create('select', 'name:selectedCourses[],id:selectedCourses,multiple:multiple');
        $row->addChild($sel);

        array_map(function ($course) use ($sel) {
            $opt = \CDOMElement::create('option', 'value:' . $course['id_corso']);
            $opt->addChild(new \CText($course['titolo'] . ' (' . $course['nome'] . ')'));
            $sel->addChild($opt);
        }, $courses);
    }
}
