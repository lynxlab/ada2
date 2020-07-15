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
  AMA_TYPE_TUTOR => array('layout', 'course', 'course_instance')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self = 'default';

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
$id_course = $courseInstanceObj->id_corso;
$start_date = AMA_DataHandler::ts_to_date($courseInstanceObj->data_inizio, "%d/%m/%Y");
$history = '';
if ($id_course) {
    // get object course
    $courseObj = read_course_from_DB($id_course);
    if ((is_object($courseObj)) && (!AMA_dataHandler::isError($courseObj))) {
        $course_title = $courseObj->titolo; //title
        $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
    } else {
        $errObj = new ADA_error(translateFN("Corso non trovato"), translateFN("Impossibile proseguire."));
    }
}

$studentObj = read_user_from_DB($id_student);
if ((is_object($studentObj)) && (!AMA_dataHandler::isError($studentObj))) {
	if ($studentObj instanceof ADAPractitioner) {
		/**
		 * @author giorgio 14/apr/2015
		 *
		 * If student is actually a tutor, build a new student
		 * object for history and evaluation purposes
		 */
		$studentObj = $studentObj->toStudent();
	}
    $studentObj->set_course_instance_for_history($id_course_instance);
    $id_profile_student = $studentObj->tipo;
    $user_name_student = $studentObj->username;
    $student_name = $studentObj->nome . " " . $studentObj->cognome;
    $user_historyObj = $studentObj->history;
    $user_level = $studentObj->livello;
    $user_historyObj->setCourse($id_course);
} else {
    $errObj = new ADA_error(translateFN("Utente non trovato"), translateFN("Impossibile proseguire."));
}

