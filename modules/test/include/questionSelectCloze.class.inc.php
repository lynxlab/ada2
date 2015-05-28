<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionSelectClozeTest extends QuestionClozeTest
{
	protected $synonym;

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

		//fifth character
		switch ($this->tipo{4}) {
			default:
			case ADA_NORMAL_SELECT_TEST:
				$this->synonym = false;
			break;
			case ADA_SYNONYM_SELECT_TEST:
				$this->synonym = true;
			break;
		}
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

		$li = new CLi();
		$li->setAttribute("class", "answer_cloze_test");
		$li->addChild(new CText($this->getPreparedText($feedback,$rating,$rating_answer)));
		$ref->addChild($li);

		return $out;
	}

	/**
	 * abstract function that will replace cloze entries in text
	 *
	 * @param array $params - matched params from regexp
	 * @return a string of HTML
	 * @see getPreparedText
	 */
	public function clozePlaceholder($params) {
		$ordine = $params[1];
		$value = $params[2];
		$class = '';

		$name = $this->getPostFieldName();

		$obj = CDOMElement::create('select');
		$obj->setAttribute('name', $name.'['.self::POST_ANSWER_VAR.']['.$ordine.']');
		if ($this->feedback) {
			$obj->setAttribute('readonly', '');
		}
		else {
			$first_option = CDOMElement::create('option');
			if ($this->synonym) {
				$class = 'answer_cloze_select_synonym_test';

				$first_option->setAttribute('value', '');
				$first_option->setAttribute('class', 'answer_cloze_option_synonym_test');
				$first_option->addChild(new CText($value));
			}
			else {
				$class = 'answer_cloze_select_test';

				$first_option->setAttribute('value', '');
				$first_option->addChild(new CText('---'));
			}
			// @author giorgio 21/ott/2013 comment/uncomment below if first option is to be disabled
			// $first_option->setAttribute('disabled', 'true');			
			$obj->addChild($first_option);
		}
		
		if (!empty($this->_children)) {
			if ($this->feedback) {
				$risposta = $this->givenAnswer['risposta'][self::POST_ANSWER_VAR][$ordine];
				$answer = $this->searchChild($risposta);
				if (!empty($risposta) && $answer) {
					$option = CDOMElement::create('option');
					$option->setAttribute('value', $answer->id_nodo);
					$option->addChild(new CText($answer->testo));
					$option->setAttribute('selected', '');
					$obj->addChild($option);
					if (!empty($this->_children)) {
						foreach($this->_children as $v) {
							if ($ordine == $v->ordine) {
								if ($this->isAnswerCorrect($v, $ordine, $answer->id_nodo)) {
									$class.= ' right_answer_test';
								}
								else {
									$class.= ' wrong_answer_test';
								}
							}
						}
					}
				}
				else {
					$class.= ' empty_answer_test';
				}
			}
			else {
				if ($this->searchParent('RootTest')->shuffle_answers) {
					shuffle($this->_children);
				}
				if (!empty($this->_children)) {
					foreach($this->_children as $v) {
						if ($ordine == $v->ordine) {
							$option = CDOMElement::create('option');
							$option->setAttribute('value', $v->id_nodo);
							$outText = $v->testo;
							$option->addChild(new CText($outText));
							if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
								$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
								$option->addChild(new CText(' ('.$v->correttezza.' '.translateFN('punti').')'));
							}
							$obj->addChild($option);
						}
					}
				}
			}
		}


		$correctAnswer = false;
		if ($this->feedback && ($this->rating || $this->rating_answer) && !strstr($class,'right_answer_test')) {
			$correctAnswer = $this->getMostCorrectAnswer($ordine);
			if ($correctAnswer) {
				$popup = CDOMElement::create('div','id:popup_'.$this->id_nodo.'_'.$ordine);
				$popup->setAttribute('style','display:none;');
				$popup->addChild(new CText($correctAnswer->testo));
				if ($this->rating) {
					$popup->addChild(new CText(' ('.$correctAnswer->correttezza.' '.translateFN('Punti').')'));
				}

				$obj->setAttribute('class', $class.' answerPopup');
				$obj->setAttribute('title',$this->id_nodo.'_'.$ordine);
				$html = $obj->getHtml().$popup->getHtml();
			}
		}

		if (!$correctAnswer) {
			$obj->setAttribute('class', $class);
			$html = $obj->getHtml();
		}

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
			$span = CDOMElement::create('span','class:clozePopup,title:'.$this->id_nodo.'_'.$ordine);
			$html.= $span->getHtml();

			$div = CDOMElement::create('div','id:popup_'.$this->id_nodo.'_'.$ordine);
			$div->setAttribute('style','display:none;');
			$risposte = array();
			if (!empty($this->_children)) {
				foreach($this->_children as $k=>$v) {
					if ($v->ordine == $ordine) {
						$risposte[] = $v->testo.' ('.$v->correttezza.' '.translateFN('Punti').')';
					}
				}
			}
			$div->addChild(new CText(implode('<br/>',$risposte)));
			$html.= $div->getHtml();
		}

		return $html;
	}

	/**
	 * implementation of exerciseCorrection for Select Cloze question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		$points = 0;

		if (is_array($data) && !empty($data) && !empty($this->_children)) {
			foreach($data[self::POST_ANSWER_VAR] as $k=>$v) {
				foreach($this->_children as $answer) {
					if ($this->isAnswerCorrect($answer, $k, $v)) {
						$points+= $answer->correttezza;
						break;
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
	 * return true/false if the given value matches the answer
	 *
	 * @access protected
	 *
	 * @param $answer answer object
	 * @param $value value to correct
	 *
	 * @return boolean
	 */
	protected function isAnswerCorrect($answer, $order, $value) {
		return ($answer->ordine == $order && $answer->id_nodo == $value && $answer->correttezza > 0);
	}
}
