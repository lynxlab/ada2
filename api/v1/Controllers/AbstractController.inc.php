<?php
/**
 * AbstractController.inc.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */
namespace AdaApi;
/**
 * Empty class to define API's own Exception
 * 
 * @author giorgio
 *
 */
class APIException extends \Exception {};

/**
 * Abstract ADA API Controller
 * All Controllers must extend this class and implement the AdaApiInterface
 * 
 * @author giorgio
 *
 */
abstract class AbstractController {
	/**
	 * ADA common data handler
	 * 
	 * @var AMA_Common_DataHandler
	 */
	protected $common_dh;
	
	/**
	 * The SLIM application object
	 * 
	 * @var \Slim\Slim
	 */
	protected $slimApp = null;
	
	/**
	 * The OAuth2 authorized user id, if any
	 * 
	 * @var number
	 */
	protected $authUserID = null;
	
	/**
	 * The array of the authorized user's tester
	 * 
	 * @var array
	 */
	protected $authUserTesters = null;
	
	/**
	 * array to map array keys as returned from ADA
	 * to keys that the API must reutrn.
	 * E.g.
	 * 	If the ADA platform return 'nome', change it to 'name'.
	 *  likewise, if an API calls sends in 'name' change it to 'nome'.
	 *  
	 * @var array
	 */
	private static $_keyMappings = array (
		'id_utente' => 'user_id',
		'nome' => 'name',
		'cognome' => 'surname',
		'indirizzo' => 'address',
		'citta' => 'city',
		'provincia' => 'province',
		'e_mail' => 'email',
		'nazione' => 'country',
		'telefono' => 'phone',
		'lingua' => 'language',
		'cap' => 'zipcode',
	);
	
	/**
	 * Constructs a new controller setting the ADA common data handler
	 * and the array of the testers associated with the authenticated Switcher
	 * 
	 * @param \Slim\Slim $app
	 * @param number $authUserID
	 */	
	public function __construct(\Slim\Slim $app, $authUserID=0) {
		
		// get an instance of the ADA common DataBase
		$this->common_dh = \AMA_Common_DataHandler::instance();
		// store the SLIM app object
		$this->slimApp = $app;
		// if an authoized user id is passed, store it
		// and retreive the testers she belongs to
		if (intval($authUserID)>0) {
			$this->authUserID = intval($authUserID);
			$this->authUserTesters = $this->common_dh->get_testers_for_user($this->authUserID);			
		}
	}
	
	/**
	 * Default APIException handler
	 *
	 * @param APIException $e
	 */
	public function handleException (APIException $e) {
		
		$this->slimApp->response->status ($e->getCode());
		$this->slimApp->response->body (json_encode(array(
				'error_code'=>$e->getCode(),
				'error_message'=>$e->getMessage())));
				
		if (strlen($e->getMessage())>0)
			$this->slimApp->response->header('X-Status-Reason', $e->getMessage());
	}
	
	/**
	 * Maps the passed (by reference) array from ADA keys to API keys
	 * 
	 * @param array $array the array to be mapped
	 * @param array $moreMappings optional additional own controller key mappings
	 * @param unknown $moreMappings
	 */
	protected function ADAtoAPIArrayMap (&$array, $moreMappings = array()) {
		
		self::doArrayMap($array, $moreMappings, TRUE);
	}
	
	/**
	 * Maps the passed (by reference) array from API keys to ADA keys
	 *
	 * @param array $array the array to be mapped
	 * @param array $moreMappings optional additional own controller key mappings
	 * @param unknown $moreMappings
	 */
	protected function APItoADAArrayMap (&$array, $moreMappings = array()) {
		
		self::doArrayMap($array, $moreMappings, FALSE);
	}
	
	/**
	 * Does the actual array mapping modifing the reference to the passed array 
	 * 
	 * @param array $array the array to be mapped
	 * @param array $moreMappings optional additional own controller key mappings
	 * @param string $ADAtoAPI true if mapping is ADA=>API, false if API=>ADA
	 */
	private static function doArrayMap (&$array, $moreMappings, $ADAtoAPI = TRUE) {
		
		foreach (array_keys($array) as $key) {
			// take a reference to the current element value
			$value  = &$array[$key];
			// unset current element
			unset($array[$key]);
			// get the translated key
			$newKey = ($ADAtoAPI) ? self::ADAtoAPIKeyMap($key, $moreMappings) : self::APItoADAKeyMap($key, $moreMappings);
			// recurse if value is an array itself
			if (is_array($value)) self::doArrayMap($value, $moreMappings, $ADAtoAPI);
			// set the new array key
			$array[$newKey] = $value;
			// unset value reference
			unset ($value);
		}
	}
	
	/**
	 * Maps an ADA array key to an API array key
	 * 
	 * @param string $adakey the key to map
	 * @param array  $moreMappings optional additional own controller key mappings
	 * @return string the mapped key or $adakey if not found
	 */
	private static function ADAtoAPIKeyMap ($adakey='', $moreMappings = array()) {
		
		$workingArray = array_merge(self::$_keyMappings, $moreMappings);
		
		if (isset($workingArray[$adakey])) {
			return $workingArray[$adakey];
		} else {
			return $adakey;
		}
	}
	
	/**
	 * Maps an API array key to an ADA array key
	 * 
	 * @param string $apikey the key to reverse map
	 * @param array  $moreMappings optional additional own controller key mappings 
	 * @return string the found key or $apikey if not found
	 */
	private static function APItoADAKeyMap ($apikey='',array $moreMappings = array()) {
		
		$workingArray = array_merge(self::$_keyMappings, $moreMappings);
		
		if (strlen($apikey)>0) {
			$adakey = array_search($apikey, $workingArray);
			if ($adakey !== FALSE) {
				return $adakey;
			} else {
				return $apikey;
			}
		} else return $apikey;
	}	
}
?>