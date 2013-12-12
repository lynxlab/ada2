<?php
/**
 * UserJobExperienceForm file
*
* PHP version 5
*
* @package   Default
* @author    giorgio <g.consorti@lynxlab.com>
* @copyright Copyright (c) 2010-2010, Lynx s.r.l.
* @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
*/
require_once 'lib/classes/FForm.inc.php';


/**
 * Description of UserMoreUserFieldsForm
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserOneToManyDataSampleForm extends FForm
{
	public function  __construct($action=NULL) {
		parent::__construct();

		$formName = 'moreUserFields';
		$classObj = new $formName();
		$fieldList = $formName::getFields();

		if ($action != NULL) {
			$this->setAction($action);
		}
		$this->setName($formName);		

		// pls don't touch theese hidden fields
		$this->addHidden('saveAsMultiRow')->withData(1);
		$this->addHidden('_isSaved')->withData(0);
		$this->addHidden('extraTableName')->withData($formName);
		$this->addHidden($formName::getForeignKeyProperty());
		$this->addHidden($formName::getKeyProperty())->withData(0);

		// the firsrt two fields are 'service' fields, so start at index 2
		$fieldIndex = 2;
		
		/**
		 * submit button text label, you can use your own here
		 */
		$this->setSubmitValue(translateFN('Salva'));
		
		/**
		 * YOUR OWN FIELDS STARTS HERE, if you're following the example,
		 * it's all about defining which type of controls you want, forget
		 * about labels and fields name and id.
		 */
		
		/**
		 * @author giorgio 22/nov/2013
		 * uncomment and edit below to add/edit fields
		 * from the one to MANY table storing extra user data
		 */
		
		// 3
// 		$this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
// 		->setRequired()
// 		->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);		
		// 4
// 		$this->addSelect($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++), 
// 				array ('0'=>'First Choice','1'=>'Second Choice','2'=>'Third Choice')
// 				, 0)
// 			  ->setRequired()
// 		      ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);		
		// 5
// 		$this->addRadios($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++),
// 				array ('0'=>'Don\'t agree','1'=>'Agree', '2'=>'Unsure'), 0)->setRequired();
	}
}
?>