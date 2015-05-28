<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class RootManagementTest extends ManagementTest {

	/**
	 * function that set "tipo" attribute from default values, post or from database record
	 */
	protected function setTipo() {
		$this->tipo = array(
			0=>$this->mode,
			1=>ADA_NO_TEST_RETURN,
			2=>ADA_RATING_TEST_INTERACTION,
			3=>ADA_ONEPAGE_TEST_MODE,
			4=>ADA_NO_TEST_BARRIER,
			5=>ADA_NO_TEST_REPETEABLE,
		);

		if ($_POST) {
			$this->tipo[1]=intval($_POST['return']);
			$this->tipo[2]=intval($_POST['feedback']);
			$this->tipo[3]=intval($_POST['suddivisione']);
			$this->tipo[4]=intval($_POST['barriera']);
			$this->tipo[5]=intval($_POST['ripetibile']);
		}
		else {
			$this->readTipoFromRecord();
		}
	}

	/**
	 * Adds a test / survey node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' keys
	 */
	public function add() {
		$dh = $GLOBALS['dh'];

		$nodo = new Node($_GET['id_node']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if ($_POST) {
			$data = $_POST;
		}

		require_once(MODULES_TEST_PATH.'/include/forms/rootFormTest.inc.php');
		if(get_class($this) == 'TestManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/testFormTest.inc.php');
			$form = new TestFormTest($data);
		}
		/**
		 * @author giorgio 24/ott/2013
		 * added else if for activity
		 */
		else if (get_class($this) == 'ActivityManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/testFormTest.inc.php');
			// giorgio, added parameter to say if it's an activity
			$form = new TestFormTest($data, true);
		}
		else if (get_class($this) == 'SurveyManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/surveyFormTest.inc.php');
			$form = new SurveyFormTest($data);
		}

		if ($_POST) {

			/**
			 * @author giorgio 30/gen/2015
			 *
			 * prepare final message, that is stored in consegna field
			 */
			$consegna = '';
			if (isset($_POST['consegna_success']) && strlen($_POST['consegna_success'])>0) {
				$consegna .= '<div class="final_success">'.
						Node::prepareInternalLinkMediaForDatabase($_POST['consegna_success'])
				.'</div>'; 
			}
			
			if (isset($_POST['consegna_error']) && strlen($_POST['consegna_error'])>0) {
				$consegna .= '<div class="final_error">'.
						Node::prepareInternalLinkMediaForDatabase($_POST['consegna_error'])
						.'</div>';
			}
			
// 			$_POST['consegna'] = Node::prepareInternalLinkMediaForDatabase($_POST['consegna']);
			$_POST['testo'] = Node::prepareInternalLinkMediaForDatabase($_POST['testo']);

			if ($form->isValid()) {
				//crea nuovo test con i dati del form
				$this->setTipo();
				$id_corso = explode('_',$nodo->id);
				$id_corso = $id_corso[0];
				$data = array(
					'id_corso'=>$id_corso,
					'id_utente'=>$_SESSION['sess_id_user'],
					'id_istanza'=>$nodo->instance,
					'nome'=>$_POST['nome'],
					'titolo'=>$_POST['titolo'],
					'consegna'=>$consegna,
					'testo'=>$_POST['testo'],
					'tipo'=>$this->getTipo(),
					'livello'=>$_POST['livello'],
					'durata'=>$_POST['min_level'],
					'correttezza'=>$_POST['correttezza'],
				);
				$id_test = $dh->test_addNode($data);
				unset($data);

				if (!AMATestDataHandler::isError($id_test)) {
					//crea nuovo nodo contenente link al test
					$last_node = explode('_', get_max_idFN($id_corso));
					$new_id = $last_node[1] + 1;
					$new_node_id = $id_corso.'_'.$new_id;
					$order = $dh->get_ordine_max_val($nodo->id);

					$url = MODULES_TEST_HTTP.'/index.php?id_test='.$id_test;
					$link = CDOMElement::create('a');
					$link->setAttribute('href',$url);
					$link->addChild(new CText($url));
					$link = $link->getHtml();

					$text = $_POST['testo'].'<br />'.$link;

					$nodo_test['id']				= $new_node_id;
					$nodo_test['id_node_author']	= $_SESSION['sess_id_user'];
					$nodo_test['title']				= $_POST['titolo'];
					$nodo_test['name']				= $_POST['nome'];
					$nodo_test['text']				= $text;
					$nodo_test['type']				= ADA_CUSTOM_EXERCISE_TEST;
					$nodo_test['parent_id']			= $nodo->id;
					$nodo_test['order']				= $order+1;
					$nodo_test['creation_date']		= today_dateFN();
					$nodo_test['pos_x0']			= 0;
					$nodo_test['pos_y0']			= 0;
					$nodo_test['pos_x1']			= 0;
					$nodo_test['pos_y1']			= 0;
					$id_nodo_riferimento = $dh->add_node($nodo_test);

					if (!AMATestDataHandler::isError($id_nodo_riferimento)) {
						$data = array(
							'id_nodo_riferimento'=>$id_nodo_riferimento,
						);

						$res = true;
						if ($this->mode == ADA_TYPE_SURVEY) { //aggiungo record nella tabella dei sondaggi-corsi per lo switcher
							$res = $dh->test_addCourseTest($id_corso,$id_test,$id_nodo_riferimento);
						}

						if ($res && $dh->test_updateNode($id_test,$data)) {
							redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$id_test);
						}
						else {
							$html = sprintf(translateFN('Errore durante la creazione del %s'),$this->what);
						}
					}
					else {
						$html = sprintf(translateFN('Errore durante la creazione del %s'),$this->what);
					}
				}
				else {
					$html = sprintf(translateFN('Errore durante la creazione del %s'),$this->what);
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
			'html'=>$html,
			'path'=>$path,
		);
	}

	/**
	 * modifies test / survey node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' keys
	 */
	public function mod() {
		$dh = $GLOBALS['dh'];

		$test = &$this->_r;

		$nodo = new Node($test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if ($_POST) {
			$data = $_POST;
			
			/**
			 * @author giorgio 30/gen/2015
			 * 
			 * prepare final message, that is stored in consegna field
			 */
			$data['consegna'] = '';
			if (isset($_POST['consegna_success']) && strlen($_POST['consegna_success'])>0) {
				$data['consegna'] .= '<div class="final_success">'.
						Node::prepareInternalLinkMediaForDatabase($_POST['consegna_success'])
						.'</div>';
			}
			if (isset($_POST['consegna_error']) && strlen($_POST['consegna_error'])>0) {
				$data['consegna'] .= '<div class="final_error">'.
						Node::prepareInternalLinkMediaForDatabase($_POST['consegna_error'])
						.'</div>';
			}
			
		}
		else {
			$data = array(
				'nome'=>$test['nome'],
				'consegna'=>$test['consegna'],
				'titolo'=>$test['titolo'],
				'consegna'=>Node::prepareInternalLinkMediaForDatabase($test['consegna']),
				'testo'=>Node::prepareInternalLinkMediaForDatabase($test['testo']),
				'livello'=>$test['livello'],
				'min_level'=>$test['durata'],
				'return'=>$test['tipo']{1},
				'feedback'=>$test['tipo']{2},
				'suddivisione'=>$test['tipo']{3},
				'barriera'=>$test['tipo']{4},
				'ripetibile'=>$test['tipo']{5},
				'correttezza'=>$test['correttezza'],
			);
		}

		require_once(MODULES_TEST_PATH.'/include/forms/rootFormTest.inc.php');
		if(get_class($this) == 'TestManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/testFormTest.inc.php');
			$form = new TestFormTest($data);
		}
		/**
		 * @author giorgio 24/ott/2013
		 * added else if for activity
		 */
		else if (get_class($this) == 'ActivityManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/testFormTest.inc.php');
			// giorgio, added parameter to say if it's an activity
			$form = new TestFormTest($data, true);
		}		
		else if (get_class($this) == 'SurveyManagementTest') {
			require_once(MODULES_TEST_PATH.'/include/forms/surveyFormTest.inc.php');
			$form = new SurveyFormTest($data);
		}

		if ($_POST) {
			if ($form->isValid()) {
				//crea nuovo test con i dati del form
				$this->setTipo();
				
				/**
				 * @author giorgio 30/gen/2015
				 *
				 * prepare final message, that is stored in consegna field
				 */
				$consegna = '';
				if (isset($_POST['consegna_success']) && strlen($_POST['consegna_success'])>0) {
					$consegna .= '<div class="final_success">'.
							Node::prepareInternalLinkMediaForDatabase($_POST['consegna_success'])
							.'</div>';
				}
					
				if (isset($_POST['consegna_error']) && strlen($_POST['consegna_error'])>0) {
					$consegna .= '<div class="final_error">'.
							Node::prepareInternalLinkMediaForDatabase($_POST['consegna_error'])
							.'</div>';
				}
				
// 				$_POST['consegna'] = Node::prepareInternalLinkMediaForDatabase($_POST['consegna']);
				$_POST['testo'] = Node::prepareInternalLinkMediaForDatabase($_POST['testo']);
				
				$data = array(
					'nome'=>$_POST['nome'],
					'titolo'=>$_POST['titolo'],
					'consegna'=>$consegna,
					'testo'=>$_POST['testo'],
					'tipo'=>$this->getTipo(),
					'livello'=>$_POST['livello'],
					'durata'=>$_POST['min_level'],
					'correttezza'=>$_POST['correttezza'],
				);
				if ($dh->test_updateNode($test['id_nodo'],$data)) {
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$test['id_nodo']);
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
			'html'=>$html,
			'path'=>$path,
		);
	}

	/**
	 * deletes a test / survey node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' key
	 */
	public function del() {
		$dh = $GLOBALS['dh'];

		$test = &$this->_r;

		$nodo = new Node($test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if (isset($_POST['delete'])) {
			if ($_POST['delete'] == 1) {

				if ($this->mode == ADA_TYPE_SURVEY) {
					$courseId = explode('_',$nodo->id);
					$courseId = $courseId[0];
					$res = $dh->test_removeCourseTest($courseId, $this->id);
				}

				if (AMATestDataHandler::isError($dh->test_deleteNodeTest($this->id))) {
					$html = sprintf(translateFN('Errore durante la cancellazione del %s'),$this->what);
				}
				else {
					redirect(HTTP_ROOT_DIR.'/browsing/view.php?id_node='.$nodo->parent_id);
				}
			}
			else {
				redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$this->id);
			}
		}
		else {
			require_once(MODULES_TEST_PATH.'/include/forms/deleteFormTest.inc.php');
			$titolo = $test['titolo'];
			if (empty($titolo)) {
				$titolo = $test['nome'];
			}
			$titolo = $this->what.' "'.$titolo.'"';
			$message = sprintf(translateFN('Stai per cancellare il %s e tutti i dati contenuti. Continuare?'),$titolo);
			$form = new DeleteFormTest($message);
			$html = $form->getHtml();
		}

		return array(
			'html'=>$html,
			'path'=>$path,
		);
	}

	/**
	 * returns status message based on $action attribute
	 */
	public function status() {
		switch ($this->action) {
			case 'add':
				return sprintf(translateFN('Aggiunta di un %s'),$this->what);
			break;
			case 'mod':
				return sprintf(translateFN('Modifica di un %s'),$this->what);
			break;
			case 'del':
				return sprintf(translateFN('Cancellazione di un %s'),$this->what);
			break;
		}
	}
}