<?php
/**
 * Edit exercise
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node','layout');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
        AMA_TYPE_AUTHOR => array('layout','node')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

include_once 'include/author_functions.inc.php';

$self =  whoami();
/*
 * YOUR CODE HERE
*/
$id_node = $nodeObj->id;

include_once 'include/exercise_classes.inc.php';
include_once ROOT_DIR.'/browsing/include/CourseViewer.inc.php';

if (!isset($op)) {
    $op = 'do';
}

/*
if (!isset($status)) {
    if (isset($msg)) {
        $status = $msg;
    } else {
        $status = translateFN(" Modifiche esercizio");
    }
}
*/


switch($op) {
    case 'edit':
    /*
       * posso modificare l'esercizio solo se:
       * 1. nessuna istanza di corso attiva
       * 2. istanza del corso attiva, ma nessuno studente ha già svolto l'esercizio.
       * 
       * quindi deve:
       * 1. ottieni istanze corso attive (esiste già il metodo AMA?)
       * 2. se istanza corso attiva, verifica se esiste studente che ha svolto esercizio
       * in questa istanza corso.
       * se tutto ok, mostra il form per la modifica dell'esercizio,
       * altrimenti mostra un messaggio che spiega perche' non e' possibile modificare l'esercizio.
       * 
       * conviene implementare un nuovo metodo per ExerciseViewer che si occupa di mostrare il
       * form per la modifica dell'esercizio.
    */

    /*
       * 1. mostra il form contenente l'esercizio. Ciò che è modificabile ha un link modifica.
       * 2. al click su un link modifica, viene mostrato il form per la modifica dell'elemento
       *    selezionato.
       * 	se l'utente clicca su salva modifica, la modifica viene salvata e si ritorna a 1.
       * 	se l'utente clicca su annulla modifica, si torna a 1
    */  $status = translateFN('Modifiche esercizio');

        $edit_form_base_action = "$self.php?op=edit";

        /*
       * The exercise object is stored in a session variable, in order
       * to update it as soon as the author updates exercise data during editing.
       * 
       * If the exercise is not in the session variable, it means that it's the first
       * load of the editing form, so there's the need to check if the author can
       * modify this exercise. If this is possible, store the exercise in session and
       * present the exercise editing form. Otherwise a message will be shown.
        */
        $navigation_history = $_SESSION['sess_navigation_history'];

        $need_to_unset_session = strcmp($navigation_history->previousItem(), __FILE__);
        
        if ( !isset($_SESSION['sess_edit_exercise']['exercise'])  || $need_to_unset_session !== 0) {
            if ( $need_to_unset_session !== 0 ) {
                unset($_SESSION['sess_edit_exercise']);
            }
        }

        if (!isset($_SESSION['sess_edit_exercise']['exercise'])) {

            $exercise = ExerciseDAO::getExercise($id_node);

            if (!ExerciseDAO::canEditExercise($id_node)) {
                $dataHa['exercise'] = translateFN("L'esercizio non può essere modificato.");

                $icon = CourseViewer::getCSSClassNameForExerciseType($exercise->getExerciseFamily());
                break;
            }

            $_SESSION['sess_edit_exercise']['exercise'] = serialize($exercise);
        }
        else {
            $exercise = unserialize($_SESSION['sess_edit_exercise']['exercise']);
        }

        if(isset($edit)  /*&&!empty($edit)*/) {
            $field = $edit;
            $viewer = ExerciseViewerFactory::create($exercise->getExerciseFamily());
            if (isset($add) && !empty($add)) {
                $form = $viewer->getAddAnswerForm($edit_form_base_action, $exercise, $field);
            }
            else {
                $form = $viewer->getEditFieldForm($edit_form_base_action, $exercise, $field);
            }
            $dataHa['exercise'] = $form->getHtml();

            $icon = CourseViewer::getCSSClassNameForExerciseType($exercise->getExerciseFamily());

        }
        else if(isset($update) && !empty($update)) {
            $field = $update;

            $exercise->updateExercise($_POST);

            if ( !ExerciseDAO::save($exercise) ) {
                $errObj = new ADA_Error(NULL, translateFN("Errore nel salvataggio delle modifiche apportate all'esercizio"));
            }
            else {
                /*
             * Update the session variable too.
                */
                $_SESSION['sess_edit_exercise']['exercise'] = serialize($exercise);

                header("Location: $edit_form_base_action");
                exit();
            }
        }
        else if(isset($add_answer_to) && !empty($add_answer_to)) {

            $position = $_POST['position']-1;

            ExerciseDAO::addAnswer($exercise, $_POST);
            $id = $exercise->getId();
            unset($_SESSION['sess_edit_exercise']['exercise']);
            $exercise = NULL;
            $exercise = ExerciseDAO::getExercise($id);
            $_SESSION['sess_edit_exercise']['exercise'] = serialize($exercise);

            header("Location: $edit_form_base_action");
            exit();
        }
        else if(isset($delete) && !empty($delete)) {
            $node_id = $delete;

            $exercise->deleteDataItem($node_id);

            if ( !ExerciseDAO::save($exercise) ) {
                $errObj = new ADA_Error(NULL, translateFN("Errore nel salvataggio delle modifiche apportate all'esercizio"));
            }
            else {
                /*
             * Update the session variable too.
                */
                $_SESSION['sess_edit_exercise']['exercise'] = serialize($exercise);

                header("Location: $edit_form_base_action");
                exit();
            }

        }
        else if(isset($save) && !empty($save)) {
            unset($_SESSION['sess_edit_exercise']['exercise']);
//        if ( !ExerciseDAO::save($exercise) ) {
//          $errObj = new ADA_Error(NULL, translateFN("Errore nel salvataggio delle modifiche apportate all'esercizio"));
//       }
//        else {
            header('Location: ' . HTTP_ROOT_DIR . '/browsing/exercise.php?op=view');
            exit();
//        }  
        }
        else { //edit form base action
            $viewer = ExerciseViewerFactory::create($exercise->getExerciseFamily());

            $form = $viewer->getEditForm($edit_form_base_action, $exercise);

            $dataHa['exercise'] = $form->getHtml();
            $icon = CourseViewer::getCSSClassNameForExerciseType($exercise->getExerciseFamily());
        }

        break;

    case 'delete':
    /*
       * posso cancellare l'esercizio solo se:
       * 1. nessuna istanza di corso attiva
       * 2. istanza del corso attiva, ma nessuno studente ha già svolto l'esercizio.
       * 
       * quindi deve:
       * 1. ottieni istanze corso attive (esiste già il metodo AMA?)
       * 2. se istanza corso attiva, verifica se esiste studente che ha svolto esercizio
       * in questa istanza corso.
       * se tutto ok, cancella l'esercizio,
       * altrimenti mostra un messaggio che spiega perche' non e' possibile cancellare l'esercizio.
       * 
    */
        if (!ExerciseDAO::canEditExercise($id_node)) {
            $dataHa['exercise'] = translateFN("L'esercizio non può essere eliminato.");
            break;
        }
        $result = ExerciseDAO::delete($id_node);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN("Errore durante la cancellazione dell'esercizio"));
        }
        $dataHa['exercise'] = translateFN("L'esercizio &egrave; stato cancellato correttamente");
        break;
}

