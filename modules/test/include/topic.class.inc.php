<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class TopicTest extends NodeTest
{
	const NODE_TYPE = ADA_GROUP_TOPIC;
	const CHILD_CLASS = 'TopicTest|QuestionTest'; //use pipe character to declare multiple child class

	protected $randomQuestions;

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

		//second character
		switch($this->tipo{1}) {
			default:
			case ADA_PICK_QUESTIONS_NORMAL:
				$this->randomQuestions = false;
			break;
			case ADA_PICK_QUESTIONS_RANDOM:
				$this->randomQuestions = true;
			break;
		}

		//third character ignored because not applicable
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
		if (!$this->display) return new CText(''); //if we don't have to display this question, let's return an empty item

		$out = CDOMElement::create('li','id:liTopic'.$this->id_nodo);
		$out->setAttribute('class', 'topic_test');

		$out->addChild(new CText($this->titolo));

		// add topic to history_esercizi table
		if (!$feedback) $this->trackTopicToExerciseHistory();

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
			if ($this->durata > 0 && is_a($this->_parent,'RootTest')) {
				$minuti = round($this->durata/60,2);
				$durata = CDOMElement::create('div','class:fright');
				$durata->addChild(new CText(translateFN('Durata:').' '.$minuti.' '.translateFN('minuti')));
				$out->addChild($durata);
			}

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
			$mod_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_topic.php?action=mod&id_topic='.$this->id_nodo.$get_topic);
			$mod_link->addChild(new CText(translateFN('Modifica')));
			$div->addChild($mod_link);

			$div->addChild(new CText(' ] [ '));

			$del_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_topic.php?action=del&id_topic='.$this->id_nodo.$get_topic);
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

			$div->addChild(new CText(' ] [ '));

			if (is_a($this->_parent,'RootTest')) {
				$add_topic_link = CDOMElement::create('a');
				$add_topic_link->setAttribute('href',MODULES_TEST_HTTP.'/edit_topic.php?action=add&id_test='.$this->id_nodo_radice.'&id_nodo_parent='.$this->id_nodo.$get_topic);
				$add_topic_link->addChild(new CText(translateFN('Aggiungi argomento')));
				$div->addChild($add_topic_link);

				$div->addChild(new CText(' ] [ '));
			}

			$add_question_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_question.php?action=add&id_test='.$this->id_nodo_radice.'&id_nodo_parent='.$this->id_nodo.$get_topic);
			$add_question_link->addChild(new CText(translateFN('Aggiungi domanda')));
			$div->addChild($add_question_link);
			$div->addChild(new CText(' ]'));

			$out->addChild($div);
		}

		if (!empty($this->testo)) {
			$div = CDOMElement::create('div');
			$div->setAttribute('class', 'topic_test_description');
			$div->addChild(new CText($this->replaceInternalLinkMedia($this->testo)));
			$out->addChild($div);
		}
		$ul = CDOMElement::create('ul');
		$ul->setAttribute('class', 'question_group_test');
		if ($feedback) {
			$ul->setAttribute('class', $ul->getAttribute('class').' feedback');
		}
		/*
		if (isset($this->_children[0])) {
			$ul->setAttribute('start', $this->_children[0]->ordine);
		}
		*/
		$out->addChild($ul);
		$ref = $ul;

		return $out;
	}

	/**
	 * @author giorgio 30/ott/2014
	 *
	 * adds the topic that's about to be rendered to the history_esercizi table
	 *
	 * @access protected
	 */
	protected function trackTopicToExerciseHistory() {
		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_STUDENT && isset($_SESSION['sess_id_user']) &&
			isset($_SESSION['sess_id_course_instance']) && isset($GLOBALS['dh'])) {

			if (!isset($_GET['unload'])) {
				$GLOBALS['dh']->add_ex_history($_SESSION['sess_id_user'], $_SESSION['sess_id_course_instance'], $this->id_nodo);
			} else {
				$GLOBALS['dh']->update_exit_time_ex_history($_SESSION['sess_id_user'], $_SESSION['sess_id_course_instance'], $this->id_nodo);
			}

		}
	}
}
