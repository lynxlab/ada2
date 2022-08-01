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
require_once  MODULES_SERVICECOMPLETE_PATH . '/include/completeCondition.class.inc.php';

/**
 * class to implement the time complete condition.
 * The condition is satisfied if the user has spend
 * more than a certain amount of time in the course.
 *
 * @author giorgio
 */
class CompleteConditionTime extends CompleteCondition
{
	/**
	 * constants to define the type of the condition
	 * and the description of the condition itself and
	 * of its parameter, both to be used when building the UI.
	 *
	 */

	/**
	 * description of the condition
	 * NOTE: THIS GOES THROUGH translateFN WHEN IT GETS USED, SO NO INTERNAZIONALIZATION PROBLEM HERE
	 * cannot put here a call to translateFN because it's a static var
	 *
	 * @var string
	 */
	public static $description = 'Condizione soddisfatta se il tempo trascorso nel corso è uguale o maggiore a quello indicato nel parametro';

	/**
	 * String used to build the condition set summary for this rule
	 * NOTE: THIS GOES THROUGH translateFN WHEN IT GETS USED, SO NO INTERNAZIONALIZATION PROBLEM HERE
	 * cannot put here a call to translateFN because it's a static var
	 *
	 * @var string
	 */
	public static $summaryStr = 'Tempo trascorso nel corso: <strong>%s</strong> su <strong>%s</strong> ore:minuti';

	/**
	 * description of the condition's own parameter
	 * NOTE: THIS GOES THROUGH translateFN WHEN IT GETS USED, SO NO INTERNAZIONALIZATION PROBLEM HERE
	 * cannot put here a call to translateFN because it's a static var
	 *
	 * @var string
	 */
	public static $paramDescription = 'Tempo in minuti dopo il quale la condizione si intende soddisfatta';

    /**
	 * method that checks if the contidion is satisfied
	 * for the passed id_user in the passed id_course_instance
	 *
	 * @param int $id_course_instance
	 * @param int $id_user
	 * @param array  $summary the array to ouput summary infos
	 * @return boolean true if condition is satisfied
	 * @access public
	 */
    private function isSatisfied($id_course_instance=null, $id_student=null, &$summary=null) {

    	require_once ROOT_DIR. '/include/history_class.inc.php';

    	$history = new History($id_course_instance, $id_student);
		$id_course = $GLOBALS['dh']->get_course_id_for_course_instance($id_course_instance);
		if (is_numeric($id_course)) $history->setCourse($id_course);
    	$history->get_visit_time();
    	if ($history->total_time>0) $timeSpentInCourse = intval($history->total_time);
		else $timeSpentInCourse = 0;
		// $this->_param is in minutes, $timeSpentInCourse is in seconds
		$param = $this->_param * 60;
		$retval = $timeSpentInCourse>=$param;

        if ($this->getLogToFile()) {
            $logLines = [
                __FILE__.': '.__LINE__,
                'running '.__METHOD__,
				print_r(['instance_id' => $id_course_instance, 'student_id' => $id_student], true),
				sprintf("timeSpentInCourse is %d, param is %d (%d sec.)", $timeSpentInCourse, $this->_param, $param),
				__METHOD__.' returning ' . ($retval ? 'true' : 'false')
            ];
            logToFile($logLines);
        }

		if (!is_null($summary) && is_array($summary)) {
			$summary[__CLASS__] = [
				'isSatisfied' => $retval,
				'param' => $param,
				'check' => $timeSpentInCourse
			];
		}

    	return $retval;
    }

    /**
     * statically build and checks if condition is satisfied
     * MUST HAVE ALWAYS 3 PARAMS, if the first is not needed use null
     *
     * @param string $param
     * @param string $id_course_instance
     * @param string $id_user
	 * @param array  $summary the array to ouput summary infos
     * @return Ambigous <boolean, number>
     */
    public static function buildAndCheck ($param=null, $id_course_instance=null, $id_user=null, &$summary = null)
    {
    	$obj = self::build($param);
    	return $obj->isSatisfied($id_course_instance, $id_user, $summary);
    }

	/**
	 * return a CDOM element to build the html summary of the condition
	 *
	 * @param array $param
	 * @return CDOMElement
	 */
	public static function getCDOMSummary($param) {
		$el = parent::getCDOMSummary($param);
		$formatCheck = sprintf("%02d:%02d", floor($param['check']/3600), floor(($param['check']/60)%60) );
		$formatParam = sprintf("%02d:%02d", floor($param['param']/3600), floor(($param['param']/60)%60) );
		$el->addChild(new CText(sprintf(translateFN(self::$summaryStr), $formatCheck, $formatParam)));
		return $el;
	}

    /**
     * staticallly build a new condition
     *
     * @param string $param
     * @return CompleteConditionTime
     */
    public static function build ($param=null)
    {
    	return new self ($param);
    }
}
?>