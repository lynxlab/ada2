<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * abstract class for login provider implementations
 */
abstract class abstractLogin implements iLogin
{
	/**
	 * provider id in ADA database
	 *
	 * @var number
	 */
	protected  $id = null;
	
	/**
	 * login button label
	 * 
	 * @var string
	 */
	protected  $buttonLabel = null;
	
	/**
	 * provider name
	 * 
	 * @var string
	 */
	protected $name = null;
	
	/**
	 * login provider's own options array
	 * 
	 * @var array
	 */
	protected  $options = null;
	
	/**
	 * datahandler to be used
	 * 
	 * @var AMALoginDataHandler
	 */
	private $dataHandler;
	
	public function __construct($id=null) {
		if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
			$dsn = MultiPort::getDSN($GLOBALS['user_provider']);
		} else $dsn = null;
		
		$this->dataHandler = AMALoginDataHandler::instance($dsn);
		
		if (is_null($id)) {
			$id = $this->dataHandler->getLoginProviderIDFromClassName(get_class($this));
		}
		
		$this->id = intval($id);
		
		// $this->buttonLabel = $this->loadButtonLabel($id);
		$this->options = $this->loadOptions($id);
	}
	
	/**
	 * loads the button label from the DB
	 * 
	 * @return string, the loaded label
	 * 
	 * @access public
	 */
	public function loadButtonLabel () {
		return $this->dataHandler->loadButtonLabel($this->id);
	}
	
	/**
	 * loads the provider name from the DB
	 *
	 * @return string, the loaded name
	 *
	 * @access public
	 */
	public function loadProviderName () {
		return $this->dataHandler->loadProviderName($this->id);
	}
	
	/**
	 * loads provider's own options from the DB
	 * 
	 * @return array the loaded options array
	 * 
	 * @access public
	 */
	public  function loadOptions() {
		return $this->dataHandler->loadOptions($this->id);
	}
	
	/**
	 * gets a list of the available login providers found in the DB
	 * 
	 * If in a multiprovider environment, common db is used
	 * else first it's searched the provider db and if no
	 * module_login_providers table is found there, use common
	 * 
	 * @param string $enabled true if only enabled providers, false if only disabled, null for all providers
	 * 
	 * @return array with provider id as the key and implementing className as value
	 * 
	 * @access public
	 */
	public static function getLoginProviders($enabled = true) {
		/**
		 * If not multiprovider, the AMALoginDataHandler must check
		 * if module's own tables are in the user_provider DB and use them
		 * or the ones in the common db if they're not found in the provider DB
		 */
		if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
			$dsn = MultiPort::getDSN($GLOBALS['user_provider']);
		} else $dsn = null;
		
		$res = AMALoginDataHandler::instance($dsn)->getLoginProviders($enabled,' `displayOrder` ASC');
		
		if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
			foreach ($res as $provider) {
				$retArr[$provider[AMALoginDataHandler::$PREFIX.'providers_id']] = $provider['className'];
			}
			return $retArr;
		} else return null;
	}
	
	/**
	 * can be overriden by derived class to draw the button
	 * and handle its onclick event properly
	 * 
	 * @param boolean $returnHtml true if html string is required
	 */
	protected function render($returnHtml)
	{
		$buttonLabel = $this->loadButtonLabel();
		$id = $this->loadProviderName();
		if (!is_null($id)) $id = strtolower($id).'-button'; 
		if (strlen($buttonLabel)>0) {
			$button = CDOMElement::create('button','id:'.$id.',type:button');
			$button->setAttribute('class', get_class($this).' login');
			$button->setAttribute('onclick', 'javascript:'.
					'$j(\'#selectedLoginProvider\').val(\''.get_class($this).'\');'.
					'$j(\'#selectedLoginProviderID\').val(\''.$this->id.'\');'.
					'$j(this).parents(\'form\').first().submit();');
			$button->addChild (new CText(translateFN($buttonLabel)));
			
			return (($returnHtml) ? $button->getHtml() : $button);			
		} else return null;
	}
	
	/**
	 * id getter
	 * 
	 * @return number id of the class
	 * 
	 * @access public
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * gets the proper login provider object from session
	 * stored $_SESSION['sess_loginProviderArr']['className'] and
	 * $_SESSION['sess_loginProviderArr']['id'] variables
	 * 
	 * @return Object the instantiated class
	 * 
	 * @access public
	 */
	public static function getLoginProviderFromSession() {
		
		if (isset($_SESSION['sess_loginProviderArr']) && 
			is_array($_SESSION['sess_loginProviderArr']) &&
			isset($_SESSION['sess_loginProviderArr']['className']) &&
			isset($_SESSION['sess_loginProviderArr']['id'])) {
				
				require_once MODULES_LOGIN_PATH . '/include/'.
							$_SESSION['sess_loginProviderArr']['className'].'.class.inc.php';
				
				return new $_SESSION['sess_loginProviderArr']['className']($_SESSION['sess_loginProviderArr']['id']);
		} else {
			return null;
		}		
	}
	
	/**
	 * gets the login button as an ADA CDOMElement object
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::getCDOMElement()
	 */
	public function getCDOMElement()
	{
		return $this->render(false);
	}
	
	/**
	 * gets the login button as an HTML string
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::getHtml()
	 */
	public function getHtml()
	{
		return $this->render(true);
	}
}

interface iLogin
{
	function doLogin ($name, $pass, $remindMe, $language);
	function getCDOMElement();
	function getHtml();
}