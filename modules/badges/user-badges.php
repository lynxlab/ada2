<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\Badges\BadgesActions;
use Lynxlab\ADA\Module\Badges\AMABadgesDataHandler;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_GDPR_PATH . '/config/config.inc.php';

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
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
require_once MODULES_SERVICECOMPLETE_PATH .'/include/init.inc.php';
BrowsingHelper::init($neededObjAr);

$self = 'user-badges';

/**
 * @var AMABadgesDataHandler
 */
// $GLOBALS['dh'] = AMABadgesDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));


$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('badges')) .' &gt; '. translateFN('Tutti i tuoi badges'),
    'data' => ''
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_NO_CONFLICT,
    MODULES_BADGES_PATH . '/js/badgesToHTML.js',
);

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    MODULES_BADGES_PATH . '/layout/tooltips.css'
);

$optionsAr['onload_func'] = 'initDoc(\''.htmlspecialchars(MODULES_BADGES_HTTP.'/ajax/getUserBadges.php', ENT_QUOTES, ADA_CHARSET).'\');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
