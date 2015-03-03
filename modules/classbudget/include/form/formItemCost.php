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

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * Class for building Item Cost form
 * 
 * @author giorgio
 *
 */
class FormItemCost extends FForm {
	
	public function __construct($data, $formName=null, $action=null) {
		parent::__construct();
				
		// translate all $availableCostItems
		array_walk($GLOBALS['availableCostItems'], function (&$value) { $value = translateFN($value); });
		 
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('cost_item_id');
		$this->addHidden('id_istanza_corso');
		
		$this->addTextInput('description', translateFN('Descrizione'))->setRequired()
		->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('price', translateFN('Costo unitario').' ('.ADA_CURRENCY_SYMBOL.')')->setRequired()
			 ->setValidator(FormValidator::NON_NEGATIVE_MONEY_VALIDATOR);
		
		$this->addSelect('applied_to', translateFN('Applicato a'), $GLOBALS['availableCostItems'], MODULES_CLASSBUDGET_COST_ITEM_UNA_TANTUM)->setRequired();
		
		$this->fillWithArrayData($data);
	}
} // class ends here
