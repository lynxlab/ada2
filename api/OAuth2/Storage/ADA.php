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

}
