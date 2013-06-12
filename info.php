<?php
/**
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout'),
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout'),
    AMA_TYPE_ADMIN => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';

$op = DataValidator::validate_string($_GET['op']);
$today_date = today_dateFN();

//$self = 'list_chatrooms'; // x template
//$self = whoami();


if($op !== false && $op == 'course_info') {
    $serviceId = DataValidator::is_uinteger($_GET['id']);
    if($serviceId !== false && $serviceId > 0) {

        $coursesAr = $common_dh->get_courses_for_service($serviceId);

        $thead_data = array(translateFN('data inizio previsto'), translateFN('data fine'),translateFN('crediti'), translateFN('azioni'));
        //$thead_data = array(translateFN('nome'), translateFN('data inizio previsto'), translateFN('durata'), translateFN('data fine'), translateFN('tutor'), translateFN('azioni'));
        $tbody_data = array();

        if(!AMA_Common_DataHandler::isError($coursesAr)) {

            $currentTesterId = 0;
            $currentTester = '';
            $tester_dh = null;

            foreach($coursesAr as $courseData) {

                $newTesterId = $courseData['id_tester'];
                if($newTesterId != $currentTesterId) {
                    $testerInfoAr = $common_dh->get_tester_info_from_id($newTesterId,'AMA_FETCH_ASSOC');
                    if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
                        $provider_name = $testerInfoAr[1];
                        $tester = $testerInfoAr[10];
                        // $tester = $testerInfoAr['puntatore'];
                        // get_tester_info_from_id usa $db->getRow quindi restituisce un array numerico
                        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
                        $currentTesterId = $newTesterId;
                        $course_dataHa = $tester_dh->get_course($courseId);
                        if (!AMA_DataHandler::isError($course_dataHa)) {
                            $credits =  $course_dataHa['crediti']; 
                            // supponiamo che tutti i corsi di un servizio (su tester diversi) abbiano lo stesso numero di crediti
                            // quindi prendiamo solo l'ultimo
                        } else {
                            $credits = 1;       // should be ADA_DEFAULT_COURSE_CREDITS
                        }    

                    }
                }

                $courseId = $courseData['id_corso'];
                //ISTANZE CORSO NON INIZIATE
                $instancesAr = $tester_dh->course_instance_subscribeable_get_list(
                        array('data_inizio_previsto', 'durata', 'data_fine', 'title'),
                        $courseId);

                if(is_array($instancesAr) && count($instancesAr) > 0) {
                    foreach($instancesAr as $instance) {
                        $instanceId = $instance[0];
                        $subscribe_link = BaseHtmlLib::link(
                                "info.php?op=subscribe&provider=$currentTesterId&course=$courseId&instance=$instanceId",
                                translateFN('Iscriviti'));
                        /*
                         * Da migliorare, spostare l'ottenimento dei dati necessari in un'unica query
                         * per ogni istanza corso (qualcosa che vada a sostituire course_instance_get_list solo in questo caso.
                         */
                         $tutorId = $tester_dh->course_instance_tutor_get($instanceId);
                         if(!AMA_DataHandler::isError($tutorId) && $tutorId !== false) {
                            $tutor_infoAr = $tester_dh->get_tutor($tutorId);
                            if(!AMA_DataHandler::isError($tutor_infoAr)) {
                                $tutorFullName = $tutor_infoAr['nome'] . ' ' . $tutor_infoAr['cognome'];
                            } else {
                                $tutorFullName = translateFN('Utente non trovato');
                            }
                         } else {
                             $tutorFullName = translateFN('Ancora non assegnato');
                         }

                        $duration = sprintf("%d giorni", $instance[2]);
                        $scheduled = AMA_DataHandler::ts_to_date($instance[1]);
                        $end_date =  AMA_DataHandler::ts_to_date($instance[3]);
                        $nome_instanza = $instance[4];
                        $course_infoAr = $tester_dh->get_course_info_for_course_instance($instanceId);
                        /*
                         * The first element of the array come from concat_ws
                         * the key of the array is like this [concat_ws(' ',u.nome,u.cognome)]
                         * the best way to get the value  is to access directly the value
                         */
                        list($key,$author_name) = each($course_infoAr);
                        /*
                         * The first element of the array come from concat_ws
                         */
                        $label = translateFN('Corso') .': '. $course_infoAr['nome'].' - '.$course_infoAr['titolo'] . ' - '
                                 . translateFN('Ente').': '.$provider_name; //.' - ' . translateFN('Autore'). ': '. $author_name;

