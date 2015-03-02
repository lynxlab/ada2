<?php
/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
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
require_once MODULES_CLASSROOM_PATH .'/config/config.inc.php';

$self = whoami();

$GLOBALS['dh'] = AMAClassroomDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

/**
 * generate HTML for 'New Venue' button and the table
 */

$venuesIndexDIV = CDOMElement::create('div','id:venuesindex');

$newButton = CDOMElement::create('button');
$newButton->setAttribute('class', 'newButton top');
$newButton->setAttribute('title', translateFN('Clicca per creare un nuovo luogo'));
$newButton->setAttribute('onclick', 'javascript:editVenue(null);');
$newButton->addChild (new CText(translateFN('Nuovo Luogo')));
$venuesIndexDIV->addChild($newButton);

$venuesData = array();
$venuesList = $GLOBALS['dh']->classroom_getAllVenues();

if (!AMA_DB::isError($venuesList)) {
	
	$labels = array (translateFN('nome'), translateFN('Nominativo di contatto'), 
					 translateFN('Telefono del contatto'), translateFN('E-Mail del contatto'), 
					 translateFN('azioni'));

	foreach ($venuesList as $i=>$venueAr) {
		$links = array();
		$linksHtml = "";
			
		for ($j=0;$j<2;$j++) {
			switch ($j) {
				case 0:
					$type = 'edit';
					$title = translateFN('Modifica luogo');
					$link = 'editVenue('.$venueAr['id_venue'].');';
					break;
				case 1:
					$type = 'delete';
					$title = translateFN ('Cancella luogo');
					$link = 'deleteVenue($j(this), '.$venueAr['id_venue'].' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
					break;
			}
				
			if (isset($type)) {
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

		if (!empty($links)) {
			$linksul = CDOMElement::create('ul','class:ulactions');
			foreach ($links as $link) $linksul->addChild ($link);
			$linksHtml = $linksul->getHtml();
		} else $linksHtml = '';
		
		if (DataValidator::validate_email($venueAr['contact_email'])) {
			$emailHref = CDOMElement::create('a');
			$emailHref->setAttribute('href', 'mailto:'.$venueAr['contact_email']);
			$emailHref->addChild(new CText($venueAr['contact_email']));
			$emailOut = $emailHref->getHtml();
		} else {
			$emailOut = $venueAr['contact_email'];
		}

		$venuesData[$i] = array (
				$labels[0]=>$venueAr['name'],
				$labels[1]=>$venueAr['contact_name'],
				$labels[2]=>$venueAr['contact_phone'],
				$labels[3]=>$emailOut,				
				$labels[4]=>$linksHtml);
	}
	
	$venuesTable = BaseHtmlLib::tableElement('id:completeVenuesList',$labels,$venuesData,'',translateFN('Elenco dei luoghi'));
	$venuesIndexDIV->addChild($venuesTable);
	
	// if there are more than 10 rows, repeat the add new button below the table
	if ($i>10) {
		$bottomButton = clone $newButton;
		$bottomButton->setAttribute('class', 'newButton bottom');
		$venuesIndexDIV->addChild($bottomButton);
	} 
} // if (!AMA_DB::isError($venuesList))


$data = $venuesIndexDIV->getHtml();

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => translateFN('classroom'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS,
		MODULES_CLASSROOM_PATH.'/layout/tooltips.css'
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
