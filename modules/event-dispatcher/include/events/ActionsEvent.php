<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2022, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher\Events;

use Lynxlab\ADA\Module\EventDispatcher\ADAEventTrait;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * ACtionsEvent class
 */
final class ActionsEvent extends GenericEvent
{
  use ADAEventTrait;

  /**
   * event own namespace
   */
  const namespace = 'actions';

  /**
   * occurs before the actions menu in the switcher/list_instances.php is rendered
   */
  const LIST_INSTANCES = self::namespace . '.listinstances';

}
