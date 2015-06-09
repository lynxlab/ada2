<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class TopicManagementTest extends ManagementTest {
	protected $id_test = null;

	/**
	 * Topic Management constructor
	 * calls parent constructor too.
	 *
	 * @param string $action represents the action to do ('add', 'mod', 'del')
	 * @param int $id topic id
	 * @param int $id_test test id
	 */
	public function __construct($action,$id=null,$id_test=null) {
		parent::__construct($action,$id);

		if ($_POST && $_POST['id_nodo_parent'] == $id_test) {
			$this->what = translateFN('sessione');
		}
		else {
			$this->what = translateFN('attivit&agrave;');
		}
		$this->id_test = $id_test;
	}

	/**
	 * function that set "tipo" attribute from default values, post or from database record
	 */
	protected function setTipo() {
		$this->tipo = array(
			0=>ADA_GROUP_TOPIC,
			1=>ADA_PICK_QUESTIONS_NORMAL,
			2=>0, //setted to zero becase it is not applicable
			3=>0, //setted to zero becase it is not applicable
			4=>0, //setted to zero becase it is not applicable
			5=>0, //setted to zero becase it is not applicable
		);

		if ($_POST) {
			$this->tipo[1]=intval($_POST['random']);
			$this->tipo[2]=0;
			$this->tipo[3]=0;
			$this->tipo[4]=0;
			$this->tipo[5]=0;
		}
		else {
			$this->readTipoFromRecord();
		}
	}

	/**
	 * Adds a topic node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' keys
	 */
	public function add() {
		$dh = $GLOBALS['dh'];

		$test = $dh->test_getNode($this->id_test);
		$nodo = new Node($test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		require_once(MODULES_TEST_PATH.'/include/forms/topicFormTest.inc.php');
		$form = new TopicFormTest($test['id_nodo'],$_POST,
				isset($_GET['id_nodo_parent']) ? $_GET['id_nodo_parent'] : null);

		if ($_POST) {
			if ($form->isValid()) {
				$siblings = $dh->test_getNodesByParent($_POST['id_nodo_parent']);
				$ordine = count($siblings)+1;

				//crea nuovo topic con i dati del form
				$this->setTipo();
				
				/**
                 * @author giorgio 09/ott/2013
                 * must glue together the pieces coming from didascalia and stimolo into testo
				 */
				
				$testo = Node::prepareInternalLinkMediaForDatabase(trim($_POST['testo']));
				
				if (isset($_POST['didascalia-field']) && trim($_POST['didascalia-field'])!=='') {
					$testo .="<div class='didascalia-field'>"
						   . Node::prepareInternalLinkMediaForDatabase(trim($_POST['didascalia-field']))
					       ."</div>";
				}
				if (isset($_POST['stimolo-field']) && trim($_POST['stimolo-field'])!=='') {
					$testo .="<div class='stimolo-field'>"
							. Node::prepareInternalLinkMediaForDatabase(trim($_POST['stimolo-field']))
							."</div>";
				}
				
				$data = array(
					'id_corso'=>$test['id_corso'],
					'id_utente'=>$_SESSION['sess_id_user'],
					'id_istanza'=>$test['id_istanza'],
					'nome'=>$_POST['nome'],
					'titolo'=>$_POST['titolo'],
					'testo'=>$testo,
					'tipo'=>$this->getTipo(),
					'livello'=>$_POST['random_number'],
					'id_nodo_parent'=>$_POST['id_nodo_parent'],
					'id_nodo_radice'=>$test['id_nodo'],
					'durata'=>$_POST['durata']*60,
					'ordine'=>$ordine
				);
				$res = $dh->test_addNode($data);
				unset($data);

				if (!AMATestDataHandler::isError($res)) {
					if ($test['id_nodo'] == $_POST['id_nodo_parent']) {
						$_GET['topic'] = $ordine-1;
					}
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$test['id_nodo'].$get_topic);
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
	 * modifies a topic node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' keys
	 */
	public function mod() {
		$dh = $GLOBALS['dh'];

		$topic = &$this->_r;

		$test = $dh->test_getNode($topic['id_nodo_radice']);
		$nodo = new Node($test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if ($_POST) {
			$data = $_POST;
		}
		else {
			$data = array(
				'nome'=>$topic['nome'],
				'titolo'=>$topic['titolo'],
				'testo'=>$topic['testo'],
				'durata'=>round($topic['durata']/60,2),
				'random'=>$topic['tipo']{1},
				'random_number'=>$topic['livello'],
				'id_nodo_parent'=>$topic['id_nodo_parent'],
			);
		}

		require_once(MODULES_TEST_PATH.'/include/forms/topicFormTest.inc.php');
		$form = new TopicFormTest($topic['id_nodo_radice'],$data,$topic['id_nodo_parent']);

		if ($_POST) {
			if ($form->isValid()) {
				//crea nuovo test con i dati del form
				$this->setTipo();
				
				/**
				 * @author giorgio 09/ott/2013
				 * must glue together the pieces coming from didascalia and stimolo into testo
				 */
				
				$testo = Node::prepareInternalLinkMediaForDatabase(trim($_POST['testo']));
				
				if (isset($_POST['didascalia-field']) && trim($_POST['didascalia-field'])!=='') {
					$testo .="<div class='didascalia-field'>"
							. Node::prepareInternalLinkMediaForDatabase(trim($_POST['didascalia-field']))
							."</div>";
				}
				if (isset($_POST['stimolo-field']) && trim($_POST['stimolo-field'])!=='') {
					$testo .="<div class='stimolo-field'>"
							. Node::prepareInternalLinkMediaForDatabase(trim($_POST['stimolo-field']))
							."</div>";
				}
				
				$data = array(
					'nome'=>$_POST['nome'],
					'titolo'=>$_POST['titolo'],
					'testo'=>$testo,
					'id_nodo_parent'=>$_POST['id_nodo_parent'],
					'tipo'=>$this->getTipo(),
					'livello'=>$_POST['random_number'],
					'durata'=>$_POST['durata']*60,
				);
				if ($dh->test_updateNode($topic['id_nodo'],$data)) {
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$test['id_nodo'].$get_topic);
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
	 * deletes a topic node
	 *
	 * @global db $dh
	 *
	 * @return array an array with 'html' and 'path' key
	 */
	public function del() {
		$dh = $GLOBALS['dh'];

		$topic = &$this->_r;

		$test = $dh->test_getNode($topic['id_nodo_radice']);
		$nodo = new Node($test['id_nodo_riferimento']);
		if (!AMATestDataHandler::isError($nodo)) {
			$path = $nodo->findPathFN();
		}

		if ($nodo->id_nodo_radice == $nodo->id_nodo_parent) {
			$this->what = translateFN('sessione');
		}

		if (isset($_POST['delete'])) {
			if ($_POST['delete'] == 1) {
				if (AMATestDataHandler::isError($dh->test_deleteNodeTest($this->id))) {
					$html = sprintf(translateFN('Errore durante la cancellazione del %s'),$this->what);
				}
				else {
					$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
					redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$topic['id_nodo_radice'].$get_topic);
				}
			}
			else {
				$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');
				redirect(MODULES_TEST_HTTP.'/index.php?id_test='.$topic['id_nodo_radice'].$get_topic);
			}
		}
		else {
			require_once(MODULES_TEST_PATH.'/include/forms/deleteFormTest.inc.php');
			$titolo = $topic['titolo'];
			if (empty($titolo)) {
				$titolo = $topic['nome'];
			}
			$titolo = $this->what.' "'.$titolo.'"';
			$message = sprintf(translateFN('Stai per cancellare %s e tutti i dati contenuti. Continuare?'),$titolo);
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
				return sprintf(translateFN('Aggiunta %s'),$this->what);
			break;
			case 'mod':
				return sprintf(translateFN('Modifica %s'),$this->what);
			break;		
			case 'del':
				return sprintf(translateFN('Cancellazione %s'),$this->what);
			break;
		}
	}
}