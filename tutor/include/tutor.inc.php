<?php

function get_courses_tutorFN($id_user, $isSuper=false) {
    $dh = $GLOBALS['dh'];
    $ymdhms= $GLOBALS['ymdhms'];
    $http_root_dir= $GLOBALS['http_root_dir'];

    $all_instance = array();
    $sub_course_dataHa = array();
    $dati_corso = array();
    $today_date = $dh->date_to_ts("now");
    $all_instance = $dh->course_tutor_instance_get($id_user, $isSuper); // Get the instance courses monitorated by the tutor

    $num_courses = 0;
    $id_corso_key    = translateFN('Corso');
	$titolo_key      = translateFN('Titolo corso');
    $id_classe_key   = translateFN('Classe');
    $nome_key      = translateFN('Nome classe');
    $data_inizio_key = translateFN('Inizio');
    $durata_key      = translateFN('Durata');
    $azioni_key      = translateFN('Azioni');
    $msg = "";
    if (is_array($all_instance)) {
        foreach ($all_instance as $one_instance) {
            $num_courses++;
            $id_instance = $one_instance[0];
            $instance_course_ha = $dh->course_instance_get($id_instance); // Get the instance courses data
            if  (AMA_DataHandler::isError($instance_course_ha)) {
                $msg .= $instance_course_ha->getMessage()."<br />";
            } else {
                $id_course = $instance_course_ha['id_corso'];
                if (!empty($id_course)) {
                    $info_course = $dh->get_course($id_course); // Get title course
                    if  (AMA_DataHandler::isError($dh)) {
                        $msg .= $dh->getMessage()."<br />";
                    }
                    if(!AMA_DB::isError($info_course)) {
	                    $titolo = $info_course['titolo'];
	                    $id_toc = $info_course['id_nodo_toc'];
	                    $durata_corso = sprintf(translateFN('%d giorni'), $instance_course_ha['durata']);
	                    $naviga = '<a href="'.$http_root_dir.'/browsing/view.php?id_node='.$id_course.'_'.$id_toc.'&id_course='.$id_course.'&id_course_instance='.$id_instance.'">'.
	                    	'<img src="img/timon.png"  alt="'.translateFN('naviga').'" title="'.translateFN('naviga').'" class="tooltip" border="0"></a>';
	                    $valuta = '<a href="'.$http_root_dir.'/tutor/tutor.php?op=student&id_instance='.$id_instance.'&id_course='.$id_course.'">'.
	                    	'<img src="img/magnify.png"  alt="'.translateFN('valuta').'" title="'.translateFN('valuta').'" class="tooltip" border="0"></a>';
                        $videochatlog = '<a href="'.$http_root_dir.'/tutor/videochatlog.php?id_course='.$id_course.'&id_course_instance='.$id_instance.'">'.
	                    	'<img src="img/videochatlog.png"  alt="'.translateFN('log videochat').'" title="'.translateFN('log videochat').'" class="tooltip" border="0"></a>';
	                    if(defined('MODULES_CLASSAGENDA') && (MODULES_CLASSAGENDA)) {
                        	$presenze = '<a href="'.MODULES_CLASSAGENDA_HTTP.'/rollcall.php?id_course='.$id_course.'&id_course_instance='.$id_instance.'"><img src="img/badge.png"  alt="'.translateFN('presenze').'"  title="'.translateFN('presenze').'" class="tooltip" border="0"></a>';
                        	$registro = '<a href="'.MODULES_CLASSAGENDA_HTTP.'/rollcallhistory.php?id_course='.$id_course.'&id_course_instance='.$id_instance.'"><img src="img/registro.png"  alt="'.translateFN('registro').'" title="'.translateFN('registro').'" class="tooltip" border="0"></a>';
                    	}
	                    $data_inizio = AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], "%d/%m/%Y");

		                $dati_corso[$num_courses][$id_corso_key]= $instance_course_ha['id_corso'];
						$dati_corso[$num_courses][$titolo_key] = $titolo;
	                    $dati_corso[$num_courses][$id_classe_key] =  $id_instance;
	                    $dati_corso[$num_courses][$nome_key] =  $instance_course_ha['title'];
	                    $dati_corso[$num_courses][$data_inizio_key] = $data_inizio;
	                    $dati_corso[$num_courses][$durata_key] = $durata_corso;
	                    $dati_corso[$num_courses][$azioni_key] = $naviga;
	                    $dati_corso[$num_courses][$azioni_key] .= $valuta;

                        if (defined('VIDEOCHAT_REPORT') && VIDEOCHAT_REPORT) {
                            $videochatlog = '<a href="'.$http_root_dir.'/tutor/videochatlog.php?id_course='.$id_course.'&id_course_instance='.$id_instance.'">'.
	                    	'<img src="img/videochatlog.png"  alt="'.translateFN('log videochat').'" title="'.translateFN('log videochat').'" class="tooltip" border="0"></a>';
                            $dati_corso[$num_courses][$azioni_key] .= $videochatlog;
                        }

	                    if(defined('MODULES_CLASSAGENDA') && (MODULES_CLASSAGENDA)) {
	                    	$dati_corso[$num_courses][$azioni_key] .= $presenze;
	                    	$dati_corso[$num_courses][$azioni_key] .= $registro;
	                    }

	                    if (defined('MODULES_TEST') && MODULES_TEST) {
	                    	$survey_title=translateFN('Report Sondaggi');
	                    	$survey_img= CDOMElement::create('img', 'src:img/_exer.png,alt:'.$survey_title.',class:tooltip,title:'.$survey_title);
	                    	$survey_link = BaseHtmlLib::link(MODULES_TEST_HTTP.'/surveys_report.php?id_course_instance='.$id_instance.'&id_course='.$id_course, $survey_img->getHtml());
	                    	$dati_corso[$num_courses][$azioni_key] .= $survey_link->getHtml();
                        }
                        if (defined('MODULES_BADGES') && MODULES_BADGES) {
                            $badges_title=translateFN('Badges disponibili');
	                    	$badges_img= CDOMElement::create('img','src:'.MODULES_BADGES_HTTP.'/layout/'.$_SESSION['sess_template_family'].'/img/course-badges.png,alt:'.$badges_title.',class:tooltip,title:'.$badges_title);
	                    	$badges_link = BaseHtmlLib::link(MODULES_BADGES_HTTP.'/user-badges.php?id_instance='.$id_instance.'&id_course='.$id_course, $badges_img->getHtml());
	                    	$dati_corso[$num_courses][$azioni_key] .= $badges_link->getHtml();
                        }
                    }
                }
            }
        }

        $courses_list = "";
        if ((count($dati_corso) > 0) &&(empty($msg))) {
            $caption = translateFN("Corsi monitorati al")." $ymdhms";
        	$tObj = BaseHtmlLib::tableElement('id:listCourses',
        			array(	$id_corso_key, $titolo_key, $id_classe_key,
        					$nome_key, $data_inizio_key, $durata_key, $azioni_key) ,$dati_corso,null,$caption);
        	$tObj->setAttribute('class', 'default_table doDataTable '.ADA_SEMANTICUI_TABLECLASS);
        	$courses_list = $tObj->getHtml();
        } else {
            $courses_list = $msg;
        }
    } else {
        $tObj = new Table();
        $tObj->initTable('0','center','0','1','','','','','','1');
        $caption = translateFN("Non ci sono corsi monitorati da te al $ymdhms");
        $summary = translateFN("Elenco dei corsi monitorati");
        $tObj->setTable($dati_corso,$caption,$summary);
        $courses_list = $tObj->getTable();
    }
    if(empty($courses_list)) {
        $courses_list = translateFN('Non ci sono corsi di cui sei tutor.');
    }
    return $courses_list;
}

