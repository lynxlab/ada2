<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

use Ramsey\Uuid\Uuid;

if (!defined('MODULES_ETHERPAD_HASHKEYTABLE')) define('MODULES_ETHERPAD_HASHKEYTABLE', AMAEtherpadDataHandler::PREFIX . 'hashkey');

/**
 * Store ADA unique key to hash ADA ids to Etherpad ids
 */
class HashKey extends EtherpadBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    public const table = MODULES_ETHERPAD_HASHKEYTABLE;

    protected $uuid;
    protected $isActive;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    public static function build($dbToUse = null) {
        if (is_null($dbToUse)) {
            $dbToUse = AMAEtherpadDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        }
        $retval = $dbToUse->findOneBy('HashKey',[ 'isActive' => true ]);
        if (!($retval instanceof HashKey)) {
            $retval = new self();
            $retval->setUuid(Uuid::uuid5(Uuid::NAMESPACE_URL, HTTP_ROOT_DIR)->toString());
            $retval->setIsActive(true);
            try {
                $saveResult = $dbToUse->saveHashKey($retval->toArray());
            } catch (EtherpadException $e) {
                $saveResult = false;
                $retval = new self();
            }
        }
        $dbToUse->disconnect();
        return $retval;
    }

    /**
     * Get the value of uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set the value of uuid
     *
     * @return  self
     */
    protected function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get the value of isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set the value of isActive
     *
     * @return  self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
}