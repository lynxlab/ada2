<?php
/**
 * Download Area
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */

use Lynxlab\ADA\Module\CollaboraACL\AMACollaboraACLDataHandler;
use Lynxlab\ADA\Module\CollaboraACL\CollaboraACLActions;
use Lynxlab\ADA\Module\CollaboraACL\FileACL;

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout','node','course','course_instance'),
  AMA_TYPE_STUDENT => array('layout','node','course','course_instance')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

$self =  whoami();

include_once 'include/browsing_functions.inc.php';

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
BrowsingHelper::init($neededObjAr);

include_once ROOT_DIR.'/include/upload_funcs.inc.php';
include_once ROOT_DIR.'/include/Course.inc.php';



/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';

if (isset($err_msg)) {
    $status = $err_msg;
} else {
    $status = translateFN('Area di condivisione files');
}

$help = translateFN('Da qui lo studente pu&ograve; inviare un file da allegare al nodo corrente');

$id_node = $_SESSION['sess_id_node'];
$id_course = $_SESSION['sess_id_course'];
$id_course_instance = $_SESSION['sess_id_course_instance'];

// ******************************************************
// get user object
$userObj = $_SESSION['sess_userObj'];
if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {
               $id_profile = $userObj->tipo;
               $user_name =  $userObj->username;
               $user_name_name = $userObj->nome;
               $user_type = $userObj->convertUserTypeFN($id_profile);
               $user_family = $userObj->template_family;
               $userHomePage =   $userObj->getHomePage();
               if ($id_profile==AMA_TYPE_STUDENT) {
	               $user_history = $userObj->history;
	               $user_level = $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
               }
}  else {
               $errObj = new ADA_error(translateFN("Utente non trovato"),translateFN("Impossibile proseguire."));
}

$ymdhms = today_dateFN();

$help = translateFN("Da qui lo studente può scaricare i file allegati ai nodi");

$banner = include ("$root_dir/include/banner.inc.php");

// ******************************************************
// get course object
    $courseObj = $_SESSION['sess_courseObj'];
    if ( $courseObj instanceof Course) {
        $author_id = $courseObj->id_autore;
    } else {
      header("Location: " . $userHomePage);
    }

    //il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
    if($courseObj->media_path != "") {
      $media_path = $courseObj->media_path;
    }
    else {
      $media_path = MEDIA_PATH_DEFAULT . $author_id ;
    }
    $download_path = $root_dir . $media_path;

