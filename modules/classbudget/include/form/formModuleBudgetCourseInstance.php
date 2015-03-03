<?php
/**
 * CLASSBUDGET MODULE.
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classbudget
 * @version			0.1
 */

require_once(ROOT_DIR.'/include/Forms/CourseInstanceForm.inc.php');
require_once  MODULES_CLASSBUDGET_PATH . '/include/AMAClassbudgetDataHandler.inc.php';

/**
 * class for handling Course Instance having module budget
 *
 * @author giorgio
 */
class FormModuleBudgetCourseInstance extends CourseInstanceForm {
	
	public $prefix;
	
	/**
	 * extends the base CourseInstanceForm by adding budget related fields
	 */
	public function  __construct() {
		parent::__construct();
		$fields = array();
		$this->prefix = AMAClassbudgetDataHandler::$PREFIX;
		$fields[] = FormControl::create(FormControl::INPUT_TEXT, $this->prefix.'budget', translateFN('budget'))
					->setValidator(FormValidator::NON_NEGATIVE_MONEY_VALIDATOR)
					->withData('0.00');
		$fields[] = FormControl::create(FormControl::TEXTAREA, $this->prefix.'references', translateFN('riferimenti'));
		$fields[] = FormControl::create(FormControl::TEXTAREA, $this->prefix.'notes', translateFN('note'));
		$fields[] = FormControl::create(FormControl::INPUT_HIDDEN, $this->prefix.'budget_instance_id','');
		
		$this->addFieldset(translateFN('Budget Istanza'),$this->prefix.'instance_budget')->withData($fields);
	}
} // class ends here
