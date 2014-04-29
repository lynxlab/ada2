<?php
/**
 * EDIT NODE.
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
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
require_once 'include/author_functions.inc.php';

/*
 * YOUR CODE HERE
*/
require_once CORE_LIBRARY_PATH.'/includes.inc.php';
require_once ROOT_DIR.'/include/form/phpOpenFormGen.inc.php';
require_once ROOT_DIR.'/admin/include/htmladmoutput.inc.php';
require_once 'include/editnode_funcs.inc.php';
require_once 'include/NodeEditing.inc.php';
require_once '../browsing/include/CourseViewer.inc.php';

if ($id_profile == 0 || ($id_profile != AMA_TYPE_TUTOR && $id_profile != AMA_TYPE_AUTHOR && $id_profile != AMA_TYPE_STUDENT)) {
    $errObj = new ADA_Error(NULL, translateFN('Utente non autorizzato, impossibile proseguire.'));
}


$level = 0; // default
$chat_link = "";

$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);

if (!isset($op)) {
    $op='edit';
}

$help = translateFN("Da qui l'autore di un nodo o di una nota ne pu&ograve; modificare le propriet&agrave;");

// MAIN: delete,copy,preview,edit

// vito 16 gennaio 2009
$form = NULL;
//vito, 20 feb 2009
$icon  = '';
$body_onload = "";

