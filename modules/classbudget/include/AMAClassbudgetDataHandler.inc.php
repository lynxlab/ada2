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

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAClassbudgetDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 * 
	 * @var string
	 */
	public static $PREFIX = 'module_classbudget_';	

	/**
	 * Saves a budget row for the course instance
	 * 
	 * @param array $data array of data to be saved, according to db table fields
	 * 
	 * @return AMA_Error|number inserted or updated id
	 * 
	 * @access public
	 */
	public function saveBudgetCourseInstance ($data) {
		$fields = array_keys($data);
		$primaryKey = 'budget_instance_id';
		return $this->_saveRecord('budget_instance', $fields, $primaryKey, $data);
	}
	
	/**
	 * Gets a budget row for a course instance
	 * 
	 * @param number $course_instance_id the instance id to load row for
	 * 
	 * @return AMA_Error|number inserted or updated id
	 * 
	 * @access public
	 */
	public function getBudgetCourseInstanceByInstanceID($course_instance_id) {
		return $this->_getRecord($course_instance_id, 'budget_instance', 'id_istanza_corso');
	}
	
	/**
	 * Deletes a budget row for a course instance
	 * 
	 * @param number $course_instance_id the instance id to delete row for
	 * 
	 * @return AMA_Error|number of affected rows
	 * 
	 * @access public
	 */
	public function deleteBudgetCourseInstanceByInstanceID($course_instance_id) {
		$sql = 'DELETE FROM `'.self::$PREFIX.'budget_instance` WHERE `id_istanza_corso`=?';
		return $this->executeCriticalPrepared($sql,$course_instance_id);
	}
	
	
	/**
	 * gets records from the DB
	 *
	 * @param number $id if null gets all rows
	 * @param string $tableName name of the table
	 * @param string $primaryKey name of the table's own primary key
	 *
	 * @return AMA_Error|array
	 *
	 * @access private
	 */
	private function _getRecord($id, $tableName, $primaryKey) {
		$sql = 'SELECT * FROM `'.self::$PREFIX.$tableName.'`';
		if (!is_null($id)) $sql .=' WHERE `'.$primaryKey.'`=?';
		$sql .= ' ORDER BY `'.$primaryKey.'` ASC';
	
		if (!is_null($id)) {
			$res = $this->getRowPrepared($sql, $id, AMA_FETCH_ASSOC);
		} else {
			$res = $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
		}
	
		// if an error is detected, an error is generated and reported
		if (AMA_DB::isError($res)) {
			return $this->generateError(AMA_ERR_GET, __FUNCTION__, $res);
		} else if (count($res)<=0 || $res===false) {
			return $this->generateError(AMA_ERR_NOT_FOUND, __FUNCTION__, $res);
		} else {
			return $res;
		}
	}
	
	/**
	 * saves a record to the DB
	 *
	 * @param string $what name of the table where to insert/update without PREFIX
	 * @param array $fields fields to insert/update
	 * @param string $primaryKey name of the table's own primary key
	 * @param array  $data assoc array of data to be saved
	 *
	 * @return AMA_Error|number inserted or updated id
	 *
	 * @access private
	 */
	private function _saveRecord ($what, $fields, $primaryKey, $data) {
		$isInsert = false;
	
		// unset invalid $data array keys
		foreach ($data as $key=>$val) {
			if (!in_array($key, $fields) && $key!=$primaryKey) unset($data[$key]);
		}
	
		// unset data that are not a field
		foreach ($fields as $key=>$val) {
			if (!isset($data[$val]) || strlen($data[$val])<=0) unset($data[$val]);
		}
	
		if (!isset($data[$primaryKey]) || $data[$primaryKey]==0 || strlen($data[$primaryKey])<=0) {
			// it's an insert
			if (isset($data[$primaryKey])) unset ($data[$primaryKey]);
	
			foreach (array_keys($data) as $field) {
				$insertValues[] = '`'.$field.'`';
			}
			
			$sql = 'INSERT INTO `'.self::$PREFIX.$what.'` (';
			$sql .= implode(',', $insertValues);
			$sql .= ') VALUES ('.$this->_buildQuestionMarksString(count(array_keys($data)));
			$sql .= ')';
	
			$params = array_values($data);
			$errorCode = AMA_ERR_ADD;
			$isInsert = true;
		} else {
			$primaryKeyVal = $data[$primaryKey];
			unset ($data[$primaryKey]);
	
			// it's an update
			foreach (array_keys($data) as $field) {
				$setValues[] = '`'.$field.'` = ?';
			}
			if (isset($setValues) && is_array($setValues) && count($setValues)>0) {
				$sql = 'UPDATE `'.self::$PREFIX.$what.'` SET ';
				$sql .= implode(',', $setValues);
				$sql .= ' WHERE `'.$primaryKey.'`= ?';
	
				$params = array_merge(array_values($data),array($primaryKeyVal));
			}
			$errorCode = AMA_ERR_UPDATE;
		}
		
		$res = $this->queryPrepared($sql,$params);
		
		// if an error is detected, an error is generated and reported
		if (AMA_DB::isError($res)) {
			return $this->generateError($errorCode, __FUNCTION__, $res);
		}
		else {
			return ($isInsert) ? $this->getConnection()->lastInsertID() : $primaryKeyVal;
		}
	}
	
	/**
	 * build the question mark string for an insert into row:
	 * if number==1 returns '?'
	 * else if number==n returns '?,?,....?' n times
	 *
	 * @param number $count how many fields are needed
	 *
	 * @return string the generated string
	 *
	 * @access private
	 */
	private function _buildQuestionMarksString($count) {
		return sprintf("?%s", str_repeat(",?", ($count  ? $count - 1 : 0)));
	}
	
	private function generateError($errorCode, $functionName, $res) {
		$errStr = $this->errorMessage(new AMA_Error($errorCode))." in ".$functionName;
		if (AMA_DB::isError($res)) $errStr .= ":".AMA_SEP.$res->getMessage();
		return new AMA_Error($errorCode, $errStr);
	}
	
	/**
	 * Returns an instance of AMAClassbudgetDataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMAClassbudgetDataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMAClassbudgetDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}
	
}
?>
