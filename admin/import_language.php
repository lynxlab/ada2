<?php
/**
 * Add user - this module provides import language in DB functionality
 *
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
//ini_set("display_errors","1");
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
$allowedUsersAr = array(AMA_TYPE_ADMIN);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
AMA_TYPE_ADMIN => array('import_lang')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

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
AdminHelper::init($neededObjAr);

include_once ROOT_DIR .'/include/xml_class.inc.php';

/*
 * YOUR CODE HERE
 */
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * Handle data from $_POST:
   * 1. validate user submitted data
   * 2. if there are errors, display the add user form updated with error messages
   * 3. if there aren't errors, add this user to the common database and to
   *    the tester databases associated with this user.
   */
  /*
   * Validazione dati
   */
  $errorsAr = array();

  if($_POST['lang_tester'] == 'none') {
    $errorsAr['lang_tester'] = true;
  }
  if($_POST['file_lang'] == 'none') {
    $errorsAr['file_lang'] = true;
  }
  if($_POST['language'] == 'none') {
    $errorsAr['language'] = true;
  }
  if(count($errorsAr) > 0) {
    unset($_POST['submit']);
    $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));

    if(AMA_Common_DataHandler::isError($testers_dataAr)) {
      $errObj = new ADA_Error($testersAr,"Errore nell'ottenimento delle informazioni sui tester");
    }
    else {
      $testersAr = array();
      foreach($testers_dataAr as $tester_dataAr) {
        $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
      }
      //      $form = AdminModuleHtmlLib::getAddUserForm($testersAr,$user_dataAr,$errorsAr);
      $form = AdminModuleHtmlLib::getFormImportLanguage('import_language.php',$testersAr, $errorsAr);
    }
  }
  else {
    $tester          = $_POST['lang_tester'];
    $suffix          = $_POST['language'];
    $file_to_import  = $_POST['file_lang'];
    $delete_messages = $_POST['delete_messages'];
    $delete_sistema  = $_POST['delete_sistema'];

    //	$array_lang = file($file_to_import);
    $lang_XML = file_get_contents($file_to_import);
    $xmlObj = new xmlConverter();
    $xmlObj->setXml($lang_XML);
    $xmlObj->xml2array();
    $dataHa = $xmlObj->getdata();
    $xml_array = $dataHa[0];
    $array_lang = $dataHa['ooo_sheet']['ooo_row'];
    //	print_r($array_lang);
    //	exit();

    if ($tester == "all") {
      $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));
      if(AMA_Common_DataHandler::isError($testers_dataAr)) {
        $errObj = new ADA_Error($testersAr,"Errore nell'ottenimento delle informazioni sui tester");
        header('Location:'.$http_root_dir.'/admin/admin.php');
        exit();
      }
    }
    else {
      $testers_dataAr = array();
      $testers_dataAr[1]['puntatore'] = $tester;
    }
    $testersAr = array();
    foreach($testers_dataAr as $tester_dataAr) {
      $tester = $tester_dataAr['puntatore'];
      //	    $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];

      $tester_dsn = MultiPort::getDSN($tester);
      $tester_dh = AMA_DataHandler::instance($tester_dsn);
      if ($delete_messages == "yes") {
        $tester_dh->delete_all_messages($suffix);
      }
      if ($delete_sistema == "yes") {
        $tester_dh->delete_all_messages("sistema");
        // inserisce le frasi di base in messaggi sistema dopo aver svuotato la tabella
        $file_sistema_to_import = "../db/messaggi/ADA_messaggi_sistema.xml";
        $lang_XML = file_get_contents($file_sistema_to_import);
        $xmlObj = new xmlConverter();
        $xmlObj->setXml($lang_XML);
        $xmlObj->xml2array();
        $dataHa = $xmlObj->getdata();
        $xml_array = $dataHa[0];
        $array_sistema_lang = $dataHa['ooo_sheet']['ooo_row'];
        foreach ($array_sistema_lang as $one_message_sitema) {
          $message_text = $one_message_sitema['message'];
          $message_id = $one_message_sitema['id'];
          $sql_message_prepared = $tester_dh->sql_prepared($message_text);
          $inserted_message = $tester_dh->add_translated_message($sql_message_prepared, $message_id, "sistema");
        }

      }
      $imported_sentences = 0;
      foreach ($array_lang as $one_message) {
        //			$one_messageAr = explode(";",$one_message);
        $sent_to_insert = TRUE;
        $message_text = $one_message['message'];
        $message_id = $one_message['id'];
        if ($delete_messages != "yes") {
          $table_name = "messaggi_".$suffix;
          $result = $tester_dh->select_message_text ($table_name, $message_id);
          if ($result != null) $sent_to_insert = FALSE;
        }
        if ($sent_to_insert) {
          $sql_message_prepared = $tester_dh->sql_prepared($message_text);
          $inserted_message = $tester_dh->add_translated_message($sql_message_prepared, $message_id, $suffix);
          if (!AMA_Common_DataHandler::isError($inserted_message)) {
            $imported_sentences++;
          }
        } else { // ID message already exist: update record
          $result = $tester_dh->update_message_translation_for_language_code($message_id, $message_text, $suffix);
          if (!AMA_Common_DataHandler::isError($result)) {
            $imported_sentences++;
          }
        }
      }
    }
    if($imported_sentences > 0) {
      $message = "Number of imported messages: " .$imported_sentences;
      $errorsAr['imported'] = $message;
    }
    //    else {
    /*
    * Qui bisogna ricreare il form per la registrazione passando in $errorsAr['registration_error']
    * $result e portando li' dentro lo switch su $result
    */
    $errorsAr['registration_error'] = $result;

    unset($_POST['submit']);
    $user_dataAr = $_POST;
    $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));

    if(AMA_Common_DataHandler::isError($testers_dataAr)) {
      $errObj = new ADA_Error($testersAr,"Errore nell'ottenimento delle informazioni sui tester");
    }
    else {
      $testersAr = array();
      foreach($testers_dataAr as $tester_dataAr) {
        $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
      }
      $form = AdminModuleHtmlLib::getFormImportLanguage('import_language.php',$testersAr, $errorsAr);      }
      //    } // imported sentences
  } // no error
} // submit
else {
  /*
   * Display the import language form
   */
  $testers_dataAr = $common_dh->get_all_testers(array('id_tester','nome'));

  if(AMA_Common_DataHandler::isError($testers_dataAr)) {

    $errObj = new ADA_Error($testersAr,"Errore nell'ottenimento delle informazioni sui tester");
  }
  else {
    $testersAr = array();
    foreach($testers_dataAr as $tester_dataAr) {
      $testersAr[$tester_dataAr['puntatore']] = $tester_dataAr['nome'];
    }
    if(!isset($errorsAr)) $errorsAr=null;
    $form = AdminModuleHtmlLib::getFormImportLanguage('import_language.php',$testersAr, "", $errorsAr);
  }
}
$label = "Import language file";

//$link_example = CDOMElement::create('a','href:../db/messaggi/example_message_to_translate.xml');
//$link_example->addChild(new CText(("see example")));
//$link_ex_created = $link_example->getHtml() . ' > ' . $label;

$link_example = "<a href://\"".HTTP_ROOT_DIR."/db/messaggi/example_message_to_translahome_te.xml\">" . ("see example") . "</a>)";
$help  = "The Admin can import a language file (XML format. ". $link_example . " in all the tester in the language selected. <br />Suggestion: It's better to delete the system message only for the first import. <br />The system import the content of file <strong>ADA_messaggi_sistema.xml</strong>. When you make an update of the messagge it's better DON'T delete the system messages.";

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText("Home dell'Amministratore"));
$module = $home_link->getHtml() . ' > ' . $label;

$menu_dataAr = array();
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $form->getHtml(),
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
?>