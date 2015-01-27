<?php

/**
 * get_studentDetails.php - return table with student details
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
include_once 'include/switcher_functions.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $id_user=$_GET['id_user'];
    $CourseAr=$dh->get_course_instances_for_this_student($id_user,true);
    
    $total_results=array();
    if(!empty($CourseAr) && !AMA_DB::isError($CourseAr)){
        
        $thead_data = array(
            translateFN('Corso'),
            translateFN('Edizione'),
            translateFN('Data iscrizione'),
            translateFN('Stato iscrizione'),
            translateFN('Crediti')
        );
        foreach($CourseAr as $course){
            $course_title=$course['titolo'];

            $span_course_title = CDOMElement::create('span');
            $span_course_title->setAttribute('class', 'courseTitle');
            $span_course_title->addChild(new CText($course_title));

            $istance_title=$course['title'];

            $span_istance_title = CDOMElement::create('span');
            $span_istance_title->setAttribute('class', 'istanceTitle');
            $span_istance_title->addChild(new CText($istance_title));

            $status=$course['status'];
            
            switch ($status){

                case ADA_STATUS_PRESUBSCRIBED:
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'userStatus');
                    $span_status->addChild(new CText(translateFN("Preiscritto")));
                    break;
                case ADA_STATUS_SUBSCRIBED:
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'userStatus');
                    $span_status->addChild(new CText(translateFN("Iscritto")));
                    
                    break;
                case ADA_STATUS_REMOVED:
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'userStatus');
                    $span_status->addChild(new CText(translateFN("Rimosso")));

                    break;
                case ADA_STATUS_VISITOR:
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'userStatus');
                    $span_status->addChild(new CText(translateFN("in visita")));

                    break;
                case ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED:
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'userStatus');
                    $span_status->addChild(new CText(translateFN("Completato")));

                    break;
            }
            
            $credits=$course['crediti'];

            $span_credits = CDOMElement::create('span');
            $span_credits->setAttribute('class', 'userCredits');
            $span_credits->addChild(new CText($status));

            $date=$course['data_iscrizione'];
            $date=ts2dFN($date);

            $span_date = CDOMElement::create('span');
            $span_date->setAttribute('class', 'Inscription_date');
            $span_date->addChild(new CText($date));

            $dataAr=array(translateFN('Corso')=>$span_course_title->getHtml(),translateFN('Edizione')=>$span_istance_title->getHtml(),
                translateFN('Data iscrizione')=>$span_date->getHtml(),  translateFN('Stato iscrizione')=>$span_status->getHtml(),
                translateFN('Crediti')=>$span_credits->getHtml());

            array_push($total_results,$dataAr);
        }
            $caption=translateFN('Dettaglio corsi dello studente');
            $result_table = BaseHtmlLib::tableElement('class:User_table', $thead_data, $total_results,null,$caption);
            $result=$result_table->getHtml();
            $retArray=array("status"=>"OK","html"=>$result);
    }else{

        $span_error = CDOMElement::create('span');
        $span_error->setAttribute('class', 'ErrorSpan');
        $span_error->addChild(new CText(translateFN('Nessun dato trovato')));
        
        $retArray=array("status"=>"ERROR","html"=>$span_error->getHtml());
    }
    echo json_encode($retArray);
}