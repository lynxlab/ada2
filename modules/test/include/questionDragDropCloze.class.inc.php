<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionDragDropClozeTest extends QuestionClozeTest
{
	protected $boxPosition;

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
	protected function renderingHtml(&$ref=null,$feedback=false,$rating=false,$rating_answer=false) {
		if (!$this->display) return new CText(''); //if we don't have to display this question, let's return an empty item
		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		$li = CDOMElement::create('li');
		$li->setAttribute('class', 'answer_cloze_test');

		$preparedText = $this->getPreparedText($feedback,$rating,$rating_answer);

		/**
		 * @author giorgio 18/set/2014
		 *
		 * Do the drag drop box if $this->testo has
		 * some <cloze> tag inside.
		 */
		$doDragDropBox = (preg_match(self::regexpCloze, $this->testo)!==0);
		
		if (!$feedback && $doDragDropBox) {
			$this->buildDragDropElements($li,$preparedText);
		}
		else {
			$li->addChild(new CText($preparedText));
		}

		$ref->addChild($li);
		return $out;
	}

	/**
	 * builds an array of box titles from the xml
	 * inside the DB field: titolo_dragdrop 
	 * 
	 * @author giorgio 11/nov/2013
	 * 
	 * @param string $xmlTitles xml from wich to extract
	 * @return multitype:string NULL if $xmlTitle is null | array of titles with key as table num
	 * 
	 * @access public
	 */
	public static function extractTitlesFromData($xmlTitles=null)
	{
		$dragdropTitles = array ();
		if (!is_null($xmlTitles) && !empty($xmlTitles))
		{
			$xmldoc = new DOMDocument ( "1.0", "UTF-8" );
			if (@$xmldoc->loadXML ($xmlTitles)) 
			{
				$titleElements = $xmldoc->getElementsByTagName ('titolo');
				foreach ($titleElements as $titleElement) 
				{
					$tablenum = intval ($titleElement->getAttribute ('table'));
					$dragdropTitles [$tablenum] = $titleElement->nodeValue;
				}
			}
		}
		
		if (empty($dragdropTitles)) $dragdropTitles[1] = '';
		
		return $dragdropTitles;
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
	public function buildDragDropElements(CBase $html,$preparedText,$showAnswers = false) {
//      giorgio: MOVED INSIDE THE FOREACH
// 		$ulBox = CDOMElement::create('ul');
// 		$ulBox->setAttribute('id', 'ulBox'.$this->id_nodo);
// 		$ulBox->setAttribute('class', 'dragdropBox sortable drop'.$this->id_nodo);
		
		$dragdropTitles = self::extractTitlesFromData($this->titolo_dragdrop);

		$this->shuffledChildren = $this->_children;
		shuffle($this->shuffledChildren);

		/**
		 * @author giorgio 08/nov/2013
		 * boolean to check if box in $text var (i.e. the droppable)
		 * has been added already
		 */
		$textAdded = false;
		// must get last element key to output a clearfix div
		end ($dragdropTitles);
		$lastKey = key ($dragdropTitles);
		foreach ($dragdropTitles as $dndKey=>$dndTitle)
		{
			$ulBox = CDOMElement::create('ul');
			$ulBox->setAttribute('id', 'ulBox'.$this->id_nodo.'_'.$dndKey);
			$ulBox->setAttribute('class', 'dragdropBox sortable drop'.$this->id_nodo);

			$box = CDOMElement::create('div');
			if (!empty($dndTitle)) {
				$span = CDOMElement::create('span','class:title_dragdrop');
				$span->addChild(new CText($dndTitle));
				$box->addChild($span);
			}
			$box->addChild($ulBox);

		if (!empty($this->_children)) {
			foreach($this->shuffledChildren as $c) {
					/*
					 * @author giorgio 08/nov/2013
					 * 
					 * NOTE: in the answer object titolo_dragdrop there is the
					 * id of the table in which to put the answer that therefore
					 * must be euqal to the current cycle key of the dragdropTitles array
					 */
					if (is_null($c->titolo_dragdrop) || $dndKey==$c->titolo_dragdrop)
					{
						$item = CDOMElement::create('li');
						$item->setAttribute('class',$dndKey.' draggable drag'.$this->id_nodo);
						$item->setAttribute('id', 'answer'.$c->id_nodo);
						if ($showAnswers) {
							$item->setAttribute('onclick',"showAnswers('ordine".$c->ordine."');");
						}
						
						$outText = $c->testo;
						
						$item->addChild(new CText($outText));
						$ulBox->addChild($item);
					}
				}
			}

			$text = CDOMElement::create('div');
			$text->addChild(new CText($preparedText));

			//switch per gestire la stampa del box delle risposte
			$boxClass = 'divDragDropBox ';
			$textClass = 'textDragDrop';
			switch($this->boxPosition) {
				case ADA_TOP_TEST_DRAGDROP:
					$html->addChild($box);
					if (!$textAdded && $dndKey==$lastKey) {
						$html->addChild($text);
						$textAdded = true;
					}
					$boxClass.= 'top';
				break;
				case ADA_RIGHT_TEST_DRAGDROP:
					$html->addChild($box);
					if (!$textAdded) {
						$html->addChild($text);
						$textAdded = true;
					}
					$boxClass.= 'right';
					$textClass.= 'Left';
				break;
				case ADA_BOTTOM_TEST_DRAGDROP:
					if (!$textAdded) {
						$html->addChild($text);
						$textAdded = true;
					}
					$html->addChild($box);
					$boxClass.= 'bottom';
				break;
				case ADA_LEFT_TEST_DRAGDROP:
					$html->addChild($box);
					if (!$textAdded && $dndKey==$lastKey) {
						$html->addChild($text);
						$textAdded = true;
					}
					$boxClass.= 'left';
					$textClass.= 'Right';
				break;
			}
			if ($dndKey == $lastKey) $html->addChild(CDOMElement::create('div','class:clear'));
			$box->setAttribute('class', $dndKey.' '.$boxClass);
			$text->setAttribute('class', $textClass);
						
		} // end foreach ($dragdropTitles);
		
		return $html;
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
		//$value = $params[2]; //non serve!

		if ($this->feedback) {
			$risposta = $this->givenAnswer['risposta'][self::POST_ANSWER_VAR][$ordine];
			$answer = $this->searchChild($risposta);
			$obj = CDOMElement::create('div');
			$class = 'answer_dragdrop_test';
			if (!empty($risposta) && $answer) {
				foreach($this->_children as $v) {
					if ($ordine == $v->ordine) {
						$obj->addChild(new CText($answer->testo));
						if ($this->isAnswerCorrect($v, $ordine, $answer->id_nodo)) {
							$class.= ' right_answer_test';
						}
						else {
							$class.= ' wrong_answer_test';
						}
						break;
					}
				}
			}
			else {
				$class.= ' empty_answer_test';
			}

			$correctAnswer = false;
			if (($this->rating || $this->rating_answer) && !strstr($class,'right_answer_test')) {
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
		}
		else {
			$html = '';

			$name = $this->getPostFieldName();
			$post_data = $this->getPostData();

			$id = $this->id_nodo.'_'.$ordine;

			$input = CDOMElement::create('hidden');
			$input->setAttribute('id', 'dropInput'.$id);
			$input->setAttribute('name', $name.'['.self::POST_ANSWER_VAR.']['.$ordine.']');
			$input->setAttribute('value', $post_data[self::POST_ANSWER_VAR][$ordine]);
			$html.= $input->getHtml();

			$ddUl = CDOMElement::create('ul');
			$ddUl->setAttribute('id', 'drop'.$id);
			$ddUl->setAttribute('class', 'sortable drop'.$this->id_nodo);
			$html.= $ddUl->getHtml();
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
						$v->correttezza = is_null($v->correttezza)?0:$v->correttezza;
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
	 * implementation of exerciseCorrection for Cloze question type
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
		if ($answer->ordine == $order) {
			$return = ($answer->id_nodo == $value && $answer->correttezza > 0);
			if (!$return) {
				$givenAnswer = $this->searchChild($value);
				if (is_object($givenAnswer)) {
					$return = (strcasecmp($answer->testo, $givenAnswer->testo) == 0 && $answer->correttezza > 0);
				}
			}
			return $return;
		}
		else {
			return false;
		}
	}
}
