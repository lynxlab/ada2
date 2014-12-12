<?php
/**
 * @package 	import/export course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */
require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAImpExportDataHandler extends AMA_DataHandler {

	/**
	 * Returns an instance of AMA_DataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMA_DataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMAImpExportDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}

	/**
	 * Get the children of a given node.
	 *
	 * FOR EXPORT PURPOSES THIS METHOD IS OVERRIDDEN TO EXCLUDE
	 * NODES OF TYPES: ADA_NOTE_TYPE, ADA_PRIVATE_NOTE_TYPE THAT
	 * THAT WE DON'T WANT TO EXPORT
	 *
	 * @access public
	 *
	 * @param $node_id the id of the father
	 *
	 * @return an array of ids containing all the id's of the children of a given node
	 *
	 * @see get_node_info
	 *
	 */
	public function &export_get_node_children($node_id,$id_course_instance="") {
		$db =& $this->getConnection();

		$excludeNodeTypes = array ( ADA_NOTE_TYPE, ADA_PRIVATE_NOTE_TYPE );

		if ( AMA_DB::isError( $db ) ) return $db;

		if ($id_course_instance!="") {
			$sql  = "select id_nodo,ordine from nodo where id_nodo_parent='$node_id' AND id_istanza='$id_course_instance'";
		}
		else {
			$sql  = "select id_nodo,ordine from nodo where id_nodo_parent='$node_id'";
		}

		if (is_array($excludeNodeTypes) && !empty($excludeNodeTypes))
			$sql .= " AND `tipo` NOT IN(".implode(',', $excludeNodeTypes).")";

		$sql .= " ORDER BY ordine ASC";

		$res_ar =& $db->getCol($sql);
		if (AMA_DB::isError($res_ar)) {
			return new AMA_Error(AMA_ERR_GET);
		}
		// return an error in case of an empty recordset
		if (!$res_ar) {
			$retErr = new AMA_Error(AMA_ERR_NOT_FOUND);
			return $retErr;
		}
		// return nested array
		return $res_ar;
	}

	/**
	 * Need to promote this method to public
	 * @see AMA_Tester_DataHandler::_get_id_position
	 */
	public function get_id_position ($pos_ar)
	{
		return parent::_get_id_position ($pos_ar);
	}
	/**
	 * Need to promote this method to public
	 * @see AMA_Tester_DataHandler::_add_position
	 */
	public function add_position ($pos_ar)
	{
		return parent::_add_position ($pos_ar);
	}

	/**
	 * gets all the nodes that have an internal link in their text (aka testo) field.
	 * 
	 * @param int $course_id the id of the course to search for
	 * 
	 * @return array of fetched rows
	 * 
	 * @access public
	 */
	public function get_nodes_with_internal_link_for_course ($course_id, $start_import_time=null)
	{
		$sql = 'SELECT `id_nodo`  FROM `nodo` WHERE UPPER(`testo`) LIKE ? AND `id_nodo` LIKE ?';
		
		$values = array ( '<%LINK%TYPE="INTERNAL"%VALUE%>%' , $course_id.'_%');

		if (!is_null($start_import_time))
		{
			$sql .= ' AND `data_creazione`>= ?';
			array_push ($values, $start_import_time);
		}
		
		return $this->getAllPrepared($sql, $values );
	}
	
	/**
	 * gets all the course nodes that 
	 * 
	 * @param int $course_id the id of the course to search for
	 * 
	 * @return array of fetched rows
	 * 
	 * @access public
	 */
	public function get_nodes_with_test_link_for_course ($course_id, $start_import_time=null)
	{
		$sql = 'SELECT N.`id_nodo`FROM `nodo` N 
				JOIN 
				`module_test_nodes` MT ON N.`id_nodo` = MT.`id_nodo_riferimento`
				WHERE N.`id_nodo` LIKE ? AND N.`testo` LIKE \'%modules/test/index.php?id_test=%\'';
		
		$values = array ($course_id.'%');
		
		if (!is_null($start_import_time))
		{
			$sql .= ' AND N.`data_creazione`>= ?';
			array_push ($values, $start_import_time);
		}

		return $this->getAllPrepared ( $sql, $values);		
	}
}

?>