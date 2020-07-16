<?php
/**
 * File uploader
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
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_AUTHOR => array('layout','course'),
  AMA_TYPE_TUTOR => array('layout','course'),
  AMA_TYPE_STUDENT => array('layout','course')
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

include_once ROOT_DIR.'/include/upload_funcs.inc.php';
//var_dump($_SESSION);

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';

if (isset($err_msg)) {
    $status = $err_msg;
} else {
    $status = translateFN('Invio documenti allegati ad un nodo');
}

$help = translateFN('Da qui lo studente pu&ograve; inviare un file da allegare al nodo corrente');

/*
 * vito, modifica all'upload dei file per l'upload dei file dall'editor dei nodi
 */

//if ( defined('DEV_EDIT_NODE') )
if ( isset($_GET['caller']) && $_GET['caller'] == 'editor' )
{
    $dh = $GLOBALS['dh'];
    /*
     * dati passati dal form di upload del file
     */
    $course_id          = $_POST['course_id'];
    $course_instance_id = $_POST['course_instance_id'];
    $user_id            = $_POST['user_id'];
    $node_id            = $_POST['node_id'];
    /*
     * dati relativi al file uploadato
     */
    $filename          = $_FILES['file_up']['name'];
    $source            = $_FILES['file_up']['tmp_name'];
    $file_size         = $_FILES['file_up']['size'];
    //$file_type         = $_FILES['file_up']['type'];
    $file_upload_error = $_FILES['file_up']['error'];
    // contiene il codice di errore da restituire al chiamante
    $error_code = 0;
    $ada_filetype = -1;

    /*
     * Obtain the uploaded file's mimetype
     */
    if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if($finfo === false) {
           $file_type = false;
        } else {
            $file_type = finfo_file($finfo, $source);
        }
    } else {
        $file_type = mime_content_type($_FILES['file_up']['type']);
    }

    /*
     * codice esistente:
     */

    $course_ha = $dh->get_course($course_id);//$id_course);
    if (AMA_DataHandler::isError($course_ha)){
        $msg = $course_ha->getMessage();
        header("Location: " . $http_root_dir . "/browsing/student.php?status=$msg");
		exit();
    }
    // look for the author, starting from author's id
    $author_id = $course_ha['id_autore'];
    //il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
    if (isset($_POST['media_path']) && $_POST['media_path'] != '') {
        $media_path = $_POST['media_path'];
    } elseif ($course_ha['media_path'] != "") {
        $media_path = $course_ha['media_path'];
    } else  {
//        $media_path = MEDIA_PATH_DEFAULT . $author_id ;
        $media_path = MEDIA_PATH_DEFAULT . $user_id ;
    }
    /*
     * fine codice esistente.
     */

    /*
     * controllo che la cartella indicata da $media_path esista e sia scrivibile
     */
    $upload_path = $root_dir . $media_path;
    if ( !is_dir($upload_path) || !is_writable($upload_path) )
    {
        // restituire un messaggio di errore e saltare la parte di scrittura del file
        $error_code = ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH;
    }
    else
    {
        // cartella di upload presente e scrivibile
        /*
         * controllo che sia stato inviato un file e che non si siano verificati errori
         * durante l'upload.
         */
    	$empty_filename = empty($filename);
    	$accepted_mimetype = ($ADA_MIME_TYPE[$file_type]['permission'] == ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE);
    	$accepted_filesize = ($file_size < ADA_FILE_UPLOAD_MAX_FILESIZE);

        if ( !$empty_filename && !$file_upload_error && $file_type !== false
             && $accepted_mimetype && $accepted_filesize )
        {
            /*
             * qui spostamento del file
             */
          // vito, 19 mar 2009, clean filename here.
          $filename = strtr($filename, array(' ' => '_', '\'' => '_'));


            //echo 'tutto ok';
           if ( $id_profile == AMA_TYPE_AUTHOR )
           {
               $filename_prefix = '';
           }
           else
           {
              /*
               * vito, 30 mar 2009:
               * in case this file has been uploaded by a tutor or by a student,
               * build the prefix for the uploaded filename adding the ADA type
               * of the uploaded file.
               */
              $uploaded_file_type = $ADA_MIME_TYPE[$file_type]['type'];

              $filename_prefix = $course_instance_id .'_'. $user_id .'_'. $node_id .'_' . $uploaded_file_type .'_';

           }
           $destination = $upload_path . DIRECTORY_SEPARATOR . $filename_prefix . $filename;

           /*
            * se esiste gia' un file con lo stesso nome di quello che stiamo
            * caricando, rinominiamo il nuovo file.
            * es. pippo.txt -> ggmmaa_hhmmss_pippo.txt
            */
           if ( is_file($destination) && isset($_POST['overwrite']) && $_POST['overwrite'] == false)
           {
               $date = date('dmy_His');
               $filename  = $date.'_'.$filename;
               $destination = $upload_path . DIRECTORY_SEPARATOR . $filename_prefix . $filename;
           }

           /*
     		* codice esistente:
     		*/
           $file_move = upload_file($_FILES, $source, $destination);

           if ($file_move[0] == "no")
           {
               // restituisco l'errore di problemi in upload_file
               $error_code = ADA_FILE_UPLOAD_ERROR_UPLOAD;
           }
           /*
     		* fine codice esistente:
     		*/
           /*
            * Se il file e' stato uploadato correttamente , inserisco il file come risorsa collegata all'autore
            * nella tabella risorse_nodi
            */
            $ada_filetype = isset($ADA_MIME_TYPE[$file_type]['type']) ? $ADA_MIME_TYPE[$file_type]['type'] : null;
            $res_ha = array(
                'nome_file' => $filename_prefix.$filename,
                'tipo'      => $ada_filetype, //array associativo definito in ada_config.php
                'copyright' => 0,
                'id_utente' => $user_id);

            $result = $dh->add_only_in_risorsa_esterna($res_ha);
            if( AMA_DataHandler::isError($result) ) return $result;
        }
        else if ( $empty_filename )
        {
            // questo lo posso gestire da javascript, comunque lascio il controllo anche qui
            //echo 'filename non passato';
            echo $filename;
        }
        else if ( $file_upload_error )
        {
            // restituisco l'errore verificatosi durante l'upload
            // codice di errore definito da PHP, al momento in [1,8]
            $error_code = $file_upload_error;
        }
        else if ($file_type === false)
        {
            $error_code = ADA_FILE_UPLOAD_ERROR_MIMETYPE;
        }
        else if ( !$accepted_mimetype )
        {
            // restituisco l'errore di mimetype non accettato
            $error_code = ADA_FILE_UPLOAD_ERROR_MIMETYPE;
        }
        else if ( !$accepted_filesize )
        {
            // restituisco l'errore di dimensione del file non accettata
            $error_code = ADA_FILE_UPLOAD_ERROR_FILESIZE;
        }
    }

