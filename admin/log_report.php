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


if(defined('CONFIG_LOG_REPORT') && CONFIG_LOG_REPORT && is_array($GLOBALS['LogReport_Array']) && count($GLOBALS['LogReport_Array']) && is_array($log_dataAr) && count($log_dataAr)){ 
    foreach($GLOBALS['LogReport_Array'] as $key=>$tableInfo){
        if($tableInfo['show']==true){
            if(!isset($thead_data[$key])){
                $thead_data[$key]=translateFN($tableInfo['label']);
            }
            foreach($log_dataAr as $providerName=>$providerData){
                if(isset($providerData['user_subscribed']) && intval($providerData['user_subscribed'])>0){
                    $totStudentSubscribed=intval($providerData['user_subscribed']);
                }else{
                    $totStudentSubscribed=0;
                }
                if(isset($providerData[$key])){
                    $testersData_Ar[$providerName][$key]=$providerData[$key];
                }else{
                    if(strpos($key,'student_CompletedStatus_sessStarted')===0){
                        if(intval($providerData['student_CompletedStatus_sessStarted'])>0 && $totStudentSubscribed>0){
                            $StatusCompleted_SessStared=intval($providerData['student_CompletedStatus_sessStarted']);
                            $testersData_Ar[$providerName][$key]=number_format(($StatusCompleted_SessStared*100)/$totStudentSubscribed,2);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }elseif(strpos($key,'student_CompletedStatus_sessionEnd')===0){
                        if(intval($providerData['student_CompletedStatus_sessionEnd'])>0 && $totStudentSubscribed>0){
                            $StatusCompleted_SessEnd=intval($providerData['student_CompletedStatus_sessionEnd']);
                            $testersData_Ar[$providerName][$key]=number_format(($StatusCompleted_SessEnd*100)/$totStudentSubscribed,2);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }elseif(strpos($key,'tot_student_CompletedStatus')===0){
                        if(intval($providerData['tot_student_CompletedStatus'])>0 && $totStudentSubscribed>0){
                            $tot_student_CompletedStatus=intval($providerData['tot_student_CompletedStatus']);
                            $testersData_Ar[$providerName][$key]=number_format(($tot_student_CompletedStatus*100)/$totStudentSubscribed,2);
                        }else{
                            $testersData_Ar[$providerName][$key]=0;
                        }
                    }
                }
            }
        }
    }
}


//if($userObj->getType()==AMA_TYPE_ADMIN){
//    $totalAr['provider'] = translateFN('totale'); 
//    foreach ($testersData_Ar as $singleProviderAr) {
//        foreach ($singleProviderAr as $key => $value) {
//            if (is_numeric($singleProviderAr[$key])) {
//                $totalAr[$key] +=  $singleProviderAr[$key];
//            }
//        }
//    }
//}
if($userObj->tipo==AMA_TYPE_ADMIN){
    $caption=translateFN('Riepilogo attività dei provider');
}
elseif($userObj->tipo==AMA_TYPE_SWITCHER){
    $caption=translateFN('Riepilogo attività del provider');
}
$table = BaseHtmlLib::tableElement('id:table_log_report',$thead_data, $testersData_Ar);  
  
$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
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

