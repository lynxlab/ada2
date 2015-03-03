<?php
/**
 * Course Instance Budget Management Class
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link				classbudget
 * @version			0.1
 */

/**
 * This class and the formItemCost.php
 * are responsible of managing the cost items defined by the user
 * 
 * @author giorgio
 *
 */
 
class costItemManagement {
	
	public $cost_item_id;
	public $id_istanza_corso;
	public $price;
	public $description;
	public $applied_to=0;	
	
	/**
	 * name constructor
	 */
	public function __construct($data=array()) {
		if (is_array($data) && count($data)>0) {
			$this->_fillFromArray($data);
		}
	}
	
	/**
	 * build, manage and display the FormItemCost
	 *
	 * @return array
	 *
	 * @access public
	 */
	public function run($action=null) {
		/* @var $html	string holds html code to be retuned */
		$htmlObj = null;
	
		switch ($action) {
			case MODULES_CLASSBUDGET_EDIT_COST_ITEM:
				/**
				 * edit action, display the form with passed data
				 */
				require_once MODULES_CLASSBUDGET_PATH . '/include/form/formItemCost.php';
				$htmlObj = new FormItemCost($this->toArray(),'editCostItemForm');
			default:
				/**
				 * return an empty page as default action
				 */
				break;
		}
	
		return array(
				'htmlObj'   => $htmlObj
		);
	}
	
	/**
	 * fills object properties from an array
	 *
	 * @param array $data assoc array to get values from
	 *
	 * @access private
	 */
	protected function _fillFromArray($data) {
		foreach ($data as $key=>$val) {
			if (property_exists($this, $key)) $this->{$key} = trim($val);
		}
	}
	
	/**
	 * returns object properties as an array
	 *
	 * @param boolean $withPrivate if true, returns private properties as well
	 * 
	 * @return array
	 *
	 * @access public
	 */
	public function toArray($withPrivate=false) {
		$filter = ReflectionProperty::IS_PUBLIC;
		if ($withPrivate===true) $filter = $filter | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED;			
		$retArray = array();
		$refclass = new ReflectionClass( $this );
		foreach ($refclass->getProperties($filter) as $property) {
			$retArray[$property->getName()] = $this->{$property->getName()};
		}
		return empty($retArray) ? null : $retArray;
	}	
} // class ends here