<?php
/**
 * Base Management Class
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

/**
 * base class for module
 *
 * @author giorgio
 */

abstract class abstractClassRoomManagement
{

	/**
     * name constructor
     */
    public function __construct($data=array()) {
    	if (is_array($data) && count($data)>0) {
    		$this->_fillFromArray($data);
    	}
    }
    
    /**
     * build, manage and display the module's pages
     *
     * @return array
     *
     * @access public
     */
    public function run($action=null) {}
    		
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
	 * @return array
	 * 
	 * @access public
	 */
	public function toArray() {
		return (array) $this;
	}
	
} // class ends here