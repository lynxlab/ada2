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
SwitcherHelper::init($neededObjAr);

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
  'banner' => isset($banner) ? $banner : '',
  'eportal' => isset($eportal) ? $eportal : '',
  'course_title' => translateFN('Modulo di traduzione'),
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml(),
  //'results'=>$results,
  'status'    => $status,
  'help'      => isset($help) ? $help : '',
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
        JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_REDRAW,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS
);

ARE::render($layout_dataAr,$content_dataAr,NULL, array('onload_func' => "initDoc();"));
?>
