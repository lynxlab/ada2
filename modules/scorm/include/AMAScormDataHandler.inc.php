<?php
/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version        0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAScormDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_scorm_';

	/**
	 * gets a scorm value using criteria in whereArr
	 *
	 * @param array $whereArr typical array keys are id_utente, scoobject, varname
	 *
	 * @return AMA_Error|string
	 *
	 * @access public
	 */
	public function scormGetValue ($whereArr) {
		if (is_array($whereArr) && count($whereArr)>0) {
			$sql = 'SELECT `varvalue` FROM `'.self::$PREFIX.'scormvars` WHERE 1';
			foreach (array_keys($whereArr) as $key) {
				$sql .= ' AND `'.$key.'` = :'.$key;
			}
			$sql .= ' ORDER BY `timestamp` DESC';
			return $this->getOnePrepared($sql, $whereArr);
		} else {
			return '';
		}
	}

	/**
	 * sets a scorm value using the passed data
	 *
	 * @param array $dataArr typical array keys are id_utente, scoobject, varname, varvalue
	 *
	 * @return AMA_Error|string
	 *
	 * @access public
	 */
	public function scormSetValue ($dataArr) {
		if (is_array($dataArr) && count($dataArr)>0) {
			if (!isset($dataArr['timestamp'])) $dataArr['timestamp'] = $this->date_to_ts('now');

			$insSql = 'INSERT INTO `'.self::$PREFIX.'scormvars` (';
			foreach (array_keys($dataArr) as $key) {
				$insSql .= '`'.$key.'`,';
			}
			$insSql = rtrim($insSql,',').') VALUES (';
			foreach (array_keys($dataArr) as $key) {
				$insSql .= ':'.$key.',';
			}
			$insSql = rtrim($insSql,',').')';
			$retVal = $this->queryPrepared($insSql, $dataArr);
		} else {
			$retVal = new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
		}
		return $retVal;
	}
}
?>
