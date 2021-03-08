<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

if (!defined('MODULES_ETHERPAD_GROUPSTABLE')) define('MODULES_ETHERPAD_GROUPSTABLE', AMAEtherpadDataHandler::PREFIX . 'groups');

/**
 * Maps Etherpad groups to ADA courses instances
 */
class Groups extends EtherpadBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    public const table = MODULES_ETHERPAD_GROUPSTABLE;

    protected $groupId;
    protected $instanceId;
    protected $isActive;
    protected $creationDate;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }


    /**
     * Get the value of groupId
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set the value of groupId
     *
     * @return  self
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get the value of instanceId
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set the value of instanceId
     *
     * @return  self
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

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

    /**
     * Get the value of creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set the value of creationDate
     *
     * @return  self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }
}