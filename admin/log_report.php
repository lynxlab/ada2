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

include_once ROOT_DIR.'/config/config_log_report.inc.php';

$label = translateFN("Log report");
$data = CDOMElement::create('div');

$thead_data=array();
$testersData_Ar=array();
$log_dataAr = array();

if($userObj->getType()==AMA_TYPE_ADMIN){
    $Services_TypeAr=$GLOBALS['dh']->get_service_type($userObj->getId());
    if(!empty($Services_TypeAr) && !AMA_DB::isError($Services_TypeAr)){
        foreach($Services_TypeAr as $service){
            if(isset($service['livello_servizio']) && isset($service['nome_servizio'])){
                $Services_Type[$service['livello_servizio']]=translateFN($service['nome_servizio']);

            }
        }
    }
    else{
        if(defined('DEFAULT_SERVICE_TYPE') && defined('DEFAULT_SERVICE_TYPE_NAME')){
            $Services_Type[DEFAULT_SERVICE_TYPE]=translateFN(DEFAULT_SERVICE_TYPE_NAME);}
    }
    $log_dataAr =  Multiport::log_report(null,$Services_Type);
}elseif($userObj->getType()==AMA_TYPE_SWITCHER){
    $log_dataAr=Multiport::log_report($userObj->getDefaultTester(),null);
    $Services_Type=$_SESSION['service_level'];
}

