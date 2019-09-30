<?php
/**
 * @package 	forked-paths module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\ForkedPaths;

/**
 * ForkedPathsHistory class
 *
 * @author giorgio
 *
 */

if (!defined('ForkedPathsHistoryTable')) define('ForkedPathsHistoryTable', AMAForkedPathsDataHandler::PREFIX . 'history');

class ForkedPathsHistory extends ForkedPathsBase {
	/**
	 * table name for this class
	 *
	 * @var string
	 */
    const table =  ForkedPathsHistoryTable;

    protected $userId;
    protected $courseInstanceId;
    protected $nodeFrom;
    protected $nodeTo;
    protected $saveTS;
    protected $userLevelFrom;
    protected $userLevelTo;
    protected $session_id;

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of courseInstanceId
     */
    public function getCourseInstanceId()
    {
        return $this->courseInstanceId;
    }

    /**
     * Set the value of courseInstanceId
     *
     * @return  self
     */
    public function setCourseInstanceId($courseInstanceId)
    {
        $this->courseInstanceId = $courseInstanceId;

        return $this;
    }

    /**
     * Get the value of nodeFrom
     */
    public function getNodeFrom()
    {
        return $this->nodeFrom;
    }

    /**
     * Set the value of nodeFrom
     *
     * @return  self
     */
    public function setNodeFrom($nodeFrom)
    {
        $this->nodeFrom = $nodeFrom;

        return $this;
    }

    /**
     * Get the value of nodeTo
     */
    public function getNodeTo()
    {
        return $this->nodeTo;
    }

    /**
     * Set the value of nodeTo
     *
     * @return  self
     */
    public function setNodeTo($nodeTo)
    {
        $this->nodeTo = $nodeTo;

        return $this;
    }

    /**
     * Get the value of saveTS
     */
    public function getSaveTS()
    {
        return $this->saveTS;
    }

    /**
     * Set the value of saveTS
     *
     * @return  self
     */
    public function setSaveTS($saveTS)
    {
        $this->saveTS = $saveTS;

        return $this;
    }

    /**
     * Get the value of userLevelFrom
     */
    public function getUserLevelFrom()
    {
        return $this->userLevelFrom;
    }

    /**
     * Set the value of userLevelFrom
     *
     * @return  self
     */
    public function setUserLevelFrom($userLevelFrom)
    {
        $this->userLevelFrom = $userLevelFrom;

        return $this;
    }

    /**
     * Get the value of userLevelTo
     */
    public function getUserLevelTo()
    {
        return $this->userLevelTo;
    }

    /**
     * Set the value of userLevelTo
     *
     * @return  self
     */
    public function setUserLevelTo($userLevelTo)
    {
        $this->userLevelTo = $userLevelTo;

        return $this;
    }

    /**
     * Get the value of session_id
     */
    public function getSession_id()
    {
        return $this->session_id;
    }

    /**
     * Set the value of session_id
     *
     * @return  self
     */
    public function setSession_id($session_id)
    {
        $this->session_id = $session_id;

        return $this;
    }
}