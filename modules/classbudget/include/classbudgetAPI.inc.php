<?php
/**
 * Classbudget Management Class
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classbudget
 * @version			0.1
 */

/**
 * class for managing Classbudget
 *
 * @author giorgio
 */
// require_once MODULES_CLASSBUDGET_PATH . '/config/config.inc.php';
require_once MODULES_CLASSBUDGET_PATH . '/include/AMAClassbudgetDataHandler.inc.php';

class classbudgetAPI {

	private $_dh;

	/**
	 * constructor
	 */
	public function __construct() {
		if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
		$this->_dh = AMAClassbudgetDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	}

	/**
	 * destructor
	 */
	public function __destruct() {
		$this->_dh->disconnect();
	}

	/**
	 * Saves a budget object for the course instance
	 *
	 * @param budgetCourseInstanceManagement $object data to be saved
	 *
	 * @return number inserted or updated row id
	 *
	 * @access public
	 */
	public function saveBudgetCourseInstance(budgetCourseInstanceManagement $object) {
		return (int) $this->_dh->saveBudgetCourseInstance($object->toArray());
	}

	/**
	 * Gets a budget object for a course instance
	 *
	 * @param number $course_instance_id the instance id to load object for
	 *
	 * @return budgetCourseInstanceManagement|AMA_Error
	 *
	 * @access public
	 */
	public function getBudgetCourseInstance($course_instance_id) {
		$dataAr = $this->_dh->getBudgetCourseInstanceByInstanceID($course_instance_id);
		if (!AMA_DB::isError($dataAr)) {
			require_once MODULES_CLASSBUDGET_PATH . '/include/management/budgetCourseInstanceManagement.inc.php';
			return new budgetCourseInstanceManagement($dataAr);
		} else return $dataAr;
	}

	/**
	 * Deletes a budget row for a course instance
	 *
	 * @param number $course_instance_id the instance id to delete row for
	 *
	 * @return AMA_Error|number of affected rows
	 *
	 * @access public
	 */
	public function deleteBudgetCourseInstance ($course_instance_id) {
		return $this->_dh->deleteBudgetCourseInstanceByInstanceID($course_instance_id);
	}


} // class ends here