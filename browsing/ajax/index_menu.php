<?php
/**
 * show index menu
 *
 * @author		Sara Capotosti <sara@lynxlab.com>
 * @copyright   Copyright (c) 2001-2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_VISITOR      => array('layout','course'),
  AMA_TYPE_STUDENT      => array('layout','tutor','course','course_instance'),
  AMA_TYPE_TUTOR 		=> array('layout','course','course_instance'),
  AMA_TYPE_AUTHOR       => array('layout','course')
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
//$self = 'index';

include_once '../include/browsing_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
BrowsingHelper::init($neededObjAr);

/**
 * YOUR CODE HERE
 */
include_once CORE_LIBRARY_PATH.'/includes.inc.php';
include_once ROOT_DIR.'/include/bookmark_class.inc.php';

if (!isset($hide_visits)) {
  $hide_visits = 1; // default: no visits countg
}

 if (!isset($order)) {
  $order = 'struct'; // default
 }

if (!isset($expand)) {
    $expand = 1; // default: 1 level of nodes
 }

$div_link = CDOMElement::create('div');
$link_expand = CDOMElement::create('a');
$link_expand->setAttribute('id','expandNodes');
$link_expand->setAttribute('href','javascript:void(0);');
$link_expand->setAttribute('onclick',"toggleVisibilityByDiv('structIndex','show');");
$link_expand->addChild(new CText(translateFN('Apri Nodi')));
$link_collapse = CDOMElement::create('a');
$link_collapse->setAttribute('href','javascript:void(0);');
$link_collapse->setAttribute('onclick',"toggleVisibilityByDiv('structIndex','hide');");
$link_collapse->addChild(new CText(translateFN('Chiudi Nodi')));

$div_link->addChild($link_expand);
$div_link->addChild(new CText(' | '));
$div_link->addChild($link_collapse);

$exp_link = $div_link->getHtml();

$order_div = CDOMElement::create('div','id:ordering');

$order = 'struct';
$alfa = CDOMElement::create('span','class:not_selected');
$link = CDOMElement::create('a', "href:main_index.php?order=alfa&expand=$expand");
$link->addChild(new CText(translateFN('Ordina per titolo')));
$alfa->addChild($link);
$order_div->addChild($alfa);
$order_div->addChild(new CText('|'));
$struct = CDOMElement::create('span','class:selected');
$struct->addChild(new CText(translateFN('Ordina per struttura')));
$order_div->addChild($struct);
$expand_nodes = true;

$search_label = translateFN('Cerca nell\'Indice:');
$node_type = 'standard_node';

/*
 * vito, 23 luglio 2008
 */

if ($expand_nodes) {
        $node_index  = $exp_link;
}
$main_index = CourseViewer::displayMainIndex($userObj, $sess_id_course, $expand, $order, $sess_id_course_instance,'structIndex');
if(!AMA_DataHandler::isError($main_index)) {
    $node_index .= $main_index->getHtml();
 }
$node_index=preg_replace('#</?'.'img'.'[^>]*>#is', '', $node_index);

echo($node_index);