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
 * NodeEvent class
 */
final class NodeEvent extends GenericEvent
{
  use ADAEventTrait;

  /**
   * event own namespace
   */
  const namespace = 'node';

  /**
   * The PRESAVE event occurs before the node is created (i.e. saved)
   *
   * This event allows you to add, remove or replace data
   *
   * @GenericEvent
   *
   * @var string
   */
  const PRESAVE = self::namespace . '.presave';

  /**
   * The POSTSAVE event occurs after the node is created (i.e. saved)
   *
   * This event allows you to add actions after the node has been saved.
   *
   * @GenericEvent
   *
   * @var string
   */
  const POSTSAVE = self::namespace . '.postsave';

  /**
   * The POSTADDREDIRECT event occurs after the node is created (i.e. saved)
   * and the redirect header has been sent
   *
   * This event allows you to add actions after the node has been created,
   * you may close the connection with the browser and do some "background" task
   *
   * @GenericEvent
   *
   * @var string
   */
  const POSTADDREDIRECT = self::namespace . 'add.postredirect';
}
