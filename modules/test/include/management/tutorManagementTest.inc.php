<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class TutorManagementTest {

	protected $student;
	protected $id_student;
	protected $test;
	protected $history_test;
	protected $action;
	protected $what;
	protected $courseObj;
	protected $course_instanceObj;
	protected $returnError = false;
	protected $singolare;
	protected $plurale;
	protected $tipo;
	protected $filepath;

	/**
	 * constructs tutor management and configure it according to parameters
	 *
	 * @global db $dh
	 *
	 * @param string $what 'test' or 'survey' string
	 * @param Course $courseObj course object reference
	 * @param Course_instance $course_instanceObj course instance object reference
	 * @param int $id_student id student
	 * @param int $id_test id test
	 * @param int $id_history_test id history test
	 */
	public function __construct($what, Course $courseObj, Course_instance $course_instanceObj, $id_student = null, $id_test = null, $id_history_test = null) {
		$dh = $GLOBALS['dh'];

		$this->filepath = MODULES_TEST_HTTP.'/'.basename($_SERVER['PHP_SELF']);
		$this->what = $what;
		$this->courseObj = $courseObj;
		$this->course_instanceObj = $course_instanceObj;

		$this->action = 'list_students';
		if (!is_null($id_student) && !is_null($id_test) && !is_null($id_history_test)) {
			$this->action = 'view_history_tests';
		}
		else if (!is_null($id_student) && !is_null($id_test)) {
			$this->action = 'list_history_tests';
		}
		else if (!is_null($id_student)) {
			$this->action = 'list_tests';
		}

		switch(true) {
			case (!is_null($id_student) && !is_null($id_test) && !is_null($id_history_test)):
				$where = array('id_history_test'=>$id_history_test);
				$history_test = $dh->test_getHistoryTestJoined($where);
				if ($dh->isError($history_test)) {
					$this->returnError = true;
				}
				$this->history_test = $history_test[0];

			case (!is_null($id_student) && !is_null($id_test)):
				$test = $dh->test_getNode($id_test);
				if ($dh->isError($test) || empty($test)) {
					$this->returnError = true;
				}
				$this->test = $test;

			case (!is_null($id_student)):
				$student = $dh->get_student($id_student);
				if ($dh->isError($student) || empty($student)) {
					$this->returnError = true;
				}
				$this->student = $student;
				$this->id_student = $id_student;

			break;
		}

		if ($this->what == 'test') {
			$this->singolare = translateFN('Test');
			$this->plurale = translateFN('Test');
			$this->tipo = '1%';
		}
		else {
			$this->singolare = translateFN('Sondaggio');
			$this->plurale = translateFN('Sondaggi');
			$this->tipo = '2%';
		}
	}

	/**
	 * function that return list of students that sent test or survey
	 *
	 * @global db $dh
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_students() {
		$dh = $GLOBALS['dh'];

		$historyTest = $dh->test_getHistoryTestJoined(array('id_corso'=>$this->courseObj->id,'id_istanza_corso'=>$this->course_instanceObj->id),$this->tipo);
		if ($dh->isError($historyTest)) {
			$this->returnError = true;
			return;
		}

		$tests = array();
		if (!empty($historyTest)) {
			foreach($historyTest as $k=>$r) {
				$tests[$r['id_utente']]['id_course'] = $r['id_corso'];
				$tests[$r['id_utente']]['id_instance'] = $r['id_istanza_corso'];
				$tests[$r['id_utente']]['id_utente'] = $r['id_utente'];
				$tests[$r['id_utente']]['cognome'] = $r['cognome'];
				$tests[$r['id_utente']]['nome'] = $r['nome'];
				if (!isset($tests[$r['id_nodo']]['count'])) {
					$tests[$r['id_utente']]['count'] = 0;
				}
				$tests[$r['id_utente']]['count']++;
			}
		}

		$thead = array(
			translateFN('Id'),
			translateFN('Studente'),
			sprintf(translateFN('%s effettuati'),$this->plurale),
			sprintf(translateFN('Lista %s'),$this->plurale),
		);

		$tbody = array();
		if (!empty($tests)) {
			foreach($tests as $k=>$t) {
				$tbody[$k][] = $t['id_utente'];
				$tbody[$k][] = '<a href="'.HTTP_ROOT_DIR.'/tutor/tutor.php?op=zoom_student&id_student='.$t['id_utente'].'&id_course='.$t['id_course'].'&id_instance='.$t['id_instance'].'">'.$t['cognome'].' '.$t['nome'].'</a>';
				$tbody[$k][] =  $t['count'];
				$tbody[$k][] =  '<a href="'.$this->filepath.'?op='.$this->what.'&id_course='.$t['id_course'].'&id_course_instance='.$t['id_instance'].'&id_student='.$t['id_utente'].'"><img src="img/magnify.png" /></a>';
			}

			$caption = sprintf(translateFN('Studenti che hanno effettuato %s'),$this->plurale);
			$table = BaseHtmlLib::tableElement('', $thead, $tbody, $thead, $caption);
			$table->setAttribute('class', $table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
			$html = $table->getHtml();
		}
		else {
			$html = sprintf(translateFN('Nessun %s è stato eseguito'), $this->singolare);
		}

		return array(
			'html' => $html,
			'path' => translateFN('Valutazione').' '.ucfirst($this->plurale),
			'title' => translateFN('Valutazione').' '.ucfirst($this->plurale),
		);
	}

	/**
	 * function that return list of test sent test or survey by student
	 *
	 * @global db $dh
	 *
	 * @param boolean $student if true switch scope from tutor to student
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_tests($student = false) {
		$dh = $GLOBALS['dh'];

		$params = array('id_corso'=>$this->courseObj->id,'id_istanza_corso'=>$this->course_instanceObj->id);
		if ($student || $this->student) $params['id_utente'] = $this->id_student;

		$historyTest = $dh->test_getHistoryTestJoined($params,$this->tipo);
		if ($dh->isError($historyTest)) {
			$this->returnError = true;
			return;
		}

		$tests = array();
		if (!empty($historyTest)) {
			foreach($historyTest as $k=>$r) {
				$tests[$r['id_nodo']]['id_test'] = $r['id_nodo'];
				$tests[$r['id_nodo']]['id_course'] = $r['id_corso'];
				$tests[$r['id_nodo']]['id_instance'] = $r['id_istanza_corso'];
				$tests[$r['id_nodo']]['id_utente'] = $r['id_utente'];
				$tests[$r['id_nodo']]['titolo'] = $r['titolo'];
				$tests[$r['id_nodo']]['ripetibile'] = $r['tipo']{5};
				$tests[$r['id_nodo']]['nome_corso'] = $r['nome_corso'];
				$tests[$r['id_nodo']]['nome_istanza'] = $r['nome_istanza'];
				$tests[$r['id_nodo']]['punteggio_barriera'] = $r['correttezza'];
				$tests[$r['id_nodo']]['punteggio'][] = $r['punteggio_realizzato'];

				if (!isset($tests[$r['id_nodo']]['count_scaduto'])) {
					$tests[$r['id_nodo']]['count_scaduto'] = 0;
				}
				$tests[$r['id_nodo']]['count_scaduto']+= $r['tempo_scaduto'];

				if (!isset($tests[$r['id_nodo']]['count_consegnato'])) {
					$tests[$r['id_nodo']]['count_consegnato'] = 0;
				}
				$tests[$r['id_nodo']]['count_consegnato']+= $r['consegnato'];

				if (!isset($tests[$r['id_nodo']]['count'])) {
					$tests[$r['id_nodo']]['count'] = 0;
				}
				$tests[$r['id_nodo']]['count']++;
			}
		}

		$thead = array(
			translateFN('Id'),
			translateFN('Titolo'),
			translateFN('Ripetibile'),
			translateFN('Punteggio Medio'),
			translateFN('Punteggio Min'),
			translateFN('Punteggio Max'),
			translateFN('Punteggio Barriera'),
			translateFN('Tentativi Consegnati'),
			translateFN('Tentativi Scaduti'),
			translateFN('Visualizza'),
		);

		$tbody = array();
		if (!empty($tests)) {
			foreach($tests as $k=>$r) {
				$nome_istanza = (empty($r['nome_istanza'])?$r['nome_corso']:$r['nome_istanza'].' ('.translateFN('corso').' '.$r['nome_corso'].')');

				$tbody[$k][] = $r['id_test'];
				$tbody[$k][] = $r['titolo'];
				$tbody[$k][] = ($r['ripetibile'])?translateFN('Si'):translateFN('No');
				$tbody[$k][] = round(array_sum($r['punteggio'])/$r['count'],2);
				$tbody[$k][] = min($r['punteggio']);
				$tbody[$k][] = max($r['punteggio']);
				$tbody[$k][] = ($r['punteggio_barriera'])?$r['punteggio_barriera']:translateFN('Nessuna');
				$tbody[$k][] = $r['count_consegnato'].' '.translateFN('su').' '.$r['count'];
				$tbody[$k][] = $r['count_scaduto'].' '.translateFN('su').' '.$r['count'];
				$tbody[$k][] = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course='.$r['id_course'].'&id_course_instance='.$r['id_instance'].'&id_student='.$r['id_utente'].'&id_test='.$r['id_test'].'"><img src="img/magnify.png" /></a>';
			}

			$caption = sprintf(translateFN('%s effettuati dallo studente %s %s per il corso "%s"'),$this->plurale,$this->student['cognome'],$this->student['nome'],$nome_istanza);
			$table = BaseHtmlLib::tableElement('', $thead, $tbody, $thead, $caption);
			$table->setAttribute('class', $table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
			$html = $table->getHtml();
		}
		else {
			if ($student) {
				$html = sprintf(translateFN('Non hai effettuato nessun %s'), $this->singolare);
			}
			else {
				$html = sprintf(translateFN('Lo studente selezionato non ha effettuato nessun %s'), $this->singolare);
			}
		}

		$path = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'">'.translateFN('Valutazione').' '.ucfirst($this->plurale).'</a> &gt; '.$this->student['cognome'].' '.$this->student['nome'];

		return array(
			'html' => $html,
			'path' => $path,
			'title' => translateFN('Valutazione').' '.ucfirst($this->plurale),
		);
	}

	/**
	 * function that return list of history test sent test or survey by student
	 *
	 * @global db $dh
	 *
	 * @param boolean $student if true switch scope from tutor to student
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function list_history_tests($student = false) {
		$dh = $GLOBALS['dh'];

		$params = array('id_nodo'=>$this->test['id_nodo'],'id_corso'=>$this->courseObj->id,'id_istanza_corso'=>$this->course_instanceObj->id);
		if ($student || $this->student) $params['id_utente'] = $this->id_student;

		$historyTest = $dh->test_getHistoryTestJoined($params,$this->tipo);
		if ($dh->isError($historyTest)) {
			$this->returnError = true;
			return;
		}

		$thead = array(
			sprintf(translateFN('%s Id'),$this->singolare),
			translateFN('Titolo'),
			translateFN('Punteggio'),
			translateFN('Punteggio Barriera'),
			translateFN('Consegnato'),
			translateFN('Tempo scaduto'),
			translateFN('Ripetibile'),
			translateFN('Data inizio'),
			translateFN('Data fine'),
			translateFN('Visualizza'),
		);

		$tbody = array();
		if (!empty($historyTest)) {
			foreach($historyTest as $k=>$r) {
				$id_student = $r['id_utente'];

				$tbody[$k][] = $k+1;
				$tbody[$k][] = $r['titolo'];
				$tbody[$k][] = $r['punteggio_realizzato'];
				$tbody[$k][] = $r['punteggio_minimo_barriera'];
				$tbody[$k][] = ($r['consegnato'])?translateFN('Si'):translateFN('No');
				$tbody[$k][] = ($r['tempo_scaduto'])?translateFN('Si'):translateFN('No');
				$tbody[$k][] = ($r['ripetibile'])?translateFN('Si'):translateFN('No');
				$tbody[$k][] = AMA_DataHandler::ts_to_date($r['data_inizio'], "%d/%m/%Y %H:%M:%S");
				$tbody[$k][] = ($r['data_fine']>0)?AMA_DataHandler::ts_to_date($r['data_fine'], "%d/%m/%Y %H:%M:%S"):'---';
				$tbody[$k][] = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course='.$r['id_corso'].'&id_course_instance='.$r['id_istanza_corso'].'&id_student='.$r['id_utente'].'&id_test='.$r['id_nodo'].'&id_history_test='.$r['id_history_test'].'"><img src="img/magnify.png" /></a>';
			}

			$caption = sprintf(translateFN('Tentativi effettuati dallo studente %s %s per il %s "%s"'),$this->student['cognome'],$this->student['nome'],$this->singolare,$this->test['titolo']);
			$table = BaseHtmlLib::tableElement('', $thead, $tbody, $thead, $caption);
			$table->setAttribute('class', $table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
			$html = $table->getHtml();
		}
		else {
			if ($student) {
				$html = sprintf(translateFN('Non hai effettuato nessun %s'), $this->singolare);
			}
			else {
				$html = sprintf(translateFN('Lo studente selezionato non ha effettuato nessun %s'), $this->singolare);
			}
		}

		$path = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'">'.translateFN('Valutazione').' '.ucfirst($this->plurale).'</a> &gt; <a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'&id_student='.$id_student.'">'.$this->student['cognome'].' '.$this->student['nome'].'</a> &gt; '.$this->test['titolo'];

		return array(
			'html' => $html,
			'path' => $path,
			'title' => translateFN('Valutazione').' '.ucfirst($this->plurale),
		);
	}

	/**
	 * function that return a specific history test
	 *
	 * @global db $dh
	 *
	 * @return array an array composed of 'html', 'path' and 'title' keys
	 */
	protected function view_history_tests() {
		$dh = $GLOBALS['dh'];

		$test = NodeTest::readTest($this->test['id_nodo']);
		if ($dh->isError($test)) {
			$html = sprintf(translateFN('%s non trovato'),$this->singolare);
		}
		else {
			$test->run($this->history_test['id_history_test'],true);
			$html = $test->render(true);
		}

		$path = '<a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'">'.translateFN('Valutazione').' '.ucfirst($this->plurale).'</a> &gt; <a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'&id_student='.$this->history_test['id_utente'].'">'.$this->student['cognome'].' '.$this->student['nome'].'</a> &gt; <a href="'.$this->filepath.'?op='.$this->what.'&id_course_instance='.$this->course_instanceObj->id.'&id_course='.$this->courseObj->id.'&id_student='.$this->history_test['id_utente'].'&id_test='.$this->test['id_nodo'].'">'.$this->test['titolo'].'</a> &gt; '.translateFN('Tentativo'). ' #'.$this->history_test['id_history_test'];

		return array(
			'html' => $html,
			'path' => $path,
			'title' => translateFN('Valutazione').' '.ucfirst($this->plurale),
		);
	}

	/**
	 * Runs correct method using action attribute
	 *
	 * @param boolean $returnHtml
	 *
	 * @return string or CBase according to $returnHtml
	 */
	public function run($returnHtml = false) {
		if (method_exists($this, $this->action)) {
			$return = $this->{$this->action}();
		}
		else {
			$this->returnError = true;
		}

		if ($this->returnError) {
			$return = array(
				'html' => translateFN('Si è verificato un errore'),
				'path' => translateFN('Si è verificato un errore'),
				'title' => translateFN('Si è verificato un errore'),
			);
		}

		if ($returnHtml && is_a($return['html'],'CBase')) {
			$return['html'] = $return['html']->getHtml();
		}
		return $return;
	}

	/**
	 * Executes run method with $returnHtml set to true
	 *
	 * @return string
	 *
	 * @see run
	 */
	public function render() {
		return $this->run(true);
	}
}
