<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classagenda
 * @version			0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling EventReminder
 *
 * @author giorgio
 */
class FormEventReminder extends FForm {

	public function __construct($data, $formName=null, $action=null) {
		parent::__construct();
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('reminderEventID')->withData(0);
		
		$this->addTextarea('reminderEventHTML', translateFN('Testo del promemoria'))
				->setRequired()
				->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR)
				->withData('');
		
		if (!is_null($data)) $this->fillWithArrayData($data);
	}
} // class ends here
