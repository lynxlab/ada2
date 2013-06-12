<?php
/**
 * AUTHOR REPORT.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
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
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
        AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = author_report!

include_once 'include/author_functions.inc.php';

$menu = '';
/* 
 * 2. Building nodes summary
*/
if ((empty($id_node)) OR (!isset($mode))) {
    $mode='summary';
}

switch ($mode) {

    case 'zoom':

        $status = translateFN('zoom di un nodo');
        $help = translateFN("Da qui l'Autore del corso può vedere  in dettaglio le caratteristiche di un nodo.");

        $out_fields_ar = array('data_visita','id_utente_studente','id_istanza_corso');
        $clause ="id_nodo = '$id_node'";

        $visits_ar = $dh->_find_nodes_history_list($out_fields_ar,$clause);
        if (AMA_DataHandler::isError($visits_ar)) {
            $msg = $visits_ar->getMessage();
            print '$msg';
            //header('Location: $error?err_msg=$msg');
            //exit;
        }
        $visits_dataHa = array();
        $count_visits = count($visits_ar);
        if ($count_visits) {
            foreach ($visits_ar as $visit) {
                $user_id = $visit[2];
                if($user_id > 0) {
                    $student = $dh->_get_user_info($visit[2]);
                    //global $debug;$debug=1;mydebug(__LINE__,__FILE__,$student);$debug=0;
                    $studentname = $student['username'];
                }
                else {
                    $studentname = translateFN('Guest');
                }
                $visits_dataHa[] = array(
                        translateFN('Data')=>ts2dFN($visit[1]),
                        translateFN('Ora')=>ts2tmFN($visit[1]),
                        translateFN('Studente')=>$studentname,
                        translateFN('Edizione del corso')=>$visit[3]
                        // etc etc
                );
            }
            $tObj = new Table();
            $tObj->initTable('0','right','1','0','90%','','','','','1','0');

            $caption = translateFN('Dettaglio');
            $summary = translateFN('Dettaglio delle visite al nodo').$id_node;
            $tObj->setTable($visits_dataHa,$caption,$summary);
            $tabled_visits_dataHa = $tObj->getTable();

        }
        else {
            $tabled_visits_dataHa = translateFN('Nessun dato disponibile');
        }
        $menu .= '<a href="author_report.php?mode=summary">'.translateFN('report').'</a>';
        break;

    case 'xml':
        $filename = $id_course.'.xml';
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');    // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');          // always modified
        header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP/1.1
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');                          // HTTP/1.0
        header('Content-Type: text/xml');
        // header('Content-Length: '.filesize($name));
        header('Content-Disposition: attachment; filename=$filename');
        readfile('$http_root_dir/courses/media/$sess_id_user/$filename');
        //              header ('Connection: close');
        exit;
        break;

    case 'summary':
    default;

        $status = translateFN('elenco dei nodi');
        $help = translateFN("Da qui l'Autore del corso può vedere la lista dei nodi di cui è autore.");


        //$dh = new AMA_DataHandler();
        $course_listaHa = $dh->find_courses_list_by_key(array('id_corso','nome','titolo','data_pubblicazione'),$sess_id_user, array('id_utente_autore'));
        /* FIXME:
     * Se course_listaHa e' un errore di tipo AMA_ERR_NOT_FOUND tutto ok, semplicemente non ci sono
     * corsi, altimenti ADA_Error
        */
        if ((AMA_DataHandler::isError($course_listaHa)) || (!is_array($course_listaHa))) {
            $total_course_data = '';
        }
        else {
            $course_dataHa = array();
            foreach ($course_listaHa as $course) {
                $course_id = $course[1];
                $course_name = $course[2];
                $course_title = $course[3];
                $course_date = getdate($course[4]);
                $course_dataHa [] ="<a href=\"author_report.php?id_course=$course_id\">$course_name</a>"
                        .$course_date['mday'].'/'.$course_date['mon'].'/'.$course_date['year'];
            }
            $lObj = new Ilist();
            $lObj->setList($course_dataHa);
            $total_course_data = $lObj->getList();
        }

        //$sent_courses = $total_course_data;

//        if (!isset($id_course)) {
//            if (is_array($course)) {
//                $id_course = $course[0]; //??
//            }
//        }

        $courseHa = $dh->get_course($course_id);
        if (AMA_DataHandler::isError($courseHa)) {
            $err_msg = $courseHa->getMessage();
            //header('Location: $error?err_msg=$msg');
            //exit;
        }
        else {
            $course_title = $courseHa['titolo'];
            $clause = "id_nodo LIKE '{$course_id}_%' AND ";
            $field_list_ar = array('nome','id_utente');
            $clause .= "id_utente='$sess_id_user'";
            $dataHa = $dh->_find_nodes_list($field_list_ar, $clause);
            if (AMA_DataHandler::isError($dataHa)) {
                $err_msg = $dataHa->getMessage();
                //header('Location: $error?err_msg=$msg');
                //exit;
            }
            $total_visits = 0;
            $visits_dataHa = array();
            foreach ($dataHa as $visited_node) {
                $id_node = $visited_node[0];
                $nome =  $visited_node[1];
                $out_fields_ar = array('data_visita');
                $clause ="id_nodo = '$id_node'";

                // FIXME: verificare quale fra queste due usare
                //         $visits = $dh->find_nodes_history_list($out_fields_ar,'', '', $node_id);
                $visits = $dh->_find_nodes_history_list($out_fields_ar,$clause);

                if (AMA_DataHandler::isError($visits)) {
                    $msg = $visits->getMessage();
                    print '$msg';
                    //header('Location: $error?err_msg=$msg');
                    //exit;
                }
                $count_visits = count($visits);

                $total_visits = $total_visits + count($visits);
                $row = array(
                        translateFN('Id')     => $id_node,
                        translateFN('Nome')   => $nome,
                        translateFN('Visite') => $count_visits,
                );

                if ($count_visits>0) {
                    $row[translateFN('Zoom')]="<a href=\"author_report.php?mode=zoom&id_node=$id_node\"><img src=\"img/magnify.png\"' border=0></a>";
                }
                else {
                    $row[translateFN('Zoom')]='&nbsp;';
                }
                $id_course_and_nodeAr = explode('_',$id_node);
                $id_course = $id_course_and_nodeAr[0];
                $row[translateFN('Naviga')]="<a href=\"$http_root_dir/browsing/view.php?id_course=$id_course&id_node=$id_node\"><img src=\"img/timon.png\" border=0></a>";
                array_push($visits_dataHa,$row);
            }
        }

        if ($err_msg) {
            $tabled_visits_dataHa = translateFN("Nessun corso assegnato all'autore.");
        }
        else {
            $tObj = new Table();
            $tObj->initTable('0','center','1','0','90%','','','','','1','0');
            $caption = translateFN('Corso:')." <strong>$course_title</strong> ".translateFN('- Report al ')." <strong>$ymdhms</strong>";
            $summary = translateFN('Elenco dei nodi visitati');
            $tObj->setTable($visits_dataHa,$caption,$summary);
            $tabled_visits_dataHa = $tObj->getTable();
        }
}

// SERVICE:  BANNER
$banner = include ROOT_DIR.'/include/banner.inc.php';

$content_dataAr = array(
        'course_title' => translateFN('Report del corso'),
        'banner'       => $banner,
        'menu'         => $menu,
        'user_name'    => $user_name,
        'user_type'    => $user_type,
        'help'         => $help,
        'status'       => $status,
        //'head'         => translateFN('Report'),
        'dati'         => $tabled_visits_dataHa,
        'agenda'       => $user_agenda->getHtml(),
        'messages'     => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);