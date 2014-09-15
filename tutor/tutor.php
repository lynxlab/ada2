<?php
/**
 * TUTOR.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
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

include_once 'include/'.$self.'_functions.inc.php';
/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
include_once 'include/tutor.inc.php';
include_once ROOT_DIR.'/config/config_class_report.inc.php';

if (!isset($_GET['mode'])) {
  $mode = 'load';
} else {
  $mode = $_GET['mode'];
}

switch ($op) {
    case 'student_level':
        $studenti_ar = array($id_student);
        $info_course = $dh->get_course($id_course);
        if (AMA_DataHandler::isError($info_course)) {

        } else {
            $updated = $dh->set_student_level($id_instance, $studenti_ar, $level);
            if(AMA_DataHandler::isError($updated)) {
               // GESTIRE ERRORE.
            } else {
               header('Location: tutor.php?op=student&id_instance='.$id_instance.'&id_course='.$id_course.'&mode=update');
               exit();
            }
        }
        break;
//    case 'student_level':        // Update the user Level
//        $studenti_ar = array();
//        $studenti_ar[] = $id_student;
//
//        $info_course = $dh->get_course($id_course); // Get title course
//        if  (AMA_DataHandler::isError($info_course)) {
//            $msg = $info_course->getMessage();
//        } else {
//
//            $online_users_listing_mode = 2;
//            $online_users = User::get_online_usersFN($id_course_instance,$online_users_listing_mode);
//
//            $course_title = $info_course['titolo'];
//
//            $chat_link = "<a href=\"$http_root_dir/comunica/adaChat.php\" target=_blank>".translateFN("chat")."</a>";
////
//
//            $updated = $dh->set_student_level($id_instance, $studenti_ar, $level);
//
//            $courses_student = get_student_coursesFN($id_instance, $id_course);
//            $data['dati'] = $courses_student;
//            $data['menu_01'] = "<a href=" . $http_root_dir . "/tutor/tutor.php>" . translateFN("Lista dei corsi") . "</a>";
//            $data['course_title']=$course_title;
//            $data['menu_02'] = $chat_link;
//            $data['status'] = translateFN("modificato il livello studente");
//            $data['chat_users']=$online_users;
//            $data['help'] = translateFN("Da qui il Tutor pu&ograve; modificare il livello dello studente,
//                                             in modo da permettere un accesso filtrato ai nodi,
//                                             ");
//        }
//        break;
//
    case 'student':
    case 'class_report': // Show the students subscribed in selected course and a report
        if(!isset($id_course)) {
            $id_course = $dh->get_course_id_for_course_instance($id_instance);
            if(AMA_DataHandler::isError($id_course)) {
                $id_course = 0;
            }
        }
        if ($mode=='update') {
            $courses_student = get_student_coursesFN($id_instance,$id_course,$order);
        } else {
            // load
            $courses_student = get_student_courses_from_dbFN($id_course, $id_instance);
        }
        $info_course = $dh->get_course($id_course); // Get title course
        if  (AMA_DataHandler::isError($info_course)) {
            $msg = $info_course->getMessage();
            $data = $msg;
        } else {
            if (isset($sess_id_course_instance) && !empty($sess_id_course_instance)) {
                $id_chatroom = $sess_id_course_instance;
            }
            else if (isset($id_instance) && !empty($id_instance)) {
                $id_chatroom = $id_instance;
            }

            $course_title = $info_course['titolo'];

            $sess_id_course_instance = $id_instance;

            $sess_id_course = $id_course;

            $chat_link = "<a href=\"$http_root_dir/comunica/chat.php\" target=_blank>".translateFN('chat di classe') .'</a>';

            $data = $courses_student;
            
            $help = translateFN("Da qui il Tutor può consultare il report della classe; il report può essere ordinato in base a una qualsiasi delle colonne.");
            $help .= '<br />' . translateFN("Cliccando sui dati si accede al dettaglio.")
                  . '<br />';

        }
        break;

    case 'student_notes':   // nodi inseriti dallo studente
    case 'student_notes_export':

        $student_dataHa = $dh->_get_user_info($id_student);
        $studente_username = $student_dataHa['username'];
//          if (isset($id_course)){    // un corso (e un'istanza...) alla volta ?
        $sub_course_dataHa = array();
        $today_date = $dh->date_to_ts("now");
        $clause = "data_inizio <= '$today_date' AND data_inizio != '0'";
        $field_ar = array('id_corso','data_inizio','data_inizio_previsto');
        $all_instance = $dh->course_instance_find_list($field_ar,$clause);
        if (is_array($all_instance)) {
            $added_nodesHa = array();
            foreach ($all_instance as $one_instance) {
                //mydebug(__LINE__,__FILE__,$one_instance);
                $id_course_instance = $one_instance[0];
                //check on tutor:
                //           $tutorId = $dh->course_instance_tutor_get($id_course_instance);
                //           if (($tutorId == $sess_id_user)  AND ($id_course_instance == $sess_id_course_instance))
                // warning: 1 tutor per class ! ELSE: $tutored_instancesAr = $dh->course_tutor_instance_get($sess_id_user); etc
                // check only on course_instance
                if  ($id_course_instance == $id_instance) {
                    $id_course = $one_instance[1];
                    $data_inizio = $one_instance[2];
                    $data_previsto = $one_instance[3];
                    $sub_courses = $dh->get_subscription($id_student, $id_instance);
                    //mydebug(__LINE__,__FILE__,$sub_courses);
                    if ($sub_courses['tipo'] == 2) {
                        $out_fields_ar = array('nome','titolo','id_istanza','data_creazione','testo');
                        $clause = "tipo = '2' AND id_utente = '$id_student'";
                        $nodes = $dh->find_course_nodes_list($out_fields_ar, $clause,$id_course);
                        $course = $dh->get_course($id_course);
                        $course_title = $course['titolo'];
                        $node_index = translateFN("Nodi aggiunti dallo studente:").$studente_username."\n\n";
                        foreach ($nodes as $one_node) {
                            $row = array(
                                    translateFN('Corso')=>$course_title,
                                    //      translateFN('Edizione')=>$id_course_instance."(".ts2dFN($data_inizio).")",
                                    translateFN('Data')=>ts2dFN($one_node[4]),
                                    translateFN('Nodo')=>$one_node[0],
                                    translateFN('Titolo')=>"<a href=\"$http_root_dir/browsing/view.php?id_node=".$one_node[0]."&id_course=$id_course&id_course_instance=$id_instance\">".$one_node[1]."</a>"
                                    //    translateFN('Keywords')=>$one_node[2]
                            );
                            array_push($added_nodesHa,$row);
                            // exporting  to RTF
                            $note =  ts2dFN($one_node[4])."\n".
                                    $one_node[1]."\n". // title
                                    $one_node[5]."\n"; //text

                            $node_index.= $note."\n____________________________\n";
                        }
                    }

                }
            }
        }


        /*
             global $debug; $debug=1;
             mydebug(__LINE__,__FILE__,$added_nodesHa);
             $debug=0;
        */

        if ($op == 'student_notes_export') {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            // always modified
            header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");                          // HTTP/1.0
            //header("Content-Type: text/plain");
            header("Content-Type: application/rtf");
            //header("Content-Length: ".filesize($name));
            header("Content-Disposition: attachment; filename=forum_".$id_course."_class_".$id_instance."_student_".$id_student.".rtf");
            echo $node_index;
            exit;
        } else {
            $tObj = new Table();
            $tObj->initTable('1','center','0','1','100%','','','','','1','1');
            // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
            $caption = "<strong>".translateFN("Nodi inseriti nel forum")."</strong>";
            $summary = translateFN("Nodi inseriti nel forum del corso");
            $tObj->setTable($added_nodesHa,$caption,$summary);
            $added_notesHa = $tObj->getTable();
            $data = $added_notesHa;
            $status = translateFN('note aggiunte dallo studente');
         
//           $data['chat_users']=$online_users;
            $help = translateFN('Da qui il Tutor può leggere le note aggiunte nel forum da questo studente.');
        }
        break;

    case 'zoom_student':
        //$sess_id_course_instance = $id_instance;
        $info_course = $dh->get_course($id_course); // Get title course
        if  (AMA_DataHandler::isError($info_course)) {
            $msg = $info_course->getMessage();
        } else {
            $course_title = $info_course['titolo'];
        }
        // Who's online
        // $online_users_listing_mode = 0 (default) : only total numer of users online
        // $online_users_listing_mode = 1  : username of users
        // $online_users_listing_mode = 2  : username and email of users

        $online_users_listing_mode = 2;
        $online_users = ADALoggableUser::get_online_usersFN($id_instance,$online_users_listing_mode);

        $chat_link = '<a href="' . HTTP_ROOT_DIR
                   . '/comunica/adaChat.php target=_blank>'
                   . translateFN('chat').'</a>';

        $data = get_student_dataFN($id_student, $id_instance);

