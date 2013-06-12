<?php
/**
 * 
 * AMAPDO Class.
 * Used to maintain compatibility with PEAR method calls.
 *
 * This must implement methods that are called mainly from AMA_PDO_wrapper class
 * on its own connection_object property that used to the an MDB2 instance.
 * 
 * Now it's a brand new AMAPDO instance, but AMA_DB_PDO_wrapper still expect it's an MDB2
 * so I'm forced to implement some methods that MDB2 has and PDO has not.
 * 
 * The first one I've found it's getDSN, but I guess the others are yet to come sooner or later.
 *
 *
 * @package		
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		ama_pear			
 * @version		0.1
 */
class AMAPDO extends PDO
{
	private $_dsn;

	/**
	 * class constructor, makes the connection by calling parent constructor and sets its own dsn
	 * 
	 * @param string $dbtype DBMS type (e.g. mysql) 
	 * @param string $dbhost DBMS hostname or IP
	 * @param string $dbname name of the DB
	 * @param string $username username to access the DB
	 * @param string $password password for the given username
	 * @param string $options specific driver options as an array
	 * 
	 * @access public
	 */
	public function __construct($dbtype, $dbhost, $dbname, $username, $password, $options)
	{
		parent::__construct($dbtype.':host='.$dbhost.';dbname='.$dbname,$username,$password, $options);
		
		// The private dsn array is used to implement the getDSN method
		$this->_dsn = array
		(
				'phptype'=>$dbtype,
				'username'=>$username,
				'password'=>$password,
				'hostspec'=>$dbhost,
				'database'=>$dbname
		);		 
	}

	/**
	 * gets the dsn string
	 * 
	 * @param string $array if set to 'array' it'll return the dsn as an array, else as a string
	 * @param string $notused notused :)
	 * @return array|string depending on the $array parameter
	 * 
	 * @access public
	 */
	public function getDSN ( $array='array', $notused=null)
	{
		if (is_null($this->_dsn)) return null;
		
		if ($array==='array') return $this->_dsn;
		else
		{
			return $this->_dsn['phptype'].'://'.$this->_dsn['username'].':'.$this->_dsn['password'].
			'@'.$this->_dsn['hostspec'].'/'.$this->_dsn['database'];
		}
	}
	
	/**
	 * frees the connection ny merely calling the destructor, mostly kept for compatibility reasons
	 * 
	 * @return boolean
	 * 
	 * @access public
	 */
	public function free()
	{
		return self::__destruct();
	}
	
	/**
	 * destructor
	 * 
	 * @return boolean
	 * 
	 * @access public
	 */
	public function __destruct()
	{
		$this->_dsn = null;
		return true;
	}
}
?>