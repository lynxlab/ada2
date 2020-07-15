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
$variableToClearAR = array('node', 'layout', 'user', 'course');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout', 'course','course_instance')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = tutor!

include_once 'include/tutor_functions.inc.php';
include_once 'include/tutor.inc.php';

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
// default parameters for activity index are in configuration file
if (empty($npar)) {
    $npar = NOTE_PAR; // notes
}
if (!isset($hpar)) {
    $hpar = HIST_PAR; // history
}
if (!isset($mpar)) {
    $mpar = MSG_PAR; //messages
}
if (!isset($epar)) {
    $epar = EXE_PAR; // exercises
}
/*
 * retrieving student data
 *
 * we shall divide visits from exercises and notes...
 *
*/

$courseObj = read_course_from_DB($courseInstanceObj->id_corso);
if(AMA_DataHandler::isError($courseObj)) {
} else {
    $course_title = $courseObj->titolo;
    $start_date = AMA_DataHandler::ts_to_date($courseInstanceObj->data_inizio);
}

$studentObj = MultiPort::findUser($id_student);
if(AMA_DataHandler::isError($studentObj)) {
    header('Location: tutor.php');
    exit();
} else if ($studentObj instanceof ADAPractitioner) {
	/**
	 * @author giorgio 14/apr/2015
	 *
	 * If student is actually a tutor, build a new student
	 * object for history and evaluation purposes
	 */
	$studentObj = $studentObj->toStudent();
}
$student_name = $studentObj->getFullName();

$history = sprintf(translateFN('Cronologia dello studente %s, aggiornata al %s'), $student_name, $ymdhms);
// @author giorgio 16/mag/2013
// id e nome della classe
$history .= "<br/>";
$history .= translateFN("Classe").": <b>".$courseInstanceObj->getTitle()."</b> (".$courseInstanceObj->getId().")";

if (empty($mode)) {
    $mode = 'visits';
}

