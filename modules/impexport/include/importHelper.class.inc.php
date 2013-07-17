<?php
/**
 * @package 	import/export course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */
class importHelper
{
	/**
	 * set to true for debugging purposes: it will make all the actual recursion,
	 * but won't write anything on the DB.
	 * @var boolean
	 */
	private static $_DEBUG = false;

	/**
	 * filename being imported
	 * @var string
	 */
	private $_importFile;

	/**
	 * user chosen author ID to which assing the imported course(s).
	 *
	 * @var int
	 */
	private $_assignedAuthorID;

	/**
	 * common AMA data handler
	 * @var AMA_Common_DataHandler
	 */
	private $_common_dh;

	/**
	 * tester AMA data hanlder
	 * @var AMAImpExportDataHandler
	 */
	private $_dh;

	/**
	 * arrayto return to the caller filled with import recap datas
	 * @var array
	 */
	private $_recapArray;


	/**
	 * @var string char for separating courseId from nodeId (e.g. 110_0) in tabella nodo
	 */
	public static $courseSeparator = '_';

	/**
	 * XML nodes for which to iterate (or recur)
	 * Should in the near or far future add some more nodes of this type,
	 * simply add names to this array and everything should be fine, provided
	 * ama.inc.php knows how to handle the datas.
	 */
	private static $_specialNodes = array ('nodi', 'tests', 'surveys');

	/**
	 * constructs a new importHelper using import file name, and assigned author ID from passed postdatas
	 * Initialized the recapArray and the two data handlers
	 *
	 * @param array $postDatas the datas coming from a POST request
	*/
	public function __construct( $postDatas )
	{
		$this->_importFile = $postDatas['importFileName'];
		$this->_assignedAuthorID = $postDatas['author'];
		$this->_recapArray = array();

		$this->_common_dh = $GLOBALS['common_dh'];
		$this->_dh = AMAImpExportDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	}

	/**
	 * runs the actual import
	 *
	 * @return Ambigous AMA_Error on error |array recpArray on success
	 *
	 * @access public
	 */
	public function runImport()
	{
		$count=0;

		$zipFileName = ADA_UPLOAD_PATH.$this->_importFile;
		$zip = new ZipArchive();
			
		if ($zip->open($zipFileName)) {
			$XMLfile = $zip->getFromName(XML_EXPORT_FILENAME);
			$XMLObj = new SimpleXMLElement($XMLfile);
			foreach ($XMLObj as $objName=>$course)
			{
				// first level object must be 'modello_corso'
				if ($objName === 'modello_corso') {

					$count++;

					// get the attributes as local vars
					// e.g. attributed exportedId=107 becomes
					// a local var named $exportedId, initialized to 107 as a string
					foreach ($course->attributes() as $name=>$val) $$name = (string) $val;
					// as a result of this foreach we have a php var for any XML object attribute
					// var_dump ($exportedId); should neither raise an error nor dump a null value.

					/**
					 * ADDS THE COURSE TO THE APPROPIATE TABLES
					 */
					if (!self::$_DEBUG)
					{
						$courseNewID = $this->_add_course($course);
						// if $courseNewID is an error, return it right away
						if (AMA_DB::isError($courseNewID)) return $courseNewID;
					} else $courseNewID=123*$count;

					/**
					 * NOW ADD  NODES, TESTS AND SURVEYS
					 */
					foreach (self::$_specialNodes as $groupName)
					{
						$method = '_import'.ucfirst(strtolower($groupName));

						/**
						 * calls a method named import<groupName> foreach special node.
						 * e.g. for nodes it will call _importNodi, for tests _importTests....
						*/
						if (method_exists($this, $method) && !empty($course->$groupName))
						{
							$specialVal = $this->$method($course->{$groupName} , $courseNewID);
							// if it's an error return it right away
							if (AMA_DB::isError($specialVal)) return $specialVal;
							else $this->_recapArray[$courseNewID][$groupName] = $specialVal;
						}
					}
				} // if ($objName === 'modello_corso')
			} // foreach ($XMLObj as $objName=>$course)

			$zip->close();
			unlink ($zipFileName);
		}

		return $this->_recapArray;
	}

