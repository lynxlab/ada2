<?php
/**
 * CLASSBUDGET MODULE.
 *
 * @package        classbudget module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2015, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classbudget
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout', 'course', 'course_instance')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/switcher/include/switcher_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_CLASSBUDGET_PATH .'/config/config.inc.php';
require_once MODULES_CLASSBUDGET_PATH.'/include/AMAClassbudgetDataHandler.inc.php';
require_once MODULES_CLASSBUDGET_PATH.'/include/management/abstractClassbudgetManagement.inc.php';

$self = 'classbudget';

$GLOBALS['dh'] = AMAClassbudgetDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

/**
 * TODO: Add your own code here
 */
 
 if (isset($_GET['export']) && in_array($_GET['export'], abstractClassbudgetManagement::$exportFormats)) {
 	$export = $_GET['export'];
 } else $export = false;
 
 $help = translateFN('Gestione Budget e Costi per il corso').': '.$courseObj->getTitle().' - '.
 	 	 translateFN('Classe').': '.$courseInstanceObj->getTitle();
 
$data = '';
$somethingFound = false;
if ($export !== false) {
	if ($export === 'pdf') {
		$action = null;
		$render = ARE_PDF_RENDER;
		$GLOBALS['adafooter'] = translateFN(PDF_EXPORT_FOOTER);
		$self .= 'PDF';
	}
	else if ($export === 'csv') {
		$action = MODULES_CLASSBUDGET_CSV_EXPORT; 
		$render = ARE_FILE_RENDER;
	}
	else die (translateFN('Formato non supportato'));
} else {
	$action = MODULES_CLASSBUDGET_EDIT;
	$render = null;
}

foreach ($classBudgetComponents as $component) {
	$includeFileName = MODULES_CLASSBUDGET_PATH . '/include/management/'.$component['classname'].'.inc.php';
	if (is_file($includeFileName) && is_readable($includeFileName)) {
		require_once $includeFileName;
		if (class_exists ($component['classname'])) {
			// $id_course_instance is coming from get
			$obj = new $component['classname']($courseInstanceObj->getId());
			$html = $obj->run($action);
			$somethingFound = $somethingFound || (count($obj->dataCostsArr)>0);

			if ($export===false || $render == ARE_PDF_RENDER) {
				if (!is_null($html)) $data .= $html->getHtml();							
			} else if ($render == ARE_FILE_RENDER) {
				// store data to export
				if (!isset($exportData)) $exportData = array();
				$exportData[] = $obj->buildCostArrayForCSV();
			}
		}
	} 
}

if ($render!=ARE_PDF_RENDER) {
	if (strlen($data)>0 && $somethingFound) {
		// add buttons
		$buttonDIV = CDOMElement::create('div','id:buttonswrapper');
		$saveButton = CDOMElement::create('button','class:budgetsave');
		$saveButton->setAttribute('onclick', 'javascript:saveBudgets();');
		$saveButton->addChild(new CText(translateFN('salva')));
		$cancelButton = CDOMElement::create('button','class:budgetcancel');
		$cancelButton->setAttribute('onclick', 'javascript:self.document.location.reload();');
		$cancelButton->addChild(new CText(translateFN('annulla')));
		$buttonDIV->addChild($saveButton);
		$buttonDIV->addChild($cancelButton);
		$data .= $buttonDIV->getHtml();
	} else {
		$div = CDOMElement::create('div','class:budgeterrorcontainer');
		$div->addChild(new CText(translateFN('Prima di poter calcolare i costi bisogna creare almeno un evento per la classe')));
		$data .= $div->getHtml();
	}
}


$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => '',
		'help' => isset($help) ? $help : '',
		'data' => isset($data) ? $data : '',
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS
);

$optionsAr['onload_func'] = 'initDoc();';

if ($render === ARE_FILE_RENDER && $export==='csv') {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset='.strtolower(ADA_CHARSET));
	header('Content-Disposition: attachment; filename=Budget-'.$courseInstanceObj->getTitle().'.csv');
	$out = fopen('php://output', 'w');	
	foreach ($exportData as $section) foreach ($section as $row) fputcsv($out, $row);
	fclose($out); 
} else ARE::render($layout_dataAr, $content_dataAr, $render, $optionsAr);
?>
