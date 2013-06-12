<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

abstract class managementTest {
	protected $id = null;
	protected $mode;
	protected $what;
	protected $action;
	protected $tipo;
	protected $_post;
	protected $_r = null;

	/**
	 * Management constructor. the $action parameter must be 'add', 'mod' or 'del'
	 * 
	 * @param string $action method that will be runned
	 * @param int $id node id
	 */
	public function __construct($action,$id=null) {
		if (!is_null($id) && intval($id)>0) {
			$this->id = intval($id);
		}
		$this->action = $action;
		$this->_post = &$_POST;
	}

	/**
	 * adds a record
	 */
	abstract public function add();
	
	/**
	 * edits a record
	 */
	abstract public function mod();

	/**
	 * deletes a record
	 */
	abstract public function del();

	/**
	 * function that set "tipo" attribute from default values, post or from database record
	 */
	abstract protected function setTipo();

	/**
	 * returns status message based on $action attribute
	 */
	abstract protected function status();

	/**
	 * function that computes "tipo" field and returns it as string
	 *
	 * @return string
	 */
	protected function getTipo() {
		$string = '000000';

		if (!empty($this->tipo)) {
			foreach($this->tipo as $k=>$v) {
				$string{$k}=$v;
			}
		}
		return $string;
	}

	/**
	 * sets tipo field from database record
	 */
	protected function readTipoFromRecord() {
		if (!empty($this->_r)) {
			for($i=0;$i<strlen($this->_r['tipo']);$i++) {
				$this->tipo[$i]=$this->_r['tipo']{$i};
			}
		}
	}

	/**
	 * Runs correct method using action attribute
	 */
	public function run() {
		$dh = $GLOBALS['dh'];
		if (!is_null($this->id)) {
			$this->_r = $dh->test_getNode($this->id);
			$this->setTipo();

			if (empty($this->_r) || AMATestDataHandler::isError($this->_r)) {
				return array(
					'path'=>translateFN('Si è verificato un errore'),
					'status'=>translateFN('Si è verificato un errore'),
					'html'=>$this->what.' '.translateFN('non trovato'),
				);
			}
		}

		if (method_exists($this, $this->action)) {
			$array = array(
				'status'=>$this->status(),
			);
			return array_merge($array,$this->{$this->action}());
		}
	}

}