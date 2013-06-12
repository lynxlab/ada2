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

class AnswerFooterControlTest extends FormControl {
	protected $modifiable = true;

	/**
	 * Answer Footer Control Test
	 * It DOESN'T call parent constructor
	 *
	 */
	public function __construct($modifiable = true) {
        $this->_controlData = array();
        $this->_selected = FALSE;
        $this->_isRequired = FALSE;
        $this->_isMissing = FALSE;
		$this->_hidden = FALSE;

        $this->_validator = null;

		$this->modifiable = $modifiable;
    }

	/**
	 * Control rendering
	 *
	 * @return string
	 */
    public function render() {
		if ($this->modifiable) {
			$div = CDOMElement::create('div');
			$div->setAttribute('class','admin_link answers_footer');
			$div->addChild(new CText(' [ '));
			$a = CDOMElement::create('a');
			$a->addChild(new CText(translateFN('Aggiungi risposta')));
			$a->setAttribute('href','javascript:void(0);');
			$a->setAttribute('onclick','add_row(this);');
			$div->addChild($a);
			$div->addChild(new CText(' ] '));

			return $div->getHtml();
		}
		else {
			return '';
		}
    }
}