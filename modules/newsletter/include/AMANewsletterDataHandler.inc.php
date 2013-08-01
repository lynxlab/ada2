<?php
/**
 * NEWSLETTER MODULE.
 *
 * @package		newsletter module
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			newsletter
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMANewsletterDataHandler extends AMA_DataHandler {
	
	public static $PREFIX = 'module_newsletter_';

	/**
	 * Returns an instance of AMA_DataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMA_DataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMANewsletterDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}
	
	public function get_newsletter ( $id )
	{
		$sql = 'SELECT * FROM `'.self::$PREFIX.'newsletters` WHERE id=?';
		
		$retval = $this->getRowPrepared($sql, $id, AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($retval)) $retval['date'] = ts2dFN($retval['date']);
		
		return $retval;
		
	}
	
	public function delete_newsletter ( $id_newsletter )
	{
		$sql = 'DELETE FROM `'.self::$PREFIX.'history` WHERE id_newsletter=?';		
		$retval = $this->executeCriticalPrepared($sql, $id_newsletter);
		
		/**
		 *  error checking and handling must be don by the caller
		 *  anyway, I don't care if I have delete nothing with the query 
		 *  above, this means that the newsletter has no history
		 */
		
		$sql = 'DELETE FROM `'.self::$PREFIX.'newsletters` WHERE id=?';
		$retval = $this->executeCriticalPrepared($sql, $id_newsletter);

		return $retval;
	}
	
	public function get_newsletter_history ( $id_newsletter )
	{
		$sql = 'SELECT * FROM `'.self::$PREFIX.'history` WHERE id_newsletter=?';
		
		$retval = $this->getAllPrepared($sql, $id_newsletter, AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($retval))
		{
			for ($i=0; $i<count($retval); $i++)	
			 $retval[$i]['datesent'] = ts2dFN($retval[$i]['datesent']);
		}
		
		return $retval;		
	}
	
	public function get_newsletters ($fields=array())
	{
		$sql = 'SELECT ';
		
		if (empty($fields)) $sql .= '*';
		else $sql .= implode(',', $fields);
		
		$sql .= ' FROM `'.self::$PREFIX.'newsletters`';
		
		return $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
	}
	
	
	public function save_newsletter ( $newsletterHa ) {
		
		if (intval($newsletterHa['id']) <= 0)
		{
			$sql = 'INSERT INTO `'.self::$PREFIX.'newsletters` (`date`,`subject`,`sender`,`htmltext`,`plaintext`,`draft`) VALUES ( ?, ?, ?, ?, ?, ?)';
			unset ($newsletterHa['id']);
		}
		else
		{
			$sql = 'UPDATE `'.self::$PREFIX.'newsletters` SET `date`=?, `subject`=?, `sender`=?, `htmltext`=?, `plaintext`=?, `draft`=? WHERE id=?';
		}
		
// 		var_dump ($sql);
// 		print_r ($newsletterHa);
		
		return $this->queryPrepared($sql, array_values($newsletterHa));		
	}
	
	
}
?>