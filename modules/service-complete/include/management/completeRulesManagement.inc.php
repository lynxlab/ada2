<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

/**
 * management class for completeRules form
 *
 * @author giorgio
 */
class CompleteRulesManagement
{
	/**
	 * the array of all the possible defined and
	 * implemented conditions used to build the 
	 * operation as described in module's own 
	 * config.ini.php
	 *  
	 * @var array
	 */
	private $_formConditionsList;
	
    /**
     * CompleteRulesManagement constructor.
     */
    public function __construct()
    {
        $this->_formConditionsList = array();        
    }
    
    /**
     * generates the form instance and returns the html
     * 
     * @return the usual array with: html, path and status keys
     */
    public function form($data=null)
    {
    	require_once MODULES_SERVICECOMPLETE_PATH.'/include/forms/formCompleteRules.inc.php';
    	$dh = $GLOBALS['dh'];    	
    	
    	// populate the conditionList array
    	foreach ($GLOBALS['completeClasses'] as $className)
    	{
    		if (is_file(MODULES_SERVICECOMPLETE_PATH.'/include/'.$className.'.class.inc.php'))
    		{    		
    			$this->_formConditionsList[$className] = $className;
    		}
    	}
    	$form = new FormCompleteRules( $data, $this->_formConditionsList );
    	
    	/**
    	 * path and status are not used for time being (03/dic/2013)
    	 */
    	return array(
    			'html'   => $form->getHtml(),
    			'path'   => '',
    			'status' => ''
    	);
    }
}