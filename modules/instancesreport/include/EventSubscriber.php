<?php

/**
 * @package 	instancesreport module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\InstancesReport;

use Lynxlab\ADA\Module\EventDispatcher\Events\CoreEvent;
use Lynxlab\ADA\Module\EventDispatcher\Events\MenuEvent;
use Lynxlab\ADA\Module\EventDispatcher\Subscribers\ADAScriptSubscriberInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EventSubscriber Class, defines node events names and handlers for this module
 */
class EventSubscriber implements EventSubscriberInterface, ADAScriptSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MenuEvent::PRERENDER => 'addMenuItems',
        ];
    }

    public static function getSubscribedScripts()
    {
        return [
            'list_instances.php' => [
                CoreEvent::PAGEPRERENDER => 'viewPreRender',
            ],
        ];
    }

    public function viewPreRender(CoreEvent $event)
    {
        $renderData = $event->getArguments();
        $renderData['menuoptions'] = [
            'id_course' => isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null,
        ];
        $event->setArguments($renderData);
    }

    public function addMenuItems(MenuEvent $event)
    {
        if (false !== stristr($_SERVER['SCRIPT_FILENAME'], 'list_instances.php')) {

            $enabledOn = [
                'func' => [
                    InstancesReportActions::class,
                    'canDo',
                ],
                'params' => [
                    'value1' => [
                        'func' => [
                            InstancesReportActions::class,
                            'getConstantFromString'
                        ],
                        'params' => 'EXPORT',
                    ],
                ],
            ];

            $menu = $event->getSubject();
            $left = $menu->get_leftItemsArray();
            $item = array_filter($left, fn($el) => 0===strcasecmp($el['label'], 'agisci'));
            $itemkey = key($item);

            $additem = [
                'item_id' => null,
                'label' => 'Report classi',
                'extraHTML' => null,
                'icon' => 'download disk',
                'icon_size' => null,
                'href_properties' => null,
                'href_prefix' => '%MODULES_INSTANCESREPORT_HTTP%',
                'href_path' => 'export.php',
                'href_paramlist' => 'id_course',
                'extraClass' => null,
                'groupRight' => '0',
                'specialItem' => '0',
                'order' => 29,
                'enabledON' => json_encode($enabledOn),
                'menuExtraClass' => '',
                'children' => null
            ];
            // Insert additem as item child.
            array_push($left[$itemkey]['children'], $additem);
            usort($left[$itemkey]['children'], fn($a,$b) => (int)$a['order']-(int)$b['order']);
            $menu->set_leftItemsArray($left);
        }
    }
}