switch ($op) {
    case 'delete':
        if (isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] == 'POST'
                AND isset($id_node) AND isset($parent_id)) {
            // vito 16 gennaio 2009
            $result = $dh->remove_node($id_node); //$id_user);  passare anche lo userid perch�se ne tenga traccia ?
            $message = urlencode(translateFN("Nodo eliminato"));
            // vito, 9 mar 2009, $parent_id
            header("Location: " . $http_root_dir . "/browsing/view.php?id_node=$parent_id&msg=$message");
            exit();
        }
        else {
            $self="author"; // per il templates
            $action = "edit_node";
            $data = delete_nodeFN($id_node,$id_course,$action);
        }
        break;
    case 'copy':
        if (isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] == 'POST'
                AND isset($new_id_node) ) {
            $nodeObj = read_node_from_DB($sess_id_node);
            if (is_object($nodeObj)) {
                $nodeObj->copy($new_id_node);
                $new_nodeObj = read_node_from_DB($new_id_node);
                if (is_object($new_nodeObj)) {
                    $message = urlencode(translateFN("Nodo copiato"));
                    header("Location: " . $http_root_dir . "/browsing/view.php?cachemode=updatecache&id_node=$new_id_node&msg=$message");
                }
            }
        }
        else {
            $self="author"; // per il templates
            $action = "edit_node";
            $status = translateFN("Copia del nodo");
            $data = copy_nodeFN($id_node,$id_course,$action);

        }
        break;

    /*
     * vito, 17 nov 2008: promote noTe to noDe.
     * A Tutor now suggests the promotion af a noTe to the author of the course, which
     * will eventually promote the noTe.
    */
    case 'suggest_publishing':
    /*
     * Get the page we're coming from
    */
    //$navigation_history = $_SESSION['sess_navigation_history'];
    //$last_page = $navigation_history->previousItem();

    /*
     * Only the tutor is allowed to suggest note promotion.
    */
        if ($id_profile != AMA_TYPE_TUTOR) {
            header("Location: $http_root_dir/browsing/view.php");
            exit();
        }
        /*
     * Obtain info about the course author
        */
        $course_data = $dh->get_course($id_course);
        if (AMA_DataHandler::isError($course_data)) {
            $errObj = new ADA_Error($course_data, translateFN("Errore nell'ottenimento delle informazioni sul corso."));
        }
        $course_author_id = $course_data['id_autore'];

        $author_data = $dh->get_author($course_author_id);
        if (AMA_DataHandler::isError($author_data)) {
            $errObj = new ADA_Error($author_data, translateFN("Errore nell'ottenimento delle informazioni sull'autore del corso."));
        }

        /*
     * Obtain note data
        */
        $note_data = $dh->get_node_info($id_node);
        if (AMA_DataHandler::isError($note_data)) {
            $errObj = new ADA_Error($note_data, translateFN("Errore nell'ottenimento dei dati relativi alla nota da promuovere"));
        }
        $note_title = $note_data['name'];
        /*
     * Prepare the text of the message
        */
        $message_text  = sprintf(translateFN("Il tutor %s segnala la seguente nota per la promozione a nodo del corso."), $user_name);
        $note_url = $http_root_dir.'/browsing/view.php?id_course='.$id_course.'&id_course_instance='.$id_course_instance.'&id_node='.$id_node;

        $link_to_note = CDOMElement::create('a',"href:$note_url");
        $link_to_note->addChild(new CText($note_title));

        $message_text .= $link_to_note->getHtml();

        $message_handler = MessageHandler::instance();

        $message_ha['destinatari'] = "{$author_data['username']}, $user_name";
        $message_ha['data_ora']    = "now";
        $message_ha['tipo']        = ADA_MSG_SIMPLE;
        $message_ha['mittente']    = $user_name;
        $message_ha['testo']       = $message_text;
        $message_ha['titolo']      = translateFN("Promozione di una nota a nodo");
        $message_ha['priorita']    = 2;

        $result = $message_handler->send_message($message_ha);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN("Errore nell'invio del messaggio di suggerimento promozione nota."));
        }
        $status = translateFN("Proposta di promozione inviata all'autore del corso");
        header("Location: $http_root_dir/browsing/view.php?status=$status");
        exit();

        break;

    case 'publish': // promote a noTe to noDe (only Tutors) or a private note to a public note (student/tutor)
    // if (isset($submit)){

        $nodeObj = read_node_from_DB($id_node);

        if (is_object($nodeObj) AND (!AMA_datahandler::isError($nodeObject))) {
            $node_type = $nodeObj->type;
            $node_name = $nodeObj->name;
            $node_ha = $nodeObj->object2arrayFN();

            switch ($type) {
                case ADA_PRIVATE_NOTE_TYPE : //  private notes  to forum notes
                    $node_ha['type'] = ADA_NOTE_TYPE;
                    $res = $dh->_edit_node($node_ha);
                    $message = urlencode(translateFN("Nota pubblicata nel forum"));
                    header("Location: " . $http_root_dir . "/browsing/view.php?cachemode=updatecache&id_node=$id_node&msg=$message");
                    exit();
                    break;

                case  ADA_NOTE_TYPE: // forum notes to nodes

                    $id_toc =  $sess_id_course."_".$courseObj->id_nodo_toc;
                    $parent_node_id = $node_ha['parent_id'];
                    $parent_node_type =  $node_ha['type'];
                    if (($parent_node_type == ADA_NOTE_TYPE) OR ($parent_node_type == ADA_PRIVATE_NOTE_TYPE)) {   // cannot attach a noDe to a noTe  !
                        $pathAr =  $nodeObj->findLogicalPathFN();
                        while (
                        ($parent_node_type == ADA_NOTE_TYPE) &&
                                ($id_toc != $parent_node_id)
                        ) {
                            $path_element = array_shift($pathAr);
                            $parent_node_id = $path_element[0];
                            $nodeObjTmp = read_node_from_DB($parent_node_id);
                            $parent_node_type = $nodeObjTmp->type;

                        }
                    }
                    if ($id_toc == $parent_node_id) {
                        $message = urlencode(translateFN("Non &egrave; possibile pubblicare questa nota."));
                        // header("Location: " . $http_root_dir . "/browsing/view.php?id_node=$id_node&msg=$message");
                    }
                    $node_ha['parent_id'] = $parent_node_id;
                    $node_ha['type'] = ADA_LEAF_TYPE;
                    $node_ha['id_instance'] = "";

                    $res = $dh->_edit_node($node_ha);
                    //$GLOBALS['debug']=1; mydebug(__LINE__,__FILE__,$res); $GLOBALS['debug']=0;
                    if (!AMA_datahandler::isError($res)) {
                        $message = urlencode(translateFN("Nota pubblicata nel corso"));
                        header("Location: " . $http_root_dir . "/browsing/view.php?cachemode=updatecache&id_node=$id_node&msg=$message");
                    }
                    else {
                        $authoObj = new ADAAuthor($course_author_id);
                        $author_name = $authoObj->username;
                        $destAr =  array($user_name);
                        /*$tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
                         $tutor = $dh->get_author($tutor_id);
                         $tutor_uname = $tutor['username'];*/
                        $mh = new MessageHandler();
                        $message_ha['destinatari'] = $destAr;
                        $message_ha['priorita'] = 1;
                        $message_ha['data_ora'] = "now";
                        $message_ha['titolo'] = translateFN("Nodo pubblicato nel corso");
                        $message_ha['testo'] = translateFN("Il tutor della classe ha ritenuto di intereesse per tutti la nota");
                        $message_ha['testo'] .= "<a href=\"$http_root_dir/browsing/view.php?id_node=$id_node\">$node_name</a>";
                        $message_ha['testo'] .= translateFN(" e ha provveduto a pubblicarla nel tuo corso.");
                        $message_ha['testo'] .= $course_title;
                        $message_ha['data_ora'] = "now";
                        $message_ha['mittente'] = $author_name;
                        // e-mail
                        // vito, 20 apr 2009
                        //                               $message_ha['tipo'] = ADA_MSG_MAIL;
                        //                               $res = $mh->send_message($message_ha);
                        // messaggio interno
                        $message_ha['tipo'] = ADA_MSG_SIMPLE;
                        $res = $mh->send_message($message_ha);

                    }
                    break;
            }
        }
        //} else {
        //$self="author"; // per il templates
        //$action = "edit_node";
        //$status = translateFN("Pubblicazione del nodo");
        //$data = copy_nodeFN($id_node,$id_course,$action);

        //}
        break;

    case 'preview':
    /*
           * Mostra l'anteprima del contenuto del nodo
    */
    //$self="edit_node"; // per il template
        $status = translateFN("Preview del nodo");
        //  $data = NodeEditingViewer::getPreviewForm('edit_node.php?op=edit','edit_node.php?op=save');
        $form = NodeEditingViewer::getPreviewForm('edit_node.php?op=edit','edit_node.php?op=save');

        /* vito, 20 feb 2009
           * usa i dati presenti nella sessione per mostrare l'anteprima del nodo
           * che si sta editando. E' il metodo NodeEditingViewer::getPreviewForm
           * che si occupa di passare i dati in $_POST nella sessione, pertanto è
           * necessario che questo sia invocato prima.
        */
        $content_dataAr = unserialize($_SESSION['sess_node_editing']['node_data']);
        $icon  = CourseViewer::getClassNameForNodeType($content_dataAr['type']);
        if($status == '') {
            $status = translateFN('Visualizzazione anteprima del nodo');
        }

        // vito, 20 apr 2009
        /*
          * Choose the right template for the preview
        */
        switch($content_dataAr['type']) {
            case ADA_NOTE_TYPE:
                $self = 'previewnote';
                break;
            case ADA_PRIVATE_NOTE_TYPE:
                $self = 'previewprivatenote';
                break;
            case ADA_GROUP_TYPE:
            case ADA_LEAF_TYPE:
            default:
                $self = 'preview';
                break;
        }
        $preview_additional_data = array(
                'title'      => $content_dataAr['name'],
                'version'    => $content_dataAr['version'],
                'author'     => $user_name,
                'node_level' => $content_dataAr['level'],
                'keywords'   => $content_dataAr['title'],
                'date'       => $content_dataAr['creation_date'],
                'edit_link'  => NodeEditingViewer::getEditLink('edit_node.php?op=edit'),
                'save_link'  => NodeEditingViewer::getSaveLink('edit_node.php?op=save')
        );
        break;

    case 'save':
    /*
               * Salvataggio delle modifiche apportate al nodo.
               * Determina i media da associare e disassociare, aggiorna  i media per il nodo
               * e salva le modifiche fatte al nodo.
    */

    /*
               * media associati al nodo prima delle modifiche
    */
        $previous_media = array();
        $previous_media = unserialize($_SESSION['sess_node_editing']['media_in_db']);
        /*
               * media trovati nel nodo dopo le modifiche
        */
        $current_media = array();
        $content_dataAr = unserialize($_SESSION['sess_node_editing']['node_data']);
        $current_media = NodeEditing::getMediaFromNodeText($content_dataAr['text']);
        /*
               * determino i media da disassociare e quelli da associare
        */
        foreach ( $previous_media as $media => $type ) {
            if ( isset($current_media[$media]) ) {
                unset($previous_media[$media]);
                unset($current_media[$media]);
            }
        }
        /*
               * se previous_media contiene degli elementi, sono elementi da disassociare dal nodo
               * se current_media  contiene degli elementi, sono elementi da associare al nodo
        */
        $result = NodeEditing::updateMediaAssociationsWithNode($_SESSION['sess_id_node'],$_SESSION['sess_id_user'],
                $previous_media, $current_media);
        if ( AMA_DB::isError($result) ) {
            $errObj = new ADA_Error($result,translateFN("Errore nell'associazione dei media con il nodo"));
        }
        /*
               * salvo le modifiche fatte al nodo
        */
        unset($content_dataAr['DataFCKeditor']);
        $result = NodeEditing::saveNode($content_dataAr);
        if ( AMA_DB::isError($result) ) {
            $errObj = new ADA_Error($result, translateFN('Errore durante il salvataggio delle modifiche al nodo'));
        }

        unset($_SESSION['sess_node_editing']);

        /* notifying all students of the editing
         * we should verify:
         * - that the platform allows for brodcasting the news
         * - the the user accept receiving the notification
         * - the way the user prefers to receive the notification
         */
        // fake configuration data, TO BE MOVED IN CONFIG_INSTALL //
         define(ADA_BROADCAST_UPDATE,1);
         define(ADA_BROADCAST_NOUPDATE,0);
         define(ADA_USER_AUTOMATIC_RECEIVE_UPDATE,1);
         define(ADA_USER_AUTOMATIC_DONOT_RECEIVE_UPDATE,0);
         define(ADA_NOTIFICATION_REALTIME,0);
         define(ADA_NOTIFICATION_DAILY,1);
         define(ADA_NOTIFICATION_WEEKLY,7);
         define(ADA_NOTIFICATION_MONTHLY,30);
         
         /* read the configuration for the platform installation (from config_install file...) */
          $broadcast_update = ADA_BROADCAST_UPDATE;
          /* read the configuration for the user (from profile...? now fixed to 1)*/
          $user_receive_updates = ADA_USER_AUTOMATIC_RECEIVE_UPDATE;
          /* read the preferred way of notification (from profile...? now fixed to mail*/
          $user_preferred_notification_channel = ADA_MSG_MAIL;
          /* notification interval (from config_install)(0= realtime; 1 = daily; 7 = weekly; 30 = monthly)  */
          $notification_interval = ADA_NOTIFICATION_REALTIME;
          
          // version
          /* we should add an option to the form to let the author choose if there have been an update of the version 
           * now it is forced to TRUE
           */
        //  if ($content_dataAr['version'] <> $nodeObj->version){
              $is_updated_version = TRUE;
        //  } else {
        //      $is_updated_version = FALSE;
        //  }
              
          
          if (
                  ($broadcast_update == ADA_BROADCAST_UPDATE) && 
                  ($user_receive_updates == ADA_USER_AUTOMATIC_RECEIVE_UPDATE) && 
                  ($is_updated_version)
                ){
              //...
               
            /* get the students subscribed to this course instance 
                 * 
                 * this is userful if we want use ths snippet of code form outside
                 *  here we use AMA get_students_for_course_instance() instead
                 */
              /*
                $tester = $_SESSION['sess_selected_tester'];
                $tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
                $tester_name = $tester_info_Ar[1];
                $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
                $students_Ar = $tester_dh->get_unique_students_for_course_instances($sess_id_course_instance);
               
               */
                if ($dh->course_has_instances($sess_id_course)){
                  $field_list_ar = array();
                  $course_instanceAr = $dh->course_instance_started_get_list($field_list_ar, $sess_id_course);
                  $students_Ar = array();
                  $res_course_instanceAr = array();
                  foreach ($course_instanceAr as $course_instance){
                    $id_course_instance = $course_instance[0];
                     if ($id_course_instance<> NULL){
                         $res_course_instanceAr[] = $id_course_instance;
                     }
                  }
                  $course_instance_students_Ar =  $dh->get_unique_students_for_course_instances($res_course_instanceAr);
                  foreach($course_instance_students_Ar as $course_instance_student){
                     $students_Ar[] = $course_instance_student['username'];
                  }
                   $destinatari = implode(',', $students_Ar);
                  
                   /* 
                     //get the sender: the admin??? 
                    
                     $admtypeAr = array(AMA_TYPE_ADMIN);
                     $admList = $dh->get_users_by_type($admtypeAr);
                     // $admList = $tester_dh-> get_users_by_type($admtypeAr); ???

                     if (!AMA_DataHandler::isError($admList)){
                                   $adm_uname = $admList[0]['username'];
                     } else {
                                   $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
                     }
                    * $sender =  $adm_uname;
                  */
                     $author_name = $userObj->username;
                     $sender =  $author_name;
                    /*
                 * Prepare the text of the message
                    */
                    $node_title = $content_dataAr['name'];

                    $base_text1 = translateFN("Gentile utente, ti segnaliamo che il nodo %s è stato aggiornato.");
                    $base_text2 = sprintf(translateFN("Please visit %s to see the new contents."), HTTP_ROOT_DIR."/browsing/user.php");
                    $footer_text = "\n"
                 				 . "\n"
                 				 . '-----'
                 				 . "\n"
                 				 . translateFN('This message has been sent to you by ADA. For additional information please visit the following address: ')
                 				 . "\n"
                 				 . HTTP_ROOT_DIR;
                    $node_url = $http_root_dir.'/browsing/view.php?id_course='.$sess_id_course.'&id_course_instance='.$id_course_instance.'&id_node='.$content_dataAr['id'];
                     
                    $message_text  = sprintf($base_text1, $node_title);                    
                    $message_text .= "\n".$node_url."\n\n".$base_text2.$footer_text;

                    $link_to_node = CDOMElement::create('a',"href:$node_url");
                    $link_to_node->addChild(new CText($node_title));
                    
                    $message_html = sprintf($base_text1, $link_to_node->getHtml());
                    $message_html .= "<br/><br/>".$base_text2.nl2br($footer_text);

                    if  ( $notification_interval == ADA_NOTIFICATION_REALTIME ){
                    	
                    	// require phpmailer
                    	require_once ROOT_DIR.'/include/phpMailer/class.phpmailer.php';
                    	require_once ROOT_DIR.'/include/data_validation.inc.php';
                    	
                    	/**
                    	 * Send the message an email message
                    	 * via PHPMailer
                    	 */
                    	$phpmailer = new PHPMailer();
                    	$phpmailer->CharSet = 'UTF-8';
                    	$phpmailer->IsSendmail();
                    	$phpmailer->SetFrom(ADA_NOREPLY_MAIL_ADDRESS);
                    	$phpmailer->IsHTML(true);
                    	$phpmailer->Priority = 2;
                    	$phpmailer->Subject = PORTAL_NAME.' - '.translateFN("Aggiornamento dei contenuti del corso");
                    	
                    	$phpmailer->AddAddress(ADA_NOREPLY_MAIL_ADDRESS);
                    	foreach ($students_Ar as $destinatario) {
                    		/**
                    		 * TODO: should check if $user_preferred_notification_channel
                    		 *       for current iteration user is ADA_MSG_MAIL. As of
                    		 *       29/apr/2014 this feature is not supported and every student
                    		 *       shall receive the notification by email only.
                    		 */
                    		if (DataValidator::validate_email($destinatario)) {
                    			$phpmailer->AddBCC($destinatario);
                    		}
                    		
                    	}
                    	
                    	$phpmailer->Body = $message_html;
                    	$phpmailer->AltBody = $message_text;
                    	if (!$phpmailer->Send()) {
                    		$result = new AMA_Error(AMA_ERR_SEND_MSG);
                    	} else {
                    		$result = true;
                    	}
                    	
                          //$message_handler = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
                          
//                           $message_handler = MessageHandler::instance();
//                           $message_ha['destinatari'] =  $destinatari ; 
//                           $message_ha['data_ora']    = "now";
//                           $message_ha['tipo']        = $user_preferred_notification_channel;
//                           $message_ha['mittente']    = $sender; // author??
//                           $message_ha['testo']       = $message_text;
//                           $message_ha['titolo']      = translateFN("Aggiornamento dei contenuti del corso");
//                           $message_ha['priorita']    = 2;
                          
//                           $result = $message_handler->send_message($message_ha);
                          
                          if (AMA_DataHandler::isError($result)) {
                              $errObj = new ADA_Error($result, translateFN("Errore nell'invio del messaggio di notifica dell'aggiornamento."));
                          }
                      } else {
                          // we should add to a list of programmed notifications, create a module that is called by CRON, ... etc
                      }

                  }     

              } 
        
      
        // end notification
        header("Location: $http_root_dir/browsing/view.php?cachemode=updatecache&id_node={$content_dataAr['id']}");
        exit();
        //    $data['form'] = translateFN("Le modifiche al nodo sono state salvate correttamente.");
        //    $self="edit_node";
        break;

    case 'edit':
    default:
        $self="edit_node"; // per il template
        $action = "edit_node";

        $body_onload = "switchToFCKeditor('$template_family');";
        /*
               * Mostra la pagina per l'editing del nodo.
        */
        /*
               * Verifica la pagina da cui proviene l'utente.
               * Se l'utente proviene da una pagina diversa da edit_node.php e i dati relativi all'editing
               * del nodo sono presenti in sessione, si tratta di dati non salvati, quindi non dovrebbero
               * essere mostrati. Al momento faccio l'unset della sessione.
               * I dati relativi alla navigazione in ADA sono gestiti da un oggetto di navigazione mantenuto
               * nella variabile di sessione $sess_navigation_history.
        */
        $navigation_history = $_SESSION['sess_navigation_history'];
        /* vito, 24 apr 2009
               * Save the page from which the user select the add node operation
               * so that, if he cancels the editing operation, we can redirect
               * him there.
        */
        if(strcmp($navigation_history->previousItem(), __FILE__) !== 0) {
            $_SESSION['page_to_load_on_cancel_editing'] = $navigation_history->previousPage();
        }

        if ( !isset($_SESSION['sess_node_editing']['node_data'])  ||
                ($need_to_unset_session = strcmp($navigation_history->previousItem(), __FILE__)) !== 0) {
            if ( $need_to_unset_session !== 0 ) {
                unset($_SESSION['sess_node_editing']);
            }

            $media_found = array();
            $node_to_edit = getNodeData($id_node);
            $media_found = NodeEditing::getMediaFromNodeText($node_to_edit['text']);
            $_SESSION['sess_node_editing']['media_in_db'] = serialize($media_found);
        }
        else {
            $node_to_edit = unserialize($_SESSION['sess_node_editing']['node_data']);
            unset($_SESSION['sess_node_editing']['node_data']);
        }
        /*
               * Ottiene le preferenze di visualizzazione per l'editor
        */
        $flags  = PreferenceSelector::getPreferences($id_profile,$node_to_edit['type'],EDIT_OPERATION,$ADA_ELEMENT_VIEWING_PREFERENCES);
        /*
               * Mostra l'editor
        */
        //    $data   = NodeEditingViewer::getEditingForm($action, $id_course, $sess_id_course_instance, $sess_id_user, $node_to_edit, $flags);
        $form   = NodeEditingViewer::getEditingForm($action, $id_course, $sess_id_course_instance, $sess_id_user, $node_to_edit, $flags);
        $status = translateFN("Modifica del nodo");
        /* vito, 20 feb 2009
               * usa i dati presenti nella sessione per mostrare alcune informazioni relative al nodo
               * che si sta editando
        */
        $icon  = CourseViewer::getClassNameForNodeType($node_to_edit['type']);
        $title = Utilities::getEditingFormTitleForNodeType($node_to_edit['type']);
        if ($status == '') {
            $status = $title;
        }
        $version = $node_to_edit['version'];
        $author = $user_name;
        $node_level = $node_to_edit['level'];
        $keywords = $node_to_edit['title'];
        $creation_date = $node_to_edit['creation_date'];
        $edit_link = '';
        $save_link = '';
        $content_dataAr_and_buttons_CSS_class = 'hide_node_data';
        // vito, 20 apr 2009
        $preview_additional_data = array(
                'title'      => $title,
                'version'    => $node_to_edit['version'],
                'author'     => $user_name,
                'node_level' => $node_to_edit['level'],
                'keywords'   => $node_to_edit['title'],
                'date'       => $node_to_edit['creation_date']
        );
}

