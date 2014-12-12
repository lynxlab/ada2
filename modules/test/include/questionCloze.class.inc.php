<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

abstract class QuestionClozeTest extends QuestionTest
{
	protected $clozeType;
	protected $feedback = false;
	protected $rating = false;
	protected $rating_answer = false;

	const regexpCloze = '#<cloze[^>]*title="([0-9]+)"[^>]*>(.*)</cloze>#mU';

	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
		if (!parent::configureProperties()) {
			return false;
		}

		//fourth character
		$this->clozeType = $this->tipo{3};
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
		if (!$this->display) return new CText(''); //if we don't have to display this question, let's return an empty item
		return parent::renderingHtml($ref,$feedback,$rating,$rating_answer);
	}

	/**
	 * function used to match cloze replacements in text
	 *
	 * @access protected
	 * 
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an array with matches or false
	 */
	protected function getPreparedText($feedback=false,$rating=false,$rating_answer=false) {
		$this->feedback = $feedback;
		$this->rating = $rating;
		$this->rating_answer = $rating_answer;
		$preparedText = preg_replace_callback(self::regexpCloze, array($this, 'clozePlaceholder'), $this->replaceInternalLinkMedia($this->testo));
		$preparedText = str_replace("\n",'',$preparedText);
		return $preparedText;
	}

	/**
	 * abstract function that will replace cloze entries in text
	 *
	 * @param array $params - matched params from regexp
	 * @return a string of HTML
	 * @see getPreparedText
	 */
	abstract public function clozePlaceholder($params);

	/**
	 * show question text under certain circumstance (may be extended by subclasses)
	 *
	 * @access protected
	 *
	 * @param $out pointer to main output object
	 */
	protected function printQuestionText($out) {
		//prints nothing because we don't need it on cloze type of exercize
	}

	/**
	 * return true/false if the given value matches the answer
	 *
	 * @access protected
	 *
	 * @param $answer answer object
	 * @param $order order
	 * @param $value value to correct
	 *
	 * @return boolean 
	 */
	abstract protected function isAnswerCorrect($answer, $order, $value);

	/**
	 * return exercise max score
	 *
	 * @access public
	 *
	 * @return a value representing the max score
	 */
	public function getMaxScore() {
		$score = 0;
		if (!empty($this->_children)) {
			foreach($this->_children as $v) {
				$score+= $v->correttezza;
			}
		}
		return $score;
	}

	/**
	 * Returns the most correct answer as string
	 *
	 * @param int $ordine cloze order
	 *
	 * @return AnswerTest object or false
	 *
	 * @see getMaxScore
	 */
	public function getMostCorrectAnswer($ordine=null) {
		$score = 0;
		$mostCorrectAnswer = null;

		if (!empty($this->_children)) {
			foreach($this->_children as $c) {
				if ($c->ordine == $ordine && $c->correttezza > $score) {
					$score = $c->correttezza;
					$mostCorrectAnswer = $c;
				}
			}
		}

		if (!is_null($mostCorrectAnswer)) {
			return $mostCorrectAnswer;
		}
		else {
			return false;
		}
	}

	/**
	 * Function that automatic creates answers (and save it to database) for cloze question type
	 *
	 * @global db $dh
	 *
	 * @param int $question_id
	 * @param array $data question data
	 * @param array $test test record
	 */
	public static function createClozeAnswers($question_id,$data,$test) {
		$dh = $GLOBALS['dh'];

		$tmp = $dh->test_getNodesByParent($question_id);
		$old_answers = array();
		if (!empty($tmp)) {
			foreach($tmp as $v) {
				$old_answers[$v['ordine']][$v['id_nodo']] = $v;
			}
		}

		$tipo = ADA_LEAF_ANSWER.ADA_NO_OPEN_TEST_ANSWER.ADA_CASE_SENSITIVE_TEST.'000';
		if ($data['tipo']{5}) {
			$tipo{5} = ADA_CASE_INSENSITIVE_TEST;
		}

		preg_match_all(self::regexpCloze, $data['testo'], $matches, PREG_SET_ORDER);
		$ordini = array();
		if (!empty($matches)) {
			foreach($matches as $k=>$v) {
				$ordine = $v[1];
				$answer = $v[2];
				$ordini[] = $ordine;
				$insert = false;
				$delete = false;

				if (!isset($old_answers[$ordine])) {
					$insert = true;
				}
				else {
					$insert = true;
					$delete = true;
					foreach($old_answers[$ordine] as $k=>$v) {
						if ($v['testo'] == $answer) {
							$delete = false;
							$insert = false;
							break;
						}
					}
				}

				if ($delete) {
					foreach($old_answers[$ordine] as $k=>$v) {
						$dh->test_deleteNodeTest($k);
					}
				}

				if ($insert)
				{
					$data = array(
						'id_corso'=>$test['id_nodo'],
						'id_utente'=>$_SESSION['sess_id_user'],
						'id_istanza'=>$test['id_istanza'],
						'nome'=>$answer,
						'testo'=>$answer,
						'correttezza'=>1,
						'tipo'=>$tipo,
						'id_nodo_parent'=>$question_id,
						'id_nodo_radice'=>$test['id_nodo'],
						'ordine'=>$ordine,
					);
					$dh->test_addNode($data);
				}
			}
		}
		//pulisco le risposte rimaste appese
		foreach($old_answers as $k=>$v) {
			if (!in_array($k, $ordini)) {
				foreach($v as $id=>$t) {
					$dh->test_deleteNodeTest($id);
				}
			}
		}
	}
}