switch ($mode) {

    case "visits":
    default:

    // lettura dei dati dal database
        $studentObj->set_course_instance_for_history($courseInstanceObj->id);
        $user_historyObj = $studentObj->history;
        $visited_nodes_table = $user_historyObj->history_nodes_visited_FN();


        // Totali: nodi e  nodi visitati (necessita dati che vengono calcolati dalla
        // funzione in history_nodes_visited_FN()
        $history .= "<p>";
        $history .= $user_historyObj->history_summary_FN() ;
        $history .= "</p>";

        // Percentuale nodi visitati (necessita dati che vengono calcolati dalla
        // funzione in history_nodes_visited_FN() )
        $history .= "<p align=\"center\">";
        $history .= translateFN("Percentuale nodi visitati/totale: ") ;
        $nodes_percent = $user_historyObj->history_nodes_visitedpercent_FN()."%" ;
        $history .= "<b>". $nodes_percent ."</b>" ;
        $history .= "</p>";

        $history .= "<p align=\"center\">";
        $history .= "<img src=\"../browsing/include/graph_pies.inc.php?nodes_percent=".urlencode($nodes_percent)."\" border=0 align=center>";
        $history .= "</p>";


        // Tempo di visita nodi
        $history .= "<p align=\"center\">";
        $history .= translateFN("Tempo totale di visita dei nodi (in ore:minuti:secondi): ") ;
        $history .= "<b>". $user_historyObj->history_nodes_time_FN() ."</b><br>" ;
        // Media di visita nodi
        $history .= translateFN("Tempo medio di visita dei nodi (in ore:minuti:secondi): ") ;
        $history .= "<b>". $user_historyObj->history_nodes_average_FN()."</b>" ;
        $history .= "</p>";

        // Ultimi nodi visitati (10)
        $history .= "<p>";
        $history .= $user_historyObj->history_last_nodes_FN(10) ;
        $history .= "</p>";

        // Nodi visitati e numero di visite per ciascun nodo
        $history .= "<p>";
        $history .= $visited_nodes_table ;
        $history .= "</p>";

        break;

    case 'score':
        $studentObj->get_exercise_dataFN($sess_id_course_instance, $id_student);
        $st_exercise_dataAr =$studentObj->user_ex_historyAr;
        $st_score = 0;
        $st_exer_number = 0 ;
        if (is_array($st_exercise_dataAr)) {
            foreach ($st_exercise_dataAr as $exercise) {
                $st_score+= $exercise[4];
                $st_exer_number++;
            }
        }

        $studentObj->set_course_instance_for_history($sess_id_course_instance);

        $st_history_count = $studentObj->total_visited_nodesFN($id_student);
        $st_history_notes_count = $studentObj->total_visited_notesFN($id_student);

        $st_exercises =  $st_score . ' ' . translateFN('su') . ' ' . ($st_exer_number*100);
        $history .=  '<br />' .translateFN('Punteggio esercizi:') . '<strong>' . $st_exercises . '</strong>'
                 . '<br />' . translateFN('Nodi visitati:') . '<strong>' . $st_history_count . '</strong>'
                 . '<br />' . translateFN('Note visitate:') . '<strong>' . $st_history_notes_count . '</strong>';

        break;

    case "writings":

    // added notes in forum
        $sub_courses = $dh->get_subscription($id_student,$sess_id_course_instance);
        if ((!AMA_datahandler::isError($sub_courses)) && ($sub_courses['tipo'] == ADA_STATUS_SUBSCRIBED)) {
            $out_fields_ar = array('nome','titolo','id_istanza','data_creazione');
            $clause = "TIPO = ".ADA_NOTE_TYPE." AND ID_UTENTE = $id_student";
            $clause.=" AND ID_ISTANZA = ".$sess_id_course_instance;
            $nodes = $dh->find_course_nodes_list($out_fields_ar, $clause,$sess_id_course);
            $added_nodes_count = count($nodes);
            $added_notes = $added_nodes_count;
        } else {
            $added_notes = '-';
        }

        $history.=  '<br />' . translateFN('Note inviate:'). '<strong>'.$added_notes.'</strong><br />';

        // we should read messages from log tables....
        // messages

        $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
        $sort_field = 'data_ora desc';
        $msgs_ha = $mh->get_sent_messages(
                $id_student,
                ADA_MSG_SIMPLE,
                array('id_mittente', 'data_ora'),
                $sort_field
        );
        if (AMA_DataHandler::isError($msgs_ha)) {
            $user_message_count = '-';
        } else {
            $user_message_count =  count($msgs_ha);
        }
        $history.=  translateFN('Messaggi inviati:').'<strong>'.$user_message_count.'</strong><br />';
        break;
    case "summary":

    // activity index
    // added notes in forum
        $sub_courses = $dh->get_subscription($id_student,$sess_id_course_instance);
        if ((!AMA_datahandler::isError($sub_courses)) && ($sub_courses['tipo'] == ADA_STATUS_SUBSCRIBED)) {
            $out_fields_ar = array('nome','titolo','id_istanza','data_creazione');
            $clause = "TIPO = ".ADA_NOTE_TYPE." AND ID_UTENTE = $id_student";
            $clause.=" AND ID_ISTANZA = ".$sess_id_course_instance;
            $nodes = $dh->find_course_nodes_list($out_fields_ar, $clause,$sess_id_course);
            $added_nodes_count = count($nodes);
            $added_notes = $added_nodes_count;
        } else {
            $added_notes = '-';
        }

        // we should read messages from log tables....
        // messages

        $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
        $sort_field = 'data_ora desc';
        $msgs_ha = $mh->get_sent_messages($id_student,
                ADA_MSG_SIMPLE,
                array('id_mittente', 'data_ora'),
                $sort_field);
        if (AMA_DataHandler::isError($msgs_ha)) {
            $user_message_count = '-';
        } else {
            $user_message_count =  count($msgs_ha);
        }

        $studentObj->get_exercise_dataFN($sess_id_course_instance, $id_student);
        $st_exercise_dataAr = isset($userObj->user_ex_historyAr) ? $userObj->user_ex_historyAr : null;
        $st_score = 0;
        $st_exer_number = 0 ;
        if (is_array($st_exercise_dataAr)) {
            foreach ($st_exercise_dataAr as $exercise) {
                $st_score+= $exercise[4];
                $st_exer_number++;
            }
        }

        $st_history_count = $studentObj->total_visited_nodesFN($id_student);
        $st_history_notes_count = $studentObj->total_visited_notesFN($id_student);

        $history.='<p>';
        $index   =  ($added_notes * $npar) + ($st_history_count * $hpar)  + ($user_message_count * $mpar) + ($st_exer_number * $epar);
        $history.=   '<br />' . translateFN('Indice attività:') . '<strong>' . $index . '</strong>';
        $history.= '</p>';
        break;

} // end switch  mode

