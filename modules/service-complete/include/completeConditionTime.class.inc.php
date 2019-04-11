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
	 * @return boolean true if condition is satisfied
	 * @access public
	 */
    private function isSatisfied($id_course_instance=null, $id_student=null) {

    	require_once ROOT_DIR. '/include/history_class.inc.php';

    	$history = new History($id_course_instance, $id_student);
    	$history->get_visit_time();
    	if ($history->total_time>0) $timeSpentInCourse = intval($history->total_time/60);
		else $timeSpentInCourse = 0;
		$retval = $timeSpentInCourse>=$this->_param;

        if ($this->getLogToFile()) {
            $logLines = [
                __FILE__.': '.__LINE__,
                'running '.__METHOD__,
				print_r(['instance_id' => $id_course_instance, 'student_id' => $id_student], true),
				sprintf("timeSpentInCourse is %d, param is %d", $timeSpentInCourse, $this->_param),
				__METHOD__.' returning ' . ($retval ? 'true' : 'false')
            ];
            logToFile($logLines);
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
     * @return Ambigous <boolean, number>
     */
    public static function buildAndCheck ($param=null, $id_course_instance=null, $id_user=null)
    {
    	$obj = self::build($param);
    	return $obj->isSatisfied($id_course_instance, $id_user);
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