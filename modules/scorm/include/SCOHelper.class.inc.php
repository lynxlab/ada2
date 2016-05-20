<?php
/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version		   0.1
 */

require_once ROOT_DIR.'/include/logger_class.inc.php';

class SCOHelper {

	/**
	 * SCO Object name
	 * @var string
	 */
	private $_SCOObject;

	/**
	 * Module's own log file to log import progress, and if something goes wrong
	 * @var string
	 */
	private $_logFile;

	/**
	 * constructor.
	 *
	 */
	public function __construct($SCOObject) {

		$this->_SCOObject = (string) $SCOObject;

		// make the module's own log dir if it's needed
		if (!is_dir(MODULES_SCORM_LOGDIR)) mkdir (MODULES_SCORM_LOGDIR, 0777, true);

		/**
		 * sets the log file name that will be used from now on!
		 */
		$this->_logFile = MODULES_SCORM_LOGDIR . $this->_SCOObject .
		"_".$_SESSION['sess_userObj']->getId().".log";
	}

	/*

	VS SCORM - IMS Manifest File Reader
	Rev 1.0 - Friday, November 06, 2009
	Copyright (C) 2009, Addison Robson LLC

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor,
	Boston, MA 02110-1301, USA.

	*/
	public static function readIMSManifestFile($manifestfile) {

		// load the imsmanifest.xml file
		$xmlfile = new DomDocument;
		$xmlfile->preserveWhiteSpace = FALSE;
		$xmlfile->load($manifestfile);

		// adlcp namespace
		$manifest = $xmlfile->getElementsByTagName('manifest');
		$adlcp = $manifest->item(0)->getAttribute('xmlns:adlcp');

		// READ THE RESOURCES LIST

		// array to store the results
		$resourceData = array();

		// get the list of resource element
		$resourceList = $xmlfile->getElementsByTagName('resource');

		$r = 0;
		foreach ($resourceList as $rtemp) {

			// decode the resource attributes
			$identifier = $resourceList->item($r)->getAttribute('identifier');
			$resourceData[$identifier]['type'] = $resourceList->item($r)->getAttribute('type');
			$resourceData[$identifier]['scormtype'] = $resourceList->item($r)->getAttributeNS($adlcp,'scormtype');
			//  @author giorgio 18 mag 2016: some manifest files have a capital 'T'
			if (strlen($resourceData[$identifier]['scormtype'])<=0) {
				$resourceData[$identifier]['scormtype'] = $resourceList->item($r)->getAttributeNS($adlcp,'scormType');
			}
			$resourceData[$identifier]['href'] = $resourceList->item($r)->getAttribute('href');
			$resourceData[$identifier]['base'] = $resourceList->item($r)->getAttribute('xml:base');

			// list of files
			$fileList = $resourceList->item($r)->getElementsByTagName('file');

			$f = 0;
			foreach ($fileList as $ftemp) {
				$resourceData[$identifier]['files'][$f] =  $fileList->item($f)->getAttribute('href');
				$f++;
			}

			// list of dependencies
			$dependencyList = $resourceList->item($r)->getElementsByTagName('dependency');

			$d = 0;
			foreach ($dependencyList as $dtemp) {
				$resourceData[$identifier]['dependencies'][$d] =  $dependencyList->item($d)->getAttribute('identifierref');
				$d++;
			}

			$r++;

		}

		// resolve resource dependencies to create the file lists for each resource
		foreach ($resourceData as $identifier => $resource) {
			$resourceData[$identifier]['files'] = self::resolveIMSManifestDependencies($identifier, $resourceData);
		}

		// READ THE ITEMS LIST

		// arrays to store the results
		$itemData = array();

		// get the list of resource element
		$itemList = $xmlfile->getElementsByTagName('item');

		$i = 0;
		foreach ($itemList as $itemp) {

			// decode the resource attributes
			$identifier = $itemList->item($i)->getAttribute('identifier');
			$itemData[$identifier]['identifierref'] = $itemList->item($i)->getAttribute('identifierref');
			$itemData[$identifier]['title'] = $itemList->item($i)->getElementsByTagName('title')->item(0)->nodeValue;
			if (is_object($itemList->item($i)->getElementsByTagNameNS($adlcp,'masteryscore')->item(0))) {
				$itemData[$identifier]['masteryscore'] = $itemList->item($i)->getElementsByTagNameNS($adlcp,'masteryscore')->item(0)->nodeValue;
			} else {
				$itemData[$identifier]['masteryscore'] = null;
			}
			if (is_object($itemList->item($i)->getElementsByTagNameNS($adlcp,'datafromlms')->item(0))) {
				$itemData[$identifier]['datafromlms'] = $itemList->item($i)->getElementsByTagNameNS($adlcp,'datafromlms')->item(0)->nodeValue;
			} else if (is_object($itemList->item($i)->getElementsByTagNameNS($adlcp,'dataFromLMS')->item(0))) {
				$itemData[$identifier]['datafromlms'] = $itemList->item($i)->getElementsByTagNameNS($adlcp,'dataFromLMS')->item(0)->nodeValue;

			} else {
				$itemData[$identifier]['datafromlms'] = null;
			}
			$itemData[$identifier]['parameters'] = $itemList->item($i)->getAttribute('parameters');

			$i++;

		}

		// PROCESS THE ITEMS LIST TO FIND SCOS

		// array for the results
		$SCOdata = array();

		// loop through the list of items
		foreach ($itemData as $identifier => $item) {

			// find the linked resource
			$identifierref = $item['identifierref'];

			// is the linked resource a SCO? if not, skip this item
			if (strtolower($resourceData[$identifierref]['scormtype']) != 'sco') { continue; }

			// save data that we want to the output array
			$SCOdata[$identifier]['title'] = $item['title'];
			$SCOdata[$identifier]['masteryscore'] = $item['masteryscore'];
			$SCOdata[$identifier]['datafromlms'] = $item['datafromlms'];
			$SCOdata[$identifier]['base'] = $resourceData[$identifierref]['base'];
			$SCOdata[$identifier]['href'] = $resourceData[$identifierref]['href'];
			$SCOdata[$identifier]['parameters'] = $item['parameters'];
			$SCOdata[$identifier]['files'] = $resourceData[$identifierref]['files'];

		}

		/**
		 * @author giorgio 18 mag 2016
		 * get the organization title
		 */
		$organizationList = $xmlfile->getElementsByTagName('organization');
		if (is_object($organizationList->item(0)) && is_object($organizationList->item(0)->getElementsByTagName('title')->item(0))) {
			$SCOdata['organizationTitle'] = $organizationList->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
		}

		/**
		 * @author giorgio 18 mag 2016
		 * get the schemaversion
		 */
		if ($xmlfile->getElementsByTagName('metadata')->item(0)->getElementsByTagName('schemaversion')->item(0)) {
			$SCOdata['schemaversion'] = $xmlfile->getElementsByTagName('metadata')->item(0)->getElementsByTagName('schemaversion')->item(0)->nodeValue;
		}

		return $SCOdata;
	}


