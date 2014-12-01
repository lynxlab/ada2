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
	 * @param asrray $eventsArray
	 * 
	 * @return AMA_Error on failure|boolean true on success
	 * 
	 * @access public
	 */
	public function saveClassroomEvents ($course_instance_id, $venueID, $eventsArray) {
		/**
		 * get all the classroom events for the passed instance
		 */
		$previousEvents = $this->getClassRoomEventsForCourseInstance ($course_instance_id, $venueID);
		if (AMA_DB::isError($previousEvents)) $previousEvents = array();
		
		foreach ($eventsArray as $event) {
			$eventID = $this->saveClassroomEvent($course_instance_id, $event);
			if (!AMA_DB::isError($eventID) && intval($eventID)>0) {
				// event has been updated, remove it from the previous events array
				if (array_key_exists($eventID, $previousEvents)) unset ($previousEvents[$eventID]);
			} else if (AMA_DB::isError($eventID)) {
				// on error return right away
				return $eventID;
			}
		}

		/**
		 * what is left in the previous events array must be delete
		 */
		foreach ($previousEvents as $eventID=>$anEvent) {
		    $this->deleteClassroomEvent($eventID);
		}
		
		return true;
	}
	
	/**
	 * gets all the classroom events for the passed instance and venue
	 * 
	 * @param number $course_instance_id
	 * @param number $venueID
	 * 
	 * @return array classroom events or empty
	 * 
	 * @access public
	 */
	public function getClassRoomEventsForCourseInstance($course_instance_id, $venueID) {
		$sql = 'SELECT CAL.* FROM `'.self::$PREFIX.'calendars` AS CAL';
		
		$params = null;
		
		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true && !is_null($venueID)) {
			require_once MODULES_CLASSROOM_PATH . '/include/AMAClassroomDataHandler.inc.php';
			
			$sql .= ' JOIN `'.AMAClassroomDataHandler::$PREFIX.'classrooms` AS CROOMS'.
					' ON CAL.id_classroom = CROOMS.id_classroom';
		}
		
		$sql .= ' WHERE 1';
		
		if (!is_null($course_instance_id)) {
			if (!is_array($course_instance_id)) $course_instance_id = array($course_instance_id);
			$sql .= ' AND CAL.`id_istanza_corso` IN('.implode(',',$course_instance_id).')';
		}
		
		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===true && !is_null($venueID)) {
			$sql .= ' AND CROOMS.`id_venue`=?';
			$params = intval($venueID);
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
		if (!defined('MODULES_CLASSROOM') || (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM===false)) {
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
			// not error, return last updated id or zero
			return ($isInsert ? 0 : $eventData['id']);
		} else return $result;
	}

	/**
	 * Returns an instance of AMAClassagendaDataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMAClassagendaDataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMAClassagendaDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}
	
}
?>
