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
 * @author      	giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			ADAUser
 * @version		0.1
 */


class ADAUser extends ADAAbstractUser
{
	/**
	 * array to list linked tables, must be private
	 * each item MUST have a corresponding class with its own fields.
	 * The constructor will build a public variable called $tbl_<array element>
	 * of type array to hold the rows from the corresponding table.
	 */
	protected static $_linkedTables = array ('educationTraining');
	
	/**
	 * table prefix used in the DB.
	 * eg. if in the linkedTables there is 'educationTraining'
	 * the corresponding table in the db must be $prefix.'educationTraining'
	*/
	protected static $_tablesPrefix = "OL_";
	
	
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
	 * WARNING: each property representing a 1:n table relationship
	 * must be named like 'tbl_'<tablename>. prefix tbl is the must! 
	 */
// 	public $tbl_educationTraining;
		
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
			// sets the properties with values coming from $user_dataAr
			foreach ($this->_extraFieldsArray as $propertyName)
				$this->$propertyName = isset ($user_dataAr[$propertyName]) ? $user_dataAr[$propertyName] : '';

			// build up a property called 'tbl_'.tableName 
			// containing an empty array for each linkedTable
			foreach (self::$_linkedTables as $tableName)
			{
				$varName = 'tbl_'.$tableName;
				$this->$varName = array();	
			}				
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
			foreach (self::$_linkedTables as $tableName)
			{
				$propertyName = 'tbl_'.$tableName;
				if (isset($this->$propertyName) && is_array($this->$propertyName))
				{					
					foreach ($this->$propertyName as $num=>$tableObject)
					{
						foreach ($tableObject->getFields() as $field)
							$extraValues[$tableName][$num][$field] = $tableObject->$field;
						// force protected property _isSaved
						if ($tableObject->getSaveState()) $extraValues[$tableName][$num]['_isSaved'] = 1; 
						
					}
					
// 					var_dump($extraValues);
// 					die(__FILE__);
				}
			}
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
				// first check if $property is a class property
				if (property_exists($this, $property)) $this->$property = $value;
				// next check if $property is an array, which means
				// it's a value coming from a table that has a 1:n relationship with the student
				else if (is_array($value))
				{ 
					$classPropertyName = 'tbl_'.$property;			
 					// $classProperyName hold something like 'tbl_educationTraining'
					foreach ($value as $arrayValues)
					{ 
// 						$this->$classPropertyName[count($this->$classPropertyName)] = new $property($arrayValues);
						array_push($this->$classPropertyName, new $property($arrayValues));						
					}					
				}
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
		foreach ($refclass->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
		{
			// if property class name == the reflection class name,
			// and its name does not start with 'tbl_'
			// then property is one of the elements we are lookin for
			if ($property->class == $refclass->name &&  
				(strpos($property->name,'tbl_')) === false) $retArray[] = $property->name;
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
	
	public static function getLinkedTables ()
	{
		return self::$_linkedTables;
	}
	
	public static function getTablesPrefix()
	{
		return self::$_tablesPrefix;
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

/*****************************************************************************/

abstract class extraTable {
	
	protected  $_isSaved;
	
	public function __construct($dataAr = array())
	{		
		if (!empty($dataAr))
		{
			foreach ($dataAr as $propname=>$propvalue)
			{
				if (property_exists($this, $propname)) $this->$propname = $propvalue;
			}
			
			if (isset($dataAr['_isSaved']) && $dataAr['_isSaved']==0) $this->_isSaved=false; 
			else $this->_isSaved = true;
		}
	}
	
	public function setSaveState ( $saveState )
	{
		$this->_isSaved = $saveState;
	}
	
	public function getSaveState ()
	{
		return $this->_isSaved;
	}
	
	public static function buildArrayFromPOST ( $className, $postData )
	{
		$retArray = array();
		$refclass = new ReflectionClass( $className );
// 		foreach (get_class_vars( $className ) as $propname=>$propdefval)
// 			$retArray[$propname] = $postData[$propname];
		foreach ($refclass->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
			$retArray[$property->name] = $postData[$property->name];
		// force procteded property _isSaved
		if (isset ($postData['_isSaved']) && $postData['_isSaved']==0 ) $retArray['_isSaved'] = 0;
		
		return empty($retArray) ? null : $retArray;
	}
	
	protected static function getFields ( $className )
	{
		$retArray = array();
// 		foreach (get_class_vars( $className ) as $propname=>$propdefval)
// 			$retArray[] = $propname;
// 		return empty($retArray) ? null : $retArray;
		$refclass = new ReflectionClass( $className );
		foreach ($refclass->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
		{
			$retArray[] = $property->name;
		}
		return empty($retArray) ? null : $retArray;							
	}	
}

/**
 * class educationTraining for storing corresponding table data
 * 
 * PLS NOTE: public properties MUST BE ONLY table column names
 * 
 * If some other properties are needed, MUST add them as protected and/or private
 * and implement setters and getters
 * 
 * @author giorgio
 *
 */
class educationTraining extends extraTable
{
	public $idEducationTraining;
	public $studente_id_utente_studente;
	public $eduStartDate;
	public $eduEndDate;
	public $title;
	public $schoolType;
	public $mark;
	public $organizationProvided;
	public $organizationAddress;
	public $organizationCity;
	public $organizationCountry;
	public $principalSkills;
	
	private static $keyProperty = "idEducationTraining";
	
	/**
	 * Gets the field list for this class (aka table),
	 * that's to say a list of all its public properties.
	 * 
	 * Must be overridden in each class because
	 * it must pass __CLASS__ to the parent.
	 */
	public static function getFields()
	{
		return parent::getFields(__CLASS__);
	}
	
	public static function buildArrayFromPOST ($postData)
	{
		return parent::buildArrayFromPOST( __CLASS__, $postData);
	}
	
	public static function getKeyProperty()
	{
		return self::$keyProperty;
	}
}
?>