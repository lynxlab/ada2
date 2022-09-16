<?php
/**
 * @package 	cloneinstance module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\CloneInstance\AMACloneInstanceDataHandler;
use Lynxlab\ADA\Module\CloneInstance\CloneInstanceActions;
use Lynxlab\ADA\Module\CloneInstance\CloneInstanceForm;

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
list($allowedUsersAr, $neededObjAr) = array_values(CloneInstanceActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';
// neededObjArr grants access to switcher only
require_once ROOT_DIR . '/switcher/include/switcher_functions.inc.php';
SwitcherHelper::init($neededObjAr);

require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';

// globals set by SwitcherHelper::init
/** @var \Course $courseObj */
/** @var \Course_instance $courseInstanceObj */

/**
 * @var AMACloneInstanceDataHandler $dh
 */
$dh = AMACloneInstanceDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$summaryArr = [];
$okclass = "green";
$okicon = "circle ok";
$nonokclass = "red";
$nonokicon = "circle ban";
$infoicon = "info";

if (strlen($courseInstanceObj->getStartDate()) > 0) {
    $summaryArr['startDate'] = [
        'text' => sprintf(translateFN("Istanza iniziata il %s"), $courseInstanceObj->getStartDate()),
        'ok' => true,
    ];
} else {
    $summaryArr['startDate'] = [
        'text' => translateFN('Istanza non iniziata'),
        'ok' => false,
    ];
}

$subscriptions = Subscription::findSubscriptionsToClassRoom($courseInstanceObj->getId(), true);
if (count($subscriptions) > 0) {
    $summaryArr['subscriptions'] = [
        'text' => (count($subscriptions) == 1 ?
                    (translateFN('Un utente iscritto')) : (sprintf(translateFN('%d utenti iscritti'), count($subscriptions))) ),
        'ok' => true,
    ];
} else {
    $summaryArr['subscriptions'] = [
        'text' => translateFN('Nessun utente iscritto'),
        'ok' => false,
    ];
}

$tutors = $dh->course_instance_tutor_info_get($courseInstanceObj->getId(), 'ALL');

if (!\AMA_DB::isError($tutors) && $tutors !== false && count($tutors) > 0) {
    $summaryArr['tutors'] = [
        'text' => (count($tutors) == 1 ?
                    (translateFN('Un tutor assegnato')) : (sprintf(translateFN('%d tutor assegnati'), count($tutors))) ),
        'ok' => true,
    ];
    $tutorList = array_map(function($el){
        return $el['nome'].' '.$el['cognome'];
    }, $tutors);
    if (count($tutorList) > 0) {
        $summaryArr['tutors']['text'] .= ': '.implode(', ', $tutorList);
    }

} else {
    $summaryArr['tutors'] = [
        'text' => translateFN('Nessun tutor assegnato'),
        'ok' => false,
    ];
}

$summaryArr['scheduledStartDate'] = [
    'text' => sprintf(translateFN('Inizio previsto il %s'), $courseInstanceObj->getScheduledStartDate()),
];

$summaryArr['duration'] = [
    'text' => translateFN('Durata') . ': ' . ($courseInstanceObj->getDuration() == 1 ?
               (translateFN('zero giorni')) : (sprintf(translateFN('%d giorni'), $courseInstanceObj->getDuration())) ),
];

if (strlen($courseInstanceObj->getEndDate()) > 0) {
    $summaryArr['duration']['text'] .= sprintf(' ('.translateFN("termina il %s").')', $courseInstanceObj->getEndDate());
}

$summaryArr['durationsubscription'] = [
    'text' => translateFN('Durata iscrizione') . ': ' . ($courseInstanceObj->getDurationSubscription() == 1 ?
               (translateFN('zero giorni')) : (sprintf(translateFN('%d giorni'), $courseInstanceObj->getDurationSubscription())) ),
];

$summaryArr['selfinstruction'] = [
    'text' => translateFN('Autoistruzione'). ': ' . ($courseInstanceObj->getSelfInstruction() ? translateFN('Sì') : translateFN('No')),
];

if (count($summaryArr) > 0) {
    $summaryEl = \CDOMElement::create('div', 'class:ui large list');
    foreach ($summaryArr as $addKey => $addEl) {
        $el = \CDOMElement::create('span', 'class:ui item ' . $addKey);
        if (array_key_exists('ok', $addEl)) {
            $el->setAttribute('class', $el->getAttribute('class'). ' ' . ($addEl['ok'] ? $okclass : $nonokclass));
            $el->addChild(\CDOMElement::create('i','class:ui icon ' . ($addEl['ok'] ? $okicon : $nonokicon)));
        } else {
            $el->addChild(\CDOMElement::create('i','class:ui icon ' . $infoicon));
        }
        $el->addChild(new \CText($addEl['text']));
        $summaryEl->addChild($el);
    }
} else {
    $summaryEl = new \CText(translateFN("Impossibile costruire il riepilogo dell'instanza"));
}

$publicServiceLevels = array_keys(
    array_filter($_SESSION['service_level_info'], function($el) { return true === (bool) $el['isPublic']; })
);
$clause = (count($publicServiceLevels)>0) ? '`tipo_servizio` NOT IN ('.implode(',', $publicServiceLevels).')': '';
/** @var array $courses */
$courses = $dh->find_courses_list(array('nome', 'titolo'), $clause);

if (!\AMA_DB::isError($courses) && $courses !== false && count($courses) > 0) {
    $form = new CloneInstanceForm('cloneinstance', null, $courses, $courseInstanceObj);
    $form->withSubmit()->toSemanticUI();
} else {
    $form = new \CText(translateFN('Nessun corso trovato'));
}

$self = whoami();

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => ucfirst(translateFN('Clonazione istanza di un corso')),
    'instanceName' => $courseInstanceObj->getTitle(),
    'courseName' => $courseObj->getTitle(),
    'summary' => $summaryEl->getHtml(),
    'form' => $form->getHtml(),
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    MODULES_CLONEINSTANCE_PATH . '/js/jquery.select-multiple.js',
    MODULES_CLONEINSTANCE_PATH . '/js/jquery.quicksearch.js',
    JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
