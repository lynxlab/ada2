<?php
/**
 * AMA_DB DB abstraction layer.
 *
 * Manages switching between PDO and any other
 * possible layers that may be used in the future.
 *
 * Requires flags to be set in  file ada_config.php:
 *  flag PDO_DB
 *  ...other flags, one for each data type connection
 *
 *  flag DB_ABS_LAYER set to the proper flag you are going to use.
 *
 * @package		db
 * @author		Vito Modena <vito@lynxlab.com>
 * @author 		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		ama_pdo
 * @version		0.2
 */

/**
 * Include the required files depending on which
 * package we are going to use to gain access to the DB.
 */

// check if DB_ABS_LAYER is not defined in ama_config.php and defaults to PDO
if (!defined('DB_ABS_LAYER')) {
	define('DB_ABS_LAYER', PDO_DB);
}

switch (DB_ABS_LAYER) {
	case PDO_DB:
	default:
		require_once 'AMAPDO.inc.php';
		require_once 'ama_pdo_wrapper.inc.php';
		break;
		/**
		 * Pls handle other databases connection here by adding more cases
		 */
}

/**
 * Provides an abstraction layer for the db
 */
class AMA_DB
{
	/**
	 * check if passed object is an instance of PDOException and returns true if success
	 *
	 * @param mixed $data
	 * @param string code not used, kept for compatibility reasons
	 * @return boolean true if is error
	 * 
	 * @access public
	 */
	public static function isError($data, $code=null) {
		return ($data instanceof PDOException || $data instanceof AMA_Error);
	}

	/**
	 * instantiate the proper class and estabilishes the connection
	 *
	 * @param mixed $dsn the string "data source name" as requested by PEAR (kept for compatibility)
	 * @param array $options an associative array of option names and values as requested by selected ABS_LAYER
	 * @return a new DB object
	 * 
	 * @access public
	 */
	public static function &connect($dsn, $options=false) {
		switch (DB_ABS_LAYER) {
			case PDO_DB:
			default:
				$wrapper = new AMA_PDO_wrapper($dsn, $options);
				if (self::isError($wrapper->connection_object())) {
					// if there were errors, $wrapper->connection_object is a PDOException
					// so we return it
					return $wrapper->connection_object();
				}
				break;
				/**
				 * Pls handle other databases connection here by adding more cases
				 */
		}
		return $wrapper;
	}
}
?>