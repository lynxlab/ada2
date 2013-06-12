<?php
/**
 * UserSubscriptionForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'UserRegistrationForm.inc.php';

class UserSubscriptionForm extends UserRegistrationForm {
    public function  __construct() {
        parent::__construct();
        $this->addTextInput('username', translateFN('Nome utente'))
             ->setRequired()
             ->setValidator(FormValidator::EMAIL_VALIDATOR);

        $this->addPasswordInput('password', translateFN('Password'))
             ->setRequired()
             ->setValidator(FormValidator::PASSWORD_VALIDATOR);

        $this->addPasswordInput('passwordcheck', translateFN('Conferma la password'))
             ->setRequired()
             ->setValidator(FormValidator::PASSWORD_VALIDATOR);

        $this->addSelect('tipo', 
                translateFN('Tipo Utente'),
                array(
                    0 => translateFN('Scegli il tipo...'),
                    AMA_TYPE_AUTHOR => translateFN('Autore'),
                    AMA_TYPE_STUDENT => translateFN('Studente'),
                    AMA_TYPE_TUTOR => translateFN('Tutor')
                    ),
                0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
    }
}