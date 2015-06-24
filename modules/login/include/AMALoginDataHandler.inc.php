<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
require_once(ROOT_DIR.'/include/ama.inc.php');

class AMALoginDataHandler extends AMA_DataHandler {
	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_login_';
	
	/**
	 * class name implementing login with ldap
	 * 
	 * @var string
	 */
	public static $LDAPCLASSNAME = 'ldapLogin';
	
	/**
	 * database to be used (if !MULTIPROVIDER)
	 */
	private static $dbToUse = null;
	
	/**
	 * loads login provider's own options
	 * 
	 * @param number $id the id of the login provider
	 * 
	 * @return array of key/value pairs or null if nothing is found
	 * 
	 * @access public
	 */
	public function loadOptions($id) {
		/**
		 * if more than one disctinct option set, return an array of options
		 */
		$makeArray = intval(self::$dbToUse->getOnePrepared(
				'SELECT COUNT(DISTINCT(`'.self::$PREFIX.'providers_options_id`)) FROM `'.
				 self::$PREFIX.'providers_options` WHERE `'.
				 self::$PREFIX.'providers_id` = ? AND `enabled`=1',$id))>1;
		
		$sql = 'SELECT OP.`key`,OP.`value`,PR_OP.`'.self::$PREFIX.'providers_options_id` '.
			   'FROM `'.self::$PREFIX.'options` OP '.
			   'JOIN `'.self::$PREFIX.'providers_options` PR_OP '.
			   'ON PR_OP.`'.self::$PREFIX.'providers_options_id`=OP.`'.self::$PREFIX.'providers_options_id` '.
			   'WHERE `'.self::$PREFIX.'providers_id`=? AND `enabled`=1';
		$res = self::$dbToUse->getAllPrepared($sql,$id,AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
			$retArr = array();
			foreach ($res as $keyvalue) {
				if (is_numeric($keyvalue['value'])) $value = $keyvalue['value'] + 0;
				else if (strcasecmp($keyvalue['value'], 'null')===0) $value = null;
				else if (strcasecmp($keyvalue['value'], 'true')===0) $value = true;
				else if (strcasecmp($keyvalue['value'], 'false')===0) $value = false;
				else if (is_string($keyvalue['value']) && is_object(json_decode($keyvalue['value']))) {
					$value = json_decode($keyvalue['value'], true); // true means return it as an assoc array
				}
				else $value = $keyvalue['value'];
				
				if ($makeArray) $retArr[$keyvalue[self::$PREFIX.'providers_options_id']][$keyvalue['key']] = $value;
				else $retArr[$keyvalue['key']] = $value;
			}
			
			if (count($retArr)>0) {
				$retArr['optionscount'] = ($makeArray ? count($retArr) : 1);
				return $retArr;
			}
		}
		return null;
	}
	
	/**
	 * loads the provider name from the DB
	 *
	 * @param number $id the id of the login provider
	 *
	 * @return string the loaded label
	 *
	 * @access public
	 */	
	public function loadProviderName($id) {
		$sql = 'SELECT `name` FROM `'.self::$PREFIX.'providers` '.
				'WHERE `'.self::$PREFIX.'providers_id`=?';
		return self::$dbToUse->getOnePrepared($sql, $id);
	}
	
	/**
	 * loads the button label from the DB
	 * 
	 * @param number $id the id of the login provider
	 * 
	 * @return string the loaded label
	 * 
	 * @access public
	 */
	public function loadButtonLabel($id) {
		$sql = 'SELECT `buttonLabel` FROM `'.self::$PREFIX.'providers` '.
			   'WHERE `'.self::$PREFIX.'providers_id`=?';
		return self::$dbToUse->getOnePrepared($sql, $id);
	}
	
	/**
	 * gets the login provider listing
	 * 
	 * @param string $enabled true if only enabled providers, false if only disabled, null for all providers
	 * 
	 * @return array of loaded providers or null on error or no record found
	 * 
	 * @access public
	 */
	public function getLoginProviders ($enabled = null, $orderby=null) {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'providers`';
		if (!is_null($enabled) && is_bool($enabled)) {
			$sql .= ' WHERE `enabled` = ?';
			$params = array (intval($enabled));
		}
		
		if (!is_null($orderby)) $sql .= ' ORDER BY '.$orderby;
		
		$res = self::$dbToUse->getAllPrepared($sql, (isset($params) ? $params : null) ,AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
			return $res;
		} else return null;
	}
	
