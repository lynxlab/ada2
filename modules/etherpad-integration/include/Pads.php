<?php

/**
 * @package     etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

if (!defined('MODULES_ETHERPAD_PADSTABLE')) define('MODULES_ETHERPAD_PADSTABLE', AMAEtherpadDataHandler::PREFIX . 'pads');

/**
 * Etherpad pads
 */
class Pads extends EtherpadBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    public const table = MODULES_ETHERPAD_PADSTABLE;
    public const groupPadsSeparator = "$";
    public const instancePadId = 'all';
    public const instancePadName = 'Documento condiviso di classe';
    public const nodePadName = "Documento condiviso per il nodo %s";

    private const emptyNodePadText = 'nodeemptypad.txt';
    private const emptyInstancePadText = 'instanceemptypad.txt';

    protected $padId;
    protected $groupId;
    protected $nodeId;
    protected $padName;
    protected $realPadName;
    protected $isActive;
    protected $creationDate;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }


    public static function getEmptyPadText($nodeData) {
        $text = '';
        $prefix = MODULES_ETHERPAD_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        if (!MULTIPROVIDER && isset($GLOBALS['user_provider'])) {
            $clientPrefix = ROOT_DIR . DIRECTORY_SEPARATOR . $GLOBALS['user_provider'] . DIRECTORY_SEPARATOR;
        } else {
            $clientPrefix = '';
        }
        $isNodePad = is_array($nodeData) && count($nodeData)>0;
        $loadfile = $isNodePad ? self::emptyNodePadText : self::emptyInstancePadText;

        $filename = $clientPrefix . $loadfile;
        // check for $loadfile in provider dir
        $filefound = !MULTIPROVIDER && is_readable($filename);
        if (!$filefound) {
            // check for $loadfile in module config dir
            $filename = $prefix . $loadfile;
            $filefound = is_readable($filename);
        }

        if ($filefound) {
            $text = file_get_contents($filename);
            if ($isNodePad) {
                $replaceArr = array_filter($nodeData, 'is_scalar');
                // placeholder support: replace every occurence of $nodeData keys surrounded by percent sign with its value
                $searchArr = array_map(function($el) {return '%'.$el.'%'; }, array_keys($replaceArr));
                $text = str_replace($searchArr, $replaceArr, $text);
            }
        }
        return $text;
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
     * Get the value of padName
     */
    public function getPadName()
    {
        return $this->padName;
    }

    /**
     * Set the value of padName
     *
     * @return  self
     */
    public function setPadName($padName)
    {
        $this->padName = $padName;

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

    /**
     * Get the value of padId
     */
    public function getPadId()
    {
        return $this->padId;
    }

    /**
     * Set the value of padId
     *
     * @return  self
     */
    public function setPadId($padId)
    {
        $this->padId = $padId;

        return $this;
    }

    /**
     * Get the value of realPadName
     */
    public function getRealPadName()
    {
        return $this->realPadName;
    }

    /**
     * Set the value of realPadName
     *
     * @return  self
     */
    public function setRealPadName($realPadName)
    {
        $this->realPadName = $realPadName;

        return $this;
    }
}