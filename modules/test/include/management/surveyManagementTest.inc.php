<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class SurveyManagementTest extends RootManagementTest {
	/**
	 * survey constructor that calls parent constructor
	 *
	 * @param string $action string that represent the action to execute 'add', 'mod' or 'del'
	 * @param int $id node id
	 */
	public function __construct($action,$id=null) {
		parent::__construct($action,$id);
		$this->mode = ADA_TYPE_SURVEY;
		$this->what = translateFN('sondaggio');		
	}
}
