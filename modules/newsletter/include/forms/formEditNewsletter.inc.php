<?php
/**
 * NEWSLETTER MODULE.
 *
 * @package		newsletter module
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			newsletter
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling export phase 1, selecting a course
 *
 * @author giorgio
*/
class FormEditNewsLetter extends FForm {

	public function __construct( $formName ) {
		parent::__construct();
		$this->setName($formName);
		
		$isDraftOptions = array ('0'=>translateFN('No'), '1'=>translateFN('Sì'));
		
		$this->addTextInput('subject', translateFN('Oggetto'))
			->setRequired()
			->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('sender', translateFN('Mittente'))
			->setRequired()
			->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('date', translateFN('Data'))
			->setRequired()
			->setValidator(FormValidator::DATE_VALIDATOR);
		
		$this->addSelect('draft', translateFN ('Bozza'), $isDraftOptions, 1)
			->setRequired()
			->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
		
		$this->addTextarea('htmltext', translateFN('Testo HTML'))
			->setRequired()
			->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
				
		$this->addTextarea('plaintext', translateFN('Testo Alternativo'))
			->setRequired()
			->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
		
		$this->setSubmitValue(translateFN('Salva'));		
		
	}
}
?>