<?php

/**
 * File add_instance.php
 *
 * The switcher can use this module to create a new course instance.
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
$self = whoami();
include_once 'include/switcher_functions.inc.php';
require_once ROOT_DIR . '/comunica/include/comunica_functions.inc.php';
require_once ROOT_DIR . '/comunica/include/ChatRoom.inc.php';
require_once ROOT_DIR . '/comunica/include/ChatDataHandler.inc.php';

/*
 * YOUR CODE HERE
 */

require_once ROOT_DIR . '/include/Forms/CourseInstanceForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new CourseInstanceForm();
    $form->fillWithPostData();
    if($form->isValid()) {
        $course_instanceAr = array(
            'data_inizio_previsto' => dt2tsFN($_POST['data_inizio_previsto']),
            'durata' => $_POST['durata'],
            'price' => $_POST['price'],
            'self_instruction' => $_POST['self_instruction'],
            'self_registration' => $_POST['self_registration'],
            'title' => $_POST['title'],
            'duration_subscription' => $_POST['duration_subscription'],
            'start_level_student' => $_POST['start_level_student'],
            'open_subscription' => $_POST['open_subscription'],
        	'duration_hours' => $_POST['duration_hours']
        );
        $result = $dh->course_instance_add($_POST['id_course'], $course_instanceAr);
        if(AMA_DataHandler::isError($result)) {
            $form = new CText(translateFN('Si è verificato un errore durante la creazione della nuova istanza'));
        }
        else {
            /*
             * Creazione della chat
             */
            $data_inizio_previsto = dt2tsFN($_POST['data_inizio_previsto']);
            $durata = $_POST['durata'];
            $data_fine = $dh->add_number_of_days($durata,isset($data_inizio) ? $data_inizio : null);
            $id_istanza_corso = $result;
            $chatroom_ha['id_chat_owner']= $userObj->id_user;
            $chatroom_ha['chat_title'] = $course_title; // $_POST['chat_title'];
//            $chatroom_ha['chat_title'] = translateFN('Chat di classe'); // $_POST['chat_title'];
            $chatroom_ha['chat_topic'] = translateFN('Chat di classe');
            $chatroom_ha['welcome_msg'] = translateFN('Benvenut* nella chat della tua classe');
            $chatroom_ha['max_users']= 99;
            $chatroom_ha['start_time']= $data_inizio_previsto;
            $chatroom_ha['end_time']= $data_fine;
            $chatroom_ha['id_course_instance']= $id_istanza_corso;

            // add chatroom_ha to the database
            $chatroom = Chatroom::add_chatroomFN($chatroom_ha);

            header('Location: list_instances.php?id_course='.$_POST['id_course']);
            exit();
        }
    } else {
        $form = new CText(translateFN('I dati inseriti nel form non sono validi'));
    }
} else {
    if($courseObj instanceof Course && $courseObj->isFull()) {
        $formData = array(
            'id_course' => $courseObj->getId(),
        	'duration_hours' => $courseObj->getDurationHours()
        );
        $course_title = $courseObj->getTitle();
        $form = new CourseInstanceForm();
        $form->fillWithArrayData($formData);
    } else {
        $form = new CText(translateFN('Corso non trovato'));
    }
}

$label = translateFN('Aggiunta di una classe (istanza) del corso:') . ' '. $course_title;
$help = translateFN('Da qui il provider admin può creare una istanza di un corso');
$error_div = CDOMElement::create('DIV', 'id:error_form');
$error_div->setAttribute('class', 'hide_error');
$error_div->addChild(new CText(translateFN("ATTENZIONE: Ci sono degli errori nel modulo!")));
$help .= $error_div->getHtml();

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $form->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);