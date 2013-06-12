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

class QuestionSelectClozeFormTest extends QuestionFormTest {

	protected function content() {
		$this->common_elements();

		//tipologia domanda cloze sinonimi
		$cloze_sinonimi = 'cloze_sinonimi';
		$options = array(
			ADA_NORMAL_SELECT_TEST => translateFN('No'),
			ADA_SYNONYM_SELECT_TEST => translateFN('Si'),
		);

		if (isset($this->data[$cloze_sinonimi])) {
			$defaultValue = $this->data[$cloze_sinonimi];
		}
		else {
			$defaultValue = ADA_NORMAL_SELECT_TEST;
		}
        $this->addRadios($cloze_sinonimi,translateFN('Nella tendina, mostrare un valore preimpostato?'),$options,$defaultValue);
    }
}