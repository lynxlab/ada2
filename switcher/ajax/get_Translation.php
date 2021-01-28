<?php
/**
 * save_traslation.php
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
require_once ROOT_DIR.'/include/Forms/TranslationForm.inc.php';


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form=$form=new TranslationForm();
    $form->fillWithPostData();
    if ($form->isValid())
    {
        $search_text=$_POST['t_name'];
        $language_code=$_POST['selectLanguage'];
        $common_dh = $GLOBALS['common_dh'];
        $thead_data = array(translateFN("Errore"));
        if(is_null($search_text) || $search_text=="")
        {
            $total_results=array();
            $msgEr=translateFN("Nessun input sottomesso");
            $temp_results=array(translateFN("")=>$msgEr);
            array_push($total_results,$temp_results);
            $result_table = BaseHtmlLib::tableElement('id:table_result', $thead_data, $total_results);
            $result_table->setAttribute('class', $result_table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
            $result=$result_table->getHtml();
            $retArray=array("status"=>"ERROR","msg"=>  translateFN("Nessun input sottomesso"),"html"=>$result);
        }
        else
        {
            //$result = $common_dh->find_translation_for_message($search_text, $language_code, ADA_SYSTEM_MESSAGES_SHOW_SEARCH_RESULT_NUM);
             $result = $common_dh->find_translation_for_message($search_text, $language_code,null);

            if (AMA_DataHandler::isError($result)) {
                $total_results=array();
                $msgEr=translateFN("Errore nella ricerca dei messaggi");
                $temp_results=array(translateFN("")=>$msgEr);
                array_push($total_results,$temp_results);
                $result_table = BaseHtmlLib::tableElement('id:table_result', $thead_data, $total_results);
                $result=$result_table->getHtml();
                $retArray=array("status"=>"ERROR","msg"=>  translateFN("Errore nella ricerca dei messaggi"),"html"=>$result);
            }

            else if ($result == NULL) {
                $total_results=array();
                $msgEr=translateFN("Nessuna frase trovata");
                $temp_results=array(translateFN("")=>$msgEr);
                array_push($total_results,$temp_results);
                $result_table = BaseHtmlLib::tableElement('id:table_result', $thead_data, $total_results);
                $result_table->setAttribute('class', $result_table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
                $result=$result_table->getHtml();
                $retArray=array("status"=>"ERROR","msg"=>  translateFN("Nessuna frase trovata"),"html"=>$result);
            }

            else {
                $thead_data = array(
                      null,
                      translateFN('Testo'),
                      translateFN('Azioni'),
                      translateFN('TestoCompleto'),
                      translateFN('CodLingua'),
                      translateFN('Id')
                );
                $total_results = array();
                //$imgDetails='<img class="imgEx tooltip" src='.HTTP_ROOT_DIR.'/layout/ada_blu/img/details_open.png >';
                $imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
                $imgDetails->setAttribute('class', 'imgDetls tooltip');
                $imgDetails->setAttribute('title', translateFN('espande/riduce il testo'));
                foreach ($result as $row){
                    $testoCompleto=$row['testo_messaggio'];
                    $testoRidotto=  substr($row['testo_messaggio'], 0, 30);
                    if(strlen($testoCompleto)>30)
                    {
                      $testoRidotto=$testoRidotto.'...';
                    }
                    $id_message=$row['id_messaggio'];
                    $newButton = CDOMElement::create('button');
                    $newButton->setAttribute('class', 'buttonTranslate tooltip');
                    $newButton->addChild (new CText(translateFN('Clicca per aggiornare la traduzione')));
                    $temp_results = array(null=>$imgDetails,translateFN('Testo') => $testoRidotto,translateFN('Azioni')=>$newButton,translateFN('TestoCompleto')=>$testoCompleto,
                    translateFN('CodLingua') =>$language_code,translateFN('Id') =>$id_message);
                    array_push ($total_results,$temp_results);
                }

                $result_table = BaseHtmlLib::tableElement('id:table_result', $thead_data, $total_results);
                $result_table->setAttribute('class', $result_table->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
                $result=$result_table->getHtml();
                $retArray=array("status"=>"OK","msg"=>  translateFN("Ricerca eseguita con successo"),"html"=>$result);
            }
      }
    }
    else
    {
        $retArray=array("status"=>"ERROR","msg"=>  translateFN("Dati inseriti non validi"),"html"=>null);
    }

    echo json_encode($retArray, JSON_INVALID_UTF8_IGNORE);
}
