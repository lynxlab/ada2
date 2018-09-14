<?php
/**
 * @package 	secretquestion module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

require_once ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php';

/**
 * Class for the secret question form
 *
 * @author giorgio
 */
class SecretQuestionForm extends FForm {

	/**
	 * constructor
	 *
	 * @param boolean $isRegistration
	 * @param boolean $isAskQuestion
	 */
	public function __construct($isRegistration = false, $isAskQuestion = false) {
		parent::__construct();

		if (!$isAskQuestion) {
			$this->addTextInput('uname', 'username')
				 ->setRequired()->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
				 ->setAttribute('readonly', 'readonly');
			if ($isRegistration) {
				// if username is null, it's the registration form itself, not in editProfileForm
				$this->addPasswordInput('upass', 'password')
					->setRequired()->setValidator(FormValidator::PASSWORD_VALIDATOR);
				$passtext = CDOMElement::create('span','class:pass-validator-text');
				$passtext->addChild(new CText(translateFN('La password deve essere lunga minimo 8 e massimo 20 caratteri'.
				' e può contenere solo cifre, lettere maiuscole o minuscole ed il trattino basso \'_\'')));
				$this->addCDOM($passtext);
			}
		} else {
			$this->setName('askquestion');
		}

		$q = $this->addTextInput('secretquestion', translateFN('Domanda segreta per il recupero della password'));
		$a = $this->addTextInput('secretanswer', translateFN('Risposta alla domanda segreta'));

		if (!$isAskQuestion) {
			$q->setRequired()->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		} else {
			$this->addHidden('userId');
		}
		if (!$isRegistration && !$isAskQuestion) {
			$q->setAttribute('title',translateFN('La domanda verrà modificata solo se si inserisce una risposta non vuota'));
		} else {
			$a->setRequired()->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
			if ($isAskQuestion) $q->setAttribute('readonly','readonly');
		}
	}

	/**
	 * Adds the SecretQuestionForm controls to the passed form, without building a new form
	 *
	 * @param FForm $theForm
	 * @return FForm
	 */
	public function addControlsToForm(FForm $theForm) {
		array_map(function($control) use ($theForm) {
			if ($control instanceof \FormControl) {
				$theForm->addControl($control);
			} else if ($control instanceof \CBaseAttributesElement) {
				$theForm->addCDOM($control);
			}
		}, $this->getControls());
		return $theForm;
	}

}
