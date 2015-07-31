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
	const LDAPCLASSNAME = 'ldapLogin';
	
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
	public function loadOptions ($id) {
		/**
		 * if more than one disctinct option set, return an array of options
		 */
		$optionsCount = intval(self::$dbToUse->getOnePrepared(
				'SELECT COUNT(DISTINCT(`'.self::$PREFIX.'providers_options_id`)) FROM `'.
				 self::$PREFIX.'providers_options` WHERE `'.
				 self::$PREFIX.'providers_id` = ? AND `enabled`=1',$id));
		$makeArray = $optionsCount > 1;
		$sql = 'SELECT OP.`key`,OP.`value`,PR_OP.`'.self::$PREFIX.'providers_options_id` '.
			   'FROM `'.self::$PREFIX.'options` OP '.
			   'JOIN `'.self::$PREFIX.'providers_options` PR_OP '.
			   'ON PR_OP.`'.self::$PREFIX.'providers_options_id`=OP.`'.self::$PREFIX.'providers_options_id` '.
			   'WHERE `'.self::$PREFIX.'providers_id`=? AND `enabled`=1';
		$res = self::$dbToUse->getAllPrepared($sql,$id,AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($res) && is_array($res)) {
			$retArr = array();
			if (count($res)>0) {
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
			}
			
			if (count($retArr)>0 || $optionsCount>0) {
				$retArr['optionscount'] = ($makeArray ? count($retArr) : $optionsCount);
				if (!$makeArray) {
					if (!is_null($keyvalue[self::$PREFIX.'providers_options_id'])) {
						$retArr['providers_options_id'] = $keyvalue[self::$PREFIX.'providers_options_id'];
					} else {
						// must still load the providers_options_id
						$sql = 'SELECT `'.self::$PREFIX.'providers_options_id`'.
						       ' FROM `'.self::$PREFIX.'providers_options`'.
						       ' WHERE `'.self::$PREFIX.'providers_id`=?';
						$retArr['providers_options_id'] = intval(self::$dbToUse->getOnePrepared($sql,$id));
					}
				}
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
	public function loadProviderName ($id) {
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
	public function loadButtonLabel ($id) {
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
	public function getLoginProviderIDFromClassName ($className) {
		$sql = 'SELECT `'.self::$PREFIX.'providers_id` FROM `'.self::$PREFIX.'providers` '.
			   'WHERE `className`=?';
		return self::$dbToUse->getOnePrepared($sql,$className);
	}
	
	/**
	 * loads a login provider data
	 * 
	 * @param number $provider_id the provider id to load
	 * 
	 * @return array|AMA_Error
	 * 
	 * @access public
	 */
	public function getLoginProvider($provider_id) {
	
		$sql = 'SELECT * FROM `'.self::$PREFIX.'providers` '.
				'WHERE `'.self::$PREFIX.'providers_id`=?';
		$res = self::$dbToUse->getAllPrepared($sql, $provider_id,AMA_FETCH_ASSOC);
		if (AMA_DB::isError($res)) return $res;
		else {
			$retval = array('provider_id'=>$provider_id);
			foreach ($res as $element) {
				foreach ($element as $key=>$value) $retval[$key] = $value;
			}
			return $retval;
		}
	}
	
	/**
	 * saves (either insert or update) a login provider
	 *
	 * @param array $dataArr array of data to be saved
	 *
	 * @return boolean|AMA_Error|number the created provider id
	 *
	 * @access public
	 */
	public function saveLoginProvider($dataArr) {
		$retval = new AMA_Error();
		
		if (!isset($dataArr['provider_id']) || (isset($dataArr['provider_id'])) && intval($dataArr['provider_id'])<=0) {
			// it's an insert
			unset ($dataArr['provider_id']);
			
			$orderValue = $this->getMaxOrderForLoginProvider();
			
			$sql = 'INSERT INTO `'.self::$PREFIX.'providers`'.
			       ' (`className`, `name`, `buttonLabel`, `displayOrder`)  VALUES(?,?,?,?)';
			
			$res = self::$dbToUse->queryPrepared($sql,
				array($dataArr['className'],$dataArr['name'],$dataArr['buttonLabel'],$orderValue));
				
			if (!AMA_DB::isError($res)) {
				$providerID = self::$dbToUse->getConnection()->lastInsertID();
				// insert a corresponding row into providers_options
				$sql = 'INSERT INTO `'.self::$PREFIX.'providers_options` '.
				       '(`'.self::$PREFIX.'providers_id`,`order`) VALUES(?,?)';
				$optRes = self::$dbToUse->queryPrepared($sql,array($providerID,1));
				if (!AMA_DB::isError($optRes)) $retval = $providerID;
				else {
					$this->deleteLoginProvider($providerID);
					$retval = $optRes;
				}
			} else $retval = $res;
		} else {
			// it's an update
			$sql = 'UPDATE `'.self::$PREFIX.'providers` SET'.
				   ' `className`=?, `name`=?, `buttonLabel`=?'.
				   ' WHERE `'.self::$PREFIX.'providers_id`=?';
			$retval = self::$dbToUse->queryPrepared($sql,
					array($dataArr['className'],$dataArr['name'],$dataArr['buttonLabel'],$dataArr['provider_id']));
		}
		return $retval;
	}
	
	/**
	 * deletes a login provider, with all of its options
	 * 
	 * @param number $provider_id the provider id to be deleted
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @accesss public
	 */
	public function deleteLoginProvider ($provider_id) {
		// get login provider options
		$options = $this->loadOptions($provider_id);
		// delete them
		if (!AMA_DB::isError($options) && isset ($options['providers_options_id'])) {
			$this->deleteOptionSet($options['providers_options_id']);
		}
		if (AMA_DB::isError($this->updateOrderBeforeDelete(true, $provider_id))) return new AMA_Error();
		// delete provider
		$sql = 'DELETE FROM `'.self::$PREFIX.'providers` WHERE `'.self::$PREFIX.'providers_id`=?';
		return self::$dbToUse->queryPrepared($sql, $provider_id);
	}
	
	/**
	 * sets the enabled status of a login provider
	 *
	 * @param number $provider_id the provider id to be enabled or disabled
	 * @param number $status 1 to enable, 0 to disable
	 *
	 * @return boolean|AMA_Error
	 *
	 * @access public
	 */
	public function setEnabledLoginProvider ($provider_id, $status) {
		$sql = 'UPDATE `'.self::$PREFIX.'providers` SET `enabled`=? '.
				'WHERE `'.self::$PREFIX.'providers_id`=?';
		return self::$dbToUse->queryPrepared($sql, array(intval($status),$provider_id));
	}
	
	/**
	 * moves the order of a login provider
	 *
	 * @param number $provider_id the provider to be moved
	 * @param number $delta amount to move (only +/-1 are tested so far)
	 *
	 * @return boolean|AMA_Error
	 *
	 * @access public
	 */
	public function moveLoginProvider ($provider_id, $delta) {
		return $this->moveTableRow ($provider_id, $delta, true);
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
	public function addLoginToHistory ($userID, $time, $loginProviderID, $successfulOptionsID) {
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
	 * gets max order from providers table, needed
	 * when inserting new provider
	 *
	 * @return number the order value
	 *
	 * @access private
	 */
	private function getMaxOrderForLoginProvider() {
		$sql = 'SELECT MAX(`displayOrder`)+1 FROM `'.self::$PREFIX.'providers`';
		$val = self::$dbToUse->getOnePrepared($sql);
		return (intval($val)>0 ? $val : 1);		
	}
	
	/**
	 * gets all options rows for a login provider
	 * 
	 * @param number $provider_id login provider id to get options for
	 * 
	 * @return array
	 * 
	 * @access private
	 */
	public function getAllOptions ($provider_id) {
		
		$sql = 'SELECT PR_OP.`'.self::$PREFIX.'providers_id`, PR_OP.`enabled`, PR_OP.`order`, OP.* '.
			   'FROM `'.self::$PREFIX.'options` OP '.
			   'JOIN `'.self::$PREFIX.'providers_options` PR_OP '.
			   'ON PR_OP.`'.self::$PREFIX.'providers_options_id`=OP.`'.self::$PREFIX.'providers_options_id` '.
			   'WHERE PR_OP.`'.self::$PREFIX.'providers_id`=? '.
			   'ORDER BY PR_OP.`order` ASC';
		$result = self::$dbToUse->getAllPrepared($sql,$provider_id,AMA_FETCH_ASSOC);
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
	 * gets a provider option set
	 * 
	 * @param number $options_id the option set id to be loaded
	 * 
	 * @return array|AMA_Error
	 * 
	 * @access public
	 */
	public function getOptionSet ($options_id) {
		
		$sql = 'SELECT * FROM `'.self::$PREFIX.'options` '.
			   'WHERE `'.self::$PREFIX.'providers_options_id`=?';
		$res = self::$dbToUse->getAllPrepared($sql, $options_id,AMA_FETCH_ASSOC);
		if (AMA_DB::isError($res)) return $res;
		else {
			$retval = array('option_id'=>$options_id);
			foreach ($res as $element) {
				$retval[$element['key']] = $element['value'];
			}
			return $retval;
		}
	}
	
	/**
	 * saves (either insert or update) a provider option set
	 * 
	 * @param array $dataArr array of data to be saved
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function saveOptionSet ($dataArr) {
		
		if (!isset($dataArr['option_id']) || (isset($dataArr['option_id'])) && intval($dataArr['option_id'])<=0) {
			// it's an insert
			unset ($dataArr['option_id']);
			
			$ldapProviderID = $this->getLoginProviderIDFromClassName(self::LDAPCLASSNAME);
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
				foreach ($dataArr as $key=>$element) {
					$sql .= '(?,?,?)';
					$values[] = $optionID;
					$values[] = $key;
					// save empty values as null, useful when it's an update
					$values[] = $this->or_null($element);
					if (++$i < count($dataArr)) $sql .= ',';
				}
				$retval = self::$dbToUse->queryPrepared($sql,$values);
			} else $retval = $res; // return the error
		} else {
			// it's an update
			$optionID = $dataArr['option_id'];
			unset ($dataArr['option_id']);
			$sql = 'UPDATE `'.self::$PREFIX.'options` SET `value`=? WHERE `key`=? AND '.
					   '`'.self::$PREFIX.'providers_options_id`=?';
			foreach ($dataArr as $key=>$value) {
				$retval = self::$dbToUse->queryPrepared($sql,array(
						(strlen($value)>0 ? $value : null),$key,$optionID));
				if (AMA_DB::isError($retval)) break;
			}
		}
		return $retval;
	}
	
	/**
	 * deletes a provider option set
	 * 
	 * @param number $options_id the option id to be deleted
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function deleteOptionSet ($options_id) {
		
		$tablesToDel = array('providers_options','options');
		
		foreach ($tablesToDel as $table) {
			$sql = 'DELETE FROM `'.self::$PREFIX.$table.'`';
			$where = ' WHERE `'.self::$PREFIX.'providers_options_id`=?';
			if ($table=='providers_options') {
				// update orders before deleting from the providers_options table
				$updRes = $this->updateOrderBeforeDelete(false, $options_id);
				if (AMA_DB::isError($updRes)) return $updRes;
			}
			$res = self::$dbToUse->queryPrepared($sql.$where,$options_id);
			if (AMA_DB::isError($res)) break;
		}
		return $res;
	}
	
	/**
	 * sets the enabled status of an option set
	 * 
	 * @param number $options_id the option id to be enabled or disabled
	 * @param number $status 1 to enable, 0 to disable
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function setEnabledOptionSet ($options_id, $status) {
		$sql = 'UPDATE `'.self::$PREFIX.'providers_options` SET `enabled`=? '.
			   'WHERE `'.self::$PREFIX.'providers_options_id`=?';
		return self::$dbToUse->queryPrepared($sql, array(intval($status),$options_id));
	}
	
	/**
	 * moves the order of an option set
	 * 
	 * @param number $options_id the option id to be moved
	 * @param number $delta amount to move (only +/-1 are tested so far)
	 *
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function moveOptionSet ($options_id, $delta) {
		return $this->moveTableRow ($options_id, $delta, false);
	}
	
	/**
	 * moves the order of a table row
	 *
	 * @param number $row_id the row id to be moved
	 * @param number $delta amount to move (only +/-1 are tested so far)
	 *
	 * @return boolean|AMA_Error
	 *
	 * @access private
	 */
	private function moveTableRow ($row_id, $delta, $isProvider) {
		
		$options = $this->getCommonQueryOptions($isProvider);
		
		$compareOperator = ($delta>0) ? '>' : '<';
		$mathOperator = ($delta>0) ? '+' : '-';
		$orderBY  = ($delta>0) ? 'ASC' : 'DESC';
		
		// 0. get the provider_id of the passed options_id
		$provider_id = self::$dbToUse->getOnePrepared( 
					'SELECT `'.self::$PREFIX.'providers_id` '.
					'FROM `'.$options['table'].'` WHERE `'.
					$options['col'].'`=?', $row_id);
		
		if (!AMA_DB::isError($provider_id)) {
			// subquery to select the order of the passed options_id
			$subquery = '(SELECT `'.$options['ordercol'].'` FROM '.
				   		'`'.$options['table'].'` WHERE '.
				   		'`'.$options['col'].'`=?)';
			
			// 1. get the options_id of the options that is one position above or below
			// (depending on delta being positive) the passed one, that will be called target_id
			$sql = 'SELECT `'.$options['col'].'`, `'.$options['ordercol'].'` '.
				   'FROM `'.$options['table'].'` WHERE ';
			
			if ($isProvider===false) {
				$sql .= '`'.self::$PREFIX.'providers_id`=? AND ';
				$values = array($provider_id,$row_id);
			} else $values = $row_id;
			
			$sql .= '`'.$options['ordercol'].'` '.$compareOperator.' '.$subquery.
			        ' ORDER BY `'.$options['ordercol'].'` '.$orderBY.' LIMIT 1';
				
			$target_id = self::$dbToUse->getOnePrepared($sql, $values);
	
			if (!AMA_DB::isError($target_id)) {
				// 2. update target_id setting its order to the one of options_id
				$sql_update_target = 'UPDATE `'.$options['table'].'` SET `'.$options['ordercol'].
				'`= (SELECT * FROM '.$subquery.' AS dummy) WHERE `'.$options['col'].'`=?';
				$res = self::$dbToUse->queryPrepared($sql_update_target, array($row_id, $target_id));
				if (!AMA_DB::isError($res)) {
					// 3. update options_id, increasing or decreasing its order
					$sql_update_option = 'UPDATE `'.$options['table'].'` SET `'.$options['ordercol'].
							'`= `'.$options['ordercol'].'`'.$mathOperator.abs($delta).
					        ' WHERE `'.$options['col'].'`=?';
					$res = self::$dbToUse->queryPrepared($sql_update_option,
							array($row_id));
				}
				return $res;
			} else return $target_id;
		} else return $provider_id;
	}
	
	/**
	 * saves (either insert or update) a provider option key/value pair
	 * 
	 * @param array $dataArr array of data to be saved
	 * 
	 * @return unknown|mixed|AMA_Error
	 * 
	 * @access public
	 */
	public function saveOptionByKey ($dataArr) {
		
		if (!is_null($dataArr['newkey']) && $dataArr['key']!=$dataArr['newkey']) {
			// user has edited a key
			if (!is_null($dataArr['key']) && strlen($dataArr['key'])>0) {
				$sql = 'UPDATE `'.self::$PREFIX.'options` SET `key`=? '.
					   'WHERE `'.self::$PREFIX.'providers_options_id`=? AND `key`=?';
				$values = array($dataArr['newkey'],$dataArr['option_id'],$dataArr['key']);
				$retval = $dataArr['newkey'];
			} else if (is_null($dataArr['key']) || strlen($dataArr['key'])<=0) {
				$sql = 'INSERT INTO `'.self::$PREFIX.'options` VALUES (?,?,?)';
				$values = array($dataArr['option_id'],$dataArr['newkey'],null);
				$retval = $dataArr['newkey'];
			}			
		} else if (!is_null($dataArr['key']) && !is_null($dataArr['value'])) {
			$sql = 'UPDATE `'.self::$PREFIX.'options` SET `value`=? '.
				   'WHERE `'.self::$PREFIX.'providers_options_id`=? AND `key`=?';
			$values = array($this->or_null($dataArr['value']),$dataArr['option_id'],$dataArr['key']);
			$retval = $dataArr['value'];
		}
		if (isset($sql)) {
			$res = self::$dbToUse->queryPrepared($sql,$values);
			if (AMA_DB::isError($res)) return $res;
			else return str_replace(array("\r\n", "\r", "\n"), "<br />", $retval);
		} else return new AMA_Error();
	}
	
	/**
	 * Deletes a login provider option by key
	 * 
	 * @param number $options_id the providers_options_id of the login provider
	 * @param string $key the key of the element to be deleted
	 * 
	 * @return boolean|AMA_Error
	 * 
	 * @access public
	 */
	public function deleteOptionByKey ($options_id, $key) {
		$sql = 'DELETE FROM `'.self::$PREFIX.'options` WHERE `'.self::$PREFIX.'providers_options_id`=?'.
				' AND `key`=?';
		return self::$dbToUse->queryPrepared($sql, array($options_id,$key));
	}
	
	/**
	 * updates the order before deleting from a table
	 * 
	 * @param boolean $isProvider true if provider, false if option set 
	 * @param number $row_id row id to perform the needed update
	 * 
	 * @access private
	 */
	private function updateOrderBeforeDelete ($isProvider, $row_id) {
		$options = $this->getCommonQueryOptions($isProvider);
		/**
		 * must update options order before deleting. A couple of nested
		 * queries are needed for updating and selecting from the same table.
		 *
		 * The logic is to set order=order-1 to all the options_id following
		 * the passed option AND of the same login provider of the passed options
		 */
		$updateOrder = 'UPDATE `'.$options['table'].'` SET `'.$options['ordercol'].'`=`'.$options['ordercol'].'`-1 '.
				'WHERE `'.$options['ordercol'].'`> ( SELECT * FROM '.
				'(SELECT `'.$options['ordercol'].'` FROM `'.$options['table'].'` WHERE '.
				'`'.$options['col'].'`=? ) AS dummy )';
		
		if ($isProvider===false) {
			$updateOrder .= ' AND `'.self::$PREFIX.'providers_id` = ( SELECT * FROM '.
			'(SELECT `'.self::$PREFIX.'providers_id` FROM `'.
			$options['table'].'` WHERE '.
			'`'.$options['col'].'`=? ) AS dummy2 )';
			$values = array($row_id,$row_id);
		} else {
			$values = $row_id;
		}
		
		return self::$dbToUse->queryPrepared($updateOrder,$values);
	}
	
	/**
	 * gets common options like, table, col and ordercol names
	 * to be used in methods sharing same queries on different tables
	 * 
	 * @param bool $isProvider true if provider, false if option set
	 * 
	 * @return array
	 * 
	 * @access private
	 */
	private function getCommonQueryOptions($isProvider) {
		if ($isProvider===false) {
			$options = array('table'=>self::$PREFIX.'providers_options',
					'col'=>self::$PREFIX.'providers_options_id',
					'ordercol'=>'order');
		} else {
			$options = array('table'=>self::$PREFIX.'providers',
					'col'=>self::$PREFIX.'providers_id',
					'ordercol'=>'displayOrder'
			);
		}
		
		return $options;
	}
	
	/**
	 * calls and sets the parent instance method, and if !MULTIPROVIDER
	 * checks if module_login_providers table is in the provider db.
	 * 
	 * If found, use the provider DB else use the common
	 * 
	 * @param string $dsn
	 */
	static function instance ($dsn = null) {
		if (!MULTIPROVIDER && is_null($dsn)) $dsn = MultiPort::getDSN($GLOBALS['user_provider']);
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