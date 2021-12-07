<?php

/**
 * LOGIN MODULE
 *
 * @package     login module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015-2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\Login;

class ldapManagement
{
	public $option_id;
	public $name;
	public $host;
	public $authdn;
	public $basedn;
	public $filter;
	public $usertype;

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
    public function run($action=null) {
    	/* @var $html	string holds html code to be retuned */
    	$htmlObj = null;
    	/* @var $path	string  path var to render in the help message */
    	$help = translateFN('Da qui puoi inserire o modifcare una fonte per l\'autenticazione meditante LDAP');
    	/* @var $status	string status var to render in the breadcrumbs */
    	$title= translateFN('Fonti LDAP');

    	switch ($action) {
    		case MODULES_LOGIN_EDIT_OPTIONSET:
    			/**
    			 * edit action, display the form with passed data
    			 */
    			$htmlObj = new FormLDAP($this->toArray());
    		default:
    			/**
    			 * return an empty page as default action
    			 */
    			break;
    	}

    	return array(
    			'htmlObj'   => $htmlObj,
    			'help'      => $help,
    			'title'     => $title,
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
	 * @return array
	 *
	 * @access public
	 */
	public function toArray() {
		return (array) $this;
	}

} // class ends here