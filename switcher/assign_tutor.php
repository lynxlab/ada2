<?php

/**
 * ASSIGN Tutor.
 *
 * @package
 * @author      Marco Benini
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
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
    AMA_TYPE_SWITCHER => array('layout', 'course', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/switcher_functions.inc.php';

require_once ROOT_DIR .'/comunica/include/ChatRoom.inc.php';
require_once ROOT_DIR .'/comunica/include/ChatDataHandler.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/TutorAssignmentForm.inc.php';
/*
 * Handle practitioner assignment
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'
        && isset($id_tutor_new) && !empty($id_tutor_new)) {

    $courseInstanceId = $_POST['id_course_instance'];
    $courseId = $_POST['id_course'];

    if ($id_tutor_old != 'no') {
        $result = $dh->course_instance_tutor_unsubscribe($courseInstanceId, $id_tutor_old);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore nel disassociare il practitioner dal client'));
        }
    }
    if ($id_tutor_new != "del") {
        $result = $dh->course_instance_tutor_subscribe($courseInstanceId, $id_tutor_new);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore durante assegnazione del practitioner al client'));
        } else {
               /*
                * For each course instance, a class chatroom with the same duration
                * is made available. Every time there is an update in the course instance
                * duration, this chatroom needs to be updated too.
                */
               $id_instance = $courseInstanceId;
/*
 *                $start_time = $start_date;
               $end_time = $dh->add_number_of_days($_POST['durata'],$start_time);
//               $end_time   = $course_instance_data_before_update['data_fine'];
*/

               $id_chatroom = ChatRoom::get_class_chatroom_for_instance($id_instance,'C');

               if(!AMA_DataHandler::isError($id_chatroom)) {
                 /*
                  * An existing chatroom with id class and type = C (chat classroom)
                  * already exists, so update this chatroom owner (= tutor id).
                  */
                 $chatroomObj = new Chatroom($id_chatroom);
                 $chatroom_data['id_chat_owner'] = $id_tutor_new;

                 $result = $chatroomObj->set_chatroomFN($chatroomObj->id_chatroom, $chatroom_data);

                 if (AMA_DataHandler::isError($result)){
                    // gestire l'errore
                  }
               }
        }
    }
    header('Location: list_instances.php?id_course=' . $courseId);
    exit();
} else {
    if ($courseInstanceObj instanceof Course_instance && $courseInstanceObj->isFull()) {
        $result = $dh->course_instance_tutor_get($courseInstanceObj->getId());
        if (AMA_DataHandler::isError($result)) {
            // FIXME: verificare che si venga redirezionati alla home page del'utente
            $errObj = new ADA_Error($result, translateFN('Errore in lettura tutor'));
        }

        if ($result === false) {
            $id_tutor_old = 'no';
        } else {
            $id_tutor_old = $result;
        }

        // array dei tutor
        $field_list_ar = array('nome', 'cognome');
        $tutors_ar = $dh->get_tutors_list($field_list_ar);
        if (AMA_DataHandler::isError($tutors_ar)) {
            $errObj = new ADA_Error($tutors_ar, translate('Errore in lettura dei tutor'));
        }


        $tutors = array();
        $ids_tutor = array();

        if ($id_tutor_old == 'no') {
            $tutors['no'] = translateFN('Nessun tutor');
        }

        foreach ($tutors_ar as $tutor) {
			$ids_tutor[] = $tutor[0];
			$nome = $tutor[1] . ' ' . $tutor[2];
			$link = CDOMElement::create('a');
			$link->setAttribute('id','tooltip'.$tutor[0]);
			$link->setAttribute('href','javascript:void(0);');
			$link->addChild(new CText($nome));
            $tutors[$tutor[0]] = $link->getHtml();
        }

		$tutor_monitoring = $dh->get_tutors_assigned_course_instance($ids_tutor);

		//create tooltips with tutor's assignments (html + javascript)
		$tooltips = '';
		$js = '<script type="text/javascript">';
		foreach($tutor_monitoring as $k=>$v) {
			$ul = CDOMElement::create('ul');
			if (!empty($v)) {
			foreach($v as $i=>$l) {
					$nome_corso = $l['titolo'].(!empty($l['title'])?' - '.$l['title']:'');
				$li = CDOMElement::create('li');
				$li->addChild(new CText($nome_corso));
				$ul->addChild($li);
			}
			}
			else {
				$nome_corso = translateFN('Nessun corso trovato');
				$li = CDOMElement::create('li');
				$li->addChild(new CText($nome_corso));
				$ul->addChild($li);
			}

			$tip = CDOMElement::create('div','id:tooltipContent'.$k);
			$tip->addChild(new CText(translateFN('Tutor assegnato ai seguenti corsi:<br />')));
			$tip->addChild($ul);
			$tooltips.=$tip->getHtml();
			$js.= 'new Tooltip("tooltip'.$k.'", "tooltipContent'.$k.'", {DOM_location: {parentId: "header"}, className: "tooltip", offset: {x:+15, y:0}, hook: {target:"rightMid", tip:"leftMid"}});'."\n";
		}
		$js.= '</script>';
			$tooltips.=$js;
		//end

        $data = new TutorAssignmentForm($tutors, $id_tutor_old);
        $data->fillWithArrayData(
                array(
                    'id_tutor_old' => $id_tutor_old,
                    'id_course_instance' => $courseInstanceObj->getId(),
                    'id_course' => $id_corso
                )
        );
    } else {
        $data = new CText(translateFN('Classe non trovata'));
    }
}

$title = translateFN('Assegnazione di un tutor alla classe');
$help = translateFN('Da qui il Provider Admin può assegnare un tutor ad una classe');
$status = translateFN('Assegnazione tutor');

$banner = include ROOT_DIR . '/include/banner.inc.php';

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

$content_dataAr = array(
    'data' => $data->getHtml() . $tooltips,
    'menu' => $menu,
    'banner' => $banner,
    'help' => $help,
    'status' => $status,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'edit_switcher'=>$edit_profile_link->getHtml(),
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
