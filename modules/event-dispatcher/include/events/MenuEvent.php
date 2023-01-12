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
 * MenuEvent class
 */
final class MenuEvent extends GenericEvent
{
  use ADAEventTrait;

  /**
   * event own namespace
   */
  const namespace = 'menu';

  /**
   * The PRERENDER event occurs before the menu tree is rendered.
   *
   * This event allows you to add, remove or replace menu items
   *
   * @Event
   *
   * @var string
   */
  const PRERENDER = self::namespace . '.prerender';

  /**
   * The POSTRENDER event occurs after the menu tree is rendered.
   *
   * This event allows you to add actions after the menu tree has been rendered.
   *
   * @Event
   *
   * @var string
   */
  const POSTRENDER = self::namespace . '.postrender';
}
