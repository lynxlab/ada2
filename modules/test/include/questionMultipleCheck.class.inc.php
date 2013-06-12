<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionMultipleCheckTest extends QuestionTest
{
	protected $variation = ADA_NORMAL_TEST_VARIATION;

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

		//fourth character: variation
		$this->variation = $this->tipo{3};
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
		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		$name = $this->getPostFieldName();
		$post_data = $this->getPostData();

		if (!empty($this->_children)) {
			$_children = $this->_children;
			if ($this->searchParent('RootTest')->shuffle_answers) {
				shuffle($_children);
			}

			while (!empty($_children)) {
				foreach($_children as $k=>$v) {
					if ($v->extra_answer && count($_children)>1) {
						continue;
					}

					$inputId = $name.'['.$k.']';
					$answer = CDOMElement::create('label','for:'.$inputId);
					$answer->addChild(new CText($v->testo));
					$input = CDOMElement::create('checkbox','id:'.$inputId);
					$input->setAttribute('class','radio_multiple_test');
					$input->setAttribute('style','vertical-align:middle; margin-top:0px;');
					$input->setAttribute('name',$name.'['.self::POST_ANSWER_VAR.'][]');
					$input->setAttribute('value',$v->id_nodo);
					//feedback section
					if ($feedback) {
						$input->setAttribute('disabled','');

						if ($this->givenAnswer['risposta'][self::POST_ANSWER_VAR]
						&& in_array($v->id_nodo,$this->givenAnswer['risposta'][self::POST_ANSWER_VAR])) {
							$input->setAttribute('checked', '');
							if ($v->correttezza>0) {
								$answer->setAttribute('class', 'right_answer_test');
							}
							else {
								$answer->setAttribute('class', 'wrong_answer_test');
							}
						}
					}
					else if ($post_data[self::POST_ANSWER_VAR] == $v->id_nodo) {
						$input->setAttribute('checked','');
					}

					$li = CDOMElement::create('li');
					$class = 'answer_multiple_test';
					switch($this->variation) {
						case ADA_ERASE_TEST_VARIATION:
							$class.= ' erase_variation_test';
						break;
						case ADA_HIGHLIGHT_TEST_VARIATION:
							$class.= ' highlight_variation_test';
						break;
					}

					$li->setAttribute('class', $class);
					$li->addChild($input);
					$li->addChild($answer);

					$string = $answer->getAttribute('class');
					if ($_SESSION['sess_id_user_type'] == AMA_TYPE_STUDENT) {
						if ($feedback && $rating_answer && !strstr($string,'right_answer_test')) {
							$correctAnswer = $this->getMostCorrectAnswer();
							if ($correctAnswer) {
								$popup = CDOMElement::create('div','id:popup_'.$this->id_nodo);
								$popup->setAttribute('style','display:none;');
								$popup->addChild(new CText($correctAnswer->testo));

								$answer->setAttribute('class', $string.' answerPopup');
								$answer->setAttribute('title',$this->id_nodo);
								$li->addChild(new CText($popup->getHtml()));
							}
						}
						else if ($feedback && $rating) {
							$li->addChild(new CText(' ('.$v->correttezza.' '.translateFN('Punti').')'));
						}
					}

					if ($v->extra_answer && $this->variation == ADA_NORMAL_TEST_VARIATION) {
						$answer = CDOMElement::create('text');
						$answer->setAttribute('class','text_standard_test');
						$answer->setAttribute('name',$name.'['.self::POST_OTHER_VAR.']['.$v->id_nodo.']');
						$answer->setAttribute('onkeyup','autoCheckForOtherAnswer(this);');

						if ($feedback && isset($this->givenAnswer['risposta'][self::POST_OTHER_VAR][$v->id_nodo])) {
							$answer->setAttribute('disabled', '');
							$answer->setAttribute('value',$this->givenAnswer['risposta'][self::POST_OTHER_VAR][$v->id_nodo]);
						}
						else if (!empty($post_data[self::POST_OTHER_VAR])) {
							$answer->setAttribute('value',$post_data[self::POST_OTHER_VAR]);
						}

						$li->addChild(new CText('&nbsp;'));
						$li->addChild($answer);
					}

					if ($_SESSION['sess_id_user_type'] != AMA_TYPE_STUDENT) {
						$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
						$li->addChild(new CText(' ('.$v->correttezza.' '.translateFN('punti').')'));
					}

					$ref->addChild($li);

					unset($_children[$k]);
				}
				$ref->addChild(CDOMElement::create('li','class:clear'));
			}
		}

		return $out;
	}

	/**
	 * implementation of exerciseCorrection for MultipleCheck question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		$points = null;

		if (is_array($data) && !empty($data)) {
			$points = 0;
			if (!empty($data[self::POST_ANSWER_VAR])) {
				foreach($data[self::POST_ANSWER_VAR] as $k=>$v) {
					$answer = $this->searchChild($v);
					if ($answer) {
						$points+= $answer->correttezza;
					}
				}
			}
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
				$score+= $v->correttezza;
			}
		}
		return $score;
	}
}
