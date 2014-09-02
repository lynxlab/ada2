<?php

/**
 * TUTOR.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
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
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();

include_once 'include/tutor_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once 'include/tutor.inc.php';
include_once ROOT_DIR . '/services/include/exercise_classes.inc.php';

$history = '';

$studentObj = read_user_from_DB($id_student);
if ((is_object($studentObj)) && (!AMA_dataHandler::isError($studentObj))) {
    $id_profile_student = $studentObj->getType();
    $student_type = $studentObj->convertUserTypeFN($id_profile_student);
    $user_name_student =  $studentObj->getUserName();
    $student_name = $studentObj->getFullName();
    $student_level = $studentObj->livello;
}  else {
    $errObj = new ADA_error(translateFN('Utente non trovato'),translateFN('Impossibile proseguire.'));
}

//if (isset($button)) {
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    //$op = 'default';

    if ( !isset($_SESSION['exercise_object']) ) {
        //print("errore");
        $errObj = new ADA_Error(NULL, translateFN('Esercizio non in sessione'));
    }

    $exercise = unserialize($_SESSION['exercise_object']);

    if ( ! ($exercise instanceof ADA_Esercizio) ) {
        $errObj = new ADA_Error(NULL, translateFN("L'oggetto in sessione non è un esercizio ADA"));
    }

    if ( isset($ripetibile) ) {
        $exercise->setRepeatable(true);
    }

    if ( isset($punteggio) ) {
        $exercise->setRating($punteggio);
    }

    $exercise->setTutorComment($comment);

    if ( !ExerciseDAO::save($exercise) ) {
        //print("Errore nel salvataggio dell'esercizio<BR>");
        $errObj = new ADA_Error(NULL, translateFN("Errore nel salvataggio dell'esercizio"));
    }

    unset($_SESSION['exercise_object']);

    if ( isset($messaggio) ) {
        $studentObj = read_user_from_DB($exercise->getStudentId());
        // controllo errore
        $subject = translateFN('Esercizio: ') . $exercise->getTitle() . "\n";

        //if ($exe_type == 4) $testo .= "\n" .$correzione;
        /*
         * I problemi potrebbero verificarsi qui.
         */
        //$res = spedisci_messaggioFN($comment,$subject,$studentObj->username, $user_name);
        $message_ha = array(
            'destinatari' => $studentObj->getUserName(),
            'data_ora' => 'now',
            'tipo' => ADA_MSG_SIMPLE,
            'mittente' => $user_uname,
            'testo' => $comment,
            'titolo' => $subject,
            'priorita' => 2
        );
        $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
        $result = $mh->send_message($message_ha);
        if(AMA_DataHandler::isError($result)) {
            // GESTIRE ERRORE
        }

    }
    $history = 'fine della correzione<br />';

    header("Location: tutor_exercise.php?op=list&id_student=$student_id&id_instance=$course_instance");
    exit();
}

if (!isset($op))
    $op = 'default';

switch ($op) {
    case 'exe':

        $user_answer = $dh->get_ex_history_info($id_exe);
        if ( AMA_DataHandler::isError($user_answer) ) {
            //print("errore");
            $errObj = new ADA_Error($user_answer, translateFN("Errore nell'ottenimento della risposta utente"));
        }

        $node            = $user_answer['node_id'];
        //$student_id      = $user_answer['student_id'];
        //$course_instance = $user_answer['course_id'];
        $id_student      = $user_answer['student_id'];
        $id_course_instance = $user_answer['course_id'];

        $exercise = ExerciseDAO::getExercise($node, $id_exe);

        $_SESSION['exercise_object'] = serialize($exercise);

        if ( AMA_DataHandler::isError($exercise) ) {
            //print("errore");
            $errObj = new ADA_Error($exercise, translateFN("Errore nella lettura dell'esercizio"));
        }
        $viewer  = ExerciseViewerFactory::create($exercise->getExerciseFamily());
        $history = $viewer->getTutorForm("$self.php",$exercise);

        $menu_03 = "<a href=" .  $http_root_dir . "/tutor/tutor_exercise.php?op=list&id_student=" . $id_student;
        $menu_03 .= "&id_instance=" . $id_course_instance .">";
        $menu_03 .= translateFN('Elenco esercizi') . "</a>";
        $status = translateFN('Esercizio dello studente');
        break;
    case 'list':
    case 'default':
    // lettura dei dati dal database
    // Seleziona gli esercizi dello studente selezionato nel corso selezionato

        $studentObj->get_exercise_dataFN($id_course_instance, $id_student) ;

        // Esercizi svolti e relativi punteggi
        $history .= '<p>';
        $history .= $studentObj->history_ex_done_FN($id_student,null,$id_course_instance) ;
        $history .= '</p>';
        $status = translateFN('Esercizi dello studente');
        $layout_dataAr['JS_filename'] = array(
        		JQUERY,
        		JQUERY_DATATABLE,
        		JQUERY_DATATABLE_DATE,
        		JQUERY_NO_CONFLICT
        );
        $layout_dataAr['CSS_filename']= array(
        		JQUERY_DATATABLE_CSS
        );
        $optionsAr['onload_func'] = 'dataTablesExec()';        

}
// CHAT, BANNER etc

