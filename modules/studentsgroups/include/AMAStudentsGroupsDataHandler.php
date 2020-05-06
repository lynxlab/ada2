<?php

/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\StudentsGroups;

require_once(ROOT_DIR . '/include/ama.inc.php');
class AMAStudentsGroupsDataHandler extends \AMA_DataHandler
{

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	const PREFIX = 'module_studentsgroups_';

	/**
	 * module's own model class namespace (can be the same of the datahandler's tablespace)
	 *
	 * @var string
	 */
	const MODELNAMESPACE = 'Lynxlab\\ADA\\Module\\StudentsGroups\\';


	/**
	 * loads an array of objects of the passed className with matching where values
	 * and ordered using the passed values by performing a select query on the DB
	 *
	 * @param string $className to use a class from your namespace, this string must start with "\"
	 * @param array $whereArr
	 * @param array $orderByArr
	 * @param \Abstract_AMA_DataHandler $dbToUse object used to run the queries. If null, use 'this'
	 * @throws StudentsGroupsException
	 * @return array
	 */
	public function findBy($className, array $whereArr = null, array $orderByArr = null, \Abstract_AMA_DataHandler $dbToUse = null)
	{
		if (
			stripos($className, '\\') !== 0 &&
			stripos($className, self::MODELNAMESPACE) !== 0
		) {
			$className = self::MODELNAMESPACE . $className;
		}
		$reflection = new \ReflectionClass($className);
		$properties =  array_map(
			function ($el) {
				return $el->getName();
			},
			$reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC)
		);

		// get object properties to be loaded as a kind of join
		$joined = $className::loadJoined();
		// and remove them from the query, they will be loaded afterwards
		$properties = array_diff($properties, array_keys($joined));
		// check for customField class const and explode matching propertiy array
		$properties = $className::explodeArrayProperties($properties);

		$sql = sprintf("SELECT %s FROM `%s`", implode(',', array_map(function ($el) {
			return "`$el`";
		}, $properties)), $className::table);

		if (!is_null($whereArr) && count($whereArr) > 0) {
			$invalidProperties = array_diff(array_keys($whereArr), $properties);
			if (count($invalidProperties) > 0) {
				throw new StudentsGroupsException(translateFN('Proprietà WHERE non valide: ') . implode(', ', $invalidProperties));
			} else {
				$sql .= ' WHERE ';
				$sql .= implode(' AND ', array_map(function ($el) use (&$whereArr) {
					if (is_null($whereArr[$el])) {
						unset($whereArr[$el]);
						return "`$el` IS NULL";
					} else {
						if (is_array($whereArr[$el])) {
							$retStr = '';
							if (array_key_exists('op', $whereArr[$el]) && array_key_exists('value', $whereArr[$el])) {
								$whereArr[$el] = array($whereArr[$el]);
							}
							foreach ($whereArr[$el] as $opArr) {
								if (strlen($retStr) > 0) $retStr = $retStr . ' AND ';
								$retStr .= "`$el` " . $opArr['op'] . ' ' . $opArr['value'];
							}
							unset($whereArr[$el]);
							return '(' . $retStr . ')';
						} else if (is_numeric($whereArr[$el])) {
							$op = '=';
						} else {
							$op = ' LIKE ';
							$whereArr[$el] = '%' . $whereArr[$el] . '%';
						}
						return "`$el`$op?";
					}
				}, array_keys($whereArr)));
			}
		}

