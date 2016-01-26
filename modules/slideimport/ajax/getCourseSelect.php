<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR   => array('layout'),
		AMA_TYPE_TUTOR    => array('layout'),
		AMA_TYPE_STUDENT  => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
require_once MODULES_SLIDEIMPORT_PATH . '/config/config.inc.php';

/**
 * load course list from the DB and output the generated select in a template field
 */
$providerCourses = $GLOBALS['dh']->find_courses_list (array ('nome','titolo'),'`id_utente_autore`='.$userObj->getId());
$html = translateFN('Nessun corso trovato');

if (!AMA_DB::isError($providerCourses)) {
	$courses = array();
	foreach($providerCourses as $course) {
		$courses[$course[0]] = '('.$course[0].') '.$course[1].' - '.$course[2];
	}

	if(count($courses)>0) {
		reset($courses);
		$html = BaseHtmlLib::selectElement2('id:courseSelect,class:ui search selection dropdown', $courses, key($courses))->getHtml();
	}
}
echo $html;
?>