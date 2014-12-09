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
	
	/**
	 * return the html with the classroom's facilities images
	 * 
	 * @param array $classroomAr
	 * 
	 * @return CDOMElement|NULL
	 * 
	 * @access public
	 */
	public function getFacilitesHTML ($classroomAr) {
		$commonIconClass = '';
		
		if (intval($classroomAr['internet'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
					'globe.png,class:'.$commonIconClass.',title:'.translateFN('Internet'));
		}
		if (intval($classroomAr['wifi'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
					'wifi.png,class:'.$commonIconClass.',title:'.translateFN('Wi-Fi'));
		}
		if (intval($classroomAr['projector'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
					'projector.png,class:'.$commonIconClass.',title:'.translateFN('Proiettore'));
		}
		if (intval($classroomAr['mobility_impaired'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
					'wheelchair.png,class:'.$commonIconClass.',title:'.translateFN('Accesso disabili'));
		}
		
		if (isset($facilities) && count($facilities)>0) {
			$divFacilities = CDOMElement::create('div','class:facilities');
			foreach ($facilities as $facility) $divFacilities->addChild($facility);
			return $divFacilities;
		} else {
			return null;
		}
	}
	
	public function __destruct() {		
		$this->_dh->disconnect();
	}	
} // class ends here