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

class SwitcherFormTest extends FormTest {
	protected $id_course;

	public function __construct($id_course) {
		$this->id_course = $id_course;

		parent::__construct();
	}

	protected function content() {
		$dh = $GLOBALS['dh'];

		$this->setName('switcherForm');

		//lista dei test presenti
		$test_list = $dh->test_getCourseTest(array('id_corso'=>$this->id_course)); //getting already present test list
		$test_ids = array();
		if (!empty($test_list)) {
			$checkboxes = array();
			foreach($test_list as $v) {
				$checkboxes[$v['id_test']] = $v['titolo'];
				$test_ids[] = $v['id_test'];
			}
			$this->addCheckboxes('delete_test[]', translateFN('Seleziona i sondaggi da rimuovere dal corso').':', $checkboxes, null);
		}

		//lista dei test da aggiungere
		$tmp_tests = $dh->test_getNodes(array('id_nodo_parent'=>null,'tipo'=>'LIKE '.ADA_TYPE_SURVEY.'%')); //getting available test
		$options = array(''=>' --- ');
		$empty = true;
		foreach($tmp_tests as $v) {
			if (!in_array($v['id_nodo'],$test_ids)) {
				$options[$v['id_nodo']] = $v['titolo'];
				$empty = false;
			}
		}

		if ($empty) {
			$options = array(''=>translateFN('Nessun questionario presente'));
			$empty = false;
		}
		if (!$empty) {
			$this->addSelect('id_test',translateFN('Seleziona il sondaggio da aggiungere al corso').':',$options,'');
		}
	}
}
