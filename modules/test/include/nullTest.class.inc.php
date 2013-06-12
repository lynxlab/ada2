<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class NullTest extends NodeTest
{
	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
	}

	/**
	 * Returns the html string for the exercise player with a message that informs
	 * the user that this exercise is not available at the moment.
	 *
	 * @access public
	 *
	 * @param $return_html choose the return type
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering*
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return string
	 */
	public function render($return_html=true,$feedback=false,$rating=false,$rating_answer=false)	{
	}

	/**
	 * return necessaries html objects that represent the object
	 *
	 * @access protected
	 *
	 * @param $ref reference to the object that will contain this rendered object
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an object of CDOMElement
	 */
	protected function renderingHtml(&$ref = null,$feedback=false,$rating=false,$rating_answer=false) {
	}
}
