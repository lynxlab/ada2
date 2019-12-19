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
