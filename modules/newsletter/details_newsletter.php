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
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

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

// MODULE's OWN IMPORTS
require_once MODULES_NEWSLETTER_PATH.'/config/config.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/forms/formFilterNewsletter.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/functions.inc.php';

$self = whoami();

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$containerDIV = CDOMElement::create('div','id:moduleContent');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET'  && !empty($_GET) && isset($_GET['id']) && intval ($_GET['id'])>0 )
{
	$idNewsletter = intval ($_GET['id']);
	$newsletterAr = $dh->get_newsletter ($idNewsletter);

	if (!AMA_DB::isError($newsletterAr) && $newsletterAr!==false)
	{
		$historyAr = $dh->get_newsletter_history ($idNewsletter);

			$labels = array ( translateFN('filtro'), translateFN('data di invio'), translateFN('n. utenti') );

			$historyData = array();

			foreach ($historyAr as $i=>$historyEl)
			{
				$historyData[$i] = array(
						$labels[0] => convertFilterArrayToString(json_decode($historyEl['filter'],true),$dh,false),
						$labels[1] => $historyEl['datesent'],
						$labels[2] => ($historyEl['status']!=MODULES_NEWSLETTER_HISTORY_STATUS_SENDING) ? $historyEl['recipientscount'] : (translateFN('Invio in corso').'...')
				);
			}

			$historyTable = new Table();
			$historyTable->initTable('0','center','1','1','90%','','','','','1','0','','default','newsletterHistoryDetails');
			$historyTable->setTable($historyData,translateFN('Stroico Newsletter').' - '.$newsletterAr['subject'],translateFN('Stroico Newsletter').' - '.$newsletterAr['subject']);
			$histData = $historyTable->getTable();
			$histData= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $histData, 1); // replace first occurence of class
			$containerDIV->addChild(new CText($histData));
	}
	else
	{
		$containerDIV->addChild (new CText(translateFN('Newsletter non trovata, id= ').$idNewsletter));
	} // if (!AMA_DB::isError($newsletterAr))

}
else {
	$containerDIV->addChild (new CText(translateFN('Nessuna newsletter da inviare')));
}

$data = $containerDIV->getHtml();

/**
 * include proper jquery ui css file depending on wheter there's one
 * in the template_family css path or the default one
 */
if (!is_dir(MODULES_NEWSLETTER_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
{
	$layout_dataAr['CSS_filename'] = array(
			JQUERY_UI_CSS
	);
}
else
{
	$layout_dataAr['CSS_filename'] = array(
			MODULES_NEWSLETTER_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui/jquery-ui-1.10.3.custom.min.css'
	);
}

array_push($layout_dataAr['CSS_filename'], SEMANTICUI_DATATABLE_CSS);

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => translateFN('Newsletter'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_MASKEDINPUT,
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_NO_CONFLICT,
		MODULES_NEWSLETTER_PATH.'/js/jquery.cascade-select.js'
);

$optionsAr = array();
$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>