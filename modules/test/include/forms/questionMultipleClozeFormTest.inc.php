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

class QuestionMultipleClozeFormTest extends QuestionFormTest {

	protected function content() {
		$this->common_elements();

		//apostrofo
		$cloze_apostrophe = 'cloze_apostrofo';
		$options = array(
			ADA_NO_APOSTROPHE_TEST_MULTIPLE => translateFN('Senza apostrofo'),
			ADA_APOSTROPHE_TEST_MULTIPLE => translateFN('Con apostrofo'),
		);

		if (isset($this->data[$cloze_apostrophe])) {
			$defaultValue = $this->data[$cloze_apostrophe];
		}
		else {
			$defaultValue = ADA_NO_APOSTROPHE_TEST_MULTIPLE;
		}
        $this->addSelect($cloze_apostrophe,translateFN('Tipologia di esercizio').':',$options,$defaultValue);
        
        /**
         * giorgio 02/gen/2014
         * select to choose if correct answer is single cell or whole column
         */
        $cloze_colAnswerMode = 'colAnswerMode';
        $options = array(
        	ADA_MULTIPLE_TEST_OK_SINGLE_CELL => translateFN('Risposta corretta nella casella'),
        	ADA_MULTIPLE_TEST_OK_WHOLE_COL => translateFN('Risposta corretta nella colonna')
        );
        
        if (isset($this->data[$cloze_colAnswerMode])) {
        	$defaultValue = $this->data[$cloze_colAnswerMode];
        } else {
        	$defaultValue = ADA_MULTIPLE_TEST_OK_SINGLE_CELL;
        }
        $this->addSelect($cloze_colAnswerMode, translateFN('Posizione risposta corretta').':', $options, $defaultValue);
        

		//posizione box
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

		//titolo drag'n'drop
		$titolo = 'titolo_dragdrop';
		if (isset($this->data[$titolo])) {
			$defaultValue = $this->data[$titolo];
		}
		else {
			$defaultValue = null;
		}
		$this->addHidden('titolo_dragdrop')->withData(htmlentities($defaultValue, ENT_COMPAT | ENT_HTML401, ADA_CHARSET));
    }
}