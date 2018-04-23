<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

/**
 * Class for a GDPR request type
 *
 * @author giorgio
 */
class GdprRequestType extends GdprBase {

	/**
	 * table name for this class
	 *
	 * @var string
	 */
	const table =  AMAGdprDataHandler::PREFIX . 'requestTypes';

	/**
	 * request types constants
	 *
	 * @var integer
	 */
	const ACCESS = 1;
	const EDIT = 2;
	const ONHOLD = 3;
	const DELETE = 4;

	protected $id;
	protected $type;
	protected $description;
	protected $extra;

	/**
	 * override fromArray method to handle the extra property that is
	 * a string holding a json encoded object
	 *
	 * {@inheritDoc}
	 * @see \Lynxlab\ADA\Module\GDPR\GdprBase::fromArray()
	 */
	public function fromArray($data = array()) {
		if (array_key_exists('extra', $data) && strlen($data['extra'])>0) {
			$this->setExtra(json_decode($data['extra'], true));
			unset($data['extra']);
		}
		return parent::fromArray($data);
	}

	/**
	 * returns true if the request type has a content field that must not be empty
	 * @return boolean
	 */
	public function hasMandatoryContent() {
		/*
		 * add '{\"showonselected\":\"requestContent\"} to the extra field in the db
		 * to show the field with ID requestContent and
		 * add in the array the type that has the mandatory content, if any
		 * (e.g. self::EDIT)
		 */
		return in_array($this->getType(), array());
	}

	/**
	 * returns true if the request type must show a confirm modal before the action is handled
	 *
	 * @return boolean
	 */
	public function confirmBeforeHandle() {
		return (is_array($this->getExtra()) && array_key_exists('confirmhandle', $this->getExtra()) && $this->getExtra()['confirmhandle'] === true);
	}

	/**
	 * Get GdrpAction const linked to this RequestType
	 *
	 * @return number|NULL
	 */
	public function getLinkedAction() {
		$actionsArr = self::getLinkedActionsArray();
		if (array_key_exists($this->getType(), $actionsArr)) {
			return $actionsArr[$this->getType()];
		}
		return null;
	}

	/**
	 * Gets the linking between GdprRequestType and GdprActions
	 *
	 * @return number[]
	 */
	private static function getLinkedActionsArray() {
		return array(
			self::ACCESS => GdprActions::REQUEST_TYPE_ACCESS,
			self::EDIT => GdprActions::REQUEST_TYPE_EDIT,
			self::ONHOLD => GdprActions::REQUEST_TYPE_ONHOLD,
			self::DELETE => GdprActions::REQUEST_TYPE_DELETE
		);
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return mixed
	 */
	public function getExtra() {
		return $this->extra;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @param mixed $extra
	 */
	public function setExtra($extra) {
		$this->extra = $extra;
		return $this;
	}
}
