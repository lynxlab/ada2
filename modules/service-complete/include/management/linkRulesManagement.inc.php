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
class LinkRulesManagement
{
	/**
	 * the array of the courses linked
	 * to the passed conditionset id
	 *  
	 * @var array
	 */
	private $_coursesList;
	
    /**
     * LinkRulesManagement constructor.
     */
    public function __construct()
    {
        $this->_coursesList = array();        
    }
    
    /**
     * generates the form instance and returns the html
     * 
     * @return the usual array with: html, path and status keys
     */
    public function form($data=null)
    {
    	require_once MODULES_SERVICECOMPLETE_PATH.'/include/forms/formLinkRules.inc.php';
    	$dh = $GLOBALS['dh'];    	
    	
    	// load the courses list to be passed to the form
    	$coursesAr = $dh->find_courses_list (array ('nome','titolo'));
    	if (!AMA_DB::isError($coursesAr))
    	{
    		foreach ($coursesAr as $courseEl)
    		{
    			$this->_coursesList[$courseEl[0]] = $courseEl[1] . ' - '.$courseEl[2];
    		}
    	}
    	
    	$form = new FormLinkRules( $data, $this->_coursesList );
    	
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