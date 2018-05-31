<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

/**
 * Class for the gpdr accept policies login form repeater
 *
 * this just sets a gdprAccepted flag, actual data are in session
 * because we do not want the plaintext password to be in the DOM
 *
 * @author giorgio
 */
class GdprLoginRepeaterForm extends GdprAbstractForm {

	public function __construct($formName=null, $action=null, $dataAr = array()) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}

		if (!is_null($action)) $this->setAction($action);

		$this->addHidden('gdprAccepted')->withData(1);

		if (is_array($dataAr) && count($dataAr)>0) {
			foreach ($dataAr as $key=>$value) {
				$this->addHidden(htmlentities($key))->withData(htmlentities($value));
			}
		}
	}
}
