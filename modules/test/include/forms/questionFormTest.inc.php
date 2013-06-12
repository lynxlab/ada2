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

class QuestionFormTest extends FormTest {
	
	protected $id_test;
	protected $id_nodo_parent;
	protected $isCloze;
	protected $savedExerciseType;

	public function __construct($id_test,$data=array(),$id_nodo_parent=null,$isCloze = false, $savedExerciseType = false) {
		$this->id_test = $id_test;
		$this->id_nodo_parent = $id_nodo_parent;
		$this->isCloze = ($isCloze)?'true':'false';
		$this->savedExerciseType = $savedExerciseType;
		parent::__construct($data);
	}

	protected function common_elements() {
		$dh = $GLOBALS['dh'];

		$this->setName('questionForm');

		$this->addHidden('step')->withData(2);

		$injectTemplate = 'injectTemplate';
		$commento = 'commento';
		$didascalia = 'didascalia';
		$js = '
			isCloze = '.$this->isCloze.';
			var injectTemplate_field = "'.$injectTemplate.'";
			var commento_field = "'.$commento.'";
			var field = "'.$didascalia.'";
			var regexp = /^(?:[\d]+|[\w]+).*$/;
			var module_http = "'.MODULES_TEST_HTTP.'";
			document.write(\'<script type="text/javascript" src="'.MODULES_TEST_HTTP.'/js/questionFormStep2.js"><\/script>\');';
		$this->setCustomJavascript($js);

		//forgetExerciseType
		if ($this->savedExerciseType) {
			require_once(MODULES_TEST_PATH.'/include/forms/controls/forgetExerciseTypeControlTest.inc.php');
			$this->addControl(new ForgetExerciseTypeControlTest());
		}

		//parent node
		$this->parentSelect();

		//nome
		if (!empty($this->data['nome'])) {
			$defaultValue = $this->data['nome'];
		}
		else {
			if (!is_null($this->id_nodo_parent)) {
				$defaultValue = translateFN('domanda').' ';
				$res = $dh->test_getNodesByRadix($this->id_test);
				if ($dh->isError($res) || empty($res)) {
					$defaultValue.= 1;
				}
				else {
					foreach($res as $k=>$v) {
						if ($v['tipo']{0} != ADA_GROUP_QUESTION) {
							unset($res[$k]);
						}
					}
					$defaultValue.= count($res)+1;
				}
			}
			else {
				$defaultValue = '';
			}
		}
        $this->addTextInput('nome', translateFN('Nome (per uso interno)').':')
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
             ->withData($defaultValue);

		//titolo
        $this->addTextInput('titolo', translateFN('Titolo').':')
             ->withData($this->data['titolo']);

		//consegna
        $this->addTextarea('consegna', translateFN('Consegna').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['consegna']));

		//importazione template
		$this->addTemplateEditor($injectTemplate, translateFN('Importare un template (opzionale):'));

		//descrizione
        $this->addTextarea('testo', translateFN('Descrizione').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['testo']));

		//commento a fine domanda
		$options = array(
			ADA_NO_TEST_COMMENT => translateFN('No'),
			ADA_YES_TEST_COMMENT => translateFN('Si'),
		);

		if (isset($this->data[$commento])) {
			$defaultValue = $this->data[$commento];
		}
		else {
			$defaultValue = ADA_NO_TEST_COMMENT;
		}
        $this->addRadios($commento,translateFN('Permetti commento a fine domanda').':',$options,$defaultValue);

