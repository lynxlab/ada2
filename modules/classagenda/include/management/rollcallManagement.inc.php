<?php
/**
 * Calendars Management Class
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classagenda
 * @version			0.1
 */

/**
 * class for managing roll calls
 *
 * @author giorgio
 */
require_once MODULES_CLASSAGENDA_PATH . '/include/management/abstractClassagendaManagement.inc.php';

class rollcallManagement extends abstractClassAgendaManagement
{
	public $id_course_instance = null;
	
	private $_userObj;
	
	public function __construct($id_course_instance, $userObj) {
		
		$this->_userObj = $userObj;
		
		// set object property to passed value
		parent::__construct(array('id_course_instance'=>$id_course_instance));
	}
    
	/**
	 * build, manage and display the module's pages
	 *
	 * @return array
	 * 
	 * @access public
	 */
	public function run($action=null) {
		
		require_once ROOT_DIR . '/include/HtmlLibrary/BaseHtmlLib.inc.php';
		
		/* @var $html	string holds html code to be retuned */
		$htmlObj = null;
		/* @var $path	string  path var to render in the help message */
		$help = '';
		/* @var $status	string status var to render in the breadcrumbs */
		$title= translateFN('Foglio presenze');
		
		switch ($action) {
			case MODULES_CLASSAGENDA_DO_ROLLCALL:
			case MODULES_CLASSAGENDA_DO_ROLLCALLHISTORY:
				
				$htmlObj = CDOMElement::create('div','id:rollcallContainer');
				if (!isset($this->id_course_instance) || is_null($this->id_course_instance) ||
					strlen($this->id_course_instance)<=0 || !is_numeric($this->id_course_instance) || 
						!$this->_isTutorOfInstance()) {
					$htmlObj->addChild(new CText(translateFN('Istanza corso non valida')));
				} else {
					/**
					 * get list of students subscribed to passed instance
					 */
					$studentsList = $this->_getStudentsList($action);
					if (!is_null($studentsList)) {
						if ($action==MODULES_CLASSAGENDA_DO_ROLLCALL){
							/**
							 * add data and action field to the student list
							 */
							$studentsList = $this->_addDetailsAndActionToStudentList($studentsList);
							/**
							 * setup arrays and variables to build the table
							 */
							$header = array ('id',
									translateFN('Nome'),
									translateFN('Cognome'),
									translateFN('E-Mail'),
									translateFN('Dettagli'),
									translateFN('Azioni'));
							$caption = translateFN('Registro Entrate-Uscite del ').ts2dFN(time());
							$tableID = 'rollcallTable';
							/**
							 * set the help message
							 */
							$help = translateFN('Gestione foglio presenze');
							
						} else if ($action==MODULES_CLASSAGENDA_DO_ROLLCALLHISTORY) {
							/**
							 * add presence details to the student list
							 */
							$studentsList = $this->_addRollCallHistoryToStudentList($studentsList);
							/**
							 * setup arrays and variables to build the table
							 */ 
							
							/**
							 * 1. get the timestamps of the first student
							 * and use them to build the header of the table
							 */
							$timestamps = array_keys(array_slice($studentsList[0], 1,null, true));							
							for ($i=0;$i<count($timestamps);$i++) $timestamps[$i] = ts2dFN($timestamps[$i]);
							/**
							 * 2. build the header with 'Nome e Cognome' in the
							 * first position and then all the timestamps converted
							 * into human readable dates
							 */
							$header = array_merge(array (translateFN('Nome e Cognome')),$timestamps);		
							
							$caption = translateFN('Riepilogo presenze studenti');
							$tableID = 'rollcallHistoryTable';
							/**
							 * set the help message
							 */
							$help = translateFN('Riepilogo presenze');
						}
						/**
						 * get passed instance name and add it to help message
						 */
						$instancename = $this->_getInstanceName();
						if (!is_null($instancename)) $help .= ' '.translateFN('della classe').' '.$instancename;
						/**
						 * build the html table
						 */
						$tableObj = BaseHtmlLib::tableElement('id:'.$tableID,$header,$studentsList,null,$caption);
						$htmlObj->addChild($tableObj);
					} else {
						$htmlObj->addChild(new CText(translateFN('Nessuno studente iscritto')));
					}
				}
				break;				
			default:
				/**
				 * return an empty page as default action
				 */
				break;
		}
		
		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
	}
	
