<?php
/**
 * CLASSAGENDA MODULE.
 *
 * @package        classagenda module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classagenda
 * @version		   0.1
 */

require_once MODULES_CLASSAGENDA_PATH.'/include/AMAClassagendaDataHandler.inc.php';

define('MODULES_CLASSAGENDA_EDIT_CAL',				1); // edit calendar action code
define('MODULES_CLASSAGENDA_DO_ROLLCALL',			2); // do the class roll call action code
define('MODULES_CLASSAGENDA_DO_ROLLCALLHISTORY',	3); // roll call history action code

define('MODULES_CLASSAGENDA_ALL_INSTANCES',			1); // filter all course instances
define('MODULES_CLASSAGENDA_STARTED_INSTANCES',		2); // filter started course instances
define('MODULES_CLASSAGENDA_NONSTARTED_INSTANCES',	3); // filter non started course instances
define('MODULES_CLASSAGENDA_CLOSED_INSTANCES',		4); // filter closed course instances

?>