		//didascalia commento fine domanda
        $didascalia = $this->addTextInput($didascalia, translateFN('Testo commento fine domanda').':')
						   ->setValidator(null)
						   ->withData($this->data[$didascalia]);
		if (isset($this->data[$commento]) && $this->data[$commento] == ADA_YES_TEST_COMMENT) {
			$didascalia->setRequired();
		}
	}

    protected final function addTemplateEditor($id, $label) {
		require_once(MODULES_TEST_PATH.'/include/forms/controls/templateEditorControlTest.inc.php');
        return $this->addControl(new TemplateEditorControlTest($id,$label));
    }

	protected function content() {
		$this->setName('questionForm');

		$this->parentSelect();

		$this->addHidden('step')->withData(1);

		$tipologia = 'tipologia';
		$cloze = 'cloze';
		$cloze_sinonimi = 'cloze_sinonimi';

		$js = '
			var tipologia_field = "'.$tipologia.'";
			var ADA_CLOZE_TEST_TYPE = '.ADA_CLOZE_TEST_TYPE.';
			var ADA_OPEN_AUTOMATIC_TEST_TYPE = '.ADA_OPEN_AUTOMATIC_TEST_TYPE.';
			var ADA_NORMAL_TEST_SIMPLICITY = '.ADA_NORMAL_TEST_SIMPLICITY.';
			var ADA_MEDIUM_TEST_SIMPLICITY = '.ADA_MEDIUM_TEST_SIMPLICITY.';
			var cloze_field = "'.$cloze.'";
			document.write(\'<script type="text/javascript" src="'.MODULES_TEST_HTTP.'/js/questionForm.js"><\/script>\');';
		$this->setCustomJavascript($js);

		//tipologia domanda
		$options = array(
			ADA_NO_QUESTION_TEST_TYPE		=> translateFN('Scegliere una tipologia di domanda'),
			ADA_STANDARD_TEST_TYPE			=> translateFN('Scelta singola'),
			ADA_MULTIPLE_CHECK_TEST_TYPE	=> translateFN('Scelta multipla'),
			ADA_CLOZE_TEST_TYPE				=> translateFN('Cloze'),
			ADA_LIKERT_TEST_TYPE			=> translateFN('Likert'),
			ADA_OPEN_MANUAL_TEST_TYPE		=> translateFN('Aperta con correzione manuale'),
			ADA_OPEN_AUTOMATIC_TEST_TYPE	=> translateFN('Aperta con correzione automatica'),
			ADA_OPEN_UPLOAD_TEST_TYPE		=> translateFN('Aperta con upload file'),
		);

		if (isset($this->data[$tipologia])) {
			$defaultValue = $this->data[$tipologia];
		}
		else {
			$defaultValue = ADA_NO_QUESTION_TEST_TYPE;
		}
        $this->addSelect($tipologia, translateFN('Tipologia domanda').':',$options,$defaultValue)
			 ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR)
			 ->setAttribute('onchange', 'hide_tipologia_cloze();');

		//tipologia domanda cloze
		$options = array(
			ADA_NORMAL_TEST_SIMPLICITY		=> translateFN('Riempimento di spazi vuoti'),
			ADA_MEDIUM_TEST_SIMPLICITY		=> translateFN('Riempimento di spazi vuoti con limitazione dei caratteri'),
			ADA_SELECT_TEST_SIMPLICITY		=> translateFN('Riempimento di spazi con tendina'),
			ADA_DRAGDROP_TEST_SIMPLICITY	=> translateFN('Riempimento di spazi con Drag\'n\'Drop'),
			ADA_ERASE_TEST_SIMPLICITY		=> translateFN('Eliminazione / Evidenziazione di parole nel testo'),
			ADA_SLOT_TEST_SIMPLICITY		=> translateFN('Incastro di parole nel testo'),
			ADA_MULTIPLE_TEST_SIMPLICITY	=> translateFN('Riempimento multiplo di spazi con Drag\'n\'Drop'),
		);

		if (isset($this->data[$cloze])) {
			$defaultValue = $this->data[$cloze];
		}
		else {
			$defaultValue = ADA_NORMAL_TEST_SIMPLICITY;
		}
        $this->addSelect($cloze,translateFN('Tipologia CLOZE').':',$options,$defaultValue)
			 ->setHidden();
    }

	protected function parentSelect() {
		$dh = $GLOBALS['dh'];

		//parent
		$tmp_nodes = $dh->test_getTopicNodesByRadix($this->id_test);
		$nodes = array();
		while(!empty($tmp_nodes)) {
			foreach($tmp_nodes as $id_nodo=>$v) {
				if (isset($nodes[$v['id_nodo_parent']])) {
					$nodes[$v['id_nodo_parent']]['children'][$id_nodo] = $v;
				}
				else {
					$nodes[$id_nodo] = $v;
				}
				unset($tmp_nodes[$id_nodo]);
			}
		}

		$options = array( 0 => translateFN('Scegli un argomento'));
		foreach($nodes as $id_nodo => $v) {
			$options[$id_nodo] = $v['titolo'].' ('.$v['nome'].')';
			if (!empty($v['children'])) {
				foreach($v['children'] as $k=>$l) {
					$options[$k] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$l['titolo'].' ('.$l['nome'].')';
				}
			}
		}

		if (isset($this->data['id_nodo_parent'])) {
			$defaultValue = $this->data['id_nodo_parent'];
		}
		else {
			if (!is_null($this->id_nodo_parent)) {
				$defaultValue = $this->id_nodo_parent;
			}
			else {
				$defaultValue = 0;
			}
		}
        $this->addSelect('id_nodo_parent',translateFN('Aggancia a').':',$options,$defaultValue)
			 ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
	}
}