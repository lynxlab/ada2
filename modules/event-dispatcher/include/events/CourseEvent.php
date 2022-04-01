<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher\Events;

use Lynxlab\ADA\Module\EventDispatcher\ADAEventTrait;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * CourseEvent class
 */
final class CourseEvent extends GenericEvent
{
  use ADAEventTrait;

  /**
   * event own namespace
   */
  const namespace = 'course';

  /**
   * The PRESAVE event occurs before the course is saved in the DB
   *
   * This event allows you to add, remove or replace course data
   *
   * @CourseEvent
   *
   * @var string
   */
  const PRESAVE = self::namespace . '.presave';

  /**
   * The POSTSAVE event occurs after the course is saved in the DB
   *
   * @CourseEvent
   *
   * @var string
   */
  const POSTSAVE = self::namespace . '.postsave';

}
