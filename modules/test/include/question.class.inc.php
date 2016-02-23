<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

abstract class QuestionTest extends NodeTest
{
	const NODE_TYPE = ADA_GROUP_QUESTION;
	const CHILD_CLASS = 'AnswerTest';

	protected $question;
	protected $comment;
	protected $givenAnswer;

	protected $feedback = false;
	protected $rating = false;
	protected $rating_answer = false;

	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
		//first character
		if ($this->tipo{0} != self::NODE_TYPE) {
			return false;
		}

		//second character delegated to child class

		//third character
		switch($this->tipo{2}) {
			default:
			case ADA_NO_TEST_COMMENT:
				$this->comment = false;
			break;
			case ADA_YES_TEST_COMMENT:
				$this->comment = true;
			break;
		}

		//fourth character delegated to child class
		//fifth character delegated to child class
		//sixth character ignored because not applicable

		return true;
	}

	/**
	 * Render the object structure
	 *
	 * @access public
	 *
	 * @param $return_html choose the return type
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an object of CDOMElement or a string containing html
	 */
	public function render($return_html=true,$feedback=false,$rating=false,$rating_answer=false) {
		$this->feedback = $feedback;
		$this->rating = $rating;
		$this->rating_answer = $rating_answer;

		$html = $this->renderingHtml($ref,$feedback,$rating,$rating_answer);

		if ($return_html) {
			return $html->getHtml();
		}
		else {
			return $html;
		}
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

		$name = $this->getPostFieldName();
		$post_data = $this->getPostData();

		$out = CDOMElement::create('li','id:liQuestion'.$this->id_nodo);
		$class = 'question_test';
		if ($this->_parent->_children[0] == $this) {
			$class.= ' first';
		}
		else if ($this->_parent->_children[count($this->_parent->_children)-1] == $this) {
			$class.= ' last';
		}
		$out->setAttribute('class', $class);
		$out->addChild(new CText($this->titolo));

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
			if (!empty($this->_children)) {
				$text = '('.$this->getMaxScore().' '.translateFN('punti').')';
			}
			else {
				if (is_null($this->correttezza)) {
					$text = '('.translateFN('Nessun punteggio inserito').')';
				}
				else {
					$text = '('.$this->correttezza.' '.translateFN('punti').')';
				}
			}

			$span = CDOMElement::create('span');
			$span->setAttribute('class', 'rating_question_test');
			$span->addChild(new CText($text));
			$out->addChild(new CText(' '));
			$out->addChild($span);
		}
		else if ($rating) {
			$points = (is_null($this->givenAnswer['punteggio']))?0:$this->givenAnswer['punteggio'];
			$text = '('.translateFN('punteggio ottenuto').' '.$points.'/'.$this->getMaxScore().')';

			$span = CDOMElement::create('span');
			$span->setAttribute('class', 'rating_question_test');
			$span->addChild(new CText($text));
			$out->addChild(new CText(' '));
			$out->addChild($span);
		}

		//start author function menu
		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
			$div = CDOMElement::create('div','class:admin_link');

			if (!is_null($this->ordine)) {
				$span = CDOMElement::create('span');
				$span->addChild(new CText(translateFN('ordine').': '));
				$div->addChild($span);

				$span = CDOMElement::create('span','class:span_order');
				$span->addChild(new CText($this->ordine));
				$div->addChild($span);
			}

			$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');

			$div->addChild(new CText(' [ '));
			$mod_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_question.php?action=mod&id_question='.$this->id_nodo.$get_topic);
			$mod_link->addChild(new CText(translateFN('Modifica')));
			$div->addChild($mod_link);

			$div->addChild(new CText(' ] [ '));

			$del_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_question.php?action=del&id_question='.$this->id_nodo.$get_topic);
			$del_link->addChild(new CText(translateFN('Cancella')));
			$div->addChild($del_link);

			$div->addChild(new CText(' ] [ '));

			$up_link = CDOMElement::create('a');
			$up_link->setAttribute('href','javascript:void(0);');
			$up_link->setAttribute('onclick','move(this,'.$this->id_nodo.',\'up\');');
			$up_link->addChild(new CText(translateFN('Sposta su')));
			$div->addChild($up_link);

			$div->addChild(new CText(' ] [ '));

			$down_link = CDOMElement::create('a');
			$down_link->setAttribute('href','javascript:void(0);');
			$down_link->setAttribute('onclick','move(this,'.$this->id_nodo.',\'down\');');
			$down_link->addChild(new CText(translateFN('Sposta giu')));
			$div->addChild($down_link);

			$div->addChild(new CText(' ] '));

			$no_managed_answers = array(ADA_NO_QUESTION_TEST_TYPE, ADA_OPEN_MANUAL_TEST_TYPE, ADA_OPEN_UPLOAD_TEST_TYPE);
			if (!in_array($this->tipo{1},$no_managed_answers)) {
				$div->addChild(new CText(' [ '));
				$add_question_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_answers.php?id_question='.$this->id_nodo.$get_topic);
				$add_question_link->addChild(new CText(translateFN('Risposte')));
				$div->addChild($add_question_link);
				$div->addChild(new CText(' ] '));

				if (empty($this->_children)) {
					$span = CDOMElement::create('span','class:wrong_answer_test');
					$span->addChild(new CText(translateFN('Attenzione! Questa domanda non possiede risposte valide!')));
					$div->addChild($span);
				}

				if ((is_a($this, 'QuestionMultipleClozeTest') && !$this->isAnswersTableDataEmpty())) {
					$span = CDOMElement::create('span','class:wrong_answer_test');
					$span->addChild(new CText(translateFN('Attenzione! Non sono state associate le risposte ai campi della tabella!')));
					$div->addChild($span);
				}
			}

			$out->addChild($div);
		}
		//finish author function menu

		$divClear = CDOMElement::create('div','class:clear');
		$out->addChild($divClear);

		if (!empty($this->consegna)) {
			$div = CDOMElement::create('div');
			$div->setAttribute('class', 'question_test_description');
			$div->addChild(new CText($this->replaceInternalLinkMedia($this->consegna)));
			$out->addChild($div);
		}

		$this->printQuestionText($out);

		$ul = CDOMElement::create('ul');
		$ul->setAttribute('class', 'answer_group_test');
		$out->addChild($ul);

		if ($this->comment) {
			$extraArea = CDOMElement::create('div');
			$extraArea->setAttribute('class','extra_area_test');

			$textArea = CDOMElement::create('textarea');
			$textArea->setAttribute('name',$name.'['.self::POST_EXTRA_VAR.']');
			$textArea->setAttribute('class','extra_answer_test');

			if ($feedback) {
				$textArea->setAttribute('disabled', '');
				$textArea->addChild(new CText($this->givenAnswer['risposta'][self::POST_EXTRA_VAR]));
			}
			else if (!empty($post_data[self::POST_EXTRA_VAR])) {
				$textArea->addChild(new CText($post_data[self::POST_EXTRA_VAR]));
			}

			$extraArea->addChild(new CText($this->didascalia));
			$extraArea->addChild($textArea);

			$out->addChild($extraArea);
		}

		if ($feedback) {
			if ($_SESSION['sess_id_user_type'] == AMA_TYPE_TUTOR) {
				$span = CDOMElement::create('span','class:tutor_comment_test');
				$span->addChild(new CText(translateFN('Commento del Tutor per l\'esercizio')));
				$span->setAttribute('onclick','toggleDiv(\'tutor_comment_div_'.$this->givenAnswer['id_answer'].'\');');
				$out->addChild($span);

				$extraArea = CDOMElement::create('div','id:tutor_comment_div_'.$this->givenAnswer['id_answer']);
				$extraArea->setAttribute('class','extra_area_test tutor_comment_div');
				if (!empty($this->givenAnswer['commento'])) {
					$extraArea->setAttribute('style','display:block;');
				}

				$textArea = CDOMElement::create('textarea','id:tutor_comment_textarea_'.$this->givenAnswer['id_answer']);
				$textArea->setAttribute('class','extra_answer_test');
				$textArea->addChild(new CText($this->givenAnswer['commento']));
				$extraArea->addChild($textArea);

				$checkbox = CDOMElement::create('checkbox','id:tutor_comment_checkbox_'.$this->givenAnswer['id_answer']);
				$extraArea->addChild($checkbox);
				$label = CDOMElement::create('label','for:tutor_comment_checkbox_'.$this->givenAnswer['id_answer']);
				$label->addChild(new CText(translateFN('Invia notifica allo studente')));
				$extraArea->addChild($label);
				$extraArea->addChild(new CText('<br />'));

				$button = CDOMElement::create('input_button');
				$button->setAttribute('class', 'test_button');
				$button->setAttribute('onclick','saveCommentAnswer('.$this->givenAnswer['id_answer'].');');
				$button->setAttribute('value',translateFN('Salva commento all\'esercizio'));
				$extraArea->addChild($button);

				$out->addChild($extraArea);
			}
			else if (!empty($this->givenAnswer['commento'])) {
				$extraArea = CDOMElement::create('div','id:tutor_comment_div_'.$this->givenAnswer['id_answer']);
				$extraArea->setAttribute('class','extra_area_test');
				$extraArea->addChild(new CText('<b>'.translateFN('Commento del Tutor:').'</b> '.$this->givenAnswer['commento']));

				$out->addChild($extraArea);
			}
		}

		$ref = $ul;
		return $out;
	}

	/**
	 * show question text under certain circumstance (may be extended by subclasses)
	 *
	 * @param $out pointer to main output object
	 *
	 * @access protected
	 *
	 */
	protected function printQuestionText($out) {
		if (!empty($this->testo)) {
			$div = CDOMElement::create('div');
			$div->setAttribute('class', 'question_test_description');
			$div->addChild(new CText($this->replaceInternalLinkMedia($this->testo)));

			$out->addChild($div);
		}
	}

	/**
	 * build attribute "name" that is used to identify questions' answer
	 *
	 * @access protected
	 *
	 * @return string
	 */
	public function getPostFieldName() {
		$topic_id = $this->id_nodo_parent;
		$question_id = $this->id_nodo;
		return self::POST_TOPIC_VAR.'['.$topic_id.']['.$question_id.']';
	}

	/**
	 * retrieve data (if exists) from related $_POST array
	 *
	 * @access protected
	 *
	 * @return variable value or false otherwise
	 */
	public function getPostData() {
		$topic_id = $this->id_nodo_parent;
		$question_id = $this->id_nodo;

		if (isset($_POST[self::POST_TOPIC_VAR][$topic_id][$question_id])) {
			return $_POST[self::POST_TOPIC_VAR][$topic_id][$question_id];
		}
		else {
			return false;
		}
	}

	/**
	 * setter method for givenAnswer variable
	 *
	 * @access public
	 *
	 * @param $answer an array containing a row from history_answer
	 *
	 * @return boolean
	 */
	public function setGivenAnswer($answer) {
		if (is_array($answer) && !empty($answer)) {
			$this->givenAnswer = $answer;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * abstract method that define exercise correction. must be implemented by every specialized question class
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	abstract public function exerciseCorrection($data);

	/**
	 * abstract method that define exercise max score. must be implemented by every specialized question class
	 *
	 * @access public
	 *
	 * @return a value representing the max score
	 */
	abstract public function getMaxScore();

	/**
	 * Returns the most correct answer as string
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
				if ($c->correttezza > $score) {
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
	 * Serialize answer data
	 *
	 * @return string serialized data
	 *
	 * @see Root::saveAnswers
	 */
	public function serializeAnswers($data) {
		return serialize($data);
	}
}