//                        print_r($value);
//                        $output = array_slice($course_infoAr, 0, 1);
//                        print_r($output);

                        /*
                        $tbody_data[] = array(
							$nome_instanza,
                            $scheduled,
                            $duration,
                            $end_date,
                            $tutorFullName,
                            $subscribe_link
                        );
                         * 
                         */
                        $tbody_data[] = array(
                            $scheduled ,
                           // $duration, why?
                            $end_date,
                           // $tutorFullName, why?
                            $credits,
                            $subscribe_link
                        );
                    }
                }
            }
        }
        if(count($tbody_data) > 0) {
            $data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data);
        } else {
            $course_infoAr = $tester_dh->get_course($courseId);
//            print_r($course_infoAr);
            $label = translateFN('Corso') .': '. $course_infoAr['nome'].' - '.$course_infoAr['titolo'] . ' - '
                     . translateFN('Ente').': '.$provider_name;

            $data = new CText(translateFN('Al momento non sono presenti edizioni del corso a cui puoi iscriverti'));
        }
    } else {
        $data = new CText('Corso non trovato');
    }
} else if($op !== false && $op == 'subscribe') {
    $providerId = DataValidator::is_uinteger($_GET['provider']);
    $courseId = DataValidator::is_uinteger($_GET['course']);
    $instanceId = DataValidator::is_uinteger($_GET['instance']);
    $_SESSION['subscription_page'] = HTTP_ROOT_DIR . '/info.php?op=subscribe&provider='.$providerId.
                                     '&course='.$courseId.'&instance='.$instanceId;
    if($userObj instanceof ADAUser) {

        if($providerId !== false && $courseId !== false && $instanceId !== false) {
            $testerInfoAr = $common_dh->get_tester_info_from_id($providerId);
            if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
                $tester = $testerInfoAr[10];
                $provider_name = $testerInfoAr[1];

                $testersAr[0] = $tester; // it is a pointer (string)
                $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
                $course_instance_infoAR = $tester_dh->course_instance_get($instanceId);
                if (!AMA_DataHandler::isError($course_instance_infoAR)) {
                    $startStudentLevel = $course_instance_infoAR['start_level_student'];

                      // add user to tester DB
                    $id_tester_user = Multiport::setUser($userObj,$testersAr,$update_user_data = FALSE);
                    if ($id_tester_user !== FALSE ) {
                        $result = $tester_dh->course_instance_student_presubscribe_add($instanceId, $userObj->getId(),$startStudentLevel);
                        if(!AMA_DataHandler::isError($result) || $result->code == AMA_ERR_UNIQUE_KEY) {
                            $data = new CText(translateFN('La tua preiscrizione è stata effettuata con successo.'));
                            if ($course_instance_infoAR['price'] > 0) {
                                $args = '?provider='.$providerId.'&course='.$courseId.'&instance='.$instanceId;
                                header('Location: ' . HTTP_ROOT_DIR . '/browsing/student_course_instance_subscribe.php'.$args);
                                exit();
                            } else {
                                $result = $tester_dh->course_instance_student_subscribe($instanceId, $userObj->getId(),ADA_STATUS_SUBSCRIBED, $startStudentLevel);
                                if(!AMA_DataHandler::isError($result)) {
                                    $info_div = CDOMElement::create('DIV', 'id:info_div');
                                    $info_div->setAttribute('class', 'info_div');
                                    $label_text = CDOMElement::create('span','class:info');
                                    $label_text->addChild(new CText(translateFN('La tua iscrizione è stata effettuata con successo.')));
                                    $info_div->addChild($label_text);
                                    $homeUser = $userObj->getHomePage();
                                    $link_span = CDOMElement::create('span','class:info_link');
                                    $link_to_home = BaseHtmlLib::link($homeUser, translateFN('vai alla home per accedere.'));
                                    $link_span->addChild($link_to_home);
                                    $info_div->addChild($link_span);
                                    //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
                                    $data = $info_div;
                                }

                            }

//                        } else if($result->code == AMA_ERR_UNIQUE_KEY) {
//                            $data = new CText(translateFN('Risulti già preiscritto a questa edizione del corso'));
                        } else {
                            $data = new CText(translateFN('Si è verificato un errore'));
                        }
                    } else {
                        $data = new CText('Si è verificato un errore aggiungendo lo studente al provider');
                    }

                }

                $course_infoAr = $tester_dh->get_course_info_for_course_instance($instanceId);
                /*
                 * The first element of the array come from concat_ws
                 * the key of the array is like this [concat_ws(' ',u.nome,u.cognome)]
                 * the best way to get the value  is to access directly the value
                 */
                list($key,$author_name) = each($course_infoAr);
                /*
                 * The first element of the array come from concat_ws
                 */
                $label = translateFN('Corso') .': '. $course_infoAr['nome'].' - '.$course_infoAr['titolo'] . ' - '
                         . translateFN('Ente').': '.$provider_name; //.' - ' . translateFN('Autore'). ': '. $author_name;

            } else {
                $data = new CText('Si è verificato un errore');
            }
        }
    } else {
        header('Location: ' . HTTP_ROOT_DIR . '/login_required.php');
        exit();
    }
} else if (($op !== false && $op == 'undo_subscription')) {
    $providerId = DataValidator::is_uinteger($_GET['provider']);
    $courseId = DataValidator::is_uinteger($_GET['course']);
    $instanceId = DataValidator::is_uinteger($_GET['instance']);
    $studentId = DataValidator::is_uinteger($_GET['student']);
    $testerInfoAr = $common_dh->get_tester_info_from_id($providerId);
    if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
        $tester = $testerInfoAr[10];
        $provider_name = $testerInfoAr[1];

        $testersAr[0] = $tester; // it is a pointer (string)
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        $course_instance_infoAR = $tester_dh->course_instance_get($instanceId);
        if (!AMA_DataHandler::isError($course_instance_infoAR)) {

            $result = $tester_dh->course_instance_student_presubscribe_remove($instanceId, $userObj->getId());
            if(!AMA_DataHandler::isError($result)) {
                $info_div = CDOMElement::create('DIV', 'id:info_div');
                $info_div->setAttribute('class', 'info_div');
                $label_text = CDOMElement::create('span','class:info');
                $label_text->addChild(new CText(translateFN('La tua pre-iscrizione è stata annullata.')));
                $info_div->addChild($label_text);
                $homeUser = $userObj->getHomePage();
                $link_span = CDOMElement::create('span','class:info_link');
                $link_to_home = BaseHtmlLib::link($homeUser, translateFN('Torna alla home.'));
                $link_span->addChild($link_to_home);
                $info_div->addChild($link_span);
                $data = $info_div;
            } else {
                $info_div = CDOMElement::create('DIV', 'id:info_div');
                $info_div->setAttribute('class', 'info_div');
                $label_text = CDOMElement::create('span','class:info');
                $label_text->addChild(new CText(translateFN("C'è stato un problema annullando la tua pre-iscrizione.")));
                $info_div->addChild($label_text);
                $homeUser = $userObj->getHomePage();
                $link_span = CDOMElement::create('span','class:info_link');
                $link_to_home = BaseHtmlLib::link($homeUser, translateFN('Torna alla home.'));
                $link_span->addChild($link_to_home);
                $info_div->addChild($link_span);
                //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
                $data = $info_div;
            }
        }
    }
}
else {
    $publishedServices = $common_dh->get_published_courses();
    if(!AMA_Common_DataHandler::isError($publishedServices)) {
//        $thead_data = array('nome', 'descrizione', 'durata (giorni)', 'informazioni');
        $thead_data = array(translateFN('nome'), translateFN('descrizione'), translateFN('crediti'), translateFN('informazioni'));
        $tbody_data = array();

        foreach($publishedServices as $service) {
            // $serviceId = $service['id_servizio'];

               $serviceId = $service['id_servizio'];
               $coursesAr = $common_dh-> get_courses_for_service($serviceId); 
               if (!AMA_DataHandler::isError($coursesAr)) {
                    $currentTesterId = 0;
                    $currentTester = '';
                    $tester_dh = null;
                    foreach($coursesAr as $courseData) {
                        $courseId = $courseData['id_corso'];    
                        $newTesterId = $courseData['id_tester'];
                        if($newTesterId != $currentTesterId) { // stesso corso su altro tester ?
                            $testerInfoAr = $common_dh->get_tester_info_from_id($newTesterId); 
                            if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
                                $tester = $testerInfoAr[10];
                                $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester)); 
                                $currentTesterId = $newTesterId;
                                $course_dataHa = $tester_dh->get_course($courseId);
                                if (!AMA_DataHandler::isError($course_dataHa)) {
                                    $credits =  $course_dataHa['crediti']; 
                                    // supponiamo che tutti i corsi di un servizio (su tester diversi) abbiano lo stesso numero di crediti
                                    // quindi prendiamo solo l'ultimo
                                } else {
                                    $credits = 1;       // should be ADA_DEFAULT_COURSE_CREDITS
                                }    
                            }
                        }
                    }   
               } else {
                    $credits = 1;       // should be ADA_DEFAULT_COURSE_CREDITS
               }

            
            $more_info_link = BaseHtmlLib::link(
                    "info.php?op=course_info&id=$serviceId",
                    translateFN('More info'));

            $tbody_data[] = array(
                $service['nome'],
                $service['descrizione'],
                $credits,
//                $service['durata_servizio'],
                $more_info_link
            );
        }

        $data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data);
    } else {
        $data = new CText(translateFN('Non sono stati pubblicati corsi'));
    }
}
$title = translateFN('Corsi ai quali puoi iscriverti');
$help = '';
$homeUser = $userObj->getHomePage();
$link_to_home = BaseHtmlLib::link($homeUser, translateFN('Home'));

$content_dataAr = array(
    'course_title' => $title,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'home' => $link_to_home->getHtml()
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr);
