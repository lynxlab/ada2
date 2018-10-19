<?php
/**
 * This module displays a report on the exercises done by the students.
 *
 * @package     Default
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
TutorHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */

if (empty($id_node)) {
    $mode='summary';
}

switch ($mode) {
    case 'zoom':
        $out_fields_ar = array('data_visita','id_utente_studente','punteggio');
        $clause ="id_nodo = '".$id_node."'";
        $visits_ar = $dh->_find_ex_history_list($out_fields_ar,$clause);
        if (AMA_DataHandler::isError($visits)) {
            $msg = $visits_ar->getMessage();
            print "$msg";
            //header("Location: $error?err_msg=$msg");
            //exit;
        }


        $exercise_dataHa = array();
        $count_visits = count($visits_ar);
        foreach ($visits_ar as $visit) {
            $student_id = $visit[2];
            // message count?
            /*
                  $mh = new MessageHandler;
                  $user_messages = $mh->get_messages($student_id, ADA_MSG_SIMPLE,array('id_mittente'));
                  $user_interaction =  count($user_messages);
            */
            $out_fields_ar = array('autore');
            $clause = "autore = $student_id";
            $course_id = $sess_id_course;
            $added_notes = $dh->find_course_nodes_list($out_fields_ar, $clause,$course_id);
            $user_interaction   = count($added_notes);
            $user = $dh->_get_user_info($student_id);
            $username = $user['username'];
            $exercise_dataHa[] = array(
                    translateFN('Data')=>AMA_DataHandler::ts_to_date($visit[1]),
                    translateFN('Studente')=>$username,
                    translateFN('Punteggio')=>$visit[3],
                    translateFN('Interazione')=>$user_interaction
                    // etc etc
            );
        }


        $tObj = new Table();
        $tObj->initTable('0','right','1','0','90%','','','','','1','0');
        // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
        $caption = translateFN("Dettaglio");
        $summary = translateFN("Dettaglio dell'esercizio").$id_node;
        $tObj->setTable($exercise_dataHa,$caption,$summary);
        $tabled_exercise_dataHa = $tObj->getTable();
        $tabled_exercise_dataHa= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $tabled_exercise_dataHa, 1); // replace first occurence of class

        break;

    case 'summary':
        $field_list_ar = array('id_nodo','data_visita');
        $clause = "";
        $dataHa = $dh->_find_ex_history_list($field_list_ar, $clause);

        if (AMA_DataHandler::isError($dataHa)) {
            $msg = $dataHa->getMessage();
            print $msg;
            //header("Location: $error?err_msg=$msg");
            //exit;
        }

        $total_visits = 0;
        $exercise_dataHa = array();
        foreach ($dataHa as $exercise) {
            $id_node = $exercise[1];
            $data =  $exercise[2];
            $row = array(
                    translateFN('Nodo')=>"<a href=\"tutor_report.php?mode=zoom&id_node=$id_node\">$id_node : $nome </a>",
                    translateFN('Data')=>AMA_DataHandler::ts_to_date($data)
            );
            array_push($exercise_dataHa,$row);
        }
        $tObj = new Table();
        $tObj->initTable('0','right','1','0','90%','','','','','1','0');
        // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
        $caption = translateFN("Esercizi eseguiti fino al $ymdhms");
        $summary = translateFN("Elenco degli esercizi eseguiti");
        $tObj->setTable($exercise_dataHa,$caption,$summary);
        $tabled_exercise_dataHa = $tObj->getTable();
        $tabled_exercise_dataHa= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $tabled_exercise_dataHa, 1); // replace first occurence of class
}

$banner = include ROOT_DIR.'/include/banner.inc.php';
$help = translateFN('Da qui il Tutor può visualizzare il report.');
$status = translateFN('Visualizzazione del report');

$title = translateFN('ADA - Report del tutor');
$content_dataAr = array(
    'banner' => $banner,
    'data' => $tabled_exercise_dataHa,
    'help' => $help,
    'status' => $status,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);