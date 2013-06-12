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

class AnswersStandardFormTest extends FormTest {
	protected $question;
	protected $open_answer = false;
	protected $case_sensitive = false;

	public function __construct($data,$question,$case_sensitive,$open_answer) {
		$this->question = $question;
		$this->open_answer = $open_answer;
		$this->case_sensitive = $case_sensitive;

		parent::__construct($data);
	}

	protected function content() {
		$this->setName('answersForm');

		$defaultData = array('case_sensitive'=>$this->question['tipo']{5});

		require_once(MODULES_TEST_PATH.'/include/forms/controls/answerHeaderControlTest.inc.php');
		$this->addControl(new AnswerHeaderControlTest($this->open_answer, $this->case_sensitive));

		require_once(MODULES_TEST_PATH.'/include/forms/controls/answerControlTest.inc.php');
		if (!empty($this->data)) {
			foreach($this->data as $k=>$v) {
				$this->addControl(new AnswerControlTest($this->open_answer, $this->case_sensitive,$v['record']))->withData($v);
			}
		}
		else {
			$this->addControl(new AnswerControlTest($this->open_answer, $this->case_sensitive))->withData($defaultData);
		}
		//hidden row
		$this->addControl(new AnswerControlTest($this->open_answer, $this->case_sensitive,null,true))->withData($defaultData);

		require_once(MODULES_TEST_PATH.'/include/forms/controls/answerFooterControlTest.inc.php');
		$this->addControl(new AnswerFooterControlTest());
	}
}