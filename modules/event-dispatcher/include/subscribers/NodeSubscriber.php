<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher\Subscribers;

use Lynxlab\ADA\Module\EventDispatcher\Events\NodeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * NodeSubscriber Class, defines node events names and handlers
 */
class NodeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            NodeEvent::PRESAVE => 'onNodePresave',
            NodeEvent::POSTSAVE => 'onNodePostSave',
        ];
    }

    /**
     * NodeEvent::PRESAVE default event handler
     *
     * @param NodeEvent $event
     * @return void
     */
    public function onNodePresave(NodeEvent $event)
    {
    }

    /**
     * NodeEvent::POSTSAVE default event handler
     *
     * @param NodeEvent $event
     * @return void
     */
    public function onNodePostSave(NodeEvent $event)
    {
        $node = $event->getSubject();
        $arguments = $event->getArguments();
    }
}