if (!isset($op)) $op = null;

switch ($op) {
    case 'export':

    	/**
		 *
		 * @author giorgio 15/mag/2013
		 *
		 * added code for PDF generation and export
		 *
    	 */

    	$allowableTags = '<b><i>';

    	$filename = date ("Ymd")."-".$courseInstanceObj->id."-" . $studentObj->getLastName() . "-" . $studentObj->getId() .".pdf" ;

    	$PDFdata['title']  = sprintf(translateFN('Cronologia dello studente %s, aggiornata al %s'), $student_name, $ymdhms);

    	$PDFdata['block1'] =  $user_historyObj->history_summary_FN();
    	// replace <br> with new line.
    	// note that \r\n MUST be double quoted, otherwise PhP won't recognize 'em as a <CR><LF> sequence!
    	$PDFdata['block1'] = preg_replace('/<br\\s*?\/??>/i', "\r\n", $PDFdata['block1']);
    	$PDFdata['block1'] = strip_tags($PDFdata['block1'],$allowableTags);
    	$PDFdata['block1'] = translateFN("Classe").": <b>".$courseInstanceObj->getTitle()."</b> (".$courseInstanceObj->getId().")\r\n".
    						 $PDFdata['block1'];

    	$PDFdata['block2'] = translateFN("Percentuale nodi visitati/totale: ") ."<b>". $nodes_percent  ."</b>" ;

    	$PDFdata['block3'] =
    				translateFN("Tempo totale di visita dei nodi (in ore:minuti): ") .
    				"<b>". $user_historyObj->history_nodes_time_FN() ."</b>\r\n" .
    				translateFN("Tempo medio di visita dei nodi (in minuti:secondi): ") .
    				"<b>". $user_historyObj->history_nodes_average_FN()."</b>" ;

    	// each element of the table array as a data and cols element holding
    	// holding datas and column orders and label respectively.
    	// Then, it has a title element containg the title of the table itself.

    	// begin table 0
    	$PDFdata['table'][0]['data'] = $user_historyObj->history_last_nodes_FN(10,false);
    	if (!AMA_DB::isError($PDFdata['table'][0]['data']) &&
    		is_array($PDFdata['table'][0]['data']) && count($PDFdata['table'][0]['data'])>0) {
	    	// add sequence number to each returned element
	    	foreach ($PDFdata['table'][0]['data'] as $num=>$row) $PDFdata['table'][0]['data'][$num]['num'] = $num+1;
	    	// prepare labels for header row and set columns order
	    	// first column is sequence number
	    	$PDFdata['table'][0]['cols'] = array ('num'=>'#');
	    	// then all the others as returned in data, we just need the keys so let's take row 0 only
	    	foreach ( $PDFdata['table'][0]['data'][0] as $key=>$val )
				if ($key!=='num') $PDFdata['table'][0]['cols'][$key] = translateFN($key);
			$PDFdata['table'][0]['title'] =  translateFN("Ultime ".count($PDFdata['table'][0]['data'])." visite");
    	} else unset($PDFdata['table'][0]);

    	// begin table 1
    	$PDFdata['table'][1]['data'] =  $user_historyObj->history_nodes_visited_FN(false);
    	if (!AMA_DB::isError($PDFdata['table'][1]['data']) &&
    		is_array($PDFdata['table'][1]['data']) && count($PDFdata['table'][1]['data'])>0) {
	    	// add sequence number to each returned element
	    	foreach ($PDFdata['table'][1]['data'] as $num=>$row) $PDFdata['table'][1]['data'][$num]['num'] = $num+1;
	    	// prepare labels for header row and set columns order
	    	// first column is sequence number
	    	$PDFdata['table'][1]['cols'] = array ('num'=>'#');
	    	// then all the others as returned in data, we just need the keys so let's take row 0 only
	    	foreach ( $PDFdata['table'][1]['data'][0] as $key=>$val )
	    		if ($key!=='num') $PDFdata['table'][1]['cols'][$key] = translateFN($key);
	    	$PDFdata['table'][1]['title'] =  translateFN("Nodi ordinati per numero di visite");
    	} else unset($PDFdata['table'][1]);

    	require_once ROOT_DIR.'/include/PdfClass.inc.php';

		$pdf = new PdfClass('',$PDFdata['title']);

		$pdf->addHeader($PDFdata['title'], ROOT_DIR.'/layout/'.$userObj->template_family.'/img/header-logo.png' )
		     ->addFooter( translateFN("Report")." ". translateFN("generato")." ". translateFN("il")." ". date ("d/m/Y")." ".
    					  translateFN("alle")." ".date ("H:i:s") );

    	/**
    	 * begin PDF body generation
    	 */

    	$pdf->ezText($PDFdata['block1'],$pdf->docFontSize);
    	$pdf->ezText($PDFdata['block2'],$pdf->docFontSize,array('justification'=>'center'));
    	$pdf->ezSetDy(-20);

    	$pdf->ezImage(HTTP_ROOT_DIR."/browsing/include/graph_pies.inc.php?nodes_percent=".urlencode($nodes_percent)
    			,5,200,'width');

    	$pdf->ezText($PDFdata['block3'],$pdf->docFontSize,array('justification'=>'center'));
    	$pdf->ezSetDy(-20);

		// tables output
		if (is_array($PDFdata['table'])) {
	    	foreach ( $PDFdata['table'] as $count=>$PDFTable )
	    	{
	    		$pdf->ezTable($PDFTable['data'], $PDFTable['cols'],
	    				$PDFTable['title'],
	    				array ('width'=>$pdf->ez['pageWidth'] - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'] ));
	    		if ($count < count($PDFdata['table'])-1  ) $pdf->ezSetDy(-20);
	    	}
		}

    	$pdf->saveAs($filename);

        /*
         * outputs the data of selected student as an excel file
         */
    	/**
    	 * @author giorgio 15/mag/2013
    	 * commented old code for generating rtf file
    	 */
//         header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
//         header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
//         header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
//         header("Cache-Control: post-check=0, pre-check=0", false);
//         header("Pragma: no-cache");                          // HTTP/1.0
//         //header("Content-Type: application/vnd.ms-excel");
//         header("Content-Type: application/rtf");
//         // header("Content-Length: ".filesize($name));
//         //   header("Content-Disposition: attachment; filename=student_".$id_student.".xls"); //????
//         header("Content-Disposition: attachment; filename=student_".$id_student.".rtf");
//         echo $history;
        // header ("Connection: close");

        exit();
} //end switch op


$banner = include ("$root_dir/include/banner.inc.php");
$home = "<a href=\"tutor.php\">".translateFN("home")."</a>";
$bookmark = "<a href=\"../browsing/bookmarks.php\">".translateFN("bookmarks")."</a>";
$chat_link = "<a href=\"$http_root_dir/comunica/ada_chat.php\" target=_blank>".translateFN("chat")."</a>";

$menu_07 = menu_detailsFN($id_student,$id_course_instance,$id_course);

$content_dataAr = array(
        'help' => isset($help) ? $help : '',
        'course_title'=> $course_title . ', ' . translateFN('iniziato il') . ' ' . $start_date,
        'user_name'=>$user_name,
        'user_type' => $user_type,
        'status' => $status,
        'banner'=> $banner,
        'home'=>$home,
        'bookmark'=>$bookmark,
        'last_visit'=>'',
        'student'=>$student_name,
        'chat_link'=>$chat_link,
        'history'=>$history,
        'menu_07'=>isset($menu_07) ? $menu_07 : '',
        'menu_08'=>isset($menu_08) ? $menu_08 : '',
        'messages'=>$user_messages->getHtml(),
        'agenda'=>$user_agenda->getHtml()
);

$menuOptions['id_course_instance'] = $id_course_instance;
$menuOptions['id_instance'] = $id_course_instance;
$menuOptions['id_student'] =$id_student;

ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);