	/**
	 * looks for a loginProvider ID given its class name
	 * 
	 * @param string $className the className to be searched
	 * 
	 * @return number the provider id|AMA_Error on Error
	 * 
	 * @access public
	 */
	public function getLoginProviderIDFromClassName($className) {
		$sql = 'SELECT `'.self::$PREFIX.'providers_id` FROM `'.self::$PREFIX.'providers` '.
			   'WHERE `className`=?';
		return self::$dbToUse->getOnePrepared($sql,$className);
	}
	
	/**
	 * adds a row to the login history table
	 * 
	 * @param number $userID user id that has logged in
	 * @param number $time unix timestamp of the login
	 * @param number $loginProviderID provider used to authenticate
	 * 
	 * @return the result of the query
	 * 
	 * @access public
	 */
	public function addLoginToHistory($userID, $time, $loginProviderID, $successfulOptionsID) {
		$sql = 'SELECT COUNT(`date`) FROM `'.self::$PREFIX.'history_login` WHERE '.
			   '`id_utente`=? AND `'.self::$PREFIX.'providers_id`=?';
		$rowCount = self::$dbToUse->getOnePrepared($sql,array($userID, $loginProviderID));
		
		if (!AMA_DB::isError($rowCount) && defined('MODULES_LOGIN_HISTORY_LIMIT') &&
				$rowCount>MODULES_LOGIN_HISTORY_LIMIT) {
			$numRowsToDel = $rowCount - MODULES_LOGIN_HISTORY_LIMIT + 1;
			self::$dbToUse->executeCritical('DELETE FROM `'.self::$PREFIX.'history_login` ORDER BY `date` ASC LIMIT '.$numRowsToDel);
		}
		
		$sql = 'INSERT INTO `'.self::$PREFIX.'history_login` (`id_utente`,`date`,`'.
				self::$PREFIX.'providers_id`,`successfulOptionsID`) VALUES (?,?,?,?)';
		return self::$dbToUse->queryPrepared($sql, array($userID, $time, $loginProviderID, $successfulOptionsID));
	}
	
	/**
	 * gets max order from providers_options table, needed
	 * when inserting new options for the passed provider
	 * 
	 * @param number $provider_id login provider to get the max order
	 * 
	 * @return number the order value
	 * 
	 * @access private
	 */
	private function getMaxOrderForProviderOptions ($provider_id) {
		$sql = 'SELECT MAX(`order`)+1 FROM '.
			   '`'.self::$PREFIX.'providers_options` WHERE `'.self::$PREFIX.'providers_id`=?';
		$val = self::$dbToUse->getOnePrepared($sql,$provider_id);
		return (intval($val)>0 ? $val : 1);
	}
	
	/**
	 * gets all LDAP options rows
	 * 
	 * @return array
	 * 
	 * @access public
	 */
	public function getAllLDAP() {
		$ldapProviderID = $this->getLoginProviderIDFromClassName(self::$LDAPCLASSNAME);
		
		$sql = 'SELECT PR_OP.`'.self::$PREFIX.'providers_id`, PR_OP.`enabled`, PR_OP.`order`, OP.* '.
			   'FROM `'.self::$PREFIX.'options` OP '.
			   'JOIN `'.self::$PREFIX.'providers_options` PR_OP '.
			   'ON PR_OP.`'.self::$PREFIX.'providers_options_id`=OP.`'.self::$PREFIX.'providers_options_id` '.			   
			   'WHERE PR_OP.`'.self::$PREFIX.'providers_id`=? '.
			   'ORDER BY PR_OP.`order` ASC';
		$result = self::$dbToUse->getAllPrepared($sql,$ldapProviderID,AMA_FETCH_ASSOC);
		$retval = array();
		
		if (!AMA_DB::isError($result) && is_array($result) && count($result)>0) {
			foreach ($result as $anOption) {
				$option_id = $anOption[self::$PREFIX.'providers_options_id'];
				if (!isset($retval[$option_id]['order'])) $retval[$option_id]['order'] = $anOption['order'];
				if (!isset($retval[$option_id]['enabled'])) $retval[$option_id]['enabled'] = $anOption['enabled'];
				$retval[$option_id][$anOption['key']] = $anOption['value'];
			}
		}
		return $retval;
	}
	
