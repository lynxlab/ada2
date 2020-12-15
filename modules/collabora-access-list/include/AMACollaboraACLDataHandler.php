<?php

/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\CollaboraACL;

require_once(ROOT_DIR . '/include/ama.inc.php');
class AMACollaboraACLDataHandler extends \AMA_DataHandler
{

    /**
     * module's own data tables prefix
     *
     * @var string
     */
    const PREFIX = 'module_collaboraacl_';

    /**
     * module's own model class namespace (can be the same of the datahandler's tablespace)
     *
     * @var string
     */
    const MODELNAMESPACE = 'Lynxlab\\ADA\\Module\\CollaboraACL\\';

    public function saveGrantedUsers($saveData)
    {
        $isUpdate = array_key_exists('fileAclId', $saveData) && intval($saveData['fileAclId']) > 0;

        try {
            if (!$this->beginTransaction()) {
                throw new CollaboraACLException(translateFN('Errore avvio transazione DB'));
            }
            if (!$isUpdate) {
                $course_ha = $this->get_course($saveData['courseId']);
                if (self::isError($course_ha)) {
                    throw new CollaboraACLException(translateFN('Errore nella verifica del corso'));
                } else {
                    if ($course_ha['media_path'] != "") {
                        $media_path = $course_ha['media_path'];
                    } else {
                        $media_path = MEDIA_PATH_DEFAULT . $course_ha['id_autore'];
                    }
                    $media_path = trim($media_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                }
                // must insert a new row in the file table
                $insertData = [
                    'filepath' => $media_path . $saveData['filename'],
                    'id_corso' => $saveData['courseId'],
                    'id_istanza' => $saveData['instanceId'],
                    'id_nodo' => $saveData['nodeId'],
                    'id_owner' => $saveData['ownerId'],
                ];
                $result = $this->executeCriticalPrepared(
                    $this->sqlInsert(
                        \Lynxlab\ADA\Module\CollaboraACL\FileACL::table,
                        $insertData
                    ),
                    array_values($insertData)
                );
            } else {
                $result = true;
            }

            if (!\AMA_DB::isError($result)) {
                if (!$isUpdate) {
                    $saveData['fileAclId'] = $this->getConnection()->lastInsertID();
                }
                // delete from files_utente table
                $delData = [
                    'file_id' => $saveData['fileAclId'],
                ];
                $this->queryPrepared(
                    $this->sqlDelete(
                        \Lynxlab\ADA\Module\CollaboraACL\FileACL::utenteRelTable,
                        $delData
                    ),
                    array_values($delData)
                );
                if (array_key_exists('grantedUsers', $saveData) && is_array($saveData['grantedUsers'])) {
                    if (count($saveData['grantedUsers']) > 0) {
                        // save in the utenteRelTable
                        $insertData = array_map(function($el) use ($saveData) {
                            return [
                                'file_id' => $saveData['fileAclId'],
                                'utente_id' => $el,
                                'permissions' => CollaboraACLActions::READ_FILE,
                            ];
                        }, $saveData['grantedUsers']);
                        $result = $this->queryPrepared(
                            $this->insertMultiRow(
                                $insertData,
                                \Lynxlab\ADA\Module\CollaboraACL\FileACL::utenteRelTable
                            ),
                            array_values($insertData)
                        );
                        if (\AMA_DB::isError($result)) {
                            throw new CollaboraACLException($result->getMessage());
                        }
                    } else {
                        // no granted users, this will become a public file
                        // must delete from the files table
                        $delData = [
                            'id' => $saveData['fileAclId']
                        ];
                        $this->queryPrepared(
                            $this->sqlDelete(
                                \Lynxlab\ADA\Module\CollaboraACL\FileACL::table,
                                $delData
                            ),
                            array_values($delData)
                        );
                        $saveData['fileAclId'] = -1;
                    }
                }
                $this->commit();
                return $saveData;
            } else {
                throw new CollaboraACLException($result->getMessage());
            }
        } catch (\Exception $e) {
            $this->rollBack();
            return ($e);
        }
    }

    public function deleteFileACL($id)
    {
        // delete from the files table, will delete the relation as well
        $delData = [
            'id' => $id,
        ];
        $this->beginTransaction();
        $result = $this->queryPrepared(
            $this->sqlDelete(
                \Lynxlab\ADA\Module\CollaboraACL\FileACL::table,
                $delData
            ),
            array_values($delData)
        );
        if (\AMA_DB::isError($result)) {
            $this->rollBack();
            return $result;
        } else {
            $this->commit();
            return true;
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
     * @throws CollaboraACLException
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
            throw new CollaboraACLException($result->getMessage(), (int) $result->getCode());
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
                        $joinSelFields = '';
                        if (array_key_exists('relproperties', $joinData) && is_array($joinData['relproperties']) && count($joinData['relproperties']) > 0) {
                            $joinSelFields = ',`' . implode('`,`', $joinData['relproperties']) . '`';
                        }
                        // this is a 1:n relation, load the linked objects querying the relation table
                        $sql = sprintf("SELECT `%s`%s FROM `%s` WHERE `%s`=?", $joinData['extkey'], $joinSelFields, $joinData['reltable'], $joinData['key']['name']);
                        $joinRes = $dbToUse->getAllPrepared($sql, [$retObj->{$joinData['key']['getter']}()], AMA_FETCH_ASSOC);
                        if (array_key_exists('callback', $joinData)) {
                            if (is_callable($joinData['callback'])) {
                                $joinRes = $joinData['callback']($joinRes);
                            } else if (method_exists($retObj, $joinData['callback'])) {
                                $joinRes = $retObj->{$joinData['callback']}($joinRes);
                            }
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
     * @param string $whereField
     * @return string
     */
    private function sqlUpdate($table, array $fields, $whereField)
    {
        return sprintf(
            "UPDATE `%s` SET %s WHERE `%s`=?;",
            $table,
            implode(',', array_map(function ($el) {
                return "`$el`=?";
            }, $fields)),
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
                throw new CollaboraACLException(translateFN('Proprietà WHERE non valide: ') . implode(', ', $invalidProperties));
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
                throw new CollaboraACLException(translateFN('Proprietà ORDER BY non valide: ') . implode(', ', $invalidProperties));
            } else {
                $sql .= ' ORDER BY ';
                $sql .= implode(', ', array_map(function ($el) use ($orderByArr) {
                    if (in_array($orderByArr[$el], array('ASC', 'DESC'))) {
                        return "`$el` " . $orderByArr[$el];
                    } else {
                        throw new CollaboraACLException(sprintf(translateFN("ORDER BY non valido %s per %s"), $orderByArr[$el], $el));
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
}
