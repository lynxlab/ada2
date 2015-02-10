<?php

/**
 * get_userDetails.php - return table with user details
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
$retArray=array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $id_user=$_GET['id_user'];
    $user_type=$dh->get_user_type($id_user);
    $DetailsAr=array();
    switch($user_type){
        case AMA_TYPE_STUDENT:
            $thead_data = array(
                translateFN('Corso'),
                translateFN('Edizione'),
                translateFN('Data iscrizione'),
                translateFN('Stato iscrizione'),
                translateFN('Crediti')
            );
            $DetailsAr=$dh->get_course_instances_for_this_student($id_user,true);
            
            break;
        case AMA_TYPE_AUTHOR:
            $thead_data = array(
                translateFN('Corso'),
                translateFN('Data creazione'),
                translateFN('Data pubblicazione'),
                translateFN('Tipo corso'),
                translateFN('ore'),
                translateFN('Crediti'),
                translateFN('N° nodi'),
                translateFN('N° attività'),
                translateFN('N° classi')
            );
            
            $field_list_ar = array('titolo','data_creazione','data_pubblicazione','tipo_servizio','duration_hours', 'crediti');
            $key = $id_user;
            $search_fields_ar = array('id_utente_autore');
            $DetailsAr = $dh->find_courses_list_by_key($field_list_ar, $key, $search_fields_ar);
            break;
        case AMA_TYPE_TUTOR:
            $thead_data = array(
                translateFN('Corso'),
                translateFN('Edizione'),
                translateFN('Data inizio'),
                translateFN('Data fine'),
                translateFN('Ore'),
                translateFN('Durata in giorni'),
                translateFN('Stato'),
                translateFN('N° iscritti'),
                translateFN('Autoistruzione')
            );
            
            $DetailsAr=$dh->get_tutors_assigned_course_instance($id_user,false);
            if(isset($DetailsAr) && !empty($DetailsAr) && !AMA_DB::isError($DetailsAr)){$DetailsAr = $DetailsAr[$id_user];}
    
            break;
    }
    
    $total_results=array();
    if(!empty($DetailsAr) && !AMA_DB::isError($DetailsAr)){
        foreach($DetailsAr as $course){
            
            /*
             * course data
             */
            if(isset($course['titolo'])){
                $course_title=$course['titolo'];}else{$course_title='';}
                
            $span_course_title = CDOMElement::create('span');
            $span_course_title->setAttribute('class', 'courseTitle');
            if (isset($course['id_corso'])) {
                $linkCourse = CDOMElement::create('a');
                if ($user_type == AMA_TYPE_AUTHOR) {
                    $linkCourse->setAttribute('href', HTTP_ROOT_DIR.'/switcher/edit_course.php?id_course='.$course['id_corso']); 
                } else {
                    $linkCourse->setAttribute('href', HTTP_ROOT_DIR.'/switcher/list_instances.php?id_course='.$course['id_corso']); 
                }
                $linkCourse->addChild(new CText($course_title));
            }
            $span_course_title->addChild($linkCourse);
            
            /*
             * instance course data
             */
            $span_istance_title = CDOMElement::create('span');
            $span_istance_title->setAttribute('class', 'istanceTitle');
            if(isset($course['title'])){
                $istance_title=$course['title'];
                if (isset($course['id_istanza_corso'])) {
                    $linkInstanceCourse = CDOMElement::create('a');
//                    if ($user_type == AMA_TYPE_AUTHOR) {
                        $linkInstanceCourse->setAttribute('href', HTTP_ROOT_DIR.'/switcher/edit_instance.php?id_course='.$course['id_corso'].'&id_course_instance='.$course['id_istanza_corso']);
//                    } else {
//                        
//                    }    
                    $linkInstanceCourse->addChild(new CText($istance_title));
                }
            }else { 
                $istance_title='';
                $linkInstanceCourse=new CText($istance_title);
            }

            $span_istance_title->addChild($linkInstanceCourse);
            
            
            if(isset($course['crediti'])){
                $credits=$course['crediti'];}else{$credits=0;}
            
            if($user_type == AMA_TYPE_STUDENT){

                if(isset($course['status'])){
                    $status=$course['status'];}else{$status='';}
                
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
                    default:
                        $span_status = CDOMElement::create('span');
                        $span_status->setAttribute('class', 'userStatus');
                        $span_status->addChild(new CText(''));
                }
                
                if( isset($course['data_iscrizione']) && !is_null($course['data_iscrizione']) && intval($course['data_iscrizione'] > 0) ){
                    $date=ts2dFN($course['data_iscrizione']);}else{ $date = '-';}
                
                $dataAr=array($thead_data[0]=>$span_course_title->getHtml(),$thead_data[1]=>$span_istance_title->getHtml(),
                        $thead_data[2]=>$date,$thead_data[3]=>$span_status->getHtml(),
                        $thead_data[4]=>$credits);
                
                $caption=translateFN('Dettaglio corsi dello studente');

                /*
                 * Settings for Sort date columns in DataTable
                 */
                $retArray['columnDefs'][] = array(
                    'sType'=>'date-eu',
                    'aTargets'=>[2]
                );
                
            } else if($user_type == AMA_TYPE_TUTOR){
                
                if(isset($course['id_istanza_corso'])){
                    $id_instance = $course['id_istanza_corso'];
                
                    /* count student for course_instance */
                    $studentsAr = $dh->get_students_for_course_instance($id_instance); 
                    $inscription=0;
                    foreach ($studentsAr as $student){
                        $status = $student['status'];
                        if((strpos($status,ADA_STATUS_SUBSCRIBED) == 0) || (strpos($status,ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) == 0)){
                            $inscription++;
                        }
                    }
                
                }else{$inscription=0;}
                
                if( isset($course['data_inizio_previsto']) && !is_null($course['data_inizio_previsto']) && intval($course['data_inizio_previsto'] > 0)){
                    $startDate = ts2dFN($course['data_inizio_previsto']);}else{ $startDate = '-';}
                
                if( isset($course['data_fine']) && !is_null($course['data_fine']) && intval($course['data_fine'] > 0)){
                    $end_Date = ts2dFN($course['data_fine']);}else{ $end_Date = '-';}
                
                if(isset($course['duration_hours'])){
                    $hours = $course['duration_hours'];}else{$hours=0;}
                  
                if(isset($course['durata'])){
                    $duration_days = $course['durata'];}else{$duration_days=0;}
              
                if(isset($course['data_inizio_previsto']) && intval($course['data_inizio_previsto'] == 0)){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('Non iniziato')));
                    
                }else if( isset($course['data_inizio_previsto']) && intval($course['data_inizio_previsto']) > 0 
                        && intval($course['data_inizio_previsto']) <= time() && intval($course['data_fine'] > time())){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('In corso')));
                    
                }else if( isset($course['data_inizio_previsto']) && intval($course['data_inizio_previsto']) > 0 
                        && intval($course['data_fine'] < time())){
                    
                    $span_status = CDOMElement::create('span');
                    $span_status->setAttribute('class', 'instanceStatus');
                    $span_status->addChild(new CText(translateFN('Terminato')));
                }
                
                if(isset($course['self_instruction']) && ($course['self_instruction'])){
                    $self_instruction=translateFN('Si');
                    
                }else if(isset($course['self_instruction']) && (!$course['self_instruction']))
                    {$self_instruction=translateFN('No');}else{$self_instruction='';}

                $span_instruction = CDOMElement::create('span');
                $span_instruction->setAttribute('class', 'self_instruction');
                $span_instruction->addChild(new CText($self_instruction));
                
                $dataAr=array($thead_data[0]=>$span_course_title->getHtml(),$thead_data[1]=>$span_istance_title->getHtml(),
                        $thead_data[2]=>$startDate,$thead_data[3]=>$end_Date,
                        $thead_data[4]=>$hours,$thead_data[5]=>$duration_days,
                        $thead_data[6]=>$span_status->getHtml(),$thead_data[7]=>$inscription,
                        $thead_data[8]=>$span_instruction->getHtml()
                    );
                
                $caption=translateFN('Dettaglio corsi tutor');

                /*
                 * Settings for Sort date columns in DataTable
                 */
                $retArray['columnDefs'][] = array(
                    'sType'=>'date-eu',
                    'aTargets'=>[2]
                );
                $retArray['columnDefs'][] = array(
                    'sType'=>'date-eu',
                    'aTargets'=>[3]
                );

            } else if($user_type == AMA_TYPE_AUTHOR){
                
                if(isset($course['id_corso'])){
                    $id_course = $course['id_corso'];
                    $InstanceAr=$dh->course_instance_get_list(null,$id_course);
                    if(!AMA_DB::isError($InstanceAr)){
                        $instanceNumber = count($InstanceAr);
                    }
                    $field_list_ar=array('tipo');
                    $clause='(tipo ='. ADA_LEAF_TYPE.' OR  tipo ='. ADA_GROUP_TYPE.' OR  tipo ='.ADA_PERSONAL_EXERCISE_TYPE.')';
                    $clause .= " AND id_nodo LIKE '%$id_course%'";
                    $NodesAr=$dh->_find_nodes_list($field_list_ar,$clause);
                    if(!AMA_DB::isError($NodesAr)){
                        $countActivity=0;
                        if(!empty($NodesAr)){
                            foreach($NodesAr as $node=>$type){
                                if($type[1]==ADA_PERSONAL_EXERCISE_TYPE){
                                    $countActivity++;
                                }
                            }
                        }
                        $nodeNumber = (count($NodesAr)-$countActivity);
                        $activitiesNumber = $countActivity;
                    }else{
                        $nodeNumber = 0;
                        $activitiesNumber = 0;
                    }
                }else{
                    $instanceNumber = 0;
                    $nodeNumber = 0;
                    $activitiesNumber = 0;
                }
               
                if( isset($course['data_creazione']) && !is_null($course['data_creazione']) && intval($course['data_creazione'] > 0)){
                    $creationDate = ts2dFN($course['data_creazione']);}else{ $creationDate = '-';}
                
                if(isset($course['data_pubblicazione']) && !is_null($course['data_pubblicazione']) && intval($course['data_pubblicazione'] > 0)){
                    $publicationDate = ts2dFN($course['data_pubblicazione']);}else{ $publicationDate = '-';}
                
                if(isset($course['tipo_servizio']) && isset($_SESSION['service_level'])){
                    $serviceType = $_SESSION['service_level'][intval($course['tipo_servizio'])];
                    
                }else{$serviceType = 'Corso Online';}
                
                $span_serviceType = CDOMElement::create('span');
                $span_serviceType->setAttribute('class', 'serviceType');
                $span_serviceType->addChild(new CText($serviceType));
                
                if(isset($course['duration_hours'])){
                    $duration = $course['duration_hours'];}else{$duration = 0;}
                
                $dataAr=array($thead_data[0]=>$span_course_title->getHtml(),$thead_data[1]=>$creationDate,
                        $thead_data[2]=>$publicationDate,$thead_data[3]=>$span_serviceType->getHtml(),
                        $thead_data[4]=>$duration,$thead_data[5]=>$credits,$thead_data[6]=>$nodeNumber,
                        $thead_data[7]=>$activitiesNumber,$thead_data[8]=>$instanceNumber
                    );
                
                $caption=translateFN('Dettaglio corsi autore');

                /*
                 * Settings for Sort date columns in DataTable
                 */
                $retArray['columnDefs'][] = array(
                    'sType'=>'date-eu',
                    'aTargets'=>[1]
                );
                $retArray['columnDefs'][] = array(
                    'sType'=>'date-eu',
                    'aTargets'=>[2]
                );

            }
            
            array_push($total_results,$dataAr);
        }
            
            $result_table = BaseHtmlLib::tableElement('class:User_table', $thead_data, $total_results,null,$caption);
            $result=$result_table->getHtml();
//            $retArray=array("status"=>"OK","html"=>$result);
            $retArray['status']='OK';
            $retArray['html']=$result;
    }else{

        $span_error = CDOMElement::create('span');
        $span_error->setAttribute('class', 'ErrorSpan');
        $span_error->addChild(new CText(translateFN('Nessun dato trovato')));
        $retArray['status']='ERROR';
        $retArray['html']=$span_error->getHtml();
        
//        $retArray=array("status"=>"ERROR","html"=>$span_error->getHtml());
    }
    echo json_encode($retArray);
}