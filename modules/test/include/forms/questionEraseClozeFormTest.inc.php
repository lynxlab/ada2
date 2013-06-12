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

class QuestionEraseClozeFormTest extends QuestionFormTest {

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

		//variante domanda erase
		$variant = 'variant';
		$options = array(
			ADA_ERASE_TEST_ERASE => translateFN('Eliminazione'),
			ADA_HIGHLIGHT_TEST_ERASE => translateFN('Evidenziazione'),
		);

		if (isset($this->data[$variant])) {
			$defaultValue = $this->data[$variant];
		}
		else {
			$defaultValue = ADA_ERASE_TEST_ERASE;
		}
        $this->addSelect($variant,translateFN('Variante esercizio').':',$options,$defaultValue);
    }
}