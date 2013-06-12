<?php
/**
 * Actions perfomed at the start of each ADA module.
 *
 * This file contains all the actions that a ADA module has to perform before
 * doing anything else.
 *
 * PHP version >= 5.0
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		module_init
 * @version		0.1
 */

/**
 * utility functions
 */
require_once ROOT_DIR.'/include/utilities.inc.php';

/**
 * Database and data validator classes
 */
require_once ROOT_DIR.'/include/database_includes.inc.php';

/**
 * Functions used in this module
 */
require_once ROOT_DIR.'/include/module_init_functions.inc.php';

/**
 * Ada Rendering Engine, used to render module output data
 */
require_once ROOT_DIR.'/include/layout_classes.inc.php';
require_once ROOT_DIR.'/include/output_classes.inc.php';


require_once ROOT_DIR.'/include/translator_class.inc.php';
require_once ROOT_DIR.'/include/output_funcs.inc.php';
//require_once ROOT_DIR.'/include/layout_classes.inc.php';

// provvisoriamente includiamo anche le vecchie classi di oggetti HTML:
require_once ROOT_DIR . '/include/HTML_element_classes.inc.php';
require_once ROOT_DIR . '/include/navigation_history.inc.php';

/**
 * Imports $_GET and $_POST variables
 */
import_request_variables('GP',ADA_GP_VARIABLES_PREFIX);

/**
 *	Validates $_SESSION data
 */
if(!is_array($neededObjAr)) {
  $neededObjAr = array();
}
if(!is_array($allowedUsersAr)) {
  $allowedUsersAr = array();
}
if (!isset($trackPageToNavigationHistory)) {
	$trackPageToNavigationHistory = true;
}
session_controlFN($neededObjAr, $allowedUsersAr, $trackPageToNavigationHistory);

/**
 * Clears variables specified in $whatAR
 */
if(is_array($variableToClearAR)) {
  clear_dataFN($variableToClearAR);
}

$ymdhms = today_dateFN();
?>