// @author giorgio 14/mag/2013
// added type parameter that defaults to 'xls'
function get_student_coursesFN($id_course_instance,$id_course,$order="",$type='HTML',$speed_mode=true) {
// wrapper for Class Student_class (in courses_class.inc.php)
	// 2nd parameter empty string means get all students
    $student_classObj = New Student_class($id_course_instance, '');
    return $student_classObj->get_class_reportFN($id_course,$order,'',$type,$speed_mode);

}

function get_student_courses_from_dbFN($id_course,$id_course_instance,$order="") {
// wrapper for Class Student_class (in courses_class.inc.php)
    $student_classObj = New Student_class($id_course_instance);
    return $student_classObj->get_class_report_from_dbFN($id_course,$id_course_instance,$order);

}

function get_student_indexattFN($id_course_instance,$id_course,$id_student) {
// wrapper for Class Student_class (in courses_class.inc.php)
    $student_classObj = New Student_class($id_course_instance);
    $student_dataHa =  $student_classObj->find_student_index_att($id_course,$id_course_instance,$id_student);
    if  (is_array($student_dataHa) ) {
        $dati_stude[0]['name_index_att'] = translateFN("Indice attivit&agrave;");
        $dati_stude[0]['index_att'] = $student_dataHa['index_att'];

        $tObj = new Table();
        // $tObj->initTable('0','center','0','1','100%','black','white','black','white');
        $tObj->initTable('0','center','0','1','','','','','','1');
        // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
        $caption = translateFN("Indice attivit&agrave; al")." $ymdhms";
        $summary = translateFN("Dati relativi al corso monitorato");
        $tObj->setTable($dati_stude,$caption,$summary);
        $tabled_report = $tObj->getTable();
    } else {
        $tabled_report = "";
    }
    return $tabled_report;
}


