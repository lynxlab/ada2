<?php
/**
 * OAuth2Auth.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;

/**
 * ADA's own inclusions
 */
require_once realpath (dirname (__FILE__)) . '/../../../config_path.inc.php';
require_once realpath (dirname (__FILE__)) . '/../../OAuth2/Autoloader.php';


class OAuth2Auth extends \Slim\Middleware {
	
	private $dsn      ;
	private $username ;
	private $password ;

	public function __construct() {
		$this->dsn      = ADA_COMMON_DB_TYPE.':dbname='.ADA_COMMON_DB_NAME.';host='.ADA_COMMON_DB_HOST;
		$this->username = ADA_COMMON_DB_USER;
		$this->password = ADA_COMMON_DB_PASS;
	}
	
	/**
	 * checks if a valid access_token has been passed
     *
	 * @see \Slim\Middleware::call()
	 */
	public function call()
	{
		\OAuth2_Autoloader::register();
		$storage = new \OAuth2_Storage_ADA(array(
				'dsn' => $this->dsn, 
				'username' => $this->username,
				'password' => $this->password));
		
		// Pass a storage object or array of storage objects to the OAuth2 server class
		$server = new \OAuth2_Server($storage);
		
		// Add the "Client Credentials" grant type (it is the simplest of the grant types)
		$server->addGrantType(new \OAuth2_GrantType_ClientCredentials($storage));
		
		// Add the "Authorization Code" grant type (this is where the oauth magic happens)
		// $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($storage));
		
		// Handle a request for an OAuth2.0 Access Token and send the response to the client
		if (!$server->verifyResourceRequest(\OAuth2_Request::createFromGlobals(),new \OAuth2_Response())) {
			// uncomment below to send $server's own response.
			// we want to use SLIM framework here
			// $server->getResponse()->send();
			$this->app->response->setStatus(401);
			$this->app->response->setBody('access_token is invalid or not authorized');
		} else {
			$this->next->call();
		}
	}
}
?>