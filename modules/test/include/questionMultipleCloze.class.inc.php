<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionMultipleClozeTest extends QuestionClozeTest
{
	const postVariable = 'multi';
	const clozeDelimiter = '§§§';
	const htmlDelimiter = '~~~';
	const spanHeader = '<span class="answer_multi_item_test">';
	const spanFooter = '</span>';

	protected $boxPosition;
	protected $apostrophe;

	protected $tableData = null;
	protected $order_points = null;

	protected $clozePlaceholders = array();
	protected $htmlPlaceholders = array();

	protected $spanInstances = 0;
	protected $clozeOrders = array();
	protected $exerciseWords = array();

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
		$this->boxPosition = $this->tipo{4};
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

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_TUTOR) {
			$rating = true;
			$rating_answer = true;
		}

		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		$li = new CLi();
		$li->setAttribute('class', 'answer_cloze_multiple_test');
		$this->buildDragDropElements($li,$this->getPreparedText($feedback,$rating,$rating_answer));
		$ref->addChild($li);

		return $out;
	}

	/**
	 * builds drag'n'drop exercise html
	 *
	 * @param CBase $html CBase element reference
	 * @param string $preparedText cloze prepared text
	 * @param boolean $showAnswers call showAnswers javascript function on elements' click
	 *
	 * @return CBase reference
	 * @see getPreparedText
	 */
	public function buildDragDropElements(CBase $html,$preparedText,$showAnswers = false, $edit = false) {
		$text = CDOMElement::create('div');
		$text->addChild(new CText($preparedText));

		$box = $this->createTable($edit);

		//switch per gestire la stampa del box delle risposte
		$boxClass = 'multipleClozeDiv ';
		$textClass = 'textDragDrop';
		switch($this->boxPosition) {
			case ADA_TOP_TEST_DRAGDROP:
				$html->addChild($box);
				$html->addChild($text);
				$boxClass.= 'top';
			break;
			case ADA_RIGHT_TEST_DRAGDROP:
				$html->addChild($box);
				$html->addChild($text);
				$boxClass.= 'right';
				$textClass.= 'Left';
			break;
			case ADA_BOTTOM_TEST_DRAGDROP:
				$html->addChild($text);
				$html->addChild($box);
				$boxClass.= 'bottom';
			break;
			case ADA_LEFT_TEST_DRAGDROP:
				$html->addChild($box);
				$html->addChild($text);
				$boxClass.= 'left';
				$textClass.= 'Right';
			break;
		}
		$divclear = CDOMElement::create('div','class:clear');
		$html->addChild($divclear);
		$box->setAttribute('class', $boxClass);
		$text->setAttribute('class', $textClass);

		return $html;
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
public function countSpanAndRemoveClozeMarker($params) {
		$ordine = ++$this->spanInstances;
		$isCloze = false;
		if (preg_match(QuestionClozeTest::regexpCloze, $params[0], $match)) {
			$this->clozeOrders[$match[1]] = $ordine;
			$isCloze = true;
			$value = $match[2];
		}
		else {
			$value = $params[1];
		}

		$this->exerciseWords[$ordine] = $value;

		return $this->clozePlaceholder(array($params[0],$ordine,$value));
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

		$new_ordine = $ordine*100; //fix to respect dragdrop.js routines
		$answer = $this->searchChild($ordine, 'ordine');

		if ($this->feedback) {
			$tableAnswers = $this->getAnswersTableData();
			$givenAnswers = $this->prepareGivenAnswer($this->givenAnswer['risposta'][self::POST_ANSWER_VAR]);

			if($this->isAGivenAnswer($ordine,$givenAnswers)) {
				$element = CDOMElement::create('ul','id:drop'.$this->id_nodo.'_'.$new_ordine.', class:sortable drop'.$this->id_nodo);
				$return = $element->getHtml();
			}
			else if (!empty($answer)) {
				$element = CDOMElement::create('span','class:wrong_answer_test answerPopup');
				$element->addChild(new CText($value));

				$popup = '';
				if (($this->rating || $this->rating_answer) && !empty($answer)) {
					$popup = '<div id="popup_'.$this->id_nodo.'_'.$ordine.'" style="display:none;">'.$answer->correttezza.' '.translateFN('Punti').'</div>';
					$element->setAttribute('title', $this->id_nodo.'_'.$ordine);
				}

				$return = trim($element->getHtml()).$popup;
			}
			else {
				$return = $value;
			}
		}
		else {
			$popup = '';
			$dragBox = CDOMElement::create('ul','id:drop'.$this->id_nodo.'_'.$new_ordine.', class:sortable drop'.$this->id_nodo);
			if ($_SESSION['sess_id_user_type'] != AMA_TYPE_STUDENT) {
				if (!empty($answer)) {
					$ordine = $answer->ordine;
					$value = $answer->testo;
					$correttezza = $answer->correttezza;
					$answer = true;
				}
				else {
					$answer = false;
					$correttezza = null;
				}

				$showItEmpty = false;
				$tableData = $this->getTableData();
				foreach($tableData['answers'] as $array) {
					foreach($array as $ordini) {
						if (in_array($ordine,$ordini)) {
							$showItEmpty = true;
							break;
						}
					}
				}

				if (!$showItEmpty) {
					$dragBox->addChild($this->createLiItem($ordine,$value,$answer,$correttezza));
					$dragBox->setAttribute('class', $dragBox->getAttribute('class').' full');
				}

				if ($answer) {
					$popup = '<div id="popup_'.$this->id_nodo.'_'.$ordine.'" style="display:none;">'.($showItEmpty?'<b>'.$value.'</b>:':'').' '.$correttezza.' '.translateFN('Punti').'</div>';
					$dragBox->setAttribute('title', $this->id_nodo.'_'.$ordine);
					$dragBox->setAttribute('class', $dragBox->getAttribute('class').' answerPopup');
				}
			}
			else {
				$dragBox->addChild($this->createLiItem($ordine,$value));
				$dragBox->setAttribute('class', $dragBox->getAttribute('class').' full');
			}
			$return = trim($dragBox->getHtml()).$popup;
		}

		return $return;
	}

	/**
	 * Return order array that matches in both parameter
	 *
	 * @param array $risposte
	 * @param array $risposte
	 *
	 * @return array
	 */
	protected function compareGivenAnswersWithTableAnswers($givenAnswers,$answers) {
		$orders = array();
		if ($givenAnswers) {
			foreach($answers as $k=>$array) {
				foreach($array as $j=>$answer) {
					if (!empty($givenAnswers[$k][$j])) {
						foreach($givenAnswers[$k][$j] as $ordine) {
							if ($this->isAnswerCorrect($answer, $ordine)) {
								$orders[] = $ordine;
							}
						}
					}
				}
			}
		}
		return $orders;
	}

	/**
	 * check if a order is present in student given answers
	 *
	 * @param int $ordine
	 * @param array $givenAnswers
	 *
	 * @return boolean
	 */
	protected function isAGivenAnswer($ordine,$givenAnswers) {
		if (!empty($givenAnswers)) {
			foreach($givenAnswers as $array) {
				foreach($array as $a) {
					foreach($a as $v) {
						if ($ordine == $v) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Return prepared student given answer
	 * 
	 * @param array $risposte
	 * 
	 * @return \array|boolean
	 */
	protected function prepareGivenAnswer($risposte) {
		if (!empty($risposte) && is_array($risposte)) {
			foreach($risposte as $k=>$array) {
				if (!empty($array) && is_array($array)) {
					foreach($array as $j=>$v) {
						$risposte[$k][$j] = explode(',',$v);
					}
				}
			}
			return $risposte;
		}
		else {
			return false;
		}
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

		if (is_array($data) && !empty($data)) {
			$tableAnswers = $this->getAnswersTableData();
			$givenAnswers = $this->prepareGivenAnswer($data[self::POST_ANSWER_VAR]);
			$validOrders = $this->compareGivenAnswersWithTableAnswers($givenAnswers, $tableAnswers);

			if (!empty($validOrders)) {
				$answers_points = $this->getOrderPoints();
				foreach($validOrders as $o) {
					$points+= $answers_points[$o];
				}
			}
		}

		return array('points'=>$points,'attachment'=>null);
	}

	/**
	 * return true/false if the given value matches the answer
	 *
	 * @access protected
	 *
	 * @param $answer array containig correct answers order
	 * @param $order user given answer order
	 * @param $value not used
	 *
	 * @return boolean
	 */
	protected function isAnswerCorrect($answer, $order, $value = null) {
		//atypical correction:
		//$answer is an array that contains all the orders coming from getTableData
		//$order contains the order of the dropped word by user
		//$value not used

		$order_points = $this->getOrderPoints();
		return (((is_array($answer) && in_array($order,$answer)) || $answer == $order) && $order_points[$order] > 0);
	}

	/**
	 * Populate the order_points array: order as key and point as value
	 *
	 * @return type
	 */
	protected function getOrderPoints() {
		if (is_null($this->order_points)) {
			$this->order_points = array();
			if (!empty($this->_children)) {
				foreach($this->_children as $a) {
					$this->order_points[$a->ordine] = $a->correttezza;
				}
			}
		}
		return $this->order_points;
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
	 * ADA_MULTIPLE_TEST_SIMPLICITY version
	 *
	 * @global db $dh
	 *
	 * @param int $question_id
	 * @param array $data question data
	 * @param array $test test record
	 * 
	 * @see QuestionEraseClozeTest::createEraseClozeAnswers
	 * @see QuestionClozeTest::createClozeAnswers
	 */
	public static function createMultipleClozeAnswers($question_id,$data,$test) {
		require_once(MODULES_TEST_PATH.'/include/questionEraseCloze.class.inc.php');
		QuestionEraseClozeTest::createEraseClozeAnswers($question_id,$data,$test);
	}

	/**
	 * Create drag'n'drop table
	 *
	 * @param boolean $edit
	 *
	 * @return CBase
	 */
	public function createTable($edit = false) {
		$tableData = $this->getTableData();

		$addRow = CDOMElement::create('th','class:colButton');
		$addRow->addChild(CDOMElement::create('div','class:addSilk,onclick:addRow();'));
		$addCol = CDOMElement::create('th','class:colButton');
		$addCol->addChild(CDOMElement::create('div','class:addSilk,onclick:addCol();'));
		$delRow = CDOMElement::create('td','class:colButton thVert');
		$delRow->addChild(CDOMElement::create('div','class:deleteSilk,onclick:delRow(this);'));
		$delCol = CDOMElement::create('th','class:colButton');
		$delCol->addChild(CDOMElement::create('div','class:deleteSilk,onclick:delCol(this);'));

		$container = CDOMElement::create('div');
		$table = CDOMElement::create('table','class:multipleClozeTable');
		$container->addChild($table);

		$thead = CDOMElement::create('thead');
		$table->addChild($thead);

		//table header
		if (!empty($tableData['cols_label'])) {
			$header = CDOMElement::create('tr');
			$thead->addChild($header);

			$th = CDOMElement::create('th','class:colButton empty');
			if ($edit) {
				$button = CDOMElement::create('button','onclick:emptyTable();');
				$button->setAttribute('type', 'button');
				$button->addChild(new CText(translateFN('Svuota Tabella')));
				$th->addChild($button);
			}
			$header->addChild($th);

			for($i=0;$i<$tableData['cols'];$i++) {
				$th = CDOMElement::create('th');
				$header->addChild($th);

				if ($edit) {
					$col = CDOMElement::create('text','class:inputAnswers cols_label');
					$col->setAttribute('value', $tableData['cols_label'][$i]);
					$col->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[cols_label][]');
					$th->addChild($col);
				}
				else {
					$trimmed = trim($tableData['cols_label'][$i]);
					if (empty($trimmed)) {
						$th->setAttribute('class',trim($th->getAttribute('class').' empty'));
					}
					$th->addChild(new CText($tableData['cols_label'][$i]));
				}
			}
			if ($edit) {
				$header->addChild($addCol);
			}
		}

		$tbody = CDOMElement::create('tbody');
		$table->addChild($tbody);

		//table body
		for($i=0,$y=0;$i<$tableData['rows'];$i++) {
			$tr = CDOMElement::create('tr');
			$tbody->addChild($tr);

			if (!empty($tableData['rows_label'])) {
				$th = CDOMElement::create('th', 'class:thVert');
				$tr->addChild($th);

				if ($edit) {
					$row = CDOMElement::create('text','class:inputAnswers rows_label');
					$row->setAttribute('value',$tableData['rows_label'][$i]);
					$row->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[rows_label][]');
					$th->addChild($row);
				}
				else {
					$trimmed = trim($tableData['rows_label'][$i]);
					if (empty($trimmed)) {
						$th->setAttribute('class',trim($th->getAttribute('class').' empty'));
					}
					$th->addChild(new CText($tableData['rows_label'][$i]));
				}
			}

			for($j=0;$j<$tableData['cols'];$j++,$y++) {
				$td = CDOMElement::create('td');

				//Drag'n'drop cell
				$input = CDOMElement::create('hidden','class:inputAnswers,id:dropInput'.$this->id_nodo.'_'.$y);
				if ($edit) {
					$input->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[answers]['.$i.'][]');
				}
				else {
					$name = $this->getPostFieldName();
					$post_data = $this->getPostData();
					$input->setAttribute('name', $name.'['.self::POST_ANSWER_VAR.']['.$i.']['.$j.']');
					$input->setAttribute('value', $post_data[self::POST_ANSWER_VAR][$i][$j]);
				}

				if ($this->feedback) {
					$ddUl = CDOMElement::create('div');
					$givenAnswers = $this->prepareGivenAnswer($this->givenAnswer['risposta'][self::POST_ANSWER_VAR]);
					$ordini = $givenAnswers[$i][$j];
					foreach($ordini as $order) {
						if ($this->isAnswerCorrect($tableData['answers'][$i][$j], $order)) {
							$class = 'right_answer_test';
							$score = $this->searchChild($order, 'ordine')->correttezza;
						}
						else {
							$class = 'wrong_answer_test';
							$score = 0;
						}
						$div = CDOMElement::create('div','class:'.$class);
						$span = CDOMElement::create('span','class:answerPopup');
						if (isset($this->exerciseWords[$order])) $span->addChild(new CText($this->exerciseWords[$order]));
						$div->addChild($span);
						if ($this->rating || $this->rating_answer) {
							$span->setAttribute('title', $this->id_nodo.'_'.$order);

							$popup = CDOMElement::create('div','id:popup_'.$this->id_nodo.'_'.$order);
							$popup->setAttribute('style','display:none;');
							$popup->addChild(new CText($score.' '.translateFN('Punti')));
							$ddUl->addChild($popup);
						}

						$ddUl->addChild($div);
					}
				}
				else {
					$ddUl = CDOMElement::create('ul','id:drop'.$this->id_nodo.'_'.$y);
					$ddUl->setAttribute('class', 'multiDragDropBox sortable drop'.$this->id_nodo);
					if ($edit || $_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR) {
						$ordini = $tableData['answers'][$i][$j];
						if (!empty($ordini)) {
							$input->setAttribute('value', implode(',',$ordini));
							foreach($this->_children as $v) {
								if (in_array($v->ordine,$ordini)) {
									$ddUl->addChild($this->createLiItem($v->ordine,$v->testo,true,$v->correttezza));
								}
							}
						}
					}
				}

				$td->addChild($input);
				$td->addChild($ddUl);
				$tr->addChild($td);
			}

			if ($edit) {
				$tr->addChild($delRow);
			}
		}

		$tfoot = CDOMElement::create('tfoot');
		$table->addChild($tfoot);

		if ($edit) {
			$tr = CDOMElement::create('tr');
			$tfoot->addChild($tr);
			$tr->addChild($addRow);
			//table footer
			for($j=0;$j<$tableData['cols'];$j++) {
				$tr->addChild($delCol);
			}
			$th = CDOMElement::create('th');
			$th->addChild(CDOMElement::create('div','class:deleteSilk,onclick:resetTable();'));
			$tr->addChild($th);
		}

		//hidden clonable fields
		$tr = CDOMElement::create('tr');
		$tr->setAttribute('style','display:none');
		$tfoot->addChild($tr);

		//clonable cell
		$td = CDOMElement::create('td','id:clonableCell');
		$input = CDOMElement::create('hidden','id:dropInput'.$this->id_nodo.'_cell');
		$input->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[answers][row][]');
		$input->setAttribute('value', '');
		$ddUl = CDOMElement::create('ul','id:drop'.$this->id_nodo.'_cell');
		$ddUl->setAttribute('class', 'multiDragDropBox sortable drop'.$this->id_nodo);
		$td->addChild($input);
		$td->addChild($ddUl);
		$tr->addChild($td);

		//clonable column
		$th = CDOMElement::create('th','id:clonableCol');
		$col = CDOMElement::create('text','class:cols_label');
		$col->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[cols_label][]');
		$th->addChild($col);
		$tr->addChild($th);

		//clonable row
		$th = CDOMElement::create('th','id:clonableRow,class:thVert');
		$row = CDOMElement::create('text','class:rows_label');
		$row->setAttribute('name', QuestionMultipleClozeTest::postVariable.'[rows_label][]');
		$th->addChild($row);
		$tr->addChild($th);

		$clone = clone $delRow;
		$clone->setAttribute('id','clonableDelRow');
		$tr->addChild($clone);

		$clone = clone $delCol;
		$clone->setAttribute('id','clonableDelCol');
		$tr->addChild($clone);

		return $container;
	}

	/**
	 * updates answers data relative to position in table
	 *
	 * @param type $data
	 */
	public function updateAnswerTable($data) {
		$dh = $GLOBALS['dh'];

		$updateData = false;
		if ($data['operation'] == 'save') {
			$updateData = true;
			$titolo_dragdrop = $this->setTableData($data['rows_label'], $data['cols_label'], $data['answers']);
		}
		else if ($data['operation'] == 'reset') {
			$updateData = true;
			$titolo_dragdrop = null;
		}

		if ($updateData) {
			$dh->test_updateNode($this->id_nodo,array('titolo_dragdrop'=>$titolo_dragdrop));
			echo '1';
			exit();
		}
	}

	/**
	 * @return boolean
	 */
	protected function isAnswersTableDataEmpty() {
		$var = $this->getAnswersTableData();
		return (!empty($var))?true:false;
	}

	/**
	 * @return array
	 */
	protected function getAnswersTableData() {
		$data = $this->getTableData();
		return $data['answers'];
	}

	/**
	 * Unserialize table data if presents or use default values
	 *
	 * @return array
	 */
	protected function getTableData() {
		if (!is_null($this->tableData)) {
			return $this->tableData;
		}
		else if (!empty($this->titolo_dragdrop)) {
			$this->tableData = unserialize($this->titolo_dragdrop);
		}
		else {
			$this->tableData = array(
				'rows'=>1,
				'cols'=>1,
				'rows_label'=>array(''),
				'cols_label'=>array(''),
				'answers'=>array(
				),
			);
		}
		
		return $this->tableData;
	}

	/**
	 * sets table data and return it serialized
	 *
	 * @param array $rows_label string array
	 * @param array $cols_label string array
	 * @param type $answers string multidimensional array
	 * 
	 * @return string
	 */
	protected function setTableData($rows_label,$cols_label,$answers) {
		if (!empty($answers)) {
			foreach($answers as $k=>$array) {
				if (!empty($array)) {
					foreach($array as $j=>$v) {
						$answers[$k][$j] = explode(',',$v);
					}
				}
			}
		}

		$this->tableData = array(
			'rows'=>count($rows_label),
			'cols'=>count($cols_label),
			'rows_label'=>$rows_label,
			'cols_label'=>$cols_label,
			'answers'=>$answers,
		);

		return serialize($this->tableData);
	}

	/**
	 * Create li item to be added as child of ul drag'n'drop box
	 *
	 * @param int $ordine order
	 * @param string $testo word
	 * @param boolean $isAnswer is a valid answer or not
	 * @param type $correttezza answer points
	 *
	 * @return CBase
	 */
	protected function createLiItem($ordine,$testo,$isAnswer = false,$correttezza = 0) {
		$dragItem = CDOMElement::create('li');
		$dragItem->setAttribute('class','draggable drag'.$this->id_nodo);
		$dragItem->setAttribute('id', 'answer'.$ordine); //used ordine instead of id_nodo voluntarily
		$dragItem->addChild(new CText($testo));

		if ($_SESSION['sess_id_user_type'] == AMA_TYPE_AUTHOR && $isAnswer) {
			$punti = is_null($correttezza)?0:$correttezza;

			$span = CDOMElement::create('span','class:clozePopup,title:'.$this->id_nodo.'_'.$ordine);
			$dragItem->addChild($span);

			$div = CDOMElement::create('div','id:popup_'.$this->id_nodo.'_'.$ordine);
			$div->setAttribute('style','display:none;');
			$div->addChild(new CText($punti.' '.translateFN('Punti')));
			$dragItem->addChild($div);
		}

		return $dragItem;
	}
}
