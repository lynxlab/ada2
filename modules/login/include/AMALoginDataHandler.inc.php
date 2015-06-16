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
		$sql = 'SELECT `key`,`value` FROM `'.self::$PREFIX.'options` '.
			   'WHERE `'.self::$PREFIX.'providers_id`=?';
		$res = self::$dbToUse->getAllPrepared($sql,$id,AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
			$retArr = array();
			foreach ($res as $keyvalue) {
				if (is_numeric($keyvalue['value'])) $value = $keyvalue['value'] + 0;
				else if (strcasecmp($keyvalue['value'], 'null')===0) $value = null;
				else if (strcasecmp($keyvalue['value'], 'true')===0) $value = true;
				else if (strcasecmp($keyvalue['value'], 'false')===0) $value = false;
				else $value = $keyvalue['value'];				
				$retArr[$keyvalue['key']] = $value;
			}
			return ((count($retArr)>0) ? $retArr : null);
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
	public function addLoginToHistory($userID, $time, $loginProviderID) {
		$sql = 'SELECT COUNT(`date`) FROM `'.self::$PREFIX.'history_login` WHERE '.
			   '`id_utente`=? AND `'.self::$PREFIX.'providers_id`=?';
		$rowCount = self::$dbToUse->getOnePrepared($sql,array($userID, $loginProviderID));
		
		if (!AMA_DB::isError($rowCount) && defined('MODULES_LOGIN_HISTORY_LIMIT') &&
				$rowCount>MODULES_LOGIN_HISTORY_LIMIT) {
			$numRowsToDel = $rowCount - MODULES_LOGIN_HISTORY_LIMIT + 1;
			self::$dbToUse->executeCritical('DELETE FROM `'.self::$PREFIX.'history_login` ORDER BY `date` ASC LIMIT '.$numRowsToDel);
		}
		
		$sql = 'INSERT INTO `'.self::$PREFIX.'history_login` (`id_utente`,`date`,`'.
				self::$PREFIX.'providers_id`) VALUES (?,?,?)';
		return self::$dbToUse->queryPrepared($sql, array($userID, $time, $loginProviderID));		
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