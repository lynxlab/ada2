<?php
/**
 * @package 	import/export course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */
require_once ROOT_DIR.'/include/logger_class.inc.php';

class exportHelper
{
	/**
	 * elements for which a cdata will be generated in course and node properties
	 */
	public static $cDataElementNameForCourse = array ('nome', 'titolo', 'descr');
	public static $cDataElementNameForCourseNode = array ('name', 'title', 'text','icon');
	public static $cDataElementNameForExtReS = array ('nome_file', 'keywords', 'titolo', 'descrizione');
	public static $cDataElementNameForTest = array ('nome', 'titolo', 'consegna', 'testo', 'copyright', 'didascalia', 'titolo_dragdrop', 'icona');

	/**
	 * @var string char for separating courseId from nodeId (e.g. 110_0) in tabella nodo
	*/
	public static $courseSeparator = '_';

	/**
	 *
	 * @var array holds media files to be exported
	 * keys are the array of the exported courses and values are arrays of files belonging to the course
	 */
	public $mediaFilesArray;

	/**
	 * derived from config_install.inc.php
	 * @var unknown
	 */
	public $mediaFilesPath;

	/**
	 * name of the zip file that will be generated.
	 * .zip extension, session UserObj name and
	 * System date in YYYYMMDD format will be appended,
	 * resulting in an actual file name such as ADAExport_switcherAda0_20130711.zip
	 * Extension will be .zip of course ;)
	 * @var unknown
	 */
	private static $_outputFileName = "ADAExport";

	/**
	 * constructor.
	 *
	 * Initialize the $mediaFilesArray
	 */
	public function __construct() {
		$this->mediaFilesArray = array();
		$this->mediaFilesPath = substr(MEDIA_PATH_DEFAULT, 1);
	}

