<?php
/**
 * Cost Item Budget Management Class
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link				classbudget
 * @version			0.1
 */

require_once MODULES_CLASSBUDGET_PATH . '/include/management/abstractClassbudgetManagement.inc.php';

class costitemBudgetManagement extends abstractClassbudgetManagement {
	
	private $_cachedQuantities = array();
	protected $_actions;
	
	public function __construct($id_course_instance) {
		$this->_objType = 'item';
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
				translateFN('Descrizione'),
				translateFN('Applicato a'),
				translateFN('Costo unitario').' ('.ADA_CURRENCY_SYMBOL.')',
				translateFN('Totale').' ('.ADA_CURRENCY_SYMBOL.')'
		);
		if ($action==MODULES_CLASSBUDGET_EDIT) { 
			$this->headerRowLabels[] = translateFN('Azioni');
			/**
			 * add actions to be added as last column
			 */
			$editButton = CDOMElement::create('button','class:editButton '.$this->_objType);
			$editButton->setAttribute('title', translateFN('Clicca per modificare la voce di costo'));
			$editButton->setAttribute('onclick', 'javascript:editCostItem(<cost_'.$this->_objType.'_id>);');		
			$deleteButton = CDOMElement::create('button','class:deleteButton '.$this->_objType);
			$deleteButton->setAttribute('title', translateFN('Clicca per cancellare la voce di costo'));
			$deleteLink = 'deleteCostItem($j(this), <cost_'.$this->_objType.'_id> , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
			$deleteButton->setAttribute('onclick', 'javascript:'.$deleteLink);		
			$this->_actions =  array($editButton, $deleteButton);
		}
		
		$this->_tableCaption = translateFN('Costi Vari');
		
		$res = $GLOBALS['dh']->getItemCostForInstance($this->_id_course_instance);
		
		if (!AMA_DB::isError($res)) {
			$this->dataCostsArr = $this->_buildCostArrayFromRes($res);
			if (count($this->dataCostsArr)>=0) {
				$htmlObj = parent::run($action);
				if ($action==MODULES_CLASSBUDGET_EDIT) {
					// add the addnew button
					$buttonContainter = CDOMElement::create('div','class:buttonContainer');
					$newButton = CDOMElement::create('button');
					$newButton->setAttribute('class', 'newCostItemButton');
					$newButton->setAttribute('title', translateFN('Clicca per creare un nuova voce di costo'));
					$newButton->setAttribute('onclick', 'javascript:editCostItem(null,\''.$this->_objType.'\');');
					$newButton->addChild (new CText(translateFN('Nuova Voce di Costo')));
					$buttonContainter->addChild($newButton);
					$htmlObj->addChild($buttonContainter);
				}
			}
			else $htmlObj = null;
		} else {
			$htmlObj = CDOMElement::create('div','id:'.$this->_objType.'BudgetContainer,class:budgeterrorcontainer');
			$errorSpan = CDOMElement::create('span','class:'.$this->_objType.' budgeterror');
			$errorSpan->addChild (new CText(translateFN('Erorre nella lettura delle voci di costo')));
			$closeSpan =  CDOMElement::create('span','class:closeSpan');
			$closeSpan->setAttribute('onclick', 'javascript:closeDIV(\''.$this->_objType.'BudgetContainer\');');
			$closeSpan->addChild(new CText('x'));
			$htmlObj->addChild($errorSpan);
			$htmlObj->addChild($closeSpan);
		}
		return $htmlObj;
	}
	
	private function _calcQuantity($value) {
		if (isset($this->_cachedQuantities[$value])) return $this->_cachedQuantities[$value];
		$retval = 0;		
		switch ($value) {
			case MODULES_CLASSBUDGET_COST_ITEM_PER_STUDENT:
				$studentsList = $GLOBALS['dh']->get_students_for_course_instance($this->_id_course_instance);
				if (!AMA_DB::isError($studentsList)) $retval = count ($studentsList);
				break;
			case MODULES_CLASSBUDGET_COST_ITEM_PER_NODE:
				// count only nodes of the following types
				$getNodeTypes = array(ADA_LEAF_TYPE, ADA_GROUP_TYPE,
				ADA_LEAF_WORD_TYPE, ADA_GROUP_WORD_TYPE, ADA_PERSONAL_EXERCISE_TYPE);
				$courseID = $GLOBALS['dh']->get_course_id_for_course_instance($this->_id_course_instance);
				if (!AMA_DB::isError($courseID)) {
					$nodeList = $GLOBALS['dh']->find_course_nodes_list (null,'`tipo` IN ('.implode(',',$getNodeTypes).')',$courseID);
					if (!AMA_DB::isError($nodeList)) $retval = count($nodeList);
				}
				break;
			case MODULES_CLASSBUDGET_COST_ITEM_UNA_TANTUM:
			default:
				$retval =  1;
				break;
		}		
		$this->_cachedQuantities[$value] = $retval;
		return $retval;
	}
	
	/**
	 * 
	 * (non-PHPdoc)
	 * @see abstractClassbudgetManagement::_buildCostArrayFromRes()
	 */
	protected function _buildCostArrayFromRes ($res) {
		$retval = array();
		foreach ($res as $row) {
			$id = $row['cost_'.$this->_objType.'_id'];
			$retval[$id] = array();
			foreach ($row as $field=>$value) {
				switch ($field) {
					case 'cost_'.$this->_objType.'_id':
						$retval[$id][$field]= (isset($value) && is_numeric($value)) ? (int) $value : null;
						break;
					case 'description':
						$retval[$id]['displayname'] = $value ;
						break;
					case 'applied_to':
						$retval[$id]['applied-to-id'] = $value;
						$retval[$id]['totalqty'] = $this->_calcQuantity($value);
						$retval[$id]['formatqty'] = translateFN($GLOBALS['availableCostItems'][$value]).
													' ('.translateFN('trovati').' '.$retval[$id]['totalqty'].')';
						break;
					case 'price':
						$retval[$id]['unitprice'] = floatval($value);
						break;
					default:
						break;
				} // switch
			} // foreach $row
		} // foreach $res
		return $retval;
	}
} // class ends here