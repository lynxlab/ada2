<?php
/**
 * ADMIN.
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
$allowedUsersAr = array(AMA_TYPE_ADMIN,AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_ADMIN => array('layout'),
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = admin!

include_once 'include/admin_functions.inc.php';
include_once ROOT_DIR.'/config/config_log_report.inc.php';

$label = translateFN("Log report");
$data = CDOMElement::create('div');

$thead_data=array();
$testersData_Ar=array();
$log_dataAr = array();

if($userObj->getType()==AMA_TYPE_ADMIN){
    $log_dataAr =  Multiport::log_report();
}elseif($userObj->getType()==AMA_TYPE_SWITCHER){
    $log_dataAr=Multiport::log_report($userObj->getDefaultTester());
}
/* Set services level in $GLOBALS['LogReport_Array']*/
if (defined('CONFIG_LOG_REPORT') && CONFIG_LOG_REPORT && is_array($GLOBALS['LogReport_Array']) && count($GLOBALS['LogReport_Array']) ){
    $service_position=0;
    $arrayService=array();
    foreach($GLOBALS['LogReport_Array'] as $key=>$value){
        if(strpos($key,'service_level')===0){
            if($value['show']==true){
                /* if isset $_SESSION['service_level'] it means that the istallation supports course type */
                if(isset($_SESSION['service_level'])){
                    foreach($_SESSION['service_level'] as $key_service=>$value){
                        $arrayService['course_'.$key_service]=array('label'=>$value,'show'=>true);
                    }
                }
            }
            unset($GLOBALS['LogReport_Array'][$key]);
            break;
        }else{
            $service_position++;
        }
    }
    if(!empty($arrayService)){
       $GLOBALS['LogReport_Array']= array_slice($GLOBALS['LogReport_Array'], 0, $service_position,true)+ $arrayService+array_slice($GLOBALS['LogReport_Array'], $service_position,null,true);
    }
}

/*  Sara-14/01/2015
 *  This cycle builds two arrays: $thead_data and $testersData_Ar like mirror of $GLOBALS['LogReport_Array'].
 *  Be required the total match of arrays to show correctly table. es: $thead_data={A,B,C} $testersData_Ar={a1,b1,c1}
 */