	/**
	 * gets an LDAP provider option set
	 * 
	 * @param number $options_id the option set id to be loaded
	 * 
	 * @return array|AMA_Error
	 * 
	 * @access public
	 */
	public function getLDAP ($options_id) {
		
		$sql = 'SELECT * FROM `'.self::$PREFIX.'options` '.
			   'WHERE `'.self::$PREFIX.'providers_options_id`=?';
		$res = self::$dbToUse->getAllPrepared($sql, $options_id,AMA_FETCH_ASSOC);
		if (AMA_DB::isError($res)) return $res;
		else {
			$retval = array('id_ldap'=>$options_id);
			foreach ($res as $element) {
				$retval[$element['key']] = $element['value'];
			}
			return $retval;
		}
	}
	
	/**
	 * saves (either insert or update) an ldap provider option set
	 * 
	 * @param array $ldapArr array of data to be saved
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function saveLDAP ($ldapArr) {
		
		if (!isset($ldapArr['id_ldap']) || (isset($ldapArr['id_ldap'])) && intval($ldapArr['id_ldap'])<=0) {
			// it's an insert
			unset ($ldapArr['id_ldap']);
			
			$ldapProviderID = $this->getLoginProviderIDFromClassName(self::$LDAPCLASSNAME);
			$orderValue = $this->getMaxOrderForProviderOptions($ldapProviderID);
			/**
			 * Insert a new row in the relation table
			 * its id shall be the optionsID to be used
			 */
			$sql = 'INSERT INTO `'.self::$PREFIX.'providers_options` '.
				   '(`'.self::$PREFIX.'providers_id`,`order`) VALUES(?,?)';			
			$res = self::$dbToUse->queryPrepared($sql,array($ldapProviderID,$orderValue));
			
			if (!AMA_DB::isError($res)) {
				$optionID = self::$dbToUse->getConnection()->lastInsertID();
				/**
				 * Insert actual key/value pairs in the options table 
				 */
				$sql = 'INSERT INTO `'.self::$PREFIX.'options` VALUES ';
				$values = array(); $i=0;
				foreach ($ldapArr as $key=>$element) {
					$sql .= '(?,?,?)';
					$values[] = $optionID;
					$values[] = $key;
					// save empty values as null, useful when it's an update
					$values[] = (strlen($element)>0) ? $element : null;
					if (++$i < count($ldapArr)) $sql .= ',';
				}
				$retval = self::$dbToUse->queryPrepared($sql,$values);				
			} else $retval = $res; // return the error			
		} else {
			// it's an update
			$optionID = $ldapArr['id_ldap'];
			unset ($ldapArr['id_ldap']);
			$sql = 'UPDATE `'.self::$PREFIX.'options` SET `value`=? WHERE `key`=? AND '.
					   '`'.self::$PREFIX.'providers_options_id`=?';
			foreach ($ldapArr as $key=>$value) {
				$retval = self::$dbToUse->queryPrepared($sql,array(
						(strlen($value)>0 ? $value : null),$key,$optionID));
				if (AMA_DB::isError($retval)) break;
			}
		}
		return $retval;
	}
	
	/**
	 * deletes an ldap provider option set
	 * 
	 * @param number $options_id the option id to be deleted
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function deleteLDAP($options_id) {
		
		$tablesToDel = array('providers_options','options');
		
		foreach ($tablesToDel as $table) {
			$sql = 'DELETE FROM `'.self::$PREFIX.$table.'`';
			$where = ' WHERE `'.self::$PREFIX.'providers_options_id`=?';
			$res = self::$dbToUse->queryPrepared($sql.$where,$options_id);
			if (AMA_DB::isError($res)) break;			
		}
		
		return $res;
	}
	
	/**
	 * calls and sets the parent instance method, and if !MULTIPROVIDER
	 * checks if module_login_providers table is in the provider db.
	 * 
	 * If found, use the provider DB else use the common
	 * 
	 * @param string $dsn
	 */
	static function instance($dsn = null) {
		$theInstance = parent::instance($dsn);
		
		if (is_null(self::$dbToUse)) {
			self::$dbToUse = AMA_Common_DataHandler::instance();
			if (!MULTIPROVIDER && !is_null($dsn)) {
				// must check if passed $dsn has the module login tables
				// execute this dummy query, if result is not an error table is there
				$sql = 'SELECT NULL FROM `'.self::$PREFIX.'providers`';
				// must use AMA_DataHandler because we are not able to
				// query AMALoginDataHandelr in this method!
				$ok = AMA_DataHandler::instance($dsn)->getOnePrepared($sql);
				if (!AMA_DB::isError($ok)) self::$dbToUse = $theInstance;
			}
		}
		
		return $theInstance;
	}
}