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
require_once MODULES_CLASSROOM_PATH . '/include/management/abstractClassroomManagement.inc.php';
require_once MODULES_CLASSROOM_PATH . '/include/form/formClassrooms.php';

class classroomManagement extends abstractClassroomManagement
{
	public $id_classroom;
	public $id_venue;
	public $venue_name;
	public $name;
	public $seats;
	public $computers;
	public $internet;
	public $wifi;
	public $projector;
	public $mobility_impaired;
	public $hourly_rate;
    
	/**
	 * build, manage and display the module's pages
	 *
	 * @return array
	 * 
	 * @access public
	 */
	public function run($action=null) {
		/* @var $html	string holds html code to be retuned */
		$htmlObj = null;
		/* @var $path	string  path var to render in the help message */
		$help = translateFN('Da qui puoi inserire o modifcare le aule in cui si terranno i corsi');
		/* @var $status	string status var to render in the breadcrumbs */
		$title= translateFN('Aule');
		
		switch ($action) {
			case MODULES_CLASSROOM_EDIT_CLASSROOM:
				/**
				 * edit action, display the form with passed data
				 */
				$htmlObj = new FormClassrooms($this->toArray(),'editClassRoomForm','ajax/edit_classroom.php');
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
} // class ends here