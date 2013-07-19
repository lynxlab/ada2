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

// MODULE's OWN IMPORTS
require_once dirname(__FILE__).'/config/config.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/forms/formImport.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/importHelper.class.inc.php';

$self = 'form';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'  && !empty($_POST))
{
	
	$importHelper = new importHelper($_POST);
	$result = $importHelper->runImport();
	
	if (AMA_DB::isError($result))
	{
		$data = translateFN("ERRORE NELL'IMPORTAZIONE: ").$result->errorMessage();
	}
	else
	{
		$data = "<h3>".translateFN('RISULTATI IMPORTAZIONE')."</h3>";
		$str = "";
		foreach ($result as $courseId=>$importedItems)
		{
			$str .= "<br/>".translateFN('IL CORSO &EGRAVE; STATO CREATO CON id:').$courseId;
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
		echo $data;
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
		$form2 = new FormSelectAuthorForImport('importStep2Form', $authors);

		$step1DIV = CDOMElement::create('div','class:importFormStep1');
		$step1DIV->addChild (new CText($form1->getHtml()));

		$step2DIV = CDOMElement::create('div','class:importFormStep2');
		$step2DIV->setAttribute('style', 'display:none');
		
			$paragraph = CDOMElement::create('div');
			$paragraph->addChild (new CText(translateFN("File caricato per l'importazione: ")));
			$paragraph->addChild(CDOMElement::create('span','id:uploadedFileName'));
			
		$step2DIV->addChild ($paragraph);		
		$step2DIV->addChild (new CText($form2->getHtml()));
		
		
		$step3DIV = CDOMElement::create('div','class:importFormStep3');
		$step3DIV->setAttribute('style', 'display:none');		
		
			$divProgressBar = CDOMElement::create('div','id:progressbar');
				$divProgressLabel = CDOMElement::create('div','id:progress-label');			
			$divProgressBar->addChild ($divProgressLabel);			
		
			$divCourse =  CDOMElement::create('div','class:currentCourse');
			$divCourse->addChild (new CText(translateFN('Corso:').'&nbsp;'));
				$spanCourse = CDOMElement::create('span','id:coursename');
			$divCourse->addChild(new CText($spanCourse->getHtml()));
			
			$divCopyZip = CDOMElement::create('div','class:copyzip');
			$divCopyZip->addChild (new CText(translateFN('Copia files multimediali in corso')));
			$divCopyZip->setAttribute('style', 'display:none');
			
		$step3DIV->addChild($divProgressBar);	
		$step3DIV->addChild($divCourse);
		$step3DIV->addChild($divCopyZip);

		$data = $step1DIV->getHtml().$step2DIV->getHtml().$step3DIV->getHtml();
	}

}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => translateFN('Importazione corso'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT,
		MODULES_IMPEXPORT_PATH.'/js/pekeUpload.js',
		MODULES_IMPEXPORT_PATH.'/js/import.js'
);
$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		MODULES_IMPEXPORT_PATH.'/layout/pekeUpload.css'
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));

$optionsAr['onload_func'] = 'initDoc('.$maxFileSize.');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);

?>