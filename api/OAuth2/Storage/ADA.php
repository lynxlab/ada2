<?php

/**
 * Simple ADA storage 
 *
 * NOTE: This class is meant to get users started
 * quickly. If your application requires further
 * customization, extend this class or create your own.
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */

require_once 'Pdo.php';

class OAuth2_Storage_ADA extends OAuth2_Storage_Pdo
{
	private $tmp_userID = null;
	
	public function __construct($connection, $config = array()) {
		
		$tbl_prefix = 'module_oauth2_';
		
		parent::__construct($connection,$config);
		
		$this->config = array_merge(array(
				'client_table' => $tbl_prefix.'oauth_clients',
				'access_token_table' => $tbl_prefix.'oauth_access_tokens',
				'refresh_token_table' => $tbl_prefix.'oauth_refresh_tokens',
				'code_table' => $tbl_prefix.'oauth_authorization_codes',
				'user_table' => $tbl_prefix.'oauth_users',
				'jwt_table' => $tbl_prefix.'oauth_jwt',
		), $config);
	}

	/**
	 * Overridden to temporarily store the user_id associated with the 
	 * given $client_id and $client_secret
	 * Looks like they're only stored in the access_token_table
	 * when the user authenticates itself, i.e. does a login
	 * 
	 * (non-PHPdoc)
	 * @see OAuth2_Storage_Pdo::checkClientCredentials()
	 */
	public function checkClientCredentials($client_id, $client_secret = null)
    {
        $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id', $this->config['client_table']));
        $stmt->execute(compact('client_id'));
        $result = $stmt->fetch();
        
        $this->tmp_userID = $result['user_id'];

        // make this extensible
        return $result['client_secret'] == $client_secret;
    }

    /**
     * Overridden to retrieve and force the insert of the user_id associated with the
     * given $client_id and $client_secret in the access_token_table
     *
     * (non-PHPdoc)
     * @see OAuth2_Storage_Pdo::checkClientCredentials()
     */
    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
    	$passUserId = null;
    	if (is_null($user_id) && !is_null($this->tmp_userID))
    	{
    		$passUserId = $this->tmp_userID
    	} else {
    		$passUserId = $user_id;    		    	
    	}
    	return parent::setAccessToken($access_token, $client_id, $passUserId, $expires, $scope);
    }
}
