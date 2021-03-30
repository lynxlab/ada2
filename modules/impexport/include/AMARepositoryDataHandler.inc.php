<?php
/**
 * @package 	import/export course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
require_once(ROOT_DIR.'/include/ama.inc.php');
class AMARepositoryDataHandler extends \AMA_Common_DataHandler {
	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	const PREFIX = 'module_impexport_';

	/**
	 * Saves export data to the repository table
	 *
	 * @param array $saveData
	 * @return void
	 * @throws Exception
	 */
	public function saveExportData(array $saveData=[]) {
		$isUpdate = array_key_exists('id', $saveData) && intval($saveData['id'])>0;
		if (!array_key_exists('exportTS', $saveData)) $saveData['exportTS'] = $this->date_to_ts('now');
		if (!array_key_exists('exporter_userid', $saveData)) $saveData['exporter_userid'] = $_SESSION['sess_userObj']->getId();
		if (!$isUpdate) {
			$result = $this->executeCriticalPrepared(
				$this->sqlInsert(
					self::PREFIX.'repository',
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
					self::PREFIX.'repository',
					array_keys($saveData),
					$whereArr
				),
				array_values($saveData + $whereArr)
			);
		}

		if (!\AMA_DB::isError($result)) {
			return $saveData;
		} else throw new Exception($result->getMessage(), $result->getCode());
	}

	/**
	 * Gets repository items
	 *
	 * @param array $whereArr
	 * @return array
	 */
	public function getRepositoryList (array $whereArr=[]) {
		$sql = 'SELECT R.*,CONCAT(U.nome," ",U.cognome) AS exporter_fullname FROM `'.self::PREFIX.'repository` R LEFT JOIN `utente` U ON U.`id_utente` = R.`exporter_userid`'
				.$this->buildWhereClause($whereArr, array_keys($whereArr));
		$res = $this->getAllPrepared($sql, array_values($whereArr), AMA_FETCH_ASSOC);

		if (!\AMA_DB::isError($res)) {
			/**
			 * this is needed to instantiate the proper dh and get course data
			 */
			$testersArr = $this->get_all_testers(['id_tester']);
			$cachedValues = ['courseTitles' => [], 'courseProviders' => []];
			$res = array_map(function($element) use ($testersArr, &$cachedValues) {
				if (!array_key_exists($element['id_course'], $cachedValues['courseTitles'])) {
					$provider = array_filter($testersArr, function ($el) use ($element) {
						// var_dump([$el, $element]);
						return $el['id_tester'] == $element['id_tester'];
					});
					$provider = reset($provider);
					$pdh = \AMA_DataHandler::instance(\MultiPort::getDSN($provider['puntatore']));
					$courseData = $pdh->get_course($element['id_course']);
					if (!\AMA_DB::isError($courseData)) {
						$cachedValues['courseTitles'][$element['id_course']] = $courseData['titolo'];
					} else {
						$cachedValues['courseTitles'][$element['id_course']] = translateFN('Corso Sconosciuto');
					}
					$cachedValues['courseProviders'][$element['id_course']] = $provider['puntatore'];
				}
				$element['courseProvider'] = $cachedValues['courseProviders'][$element['id_course']];
				$element['courseTitle'] = $cachedValues['courseTitles'][$element['id_course']];
				$element['exportDateTime'] = ts2dFN($element['exportTS']).' '.ts2tmFN($element['exportTS']);

				return $element;
			}, $res);
			return $res;
		} else return [];
	}

	/**
	 * Deletes export data to the repository table
	 *
	 * @param array $delData
	 * @return boolean
	 * @throws Exception
	 */
	public function deleteExport(array $delData=[]) {
		if (array_key_exists('id', $delData) && intval($delData['id'])>0) {
			$toDelete = $this->getRepositoryList(['id' => $delData['id']]);
			if (is_array($toDelete) && count($toDelete)==1) {
				$toDelete = reset($toDelete);
				$delArr = ['id' => $toDelete['id'] ];
				$result = $this->queryPrepared(
					$this->sqlDelete(
						self::PREFIX.'repository',
						$delArr
					),
					array_values($delArr)
				);
				if (!\AMA_DB::isError($result)) {
					$repodir = MODULES_IMPEXPORT_REPOBASEDIR .$toDelete['id_course'] . DIRECTORY_SEPARATOR . MODULES_IMPEXPORT_REPODIR;
					@unlink($repodir. DIRECTORY_SEPARATOR. $toDelete['filename']);
					@rmdir ($repodir);
					return true;
				} else throw new Exception($result->getMessage());
			} else throw new Exception(translateFN('Errore nella lettura dati'));
		} else throw new Exception(translateFN('ID export non valido'));
	}

	/**
	 * Gets a tester id from its pointer
	 *
	 * @param string $pointer
	 * @return int
	 */
	public function getTesterIDFromPointer($pointer = null) {
		$testerId = null;
		if (is_null($pointer)) $pointer = $_SESSION['sess_selected_tester'];
		$testerInfo = $this->get_tester_info_from_pointer($pointer);
		if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo[0])) {
			$testerId = $testerInfo[0];
		}
		return $testerId;
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
				throw new Exception(translateFN('Proprietà WHERE non valide: ').implode(', ', $invalidProperties));
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
				throw new Exception(translateFN('Proprietà ORDER BY non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' ORDER BY ';
				$sql .= implode(', ', array_map(function($el) use ($orderByArr){
					if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
						return "`$el` ".$orderByArr[$el];
					} else {
						throw new Exception(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
					}
				}, array_keys($orderByArr)));
			}
		}
		return $sql;
	}

}
