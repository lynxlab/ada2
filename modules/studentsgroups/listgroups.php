<?php
/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\StudentsGroups\Groups;
use Lynxlab\ADA\Module\StudentsGroups\StudentsGroupsActions;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

// MODULE's OWN IMPORTS

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(StudentsGroupsActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

$self = whoami();

/**
 * generate HTML for 'New Group' button and the empty table
 */
$groupsIndexDIV = CDOMElement::create('div', 'id:groupsindex');

if (StudentsGroupsActions::canDo(StudentsGroupsActions::NEW_GROUP)) {
    $newButton = CDOMElement::create('button');
    $newButton->setAttribute('class', 'newButton top');
    $newButton->setAttribute('title', translateFN('Clicca per creare un nuovo gruppo'));
    $newButton->setAttribute('onclick', 'javascript:editGroup(null);');
    $newButton->addChild(new CText(translateFN('Nuovo Gruppo')));
    $groupsIndexDIV->addChild($newButton);
}

$groupsIndexDIV->addChild(CDOMElement::create('div', 'class:clearfix'));

$labels = [
    '&nbsp;',
    translateFN('nome'),
];

foreach(Groups::customFieldLbl as $cLbl) {
    array_push($labels, translateFN($cLbl));
}

$labels[] = translateFN('azioni');

$groupsTable = BaseHtmlLib::tableElement('id:completeGropusList', $labels, [], '', translateFN('Elenco dei gruppi'));
$groupsTable->setAttribute('class', $groupsTable->getAttribute('class') . ' ' . ADA_SEMANTICUI_TABLECLASS);
$groupsIndexDIV->addChild($groupsTable);

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('Gruppi di Studenti')),
    'data' => $groupsIndexDIV->getHtml()
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    JQUERY_UI,
    MODULES_STUDENTSGROUPS_PATH . '/js/dropzone.js',
    JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    SEMANTICUI_DATATABLE_CSS,
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