//$dataHa['go_back'].= "<BR><a href=\"$http_root_dir/browsing/exercise.php?id_node=$id_next_exercise\">".translateFN("Prossimo esercizio")."</a>";

$content_dataAr = array(
        'banner'=> isset($banner) ? $banner : '',
        'status'=>$status,
        'course_title'=>isset($course_title) ? $course_title : '',
        'user_name'=>$user_name,
        'user_type'=>$user_type,
        'user_level'=>$user_level,
        'author'=>isset($node_author) ? $node_author : '',
        'node_level'=>isset($node_level) ? $node_level : '',
        'visited'=>isset($visited) ? $visited : '',
        'path'=>$node_path,
// 'index'=>$node_index,
// 'link'=>$data['link'],
        'title'=>$node_title,
        'form'=>$dataHa['exercise'],
        'media'=>isset($dataHa['media']) ? $dataHa['media']: '',
//                   'edit_exercise'=>$edit_exercise_html,
        'messages'=>$user_messages->getHtml(),
        'agenda'=>$user_agenda->getHtml(),
        'chat_users'=>isset($online_users) ? $online_users : '',
        'icon' => $icon

);
// FIXME: non dovrebbe essere necessario aggiungere questa riga!
$layout_dataAr['node_type'] = '';
ARE::render($layout_dataAr, $content_dataAr);