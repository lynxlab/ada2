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

class DeleteFormTest extends FormTest {
	protected $message;

	public function __construct($message) {
		$this->message = $message;

		parent::__construct();		
	}

	protected function content() {
		$this->setName('deleteForm');

		//cancella
		$radios = array(
			1 => translateFN('Si'),
			0 => translateFN('No'),
		);
		$this->addRadios('delete',$this->message,$radios,0);
	}
}