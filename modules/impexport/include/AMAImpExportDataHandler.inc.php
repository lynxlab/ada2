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
	public function get_node_children($node_id,$id_course_instance="") {
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
			return new AMA_Error(AMA_ERR_NOT_FOUND);
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
	 * Need to promote this method to public
	 * @see AMA_Tester_DataHandler::_add_extension_node
	 */
	public function add_extension_node($node_ha)
	{
		return parent::_add_extension_node($node_ha);
	}

	/**
	 * This is an exact duplicate of the method found in ama.inc.php
	 * BUT IT DOES NOT GENERATE A NEW node_id FOR EVERY PASSED NODE
	 * IT WILL USE THE node_id PASSED IN THE $node_ha ARRAY
	 *
	 *
	 * @see AMA_Tester_DataHandler::_add_node()
	 */
	protected function _add_node($node_ha) {
		ADALogger::log_db("entered _add_node");

		$db =& $this->getConnection();
		if ( AMA_DB::isError( $db ) ) return $db;

		
		// uncommented below line in this version of the method
		// @author giorgio 17/lug/2013
		$id_node = $this->sql_prepared($node_ha['id']);
		$id_author = $node_ha['id_node_author'];
		$name = $this->sql_prepared($this->or_null($node_ha['name']));
		$title = $this->sql_prepared($this->or_null($node_ha['title']));

		$text = $this->sql_prepared($node_ha['text']);
		$type = $this->sql_prepared($this->or_zero($node_ha['type']));
		$creation_date = $this->date_to_ts($this->or_null($node_ha['creation_date']));
		$parent_id = $this->sql_prepared($node_ha['parent_id']);
		$order = $this->sql_prepared($this->or_null($node_ha['order']));
		$level = $this->sql_prepared($this->or_zero($node_ha['level']));
		$version = $this->sql_prepared($this->or_zero($node_ha['version']));
		$n_contacts = $this->sql_prepared($this->or_zero($node_ha['n_contacts']));
		$icon = $this->sql_prepared($this->or_null($node_ha['icon']));

		// modified 7/7/01 ste
		// $color = $this->or_zero($node_ha['color']);
		$bgcolor = $this->sql_prepared($this->or_null($node_ha['bgcolor']));
		$color = $this->sql_prepared($this->or_null($node_ha['color']));
		// end
		$correctness = $this->sql_prepared($this->or_zero($node_ha['correctness']));
		$copyright = $this->sql_prepared($this->or_zero($node_ha['copyright']));
		// added 6/7/01 ste
		$id_position = $this->sql_prepared($node_ha['id_position']);
		$lingua = $this->sql_prepared($node_ha['lingua']);
		$pubblicato = $this->sql_prepared($node_ha['pubblicato']);
		// end
		// added 24/7/02 ste
		//  $family = $this->date_to_ts($this->or_null($node_ha['family']));
		// end

		// added  2/4/03
		if (array_key_exists('id_instance',$node_ha)) {
			$id_instance = $this->sql_prepared($this->or_null($node_ha['id_instance']));
		}
		else {
			$id_instance = "''";
		}
		//end
		
		$sql  = "insert into nodo (id_nodo, id_utente,id_posizione, nome, titolo, testo, tipo, data_creazione, id_nodo_parent, ordine, livello, versione, n_contatti, icona, colore_didascalia, colore_sfondo, correttezza, copyright, lingua, pubblicato, id_istanza)";
		$sql .= " values ($id_node,  $id_author, $id_position, $name, $title, $text, $type, $creation_date, $parent_id, $order, $level, $version, $n_contacts, $icon, $color, $bgcolor, $correctness, $copyright, $lingua, $pubblicato, $id_instance)";
		ADALogger::log_db("trying inserting the node: $sql");

		$res = $db->query($sql);
		// if an error is detected, an error is created and reported
		if (AMA_DB::isError($res)) {
			return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _add_node." .
					AMA_SEP . ": " . $res->getMessage());
		}

		//return true;
		return $new_node_id;
	}
}

?>