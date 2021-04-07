<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

declare(strict_types=1);

namespace Lynxlab\ADA\Module\EventDispatcher;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * ADAEventDispatcher Class
 */
class ADAEventDispatcher extends EventDispatcher implements EventDispatcherInterface
{

  /**
   * Set to true to have getInstance to return a TraceableEventDispatcher
   * with getCalledListeners and getNotCalledListeners methods
   */
  const TRACEABLE = false;

  /**
   * This class is not allowed to call from outside: private!
   */
  protected function __construct()
  {
  }

  /**
   * Prevent the object from being cloned.
   */
  protected function __clone()
  {
  }

  /**
   * Avoid serialization.
   */
  public function __wakeup()
  {
  }

  /**
   * Returns a Singleton instance of this class.
   *
   * @return ADAEventDispatcher
   */
  public static function getInstance(): EventDispatcherInterface
  {
    static $instance;
    if (null === $instance) {
      $instance = new self();
      if (self::TRACEABLE) {
        $instance = new TraceableEventDispatcher($instance, new Stopwatch());
      }
    }
    return $instance;
  }

  /**
   * builds an event and dispatch it using passed subject and arguments
   *
   * @param array $eventData Associative array to build the event. MUST have 'eventClass' and 'eventName' keys
   * @param mixed $subject   Subject passed to the dispatcher
   * @param array $arguments Arguments passed to the dispatcher
   * @return object as returned by the dispatch method
   */
  public static function buildEventAndDispatch(array $eventData = [], $subject = null, array $arguments = [])
  {
    $eventsNS = 'Events';
    if (array_key_exists('eventClass', $eventData)) {
      if (array_key_exists('eventName', $eventData)) {
        $classname = __NAMESPACE__ . '\\' . $eventsNS . '\\' . $eventData['eventClass'];
        if (class_exists($classname)) {
          $constantname = $classname . '::' . $eventData['eventName'];
          if (defined($constantname)) {
            $event = new $classname($subject, $arguments);
            return self::getInstance()->dispatch($event, constant($constantname));
          } else throw new ADAEventException(sprintf("Event constant %s is not defined", $eventData['eventName']), ADAEventException::EVENTNAMENOTFOUND);
        } else throw new ADAEventException(sprintf("Class %s not found", $eventData['eventClass']), ADAEventException::EVENTCLASSNOTFOUND);
      } else throw new ADAEventException("Must pass an Event name", ADAEventException::NOEVENTNAME);
    } else throw new ADAEventException("Must pass an Events class", ADAEventException::NOEVENTCLASS);
  }

  /**
   * {@inheritdoc}
   *
   * eventName can be a regexp and will dispatch all events that matches
   */
  public function dispatch(object $event, string $eventName = null): object
  {
    // check if $eventName is a regexp
    set_error_handler(function () {}, E_WARNING);
    $isRegularExpression = preg_match($eventName, "") !== FALSE;
    restore_error_handler();
    if ($isRegularExpression) {
      foreach ($this->getListeners() as $anEvent) {
        if (preg_match($eventName, $anEvent[1])) {
          $event = parent::dispatch($event, $anEvent[1]);
        }
      }
      return $event;
    }
    return parent::dispatch($event, $eventName);
  }

  /**
   * return getCalledListeners if TRACEABLE is true or throws an exception
   *
   * @return void
   */
  public function getCalledListeners()
  {
    return $this->callParentIfExists('getCalledListeners');
  }

  /**
   * return getNotCalledListeners if TRACEABLE is true or throws an exception
   *
   * @return void
   */
  public function getNotCalledListeners()
  {
    return $this->callParentIfExists('getNotCalledListeners');
  }

  /**
   * If $method exists in parent class, call it and return its return value
   * else throw an Excecptio
   *
   * @param string $method
   * @return void
   */
  private function callParentIfExists($method)
  {
    foreach (class_parents($this) as $parent) {
      if (method_exists($parent, $method)) {
        return call_user_func([$parent, $method]);
      }
    }
    throw new \Exception(sprintf('The required method %s does not exist for %s', $method, get_class($this)));
  }

  /**
   * Adds all the events subscribers found in the subscribers directory
   *
   * @return void
   */
  public static function addAllSubscribers()
  {
    $fileext = '.php';
    $subscribersNS = 'Subscribers';
    $fullNS = __NAMESPACE__ . '\\' . $subscribersNS . '\\';
    $dispatcher = self::getInstance();
    foreach (glob(dirname(__FILE__) . '/' . strtolower($subscribersNS) . '/*' . $fileext) as $filename) {
      if (is_readable($filename)) {
        $classname = $fullNS . rtrim(basename($filename), $fileext);
        if (class_exists($classname)) {
          $dispatcher->addSubscriber(new $classname());
        }
      }
    }
  }
}
