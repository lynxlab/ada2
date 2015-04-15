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

if (!isset($op)) $op = null;

/**
 * check if it's not a supertutor asking for op='tutor'
 * then set $op to make the default action
 */
if (!$userObj->isSuper() && $op=='tutor') $op=null;

switch ($op) {
	case 'tutor':
		$help = '';
		$fieldsAr = array('nome','cognome','username');
		$tutorsAr = $dh->get_tutors_list($fieldsAr);
		if (!AMA_DB::isError($tutorsAr) && is_array($tutorsAr) && count($tutorsAr)>0) {
			$tableDataAr = array();
			$imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
			$imgDetails->setAttribute('title', translateFN('visualizza/nasconde i dettagli del tutor'));
			$imgDetails->setAttribute('style', 'cursor:pointer;');
			$imgDetails->setAttribute('class', 'tooltip');
			
			$mh = MessageHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
			
			foreach ($tutorsAr as $aTutor) {
				// open details button
				$imgDetails->setAttribute('onclick',"toggleTutorDetails(".$aTutor[0].",this);");
				// received messages
				$receivedMessages = 0;
				$msgs_ha = $mh->get_messages($aTutor[0],ADA_MSG_SIMPLE);
				if (!AMA_DataHandler::isError($msgs_ha)) {
					$receivedMessages = count($msgs_ha);
				}
				// sent messages				
				$sentMessages = 0;
				$msgs_ha = $mh->get_sent_messages($aTutor[0], ADA_MSG_SIMPLE);
				if (!AMA_DataHandler::isError($msgs_ha)) {
					$sentMessages = count($msgs_ha);
				}
				$tableDataAr[] = array_merge(array($imgDetails->getHtml()),$aTutor,array($receivedMessages,$sentMessages));
			}
		}
		$thead = array(null,
				translateFN('Id'),
				translateFN('Nome'),
				translateFN('Cognome'),
				translateFN('username'),
				translateFN('Msg Ric'),
				translateFN('Msg Inv')
		);		
		$tObj = BaseHtmlLib::tableElement('id:listTutors',$thead,$tableDataAr,null,translateFN('Elenco dei tutors'));
        $tObj->setAttribute('class', 'default_table doDataTable');
        $data = $tObj->getHtml();
		break;
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
    case 'student':
    case 'class_report': // Show the students subscribed in selected course and a report
        if(!isset($id_course)) {
            $id_course = $dh->get_course_id_for_course_instance($id_instance);
            if(AMA_DataHandler::isError($id_course)) {
                $id_course = 0;
            }
        }
        if ($mode=='update') {
        	if (!isset($order)) $order=null;
            $courses_student = get_student_coursesFN($id_instance,$id_course,$order);
        } else {
            // load
            $courses_student = get_student_courses_from_dbFN($id_course, $id_instance);
        }
        
        if (!is_null($courses_student)) {
        	if (isset($courses_student['report_generation_date']) && !is_null($courses_student['report_generation_date'])) {
        		$report_generation_TS = $courses_student['report_generation_date'];
        		unset ($courses_student['report_generation_date']);
        	}
        	$thead = array_shift($courses_student);
        	$tfoot = array_pop($courses_student);
        	$tObj = BaseHtmlLib::tableElement('id:table_Report',$thead,$courses_student,$tfoot,null);
        	$tObj->setAttribute('class', 'default_table doDataTable');
        	$data = $tObj->getHtml();
        } else {
        	if ($mode=='update') {
        		$data = translateFN("Non ci sono studenti in questa classe");
        	} else {
//         		$http_root_dir = $GLOBALS['http_root_dir'];
//         		$data  = translateFN("Non è presente un report dell'attivita' della classe aggiornato alla data odierna. ");
//         		$data .= "<a href=\"$http_root_dir/tutor/tutor.php?op=student&id_instance=$id_instance&id_course=$id_course&mode=update\">";
//         		$data .= translateFN("Aggiorna il report.");
//         		$data .= "</a>";
				/**
				 * @author giorgio 27/ott/2014
				 * 
				 * if no class report was ever generated, redirect the user to the mode=update page
				 */
        		redirect("$http_root_dir/tutor/tutor.php?op=student&id_instance=$id_instance&id_course=$id_course&mode=update");
        	}        	
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
            
            $instance_course_ha = $dh->course_instance_get($id_instance); // Get the instance courses data
            $start_date =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], ADA_DATE_FORMAT);

           	$help = translateFN("Studenti del corso") . " <strong>$course_title</strong>  - ".
           			translateFN("Classe")." ".$instance_course_ha['title']." (".
           			$id_instance.") - " . translateFN("Iniziato il ");
           	$help .= "&nbsp;<strong>$start_date</strong>" ;
           	$help .= '<br />' . translateFN("Cliccando sui dati si accede al dettaglio.");
           	if (isset($report_generation_TS)) {
           		$updateDIV = CDOMElement::create('div','class:updatelink');
           		$updateSPAN = CDOMElement::create('span');
           		$updateSPAN->addChild(new CText(translateFN('Report aggiornato al').' '.ts2dFN($report_generation_TS)));           		
           		$updateLink = CDOMElement::create('a','href:'.$http_root_dir.
           				'/tutor/tutor.php?op=student&id_instance='.$id_instance.'&id_course='.$id_course.'&mode=update');
           		$updateLink->addChild(new CText(' '.translateFN("Aggiorna il report")));
           		$updateDIV->addChild($updateSPAN);
           		$updateDIV->addChild($updateLink);
        		$help .= $updateDIV->getHtml();
           	}
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
        if (isset($id_course)) {
	        $info_course = $dh->get_course($id_course); // Get title course
	        if  (AMA_DataHandler::isError($info_course)) {
	            $msg = $info_course->getMessage();
	        } else {
	            $course_title = $info_course['titolo'];
	        }
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

    	// build the caption
    	// 0. Get title course
    	$info_course = $dh->get_course($id_course);
    	if (AMA_DB::isError($info_course)) $course_title = '';
    	else $course_title =  $info_course['titolo'];
    	// 1. Get the instance courses data
    	$instance_course_ha = $dh->course_instance_get($id_instance);
    	if (AMA_DB::isError($instance_course_ha)) {
    		$start_date = '';
    		$instance_title = '';
    	} else {
    		$start_date =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], ADA_DATE_FORMAT);
    		$instance_title = $instance_course_ha['title'];    		
    	}
    	
    	$caption = translateFN("Studenti del corso") . " <strong>$course_title</strong>  - ".
    			translateFN("Classe")." ".$instance_title." (".
    			$id_instance.") - " . translateFN("Iniziato il ")."&nbsp;<strong>$start_date</strong>" ;
    	    	
    	// build up filename to be streamed out
    	$filename = 'course_'.$id_course.'_class_'.$id_instance.'.'.$type;
    	
    	if ($type==='pdf')
    	{
    		require_once ROOT_DIR.'/include/PdfClass.inc.php';
    		
    		$pdf = new PdfClass('landscape', strip_tags(html_entity_decode($courses_student['caption'])) );
    		
    		$pdf->addHeader(strip_tags(html_entity_decode($caption)),
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
    		$tObj = BaseHtmlLib::tableElement('id:table_Report',array_shift($courses_student),$courses_student,array(),null);
	        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
	        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	        header("Cache-Control: post-check=0, pre-check=0", false);
	        header("Pragma: no-cache");                          // HTTP/1.0
	        header("Content-Type: application/vnd.ms-excel");
	        // header("Content-Length: ".filesize($name));
	        header("Content-Disposition: attachment; filename=course_".$id_course."_class_".$id_instance.".xls");
	        echo  $tObj->getHtml();
			// header ("Connection: close");
    	}
        exit();
        break;


    case 'list_courses':
    default:
        if (!isset($status) || empty($status)) {
            $data['status'] = translateFN('lista dei corsi tutorati');
        }
        $isSuper = (isset($userObj) && $userObj instanceof ADAPractitioner && $userObj->isSuper());
        $data = get_courses_tutorFN($_SESSION['sess_id_user'], $isSuper);
        $help = translateFN("Da qui il Tutor può visualizzare l'elenco dei corsi di cui è attualmente tutor.");
        $online_users_listing_mode = 2;
        if (!isset($id_course_instance)) $id_course_instance=null;
        $online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


        $chat_link = "<a href=\"../comunica/adaChat.php\" target=_blank>".translateFN("chat")."</a>";

        break;
}

