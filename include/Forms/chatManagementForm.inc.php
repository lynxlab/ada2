<?php
/**
 * ChatManagementForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    maurizio graffio mazzoneschi <graffio@lynxlab.com>
 * @copyright Copyright (c) 2010-2012, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';
/**
 * Description of ChatManagementForm
 *
 * @package   Default
 * @author    maurizio graffio mazzoneschi <graffio@lynxlab.com>
 * @copyright Copyright (c) 2010-2012, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ChatManagementForm extends FForm
{
    public function  __construct() {
        parent::__construct();
        
        $this->addTextInput('id_room', translateFN('ChatRoom ID'))
             ->setAttribute('readonly', 'readonly');

        $this->addTextInput('chat_title', translateFN('Titolo'))
             ->setRequired()
             ->setValidator(FormValidator::FIRSTNAME_VALIDATOR);

        $this->addTextInput('chat_topic', translateFN('Argomento'))
             ->setRequired()
             ->setValidator(FormValidator::DEFAULT_VALIDATOR);

        $this->addTextArea('welcome_msg', translateFN('Messaggio di benvenuto'))
             ->setValidator(FormValidator::DEFAULT_VALIDATOR);

        $this->addTextInput('chat_owner', translateFN('Proprietario'))
             ->setRequired()
             ->setAttribute('readonly', 'readonly')
             ->setValidator(FormValidator::USERNAME_VALIDATOR);

        $this->addTextInput('actual_chat_type', translateFN('Tipo'))
             ->setAttribute('readonly', 'readonly');

        if(!isset($chatroom_started) || (isset($chatroom_started) && !$chatroom_started)){
             $this->addSelect(
                'new_chat_type',
                 translateFN('Nuovo tipo'),
                 array(
                     '-- select --'=>'-- select --',
//                     'Privata' => translateFN('Privata'),
                     'Classe' => translateFN('Classe'),
                     'Pubblica'=>translateFN('Pubblica')
                 ),0);
          }

        $this->addTextInput('max_users', translateFN('Numero di utenti'))
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);

        $this->addTextInput('start_day', translateFN('Data di apertura (gg/mm/aaaa)'))
             ->setAttribute('readonly', 'readonly')
             ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextInput('start_time', translateFN('Ora di apertura (oo:mm:ss)'))
             ->setAttribute('readonly', 'readonly')
             ->setValidator(FormValidator::TIME_VALIDATOR);

        $this->addTextInput('end_day', translateFN('Data di chiusura (gg/mm/aaaa)'))
             ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextInput('end_time', translateFN('Ora di chiusura (oo:mm:ss)'))
             ->setValidator(FormValidator::TIME_VALIDATOR);

        $this->addTextInput('id_course_instance', translateFN('ID Classe'))
             ->setRequired()
             ->setAttribute('readonly', 'readonly')
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
    }
}
