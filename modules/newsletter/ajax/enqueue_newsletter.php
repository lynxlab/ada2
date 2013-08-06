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
ini_set('display_errors', '1'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

require_once ROOT_DIR.'/include/logger_class.inc.php';

// MODULE's OWN IMPORTS
require_once MODULES_NEWSLETTER_PATH .'/config/config.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';

// should something have gone wronk, hopefully this gets called!
function shutDown()
{
	$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	$dh->set_history_status ($history_id, MODULES_NEWSLETTER_HISTORY_STATUS_UNDEFINED);
}

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

register_shutdown_function('shutDown');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') 
{
	if (isset($_POST['id']) && intval($_POST['id'])>0) 
	{
		$id_newsletter = intval ($_POST['id']);
		$filterArray = $dh->build_filter_from_array($_POST);		
		$recipients = $dh->get_users_filtered($filterArray, false);		
		$count = count ($recipients);
		
		$history_id = $dh->save_newsletter_history ($id_newsletter, $filterArray, $count, MODULES_NEWSLETTER_HISTORY_STATUS_UNDEFINED );
		
		if (!AMA_DB::isError($history_id))
		{
			ignore_user_abort(true);
			session_write_close();
			
			$sleepTime = intval (3600 / MODULES_NEWSLETTER_EMAILS_PER_HOUR * 1000000); // sleep time in microseconds
			
			$logFile = MODULES_NEWSLETTER_LOGDIR.'log-'.$id_newsletter.'-'.date('d-m-Y_His');
			if (!is_file($logFile)) touch ($logFile);
			
			ADAFileLogger::log("Sending out to: \n".print_r($recipients,true), $logFile );			
			
			$dh->set_history_status ($history_id, MODULES_NEWSLETTER_HISTORY_STATUS_SENDING);
			foreach ($recipients as $num=>$recipient)
			{
				set_time_limit(0);
				// do here the actual newsletter substitution and sending			
				ADAFileLogger::log('sending out#'.$num.' userID='.$recipient[0].' e-mail='.$recipient[1], $logFile);
				
				if ($num<count($recipients)-1)
				{				
					ADAFileLogger::log('goin to sleep...', $logFile);				
					usleep ($sleepTime);
					ADAFileLogger::log('...got woken up', $logFile);
				}				
			}			
			$res = $dh->set_history_status ($history_id, MODULES_NEWSLETTER_HISTORY_STATUS_SENT);
			if (AMA_DB::isError($res)) ADAFileLogger::log( print_r($res,true), $logFile );
			ADAFileLogger::log('Done... OK!', $logFile);
		}
	}
}
?>