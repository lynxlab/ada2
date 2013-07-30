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
require_once MODULES_NEWSLETTER_PATH.'/include/forms/formEditNewsletter.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';

$self = 'newsletter';

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$containerDIV = CDOMElement::create('div','id:moduleContent');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'  && !empty($_POST)) 
{
	// saves the newsletter
		
	$newsletterHa['date'] = ( isset($_POST['subject']) && trim($_POST['subject'])!=='' ) ? $dh->date_to_ts(trim($_POST['date'])) : $dh->date_to_ts(date("d/m/Y"));
	$newsletterHa['subject'] = ( isset($_POST['subject']) && trim($_POST['subject'])!=='' ) ? trim ($_POST['subject']) : null;
	$newsletterHa['sender'] = ( isset($_POST['sender']) && trim($_POST['sender'])!=='' ) ? trim ($_POST['sender']) : null;
	$newsletterHa['htmltext'] = ( isset($_POST['htmltext']) && trim($_POST['htmltext'])!=='' ) ? trim ($_POST['htmltext']) : null;
	$newsletterHa['plaintext'] = ( isset($_POST['plaintext']) && trim($_POST['plaintext'])!=='' ) ? trim ($_POST['plaintext']) : null;
	$newsletterHa['draft'] = intval ($_POST['draft']);
	$newsletterHa['id'] = ( isset($_POST['id']) && intval($_POST['id'])>0 ) ? intval($_POST['id']) : 0;
	
	$retval = $dh->save_newsletter ( $newsletterHa );
	
	if (AMA_DB::isError($retval)) $msg = new CText(translateFN('Errore nel salvataggio della newsletter'));
	else $msg = new CText(translateFN('Newsletter salvata'));
	
	$containedElement = CDOMElement::create('div','class:newsletterSaveResults');
	
		$spanmsg = CDOMElement::create('span','class:newsletterSaveResultstext');
		$spanmsg->addChild ($msg);
		
		$button = CDOMElement::create('button','id:newsletterSaveResultsbutton');
		$button->addChild (new CText(translateFN('OK')));
		$button->setAttribute('onclick', 'javascript:self.document.location.href=\''.MODULES_NEWSLETTER_HTTP.'\'');
		
		$containedElement->addChild ($spanmsg);
		$containedElement->addChild ($button);
	
		$data = $containedElement->getHtml();
		
		/// if it's an ajax request, output html and die
		if (isset($_POST['requestType']) && trim($_POST['requestType'])==='ajax')
		{
			echo $data;
			die();
		}
		
	
} else {
	$containedElement = new FormEditNewsLetter( 'editnewsletter' );
	$data = $containedElement->render();
}

$containerDIV->addChild (new CText($data));
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
// 		JQUERY_DATATABLE,
// 		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		MODULES_NEWSLETTER_PATH.'/js/edit_newsletter.js'
);

$optionsAr = array();
$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>