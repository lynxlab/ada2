<?php

/**
 * List instances - this module provides list instances functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';

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
SwitcherHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */

//$courseId = DataValidator::is_uinteger($_GET['course']);
//if($courseId !== false && $courseId > 0) {


if ($courseObj instanceof Course && $courseObj->isFull()) {

    $courseId = $courseObj->getId();
    $course_title = $courseObj->getTitle();



    $fieldsAr = array('data_inizio', 'data_inizio_previsto', 'durata', 'data_fine', 'title');
    $instancesAr = $dh->course_instance_get_list($fieldsAr, $courseId);
    if (is_array($instancesAr) && count($instancesAr) > 0) {
        $thead_data = array(
            translateFN('id'),
            translateFN('classe'),
            translateFN('data inizio previsto'),
            translateFN('durata'),
            translateFN('data inizio'),
            translateFN('data fine'),
            translateFN('tutor'),
            translateFN('iscritti'),
            translateFN('azioni')
        );
        $tbody_data = array();

        $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
        $delete_img = CDOMElement::create('img', 'src:img/trash.png,alt:' . translateFN('Delete instance'));
        //$view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');
        if (defined('MODULES_STUDENTSGROUPS') && MODULES_STUDENTSGROUPS) {
            $subscribeGroup_img = CDOMElement::create('img', 'class:subscribe-group-icon,src:img/add_instances.png,alt:' . translateFN('Iscrivi gruppo'));
        }

        foreach ($instancesAr as $instance) {
            $instanceId = $instance[0];

            /*
             * Da migliorare, spostare l'ottenimento dei dati necessari in un'unica query
             * per ogni istanza corso (qualcosa che vada a sostituire course_instance_get_list solo in questo caso.
             */
            $tutorId = $dh->course_instance_tutor_get($instanceId);
            if (!AMA_DataHandler::isError($tutorId) && $tutorId !== false) {
                $tutor_infoAr = $dh->get_tutor($tutorId);
                if (!AMA_DataHandler::isError($tutor_infoAr)) {
                    $tutorFullName = $tutor_infoAr['nome'] . ' ' . $tutor_infoAr['cognome'];
                } else {
                    $tutorFullName = translateFN('Utente non trovato');
                }
            } else {
                $tutorFullName = translateFN('Nessun tutor');
            }

            $edit_link = BaseHtmlLib::link("edit_instance.php?id_course=$courseId&id_course_instance=$instanceId", $edit_img->getHtml());
            $edit_link->setAttribute('title', translateFN('Modifica istanza'));
            //  $view_link = BaseHtmlLib::link("view_instance.php?id=$instanceId", $view_img->getHtml());
            $delete_link = BaseHtmlLib::link("delete_instance.php?id_course=$courseId&id_course_instance=$instanceId", $delete_img->getHtml());
            $delete_link->setAttribute('title', translateFN('Cancella istanza'));
            $actionsArr = [
                $edit_link,
                // $view_link,
                $delete_link
            ];
            if (defined('MODULES_STUDENTSGROUPS') && MODULES_STUDENTSGROUPS) {
                $subscribeGroup_link = BaseHtmlLib::link('javascript:void(0)', $subscribeGroup_img);
                $subscribeGroup_link->setAttribute('class', 'subscribe-group');
                $subscribeGroup_link->setAttribute('data-courseid', $courseId);
                $subscribeGroup_link->setAttribute('data-instanceid', $instanceId);
                $subscribeGroup_link->setAttribute('title', translateFN('Iscrivi gruppo'));
                /**
                 * insert subscribeGroup link before deletelink
                 */
                array_splice($actionsArr, count($actionsArr) - 1, 0, [$subscribeGroup_link]);
            }
            $actions = BaseHtmlLib::plainListElement('class:actions inline_menu', $actionsArr);

            if ($instance[1] > 0) {
                $start_date = AMA_DataHandler::ts_to_date($instance[1]);
            } else {
                $start_date = translateFN('Non iniziato');
            }
            $duration = sprintf("%d giorni", $instance[3]);
            $scheduled = AMA_DataHandler::ts_to_date($instance[2]);
            $end_date =  AMA_DataHandler::ts_to_date($instance[4]);
            $title = $instance[5];

            $assign_tutor_link = BaseHtmlLib::link("assign_tutor.php?id_course=$courseId&id_course_instance=$instanceId", $tutorFullName);
            $subscriptions_link = BaseHtmlLib::link(
                "course_instance.php?id_course=$courseId&id_course_instance=$instanceId",
                translateFN('Lista studenti')
            );
            $tbody_data[] = array(
                $instanceId,
                $title,
                $scheduled,
                $duration,
                $start_date,
                $end_date,
                $assign_tutor_link,
                $subscriptions_link,
                $actions
            );
        }
        $data = BaseHtmlLib::tableElement('id:list_instances, class:' . ADA_SEMANTICUI_TABLECLASS, $thead_data, $tbody_data);
    } else {
        $data = new CText(translateFN('Non sono state trovate istanze per il corso selezionato'));
    }
} else {
    $data = new CText(translateFN('Non sono state trovate istanze per il corso selezionato'));
}


$label = translateFN('Lista istanze del corso') . ' ' . $course_title;
$help = translateFN('Da qui il provider admin può vedere la lista delle istanze del corso selezionato');

$layout_dataAr['CSS_filename'] = array(
    JQUERY_UI_CSS,
    SEMANTICUI_DATATABLE_CSS,
);
$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    JQUERY_NO_CONFLICT
);

$dataForJS = [
    'datatables' => ['list_instances'],
];

if (defined('MODULES_STUDENTSGROUPS') && MODULES_STUDENTSGROUPS) {
    $layout_dataAr['JS_filename'][] = MODULES_STUDENTSGROUPS_PATH . '/js/instanceSubscribe.js';
    $layout_dataAr['CSS_filename'][] = MODULES_STUDENTSGROUPS_PATH . '/layout/ada_blu/css/showHideDiv.css';
    $dataForJS['loadModuleJS'] = [
        [
            'baseUrl' => MODULES_STUDENTSGROUPS_HTTP,
            'className' => 'studentsgroupsAPI.GroupInstanceSubscribe',
        ],
    ];
}


$optionsAr = array('onload_func' => 'initDoc(' . htmlentities(json_encode($dataForJS), ENT_COMPAT, ADA_CHARSET) . ');');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'edit_profile' => $userObj->getEditProfilePage(),
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);
