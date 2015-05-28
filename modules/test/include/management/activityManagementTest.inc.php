<?php
/**
 * @package test
 * @author	giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class ActivityManagementTest extends RootManagementTest {
	/**
	 * test constructor that calls parent constructor
	 *
	 * @param string $action string that represent the action to execute 'add', 'mod' or 'del'
	 * @param int $id node id
	 */
	public function __construct($action,$id=null) {
		parent::__construct($action,$id);
		$this->mode = ADA_TYPE_ACTIVITY;
		$this->what = translateFN('activity');
	}
}