<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionEraseClozeTest extends QuestionClozeTest
{
	const clozeDelimiter = '§§§';
	const htmlDelimiter = '~~~';
	const spanHeader = '<span class="answer_erase_item_test">';
	const spanFooter = '</span>';

	protected $exerciseVariation;
	protected $apostrophe;

	protected $clozePlaceholders = array();
	protected $htmlPlaceholders = array();

	protected $spanInstances = 0;
	protected $clozeOrders = array();

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
		$this->exerciseVariation = $this->tipo{4};
		$this->apostrophe = $this->tipo{5};
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

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
			$feedback = true;
			$rating = true;
			$rating_answer = true;
		}

		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		switch($this->exerciseVariation) {
			default:
			case ADA_ERASE_TEST_ERASE:
				$class = 'answer_cloze_erase_test';
			break;						
			case ADA_HIGHLIGHT_TEST_ERASE:
				$class = 'answer_cloze_highlight_test';
			break;
		}

		$li = new CLi();
		$li->setAttribute('class', $class);
		$li->addChild(new CText($this->getPreparedText($feedback,$rating,$rating_answer)));
		$ref->addChild($li);

		/**
		 * giorgio 27/gen/2014
		 * if is correct answer, increment neededAnswers counter
		 */
		$neededAnswers = 0;
		if (!empty($this->_children)) {
			foreach ($this->_children as $k=>$v) {
				if ($v->correttezza > 0) $neededAnswers++;
			}
		}
		
		/**
		 * Add a span to hold the value of the answers
		 * a user must give when she is taking the test
		 */
		if (isset($neededAnswers) && $neededAnswers>=0)
		{
			$neededAnswersSpan = CDOMElement::create('span','style:display:none;');
			$neededAnswersSpan->setAttribute('class', 'must-data-'.$this->getPostFieldName());
			$neededAnswersSpan->addChild (new CText($neededAnswers));
			$out->addChild ($neededAnswersSpan);
		}
		
		/**
		 * @author giorgio 03/feb/2015
		 *
		 * add a fake hidden input to submit something
		 * if the user confirms to send a non-answered question
		 */
		if (!$feedback) {
			$input = CDOMElement::create('hidden');
			$input->setAttributes('value:null,name:'.$this->getPostFieldName().'['.self::POST_ANSWER_VAR.'][]');
			$input->setAttribute('style', 'display:none;');
			$out->addChild($input);
		}

		return $out;
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

		$regexpPutHtmlPlaceholder = '#\s*<[^>]+>.*(</[^>]+>)*\s*#mU';
		$regexpRemoveHtmlPlaceholder = '#'.self::htmlDelimiter.'#mU';
		$regexpRemoveClozePlaceholder = '#'.self::clozeDelimiter.'#mU';
		$regexpImageHtml = '#<img[^>]*>#mU';
		$regexpSpan = '#'.self::spanHeader.'(.*)'.self::spanFooter.'#mU';

		switch($this->apostrophe) {
			case ADA_APOSTROPHE_TEST_MULTIPLE:
				$regexpWords = '#[\w\xC0-\xFF\'’]*#mu';
			break;
			default:
			case ADA_NO_APOSTROPHE_TEST_MULTIPLE:
				$regexpWords = '#[\w\xC0-\xFF]*#mu';
			break;
		}
		$html = $this->replaceInternalLinkMedia($this->testo);
		$html = preg_replace_callback(QuestionClozeTest::regexpCloze, array($this,'putClozePlaceholder'), $html);
		$html = preg_replace_callback($regexpPutHtmlPlaceholder, array($this,'putHtmlPlaceholder'), $html);
		$html = preg_replace_callback($regexpWords,array($this,'addSpan'), $html);
		$html = preg_replace_callback($regexpRemoveHtmlPlaceholder, array($this,'removeHtmlPlaceholder'), $html);
		$html = preg_replace_callback($regexpImageHtml,array($this,'addSpan'), $html); //put a span around each image
		$html = preg_replace_callback($regexpRemoveClozePlaceholder, array($this,'removeClozePlaceholder'), $html);
		$html = preg_replace_callback($regexpSpan, array($this,'countSpanAndRemoveClozeMarker'), $html);

		return $html;
	}

	/**
	 * wrap a span around an element
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function addSpan($params) {
		if (is_array($params)) {
			$params = $params[0];
		}
		if (empty($params)) return;
		return self::spanHeader.$params.'</span>';
	}

	/**
	 * saves html coming from getPreparedText
	 * and replaced it with a placeholder
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function putHtmlPlaceholder($params) {
		$this->htmlPlaceholders[] = $params[0];
		return self::htmlDelimiter;
	}

	/**
	 * restores saved html over placeholders
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function removeHtmlPlaceholder($params = null) {
		return array_shift($this->htmlPlaceholders);
	}

	/**
	 * saves cloze markers coming from getPreparedText
	 * and replaced it with a placeholder
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function putClozePlaceholder($params) {
		$this->clozePlaceholders[] = $params[0];
		return self::clozeDelimiter;
	}

	/**
	 * restores saved cloze markers over placeholders
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function removeClozePlaceholder($params = null) {
		$value = array_shift($this->clozePlaceholders);
		return $this->addSpan($value);
	}

	/**
	 * restores saved cloze markers over placeholders
	 *
	 * @access protected
	 *
	 * @param $params params coming from getPreparedText
	 *
	 * @return string
	 * @see getPreparedText
	 */
	protected function countSpanAndRemoveClozeMarker($params) {
		$ordine = ++$this->spanInstances;
		
		if (preg_match(QuestionClozeTest::regexpCloze, $params[0], $match)) {
			$this->clozeOrders[$match[1]] = $ordine;
			$value = $match[2];
		}
		else {
			$value = $params[1];
		}

		$title = '';
		$popup = '';
		$class = '';
		if ($this->feedback) {
			$answer = $this->searchChild($ordine, 'ordine');

			$class = null;
			$popup = false;
			if (!empty($answer)) {
				$class = 'bold';
				$popup = true;
				if ($this->givenAnswer && in_array($ordine,$this->givenAnswer['risposta'][self::POST_ANSWER_VAR])) {
					$class = 'wrong_answer_test';
					$popup = false;
					if ($this->isAnswerCorrect($answer, $ordine)) {
						$class = 'right_answer_test';
						$popup = true;
					}
				}
			}
			else if ($this->givenAnswer && in_array($ordine,$this->givenAnswer['risposta'][self::POST_ANSWER_VAR])) {
				$class = 'wrong_answer_test';
			}
			/**
			 * giorgio 20/dic/2013
			 * if it's an activity of type:
			 * 	ADA_TYPE_ACTIVITY with feedback of type ADA_IMMEDIATE_TEST_INTERACTION
			 * do not show the popup AT ALL
			 */
			$parentActivity = $this->searchParent('ActivityTest');			
			if ($parentActivity->tipo{0}==ADA_TYPE_ACTIVITY && $parentActivity->tipo{2}==ADA_IMMEDIATE_TEST_INTERACTION) $popup = false;
			
			if (!is_null($class)) {				
				if ($popup && $answer && ($this->rating || $this->rating_answer)) {
					$ref = $this->id_nodo.'_'.$answer->ordine;
					$class.= ' answerPopup';
					$title = ' title="'.$ref.'"';
					
					$popup = '<div id="popup_'.$ref.'" style="display:none;">'.$answer->correttezza.' '.translateFN('Punti').'</div>';
				}
				$class = ' '.$class;
			}
		}

		$rel = $this->getPostFieldName().'['.self::POST_ANSWER_VAR.'][]';
		$html = substr(self::spanHeader,0,-2).$class.'"'.$title.' rel="'.$rel.'" value="'.$ordine.'">'.$value.self::spanFooter.$popup;

		if ($this->feedback && RootTest::isSessionUserAStudent()) {
			$html = str_replace('answer_erase_item_test', '', $html);
		}

		$html = str_replace("\n",'',$html);
		return $html;
	}

	/**
	 * function that will replace cloze entries in text
	 *
	 * @param array $params - matched params from regexp
	 * @return a string of HTML
	 * @see getPreparedText
	 */
	public function clozePlaceholder($params) {
		$ordine = $params[1];
		$value = $params[2];

		$value = '<cloze title="'.$ordine.'">'.$value.'</cloze>';
		$html = $value;
		
		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {	
			$span = CDOMElement::create('span','class:clozePopup,title:'.$this->id_nodo.'_'.$ordine);
			$html.= $span->getHtml();

			$div = CDOMElement::create('div','id:popup_'.$this->id_nodo.'_'.$ordine);
			$div->setAttribute('style','display:none;');
			$risposte = array();
			$answers = $this->searchChild($ordine, 'ordine', true);
			if (!empty($answers)) {
				foreach($answers as $v) {
					$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
					$risposte[] = $v->testo.' ('.$v->correttezza.' '.translateFN('Punti').')';
				}
			}
			$div->addChild(new CText(implode('<br/>',$risposte)));
			$html.= $div->getHtml();
		}

		return $html;
	}

	/**
	 * implementation of exerciseCorrection for Normal Cloze question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		$points = 0;

		//atypical correction: $data[self::POST_ANSWER_VAR] contains orders of erased/highlighted words by user
		//so we have to check each order if match with an answer
		if (is_array($data) && !empty($data) && !empty($this->_children)) {
			foreach($data[self::POST_ANSWER_VAR] as $v) {
				foreach($this->_children as $answer) {
					if ($this->isAnswerCorrect($answer, $v)) {
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
	 * @param $order user given answer order
	 * @param $value not used
	 *
	 * @return boolean
	 */
	protected function isAnswerCorrect($answer, $order, $value = null) {
		//atypical correction: $order contains the order of the erased/highlighted word by user
		//so we have to check just the order and not the id. $value is not used
		return ($answer->ordine == $order && $answer->correttezza > 0);
	}

	/**
	 * Computes cloze orders with span orders (calling getPreparedText)
	 * and returns them
	 *
	 * @access public
	 *
	 * @return array
	 * @see getPreparedText
	 */
	public function getClozeOrders() {
		$this->getPreparedText();
		return $this->clozeOrders;
	}

	/**
	 * Function that automatic creates answers (and save it to database) for cloze question type
	 * ADA_ERASE_TEST_SIMPLICITY version
	 *
	 * @global db $dh
	 *
	 * @param int $question_id
	 * @param array $data question data
	 * @param array $test test record
	 *
	 * @see QuestionClozeTest::createClozeAnswers
	 */
	public static function createEraseClozeAnswers($question_id,$data,$test) {
		$dh = $GLOBALS['dh'];

		QuestionClozeTest::createClozeAnswers($question_id,$data,$test);

		require_once(MODULES_TEST_PATH.'/include/nodeTest.class.inc.php');
		$questionObj = nodeTest::readNode($question_id);
		//the next function computes cloze orders with span orders and returns it
		$nuovi_ordini = $questionObj->getClozeOrders();

		//save new computed orders for each answer
		$res = $dh->test_getNodesByParent($questionObj->id_nodo);
		if (!empty($res)) {
			foreach($res as $v) {
				$v['ordine'] = $nuovi_ordini[$v['ordine']];
				$dh->test_updateNode($v['id_nodo'],array('ordine'=>$v['ordine']));
			}
		}
		
		//change original orders with new ones inside question text
		$testo = $questionObj->testo;
		foreach($nuovi_ordini as $k=>$v) {
			$testo = str_replace('<cloze title="'.$k.'">','<clozeTMP title="'.$v.'">',$testo);
		}
		$testo = str_replace('<clozeTMP','<cloze',$testo);
		$dh->test_updateNode($questionObj->id_nodo,array('testo'=>$testo));
	}
}
