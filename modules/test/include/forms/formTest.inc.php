<?php
/**
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

abstract class FormTest extends FForm {
	
	protected $data;

	public function __construct($data=array()) {
		parent::__construct();
		
		$this->data = $data;

		$this->header();
		$this->content();
		$this->footer();
	}

	abstract protected function content();

	/**
	 * use it to specify a header for every child form
	 */
	protected function header() {
	}

	/**
	 * use it to specify a footer for every child form
	 */
	protected function footer() {
	}
}