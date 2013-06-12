<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionOpenManualTest extends QuestionTest
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

		$li = CDOMElement::create('li','class:answer_open_test');

		$textArea = CDOMElement::create('textarea');
		$textArea->setAttribute('name',$name.'['.self::POST_ANSWER_VAR.']');
		$textArea->setAttribute('class','open_answer_test');
		$li->addChild($textArea);
		
		if ($feedback) {
			$textArea->addChild(new CText($this->givenAnswer['risposta'][self::POST_ANSWER_VAR]));
			$textArea->setAttribute('disabled','');

			if ($_SESSION['sess_id_user_type'] == AMA_TYPE_TUTOR) {
				$textAreaCorrect = CDOMElement::create('textarea','id:open_answer_test_point_textarea_'.$this->givenAnswer['id_answer']);
				$textAreaCorrect->setAttribute('class', 'open_answer_test fright');
				$textAreaCorrect->addChild(new CText($this->givenAnswer['correzione_risposta']));
				$li->addChild($textAreaCorrect);

				$button = CDOMElement::create('input_button');
				$button->setAttribute('class', 'test_button fright');
				$button->setAttribute('onclick','saveCorrectOpenAnswer('.$this->givenAnswer['id_answer'].');');
				$button->setAttribute('value',translateFN('Salva correzione risposta'));
				$li->addChild($button);

				$punti = $this->givenAnswer['punteggio'];
				
				$input = CDOMElement::create('text','id:open_answer_test_point_input_'.$this->givenAnswer['id_answer']);
				$input->setAttribute('size',4);
				$input->setAttribute('maxlength',4);
				$input->setAttribute('value',$punti);

				$button = CDOMElement::create('input_button','class:test_button');
				$button->setAttribute('onclick','saveOpenAnswerPoints('.$this->givenAnswer['id_answer'].');');
				$button->setAttribute('value',translateFN('Assegna punti'));

				$span = CDOMElement::create('span','id:open_answer_test_point_span_'.$this->givenAnswer['id_answer']);

				if (is_null($punti)) {
					$punti = translateFN('Nessun punteggio assegnato');
				}
				$span->addChild(new CText($punti));

				$div = CDOMElement::create('div','class:open_answer_test_point');
				$div->addChild($input);
				$div->addChild($button);
				$div->addChild(new CText('&nbsp;&nbsp;&nbsp;'.translateFN('Punti già assegnati').': '));
				$div->addChild($span);
				$li->addChild($div);
			}
			else if (!empty($this->givenAnswer['correzione_risposta'])) {
				$divCorrectAnswer = CDOMElement::create('div','id:open_answer_test_point_textarea_'.$this->givenAnswer['id_answer']);
				$divCorrectAnswer->setAttribute('class', 'open_answer_test fright');
				$divCorrectAnswer->addChild(new CText('<b>'.translateFN('Risposta corretta:').'</b> '.$this->givenAnswer['correzione_risposta']));
				$li->addChild($divCorrectAnswer);
			}
		}

		if (!empty($post_data[self::POST_ANSWER_VAR])) {
			$textArea->addChild(new CText($post_data[self::POST_ANSWER_VAR]));
		}
		
		$ref->addChild($li);

		return $out;
	}

	/**
	 * implementation of exerciseCorrection for OpenManual question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		//manual corrections, no points gained
		return array('points'=>null, 'attachment'=>null);
	}

	/**
	 * return exercise max score
	 *
	 * @access public
	 *
	 * @return a value representing the max score
	 */
	public function getMaxScore() {
		return $this->correttezza;
	}
}
