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
// require_once MODULES_GDPR_PATH . '/config/config.inc.php';

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
BrowsingHelper::init($neededObjAr);

$self = 'user-badges';

if (in_array($userObj->getType(), [ AMA_TYPE_SWITCHER , AMA_TYPE_TUTOR ])) {
    if (isset($_GET['id_student'])) {
        $title = translateFN('Badges dello studente');
        $studentObj = \MultiPort::findUser(trim($_GET['id_student']));
        if(!AMA_DataHandler::isError($studentObj)) {
            $title .= ': <strong>'.$studentObj->getFullName().'</strong>';
        }
    }
    if (isset($_GET['id_instance'])) {
        /**
         * @var AMABadgesDataHandler $bdh
         */
        $bdh = AMABadgesDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $instance = $bdh->get_instance_with_course($_GET['id_instance']);
        if (!\AMA_DB::isError($instance) && is_array($instance) && count($instance)==1) {
            $instance = reset($instance);
            if (!isset($title)) $title = "Badges";
            $title .= " per la classe <strong>%s</strong> del corso <strong>%s</strong>";
            $title = sprintf(translateFN($title),$instance['title'], $instance['titolo']);
        }
    }
} else {
    $title = translateFN('Tutti i tuoi badges');
}

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('badges')) .' &gt; '. $title,
    'edit_profile'=>$userObj->getEditProfilePage(),
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

$params = [ 'id_student' => 'userId' , 'id_course' => 'courseId' , 'id_instance' => 'courseInstanceId' ];
foreach($params as $key => $val) {
    if (isset($_GET[$key]) && strlen(trim($_GET[$key]))) $params[$val] = trim($_GET[$key]);
    unset($params[$key]);
}
$params = http_build_query($params);

$optionsAr['onload_func'] = 'initDoc(\''.htmlspecialchars(MODULES_BADGES_HTTP.'/ajax/getUserBadges.php'.(strlen($params)>0 ? '?'.$params : ''), ENT_QUOTES, ADA_CHARSET).'\');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
