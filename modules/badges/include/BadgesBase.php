<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Badges;

use Ramsey\Uuid\Uuid;

/**
 * Badges module base class
 *
 * @author giorgio
 *
 */
abstract class BadgesBase {

	const GETTERPREFIX = 'get';
	const SETTERPREFIX = 'set';
	const ADDERPREFIX  = 'add';

	/**
	 * base constructor
	 */
	public function __construct($data = array()) {
		$this->fromArray($data);
	}

	/**
	 * Tells which properties are to be loaded using a kind-of-join
	 *
	 * @return array
	 */
	public static function loadJoined() {
		return array();
	}

	/**
	 * adds class own properties to the passed form
	 *
	 * @param \FForm $form
	 * @return \FForm
	 */
	public static function addFormControls (\FForm $form) {
		return $form;
	}

	/**
	 * Populates object properties with the passed values in the data array
	 * NOTE: array keys must match object properties names
	 *
	 * @param array $data
	 * @return \Lynxlab\ADA\Module\Badges\BadgesBase
	 */
	public function fromArray($data = array()) {
		foreach ($data as $key=>$val) {
			if (property_exists($this, $key) && method_exists($this, 'set'.ucfirst($key))) {
				$this->{'set'.ucfirst($key)}($val);
			}
		}
		return $this;
	}

	/**
	 * Convert Object (With Protected Values) To Associative Array
	 * http://www.beliefmedia.com/object-to-array
	 *
	 * @return NULL[]
	 */
	public function toArray() {
		$reflectionClass = new \ReflectionClass(get_class($this));
		$array = array();
		foreach ($reflectionClass->getProperties() as $property) {
			$property->setAccessible(true);
			$toSet = $property->getValue($this);
			$array[$property->getName()] = $toSet;
			$property->setAccessible(false);
		}
		return $array;
	}

	protected function generateUUID() {
		return Uuid::uuid4();
	}
}