//echo $error_code;
?>
<script type="text/javascript">
	var error    = <?php echo $error_code; ?>;
	var filename = '<?php echo $filename_prefix.$filename; ?>';
	var filetype = <?php echo $ada_filetype; ?>;
	window.parent.exitUploadFileState(error, filename, filetype);
</script>
<?php
exit();
}
/*
 * upload di un file da Collabora:invia file
 */
else if($id_profile == AMA_TYPE_STUDENT || $id_profile == AMA_TYPE_TUTOR || $id_profile == AMA_TYPE_AUTHOR){

    $id_node = $_SESSION['sess_id_node'];
    $id_course = $_SESSION['sess_id_course'];
    $id_course_instance = $_SESSION['sess_id_course_instance'];


  if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    /*
     * dati passati dal form di upload del file
     */
    $course_id          = $_POST['id_course'];
    $course_instance_id = $_POST['id_course_instance'];
    $user_id            = $_POST['sender'];
    $node_id            = $_POST['id_node'];
    /*
     * dati relativi al file uploadato
     */
    $filename          = $_FILES['file_up']['name'];
    $source            = $_FILES['file_up']['tmp_name'];
    $file_size         = $_FILES['file_up']['size'];
    $file_type         = mime_content_type($source);
    // $_FILES['file_up']['type'];
    $file_upload_error = $_FILES['file_up']['error'];
    // contiene il codice di errore da restituire al chiamante
    $error_code = 0;
    $ada_filetype = -1;
	/*
     * codice esistente:
     */

    $course_ha = $dh->get_course($id_course);
    $course_title = $course_ha['titolo'];
    if (AMA_DataHandler::isError($course_ha)){
      $msg = $course_ha->getMessage();
      header("Location: " . $http_root_dir . "/browsing/student.php?status=$msg");
    }

    // look for the author, starting from author's id
    $author_id = $course_ha['id_autore'];
    //il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
    if($course_ha['media_path'] != "") {
      $media_path = $course_ha['media_path']  ;
    }
    else {
      $media_path = MEDIA_PATH_DEFAULT . $author_id ;
    }
    /*
     * fine codice esistente.
     */

    /*
     * controllo che la cartella indicata da $media_path esista e sia scrivibile
     */
    $upload_path = $root_dir . $media_path;
    if ( !is_dir($upload_path) || !is_writable($upload_path) ) {
      // restituire un messaggio di errore e saltare la parte di scrittura del file
      $error_code = ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH;
      $error_message = translateFN('Upload del file non riuscito.');
      $error_message .= ' '.translateFN('Il percorso di destinazione non è scrivibile.');
      $form = UserModuleHtmlLib::uploadForm('upload.php', $sess_id_user, $id_course, $id_course_instance, $id_node, $error_message);
      $form = $form->getHtml();
    }
    else {
      // cartella di upload presente e scrivibile
      /*
       * controllo che sia stato inviato un file e che non si siano verificati errori
       * durante l'upload.
       */
	  $empty_filename = empty($filename);
	  $accepted_mimetype = isset($ADA_MIME_TYPE[$file_type]) && ($ADA_MIME_TYPE[$file_type]['permission'] == ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE);
	  $accepted_filesize = ($file_size < ADA_FILE_UPLOAD_MAX_FILESIZE);

      if ( !$empty_filename && !$file_upload_error &&
            $accepted_mimetype && $accepted_filesize ){
        /*
         * qui spostamento del file
         */
        // vito, 19 mar 2009, clean filename here.
        $filename = strtr($filename, array(' ' => '_', '\'' => '_'));

        //echo 'tutto ok';
        if ( $id_profile == AMA_TYPE_AUTHOR ) {
           $filename_prefix = '';
        }
        else {
        /*
         * vito, 30 mar 2009:
         * in case this file has been uploaded by a tutor or by a student,
         * build the prefix for the uploaded filename adding the ADA type
         * of the uploaded file.
         */
          $uploaded_file_type = $ADA_MIME_TYPE[$file_type]['type'];

          $filename_prefix = $course_instance_id .'_'. $user_id .'_'. $node_id .'_' . $uploaded_file_type .'_';
        }
        $destination = $upload_path . DIRECTORY_SEPARATOR . $filename_prefix . $filename;

       /*
        * se esiste gia' un file con lo stesso nome di quello che stiamo
        * caricando, rinominiamo il nuovo file.
        * es. pippo.txt -> ggmmaa_hhmmss_pippo.txt
        */
        if ( is_file($destination) ) {
          $date = date('dmy_His');
          $filename  = $date.'_'.$filename;
          $destination = $upload_path . DIRECTORY_SEPARATOR . $filename_prefix . $filename;
        }

       /*
   		* codice esistente:
   		*/
        $file_move = upload_file($_FILES, $source, $destination);

        if ($file_move[0] == "no") {
          // restituisco l'errore di problemi in upload_file
          $error_code = ADA_FILE_UPLOAD_ERROR_UPLOAD;
        }
       /*
   		* fine codice esistente:
		*/
        if($error_code != 0) {
          // gestire stampa del messaggio di errore
          $error_message = translateFN('Upload del file non riuscito.');

          $form = UserModuleHtmlLib::uploadForm('upload.php', $sess_id_user, $id_course, $id_course_instance, $id_node, $error_message);
          $form = $form->getHtml();
        }
        else {
          // redirige l'utente alla pagina da cui è arrivato all'upload.
          $navigation_history = $_SESSION['sess_navigation_history'];
          $last_visited_node  = $navigation_history->lastModule();
          /**
           * Must ask user what she wants to do.
           * This is done with a modal dialog, jQuery is needed
           */

          $layout_dataAr['JS_filename'] = array(
					JQUERY,
					JQUERY_UI,
					JQUERY_NO_CONFLICT
		  );

		  $layout_dataAr['CSS_filename'] = array(
					JQUERY_UI_CSS
		  );

		  $askOptions['title'] = translateFN('File caricato con successo');
	      $askOptions['message']  = translateFN('Cosa vuoi fare ora?');
		  $askOptions['buttons'][] = array ('label' => translateFN ('Torna al Corso'),
											'action'=>HTTP_ROOT_DIR.'/browsing/view.php?id_node='.$id_node,
											'icon'=>'ui-icon-arrowrefresh-1-w');
		  $askOptions['buttons'][] = array ('label' => translateFN ('Carica un altro file'),
											'action'=>$_SERVER['PHP_SELF'] ,
											'icon'=>'ui-icon-circle-arrow-n');
		  $askOptions['buttons'][] = array ('label' => translateFN('Vai all\'elenco dei file'),
											'action'=>HTTP_ROOT_DIR.'/browsing/download.php',
											'icon'=>'ui-icon-folder-open');

		  $optionsAr['onload_func']  = "askActionToUser('".rawurlencode(json_encode($askOptions))."');";

          // header("Location: $last_visited_node");
          // exit();
        }
      }
      else {
        $error_message = translateFN('Upload del file non riuscito.');

        if(!$accepted_filesize) {
          $error_message .= translateFN('La dimensione del file supera quella massima consentita.');
        }
        else if(!$accepted_mimetype) {
          $error_message .= translateFN('Il tipo di file inviato non &egrave; tra quelli accettati dalla piattaforma.').' '.$file_type;
        }

        $form = UserModuleHtmlLib::uploadForm('upload.php', $sess_id_user, $id_course, $id_course_instance, $id_node, $error_message);
        $form = $form->getHtml();
      }
    }
  }
  else {
    $form = UserModuleHtmlLib::uploadForm('upload.php', $sess_id_user, $id_course, $id_course_instance, $id_node);
    $form = $form->getHtml();
  }

  $nodeObj = read_node_from_DB($id_node);
  if(!AMA_DataHandler::isError($nodeObj)) {
     $node_title = $nodeObj->name;
     $node_version = $nodeObj->version;
     $node_date = $nodeObj->creation_date;
     $authorHa = $nodeObj->author;
     $node_author = $authorHa['username'];
     $node_level = $nodeObj->level;
     $node_keywords = ltrim($nodeObj->title);
     $node_path = $nodeObj->findPathFN();
  }


  $content_dataAr = array(
    //'head'         => $head_form,
    //'banner'       => $banner,
    'form'         => isset($form) ? $form : '',
    'status'       => $status,
    'user_name'    => $user_name,
    'user_type'    => $user_type,
    'messages'     => $user_messages->getHtml(),
    'agenda'       => $user_agenda->getHtml(),
    'title'        => $node_title,
    'version'      => $node_version,
    'date'         => $node_date,
    'author'       => $node_author,
    'level'        => $node_level,
    'keywords'	   => $node_keywords,
    'course_title' => $course_title,
    'path'         => $node_path
    //'node_medias'  => $node_medias,
    //'node_links'   => $media_links
  );

  /* 5.
  HTML page building
  */



  ARE::render($layout_dataAr, $content_dataAr,NULL,isset($optionsAr) ? $optionsAr : null);
}
/*
 * L'autore e l'amministratore non possono utilizzare il modulo collabora,
 * pertanto li rimandiamo alla pagina da cui provengono.
 */
else {
  $navigation_history = $_SESSION['sess_navigation_history'];
  $last_visited_node  = $navigation_history->lastModule();
  header("Location: $last_visited_node");
  exit();
}
?>