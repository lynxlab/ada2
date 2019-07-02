<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Badges;

/**
 * Badge class
 *
 * @author giorgio
 *
 */
class Badge extends BadgesBase {
	/**
	 * table name for this class
	 *
	 * @var string
	 */
    const table =  AMABadgesDataHandler::PREFIX . 'badges';

    protected $name;
    protected $description;
    protected $criteria;

    private $uuid;
    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the url of image
     */
    public function getImageUrl()
    {
        return HTTP_ROOT_DIR .str_replace(ROOT_DIR, '', MODULES_BADGES_MEDIAPATH).strtoupper($this->getUuid()).'.png';
    }

    /**
     * Get the value of criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set the value of criteria
     *
     * @return  self
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

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
        return $this->setUuid((\Ramsey\Uuid\Uuid::fromBytes($uuid))->toString());
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
}