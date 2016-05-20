<?php
/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version        0.1
 */

define ('SCO_OBJECTS_DIR' , '/SCOObjects');
define ('SCO_BASEDIR', MODULES_SCORM_PATH. SCO_OBJECTS_DIR);
define ('MODULES_SCORM_LOGDIR' , ROOT_DIR.'/log/scorm/');
define ('SCO_MANIFEST_XML', 'imsmanifest.xml');
define ('SCORM_ZERO_TIME', '0000:00:00');
define ('SCORM2004_ZERO_TIME', 'PT0H0M0S');

$GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS'] = array(
		'1.2' => '1.2',
		'CAM 1.3' => '2004',
		'2004 3rd Edition' => '2004',
		'2004 4th Edition' => '2004'
);

$GLOBALS['MODULES_SCORM_STATIC']['cmi.core._children'] = 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,exit,session_time';
$GLOBALS['MODULES_SCORM_STATIC']['cmi.core.score._children'] = 'raw,min,max';
$GLOBALS['MODULES_SCORM_STATIC']['cmi.score._children'] = $GLOBALS['MODULES_SCORM_STATIC']['cmi.core.score._children'];
$GLOBALS['MODULES_SCORM_STATIC']['cmi.core.student_name'] = function() {
	return $_SESSION['sess_userObj']->getFullName();
};
$GLOBALS['MODULES_SCORM_STATIC']['cmi.core.student_id'] = function() {
	return $_SESSION['sess_userObj']->getId();
}
?>