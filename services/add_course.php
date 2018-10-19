<?php
/**
 * AUTHOR ADD COURSE.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/*
 * THE AUTHOR IS NOT ALLOWED TO CREATE A COURSE.
 * COURSES ARE CREATED BY THE ADMIN.
 * so we do not allow users here.
*/

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();

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

$self =  whoami();

include_once 'include/author_functions.inc.php';

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
ServiceHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
*/
include_once ROOT_DIR.'/include/form/phpOpenFormGen.inc.php';
include_once ROOT_DIR.'/admin/include/htmladmoutput.inc.php';

//print_r($GLOBALS['testers_dataAr']);
// definizione delle variabili
$dati = "";

$success = ($course["xml"])
        ? "author_course_xml_to_db_process.php"
        : "author.php";

//$success = "author_course_xml_to_db.php";
//die($success);
$error = "error.php";
$menu = "author.php";

$help = translateFN("Da qui l'Autore  pu&ograve; aggiungere un nuovo corso.<br>
Inserire un breve codice mnemonico nel campo Nome (es. C_1) e un titolo pi&ugrave; esplicativo nel campo Titolo<br><br>
Il media_path &egrave; creato automaticamente; se si vuole riutilizzare il corredo multimediale di altri corsi si pu&ograve specificare un percorso qui (relativo).<br>
E' anche possibile specificare un ID per il nodo Indice e per il nodo di partenza (se diversi da quelli standard).<br><br>
Vedi in proposito la <a href=\"../docs/html/amministratore.html\">documentazione</a>.");

if (!$status) {
    $status = translateFN('inserimento corso');
}

$title = translateFN('ADA - Aggiungi Corso');

$menu='';

//*************************************************************

// Se si sono riempiti i dati del form esegue operazione di controllo dei dell' identit&agrave; utente
if(@$submit) {
    // controllo validita' dei dati immessi
    $dati = "";
    if (trim($course['nome']) == '') {
        $dati = translateFN('Non possono essere vuoti i campi:<br>');
        $dati .= translateFN('nome').'<br>';
    }
    if (trim($course['titolo']) == '') {
        if (empty($dati)) {
            $dati = translateFN('Non possono essere vuoti i campi:<br>');
        }
        $dati .= translateFN('titolo') . '<br>';
    }

    // aggiunta Corso
    // se i dati sono ok, prova l'inserimento nel DB
    if (!$dati) {
        $course_id = $dh->add_course($course);

        if (AMA_DataHandler::isError($course_id)) {
            // $dati = $result->getMessage();
            $msg =  $course_id->getMessage();
            if ($course_id->code == AMA_ERR_UNIQUE_KEY) {
                $status .= " " . translateFN('Il corso esiste!');
                header("Location: $self.php?status=$status");
                exit();
            }
            else {
                header("Location: $error?status=$msg");
                exit();
            }
        }
        else {
            $msg =  urlencode(translateFN('inserimento nuovo corso riuscito'));

            if ($course['xml']) {
                $xml = urlencode($course['xml']);
                header("Location: $success?id=$course_id&xml=$xml&status=$msg");
                die();
            }
            else {
                // Inserimento nodo principale!
                $dataHa['type'] = ADA_GROUP_TYPE;
                $dataHa['icon']='gruppo.png';
                $dataHa['id']=$course_id."_0";
                $dataHa['parent_id']= "";
                $dataHa['id_node_author']= $sess_id_user;
                $dataHa['creation_date']=$ymdhms;
                $dataHa['family'] = $node_family;
                $dataHa['name'] = translateFN("Principale");
                $dataHa['title'] = $course['titolo'];
                $dataHa['pos_x0']=0;
                $dataHa['pos_x1']=0;
                $dataHa['pos_y0']=0;
                $dataHa['pos_y1']=0;
                $result = $dh->add_node($dataHa);
                if (AMA_DataHandler::isError($result)) {
                    // $dati = $result->getMessage();
                    $msg =  $result->getMessage();
                    if ($result->code == -4) {
                        $status .= " " . translateFN('Il corso esiste!');
                        header("Location: $self.php?status=$status");
                        exit();
                    }
                    else {
                        header("Location: $error?status=$msg");
                        exit();
                    }
                }
                else {
                    /*
                     * Il corso è stato creato correttamente,
                     * inserisce ed associa il corso tra i servizi del provider
                     */
                     $common_dh = $GLOBALS['common_dh'];
                     $service_dataAr['service_name'] = $course['nome'];
                     $service_dataAr['service_description'] = $course['descr'];
                     $service_dataAr['service_level'] = 99;
                     $service_dataAr['service_duration'] = 9999;
                     $service_dataAr['service_min_meetings'] = 1;
                     $service_dataAr['service_max_meetings'] = 999;
                     $service_dataAr['service_meeting_duration'] = 7200;
                     $service_id = $common_dh->add_service($service_dataAr);
                     if (AMA_Common_DataHandler::isError($service_id)) {
                        $service_id = NULL;
                        $msg .= ' Aggiunta servizio non riuscita';
                     }
                     else {
                        $selected_tester = $_SESSION['sess_selected_tester'];
                        $testerId = $GLOBALS['testers_dataAr'][$selected_tester];
                        $link_service = $common_dh->link_service_to_course($testerId, $service_id, $course_id );
                        if (AMA_Common_DataHandler::isError($link_service)) {
                            $msg .= ' Associazione servizio non riuscita';
                        }
                     }
                    /*
                     * Il corso e' stato creato correttamente, redirezioniamo
                     * l'utente allla vista del nodo principale del corso
                     * appena creato.
                    */
                    $root_node_id = $course_id.'_0';
                    $redirect_to = $http_root_dir.'/browsing/view.php?id_course='.$course_id.'&id_node='.$root_node_id;

                    header("Location: $redirect_to");
                    exit();
                }
            }
        }
        // header("Location: $success?status=$msg");
    }
}
else {
    // retrieve authors' data
    //$dh = AMA_DataHandler::instance();
    $author = array(
            array($sess_id_user,$user_name,"")
    );

    // visualizzazione form di input
    $op = new htmladmoutput();
    $is_author = 1;
    $home = "author.php";
    $dati = $op->form_addcourse("add_course.php",$home,$author,$is_author);
}

// preparazione output HTML e print dell' output
$content_dataAr = array(
    'menu'      => $menu,
    'banner'    => $banner,
    'dati'      => $dati,
    'help'      => $help,
    'status'    => $status,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages'  => $user_messages->getHtml(),
    'agenda'    => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);