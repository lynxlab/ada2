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
 * abstract class for describing a complete condition.
 * All complete condition classes must extend this one
 * and implement their own method(s).
 *
 * @author giorgio
 */
abstract class CompleteCondition
{
	/**
	 * the single int param a condition accepts for time being (10/dic/2013)
	 *
	 * @var int
	 */
	protected $_param;

	/**
	 * true to write debug info in ADA log subdir
	 *
	 * @var boolean
	 */
	protected $logToFile = false;

	/**
	 * CompleteCondition constructor.
	 */
	public function __construct() {
		/**
		 * assume everything is NOT ok
		 */
		$isOk = false;

		/**
		 * support variable number of arguments for future use
		 */
		$args = func_get_args();

		switch (func_num_args())
		{
			case 0:
				/**
				 * init the time to the maximum possible
				 * int for this php build, should be enough
				 * to always consider the condition not satisfied
				 */
				$this->_param = PHP_INT_MAX;
				$isOk = true;
				break;
			case 1:
				/**
				 * args[0] is the time the user wants to set
				 */
				$isOk = $this->setIntParam($args[0]);
				break;
			default:
				/**
				 * by default, some error has occoured
				 */
				$isOk = false;
				break;
		}
		/**
		 * handle the error some way...
		 */
		if (!$isOk) {
			/**
			 * wrong number of arguments, let's take some action:
			 * first: log the error. this shall go to log/trace.log file
			 */
			ADAFileLogger::log(__CLASS__.'::'.__METHOD__.' WRONG NUMBER OR TYPE OF PARAMETERS');
			/**
			 * third: raise an ADA_Error if wrong number of arguments
			 * see config_errors.inc.php line 167 and following.
			 * depending on the erorr phase / severity something will happen...
			*/
			new ADA_Error(NULL,NULL,__METHOD__, AMA_ERR_WRONG_ARGUMENTS,ADA_ERROR_SEVERITY_NONE);
			/**
			 * third throw an exception to be catched by the caller
			*/
			throw new Exception('Fatal Error: could not instantiate '.__CLASS__,AMA_ERR_WRONG_ARGUMENTS);
		}
		$this->setLogToFile(defined('MODULES_SERVICECOMPLETE_LOG') && MODULES_SERVICECOMPLETE_LOG === true);
	}


	/**
	 * time setter
	 *
	 * @return boolean true if succesfully set
	 */
	public function setIntParam ($param) {
		if (is_int($param) && intval($param)>=0)
		{
			$this->_param = intval($param);
			return true;
		}
		return false;
	}

	/**
     * logToFile setter
     *
     * @param boolean $logToFile
     * @return CompleteConditionSet
     */
    public function setLogToFile($logToFile = false)
    {
        $this->logToFile = $logToFile;
        return $this;
    }

    /**
     * logToFile getter
     *
     * @return bool
     */
    public function getLogToFile()
    {
        return $this->logToFile;
    }

	/**
	 * return a CDOM element to build the html summary of the condition
	 *
	 * @param array $param
	 * @return CDOMElement
	 */
	public static function getCDOMSummary($param) {
		$el = CDOMElement::create('span', 'class:condition');
		if (array_key_exists('check', $param) && is_array($param['check'])) {
			$el->setAttribute('class', $el->getAttribute('class').' container');
			$addIcon = false;
		} else {
			$el->setAttribute('class', $el->getAttribute('class').' item');
			$addIcon = true;
		}
		if ($param['isSatisfied']) {
			$el->setAttribute('class', $el->getAttribute('class').' satisfied');
			if ($addIcon) $el->addChild(CDOMElement::create('i','class:ui circle ok icon'));
		} else {
			$el->setAttribute('class', $el->getAttribute('class').' unsatisfied');
			if ($addIcon) $el->addChild(CDOMElement::create('i','class:ui circle ban icon'));
		}
		return $el;
	}

    private function isSatisfied($id_course_instance, $id_student) {}
}
?>