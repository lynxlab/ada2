<?php

/**
 * @package 	notifications module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Notifications;

if (!defined('notificationsTable')) define('notificationsTable', AMANotificationsDataHandler::PREFIX . 'notification');

class Notification extends NotificationBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    const table = notificationsTable;

    const types = [
        ADA_NOTE_TYPE => 1,
    ];

    protected $notificationId;
    protected $notificationType;
    protected $userId;
    protected $nodeId;
    protected $instanceId;
    protected $isActive;
    protected $jsonField;
    protected $creationTS;
    protected $lastEditTS;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    /**
     * Gets the notification type for the passed node type
     *
     * @param int $nodeType
     *
     * @return void
     */
    public static function getNotificationFromNodeType($nodeType)
    {
        return (array_key_exists($nodeType, self::types) ? self::types[$nodeType] : null);
    }

    /**
     * Parse an ADA tpl file with passed content data array and returns the html as a string
     *
     * @param string $fileName
     * @param array $content_dataAr
     * @param string $forceDir not null to force the directory where the template file must be (always inside layout/FAMILY/templates)
     * @param \Layout $layoutObj the layout obh to be used, or null to get it from the filename
     *
     * @return string
     */
    public static function HTMLFromTPL($fileName, $content_dataAr = array(), $forceDir = null, $layoutObj = null)
    {
        if (is_null($layoutObj)) {
            $layoutObj = self::getLayoutObj($fileName);
        }

        if (!is_null($forceDir)) {
            $template = "$forceDir/layout/{$layoutObj->family}/templates/$fileName";
            $html_renderer = new \Generic_Html($template, '');
            $html_renderer->module_dir = pathinfo($template, PATHINFO_DIRNAME);
            $html_renderer->JS_filename = '';
        } else {
            $html_renderer = new \Html($layoutObj->template, $layoutObj->CSS_filename, '', '', '', '', '', '', '', '', $layoutObj);
            $template = $layoutObj->template;
        }

        $html_renderer->fillin_templateFN($content_dataAr);
        $html_renderer->resetImgSrcFN(dirname($template), $layoutObj->family);
        $html_renderer->apply_styleFN();

        return $html_renderer->htmlheader . $html_renderer->htmlbody . $html_renderer->htmlfooter;
    }

    /**
     * Get the layout objcet for the passed filename, to be used by HTMLfromTPL
     *
     * @param string $fileName
     *
     * @return \Layout
     */
    public static function getLayoutObj($fileName)
    {
        $oldSelf = isset($GLOBALS['self']) ? $GLOBALS['self'] : null;
        $GLOBALS['self'] = pathinfo($fileName, PATHINFO_FILENAME);
        $layoutObj = \read_layout_from_DB(666, ADA_TEMPLATE_FAMILY);
        $GLOBALS['self'] = $oldSelf;
        return $layoutObj;
    }

    /**
     * Get the value of notificationId
     */
    public function getNotificationId()
    {
        return $this->notificationId;
    }

    /**
     * Set the value of notificationId
     *
     * @return  self
     */
    public function setNotificationId($notificationId)
    {
        $this->notificationId = $notificationId;

        return $this;
    }

    /**
     * Get the value of notificationType
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * Set the value of notificationType
     *
     * @return  self
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;

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

    /**
     * Get the value of nodeId
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set the value of nodeId
     *
     * @return  self
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;

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
     * Get the value of jsonField
     */
    public function getJsonField()
    {
        return $this->jsonField;
    }

    /**
     * Set the value of jsonField
     *
     * @return  self
     */
    public function setJsonField($jsonField)
    {
        $this->jsonField = $jsonField;

        return $this;
    }

    /**
     * Get the value of creationTS
     */
    public function getCreationTS()
    {
        return $this->creationTS;
    }

    /**
     * Set the value of creationTS
     *
     * @return  self
     */
    public function setCreationTS($creationTS)
    {
        $this->creationTS = $creationTS;

        return $this;
    }

    /**
     * Get the value of lastEditTS
     */
    public function getLastEditTS()
    {
        return $this->lastEditTS;
    }

    /**
     * Set the value of lastEditTS
     *
     * @return  self
     */
    public function setLastEditTS($lastEditTS)
    {
        $this->lastEditTS = $lastEditTS;

        return $this;
    }
}