	/**
	 * Gets a position ID from a position array.
	 * If it's needed, it will save a new row in the posizione table.
	 *
	 * @param array $positionObj
	 * 
	 * @return AMA_Error on error | number position id
	 *
	 * @access private
	 */
	private function _getPosition ( $positionObj )
	{
		$pos_ar = array();
		$pos_ar[0] = (int) $positionObj['x0'];
		$pos_ar[1] = (int) $positionObj['y0'];
		$pos_ar[2] = (int) $positionObj['x1'];
		$pos_ar[3] = (int) $positionObj['y1'];

		// gets a position id by checkin if it's already in the DB
		// or adding a new position row if needed.

		if (($id=$this->_dh->get_id_position($pos_ar))!=-1) {
			// if a position is found in the posizione table, the use it
			$id_posizione = $id;
		}
		else {
			// add row to table "posizione"
			if (AMA_DB::isError($res = $this->_dh->add_position($pos_ar))) {
				return new AMA_Error($res->getMessage());
			}
			else {
				// get id of position just added
				$id_posizione = $this->_dh->get_id_position($pos_ar);
			}
		}
		return $id_posizione;
	}

	/**
	 * Saves an extended node data rows in the DB
	 *
	 * @param SimpleXMLElement $extObj the element to be saved
	 * @param int $courseNewID the generated ID of the imported course
	 * 
	 * @return boolean on debug|AMA_Error on error|true on success
	 *
	 * @access private
	 */
	private function _saveExtended ( $extObj, $courseNewID )
	{
		$extdendedArr = array();
		foreach ($extObj as $name=>$value)
		{
			$extdendedArr[$name] = (string) $value;
		}

		$extdendedArr['id'] = $courseNewID.self::$courseSeparator.$extObj['id_node'];
		$extdendedArr['lingua'] = self::getLanguageIDFromTable($extdendedArr['language']);
		unset ($extdendedArr['language']);

		if (self::$_DEBUG) return self::$_DEBUG;

		$retval = $this->_dh->add_extension_node($extdendedArr);
		if (!AMA_DB::isError($retval)) {
			if (!isset($this->_recapArray[$courseNewID]['extended-nodes'])) $this->_recapArray[$courseNewID]['extended-nodes']=1;
			else $this->_recapArray[$courseNewID]['extended-nodes']++;
		}
		return $retval;
	}

	/**
	 * Saves an external resource data rows in the DB
	 *
	 * @param SimpleXMLElement $resObj
	 * @param string  $nodeID the id of the node it's saving resources for
	 * @param int $courseNewID the generated ID of the imported course
	 * 
	 * @return boolean on debug|AMA_Error on error|int inserted id on success
	 *
	 * @access private
	 */
	private function _saveResource ( $resObj, $nodeID, $courseNewID )
	{
		$resourceArr = array();
		foreach ($resObj as $name=>$value)
		{
			$resourceArr[$name] = (string) $value;
		}

		if (!isset($resourceArr['lingua'])) $resourceArr['lingua'] = 0;
		$resourceArr['lingua'] = self::getLanguageIDFromTable($resourceArr['lingua']);
		$resourceArr['id_utente'] = $this->_assignedAuthorID;
		$resourceArr['id_nodo'] = $nodeID;

		if (self::$_DEBUG) return self::$_DEBUG;

		$retval = $this->_dh->add_risorsa_esterna($resourceArr);
		if (!AMA_DB::isError($retval)) {
			if (!isset($this->_recapArray[$courseNewID]['resource'])) $this->_recapArray[$courseNewID]['resource']=1;
			else $this->_recapArray[$courseNewID]['resource']++;
		}
		return $retval;

	}

