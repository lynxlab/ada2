<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

require_once($root_dir.'/include/ama.inc.php');
class AMATestDataHandler extends AMA_DataHandler {

	public static $PREFIX = 'module_test_';

    /**
     * Returns an instance of AMA_DataHandler.
     *
     * @param  string $dsn - optional, a valid data source name
	 *
     * @return an instance of AMA_DataHandler
     */
    static function instance($dsn = null) {
        if(self::$instance === NULL) {
            self::$instance = new AMATestDataHandler($dsn);
        }
        else {
            self::$instance->setDSN($dsn);
        }
        //return null;
        return self::$instance;
    }

    /**
     * get the available test/survey list
     *
     * @access public
     *
     * @param $id_instance - course instance id
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     */
    public function test_getList($id_instance,$id_nodo_riferimento = false) {
        $sql = "SELECT *
                FROM `".self::$PREFIX."nodes` t
                WHERE t.`id_istanza`= ?";
        if (!$id_nodo_riferimento) {
			$values[] = $id_nodo_riferimento;
            $sql.= " AND t.`id_nodo_riferimento` = ?";
        }
        $res =  $this->getAllPrepared($sql, array($id_instance), AMA_FETCH_ASSOC);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (!empty($res)) {
            foreach($res as $v) {
                $tests[$v['id_nodo']] = $v;
            }
            unset($res);
            return $tests;
        }
        else {
            return array();
        }
    }

