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
 * CoreEvent class
 */
final class CoreEvent extends GenericEvent
{
  use ADAEventTrait;

  /**
   * event own namespace
   */
  const namespace = 'adacore';

  /**
   * The PAGEPRERENDER event occurs before the page is rendered by the ARE::render
   *
   * This event allows you to add, remove or replace render data
   *
   * @CoreEvent
   *
   * @var string
   */
  const PAGEPRERENDER = self::namespace . '.page.prerender';

  /**
   * The AMAPDOPREGETALL event occurs before the AMA_PDO_wrapper::getAll runs its query
   *
   * This event allows you manipulate the query being executed
   *
   * @CoreEvent
   *
   * @var string
   */
  const AMAPDOPREGETALL = self::namespace . '.amapdo.pregetall';

  /**
   * The AMAPDOPOSTGETALL event occurs after the AMA_PDO_wrapper::getAll is run
   *
   * This event allows you to manipulate the retunred results array
   *
   * @CoreEvent
   *
   * @var string
   */
  const AMAPDOPOSTGETALL = self::namespace . '.amapdo.postgetall';

}
