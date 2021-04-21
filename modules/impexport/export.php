<?php
/**
 * EXPORT MODULE
 *
 * @package		export/import course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		impexport
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_IMPEXPORT_PATH.'/include/exportHelper.class.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/forms/formExport.inc.php';
require_once MODULES_IMPEXPORT_PATH.'/include/forms/formExportToRepoDetails.inc.php';

$self = 'impexport';

$error = false;
$exportToRepo = isset($_GET['exporttorepo']) && intval($_GET['exporttorepo']) === 1;
$exportCourse = (isset($_GET['id_course']) && intval($_GET['id_course'])>0) ? intval($_GET['id_course']) : 0;
/**
 * load course list from the DB
 */
$providerCourses = $dh->get_courses_list (array ('nome','titolo'));

$courses = array();
foreach($providerCourses as $course) {
	$courses[$course[0]] = '('.$course[0].') '.$course[1].' - '.$course[2];
}

if (empty($courses))
{
	$data = translateFN ("Nessun corso trovato. Impossibile continuare l'esportazione");
	$error = true;
}

if (!$error)
{

	$form1 = new FormSelectExportCourse('exportStep1Form', $courses, $exportCourse);

	$step1DIV = CDOMElement::create('div','class:exportFormStep1 '. ($userObj->getType() == AMA_TYPE_SWITCHER ? 'initshown' : 'inithidden'));
	$step1DIV->addChild (new CText($form1->getHtml()));

	$step2DIV = CDOMElement::create('div','class:exportFormStep2 inithidden');
	$step2DIV->setAttribute('style', 'display:none');

	$spanHelpText = CDOMElement::create('span','class:exportStep2Help');
	$spanHelpText->addChild (new CText(translateFN('Scegli il nodo del corso che vuoi esportare.')));
	$spanHelpText->addChild (new CText(translateFN('Il nodo scelto sar&agrave; esportato con tutti i suoi figli.')));

	$courseTreeDIV = CDOMElement::create('div','id:courseTree');

	$courseTreeLoading = CDOMElement::create('span','id:courseTreeLoading');
	$courseTreeLoading->addChild (new CText(translateFN('Caricamento albero del corso').'...<br/>'));

	$spanSelCourse = CDOMElement::create('span','id:selCourse');
	$spanSelCourse->setAttribute('style', 'display:none');
	$spanSelNode = CDOMElement::create('span','id:selNode');
	$spanSelNode->setAttribute('style', 'display:none');
	$spanSelCourseDescr = CDOMElement::create('span','id:selCourseDescr');
	$spanSelCourseDescr->setAttribute('style', 'display:none');

	$buttonDIV = CDOMElement::create('div','class:step2buttons');

	$buttonPrev = CDOMElement::create('button','id:backButton');
	$buttonPrev->setAttribute('type', 'button');
	$buttonPrev->setAttribute('onclick', 'javascript:initDoc();');
	$buttonPrev->addChild (new CText('&lt;&lt;&nbsp;'.translateFN('Indietro')));

	$buttonNext = CDOMElement::create('button','id:exportButton');
	$buttonNext->setAttribute('type', 'button');

	$step3DIV = CDOMElement::create('div','class:exportFormStep3 inithidden');
	$step3DIV->setAttribute('style', 'display:none');
	$form3 = new FormExportToRepoDetails('exportStep3Form', $exportToRepo);
	$step3DIV->addChild(new CText($form3->getHtml()));

	if ($exportToRepo) {
		$buttonNext->setAttribute('onclick', 'return goToExportStepThree();');
		$buttonNext->addChild (new CText(translateFN('Avanti')."&nbsp;&gt;&gt;"));
	} else {
		$buttonNext->setAttribute('onclick', 'doExport(\'exportFormStep2\');');
		$buttonNext->addChild (new CText(translateFN('Esporta')));
	}

	$buttonDIV->addChild($buttonPrev);
	$buttonDIV->addChild($buttonNext);

	$step2DIV->addChild ($spanHelpText);
	$step2DIV->addChild ($courseTreeDIV);
	$step2DIV->addChild ($courseTreeLoading);
	$step2DIV->addChild ($spanSelCourse);
	$step2DIV->addChild ($spanSelNode);
	$step2DIV->addChild ($spanSelCourseDescr);
	$step2DIV->addChild ($buttonDIV);

	$stepExportDIV = CDOMElement::create('div','class:exportFormStepExport inithidden');
	$stepExportDIV->setAttribute('style', 'display:none');

	$exporting = CDOMElement::create('span','class:stepExportTitle');
	$exporting->addChild (new CText(translateFN('Esportazione in corso')));

	$txtSpan = CDOMElement::create('span','class:stepExportText');
	$txtSpan->addChild (new CText(translateFN('Il download si avvier&agrave; automaticamente ad esportazione ultimata')));

	$stepExportDIV->addChild($exporting);
	$stepExportDIV->addChild($txtSpan);

	$data = $step1DIV->getHtml().$step2DIV->getHtml().$step3DIV->getHtml().$stepExportDIV->getHtml();
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'label' => translateFN('Esportazione corso'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT,
		MODULES_IMPEXPORT_PATH.'/js/tree.jquery.js',
		MODULES_IMPEXPORT_PATH.'/js/export.js'
);
$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		MODULES_IMPEXPORT_PATH.'/layout/pekeUpload.css',
		MODULES_IMPEXPORT_PATH.'/layout/jqtree.css'
);


$optionsAr['onload_func'] = 'initDoc();';
if ($userObj->getType() == AMA_TYPE_AUTHOR) {
	$optionsAr['onload_func'] .= 'goToExportStepTwo();';
}

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>