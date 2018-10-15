<?php
/**
 * save_traslation.php - save traslation data in the DB
 *
 * @package
 * @author		sara <sara@lynxlab.com>
 * @copyright           Copyright (c) 2009-2013, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  "switcher";
include_once '../include/'.$self.'_functions.inc.php';

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
require_once ROOT_DIR.'/include/Forms/EditTranslationForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form=$form=new EditTranslationForm();
    $form->fillWithPostData();
    if ($form->isValid())
    {
        $message=trim($_POST['TranslationTextArea']);
        $id_message=$_POST['id_record'];
        $cod_lang=$_POST['cod_lang'];
        $common_dh = $GLOBALS['common_dh'];
        if(is_null($message) || $message=="")
        {
            $retArray=array("status"=>"ERROR","msg"=>  translateFN("Nessun input sottomesso"),"title"=>  translateFN('Notifica'));
        }
        else
        {
            $result = $common_dh->update_message_translation_for_language_code($id_message,$message,$cod_lang);
            if (AMA_DataHandler::isError($result))
            {
                $retArray=array("status"=>"ERROR","msg"=>  translateFN("Attenzione: si &egrave; verificato un errore nell\'aggiornamento della traduzione."),"title"=>  translateFN('Notifica'));
            }
            else
            {
                $retArray=array("status"=>"OK","msg"=>  translateFN("Traduzione salvata con successo"),"text"=>$message,"title"=>  translateFN('Notifica'));
            }
        }
    }

    else
    {
        $retArray=array("status"=>"ERROR","msg"=>  translateFN("Dati inseriti non validi"),"title"=>  translateFN('Notifica'));
    }
    echo json_encode($retArray);
}