		if (!is_null($orderByArr) && count($orderByArr) > 0) {
			$invalidProperties = array_diff(array_keys($orderByArr), $properties);
			if (count($invalidProperties) > 0) {
				throw new StudentsGroupsException(translateFN('Proprietà ORDER BY non valide: ') . implode(', ', $invalidProperties));
			} else {
				$sql .= ' ORDER BY ';
				$sql .= implode(', ', array_map(function ($el) use ($orderByArr) {
					if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
						return "`$el` " . $orderByArr[$el];
					} else {
						throw new StudentsGroupsException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
					}
				}, array_keys($orderByArr)));
			}
		}

		if (is_null($dbToUse)) $dbToUse = $this;

		$result = $dbToUse->getAllPrepared($sql, (!is_null($whereArr) && count($whereArr) > 0) ? array_values($whereArr) : array(), AMA_FETCH_ASSOC);
		if (\AMA_DB::isError($result)) {
			throw new StudentsGroupsException($result->getMessage(), (int) $result->getCode());
		} else {
			$retArr = array_map(function ($el) use ($className, $dbToUse) {
				return new $className($el, $dbToUse);
			}, $result);
			// load properties from $joined array
			foreach ($retArr as $retObj) {
				foreach ($joined as $joinKey => $joinData) {
					if (array_key_exists('idproperty', $joinData)) {
						// this is a 1:1 relation, load the linked object using object property
						$retObj->{$retObj::ADDERPREFIX . ucfirst($joinKey)}(
							$retObj->{$retObj::GETTERPREFIX . ucfirst($joinData['idproperty'])}(),
							$dbToUse
						);
					} else if (array_key_exists('reltable', $joinData)) {
						if (!is_array($joinData['key'])) {
							$joinData['key'] = [
								'name' => $joinData['key'],
								'getter' => $retObj::GETTERPREFIX . ucfirst($joinData['key'])
							];
						}
						// this is a 1:n relation, load the linked objects querying the relation table
						$sql = sprintf("SELECT `%s` FROM `%s` WHERE `%s`=?", $joinData['extkey'], $joinData['reltable'], $joinData['key']['name']);
						$joinRes = $dbToUse->getAllPrepared($sql, [$retObj->{$joinData['key']['getter']}()]);
						if (array_key_exists('callback', $joinData)) {
							$joinRes = $retObj->{$joinData['callback']}($joinRes);
						}
						$retObj->{$retObj::SETTERPREFIX . ucfirst($joinKey)}($joinRes);
					}
				}
			}
			return $retArr;
		}
	}

	/**
	 * loads an array holding all of the passed className objects, possibly ordered.
	 * Actually it's an alias for findBy($className, null, $orderby)
	 *
	 * @param string $className
	 * @param array $orderBy
	 * @param \Abstract_AMA_DataHandler $dbToUse object used to run the queries. If null, use 'this'
	 * @return array
	 */
	public function findAll($className, array $orderBy = null, \Abstract_AMA_DataHandler $dbToUse = null)
	{
		return $this->findBy($className, null, $orderBy, $dbToUse);
	}

	/**
	 * Saves a Groups object
	 *
	 * @param array $saveData
	 * @return \Lynxlab\ADA\Module\StudentsGroups\Groups|StudentsGroupsException
	 */
	public function saveGroup($saveData)
	{
		if (array_key_exists('id', $saveData)) {
			$isUpdate = true;
		} else {
			$isUpdate = false;
		}

		if (array_key_exists('studentsgroupsfilefileNames', $saveData) && is_array($saveData['studentsgroupsfilefileNames']) && count($saveData['studentsgroupsfilefileNames']) === 1) {
			$groupscsv = reset($saveData['studentsgroupsfilefileNames']);
		}

		unset($saveData['studentsgroupsfile']);
		unset($saveData['studentsgroupsfilefileNames']);

		// set to null all empty passed fields
		foreach (array_keys($saveData) as $aKey) {
			if (strpos($aKey, Groups::customFieldPrefix) === 0 && strlen($saveData[$aKey])<=0) {
				$saveData[$aKey] = null;
			}
		}

		if (!$isUpdate) {
			$result = $this->executeCriticalPrepared(
				$this->sqlInsert(
					\Lynxlab\ADA\Module\StudentsGroups\Groups::table,
					$saveData
				),
				array_values($saveData)
			);
			$saveData['id'] = $this->getConnection()->lastInsertID();
		} else {
			$whereArr = ['id' => $saveData['id']];
			unset($saveData['id']);
			$result = $this->queryPrepared(
				$this->sqlUpdate(
					\Lynxlab\ADA\Module\StudentsGroups\Groups::table,
					array_keys($saveData),
					$whereArr
				),
				array_values($saveData + $whereArr)
			);
			$saveData['id'] = $whereArr['id'];
		}

		if (!\AMA_DB::isError($result)) {
			$retArr = ['group' => new Groups($saveData)];
			/**
			 * import the uploaded CSV if it's not an update
			 */
			if (!$isUpdate && isset($groupscsv)) {
				$counters = [
					'registered' => 0,
					'duplicates' => 0,
					'errors' => 0,
					'invalidpasswords' => 0,
					'total' => 0,
				];
				/**
				 * handle uploaded file here!
				 */
				$groupscsv = ADA_UPLOAD_PATH . DIRECTORY_SEPARATOR . MODULES_STUDENTSGROUPS_NAME . DIRECTORY_SEPARATOR . $groupscsv;
				if (is_readable($groupscsv)) {
					$usersToAdd = [];
					$providers = $_SESSION['sess_userObj']->getTesters();
					if (MULTIPROVIDER) {
						array_unshift($providers, ADA_PUBLIC_TESTER);
					}
					$usersToSubscribe = file($groupscsv);

					/* remove blank lines form array */
					foreach ($usersToSubscribe as $key => $value) {
						if (!trim($value))
							unset($usersToSubscribe[$key]);
					}

					foreach ($usersToSubscribe as $row) {
						++$counters['total'];
						$userDataAr = explode(',', $row);
						if (is_array($userDataAr) && count($userDataAr) == MODULES_STUDENTSGROUPS_FIELDS_IN_CSVROW) {
							$userDataAr = array_map('trim', explode(',', $row));
							$subscriberObj = \MultiPort::findUserByUsername($userDataAr[2]);
							if ($subscriberObj == NULL) {
								$subscriberObj = new \ADAUser(
									array(
										'nome' => trim($userDataAr[0]),
										'cognome' => trim($userDataAr[1]),
										'email' => trim($userDataAr[2]),
										'tipo' => AMA_TYPE_STUDENT,
										'username' => trim($userDataAr[2]),
										'stato' => ADA_STATUS_REGISTERED, // these students will never get the confirm email
										'birthcity' => ''
									)
								);
								if (\DataValidator::validate_password($userDataAr[3], $userDataAr[3])) {
									$subscriberObj->setPassword($userDataAr[3]);
									if (defined('MODULES_SECRETQUESTION') && MODULES_SECRETQUESTION === true) {
										$subscriberObj->setEmail('');
									}
									/**
									 * save the user and add it to the providers of the session user (that is a switcher)
									 */
									$result = \MultiPort::addUser($subscriberObj, $providers);
									if ($result > 0) {
										++$counters['registered'];
										$usersToAdd[] = $result;
									} else {
										++$counters['errors'];
									}
								} else {
									++$counters['errors'];
									++$counters['invalidpasswords'];
								}
							} else {
								// user was found by findUserByUsername
								++$counters['duplicates'];
								/**
								 * add the user to the providers of the session user (that is a switcher)
								 */
								\MultiPort::setUser($subscriberObj, $providers, false);
								$usersToAdd[] = $subscriberObj->getId();
							}
						} else {
							// not array or less than expected fields
							++$counters['errors'];
						}
					}
					$retArr['importResults'] = $counters;
					/**
					 * add users to the group
					 */
					$sql = sprintf(
						"INSERT INTO `%s` VALUES %s;",
						\Lynxlab\ADA\Module\StudentsGroups\Groups::utenteRelTable,
						implode(
							',',
							array_map(function ($el) use ($retArr) {
								return '(' . $retArr['group']->getId() . ',' . $el . ')';
							}, $usersToAdd)
						)
					);
					$this->executeCriticalPrepared($sql);
					@unlink($groupscsv);
				}
			}
			return $retArr;
		} else return new StudentsGroupsException($result->getMessage());
	}

	/**
	 * Deletes a Group
	 *
	 * @param array $saveData
	 * @return StudentsGroupsException|bool
	 */
	public function deleteGroup($saveData) {

		$result = $this->queryPrepared(
			$this->sqlDelete(
				\Lynxlab\ADA\Module\StudentsGroups\Groups::table,
				$saveData
			),
			array_values($saveData)
		);

		if (!\AMA_DB::isError($result)) {
			return true;
		} else return new StudentsGroupsException($result->getMessage());

	}

	/**
	 * Builds an sql update query as a string
	 *
	 * @param string $table
	 * @param array $fields
	 * @param array $whereArr
	 * @return string
	 */
	private function sqlUpdate($table, array $fields, &$whereArr) {
		return sprintf(
			"UPDATE `%s` SET %s",
			$table,
			implode(',', array_map(function ($el) {
				return "`$el`=?";
			}, $fields))
		) . $this->buildWhereClause($whereArr, array_keys($whereArr)) . ';';
	}

	/**
	 * Builds an sql insert into query as a string
	 *
	 * @param string $table
	 * @param array $fields
	 * @return string
	 */
	private function sqlInsert($table, array $fields) {
		return sprintf("INSERT INTO `%s` (%s) VALUES (%s);",
				$table,
				implode(',',array_map(function($el){ return "`$el`"; }, array_keys($fields))),
				implode(',',array_map(function($el){ return "?"; }, array_keys($fields)))
		);
	}

	/**
	 * Builds an sql delete query as a string
	 *
	 * @param string $table
	 * @param array $whereArr
	 * @return string
	 */
	private function sqlDelete($table, &$whereArr) {
		return sprintf(
			"DELETE FROM `%s`",
			$table
		) . $this->buildWhereClause($whereArr, array_keys($whereArr)) . ';';
	}

	/**
	 * Builds an sql where clause
	 *
	 * @param array $whereArr
	 * @param array $properties
	 * @return string
	 */
	private function buildWhereClause(&$whereArr, $properties) {
		$sql  ='';
		$newWhere = [];
		if (!is_null($whereArr) && count($whereArr)>0) {
			$invalidProperties = array_diff(array_keys($whereArr),$properties);
			if (count($invalidProperties)>0) {
				throw new StudentsGroupsException(translateFN('Proprietà WHERE non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' WHERE ';
				$sql .= implode(' AND ', array_map(function($el) use (&$newWhere, $whereArr){
					if (is_null($whereArr[$el])) {
						unset($whereArr[$el]);
						return "`$el` IS NULL";
					} else {
						if (is_array($whereArr[$el])) {
							$retStr = '';
							if (array_key_exists('op', $whereArr[$el]) && array_key_exists('value', $whereArr[$el])) {
								$whereArr[$el] = array($whereArr[$el]);
							}
							foreach ($whereArr[$el] as $opArr) {
								if (strlen($retStr)>0) $retStr = $retStr. ' AND ';
								$retStr .= "`$el` ".$opArr['op'].' '.$opArr['value'];
							}
							unset($whereArr[$el]);
							return '('.$retStr.')';
						} else if (is_numeric($whereArr[$el])) {
							$op = '=';
						} else {
							$op = ' LIKE ';
							$whereArr[$el] = '%'.$whereArr[$el].'%';
						}
						$newWhere[$el] = $whereArr[$el];
						return "`$el`$op?";
					}
				}, array_keys($whereArr)));
			}
		}
		$whereArr = $newWhere;
		return $sql;
	}

	/**
	 * Builds an sql orderby clause
	 *
	 * @param array $orderByArr
	 * @param array $properties
	 * @return string
	 */
	private function buildOrderBy(&$orderByArr, $properties) {
		$sql = '';
		if (!is_null($orderByArr) && count($orderByArr)>0) {
			$invalidProperties = array_diff(array_keys($orderByArr),$properties);
			if (count($invalidProperties)>0) {
				throw new StudentsGroupsException(translateFN('Proprietà ORDER BY non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' ORDER BY ';
				$sql .= implode(', ', array_map(function($el) use ($orderByArr){
					if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
						return "`$el` ".$orderByArr[$el];
					} else {
						throw new StudentsGroupsException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
					}
				}, array_keys($orderByArr)));
			}
		}
		return $sql;
	}

}
