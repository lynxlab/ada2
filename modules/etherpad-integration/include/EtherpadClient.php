<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

/**
 * extend \EtherpadLite\Client to have a hash prepended to etherpad groups and author mapper
 *
 * ADA may need a single etherpad instance to support more than one ada instance
 *
 * The idea is to generate a hash key unique to every ada instance and prepend it
 * to potentially conflicting ids such as ada user (mapped to etherpad author) and
 * course instance (mapped to etherpad goups)
 */
class EtherpadClient extends \EtherpadLite\Client
{
    const separator = '#';
    private static $hashKey = null;

    public function __construct($apiKey, $baseUrl = null)
    {
        if (is_null(self::$hashKey)) {
            self::$hashKey = HashKey::build();
        }
        parent::__construct($apiKey, $baseUrl);
    }

    private function getHashed($val)
    {
        if (self::$hashKey instanceof HashKey) {
            $val = self::$hashKey->getUuid() . self::separator . $val;
        }
        return $val;
    }

    public function createGroupIfNotExistsFor($groupMapper)
    {
        return parent::createGroupIfNotExistsFor($this->getHashed($groupMapper));
    }

    public function createAuthorIfNotExistsFor($authorMapper, $name = null)
    {
        return parent::createAuthorIfNotExistsFor($this->getHashed($authorMapper), $name);
    }
}
