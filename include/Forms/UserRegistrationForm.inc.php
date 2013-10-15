<?php
/**
 * UserRegistrationForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';
include_once ('nationList.inc.php');

/**
 * Description of UserRegistrationForm
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserRegistrationForm extends FForm
{
    public function  __construct($cod=FALSE, $action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('registration');

        $this->addTextInput('nome', translateFN('Nome'))
             ->setRequired()
             ->setValidator(FormValidator::FIRSTNAME_VALIDATOR);

        $this->addTextInput('cognome', translateFN('Cognome'))
             ->setRequired()
             ->setValidator(FormValidator::LASTNAME_VALIDATOR);

        $this->addTextInput('birthdate', translateFN('Data di nascita'))
        	 ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextInput('email', translateFN('Email'))
             ->setRequired()
             ->setValidator(FormValidator::EMAIL_VALIDATOR);

        $this->addSelect(
            'sesso',
             translateFN('Genere'),
             array(
                 0 => translateFN('Scegli un genere'),
                 'M' => translateFN('Maschio'),
                 'F' => translateFN('Femmina')
             ),
             0);

        $this->addTextInput('matricola', translateFN('numero di matricola (se studente)'));
/*
 * 
        if ($cod) {
            $this->addTextInput('codice', translateFN('Codice'))
                 ->setRequired()
                 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        }

 * 
 */
    }
}
