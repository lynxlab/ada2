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

class AnswerHeaderControlTest extends FormControl {
	protected $open_answer = false;
	protected $show_case_sensitive = false;
	protected $modifiable = true;

	/**
	 * Answer Header Control Test
	 * It DOESN'T call parent constructor
	 *
	 * @param boolean $open_answer
	 * @param boolean $show_case_sensitive
	 * @param boolean $checked
	 */
	public function __construct($open_answer,$show_case_sensitive,$modifiable = true) {
        $this->_controlData = array();
        $this->_selected = FALSE;
        $this->_isRequired = FALSE;
        $this->_isMissing = FALSE;
		$this->_hidden = FALSE;

        $this->_validator = null;

		$this->open_answer = $open_answer;
		$this->show_case_sensitive = $show_case_sensitive;
		$this->modifiable = $modifiable;
    }

	/**
	 * Control rendering
	 *
	 * @return string
	 */
    public function render() {
		$i = 1;
		$div = CDOMElement::create('div', 'class:answers_header');

		if ($this->open_answer) {
			$cell = CDOMElement::create('div');
			$cell->setAttribute('class','cell other_answer');
			$cell->addChild(new CText(translateFN('Risposta aperta')));
			$div->addChild($cell);
		}
		$i++;

		$cell = CDOMElement::create('div');
		$cell->setAttribute('class','cell answer');
		if ($this->open_answer) {
			$cell->addChild(new CText(translateFN('Risposta').' / '.translateFN('Indicazione (risp. aperta)')));
		}
		else {
			$cell->addChild(new CText(translateFN('Risposta')));
		}
		$div->addChild($cell);
		$i++;

		$cell = CDOMElement::create('div');
		$cell->setAttribute('class','cell value');
		$cell->addChild(new CText(translateFN('Punteggio')));
		$div->addChild($cell);
		$i++;

		if ($this->show_case_sensitive) {
			$cell = CDOMElement::create('div');
			$cell->setAttribute('class','cell case_sensitive');
			$cell->addChild(new CText(translateFN('Ignora Maiuscole')));
			$checkbox = CDOMElement::create('checkbox', 'class:case_sensitive_control');
			$checkbox->setAttribute('onchange', 'check_case_sensitive(this);');
			$cell->addChild($checkbox);
			$div->addChild($cell);
		}
		$i++;

		if ($this->modifiable) {
			$cell = CDOMElement::create('div');
			$cell->setAttribute('class','cell operations');
			$cell->addChild(new CText(translateFN('Operazioni')));
			$div->addChild($cell);
			$i++;
		}

        return $div->getHtml();
    }
}