<?php

/**
 * @package 	notifications module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Notifications;

if (!defined('emailqueueTable')) define('emailqueueTable', AMANotificationsDataHandler::PREFIX . 'emailqueue');

class EmailQueueItem extends NotificationBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    const table = emailqueueTable;

    const STATUS_ENQUEUED = 1;
    const STATUS_PROCESSED_OK = 2;
    const STATUS_PROCESSED_ERROR = 4;

    const EMAILS_PER_HOUR = MODULES_NOTIFICATIONS_EMAILPERHOUR;

    const NEWFORUMNOTE = 'new/forumnote';

    const emailConfigs = [
        self::NEWFORUMNOTE => [
            'template' => 'newforumnote.tpl',
            'subject' => 'Nuovo post nel forum del corso:',
        ],
    ];

    protected $id;
    protected $recipientEmail;
    protected $recipientFullName;
    protected $userId;
    protected $subject;
    protected $body;
    protected $emailType;
    protected $status;
    protected $sendResult;
    protected $enqueueTS;
    protected $processTS;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    /**
     * Gets the email configuration array for the passed type
     *
     * @param int $type
     *
     * @return void
     */
    public static function getEmailConfigFromType($type) {
        return(array_key_exists($type, self::emailConfigs) ? self::emailConfigs[$type] : null);
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of recipientEmail
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * Set the value of recipientEmail
     *
     * @return  self
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * Get the value of recipientFullName
     */
    public function getRecipientFullName()
    {
        return $this->recipientFullName;
    }

    /**
     * Set the value of recipientFullName
     *
     * @return  self
     */
    public function setRecipientFullName($recipientFullName)
    {
        $this->recipientFullName = $recipientFullName;

        return $this;
    }

    /**
     * Get the value of subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the value of subject
     *
     * @return  self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @return  self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of emailType
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * Set the value of emailType
     *
     * @return  self
     */
    public function setEmailType($emailType)
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of sendResult
     */
    public function getSendResult()
    {
        return $this->sendResult;
    }

    /**
     * Set the value of sendResult
     *
     * @return  self
     */
    public function setSendResult($sendResult)
    {
        $this->sendResult = $sendResult;

        return $this;
    }

    /**
     * Get the value of enqueueTS
     */
    public function getEnqueueTS()
    {
        return $this->enqueueTS;
    }

    /**
     * Set the value of enqueueTS
     *
     * @return  self
     */
    public function setEnqueueTS($enqueueTS)
    {
        $this->enqueueTS = $enqueueTS;

        return $this;
    }

    /**
     * Get the value of processTS
     */
    public function getProcessTS()
    {
        return $this->processTS;
    }

    /**
     * Set the value of processTS
     *
     * @return  self
     */
    public function setProcessTS($processTS)
    {
        $this->processTS = $processTS;

        return $this;
    }

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
}
