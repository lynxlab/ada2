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
		/**
		 * giorgio 24/gen/2014
		 * variable needed to add an hidden span containing 
		 * the number of answer a student must give
		 */
		$neededAnswers = 0;

		if (!empty($this->_children)) {
			$_children = $this->_children;
			if ($this->searchParent('RootTest')->shuffle_answers) {
				// giorgio 24/gen/2014 shuffle is not wanted for this kind of exercise
				// shuffle($_children);
			}
			
			/**
			 * @author giorgio 03/feb/2015
			 *
			 * add a fake hidden checkbox to submit something
			 * if the user confirms to send a non-answered question
			 */
			if (!$feedback) {
				$li = CDOMElement::create('li');
				$input = CDOMElement::create('checkbox');
				$input->setAttributes('value:null,checked:checked,name:'.$name.'['.self::POST_ANSWER_VAR.'][]');
				$input->setAttribute('style', 'display:none;');
				$li->addChild($input);
				$ref->addChild($li);
			}

			while (!empty($_children)) {
				foreach($_children as $k=>$v) {
					if ($v->extra_answer && count($_children)>1) {
						continue;
					}
					
					$hasImage = preg_match('/<MEDIA([^>]*)TYPE=\"'._IMAGE.'\"([^>]*)>/i',$v->testo) >0;
					if (!$hasImage) $hasMedia = preg_match('/<MEDIA([^>]*)>/i',$v->testo) >0;
					else $hasMedia = false;

					$inputId = $name.'['.$k.']';
					$answer = CDOMElement::create('label','for:'.$inputId);

					$outText = $v->testo;
					
					$answer->addChild(new CText($this->replaceInternalLinkMedia($outText)));
					if ($hasImage) $answer->setAttribute('class',$answer->getAttribute('class').' isimage');
					$checkInput = CDOMElement::create('checkbox','id:'.$inputId);
					$checkInput->setAttribute('class','radio_multiple_test');
					if ($hasMedia) $checkInput->setAttribute('class',$checkInput->getAttribute('class').' media');
					$checkInput->setAttribute('style','vertical-align:middle; margin-top:0px;');
					$checkInput->setAttribute('name',$name.'['.self::POST_ANSWER_VAR.'][]');
					$checkInput->setAttribute('value',$v->id_nodo);
					
					if ($hasMedia) {
						/**
						 * if has a media, checkInput must be
						 * wrapped around a div for proper styling
						 */
						$input = CDOMElement::create('div','class:mediainputwrapper');
						$input->addChild($checkInput);
					} else {
						$input = $checkInput;
					}
					
					/**
					 * giorgio 24/gen/2014
					 * if is correct answer, increment neededAnswers counter
					 */
					if ($v->correttezza > 0) $neededAnswers++;
					
					//feedback section
					if ($feedback) {
						$input->setAttribute('disabled','');
						$checked = $this->givenAnswer['risposta'][self::POST_ANSWER_VAR] && in_array($v->id_nodo,$this->givenAnswer['risposta'][self::POST_ANSWER_VAR]);
						if ($checked) $input->setAttribute('checked', '');
						
						if ($v->correttezza>0) {
							$class = ($checked) ? 'right_answer_test' : 'unanswered_answer_test';
						} else {
							$class = ($checked) ? 'wrong_answer_test' : '';							
						}
						
						$clonedinput = clone $input;
						$input = CDOMElement::create('span','class:answer_test '.$class);
						$input->addChild($clonedinput);
												
// 						$answer->setAttribute('class', $class);
						if ($hasMedia) $answer->setAttribute('class',$answer->getAttribute('class').' media');
						if ($hasImage) $answer->setAttribute('class',$answer->getAttribute('class').' isimage');
					}
					else if ($post_data[self::POST_ANSWER_VAR] == $v->id_nodo) {
						$input->setAttribute('checked','');
					}

					$li = CDOMElement::create('li');
					$class = 'answer_multiple_test';
					if ($hasMedia) $class .= ' media';
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
					if (RootTest::isSessionUserAStudent()) {
						/**
						 * giorgio added false to the if to not display the popup
						 */
						if (false && $feedback && $rating_answer && !strstr($string,'right_answer_test')) {
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

					if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
						$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
						$li->addChild(new CText(' ('.$v->correttezza.' '.translateFN('punti').')'));
					}

					$ref->addChild($li);

					unset($_children[$k]);
				}
				$ref->addChild(CDOMElement::create('li','class:clear'));
			}
		}
		
		/**
		 * Add a span to hold the value of the answers
		 * a user must give when she is taking the test
		 */
		if (isset($neededAnswers) && $neededAnswers>=0)
		{
			$neededAnswersSpan = CDOMElement::create('span','id:must-data_'.$this->id_nodo);
			$neededAnswersSpan->setAttribute('style', 'display:none');
			$neededAnswersSpan->addChild (new CText($neededAnswers));
			$out->addChild ($neededAnswersSpan);
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
