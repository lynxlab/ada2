<?php
/**
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

class AnswerControlTest extends FormControl {
	protected static $i = 0;
	protected $item;
	protected $id;
	protected $open_answer = false;
	protected $show_case_sensitive = false;
	protected $clonable = false;

	/**
	 * Answer Control constructor
	 * It DOESN'T call parent constructor
	 *
	 * @param boolean $open_answer
	 * @param boolean $show_case_sensitive
	 * @param string $id tag id
	 * @param boolean $clonable if true, the control will be used as subject to clone (javascript)
	 */
	public function __construct($open_answer,$show_case_sensitive,$id = null,$clonable = null) {
		$this->id = $id;
		$this->_controlId = self::$i++;
        $this->_controlData = array();
        $this->_selected = FALSE;
        $this->_isRequired = FALSE;
        $this->_isMissing = FALSE;
		$this->_hidden = false;

        $this->_validator = null;

		$this->item = array();
		$this->_attributes = array();

		$this->open_answer = $open_answer;
		$this->show_case_sensitive = $show_case_sensitive;

		if ($clonable) {
			$this->_hidden = true;
			$this->clonable = true;
		}
    }

	/**
	 * Function called by render method to initialize components
	 * (following business logic and with data) that will be rendered
	 *
	 * @see render
	 */
	public function constructComponents() {
		if ($this->open_answer) {
			$span = CDOMElement::create('span');
			$checkbox_value = CDOMElement::create('hidden','name:other_answer[]');
			$checkbox_value->setAttribute('class', 'other_answer');
			$checkbox = CDOMElement::create('checkbox');
			$checkbox->setAttribute('class', 'other_answer_checkbox');
			$checkbox->setAttribute('onchange', 'change_other_answer(this);');
			if ($this->_controlData['other_answer']) {				
				$checkbox_value->setAttribute('value', 1);
				$checkbox->setAttribute('checked','');
			}
			else {
				$checkbox_value->setAttribute('value', 0);
			}
			$span->addChild($checkbox_value);
			$span->addChild($checkbox);
			$this->item['other_answer'] = $span;
		}
		else {
			$this->item['other_answer'] = null;
		}

		$this->_controlData['answer'] = Node::prepareInternalLinkMediaForEditor($this->_controlData['answer']);

		$input = CDOMElement::create('text','name:answer[]');
		$input->setAttribute('class', 'answer');
		$input->setAttribute('value', htmlspecialchars($this->_controlData['answer']));
		if ($this->clonable) {
			$input->setAttribute('disabled','');
		}

		$img = CDOMElement::create('img','class:addImage,onclick:insertImage(this);,title:'.translateFN('Inserisci Immagine'));
		$img->setAttribute('src', 'img/img.png');

		$this->item['answer'] = CDOMElement::create('span');
		$this->item['answer']->addChild($input);
		$this->item['answer']->addChild($img);

		$this->item['value'] = CDOMElement::create('text','name:value[]');
		$this->item['value']->setAttribute('class', 'value');
		$this->item['value']->setAttribute('value', $this->_controlData['value']);

		if ($this->show_case_sensitive) {
			$span = CDOMElement::create('span');
			$checkbox_value = CDOMElement::create('hidden','name:case_sensitive[]');
			$checkbox_value->setAttribute('class', 'case_sensitive');
			$checkbox = CDOMElement::create('checkbox');
			$checkbox->setAttribute('class', 'case_sensitive_checkbox');
			$checkbox->setAttribute('onchange', 'change_case_sensitive(this);');			
			if ($this->_controlData['case_sensitive']) {
				$checkbox_value->setAttribute('value',1);
				$checkbox->setAttribute('checked','');
			}
			else {
				$checkbox_value->setAttribute('value',0);
			}
			$span->addChild($checkbox_value);
			$span->addChild($checkbox);
			$this->item['case_sensitive'] = $span;
		}
		else {
			$this->item['case_sensitive'] = null;
		}

		$this->item['record'] = CDOMElement::create('hidden','name:record[]');
		$this->item['record']->setAttribute('class', 'record');
		$this->item['record']->setAttribute('value', $this->_controlData['record']);
	}

	/**
	 * Control rendering
	 *
	 * @return string
	 * 
	 * @see constructComponents
	 */
    public function render() {
		$this->constructComponents();

		$html = '';
		foreach ($this->item as $field=>$v) {
			if (is_null($v)) continue;
			
			if ($this->clonable) {
				$this->item[$field]->setAttribute('disabled','');
			}

			if ($field == 'record') {
				$html.= $this->item[$field]->getHtml();
			}
			else {
				$div = CDOMElement::create('div');
				$div->setAttribute('class', 'answers_cell '.$field);
				$div->addChild($this->item[$field]);
				$html.= $div->getHtml();
			}
        }

		$div = CDOMElement::create('div');
		$div->setAttribute('class','answers_cell operations');

		$a = CDOMElement::create('a');
		$a->addChild(new CText(translateFN('Su')));
		$a->setAttribute('href','javascript:void(0);');
		$a->setAttribute('onclick',"move(this,'up');");
		$div->addChild($a);

		$a = CDOMElement::create('a');
		$a->addChild(new CText(translateFN('Giù')));
		$a->setAttribute('href','javascript:void(0);');
		$a->setAttribute('onclick',"move(this,'down');");
		$div->addChild($a);
			
		$a = CDOMElement::create('a');
		$a->addChild(new CText(translateFN('Cancella')));
		$a->setAttribute('href','javascript:void(0);');
		if (!is_null($this->id)) {
			$a->setAttribute('onclick',"del(this,".$this->id.");");
		}
		else {
			$a->setAttribute('onclick',"del(this);");
		}
		$div->addChild($a);

		$html.= $div->getHtml();

		if ($this->clonable) {
			$div = CDOMElement::create('div','class:clonable');
			$html.= $div->getHtml();
		}

		$div = CDOMElement::create('div','class:answer_content');
		$div->addChild(new CText($html));

        return $div->getHtml();
    }

	/**
	 * Fills in control with specific data
	 *
	 * @param array $data
	 */
	public function withData($data) {
		$this->_controlData = array (
			'other_answer' => null,
			'answer' => null,
			'value' => null,
			'case_sensitive' => null,
			'record' => null,
		);

		if (is_array($data) && !empty($data)) {
			foreach($data as $k=>$v) {
				if (array_key_exists($k,$this->_controlData)) {
					$this->_controlData[$k] = $v;
				}
			}
		}
	}
}