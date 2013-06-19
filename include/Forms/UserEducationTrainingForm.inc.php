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
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName($formName);
        $this->setSubmitValue(translateFN('Salva'));
        
        $this->addHidden('saveAsMultiRow')->withData(1);
        $this->addHidden('_isSaved')->withData(0);
        $this->addHidden('extraTableName')->withData($formName);           
        $this->addHidden('studente_id_utente_studente');
        
        $this->addTextInput($formName::getKeyProperty(),'(id key property if exists) hidden2');       
        
        $this->addTextInput('eduStartDate', translateFN('Data di inizio'))
        	 ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);
        
        $this->addTextInput('eduEndDate', translateFN('Data di fine'))
        	 ->setRequired()
        	 ->setValidator(FormValidator::DATE_VALIDATOR);
        
        $this->addTextInput('title', translateFN('Titolo'))
        	 ->setRequired()
        	 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        
        $this->addTextInput('schoolType', translateFN('Tipo di Scuola'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('mark', translateFN('Mark'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('organizationProvided', translateFN('Provider'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('organizationAddress', translateFN('Indirizzo'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('organizationCity', translateFN('Citta\''))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('organizationCountry', translateFN('Paese'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
        
        $this->addTextInput('principalSkills', translateFN('Capacit&agrave; Principali'))
        	 ->setValidator(FormValidator::DEFAULT_VALIDATOR);
    }
}
