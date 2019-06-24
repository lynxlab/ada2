<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Badges;

use Ramsey\Uuid\Uuid;

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMABadgesDataHandler extends \AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	const PREFIX = 'module_badges_';

	/**
	 * module's own model class namespace (can be the same of the datahandler's tablespace)
	 *
	 * @var string
	 */
	const MODELNAMESPACE = 'Lynxlab\\ADA\\Module\\Badges\\';

	public function saveBadge($saveData) {
		if (array_key_exists('badgeuuid', $saveData)) {
			$isUpdate = true;
		} else {
			$isUpdate = false;
		}

		if (array_key_exists('badgefilefileNames', $saveData) && is_array($saveData['badgefilefileNames']) && count($saveData['badgefilefileNames'])===1) {
			$badgepng = reset($saveData['badgefilefileNames']);
		}

		unset($saveData['badgefile']);
		unset($saveData['badgefilefileNames']);

		if (!is_dir(MODULES_BADGES_MEDIAPATH)) {
			$oldmask = umask(0);
			$dirok = mkdir (MODULES_BADGES_MEDIAPATH, 0775, true);
			umask($oldmask);
			if ($dirok === false) return new BadgesException(translateFN('Impossibile creare la directory bagdes!'));
		}

		if (!$isUpdate) {
			$uuid = Uuid::uuid4();
			// uuid_bin is only used when inserting, the uuid field (human readable) is MySql virtual generated
			$saveData['uuid_bin'] = $uuid->getBytes();
			$result = $this->executeCriticalPrepared($this->sqlInsert(\Lynxlab\ADA\Module\Badges\Badge::table, $saveData), array_values($saveData));
			unset($saveData['uuid_bin']);
			$saveData['uuid'] = $uuid->toString();
		} else {
			$uuid = Uuid::fromString($saveData['badgeuuid']);
			unset($saveData['badgeuuid']);
			$result = $this->queryPrepared($this->sqlUpdate(\Lynxlab\ADA\Module\Badges\Badge::table, array_keys($saveData), 'uuid_bin'), array_values($saveData + [$uuid->getBytes()]));
			$saveData['uuid'] = $uuid->toString();
		}

		if (!\AMA_DB::isError($result)) {
			$badge = new Badge($saveData);
			if (isset($badgepng)) {
				$this->moveBadgeFile($badgepng, strtoupper($badge->getUuid()).'.png');
			}
			return $badge;
		} else return new BadgesException($result->getMessage());
	}

	private function moveBadgeFile($src, $dest) {
		$src = ADA_UPLOAD_PATH.DIRECTORY_SEPARATOR.MODULES_BADGES_NAME.DIRECTORY_SEPARATOR . $src;
		$dest = MODULES_BADGES_MEDIAPATH . $dest;
		if (!is_dir(MODULES_BADGES_MEDIAPATH)) {
			$oldmask = umask(0);
			mkdir (MODULES_BADGES_MEDIAPATH, 0775, true);
			umask($oldmask);
		}
		rename($src, $dest);
	}

	/**
	 * loads an array of objects of the passed className with matching where values
	 * and ordered using the passed values by performing a select query on the DB
	 *
	 * @param string $className to use a class from your namespace, this string must start with "\"
	 * @param array $whereArr
	 * @param array $orderByArr
	 * @param \Abstract_AMA_DataHandler $dbToUse object used to run the queries. If null, use 'this'
	 * @throws BadgesException
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
				throw new BadgesException(translateFN('Proprietà WHERE non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' WHERE ';
				$sql .= implode(' AND ', array_map(function($el) use (&$whereArr){
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
						return "`$el`$op?";
					}
				}, array_keys($whereArr)));
			}
		}

		if (!is_null($orderByArr) && count($orderByArr)>0) {
			$invalidProperties = array_diff(array_keys($orderByArr),$properties);
			if (count($invalidProperties)>0) {
				throw new BadgesException(translateFN('Proprietà ORDER BY non valide: ').implode(', ', $invalidProperties));
			} else {
				$sql .= ' ORDER BY ';
				$sql .= implode(', ', array_map(function($el) use ($orderByArr){
					if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
						return "`$el` ".$orderByArr[$el];
					} else {
						throw new BadgesException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
					}
				}, array_keys($orderByArr)));
			}
		}

		if (is_null($dbToUse)) $dbToUse = $this;

		$result = $dbToUse->getAllPrepared($sql, (!is_null($whereArr) && count($whereArr)>0) ? array_values($whereArr): array(), AMA_FETCH_ASSOC);
		if (\AMA_DB::isError($result)) {
			throw new BadgesException($result->getMessage(), (int)$result->getCode());
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
	 * calls and sets the parent instance method, and if !MULTIPROVIDER
	 * checks if module_gdpr_policy_content table is in the provider db.
	 *
	 * If found, use the provider DB else use the common
	 *
	 * @param string $dsn
	 */
	/*
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
	*/
}
