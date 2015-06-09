<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class AnswersManagementTest {
	protected $test = null;
	protected $question = null;
	protected $what;

	/**
	 * Answers Management constructor
	 *
	 * @param int $id_question question node id
	 */
	public function __construct($id_question) {
		$dh = $GLOBALS['dh'];

		$question = $dh->test_getNode($id_question);
		if (AMATestDataHandler::isError($question) || empty($question)) {
			return;
		}
		$this->question = $question;

		$test = $dh->test_getNode($question['id_nodo_radice']);
		if (AMATestDataHandler::isError($test) || empty($test)) {
			return;
		}
		$this->test = $test;
	}

	/**
	 * function that executes answer logic
	 *
	 * @return array that contains 'html', 'status' and 'path' keys
	 */
	public function run() {
		if (is_null($this->question)) {
			return array(
				'status'=>$this->status(),
				'html'=>translateFN('domanda').' '.translateFN('non trovata'),
			);
		}
		if (is_null($this->test)) {
			return array(
				'status'=>$this->status(),
				'html'=>translateFN('test').' '.translateFN('non trovato'),
			);
		}

		//calculate node path
		$path = '';
		$nodo = new Node($this->test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		return array(
			'html'=>$this->action(),
			'status'=>$this->status(),
			'path'=>$path,
		);
	}

	/**
	 * Return status message
	 *
	 * @return string
	 */
	protected function status() {
		return sprintf(translateFN('Gestione %s'),translateFN('risposte'));
	}

	/**
	 * Function that executes answer logic (add / mod)
	 *
	 * @global db $dh
	 * 
	 * @return string
	 */
	protected function action() {
		$dh = $GLOBALS['dh'];
		
		$tmp = $dh->test_getNodesByParent($this->question['id_nodo']);
		$risposte = array();
		if (!empty($tmp)) {
			foreach($tmp as $k=>$v) {
				$risposte[] = array(
					'other_answer' => $v['tipo']{1},
					'answer' => $v['testo'],
					'value' => $v['correttezza'],
					'case_sensitive' => $v['tipo']{2},
					'record' => $v['id_nodo'],
					'ordine' => $v['ordine'],
					'titolo_dragdrop' => $v['titolo_dragdrop'] // giorgio, used for table num in drag'n'drop
				);
			}
		}		

		$case_sensitive = false;
		$open_cloze = array(ADA_NORMAL_TEST_SIMPLICITY,ADA_MEDIUM_TEST_SIMPLICITY);
		if ($this->question['tipo']{1} == ADA_OPEN_AUTOMATIC_TEST_TYPE ||
			($this->question['tipo']{1} == ADA_CLOZE_TEST_TYPE && in_array($this->question['tipo']{3},$open_cloze))) {
			$case_sensitive = true;
		}

		$open_answer = false;
		if (in_array($this->question['tipo']{1},array(ADA_STANDARD_TEST_TYPE,ADA_MULTIPLE_CHECK_TEST_TYPE))) {
			$open_answer = true;
		}

		$form = $this->instantiateObject($risposte, $this->question, $case_sensitive, $open_answer);

		if ($_POST) {

			//hack to capture ajax calls from ADA_CLOZE_TEST_TYPE -> ADA_MULTIPLE_TEST_SIMPLICITY
			if ($this->question['tipo']{3} == ADA_MULTIPLE_TEST_SIMPLICITY) {
				require_once(MODULES_TEST_PATH.'/include/nodeTest.class.inc.php');
				$questionObj = nodeTest::readNode($this->question['id_nodo']);
				$updateVal = isset($_POST[QuestionMultipleClozeTest::postVariable]) ? $_POST[QuestionMultipleClozeTest::postVariable] : null;
				$questionObj->updateAnswerTable($updateVal);
			}

			if ($form->isValid()) {

				//crea nuove risposte con i dati del form
				$post = array();
				foreach($_POST['answer'] as $k=>$v) {
					$post[] = array(
						'answer'=> $_POST['answer'][$k],
						'value'=> $_POST['value'][$k],
						'case_sensitive'=> (isset($_POST['case_sensitive'][$k]) && $_POST['case_sensitive'][$k]==1)?true:false,
						'other_answer'=> (isset($_POST['other_answer'][$k]) && $_POST['other_answer'][$k]==1)?true:false,
						'record'=> $_POST['record'][$k],
						'ordine'=> isset($_POST['ordine'][$k]) ? $_POST['ordine'][$k] : null,
						'titolo_dragdrop' => ( isset( $_POST['titolo_dragdrop'][$k]) && trim($_POST['titolo_dragdrop'][$k])!=='' 
													? trim($_POST['titolo_dragdrop'][$k]) : null ),
					);
				}

				$result = true;
				$tipo = ADA_LEAF_ANSWER.ADA_NO_OPEN_TEST_ANSWER.ADA_CASE_SENSITIVE_TEST.'000';
				foreach($post as $k=>$v) {
					$t = $tipo;					
					$t{1} = ($v['other_answer'])?ADA_OPEN_TEST_ANSWER:ADA_NO_OPEN_TEST_ANSWER;
					$t{2} = ($v['case_sensitive'])?ADA_CASE_INSENSITIVE_TEST:ADA_CASE_SENSITIVE_TEST;

					$data = array(
						'id_corso'=>$this->test['id_corso'],
						'id_utente'=>$_SESSION['sess_id_user'],
						'id_istanza'=>$this->test['id_istanza'],
						'nome'=>$v['answer'],
						'testo'=>Node::prepareInternalLinkMediaForDatabase($v['answer']),
						'correttezza'=>$v['value'],
						'tipo'=>$t,
						'id_nodo_parent'=>$this->question['id_nodo'],
						'id_nodo_radice'=>$this->test['id_nodo'],
						'ordine'=>($v['ordine'])?$v['ordine']:$k+1,
 						'titolo_dragdrop'=>$v['titolo_dragdrop'],
					);

					if (intval($v['record']) > 0) {
						$res = $dh->test_updateNode(intval($v['record']),$data);
					}
					else {
						$res = $dh->test_addNode($data);
					}
					if (AMATestDataHandler::isError($res)) {
						$result = false;
						$html = translateFN('Errore durante la creazione delle risposte');
						break;
					}
					unset($data);
				}

				if ($result) {
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					if (isset($_POST['return']) && $_POST['return'] == 'here') {						
						redirect(MODULES_TEST_HTTP.'/edit_answers.php?id_question='.$this->question['id_nodo'].$get_topic . '&saved=1');
					}
					else {
						redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$this->test['id_nodo'].$get_topic.'#liQuestion'.$this->question['id_nodo']);
					}					
				}
			}
		}
		else {
			$html = $form->getHtml();
			$div = CDOMElement::create('div','id:insertImage,class:hide');
			$div->setAttribute('title',translateFN('Inserisci Immagine'));
			$div->setAttribute('style','text-align:right;line-height:35px;');
			
			$labelUrl = CDOMElement::create('label','for:inputUrl,style:width:20%;');
			$labelUrl->addChild(new CText(translateFN('Url').':'));
			$inputUrl = CDOMElement::create('text','id:inputUrl,style:width:47%;');
			$div->addChild($labelUrl);
			$div->addChild($inputUrl);
			
			$browseButton = CDOMElement::create('span','id:browseserver,style:font-size:0.85em;width:30%;margin-bottom:6px;');
			$browseButton->addChild(new CText('Sfoglia'));
			$div->addChild($browseButton);
			
			//$div->addChild(new CText('<br />'));

			$labelTitle = CDOMElement::create('label','for:inputTitle, style:width:20%;');
			$labelTitle->addChild(new CText(translateFN('Titolo').':'));
			$inputTitle = CDOMElement::create('text','id:inputTitle,style:width:80%;');
			$div->addChild($labelTitle);
			$div->addChild($inputTitle);
			$div->addChild(new CText('<br />'));

			$labelRadio = CDOMElement::create('label');
			$labelRadio->addChild(new CText(translateFN('Permetti zoom').':'));
			$labelYes = CDOMElement::create('label','for:radioPopupYes');
			$labelYes->addChild(new CText(translateFN('Si')));
			$labelNo = CDOMElement::create('label','for:radioPopupNo');
			$labelNo->addChild(new CText(translateFN('No')));
			$radioYes = CDOMElement::create('radio','id:radioPopupYes,name:radioPopup');
			$radioYes->setAttribute('checked','');
			$radioNo = CDOMElement::create('radio','id:radioPopupNo,name:radioPopup');

			$div->addChild($labelRadio);
			$div->addChild($radioYes);
			$div->addChild($labelYes);
			$div->addChild($radioNo);
			$div->addChild($labelNo);
			$div->addChild(new CText('<br />'));



			$labelWidth = CDOMElement::create('label','for:inputWidth');
			$labelWidth->addChild(new CText(translateFN('Larghezza').':'));
			$inputWidth = CDOMElement::create('text','id:inputWidth,size:4,value:75');
			$labelHeight = CDOMElement::create('label','for:inputHeight');
			$labelHeight->addChild(new CText(translateFN('Altezza').':'));
			$inputHeight = CDOMElement::create('text','id:inputHeight,size:4,value:75');

			$div->addChild($labelWidth);
			$div->addChild($inputWidth);
			$div->addChild($labelHeight);
			$div->addChild($inputHeight);

			$html.= $div->getHtml();
		}

		return $html;
	}

	/**
	 * function that instantiate the correct answers form
	 *
	 * @param int $risposte node id
	 * @param array $question array that contains data from database's record
	 * @param int $case_sensitive parent node id
	 * @param string $open_answer node type string
	 *
	 * @return AnswersStandardFormTest|AnswersClozeFormTest|CBase
	 */
	protected function instantiateObject($risposte, $question, $case_sensitive, $open_answer) {
		switch($question['tipo']{1}) {
			case ADA_MULTIPLE_CHECK_TEST_TYPE:
			case ADA_STANDARD_TEST_TYPE:
			case ADA_LIKERT_TEST_TYPE:
			case ADA_OPEN_AUTOMATIC_TEST_TYPE:
				require_once(MODULES_TEST_PATH.'/include/forms/answersStandardFormTest.inc.php');
				$form = new AnswersStandardFormTest($risposte,$question,$case_sensitive,$open_answer);
			break;
		
			case ADA_CLOZE_TEST_TYPE:
				require_once(MODULES_TEST_PATH.'/include/forms/answersClozeFormTest.inc.php');

				$modifiable = true;
				$notModifiableSubType = array(ADA_ERASE_TEST_SIMPLICITY, ADA_MULTIPLE_TEST_SIMPLICITY);
				if (in_array($question['tipo']{3},$notModifiableSubType)) {
					$modifiable = false;
				}

				if ($question['tipo']{3} == ADA_MULTIPLE_TEST_SIMPLICITY) {
					require_once(MODULES_TEST_PATH.'/include/forms/answersMultipleClozeFormTest.inc.php');
					$form = new AnswersMultipleClozeFormTest($risposte,$question,$case_sensitive,$modifiable);
				}
				else {
					$form = new AnswersClozeFormTest($risposte,$question,$case_sensitive,$modifiable);
				}
			break;

			default:
			case ADA_NO_QUESTION_TEST_TYPE:
			case ADA_OPEN_AUTOMATIC_TEST_TYPE:
			case ADA_OPEN_UPLOAD_TEST_TYPE:
				$form = CDOMElement::create('div');
				$form->addChild(new CText(translateFN('Non è possibile gestire le risposte per questo tipo di domanda.')));
			break;
		}
		return $form;
	}
}