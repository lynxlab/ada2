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
require_once MODULES_CLASSBUDGET_PATH . '/include/classbudgetAPI.inc.php';

$self = 'classbudget';

$GLOBALS['dh'] = AMAClassbudgetDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

 if (isset($_GET['export']) && in_array($_GET['export'], abstractClassbudgetManagement::$exportFormats)) {
 	$export = $_GET['export'];
 } else $export = false;
 
 $help = translateFN('Gestione Budget e Costi per il corso').': '.$courseObj->getTitle().' - '.
 	 	 translateFN('Classe').': '.$courseInstanceObj->getTitle();
 
$data = '';
$totalcost = 0;
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
			$totalcost += $obj->getGrandTotal();
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
		$data .= CDOMElement::create('div','class:clearfix')->getHtml();
	} else {
		$div = CDOMElement::create('div','class:budgeterrorcontainer');
		$div->addChild(new CText(translateFN('Prima di poter calcolare i costi bisogna creare almeno un evento per la classe')));
		$data .= $div->getHtml();
	}
}

$budgetAPI = new classbudgetAPI();
$budgetObj = $budgetAPI->getBudgetCourseInstance($courseInstanceObj->getId());
$budget = (isset($budgetObj->budget)) ? floatval($budgetObj->budget) : 0.00;
$balance = $budget - $totalcost;

$balanceclass = ($balance>=0) ? 'balancegreen' : 'balancered';

$budgetStr = number_format($budget,ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP);
$totalcostStr = number_format($totalcost,ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP);
$balanceStr = number_format($balance,ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP);

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => '',
		'help' => isset($help) ? $help : '',
		'data' => isset($data) ? $data : '',
		'currency' => ADA_CURRENCY_SYMBOL,
		'budgetStr' => $budgetStr,
		'totalcostStr' => $totalcostStr,
		'balanceStr' => $balanceStr,
		'balanceclass' => $balanceclass,
		'budget' => $budget,
		'totalcost' => $totalcost,
		'balance' => $balance
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
	// build a resume array
	$resumeArr = array (
			array (translateFN('Budget'), $budgetStr),
			array (translateFN('Costo totale'), $totalcostStr),
			array (translateFN('Differenza'), $balanceStr),
			array()
	);
	// put it as first exported data element
	array_unshift($exportData, $resumeArr);
	$out = fopen('php://output', 'w');
	foreach ($exportData as $section) foreach ($section as $row) fputcsv($out, $row);
	fclose($out); 
} else {
	$menuOptions['id_course'] = $courseObj->getId();
	$menuOptions['id_course_instance'] = $courseInstanceObj->getId();
	ARE::render($layout_dataAr, $content_dataAr, $render, $optionsAr, $menuOptions);
}
?>
