<?php
/**
 * APPS MODULE.
 *
 * @package        apps module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           oauth2
 * @version		   0.1
 */
require_once(ROOT_DIR.'/include/ama.inc.php');

class AMAAppsDataHandler extends AMA_Common_DataHandler {
	/**
	 * module's own data tables prefix
	 * 
	 * @var string
	 */
	public static $PREFIX = 'module_oauth2_';
	
	/**
	 * Looks for a known client_id and client_secret for the passed user,
	 * if something is found, return it. Else save the passed pair for
	 * the passed user and return the passed pair.
	 * 
	 * 
	 * @param unknown $client_array the generated pair to be saved if nothing is found for the passed user
	 * @param unknown $userID the user id to look for or save a client id/client secret pair
	 * @return array|null
	 * 
	 * @access public
	 */
	public function saveClientIDAndSecret ($client_array,$userID) {
		
		//look for a client_id and secret associated with the passed user
		$sql = 'SELECT client_id, client_secret FROM '.self::$PREFIX.'oauth_clients WHERE user_id=?';
		$res = $this->getRowPrepared($sql,$userID,AMA_FETCH_ASSOC);
		
		if (is_array($res) && count($res)===2) {
			// if the pair is found, return it
			return $res;
		}
		else {
			// else save the passed one and return it on success
			$sql = "INSERT INTO ".self::$PREFIX."oauth_clients VALUES (?,?,?,NULL,NULL,?)";
			$res = $this->queryPrepared($sql, array($client_array['client_id'], $client_array['client_secret'],'',$userID));

			if (!AMA_DB::isError($res)) return $client_array;
			else return null;
		}
		
		return null;
	}	

	/**
	 * Returns an instance of AMAAppsDataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMAAppsDataHandler
	 */
	public static function instance($dsn=null) {            
        return new AMAAppsDataHandler();
	}
}
?>