	/**
	 * recursive function used to resolve the dependencies (see above)
	 *
	 * @param unknown $identifier
	 * @param unknown $resourceData
	 *
	 * @return unknown
	 */
	private static function resolveIMSManifestDependencies($identifier, &$resourceData) {

		$files = $resourceData[$identifier]['files'];

		if (isset($resourceData[$identifier]['dependencies'])) {
			$dependencies = $resourceData[$identifier]['dependencies'];
			if (is_array($dependencies)) {
				foreach ($dependencies as $d => $dependencyidentifier) {
				      if (is_array($files)) {
				      	$files = array_merge($files,resolveIMSManifestDependencies($dependencyidentifier));
				      } else {
				      	$files = resolveIMSManifestDependencies($dependencyidentifier);
				      }
					unset($resourceData[$identifier]['dependencies'][$d]);
				}
				$files = array_unique($files);
			}
		}
		return $files;
	}

	/**
	 * logs a message in the log file defined in the logFile private property.
	 *
	 * @param string $text the message to be logged
	 *
	 * @return unknown_type
	 *
	 * @access private
	 */
	public function logMessage ($text)
	{
		// the file must exists, otherwise logger won't log
		if (!is_file($this->_logFile)) touch ($this->_logFile);
		ADAFileLogger::log($text, $this->_logFile);
	}
}