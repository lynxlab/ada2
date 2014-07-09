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
    AMA_TYPE_SWITCHER => array('layout','course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */

//$courseId = DataValidator::is_uinteger($_GET['course']);
//if($courseId !== false && $courseId > 0) {


if($courseObj instanceof Course && $courseObj->isFull()) {

    $courseId = $courseObj->getId();
    $course_title = $courseObj->getTitle();



    $fieldsAr = array('data_inizio', 'data_inizio_previsto', 'durata', 'data_fine', 'title');
    $instancesAr = $dh->course_instance_get_list($fieldsAr, $courseId);
    if(is_array($instancesAr) && count($instancesAr) > 0) {
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
        //$view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');

        foreach($instancesAr as $instance) {
            $instanceId = $instance[0];

            /*
             * Da migliorare, spostare l'ottenimento dei dati necessari in un'unica query
             * per ogni istanza corso (qualcosa che vada a sostituire course_instance_get_list solo in questo caso.
             */
             $tutorId = $dh->course_instance_tutor_get($instanceId);
             if(!AMA_DataHandler::isError($tutorId) && $tutorId !== false) {
                $tutor_infoAr = $dh->get_tutor($tutorId);
                if(!AMA_DataHandler::isError($tutor_infoAr)) {
                    $tutorFullName = $tutor_infoAr['nome'] . ' ' . $tutor_infoAr['cognome'];
                } else {
                    $tutorFullName = translateFN('Utente non trovato');
                }
             } else {
                 $tutorFullName = translateFN('Nessun tutor');
             }

            $edit_link = BaseHtmlLib::link("edit_instance.php?id_course=$courseId&id_course_instance=$instanceId", $edit_img->getHtml());
          //  $view_link = BaseHtmlLib::link("view_instance.php?id=$instanceId", $view_img->getHtml());
            $delete_link = BaseHtmlLib::link("delete_instance.php?id_course=$courseId&id_course_instance=$instanceId",
                    translateFN('Delete instance')
                    );
            $actions = BaseHtmlLib::plainListElement('class:inline_menu',array($edit_link/*,$view_link*/, $delete_link));

            if($instance[1] > 0) {
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
                $scheduled ,
                $duration,
                $start_date,
                $end_date,
                $assign_tutor_link,
                $subscriptions_link,
                $actions
            );
        }
        $data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data);
    } else {
        $data = new CText(translateFN('Non sono state trovate istanze per il corso selezionato'));
    }
} else {
    $data = new CText(translateFN('Non sono state trovate istanze per il corso selezionato'));
}


$label = translateFN('Lista istanze del corso'). ' '.$course_title;
$help = translateFN('Da qui il provider admin può vedere la lista delle istanze del corso selezionato');

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'edit_switcher'=>$edit_profile_link->getHtml(),
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);