<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

if (!defined('MODULES_ETHERPAD_SESSIONSTABLE')) define('MODULES_ETHERPAD_SESSIONSTABLE', AMAEtherpadDataHandler::PREFIX . 'sessions');

/**
 * Etherpad session
 */
class Session extends EtherpadBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    public const table = MODULES_ETHERPAD_SESSIONSTABLE;
    public const sessionDuration = 1 * 24 * 3600; // in seconds

    protected $authorId;
    protected $groupId;
    protected $sessionId;
    protected $validUntil;
    protected $creationDate;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }



    /**
     * Get the value of authorId
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * Set the value of authorId
     *
     * @return  self
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
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
     * Get the value of sessionId
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set the value of sessionId
     *
     * @return  self
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get the value of validUntil
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set the value of validUntil
     *
     * @return  self
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

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