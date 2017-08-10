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
require_once MODULES_NEWSLETTER_PATH .'/config/config.inc.php';
require_once MODULES_NEWSLETTER_PATH.'/include/AMANewsletterDataHandler.inc.php';

$self = 'newsletter';

$GLOBALS['dh'] = AMANewsletterDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));


/**
 * generate HTML for 'New Newsletter' button and the table with
 * old newsletters for editing, sending, deleting, duplicating and details view.
*/

$newsletterIndexDIV = CDOMElement::create('div','id:newsletterindex');

$newButton = CDOMElement::create('button');
$newButton->setAttribute('class', 'newButton top');
$newButton->setAttribute('onclick', 'javascript:newNewsletter();');
$newButton->addChild (new CText(translateFN('Nuova Newsletter')));

$newsletterData = array();

$newslettersList = $dh->get_newsletters( array ( 'id','subject', 'date','draft' ));

if (!AMA_DB::isError($newslettersList))
{
	$labels = array (translateFN('oggetto'), translateFN('data'), translateFN('bozza') , translateFN('N. Invii'), translateFN('azioni'));

	foreach ($newslettersList as $i=>$newsletterAr)
	{
		$sentDetails = $dh->get_newsletter_history ( $newsletterAr['id'] );
		$isSending = $dh->isSending ( $newsletterAr['id'] );
		if (AMA_DB::isError($sentDetails)) $displayDetailsLink = false;
		else $displayDetailsLink = ( count($sentDetails) > 0 );

		$links = array();
		$linksHtml = "";

		for ($j=0;$j<5;$j++)
		{
			switch ($j)
			{
				case 0:
					$type = 'edit';
					$title = translateFN('Clicca per modificare la newsletter');
					$link = 'self.document.location.href=\'edit_newsletter.php?id='.$newsletterAr['id'].'\';';
					$disabled = $isSending;
					break;
				case 1:
					$type = 'send';
					$title = translateFN('Clicca per inviare la newsletter');
					$link = 'self.document.location.href=\'send_newsletter.php?id='.$newsletterAr['id'].'\';';
					$disabled = ($newsletterAr['draft']==1) || $isSending;
					break;
				case 2:
					$type = 'details';
					$title = translateFN('Clicca per i dettagli della newsletter');
					$link = 'self.document.location.href=\'details_newsletter.php?id='.$newsletterAr['id'].'\';';
					$disabled = (!$displayDetailsLink);

					break;
				case 3:
					$type = 'copy';
					$title = translateFN('Clicca per duplicare la newsletter');
					$link = 'duplicateNewsletter('.$newsletterAr['id'].');';
					$disabled = false;
					break;
				case 4:
					$type = 'delete';
					$title = translateFN ('Clicca per cancellare la newsletter');
					$link = 'deleteNewsletter ($j(this), '.$newsletterAr['id'].' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
					$disabled = $isSending;
					break;
			}

			if (isset($type))
			{
				$links[$j] = CDOMElement::create('li','class:liactions');

				$linkshref = CDOMElement::create('button');
				$linkshref->setAttribute('onclick','javascript:'.$link);
				$linkshref->setAttribute('class', $type.'Button tooltip');
				if ($disabled) $linkshref->setAttribute('disabled', 'true'); // tells jquery to disable the button
				$linkshref->setAttribute('title',$title);
				$links[$j]->addChild ($linkshref);
				// unset for next iteration
				unset ($type);
			}
		}

		if (!empty($links))
		{
			$linksul = CDOMElement::create('ul','class:ulactions');
			foreach ($links as $link) $linksul->addChild ($link);
			$linksHtml = $linksul->getHtml();
		} else $linksHtml = '';

		$newsletterData[$i] = array (
				$labels[0]=>$newsletterAr['subject'],
				$labels[1]=>ts2dFN($newsletterAr['date']),
				$labels[2]=>($newsletterAr['draft']==1) ? translateFN('Sì') : translateFN('No'),
				$labels[3]=>($isSending) ? translateFN('Invio in corso').'...' : count($sentDetails),
				$labels[4]=>$linksHtml);
	}

	$historyTable = new Table();
	$historyTable->initTable('0','center','1','1','90%','','','','','1','0','','default','newsletterHistory');
	$historyTable->setTable($newsletterData,translateFN('Archivio Newsletter'),translateFN('Archivio Newsletter'));


	$newsletterIndexDIV->addChild($newButton);
	$newsletterIndexDIV->addChild(CDOMElement::create('div','class:clearfix'));
	$histData = $historyTable->getTable();
	$histData= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $histData, 1); // replace first occurence of class
	$newsletterIndexDIV->addChild(new CText($histData));
	// if there are more than 10 rows, repeat the add new button below the table
	if ($i>10)
	{
		$bottomButton = clone $newButton;
		$bottomButton->setAttribute('class', 'newButton bottom');
		$newsletterIndexDIV->addChild($bottomButton);
	}
} // if (!AMA_DB::isError($newslettersAr))
else
{
	$newsletterIndexDIV->addChild (new CText(translateFN('Errore nella lettura dell\'archivio newsletter')));
}

$data = $newsletterIndexDIV->getHtml();

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
array_push($layout_dataAr['CSS_filename'], MODULES_NEWSLETTER_PATH.'/layout/tooltips.css');

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'label' => translateFN('Newsletter'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>