	/**
	 * builds the xml object with the passed node and all of its children
	 * The passed node is treated like it's a root node, so to export the
	 * whole course it's enough to pass <course_id>_0, otherwhise pass
	 * the nodeId (e.g. 110_1) you want to export.
	 *
	 * @param int $course_id the id of the course to export
	 * @param string $nodeId the id of the node to export, in ADA format (e.g. xxx_yyy)
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param AMA_Tester_DataHandler $dh the dataHandler used to retreive datas
	 * @param boolean $mustRecur if set to true, will do recursion for exporting children
	 *
	 * @return void on error | DOMElement pointer to the exported root XML node
	 *
	 * @access public
	 */
	public function exportCourseNodeChildren($course_id, $nodeId, $domtree, $dh, $mustRecur = false)
	{
		static $depth=0;
		// first export all passed node data
		$nodeInfo =& $dh->get_node_info($nodeId);
		if (AMA_DB::isError($nodeInfo)) return;

		unset ($nodeInfo['author']);
		/**
		 * NOTE: Following fields will be modified or omitted and must be calculated when importing:
		 *
		 * - id_node: is exported with '<course_id>_' prefix removed
		 * - id_parent: is exported with '<course_id>_' prefix removed
		 * - id_utente: WILL BE SELECTED BY THE USER DOING THE IMPORT (is the author, actually)
		 * - id_posizione: exporting as an xml object, shall check if exists on table posizione when importing
		 *
		*/
		$nodeInfo['id'] = self::stripOffCourseId($course_id, $nodeId);
		$nodeInfo['parent_id'] = self::stripOffCourseId($course_id, $nodeInfo['parent_id']);

		// create XML node for current course node
		$XMLnode =& $domtree->createElement("nodo");

		foreach ($nodeInfo as $name=>$value)
		{
			$name = strtolower($name);
			if ($name==='position') continue;
			else if (in_array($name, self::$cDataElementNameForCourseNode))
			{
				if ($name==='text' || $name==='icon') $value = $this->_doPathExportingSubstitutions($name, $value);
				$XMLElementForCourseNode = self::buildCDATASection($domtree, $name, $value);
			}
			else if (preg_match('/id/',$name))
				$XMLnode->setAttribute($name, $value);
			else if ($name==='language')
			{
				$XMLElementForCourseNode = $domtree->createElement($name,self::getLanguageTableFromID($value));
			}
			else
				$XMLElementForCourseNode = $domtree->createElement($name,$value);

			if (isset ($XMLElementForCourseNode))
			{
				$XMLnode->appendChild($XMLElementForCourseNode);
				unset ($XMLElementForCourseNode);
			}

		}
		// set the position object
		$XMLnode->appendChild ( self::buildPosizioneXML($domtree, $nodeInfo['position']) );
		unset ($nodeInfo);
			
		// get the list of the links from the node
		$nodeLinksArr =& $dh->get_node_links($nodeId);
		if (!empty ($nodeLinksArr) && !AMA_DB::isError($nodeLinksArr))
		{
			foreach ($nodeLinksArr as &$nodeLinkId)
			{
				$nodeLinkInfo =& $dh->get_link_info ($nodeLinkId);
				/**
				 * - id_autore: WILL BE SELECTED BY THE USER DOING THE IMPORT (is the author, actually)
				*/
				if (!AMA_DB::isError($nodeLinkInfo))
				{
					unset ($nodeLinkInfo['autore']);
					$nodeLinkInfo['id_nodo'] = self::stripOffCourseId($course_id, $nodeLinkInfo['id_nodo']);
					$nodeLinkInfo['id_nodo_to'] = self::stripOffCourseId($course_id, $nodeLinkInfo['id_nodo_to']);
					$nodeLinkInfo['id_LinkEsportato'] = $nodeLinkId;
					$XMLnode->appendChild( self::buildLinkXML($domtree, $nodeLinkInfo));
				}
			}
		}
		unset ($nodeLinksArr);
		unset ($nodeLinkInfo);
		// end get links

		// get the list of external resources associated to the node
		$extResArr =& $dh->get_node_resources($nodeId);
		if (!empty ($extResArr) && !AMA_DB::isError($extResArr))
		{
			foreach ($extResArr as &$extResId)
			{
				$extResInfo =& $dh->get_risorsa_esterna_info ($extResId);
				if (!AMA_DB::isError($extResInfo))
				{
					$XMLnode->appendChild( self::buildExternalResourceXML($domtree, $extResInfo, $course_id));
				}
			}
		}
		unset ($extResArr);
		unset ($extResInfo);
		// end get external resources

		// get extended nodes
		$extendedNode =& $dh->get_extended_node($nodeId);
		if (!empty($extendedNode) && !AMA_DB::isError($extendedNode))
		{
			$extendedNode['id_node'] = self::stripOffCourseId($course_id, $extendedNode['id_node']);
			$XMLnode->appendChild( self::buildExtendedNodeXML($domtree, $extendedNode));
		}
		unset ($extendedNode);
		// end extended nodes

		// Okay, the node itself has been added to the XML, now do the recursion if asked to
		if ($mustRecur)
		{
			// get node children only having instance=0
			$childNodesArray =& $dh->get_node_children ($nodeId,0);
			if (!empty($childNodesArray) && !AMA_DB::isError($childNodesArray))
			{
				foreach ($childNodesArray as &$childNodeId)
				{
					$XMLnode->appendChild
					(
							self::exportCourseNodeChildren($course_id, $childNodeId, $domtree, $dh, $mustRecur)
					);
				}
			}
			unset ($childNodesArray);
		}
		return $XMLnode;
	}

	/**
	 * builds the xml object with the passed TEST node and all of its children
	 *
	 * @param int $course_id the id of the course to export
	 * @param string $nodeId the id of the node to export, in ADA format (e.g. xxx_yyy)
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param AMATestDataHandler $dh_test the dataHandler used to retreive datas
	 * @param DOMElement $XMLElement the element to append nodes to
	 *
	 * @access public
	 */
	public function exportTestNodeChildren ($course_id, $nodeId, $domtree, $dh_test, $XMLElement)
	{
		$nodeInfo = $dh_test->test_getNode($nodeId);
		if (!AMA_DB::isError($nodeInfo))
		{
			$XMLElement = $XMLElement->appendChild(self::buildTestXML($domtree, $nodeInfo));

			$childrenNodesArr = $dh_test->test_getNodesByParent ($nodeId, null, array('id_istanza'=>0));
			foreach ($childrenNodesArr as $childNode)
				self::exportTestNodeChildren($course_id, $childNode['id_nodo'], $domtree, $dh_test, $XMLElement);
		}
	}

