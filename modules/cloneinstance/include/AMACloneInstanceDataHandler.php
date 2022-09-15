<?php

/**
 * @package 	cloneinstance module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\CloneInstance;

use ClosedGeneratorException;

require_once(ROOT_DIR . '/include/ama.inc.php');
class AMACloneInstanceDataHandler extends \AMA_DataHandler
{

    /**
     * module's own data tables prefix
     *
     * @var string
     */
    const PREFIX = 'module_cloneinstance_';

    /**
     * module's own model class namespace (can be the same of the datahandler's tablespace)
     *
     * @var string
     */
    const MODELNAMESPACE = 'Lynxlab\\ADA\\Module\\CloneInstance\\';


    public function cloneInstance(int $sourceInstanceID, array $destCoursesID)
    {
        if ($sourceInstanceID > 0) {
            if (count($destCoursesID) > 0) {
                // ok to clone, load instance data
                $instanceArr = $this->course_instance_get($sourceInstanceID);
                if (!\AMA_DB::isError($instanceArr)) {
                    // get subscritions list from its table
                    $subscriptionArr =  $this->getAllPrepared(
                        "SELECT * FROM `iscrizioni` WHERE `id_istanza_corso`=?",
                        [$sourceInstanceID],
                        AMA_FETCH_ASSOC
                    );
                    if (!\AMA_DB::isError($subscriptionArr)) {
                        // get tutors list from its table
                        $tutorsList = $this->getAllPrepared(
                            "SELECT * FROM `tutor_studenti` WHERE `id_istanza_corso`=?",
                            [$sourceInstanceID],
                            AMA_FETCH_ASSOC
                        );
                        if (!\AMA_DB::isError($tutorsList)) {
                            // so far, so good. clone it!
                            $errMsg = null;
                            $this->beginTransaction();
                            foreach ($destCoursesID as $courseID) {
                                $instanceID = $this->course_instance_add($courseID, $instanceArr);
                                if (\intval($instanceID) > 0) {
                                    // add subscriptions
                                    // this will be modified by inserMultiRow
                                    $saveSubsArr = array_map(function ($el) use ($instanceID) {
                                        return (array_merge($el, ['id_istanza_corso' => $instanceID]));
                                    }, $subscriptionArr);
                                    $result = $this->queryPrepared(
                                        $this->insertMultiRow(
                                            $saveSubsArr,
                                            'iscrizioni'
                                        ),
                                        array_values($saveSubsArr)
                                    );
                                    if (!\AMA_DB::isError($result)) {
                                        // add tutors
                                        // this will be modified by inserMultiRow
                                        $saveTutorsArr = array_map(function ($el) use ($instanceID) {
                                            return (array_merge($el, ['id_istanza_corso' => $instanceID]));
                                        }, $tutorsList);
                                        $result = $this->queryPrepared(
                                            $this->insertMultiRow(
                                                $saveTutorsArr,
                                                'tutor_studenti'
                                            ),
                                            array_values($saveTutorsArr)
                                        );
                                        if (!\AMA_DB::isError($result)) {
                                            // done!
                                        } else {
                                            $errMsg = $result->getMessage();
                                        }
                                    } else {
                                        $errMsg = $result->getMessage();
                                    }
                                } else {
                                    $errMsg = translateFN('Errore nella creazione della nuova istanza.');
                                }

                                if (!empty($errMsg)) {
                                    $this->rollBack();
                                    throw new CloneInstanceException($errMsg);
                                }
                            }

                            // final commit or rollback
                            if (!empty($errMsg)) {
                                $this->rollBack();
                                throw new CloneInstanceException($errMsg);
                            } else {
                                $this->commit();
                            }

                        } else {
                            throw new ClosedGeneratorException(translateFN("Errore nella lettura dei tutor dell'istanza"));
                        }
                    } else {
                        throw new CloneInstanceException(translateFN("Errore nella lettura delle iscrizioni all'istanza"));
                    }
                } else {
                    throw new CloneInstanceException(translateFN('Errore nella lettura dati istanza'));
                }
            } else {
                throw new CloneInstanceException(translateFN('Elenco corsi destinazione non valido'));
            }
        } else {
            throw new CloneInstanceException(translateFN('ID istanza non valido'));
        }
    }


    /**
     * loads an array of objects of the passed className with matching where values
     * and ordered using the passed values by performing a select query on the DB
     *
     * @param string $className to use a class from your namespace, this string must start with "\"
     * @param array $whereArr
     * @param array $orderByArr
     * @param \Abstract_AMA_DataHandler $dbToUse object used to run the queries. If null, use 'this'
     * @throws CloneInstanceException
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
        }, $properties)), $className::table)
            . $this->buildWhereClause($whereArr, $properties) . $this->buildOrderBy($orderByArr, $properties);

        if (is_null($dbToUse)) $dbToUse = $this;

        $result = $dbToUse->getAllPrepared($sql, (!is_null($whereArr) && count($whereArr) > 0) ? array_values($whereArr) : array(), AMA_FETCH_ASSOC);
        if (\AMA_DB::isError($result)) {
            throw new CloneInstanceException($result->getMessage(), (int) $result->getCode());
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
     * Builds an sql update query as a string
     *
     * @param string $table
     * @param array $fields
     * @param array $whereArr
     * @return string
     */
    private function sqlUpdate($table, array $fields, &$whereArr)
    {
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
    private function sqlInsert($table, array $fields)
    {
        return sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s);",
            $table,
            implode(',', array_map(function ($el) {
                return "`$el`";
            }, array_keys($fields))),
            implode(',', array_map(function ($el) {
                return "?";
            }, array_keys($fields)))
        );
    }

    /**
     * Builds an sql delete query as a string
     *
     * @param string $table
     * @param array $whereArr
     * @return string
     */
    private function sqlDelete($table, &$whereArr)
    {
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
    private function buildWhereClause(&$whereArr, $properties)
    {
        $sql  = '';
        $newWhere = [];
        if (!is_null($whereArr) && count($whereArr) > 0) {
            $invalidProperties = array_diff(array_keys($whereArr), $properties);
            if (count($invalidProperties) > 0) {
                throw new CloneInstanceException(translateFN('Proprietà WHERE non valide: ') . implode(', ', $invalidProperties));
            } else {
                $sql .= ' WHERE ';
                $sql .= implode(' AND ', array_map(function ($el) use (&$newWhere, $whereArr) {
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
    private function buildOrderBy(&$orderByArr, $properties)
    {
        $sql = '';
        if (!is_null($orderByArr) && count($orderByArr) > 0) {
            $invalidProperties = array_diff(array_keys($orderByArr), $properties);
            if (count($invalidProperties) > 0) {
                throw new CloneInstanceException(translateFN('Proprietà ORDER BY non valide: ') . implode(', ', $invalidProperties));
            } else {
                $sql .= ' ORDER BY ';
                $sql .= implode(', ', array_map(function ($el) use ($orderByArr) {
                    if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
                        return "`$el` " . $orderByArr[$el];
                    } else {
                        throw new CloneInstanceException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
                    }
                }, array_keys($orderByArr)));
            }
        }
        return $sql;
    }

    /**
     * PDO::beginTransaction wrapper
     *
     * @return bool
     */
    private function beginTransaction()
    {
        return $this->getConnection()->connection_object()->beginTransaction();
    }

    /**
     * PDO::rollBack wrapper
     *
     * @return bool
     */
    private function rollBack()
    {
        return $this->getConnection()->connection_object()->rollBack();
    }

    /**
     * PDO::commit wrapper
     *
     * @return bool
     */
    private function commit()
    {
        return $this->getConnection()->connection_object()->commit();
    }

    private function insertMultiRow(&$valuesArray = array(), $tableName = null, $subPrefix = '')
    {

        if (is_array($valuesArray) && count($valuesArray) > 0 && !is_null($tableName)) {

            // 0. init the query
            if (strlen($subPrefix) > 0) $tableName = $subPrefix . '_' . $tableName;

            $sql = 'INSERT INTO `' . $tableName . '` ';
            // 1. get the keys of the passed array
            $fields = array_keys(reset($valuesArray));
            // 2. build the placeholders string
            $flCount = count($fields);
            $lCount = ($flCount  ? $flCount - 1 : 0);
            $questionMarks = sprintf("?%s", str_repeat(",?", $lCount));

            $arCount = count($valuesArray);
            $rCount = ($arCount  ? $arCount - 1 : 0);
            $criteria = sprintf("(" . $questionMarks . ")%s", str_repeat(",(" . $questionMarks . ")", $rCount));
            // 3. build the fields list in sql
            $sql .= '(`' . implode('`,`', $fields) . '`)';
            // 4. append the placeholders
            $sql .= ' VALUES ' . $criteria;
            $toSave = array();
            foreach ($valuesArray as $v) $toSave = array_merge($toSave, array_values($v));
            $valuesArray = $toSave;
            return $sql;
        }
    }
}
