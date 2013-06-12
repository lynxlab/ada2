<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class AnswerTest extends NodeTest
{
	const NODE_TYPE = ADA_LEAF_ANSWER;
	const CHILD_CLASS = null;

	protected $extra_answer;
	protected $compareFunction;

	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 * @param $data database record as array
	 *
	 */
	protected function configureProperties() {
		//first character
		if ($this->tipo{0} != self::NODE_TYPE) {
			return false;
		}

		//second character
		switch($this->tipo{1}) {
			default:
			case ADA_NO_OPEN_TEST_ANSWER:
				$this->extra_answer = false;
			break;
			case ADA_OPEN_TEST_ANSWER:
				$this->extra_answer = true;
			break;
		}

		//third character
		switch($this->tipo{2}) {
			default:
			case ADA_CASE_SENSITIVE_TEST:
				$this->compareFunction = "strcmp";
			break;
			case ADA_CASE_INSENSITIVE_TEST:
				$this->compareFunction = "strcasecmp";
			break;
		}

		//fourth character ignored because not applicable
		//fifth character ignored because not applicable
		//sixth character ignored because not applicable

		return true;
	}

	/**
	 * return necessaries html objects that represent the object
	 *
	 * @access protected
	 *
	 * @param $ref reference to the object that will contain this rendered object
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an object of CDOMElement
	 */
	protected function renderingHtml(&$ref = null,$feedback=false,$rating=false,$rating_answer=false) {
		return;
	}

	/**
	 * Render the object structure
	 *
	 * @access public
	 *
	 * @param $return_html choose the return type
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 *
	 * @return an object of CDOMElement or a string containing html
	 */
	public function render($return_html=true,$feedback=false,$rating=false,$rating_answer=false) {
		return;
	}
}