/* Set services level in $GLOBALS['LogReport_Array']*/
if (defined('CONFIG_LOG_REPORT') && CONFIG_LOG_REPORT && is_array($GLOBALS['LogReport_Array']) && count($GLOBALS['LogReport_Array']) ){
    $service_position=0;
    $arrayService=array();
    foreach($GLOBALS['LogReport_Array'] as $key=>$value){
        if(strpos($key,'service_level')===0){
            if($value['show']==true){
                if(isset($Services_Type)){
                    foreach($Services_Type as $key_service=>$value){
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
                    $title=  translateFN('Numero corsi di tipo : '.$tableInfo['label']);
                    $span_label = CDOMElement::create('span');
                    $span_label->setAttribute('title', $title);
                    $span_label->setAttribute('class', 'tooltip');
                    $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                    $thead_data[$key]=$span_label->getHtml();
                }else{
                    switch($key){
                        case 'final_users':
                            $title=  translateFN('Utenti registrati al provider');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'user_subscribed':
                            $title=  translateFN('Utenti iscritti ad almeno un corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'course':
                            $title=  translateFN('Totale corsi');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'sessions_started':
                            $title=  translateFN('Edizioni in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'sessions_closed':
                            $title=  translateFN('Edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessStarted_Rate':
                            $title=  translateFN('Percentuale di completamento delle edizioni in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessionEnd_Rate':
                            $title=  translateFN('Percentuale di completamento delle edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_student_CompletedStatus_Rate':
                            $title=  translateFN('Percentuale di completamento calcolata sulle edizioni in corso e su quelle terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_Session':
                            $title=  translateFN('Totale edizioni calcolato sommando le edizioni in corso, le edizioni terminate, le edizioni esistenti me non ancora iniziate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessStarted':
                            $title=  translateFN('Numero di studenti che hanno completato le edizioni in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_subscribedStatus_sessStarted':
                            $title=  translateFN('Numero di studenti iscritti alle edizioni in corso');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_CompletedStatus_sessionEnd':
                            $title=  translateFN('Numero di studenti che hanno completato le edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'student_subscribedStatus_sessEnd':
                            $title=  translateFN('Numero di studenti iscritti alle edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_student_CompletedStatus':
                            $title=  translateFN('Quantità calcolata  sommando gli studenti che hanno completato le edizioni iniziate e quelli che hanno compleato le edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'tot_student_subscribedStatus':
                            $title=  translateFN('Quantità calcolata  sommando gli studenti iscritti alle edizioni iniziate e quelli iscritti alle edizioni terminate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'visits':
                            $title=  translateFN('Pagine visitate');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'system_messages':
                            $title=  translateFN('Numero di messaggi');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'chatrooms':
                            $title=  translateFN('Numero di chat');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
                            $thead_data[$key]=$span_label->getHtml();
                            break;
                        case 'videochatrooms':
                            $title=  translateFN('Numero di chatrooms');
                            $span_label = CDOMElement::create('span');
                            $span_label->setAttribute('title', $title);
                            $span_label->setAttribute('class', 'tooltip');
                            $span_label->addChild(new CText(translateFN($tableInfo['label'])));
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
                        if (strcmp($key,'provider')===0 && $userObj->getType()==AMA_TYPE_ADMIN) {
                            $provider_link = BaseHtmlLib::link('tester_profile.php?id_tester='.$providerData['provider_id'], $providerData[$key]);
                            $provider_link->setAttribute('class', 'provider_link ui tiny button');
                            $testersData_Ar[$providerName][$key] = $provider_link->getHtml();
                            unset($log_dataAr[$providerName]['provider_id']);
                        }
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
$Tot_student_subscribedStatus_sessStarted=0;
$Tot_student_CompletedStatus_sessStarted=0;
$Tot_student_subscribedStatus_sessEnd=0;
$Tot_student_CompletedStatus_sessionEnd=0;
$Tot_student_subscribedStatus=0;
$Tot_student_CompletedStatus=0;

if($userObj->getType()==AMA_TYPE_ADMIN){
    /* values for rates calculation in tfoot */
    foreach ($log_dataAr as $singleProviderAr) {
        foreach ($singleProviderAr as $key => $value) {
            switch($key){
                case 'student_subscribedStatus_sessStarted':
                    $Tot_student_subscribedStatus_sessStarted += $singleProviderAr[$key];
                    break;
                case 'student_CompletedStatus_sessStarted':
                    $Tot_student_CompletedStatus_sessStarted += $singleProviderAr[$key];
                    break;
                case 'student_subscribedStatus_sessEnd':
                    $Tot_student_subscribedStatus_sessEnd += $singleProviderAr[$key];
                    break;
                case 'student_CompletedStatus_sessionEnd':
                    $Tot_student_CompletedStatus_sessionEnd += $singleProviderAr[$key];
                    break;
                case 'tot_student_subscribedStatus':
                    $Tot_student_subscribedStatus += $singleProviderAr[$key];
                    break;
                case 'tot_student_CompletedStatus':
                    $Tot_student_CompletedStatus += $singleProviderAr[$key];
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
                    if($Tot_student_subscribedStatus_sessStarted >0 || $Tot_student_CompletedStatus_sessStarted >0){
                        $totalAr[$key]=number_format(($Tot_student_CompletedStatus_sessStarted*100)/($Tot_student_subscribedStatus_sessStarted+$Tot_student_CompletedStatus_sessStarted),1);
                    }
                    else{$totalAr[$key]=0;}
                }elseif(strpos($key,'student_CompletedStatus_sessionEnd_Rate')===0){
                    if($Tot_student_subscribedStatus_sessEnd >0 || $Tot_student_CompletedStatus_sessionEnd >0){
                        $totalAr[$key]=number_format(($Tot_student_CompletedStatus_sessionEnd*100)/($Tot_student_subscribedStatus_sessEnd+$Tot_student_CompletedStatus_sessionEnd),1);
                    }
                    else{$totalAr[$key]=0;}
                }elseif(strpos($key,'tot_student_CompletedStatus_Rate')===0){
                    if($Tot_student_subscribedStatus >0 || $Tot_student_CompletedStatus >0){
                        $totalAr[$key]=number_format(($Tot_student_CompletedStatus*100)/($Tot_student_subscribedStatus+$Tot_student_CompletedStatus),1);
                    }
                    else{$totalAr[$key]=0;}
                }
                else{$totalAr[$key] +=  $singleProviderAr[$key];}
            }else{$totalAr[$key]=translateFN('totale');}
        }
    }
}


if($userObj->tipo==AMA_TYPE_ADMIN){
    $caption=translateFN('Riepilogo attività dei provider');
    $home_link = CDOMElement::create('a','href:admin.php');
    $home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
}
elseif($userObj->tipo==AMA_TYPE_SWITCHER){
    $caption=translateFN('Riepilogo attività del provider');
    $home_link = CDOMElement::create('a','href:../switcher/list_courses.php');
    $home_link->addChild(new CText(translateFN("Home del provider admin")));
    $totalAr=null;
}
$table = BaseHtmlLib::tableElement('id:table_log_report,class:ui table',$thead_data, $testersData_Ar,$totalAr,$caption);
$module = $home_link->getHtml() . ' > ' . $label;

$help  = null;

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'label'        => $label,
  'help'         => $help,
  'data'         => $table->getHtml(),
  'module'       => $module,
);

$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_DATATABLE,
    SEMANTICUI_DATATABLE,
    JQUERY_DATATABLE_DATE,
    JQUERY_NO_CONFLICT,
);

$layout_dataAr['CSS_filename']= array(
    JQUERY_UI_CSS,
    SEMANTICUI_DATATABLE_CSS,
);
$render = null;
$options['onload_func'] = 'initDoc('.(($userObj->getType()==AMA_TYPE_ADMIN) ? 1 : 0).')';
  /**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);

