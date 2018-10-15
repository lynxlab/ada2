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
ini_set('display_errors', '0'); error_reporting(E_ALL);
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
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once MODULES_NEWSLETTER_PATH .'/config/config.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/functions.inc.php';

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST['id']) && intval($_POST['id'])>0 )
	{
		$idNewsletter = intval ($_POST['id']);

		if (isset ($_POST['userType']) && $_POST['userType']>0)
		{
			$html = convertFilterArrayToString ($_POST, $dh);

			$count = $dh->get_users_filtered ($_POST, true);

			$htmlcount = translateFN ('In totale, la newsletter sar&agrave; inviata a ');
			$htmlcount .= '<strong>'.$count.'</strong> ';
			$htmlcount .= ($count==1) ? translateFN('utente') : translateFN('utenti');
			$htmlcount .='.';
		}
		else
		{
			$html = translateFN(DEFAULT_FILTER_SENTENCE);
		}
	} // if (isset($_POST['id']) && intval($_POST['id'])>0 )
}

$outstr = (isset($htmlcount)) ? $html.'<br/><br/>'.ucfirst(strtolower($htmlcount)) : $html;

echo json_encode( array ('html'=>$outstr, 'count'=>intval($count)));
?>