    /**
     * adds a test-type node to database
     *
     * @access public
     *
     * @param $data - an associative array containing all the node's data
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_addNode($data) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;

        //validazione campi
        $d = array(
			'id_corso','id_posizione','id_utente','id_istanza','nome','titolo','consegna',
			'testo','tipo','data_creazione','ordine','id_nodo_parent','id_nodo_radice',
			'id_nodo_riferimento','livello','versione','n_contatti','icona','colore_didascalia',
			'colore_sfondo','correttezza','copyright','didascalia','durata','titolo_dragdrop',
		);
        foreach($data as $k=>$v) {
			if (!in_array($k, $d)) {
				unset($data[$k]);
			}
        }
		$data['data_creazione'] = time();
        //fine validazione campi
		
        $keys = array_keys($data);
		$array_values = array_values($data);
		$placeholders = array_fill(0, count($data), '?');

        $sql = "INSERT INTO `".self::$PREFIX."nodes` (".implode(',',$keys).") VALUES (".implode(",",$placeholders).")";
        ADALogger::log_db("trying inserting the test node: ".$sql);

		$res = $this->queryPrepared($sql,$array_values);
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in test_addNode.".AMA_SEP.": ".$res->getMessage());
        }
        return $db->lastInsertID();
    }

    /**
     * updates a test-type node to database
     *
     * @access public
     *
     * @param $id_nodo - id node
     * @param $d - an associative array containing all the node's data
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_updateNode($id_nodo,$data) {
        //validazione campi
        $d = array(
            'nome','titolo','consegna','testo','tipo','ordine','id_nodo_parent','id_nodo_radice',
			'id_nodo_riferimento','livello','versione','n_contatti','icona','colore_didascalia',
            'colore_sfondo','correttezza','copyright','didascalia','durata','titolo_dragdrop',
		);

        foreach($data as $k=>$v) {
			if (!in_array($k, $d)) {
				unset($data[$k]);
			}
        }
        //fine validazione campi

		$sql = array();
		foreach($data as $k=>$v) {
			$sql[$k] = "`".$k."` = ?";
		}

		$array_values = array_merge(array_values($data),array($id_nodo));
		$sql = "UPDATE `".self::$PREFIX."nodes` SET ".implode(",",$sql)." WHERE `id_nodo`= ?";
        $res = $this->queryPrepared($sql,$array_values);

        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in test_updateNode.".AMA_SEP.": ".$res->getMessage());
        }
		else {
			$this->test_countVersion($id_nodo);
			return true;
		}
    }

    /**
     * delete test by radix
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_deleteByRadixTest($id_node) {
        $values = array($id_node);
        $sql = "DELETE FROM `".self::$PREFIX."nodes` t
				WHERE t.`id_nodo_radice` = ?";

        $result = $this->queryPrepared($sql, $values);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
		else {
			return $this->test_deleteNodeTest($id_node);
		}
    }

    /**
     * delete node
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_deleteNodeTest($id_node) {
		$res = $this->test_getNode($id_node);
		if (self::isError($res)) {
			return new AMA_Error(AMA_ERR_REMOVE);
		}
		else {
			if (!empty($res['id_nodo_riferimento'])) {
				//if exists, delete also standard ada node
				$this->remove_node($res['id_nodo_riferimento']);
			}
		}

        $values = array($id_node);
        $sql = "DELETE FROM `".self::$PREFIX."nodes` WHERE `id_nodo` = ?";

		$result = $this->queryPrepared($sql, $values);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
		else {
			$res = $this->test_getNodesByParent($id_node);
			$return = true;
			if (!empty($res)) {
				foreach($res as $k=>$r) {
					$return = $return && $this->test_deleteNodeTest($r['id_nodo']);
				}
			}
			return $return;
		}
    }

    /**
     * get a node from test table
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or an array (empty if the node doesn't exists)
     *
     */
    public function test_getNode($id_node) {
        $values = array($id_node);
        $sql = "SELECT *
                FROM `".self::$PREFIX."nodes` t
                WHERE t.`id_nodo` = ?";
        $res =  $this->getRowPrepared($sql, $values, AMA_FETCH_ASSOC);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            return $res;
        }
    }

    /**
     * get nodes from test table
     *
     * @access public
     *
     * @param $where - array with key (field) and values (value)
     *
     * @return an error if something goes wrong or an array (empty if the node doesn't exists)
     *
     */
    public function test_getNodes($where) {
        // $values = array($id_node);
        $sql = "SELECT *
                FROM `".self::$PREFIX."nodes` t
                WHERE true";

		if (is_array($where) && !empty($where)) {
			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND t.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else if (is_array($v) && !empty($v)) {
					$sql.=" AND t.`".$k."` IN ('".implode("','",$v)."')";
					unset($where[$k]);
				}
				else if (strpos($v,'LIKE ') === 0) {
					$sql.=" AND t.`".$k."` LIKE '".str_replace('LIKE ','',$v)."'";
					unset($where[$k]);
				}
				else {
					$sql.=" AND t.`".$k."` = ?";
				}
			}
		}

        $tmp_res =  $this->getAllPrepared($sql, array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            $res = array();
			if (!empty($tmp_res)) {
				foreach($tmp_res as $k=>$v) {
					$res[$v['id_nodo']] = $v;
				}
			}
            unset($tmp_res);
            return $res;
        }
    }

    /**
     * gets nodes from test table by node radix
     *
     * @access public
     *
     * @param $id_node - node id
     * @param $where - array with key (field) and values (value)
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     *
     */
    public function test_getNodesByRadix($id_node,$where = array()) {
        $id_node = $this->sql_prepared($id_node);
        $sql = "SELECT *
				FROM `".self::$PREFIX."nodes` t
				WHERE t.`id_nodo` = ".$id_node."
				OR t.`id_nodo_radice` = ".$id_node;

		if (is_array($where) && !empty($where)) {
			$sql.= " AND (true ";
			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND t.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else if (is_array($v) && !empty($v)) {
					$sql.=" AND t.`".$k."` IN ('".implode("','",$v)."')";
					unset($where[$k]);
				}
				else if (strpos($v,'LIKE ') === 0) {
					$sql.=" AND t.`".$k."` LIKE '".str_replace('LIKE ','',$v)."'";
					unset($where[$k]);
				}
				else {
					$sql.=" AND t.`".$k."` = ?";
				}
			}
			$sql.= ")";
		}
		$sql.= " ORDER BY t.`id_nodo_parent` ASC, t.`ordine` ASC";
        $tmp_res =  $this->getAllPrepared($sql, array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            $res = array();
			if (!empty($tmp_res)) {
				foreach($tmp_res as $k=>$v) {
					$res[$v['id_nodo']] = $v;
				}
			}
            unset($tmp_res);
            return $res;
        }
    }


    /**
     * gets topic nodes from test table by node radix
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     *
     */
    public function test_getTopicNodesByRadix($id_node) {
		$sql = "SELECT *
				FROM `".self::$PREFIX."nodes` t
				WHERE t.`tipo` LIKE '3%'
				AND t.`id_nodo_radice` = ?
				ORDER BY t.`id_nodo_parent` ASC, t.`ordine` ASC";

		$tmp_res = $this->getAllPrepared($sql,array($id_node), AMA_FETCH_ASSOC);
        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            $res = array();
			if (!empty($tmp_res)) {
				foreach($tmp_res as $k=>$v) {
					$res[$v['id_nodo']] = $v;
				}
			}
            unset($tmp_res);
            return $res;
        }
    }

    /**
     * gets nodes from test table by id_nodo_parent and (eventually) by id_nodo too
     *
     * @access public
     *
     * @param $id_nodo_parent - id_node_parent id
     * @param $id_nodo - id_node id
     * @param $where - array with key (field) and values (value)
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     *
     */
    public function test_getNodesByParent($id_nodo_parent, $id_nodo = null, $where = array()) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;

		$id_nodo_parent = $this->sql_prepared($id_nodo_parent);

		if (!is_null($id_nodo)) {
			$id_nodo = $this->sql_prepared($id_nodo);
		}

        $sql = "SELECT *
				FROM `".self::$PREFIX."nodes` t
				WHERE t.`id_nodo_parent` = ".$id_nodo_parent;
		if (!is_null($id_nodo)) {
		$sql.= " OR t.`id_nodo` = ".$id_nodo;
		}
		if (is_array($where) && !empty($where)) {
			$sql.= " AND (true ";
			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND t.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else if (is_array($v) && !empty($v)) {
					$sql.=" AND t.`".$k."` IN ('".implode("','",$v)."')";
					unset($where[$k]);
				}
				else if (strpos($v,'LIKE ') === 0) {
					$sql.=" AND t.`".$k."` LIKE '".str_replace('LIKE ','',$v)."'";
					unset($where[$k]);
				}
				else {
					$sql.=" AND t.`".$k."` = ?";
				}
			}
			$sql.= ")";
		}
		$sql.= " ORDER BY t.`ordine` ASC";
        $tmp_res =  $this->getAllPrepared($sql, array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            $res = array();
			if (!empty($tmp_res)) {
				foreach($tmp_res as $k=>$v) {
					$res[$v['id_nodo']] = $v;
				}
			}
            unset($tmp_res);
            return $res;
        }
    }

    /**
     * gets given answers for a specific test
     *
     * @access public
     *
     * @param $id_history_test - id history test
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     *
     */
    public function test_getGivenAnswers($id_history_text) {
        $sql = "SELECT *
				FROM `".self::$PREFIX."history_answer` ha
				WHERE ha.`id_history_test` = ?";
        $res =  $this->getAllPrepared($sql, array($id_history_text), AMA_FETCH_ASSOC);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            return $res;
        }
    }

    /**
     * gets an answer
     *
     * @access public
     *
     * @param $id_answer - id answer
     *
     * @return an error if something goes wrong or an array (empty if there are no tests)
     *
     */
    public function test_getAnswer($id_answer) {
        $sql = "SELECT *
				FROM `".self::$PREFIX."history_answer` ha
				WHERE ha.`id_answer` = ?";
        $res =  $this->getAllPrepared($sql, array($id_answer), AMA_FETCH_ASSOC);

        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            return $res;
        }
    }

	/**
	 * Refresh end time of a test attempt
	 *
	 * @param $id_history_test the id of the node to be updated
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_updateEndTestDate($id_history_test) {
		$sql = "UPDATE `".self::$PREFIX."history_test` SET
				`data_fine` = ?
				WHERE `id_history_test` = ?";

		$data_fine = time();

		$values = array($data_fine,$id_history_test);

		$result = $this->queryPrepared($sql, $values);
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_UPDATE);
		}

		return true;
	}

	/**
	 * Removes a test's answer node.
	 *
	 * @param $id_answer the id of the node to be removed or an array with ids
	 *
	 * @return an AMA_Error object if something goes wrong, true on success
	 *
	 */
	public function test_removeTestAnswerNode($id_answer) {
		if (!is_array($id_answer)) {
			$id_answer = array($id_answer);
		}
		$sql = "DELETE FROM history_answer WHERE id_answer IN (".implode(',',$id_answer).")";

		$res = $this->executeCritical($sql);
		if(self::isError($res)) {
			return $res;
		}
		else return true;
	}

	/**
	 * Retrieves history test record
	 *
     * @param $where - array with key (field) and values (value)
	 *
	 * @return an AMA_Error object if something goes wrong, true on success
	 *
	 */
	public function test_getHistoryTest($where = array()) {
		$sql = "SELECT *
				FROM  `".self::$PREFIX."history_test` ht
				WHERE 1";

		if (!empty($where)) {
			if (!is_array($where) && intval($where)>0) {
				$where = array('id_history_test'=>$where);
			}

			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND ht.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else {
					$sql.=" AND ht.`".$k."` = ?";
				}
			}
		}

		$sql.= " ORDER BY ht.`data_inizio` ASC, ht.`data_fine` ASC";

		$res = $this->getAllPrepared($sql,array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
		else {
			return $res;
		}
	}


	/**
	 * Retrieves history test record joined with other tables
	 *
     * @param $where - array with key (field) and values (value)
	 *
	 * @return an AMA_Error object if something goes wrong, true on success
	 *
	 */
	public function test_getHistoryTestJoined($where = array(),$tipo=null) {
		$sql = "SELECT
					ht.*,
					u.`id_utente`, u.`nome`, u.`cognome`,
					i.`title` as nome_istanza,
					c.`titolo` as nome_corso,
					t.`tipo`, t.`titolo`, t.`nome` as nome_test, t.`correttezza`
				FROM `".self::$PREFIX."history_test` ht
				JOIN `".self::$PREFIX."nodes` t ON (ht.`id_nodo` = t.`id_nodo`)
				JOIN `utente` u ON (ht.`id_utente` = u.`id_utente`)
				JOIN `modello_corso` c ON (ht.`id_corso` = c.`id_corso`)
				LEFT OUTER JOIN `istanza_corso` i ON (ht.`id_istanza_corso` = i.`id_istanza_corso`)
				WHERE 1";
		if (!is_null($tipo)) {
			$sql.= " AND t.`tipo` LIKE '".$tipo."'";
		}

		if (!empty($where)) {
			if (!is_array($where) && intval($where)>0) {
				$where = array('id_history_test'=>$where);
			}

			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND ht.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else if (is_array($v) && !empty($v)) {
					$sql.=" AND ht.`".$k."` IN ('".implode("','",$v)."')";
					unset($where[$k]);
				}
				else if (strpos($v,'LIKE ') === 0) {
					$sql.=" AND ht.`".$k."` LIKE '".str_replace('LIKE ','',$v)."'";
					unset($where[$k]);
				}
				else {
					$sql.=" AND ht.`".$k."` = ?";
				}
			}
		}

		$sql.= " ORDER BY ht.`data_inizio` ASC, ht.`data_fine` ASC";

		$res = $this->getAllPrepared($sql,array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
		else {
			return $res;
		}
	}


	/**
	 * Retrieves test points from previously saved answers
	 *
	 * @param $id_history_test the id of the history test record
	 *
	 * @return an AMA_Error object if something goes wrong, true on success
	 *
	 */
	public function test_retrieveTestPoints($id_history_test) {
		$values = array($id_history_test);
		$sql = "SELECT SUM(ha.`punteggio`)
				FROM  `".self::$PREFIX."history_answer` ha
				WHERE ha.`id_history_test` = ?";
		$res = $this->getOnePrepared($sql,$values);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
		else {
			return $res;
		}
	}

	/**
	 * Records a test attempt
	 *
	 * @param $id_test the id of the node to be removed
 	 * @param $id_istanza_corso the id of the node to be removed
 	 * @param $id_studente the id of the node to be removed
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_recordAttempt($id_test,$id_istanza_corso,$id_corso,$id_utente,$domande) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;

		$sql = "INSERT INTO `".self::$PREFIX."history_test` SET
				`id_nodo` = ?,
				`id_istanza_corso` = ?,
				`id_corso` = ?,
				`id_utente` = ?,
				`data_inizio` = ?,
				`domande` = ?";

		$data_inizio = time();

		$values = array($id_test,$id_istanza_corso,$id_corso,$id_utente,$data_inizio,$domande);

		$result = $this->queryPrepared($sql, $values);
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_ADD);
		}

		return $db->lastInsertID();
	}

    /**
     * Needed to count visits of a test
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or an array (empty if the node doesn't exists)
     *
     */
    public function test_countVisit($id_node) {
        $sql = "UPDATE `".self::$PREFIX."nodes`
				SET `n_contatti`=`n_contatti`+1
                WHERE `id_nodo` = ".$id_node;
        $res =  $this->executeCritical($sql);

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            return $res;
        }
    }

    /**
     * Needed to count changes of a test
     *
     * @access public
     *
     * @param $id_node - node id
     *
     * @return an error if something goes wrong or an array (empty if the node doesn't exists)
     *
     */
    public function test_countVersion($id_node) {
        $sql = "UPDATE `".self::$PREFIX."nodes`
				SET `versione`=`versione`+1
                WHERE `id_nodo` = ?";
        $res =  $this->queryPrepared($sql,array($id_node));

        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            return $res;
        }
    }

	/**
	 * Records a test attempt
	 *
	 * @param $id_history_test id
 	 * @param $end_date test's delivery date and time
 	 * @param $points test's points
	 * @param $repeatable boolean, sets the test repeatable or not
 	 * @param $min_barrier_point minimum barrier points to gain a new level
 	 * @param $level_gained new level gained by user
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_saveTest($id_history_test,$tempo_scaduto=0,$points=0,$repeatable=false,$min_barrier_point=0,$level_gained=null) {
		$sql = "UPDATE `".self::$PREFIX."history_test` SET
				`data_fine` = ?,
				`tempo_scaduto` = ?,
				`punteggio_realizzato` = ?,
				`ripetibile` = ?,
				`punteggio_minimo_barriera` = ?,
				`livello_raggiunto` = ?,
				`consegnato` = 1
				WHERE `id_history_test` = ?";

		$repeatable = $repeatable ? 1 : 0;
		$end_date = time();

		$values = array($end_date,$tempo_scaduto,$points,$repeatable,$min_barrier_point,$level_gained,$id_history_test);

		$result = $this->queryPrepared($sql, $values);
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_UPDATE);
		}

		return true;
	}

	/**
	 * Recaulcates history test points
	 *
	 * @param $id_history_test id
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_recalculateHistoryTestPoints($id_history_test) {
		$sql = "UPDATE `".self::$PREFIX."history_test` SET
				`ripetibile` = ?
				WHERE `id_history_test` = ?";

		$sql = "UPDATE `module_test_history_test` t 
				SET t.`punteggio_realizzato` =
				(
					SELECT SUM(a.`punteggio`)
					FROM `module_test_history_answer` a
					WHERE a.`id_history_test` = ?
				)
				WHERE t.`id_history_test` = ?";

		$result = $this->queryPrepared($sql, array($id_history_test,$id_history_test));
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_UPDATE);
		}

		return true;
	}

	/**
	 * Set repeatable for a specific test instance
	 *
	 * @param $id_history_test id
	 * @param $repeatable boolean, sets the test repeatable or not
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_setHistoryTestRepeatable($id_history_test,$repeatable) {
		$sql = "UPDATE `".self::$PREFIX."history_test` SET
				`ripetibile` = ?
				WHERE `id_history_test` = ?";

		$repeatable = $repeatable ? 1 : 0;

		$result = $this->queryPrepared($sql, array($repeatable, $id_history_test));
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_UPDATE);
		}

		return true;
	}

	/**
	 * Saves a test answer
	 *
	 * @param $id_history_test history_test id
	 * @param $student_id student id
 	 * @param $topic_id topic id that contains the question
 	 * @param $question_id question id
	 * @param $course_instance_id course instance id
 	 * @param $answer question's answer: could be a reference to a test node, a serialized object or an open answer
 	 * @param $points points gained
 	 * @param $attachment attachment url
	 *
	 * @return an AMA_Error object if something goes wrong, the record's id on success
	 */
	public function test_saveAnswer($id_history_test,$student_id,$topic_id,$question_id,$course_id,$course_instance_id,$answer,$points,$attachment='') {
		if (is_null($id_history_test)) {
			return false;
		}
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;

		$sql = "INSERT INTO `".self::$PREFIX."history_answer` SET
				`id_history_test` = ?,
				`id_utente` = ?,
				`id_topic` = ?,
				`id_nodo` = ?,
				`id_corso` = ?,
				`id_istanza_corso` = ?,
				`risposta` = ?,
				`punteggio` = ?,
				`allegato` = ?,
				`data` = ?";

		$data = time();

		$values = array($id_history_test,$student_id,$topic_id,$question_id,$course_id,$course_instance_id,$answer,$points,$attachment,$data);

		$result = $this->queryPrepared($sql, $values);
		if(self::isError($result)) {
			return new AMA_Error(AMA_ERR_ADD);
		}

		return $db->lastInsertID();
	}

	/**
	 * Returns all sibling nodes of a given node id (the given node is included)
	 *
	 * @param $id_nodo node id
	 *
	 * @return an AMA_Error object if something goes wrong, an array with siblings nodes
	 */
	public function test_getSiblingsNode($id_nodo) {
		$nodo = $this->test_getNode($id_nodo);
		if (self::isError($nodo)) return $nodo;

		$siblings = $this->test_getNodesByParent($nodo['id_nodo_parent']);
		if (self::isError($siblings)) return $siblings;

		return $siblings;
	}

	/**
	 * Move a node up or down by 1 position (and reorder all other nodes)
	 *
	 * @param $id_nodo node id
	 * @param $direction direction (a string: 'up' or 'down')
	 *
	 * @return boolean
	 */
	public function test_moveNode($id_nodo,$direction) {
		$siblings = $this->test_getSiblingsNode($id_nodo);
		if (self::isError($siblings)) return false;

		$i=0;
		$nodes = array();
		foreach($siblings as $k=>$v) {
			$v['ordine'] = $i;
			$nodes[$i] = $v;
			if ($id_nodo == $v['id_nodo']) {
				$ordine = $i;
			}
			$i++;
		}
		unset($siblings);

		$moved_items = false;
		if ($direction == 'up' && $ordine > 0) {
			$nodes[$ordine-1]['ordine']++;
			$nodes[$ordine]['ordine']--;
			$moved_items = true;
		}
		else if ($direction == 'down' && $ordine < count($nodes)-1) {
			$nodes[$ordine+1]['ordine']--;
			$nodes[$ordine]['ordine']++;
			$moved_items = true;
		}

		if ($moved_items) {
			foreach($nodes as $k=>$v) {
				$v['ordine']++;
				$res = $this->test_updateNode($v['id_nodo'], array('ordine'=>$v['ordine']));
				if (self::isError($res)) return false;
			}
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns all sibling nodes of a given node id (the given node is included)
	 *
	 * @param $id_course course id
	 * @param $id_instance instance id
	 *
	 * @return an AMA_Error object if something goes wrong, an array with siblings nodes
	 */
	public function getStudentsScores($id_course, $id_instance) {
		$sql = "SELECT t.`tipo`, ht.`id_utente`, ht.`domande`, ht.`punteggio_realizzato` as punteggio
				FROM `".self::$PREFIX."history_test` ht
				JOIN `".self::$PREFIX."nodes` t ON (t.`id_nodo` = ht.`id_nodo`)
				WHERE ht.`id_corso` = ?
				AND ht.`id_istanza_corso` = ?
				AND ( ht.`consegnato` = 1 OR ht.`tempo_scaduto` = 1 )";
		$res = $this->getAllPrepared($sql,array($id_course,$id_instance), AMA_FETCH_ASSOC);
		if (self::isError($res)) return $res;

		$array = array();
		if(!empty($res)) {
			foreach($res as $v) {
				if ($v['tipo']{0} == ADA_TYPE_TEST) {
					$key = 'score_test';
				}
				else if ($v['tipo']{0} == ADA_TYPE_SURVEY) {
					$key = 'score_survey';
				}
				if (!isset($array[$v['id_utente']][$key])) {
					$array[$v['id_utente']][$key] = 0;
				}
				$array[$v['id_utente']][$key]+= $v['punteggio'];

				$domande = unserialize($v['domande']);

				$sql = "SELECT
							t.`tipo` as test_tipo,
							q.`id_nodo`,q.`tipo`, q.`correttezza` as max_punti_domanda,
							SUM(a.`correttezza`) as sum_punti,MAX(a.`correttezza`) as max_punti
						FROM `".self::$PREFIX."nodes` q
						JOIN `".self::$PREFIX."nodes` a ON (a.`id_nodo_parent` = q.`id_nodo`)
						JOIN `".self::$PREFIX."nodes` t ON (t.`id_nodo` = q.`id_nodo_radice`)
						WHERE q.`id_nodo` IN (".implode(',',$domande).")
						GROUP BY t.`tipo`, q.`id_nodo`, q.`tipo`";
				$res = $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
				if (self::isError($res)) return $res;

				if (!empty($res)) {
					foreach($res as $i) {
						if ($i['test_tipo']{0} == ADA_TYPE_TEST) {
							$key = 'max_score_test';
						}
						else if ($i['test_tipo']{0} == ADA_TYPE_SURVEY) {
							$key = 'max_score_survey';
						}

						switch($i['tipo']{1}) {
							case ADA_MULTIPLE_CHECK_TEST_TYPE:
							case ADA_LIKERT_TEST_TYPE:
							case ADA_CLOZE_TEST_TYPE:
								$punti = $i['sum_punti'];
							break;
							case ADA_STANDARD_TEST_TYPE:
							case ADA_LIKERT_TEST_TYPE:
								$punti = $i['max_punti'];
							break;
							default:
								$punti = !is_null($i['max_punti_domanda'])?$i['max_punti_domanda']:0;
							break;
						}

						if (!isset($array[$v['id_utente']][$key])) {
							$array[$v['id_utente']][$key] = 0;
						}
						$array[$v['id_utente']][$key]+= $punti;
					}
				}
			}
		}

		return $array;
	}

    /**
     * updates an answer-type node to database
     *
     * @access public
     *
     * @param $id_answer - node id
     * @param $data - an associative array containing all the node's data
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_updateAnswer($id_answer,$data) {
        //validazione campi
        $d = array('risposta','commento','punteggio','correzione_risposta','allegato');

        foreach($data as $k=>$v) {
			if (!in_array($k, $d)) {
				unset($data[$k]);
			}
        }
        //fine validazione campi

		$sql = array();
		foreach($data as $k=>$v) {
			$sql[$k] = "`".$k."` = ?";
		}

		$array_values = array_merge(array_values($data), array($id_answer));
		$sql = "UPDATE `".self::$PREFIX."history_answer` SET ".implode(",",$sql)." WHERE `id_answer`= ?";
        $res = $this->queryPrepared($sql,$array_values);

        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
			print_r($res);
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in test_updateAnswer.".AMA_SEP.": ".$res->getMessage());
        }
		else {
			return true;
		}
    }

    /**
     * records the presence of a particular test inside a specified course,
	 * storing the node id that contains test link.
	 * It can also be used to update node reference without deleting the record first
     *
     * @access public
     *
     * @param $id_course - course id
	 * @param $id_test - test id
	 * @param $id_node - node id
     *
     * @return an error if something goes wrong or true
     *
     */
    public function test_addCourseTest($id_course, $id_test, $id_node) {
        $sql = "INSERT INTO `".self::$PREFIX."course_survey` (`id_corso`, `id_test`, `id_nodo`)
				VALUES (?,?,?)
				ON DUPLICATE KEY UPDATE `id_nodo` = ?";

		$res = $this->queryPrepared($sql, array($id_course, $id_test, $id_node, $id_node));
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in test_addCourseTest.".AMA_SEP.": ".$res->getMessage());
        }
        return true;
    }

    /**
     * delete a record inside course_survey table
     *
     * @access public
     *
     * @param $id_course - course id
	 * @param $id_test - test id
     *
     * @return an error if something goes wrong or true
     *
	 * @see test_addCourseTest
     */
    public function test_removeCourseTest($id_course, $id_test) {
        $sql = "DELETE FROM `".self::$PREFIX."course_survey`
				WHERE `id_corso` = ?
				AND `id_test` = ?";

		$res = $this->queryPrepared($sql, array($id_course, $id_test));
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_REMOVE)." while in test_removeCourseTest.".AMA_SEP.": ".$res->getMessage());
        }
        return true;
    }

    /**
     * retrieve course_test records
     *
     * @access public
     *
     * @param $where - array with key (field) and values (value)
     *
     * @return an error if something goes wrong or an array (empty if the node doesn't exists)
     *
     */
    public function test_getCourseTest($where) {
        $values = array($id_node);
        $sql = "SELECT t.*, n.`titolo`
                FROM `".self::$PREFIX."course_survey` t
				JOIN `".self::$PREFIX."nodes` n ON (n.`id_nodo` = t.`id_test`)
                WHERE true";

		if (is_array($where) && !empty($where)) {
			foreach($where as $k=>$v) {
				if (is_null($v)) {
					$sql.=" AND t.`".$k."` IS NULL";
					unset($where[$k]);
				}
				else if (is_array($v) && !empty($v)) {
					$sql.=" AND t.`".$k."` IN ('".implode("','",$v)."')";
					unset($where[$k]);
				}
				else if (strpos($v,'LIKE ') === 0) {
					$sql.=" AND t.`".$k."` LIKE '".str_replace('LIKE ','',$v)."'";
					unset($where[$k]);
				}
				else {
					$sql.=" AND t.`".$k."` = ?";
				}
			}
		}

        $tmp_res =  $this->getAllPrepared($sql, array_values($where), AMA_FETCH_ASSOC);

        if(self::isError($tmp_res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
            $res = array();
			if (!empty($tmp_res)) {
				foreach($tmp_res as $k=>$v) {
					$res[] = $v;
				}
			}
            unset($tmp_res);
            return $res;
        }
    }
    
    /**
     * @author giorgio 30/ott/2014
     * 
     * methods for accessing and manipulating the history_esercizi table
     */

    /**
     * Add an item  to table history_esercizi
     * Useful during the navigation. The date of the visit is computed automatically.
     *
     * @access public
     *
     * @param $student_id   the id of the student
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $node_id      the node to be registered in the history
     * @param $answer       NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * @param $remark       NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * @param $points       NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * @param $correction   NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * @param $ripetibile   NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * @param $attach       NOT USED IN MODULES_TEST, kept for compatibility reasons.
     * 
     * @return number|AMA_Error inserted row id or AMA_Error object
     *
     * (non-PHPdoc)
     * @see AMA_Tester_DataHandler::add_ex_history()
     */
    public function add_ex_history($student_id, $course_instance_id, $node_id, $answer='', $remark='', $points=0, $correction='', $ripetibile=0, $attach='') {
    	$result = parent::add_ex_history($student_id, $course_instance_id, $node_id);
    	if (!AMA_DB::isError($result)) return $this->getConnection()->lastInsertId();
    	else return $result;
    }
    
    /**
     * updates the exit time of a node in history_esercizi
     * 
     * @param $student_id   the id of the student
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $node_id      the node to be registered in the history
     * 
     * @return mixed
     */
    public function update_exit_time_ex_history($student_id, $course_instance_id, $node_id) {
    	$sql = 'UPDATE `history_esercizi` SET `data_uscita`=? WHERE `data_visita`=`data_uscita` AND '.
      	'`id_utente_studente` = ? AND `id_nodo` = ? AND `id_istanza_corso`= ?';
    	
    	return $this->queryPrepared($sql, array(time(), $student_id, $node_id, $course_instance_id));
    	
    }
    
}
