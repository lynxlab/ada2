<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace  Lynxlab\ADA\Module\Badges;

/**
 * RewardedBadge class
 *
 * @author giorgio
 *
 */
class RewardedBadge extends BadgesBase {
	/**
	 * table name for this class
	 *
	 * @var string
	 */
    const table =  AMABadgesDataHandler::PREFIX . 'rewarded_badges';

    protected $uuid;
    protected $badge_uuid;
    protected $issuedOn;
    protected $approved;
    protected $notified;
    protected $id_utente;
    protected $id_corso;
    protected $id_istanza_corso;

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
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get the value of badge_uuid
     */
    public function getBadge_uuid()
    {
        return $this->badge_uuid;
    }

    /**
     * Set the value of badge_uuid
     *
     * @return  self
     */
    public function setBadge_uuid($badge_uuid)
    {
        $this->badge_uuid = $badge_uuid;

        return $this;
    }

    /**
     * Get the value of issuedOn
     */
    public function getIssuedOn()
    {
        return $this->issuedOn;
    }

    /**
     * Set the value of issuedOn
     *
     * @return  self
     */
    public function setIssuedOn($issuedOn)
    {
        $this->issuedOn = $issuedOn;

        return $this;
    }

    /**
     * Get the value of approved
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set the value of approved
     *
     * @return  self
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get the value of notified
     */
    public function getNotified()
    {
        return $this->notified;
    }

    /**
     * Set the value of notified
     *
     * @return  self
     */
    public function setNotified($notified)
    {
        $this->notified = $notified;

        return $this;
    }

    /**
     * Get the value of id_utente
     */
    public function getId_utente()
    {
        return $this->id_utente;
    }

    /**
     * Set the value of id_utente
     *
     * @return  self
     */
    public function setId_utente($id_utente)
    {
        $this->id_utente = $id_utente;

        return $this;
    }

    /**
     * Get the value of id_corso
     */
    public function getId_corso()
    {
        return $this->id_corso;
    }

    /**
     * Set the value of id_corso
     *
     * @return  self
     */
    public function setId_corso($id_corso)
    {
        $this->id_corso = $id_corso;

        return $this;
    }

    /**
     * Get the value of id_istanza_corso
     */
    public function getId_istanza_corso()
    {
        return $this->id_istanza_corso;
    }

    /**
     * Set the value of id_istanza_corso
     *
     * @return  self
     */
    public function setId_istanza_corso($id_istanza_corso)
    {
        $this->id_istanza_corso = $id_istanza_corso;

        return $this;
    }

    /**
     * approved getter, boolean version
     *
     * @return boolean
     */
    public function isApproved() {
        return (bool) $this->getApproved();
    }

    /**
     * notified getter, boolean version
     *
     * @return boolean
     */
    public function isNotified() {
        return (bool) $this->getNotified();
    }
}