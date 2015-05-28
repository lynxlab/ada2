<?php
/**
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * @author giorgio 11/nov/2013
 * must include all of the following to use QuestionDragDropClozeTest::extractTitlesFromData
 */
require_once MODULES_TEST_PATH . '/include/question.class.inc.php';
require_once MODULES_TEST_PATH . '/include/questionCloze.class.inc.php';
require_once MODULES_TEST_PATH . '/include/questionDragDropCloze.class.inc.php';

class QuestionDragDropClozeFormTest extends QuestionFormTest {
	
	private  $_clozeType;

	public function __construct($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType, $cloze_type) {
		$this->_clozeType = $cloze_type;
		parent::__construct($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
	}
	
	protected function content() {
		$this->common_elements();

		//tipologia domanda cloze
		$box = 'box_position';
		$options = array(
			ADA_TOP_TEST_DRAGDROP => translateFN('Sopra il testo'),
			ADA_RIGHT_TEST_DRAGDROP => translateFN('A destra del testo'),
			ADA_BOTTOM_TEST_DRAGDROP => translateFN('Sotto il testo'),
			ADA_LEFT_TEST_DRAGDROP => translateFN('A sinistra del testo'),
		);

		if (isset($this->data[$box])) {
			$defaultValue = $this->data[$box];
		}
		else {
			$defaultValue = ADA_RIGHT_TEST_DRAGDROP;
		}
        $this->addSelect($box,translateFN('Posizione box drag\'n\'drop').':',$options,$defaultValue);

        /**
         * giorgio 28/gen/2014
         * 
         * ADA_DRAGDROP_TEST_SIMPLICITY and ADA_SLOT_TEST_SIMPLICITY
         * both share the same form, but ADA_SLOT_TEST_SIMPLICITY
         * cannot add Drag and Drop boxes at all!
         */
        
        if ($this->_clozeType == ADA_DRAGDROP_TEST_SIMPLICITY) {   
	        $btnAdd = FormControl::create(FormControl::INPUT_BUTTON, 'add_dndTitle', translateFN('Aggiungi Box'));
	        $btnAdd->setAttribute('onclick', 'javascript:addBoxTitleElement();');
	        
	        $btnDel = FormControl::create(FormControl::INPUT_BUTTON, 'del_dndTitle', translateFN('Elimina Box'));
	        $btnDel->setAttribute('onclick', 'javascript:removeLastBoxTitleElement();');
	        
	        $this->addFieldset('','btnFieldset')->withData(array($btnAdd, $btnDel));
        }
        
        
        $dragdropTitles = QuestionDragDropClozeTest::extractTitlesFromData($this->data['titolo_dragdrop']);        
		if (is_null($dragdropTitles) || empty($dragdropTitles)) $dragdropTitles[1]='';

		if ($this->_clozeType == ADA_DRAGDROP_TEST_SIMPLICITY) {
        foreach ($dragdropTitles as $dndKey=>$dndTitle)
	        {        	
				//titolo box drag'n'drop
		        $this->addTextInput('titolo_dragdrop_'.$dndKey, '#'. $dndKey .'. '.translateFN('Titolo box drag\'n\'drop (lasciare vuoto se non usato)').':')
		        	 ->setName('titolo_dragdrop['.$dndKey.']')
		        	 ->withData($dndTitle);
//	 	             ->withData($this->data['titolo_dragdrop']);
	        }
		} else if ($this->_clozeType == ADA_SLOT_TEST_SIMPLICITY) {
			//titolo box drag'n'drop
			$this->addTextInput('titolo_dragdrop', translateFN('Titolo box drag\'n\'drop (lasciare vuoto se non usato)').':')
			->setName('titolo_dragdrop')
			->withData($this->data['titolo_dragdrop']);
			
		}
        
        
    }
    

}