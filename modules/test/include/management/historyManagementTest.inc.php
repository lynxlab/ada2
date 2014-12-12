<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

require_once(MODULES_TEST_PATH.'/include/management/tutorManagementTest.inc.php');
class HistoryManagementTest extends TutorManagementTest {
	protected $id_student;

	/**
	 * constructs tutor management and configure it according to parameters,
	 * invoking parent constructor
	 *
	 * @param string $what 'test' or 'survey' string
	 * @param Course $courseObj course object reference
	 * @param Course_instance $course_instanceObj course instance object reference
	 * @param int $id_student id student
	 * @param int $id_test id test
	 * @param int $id_history_test id history test
	 */
	public function __construct($what, $courseObj, $course_instanceObj, $id_student = null, $id_test = null, $id_history_test = null) {
		parent::__construct($what, $courseObj, $course_instanceObj, $id_student, $id_test, $id_history_test);

		$this->id_student = $id_student;
	}

	/**
	 * function that return list of students that sent test or survey
	 *
	 * @global db $dh
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_students() {
		$array = array(
			'html' => translateFN(),
			'path' => translateFN('Storico').' '.ucfirst($this->plurale),
			'title' => translateFN('Storico').' '.ucfirst($this->plurale),
		);
		return $array;
	}

	/**
	 * function that return list of test sent test or survey by student
	 *
	 * @global db $dh
	 *
	 * @param boolean $student if true switch scope from tutor to student
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_tests($student = false) {
		$array = parent::list_tests(true);

		$array['html'] = str_replace('&id_student='.$this->id_student,'',$array['html']);
		$array['path'] = translateFN('Storico').' '.ucfirst($this->plurale);
		$array['title'] = translateFN('Storico').' '.ucfirst($this->plurale);
		return $array;
	}

	/**
	 * function that return list of history test sent test or survey by student
	 *
	 * @global db $dh
	 *
	 * @param boolean $student if true switch scope from tutor to student
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_history_tests($student = false) {
		$array = parent::list_history_tests(true);

		$array['html'] = str_replace('&id_student='.$this->id_student,'',$array['html']);
		$array['path'] = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'">'.translateFN('Storico').' '.ucfirst($this->plurale).'</a> &gt; '.$this->test['titolo'];
		$array['title'] = translateFN('Storico').' '.ucfirst($this->plurale);
		return $array;
	}

	/**
	 * function that return a specific history test
	 *
	 * @global db $dh
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function view_history_tests() {
		$array = parent::view_history_tests();

		$array['path'] = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'">'.translateFN('Storico').' '.ucfirst($this->plurale).'</a> &gt; <a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'&id_test='.$this->test['id_nodo'].'">'.$this->test['titolo'].'</a> &gt; '.translateFN('Tentativo'). ' #'.$this->history_test['id_history_test'];
		$array['title'] = translateFN('Storico').' '.ucfirst($this->plurale);
		return $array;
	}
}
