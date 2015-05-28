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
	public $isAnswered = false;

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

		$spanTitleClass = 'topictitle';
		$spanTitle = CDOMElement::create('span');
		if ($_SESSION['sess_id_user_type'] != AMA_TYPE_AUTHOR && !$feedback && $this->isAnswered) {
			$spanTitle->setAttribute('class', $spanTitleClass.' answered');
			$spanTitle->setAttribute('title', translateFN('Hai risposto a questa domanda'));
		} else {
			$spanTitle->setAttribute('class', $spanTitleClass);
		}
		$spanTitle->addChild(new CText($this->titolo));
		$out->addChild($spanTitle);
		
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
				$add_topic_link->addChild(new CText(translateFN('Aggiungi attivit&agrave;')));
				$div->addChild($add_topic_link);

				$div->addChild(new CText(' ] [ '));
			}

			$add_question_link = CDOMElement::create('a','href:'.MODULES_TEST_HTTP.'/edit_question.php?action=add&id_test='.$this->id_nodo_radice.'&id_nodo_parent='.$this->id_nodo.$get_topic);
			$add_question_link->addChild(new CText(translateFN('Aggiungi domanda')));
			$div->addChild($add_question_link);
			$div->addChild(new CText(' ]'));

			$out->addChild($div);
		}

		$mediaClassForUL = '';
		if (!empty($this->testo)) {
			$div = CDOMElement::create('div');
			$div->setAttribute('class', 'topic_test_description');
			
			if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {				
				$div->addChild(new CText($this->replaceInternalLinkMedia($this->testo)));
			} else {
			    /**
				 * split testo into consegna(aka testo), didascalia and stimolo (if any)
				 */			
				require_once MODULES_TEST_PATH.'/include/forms/topicFormTest.inc.php';
				$splittedAr = TopicFormTest::extractFieldsFromTesto($this->testo, array('stimolo-field','didascalia-field'));	

				/**
				 * note the following cannot be put into a foreach 'cuz we must be sure
				 * that the ouptut sequence will be: consegna(aka testo), didascalia and stimolo
				 */
				
				// add consegna
				$consegna = $this->replaceInternalLinkMedia($splittedAr['testo']);
				$div->addChild(new CText($consegna));
				
	 			// add didascalia
	 			if (isset($splittedAr['didascalia']) && strlen($splittedAr['didascalia'])>0) {
	 				$didascalia = $this->replaceInternalLinkMedia($splittedAr['didascalia']);
	 			} else $didascalia = '';
	 			
				$div->addChild(new CText($didascalia));
				
				if (isset($splittedAr['stimolo']) && strlen($splittedAr['stimolo'])>0) {
					$mediaClassForUL = $this->renderStimolo($splittedAr['stimolo'],$div);
				}				
			}

			// click to zoom label to be read from index.js when click to zoom stimolo is generated
			$clickToZoomLbl = CDOMElement::create('span','id:clickToZoomLbl');
			$clickToZoomLbl->setAttribute('style','display:none');
			$clickToZoomLbl->addChild(new CText(translateFN('zoom')));
			
			// click to expand label to be read from index.js when click to zoom stimolo is generated
			$clickToExpandLbl = CDOMElement::create('span','id:clickToExpandLbl');
			$clickToExpandLbl->setAttribute('style','display:none');
			$clickToExpandLbl->addChild(new CText(translateFN('espandi')));
			
			// click to reduce label to be read from index.js when click to zoom stimolo is generated
			$clickToReduceLbl = CDOMElement::create('span','id:clickToReduceLbl');
			$clickToReduceLbl->setAttribute('style','display:none');
			$clickToReduceLbl->addChild(new CText(translateFN('riduci')));
			
			$div->addChild($clickToZoomLbl);
			$div->addChild($clickToExpandLbl);
			$div->addChild($clickToReduceLbl);
			
			$out->addChild($div);
		}
		$ul = CDOMElement::create('ul');
		$ul->setAttribute('class', 'question_group_test'.$mediaClassForUL);
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
	 * @author giorgio 29/lug/2014
	 * 
	 * builds the stimolo div and adds it to the passed mainDIV
	 * 
	 * @param string $stimoloText
	 * @param CDiv   $mainDIV
	 * 
	 * @return string the class to be used in the container UL element
	 * 
	 * @access private
	 */
	private function renderStimolo ($stimoloText, $mainDIV) {

		$stimolo = CDOMElement::create('div','class:stimolo-field');
		
		/**
		 * must check if stimolo has a media and,
		 * if it has, add a proper class to the
		 * <ul> classed question_group_test for proper styling
		 */
		$hasMediaArr = Node::extractLinkMediaTags($stimoloText);
		$mediaClassForUL = '';
		if (!empty($hasMediaArr)) {
			foreach ($hasMediaArr as $hasMedia) {
				switch ($hasMedia['type']) {
					case _IMAGE:
						if (strpos($mediaClassForUL,'image')===false) $mediaClassForUL .= ' imagestimolo';
					break;
					case _SOUND:
						if (strpos($mediaClassForUL,'audio')===false) $mediaClassForUL .= ' audio';
					break;
					case _VIDEO:
						if (strpos($mediaClassForUL,'video')===false) $mediaClassForUL .= ' video';
					break;
					default:
					break;
				}
			}
		}
		
		if (preg_match('/(<img) (.*)(\\\?>)/i', $stimoloText)) {
			// if there's an image without the <media> tag, the extractLinkMediaTags
			// won't detect it and must be done by hand now.
			if (strpos($mediaClassForUL,'image')===false) $mediaClassForUL .= ' imagestimolo';
		}
		
		if (strpos($stimoloText,'text-align: right')!==false) {
			$stimolo->setAttribute('class',$stimolo->getAttribute('class').' rightstimolo');
		} else if (strpos($stimoloText,'text-align: center')!==false) {
			$stimolo->setAttribute('class',$stimolo->getAttribute('class').' centerstimolo');
			$mediaClassForUL .= ' centerstimolo';
		}
		
		$stimolo->addChild(new CText($this->replaceInternalLinkMedia($stimoloText)));
		$mainDIV->addChild($stimolo);
		
		return $mediaClassForUL;
	}

	/**
	 * @author giorgio 30/ott/2014
	 * 
	 * adds the topic that's about to be rendered to the history_esercizi table
	 * 
	 * @access protected
	 */
	protected function trackTopicToExerciseHistory() {
		if (($this->searchParent('RootTest')->previewMode===false) && 
			 isset($_SESSION['close_test_instance']) && 
			 RootTest::isSessionUserAStudent($_SESSION['close_test_instance']) && 
			 isset($_SESSION['sess_id_user']) && isset($GLOBALS['dh'])) {
				
			if (!isset($_GET['unload'])) {
				$GLOBALS['dh']->add_ex_history($_SESSION['sess_id_user'], $_SESSION['close_test_instance'], $this->id_nodo);
			} else {
				$GLOBALS['dh']->update_exit_time_ex_history($_SESSION['sess_id_user'], $_SESSION['close_test_instance'], $this->id_nodo);
			}
			
		}
	}

	/**
	 * if session user is not an author,checks if the number of answered 
	 * questions in the current topic equals the total number of questions
	 * in the current topic and sets the isAnswered class property accordingly
	 * 
	 * NOTE: this is used to place the checkmark near the topic title when the
	 * user is browsing the activity, a topic is considered to be answered when
	 * each single question has been answered once at least.
	 * 
	 * @author giorgio 18/mar/2015
	 */
	public function checkAndSetIsAnswered() {
		if ($_SESSION['sess_id_user_type'] != AMA_TYPE_AUTHOR) {
			$isTopic = true;
			$numQuestions = $GLOBALS['dh']->test_countPossibleQuestionsForNode($this->id_nodo, $isTopic);
			$givenAnswers = $GLOBALS['dh']->test_countGivenAnswersForNode ($_SESSION['sess_id_user'], 
					$this->id_corso, 
					isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null,
					$this->id_nodo, $isTopic);
			
			$this->isAnswered = (intval($numQuestions)===intval($givenAnswers));
		} else $this->isAnswered = false;
	}
}