$banner = include ROOT_DIR . '/include/banner.inc.php';

$online_users_listing_mode = 2;
//$online_users = ADAGenericUser::get_online_usersFN($id_course_instance, $online_users_listing_mode);
if (!isset($id_course_instance)) $id_course_instance=null;
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

	if (!isset($nodeObj) || !is_object($nodeObj)) {
		if (!isset($node)) $node=null;
		$nodeObj = read_node_from_DB($node);
	}
	
	if (!ADA_Error::isError($nodeObj) AND isset($courseObj->id)) {
		$_SESSION['sess_id_course'] = $courseObj->id;
		$node_path = $nodeObj->findPathFN();
	}
}

if (isset($courseObj) && $courseObj instanceof Course && strlen($courseObj->getTitle())>0)
{
	$course_title = ' > <a href="'.HTTP_ROOT_DIR.'/browsing/main_index.php">'.$courseObj->getTitle().'</a>';
}

$content_dataAr = array(
    'course_title'=>translateFN('Modulo tutor').(isset($course_title) ? (' '.$course_title): null),
    'path'=>isset($node_path) ? $node_path : '',
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
    'chat_link' => isset($chat_link) ? $chat_link : ''
 );

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS,
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		ROOT_DIR.'/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
		JQUERY_NO_CONFLICT
);
$menuOptions = array();
if (isset($id_course))   $menuOptions['id_course'] = $id_course;
if (isset($id_instance)) $menuOptions['id_instance'] = $id_instance;
if (isset($id_instance)) $menuOptions['id_course_instance'] = $id_instance;
if (isset($id_student))  $menuOptions['id_student'] =$id_student;
/**
 * add a define for the supertutor menu item to appear
 */
if ($userObj instanceof ADAPractitioner && $userObj->isSuper()) define ('IS_SUPERTUTOR', true);
else define ('NOT_SUPERTUTOR', true);

$optionsAr['onload_func'] = 'initDoc(';
if (isset($id_course) && intval($id_course)>0 && isset($id_instance) && intval($id_instance)>0)
	$optionsAr['onload_func'] .= $id_course.','.$id_instance;
$optionsAr['onload_func'] .= ');';

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr,$menuOptions);
