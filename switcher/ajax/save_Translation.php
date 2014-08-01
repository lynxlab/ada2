<?php
/**
 * save_registration.php - save user personal data in the DB
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

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  "switcher";
include_once 'include/'.$self.'_functions.inc.php';
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
        $message=$_POST['TranslationTextArea'];
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