	/**
	 * Save an internal link row in the DB
	 *
	 * @param SimpleXMLElement $linkObj the element to be saved
	 * @param int $courseNewID the generated ID of the imported course
	 * 
	 * @return boolean on debug|AMA_Error on error|true on success
	 *
	 * @access private
	 */
	private function _saveLink ($linkObj, $courseNewID)
	{
		$linkArr = array();

		foreach ($linkObj->attributes() as $name=>$value)
		{
			$linkArr[$name] = (string) $value;
		}

		if ($linkObj->posizione)
		{
			$linkArr['posizione'][0] = (int)  $linkObj->posizione['x0'];
			$linkArr['posizione'][1] = (int)  $linkObj->posizione['y0'];
			$linkArr['posizione'][2] = (int)  $linkObj->posizione['x1'];
			$linkArr['posizione'][3] = (int)  $linkObj->posizione['y1'];
		}

		unset ($linkArr['id_LinkEsportato']);
		$linkArr['id_nodo'] = $courseNewID.self::$courseSeparator.$linkArr['id_nodo'];
		$linkArr['id_nodo_to'] = $courseNewID.self::$courseSeparator.$linkArr['id_nodo_to'];
		$linkArr['id_utente'] = $this->_assignedAuthorID;
		$linkArr['data_creazione'] = ts2dFN(time());

		if (self::$_DEBUG) return self::$_DEBUG;

		$retval = $this->_dh->add_link($linkArr);
		if (!AMA_DB::isError($retval)) {
			if (!isset($this->_recapArray[$courseNewID]['links'])) $this->_recapArray[$courseNewID]['links']=1;
			else $this->_recapArray[$courseNewID]['links']++;
		}
		return $retval;
	}

