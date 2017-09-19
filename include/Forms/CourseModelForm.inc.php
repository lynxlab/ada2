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
require_once 'lib/classes/FForm.inc.php';
/**
 *
 */
class CourseModelForm extends FForm {
    public function  __construct($authors, $languages) {
        parent::__construct();
        //$authors = array_merge(array(0 => translateFN('Scegli un autore per il corso')), $authors);
        //$languages = array_merge(array(0 => translateFN('Scegli una lingua per il corso')), $languages);

        $authors[0] = translateFN('Scegli un autore per il corso');
        $languages[0] = translateFN('Scegli una lingua per il corso');


        $this->addSelect('id_utente_autore',translateFN('Autore'),$authors,0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        $this->addSelect('id_lingua', translateFN('Lingua del corso'),$languages,0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        $this->addHidden('id_corso');

        $this->addHidden('id_layout')->withData(0);

        $this->addTextInput('nome', translateFN('Codice corso'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('titolo', translateFN('Titolo'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addHidden('data_creazione');
        //$this->addTextInput('data_creazione', translateFN('Data creazione'));

        $this->addTextInput('data_pubblicazione', translateFN('Data pubblicazione'));
//             ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextarea('descrizione', translateFN('Descrizione'))
             ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);

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

        $this->addTextInput('crediti', translateFN('Crediti corso'))
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
        
        $this->addTextInput('duration_hours', translateFN('Durata prevista in ore'))
        	->setRequired()
        	->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        
        /* if isset $_SESSION['service_level'] it means that the istallation supports course type */
       
        if(isset($_SESSION['service_level'])){
        	/**
        	 * @author giorgio 23/apr/2015
        	 * 
        	 * switcher can add public courses only if:
        	 * - it's a multiprovider having session tester equals to PUBLIC tester
        	 * - it's not multiprovider
        	 */
        	$shownServiceTypes = array();        	
        	foreach ($_SESSION['service_level'] as $key=>$val) {
        		if ((bool)$_SESSION['service_level_info'][$key]['isPublic']) {
        			// this coud have been an OR, but looks more readable this way
        			if (MULTIPROVIDER && $_SESSION['sess_selected_tester']==ADA_PUBLIC_TESTER) {
        				$shownServiceTypes[$key]=$val;
        			} else if (!MULTIPROVIDER) {
        				$shownServiceTypes[$key]=$val;
        			}
        		} else $shownServiceTypes[$key]=$val;
        	}
        	
            $desc = translateFN('Tipo di corso').':';
            $this->addSelect('service_level',$desc,$shownServiceTypes,reset($shownServiceTypes))
            ->setRequired();  
        }


        $this->addHidden('id_nodo_iniziale')->withData(0);
        $this->addHidden('id_nodo_toc');
        $this->addHidden('media_path');
        $this->addHidden('static_mode');
    }

    /**
     * Adds an upload file section to the form
     */
    public function addFileSection() {
    	$fileSection = array();
    	$fileSection[] = FormControl::create(FormControl::INPUT_HIDDEN, 'fieldUploadName', '')->withData('uploadFile');
    	$fileSection[] = FormControl::create(FormControl::INPUT_TEXT, 'filetitle', translateFN('Titolo del file'));
    	$fileSection[] = FormControl::create(FormControl::INPUT_TEXT, 'filedescr', translateFN('Descrizione del file'));
    	$fileSection[] = FormControl::create(FormControl::INPUT_TEXT, 'filekeywords', translateFN('Parole chiave del file'));
    	$fileSection[] = FormControl::create(FormControl::INPUT_FILE, 'uploadFile', translateFN('Scegli un file'));
    	$fileSection[] = FormControl::create(FormControl::INPUT_BUTTON, 'saveFileButton', translateFN('Carica file'))->setAttribute('disabled', 'disabled');
    	$this->addFieldset(translateFN('Carica un file'),'fileData')->withData($fileSection);
    	$this->addHidden('fileDeleteConfirmMSG')->withData(translateFN('Confermi la cancellazione del file?'));
    }
}