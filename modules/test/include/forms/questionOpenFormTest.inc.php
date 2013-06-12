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

class QuestionOpenFormTest extends QuestionFormTest {

	protected function content() {
		$this->common_elements();

		//punteggio massimo
		if (isset($this->data['correttezza'])) {
			$defaultValue = $this->data['correttezza'];
		}
		else {
			$defaultValue = 0;
		}
        $this->addTextInput('correttezza', translateFN('Punteggio massimo assegnabile').': ')
             ->setRequired()
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR)
             ->withData($defaultValue);
    }
}