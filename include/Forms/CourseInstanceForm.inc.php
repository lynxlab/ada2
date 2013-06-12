<?php
/**
 * CourseInstanceForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 * 
 */
class CourseInstanceForm extends FForm {
    public function  __construct() {
        parent::__construct();

        $this->addHidden('id_course');
        $this->addHidden('id_course_instance');
             
        $this->addHidden('id_layout')->withData(0);

        $this->addTextInput('title', translateFN('Titolo'));

        $this->addTextInput('price', translateFN('Prezzo (99999.99)'))
             ->setValidator(FormValidator::NON_NEGATIVE_MONEY_VALIDATOR);

        $this->addTextInput('data_inizio_previsto', translateFN('Data inizio previsto (gg/mm/aaaa)'))
             ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);

        $desc = translateFN('Iscrizioni aperte');
        $this->addRadios(
                'open_subscription',
                $desc,
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);

        $desc = translateFN('Iniziato');
        $this->addRadios(
                'started',
                $desc,
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);

        $this->addTextInput('durata', translateFN('Durata'))
             ->setRequired()
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
        $desc = translateFN('modo autoistruzione');
        
        $this->addRadios(
                'self_instruction',
                $desc,
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);
        $desc = translateFN("Iscrizione autonoma dell'utente");
        $this->addRadios(
                'self_registration',
                $desc,
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);
        

        $this->addTextInput('duration_subscription', translateFN("Durata iscrizione dell'utente in gg."))
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);

        $this->addTextInput('start_level_student', translateFN('Livello assegnato agli studenti (0 - 99)'))
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);


    }
}