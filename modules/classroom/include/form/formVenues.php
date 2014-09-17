<?php
/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling Venues
 *
 * @author giorgio
 */
class FormVenues extends FForm {

	public function __construct($data, $formName=null, $action=null) {
		parent::__construct();
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('id_venue');
		
		$this->addTextInput('name', translateFN('Nome'))->setRequired()
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addTextInput('addressline1', translateFN('Indirizzo').' '.translateFN('riga').' 1');
		$this->addTextInput('addressline2', translateFN('Indirizzo').' '.translateFN('riga').' 2');
		
		$this->addTextInput('contact_name', translateFN('Nominativo di contatto'));
		$this->addTextInput('contact_phone', translateFN('Telefono del contatto'));
		$this->addTextInput('contact_email', translateFN('E-Mail del contatto'));
		
		$this->addTextInput('map_url', translateFN('Link alla mappa'));
		
		$this->fillWithArrayData($data);
	}
} // class ends here
