<?php
use Lynxlab\ADA\Module\Badges\BadgesActions;
use Lynxlab\ADA\Module\Badges\AMABadgesDataHandler;

/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_BADGES_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(BadgesActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

/**
 * @var AMABadgesDataHandler $GLOBALS['dh']
 */
if (array_key_exists('sess_selected_tester', $_SESSION)) {
	$GLOBALS['dh'] = AMABadgesDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
}

$data = ['error' => translateFN('errore sconosciuto')];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	$params = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

	if (class_exists(AMABadgesDataHandler::MODELNAMESPACE.$params['object'])) {
		if ($params['object'] == 'Badge') {
			$badgesData = array();
			$badgesList = $GLOBALS['dh']->findAll($params['object']);
			if (!AMA_DB::isError($badgesList)) {
				/**
				 * @var \Lynxlab\ADA\Module\Badges\Badge $badge
				 */
				foreach ($badgesList as $badge) {
					$links = array();
					$linksHtml = "";

					for ($j = 0; $j < 2; $j++) {
						switch ($j) {
							case 0:
								if (BadgesActions::canDo(BadgesActions::EDIT_BADGE)) {
									$type = 'edit';
									$title = translateFN('Modifica badge');
									$link = 'editBadge(\'' . $badge->getUuid() . '\');';
								}
								break;
							case 1:
								if (BadgesActions::canDo(BadgesActions::TRASH_BADGE)) {
									$type = 'delete';
									$title = translateFN('Cancella badge');
									$link = 'deleteBadge($j(this), \'' . $badge->getUuid() . '\');';
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

					$tmpelement = \CDOMElement::create('img','class:ui tiny image,src:'.$badge->getImageUrl().'?t='.time());
					$badgesData[] = array(
						// NOTE: the timestamp parameter added to the png will prevent caching
						$tmpelement->getHtml(),
						$badge->getName(),
						nl2br($badge->getDescription()),
						nl2br($badge->getCriteria()),
						$linksHtml
					);
				}
			} // if (!AMA_DB::isError($badgesList))
			$data = [ 'data' => $badgesData ];
		} else if ($params['object'] == 'CourseBadge') {
			require_once MODULES_SERVICECOMPLETE_PATH .'/include/init.inc.php';
			$cdh = AMACompleteDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

			$badgesData = array();
			$badgesList = $GLOBALS['dh']->findBy($params['object'],['id_corso' => $params['courseId']]);

			if (!AMA_DB::isError($badgesList)) {
				/**
				 * @var \Lynxlab\ADA\Module\Badges\CourseBadges $cb
				 */
				foreach ($badgesList as $cb) {
					$links = array();
					$linksHtml = "";

					$badge = $GLOBALS['dh']->findBy('Badge', [ 'uuid' => $cb->getBadge_uuid() ]);
					$cs = $cdh->getCompleteConditionSet($cb->getId_conditionset());
					if (is_array($badge) && count($badge)==1) {
						$badge = reset($badge);
					}

					if ($badge instanceof \Lynxlab\ADA\Module\Badges\Badge && $cs instanceof \CompleteConditionSet) {
						for ($j = 0; $j < 1; $j++) {
							switch ($j) {
								case 0:
									if (BadgesActions::canDo(BadgesActions::BADGE_COURSE_TRASH)) {
										$type = 'delete';
										$title = translateFN('Cancella');
										$link = 'deleteCourseBadge($j(this), '.
											htmlspecialchars(json_encode(
												[
													'badge_uuid' => $badge->getUuid(),
													'id_corso' => $params['courseId'],
													'id_conditionset' => $cs->getID()
												]
											), ENT_QUOTES, ADA_CHARSET)
										.');';
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

						$badgesData[] = array(
							$badge->getName(),
							$cs->description,
							$linksHtml
						);
					}
				}
			} // if (!AMA_DB::isError($badgesList))
			$data = [ 'data' => $badgesData ];
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