	/**
	 * strips off the course id and the separator character from an ADA node id.
	 * (e.g. if value is 110_23 and course id is 110, will return 23)
	 *
	 * @param int $course_id the course id to be stripped off
	 * @param string $value the string to be stripped off
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function stripOffCourseId ($course_id, $value)
	{
		return str_replace($course_id.self::$courseSeparator, '', $value);
	}

	/**
	 * builds a CDATA section
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param string $name the name of the XML object to be generated
	 * @param string $value the contents of the generated CDATA section
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access public
	 */
	public function buildCDATASection ($domtree, $name, $value)
	{
		// creates a CDATA section
		$XMLCDATAElement = $domtree->createElement($name);
		$XMLCDATAElement->appendChild( $domtree->createCDATASection($value)) ;

		return $XMLCDATAElement;
	}

	/**
	 * builds the XML for note 'extended_node' infos
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param array $extendedInfo the array for which XML will be generated
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access public
	 */
	public function buildExtendedNodeXML ($domtree, $extendedInfo)
	{
		$XMLNodeExtended = $domtree->createElement("extended");
		foreach ($extendedInfo as $name=>$value)
		{
			// all fields but language and id_node are cdatas
			if (preg_match('/id/',$name))
				$XMLNodeExtended->setAttribute($name,$value);
			else if ($name=='language')
			{
				$XMLExtendedNodeElement = $domtree->createElement($name,self::getLanguageTableFromID($value));
			}
			else
				$XMLExtendedNodeElement = self::buildCDATASection($domtree, $name, $value);

			if (isset($XMLExtendedNodeElement))
			{
				$XMLNodeExtended->appendChild($XMLExtendedNodeElement);
				unset ($XMLExtendedNodeElement);
			}
		}
		return $XMLNodeExtended;
	}


	/**
	 * builds the XML for external resource
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param array $extResInfo the array for which XML will be generated
	 * @param int $course_id the id of the course that's being exported
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access public
	 */
	public function buildExternalResourceXML ($domtree, $extResInfo, $course_id)
	{
		$XMLNodeExtRes = $domtree->createElement ("resource");

		foreach ($extResInfo as $name=>$value)
		{
			if (in_array($name, self::$cDataElementNameForExtReS))
			{
				if ($name==='nome_file')
				{
					// add to the mediaFilesArray
					$fileName = ROOT_DIR.MEDIA_PATH_DEFAULT.$extResInfo['id_utente'].'/'.$value;
					$this->addFileToMediaArray($course_id, $fileName);
				}
				else if ($name==='id_utente') continue;

				$XMLElementForCourseRes = self::buildCDATASection($domtree, $name, $value);
			}
			else if ($name==='lingua')
			{
				$XMLElementForCourseRes = $domtree->createElement($name,self::getLanguageTableFromID($value));
			}
			else
			{
				$XMLElementForCourseRes = $domtree->createElement($name,$value);
			}
				
			$XMLNodeExtRes->appendChild($XMLElementForCourseRes);
		}
		return $XMLNodeExtRes;
	}

	/**
	 * called by exportTestNodeChildren to generate the XML for a test node
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param array $testElement the array for which XML will be generated
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access private
	 */
	private function buildTestXML ($domtree, $testElement)
	{
		$XMLTest = $domtree->createElement('test');
		foreach ($testElement as $name=>$value)
		{
			if ($name==='id_corso' || $name === 'id_utente') continue;
			else if (preg_match('/id_/',$name)) {
				if ($name==='id_nodo') $name = 'id_nodoTestEsportato';
				$XMLTest->setAttribute($name, $value);
			}
			else if (in_array($name, self::$cDataElementNameForTest))
			{
				if ($name==='icona') $value = $this->_doPathExportingSubstitutions('icon', $value);
				else if ($name==='testo') $value = $this->_doPathExportingSubstitutions('text', $value);
				
				$XMLTest->appendChild( self::buildCDATASection($domtree, $name, $value) );
			}
			else $XMLTest->appendChild($domtree->createElement($name,$value));
		}

		return $XMLTest;
	}

	/**
	 * builds the XML for node internal links
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param array $link the array for which XML will be generated
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access public
	 */
	public function buildLinkXML ($domtree, $link)
	{
		$XMLNodeLink = $domtree->createElement("link");
		foreach ($link as $name=>$value)
		{
			if ($name === 'posizione') $XMLNodeLink->appendChild ( self::buildPosizioneXML($domtree, $value));
			else $XMLNodeLink->setAttribute ($name, $value);
		}
		return $XMLNodeLink;
	}

