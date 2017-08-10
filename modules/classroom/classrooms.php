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
 * generate HTML for 'New Classroom' button and the table
 */

$classroomsIndexDIV = CDOMElement::create('div','id:classroomsindex');

$newButton = CDOMElement::create('button');
$newButton->setAttribute('class', 'newButton top');
$newButton->setAttribute('title', translateFN('Clicca per creare una nuova aula'));
$newButton->setAttribute('onclick', 'javascript:editClassroom(null);');
$newButton->addChild (new CText(translateFN('Nuova Aula')));
$classroomsIndexDIV->addChild($newButton);
$classroomsIndexDIV->addChild(CDOMElement::create('div','class:clearfix'));

$classRoomsData = array();
$classroomsList = $GLOBALS['dh']->classroom_getAllClassrooms();

if (!AMA_DB::isError($classroomsList)) {

	$labels = array (translateFN('nome'), translateFN('Luogo'),
					 translateFN('Posti'), translateFN('Computer'),
					 translateFN('Comodità'), translateFN('azioni'));

	foreach ($classroomsList as $i=>$classroomAr) {
		$links = array();
		$linksHtml = "";

		for ($j=0;$j<2;$j++) {
			switch ($j) {
				case 0:
					$type = 'edit';
					$title = translateFN('Modifica aula');
					$link = 'editClassroom('.$classroomAr['id_classroom'].');';
					break;
				case 1:
					$type = 'delete';
					$title = translateFN ('Cancella aula');
					$link = 'deleteClassroom($j(this), '.$classroomAr['id_classroom'].' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
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

		$commonIconClass = 'tooltip';

		if (intval($classroomAr['internet'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
							'globe.png,class:'.$commonIconClass.',title:'.translateFN('Internet'));
		}
		if (intval($classroomAr['wifi'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
							'wifi.png,class:'.$commonIconClass.',title:'.translateFN('Wi-Fi'));
		}
		if (intval($classroomAr['projector'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
							'projector.png,class:'.$commonIconClass.',title:'.translateFN('Proiettore'));
		}
		if (intval($classroomAr['mobility_impaired'])==1) {
			$facilities[] = CDOMElement::create('img','src:'.MODULES_CLASSROOM_HTTP.'/layout/images/'.
							'wheelchair.png,class:'.$commonIconClass.',title:'.translateFN('Accesso disabili'));
		}

		if (isset($facilities) && count($facilities)>0) {
			$spanFacilities = CDOMElement::create('span','class:facilities');
			foreach ($facilities as $facility) $spanFacilities->addChild($facility);
			unset ($facilities);
		} else {
			unset ($spanFacilities);
		}


		$classroomsData[$i] = array (
				$labels[0]=>$classroomAr['name'],
				$labels[1]=>$classroomAr['venue_name'],
				$labels[2]=>$classroomAr['seats'],
				$labels[3]=>$classroomAr['computers'],
				$labels[4]=>(isset($spanFacilities)) ? $spanFacilities->getHtml() : 'N/A',
				$labels[5]=>$linksHtml);
	}

	$classroomsTable = BaseHtmlLib::tableElement('id:completeClassroomsList',$labels,$classroomsData,'',translateFN('Elenco delle aule'));
	$classroomsTable->setAttribute('class', $classroomsTable->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
	$classroomsIndexDIV->addChild($classroomsTable);

	// if there are more than 10 rows, repeat the add new button below the table
	if ($i>10) {
		$bottomButton = clone $newButton;
		$bottomButton->setAttribute('class', 'newButton bottom');
		$classroomsIndexDIV->addChild($bottomButton);
	}
} // if (!AMA_DB::isError($classroomsList))

$data = $classroomsIndexDIV->getHtml();

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
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS,
		MODULES_CLASSROOM_PATH.'/layout/tooltips.css'
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
