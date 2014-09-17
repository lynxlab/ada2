<?php
/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling Classrooms
 *
 * @author giorgio
 */
class FormClassrooms extends FForm {

	public function __construct($data, $formName=null, $action=null) {
		parent::__construct();
		
		if (!is_null($formName)) {
			$this->setName($formName);
			/**
			 * Do some jQuery DOM manipulation to enable
			 * the semantic-ui CSS only checkbox toggle
			 */
			$semanticToggleCheckBoxJS = "
				\$j('form[name=\"$formName\"]').find('input[type=\"checkbox\"]').each(function(index,el){					
					var label = \$j(el).parents('li').first().find('label').html();
					\$j(el).parents('li').first().find('label').html('<span style=\"display:block; margin-top:-.25em;\">'+label+'</span>');
					var inner = \$j(el).parents('li').first().html();
					\$j(el).parents('li').first().html('<div style=\"width:100%;\" class=\"ui toggle checkbox\">'+inner+'</div>');
				});
			";
			
			$this->setCustomJavascript($semanticToggleCheckBoxJS);
		}
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('id_classroom');
		$this->addHidden('id_venue');
		
		$this->addTextInput('name', translateFN('Nome'))->setRequired()
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('venue_name', translateFN('Luogo'))->setRequired()
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('seats', translateFN('Numero di Posti'))->withData(0)
			 ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
		
		$this->addTextInput('computers', translateFN('Numero di Computer'))->withData(0)
			 ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
		
		$internet = FormControl::create(FormControl::INPUT_CHECKBOX, 'internet', translateFN('Internet'))->withData(1);
		$wifi = FormControl::create(FormControl::INPUT_CHECKBOX, 'wifi', translateFN('Wi-Fi'))->withData(1);
		$projector = FormControl::create(FormControl::INPUT_CHECKBOX, 'projector', translateFN('Proiettore'))->withData(1);
		$mobility = FormControl::create(FormControl::INPUT_CHECKBOX, 'mobility_impaired', translateFN('Accesso disabili'))->withData(1);

		$fieldSet = FormControl::create(FormControl::FIELDSET, 'options', translateFN('Comodità'));
		$fieldSet->withData(array($internet,$wifi,$projector,$mobility));
		
		$this->addControl($fieldSet);
		
		
		$this->addTextInput('hourly_rate', translateFN('Tariffa Oraria'))->withData('0.00')
			 ->setValidator(FormValidator::NON_NEGATIVE_MONEY_VALIDATOR);
		
		$this->fillWithArrayData($data);
	}
} // class ends here