	private function _isTutorOfInstance() {
		$dh = $GLOBALS['dh'];
		$res = $dh->course_tutor_instance_get($this->_userObj->getId());
		if (!AMA_DB::isError($res) && is_array($res) && $res!==false ) {		
			foreach ($res as $tutored_instance) {
				if ($this->id_course_instance == $tutored_instance[0]) return true;
			}			
		} else return false;
	}
	
	private function _getInstanceName() {
		$dh = $GLOBALS['dh'];
		$courseInstance = $dh->course_instance_get($this->id_course_instance);
		if (!AMA_DB::isError($courseInstance) && isset($courseInstance['title']) && strlen($courseInstance['title'])>0) {
			return $courseInstance['title'];
		} else return null;
	}
	
	private function _addDetailsAndActionToStudentList($studentsList) {
		if (is_array($studentsList) && count($studentsList)>0) {
			foreach ($studentsList as $i=>$student) {
				
				// TODO: load module's own data here?
				$userDetailsSPAN = CDOMElement::create('span','id:'.$student[0].'_details');
				
				$studentsList[$i]['details'] = $userDetailsSPAN->getHtml(); 
				$studentsList[$i]['actions'] = $this->_buildEnterExitButtons($student[0]);
			}
		}
		return $studentsList;
	}
	
	private function _buildEnterExitButtons($id_student) {
		
		// TODO: check user enter/exit status and hide proper buttons here? 
		
		$enterButton = CDOMElement::create('button','class:enterbutton');
		$enterButton->setAttribute('onclick', 'javascript:toggleStudentEnterExit($j(this), '.$id_student.','.$this->id_course_instance.',true);');
		$enterButton->addChild(new CText(translateFN('Entra')));
		
		$exitButton = CDOMElement::create('button','class:exitbutton');
		$exitButton->setAttribute('style', 'display:none');
		$exitButton->setAttribute('onclick', 'javascript:toggleStudentEnterExit($j(this), '.$id_student.','.$this->id_course_instance.',false);');
		$exitButton->addChild(new CText(translateFN('Esce')));
		
		$buttonsDIV = CDOMElement::create('div','class:buttonsContainer');
		$buttonsDIV->addChild($enterButton);
		$buttonsDIV->addChild($exitButton);
		
		return $buttonsDIV->getHtml();
	}
	
	private function _addRollCallHistoryToStudentList($studentsList) {
		
		/**
		 * add 5 days of empty data starting from now
		 * just for testing purposes
		 */
		$now = strtotime('now');
		$onedaystep = strtotime('+1 days') - $now;
		$stoptime = strtotime ('+5 days', $now);
		
		foreach ($studentsList as $i=>$student) {
			for ($timestamp = $now; $timestamp < $stoptime; $timestamp += $onedaystep) {
				$studentsList[$i][$timestamp] = '';
			}
		}
		return $studentsList;
	}
	
	private function _getStudentsList($action) {
		$dh = $GLOBALS['dh'];
		$student_listHa = array();
		
		$stud_status = ADA_STATUS_SUBSCRIBED; //only subscribed students
		$students =  $dh->course_instance_students_presubscribe_get_list($this->id_course_instance,$stud_status);
		if (!AMA_DB::isError($students) && is_array($students) && count($students)>0) {
			foreach ($students as $one_student) {
				$id_stud = $one_student['id_utente_studente'];
				if ($dh->get_user_type($id_stud)==AMA_TYPE_STUDENT) {
					$studn = $dh->get_student($id_stud);
					if ($action==MODULES_CLASSAGENDA_DO_ROLLCALL) {
						$row = array(
								$one_student['id_utente_studente'],
								$studn['nome'],
								$studn['cognome'],
								$studn['email'] );						
					} else if ($action==MODULES_CLASSAGENDA_DO_ROLLCALLHISTORY) {
						$row = array($studn['nome'].' '.$studn['cognome']);
					}
					array_push($student_listHa,$row);
				}
			}
		}
		return (count($student_listHa)>0) ? $student_listHa : null;
	}
} // class ends here