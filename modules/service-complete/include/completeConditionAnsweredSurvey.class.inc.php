<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2017, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */
require_once  MODULES_SERVICECOMPLETE_PATH . '/include/completeCondition.class.inc.php';

/**
 * class to implement the 'are all surveys answered' complete condition.
 * The condition is satisfied if the user has answered the course
 * survey at least one time. If the course has no survey, the condition is always false
 *
 * @author giorgio
 */
class CompleteConditionAnsweredSurvey extends CompleteCondition
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
	public static $description = 'Condizione soddisfatta se lo studente ha risposto a tutti i sondaggi del corso almeno il numero di volte specificato nel parametro';

	/**
	 * description of the condition's own parameter
	 * NOTE: THIS GOES THROUGH translateFN WHEN IT GETS USED, SO NO INTERNAZIONALIZATION PROBLEM HERE
	 * cannot put here a call to translateFN because it's a static var
	 *
	 * @var string
	 */
	public static $paramDescription = 'Numero di sottomissioni dei sondaggi per cui la condizione si intende soddisfatta';

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
    	if (defined('MODULES_TEST') && MODULES_TEST) {
    		require_once(MODULES_TEST_PATH.'/include/AMATestDataHandler.inc.php');
    		if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
    		$GLOBALS['dh']= AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
    		if (!AMA_DB::isError($GLOBALS['dh'])) {
    			$courseId = $GLOBALS['dh']->get_course_id_for_course_instance($id_course_instance);
    			$test_list = $GLOBALS['dh']->test_getCourseSurveys(array('id_corso'=>$courseId));
    			if (!AMA_DB::isError($test_list) && is_array($test_list)) {
    				if (count($test_list) === 0) {
    					// define no-survey behaviour here
    					return false;
    				} else {
    					$retval = true;
    					foreach($test_list as $test_listEL) {
    						$historyArr = $GLOBALS['dh']->test_getHistoryTest(array(
    								'id_corso' => $courseId,
    								'id_istanza_corso' => $id_course_instance,
    								'id_nodo' => $test_listEL['id_test'],
    								'id_utente' => $id_student,
    								'consegnato' => 1
    						));
    						/**
    						 * Should the course have more than one survey, the condition is true
    						 * only if the student has answered at least $this->_param times to EVERY survey
    						 */
    						$retval = $retval && !AMA_DB::isError($historyArr) && is_array($historyArr) && count($historyArr)>=$this->_param;
    						// if condition is not satisfied for one course, stop checking
    						if ($retval === false) break;
    					}
    				}
    			}
    		}
			$GLOBALS['dh']->disconnect();

			if ($this->getLogToFile()) {
				$logLines = [
					__FILE__.': '.__LINE__,
					'running '.__METHOD__,
					print_r(['instance_id' => $id_course_instance, 'student_id' => $id_student], true),
					sprintf("survey answered %d times, param is %d", count($historyArr), $this->_param),
					__METHOD__.' returning ' . ($retval ? 'true' : 'false')
				];
				logToFile($logLines);
			}

    		return $retval;
    	} else {
	    	// if no module test return true
    		return true;
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