function get_student_dataFN($id_student, $id_instance) {
    $dh = $GLOBALS['dh'];
    $http_root_dir= $GLOBALS['http_root_dir'];

    $student_info_ha = $dh->_get_user_info($id_student); // Get info of each student
    if  (AMA_DataHandler::isError($student_info_ha)) {
        $msg = $student_info_ha->getMessage();
        return $msg;
    }

    $instance_course_ha = $dh->course_instance_get($id_instance); // Get the instance courses data
    if  (AMA_DataHandler::isError($instance_course_ha)) {
        $msg = $instance_course_ha->getMessage();
        return $msg;
    }


    $id_course = $instance_course_ha['id_corso'];
    $start_date =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], ADA_DATE_FORMAT);

    $info_course = $dh->get_course($id_course); // Get title course
    if  (AMA_DataHandler::isError($info_course)) {
        $msg = $info_course->getMessage();
        return $msg;
    }
    $course_title = $info_course['titolo'];


    /*
        global $debug;$debug=1;
        mydebug(__LINE__,__FILE__,$student_info_ha);
        $debug = 0;
    */

    $name = $student_info_ha['nome'];
    $name_desc = "<B>" . translateFN("Nome") . "</B>";
    $surname = $student_info_ha['cognome'];
    $surname_desc = "<B>" . translateFN("Cognome") . "</B>";
    $email = $student_info_ha['email'];
    $email_desc = "<B>" . translateFN("Email") . "</B>";
    $phone_n = $student_info_ha['telefono'];
    $phone_desc = "<B>" . translateFN("Telefono") . "</B>";
    $user = $student_info_ha['username'];
    $user_desc = "<B>" . translateFN("User Name") . "</B>";
    $course_desc = "<B>" . translateFN("Titolo del Corso") . "</B>";
    $start_desc = "<B>" . translateFN("Data di inizio") . "</B>";


    $dati_stude[0]['name_desc'] = $name_desc;
    $dati_stude[0]['name'] = $name;
    $dati_stude[1]['surname_desc'] = $surname_desc;
    $dati_stude[1]['surname'] = $surname;
    $dati_stude[2]['email_desc'] = $email_desc;
    $dati_stude[2]['email'] = $email;
    $dati_stude[3]['phone_desc'] = $phone_desc;
    $dati_stude[3]['phone'] = $phone_n;
    $dati_stude[4]['user_desc'] = $user_desc;
    $dati_stude[4]['user'] = $user;
    $dati_stude[5]['course_desc'] = $course_desc;
    $dati_stude[5]['course'] = $course_title;
    $dati_stude[6]['start_desc'] = $start_desc;
    $dati_stude[6]['start'] = $start_date;

    $tObj = new Table();
    // $tObj->initTable('0','center','0','1','100%','black','white','black','white');
    $tObj->initTable('1','center','0','1','','','','','','1');
    // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
    $caption = translateFN("Studente selezionato: <B>") . $id_student . "</B> ";
    // $summary = translateFN("Elenco dei corsi monitorati");
    $summary = "";
    // $tObj->setTable($dati_stude,$caption,$summary);
    $tObj->setTable($dati_stude,$caption,$summary);
    $student_info=$tObj->getTable();

    return $student_info;


}


