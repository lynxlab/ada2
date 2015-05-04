<?php

/**
 * STUDENT EXERCISE HISTORY
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Valerio Riva <valerio.riva@gmail.com>
 * @copyright	        Copyright (c) 2009-2011, Lynx s.r.l.
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
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();

include_once 'include/browsing_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR . '/services/include/exercise_classes.inc.php';

$history = '';

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
        $history = $viewer->getExerciseHtml($exercise);
        $status = translateFN('Esercizio dello studente');
        break;
    case 'list':
    case 'default':
    // lettura dei dati dal database
    // Seleziona gli esercizi dello studente selezionato nel corso selezionato

        $userObj->get_exercise_dataFN($id_course_instance, $userObj->getId()) ;

        // Esercizi svolti e relativi punteggi
        $history .= '<p>';
        $history .= $userObj->history_ex_done_FN($userObj->id_user,AMA_TYPE_STUDENT,$id_course_instance) ;
        $history .= '</p>';
        $status = translateFN('Esercizi dello studente');

	break;
}
// CHAT, BANNER etc

$banner = include ("$root_dir/include/banner.inc.php");

// Costruzione del link per la chat.
// per la creazione della stanza prende solo la prima parola del corso (se piu' breve di 24 caratteri)
// e ci aggiunge l'id dell'istanza corso
$help = translateFN("Da qui lo studente può rivedere i propri esercizi.");

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


/*
 * Last access link
 */

if(isset($_SESSION['sess_id_course_instance'])){
    $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
else {
    $last_access=$userObj->get_last_accessFN(null,"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
if($last_access=='' || is_null($last_access)){
   $last_access='-';
}


$content_dataAr = array(
    'banner'=>$banner,
    'course_title'=>translateFN('Storico Esercizi').' > <a href="main_index.php">'.$course_title.'</a>',
    'path'=>$node_path,
    // 'class'=>$class . ' ' . translateFN('iniziata il') . ' ' . $start_date,
    'user_name'=>$user_name,
    'user_type'=>$user_type,
    'student'=>$userObj->getFullName(),
    'level'=>$userObj->livello,
    'edit_profile'=> $userObj->getEditProfilePage(),
    'data'=>$history,
    'user_level'=>$user_level,
    'last_visit' => $last_access,
    'help'=>$help,
    'status'=>$status,
    'messages'=>$user_messages->getHtml(),
    'agenda'=>$user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