if (isset($_GET['file'])){
    $complete_file_name = $_GET['file'];
    $filenameAr = explode('_',$complete_file_name);
    $stop = count($filenameAr)-1;
    $course_instance = $filenameAr[0];
    $id_sender  = $filenameAr[1];
    $id_node =  $filenameAr[2]."_".$filenameAr[3];
    $filename = "";
    for ($k = 5; $k<=$stop;$k++){
        $filename .=  $filenameAr[$k];
        if ($k<$stop)
           $filename .= "_";
    }
    $mimetype = mime_content_type($download_path . DIRECTORY_SEPARATOR . $complete_file_name);
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
   // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");                          // HTTP/1.0
    //header("Content-Type: text/plain");
    header("Content-Description: File Transfer");
    if ($mimetype === 'application/octet-stream' || $mimetype === false) {
      header("Content-Type: application/force-download");
      header("Content-Disposition: attachment; filename=".basename($filename));
    } else {
      header("Content-Type: $mimetype");
      header("Content-Length: ".filesize($download_path . DIRECTORY_SEPARATOR . $complete_file_name));
    }
    @readfile($download_path . DIRECTORY_SEPARATOR . $complete_file_name);
    exit;

} else {

	// indexing files
	$elencofile = leggidir($download_path);
	if ($elencofile == NULL) {
//           $lista = translateFN("Nessun file inviato dagli studenti di questa classe.");
           $html = translateFN("Nessun file inviato dagli studenti di questa classe.");
	} else {
          if (defined('MODULES_COLLABORAACL') && MODULES_COLLABORAACL) {
            $aclDH = AMACollaboraACLDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
            $filesACL = $aclDH->findBy('FileACL', [ 'id_corso' => $id_course, 'id_istanza' => $id_course_instance, 'id_nodo' => $id_node ] );
            $elencofile = array_filter($elencofile, function($fileel) use ($filesACL, $userObj) {
              $elPath = str_replace(ROOT_DIR. DIRECTORY_SEPARATOR, '', $fileel['path_to_file']);
              return FileACL::isAllowed($filesACL, $userObj->getId(), $elPath, CollaboraACLActions::READ_FILE);
            });
            $aclDH->disconnect();

          }
  //          $fstop = count($elencofile);
  //          $lista ="<ol>";
        //  for  ($i=0; $i<$fstop; $i++){
//            $div = CDOMElement::create('div','id:file_sharing');
            $table = CDOMElement::create('table','id:file_sharing_table,class:'.ADA_SEMANTICUI_TABLECLASS);
//            $div->addChild($table);
            $thead = CDOMElement::create('thead');
            $tbody = CDOMElement::create('tbody');
            $tfoot = CDOMElement::create('tfoot');
            $table->addChild($thead);
            $table->addChild($tbody);

            $trHead = CDOMElement::create('tr');

            $thHead = CDOMElement::create('th','class: file');
            $thHead->addChild(new CText(translateFN('file')));
            $trHead->addChild($thHead);

            $thHead = CDOMElement::create('th','class: student');
            $thHead->addChild(new CText(translateFN('inviato da')));
            $trHead->addChild($thHead);

            $thHead = CDOMElement::create('th','class: date');
            $thHead->addChild(new CText(translateFN('data')));
            $trHead->addChild($thHead);

            $thHead = CDOMElement::create('th','class: node');
            $thHead->addChild(new CText(translateFN('nodo')));
            $trHead->addChild($thHead);

            if ($userObj->getType()==AMA_TYPE_TUTOR) {
            	$thHead = CDOMElement::create('th','class: node');
            	$thHead->addChild(new CText(translateFN('azioni')));
            	$trHead->addChild($thHead);
            }

            $thead->addChild($trHead);

            $i=0;
            foreach ($elencofile as $singleFile) {
                $i++;
        	 $data = date("d/m/Y",$singleFile['filemtime']);
         	 $complete_file_name = $singleFile['file'];
	         $filenameAr = explode('_',$complete_file_name);
	         $stop = count($filenameAr)-1;
	         $course_instance = isset($filenameAr[0]) ? $filenameAr[0] : null;
	         $id_sender  = isset($filenameAr[1]) ? $filenameAr[1] : null;
	         $id_course = isset($filenameAr[2]) ? $filenameAr[2] : null;
                 if ($course_instance == $sess_id_course_instance  && $id_course == $sess_id_course) {
                     if (is_numeric($id_sender)) {
                             $id_node =  $filenameAr[2]."_".$filenameAr[3];
                             $filename = '';
                             for ($k = 5; $k<=$stop;$k++){
                                  $filename .=  $filenameAr[$k];
                                  if ($k<$stop)
                                    $filename .= "_";
                             }
                            $sender_array = $common_dh->get_user_info($id_sender);
                            if(!AMA_Common_DataHandler::isError($sender_array)) {
                                $id_profile = $sender_array['tipo'];
                                switch ($id_profile){
                                    case   AMA_TYPE_STUDENT:
                                    case   AMA_TYPE_AUTHOR:
                                    case   AMA_TYPE_TUTOR:
                                          $user_name_sender =  $sender_array['username'];
                                          $user_surname_sender =  $sender_array['cognome'];
                                          $user_name_sender = $sender_array['nome'];
                                          $user_name_complete_sender = $user_name_sender .' ' . $user_surname_sender;

                                          /**
                                           * @todo verificare a cosa serve $fid_node. Apparentemente non usato
                                           */
                                          if (!isset($fid_node) OR ($fid_node == $id_node)) {
                                                $out_fields_ar = array('nome');
                                                $clause = "ID_NODO = '$id_node'";
                                                $nodes = $dh->_find_nodes_list($out_fields_ar, $clause);
                                                if(!AMA_DB::isError($nodes)) {
                                                    foreach ($nodes as $single_node) {
                                                        $id_node = $single_node[0];
                                                        $node_name = $single_node[1];
                                                    }
                                                }
                                                $tr = CDOMElement::create('tr','id:row'.$i);
                                                $tbody->addChild($tr);

                                                $td = CDOMElement::create('td');
                                                $td->addChild(new CText('<a href="download.php?file='.$complete_file_name.'" target=_blank>'.$filename.'</a> '));
                                                $tr->addChild($td);

                                                $td = CDOMElement::create('td');
                                                $td->addChild(new CText($user_name_complete_sender));
                                                $tr->addChild($td);

                                                $td = CDOMElement::create('td');
                                                $td->addChild(new CText($data));
                                                $tr->addChild($td);

                                                $td = CDOMElement::create('td');
                                                $td->addChild(new CText('<a href=../browsing/view.php?id_node='.$id_node.'>'.$node_name.'</a>'));
                                                $tr->addChild($td);

                                                if ($userObj->getType()==AMA_TYPE_TUTOR) {
                                                	$td = CDOMElement::create('td');
                                                    $buttonDel = CDOMElement::create('button','class:ui icon button deleteButton');
                                                    $buttonDel->addChild(CDOMElement::create('i','class:trash icon'));
	                                                	$buttonDel->setAttribute('onclick','javascript:deleteFile(\''.rawurlencode(translateFN('Confermi la cancellazione del file').' '.$filename.' ?').'\',\''.rawurlencode($complete_file_name).'\',\'row'.$i.'\');');
	                                                	$buttonDel->setAttribute('title',translateFN('Clicca per cancellare il file'));
                                                  $td->addChild($buttonDel);
                                                  if (defined('MODULES_COLLABORAACL') && MODULES_COLLABORAACL) {
                                                    $aclId = FileACL::getIdFromFileName($filesACL, $singleFile['path_to_file']);
                                                    if (!is_null($aclId)) {
                                                      $aclObj = FileACL::getObjectById($filesACL, $aclId);
                                                    } else {
                                                      $aclObj = null;
                                                    }
                                                    if ((is_null($aclObj) && $id_sender == $userObj->getId()) || (!is_null($aclObj) && $aclObj->getId_owner() == $userObj->getId())) {
                                                      $buttonACL = CDOMElement::create('button','class:ui icon button aclButton');
                                                      $buttonACL->addChild(CDOMElement::create('i','class:basic add user icon'));
                                                      $buttonACL->setAttribute('title',translateFN('Imposta chi può vedere il file'));
                                                      $buttonACL->setAttribute('data-filename',rawurlencode($complete_file_name));
                                                      $buttonACL->setAttribute('data-course-id',$id_course);
                                                      $buttonACL->setAttribute('data-instance-id',$id_course_instance);
                                                      $buttonACL->setAttribute('data-node-id',$id_node);
                                                      $buttonACL->setAttribute('data-owner-id',$userObj->getId());
                                                      $buttonACL->setAttribute('data-file-acl-id',is_null($aclId) ? -1 : $aclId);
                                                      $td->addChild($buttonACL);
                                                    }
                                                  }
                                                	$tr->addChild($td);
                                                }

                                            }

                                            break;
                                    default:
                                        // errore
                                       $sender_error = 1;
                                }
                            }
                     }

                 }
           } // end foreach
           $html = $table->getHtml();
        }
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

$node_data = array(
               //               'data'=>$lista,
               'banner'=>$banner,
               'data'=>$html,
               'status'=>$status,
               'user_name'=>$user_name_name,
               'user_type'=>$user_type,
               'status'=>$status,
               'user_level'=>$user_level,
               'messages'=>$user_messages->getHtml(),
               'agenda'=>$user_agenda->getHtml(),
               'edit_profile'=> $userObj->getEditProfilePage(),
               'title'=>$node_title,
               'course_title'=>isset($course_title) ? $course_title : null,
               'path'=>$nodeObj->findPathFN(),
               'help'=>$help,
               'last_visit' => $last_access
               );


/* 5.
  HTML page building
  */

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
	);