//        $student_activity_index = get_student_indexattFN($id_instance,$id_course,$id_student);

        $status = translateFN('caratteristiche dello studente');

        $help = translateFN('Da qui il Tutor può consultare le caratteristiche di uno studente.');
        break;

    case 'export': // outputs the users of selected course as a file excel
    // $courses_student = get_student_courses_tableFN($id_instance, $id_course);
    
    	/**
		 * @author giorgio 14/mag/2013
		 * 
		 * set allowed types of export and if $type is not in the list
		 * than default to xls type export.
		 *
    	 */
    	$allowed_export_types = array ('xls' , 'pdf');    	
    	if (!in_array($type, $allowed_export_types)) $type = 'xls';
    	
    	// get needed data
    	$courses_student = get_student_coursesFN($id_instance, $id_course,'', ($type=='xls') ? 'HTML' : 'FILE');    	
    	
    	// build up filename to be streamed out
    	$filename = 'course_'.$id_course.'_class_'.$id_instance.'.'.$type;
    	
    	if ($type==='pdf')
    	{
    		require_once ROOT_DIR.'/include/PdfClass.inc.php';
    		
    		$pdf =& new PdfClass('landscape', strip_tags(html_entity_decode($courses_student['caption'])) );
    		
    		$pdf->addHeader(strip_tags(html_entity_decode($courses_student['caption'])),
    						ROOT_DIR.'/layout/'.$userObj->template_family.'/img/header-logo.png', 14)
    			->addFooter( translateFN("Report")." ". translateFN("generato")." ". translateFN("il")." ". date ("d/m/Y")." ".
    					     translateFN("alle")." ".date ("H:i:s") );
    			    		    	
	    	// prepare header row
	    	foreach ($courses_student[0] as $key=>$val)
	    	{
	    		// skip level up and down images, cannot be done in config file
	    		// because it would remove cols from html too, and this is not good
	    		if (preg_match('/img/', $val) !== 0 ) continue;
	    		$cols[$key] = strip_tags($val);
	    	}
	    	
	    	array_shift($courses_student);
	    	// prepare data rows
	    	$data = array(); $i=0;
	    	foreach ($courses_student as $num=>$elem)
	    	{
    			foreach ($elem as $key=>$val) $data[$i][$key]=strip_tags($val);
    			$i++;    					    		
	    	}
	    	$pdf->ezTable ($data, $cols,
	    				   array ('width'=>$pdf->ez['pageWidth'] - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin']) );
			$pdf->saveAs($filename);
    	} 	     	
    	else if ($type === 'xls'){
	        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
	        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	        header("Cache-Control: post-check=0, pre-check=0", false);
	        header("Pragma: no-cache");                          // HTTP/1.0
	        header("Content-Type: application/vnd.ms-excel");
	        // header("Content-Length: ".filesize($name));
	        header("Content-Disposition: attachment; filename=course_".$id_course."_class_".$id_instance.".xls");
	        echo $courses_student;
			// header ("Connection: close");
    	}
        exit();
        break;


    case 'list_courses':
    default:
        if (!isset($status) || empty($status)) {
            $data['status'] = translateFN('lista dei corsi tutorati');
        }
        $data = get_courses_tutorFN($_SESSION['sess_id_user']);
        $help = translateFN("Da qui il Tutor può visualizzare l'elenco dei corsi di cui è attualmente tutor.");
        $online_users_listing_mode = 2;
        $online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


        $chat_link = "<a href=\"../comunica/adaChat.php\" target=_blank>".translateFN("chat")."</a>";

        break;
}

$banner = include ROOT_DIR . '/include/banner.inc.php';

$online_users_listing_mode = 2;
//$online_users = ADAGenericUser::get_online_usersFN($id_course_instance, $online_users_listing_mode);
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance, $online_users_listing_mode);

if (!empty($id_instance))
{
	$courseInstanceObj = new Course_instance($id_instance);
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
}

if (!empty($course_title))
{
	$course_title = ' > <a href="'.HTTP_ROOT_DIR.'/browsing/main_index.php">'.$course_title.'</a>';
}


$content_dataAr = array(
    'course_title'=>translateFN('Modulo tutor').$course_title,
    'path'=>$node_path,
    'banner' => $banner,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'edit_profile'=>$userObj->getEditProfilePage(),
    'level' => $user_level,
    'messages'=> $user_messages->getHtml(),
//        'events'=>$user_events->getHtml(),
    'agenda'=> $user_agenda->getHtml(),
    'help'  => $help,
    'dati'  => $data,
    'status' => $status,
    'chat_users' => $online_users,
    'chat_link' => $chat_link,
 );

$menuOptions['id_course'] = $id_course;
$menuOptions['id_instance'] = $id_instance;
$menuOptions['id_course_instance'] = $id_instance;
$menuOptions['id_student'] =$id_student;

ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
