<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMACompleteDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_complete_';

	/**
	 * Gets all the fields of the given table in an array, EXCLUDING the id field
	 *
	 * @param string $tablename table name to retreive fields list
	 * @param bool $backTick true if fields name must be backtrick enquoted
	 * @return AMA_Error on error, array on success
	 * @access private
	 */
	private function _get_fields_list ($tablename, $backTick = true)
	{
		$db =& $this->getConnection();
		if ( AMA_DB::isError( $db ) ) return $db;

		$fields = array();

		$sql = "SHOW COLUMNS FROM ".self::$PREFIX.$tablename." WHERE field NOT LIKE 'id'";
		$res = $db->getAll($sql, array(), AMA_FETCH_ORDERED);

		if (!AMA_DB::isError($res)){
			// row index 0 is 'Field' field
			foreach ($res as $row) $fields[] = (($backTick) ? '`' : '') . $row[0] . (($backTick) ? '`' : '');
		}
		return $fields;
	}

	/**
	 * gets all the complete conditions set descriptions and ids
	 * as a 2d array, the inner being associative. i.e.
	 *	Array
	 *	(
	 *	    [0] => Array
	 *	        (
	 *	            [id] => 1
	 *	            [descrizione] => 'description of condition set'
	 *	        )
	 *  )
	 *
	 * @param string $orderBy optional to order other than descrizione ASC
	 * @throws Exception
	 * @return array|AMA_Error
	 * @access public
	 */

	public function get_completeConditionSetList($orderBy='descrizione ASC')
	{
		$result = $this->getAllPrepared('SELECT * FROM `'.self::$PREFIX.'conditionset` ORDER BY '.$orderBy,null,AMA_FETCH_ASSOC);
		if (AMA_DB::isError($result))
		{
			throw new Exception('Could not load condition set list');
		}
		return $result;
	}

	/**
	 * saves a CompleteConditionSet object, with all
	 * its associated operations
	 *
	 * @param CompleteConditionSet $cond
	 * @throws Exception
	 * @return Ambigous AMA_Error, boolean false|boolean true on success
	 * @access public
	 */
	public function saveCompleteConditionSet (CompleteConditionSet $cond)
	{
		// the db is needed to get the last insert id
		$db =& $this->getConnection();
		if (AMA_DB::isError($db)) return $db;

		if (is_null($cond->getID()))
		{
			$isUpdate = false;
			$sql = 'INSERT INTO `'.self::$PREFIX.'conditionset` ('.implode(',', $this->_get_fields_list('conditionset')).') VALUES (?)';
			$result = $this->queryPrepared($sql, $cond->description);
		}
		else
		{
			$isUpdate = $cond->getID();
			$sql = 'UPDATE `'.self::$PREFIX.'conditionset` SET '.implode ('=?,',$this->_get_fields_list('conditionset')).'=? WHERE id=?';
			$result = $this->queryPrepared($sql,array ($cond->description, $isUpdate));
		}

		// saves the operation
		if (!AMA_DB::isError($result))
		{
			if ($isUpdate===false)
				$id_conditionset = $db->lastInsertID();
			else {
				$id_conditionset = $isUpdate;
				$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'operations` WHERE `id_conditionset`=?', $id_conditionset);
			}

			$sql = 'INSERT INTO  `'.self::$PREFIX.'operations` ('.implode(',', $this->_get_fields_list('operations')).') VALUES (?,?,?,?,?)';

			$toSave    = $cond->toArray();

			if (!empty($toSave))
			{
				$loopCounter = count ($toSave);
				$mappedIDs = array();
				$regexp = '/expr[(](\d+)[)]/';

				while (!empty($toSave) && ($loopCounter-- >= 0))
				{
					foreach ($toSave as $key=>$currentOperation)
					{
						$processed = false;
						$matches = array();
						$saveData = array(
								$id_conditionset,
								$currentOperation['operator'],
								$currentOperation['operand1'],
								$currentOperation['operand2'],
								$currentOperation['priority']);

						// must check operand1 and operand2 are pointer to expressions
						if (preg_match($regexp, $currentOperation['operand1'],$matches)) {
							$op1Pointer = $matches[1];
						} else $op1Pointer = false;

						if (preg_match($regexp, $currentOperation['operand2'],$matches)) {
							$op2Pointer = $matches[1];
						} else $op2Pointer = false;

						if (!$op1Pointer && !$op2Pointer)
						{
							// neither operand are pointers, create the operation
							$processed = 1;
						}
						else if ($op1Pointer && !$op2Pointer && isset($mappedIDs[$op1Pointer]))
						{
							// operand 1 is a pointer and its pointed operation has been set up, create the operation
							$operand1 = preg_replace($regexp, $mappedIDs[$op1Pointer], $currentOperation['operand1']);
							$processed = 2;
						}
						else if (!$op1Pointer && $op2Pointer && isset($mappedIDs[$op2Pointer]))
						{
							// operand 2 is a pointer and its pointed operation has been set up, create the operation
							$operand2 = preg_replace($regexp, $mappedIDs[$op2Pointer], $currentOperation['operand2']);
							$processed = 3;
						}
						else if ($op1Pointer && $op2Pointer && isset($mappedIDs[$op1Pointer]) && isset($mappedIDs[$op2Pointer]))
						{
							// both operands are pointers and their pointed operations have been set up, create the operation
							$operand1 = preg_replace($regexp, $mappedIDs[$op1Pointer], $currentOperation['operand1']);
							$operand2 = preg_replace($regexp, $mappedIDs[$op2Pointer], $currentOperation['operand2']);
							$processed = 4;
						}

						// sets proper data to be saved
						if ($processed==2 || $processed==4) $saveData[2] = 'expr('.$operand1.')';
						if ($processed==3 || $processed==4) $saveData[3] = 'expr('.$operand2.')';

						$result = $this->queryPrepared($sql,$saveData);

						if (!AMA_DB::isError($result))
							$mappedIDs[$currentOperation['id']] = $db->lastInsertID();

						if ($processed!==false)
						{
							unset ($toSave[$key]);
							break;
						}

						/**
						 * each iteration of the above foreach loop
						 * should process one row of the $toSave array.
						 *
						 * So if $loopCounter goes below zero (i.e.
						 * exceeds the initial $toSave length)
						 * and there are still some rows to be processed, something went wrong
						 * delete everything saved and throw the exception
						 */
						if ($loopCounter <= 0 && !empty($inputOperations)) {
							$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'conditionset` WHERE id=?', $id_conditionset);
							$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'operations` WHERE id_conditionset=?', $id_conditionset);
							throw new Exception('Could not save conditionSet operations', ADA_ERROR_ID_UNKNOWN_ERROR);
							return false;
						}
					} // ends foreach ($toSave as $key=>$currentOperation)
				} // ends while (!empty($toSave) && ($loopCounter-- >= 0))
			} // ends if (!empty($toSave))
		} else {
			throw new Exception('Could not save conditionSet', ADA_ERROR_ID_UNKNOWN_ERROR);
			return $result;
		}
		// if the code reaches this point, everything is fine!
		return $id_conditionset;
	}

	/**
	 * deletes a conditionset together with its associated operations
	 * and all linking to the courses
	 *
	 * @param int $id_conditionSet the id of the conditionset to be deleted
	 * @return array|AMA_Error
	 * @access public
	 */
	public function delete_completeRule ($id_conditionSet)
	{
		$sqlArr = array (
				'DELETE FROM `'.self::$PREFIX.'operations` WHERE id_conditionset=?',
				'DELETE FROM `'.self::$PREFIX.'conditionset_course` WHERE `id_conditionset`=?',
				'DELETE FROM `'.self::$PREFIX.'conditionset` WHERE id=?'
		);

		foreach ($sqlArr as $sql) {
			$retval = $this->queryPrepared($sql, $id_conditionSet);
		}

		return $retval;
	}

	/**
	 * loads a CompleteConditionSet from the DB,
	 * returning the generated object
	 *
	 * @param int $id_conditionSet the id of the condition set to be loaded
	 * @throws Exception
	 * @return CompleteConditionSet
	 * @access public
	 */
	public function getCompleteConditionSet ($id_conditionSet)
	{
		$sql = 'SELECT A.descrizione, B.*
				FROM  `'.self::$PREFIX.'conditionset` A,  `'.self::$PREFIX.'operations` B
				WHERE A.id=? AND A.id = B.id_conditionset
				ORDER BY priority ASC, A.id ASC ';

		$arrOperations = $this->getAllPrepared($sql,$id_conditionSet, AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($arrOperations) && !empty($arrOperations))
		{
			require_once 'completeConditionSet.class.inc.php';
			require_once 'operation.class.inc.php';
			$conditionSet = new CompleteConditionSet($arrOperations[0]['id_conditionset'],$arrOperations[0]['descrizione']);
			$conditionSet->setOperation(Operation::buildOperationTreeFromArray($arrOperations));
			return $conditionSet;
		} else {
    		throw new Exception('Could not load conditionSet', ADA_ERROR_ID_UNKNOWN_ERROR);
    		return null;
    	}
	}

	/**
	 * gets the conditionSet linked to the passed id_course
	 *
	 * @param int $id_course the course id
	 * @return mixed CompleteConditionSet|AMA_Error
	 * @access public
	 */
	public function get_linked_conditionset_for_course ($id_course)
	{
		$sql = 'SELECT `id_conditionset` FROM `'.self::$PREFIX.'conditionset_course` WHERE `id_course`=?';
		$result = $this->getOnePrepared($sql,$id_course);

		try {
			if (!AMA_DB::isError($result))
			{
				return $this->getCompleteConditionSet($result);
			}
		} catch (Exception $e) {}
		return $result;
	}

	/**
	 * gets the array of the courses ids linked to the passed conditionSetId
	 *
	 * @param int $id_conditionSet the id of the conditionset to be loaded
	 * @throws Exception
	 * @return array|AMA_Error
	 * @access public
	 */
	public function get_linked_courses_for_conditionset($id_conditionSet)
	{
		$sql = 'SELECT `id_course` FROM `'.self::$PREFIX.'conditionset_course` WHERE `id_conditionset`=?';

		$result = $this->getAllPrepared($sql, $id_conditionSet);

		if (AMA_DB::isError($result))
		{
			throw new Exception('Could not load linked courses list');
		}
		return $result;
	}

	/**
	 * saves the link between a conditionSet and a course
	 *
	 * @param int $id_conditionSet
	 * @param array $linkCourse
	 * @throws Exception
	 * @return boolean
	 */
	public function linkCoursesToConditionSet($id_conditionSet, $linkCourse)
	{
		// unlink all courses to the passed condition set
		$result = $this->queryPrepared('DELETE FROM `'.self::$PREFIX.'conditionset_course` WHERE `id_conditionset`=?',$id_conditionSet);
		if (!AMA_DB::isError($result))
		{
			if (is_array($linkCourse) && !empty($linkCourse))
			{
				$sqlLink = 'INSERT INTO  `'.self::$PREFIX.'conditionset_course` ('.implode(',', $this->_get_fields_list('conditionset_course')).') VALUES (?,?)';
				$sqlUnlink = 'DELETE FROM `'.self::$PREFIX.'conditionset_course` WHERE `id_course`=?';
				foreach ($linkCourse as $course_id=>$isLinked)
				{
					if (intval($isLinked)===1)
					{
						// unlink the course to any previous conditions it might have
						$result = $this->queryPrepared($sqlUnlink,$course_id);
						if (AMA_DB::isError($result))
						{
							throw new Exception('Could not unlink conditionset from single course');
							return false;
						} else {
							// link the selected course to the selected conditionset
							$result = $this->queryPrepared($sqlLink, array($id_conditionSet,$course_id));
							if (AMA_DB::isError($result))
							{
								throw new Exception('Could not link conditionset to course');
								return false;
							}
						}
					}
				}
			}
		} else {
			throw new Exception('Could not unlink conditionset from courses');
			return false;
		}
		return true;
	}

}
?>