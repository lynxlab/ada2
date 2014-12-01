<?php
/**
 * Classroom Management Class
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

/**
 * class for managing Classroom
 *
 * @author giorgio
 */
require_once MODULES_CLASSROOM_PATH . '/config/config.inc.php';

class classroomAPI {
	
	private $_dh;
	
	public function __construct() {
		if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
		$this->_dh = AMAClassroomDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	}
	
	/**
	 * gets all available venues
	 */
	public function getAllVenues() {		
		return $this->_dh->classroom_getAllVenues();
	}
	
	/**
	 * gets all available venues having at least one classroom
	 */
	public function getAllVenuesWithClassrooms() {
		return $this->_dh->getJoined(
			array(
					AMAClassroomDataHandler::$PREFIX.'venues' => array(
							'fields'=>array('id_venue','name'),
							'join_field'=>'id_venue'
					),			
					AMAClassroomDataHandler::$PREFIX.'classrooms' => array(
							'fields'=>array(),
							'join_field'=>'id_venue'
							)
			), null, 'name ASC'
		);
	}
	
	/**
	 * gets list of classrooms associated to the passed venue
	 * 
	 * @param number $id_venue
	 */
	public function getClassroomsForVenue($id_venue) {
		return $this->_dh->classroom_getClassroom(null,$id_venue);
	}
	
	public function __destruct() {		
		$this->_dh->disconnect();
	}	
} // class ends here