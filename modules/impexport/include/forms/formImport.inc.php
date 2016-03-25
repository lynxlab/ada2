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

		$importURL = FormControl::create(FormControl::INPUT_TEXT, 'importURL', translateFN('URL per l\'importazione'));
		$importURLBtn = FormControl::create(FormControl::INPUT_BUTTON, 'importUrlBtn', translateFN('Carica da URL e importa'));

		// creare il fieldset con i campi appena creati
		$this->addFieldset(translateFN('oppure inserisci una URL da cui importare'),'importUrlFSet')->withData(array ($importURL, $importURLBtn));
	}
}

/**
 * class for handling author assignment form
 *
 * @author giorgio
 */
class FormSelectDatasForImport extends FForm {

	public function __construct( $formName, $authorsList, $courseList ) {
		parent::__construct();

		$authorsList[0] = translateFN('Scegli un autore per il corso');

		$courseList[0] = translateFN('Importa come nuovo corso');

		$this->setName($formName);

		$this->addHidden('importFileName');

		$this->addSelect('author', translateFN ("Seleziona l'autore a cui assegnare il corso importato"), $authorsList, 0)
			->setRequired()
			->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

		$this->addSelect('courseID', translateFN ("Seleziona il corso in cui importare"), $courseList, 0)
			->setRequired()
			->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);

		if(isset($_SESSION['service_level'])){
			/**
			 * @author giorgio 06/mag/2015
			 *
			 * switcher can add public courses only if:
			 * - it's a multiprovider having session tester equals to PUBLIC tester
			 * - it's not multiprovider
			 */
			$shownServiceTypes = array();
			foreach ($_SESSION['service_level'] as $key=>$val) {
				if ((bool)$_SESSION['service_level_info'][$key]['isPublic']) {
					// this coud have been an OR, but looks more readable this way
					if (MULTIPROVIDER && $_SESSION['sess_selected_tester']==ADA_PUBLIC_TESTER) {
						$shownServiceTypes[$key]=$val;
					} else if (!MULTIPROVIDER) {
						$shownServiceTypes[$key]=$val;
					}
				} else $shownServiceTypes[$key]=$val;
			}

			$desc = translateFN('Tipo di corso').':';
			$this->addSelect('service_level',$desc,$shownServiceTypes,DEFAULT_SERVICE_TYPE);
		}

		$this->setSubmitValue(translateFN('Avanti')."&nbsp;&gt;&gt;");

		$this->setOnSubmit('return goToImportSelectNode();');
	}
}