$banner = include ("$root_dir/include/banner.inc.php");

// Costruzione del link per la chat.
// per la creazione della stanza prende solo la prima parola del corso (se piu' breve di 24 caratteri)
// e ci aggiunge l'id dell'istanza corso
$help = translateFN("Da qui il tutor del corso può vedere e correggere gli esercizi degli studenti. Può anche inviare un commento e/o decidere di far ripetere l'esercizio allo studente.");

$menu_01 = "<a href=" .  HTTP_ROOT_DIR . "/tutor/tutor.php?op=zoom_student&id_student=" . $id_student;
$menu_01 .= "&id_instance=" . $id_course_instance .">";
$menu_01 .= translateFN('Scheda corsista') . '</a>';

$menu_02 = " <a href=" .  HTTP_ROOT_DIR . "/tutor/tutor.php?op=student&id_instance=" . $id_course_instance;
$menu_02.= ">" . translateFN('Elenco studenti') . '</a>';

if (!isset($menu_03)) {
    $menu_03 = '';
}
if (!isset($status)) {
    $status = '';
}

$courseInstanceObj = new Course_instance($id_course_instance);
$courseObj = new Course($courseInstanceObj->id_corso);
$course_title = $courseObj->titolo;
//show course istance name if isn't empty - valerio
if (!empty($courseInstanceObj->title)) {
	$course_title .= ' - '.$courseInstanceObj->title;
}

if (!is_object($nodeObj)) {
	$nodeObj = read_node_from_DB($node);
}
if (!ADA_Error::isError($nodeObj) AND isset($courseObj->id)) {

	$_SESSION['sess_id_course'] = $courseObj->id;
	$node_path = $nodeObj->findPathFN();
}
$back_link= "<a href='".$_SERVER['HTTP_REFERER']."' class='backLink' title='".translateFN("Torna")."'>".translateFN("Torna")."</a>";
$content_dataAr = array(
    'banner'=>$banner,
    'course_title'=>translateFN('Modulo tutor').' > <a href="'.HTTP_ROOT_DIR.'/browsing/main_index.php">'.$course_title.'</a>',
    'back_link'=>$back_link,
    'path'=>$node_path,
    'class'=>$class . ' ' . translateFN('iniziata il') . ' ' . $start_date,
    'user_name'=>$user_name,
    'user_type'=>$user_type,
    'student'=>$student_name,
    'level'=>$student_level,
    'data'=>$history,
    'menu_01'=>$menu_01,
    'menu_02'=>$menu_02,
    'menu_03'=>$menu_03,
    'menu_04'=>'',//$chat_link,
    'help'=>$help,
    'status'=>$status,
    'messages'=>$user_messages->getHtml(),
    'agenda'=>$user_agenda->getHtml()
);

$menuOptions['id_instance'] = $id_course_instance;
$menuOptions['id_student'] =$id_student;

if(!isset($optionsAr)) {
    ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
} else {
//    print_r($optionsAr);
    ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr,$menuOptions);
}
