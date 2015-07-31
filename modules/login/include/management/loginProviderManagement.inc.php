<?php
/**
 * LOGIN MODULE - login provider management class
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

require_once MODULES_LOGIN_PATH . '/include/form/formLoginProvider.php';

class loginProviderManagement
{
	public $provider_id;
	public $className;
	public $name;
	public $buttonLabel;

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
    	$help = translateFN('Da qui puoi inserire o modifcare un login provider');
    	/* @var $status	string status var to render in the breadcrumbs */
    	$title= translateFN('Login Provider');
    	
    	switch ($action) {
    		case MODULES_LOGIN_EDIT_LOGINPROVIDER:
    			/**
    			 * edit action, display the form with passed data
    			 */
    			
    			/**
    			 * CARICARE LE CLASSI DAI FILES!!!!!
    			 */
    			$htmlObj = new FormLoginProvider($this->toArray(), null, null,self::getAvailableClasses());
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
     * gets available login providers classes by looking inside the directory
     * MODULES_LOGIN_PATH . '/include' and selecting those who:
     * - are NOT directories
     * - have a .class.inc.php extension
     * - have the name that does not start with 'abstract'
     * 
     * @return Ambigous <NULL, array>
     * 
     * @access public
     */
    public static function getAvailableClasses() {
    	$files = array();
    	$classdir = MODULES_LOGIN_PATH . DIRECTORY_SEPARATOR . 'include';
	    if ($handle = opendir($classdir)) {
		    while (false !== ($entry = readdir($handle))) {
		    	/**
		    	 * matches all entries that are not directorys and have a
		    	 * .class.inc.php extension and does not start with 'abstract'
		    	 */
		        if ($entry != "." && $entry != ".."  && 
		        	!is_dir($classdir . DIRECTORY_SEPARATOR . $entry) &&
		        	1 === preg_match("/^(?!abstract)(.*).class.inc.php$/", $entry, $output_array)) {
		            if(isset($output_array[1])) $files[$output_array[1]] = $output_array[1];
		        }
		    }
	    closedir($handle);
	    }
	    asort($files);
	    return (count($files)>0 ? $files : null);
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