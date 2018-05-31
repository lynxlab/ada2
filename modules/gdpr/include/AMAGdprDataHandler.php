<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAGdprDataHandler extends \AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	const PREFIX = 'module_gdpr_';

	/**
	 * module's own model class namespace (can be the same of the datahandler's tablespace)
	 *
	 * @var string
	 */
	const MODELNAMESPACE = 'Lynxlab\\ADA\\Module\\GDPR\\';

	/**
	 * database to be used (if !MULTIPROVIDER)
	 */
	private static $policiesDB = null;

	/**
	 * key of the objectClasses array used to tell which class name to use for a GdprRequest
	 *
	 * @var string
	 */
	const REQUESTCLASSKEY = 'GdprRequest';

	/**
	 * key of the objectClasses array used to tell which class name to use for a GdprRequestType
	 *
	 * @var string
	 */
	const REQUESTTYPECLASSKEY = 'GdprRequestType';

	/**
	 * key of the objectClasses array used to tell which class name to use for a GdprPolicy
	 *
	 * @var string
	 */
	const POLICYCLASSKEY = 'GdprPolicy';

	/**
	 * Objects class names to be used, to use other classes than the default (declared in the constants)
	 * please use the setObjectClasses and/or setObjectClassesFromRequest methods
	 *
	 * @var array
	 */
	private static $objectClasses = null;

	/**
	 * save a new gdpr request object
	 *
	 * @param array $data
	 * @throws GdprException
	 * @return \Lynxlab\ADA\Module\GDPR\GdprRequest
	 */
	public function saveRequest($data) {

			if (array_key_exists('requestUUID', $data)) {
				// load the request with the passed uuid
				$request = $this->findBy(self::getObjectClasses()[self::REQUESTCLASSKEY], array('uuid' => trim($data['requestUUID'])));
				$request = reset($request);
				if (!($request instanceof GdprRequest)) {
					throw new GdprException(translateFN("Impossibile trovare la richiesta da modificare"));
				} else {
					$isUpdate = true;
					unset($data['requestUUID']);
					if (array_key_exists('requestContent', $data)) {
						$request->setContent(strip_tags(trim($data['requestContent'])));
						unset($data['requestContent']);
					}

					if (array_key_exists('confirmedTs', $data) && is_int($data['confirmedTs'])) {
						$request->setConfirmedTs(intval($data['confirmedTs']));
						unset($data['confirmedTs']);
					}
				}
			} else {
				if (array_key_exists('requestType', $data) && intval($data['requestType'])>0) {
					$type = $this->findBy(self::getObjectClasses()[self::REQUESTTYPECLASSKEY], array('id'=>intval($data['requestType'])));
				} else {
					$type = array();
				}
				if (count($type) === 1) {
					// make a new request
					$className = self::getObjectClasses()[self::REQUESTCLASSKEY];
					$request = new $className(array(), $this);
					$request->setGeneratedTs($this->date_to_ts('now'))->setType(reset($type));
					$isUpdate = false;

					if (!array_key_exists('requestContent', $data)) $data['requestContent'] = '';
					$request->setContent(strip_tags(trim($data['requestContent'])));
					unset($data['requestContent']);

					if (!array_key_exists('selfOpened', $data)) $data['selfOpened'] = 0;
					else $data['selfOpened'] = intval($data['selfOpened'])>0;
					$request->setSelfOpened($data['selfOpened']);
					unset($data['selfOpened']);

					if (array_key_exists('generatedBy', $data) && intval($data['generatedBy'])>0) {
						$request->setGeneratedBy(intval($data['generatedBy']));
						unset($data['generatedBy']);
					} else {
						throw new GdprException(translateFN("Impossibile determinare l'utente per cui generare la richiesta"));
					}

					if (!array_key_exists('dontConfirm', $data) || (array_key_exists('dontConfirm', $data) && intval($data['dontConfirm'])!==1)) {
						$request->setConfirmedTs($request->getGeneratedTs()+1);
					}
					if (array_key_exists('dontConfirm', $data)) unset($data['dontConfirm']);

				} else {
					throw new GdprException(translateFN('Tipo di richiesta non valido'));
				}
			}

			/**
			 * let the object handle the remaining keys of $data with its setters
			 * this is needed to save properites when $className inherits from GdprRequest
			 * and adds its own properties
			 */
			foreach ($data as $key => $val) {
				$setter = GdprBase::SETTERPREFIX.ucfirst($key);
				if (method_exists($request, $setter)) {
					$request->{$setter}(trim($val));
					unset($data[$key]);
				}
			}

			if (strlen($request->getContent())<=0) {
				if ($request->getType()->hasMandatoryContent()) {
					throw new GdprException(translateFN('Il testo non può essere vuoto per il tipo di richiesta'));
				} else $request->setContent(null);
			}

		if ($request->beforeSave($isUpdate)) {
			$fields = $request->toArray();
			$fields['type'] = $fields['type']->getId();
			if (!$isUpdate) {
				$result = $this->executeCriticalPrepared($this->sqlInsert($request::table, $fields), array_values($fields));
			} else {
				unset($fields['uuid']);
				$result = $this->queryPrepared($this->sqlUpdate($request::table, array_keys($fields), 'uuid'), array_values($fields + array($request->getUuid())));
			}

			if (\AMA_DB::isError($result)) {
				throw new GdprException($result->getMessage(), is_numeric($result->getCode()) ? $result->getCode()  : null);
			}
			$request->afterSave($isUpdate);
		} else {
			throw new GdprException(translateFN('Salvataggio richiesta interroto dal codice'));
		}

		return $request;
	}

	/**
	 * save a new privacy policy object, either insert or update
	 *
	 * @param array $data
	 * @throws GdprException
	 * @return \Lynxlab\ADA\Module\GDPR\GdprPolicy|mixed
	 */
	public function savePolicy($data) {

		$isUpdate = false;
		$policy = new GdprPolicy();
		if (array_key_exists('policy_content_id', $data)) {
			// load the policy with the passed uuid
			$policy = $this->findBy(self::getObjectClasses()[self::POLICYCLASSKEY], array('policy_content_id' => trim($data['policy_content_id'])), null, self::getPoliciesDB());
			$policy = reset($policy);
			if (!($policy instanceof GdprPolicy)) {
				throw new GdprException(translateFN("Impossibile trovare la policy da modificare"));
			} else {
				$isUpdate = true;
			}
		}

		if (!$isUpdate) {
			$policy->setTester_pointer($_SESSION['sess_selected_tester'])->setVersion(1);
		} else {
			if ((int)(array_key_exists('newVersion',$data) && intval($data['newVersion'])===1)) {
				unset($data['newVersion']);
				$policy->setVersion($policy->getVersion()+1);
			}
		}

		$policy->setTitle(trim($data['title']))->setContent(trim($data['content']))
			   ->setMandatory((int)(array_key_exists('mandatory',$data) && intval($data['mandatory'])===1))
			   ->setIsPublished((int)(array_key_exists('isPublished',$data) && intval($data['isPublished'])===1))
			   ->setLastEditTS($this->date_to_ts('now'));

		if (strlen($policy->getTitle())<=0) $policy->setTitle(null);
		if (strlen($policy->getContent())<=0) $policy->setContent(null);

		$fields = $policy->toArray();
		if (!$isUpdate) {
			$fields['policy_content_id'] = null;
			$result = self::getPoliciesDB()->executeCriticalPrepared($this->sqlInsert($policy::table, $fields), array_values($fields));
		} else {
			unset($fields['policy_content_id']);
			$result = self::getPoliciesDB()->queryPrepared($this->sqlUpdate($policy::table, array_keys($fields), 'policy_content_id'), array_values($fields + array($policy->getPolicy_content_id())));
		}

		if (\AMA_DB::isError($result)) {
			throw new GdprException($result->getMessage(), is_numeric($result->getCode()) ? $result->getCode()  : null);
		}

		$policy->redirecturl = 'listPolicies.php';
		return $policy;
	}

	/**
	 * Gets the array of the policies accepted by the user
	 *
	 * @param integer $userID
	 * @return array
	 */
	public function getUserAcceptedPolicies($userID) {
		$sql = 'SELECT `id_policy`, `acceptedVersion`, `acceptedTS` FROM `'.self::PREFIX.'policy_utente` WHERE `id_utente`=?';
		$result = self::getPoliciesDB()->getAllPrepared($sql, $userID, AMA_FETCH_ASSOC);
		$retArr = array();
		if (!\AMA_DB::isError($result) && is_array($result) && count($result)>0) {
			foreach ($result as $row) {
				$retArr[$row['id_policy']] = $row;
			}
		}
		return $retArr;
	}

	/**
	 * Gets the array of the published policies objects,
	 * either mandatory or not
	 *
	 * @return array
	 */
	public function getPublishedPolicies() {
		return $this->findBy(self::getObjectClasses()[self::POLICYCLASSKEY], array('isPublished' => 1), null, self::getPoliciesDB());
	}

	/**
	 * Saves the policies accepted by the user
	 *
	 * @param array $data
	 * @param array $mandatoryPolicies
	 * @param array $userAcceptedPolicies
	 * @throws GdprException
	 * @return \stdClass
	 */
	public function saveUserPolicies($data = array(), $mandatoryPolicies = array(), $userAcceptedPolicies = array()) {
		$queries = array();
		$retObj = new \stdClass();
		foreach ($data['acceptPolicy'] as $policyID => $accepted) {
			$policyID = intval($policyID);
			$accepted = intval($accepted);
			$tableName = self::PREFIX.'policy_utente';
			$where = " WHERE `id_utente`={$data['userId']} AND `id_policy`=%d";
			if ($accepted === 0) {
				// user DID NOT accept the $policyID, delete the row
				$queries[] = sprintf("DELETE FROM `%s`".$where, $tableName, $policyID);
			} else if ($accepted === 1) {
				// user accept the $policyID, must do some computations:
				/**
				 * 0. get the policy object
				 * @var GdprPolicy $policyObj
				 */
				$tmp = array_filter($mandatoryPolicies,
					function(GdprPolicy $el) use ($policyID) { return intval($el->getPolicy_content_id()) === $policyID; }
				);
				$policyObj = reset($tmp);

				if (array_key_exists($policyID, $userAcceptedPolicies)) {
					// 1. if the user already accepted the $policyID, must update only if $policyObj version is newer than accepted one
					if ($policyObj->getVersion() > $userAcceptedPolicies[$policyID]['acceptedVersion']) {
						$queries[] = sprintf("UPDATE `%s` SET `acceptedVersion`=%d, `acceptedTS`=%d".$where, $tableName, $policyObj->getVersion(), $this->date_to_ts('now'), $policyID);
					}
				} else {
					// 2. if the user did not already accepted the $policyID, must insert a new row
					$fields = array(
						'id_utente' => $data['userId'],
						'id_policy' => $policyID,
						'acceptedVersion' => $policyObj->getVersion(),
						'acceptedTS' => $this->date_to_ts('now')
					);
					$query = $this->sqlInsert($tableName, $fields);
					// replace question marks with values from fields array
  					$query = preg_replace_callback('/\?/', function($match) use(&$fields)
  						{ return array_shift($fields); }, $query);
  					$queries[] = rtrim($query,';');
				}
			}
		}

		$result = count($queries)>0 ? self::getPoliciesDB()->queryPrepared(implode(';', $queries)) : true;

		if (\AMA_DB::isError($result)) {
			throw new GdprException($result->getMessage(), is_numeric($result->getCode()) ? $result->getCode()  : null);
		} else {
			$retObj->submit = true;
		}
		return $retObj;
	}

	/**
	 * Gets the array of the mandatory policies objects
	 *
	 * @return array
	 */
	public function getMandatoryPolicies() {
		return $this->findBy(self::getObjectClasses()[self::POLICYCLASSKEY], array('mandatory' => 1, 'isPublished' => 1), null, self::getPoliciesDB());
	}

	/**
	 * closes the request with the passed uuid, and set closed by as the optional userID
	 *
	 * @param string|GdprRequest $request passs a GdprRequest object to skip findBy uuid
	 * @param integer $closedBy
	 * @throws GdprException
	 */
	public function closeRequest($request, $closedBy=null) {
		if (is_null($closedBy)) $closedBy = $_SESSION['sess_userObj']->getId();
		if (!($request instanceof GdprRequest)) {
			$tmp = $this->findBy(self::getObjectClasses()[self::REQUESTCLASSKEY],array('uuid'=>$request));
			$request = reset($tmp);
		}
		if ($request instanceof GdprRequest) {
			$result = $this->queryPrepared($this->sqlUpdate(GdprRequest::table, array('closedTs', 'closedBy'), 'uuid'),
					array($this->date_to_ts('now'), $closedBy, $request->getUuid()));
			if (\AMA_DB::isError($result)) {
				throw new GdprException($result->getMessage(), $result->getCode());
			}
		} else throw new GdprException(translateFN('Pratica non trovata'));
	}

	/**
	 * confirms the request with the passed uuid, and set confirmedTs to now
	 *
	 * @param string|GdprRequest $request passs a GdprRequest object to skip findBy uuid
	 * @throws GdprException
	 */
	public function confirmRequest($request) {
		if (!($request instanceof GdprRequest)) {
			$tmp = $this->findBy(self::getObjectClasses()[self::REQUESTCLASSKEY],array('uuid'=>$request,'confirmedTs'=>null));
			$request = reset($tmp);
		}
		if ($request instanceof GdprRequest) {
			$this->saveRequest(array('requestUUID' => $request->getUuid(), 'confirmedTs' => $this->date_to_ts('now')));
		} else throw new GdprException(translateFN('Pratica non trovata'));
	}

	/**
	 * save gdpr user data, with type
	 *
	 * @param array $data
	 * @throws GdprException
	 */
	public function saveGdprUser($data) {
		if (array_key_exists('id_utente', $data)) {
			$sql = "DELETE FROM `".GdprUser::table."` WHERE `id_utente`=?";
			// use queryPrepared because executeCriticalPrepared will return
			// an error if no deleted rows
			$result = $this->queryPrepared($sql, array(intval($data['id_utente'])));
			if (\AMA_DB::isError($result)) {
				throw new GdprException($result->getMessage(), $result->getCode());
			}
			if (array_key_exists('type', $data) && is_array($data['type']) && count($data['type'])>0) {
				foreach ($data['type'] as $gdprUserType) {
					$result = $this->executeCriticalPrepared($this->sqlInsert(
						GdprUser::table,
						array('id_utente' => $data['id_utente'], 'type' => $gdprUserType->getId())),
						array($data['id_utente'], $gdprUserType->getId()));
					if (\AMA_DB::isError($result)) {
						throw new GdprException($result->getMessage(), $result->getCode());
					}
				}
			}
		}
	}

	/**
	 * Performs a request loojup by uuid looping all available testers/providers
	 *
	 * @param string $uuid
	 * @throws GdprException
	 * @return array
	 */
	public static function lookupRequest ($uuid) {
		$retVal = array('uuid' => $uuid);
		$found = false;

		$testers_infoAr = $GLOBALS['common_dh']->get_all_testers(array('id_tester','e_mail','responsabile'));
		if (!\AMA_DB::isError($testers_infoAr)) {
			while (!$found && $tester = current($testers_infoAr)) {
				if (!$found) {
					$gdprAPI = new GdprAPI($tester['puntatore']);
					try {
						$found = $found || (count($gdprAPI->findBy($gdprAPI->getObjectClasses()[self::REQUESTCLASSKEY],array('uuid'=>$uuid)))>0);
					} catch (\Exception $e) {}
				}
				next($testers_infoAr);
			}
		} else {
			throw new GdprException(translateFN('Errore nel caricare i provider'));
		}

		$retVal['found'] = $found;

		if ($found) {
			$retVal['icon'] = 'checkmark';
			$retVal['cssClass'] = 'success';
			$retVal['lookupResponse'] = sprintf(translateFN("La pratica %s è stata trovata"), "<span class='requestUUID'>$uuid</span>");
			$retVal['lookupMessage'] = sprintf(translateFN("Per informazioni sulla pratica scrivere all'indirizzo %s"), '<strong>'.$tester['e_mail'].'</strong>');
			$retVal['lookupMessage'] .= '<small>(PRVD '.$tester['id_tester'].')</small>';
		} else {
			$retVal['icon'] = 'attention';
			$retVal['cssClass'] = 'error';
			$retVal['lookupResponse'] = sprintf(translateFN("La pratica %s non è stata trovata"), "<span class='requestUUID'>$uuid</span>");
			$retVal['lookupMessage'] = '';
		}
		return $retVal;
	}


	/**
	 * loads an array of objects of the passed className with matching where values
	 * and ordered using the passed values by performing a select query on the DB
	 *
	 * @param string $className to use a class from your namespace, this string must start with "\"
	 * @param array $whereArr
	 * @param array $orderByArr
	 * @param \Abstract_AMA_DataHandler $dbToUse object used to run the queries. If null, use 'this'
	 * @throws GdprException
	 * @return array
	 */
	public function findBy($className, array $whereArr = null, array $orderByArr = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		if (stripos($className, '\\') !== 0 &&
			stripos($className, self::MODELNAMESPACE) !== 0) $className = self::MODELNAMESPACE.$className;
		$reflection = new \ReflectionClass($className);
		$properties =  array_map(
			function($el){ return $el->getName(); },
			$reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC)
		);

		// get object properties to be loaded as a kind of join
		$joined = $className::loadJoined();
		// and remove them from the query, they will be loaded afterwards
		$properties = array_diff($properties, $joined);

		$sql = sprintf ("SELECT %s FROM `%s`", implode(',',array_map(function($el){ return "`$el`"; }, $properties)), $className::table);

		if (!is_null($whereArr) && count($whereArr)>0) {
			$invalidProperties = array_diff(array_keys($whereArr),$properties);
			if (count($invalidProperties)>0) {
				throw new GdprException(translateFN('Proprietà WHERE non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' WHERE ';
				$sql .= implode(' AND ', array_map(function($el) use (&$whereArr){
					if (is_null($whereArr[$el])) {
						unset($whereArr[$el]);
						return "`$el` IS NULL";
					} else {
						if (is_numeric($whereArr[$el])) {
							$op = '=';
						} else {
							$op = ' LIKE ';
							$whereArr[$el] = '%'.$whereArr[$el].'%';
						}
						return "`$el`$op?";
					}
				}, array_keys($whereArr)));
			}
		}

		if (!is_null($orderByArr) && count($orderByArr)>0) {
			$invalidProperties = array_diff(array_keys($orderByArr),$properties);
			if (count($invalidProperties)>0) {
				throw new GdprException(translateFN('Proprietà ORDER BY non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' ORDER BY ';
				$sql .= implode(', ', array_map(function($el) use ($orderByArr){
					if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
						return "`$el` ".$orderByArr[$el];
					} else {
						throw new GdprException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
					}
				}, array_keys($orderByArr)));
			}
		}

		if (is_null($dbToUse)) $dbToUse = $this;

		$result = $dbToUse->getAllPrepared($sql, (!is_null($whereArr) && count($whereArr)>0) ? array_values($whereArr): array(), AMA_FETCH_ASSOC);
		if (\AMA_DB::isError($result)) {
			throw new GdprException($result->getMessage(), (int)$result->getCode());
		} else {
			$retArr = array_map(function($el) use ($className, $dbToUse) { return new $className($el, $dbToUse); }, $result);
			// load properties from $joined array
			foreach ($retArr as $retObj) {
				foreach ($joined as $joinKey) {
					$sql = sprintf ("SELECT `%s` FROM `%s` WHERE `%s`=?", $joinKey, $retObj::table, $retObj::key);
					$res = $dbToUse->getAllPrepared($sql, $retObj->{$retObj::GETTERPREFIX.ucfirst($retObj::key)}(), AMA_FETCH_ASSOC);
					if (!\AMA_DB::isError($res)) {
						foreach ($res as $row) {
							$retObj->{$retObj::ADDERPREFIX.ucfirst($joinKey)}($row[$joinKey], $dbToUse);
						}
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
	public function findAll($className, array $orderBy = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		return $this->findBy($className, null, $orderBy, $dbToUse);
	}

	/**
	 * Builds an sql update query as a string
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $whereField
	 * @return string
	 */
	private function sqlUpdate($table, array $fields, $whereField) {
		return sprintf("UPDATE `%s` SET %s WHERE `%s`=?;",
				$table,
				implode(',', array_map(function($el) { return "`$el`=?"; }, $fields)),
				$whereField
		);
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
	 * Gets the AMA_DataHandler object to be used for policies objects
	 *
	 * @return \Abstract_AMA_DataHandler
	 */
	public static function getPoliciesDB() {
		return self::$policiesDB;
	}

	/**
	 * Sets the object classes form the $_REQUEST array
	 */
	public static function setObjectClassesFromRequest() {
		self::setObjectClasses($_REQUEST);
	}

	/**
	 * Sets the object classes from the passed array
	 *
	 * This will not add any key to self::objectClasses but will only
	 * overwrite already defined keys
	 *
	 * @param array $objectClasses
	 */
	public static function setObjectClasses (array &$objectClasses = array()) {
		foreach (array_keys(self::getObjectClasses()) as $key) {
			if (array_key_exists($key, $objectClasses) && strlen(trim($objectClasses[$key]))>0) {
				self::$objectClasses[$key] = trim($objectClasses[$key]);
				unset($objectClasses[$key]);
			}
		}
	}

	/**
	 * Gets the objectClasses array
	 *
	 * @return array|string[]
	 */
	public static function getObjectClasses() {
		if (is_null(self::$objectClasses)) {
			self::$objectClasses = array(
				self::REQUESTCLASSKEY => self::MODELNAMESPACE.self::REQUESTCLASSKEY,
				self::REQUESTTYPECLASSKEY => self::MODELNAMESPACE.self::REQUESTTYPECLASSKEY,
				self::POLICYCLASSKEY => self::MODELNAMESPACE.self::POLICYCLASSKEY
			);
		}
		return self::$objectClasses;
	}

	/**
	 * calls and sets the parent instance method, and if !MULTIPROVIDER
	 * checks if module_gdpr_policy_content table is in the provider db.
	 *
	 * If found, use the provider DB else use the common
	 *
	 * @param string $dsn
	 */
	static function instance ($dsn = null) {
		if (!MULTIPROVIDER && is_null($dsn)) $dsn = \MultiPort::getDSN($GLOBALS['user_provider']);
		$theInstance = parent::instance($dsn);

		if (is_null(self::$policiesDB)) {
			self::$policiesDB = \AMA_Common_DataHandler::instance();
			if (!MULTIPROVIDER && !is_null($dsn)) {
				// must check if passed $dsn has the module login tables
				// execute this dummy query, if result is not an error table is there
				$sql = 'SELECT NULL FROM `'.GdprPolicy::table.'`';
				// must use AMA_DataHandler because we are not able to
				// query AMALoginDataHandelr in this method!
				$ok = \AMA_DataHandler::instance($dsn)->getOnePrepared($sql);
				if (!\AMA_DB::isError($ok)) self::$policiesDB = $theInstance;
			}
		}
		return $theInstance;
	}
}
