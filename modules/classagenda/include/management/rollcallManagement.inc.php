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
	public $eventData = null;
	
	private $_userObj = null;
	
	public function __construct($id_course_instance = null) {
		parent::__construct(array('id_course_instance'=>$id_course_instance));
		
		$this->_userObj = $_SESSION['sess_userObj'];
		
		if ($this->_userObj instanceof ADALoggableUser) {
			$this->eventData = $this->_findClosestCourseInstance();
			
			if (!is_null($this->eventData)) {
				$this->id_course_instance = $this->eventData['id_istanza_corso'];
			}
		}
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
					$htmlObj->addChild(new CText(translateFN('Nessun evento da mostrare trovato')));
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
							$caption = translateFN('Registro Entrate-Uscite del');
							list ($startDate,$starTime) = explode(' ', $this->eventData['start']);
							list ($endDate,$endTime) = explode(' ', $this->eventData['end']);
							$caption .= ' '.$startDate.' '.translateFN('ore').' '.$starTime;
							$caption .= ' '.translateFN('al').' '.$endDate.' '.translateFN('ore').' '.$endTime;
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
	
	/**
	 * check if $this->_userObj is a tutor for $this->id_course instance
	 * 
	 * @return boolean true on success
	 * 
	 * @access private
	 */
	private function _isTutorOfInstance() {
		if (is_null($this->_userObj) || $this->_userObj->getType()!=AMA_TYPE_TUTOR) return false;
		
		$dh = $GLOBALS['dh'];
		$res = $dh->course_tutor_instance_get($this->_userObj->getId());
		if (!AMA_DB::isError($res) && is_array($res) && $res!==false ) {		
			foreach ($res as $tutored_instance) {
				if ($this->id_course_instance == $tutored_instance[0]) return true;
			}			
		} else return false;
	}
	
	/**
	 * gets the instance name of $this_>id_course_instance
	 * 
	 * @return string|NULL
	 * 
	 * @access private
	 */
	private function _getInstanceName() {
		$dh = $GLOBALS['dh'];
		$courseInstance = $dh->course_instance_get($this->id_course_instance);
		if (!AMA_DB::isError($courseInstance) && isset($courseInstance['title']) && strlen($courseInstance['title'])>0) {
			return $courseInstance['title'];
		} else return null;
	}
	
	/**
	 * adds detail and action buttons to student list array
	 * 
	 * @param array $studentsList
	 * 
	 * @return array $studentsList with added fields 'details' and 'actions'
	 *  
	 * @access private
	 */
	private function _addDetailsAndActionToStudentList($studentsList) {
		if (is_array($studentsList) && count($studentsList)>0) {
			$dh = $GLOBALS['dh'];
			
			foreach ($studentsList as $i=>$student) {
				
				$userDetailsSPAN = CDOMElement::create('span','id:'.$student[0].'_details');
				$isEnterButtonVisibile = true;
				$detailsStr = '';
				
				/**
				 * load and display details data column
				 */
				$detailsAr = $dh->getRollCallDetails($student[0],$this->eventData['module_classagenda_calendars_id']);
				
				if (!AMA_DB::isError($detailsAr) && is_array($detailsAr) && count($detailsAr)>0) {					
					foreach ($detailsAr as $j=>$enterexittime) {
						if (strlen($enterexittime['entertime'])>0) {
							if ($j>0) $detailsStr .= '<br/>';
							$detailsStr .= translateFN('Entrata alle: ');
							$detailsStr .= ts2tmFN($enterexittime['entertime']);
							$isEnterButtonVisibile = false;
						}
						if (strlen($enterexittime['exittime'])>0) {
							$detailsStr .= '<br/>';
							$detailsStr .= translateFN('Uscita alle: ');
							$detailsStr .= ts2tmFN($enterexittime['exittime']);
							$isEnterButtonVisibile = true;
						}
					}
				}
				
				if (strlen($detailsStr)>0) $userDetailsSPAN->addChild (new CText($detailsStr.'<br/>'));
				
				$studentsList[$i]['details'] = $userDetailsSPAN->getHtml(); 
				$studentsList[$i]['actions'] = $this->_buildEnterExitButtons($student[0], $isEnterButtonVisibile);
			}
		}
		return $studentsList;
	}
	
	/**
	 * builds the enter and exit buttons for the currrent table row
	 * 
	 * @param number $id_student the student for whom the buttons are genertated
	 * @param boolean $isEnterButtonVisibile true if enter button must be made visible
	 * 
	 * @return CDiv
	 * 
	 * @access private
	 */
	private function _buildEnterExitButtons($id_student, $isEnterButtonVisibile=true) {
		
		$enterButton = CDOMElement::create('button','class:enterbutton');
		if (!$isEnterButtonVisibile) $enterButton->setAttribute('style', 'display:none');
		$enterButton->setAttribute('onclick', 'javascript:toggleStudentEnterExit($j(this), '.$id_student.','.$this->eventData['module_classagenda_calendars_id'].',true);');
		$enterButton->addChild(new CText(translateFN('Entra')));
		
		$exitButton = CDOMElement::create('button','class:exitbutton');
		if ($isEnterButtonVisibile) $exitButton->setAttribute('style', 'display:none');
		$exitButton->setAttribute('onclick', 'javascript:toggleStudentEnterExit($j(this), '.$id_student.','.$this->eventData['module_classagenda_calendars_id'].',false);');
		$exitButton->addChild(new CText(translateFN('Esce')));
		
		$buttonsDIV = CDOMElement::create('div','class:buttonsContainer');
		$buttonsDIV->addChild($enterButton);
		$buttonsDIV->addChild($exitButton);
		
		return $buttonsDIV->getHtml();
	}
	
	/**
	 * adds the roll call history to each element of the students list
	 * 
	 * @param array $studentsList
	 * 
	 * @return array the passed array, with the added roll call history
	 * 
	 * @access private
	 */
	private function _addRollCallHistoryToStudentList($studentsList) {
		
		$dh = $GLOBALS['dh'];
		$allTimestamps = array();
		
		foreach ($studentsList as $i=>$student) {			
			$result = $dh->getRollCallDetailsForInstance($student['id'],$this->id_course_instance);			
			
			if (!AMA_DB::isError($result) && is_array($result) && count($result)>0) {
				foreach ($result as $aRow) {
					if (strlen($aRow['entertime'])>0) {
						// get entertime date only as a timestamp for the array key
						$arrKey = $dh->date_to_ts(ts2dFN($aRow['entertime']));
						if(!in_array($arrKey, $allTimestamps)) $allTimestamps[] = $arrKey;
						
						if (strlen($studentsList[$i][$arrKey])>0) $studentsList[$i][$arrKey].='<br/>';
						else $studentsList[$i][$arrKey] = '';
						
						$studentsList[$i][$arrKey] .= translateFN('Entrata alle: ');
						$studentsList[$i][$arrKey] .= ts2tmFN($aRow['entertime']);
						
						if (strlen($aRow['exittime'])>0) {
							$studentsList[$i][$arrKey] .= '<br/>';
							$studentsList[$i][$arrKey] .= translateFN('Uscita alle: ');
							$studentsList[$i][$arrKey] .= ts2tmFN($aRow['exittime']);
						}
					}
				}
			}
			// remove student id for proper table display
			unset($studentsList[$i]['id']);
		}
		
		/**
		 * every array MUST have all the generated keys (timestamps)
		 * for the HTML table to be properly rendered
		 */
		sort ($allTimestamps, SORT_NUMERIC);
		$retArray = array();
		
		foreach ($studentsList as $i=>$student) {
			$retArray[$i]['name'] = strtolower($student['name']);
			foreach ($allTimestamps as $timestamp) {
				$retArray[$i][$timestamp] = (!array_key_exists($timestamp, $student)) ? '' : $studentsList[$i][$timestamp]; 
			}			
		}
		
		return $retArray;
	}
	
	/**
	 * gets the student list to be displayed either when doing a roll call
	 * or displaying the roll call history details
	 *  
	 * @param number $action
	 * 
	 * @return Ambigous <NULL, array>
	 * 
	 * @access private
	 */
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
						$row = array(
								'id'=>$one_student['id_utente_studente'],
								'name'=>$studn['nome'].' '.$studn['cognome']);
					}
					array_push($student_listHa,$row);
				}
			}
		}
		return (count($student_listHa)>0) ? $student_listHa : null;
	}
	
	private function _findClosestCourseInstance() {
		$dh = $GLOBALS['dh'];
		$result = $dh->findClosestClassroomEvent ($this->_userObj->getId(), $this->id_course_instance);
		
		if (AMA_DB::isError($result)) return null;
		else {
			$result['start'] = ts2dFN($result['start']). ' '.ts2tmFN($result['start']);
			$result['end'] = ts2dFN($result['end']). ' '.ts2tmFN($result['end']);
			return $result;
		}
	}
} // class ends here