function set_levelFN($id_course_instance, $student_ar, $level) {
    $dh = $GLOBALS['dh'];
    $http_root_dir= $GLOBALS['http_root_dir'];
    $level_updated = set_student_level($id_course_instance, $studenti_ar, $level);
    return $level_updated;

}

/*
function get_course_instance_info ($id_course_instance){
                global $dh;
                $instance_course_ha = $dh->course_instance_get($id_course_instance); // Get the instance courses data
                if  (AMA_DataHandler::isError($dh)) {
                        $msg = $dh->getMessage();
                        return $msg;
                }
                $dati_corso[$num_courses]['id_corso'] = $instance_course_ha['id_corso'];
                $dati_corso[$num_courses]['data_inizio'] =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], "%d/%m/%Y");
                $dati_corso[$num_courses]['durata'] = $instance_course_ha['durata'];
                $id_course = $instance_course_ha['id_corso'];
                if (!empty($id_course)) {
                        $info_course = $dh->get_course($id_course); // Get title course
                        if  (AMA_DataHandler::isError($dh)) {
                                $msg = $dh->getMessage();
                                return $msg;
                        }
                        $titolo = "<a href=" .  $http_root_dir . "/tutor/tutor.php?op=student&id_instance=" . $id_instance;
                        $titolo .= "&id_course=" . $id_course .">";
                        $titolo .= $info_course['titolo'] . "</a>";
                }
                return
}



*/

// Funzione scrittura form Correzione esercizio
function form_exercise($file_action,$file_back,$data_ha) {
    /* $data_ha contiene:

        global $debug; $debug=1;
        mydebug(__LINE__,__FILE__,$data_ha);
        $debug = 0;
    */

    $id_nodo= $data_ha['id_nodo'];
    $id_exe = $data_ha['id_exe'];
    $titolo = $data_ha['titolo'];
    $node_type = $data_ha['node_type'];
    $exe_type = $node_type;
    $text = $data_ha['text'];

    $id_student = $data_ha['id_student'];
    $id_course_instance = $data_ha['id_course_instance'];

    $date_exe = $data_ha['date_exe'];
    $answer_id = $data_ha['answer_id'];
    $commento = $data_ha['commento'];
    $punteggio = $data_ha['punteggio'];
    $correzione = $data_ha['correzione'];
    $ripetibile = $data_ha['ripetibile'];
    $answer = $data_ha['answer'];

    $spedisci = 0;


    // inizializzazione variabili
    $str = "";

    // id_exe
    $fields["add"][]="id_exe";
    $names["add"][]="id_exe";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_exe;
    $options["add"][]="";
    $maxsize["add"][]=64;

    // student_id
    $fields["add"][]="id_student";
    $names["add"][]="id__student";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_student;
    $options["add"][]="";
    $maxsize["add"][]=64;

    // istance_id
    $fields["add"][]="id_course_instance";
    $names["add"][]="id_course_instance";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_course_instance;
    $options["add"][]="";
    $maxsize["add"][]=64;

    // id_nodo
    $fields["add"][]="id_nodo";
    $names["add"][]="id_nodo";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_nodo;
    $options["add"][]="";
    $maxsize["add"][]=64;

    // nome/Esercizio
    $fields["add"][]="titolo";
    $names["add"][]=translateFN("Titolo");
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$titolo;
    $options["add"][]="";
    $maxsize["add"][]=128;

    // Punteggio
    $fields["add"][]="punteggio";
    $names["add"][]="Punteggio";
    if ($exe_type == 4) {
        $edittypes["add"][]="text";
    } else {
        $edittypes["add"][]="hidden";
    }
    $necessary["add"][]="";
    $values["add"][]=$punteggio;
    $options["add"][]="";
    $maxsize["add"][]=5;

    // Tipo di nodo
    $fields["add"][]="exe_type";
    $names["add"][]="exe_type";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$exe_type;
    $options["add"][]="";
    $maxsize["add"][]=5;

    // Correzione risposta libera
    $fields["add"][]="correzione";
    $names["add"][]="Correzione";
    if ($node_type == 4) {
        $edittypes["add"][]="textarea";
    }else {
        $edittypes["add"][]="hidden";
    }
    $necessary["add"][]="";
    $values["add"][]=$correzione;
    $options["add"][]="";
    $maxsize["add"][]="";

    // risposta libera. In realt�contiene il nodo risposta selezionato.
    $fields["add"][]="answer_id";
    $names["add"][]="answer_id";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$answer_id;
    $options["add"][]="";
    $maxsize["add"][]="";

    // Commento
    $fields["add"][]="commento";
    $names["add"][]=translateFN("Commento");
    $edittypes["add"][]="textarea";
    $necessary["add"][]="";
    $values["add"][]=$commento;
    $options["add"][]="";
    $maxsize["add"][]="";

    // Ripetibile
    $fields["add"][]="ripetibile";
    $names["add"][]=translateFN("Lo studente pu&ograve; ripetere l'esercizio") . ":";
    $edittypes["add"][]="checkbox";
    $necessary["add"][]="";
    $values["add"][]=$ripetibile;
    $options["add"][]="1:";
    $maxsize["add"][]="";

    // Spedire allo studente il commento e la risposta
    $fields["add"][]="spedisci";
    $names["add"][]=translateFN("Invia messaggio allo studente") . ":";
    $edittypes["add"][]="checkbox";
    $necessary["add"][]="";
    $values["add"][]=$spedisci;
    $options["add"][]="1:";
    $maxsize["add"][]="";

    $submit_desc = translateFN("Salva");
    // creazione del form
    $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true,$submit_desc);

    // scrittura stringa back
    // $str .= $this->go_file_back($file_back,"Home");

    return $str;
}


