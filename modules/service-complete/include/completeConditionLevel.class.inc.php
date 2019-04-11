<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */
require_once  MODULES_SERVICECOMPLETE_PATH . '/include/completeCondition.class.inc.php';

/**
 * class to implement the score complete condition.
 * The condition is satisfied if the user has scored
 * more than the specified test score.
 *
 * @author giorgio
 */
class CompleteConditionLevel extends CompleteCondition
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
	public static $description = 'Condizione soddisfatta se il livello dello studente è maggiore di quello impostato';

	/**
	 * description of the condition's own parameter
	 * NOTE: THIS GOES THROUGH translateFN WHEN IT GETS USED, SO NO INTERNAZIONALIZATION PROBLEM HERE
	 * cannot put here a call to translateFN because it's a static var
	 *
	 * @var string
	 */
	public static $paramDescription = 'Livello oltre il quale la condizione si intende soddisfatta. Scrivere <b>0</b> per dire il massimo livello possibile nel corso.';

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

		$user = MultiPort::findUser($id_student,$id_course_instance);
		$retval = false;

    	if ($user instanceof ADAUser && isset($GLOBALS['dh'])) {
    		$level = $user->get_student_level($id_student, $id_course_instance);
    		if (!AMA_DB::isError($level) && is_numeric($level)) {
    			if (intval($this->_param)===0) {
    				$course_id = $GLOBALS['dh']->get_course_id_for_course_instance($id_course_instance);
    				if (!AMA_DB::isError($course_id) && is_numeric($course_id)) {
    					$max_level = $GLOBALS['dh']->get_course_max_level($course_id);
    					if (!AMA_DB::isError($max_level) && is_numeric($max_level)) {
    						$retval = intval($level)>intval($max_level);
    					}
    				}
    			}
    			else if (is_numeric($this->_param)) $retval =  intval($level)>intval($this->_param);
    		}
		}

        if ($this->getLogToFile()) {
            $logLines = [
                __FILE__.': '.__LINE__,
                'running '.__METHOD__,
				print_r(['instance_id' => $id_course_instance, 'student_id' => $id_student], true),
				sprintf("level is %d, max_level is %d, param is %d", $level, isset($max_level) ? $max_level : 'not set',  $this->_param),
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