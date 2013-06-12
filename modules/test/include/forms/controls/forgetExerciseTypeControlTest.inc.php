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

class ForgetExerciseTypeControlTest extends FormControl {

	/**
	 * Answer Footer Control Test
	 * It DOESN'T call parent constructor
	 *
	 */
	public function __construct() {		
        $this->_controlData = array();
        $this->_selected = FALSE;
        $this->_isRequired = FALSE;
        $this->_isMissing = FALSE;
		$this->_hidden = FALSE;

        $this->_validator = null;
    }

	/**
	 * Control rendering
	 *
	 * @return string
	 */
    public function render() {
		$div = CDOMElement::create('div');
		$div->setAttribute('style','text-align:center;');
		$div->addChild(new CText(' [ '));
		$a = CDOMElement::create('a');
		$a->addChild(new CText(sprintf(translateFN('Cambia tipologia %s'),translateFN('Domanda'))));
		$a->setAttribute('href',$_SERVER['REQUEST_URI'].'&forgetExerciseType');
		$div->addChild($a);
		$div->addChild(new CText(' ] '));

        return $div->getHtml();
    }
}