<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\StudentsGroups\AMAStudentsGroupsDataHandler;
use Lynxlab\ADA\Module\StudentsGroups\Groups;
use Lynxlab\ADA\Module\StudentsGroups\StudentsGroupsActions;
use Svg\Tag\Group;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(StudentsGroupsActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMAStudentsGroupsDataHandler $GLOBALS['dh']
 */
if (array_key_exists('sess_selected_tester', $_SESSION)) {
	$GLOBALS['dh'] = AMAStudentsGroupsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
}

$data = ['error' => translateFN('errore sconosciuto')];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	$params = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

	if (class_exists(AMAStudentsGroupsDataHandler::MODELNAMESPACE.$params['object'])) {
		if ($params['object'] == 'Groups') {
			$groupsData = array();
			if ( isset($_REQUEST['id']) && intval(trim($_REQUEST['id']))>0 ) {
				$withMembers = true;
				$withActions = false;
				$withGroupDetails = false;
				$groupsList = $GLOBALS['dh']->findBy($params['object'], [ 'id' => intval(trim($_REQUEST['id'])) ] );
			} else {
				$withMembers = false;
				$withActions = true;
				$withGroupDetails = true;
				$groupsList = $GLOBALS['dh']->findAll($params['object']);
			}
			if (!AMA_DB::isError($groupsList)) {
				/**
				 * @var \Lynxlab\ADA\Module\StudentsGroups\Groups $group
				 */
				foreach ($groupsList as $group) {
					if ($withActions) {
						$links = array();
						$linksHtml = "";

						for ($j = 0; $j < 2; $j++) {
							switch ($j) {
								case 0:
									if (StudentsGroupsActions::canDo(StudentsGroupsActions::EDIT_GROUP)) {
										$type = 'edit';
										$title = translateFN('Modifica gruppo');
										$link = 'editGroup(\'' . $group->getId() . '\');';
									}
									break;
								case 1:
									if (StudentsGroupsActions::canDo(StudentsGroupsActions::TRASH_GROUP)) {
										$type = 'delete';
										$title = translateFN('Cancella gruppo');
										$link = 'deleteGroup($j(this), \'' . $group->getId() . '\');';
									}
									break;
							}

							if (isset($type)) {
								$links[$j] = CDOMElement::create('li', 'class:liactions');

								$linkshref = CDOMElement::create('button');
								$linkshref->setAttribute('onclick', 'javascript:' . $link);
								$linkshref->setAttribute('class', $type . 'Button tooltip');
								$linkshref->setAttribute('title', $title);
								$links[$j]->addChild($linkshref);
								// unset for next iteration
								unset($type);
							}
						}

						if (!empty($links)) {
							$linksul = CDOMElement::create('ul', 'class:ulactions');
							foreach ($links as $link) $linksul->addChild($link);
							$linksHtml = $linksul->getHtml();
						} else $linksHtml = '';
					}

					$tmpData['label'] = $group->getLabel();
					foreach($group->getCustomFields() as $key => $val) {
						if (array_key_exists($key, Groups::customFieldsVal) && array_key_exists($val, Groups::customFieldsVal[$key])) {
							$tmpData[Groups::customFieldPrefix.$key] = Groups::customFieldsVal[$key][$val];
						} else $tmpData[Groups::customFieldPrefix.$key] = null;
					}
					if ($withGroupDetails) {
						$imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
						$imgDetails->setAttribute('title', translateFN('visualizza/nasconde i dettagli del gruppo'));
						$imgDetails->setAttribute('onclick','toggleGroupDetails('.$group->getId().',this);');
						$imgDetails->setAttribute('style', 'cursor:pointer;');
						$tmpData['detailsBtn'] = $imgDetails->getHtml();
					}
					if ($withActions) {
						$tmpData['actions'] = $linksHtml;
					}
					if ($withMembers) {
						$tmpData['members'] = $group->getMembers();
					}
					array_push($groupsData, $tmpData);
				}
			} // if (!AMA_DB::isError($groupsList))
			$data = [ 'data' => $groupsData ];
		}
	} else {
		$data = [ 'error' => translateFN('Oggetto non valido')];
	}
}

if (array_key_exists('error', $data)) {
	header (' ', true, 404);
	$data['data'] = [];
}
header('Content-Type: application/json');
die (json_encode($data));