	/**
	 * Recursive method saving a node in the DB and then recurring over all of its children
	 * 
	 * @param SimpleXMLElement $xml the element from which the recursion starts (i.e. root node)
	 * @param int $courseNewID the generated ID of the imported course
	 * 
	 * @return boolean on debug |AMA_Error on error |int number of imported nodes on success
	 * 
	 * @access private
	 */
	private  function _importNodi ($xml, $courseNewID)
	{
		static $savedCourseID = 0;
		static $count = 0;

		/**
		 * needed to count how many nodes were imported
		 * in each disctinct course
		 */
		if ($savedCourseID != $courseNewID) {
			$savedCourseID = $courseNewID;
			$count = 0;
		}

		if (self::$_DEBUG) echo '<pre>';

		$outArr = array();
		$currentElement = $xml;
			
		$outArr ['id'] = (string) $currentElement['id'];
		$outArr ['id_parent'] = (string) $currentElement['parent_id'];

		foreach ($currentElement->children() as $name=>$value)
		{
			if ($name === 'posizione')
			{
				$outArr['pos_x0'] = (int) $value['x0'];
				$outArr['pos_y0'] = (int) $value['y0'];
				$outArr['pos_x1'] = (int) $value['x1'];
				$outArr['pos_y1'] = (int) $value['y1'];
			}
			else if ($name === 'resource')
			{
				$idResource = $this->_saveResource ( $value, $courseNewID.self::$courseSeparator.$outArr['id'] , $courseNewID);
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($idResource)) return $idResource;
				// NOTE: the files will be copied later on, together with the others
			}
			else if ($name === 'link')
			{
				$idLink = $this->_saveLink ( $value, $courseNewID );
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($idLink)) return $idLink;
			}
			else if ($name === 'extended')
			{
				$idExt = $this->_saveExtended ( $value, $courseNewID );
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($idExt)) return $idExt;
			}
			else if ($name=== 'nodo') continue;
			else
			{
				$outArr[$name] = (string) $value;
			}
		}

		if ($outArr['id'] != '')
		{
			// add the node to the counted elements
			$count++;

			// make some adjustments to invoke the datahandler's add_node method

			$outArr['id'] = $courseNewID.self::$courseSeparator.$outArr['id'];

			if (!is_null($outArr['id_parent']) && strtolower($outArr['id_parent']) !='null')
			{
				$outArr['parent_id'] = $courseNewID.self::$courseSeparator.$outArr['id_parent'];
			}
			unset ($outArr['id_parent']);

			$outArr['creation_date'] = ts2dFN(time());
			$outArr['id_node_author'] = $this->_assignedAuthorID;
			$outArr['version'] = 0;
			$outArr['contacts'] = 0;

			$outArr['icon'] = str_replace('<root_dir/>', ROOT_DIR, $outArr['icon']);
			$outArr['icon'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['icon']);

			$outArr['text'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['text']);

			// prints out some basic info if in debug mode
			if (self::$_DEBUG)
			{
				echo "count=".$count.PHP_EOL;
				if ($count==1) {
					//				if ($outArr['id']==$courseNewID.self::$courseSeparator.'1') {
					print_r($outArr);
					echo "<hr/>";
				}
				else {
					var_dump($outArr['id']);
					var_dump($outArr['parent_id']);
					var_dump($outArr['name']);
				}
			}

			/**
			 * ACTUALLY SAVE THE NODE!! YAHOOOO!!!
			 */
			if (!self::$_DEBUG)
			{
				$addResult = $this->_dh->add_node($outArr);
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($addResult)) return $addResult;
			}
		}

		// recur the children
		if ($currentElement->nodo)
		{
			for ($i=0; $i< count ($currentElement->nodo) ; $i++ )
			{
				$this->_importNodi ($currentElement->nodo[$i], $courseNewID);
			}
		}
		if (self::$_DEBUG) echo "</pre>";
		return $count;
	}
	
	
	/**
	 * Adds a course to the modello_corso table of the current provider, 
	 * and then adds a service to the platform and links it to the provider
	 * 
	 * @param SimpleXMLElement $course the root course node to be saved
	 * 
	 * @return AMA_Error on error | int generated course id on success
	 * 
	 * @access private
	 */
	private function _add_course($course)
	{
		// gets all object inside 'modello_corso' that are NOT
		// of type 'nodi', 'tests', 'surveys'

		// holds datas of the course to be saved
		$courseArr = array();
		foreach ($course as $nodeName=>$nodeValue)
		{
			if (!in_array($nodeName, self::$_specialNodes))
			{
				$courseArr[$nodeName] = (string) $nodeValue;
			}
		}

		$courseArr['id_autore'] = $this->_assignedAuthorID;
		$courseArr['d_create'] = ts2dFN(time());
		$courseArr['d_publish'] = NULL;

		$courseNewID = $this->_dh->add_course($courseArr);
		if (!AMA_DB::isError($courseNewID))
		{
			$retval = $courseNewID;
			// add a row in common.servizio
			$service_dataAr = array(
					'service_name' => $courseArr['nome'],
					'service_description' => $courseArr['titolo'],
					'service_level' => 1,
					'service_duration'=> 0,
					'service_min_meetings' => 0,
					'service_max_meetings' => 0,
					'service_meeting_duration' => 0
			);
			$id_service = $this->_common_dh->add_service($service_dataAr);
			if (!AMA_DB::isError($id_service))
			{
				$tester_infoAr = $this->_common_dh->get_tester_info_from_pointer($_SESSION['sess_selected_tester']);
				if (!AMA_DB::isError($tester_infoAr))
				{
					$id_tester = $tester_infoAr[0];
					$result = $this->_common_dh->link_service_to_course($id_tester, $id_service, $courseNewID);
					if (AMA_DB::isError($result)) $retval = $result;
				} else $retval = $tester_infoAr; // if (!AMA_DB::isError($tester_infoAr))
			} else $retval = $id_service; // if (!AMA_DB::isError($id_service))
		} else $retval = $courseNewID; // if (!AMA_DB::isError($courseNewID))
		return $retval;
	}

	/**
	 * static method to get the language id corresponding to the passed language table identifier
	 * (e.g. on most installations, passing 'it' will return 1)
	 * 
	 * @param string $tableName the 2 chars ADA language table identifier
	 * 
	 * @return int 0 if empty string passed|AMA_Error on error|int retrieved id on success
	 * 
	 * @access private
	 */
	private static function getLanguageIDFromTable ($tableName)
	{
		if ($tableName=='') return 0;
		$res = $GLOBALS['common_dh']->find_language_id_by_langauge_table_identifier ($tableName);
		return (AMA_DB::isError($res)) ? 0 : $res;
	}
}
?>