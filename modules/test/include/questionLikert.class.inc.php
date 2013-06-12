<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionLikertTest extends QuestionTest
{
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

		//setting parameters
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
		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		$name = $this->getPostFieldName();
		$post_data = $this->getPostData();

		if (!empty($this->_children)) {
			foreach($this->_children as $k=>$v) {
				$answer = new CText($v->testo);
				$input = CDOMElement::create('radio');
				$input->setAttribute('class','radio_liker_test');
				$input->setAttribute('style','vertical-align:top;');
				$input->setAttribute('name',$name.'['.self::POST_ANSWER_VAR.']');
				$input->setAttribute('value',$v->id_nodo);
				//feedback section
				if ($feedback) {
					$input->setAttribute('disabled','');
					if ($this->givenAnswer['risposta'][self::POST_ANSWER_VAR] == $v->id_nodo) {
						$input->setAttribute('checked', '');
						$tmp = CDOMElement::create('span');
						if ($this->givenAnswer['punteggio']>0) {
							$tmp->setAttribute('class', 'right_answer_test');
						}
						else {
							$tmp->setAttribute('class', 'wrong_answer_test');
						}
						$tmp->addChild($answer);
						$answer = $tmp;
					}
				}

				if ($post_data[self::POST_ANSWER_VAR] == $v->id_nodo) {
					$input->setAttribute('checked','');
				}

				$li = CDOMElement::create('li');
				$class = 'answer_likert_test';
				if ($k==0) {
					$class.= ' first_test';
					$li->addChild($answer);
					$li->addChild($input);
				}
				else if ($k==count($this->_children)-1) {
					$class.= ' last_test';
					$li->addChild($input);
					$li->addChild($answer);
				}
				else {
					$li->addChild($input);
					$li->addChild($answer);
				}

				if ($_SESSION['sess_id_user_type'] != AMA_TYPE_STUDENT) {
					$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
					$li->addChild(new CText(' ('.$v->correttezza.' '.translateFN('punti').')'));
				}

				$li->setAttribute('class', $class);

				$ref->addChild($li);
			}
		}

		return $out;
	}

	/**
	 * implementation of exerciseCorrection for Likert question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		$points = null;
		$answer = $this->searchChild($data[self::POST_ANSWER_VAR]);
		if ($answer) {
			$points = $answer->correttezza;
		}

		if ($points > $this->getMaxScore()) {
			$points = $this->getMaxScore();
		}

		return array('points'=>$points,'attachment'=>null);
	}

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
				if ($v->correttezza > $score) {
					$score= $v->correttezza;
				}
			}
		}
		return $score;
	}
}
