<?php

/**
 * @package 	cloneinstance module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\CloneInstance;

use Lynxlab\ADA\Module\EventDispatcher\Events\ActionsEvent;
use Lynxlab\ADA\Module\EventDispatcher\Events\MenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EventSubscriber Class, defines node events names and handlers for this module
 */
class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ActionsEvent::LIST_INSTANCES => 'addListInstancesActions',
            MenuEvent::PRERENDER => 'addMenuItems',
        ];
    }

    public function addListInstancesActions(ActionsEvent $e)
    {
        list($courseId, $instanceId) = array_values($e->getSubject());
        $cloneInstance_img = \CDOMElement::create('img', 'class:cloneinstance-icon,src:img/gruppo.png,alt:' . translateFN('Clona istanza'));
        $cloneInstance_link = \BaseHtmlLib::link(MODULES_CLONEINSTANCE_HTTP . "/cloneinstance.php?id_course=$courseId&id_course_instance=$instanceId", $cloneInstance_img);
        $cloneInstance_link->setAttribute('class', 'cloneinstance');
        $cloneInstance_link->setAttribute('data-courseid', $courseId);
        $cloneInstance_link->setAttribute('data-instanceid', $instanceId);
        $cloneInstance_link->setAttribute('title', translateFN('Clona istanza'));
        /**
         * insert cloneInstance link before deletelink
         */
        $actionsArr = $e->getArgument('actionsArr');
        array_splice($actionsArr, count($actionsArr) - 1, 0, [$cloneInstance_link]);
        // set argument to be returned
        $e->setArgument('actionsArr', $actionsArr);
    }

    public function addMenuItems(MenuEvent $event)
    {
        if (false !== stristr($_SERVER['SCRIPT_FILENAME'], MODULES_CLONEINSTANCE_PATH)) {
            $menu = $event->getSubject();
            $left = $menu->get_leftItemsArray();
            $item = [
                'item_id' => null,
                'label' => 'Indietro',
                'extraHTML' => null,
                'icon' => 'circle left',
                'icon_size' => 'large',
                'href_properties' => json_encode(['onclick' => 'history.go(-1);']),
                'href_prefix' => null,
                'href_path' => null,
                'href_paramlist' => null,
                'extraClass' => null,
                'groupRight' => '0',
                'specialItem' => '0',
                'order' => '0',
                'enabledON' => '%ALWAYS%',
                'menuExtraClass' => '',
                'children' => null
            ];
            // Insert item at 2nd position, i.e. after Home
            array_splice($left, 1, 0, [$item]);
            $menu->set_leftItemsArray($left);
        }
    }
}