$layout_dataAr['CSS_filename']= array(
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS,
	);
  $render = null;
  $options['onload_func'] = 'initDoc()';

  if (defined('MODULES_COLLABORAACL') && MODULES_COLLABORAACL && $userObj->getType()==AMA_TYPE_TUTOR) {
    $layout_dataAr['CSS_filename'][] = MODULES_COLLABORAACL_PATH . '/layout/ada-blu/css/moduleADAForm.css';
    array_splice( $layout_dataAr['JS_filename'], count($layout_dataAr['JS_filename']) - 1 , 0, [ MODULES_COLLABORAACL_PATH . '/js/multiselect.min.js' ] );
    $layout_dataAr['JS_filename'][] = MODULES_COLLABORAACL_PATH . '/js/collaboraaclAPI.js';
    $layout_dataAr['JS_filename'][] = MODULES_COLLABORAACL_PATH . '/js/download.js';
    $dataForJS = [
      'url' => MODULES_COLLABORAACL_HTTP,
    ];
    $options['onload_func'] .= '; initCollabora('.htmlentities(json_encode($dataForJS), ENT_COMPAT, ADA_CHARSET).');';
  }

  $imgAvatar = $userObj->getAvatar();
  $avatar = CDOMElement::create('img','src:'.$imgAvatar);
  $avatar->setAttribute('class', 'img_user_avatar');

  $node_data['user_modprofilelink'] = $userObj->getEditProfilePage();
  $node_data['user_avatar'] = $avatar->getHtml();
  ARE::render($layout_dataAr, $node_data, $render, $options);