function spedisci_messaggioFN($testo,$subject,$destinatario, $mittente) {
    $sess_id_user = $_SESSION['sess_id_user'];
    // Initialize errors array
    $errors = array();

    $mh = new MessageHandler();

    // prepare message to send
    $message_ha['destinatari'] = $destinatario;
    $message_ha['data_ora'] = "now";
    $message_ha['tipo'] = ADA_MSG_SIMPLE;
    $message_ha['mittente'] = $mittente;
    $message_ha['testo'] = $testo;
    $message_ha['titolo'] = $subject;
    $message_ha['priorita'] = 2;

    /*
        global $debug; $debug=1;
        mydebug(__LINE__,__FILE__,$message_ha);
        $debug=0;
    */


    // delegate sending to the message handler
    $res = $mh->send_message($message_ha);
    if (AMA_DataHandler::isError($res)) {
        $err_code = $res->code;
        $errore = urlencode(translateFN("Impossibile spedire il messaggio. Errore n. "));
        //header("Location: list_messages.php?status= $errore $err_code");
        exit;
    }


} // Fine funzione di spedizione del messaggio di correzione di un esercizio




function menu_detailsFN($id_student,$id_course_instance,$id_course) {
// Menu nodi visitati per periodo
    $menu_history = translateFN("Nodi visitati recentemente:")."<br>\n" ;
    $menu_history .= "<a href=\"tutor_history_details.php?period=1&id_student=" . $id_student;
    $menu_history .= "&id_course_instance=" . $id_course_instance . "&id_course=" . $id_course ."\">".translateFN("1 giorno"). "</a><br>\n";

    $menu_history .= "<a href=\"tutor_history_details.php?period=5&id_student=" . $id_student;
    $menu_history .= "&id_course_instance=" . $id_course_instance . "&id_course=" . $id_course. "\">".translateFN("5 giorni") . "</a><br>\n";

    $menu_history .= "<a href=\"tutor_history_details.php?period=15&id_student=" . $id_student;
    $menu_history .= "&id_course_instance=" . $id_course_instance . "&id_course=" . $id_course. "\">".translateFN("15 giorni")."</a><br>\n";

    $menu_history .= "<a href=\"tutor_history_details.php?period=30&id_student=" . $id_student;
    $menu_history .= "&id_course_instance=" . $id_course_instance . "&id_course=" . $id_course. "\">".translateFN("30 giorni")."</a><br>\n";

    $menu_history .= "<a href=\"tutor_history_details.php?period=all&id_student=" . $id_student;
    $menu_history .= "&id_course_instance=" . $id_course_instance . "&id_course=" . $id_course. "\">".translateFN("tutto")."</a><br>\n";


    return $menu_history;

}

