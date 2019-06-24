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
require_once MODULES_GDPR_PATH . '/config/config.inc.php';

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
 * @var AMABadgesDataHandler
 */
$GLOBALS['dh'] = AMABadgesDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

/**
 * generate HTML for 'New Badge' button and the table
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

$i = 0;
$badgesData = array();
$badgesList = $GLOBALS['dh']->findAll('Badge');

if (!AMA_DB::isError($badgesList)) {

    $labels = array( '&nbsp;',
        translateFN('nome'), translateFN('descrizione'),
        translateFN('criterio'), translateFN('azioni')
    );

    /**
     * @var \Lynxlab\ADA\Module\Badges\Badge $badge
     */
    foreach ($badgesList as $i => $badge) {
        $links = array();
        $linksHtml = "";

        for ($j = 0; $j < 2; $j++) {
            switch ($j) {
                case 0:
                    if (BadgesActions::canDo(BadgesActions::EDIT_BADGE)) {
                        $type = 'edit';
                        $title = translateFN('Modifica badge');
                        $link = 'editBadge(\'' . $badge->getUuid() . '\');';
                    }
                    break;
                case 1:
                    if (BadgesActions::canDo(BadgesActions::TRASH_BADGE)) {
                        $type = 'delete';
                        $title = translateFN('Cancella badge');
                        $link = 'deleteBadge($j(this), \'' . $badge->getUuid() . '\' , \'' . urlencode(translateFN("Questo cancellerà l'elemento selezionato")) . '\');';
                    }
                    break;
            }

            if (isset($type)) {
                $links[$j] = CDOMElement::create('li', 'class:liactions');

                $linkshref = CDOMElement::create('button');
                $linkshref->setAttribute('onclick', 'javascript:' . $link);
                $linkshref->setAttribute('class', $type . 'Button tooltip');
                $linkshref->setAttribute('title', $title);
                $links[$j]->addChild($linkshref);
                // unset for next iteration
                unset($type);
            }
        }

        if (!empty($links)) {
            $linksul = CDOMElement::create('ul', 'class:ulactions');
            foreach ($links as $link) $linksul->addChild($link);
            $linksHtml = $linksul->getHtml();
        } else $linksHtml = '';

        $badgesData[$i] = array(
            $labels[0] => (\CDOMElement::create('img','class:ui tiny image,src:'.$badge->getImageUrl()))->getHtml(),
            $labels[1] => $badge->getName(),
            $labels[2] => $badge->getDescription(),
            $labels[3] => $badge->getCriteria(),
            $labels[4] => $linksHtml
        );
    }

    $badgesTable = BaseHtmlLib::tableElement('id:completeBadgesList', $labels, $badgesData, '', translateFN('Elenco dei badges'));
    $badgesTable->setAttribute('class', $badgesTable->getAttribute('class') . ' ' . ADA_SEMANTICUI_TABLECLASS);
    $badgesIndexDIV->addChild($badgesTable);

    // if there are more than 10 rows, repeat the add new button below the table
    if (BadgesActions::canDo(BadgesActions::NEW_BADGE) && $i > 10) {
        $bottomButton = clone $newButton;
        $bottomButton->setAttribute('class', 'newButton bottom');
        $badgesIndexDIV->addChild($bottomButton);
    }
} // if (!AMA_DB::isError($badgesList))


$data = $badgesIndexDIV->getHtml();

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('badges')),
    'data' => $data,
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