if(defined('CONFIG_LOG_REPORT') && CONFIG_LOG_REPORT && is_array($GLOBALS['LogReport_Array']) && count($GLOBALS['LogReport_Array']) && is_array($log_dataAr) && count($log_dataAr)){ 
    $checkAr=reset($log_dataAr);
    foreach($GLOBALS['LogReport_Array'] as $key=>$tableInfo){
        if($tableInfo['show']==true && array_key_exists($key, $checkAr)){
            if(!isset($thead_data[$key])){
                /* init Tooltip */
                if(strpos($key,'course_')===0){
                    $title=  translateFN('Numero corsi di tipo - '.$tableInfo['label']);
                    $span_label = CDOMElement::create('span');
                    $span_label->setAttribute('title', $title);
                    $span_label->setAttribute('class', 'Rate tooltip');
                    $span_label->addChild(new CText($tableInfo['label']));
                    $thead_data[$key]=$span_label->getHtml();
                }else{
                    switch($key){
                        case 'final_users':
                            $title=  translateFN('Utenti registrati al provider');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'user_subscribed':
                            $title=  translateFN('Utenti iscritti ad almeno un corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'sessions_started':
                            $title=  translateFN('Classi iniziate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'sessions_closed':
                            $title=  translateFN('Classi terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessStarted_Rate':
                            $title=  translateFN('Percentuale di completamento delle classi in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessionEnd_Rate':
                            $title=  translateFN('Percentuale di completamento delle classi terminate');    
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_student_CompletedStatus_Rate':
                            $title=  translateFN('Percentuale di completamento calcolata sulle classi in corso e su quelle terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_Session':
                            $title=  translateFN('Totale classi calcolato sommando le edizioni iniziate, le edizioni terminate, le edizioni esistenti me non ancora iniziate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessStarted':
                            $title=  translateFN('Numero di studenti che hanno completato le classi in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessionEnd':
                            $title=  translateFN('Numero di studenti che hanno completato le classi terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_student_CompletedStatus':
                            $title=  translateFN('Quantità calcolata  sommando gli studenti che hanno completato le classi iniziate e quelli che hanno compleato le classi terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'Rate tooltip');
                            $span_label->addChild(new CText($tableInfo['label']));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        default:
                            $thead_data[$key]=translateFN($tableInfo['label']);
                    }
                }
            }
            foreach($log_dataAr as $providerName=>$providerData){
                if(isset($providerData[$key]) && ((is_numeric($providerData[$key]) && intval($providerData[$key])>=0)||( !is_numeric($providerData[$key])))){
                    if($userObj->getType()==AMA_TYPE_SWITCHER){
                        if(array_key_exists(preg_replace('/course_/', '',$key), $_SESSION['service_level'])){
                            $service_id=preg_replace('/course_/', '',$key);
                            $link_Service_level= BaseHtmlLib::link("../switcher/list_courses.php?filter=$service_id", $providerData[$key]);
                            $testersData_Ar[$providerName][$key]=$link_Service_level->getHtml();
                        }elseif(strpos($key,'course')===0){
                            $link_Courses= BaseHtmlLib::link("../switcher/list_courses.php", $providerData[$key]);
                            $testersData_Ar[$providerName][$key]=$link_Courses->getHtml();
                        }elseif(strpos($key,'final_users')===0){
                            $link_Users= BaseHtmlLib::link("../switcher/list_users.php?list=students", $providerData[$key]);
                            $testersData_Ar[$providerName][$key]=$link_Users->getHtml();
                        }
                    }
                    if(!isset($testersData_Ar[$providerName][$key])){
                        $testersData_Ar[$providerName][$key]=$providerData[$key];
                    }
                }else{
                    /* rates calculation */
                    if(strpos($key,'student_CompletedStatus_sessStarted_Rate')===0){
                        $StudentCompleted_SessStared=intval($providerData['student_CompletedStatus_sessStarted']);
                        $StudentSubscribed_SessStared=intval($providerData['student_subscribedStatus_sessStarted']);
                        $totStudent=$StudentCompleted_SessStared+$StudentSubscribed_SessStared;
                        if($StudentCompleted_SessStared >0 || $StudentSubscribed_SessStared >0){
                            $testersData_Ar[$providerName][$key]=number_format(($StudentCompleted_SessStared*100)/$totStudent,1);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }elseif(strpos($key,'student_CompletedStatus_sessionEnd_Rate')===0){
                        $StudentCompleted_SessEnd=intval($providerData['student_CompletedStatus_sessionEnd']);
                        $StudentSubscribed_SessEnd=intval($providerData['student_subscribedStatus_sessEnd']);
                        $totStudent=$StudentCompleted_SessEnd+$StudentSubscribed_SessEnd;
                        if($StudentCompleted_SessEnd >0 || $StudentSubscribed_SessEnd >0){
                            $testersData_Ar[$providerName][$key]=number_format(($StudentCompleted_SessEnd*100)/$totStudent,1);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }elseif(strpos($key,'tot_student_CompletedStatus_Rate')===0){
                        $tot_student_CompletedStatus=intval($providerData['tot_student_CompletedStatus']);
                        $tot_student_subscribedStatus=intval($providerData['tot_student_subscribedStatus']);
                        $totStudent=$tot_student_CompletedStatus+$tot_student_subscribedStatus;
                        if($tot_student_CompletedStatus >0 || $tot_student_subscribedStatus >0){
                           $testersData_Ar[$providerName][$key]=number_format(($tot_student_CompletedStatus*100)/$totStudent,1);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }
                }
            }
        }
    }
}

$totalAr=array();
$student_subscribedStatus_sessStarted=0;
$student_CompletedStatus_sessStarted=0;
$student_subscribedStatus_sessEnd=0;
$student_CompletedStatus_sessionEnd=0;
$tot_student_subscribedStatus=0;
$tot_student_CompletedStatus=0;

if($userObj->getType()==AMA_TYPE_ADMIN){
    /* values for rates calculation in tfoot */
    foreach ($log_dataAr as $singleProviderAr) {
        foreach ($singleProviderAr as $key => $value) {
            switch($key){
                case 'student_subscribedStatus_sessStarted':
                    $student_subscribedStatus_sessStarted += $singleProviderAr[$key];
                    break;
                case 'student_CompletedStatus_sessStarted':
                    $student_CompletedStatus_sessStarted += $singleProviderAr[$key];
                    break;
                case 'student_subscribedStatus_sessEnd':
                    $student_subscribedStatus_sessEnd += $singleProviderAr[$key];
                    break;
                case 'student_CompletedStatus_sessionEnd':
                    $student_CompletedStatus_sessionEnd += $singleProviderAr[$key];
                    break;
                case 'tot_student_subscribedStatus':
                    $tot_student_subscribedStatus += $singleProviderAr[$key];
                    break;
                case 'tot_student_CompletedStatus':
                    $tot_student_CompletedStatus += $singleProviderAr[$key];
                    break;
                
                }
        }
    }

    foreach ($testersData_Ar as $singleProviderAr) {
        foreach ($singleProviderAr as $key => $value) {
            if (isset($singleProviderAr[$key]) && is_numeric($singleProviderAr[$key])) {
                if(!isset($totalAr[$key])){
                    $totalAr[$key]=0;
                }
                if(strpos($key,'student_CompletedStatus_sessStarted_Rate')===0){
                    if($student_subscribedStatus_sessStarted >0 || $student_CompletedStatus_sessStarted >0){
                        $totalAr[$key]=number_format(($student_CompletedStatus_sessStarted*100)/($student_subscribedStatus_sessStarted+$student_CompletedStatus_sessStarted),1);
                    }
                    else{$totalAr[$key]=0;}
                }elseif(strpos($key,'student_CompletedStatus_sessionEnd_Rate')===0){
                    if($student_subscribedStatus_sessEnd >0 || $student_CompletedStatus_sessionEnd >0){
                        $totalAr[$key]=number_format(($student_CompletedStatus_sessionEnd*100)/($student_subscribedStatus_sessEnd+$student_CompletedStatus_sessionEnd),1);
                    }
                    else{$totalAr[$key]=0;}
                }elseif(strpos($key,'tot_student_CompletedStatus_Rate')===0){
                    if($tot_student_subscribedStatus >0 || $tot_student_CompletedStatus >0){
                        $totalAr[$key]=number_format(($tot_student_CompletedStatus*100)/($tot_student_subscribedStatus+$tot_student_CompletedStatus),1);
                    }
                    else{$totalAr[$key]=0;}
                }
                else{$totalAr[$key] +=  $singleProviderAr[$key];}
            }else{$totalAr[$key]=translateFN('totale');}
        }
    }
}

$home_link = CDOMElement::create('a','href:admin.php');
if($userObj->tipo==AMA_TYPE_ADMIN){
    $caption=translateFN('Riepilogo attività dei provider');
    $home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
}
elseif($userObj->tipo==AMA_TYPE_SWITCHER){
    $caption=translateFN('Riepilogo attività del provider');
    $home_link->addChild(new CText(translateFN("Home del provider admin")));
    $totalAr=null;
}
$table = BaseHtmlLib::tableElement('id:table_log_report',$thead_data, $testersData_Ar,$totalAr,$caption);  
$module = $home_link->getHtml() . ' > ' . $label;

$help  = null;

$menu_dataAr = array(
);
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $table->getHtml(), 
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);

$layout_dataAr['JS_filename'] = array(
                JQUERY,
                JQUERY_UI,
                JQUERY_DATATABLE,
                JQUERY_DATATABLE_DATE,
                JQUERY_NO_CONFLICT
        );

$layout_dataAr['CSS_filename']= array(
                JQUERY_UI_CSS,        
                JQUERY_DATATABLE_CSS
        );
$render = null;
$options['onload_func'] = 'initDoc('.(($userObj->getType()==AMA_TYPE_ADMIN) ? 1 : 0).')';
  /**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);

