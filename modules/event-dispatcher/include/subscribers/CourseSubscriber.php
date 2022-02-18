<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher\Subscribers;

use Lynxlab\ADA\Module\EventDispatcher\Events\CourseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * CourseSubscriber Class, defines course events names and handlers
 */
class CourseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CourseEvent::PRESAVE => 'onCoursePresave',
            CourseEvent::POSTSAVE => 'onCoursePostSave',
        ];
    }

    /**
     * CourseEvent::PRESAVE default event handler
     *
     * @param CourseEvent $event
     * @return void
     */
    public function onCoursePresave(CourseEvent $event)
    {
        /**
         * sample, dummy code
         */
        /*
        $arguments = $event->getArguments();
        if ($arguments['isUpdate']) {
            $courseData = $event->getSubject();
            $event->setArguments($courseData);
        }
        */
    }

    /**
     * CourseEvent::POSTSAVE default event handler
     *
     * @param CourseEvent $event
     * @return void
     */
    public function onCoursePostSave(CourseEvent $event)
    {
    }
}
