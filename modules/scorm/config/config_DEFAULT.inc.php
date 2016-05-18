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

$GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVARSIONS'] = array(
		'1.2' => '1.2',
		'CAM 1.3' => '2004',
		'2004 3rd Edition' => '2004',
		'2004 4th Edition' => '2004'
);

?>
