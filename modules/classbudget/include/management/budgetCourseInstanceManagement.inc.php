<?php
/**
 * Course Instance Budget Management Class
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link				classbudget
 * @version			0.1
 */

require_once MODULES_CLASSBUDGET_PATH . '/include/management/abstractClassbudgetManagement.inc.php';
/**
 * This class and the formModuleBudgetCourseInstance.php
 * are responsible of managing the course instance budget
 * that can be edited directly using switcher/edit_instance.php
 * 
 * @author giorgio
 *
 */
 
class budgetCourseInstanceManagement extends abstractClassbudgetManagement {
	
	public $budget_instance_id;
	public $id_istanza_corso;
	public $budget;
	public $references;
	public $notes;
	
	
} // class ends here