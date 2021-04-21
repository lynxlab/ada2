<?php
/**
 * EXPORT TEST.
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

if (MODULES_TEST) {
	require_once(MODULES_TEST_PATH.'/include/AMATestDataHandler.inc.php');
}

/**
 * array of root nodes to export, can come from a form submission...!
 * the export will contain all the passed nodes AND their respective children
 * id MUST be in the ADA format: <course_id>_<node_id>
 *
 * if value associated to a key is not an array, or is an empty array
 * then all the course will be exported!
 *
 */

$exportCourse = (isset($_REQUEST['selCourse']) && (intval($_REQUEST['selCourse'])>0)) ? intval ($_REQUEST['selCourse']) : 0;
$exportNode = (isset($_REQUEST['selNode']) && (trim($_REQUEST['selNode'])!=='')) ? trim ($_REQUEST['selNode']) : '';
$exportMedia = !(isset($_REQUEST['exportMedia']) && (intval($_REQUEST['exportMedia'])>0));
$exportSurvey = !(isset($_REQUEST['exportSurvey']) && (intval($_REQUEST['exportSurvey'])>0));
$exportToRepo = isset($_REQUEST['exporttorepo']) && intval($_REQUEST['exporttorepo']) === 1;

try {
	if ($exportToRepo) {
		require_once MODULES_IMPEXPORT_PATH .'/include/AMARepositoryDataHandler.inc.php';
		$rdh = AMARepositoryDataHandler::instance();
		// build needed save data and throw exception on error
		if ($exportCourse > 0) {
			$saveData = [
				'id_course' => $exportCourse
			];
			$testerId = $rdh->getTesterIDFromPointer();
			if (!is_null($testerId)) {
				$saveData['id_tester'] = $testerId;
				if (isset($_REQUEST['repotitle']) && strlen(trim($_REQUEST['repotitle']))>0) {
					$saveData['title'] = trim($_REQUEST['repotitle']);
					if (isset($_REQUEST['repodescr']) && strlen(trim($_REQUEST['repodescr']))>0) {
						$saveData['description'] = trim($_REQUEST['repodescr']);
					} else throw new Exception(translateFN('La descrizione non può essere vuota'), 400);
				} else throw new Exception(translateFN('Il titolo non può essere vuoto'), 400);
			} else throw new Exception(translateFN('Errore lettura informazioni provider del corso', 500));
		} else throw new Exception(translateFN('Corso selezionato non valido'), 400);

	}
	if ($exportCourse>0 && $exportNode!=='') {
		$nodesToExport =  array( $exportCourse=>array($exportNode) );
	} else $nodesToExport = array();
	/**
	 * exportHelper object to help us in the exporting process...
	*/
	$exportHelper = new exportHelper($exportCourse);
	/**
	 * comment to be inserted as first line of XML document
	*/
	$commentStr = "Exported From ".PORTAL_NAME." v".ADA_VERSION;

	// create a dom document with encoding utf8
	$domtree = new DOMDocument('1.0', ADA_CHARSET);
	$domtree->preserveWhiteSpace = false;
	$domtree->formatOutput = true;

	// generate and add comment, if any
	if (isset($commentStr)){
		$domtree->appendChild($domtree->createComment($commentStr));
		unset ($commentStr);
	}

	// create the root element of the xml tree
	$xmlRoot = $domtree->createElement("ada_export");
	$xmlRoot->setAttribute("exportDate", date('r'));
	// append it to the document created
	$xmlRoot = $domtree->appendChild($xmlRoot);

	foreach ($nodesToExport as $course_id=>$nodeList)
	{
		// need an Import/Export DataHandler
		$dh = AMAImpExportDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

		$course_data = $dh->get_course ($course_id);

		if (!empty($course_data) && !AMA_DB::isError($course_data))
		{
			// create node for current course
			$XMLcourse = $domtree->createElement('modello_corso');
			$XMLcourse->setAttribute('exportedId', $course_id);
			// set course model datas
			foreach ($course_data as $name=>$value)
			{
				$name = strtolower($name);
				if ($name ==='id_autore') continue;
				else if (in_array($name, exportHelper::$cDataElementNameForCourse))
					$XMLElementForCourse = $exportHelper->buildCDATASection($domtree, $name, $value);
				else if ($name === 'id_lingua')
				{
					$XMLElementForCourse = $domtree->createElement($name,exportHelper::getLanguageTableFromID($value));
				}
				else
					$XMLElementForCourse = $domtree->createElement($name,$value);

				if (isset($XMLElementForCourse)) {
					$XMLcourse->appendChild($XMLElementForCourse);
					unset ($XMLElementForCourse);
				}
			}
			// ok, course model datas are all set
			// now get all the requested nodes for the current course

			// if passed list of nodes to be exported is empty, export the whole course!
			if (!is_array($nodeList) ||
			(is_array($nodeList) &&  empty($nodeList))) $nodeList=array ($course_id.exportHelper::$courseSeparator.$course_data['id_nodo_iniziale']);

			$XMLAllNodes = $domtree->createElement("nodi");
			$XMLNodeChildren = array();
			// loop the nodes to be exported
			foreach ($nodeList as &$aNodeId)
			{
				$XMLNodeChildren[] = $exportHelper->exportCourseNodeChildren($course_id, $aNodeId, $domtree, $dh, true, $exportSurvey);
			}
			// now add all the children to the all nodes element, this array
			// is kept for possible future uses!
			foreach ($XMLNodeChildren as &$XMLNodeChild) $XMLAllNodes->appendChild($XMLNodeChild);
			unset ($XMLNodeChildren);

			// at least XMLAllNodes should be always set, anyway...
			if (isset($XMLAllNodes))   $XMLcourse->appendChild($XMLAllNodes);
			unset ($XMLAllNodes);

			// tests and surveys are objects related to the course, not to the node
			// so this is out of the nodes loop
			if (MODULES_TEST) {
				// need an AMATestDataHandler, so disconnect the AMAImpExportDataHandler and reconnect
				$dh->disconnect();
				$dh_test = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

				if ($exportSurvey) {
					// get surveys
					$surveysArr = $dh_test->test_getCourseSurveys (array('id_corso'=>$course_id));
				} else {
					$surveysArr = array();
				}
				// build an array of test root nodes id that MUST be exported
				$surveyRootNodes = array();

				if (!empty($surveysArr) && !AMA_DB::isError($surveysArr))
				{
					$XMLAllSurveys = $domtree->createElement('surveys');
					foreach ($surveysArr as &$surveyElement)
					{
						$XMLSurvey = $domtree->createElement('survey');
						foreach ($surveyElement as $name=>$value)
						{
							if ($name==='titolo'|| $name==='id_corso') continue;
							else if ($name==='id_nodo')
								$value = $exportHelper->stripOffCourseId($course_id, $value);
							else if ($name==='id_test')
							{
								$name = 'id_nodoTestEsportato';
								if (!in_array ($value,$surveyRootNodes)) array_push($surveyRootNodes, $value);
							}
							$XMLSurvey->setAttribute($name, $value);
						}
						$XMLAllSurveys->appendChild($XMLSurvey);
						unset ($XMLSurvey);
					}
				}
				if (isset($XMLAllSurveys)) $XMLcourse->appendChild($XMLAllSurveys);
				unset ($XMLAllSurveys);
				// end get surveys

				/**
				 * WARNING!! nodes linking is between:
				 * test_course_survey.id_test and test_nodes.id_nodo !!!
				 */
				// get tests
				$testsArr = $dh_test->test_getNodes(array('id_corso'=>$course_id,'id_nodo_parent'=>null,'id_nodo_radice'=>null,
						'id_nodo_riferimento'=>$exportHelper->exportedNONTestNodeArray,'id_istanza'=>0));

				if (!$exportSurvey) {
					// If user requested to NOT export surveys,
					// remove them from the testsArr array
					$testsArr = array_filter($testsArr, function($element) {
						return $element['tipo']{0} != ADA_TYPE_SURVEY;
					});
				}

				if (!empty ($testsArr) && !AMA_DB::isError($testsArr))
				{
					// $XMLAllTests =& $domtree->createElement('tests');
					$exportHelper->testNodeXMLElement = $domtree->createElement('tests');

					foreach ($testsArr as &$testElement)
					{
						// if this node is in the array of root nodes that MUST
						// be exported, I can safely remove it from the array itself.
						$array_key = array_search($testElement['id_nodo'], $surveyRootNodes);
						if ($array_key!==false) unset ($surveyRootNodes[$array_key]);

						// export the node and all of its kids recursively
						$exportHelper->exportTestNodeChildren($course_id, $testElement['id_nodo'], $domtree, $dh_test);// $XMLAllTests);
					}
				}
				// end get tests

				// if there is still some value in the root nodes that MUST
				// be exported, do it NOW!
				if (!empty($surveyRootNodes))
				{
					if (!isset($XMLAllTests)) $XMLAllTests = $domtree->createElement('tests');
					if (!isset($exportHelper->testNodeXMLElement) || is_null($exportHelper->testNodeXMLElement)) {
					$exportHelper->testNodeXMLElement = $domtree->createElement('tests');
					}

					foreach ($surveyRootNodes as &$rootNode)
						// export the node and all of its kids recursively
						$exportHelper->exportTestNodeChildren($course_id, $rootNode, $domtree, $dh_test); //, $XMLAllTests);
				}
				// end of exporting nodes that MUST be exported.
				$dh_test->disconnect();
			} // end if (MODULES_TEST)

			// at least XMLAllNodes should be always set, anyway...
	// 		if (isset($XMLAllNodes))   $XMLcourse->appendChild($XMLAllNodes);
	// 		if (isset($XMLAllSurveys)) $XMLcourse->appendChild($XMLAllSurveys);
	// 		if (isset($XMLAllTests))   $XMLcourse->appendChild($XMLAllTests);
	// 		unset ($XMLAllTests);
			if (isset($exportHelper->testNodeXMLElement))   $XMLcourse->appendChild($exportHelper->testNodeXMLElement);

			// append current course to the xml root element
			$xmlRoot->appendChild($XMLcourse);

			// unset all for the next loop iteration
	// 		unset ($XMLAllNodes);
	// 		unset ($XMLAllSurveys);
	// 		unset ($XMLAllTests);

			// to export only test (or nodes or surveys or...) it's enough to say:
			// $xmlRoot->appendChild($XMLAllTests);
		}
	}

	// otuput XML to string
	$XMLfile =   $domtree->saveXML();
	$outZipFile = $exportHelper->makeZipFile($XMLfile, $exportMedia);

	// echo '<pre>'.htmlentities($XMLfile, ENT_COMPAT | ENT_HTML401, ADA_CHARSET).'<pre/><hr/>';
	// print_r($exportHelper->mediaFilesArray); die();

	if (!is_null($outZipFile))
	{
		if ($exportToRepo) {
			$exportDir = MODULES_IMPEXPORT_REPOBASEDIR . $course_id . DIRECTORY_SEPARATOR . MODULES_IMPEXPORT_REPODIR;
			if (!is_dir($exportDir)) {
				$oldmask = umask(0);
				mkdir($exportDir, 0775, true);
				umask($oldmask);
			}
			$fileName = str_replace('.zip', '-'.date('His').'.zip', basename($outZipFile));
			$ok = rename($outZipFile, $exportDir .DIRECTORY_SEPARATOR. $fileName);
			if ($ok === false) {
				unlink($outZipFile);
				throw new Exception(translateFN('Impossibile scrivere il file di destinazione'), 500);
			}
			// save to the db
			try {
				$saveData['filename'] = $fileName;
				$rdh->saveExportData($saveData);
				header('Content-Type: application/json');
				die (json_encode([
					'title' => translateFN('Esportazione'),
					'message' => translateFN('Esportazione salvata nel Repository')
				]));
			} catch(\Exception $e) {
				// on db error delete all of the generated files
				@unlink($outZipFile);
				@unlink($exportDir .DIRECTORY_SEPARATOR. $fileName);
				throw new Exception($e->getMessage(), 500);
			}
		} else {
			// http headers for zip downloads
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".basename($outZipFile)."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($outZipFile));
			if(ob_get_length()) ob_end_flush();
			@readfile($outZipFile);
			/**
			 * PLS NOTE: Looks like the file will be unlinked
			 * AFTER it's been served by the sever!!!
			 */
			unlink ($outZipFile);
		}
	} else throw new Exception ("Fatal: Cannot generate zip file", 500);
} catch (\Exception $e) {
	if (!$exportToRepo) {
		die ($e->getMessage());
	} else {
		$code = $e->getCode();
		if (!empty($code)) {
			header(' ', true, $code);
		}
		header('Content-Type: application/json');
		die (json_encode(['title' => translateFN('Esportazione'), 'message' => $e->getMessage()]));
	}
}