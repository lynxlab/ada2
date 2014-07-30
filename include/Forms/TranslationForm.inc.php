<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'lib/classes/FForm.inc.php';

class TranslationForm extends FForm
{
    public function  __construct($language=NULL) {
        parent::__construct();
        
        $this->setName('translatorForm');
        $this->addTextInput('t_name',translateFN('Cerca nella traduzione'));
        $this->addSelect('selectLanguage', translateFN('Selezionare una lingua '),$language , 1);
        $this->setMethod('POST');
        $j='return showDataTable();';
        $this->setOnSubmit($j);
   }
}



