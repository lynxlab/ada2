<?php
/**
 * token.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */

/**
 * ADA's own inclusions
 */
require_once realpath (dirname (__FILE__)) . '/../config_path.inc.php';

/**
 * OAuth2 test start
 */

$dsn      = ADA_COMMON_DB_TYPE.':dbname='.ADA_COMMON_DB_NAME.';host='.ADA_COMMON_DB_HOST;
$username = ADA_COMMON_DB_USER;
$password = ADA_COMMON_DB_PASS;

// Autoloading (composer is preferred, but for this example let's just do this)
require_once 'OAuth2/Autoloader.php';
OAuth2_Autoloader::register();

// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2_Storage_ADA(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

// Pass a storage object or array of storage objects to the OAuth2 server class
$server = new OAuth2_Server($storage);

// Add the "Client Credentials" grant type (it is the simplest of the grant types)
$server->addGrantType(new OAuth2_GrantType_ClientCredentials($storage));

// Handle a request for an OAuth2.0 Access Token and send the response to the client
$server->handleTokenRequest(OAuth2_Request::createFromGlobals(),new OAuth2_Response())->send();
?>