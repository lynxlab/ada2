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
	 * id that the course had at the time it has been exported.
	 * Note that it's not set until the runImport method gets executed
	 *
	 * @var int
	 */
	private $_courseOldID;

	/**
	 * array to map old test node id to new (generated) ones
	 * @var array
	 */
	private $_testNodeIDMapping;

	/**
	 * array to map old course node it to new (generated) ones
	 * @var array
	 */
	private $_courseNodeIDMapping;

	/**
	 * stores all the internal link between nodes that must be saved after the
	 * last imported node insertions
	 * @var array
	 */
	private $_linksArray;

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
	 * Module's own log file to log import progress, and if something goes wrong
	 * @var string
	 */
	private $_logFile;

	/**
	 * the course_id select by the user to import into. If null means new course
	 * @var int
	 */
	private $_selectedCourseID;

	/**
	 * the node id selected by the user to import the imported nodes as if they where its children
	 * if null means new course and new nodes.
	 * @var string
	 */
	private $_selectedNodeID;

	/**
	 * @var string char for separating courseId from nodeId (e.g. 110_0) in tabella nodo
	 */
	public static $courseSeparator = '_';

	/**
	 * XML nodes for which to iterate (or recur)
	 * Should in the near or far future add some more nodes of this type,
	 * simply add names to this array and everything should be fine, provided
	 * ama.inc.php knows how to handle the datas.
	 *
	 * The constructor shall add tests and surveys if MODULES_TEST is set
	 * also, it can add other stuff provided the _import* method is implemented
	 *
	 * @var array
	 */
	private $_specialNodes = array ('nodi');

	/**
	 * must save the selected tester that's usually stored in $_SESSION
	 * because this class is going to write_close and open the session
	 * several times.
	*/
	private $_selectedTester;

	/**
	 * constructs a new importHelper using import file name, and assigned author ID from passed postdatas
	 * Initialized the recapArray and the two data handlers
	 *
	 * @param array $postDatas the datas coming from a POST request
	 */
	public function __construct( $postDatas )
	{
		// 		$this->_importFile = $postDatas['importFileName'];
		$this->_importFile = basename ( $_SESSION['importHelper']['filename']);
		$this->_assignedAuthorID = $postDatas['author'];
		$this->_selectedTester = $_SESSION['sess_selected_tester'];

		$this->_selectedCourseID = (isset ($postDatas['courseID']) && intval($postDatas['courseID'])>0) ? intval($postDatas['courseID']) : null;
		$this->_selectedNodeID =  (isset ($postDatas['nodeID']) && trim($postDatas['nodeID'])!=='') ? trim($postDatas['nodeID']) : null;

		$this->_recapArray = array();
		$this->_linksArray = null;

		$this->_common_dh = $GLOBALS['common_dh'];
		$this->_dh = AMAImpExportDataHandler::instance(MultiPort::getDSN($this->_selectedTester));

		unset ( $_SESSION['importHelper']['filename']);

		$this->_progressInit();

		if (MODULES_TEST)
		{
			/**
			 * entries will be processed in the order they appear.
			 * So in this case it is IMPORTANT that surveys MUST be added AFTER the tests are imported
			 *
			 * Keep this in mind should you ever add other specialNodes to the array
			 */
			$this->_specialNodes = array_merge( $this->_specialNodes, array ('tests', 'surveys'));
		}

		// make the module's own log dir if it's needed
		if (!is_dir(MODULES_IMPEXPORT_LOGDIR)) mkdir (MODULES_IMPEXPORT_LOGDIR, 0777, true);

		/**
		 * REMOVE THEESE WHEN YOU'VE FINISHED!!!!
		*/
		// 		$this->_selectedCourseID = 110;
		// 		$this->_selectedNodeID = $this->_selectedCourseID.self::$courseSeparator."0";
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

			$this->_progressResetValues(substr_count($XMLfile, '</nodo>') +
					substr_count($XMLfile, '<survey ') +
					substr_count($XMLfile, '</test>'));

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
					$this->_courseOldID = $exportedId;

					/**
					 * sets the log file name that will be used from now on!
					 */
					$this->_logFile = MODULES_IMPEXPORT_LOGDIR . "import-".$this->_courseOldID .
					"_".date('d-m-Y_His').".log";

					$this->_logMessage('**** IMPORT STARTED at '.date('d/m/Y H:i:s'). ' ****');
						
					$this->_progressSetTitle( (string) $course->titolo);
					/**
					 * ADDS THE COURSE TO THE APPROPIATE TABLES
					*/
					if (!self::$_DEBUG)
					{
						if (is_null($this->_selectedCourseID))
							$courseNewID = $this->_add_course($course);
						else
						{
							$courseNewID = $this->_selectedCourseID;
						}

						if (AMA_DB::isError($courseNewID)) return $courseNewID;
					} else $courseNewID=123*$count;

					/**
					 * NOW ADD  NODES, TESTS AND SURVEYS
					 */
					foreach ($this->_specialNodes as $groupName)
					{
							
						$method = '_import'.ucfirst(strtolower($groupName));

						$this->_logMessage(__METHOD__.' Saving '.$groupName.' by calling method: '.$method);

						if ($groupName==='tests'  || $groupName==='surveys')
						{
							// prepares the mapping array by emptying it
							if ($groupName==='tests')
							{
								if (isset($this->_testNodeIDMapping)) unset ($this->_testNodeIDMapping);
								$this->_testNodeIDMapping = array();
							}
							// prepares the test data handler
							$this->_dh->disconnect();
							$this->_dh = AMATestDataHandler::instance(MultiPort::getDSN($this->_selectedTester));
						}

						/**
						 * calls a method named _import<groupName> foreach special node.
						 * e.g. for nodes it will call _importNodi, for tests _importTests....
						 */
						if (method_exists($this, $method) && !empty($course->$groupName))
						{
							$specialVal = $this->$method($course->{$groupName} , $courseNewID);
							// if it's an error return it right away
							if (AMA_DB::isError($specialVal)) {
								$this->_logMessage(__METHOD__.' Error saving '.$groupName.'. DB returned the following:');
								$this->_logMessage(print_r($specialVal,true));

								return $specialVal;
							} else {
								$this->_logMessage(__METHOD__.' Saving '.$groupName.' successfully ended');
								$this->_recapArray[$courseNewID][$groupName] = $specialVal;
							}
						}

						if ($groupName==='nodi') {
							// save all the links and clean the array
							$this->_saveLinks($courseNewID);
							// after links have been saved, update inernal links pseudo html in proper nodes
							$this->_updateInternalLinksInNodes($courseNewID);
						} else if ($groupName==='tests' || $groupName==='surveys') {
							// restores the import/export data handler
							$this->_dh->disconnect();
							$this->_dh = AMAImpExportDataHandler::instance(MultiPort::getDSN($this->_selectedTester));
							if ($groupName==='tests') $this->_updateTestLinksInNodes ( $courseNewID );
						}
					}
					// 					$this->_updateTestLinksInNodes ( $courseNewID );
				} // if ($objName === 'modello_corso')
				$this->_logMessage('**** IMPORT ENDED at '.date('d/m/Y H:i:s'). ' ****');
				$this->_logMessage('If there\'s no zip log below, this is a multi course import: pls find unzip log at the end of the last course log');
			} // foreach ($XMLObj as $objName=>$course)

			// extract the zip files to the appropriate media dir
			$this->_unzipToMedia ($zip);

			$zip->close();
			if (!self::$_DEBUG) unlink ($zipFileName);
		}
		$this->_progressDestroy();
		return $this->_recapArray;
	}

	private function _unzipToMedia ($zip)
	{
		if ($zip->numFiles>0)
		{
			$this->_progressSetStatus ('COPY');

			$this->_logMessage(__METHOD__.' Copying files from zip archive, only failures will be logged here');
			$destDir = ROOT_DIR.MEDIA_PATH_DEFAULT.$this->_assignedAuthorID;
			if (self::$_DEBUG) print_r ($destDir);
			if (!is_dir($destDir)) mkdir ($destDir,0777, true);

			for($i = 0; $i < $zip->numFiles; $i++)
			{
				$filename = $zip->getNameIndex($i);
				$fileinfo = pathinfo($filename);

				if ($fileinfo['basename']!==XML_EXPORT_FILENAME)
				{
					/**
					 * strips off course id from the directory of the file to be copied
					 * e.g. ZIPFILE/107/exerciseMedia/foo.png will be copied to:
					 * 		/services/media/<AUTHORID>/exerciseMedia/foo.png
					 */
					if (preg_match('/^[0-9]+(\/{1}.+)$/',$fileinfo['dirname'],$matches))
						$outDir = $destDir.$matches[1];
					else $outDir = $destDir;
					// attempts to make outdir
					mkdir ($outDir, 0777, true);
					if (!copy("zip://".$zip->filename."#".$filename, $outDir."/".$fileinfo['basename'])) {
						$this->_logMessage(__METHOD__.' Could not copy from zip: source='.$filename. ' dest='.$outDir."/".$fileinfo['basename']);
					}
				}
			}
			$this->_logMessage(__METHOD__.' Done copying from zip archive');
		}
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

		$this->_logMessage(__METHOD__.' passed position \n'.print_r($pos_ar,true) );

		// gets a position id by checkin if it's already in the DB
		// or adding a new position row if needed.

		if (($id=$this->_dh->get_id_position($pos_ar))!=-1) {
			// if a position is found in the posizione table, the use it
			$id_posizione = $id;
		}
		else {
			// add row to table "posizione"
			if (AMA_DB::isError($res = $this->_dh->add_position($pos_ar))) {
				$this->_logMessage(__METHOD__.' Error adding position! DB returned the following:');
				$this->_logMessage(print_r($res,true));

				return new AMA_Error($res->getMessage());
			}
			else {
				// get id of position just added
				$id_posizione = $this->_dh->get_id_position($pos_ar);
			}
		}
		$this->_logMessage('Successfully got position_id='.$id_posizione);

		return $id_posizione;
	}

	/**
	 * Builds an extended node array from the passed XML element.
	 * The extended node datas are then merged to the array passed
	 * to the add_node method that saves everything up in the DB.
	 *
	 * @param SimpleXMLElement $extObj the element to be saved
	 * @param int $courseNewID the generated ID of the imported course
	 *
	 * @return boolean on debug|AMA_Error on error|true on success
	 *
	 * @access private
	 */
	private function _buildExtendedArray ( $extObj, $courseNewID)
	{
		$extdendedArr = array();
		foreach ($extObj as $name=>$value)
		{
			$extdendedArr[$name] = (string) $value;
		}

		$extdendedArr['id'] = $courseNewID.self::$courseSeparator.$extObj['id_node'];
		$extdendedArr['lingua'] = self::getLanguageIDFromTable($extdendedArr['language']);
		unset ($extdendedArr['language']);

		$this->_logMessage(__METHOD__.' Saving extended node info:');
		$this->_logMessage(print_r($extdendedArr,true));

		unset ($extdendedArr['id']);
		$retval = $extdendedArr;

		if (!isset($this->_recapArray[$courseNewID]['extended-nodes'])) $this->_recapArray[$courseNewID]['extended-nodes']=1;
		else $this->_recapArray[$courseNewID]['extended-nodes']++;

		$this->_logMessage(__METHOD__.' Successfully built extended node array: '.print_r($retval,true));
			
		return $retval;
	}

	/**
	 * Builds an external resource array from the passed XML element.
	 * Resources are then added to the array passed to the add_node method
	 * that saves everything up in the DB.
	 *
	 * @param SimpleXMLElement $resObj
	 * @param string  $nodeID the id of the node it's saving resources for
	 * @param int $courseNewID the generated ID of the imported course
	 *
	 * @return boolean on debug|AMA_Error on error|int inserted id on success
	 *
	 * @access private
	 */
	private function _buildResourceArray ( $resObj, $nodeID, $courseNewID )
	{
		$resourceArr = array();
		foreach ($resObj as $name=>$value)
		{
			$resourceArr[$name] = (string) $value;
		}

		if (!isset($resourceArr['lingua'])) $resourceArr['lingua'] = 0;
		$resourceArr['lingua'] = self::getLanguageIDFromTable($resourceArr['lingua']);
		$resourceArr['id_utente'] = $this->_assignedAuthorID;

		$retval = $resourceArr;

		if (!isset($this->_recapArray[$courseNewID]['resource'])) $this->_recapArray[$courseNewID]['resource']=1;
		else $this->_recapArray[$courseNewID]['resource']++;

		$this->_logMessage(__METHOD__.' Successfully built external resource array: '.print_r($retval,true));

		return $retval;
	}

	/**
	 * Builds an internal link array from the passed XML element.
	 * Links are saved after all the nodes have been imported in the
	 * _saveLinks method that does also the convertion from exported
	 * node id to imported ones.
	 *
	 * @param SimpleXMLElement $linkObj the element to be saved
	 * @param int $courseNewID the generated ID of the imported course
	 *
	 * @return boolean on debug|AMA_Error on error|true on success
	 *
	 * @access private
	 */
	private function _buildLinkArray ($linkObj, $courseNewID)
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
		/**
		 * keep the old node id in the links, they will be converted into new ones
		 * just after all the nodes have been inserted
		*/
		$linkArr['id_nodo'] = $this->_courseOldID.self::$courseSeparator.$linkArr['id_nodo'];
		$linkArr['id_nodo_to'] = $this->_courseOldID.self::$courseSeparator.$linkArr['id_nodo_to'];
		$linkArr['id_utente'] = $this->_assignedAuthorID;
		$linkArr['data_creazione'] = ts2dFN(time());

		$retval = $linkArr;

		// the recapArray shall be updated when actually saving links in the _saveLinks method

		$this->_logMessage(__METHOD__.' Successfully built link element: '.print_r($retval,true));

		return $retval;
	}

	/**
	 * Iterative method saving the surveys entries in the DB
	 *
	 * @param SimpleXMLElement $xml the element from which the recursion starts (i.e. root node)
	 * @param int $courseNewID the generated ID of the imported course
	 *
	 * @return boolean on debug |AMA_Error on error |int number of imported nodes on success
	 *
	 * @access private
	 */
	private function _importSurveys ($xml, $courseNewID)
	{
		$count=0;

		if (self::$_DEBUG) echo '<pre>'.__METHOD__.PHP_EOL;

		foreach ($xml->children() as $survey)
		{
			foreach ($survey->attributes() as $name=>$value)
			{
				// export every xml <survey> tag attribute as a local var
				$$name = (string) $value;
			}
			// if the test referenced by the id_nodoTestEsportato is not set
			// there's no corresponding test in the DB and we cannot save :(
			if (isset($this->_testNodeIDMapping[$id_nodoTestEsportato]))
			{
				if (!self::$_DEBUG)
				{
					// saves the survey row in the DB

					$this->_logMessage(__METHOD__.' Saving survey: id_corso='.$courseNewID.' id_test='.$this->_testNodeIDMapping[$id_nodoTestEsportato].' id_nodo='.$courseNewID.self::$courseSeparator.$id_nodo);

					$surveyResult = $this->_dh->test_addCourseTest( $courseNewID,
							$this->_testNodeIDMapping[$id_nodoTestEsportato],
							$courseNewID.self::$courseSeparator.$id_nodo);
				}
				else
				{  // prints out some basic info if in debug mode
					print_r ("id_corso=".$courseNewID.PHP_EOL);
					print_r ("id_test=".$this->_testNodeIDMapping[$id_nodoTestEsportato].PHP_EOL);
					print_r ("id_nodo=".$courseNewID.self::$courseSeparator.$id_nodo.PHP_EOL);
					$surveyResult = true;
				}
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($surveyResult)) {

					$this->_logMessage(__METHOD__.' Error saving survey. DB returned the following:');
					$this->_logMessage(print_r($surveyResult,true));

					return $surveyResult;
				}
				else {
					$count++;
					$this->_progressIncrement();

					$this->_logMessage(__METHOD__.' Successfully saved survey');
				}
					
			}
		}
		if (self::$_DEBUG) echo '</pre>';
		return $count;
	}

	/**
	 * Recursive method saving a testnode in the DB and then recurring over all of its children
	 *
	 * @param SimpleXMLElement $xml the element from which the recursion starts (i.e. root node)
	 * @param int $courseNewID the generated ID of the imported course
	 *
	 * @return boolean on debug |AMA_Error on error |int number of imported nodes on success
	 *
	 * @access private
	 */
	private function _importTests ($xml, $courseNewID)
	{

		static $savedCourseID = 0;
		static $count = 0;
		static $depth = 0;

		/**
		 * needed to count how many test were imported
		 * in each disctinct course
		 */
		if ($savedCourseID != $courseNewID) {
			$savedCourseID = $courseNewID;
			$count = 0;
		}

		if (self::$_DEBUG) echo '<pre>'.__METHOD__.PHP_EOL;

		$outArr = array();
		$currentElement = $xml;

		$oldNodeID = (string) $currentElement['id_nodoTestEsportato'];
		$parentNodeID = (string) $currentElement['id_nodo_parent'];
		$rootNodeID = (string) $currentElement['id_nodo_radice'];
		$refNodeID = (string) $currentElement['id_nodo_riferimento'];

		foreach ($currentElement->children() as $name=>$value)
		{
			if ($name === 'test') continue;
			else {
				$outArr[$name] = (string) $value;
			}

		}

		if (!empty($outArr))
		{
			// make some adjustments to invoke the test datahandler's test_addNode method

			$this->_logMessage(__METHOD__.' Saving test node. course id='.$courseNewID.
					' so far '.$count.' nodes have been exported');

			$count++;
			$this->_progressIncrement();

			$outArr['id_corso'] = $courseNewID;
			$outArr['id_posizione'] = (string) $currentElement['id_posizione'];
			$outArr['id_utente'] = $this->_assignedAuthorID;
			$outArr['id_istanza'] = (string) $currentElement['id_istanza'];

			if (isset ( $this->_testNodeIDMapping[$parentNodeID] )) $outArr['id_nodo_parent'] = $this->_testNodeIDMapping[$parentNodeID];
			if (isset ( $this->_testNodeIDMapping[$rootNodeID]   )) $outArr['id_nodo_radice'] = $this->_testNodeIDMapping[$rootNodeID];
			if (isset ($refNodeID) && $refNodeID!='')
			{
				if (isset ($this->_courseNodeIDMapping[$refNodeID]) )
					$outArr['id_nodo_riferimento'] = $this->_courseNodeIDMapping[$refNodeID];
			}

			$outArr['icona'] = str_replace('<root_dir/>', ROOT_DIR, $outArr['icona']);
			$outArr['icona'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['icona']);

			$outArr['testo'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['testo']);
			$outArr['testo'] = str_replace('<http_root/>', HTTP_ROOT_DIR, $outArr['testo']);
			$outArr['testo'] = str_replace('<http_path/>', parse_url(HTTP_ROOT_DIR, PHP_URL_PATH), $outArr['testo']);

			$outArr['nome'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['nome']);
			$outArr['nome'] = str_replace('<http_root/>', HTTP_ROOT_DIR, $outArr['nome']);
			$outArr['nome'] = str_replace('<http_path/>', parse_url(HTTP_ROOT_DIR, PHP_URL_PATH), $outArr['nome']);

			unset ($outArr['data_creazione']);
			unset ($outArr['versione']);
			unset ($outArr['n_contatti']);

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
					var_dump($outArr['id_nodo_parent']);
					var_dump($outArr['nome']);
					var_dump($outArr['tipo']);
				}
			}

			/**
			 * ACTUALLY SAVE THE NODE!! YAHOOOO!!!
			 */
			if (!self::$_DEBUG)
			{
				$this->_logMessage('Saving test node with a call to test_addNode test data handler, passing:');
				$this->_logMessage(print_r($outArr, true));

				$newNodeID = $this->_dh->test_addNode($outArr);
				// if it's an error return it right away, as usual
				if (AMA_DB::isError($newNodeID)) {
					$this->_logMessage(__METHOD__.' Error saving test node. DB returned the following:');
					$this->_logMessage(print_r($newNodeID,true));

					return $newNodeID;
				} else {
					$this->_logMessage(__METHOD__.' Successfully saved test node');
				}
			} else $newNodeID=666;

			$this->_testNodeIDMapping[$oldNodeID] = $newNodeID;
		}

		// recur the children
		if ($currentElement->test)
		{
			for ($i=0; $i< count ($currentElement->test) ; $i++ )
			{
				$this->_logMessage(__METHOD__.' RECURRING TEST NODES: depth='.(++$depth).
						' This test has '.count($currentElement->test).' kids and is the brother n.'.$i);

				$this->_importTests ($currentElement->test[$i], $courseNewID);
			}
		}

		if (self::$_DEBUG) echo '</pre>';
		$depth--;
		return $count;
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
		static $depth = 0;

		/**
		 * needed to count how many nodes were imported
		 * in each disctinct course
		 */
		if ($savedCourseID != $courseNewID) {
			$savedCourseID = $courseNewID;
			$count = 0;
		}

		if (self::$_DEBUG) echo '<pre>'.__METHOD__.PHP_EOL;

		$outArr = array();
		$resourcesArr = array( 0=>'unused' );

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
				// must do an array push because the method that saves the resources expects
				// the array to start at index 1
				array_push($resourcesArr, $this->_buildResourceArray ( $value, $courseNewID.self::$courseSeparator.$outArr['id'] , $courseNewID));
				// NOTE: the files will be copied later on, together with the others
			}
			else if ($name === 'link')
			{
				// this array is saved in the _saveLinks method
				$this->_linksArray[] = $this->_buildLinkArray ( $value, $courseNewID );
			}
			else if ($name === 'extended')
			{
				// it's enough to merge the extended array to the outArr and then add_node saves 'em all
				$outArr = array_merge($outArr, $this->_buildExtendedArray ( $value, $courseNewID ));
			}
			else if ($name=== 'nodo') continue;
			else
			{
				$outArr[$name] = (string) $value;
			}
		}

		if ($outArr['id'] != '')
		{
			$this->_logMessage(__METHOD__.' Saving course node. course id='.$courseNewID.
					' so far '.$count.' nodes have been imported');

			// add the node to the counted elements
			$count++;
			$this->_progressIncrement();

			// make some adjustments to invoke the datahandler's add_node method

			if (!is_null($outArr['id_parent']) && strtolower($outArr['id_parent']) !='null' && ($outArr['id_parent']!=''))
			{
				$oldNodeID = $this->_courseOldID.self::$courseSeparator.$outArr['id_parent'];
				if (isset ($this->_courseNodeIDMapping[$oldNodeID])) {
					$outArr['parent_id'] = $this->_courseNodeIDMapping[$oldNodeID];
				}
				else  {
					$outArr['parent_id'] = $courseNewID.self::$courseSeparator.$outArr['id_parent'];
				}
					
			}
			// 			else
				// 			{
				// 				$outArr['parent_id'] = null;
				// 			}
					unset ($outArr['id_parent']);

					$outArr['id_course'] = $courseNewID;
					$outArr['creation_date'] = ts2dFN(time());
					$outArr['id_node_author'] = $this->_assignedAuthorID;
					$outArr['version'] = 0;
					$outArr['contacts'] = 0;

					$outArr['icon'] = str_replace('<root_dir/>', ROOT_DIR, $outArr['icon']);
					$outArr['icon'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['icon']);
					$outArr['icon'] = str_replace('<http_path/>', parse_url(HTTP_ROOT_DIR, PHP_URL_PATH), $outArr['icon']);
						

					$outArr['text'] = str_replace('<id_autore/>', $this->_assignedAuthorID, $outArr['text']);
					$outArr['text'] = str_replace('<http_root/>', HTTP_ROOT_DIR, $outArr['text']);
					$outArr['text'] = str_replace('<http_path/>', parse_url(HTTP_ROOT_DIR, PHP_URL_PATH), $outArr['text']);

					// oldID is needed below, for creating the array that maps the old node id
					// to the new node id. This must be done AFTER node is saved.
					$oldID = $outArr['id'];
					unset ($outArr['id']); // when a generated id will be used and comment below

					// set array of resources to be saved together with the node
					// for some unbelievable reason the _add_media method called by add_node
					// expects the resources array to start at index 1, so let's make it happy.
					unset ($resourcesArr[0]);
					$outArr['resources_ar'] =  $resourcesArr;

					// sets the parent if an exported root node is made child in import
					if (!isset($outArr['parent_id']) && !is_null ($this->_selectedNodeID))
						$outArr['parent_id'] = $this->_selectedNodeID;

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
						// $outArr['id'] = $courseNewID.self::$courseSeparator.$outArr['id'];

						$this->_logMessage('Saving course node, passing:');
						$this->_logMessage(print_r($outArr, true));

						$addResult = $this->_dh->add_node($outArr);
						// if it's an error return it right away, as usual
						if (AMA_DB::isError($addResult)) {
							$this->_logMessage(__METHOD__.' Error saving course node. DB returned the following:');
							$this->_logMessage(print_r($addResult,true));
							return $addResult;
						} else {
							// add node to the course node mapping array,
							// keys are exported node ids, values are imported ones
							$this->_courseNodeIDMapping[$this->_courseOldID.self::$courseSeparator.$oldID] = $addResult;
							$this->_logMessage(__METHOD__.' Successfully saved course node');
						}
					}
		}

		// recur the children
		if ($currentElement->nodo)
		{
			for ($i=0; $i< count ($currentElement->nodo) ; $i++ )
			{
				$this->_logMessage(__METHOD__.' RECURRING COURSE NODES: depth='.(++$depth).
						' This node has '.count($currentElement->test).' kids and is the brother n.'.$i);

				$this->_importNodi ($currentElement->nodo[$i], $courseNewID);
			}
		}
		if (self::$_DEBUG) echo "</pre>";
		$depth--;
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
			if (!in_array($nodeName, $this->_specialNodes))
			{
				$courseArr[$nodeName] = (string) $nodeValue;
			}
		}

		$courseArr['id_autore'] = $this->_assignedAuthorID;
		$courseArr['d_create'] = ts2dFN(time());
		$courseArr['d_publish'] = NULL;

		$this->_logMessage('Adding course model by calling data handler add_course with the following datas:');
		$this->_logMessage(print_r($courseArr, true));

		$rename_count = 0;
		do {
			$courseNewID = $this->_dh->add_course($courseArr);
			if (AMA_DB::isError($courseNewID))
			{
				if (strlen($courseArr['nome'])>32) // 32 is the size of the field in the database
				{
					$this->_logMessage('Generated name will be over maximum allowed size, I\'ll give up and generate an error message.');
					$rename_count = -1; // this will force an exit from the while loop
				} else
				{				
					$this->_logMessage($courseArr['nome'].' will generate a duplicate key, rename attempt #'.++$rename_count);
					$courseArr['nome'] .= '-DUPLICATE';
				}
			}
			else 
			{
				$this->_logMessage('Successfully created new corse with name:'.$courseArr['nome'].' and id: '.$courseNewID);
			}
		} while (AMA_DB::isError($courseNewID) && $rename_count>=0);

		if (!AMA_DB::isError($courseNewID))
		{
			$retval = $courseNewID;
			// add a row in common.servizio
			$service_dataAr = array(
					'service_name' => $courseArr['titolo'],
					'service_description' => $courseArr['descr'],
					'service_level' => 1,
					'service_duration'=> 0,
					'service_min_meetings' => 0,
					'service_max_meetings' => 0,
					'service_meeting_duration' => 0
			);
			$id_service = $this->_common_dh->add_service($service_dataAr);
			if (!AMA_DB::isError($id_service))
			{
				$tester_infoAr = $this->_common_dh->get_tester_info_from_pointer($this->_selectedTester);
				if (!AMA_DB::isError($tester_infoAr))
				{
					$id_tester = $tester_infoAr[0];
					$result = $this->_common_dh->link_service_to_course($id_tester, $id_service, $courseNewID);
					if (AMA_DB::isError($result)) $retval = $result;
				} else $retval = $tester_infoAr; // if (!AMA_DB::isError($tester_infoAr))
			} else $retval = $id_service; // if (!AMA_DB::isError($id_service))
		} else $retval = $courseNewID; // if (!AMA_DB::isError($courseNewID))

		if (AMA_DB::isError($retval))
		{
			$this->_logMessage('Adding course (modello_corso table) has FAILED! Pls find details below:');
			$this->_logMessage(print_r($retval,true));
		} else $this->_logMessage('Adding course OK! Generated course_id='.$retval);

		return $retval;
	}

	/**
	 * updates the internal link ADA-html internal link tag with the new node id to ling to.
	 * e.g. <LINK TYPE="INTERNAL" VALUE="8"> will be converted in <LINK TYPE="INTERNAL" VALUE="NEWID">
	 *
	 * @param int $courseNewID
	 *
	 * @access private
	 */
	private function _updateInternalLinksInNodes ( $courseNewID )
	{
		$this->_logMessage(__METHOD__.' Updating nodes that have an internal link');

		$nodesToUpdate = $this->_dh->get_nodes_with_internal_link_for_course ( $courseNewID );

		if (!AMA_DB::isError($nodesToUpdate))
		{
			$this->_logMessage(__METHOD__." Candidates for updating: \n".print_r($nodesToUpdate, true));
			$this->_logMessage(__METHOD__." This is the replacement NODE ids array \n".print_r($this->_courseNodeIDMapping,true));
				
			/**
			 * build up source and replacements array
			 * replacements are going to have a random string as
			 * a fake attribute to prevent cyclic substitutions
			 * e.g. if we have in text:
			 *
			 * blablabla... value='1'.... blablabla.... value='7'...blablabla value='1'...
			 *
			 * and in the mapping array: 1=>7 .... 7=>23....
			 *
			 * the result will be that all 1 become 7, and all 7 become 23 and at the and
			 * all of the three links will point to 23.
			*/
				
			$randomStr = substr(md5(time()), 0, 8);
				
			$prefix = '<LINK TYPE="INTERNAL" VALUE="';
			$suffix = '">';
			$suffix2 = '"'.$randomStr.'>';
				
			$search = array();
			$replace = array();
				
			foreach ($this->_courseNodeIDMapping as $oldID=>$newID)
			{
				$oldID = str_replace($this->_courseOldID.self::$courseSeparator, '', $oldID);
				$newID = str_replace($courseNewID.self::$courseSeparator, '', $newID);

				$search[] = $prefix.$oldID.$suffix;
				$replace[] = $prefix.$newID.$suffix2;
			}


			foreach ($nodesToUpdate as $arrElem)
			{
				foreach ($arrElem as $nodeID)
				{
					$this->_logMessage(__METHOD__.' UPDATING NODE id='.$nodeID);
					$nodeInfo = $this->_dh->get_node_info($nodeID);
					$nodeInfo['text'] = str_ireplace($search, $replace, $nodeInfo['text']);
					// strip off the random fake attribute
					$nodeInfo['text'] = str_ireplace($randomStr, '', $nodeInfo['text']);
					$this->_dh->set_node_text($nodeID, $nodeInfo['text']);

				}
			}
		}
	}

	/**
	 * updates the links to the test nodes inside the testo fields of the node
	 * the id of the test MUST be substituted with the generated ones
	 *
	 * @param int $courseNewID
	 *
	 * @access private
	 */
	private function _updateTestLinksInNodes ( $courseNewID )
	{
		$this->_logMessage(__METHOD__.' Updating nodes that have a link to a test:');

		$nodesToUpdate = $this->_dh->get_nodes_with_test_link_for_course ( $courseNewID );

		if (!AMA_DB::isError($nodesToUpdate))
		{
			$this->_logMessage(__METHOD__." Candidates for updating: \n".print_r($nodesToUpdate, true));
			$this->_logMessage(__METHOD__." This is the replacement TEST ids array \n".print_r($this->_testNodeIDMapping,true));

			foreach ($nodesToUpdate as $arrElem)
			{
				foreach ($arrElem as $nodeID)
				{
					$this->_logMessage(__METHOD__.' UPDATING NODE id='.$nodeID);
					$nodeInfo = $this->_dh->get_node_info($nodeID);

					foreach ($this->_testNodeIDMapping as $key=>$val)
					{
						$checkVal = preg_replace('/'.$key.'/', $val, $nodeInfo['text']);
						if ($checkVal != $nodeInfo['text']) {
							$nodeInfo['text']=$checkVal; break;
						}
					}
				}
				$this->_dh->set_node_text($nodeID, $nodeInfo['text']);
			}
		} else {
			$this->_logMessage(__METHOD__. ' Error in retreiving nodes to be updated: '.$nodesToUpdate->getMessage());
		}
	}

	/**
	 * Saves into the DB all the intrenal links between nodes.
	 * Before adding the row to the DB, it maps the exported
	 * id nodes to the imported ones. That's the reason why
	 * this must be execute AFTER all nodes have been imported.
	 *
	 * @param int $courseNewID
	 */
	private function _saveLinks( $courseNewID )
	{
		if (is_array($this->_linksArray))
		{
			$this->_logMessage(__METHOD__. ' Saving internal links for course');
			foreach ($this->_linksArray as $num=>$linkArray)
			{
				if (isset ($this->_courseNodeIDMapping[$linkArray['id_nodo']]) &&
				isset ($this->_courseNodeIDMapping[$linkArray['id_nodo_to']]))
				{
					$linkArray['id_nodo'] = $this->_courseNodeIDMapping[$linkArray['id_nodo']];
					$linkArray['id_nodo_to'] = $this->_courseNodeIDMapping[$linkArray['id_nodo_to']];
						
					$res = $this->_dh->add_link($linkArray);
						
					if (!AMA_DB::isError($res))
					{
						if (!isset($this->_recapArray[$courseNewID]['links'])) $this->_recapArray[$courseNewID]['links']=1;
						else $this->_recapArray[$courseNewID]['links']++;
						$this->_logMessage(__METHOD__.' link # '.$num.' successfully saved. id_nodo='.$linkArray['id_nodo'].' id_nodo_to='.$linkArray['id_nodo_to']);
					} else $this->_logMessage(__METHOD__.' link # '.$num.' FAILED! id_nodo='.$linkArray['id_nodo'].' id_nodo_to='.$linkArray['id_nodo_to']);
				} else {
					$this->_logMessage(__METHOD__.' could not find a match in the mapping array. id_nodo='.$linkArray['id_nodo'].' id_nodo_to='.$linkArray['id_nodo_to']);
				}
			}
		}
		else $this->_logMessage(__METHOD__.' No links to be saved this time');
		$this->_linksArray = null;
	}

	/**
	 * static method to get the language id corresponding to the passed language table identifier
	 * (e.g. on most installations, passing 'it' will return 1)
	 *
	 * @param string $tableName the 2 chars ADA language table identifier
	 *
	 * @return int 0 if empty string passed|AMA_Error on error|int retrieved id on success
	 *
	 * @access public
	 */
	public static function getLanguageIDFromTable ($tableName)
	{
		if ($tableName=='') return 0;
		$res = $GLOBALS['common_dh']->find_language_id_by_langauge_table_identifier ($tableName);
		return (AMA_DB::isError($res)) ? 0 : $res;
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
	private function _logMessage ($text)
	{
		// the file must exists, otherwise logger won't log
		if (!is_file($this->_logFile)) touch ($this->_logFile);
		ADAFileLogger::log($text, $this->_logFile);
	}

	/**
	 * Private methods dealing with sessions
	 *
	 * all of the below methods open and close session because the requestProgress.php file
	 * that is used to display to the user the progress of the import must reads theese
	 * session vars, and if the session is left open, it gets stuck until this php ends.
	 *
	 */

	/**
	 * Initializes empty progress session vars
	 */
	private function _progressInit()
	{
		/**
		 * sets a session array for progrss displaying to the poor user
		 */
		session_write_close();
		session_start();
		if (isset($_SESSION['importProgress'])) unset ($_SESSION['importProgress']);
		$_SESSION['importProgress'] = array();
		session_write_close();

	}

	/**
	 * Unsets progress session vars
	 */
	private function _progressDestroy()
	{
		session_start();
		if (isset($_SESSION['importProgress'])) unset ($_SESSION['importProgress']);
		session_write_close();
		// leave the session open, please (?)
		// session_write_close();
	}

	/**
	 * Resets (aka initializes with values) the progress session vars
	 *
	 * @param int $total count of total items to be imported
	 */
	private function _progressResetValues( $total )
	{
		session_start();
		$_SESSION['importProgress']['totalItems'] = $total;
		$_SESSION['importProgress']['currentItem'] = 0;
		$_SESSION['importProgress']['status'] = 'ITEMS';
		session_write_close();
	}

	/**
	 * Sets the status of the import process
	 *
	 * @param string $status status to be set
	 */
	private function _progressSetStatus ($status)
	{
		session_start();
		$_SESSION['importProgress']['status'] = $status;
		session_write_close();
	}

	/**
	 * Increments the current item count being imported
	 */
	private function _progressIncrement()
	{
		session_start();
		$_SESSION['importProgress']['currentItem']++;
		session_write_close();
	}

	/**
	 * Sets the title of the course being imported
	 *
	 * @param string $title the title to be set
	 */
	private function _progressSetTitle($title)
	{
		session_start();
		$_SESSION['importProgress']['courseName'] = $title;
		session_write_close();
	}
}
?>