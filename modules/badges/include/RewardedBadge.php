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

 if (!defined('RewardedBadgeTable')) define('RewardedBadgeTable', AMABadgesDataHandler::PREFIX . 'rewarded_badges');

class RewardedBadge extends BadgesBase {
	/**
	 * table name for this class
	 *
	 * @var string
	 */
    const table =  RewardedBadgeTable;

    protected $uuid;
    protected $badge_uuid;
    protected $issuedOn;
    protected $approved;
    protected $notified;
    protected $id_utente;
    protected $id_corso;
    protected $id_istanza_corso;

    private static $instanceRewards = [];

    /**
	 * Tells which properties are to be loaded using a kind-of-join
	 *
	 * @return array
	 */
	public static function doNotLoad() {
		return array('instanceRewards');
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
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Set the value of uuid, binary version
     *
     * @param string $uuid
     *
     * @return self
     */
    public function setUuid_bin($uuid)
    {
        $tmpuuid = \Ramsey\Uuid\Uuid::fromBytes($uuid);
        return $this->setUuid($tmpuuid->toString());
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
     * Set the value of badge_uuid, binary version
     *
     * @param string $uuid
     *
     * @return self
     */
    public function setBadge_uuid_bin($uuid)
    {
        $tmpuuid = \Ramsey\Uuid\Uuid::fromBytes($uuid);
        return $this->setBadge_uuid($tmpuuid->toString());
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

    public static function buildStudentRewardHTML ($courseId, $instanceId, $studentId) {
        $studentsRewards = self::getInstanceRewards()['studentsRewards'];
        $awbadges = array_key_exists($studentId, $studentsRewards) ? $studentsRewards[$studentId] : 0;
        $baseStr = $awbadges.' '.translateFN('su').' '.self::getInstanceRewards()['total'];
        if ($awbadges>0) {
            $retObj = \CDOMElement::create('a','class:dontwrap,href:'.MODULES_BADGES_HTTP.'/user-badges.php?id_instance='.$instanceId.'&id_course='.$courseId.'&id_student='.$studentId);
        } else {
            $retObj = \CDOMElement::create('span');
        }
        $retObj->addChild(new \CText($baseStr));
        return $retObj;
    }

    public static function loadInstanceRewards($courseId, $instanceId) {
        $bdh = AMABadgesDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $totalBadges = $bdh->getBadgesCountForCourse($courseId);
        $studentBadges = $bdh->getRewardedBadgesCount(['id_corso' => $courseId, 'id_istanza_corso' => $instanceId]);
        self::setInstanceRewards(['total' => $totalBadges, 'studentsRewards' => $studentBadges]);
        return self::getInstanceRewards();
    }

    /**
     * Get the value of instanceRewards
     */
    public static function getInstanceRewards()
    {
        return self::$instanceRewards;
    }

    /**
     * Set the value of instanceRewards
     *
     * @return  self
     */
    private static function setInstanceRewards($instanceRewards)
    {
        self::$instanceRewards = $instanceRewards;
    }
}