if (is_object($data)) {
    $msg = urlencode($data->message);
    header("Location: " . $http_root_dir . "/browsing/view.php?id_node=$id_node&msg=$msg");
}
// vito, 20 apr 2009, commentate le righe seguenti
/*
 $course_dataHa = $dh->get_course($id_course);
 if ((is_array($course_dataHa) && count($course_dataHa)>0)){
 $course_title = $course_dataHa['titolo'];
 }
*/
$chat_link = "<a href=\"$http_root_dir/comunica/ada_chat.php target=\"Chat\">".translateFN("chat")."</a>";

// vito, 20 apr 2009, commentate le righe seguenti
/*
 // find all course available
 $field_list_ar = array('nome','titolo','data_pubblicazione');
 $clause = "ID_UTENTE_AUTORE = '$sess_id_user'";   // matching conditions: ...
 $courses_dataHa = $dh->find_courses_list($field_list_ar, $clause);
 if (AMA_DataHandler::isError($courses_dataHa)){
 $msg = $courses_dataHa->getMessage();

 }
*/
$title = translateFN('ADA - Modifica Nodo');
// vito 16 gennaio 2009
if ($form == NULL) {
    if (isset($data['form'])) {
        $html_form = $data['form'];
    }
}
else {
    $html_form = $form->getHtml();
}

