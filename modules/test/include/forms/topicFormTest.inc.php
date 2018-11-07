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

class TopicFormTest extends FormTest {

	protected $id_test;
	protected $id_nodo_parent;

	public function __construct($id_test,$data=array(),$id_nodo_parent=null) {
		$this->id_test = $id_test;
		$this->id_nodo_parent = $id_nodo_parent;
		parent::__construct($data);
	}

	protected function content() {
		$dh = $GLOBALS['dh'];

		$this->setName('topicForm');

		$random = 'random';
		$random_number = 'random_number';

		$js = 'var random_field = "'.$random.'";
			var field = "'.$random_number.'";
			var regexp = /^[0-9]+$/;
			var module_http = "'.MODULES_TEST_HTTP.'";
			document.write(\'<script type="text/javascript" src="'.MODULES_TEST_HTTP.'/js/topicForm.js"><\/script>\');';
		$this->setCustomJavascript($js);

		//parent
		$nodes = $dh->test_getNodesByParent($this->id_test,$this->id_test);
		$options = array(
			$this->id_test => $nodes[$this->id_test]['titolo'].' ('.$nodes[$this->id_test]['nome'].')',
		);
		foreach($nodes as $id_nodo=>$v) {
			if ($id_nodo != $this->id_test) {
				$options[$id_nodo] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$v['titolo'].' ('.$v['nome'].')';
			}
		}
		if (isset($this->data['id_nodo_parent'])) {
			$defaultValue = $this->data['id_nodo_parent'];
		}
		else {
			if (is_null($this->id_nodo_parent)) {
				$defaultValue = $this->id_test;
			}
			else {
				$defaultValue = $this->id_nodo_parent;
			}
		}
        $this->addSelect('id_nodo_parent',translateFN('Aggancia a').':',$options,$defaultValue);

		//nome
		if (!empty($this->data['nome'])) {
			$defaultValue = $this->data['nome'];
		}
		else {
			if (is_null($this->id_nodo_parent)) {
				$defaultValue = translateFN('sessione').' ';
			}
			else {
				$defaultValue = translateFN('argomento').' ';
			}
			$res = $dh->test_getNodesByRadix($this->id_test);
			if ($dh->isError($res) || empty($res)) {
				$defaultValue.= 1;
			}
			else {
				foreach($res as $k=>$v) {
					if ($v['tipo']{0} != ADA_GROUP_TOPIC) {
						unset($res[$k]);
					}
				}
				$defaultValue.= count($res)+1;
			}
		}
        $this->addTextInput('nome', translateFN('Nome (per uso interno)').':')
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
             ->withData($defaultValue);

		//titolo
        $this->addTextInput('titolo', translateFN('Titolo').':')
             ->withData($this->data['titolo']);

		//descrizione
        $this->addTextarea('testo', translateFN('Descrizione').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['testo']));

		//durata
		if (false && !is_null($this->id_nodo_parent)) {
			$this->addHidden('durata')->withData(0);
		}
		else {
			if (isset($this->data['durata'])) {
				$defaultValue = $this->data['durata'];
			}
			else {
				$defaultValue = 0;
			}
			$this->addTextInput('durata', translateFN('Tempo limite (in minuti, 0 = senza limite)').': ')
				 ->setRequired()
				 ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR)
				 ->withData($defaultValue);
		}

		//random questions
		$radios = array(
			ADA_PICK_QUESTIONS_NORMAL => translateFN('No'),
			ADA_PICK_QUESTIONS_RANDOM => translateFN('Si'),
		);
		if (isset($this->data[$random])) {
			$defaultValue = $this->data[$random];
		}
		else {
			$defaultValue = ADA_PICK_QUESTIONS_NORMAL;
		}

		if (is_null($this->id_nodo_parent)) {
			$randomTranslation = translateFN('Scelta casuale degli argomenti');
		}
		else {
			$randomTranslation = translateFN('Scelta casuale delle domande');
		}
		$this->addRadios($random,$randomTranslation.':',$radios,$defaultValue);

		//how many random questions
		if (is_null($this->id_nodo_parent)) {
			$label = translateFN('Numero di argomenti da mostrare');
		}
		else {
			$label = translateFN('Numero di domande da mostrare');
		}
        $num = $this->addTextInput($random_number, $label.':')
					->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR)
					->withData(isset($this->data[$random_number]) ? $this->data[$random_number] : 0);
		if (isset($this->data[$random]) && $this->data[$random] == ADA_PICK_QUESTIONS_RANDOM) {
			$num->setRequired();
		}
    }
}
