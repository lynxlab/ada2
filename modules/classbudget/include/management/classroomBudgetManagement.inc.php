<?php
/**
 * Classroom Budget Management Class
*
* @package			classbudget module
* @author			Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright		Copyright (c) 2015, Lynx s.r.l.
* @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link				classbudget
* @version			0.1
*/

require_once MODULES_CLASSBUDGET_PATH . '/include/management/abstractClassbudgetManagement.inc.php';

class classroomBudgetManagement extends abstractClassbudgetManagement {
	
	public function __construct($id_course_instance) {
		$this->_objType = 'classroom';
		parent::__construct(array('_id_course_instance'=>$id_course_instance));
	}
	
	/**
	 * Retreives the data from the DB and builds the HTML table
	 * for the Classroom Costs of the page
	 * 
	 * (non-PHPdoc)
	 * @see abstractClassbudgetManagement::run()
	 */
	public function run($action = null) {
		$this->headerRowLabels = array (
				translateFN('Luogo/Aula'),
				translateFN('Tempo di fruizione'),
				translateFN('Tariffa Oraria').' ('.ADA_CURRENCY_SYMBOL.')',
				translateFN('Totale').' ('.ADA_CURRENCY_SYMBOL.')'
		);
		$this->_tableCaption = translateFN('Costi Aule');
		
		$res = $GLOBALS['dh']->getClassroomCostForInstance($this->_id_course_instance);
		
		if (!AMA_DB::isError($res)) {
			$this->dataCostsArr = $this->_buildCostArrayFromRes($res);
			if (count($this->dataCostsArr)>0) $htmlObj = parent::run($action);
		} else {
			$htmlObj = CDOMElement::create('div','id:'.$this->_objType.'BudgetContainer,class:budgeterrorcontainer');
			$errorSpan = CDOMElement::create('span','class:'.$this->_objType.' budgeterror');
			$errorSpan->addChild (new CText(translateFN('Erorre nella lettura dei costi aule')));
			$closeSpan =  CDOMElement::create('span','class:closeSpan');
			$closeSpan->setAttribute('onclick', 'javascript:closeDIV(\''.$this->_objType.'BudgetContainer\');');
			$closeSpan->addChild(new CText('x'));
			$htmlObj->addChild($errorSpan);
			$htmlObj->addChild($closeSpan);
		}
		return $htmlObj;
		
	}
} // class ends here