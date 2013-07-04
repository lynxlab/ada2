<?php
/**
 * EXPORT TEST.
 *
 * @package		export/import course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			test/impexport
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

// require_once(MODULES_TEST_PATH.'/config/config.inc.php');
// require_once(MODULES_TEST_PATH.'/include/init.inc.php');
$dh = $GLOBALS['dh'];

define ('COURSE_ID', 110);
$course_id = COURSE_ID;

$course_data = $dh->get_course ($course_id);

if (!empty($course_data))
{
	// create a dom document with encoding utf8 
	$domtree = new DOMDocument('1.0', 'UTF-8');
	// create the root element of the xml tree
	$xmlRoot = $domtree->createElement("ada_export");
	// append it to the document created
	$xmlRoot = $domtree->appendChild($xmlRoot);
	// create node for current course
	$XMLcourse = $domtree->createElement('modello_corso');

	foreach ($course_data as $name=>$value)
	{
		// set attributes for current course
		$XMLcourse->setAttribute($name, $value);
	}
		// get all the nodes for the current course
		$nodesListArr = $dh->find_course_nodes_list( null ,null ,$course_id);
		if (!empty($nodesListArr))
		{
			foreach ($nodesListArr as $nodesList)
			{
				$nodeId = $nodesList[0]; 
				$nodeInfo = $dh->get_node_info ($nodeId);
				unset ($nodeInfo['author']);
				/**
				 * NOTE: Following fields will be modified or omitted and must be calculated when importing:
				 * 
				 * - id_node: is exported with '<course_id>_' prefix removed
				 * - id_parent: is exported with '<course_id>_' prefix removed
				 * - id_utente: shall be the one of the logged user that is doing the import?
				 * - id_posizione: exporting as an xml object, shall check if exists on table posizione when importing
				 * - icona: is exported "AS IS" now. SECURITE', SECURITE', SECURITE' check the file name and path!!!
				 * 
				 */
				$nodeInfo['id'] = str_replace($course_id.'_', '', $nodeId);
				$nodeInfo['parent_id'] = str_replace($course_id.'_', '', $nodeInfo['parent_id']);
				
				// create XML node for current course node
				$XMLnode = $domtree->createElement("node");
				foreach ($nodeInfo as $name=>$value)
				{
					if ($name==='position') continue;
					$XMLnode->setAttribute($name, $value);
				}
				// set the position object
				$XMLNodePosition = $domtree->createElement("position");
// 				foreach ($nodeInfo['position'] as $name=>$value)
// 				{
// 					if      ($name==0) $name = 'x0';
// 					else if ($name==1) $name = 'y0';
// 					else if ($name==2) $name = 'x1';
// 					else if ($name==3) $name = 'y1';
					
// 					$XMLNodePosition->setAttribute($name, $value);
// 				}				
// 				// add the position object to the node
// 				$XMLnode = $XMLnode->appendChild($XMLNodePosition);
				// add the node to the course
				$XMLcourse = $XMLcourse->appendChild($XMLnode);
			}
		}

	// append current course to the xml root element
	$XMLcourse = $xmlRoot->appendChild($XMLcourse);
	
	// otuput XML
	Header('Content-type: text/xml');
	echo  $domtree->saveXML();		
	die();
}


?>