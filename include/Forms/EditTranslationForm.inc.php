<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EditTranslationForm
 *
 * @author sara
 */
require_once 'lib/classes/FForm.inc.php';

class EditTranslationForm extends FForm
{
     public function  __construct() {
     parent::__construct();
        
        $this->setName('EditranslatorForm');
        $this->addTextarea('TranslationTextArea', translateFN('Modifica traduzione'));
        $j='return saveTranslation()';
        $this->setOnSubmit($j);
   }
}
