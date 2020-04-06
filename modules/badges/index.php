<?php
/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\Badges\BadgesActions;

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
list($allowedUsersAr, $neededObjAr) = array_values(BadgesActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR . '/include/module_init.inc.php');
require_once(ROOT_DIR . '/browsing/include/browsing_functions.inc.php');
BrowsingHelper::init($neededObjAr);

$self = 'badges';

/**
 * generate HTML for 'New Badge' button and the empty table
 */
$badgesIndexDIV = CDOMElement::create('div', 'id:badgesindex');

if (BadgesActions::canDo(BadgesActions::NEW_BADGE)) {
    $newButton = CDOMElement::create('button');
    $newButton->setAttribute('class', 'newButton top');
    $newButton->setAttribute('title', translateFN('Clicca per creare un nuovo badge'));
    $newButton->setAttribute('onclick', 'javascript:editBadge(null);');
    $newButton->addChild(new CText(translateFN('Nuovo Badge')));
    $badgesIndexDIV->addChild($newButton);
}

$badgesIndexDIV->addChild(CDOMElement::create('div', 'class:clearfix'));

$labels = array( '&nbsp;',
    translateFN('nome'), translateFN('descrizione'),
    translateFN('criterio'), translateFN('azioni')
);

$badgesTable = BaseHtmlLib::tableElement('id:completeBadgesList', $labels, [], '', translateFN('Elenco dei badges'));
$badgesTable->setAttribute('class', $badgesTable->getAttribute('class') . ' ' . ADA_SEMANTICUI_TABLECLASS);
$badgesIndexDIV->addChild($badgesTable);

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('badges')),
    'data' => $badgesIndexDIV->getHtml()
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    JQUERY_UI,
    MODULES_BADGES_PATH . '/js/dropzone.js',
    JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    SEMANTICUI_DATATABLE_CSS,
    MODULES_BADGES_PATH . '/layout/tooltips.css'
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
