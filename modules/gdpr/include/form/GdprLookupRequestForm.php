<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

use Ramsey\Uuid\Uuid;

/**
 * Class for the gpdr request form
 *
 * @author giorgio
 */
class GdprLookupRequestForm extends GdprAbstractForm {

	public function __construct($formName=null, $action=null) {
		parent::__construct($formName, $action);
		if (!is_null($formName)) {
			$this->setId($formName);
			$this->setName($formName);
		}
		if (!is_null($action)) $this->setAction($action);

		$f = $this->addTextInput('requestUUID', translateFN('ID pratica da cercare'))
			 ->setRequired()->setValidator(\FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$f->setAttribute('maxlength', strlen(Uuid::NIL));

		$captchaDIV = \CDOMElement::create('div','class:captchacontainer ui two column relaxed grid basic segment');

		$captchaleft = \CDOMElement::create('div','class:column');

		$captchaLbl = \CDOMElement::create('label','for:checktxt');
		$captchaLbl->addChild(new \CText(translateFN('Inserisci il codice di controllo').' (*)'));
		$captchaInput = \CDOMElement::create('text','id:checktxt,name:checktxt');
		$captchaleft->addChild($captchaLbl);
		$captchaleft->addChild($captchaInput);

		$captcharight = \CDOMElement::create('div','class:center aligned column');
		$captcharight->addChild(\CDOMElement::create('img','id:checkimg,class:ui large image'));
		$captchareload = \CDOMElement::create('i','id:checkimgreload,class:green refresh large icon');
		$captchareload->setAttribute('onclick', 'loadCaptcha(\'checkimg\');');
		$captcharight->addChild($captchareload);
		$captchaerror = \CDOMElement::create('div','id:checkimg_error,class:ui error message');
		$captchaerror->addChild(new \CText("Impossibile caricare l'immagine del codice"));
		$captcharight->addChild($captchaerror);

		$captchaDIV->addChild($captchaleft);
		$captchaDIV->addChild($captcharight);

		$this->addCDOM($captchaDIV);
	}
}
