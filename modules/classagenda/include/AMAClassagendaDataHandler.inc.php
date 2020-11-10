<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package        classagenda module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classagenda
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAClassagendaDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_classagenda_';

	/**
	 * saves all the passed classroom events for the passed instance and venue
	 *
	 * @param number $course_instance_id
	 * @param number $venueID
	 * @param array $eventsArray
	 * @param number $start save events with start timestamp >= $start
	 * @param number $end save events with start timestamp >= $end
	 *
	 * @return AMA_Error on failure|boolean true on success
	 *
	 * @access public
	 */
	public function saveClassroomEvents ($course_instance_id, $venueID, $eventsArray, $start, $end) {
		/**
		 * get all the classroom events for the passed instance
		 */
		$previousEvents = $this->getClassRoomEventsForCourseInstance ($course_instance_id, $venueID, $start, $end);
		if (AMA_DB::isError($previousEvents)) $previousEvents = array();

		if (!is_null($eventsArray)) {
			$generatedIDs = [];
			foreach ($eventsArray as $event) {
				if (strlen($event['id'])<=0) {
					$tempID = (isset($event['id']) && intval($event['id'])>0) ? null: $event['tempID'];
				} else $tempID = null;
				$eventID = $this->saveClassroomEvent($course_instance_id, $event);
				if (!AMA_DB::isError($eventID) && intval($eventID)>0) {
					// event has been updated, remove it from the previous events array
					if (array_key_exists($eventID, $previousEvents)) {
						unset ($previousEvents[$eventID]);
					}
					/**
					 * if we've just inserted the event that was selected in the UI,
					 * return its ID in the database, so that the JS can re-select it
					 */
					if ($event['wasSelected']) {
						$newSelectedID = $eventID;
					}
					// if it was a new event, link its tempID with the generated id
					if (!is_null($tempID)) {
						$generatedIDs[$tempID] = $eventID;
					}
				} else if (AMA_DB::isError($eventID)) {
					// on error return right away
					return $eventID;
				}
			}
		}

		/**
		 * what is left in the previous events array must be deleted
		 */
		foreach ($previousEvents as $eventID=>$anEvent) {
		    $this->deleteClassroomEvent($eventID);
		}

		return [
			'newSelectedID' => isset($newSelectedID) ? $newSelectedID : true,
			'generatedIDs' => (isset($generatedIDs) && is_array($generatedIDs) && count($generatedIDs)>0) ? $generatedIDs : [],
		];
	}

	/**
	 * gets all the classroom events for the passed instance and venue
	 *
	 * @param number $course_instance_id
	 * @param number $venueID
	 * @param number $start select events with start timestamp >= $start
	 * @param number $end select events with start timestamp >= $end
	 *
	 * @return array classroom events or empty
	 *
	 * @access public
	 */
	public function getClassRoomEventsForCourseInstance($course_instance_id, $venueID, $start=0, $end=0) {

		if (!isset($venueID)) $venueID=null;

		$sql = 'SELECT CAL.* ';
		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true) {
			$sql .= ',CROOMS.`id_venue` ';
		}
		$sql .='FROM `'.self::$PREFIX.'calendars` AS CAL';

		$params = null;

		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true) {
			require_once MODULES_CLASSROOM_PATH . '/include/AMAClassroomDataHandler.inc.php';
			/**
			 * must get null classrooms and venues as well, so use a LEFT JOIN here
			 */
			$sql .= ' LEFT JOIN `'.AMAClassroomDataHandler::$PREFIX.'classrooms` AS CROOMS'.
					' ON CAL.id_classroom = CROOMS.id_classroom';
		}

		$sql .= ' WHERE 1';

		if ($start>0 && $end>0) {
			$sql .= ' AND (start>=? AND end<=?)';
			$params = array ($start, $end);
		}

		if (!is_null($course_instance_id)) {
			if (!is_array($course_instance_id)) $course_instance_id = array($course_instance_id);
			$sql .= ' AND CAL.`id_istanza_corso` IN('.implode(',',$course_instance_id).')';
		}

		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true && !is_null($venueID)) {
			$sql .= ' AND CROOMS.`id_venue`=?';
			if (is_null($params)) $params = array();
			$params[] = intval($venueID);
		}


		$result = $this->getAllPrepared($sql,$params,AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($result) && count($result)>0) {
			foreach ($result as $aResult) {
				$retArray[$aResult[self::$PREFIX.'calendars_id']] = $aResult;
			}
			return $retArray;
		} else return array();
	}

	/**
	 * deletes a class room events by its id
	 *
	 * @param number $eventID
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function deleteClassroomEvent($eventID) {
		return $this->queryPrepared('DELETE FROM `'.self::$PREFIX.'calendars` WHERE '.
				self::$PREFIX.'calendars_id=?',$eventID);
	}

	/**
	 * saves one single classroom event array
	 *
	 * @param number $course_instance_id
	 * @param array $eventData
	 *
	 * @return AMA_Error on failure|updated element id (zero if it's a newly inserted element)
	 *
	 * @access private
	 */
	private function saveClassroomEvent ($course_instance_id, $eventData) {
		/**
		 * prepare start timestamp
		 */
		list ($date,$time) = explode('T',$eventData['start']);
		list ($year, $month, $day) = explode('-', $date);

		$startTimestamp = $this->date_to_ts($day.'/'.$month.'/'.$year, $time);
		/**
		 * prepare end timestamp
		 */
		list ($date,$time) = explode('T',$eventData['end']);
		list ($year, $month, $day) = explode('-', $date);

		$endTimestamp = $this->date_to_ts($day.'/'.$month.'/'.$year, $time);

		/**
		 * set classroom to null if no module classroom is there
		 */
		if (strlen($eventData['classroomID'])<=0 || !defined('MODULES_CLASSROOM') || (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===false)) {
			$eventData['classroomID'] = null;
		}

		$values = array ($startTimestamp, $endTimestamp, $course_instance_id, $eventData['classroomID'], $eventData['tutorID']);

		if (isset($eventData['id']) && strlen($eventData['id'])>0) {
			$isInsert = false;
			$sql = 'UPDATE `'.self::$PREFIX.'calendars` SET start=?, end=?, '.
					'id_istanza_corso=?, id_classroom=?, id_utente_tutor=? WHERE '.self::$PREFIX.'calendars_id=?';
			array_push($values, intval($eventData['id']));
		} else {
			$isInsert = true;
			// null is passed to generate a new autoincrement
			$sql = 'INSERT INTO `'.self::$PREFIX.'calendars` VALUES(null,?,?,?,?,?)';
		}

		$result = $this->queryPrepared($sql,$values);

		if (!AMA_DB::isError($result)) {
			// not error, return last updated id or inserted
			return ($isInsert ? $this->db->lastInsertId() : $eventData['id']);
		} else return $result;
	}

	/**
	 * find the closest class room event for the passed tutor and instance.
	 * Used to make the roll call page
	 *
	 * @param number $tutor_id
	 * @param number $course_instance_id
	 */
	public function findClosestClassroomEvent ($tutor_id,$course_instance_id=null) {
		if (strlen($tutor_id)>0 && is_numeric($tutor_id)) {
			$params = array ($tutor_id);
			$sql = 'SELECT *,TIMESTAMPDIFF(SECOND,NOW(),FROM_UNIXTIME(end)) AS endseconds'.
					' FROM `'.self::$PREFIX.'calendars` WHERE `id_utente_tutor`=?';
					if (!is_null($course_instance_id) && strlen($course_instance_id)>0 && is_numeric($course_instance_id)) {
						$sql .= ' AND `id_istanza_corso`=?';
						$params[] = $course_instance_id;
					}
					// date is today
					$sql .= ' AND DATE(FROM_UNIXTIME(start))>=CURDATE() AND'.
					// end time is less than now
					' (TIMESTAMPDIFF(SECOND,NOW(),FROM_UNIXTIME(end)))>=0'.
					// order by endseconds ASC and get the first
					// row only to get the event that ends closest to now
					' ORDER BY endseconds ASC';

			return $this->getRowPrepared($sql,$params,AMA_FETCH_ASSOC);

		} else return null;
	}

	/**
	 * saves roll call enter or exit time for a student and the passed calendar
	 *
	 * @param number $id_student
	 * @param number $id_calendar
	 * @param boolean $isEntering
	 *
	 * @return boolean true on success
	 *
	 * @access public
	 */
	public function saveRollCallEnterExit ($id_student, $id_calendar, $isEntering) {
		if ($isEntering) {
			$sql = 'INSERT INTO `'.self::$PREFIX.'rollcall` (`id_utente_studente`,'.
				   '`'.self::$PREFIX.'calendars_id`,`entertime`) VALUES (?,?,?)';

			$params = array ($id_student,$id_calendar,$this->date_to_ts('now'));
		} else {

			$sql = 'UPDATE `'.self::$PREFIX.'rollcall` SET `exittime`=? WHERE '.
				   '`id_utente_studente`=? AND `'.self::$PREFIX.'calendars_id`=? AND ISNULL(`exittime`)';

			$params = array ($this->date_to_ts('now'),$id_student,$id_calendar);
		}

		return !AMA_DB::isError($this->queryPrepared($sql,$params));
	}

	/**
	 * gets roll call detail about a student for the passed calendar
	 *
	 * @param number $id_student
	 * @param number $id_calendar
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function getRollCallDetails ($id_student, $id_calendar) {
		$sql = 'SELECT `entertime`,`exittime` FROM `'.self::$PREFIX.'rollcall`'.
			   ' WHERE `id_utente_studente`=? AND `'.self::$PREFIX.'calendars_id`=? ORDER BY `entertime` ASC';

		return $this->getAllPrepared($sql,array($id_student, $id_calendar),AMA_FETCH_ASSOC);
	}

	/**
	 * gets roll call detail about a student for the passed course instance
	 *
	 * @param number $id_student
	 * @param number $id_course_instance
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function getRollCallDetailsForInstance ($id_student, $id_course_instance) {
		$sql = 'SELECT RC.`'.self::$PREFIX.'rollcall_id`, RC.`'.self::$PREFIX.'calendars_id`,'.
			   ' RC.`entertime`,RC.`exittime` FROM '.
			   ' `'.self::$PREFIX.'rollcall` AS RC JOIN `'.self::$PREFIX.'calendars` AS CAL'.
			   ' ON RC.`'.self::$PREFIX.'calendars_id` = CAL.`'.self::$PREFIX.'calendars_id`'.
			   ' WHERE `id_utente_studente` = ? AND `id_istanza_corso`=? ORDER BY RC.`entertime` ASC';

		return $this->fixExitTimes(
			$id_course_instance,
			$this->getAllPrepared($sql,array($id_student, $id_course_instance), AMA_FETCH_ASSOC)
		);
	}

	/**
	 * set the exittime of the passed rollcall elements to the calendar event 'end' time
	 * only if the exittime is null and the calendar event ends in the past
	 *
	 * @param integer $id_course_instance
	 * @param array $dataArr
	 * @return array
	 */
	private function fixExitTimes($id_course_instance = 0, array $dataArr = []) {
		$events = null;
		$sql = 'UPDATE `'.self::$PREFIX.'rollcall` SET `exittime`=? WHERE `'.self::$PREFIX.'rollcall_id`=?';
		foreach($dataArr as $i => $element) {
			if (is_null($element['exittime'])) {
				if (is_null($events)) {
					$events = $this->getClassRoomEventsForCourseInstance($id_course_instance, null);
				}
				if (array_key_exists($element[self::$PREFIX.'calendars_id'], $events) && time() >= $events[$element[self::$PREFIX.'calendars_id']]['end']) {
					$result = $this->queryPrepared($sql, [
						$events[$element[self::$PREFIX.'calendars_id']]['end'] ,
						$element[self::$PREFIX.'rollcall_id'],
					 ]);
					if (!AMA_DB::isError($result)) {
						$dataArr[$i]['exittime'] = $events[$element[self::$PREFIX.'calendars_id']]['end'];
					}
				}
			}
		}
		return $dataArr;
	}

	/**
	 * checks if the passed tutor has already an event scheduled between the passed timestamps
	 *
	 * @param unknown $startTS start timestamp
	 * @param unknown $endTS end timestamp
	 * @param unknown $tutorID tutor ID
	 * @param unknown $eventID event ID
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function checkTutorOverlap($startTS, $endTS, $tutorID, $eventID) {

		$params = array (':tutorID'=>$tutorID);
		$sql = 'SELECT * FROM `'.self::$PREFIX.'calendars` WHERE id_utente_tutor= :tutorID';

		if (!is_null($eventID)) {
			$sql .= ' AND `'.self::$PREFIX.'calendars_id` != :eventID';
			$params = array_merge($params, array(':eventID'=>$eventID));
		}

		$sql .=' AND (`start`= :startTS OR `end`= :endTS OR '.
					 '`start`= :endTS OR `end`= :startTS OR '.
					 '(:startTS <=`start` AND :endTS >=`start`) OR '.
					 '(:startTS >=`start` AND :startTS <=`end`))';

		$params = array_merge($params, array(':startTS'=>$startTS, ':endTS'=>$endTS));

		return $this->getRowPrepared($sql, $params, AMA_FETCH_ASSOC);
	}

	/**
	 * Save a reminder html for the passed event
	 *
	 * @param number $eventID the eventID associated with the text
	 * @param string $html the html to be saved
	 *
	 * @return number last instert id|AMA_Error object on error
	 *
	 * @access public
	 */
	public function saveReminderForEvent($eventID, $html) {
		$isUpdate = false;
		$sql = 'INSERT INTO `'.self::$PREFIX.'reminder_history` (`'.
				self::$PREFIX.'calendars_id`, `html`, `creation_date`) VALUES (?,?,?)';
		$sqlParams = [
			$eventID,
			$html,
			$this->date_to_ts('now'),
		];

		if (!MODULES_CLASSAGENDA_EMAIL_REMINDER) {
			// check if a reminder is there
			$evData  = $this->getReminderForEvent($eventID);
			if (!AMA_DB::isError($evData) && $evData!==false) {
				$isUpdate = true;
				$sql = 'UPDATE `'.self::$PREFIX.'reminder_history` SET `html`=?, `creation_date`=?  WHERE `'.self::$PREFIX.'reminder_history_id`=?';
				$sqlParams = [
					$html,
					$this->date_to_ts('now'),
					$evData['id']
				];
			}
		}
		$result = $this->queryPrepared($sql, $sqlParams);

		if (!AMA_DB::isError($result)) {
			// not error, return last updated id or zero
			return $isUpdate ? true : $this->db->lastInsertID();
		} else return $result;
	}

	/**
	 * gets a reminder table row for the passed eventid
	 *
	 * @param number $eventID
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function getReminderForEvent($eventID) {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'reminder_history` WHERE `'.self::$PREFIX.'calendars_id`=? ORDER BY `creation_date` DESC';
		$retval = $this->getRowPrepared($sql,$eventID,AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($retval) && $retval!==false) {
			$retval['id'] = $retval[self::$PREFIX.'reminder_history_id'];
			$retval['date'] = ts2dFN($retval['creation_date']);
			$retval['time'] = substr(ts2tmFN($retval['creation_date']), 0, -3); // remove seconds from time
		}
		return $retval;
	}

	/**
	 * gets the html of the most recent reminder history row
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function getLastEventReminderHTML($eventID) {
		$sql = 'SELECT `html` FROM `'.self::$PREFIX.'reminder_history` WHERE `'.self::$PREFIX.'calendars_id`=? ORDER BY `creation_date` DESC';
		return $this->getOnePrepared($sql, $eventID);
	}

	/**
	 * gets all non-user related data to be used when
	 * substituting the placeholders in the reminder html
	 *
	 * @param number $reminderID
	 *
	 * @return Ambigous <mixed, string, unknown>
	 *
	 * @access public
	 */
	public function getReminderDataToEmail ($reminderID) {
		/**
		 * get first block of needed data:
		 * - html of the reminder
		 * - start timestamp
		 * - end timestamp
		 * - id_classroom
		 * - instance title and id
		 * - course title and id
		 * - tutor name
		 * - tutor lastname
		 */

		$sql = 'SELECT RH.`html`, CAL.`start`, CAL.`end`, CAL.`id_classroom`,'.
				' IST.`title` AS `instancename`, IST.`id_istanza_corso`, MCO.`titolo` AS `coursename`,'.
				' MCO.`id_corso` AS `id_course`, IST.`id_istanza_corso` AS `id_course_instance`,'.
			    ' USER.`nome` AS `tutorname`, USER.`cognome` AS `tutorlastname`'.
			   	' FROM `'.self::$PREFIX.'reminder_history` RH '.
				' JOIN `'.self::$PREFIX.'calendars` CAL ON RH.`'.self::$PREFIX.'calendars_id` = CAL.`'.self::$PREFIX.'calendars_id`'.
				' JOIN `istanza_corso` IST ON CAL.`id_istanza_corso`=IST.`id_istanza_corso`'.
				' JOIN `modello_corso` MCO ON IST.`id_corso`=MCO.`id_corso`'.
				' JOIN `utente` AS USER ON CAL.`id_utente_tutor`=USER.`id_utente`'.
				' WHERE RH.`'.self::$PREFIX.'reminder_history_id`=?';

		$result = $this->getRowPrepared($sql,$reminderID,AMA_FETCH_ASSOC);

		$result['eventdate'] = ts2dFN($result['start']);
		$result['eventstart'] = substr(ts2tmFN($result['start']), 0, -3);
		$result['eventend'] = substr(ts2tmFN($result['end']), 0, -3);

		if (!AMA_DB::isError($result) && defined('MODULES_CLASSROOM') && MODULES_CLASSROOM &&
			!is_null($result['id_classroom']) && intval($result['id_classroom'])>0) {
			/**
			 * get data about classroom and venue
			 */
			require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
			$classroomAPI = new classroomAPI();
			$classroomresult = $classroomAPI->getClassroom(intval($result['id_classroom']));

			if (!AMA_DB::isError($classroomresult)) {
				$result['classroomname'] = isset($classroomresult['name']) ? $classroomresult['name'] : '';
				if (isset($classroomresult['id_venue']) && !is_null($classroomresult['id_venue'])) {
					$venueresult = $classroomAPI->getVenue($classroomresult['id_venue']);
					if (!AMA_DB::isError($venueresult)) {
						$result['venuename'] = isset($venueresult['name']) ? $venueresult['name'] : '';
						$result['venueaddress'] = isset($venueresult['addressline1']) ? $venueresult['addressline1'] : '';
						$result['venueaddress'] .= isset($venueresult['addressline2']) ? ' - '.$venueresult['addressline2'] : '';
						$result['venuemaplink'] = isset($venueresult['map_url']) ? $venueresult['map_url'] : '';
					}
				}
			}
		}
		return $result;
	}

	/**
	 * gets instance calendar export data
	 *
	 * @param number $instanceID
	 *
	 * @return array|AMA_Error
	 *
	 * @access public
	 */
	public function getInstanceFullCalendar ($instanceID) {

		$sql =  'SELECT CAL.*, USER.`nome`,USER.`cognome`'.
				' FROM  `'.self::$PREFIX.'calendars` AS CAL'.
				' LEFT JOIN `utente` AS USER ON CAL.`id_utente_tutor`=USER.`id_utente`'.
				' WHERE `id_istanza_corso`=?';

		$retres = $this->getAllPrepared($sql,$instanceID,AMA_FETCH_ASSOC);
		$retval = array();
		if (!AMA_DB::isError($retres)) {
			foreach ($retres as $i=>$result) {
				$retval[$i]['date'] = ts2dFN($result['start']);
				$retval[$i]['eventstart'] = substr(ts2tmFN($result['start']), 0, -3);
				$retval[$i]['eventend'] = substr(ts2tmFN($result['end']), 0, -3);
				$retval[$i]['tutor'] = $result['nome'].' '.$result['cognome'];

				if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
					/**
					 * get data about classroom and venue
					 */
					require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
					$classroomAPI = new classroomAPI();
					$classroomresult = $classroomAPI->getClassroom(intval($result['id_classroom']));
					if (!is_null($result['id_classroom']) && intval($result['id_classroom'])>0) {
						if (!AMA_DB::isError($classroomresult)) {
							$retval[$i]['classroomname'] = isset($classroomresult['name']) ? $classroomresult['name'] : '';
							if (isset($classroomresult['id_venue']) && !is_null($classroomresult['id_venue'])) {
								$venueresult = $classroomAPI->getVenue($classroomresult['id_venue']);
								if (!AMA_DB::isError($venueresult)) {
									$retval[$i]['venuename'] = isset($venueresult['name']) ? $venueresult['name'] : '';
									$retval[$i]['venueaddress'] = isset($venueresult['addressline1']) ? $venueresult['addressline1'] : '';
									$retval[$i]['venueaddress'] .= isset($venueresult['addressline2']) ? ' - '.$venueresult['addressline2'] : '';
								}
							}
						}
					}
					if (!isset($retval[$i]['classroomname'])) $retval[$i]['classroomname'] = '-';
					if (!isset($retval[$i]['venuename'])) $retval[$i]['venuename'] = '-';
					if (!isset($retval[$i]['venueaddress'])) $retval[$i]['venueaddress'] = '-';
				}
			}
		}

		return ((count($retval)>0) ? $retval : new AMA_Error(AMA_ERR_GET));

	}

}
