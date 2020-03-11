<?php
/**
 * bootstrap.php
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
 * ADA's own inclusions
 */
require_once realpath (dirname (__FILE__)) . '/../../config_path.inc.php';
require_once ROOT_DIR . '/include/utilities.inc.php';
require_once ROOT_DIR . '/include/ama.inc.php';
require_once ROOT_DIR . '/include/multiport.inc.php';
require_once ROOT_DIR . '/include/logger_class.inc.php';
require_once ROOT_DIR . '/include/error_class.inc.php';
require_once ROOT_DIR . '/include/data_validation.inc.php';
require_once ROOT_DIR . '/include/user_classes.inc.php';

/**
 * Slim framework inclusion
 */
require_once '../Slim/Slim.php';

class AdaApi  {

	public static $supportedFormats = array ('json','php','xml');

	public static function registerAutoloader() {
		spl_autoload_register (__NAMESPACE__ . "\\AdaApi::autoload");
	}

	public static function autoload($class) {

		$splitted = explode('\\', $class);
		$class = end($splitted);

		foreach ( array ('Controllers','Views','Middleware') as $key=>$dirname)
		{
			$classfilename = $dirname.'/' . $class . '.inc.php';
			if (is_file ($classfilename)) {
				require_once $classfilename;
				break;
			}
		}
	}
}

\Slim\Slim::registerAutoloader ();
\AdaApi\AdaApi::registerAutoloader();

/**
 * Turn off logging to HTML for all error phases.
 */

$GLOBALS['ADA_ERROR_POLICY'][ADA_ERROR_PHASE_DEVELOP][ADA_ERROR_SEVERITY_FATAL]  = ADA_ERROR_LOG_TO_FILE;
$GLOBALS['ADA_ERROR_POLICY'][ADA_ERROR_PHASE_DEVELOP][ADA_ERROR_SEVERITY_NORMAL] = ADA_ERROR_LOG_TO_FILE;
$GLOBALS['ADA_ERROR_POLICY'][ADA_ERROR_PHASE_DEVELOP][ADA_ERROR_SEVERITY_LIGHT]  = ADA_ERROR_LOG_TO_FILE;
$GLOBALS['ADA_ERROR_POLICY'][ADA_ERROR_PHASE_DEVELOP][ADA_ERROR_SEVERITY_NONE]   = ADA_ERROR_LOG_TO_FILE;

/**
 * Set the globals of the user_provider guessed from 3rd level domain
 */
if (!MULTIPROVIDER) {

	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$servername = $_SERVER['HTTP_X_FORWARDED_HOST'];
		} else if (isset($_SERVER['SERVER_NAME'])) {
			$servername = $_SERVER['SERVER_NAME'];
		}
		list ($client) = explode ('.',preg_replace('/(http[s]?:\/\/)/', '', $servername));
	}

	if (isset($client) && !empty ($client) && is_dir(ROOT_DIR.'/clients/'.$client))
	{
		$tmpcommon = \AMA_Common_DataHandler::instance();
		// $_SESSION['sess_user_provider'] = $client;
		$GLOBALS['user_provider'] = $tmpcommon->getPointerFromThirdLevel($client);
		unset($tmpcommon);
		// other session vars per provider may go here...
	}
	else unset ($GLOBALS['user_provider']);
}
?>