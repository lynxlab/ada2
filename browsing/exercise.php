<?php
/**
 * Exercise
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
        AMA_TYPE_VISITOR      => array('node','layout','course'),
        AMA_TYPE_STUDENT         => array('node','layout','tutor','course','course_instance'),
        AMA_TYPE_TUTOR => array('node','layout','course','course_instance'),
        AMA_TYPE_AUTHOR       => array('node','layout','course')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';

$self = whoami();

include_once 'include/browsing_functions.inc.php';

/*
 *
*/
include_once ROOT_DIR . '/services/include/exercise_classes.inc.php';

$id_node = $nodeObj->id;

//redirect to test module if necessary
if (MODULES_TEST && ADA_REDIRECT_TO_TEST && strpos($nodeObj->type,(string) constant('ADA_PERSONAL_EXERCISE_TYPE')) === 0) {
		NodeTest::checkAndRedirect($nodeObj);
}
if (!isset($op)) $op=null;
switch($op) {
    case 'answer':
        if (isset($useranswer)) {
            $exercise   = ExerciseDAO::getExercise($id_node);

            $correttore = ExerciseCorrectionFactory::create($exercise->getExerciseFamily());
            $correttore->rateStudentAnswer($exercise, $useranswer, $sess_id_user, $sess_id_course_instance);

           /*
            * salviamo l'esercizio svolto solo se l'utente che lo ha svolto
            * e' uno studente, altrimenti si tratta di un autore o di un tutor che ha
            * testato l'esercizio.
            */
            if ($id_profile == AMA_TYPE_STUDENT) {
				if (!ExerciseDAO::save($exercise)) {
					$errObj = new ADA_Error(NULL, translateFN('Errore nel salvataggio della risposta utente'));
				}

				// se l'esercizio appena corretto è un esercizio di sbarramento e lo studente lo ha superato,
				// aumenta di uno il livello dello studente
				if ($correttore->raiseUserLevel($exercise)) {
					$result = $dh->raise_student_level($sess_id_user, $sess_id_course_instance, 1);
					if (AMA_DataHandler::isError($result)) {
						$errObj = new ADA_Error($result, translateFN("Errore nell'aggiornamento dati utente"));
					}
					//$new_user_level = $user_level + 1;
					$new_user_level = $userObj->get_student_level($sess_id_user, $sess_id_course_instance);
					//$max_level = ADA_MAX_USER_LEVEL; // da config_install.inc.php
					$max_level = $dh->get_course_max_level($sess_id_course);
					if ($new_user_level >= $max_level) {
						// se è l'ultimo esercizio (ovvero se il livello dello studente è il massimo possibile)
						// e l'esercizio è di tipo sbarramento?
						// genera il messaggio da inviare allo switcher
						$tester = $userObj->getDefaultTester();
						$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
						$tester_info_Ar = $dh->get_tester_info_from_pointer($tester); // common?
						$tester_name = $tester_info_Ar[1];
						$switchers_Ar = $tester_dh->get_users_by_type(array(AMA_TYPE_SWITCHER));
						if (AMA_DataHandler::isError($switchers_Ar) || !is_array($switchers_Ar)) {
							// ??
						}
						else {
							$switcher_id = $switchers_Ar[0];
							//
							/* FIXME:
							 * only the firset switcher per provider !
							 */
							if ($switcher_id) {
								$switcher = $dh->get_switcher($switcher_id);
								if (!AMA_DataHandler::isError($switcher)) {
									// prepare message to send
									$message_ha['destinatari'] = $switcher['username'];
									$message_ha['titolo'] = translateFN("Completamento corso") . "<br>";

									//$message_ha['testo'] = $correttore->getMessageForTutor($user_name, $exercise);
									/* FIXME
									 * should be a function of ExerciseCorrectionFactory??
									 */
									$message_ha['testo'] = translateFN("Il corsista") . " $user_name " . translateFN("ha terminato il corso con id") . " " . $sess_id_course . "/" . $sess_id_course_instance;
									$message_ha['data_ora'] = "now";
									$message_ha['tipo'] = ADA_MSG_SIMPLE;
									$message_ha['priorita'] = 1;
									$message_ha['mittente'] = $user_name;
									$mh = new MessageHandler();
									$mh->send_message($message_ha);
								}
							}
						}

						// genera il messaggio da inviare al tutor
						// codice precedente
						$tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
						if (AMA_DataHandler::isError($tutor_id)) {
							//?
						}
						// only one tutor per class
						if ($tutor_id) {
							$tutor = $dh->get_tutor($tutor_id);
							if (!AMA_DataHandler::isError($tutor)) {
								// prepare message to send
								$message_ha['destinatari'] = $tutor['username'];
								$message_ha['titolo'] = translateFN("Esercizio svolto da ") . $user_name . "<br>";

								$message_ha['testo'] = $correttore->getMessageForTutor($user_name, $exercise);

								$message_ha['data_ora'] = "now";
								$message_ha['tipo'] = ADA_MSG_SIMPLE;
								$message_ha['priorita'] = 1;
								$message_ha['mittente'] = $user_name;
								$mh = new MessageHandler();
								$mh->send_message($message_ha);
							}
						}
					} // max level attained
				}
			}
            // genera il messaggio per lo studente
            // $dataHa['exercise'] = $correttore->getMessageForStudent($user_name, $exercise);
            $message = $correttore->getMessageForStudent($user_name, $exercise);
            $dataHa['exercise'] = $message->getHtml();



            // ottiene il prossimo esercizio da svolgere, se previsto.
            $next_exercise_id = ExerciseDAO::getNextExerciseId($exercise, $sess_id_user);
            if (AMA_DataHandler::isError($next_exercise_id)) {
                $errObj = new ADA_Error($next_exercise_id, translateFN('Errore nel caricamento del prossimo esercizio'));
            }
            else if ($next_exercise_id) {
                $dataHa['exercise'] .= "<a href=\"$http_root_dir/browsing/exercise.php?id_node=$next_exercise_id\">";
                $dataHa['exercise'] .= translateFN('Prossimo esercizio').'</a>';
            }
        }
        break;
    case 'view':
    default:
        $exercise = ExerciseDAO::getExercise($id_node);
        if ($user_level < $exercise->getExerciseLevel()) {
            $form = translateFN("Esercizio di livello superiore al tuo");
        } else {
            $viewer   = ExerciseViewerFactory::create($exercise->getExerciseFamily());
            $action = 'exercise.php';
            $form = $viewer->getViewingForm($userObj, $exercise, $sess_id_course_instance, $action);

            // vito 26 gennaio 2009
            if (($id = ExerciseDAO::getNextExerciseId($exercise, $sess_id_user)) != NULL) {
                $next_exercise_menu_link = CDOMElement::create('a');
                $next_exercise_menu_link->setAttribute('href', "$http_root_dir/browsing/exercise.php?id_node=$id");
                $next_exercise_menu_link->addCHild(new CText(translateFN('Prossimo esercizio')));
                $dataHa['go_back'] .= $next_exercise_menu_link->getHtml();
            }
        }
        $dataHa['exercise'] = $form;
        $node_title = $exercise->getTitle();
        $icon = CourseViewer::getCSSClassNameForExerciseType($exercise->getExerciseFamily());
        break;
}

/*
 * Actions menu
*/
if($id_profile == AMA_TYPE_AUTHOR) {
    /*
     * build onclick event for new menu.
    */

    $link   = HTTP_ROOT_DIR. '/services/edit_exercise.php?op=delete';
    $text   = addslashes(translateFN('Confermi cancellazione esercizio?'));
    $onclick="confirmCriticalOperationBeforeRedirect('$text','$link')";

}
else {
    $edit_exercise = new CText('');
}

/*
 * Output
 */
$content_dataAr = array(
        'path' => $nodeObj->findPathFN(),
        'user_name' => $user_uname,
        'user_type' => $user_type,
        'user_level' => $user_level,
        'visited' => '-',
        'icon' => isset($icon) ? $icon : '',
        'text' => isset($dataHa['exercise']) ? $dataHa['exercise'] : null,
        'onclick'=> $onclick,
        'title' => $nodeObj->name,
        'author' => $nodeObj->author['username'],
        'node_level' => 'livello nodo',
        'messages' => $user_messages->getHtml(),
        'agenda' => $user_agenda->getHtml(),
        //'course_title' => '',
        //'media' => 'media',
);

$menuOptions['id_node'] = $id_node;
ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);