	/**
	 * builds the xml for a 'posizione' object
	 *
	 * @param DOMDocument $domtree the XML object to append nodes to
	 * @param array $posizione the array for which XML will be generated
	 *
	 * @return DOMDocument the generated XML node
	 *
	 * @access public
	 */
	public function buildPosizioneXML ($domtree, $posizione)
	{
		$XMLNodePosition = $domtree->createElement("posizione");
		foreach ($posizione as $name=>$value)
		{
			if      ($name==0) $name = 'x0';
			else if ($name==1) $name = 'y0';
			else if ($name==2) $name = 'x1';
			else if ($name==3) $name = 'y1';

			$XMLNodePosition->setAttribute($name, $value);
		}
		return $XMLNodePosition;
	}

	/**
	 * adds a filename to the mediaFilesArray
	 *
	 * @param int $course_id id of the course to which add the file
	 * @param string $filePath filename to add
	 *
	 * @access private
	 */
	private function addFileToMediaArray ($course_id, $filePath)
	{
		if (is_file(ROOT_DIR.'/'.$filePath))
		{
			if (!isset($this->mediaFilesArray[$course_id]))
				$this->mediaFilesArray[$course_id] = array();
			if (!in_array($filePath, $this->mediaFilesArray[$course_id]))
				array_push ($this->mediaFilesArray[$course_id], $filePath);
		}
	}

	/**
	 * Generates the actual zip file to be downloaded
	 *
	 * @param string $XMLFile the string containing the generate XML string
	 * @return string|NULL created zip file name or null on error
	 *
	 * @access public
	 */
	public function makeZipFile ($XMLFile)
	{
		$zipFileName = ADA_UPLOAD_PATH.self::$_outputFileName.'_'.
				$_SESSION['sess_userObj']->username.'_'.date("Ymd").'.zip';

		$zip = new ZipArchive();
		$zip->open($zipFileName, ZipArchive::OVERWRITE);

		$zip->addFromString(XML_EXPORT_FILENAME, $XMLFile);

		foreach ($this->mediaFilesArray as $course_id=>$mediaFiles)
		{
			foreach ($mediaFiles as $mediaFile)
			{
				// build outFileName by removing services/media/<id author>/
				// from the mediaFile
				$regExp = '/'.preg_quote($this->mediaFilesPath,'/').'\d+\/(.+)/';
				if (preg_match($regExp, $mediaFile, $matches)) $outFileName = $matches[1];
				else $outFileName = $mediaFile;

				if (is_file(ROOT_DIR.'/'.$mediaFile))
					$zip->addFile(ROOT_DIR.'/'.$mediaFile, $course_id.'/'.$outFileName);
			}
		}
		if ($zip->close()) return $zipFileName;
		else return null;
	}

	/**
	 * static method to get the table identifier corresponding to the passed language id
	 * (e.g. on most installations, passing 'it' will return 1)
	 *
	 * @param int $languageID language id
	 *
	 * @return string empty if value <=0 is passed|AMA_Error on error|int retrieved table identifier on success
	 *
	 * @access public
	 */
	public static function getLanguageTableFromID ($languageID)
	{
		if (intval ($languageID) <=0 ) return '';
		$res = $GLOBALS['common_dh']->find_language_table_identifier_by_langauge_id ($languageID);
		return (AMA_DB::isError($res)) ? '' : $res;
	}
	
	
	private function _doPathExportingSubstitutions($name, $value)
	{
		/**
		 * check for media files inside the text or in the icon
		 */
		if ($name==='text')
		{
			// remove HTTP_ROOT_DIR so that it'll become
			// a relative path (no more, it will be substituted with other abs path)
			$value = str_replace(HTTP_ROOT_DIR, '<http_root/>', $value);
			$regExp = '/('.preg_quote($this->mediaFilesPath,'/').')(\d+)\/([^\"]+)/';
		}
		else if ($name==='icon')
		{
			// substitute ROOT_DIR with a special tag that will
			// be used to restore ROOT_DIR in the import environment
			$value = str_replace(ROOT_DIR, '<root_dir/>', $value);
			$regExp = '/('.preg_quote($this->mediaFilesPath,'/').')(\d+)\/(.+)/';
		}
		
		/**
		 * run regExp on $value to check for media files
		 */
		if (isset ($regExp))
		{
			if (preg_match($regExp, $value, $matches)) {
				$this->addFileToMediaArray($course_id,$matches[0]);
				$replacement = '<id_autore/>';
				$value = preg_replace($regExp, "$1".$replacement."/$3", $value);
			}
			unset ($regExp);
		}
		return $value;
	}
	
	
}
?>