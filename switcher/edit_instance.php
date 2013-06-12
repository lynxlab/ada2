<?php

/**
 * File edit_instance.php
 *
 * The switcher can use this module to update the informations about an existing
 * course instance.
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
    AMA_TYPE_SWITCHER => array('layout','course', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
include_once("$root_dir/comunica/include/ChatRoom.inc.php");


/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/CourseInstanceForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $data = new CText(translateFN('Corso non trovato'));
    } else if(!($courseInstanceObj instanceof Course_instance) || !$courseInstanceObj->isFull()) {
        $data = new CText(translateFN('Classe non trovata'));
    } else {
        $form = new CourseInstanceForm();
        $form->fillWithPostData();
        if($form->isValid()) {

            if($_POST['started'] == 0) {
                $start_date = 0;
            } elseif($courseInstanceObj->isStarted()) {
                $start_date = dt2tsFN($courseInstanceObj->getStartDate());
            } else {
                $start_date = time();
            }           
            $course_instanceAr = array(
                'data_inizio' => $start_date,
                'data_inizio_previsto' => dt2tsFN($_POST['data_inizio_previsto']),
                'durata' => $_POST['durata'],
                'price' => $_POST['price'],
                'self_instruction' => $_POST['self_instruction'],
                'self_registration' => $_POST['self_registration'],
                'title' => $_POST['title'],
                'duration_subscription' => $_POST['duration_subscription'],
                'start_level_student' => $_POST['start_level_student'],
                'open_subscription' => $_POST['open_subscription']
            );
            $result = $dh->course_instance_set($_POST['id_course_instance'], $course_instanceAr);
            if(AMA_DataHandler::isError($result)) {
                $data = new CText(translateFN("Si sono verificati degli errori durante l'aggiornamento") . '(1)');
            } else {



               /*
                * For each course instance, a class chatroom with the same duration
                * is made available. Every time there is an update in the course instance
                * duration, this chatroom needs to be updated too.
                */
               $id_instance = $_POST['id_course_instance'];
               $start_time = $start_date;
               $end_time = $dh->add_number_of_days($_POST['durata'],$start_time);
//               $end_time   = $course_instance_data_before_update['data_fine'];
//               $id_chatroom = ChatRoom::get_class_chatroom_with_durationFN($id_instance,$start_time,$end_time);
               $id_chatroom = ChatRoom::get_class_chatroom_for_instance($id_instance,'C');

               if(AMA_DataHandler::isError($id_chatroom)) {

                 if($id_chatroom->code == AMA_ERR_NOT_FOUND) {
                   /*
                    * if a class chatroom with the same duration of the course instance does not exist,
                    * create it.
                    */
                    $id_course = $dh->get_course_id_for_course_instance($id_instance);
                    if (AMA_DataHandler::isError($id_course)){
                      // gestire l'errore
                    }

                    $course_data = $dh->get_course($id_course);
                    if (AMA_DataHandler::isError($course_data)){
                      // gestire l'errore
                    }

                    $id_tutor = $dh->course_instance_tutor_get($id_instance);
                    if (!AMA_DataHandler::isError($id_tutor)) {
                        $chatroom_ha['id_chat_owner'] = $id_tutor;
                    } else {
                        $chatroom_ha['id_chat_owner'] = $sess_id_user;
                    }


                    $chatroom_ha = array(
                      'chat_title'    => $course_data['titolo'],
                      'chat_topic'    => translateFN('Discussione sui contenuti del corso'),
                      'start_time'    => $start_time,
                      'end_time'      => $end_time,
                      'max_utenti'    => '999',
                      'id_course_instance' => $id_instance
                    );

                    $result = ChatRoom::add_chatroomFN($chatroom_ha);
                    if (AMA_DataHandler::isError($result)){
                      // gestire l'errore
                    }

                 }
                 else {
                   // e' un errore, gestire
                 }
               }
               else {
                 /*
                  * An existing chatroom with duration == class duration
                  * already exists, so update this chatroom start and end time.
                  */
                 $chatroomObj = new Chatroom($id_chatroom);
                 $id_tutor = $dh->course_instance_tutor_get($id_instance);
                 if (!AMA_DataHandler::isError($id_tutor)) {
                        $chatroom_data['id_chat_owner'] = $id_tutor;
                 } else {
                        $chatroom_data['id_chat_owner'] = $sess_id_user;
                 }
                 $chatroom_data = array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'max_utenti'    => '999'
                 );

                 $result = $chatroomObj->set_chatroomFN($chatroomObj->id_chatroom, $chatroom_data);

                 if (AMA_DataHandler::isError($result)){
                    // gestire l'errore
                  }
               }


                header('Location: list_instances.php?id_course=' . $courseObj->getId());
                exit();
            }
        } else {            
            $data = new CText(translateFN('I dati inseriti nel form non sono validi'));
        }
    }
} else {
    if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $data = new CText(translateFN('Corso non trovato'));
    } else if(!($courseInstanceObj instanceof Course_instance) || !$courseInstanceObj->isFull()) {
        $data = new CText(translateFN('Classe non trovata'));
    } else {
        $formData = array(
            'id_course' => $courseObj->getId(),
            'id_course_instance' => $courseInstanceObj->getId(),
            'data_inizio_previsto' => $courseInstanceObj->getScheduledStartDate(),
            'durata' => $courseInstanceObj->getDuration(),
            'started' => $courseInstanceObj->isStarted() ? 1 : 0,
            'price' => $courseInstanceObj->getPrice(),
            'self_instruction' => $courseInstanceObj->getSelfInstruction() ? 1 : 0,
            'self_registration' => $courseInstanceObj->getSelfRegistration() ? 1 : 0,
            'title' => $courseInstanceObj->getTitle(),
            'duration_subscription' => $courseInstanceObj->getDurationSubscription(),
            'start_level_student' => $courseInstanceObj->getStartLevelStudent(),
            'open_subscription' => $courseInstanceObj->getOpenSubscription() ? 1 : 0
        );
        $data = new CourseInstanceForm();
        $data->fillWithArrayData($formData);
    }
}
    $help = translateFN('Da qui il provider admin può modificare una istanza corso esistente');
    $error_div = CDOMElement::create('DIV', 'id:error_form');
    $error_div->setAttribute('class', 'hide_error');
    $error_div->addChild(new CText(translateFN("ATTENZIONE: Ci sono degli errori nel modulo!")));
    $help .= $error_div->getHtml();

$label = translateFN('Modifica istanza corso');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);