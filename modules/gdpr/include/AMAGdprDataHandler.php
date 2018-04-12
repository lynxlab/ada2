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
	 * save a new gdpr request object
	 *
	 * @param array $data
	 * @throws GdprException
	 * @return \Lynxlab\ADA\Module\GDPR\GdprRequest
	 */
	public function saveRequest($data) {

		if (array_key_exists('requestType', $data) && intval($data['requestType'])>0) {
			$type = $this->findBy('GdprRequestType', array('id'=>intval($data['requestType'])));
		}

		if (count($type) === 1) {
			if (!array_key_exists('requestContent', $data)) $data['requestContent'] = '';
			$data['requestContent'] = strip_tags(trim($data['requestContent']));

			if (!array_key_exists('selfOpened', $data)) $data['selfOpened'] = 0;
			else $data['selfOpened'] = intval($data['selfOpened']>0);

			$request = new GdprRequest();
			$request->setGeneratedTs($this->date_to_ts('now'))->setType(reset($type))
					->setSelfOpened($data['selfOpened'])->setConfirmedTs($request->getGeneratedTs()+1);

			if ($request->getType()->hasMandatoryContent()) {
				if (strlen($data['requestContent'])>0) {
					$request->setContent($data['requestContent']);
				} else {
					throw new GdprException(translateFN('Il testo non può essere vuoto per il tipo di richiesta'));
				}
			}

			if (array_key_exists('generatedBy', $data) && intval($data['generatedBy'])>0) {
				$request->setGeneratedBy(intval($data['generatedBy']));
			} else {
				throw new GdprException(translateFN("Impossibile determinare l'utente per cui generare la richiesta"));
			}

		} else {
			throw new GdprException(translateFN('Tipo di richiesta non valido'));
		}

		$fields = $request->toArray();
		$fields['type'] = $fields['type']->getId();
		// $fields['type'] = $fields['type']['id'];

		$result = $this->executeCriticalPrepared($this->sqlInsert(GdprRequest::table, $fields), array_values($fields));
		if (\AMA_DB::isError($result)) {
			throw new GdprException($result->getMessage(), $result->getCode());
		}

		return $request;
	}

	/**
	 * closes the request with the passed uuid, and set closed by as the optional userID
	 *
	 * @param string|GdprRequest $request
	 * @param integer $closedBy
	 * @throws GdprException
	 */
	public function closeRequest($request, $closedBy=null) {
		if (is_null($closedBy)) $closedBy = $_SESSION['sess_userObj']->getId();
		if (!($request instanceof GdprRequest)) {
			$tmp = $tmp = $this->findBy('GdprRequest',array('uuid'=>$request));
			$request = reset($tmp);
		}
		if ($request instanceof GdprRequest) {
			$result = $this->executeCriticalPrepared($this->sqlUpdate(GdprRequest::table, array('closedTs', 'closedBy'), 'uuid'),
					array($this->date_to_ts('now'), $closedBy, $request->getUuid()));
			if (\AMA_DB::isError($result)) {
				throw new GdprException($result->getMessage(), $result->getCode());
			}
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
					$GLOBALS['dh'] = AMAGdprDataHandler::instance(\MultiPort::getDSN($tester['puntatore']));
					try {
						$found = $found || (count($GLOBALS['dh']->findBy('GdprRequest',array('uuid'=>$uuid)))>0);
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
	 * @param string $className
	 * @param array $whereArr
	 * @param array $orderByArr
	 * @throws GdprException
	 * @return array
	 */
	public function findBy($className, array $whereArr = null, array $orderByArr = null) {
		if (stripos($className, self::MODELNAMESPACE) !== 0) $className = self::MODELNAMESPACE.$className;
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

		$result = $this->getAllPrepared($sql, (!is_null($whereArr) && count($whereArr)>0) ? array_values($whereArr): array(), AMA_FETCH_ASSOC);
		if (\AMA_DB::isError($result)) {
			throw new GdprException($result->getMessage(), $result->getCode());
		} else {
			$retArr = array_map(function($el) use ($className) { return new $className($el); }, $result);
			// load properties from $joined array
			foreach ($retArr as $retObj) {
				foreach ($joined as $joinKey) {
					$sql = sprintf ("SELECT `%s` FROM `%s` WHERE `%s`=?", $joinKey, $retObj::table, $retObj::key);
					$res = $this->getAllPrepared($sql, $retObj->{$retObj::GETTERPREFIX.ucfirst($retObj::key)}(), AMA_FETCH_ASSOC);
					if (!\AMA_DB::isError($res)) {
						foreach ($res as $row) {
							$retObj->{$retObj::ADDERPREFIX.ucfirst($joinKey)}($row[$joinKey]);
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
	 * @return array
	 */
	public function findAll($className, array $orderBy = null) {
		return $this->findBy($className, null, $orderBy);
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
}