/*
 * vito, 24 apr 2009
 * build the link for the Cancel operation, that when confirmed, redirects the user
 * to the page where he clicked Add Node.
*/
$link   = $_SESSION['page_to_load_on_cancel_editing'];
$text   = addslashes(translateFN('Vuoi annullare le modifiche apportate al nodo?'));
$cancel = "confirmCriticalOperationBeforeRedirect('$text','$link')";

$content_dataAr = array(
        'banner'     => $banner,
        'status'     => $status,
        'user_name'  => $user_name,
        'user_type'  => $user_type,
        'level'      => $user_level,
        'path'       => $node_path,
        'chat_link'  => $chat_link,
        'user_type'  => $user_type,
        'help'       => $help,
        'messages'   => $user_messages->getHtml(),
        'agenda'     => $user_agenda->getHtml(),
        'chat_users' => $online_users,
        'menu'       => $data['menu'],
        'head'       => $data['head_form'],
        'form'       => $html_form,
        'icon'       => $icon,
        'cancel'     => $cancel
);

if (is_array($preview_additional_data)) {
    $content_dataAr = array_merge($content_dataAr, $preview_additional_data);
}

$banner = include ROOT_DIR.'/include/banner.inc.php';
/*
 * vito, 1 ottobre 2008: passiamo il parametro onload_func=switchToFCKeditor() per
 * mostrare l'editor. Questo risolve i problemi che si avevano con IE e event.observe di prototype
*/
$options = array('onload_func' => $body_onload);

ARE::render($layout_dataAr, $content_dataAr, NULL, $options);