if ($period != 'all') {
    // Nodi visitati negli ultimi n giorni. Periodo in giorni.
//     $history = '<p>' . translateFN('Periodo:') . ' ' . $period . ' ' . translateFN('giorno/i') . '<br>';
    //  $history .= translateFN('Nodi visitati negli ultimi $period giorni:') ;
    $history .= $user_historyObj->history_nodes_list_filtered_FN($period);
//     $history .= '</p>';
} else {
// Full history
//     $history = '<p>' . translateFN('Periodo:') . ' ' . translateFN('tutto') . '<br>';
    //  $history .= translateFN('Cronologia completa:') ;
    $history .= $user_historyObj->get_historyFN();
//     $history .= '</p>';
}
if (!isset($op)) $op = null;
switch ($op) {
    case 'export':
    	/**
    	 * @author giorgio 16/mag/2013
    	 *
    	 * handles pdf and xls export
    	 */

    	$allowed_export_types = array ('xls' , 'pdf');
    	if (!isset($type) || !in_array($type, $allowed_export_types)) $type = 'xls';

    	$filename = date ("Ymd")."-".$courseInstanceObj->id."-" . $studentObj->getLastName() . "-" . $studentObj->getId() ."_period_" .$period.".".$type;

    	if ($type === 'pdf')
    	{
    		$nodes_percent = $user_historyObj->history_nodes_visitedpercent_FN()."%" ;
    		$allowableTags = '<b><i>';

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

    		if ($period!='all')
    			$PDFdata['table'][0]['data'] = $user_historyObj->history_nodes_list_filtered_FN($period,false);
    		else
    			$PDFdata['table'][0]['data'] = $user_historyObj->get_historyFN(false);

    		if (!AMA_DB::isError($PDFdata['table'][0]['data']) &&
    				is_array($PDFdata['table'][0]['data']) && count($PDFdata['table'][0]['data'])>0) {

	    		// set table title
	    		$PDFdata['table'][0]['title'] =  $PDFdata['table'][0]['data']['caption'];
	    		unset ($PDFdata['table'][0]['data']['caption']);

	    		// add sequence number to each returned element
	    		foreach ($PDFdata['table'][0]['data'] as $num=>$row) $PDFdata['table'][0]['data'][$num]['num'] = $num+1;
	    		// prepare labels for header row and set columns order
	    		// first column is sequence number
	    		$PDFdata['table'][0]['cols'] = array ('num'=>'#');
	    		if (isset($PDFdata['table'][0]['data'][0]) && is_array($PDFdata['table'][0]['data'][0])) {
		    		// then all the others as returned in data, we just need the keys so let's take row 0 only
		    		foreach ( $PDFdata['table'][0]['data'][0] as $key=>$val )
		    			if ($key!=='num') $PDFdata['table'][0]['cols'][$key] = translateFN($key);

		    		// this time returned data contains html tags, let's strip'em down
		    		foreach ( $PDFdata['table'][0]['data'] as $num=>$rowElement )
		    		{
		    			foreach ($rowElement as $key=>$cellValue)
		    				$PDFdata['table'][0]['data'][$num][$key] = strip_tags($cellValue,$allowableTags);
		    		}
	    		}
    		} else unset($PDFdata['table'][0]);

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

    		if (is_array($PDFdata['table'])) {
	    		// tables output
	    		foreach ( $PDFdata['table'] as $count=>$PDFTable )
	    		{
	    			$pdf->ezTable($PDFTable['data'], $PDFTable['cols'],
	    					$PDFTable['title'],
	    					array ('width'=>$pdf->ez['pageWidth'] - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'] ));
	    			if ($count < count($PDFdata['table'])-1  ) $pdf->ezSetDy(-20);
	    		}
    		}
    		$pdf->saveAs($filename);
    	}
    	else
    	{
	        /*
	         * outputs the data of selected student as a file excel
	         */
	        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
	        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	        header("Cache-Control: post-check=0, pre-check=0", false);
	        header("Pragma: no-cache");                          // HTTP/1.0
	        header("Content-Type: application/vnd.ms-excel");
	        // header("Content-Length: ".filesize($name));
// 	        header("Content-Disposition: attachment; filename=student_" . $id_student . "_period_" . $period . ".xls");
	        header("Content-Disposition: attachment; filename=".$filename);
	        echo $history;
	        // header ("Connection: close");
    	}

        exit();
    default:
        break;
}
	$student_name = $studentObj->getFullName();

	$prehistory  = translateFN("Corsista").": ".$student_name."<br/>";
	$prehistory .= translateFN("Corso").": ".$course_title . ', ' . translateFN('iniziato il') . ' ' . $start_date."<br/>";

	$prehistory .= sprintf(translateFN('Cronologia dello studente %s, aggiornata al %s'), $student_name, $ymdhms);
	// @author giorgio 16/mag/2013
	// id e nome della classe
	$prehistory .= "<br/>";
	$prehistory .= translateFN("Classe").": <b>".$courseInstanceObj->getTitle()."</b> (".$courseInstanceObj->getId().")";

	// lettura dei dati dal database
	$studentObj->set_course_instance_for_history($courseInstanceObj->id);
	$user_historyObj = $studentObj->history;
	$visited_nodes_table = $user_historyObj->history_nodes_visited_FN();


	// Totali: nodi e  nodi visitati (necessita dati che vengono calcolati dalla
	// funzione in history_nodes_visited_FN()
	$prehistory .= "<p>";
	$prehistory .= $user_historyObj->history_summary_FN() ;
	$prehistory .= "</p>";

	// Percentuale nodi visitati (necessita dati che vengono calcolati dalla
	// funzione in history_nodes_visited_FN() )
	$prehistory .= "<p align=\"center\">";
	$prehistory .= translateFN("Percentuale nodi visitati/totale: ") ;
	$nodes_percent = $user_historyObj->history_nodes_visitedpercent_FN()."%" ;
	$prehistory .= "<b>". $nodes_percent ."</b>" ;
	$prehistory .= "</p>";

	$prehistory .= "<p align=\"center\">";
	$prehistory .= "<img src=\"../browsing/include/graph_pies.inc.php?nodes_percent=".urlencode($nodes_percent)."\" border=0 align=center>";
	$prehistory .= "</p>";


	// Tempo di visita nodi
	$prehistory .= "<p align=\"center\">";
	$prehistory .= translateFN("Tempo totale di visita dei nodi (in ore:minuti): ") ;
	$prehistory .= "<b data-seconds=".$user_historyObj->total_time.">". $user_historyObj->history_nodes_time_FN() ."</b><br>" ;
	// Media di visita nodi
	$prehistory .= translateFN("Tempo medio di visita dei nodi (in minuti:secondi): ") ;
	$prehistory .= "<b>". $user_historyObj->history_nodes_average_FN()."</b>" ;
	$prehistory .= "</p>";

	$history = $prehistory.$history;

$banner = include ROOT_DIR . '/include/banner.inc.php';

$content_dataAr = array(
    'course_title' => $course_title,
    'user_name' => $user_name,
    'student' => $student_name,
    'banner' => $banner,
    'level' => $user_level,
    'data' => menu_detailsFN($id_student, $id_course_instance, $id_course)
           . $history,
    'status'=>$status,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml()
);

$menuOptions['id_instance'] = $id_course_instance;
$menuOptions['id_course_instance'] = $id_course_instance;
$menuOptions['id_student'] =$id_student;
$menuOptions['id_course']=$id_course;
$menuOptions['period'] =$period;

ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);