<?php
/**
 * provider index
 *
 * @package	view
 * @author		giorgio <g.consorti@lynxlab.com>
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version	0.1
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);
	

	/**
	 * Base config file, pls note that selected provider will be set in 
	 * session_controlFN function of module_init_functions.inc.php
	 * and will be detected from script filename.
	 */
	require_once realpath(dirname(__FILE__)).'../../../config_path.inc.php';
	/**
	 * if trying to access this page in a non multiprovider
	 * environment, redirect to standard home page
	 */
	if (!MULTIPROVIDER)	
		include_once ROOT_DIR.'/index.php';
	else 
		header ('Location: '.HTTP_ROOT_DIR);
?>