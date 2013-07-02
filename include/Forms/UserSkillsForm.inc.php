<?php
/**
 * UserSkillsForm file
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
 * Description of UserSkillsForm
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserSkillsForm extends FForm
{
    public function  __construct($action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('skills');
        $this->setSubmitValue(translateFN('Salva'));
        
        // $this->addFileInput('picture', translateFN('Immagine'));

        $this->addTextInput('picture', translateFN('Immagine'))
        ->setRequired()
        ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('preferredJob', translateFN('Lavoro Preferito'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        
        $this->addTextInput('preferredJobCodes', translateFN('Codice Lavoro Preferito'))
        ->setRequired()
        ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        
        $this->addTextarea('socialSkills', translateFN('Capacit&agrave; sociali'))
        		->setRequired()
        		->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextarea('organizationalSkills', translateFN('Capacit&agrave; organizzative'))
        ->setRequired()
        ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextarea('technicalSkills', translateFN('Capacit&agrave; tecniche'))
        ->setRequired()
        ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextarea('computerSkills', translateFN('Capacit&agrave; al computer'))
        ->setRequired()
        ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextarea('artisticSkills', translateFN('Capacit&agrave; artistiche'))
        ->setRequired()
        ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextarea('otherSkills', translateFN('Altre capacit&agrave;'))
        ->setRequired()
        ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
        $this->addTextInput('drivingLicences', translateFN('Patenti'))
        ->setRequired()
        ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
    }
}
