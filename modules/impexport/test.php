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
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once dirname(__FILE__).'/config/config.inc.php';
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
$nodesToExport =  array( 110=>null );
/**
 * exportHelper object to help us in the exporting process...
*/
$exportHelper = new exportHelper();
/**
 * comment to be inserted as first line of XML document
*/
$commentStr = "Exported From ".PORTAL_NAME." v".ADA_VERSION;

// create a dom document with encoding utf8
$domtree = new DOMDocument('1.0', 'UTF-8');
$domtree->preserveWhiteSpace = false;
$domtree->formatOutput = true;

// generate and add comment, if any
if (isset($commentStr)){
	$domtree->appendChild($domtree->createComment($commentStr));
	unset ($commentStr);
}

// create the root element of the xml tree
$xmlRoot =& $domtree->createElement("ada_export");
$xmlRoot->setAttribute("exportDate", date('r'));
// append it to the document created
$xmlRoot =& $domtree->appendChild($xmlRoot);

foreach ($nodesToExport as $course_id=>$nodeList)
{
	// need an Import/Export DataHandler
	$dh =& AMAImpExportDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	
	$course_data =& $dh->get_course ($course_id);

	if (!empty($course_data) && !AMA_DB::isError($course_data))
	{
		// create node for current course
		$XMLcourse =& $domtree->createElement('modello_corso');
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

		$XMLAllNodes =& $domtree->createElement("nodi");
		$XMLNodeChildren = array();
		// loop the nodes to be exported
		foreach ($nodeList as &$aNodeId)
		{
			$XMLNodeChildren[] = $exportHelper->exportCourseNodeChildren($course_id, $aNodeId, $domtree, $dh, true);
		}
		// now add all the children to the all nodes element, this array
		// is kept for possible future uses!
		foreach ($XMLNodeChildren as &$XMLNodeChild) $XMLAllNodes->appendChild($XMLNodeChild);
		unset ($XMLNodeChildren);

		// tests and surveys are objects related to the course, not to the node
		// so this is out of the nodes loop
		if (MODULES_TEST) {
			// need an AMATestDataHandler, so disconnect the AMAImpExportDataHandler and reconnect
			$dh->disconnect();
			$dh_test =& AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
			
			// get surveys
			$surveysArr =& $dh_test->test_getCourseTest (array('id_corso'=>$course_id));
			// build an array of test root nodes id that MUST be exported
			$surveyRootNodes = array();

			if (!empty($surveysArr) && !AMA_DB::isError($surveysArr))
			{
				$XMLAllSurveys =& $domtree->createElement('surveys');
				foreach ($surveysArr as &$surveyElement)
				{
					$XMLSurvey =& $domtree->createElement('survey');
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
			// end get surveys

			/**
			 * WARNING!! nodes linking is between:
			 * test_course_survey.id_test and test_nodes.id_nodo !!!
			 */
			// get tests
			$testsArr =& $dh_test->test_getNodes(array('id_corso'=>$course_id,'id_nodo_parent'=>null,'id_nodo_radice'=>null,'id_istanza'=>0));
			if (!empty ($testsArr) && !AMA_DB::isError($testsArr))
			{
				$XMLAllTests =& $domtree->createElement('tests');
				foreach ($testsArr as &$testElement)
				{
					// if this node is in the array of root nodes that MUST
					// be exported, I can safely remove it from the array itself.
					$array_key = array_search($testElement['id_nodo'], $surveyRootNodes);
					if ($array_key!==false) unset ($surveyRootNodes[$array_key]);

					// export the node and all of its kids recursively
					$exportHelper->exportTestNodeChildren($course_id, $testElement['id_nodo'], $domtree, $dh_test, $XMLAllTests);
				}
			}
			// end get tests

			// if there is still some value in the root nodes that MUST
			// be exported, do it NOW!
			if (!empty($surveyRootNodes))
			{
				if (!isset($XMLAllTests)) $XMLAllTests = $domtree->createElement('tests');
				foreach ($surveyRootNodes as &$rootNode)
					// export the node and all of its kids recursively
					$exportHelper->exportTestNodeChildren($course_id, $rootNode, $domtree, $dh_test, $XMLAllTests);
			}
			// end of exporting nodes that MUST be exported.
			$dh_test->disconnect();
		} // end if (MODULES_TEST)

		// at least XMLAllNodes should be always set, anyway...
		if (isset($XMLAllNodes))   $XMLcourse->appendChild($XMLAllNodes);
		if (isset($XMLAllSurveys)) $XMLcourse->appendChild($XMLAllSurveys);
		if (isset($XMLAllTests))   $XMLcourse->appendChild($XMLAllTests);

		// append current course to the xml root element
		$xmlRoot->appendChild($XMLcourse);

		// unset all for the next loop iteration
		unset ($XMLAllNodes);
		unset ($XMLAllSurveys);
		unset ($XMLAllTests);

		// to export only test (or nodes or surveys or...) it's enough to say:
		// $xmlRoot->appendChild($XMLAllTests);
	}
}

// otuput XML to string
$XMLfile =   $domtree->saveXML();
$outZipFile = $exportHelper->makeZipFile($XMLfile);

// echo '<pre>'.htmlentities($XMLfile).'<pre/><hr/>';
// print_r($exportHelper->mediaFilesArray); die();

if (!is_null($outZipFile))
{
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
	ob_end_flush();
	@readfile($outZipFile);
	/**
	 * PLS NOTE: Looks like the file will be unlinked
	 * AFTER it's been served by the sever!!!
	 */
	unlink ($outZipFile);
} else die ("Fatal: Cannot generate zip file");
?>