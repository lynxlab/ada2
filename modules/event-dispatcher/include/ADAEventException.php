<?php

/**
 * @package     event-dispatcher module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EventDispatcher;

/**
 * ADAEventException class to handle custom exceptions
 */
class ADAEventException extends \Exception
{
    const NOEVENTCLASS = 1;
    const EVENTCLASSNOTFOUND = 2;
    const NOEVENTNAME = 3;
    const EVENTNAMENOTFOUND = 4;

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
