<?php
/**
 * IMPORT MODULE
 *
 * @package		export/import course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		impexport
 * @version		0.1
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
        AMA_TYPE_SWITCHER => array('layout'),
        AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS
require_once dirname(__FILE__).'/config/config.inc.php';

$self = whoami();

$data = null;
$canEdit = in_array($userObj->getType(), [ AMA_TYPE_SWITCHER ]);

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('Repository corsi')) .' &gt; '. translateFN('Elenco esportazioni'),
    'data' => $data,
    'modalID' => 'deleteConfirm',
    'modalHeader' => translateFN('Conferma cancellazione'),
    'modalContent' => '<p>'.translateFN("Questo cancellerà l'esportazione definitivamente").'</p>',
    'modalYES' => translateFN('S&igrave;'),
    'modalNO' => translateFN('NO')
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    ROOT_DIR . '/js/include/jquery/dataTables/dataTables.rowGroup.min.js',
    JQUERY_NO_CONFLICT
);

/**
 * include proper jquery ui css file depending on wheter there's one
 * in the template_family css path or the default one
*/
$templateFamily = (isset($userObj->template_family) && strlen($userObj->template_family)>0) ? $userObj->template_family : ADA_TEMPLATE_FAMILY;
$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    ROOT_DIR . '/js/include/jquery/dataTables/rowGroup.semanticui.min.css',
    SEMANTICUI_DATATABLE_CSS
);

$optionsAr['onload_func'] = 'initDoc('.($canEdit ? 'true':'false').');';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
