<?php
// +----------------------------------------------------------------------+
// | ADA version 1.8 alpha                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2008 Lynx                                         |
// +----------------------------------------------------------------------+
// |                                                                      |
// |                  T R A N S L A T O R                                 |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Stefano Penge <steve@lynxlab.com>                            |
// | Modified by: vito (nov 2008)                                         |
// +----------------------------------------------------------------------+


/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/*
 * Only admins and switchers are allowed to update translations
 */
$allowedUsersAr = array(AMA_TYPE_ADMIN, AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER => array('layout')
);

//import_request_variables("gP","");
//extract($_GET,EXTR_OVERWRITE,ADA_GP_VARIABLES_PREFIX);
//extract($_POST,EXTR_OVERWRITE,ADA_GP_VARIABLES_PREFIX);

require_once ROOT_DIR.'/include/module_init.inc.php';
//$self =  whoami();  // = admin!
$self =  "switcher";
include_once 'include/'.$self.'_functions.inc.php';

/*
 * Html Library containing forms used in this module.
 */
require_once ROOT_DIR.'/include/HtmlLibrary/AdminModuleHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';
/**

/* *****************************
 *
 * FUNCTIONS USED IN THIS MODULE
 *
 * *****************************
 */

/**
 * function get_excel_file_for_these_messagesFN(): used to return a tab delimited file
 * ,that can be easily opened by excel, containing all of the messages in table 'messaggi_sistema'
 *
 * @param array $messages
 * @param string $filename
 */
function get_excel_file_for_these_messagesFN($messages, $filename=null) {

  if ($filename == null) {
    $filename='messaggi.csv';
  }

  header("Content-type: text/plain");
  header("Content-Disposition: attachment;filename=$filename");

  foreach($messages as $message) {
    echo "\"{$message['testo_messaggio']}\"\t\r\n";
  }
  exit();
}

/**
 * function error_messageFN
 *
 * @param string $error_message
 * @param string $container_id
 * @return string - the error message
 */
function error_messageFN($error_message,$container_id=null) {
  if($container_id != null) {
    $id = $container_id;
  }
  else {
    $id = 'error_message_container';
  }

  $div = CDOMElement::create('div');

  $span_message = CDOMElement::create('span', "id:$id, class:operation_error");
  $span_message->addChild(new CText($error_message));

  $span_goback = CDOMElement::create('span','id:goback_link');
  
  $navigation_history = unserialize($_SESSION['sess_navigation_history']);
  $previous_page = $navigation_history->previousPage();
  $a = CDOMElement::create('a', "href: $previous_page");
  $a->addChild(new CText(translateFN("Torna alla pagina precedente")));
  $span_goback->addChild($a);

  $div->addChild($span_message);
  $div->addChild($span_goback);

  return $div;
}

/**
 * function operation_completed_messageFN
 *
 * @param string $translated_message_text
 * @param string $go_back_link
 * @param string $translated_go_back_text
 * @return CORE_Base object
 */
function operation_completed_messageFN($translated_message_text, $go_back_link, $translated_go_back_text) {

  $div = CDOMElement::create('div','id:message_text_updated, class:operation_ok');

  $div_text = CDOMElement::create('div', 'id:message_div, class:page_text');
  $div_text->addChild(new CText($translated_message_text));

  $div_link = CDOMElement::create('div', 'id:goback_link_div, class:page_link');
  $a        = CDOMElement::create('a',"href: $go_back_link");
  $a->addChild(new CText($translated_go_back_text));
  $div_link->addChild($a);

  $div->addChild($div_text);
  $div->addChild($div_link);

  return $div;
}

/* ****************
 *
 * END OF FUNCTIONS
 *
 * ****************
 */

/* ****************
 *
 * START OF MODULE
 *
 * ****************
 */

/*if (!stristr($_SERVER['PHP_SELF'],"index.php")) {
  die($_SERVER['PHP_SELF']."Non &egrave; possibile accedere direttamente a questo modulo.");
}
*/
$module_title = translateFN("Traduttore messaggi di sistema");

