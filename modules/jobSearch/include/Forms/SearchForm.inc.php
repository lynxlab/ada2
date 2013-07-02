<?php
/**
 * CourseModelForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php';
/**
 *
 */
class SearchForm extends FForm {
    public function  __construct() {
        parent::__construct();

        $this->addTextInput('keywords', translateFN('Qualifica/Corso'));

/*             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
 * 
 */
        $this->addTextInput('city', translateFN('Comune'));

        $this->addTextInput('qualification', translateFN('Titolo di studio richiesto'));
        
        $lookFor = array('jobs'=>translateFN('lavoro'),'training'=>translateFN('formazione'));
        $this->addSelect('op',translateFN('cerco'),$lookFor,'jobs')
             ->setRequired();
//             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
/*
        $this->addSelect('id_lingua', translateFN('Lingua del servizio'),$languages,0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
 * 
 
        $this->addHidden('id_corso');

        $this->addHidden('id_layout')->withData(0);

        $this->addTextInput('nome', translateFN('Codice servizio'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('titolo', translateFN('Titolo'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('data_creazione', translateFN('Data creazione'). ' '. translateFN('dd/mm/yyyy'));

        $this->addTextInput('data_pubblicazione', translateFN('Data pubblicazione').' ' . translateFN('dd/mm/yyyy'));

        $this->addTextarea('descrizione', translateFN('Descrizione'))
             ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);

        $this->addRadios(
                'common_also',
                sprintf(translateFN('Do you want create also a new general service')),
                array(0 => translateFN('No'), 1 => translateFN('Yes')),
                0);
*/


//        $this->addTextInput('id_nodo_iniziale', translateFN('Id nodo iniziale'))
//             ->withData(0)
//             ->setRequired()
//             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
//
//        $this->addTextInput('id_nodo_toc', translateFN('Id nodo toc'));
//
//        $this->addTextInput('media_path', translateFN('Media path'))
//             ->withData(MEDIA_PATH_DEFAULT);
//
//        $this->addTextInput('static_mode', translateFN('Static mode'));

/*        $this->addTextInput('crediti', translateFN('Crediti corso'))
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
 *

        $this->addHidden('id_nodo_iniziale')->withData(0);
        $this->addHidden('id_nodo_toc');
        $this->addHidden('media_path');
        $this->addHidden('static_mode');
 */
    }
}