/***************************************************/
// FUNCTIONS
/***************************************************/

function form_list_register($id_course,$id_instance,$id_tutor,$file_action,$file_back) {
    // $file_action = "registration.php";

    // id autore
    $fields["add"][]="IDautore";
    $names["add"][]="IDautore";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_tutor;
    $options["add"][]="";
    $maxsize["add"][]=12;

    // input file
    $fields["add"][]="file_up";
    $names["add"][]="File elenco studenti:";
    $edittypes["add"][]="file";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=255;

    // course_instance
    $fields["add"][]="id_instance";
    $names["add"][]="id_instance";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    // $values["add"][]="";
    $values["add"][]=$id_instance;
    $options["add"][]="";
    $maxsize["add"][]=12;

    // course
    $fields["add"][]="id_course";
    $names["add"][]="id_course";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    // $values["add"][]="";
    $values["add"][]=$id_course;
    $options["add"][]="";
    $maxsize["add"][]=12;

    // creazione del form
    $dati = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);
    return $dati;

}


function form_single_register($id_course,$id_instance,$file_action,$file_back) {
    // inizializzazione variabili
    $str = "";

    // nome
    $fields["add"][]="student[nome]";
    $names["add"][]="Nome";
    $edittypes["add"][]="text";
    $necessary["add"][]="true";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // cognome
    $fields["add"][]="student[cognome]";
    $names["add"][]="Cognome";
    $edittypes["add"][]="text";
    $necessary["add"][]="true";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // email
    $fields["add"][]="student[email]";
    $names["add"][]="e-mail";
    $edittypes["add"][]="text";
    $necessary["add"][]="true";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // username
    $fields["add"][]="student[username]";
    $names["add"][]="username";
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // password
    $fields["add"][]="student[password]";
    $names["add"][]="password";
    $edittypes["add"][]="password";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // password check
    $fields["add"][]="student[passwordcheck]";
    $names["add"][]="ripeti password";
    $edittypes["add"][]="password";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;

    // telefono
    $fields["add"][]="student[telefono]";
    $names["add"][]="telefono";
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=12;

    // course_instance
    $fields["add"][]="id_instance";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_instance;
    $options["add"][]="";
    $maxsize["add"][]=12;

    // course
    $fields["add"][]="id_course";
    $names["add"][]="id_course";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    // $values["add"][]="";
    $values["add"][]=$id_course;
    $options["add"][]="";
    $maxsize["add"][]=12;

    // creazione del form
    $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

    return $str ;
}


function notify_admin($title,$msg) {
    $dh=$GLOBALS['dh'];

    $admins_list = $dh->get_admins_ids();
    $admin_id =   $admins_list[0][0];
    $admin = $dh->get_admin($admin_id);
    $admin_uname = $admin['username'];
// primo accesso in ADA

    $mh = new MessageHandler();
    $message_ha['destinatari'] = $admin_uname;
    $message_ha['priorita'] = 1;
    $message_ha['data_ora'] = "now";
    $message_ha['titolo'] = $title;
    $message_ha['testo'] = $msg;
    $message_ha['data_ora'] = "now";
    $message_ha['mittente'] = $admin_uname;
// e-mail
    $message_ha['tipo'] = ADA_MSG_MAIL;
    $res = $mh->send_message($message_ha);
// messaggio interno
    $message_ha['tipo'] = ADA_MSG_SIMPLE;
    $res = $mh->send_message($message_ha);
}

?>