switch ($op) {
  /*
   * Handle user search for a message in a translation.
   * 
   * Obtain from post(if coming from translation main page) or from get(if coming translation edit form)
   * the text specified by the user, search for a matching
   * message in the given target language, and if a message is found, display
   * an editing form for the message.
   * If a message is not found, display an error message and a link to the previous
   * page.
   */
  case 'search':
    if (isset($post_translation_search_text) && !empty($post_translation_search_text)) {
      $language_code = $post_translation_search_select_language;
      $search_text   = $post_translation_search_text;
    }
    else if (isset($get_q) && !empty($get_q)) {
      $language_code = $get_code;
      $search_text   = $get_q;      
    }
    else {
      $page_content = error_messageFN(translateFN("Attenzione: non &egrave; stato specificato il testo da cercare."));
      break;
    }
    $common_dh = $GLOBALS['common_dh'];
    $result = $common_dh->find_translation_for_message($search_text, $language_code, ADA_SYSTEM_MESSAGES_SHOW_SEARCH_RESULT_NUM);
    
    if (AMA_DataHandler::isError($result)) {
      new ADA_Error($result, translateFN('Errore nella ricerca dei messaggi'));
    }
    
    if ($result == NULL) {
      $page_content = new CText(translateFN("No sentences found"));
    }
    else {
      $page_content = UserModuleHtmlLib::translationFoundMessagesList($result,$language_code, $search_text);
    }
    break;
    
//    if (isset($post_translation_search_text) && !empty($post_translation_search_text)) {
//      $language_code = $post_translation_search_select_language;
//
//      $result = $dh->find_translation_for_message($post_translation_search_text, $language_code,
//      ADA_SYSTEM_MESSAGES_SHOW_SEARCH_RESULT_NUM);
//
//      if (AMA_DataHandler::isError($result)) {
//        new ADA_Error($result, translateFN('Errore nella ricerca dei messaggi'));
//      }
//      
//      if ($result == NULL) {
//        $page_content = new CText(translateFN("Non sono stati trovati messaggi"));
//      }
//      else {
//        $page_content = UserModuleHtmlLib::translationFoundMessagesList($result,$post_translation_search_select_language, $post_translation_search_text);
//      }
//    }
//    else {
//      $page_content = error_messageFN(translateFN("Attenzione: non &egrave; stato specificato il testo da cercare."));
//    }
    break;

    /*
     * Given a translated message for a language, updates the message text
     * in the corresponding table.
     */
  case 'edit':
    $data = array($get_id, $get_text);
    $page_content = UserModuleHtmlLib::translationTranslatedMessageEditForm($data, $get_code, $get_q);
    break;

  case 'update':
    if(isset($post_translation_edit_button)
    && isset($post_message_language_code)
    && isset($post_translation_edit_textarea)
    && !empty($post_translation_edit_textarea)
    && isset($post_message_id)
    && ctype_digit($post_message_id)) {

      $result = $common_dh->update_message_translation_for_language_code($post_message_id,
      $post_translation_edit_textarea,
      $post_message_language_code);
      if (AMA_DataHandler::isError($result)) {
        //$page_content = error_messageFN(translateFN('Attenzione: si &egrave; verificato un errore nell\'aggiornamento della traduzione.'));
        new ADA_Error($result, translateFN('Attenzione: si &egrave; verificato un errore nell\'aggiornamento della traduzione.'));
      }
      else {
        $message      = translateFN('The translation of the sentence has been updated.');
        $go_back_link = $http_root_dir.'/switcher/translation.php';
        $go_back_text = translateFN('Go back to the translation page');

        $page_content = operation_completed_messageFN($message, $go_back_link, $go_back_text);
      }
    }
    break;

    /*
     * Export the contents of table messaggi_sistema as a tab-delimited file
     */
  case 'export':
    $messages = $dh->get_all_system_messages();
    if (AMA_DataHandler::isError($messages)) {
      //$page_content = error_messageFN(translateFN('Attenzione: si &egrave; verificato un errore nell\'esportazione del file.'));
      new ADA_Error($messages,translateFN('Attenzione: si &egrave; verificato un errore nell\'esportazione del file.'));
    }
    else {
      get_excel_file_for_these_messagesFN($messages);
    }
    break;

    /*
     * Handle importing translated messages from an uploaded tab-delimited file into a selected
     * translation.
     */
  case 'import':
    if (isset($post_translation_upload_button)
    && isset($post_translation_import_file)
    && isset($post_translation_import_select_language)) {
      /*
       * if there weren't errors and the uploaded file has mimetype text/plain
       * process the file
       */

      if($_FILES['error'] == UPLOAD_ERR_OK
      && $_FILES['post_translation_import_file']['type'] == 'text/plain') {
        //$translations = file($_FILES['post_translation_import_file']['tmp_name']);

        $language_code = $post_translation_import_select_language;

        /*
         * If there weren't errors during file upload, open the tmp file created and
         * parse it searching for tab-delimited values.
         */
        $handle = fopen($_FILES['post_translation_import_file']['tmp_name'], "r");

        if($handle !== FALSE) {
          // Since length paramter is optional (PHP5), it is passed as null
          $errors = '';
          while(($data = fgetcsv($handle, null, "\t")) !== FALSE) {
            /* $data contains the two columns read from the file.
             * for a translation to be imported, the second column
             * must be not empty
             */
            if(count($data) == 2 && !empty($data[1])) {
              echo "$data[0] => $data[1]<br />";

              $result = $dh->update_message_translation_for_language_code_given_this_text($data[0], $data[1], $language_code);
              if(AMA_DataHandler::isError($result)) {
                $errors .= 'Errore per il messaggio: ' . $new_message;
              }
              // TODO: se non è un errore, incrementare una variabile contatore dei messaggi aggiornati
            }
          }
        }
        fclose($handle);

        $message = translateFN("Aggiornamento dei messaggi per la traduzione completato.");
        $go_back_link = $http_root_dir.'/switcher/translation.php';
        $go_back_text = translateFN("Torna alla pagina iniziale per le traduzioni");
        $page_content = operation_completed_messageFN($message, $go_back_link, $go_back_text);

      }
      else {
        $page_content = error_messageFN(translateFN('Attenzione: si &egrave; verificato un errore nell\'upload del file.'));
      }
    }
    break;

    /*
     * Print translation operations.
     */
  default:
    /*
     * if usertype is switcher assume as client the first element of the testers array
     */
    $languages = Translator::getSupportedLanguages();
    if ($_SESSION['sess_id_user_type'] == AMA_TYPE_SWITCHER)  {
        $tester_client_Ar = $userObj->getTesters();
        $tester_client = strtoupper($tester_client_Ar[0]);
        $tester_default_language_constant = $tester_client . "_DEFAULT_LANGUAGE";
        if (defined($tester_default_language_constant))  {
            $tester_default_language = constant($tester_default_language_constant);
            $languages = array();
            $languages[0] = array('nome_lingua' => $tester_default_language, 'codice_lingua' => $tester_default_language);
        }
    }
/*    else {
        $languages = Translator::getSupportedLanguages();
    }
*/
//    $upload_file_handler = $http_root_dir .'/switcher/translation.php?op=import';
    $page_content  = CDOMElement::create('div','id:container');

    $page_content->addChild(UserModuleHtmlLib::translationSearchForm($languages));
//    $page_content->addChild(UserModuleHtmlLib::translationExportSystemMessagesLink());
  //  $page_content->addChild(UserModuleHtmlLib::translationImportSystemMessagesForm($upload_file_handler, $languages));

}
$status = translateFN('translation mode');
$content_dataAr = array(
  'banner' => $banner,
  'eportal' => $eportal,
  'course_title' => translateFN('Modulo di traduzione'),
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml(),
  'status'    => $status,
  'banner'    => $banner,
  'help'      => $help,
//  'dati'      => $table->getHtml(),
  'data'      => $page_content->getHtml()

);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr,$content_dataAr);
?>
