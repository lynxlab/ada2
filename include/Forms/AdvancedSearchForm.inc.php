<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'lib/classes/FForm.inc.php';

class AdvancedSearchForm extends FForm
{
    public function  __construct($cod=FALSE, $action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('advancedForm');
        $this->addTextInput('s_node_name', translateFN('Titolo'));
        $this->addTextInput('s_node_title', translateFN('Keywords'));
        $this->addTextarea('s_node_text', translateFN('Testo'));
        $this->setMethod('GET');
        $this->addHidden('s_AdvancedForm');
        $j='javascript:disableForm()';
        $this->setOnSubmit($j);
        
        
        
    }
}



