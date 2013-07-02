<?php
/**
 * UserEducationTrainingForm file
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
 * Description of UserEducationTrainingForm
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserEducationTrainingForm extends FForm
{
    public function  __construct($action=NULL) {
        parent::__construct();
        
        $formName = 'educationTraining';
        $classObj = new $formName();
        $fieldList = $formName::getFields();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName($formName);
        $this->setSubmitValue(translateFN('Salva'));
                
        // pls don't touch theese hidden fields
        $this->addHidden('saveAsMultiRow')->withData(1);
        $this->addHidden('_isSaved')->withData(0);
        $this->addHidden('extraTableName')->withData($formName);           
        $this->addHidden($formName::getForeignKeyProperty());        
        $this->addHidden($formName::getKeyProperty())->withData(0);
        
        // the firsrt two fields are 'service' fields, so start at index 2
        $fieldIndex = 2;
        // 3
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);
        // 4
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setRequired()
        	 ->setValidator(FormValidator::DATE_VALIDATOR);
        // 5
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setRequired()
        	 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        // 6
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 7
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 8
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 9
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 10
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 11
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        // 12
        $this->addTextInput($fieldList[$fieldIndex], $classObj->getLabel($fieldIndex++))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
    }
}
