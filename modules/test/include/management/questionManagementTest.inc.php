<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionManagementTest extends ManagementTest {
	protected $test = null;

	protected $_new_question;

	/**
	 * Management constructor. the $action parameter must be 'add', 'mod' or 'del'
	 *
	 * @global db $dh
	 *
	 * @param string $action
	 * @param int $id node id
	 * @param int $id_test test node id
	 */
	public function __construct($action,$id=null,$id_test=null) {
		$dh = $GLOBALS['dh'];

		parent::__construct($action,$id);

		$this->what = translateFN('domanda');

		if (is_null($id_test)) {
			$q = $dh->test_getNode($id);
			if (AMATestDataHandler::isError($q) || empty($q)) {
				return;
			}
			$id_test = $q['id_nodo_radice'];
		}
		$test = $dh->test_getNode($id_test);
		if (AMATestDataHandler::isError($test) || empty($test)) {
			return;
		}
		$this->test = $test;			
	}

	/**
	 * function that set "tipo" attribute from default values, post or from database record
	 */
	protected function setTipo() {
		$this->tipo = array(
			0=>ADA_GROUP_QUESTION,
			1=>ADA_NO_QUESTION_TEST_TYPE,
			2=>ADA_NO_TEST_COMMENT,
			3=>0, //applicable only for cloze type
			4=>0, //applicable only for certain cloze subtype
			5=>0, //applicable only for certain cloze subtype
		);

		if ($_POST['step'] == 2 && isset($_SESSION['new_question'][$_GET['id_nodo_parent']])) {
			$new_q = &$_SESSION['new_question'][$_GET['id_nodo_parent']];
			$this->tipo[1] = intval($new_q['tipologia']);
			$this->tipo[2] = intval($_POST['commento']);
			switch($new_q['tipologia']) {
				case ADA_STANDARD_TEST_TYPE:
				case ADA_MULTIPLE_CHECK_TEST_TYPE:
					$this->tipo[3] = intval($_POST['variant']);
				break;
				case ADA_CLOZE_TEST_TYPE:
					$this->tipo[3] = intval($new_q['cloze']);

					switch($this->tipo[3]) {
						case ADA_SELECT_TEST_SIMPLICITY:
							$this->tipo[4] = intval($_POST['cloze_sinonimi']);
						break;
						case ADA_DRAGDROP_TEST_SIMPLICITY:
						case ADA_SLOT_TEST_SIMPLICITY:
							$this->tipo[4] = intval($_POST['box_position']);
						break;
						case ADA_ERASE_TEST_SIMPLICITY:
							$this->tipo[4] = intval($_POST['variant']);
							$this->tipo[5] = intval($_POST['cloze_apostrofo']);
						break;
						case ADA_MULTIPLE_TEST_SIMPLICITY:
							$this->tipo[4] = intval($_POST['box_position']);
							$this->tipo[5] = intval($_POST['cloze_apostrofo']);
						break;
					}
				break;
			}
		}
		else {
			$this->readTipoFromRecord();
			if (isset($_POST['commento'])) {
				$this->tipo[2] = intval($_POST['commento']);
			}
			switch($this->tipo[1]) {
				case ADA_STANDARD_TEST_TYPE:
				case ADA_MULTIPLE_CHECK_TEST_TYPE:
					$this->tipo[3] = intval($_POST['variant']);
				break;
				case ADA_CLOZE_TEST_TYPE:
					switch($this->tipo[3]) {
						case ADA_SELECT_TEST_SIMPLICITY:
							if (isset($_POST['cloze_sinonimi'])) {
								$this->tipo[4] = intval($_POST['cloze_sinonimi']);
							}
						break;
						case ADA_DRAGDROP_TEST_SIMPLICITY:
						case ADA_SLOT_TEST_SIMPLICITY:
							if (isset($_POST['box_position'])) {
								$this->tipo[4] = intval($_POST['box_position']);
							}
						break;
						case ADA_ERASE_TEST_SIMPLICITY:
							if (isset($_POST['variant'])) {
								$this->tipo[4] = intval($_POST['variant']);
							}
							if (isset($_POST['cloze_apostrofo'])) {
								$this->tipo[5] = intval($_POST['cloze_apostrofo']);
							}
						break;
						case ADA_MULTIPLE_TEST_SIMPLICITY:
							if (isset($_POST['box_position'])) {
								$this->tipo[4] = intval($_POST['box_position']);
							}
							if (isset($_POST['cloze_apostrofo'])) {
								$this->tipo[5] = intval($_POST['cloze_apostrofo']);
							}
						break;
					}
				break;
			}
		}
	}

	/**
	 * adds a record
	 */
	public function add() {
		$dh = $GLOBALS['dh'];

		$nodo = new Node($this->test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		require_once(MODULES_TEST_PATH.'/include/forms/questionFormTest.inc.php');
		$form = new QuestionFormTest($this->test['id_nodo'],$_POST,$_GET['id_nodo_parent']);

		$bypass = false;
		if (!$_POST && isset($_SESSION['new_question'][$_GET['id_nodo_parent']])) {
			if (isset($_GET['forgetExerciseType'])) {
				unset($_SESSION['new_question'][$_GET['id_nodo_parent']]);
			}
			else {
				$_POST['step'] = 1;
				$bypass = true;
			}
		}

		if ($_POST) {
			if ($_POST['step'] == 2) {
				$new_q = &$_SESSION['new_question'][$_GET['id_nodo_parent']];
				$form = $this->instantiateObject($this->test['id_nodo'],$_POST,$_POST['id_nodo_parent'],$new_q['tipologia'],$new_q['cloze']);
				if ($form->isValid()) {
					$siblings = $dh->test_getNodesByParent($_POST['id_nodo_parent']);
					$ordine = count($siblings)+1;

					//crea nuova domanda con i dati del form
					$this->setTipo();
					$data = array(
						'id_corso'=>$this->test['id_corso'],
						'id_utente'=>$_SESSION['sess_id_user'],
						'id_istanza'=>$this->test['id_istanza'],
						'nome'=>$_POST['nome'],
						'titolo'=>$_POST['titolo'],
						'consegna'=>Node::prepareInternalLinkMediaForDatabase($_POST['consegna']),
						'testo'=>Node::prepareInternalLinkMediaForDatabase($_POST['testo']),
						'tipo'=>$this->getTipo(),
						'id_nodo_parent'=>$_POST['id_nodo_parent'],
						'id_nodo_radice'=>$this->test['id_nodo'],
						'didascalia'=>$_POST['didascalia'],
						'ordine'=>$ordine,
						'titolo_dragdrop'=>$_POST['titolo_dragdrop'],
						'correttezza'=>$_POST['correttezza'],
					);
					$res = $this->saveQuestion($data);
					unset($data);

					if (!AMATestDataHandler::isError($res)) {						
						unset($new_q);
						$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
						redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$this->test['id_nodo'].$get_topic.'#liQuestion'.$res);
					}
					else {
						$html = sprintf(translateFN('Errore durante la creazione della %s'),$this->what);
					}
				}
				else {
					$html = $form->getHtml();
				}
			}
			else if ($_POST['step'] == 1 && $form->isValid()) {
				if (!$bypass) {
					$_SESSION['new_question'][$_POST['id_nodo_parent']] = $_POST;
				}
				$form = $this->instantiateObject($this->test['id_nodo'],$_POST,$_GET['id_nodo_parent'],$_POST['tipologia'],$_POST['cloze']);
				$html = $form->getHtml();
			}
			else {
				$html = $form->getHtml();
			}
		}
		else {
			$html = $form->getHtml();
		}

		return array(
			'html' => $html,
			'path' => $path,
		);
	}

	/**
	 * edits a record
	 */
	public function mod() {
		$question = &$this->_r;

		$nodo = new Node($this->test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if ($_POST) {
			$data = $_POST;
		}
		else {
			$data = array(
				'nome'=>$question['nome'],
				'titolo'=>$question['titolo'],
				'consegna'=>$question['consegna'],
				'testo'=>$question['testo'],
				'commento'=>$question['tipo']{2},
				'didascalia'=>$question['didascalia'],
				'titolo_dragdrop'=>$question['titolo_dragdrop'],
				'id_nodo_parent'=>$question['id_nodo_parent'],
				'correttezza'=>$question['correttezza'],
			);

			switch($question['tipo']{1}) {
				case ADA_STANDARD_TEST_TYPE:
				case ADA_MULTIPLE_CHECK_TEST_TYPE:
					$data['variant'] = $question['tipo']{3};
				break;
				case ADA_CLOZE_TEST_TYPE:
					switch($question['tipo']{3}) {
						case ADA_SELECT_TEST_SIMPLICITY:
							$data['cloze_sinonimi'] = $question['tipo']{4};
						break;
						case ADA_DRAGDROP_TEST_SIMPLICITY:
						case ADA_SLOT_TEST_SIMPLICITY:
							$data['box_position'] = $question['tipo']{4};
						break;
						case ADA_ERASE_TEST_SIMPLICITY:
							$data['variant'] = $question['tipo']{4};
							$data['cloze_apostrofo'] = $question['tipo']{5};
						break;
						case ADA_MULTIPLE_TEST_SIMPLICITY:
							$data['box_position'] = $question['tipo']{4};
							$data['cloze_apostrofo'] = $question['tipo']{5};
						break;
					}
				break;
			}
		}

		unset($_SESSION['new_question'][$data['id_nodo_parent']]);

		require_once(MODULES_TEST_PATH.'/include/forms/questionFormTest.inc.php');
		$form = $this->instantiateObject($question['id_nodo_radice'], $data, $question['id_nodo_parent'], $question['tipo']{1}, $question['tipo']{3});

		if ($_POST) {
			if ($form->isValid()) {
				//crea nuovo test con i dati del form
				$this->setTipo();
				$data = array(
					'nome'=>$_POST['nome'],
					'titolo'=>$_POST['titolo'],
					'consegna'=>Node::prepareInternalLinkMediaForDatabase($_POST['consegna']),
					'testo'=>Node::prepareInternalLinkMediaForDatabase($_POST['testo']),
					'id_nodo_parent'=>$_POST['id_nodo_parent'],
					'tipo'=>$this->getTipo(),
					'didascalia'=>$_POST['didascalia'],
					'titolo_dragdrop'=>$_POST['titolo_dragdrop'],
					'correttezza'=>$_POST['correttezza'],
				);

				if ($this->saveQuestion($data,$question['id_nodo'])) {
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$question['id_nodo_radice'].$get_topic.'#liQuestion'.$question['id_nodo']);
				}
				else {
					$html = sprintf(translateFN('Errore durante la modifica del %s'),$this->what);
				}
			}
			else {
				$html = $form->getHtml();
			}
		}
		else {
			$html = $form->getHtml();
		}

		return array(
			'html' => $html,
			'path' => $path,
		);
	}

	/**
	 * deletes a record
	 */
	public function del() {
		$dh = $GLOBALS['dh'];

		$question = &$this->_r;

		$nodo = new Node($this->test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if (isset($_POST['delete'])) {
			if ($_POST['delete'] == 1) {
				if (AMATestDataHandler::isError($dh->test_deleteNodeTest($this->id))) {
					$html = sprintf(translateFN('Errore durante la cancellazione della %s'),$this->what);
				}
				else {
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$question['id_nodo_radice'].$get_topic);
				}
			}
			else {
				$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
				redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$question['id_nodo_radice'].$get_topic);
			}
		}
		else {
			require_once(MODULES_TEST_PATH.'/include/forms/deleteFormTest.inc.php');
			$titolo = $question['titolo'];
			if (empty($titolo)) {
				$titolo = $question['nome'];
			}
			$titolo = $this->what.' "'.$titolo.'"';
			$message = sprintf(translateFN('Stai per cancellare la %s e tutti i dati contenuti. Continuare?'),$titolo);
			$form = new DeleteFormTest($message);
			$html = $form->getHtml();
		}

		return array(
			'html' => $html,
			'path' => $path,
		);
	}

	/**
	 * returns status message based on $action attribute
	 */
	public function status() {
		switch ($this->action) {
			case 'add':
				return sprintf(translateFN('Aggiunta di una %s'),$this->what);
			break;
			case 'mod':
				return sprintf(translateFN('Modifica di una %s'),$this->what);
			break;
			case 'del':
				return sprintf(translateFN('Cancellazione di una %s'),$this->what);
			break;
		}
	}

	/**
	 * function that instantiate the correct question form
	 *
	 * @param int $id_test node id
	 * @param array $data array that contains data from database's record
	 * @param int $id_nodo_parent parent node id
	 * @param string $type node type string
	 * @param int $cloze_type cloze type id
	 * 
	 * @return QuestionStandardFormTest|QuestionOpenFormTest|QuestionSelectClozeFormTest|QuestionDragDropClozeFormTest
	 */
	protected function instantiateObject($id_test, $data, $id_nodo_parent, $type, $cloze_type = null) {
		$isCloze = false;
		if ($type == ADA_CLOZE_TEST_TYPE) {
			$isCloze = true;
		}

		$savedExerciseType = false;
		if (!empty($_SESSION['new_question'][$id_nodo_parent])) {
			$savedExerciseType = true;
		}

		if ($type == ADA_CLOZE_TEST_TYPE && ($cloze_type == ADA_DRAGDROP_TEST_SIMPLICITY || $cloze_type == ADA_SLOT_TEST_SIMPLICITY)) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionDragDropClozeFormTest.inc.php');
			$form = new QuestionDragDropClozeFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else if ($type == ADA_CLOZE_TEST_TYPE && $cloze_type == ADA_SELECT_TEST_SIMPLICITY) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionSelectClozeFormTest.inc.php');
			$form = new QuestionSelectClozeFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else if ($type == ADA_CLOZE_TEST_TYPE && $cloze_type == ADA_ERASE_TEST_SIMPLICITY) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionEraseClozeFormTest.inc.php');
			$form = new QuestionEraseClozeFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else if ($type == ADA_CLOZE_TEST_TYPE && $cloze_type == ADA_MULTIPLE_TEST_SIMPLICITY) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionMultipleClozeFormTest.inc.php');
			$form = new QuestionMultipleClozeFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else if ($type == ADA_OPEN_MANUAL_TEST_TYPE || $type == ADA_OPEN_UPLOAD_TEST_TYPE) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionOpenFormTest.inc.php');
			$form = new QuestionOpenFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else if ($type == ADA_STANDARD_TEST_TYPE || $type == ADA_MULTIPLE_CHECK_TEST_TYPE) {
			require_once(MODULES_TEST_PATH.'/include/forms/questionStandardFormTest.inc.php');
			$form = new QuestionStandardFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		else {
			require_once(MODULES_TEST_PATH.'/include/forms/questionEmptyFormTest.inc.php');
			$form = new QuestionEmptyFormTest($id_test, $data, $id_nodo_parent, $isCloze, $savedExerciseType);
		}
		return $form;
	}

	/**
	 * function used to save question
	 *
	 * @global db $dh
	 *
	 * @param array $data question data to be saved in database
	 * @param int $question_id question node id
	 * 
	 * @return node id in case of successful insert or boolean
	 */
	protected function saveQuestion($data,$question_id=null) {
		$dh = $GLOBALS['dh'];

		$data['testo'] =  preg_replace(array('#<p[^>]*>#','#</p>#'), array('','<br />'), $data['testo']);

		if (is_null($question_id)) {
			$question_res = $dh->test_addNode($data);
			$question_id = $question_res;
		}
		else {
			$question_res = $dh->test_updateNode($question_id,$data);
		}

		if ($data['tipo']{1} == ADA_CLOZE_TEST_TYPE && $question_res) {
			require_once(MODULES_TEST_PATH.'/include/question.class.inc.php');
			require_once(MODULES_TEST_PATH.'/include/questionCloze.class.inc.php');
			if ($data['tipo']{3} == ADA_ERASE_TEST_SIMPLICITY) {
				require_once(MODULES_TEST_PATH.'/include/questionEraseCloze.class.inc.php');
				QuestionEraseClozeTest::createEraseClozeAnswers($question_id, $data, $this->test);
			}
			else if ($data['tipo']{3} == ADA_SLOT_TEST_SIMPLICITY) {
				require_once(MODULES_TEST_PATH.'/include/questionSlotCloze.class.inc.php');
				QuestionSlotClozeTest::createSlotClozeAnswers($question_id, $data, $this->test);
			}
			else if ($data['tipo']{3} == ADA_MULTIPLE_TEST_SIMPLICITY) {
				require_once(MODULES_TEST_PATH.'/include/questionMultipleCloze.class.inc.php');
				QuestionMultipleClozeTest::createMultipleClozeAnswers($question_id, $data, $this->test);
			}
			else {
				QuestionClozeTest::createClozeAnswers($question_id, $data, $this->test);
			}
		}

		return $question_res;
	}
}
