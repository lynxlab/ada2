<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher\Events;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * ForumEvent class
 */
final class ForumEvent extends GenericEvent
{
  /**
   * event own namespace
   */
  const namespace = 'forum';

  /**
   * The NOTEPRESAVE event occurs before the a forum note (aka post) is created (i.e. saved)
   *
   * This event allows you to add, remove or replace data
   *
   * @GenericEvent
   *
   * @var string
   */
  const NOTEPRESAVE = self::namespace . '.note.presave';

  /**
   * The NOTEPOSTSAVE event occurs after the forum note (aka post) is created (i.e. saved)
   *
   * This event allows you to add actions after the node has been saved.
   *
   * @GenericEvent
   *
   * @var string
   */
  const NOTEPOSTSAVE = self::namespace . '.note.postsave';

  /**
   * The INDEXACTIONDONE event occurs after the action buttons for the forum index have been generated
   *
   * This event allows you to add actions custom buttons to the default buttons container
   *
   * @GenericEvent
   *
   * @var string
   */
  const INDEXACTIONDONE = self::namespace . '.index.action.done';
}

