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

class QuestionStandardFormTest extends QuestionFormTest {

	protected function content() {
		$this->common_elements();

		//variante
		$variant = 'variant';
		$options = array(
			ADA_NORMAL_TEST_VARIATION => translateFN('Nessuna variante'),
			ADA_ERASE_TEST_VARIATION => translateFN('Variante cancellazione'),
			ADA_HIGHLIGHT_TEST_VARIATION => translateFN('Variante evidenziazione'),
		);

		if (isset($this->data[$variant])) {
			$defaultValue = $this->data[$variant];
		}
		else {
			$defaultValue = ADA_NORMAL_TEST_VARIATION;
		}
        $this->addSelect($variant,translateFN('Variante visualizzazione esercizio').':',$options,$defaultValue);
    }
}