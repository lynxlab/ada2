<?php
/**
 * IMPORT MODULE
 *
 * @package		export/import course
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			impexport
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling file upload module form
 *
 * @author giorgio
 */
class FormUploadImportFile extends FForm {

	public function __construct( $formName ) {
		parent::__construct();
		$this->setName($formName);
		$this->addFileInput('importfile', translateFN ('Seleziona un file .zip da importare'));
	}
}

/**
 * class for handling author assignment form
 *
 * @author giorgio
 */
class FormSelectAuthorForImport extends FForm {

	public function __construct( $formName, $authorsList ) {
		parent::__construct();
		
		$authorsList[0] = translateFN('Scegli un autore per il corso');
		
		$this->setName($formName);
		
		$this->addHidden('importFileName');
		
		$this->addSelect('author', translateFN ("Seleziona l'autore a cui assegnare il corso importato"), $authorsList, 0)
			->setRequired()
			->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);	

		$this->setOnSubmit('return goToImportStepThree();');
	}
}