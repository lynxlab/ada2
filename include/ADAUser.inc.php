<?php
/**
 * ADAUser class
 *
 * This is the new implementation of the ADAUser class that
 * the whole ADA system is used to work with.
 *
 * The new implementation shall basically manage any extra required
 * field that a user may have beside the 'standard ones'. Theese are
 * usually stored in the tables named: 'autore', 'studente', 'tutor'
 * depending upon user's role.
 *
 * PLS NOTE:
 * For the 'standard' version this class should only have the hasExtra and 
 * extraFieldsArray properties, and class code will take care of everything.
 *
 * For the customizations, you must implement all the stuff you need here,
 * keeping in mind that the parent it's always there to help you, kiddy!
 *
 *
 * @package		model
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		ADAUser
 * @version		0.1
 */


class ADAUser extends ADAAbstractUser
{
	/**
	 * Public properties.
	 * PLS set the list of properties you want the extra user to have, and the class code
	 * should take care of the rest. If no extra properites are needed, delete them all!
	 */
	public $picture;
	public $preferredJob;
	public $preferredJobCodes;
	public $socialSkills;
	public $organizationalSkills;
	public $technicalSkills;
	public $computerSkills;
	public $artisticSkills;
	public $otherSkills;
	public $drivingLicences;
			 
	/**
	 * boolean to tell if the class is for a customization
	 * and thus has extra values (i.e. properties).
	 *
	 * For readability reason, I feel it's better to valorize hasExtra
	 * in this class constructor rather than in ADAAbstractUser.
	 *
	 * @var boolean
	 */
	protected $_hasExtra;
	
	/**
	 * array containg extra fields list, builded automatically in the constructor
	 */
	protected $_extraFieldsArray;

	/**
	 * ADAUser constructor
	 *
	 * If this is no customization file, calls the parent and sets hasExtra to false
	 *
	 *
	 * @param array $user_dataAr the array of user datas to fill the class properties with
	 */
	public function __construct($user_dataAr=array()) {
		parent::__construct($user_dataAr);

		$this->_extraFieldsArray = $this->buildExtraFieldsArray();
		$this->_hasExtra = !is_null($this->_extraFieldsArray);

		if ($this->_hasExtra)
		{
			foreach ($this->_extraFieldsArray as $propertyName)
				$this->$propertyName = isset ($user_dataAr[$propertyName]) ? $user_dataAr[$propertyName] : '';
		}

	}

	/**
	 * converts object to array by calling the corresponding parent method
	 * and building up an array with extra properties, finally returning
	 * the merge of the two arrays
	 *
	 * @return array the array containing the converted object
	 * @see ADAGenericUser::toArray()
	 * @access public
	 */
	public function toArray() {
		$stdValues = parent::toArray();
		if ($this->_hasExtra) {
			foreach ($this->_extraFieldsArray as $propertyName) $extraValues[$propertyName] = $this->$propertyName;
			return array_merge ($stdValues,$extraValues);
		}
		else return $stdValues;
	}

	/**
	 * Sets extra values by checking if each array element key has a corresponding class property.
	 * If it has, then set the property else do nothing and disregard the key.
	 * There should be no need to override this method for each customization.
	 *
	 * @param array $extraAr array of values to be set
	 * @access public
	 */
	public function setExtras ($extraAr) {
		if ($this->_hasExtra) {
			foreach ($extraAr as $property=>$value) {
				if (property_exists($this, $property)) $this->$property = $value;
			}
		}
	}

	/**
	 * method to build the list of all extra properites
	 * called only once in the constructor. No one else
	 * should ever need to call again this method, but
	 * must get the builded array using the getExtraFields method.
	 *
	 * @return array list of all extra properties, excluding _hasExtra
	 * @access private
	 */
	private function buildExtraFieldsArray() {
		$retArray = array();
		// instantiate a ReflectionClass
		$refclass = new ReflectionClass($this);
		// loop through each property
		foreach ($refclass->getProperties() as $property)
		{
			// if property class name == the reflection class name,
			// then property is one of the elements we are lookin for
			if ($property->class == $refclass->name &&
			$property->name!=='_hasExtra' && $property->name!=='_extraFieldsArray' )  $retArray[] = $property->name;
		}
		return empty($retArray) ? null : $retArray;
	}

	/**
	 * extraFieldsArray getter
	 * 
	 * @return array extraFieldsArray if hasExtra is true, else false
	 * @access public
	 */
	public function getExtraFields()
	{
		if ($this->_hasExtra) return $this->_extraFieldsArray;
		else return null;
	}

	/**
	 * hasExtra getter
	 *
	 * @return boolean
	 * @access public
	 */
	public function hasExtra() {
		return $this->_hasExtra;
	}
}

?>