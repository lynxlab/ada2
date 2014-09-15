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
$self =  "translation";
/*
 * Html Library containing forms used in this module.
 */
require_once ROOT_DIR.'/include/HtmlLibrary/AdminModuleHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';
require_once ROOT_DIR.'/include/Forms/TranslationForm.inc.php';
require_once ROOT_DIR.'/include/Forms/EditTranslationForm.inc.php';
/**
 * 
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


$languageName=array();

foreach($languages as $language)
{
    $languageName[$language['codice_lingua']]=$language['nome_lingua'];
}
$form=new TranslationForm($languageName);
$data=$form->getHtml();
$EditTranslFr=new EditTranslationForm();
$dataEdtTslFr=$EditTranslFr->getHtml();
   
$status = translateFN('translation mode');

$content_dataAr = array(
  'banner' => $banner,
  'eportal' => $eportal,
  'course_title' => translateFN('Modulo di traduzione'),
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml(),
  //'results'=>$results,
  'status'    => $status,
  'banner'    => $banner,
  'help'      => $help,
//  'dati'      => $table->getHtml(),
  'data'      => $data,
  'dataEditTranslation' => $dataEdtTslFr,

);

/**
 * Sends data to the rendering engine
 */
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_UNIFORM,
                JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT,
                JQUERY_MASKEDINPUT,
                
                );

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
                JQUERY_UNIFORM_CSS,
                JQUERY_DATATABLE_CSS,
                );

ARE::render($layout_dataAr,$content_dataAr,NULL, array('onload_func' => "initDoc();"));
?>
