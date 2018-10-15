<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
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
require_once MODULES_SERVICECOMPLETE_PATH .'/config/config.inc.php';
require_once MODULES_SERVICECOMPLETE_PATH .'/include/init.inc.php';

$self = 'complete';

$GLOBALS['dh'] = AMACompleteDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));


/**
 * generate HTML for 'New Rule' button and the table
 */

$rulesIndexDIV = CDOMElement::create('div','id:rulesindex');

$newButton = CDOMElement::create('button');
$newButton->setAttribute('class', 'newButton top');
$newButton->setAttribute('title', translateFN('Clicca per creare una nuova regola'));
$newButton->setAttribute('onclick', 'javascript:self.document.location.href=\''.MODULES_SERVICECOMPLETE_HTTP.'/edit_completerule.php\'');
$newButton->addChild (new CText(translateFN('Nuova Regola')));

$rulesData = array();

$rulesList = $dh->get_completeConditionSetList();

if (!AMA_DB::isError($rulesList))
{
	$labels = array (translateFN('descrizione'), translateFN('azioni'));

	foreach ($rulesList as $i=>$ruleAr)
	{
		$links = array();
		$linksHtml = "";

		for ($j=0;$j<3;$j++)
		{
			switch ($j)
			{
				case 0:
					$type = 'edit';
					$title = translateFN('Clicca per modificare la regola');
					$link = 'self.document.location.href=\'edit_completerule.php?conditionSetId='.$ruleAr['id'].'\';';
					break;
				case 1:
					$type = 'apply';
					$title = translateFN('Clicca per collegare la regola ai corsi');
					$link = 'self.document.location.href=\'completerule_link_courses.php?conditionSetId='.$ruleAr['id'].'\';';
					break;
				case 2:
					$type = 'delete';
					$title = translateFN ('Clicca per cancellare la regola');
					$link = 'deleteRule ($j(this), '.$ruleAr['id'].' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
					break;
			}

			if (isset($type))
			{
				$links[$j] = CDOMElement::create('li','class:liactions');

				$linkshref = CDOMElement::create('button');
				$linkshref->setAttribute('onclick','javascript:'.$link);
				$linkshref->setAttribute('class', $type.'Button tooltip');
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

		$rulesData[$i] = array (
				$labels[0]=>$ruleAr['descrizione'],
				$labels[1]=>$linksHtml);
	}

	$historyTable = new Table();
	$historyTable->initTable('0','center','1','1','90%','','','','','1','0','','default','completeRulesList');
	$historyTable->setTable($rulesData,translateFN('Elenco delle regole di completamento'),translateFN('Elenco delle regole di completamento'));
	$histData = $historyTable->getTable();
	$histData= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $histData, 1); // replace first occurence of class

	$rulesIndexDIV->addChild($newButton);
	$rulesIndexDIV->addChild(CDOMElement::create('div','class:clearfix'));
	$rulesIndexDIV->addChild(new CText($histData));
	// if there are more than 10 rows, repeat the add new button below the table
	if (isset($i) && $i>10)
	{
		$bottomButton = clone $newButton;
		$bottomButton->setAttribute('class', 'newButton bottom');
		$rulesIndexDIV->addChild($bottomButton);
	}
} // if (!AMA_DB::isError($rulesList))
else
{
	$rulesIndexDIV->addChild (new CText(translateFN('Errore nella lettura dell\'elenco delle regole')));
}

$data = $rulesIndexDIV->getHtml();

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
array_push($layout_dataAr['CSS_filename'], MODULES_SERVICECOMPLETE_PATH.'/layout/tooltips.css');

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'label' => translateFN('Regole di completamento'),
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