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
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

// MODULE's OWN IMPORTS
require_once dirname(__FILE__).'/config/config.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/forms/formImport.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/importHelper.class.inc.php';

$self = 'impexport';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'  && !empty($_POST))
{

	$importHelper = new importHelper($_POST);
	$result = $importHelper->runImport();

	if (AMA_DB::isError($result))
	{
		$data = translateFN("ERRORE NELL'IMPORTAZIONE: ").$result->errorMessage();

		/* only a call to the add_course data handler method should
		 * generate a duplicate record error. Shall give out a 'special' error for it
		*/
		if ($result->code == AMA_ERR_ADD || $result->code == AMA_ERR_UNIQUE_KEY)
		    $data .= '<br/>'.translateFN('Provare a modificare il campo nome del corso nel file ada_export.xml contenuto nel file .zip e riprovare.');
	}
	else
	{
		$data = "<h3>".translateFN('RISULTATI IMPORTAZIONE')."</h3>";
		$str = "";
		foreach ($result as $courseId=>$importedItems)
		{
			$str .= "<br/>".translateFN('IL CORSO &Egrave; STATO CREATO CON id:').$courseId;
			$str .= "<ul>";
			foreach ($importedItems as $type=>$count)
			{
				$str .="<li><b>".$count."</b> ".translateFN('oggetti di tipo')." <b>".$type."</b> ".
				translateFN('aggiunti')."</li>";
			}
			$str .= "</ul>";
		}
		$data .= $str;
	}

	if (isset($_POST['op']) && $_POST['op']=='ajaximport')
	{
		// if it's an ajax request, echo the html and die
		sleep (1); // if we're too fast, the jquery switching divs is going to flicker
		echo json_encode(array('html'=>$data));
		die();
	}
}
else
{
	$error = false;
	/**
	 * load authors list from the DB
	 */
	$providerAuthors = $dh->find_authors_list(array('username'), '');
	$authors = array();
	foreach($providerAuthors as $author) {
		$authors[$author[0]] = $author[1];
	}

	/**
	 * load course list from the DB
	 */
	$providerCourses = $dh->get_courses_list (array ('nome','titolo'));

	$courses = array();
	foreach($providerCourses as $course) {
		$courses[$course[0]] = '('.$course[0].') '.$course[1].' - '.$course[2];
	}

	if (empty($authors))
	{
		$data = translateFN ("Nessun autore trovato. Impossibile continuare l'importazione");
		$error = true;
	}

	if (!$error) {
		/**
		 * generate the HTML used for import steps, strictyl handled by javascript (import.js)
		 */

		/**
		 * form1 has a css class in form.css to hide the submit button
		 * should someone ever chagne its name, pls reflect change in css
		 */
		$form1 = new FormUploadImportFile('importStep1Form');
		$form2 = new FormSelectDatasForImport('importStep2Form', $authors, $courses);

		$step1DIV = CDOMElement::create('div','class:importFormStep1');
		$step1DIV->addChild (new CText($form1->getHtml()));

		$step2DIV = CDOMElement::create('div','class:importFormStep2');
		$step2DIV->setAttribute('style', 'display:none');

			$paragraph = CDOMElement::create('div');
			$paragraph->addChild (new CText(translateFN("File caricato per l'importazione: ")));
			$paragraph->addChild(CDOMElement::create('span','id:uploadedFileName'));
			$step2DIV->addChild ($paragraph);
		$step2DIV->addChild (new CText($form2->getHtml()));

		$importSelectNode = CDOMElement::create('div','class:divImportSN');
		$importSelectNode->setAttribute('style', 'display:none');

		$spanHelpText = CDOMElement::create('span','class:importSNHelp');
		$spanHelpText->addChild (new CText(translateFN('Scegli il nodo del corso che sar&agrave; genitore dei nodi importati.')));

		$courseTreeDIV = CDOMElement::create('div','id:courseTree');

		$courseTreeLoading = CDOMElement::create('span','id:courseTreeLoading');
		$courseTreeLoading->addChild (new CText(translateFN('Caricamento albero del corso').'...<br/>'));

		$spanSelCourse = CDOMElement::create('span','id:selCourse');
		$spanSelCourse->setAttribute('style', 'display:none');
		$spanSelNode = CDOMElement::create('span','id:selNode');
		$spanSelNode->setAttribute('style', 'display:none');

		$buttonDIV = CDOMElement::create('div','class:importSN2buttons');

		$buttonPrev = CDOMElement::create('button','id:backButton');
		$buttonPrev->setAttribute('type', 'button');
		$buttonPrev->setAttribute('onclick', 'javascript:returnToImportStepTwo();');
		$buttonPrev->addChild (new CText('&lt;&lt;'.translateFN('Indietro')));

		$buttonNext = CDOMElement::create('button','id:exportButton');
		$buttonNext->setAttribute('type', 'button');
		$buttonNext->setAttribute('onclick', 'javascript:goToImportStepThree();');
		$buttonNext->addChild (new CText(translateFN('Importa')));

		$buttonDIV->addChild($buttonPrev);
		$buttonDIV->addChild($buttonNext);

		$importSelectNode->addChild ($spanHelpText);
		$importSelectNode->addChild ($courseTreeDIV);
		$importSelectNode->addChild ($courseTreeLoading);
		$importSelectNode->addChild ($spanSelCourse);
		$importSelectNode->addChild ($spanSelNode);
		$importSelectNode->addChild ($buttonDIV);



		$step3DIV = CDOMElement::create('div','class:importFormStep3');
		$step3DIV->setAttribute('style', 'display:none');

			$divProgressBar = CDOMElement::create('div','id:progressbar');
				$divProgressLabel = CDOMElement::create('div','id:progress-label');
			$divProgressBar->addChild ($divProgressLabel);

			$divCourse =  CDOMElement::create('div','class:currentCourse');
			$divCourse->addChild (new CText(translateFN('Importazione dal corso:').'&nbsp;'));
				$spanCourse = CDOMElement::create('span','id:coursename');
			$divCourse->addChild(new CText($spanCourse->getHtml()));

			$divCopyZip = CDOMElement::create('div','class:copyzip');
			$divCopyZip->addChild (new CText(translateFN('Copia files multimediali in corso')));
			$divCopyZip->setAttribute('style', 'display:none');

		$step3DIV->addChild($divProgressBar);
		$step3DIV->addChild($divCourse);
		$step3DIV->addChild($divCopyZip);

		$data = $step1DIV->getHtml().$step2DIV->getHtml().$importSelectNode->getHtml().$step3DIV->getHtml();
	}

}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'label' => translateFN('Importazione corso'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT,
		MODULES_IMPEXPORT_PATH.'/js/pekeUpload.js',
		MODULES_IMPEXPORT_PATH.'/js/tree.jquery.js',
		MODULES_IMPEXPORT_PATH.'/js/import.js'
);
$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		MODULES_IMPEXPORT_PATH.'/layout/pekeUpload.css',
		MODULES_IMPEXPORT_PATH.'/layout/jqtree.css'
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));

$optionsAr['onload_func'] = 'initDoc('.$maxFileSize.');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);

?>