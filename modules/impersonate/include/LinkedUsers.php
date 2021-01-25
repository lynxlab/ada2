<?php

/**
 * @package     impersonate module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\Impersonate;

if (!defined('linkedUsersTable')) define('linkedUsersTable', AMAImpersonateDataHandler::PREFIX . 'linkedusers');

class LinkedUsers extends ImpersonateBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    const table = linkedUsersTable;

    protected $source_id;
    protected $linked_id;
    protected $source_type;
    protected $linked_type;
    protected $is_active;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    /**
     * gets the strings to be prefixed to the userName when building a new linked users
     *
     * @return array user type as key, prefix string as value
     */
    public static function getNewUserPrefix() {
        return [
            AMA_TYPE_SWITCHER => 'sw.',
            AMA_TYPE_TUTOR => 'tu.',
            AMA_TYPE_AUTHOR => 'au.',
        ];
    }

    /**
     * gets the supported links between user types
     *
     * @return array user type as key, array of linkable user types as value
     */
    public static function getSupportedLinks() {
        return [
            AMA_TYPE_ADMIN => [],
            AMA_TYPE_SWITCHER => [],
            AMA_TYPE_TUTOR => [
                AMA_TYPE_AUTHOR,
            ],
            AMA_TYPE_SUPERTUTOR => [
                AMA_TYPE_AUTHOR,
            ],
            AMA_TYPE_AUTHOR => [],
            AMA_TYPE_STUDENT => [],
        ];
    }

    /**
     * gets all users having the passed linked user type
     *
     * @param int $userType
     * @return array of LinkedUsers objects
     */
    public static function getUsersWithLinkedUserType($userType = null) {
        if (is_null($userType)) {
            $userType = -1;
        }
        /**
         * @var AMAImpersonateDataHandler $impDH
         */
        $impDH = AMAImpersonateDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $res = $impDH->findBy('LinkedUsers', [
            'linked_type' => intval($userType),
            'is_active' => true,
        ]);
        $impDH->disconnect();
        return $res;
    }

    /**
     * sets the session array of LinkedUsers for the session user
     *
     * @param array $options opitonal whereArr passed to the findBy method
     * @return void
     */
    public static function setSessionLinkedUser($options = []) {
        $_SESSION[MODULES_IMPERSONATE_SESSLINKEDOBJ] = [];
        $default = [
            'source_id' => isset($_SESSION['sess_userObj']) ? $_SESSION['sess_userObj']->getId() : -1,
            'is_active' => true,
        ];
        $supportedLinks = self::getSupportedLinks()[$_SESSION['sess_userObj']->getType()];
        if (count($supportedLinks)>1) {
            $default['linked_type'] = [
                'op' => 'IN',
                'value' => '('.implode(',', $supportedLinks).')',
            ];
        } else {
            $default['linked_type'] = reset($supportedLinks);
        }

        /**
         * @var AMAImpersonateDataHandler $impDH
         */
        $impDH = AMAImpersonateDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $res = $impDH->findBy('LinkedUsers', array_merge($options, $default));
        if (is_array($res) && count($res)>0) {
            $_SESSION[MODULES_IMPERSONATE_SESSLINKEDOBJ] = $res;
        } else {
            throw new ImpersonateException('Zero linked users found');
        }
        $impDH->disconnect();
    }

    /**
     * gets the session array of LinkedUsers for the session user
     *
     * @return array
     */
    public static function getSessionLinkedUser() {
        if (!isset ($_SESSION[MODULES_IMPERSONATE_SESSLINKEDOBJ])) {
            self::setSessionLinkedUser();
        }
        return $_SESSION[MODULES_IMPERSONATE_SESSLINKEDOBJ];
    }

    /**
     * Get the value of source_id
     */
    public function getSource_id()
    {
        return $this->source_id;
    }

    /**
     * Set the value of source_id
     *
     * @return  self
     */
    public function setSource_id($source_id)
    {
        $this->source_id = $source_id;

        return $this;
    }

    /**
     * Get the value of linked_id
     */
    public function getLinked_id()
    {
        return $this->linked_id;
    }

    /**
     * Set the value of linked_id
     *
     * @return  self
     */
    public function setLinked_id($linked_id)
    {
        $this->linked_id = $linked_id;

        return $this;
    }

    /**
     * Get the value of source_type
     */
    public function getSource_type()
    {
        return $this->source_type;
    }

    /**
     * Set the value of source_type
     *
     * @return  self
     */
    public function setSource_type($source_type)
    {
        $this->source_type = $source_type;

        return $this;
    }

    /**
     * Get the value of linked_type
     */
    public function getLinked_type()
    {
        return $this->linked_type;
    }

    /**
     * Set the value of linked_type
     *
     * @return  self
     */
    public function setLinked_type($linked_type)
    {
        $this->linked_type = $linked_type;

        return $this;
    }

    /**
     * Get the value of is_active
     */
    public function getIs_active()
    {
        return $this->is_active;
    }

    /**
     * Set the value of is_active
     *
     * @return  self
     */
    public function setIs_active($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

}
