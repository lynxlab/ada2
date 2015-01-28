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
include_once '../include/switcher_functions.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $id_user=$_GET['id_user'];
    $user_type=$dh->get_user_type($id_user);
    $DetailsAr=array();
    switch($user_type){
        case AMA_TYPE_STUDENT:
            $DetailsAr=$dh->get_course_instances_for_this_student($id_user,true);
            $thead_data = array(
                translateFN('Corso'),
                translateFN('Edizione'),
                translateFN('Data iscrizione'),
                translateFN('Stato iscrizione'),
                translateFN('Crediti')
            );
            
            break;
        case AMA_TYPE_AUTHOR:
            
            break;
        case AMA_TYPE_TUTOR:
            $thead_data = array(
                translateFN('Corso'),
                translateFN('Edizione'),
                translateFN('Data inizio'),
                translateFN('Data fine'),
                translateFN('Ore fruizione'),
                translateFN('Durata in giorni'),
                translateFN('Stato'),
                translateFN('Autoistruzione')
            );
            
            $InstanceAr=$dh->course_tutor_instance_get($id_user);
            if(($InstanceAr) && !empty($InstanceAr) && !AMA_DB::isError($Instance)){
                foreach($InstanceAr as $key=>$value){
                    $id_instance=$value[0];
                    $InfoInstance=$dh->course_instance_get($id_instance);
                    if(!empty($InfoInstance) && !AMA_DB::isError($Instance)){
                        $id_course=$InfoInstance['id_corso'];
                        $courseData=$dh->get_course($id_course);
                        if(!empty($courseData) && !AMA_DB::isError($courseData)){
                            $InfoInstance['titolo']=$courseData['nome'];
                            array_push($DetailsAr,$InfoInstance);
                        }
                    }
                }
                
            }
            break;
    }
    
    $total_results=array();
    if(!empty($DetailsAr) && !AMA_DB::isError($DetailsAr)){
        foreach($DetailsAr as $course){
            $course_title=$course['titolo'];

            $span_course_title = CDOMElement::create('span');
            $span_course_title->setAttribute('class', 'courseTitle');
            $span_course_title->addChild(new CText($course_title));

            $istance_title=$course['title'];

            $span_istance_title = CDOMElement::create('span');
            $span_istance_title->setAttribute('class', 'istanceTitle');
            $span_istance_title->addChild(new CText($istance_title));
            
            if($user_type == AMA_TYPE_STUDENT){

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

                $dataAr=array($thead_data[0]=>$span_course_title->getHtml(),$thead_data[1]=>$span_istance_title->getHtml(),
                        $thead_data[2]=>$span_date->getHtml(),$thead_data[3]=>$span_status->getHtml(),
                        $thead_data[4]=>$span_credits->getHtml());
                
                $caption=translateFN('Dettaglio corsi dello studente');
            }
            
            if($user_type == AMA_TYPE_TUTOR){
                
                $startDate = ts2dFN($course['data_inizio_previsto']);
                
                $span_startDate = CDOMElement::create('span');
                $span_startDate->setAttribute('class', 'startDate');
                $span_startDate->addChild(new CText($startDate));
                
                $end_Date = ts2dFN($course['data_fine']);
                
                $span_end_Date = CDOMElement::create('span');
                $span_end_Date->setAttribute('class', 'end_Date');
                $span_end_Date->addChild(new CText($end_Date));
                
                $hours = $course['duration_hours'];
                
                $span_hours = CDOMElement::create('span');
                $span_hours->setAttribute('class', 'hours');
                $span_hours->addChild(new CText($hours));
                
                $duration_days = $course['durata'];
                
                $span_duration_days = CDOMElement::create('span');
                $span_duration_days->setAttribute('class', 'hours');
                $span_duration_days->addChild(new CText($duration_days));
                
                if($startDate == 0){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('Non iniziato')));
                    
                }else if($startDate > 0 && $startDate <= time() && $end_Date > time()){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('In corso')));
                    
                }else if($startDate > 0 && $end_Date < time()){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('Terminato')));
                }
                
                $self_instruction = $course['self_instruction'];
                
                if($self_instruction){$self_instruction=translateFN('Si');}else{$self_instruction=translateFN('No');}

                $span_instruction = CDOMElement::create('span');
                $span_instruction->setAttribute('class', 'self_instruction');
                $span_instruction->addChild(new CText($self_instruction));
                
                $dataAr=array($thead_data[0]=>$span_course_title->getHtml(),$thead_data[1]=>$span_istance_title->getHtml(),
                        $thead_data[2]=>$span_startDate->getHtml(),$thead_data[3]=>$span_end_Date->getHtml(),
                        $thead_data[4]=>$span_hours->getHtml(),$thead_data[5]=>$span_duration_days->getHtml(),
                        $thead_data[6]=>$span_status->getHtml(),$thead_data[7]=>$span_instruction->getHtml()
                    );
                
                $caption=translateFN('Dettaglio corsi tutor');
            }
            
            array_push($total_results,$dataAr);
        }
            
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