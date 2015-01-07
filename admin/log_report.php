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

$log_dataAr = array();

if($userObj->getType()==AMA_TYPE_ADMIN){
    $log_dataAr =  Multiport::log_report();
}elseif($userObj->getType()==AMA_TYPE_SWITCHER){
    $log_dataAr=Multiport::log_report($userObj->getDefaultTester());
}

$label = translateFN("Log report");
$data = CDOMElement::create('div');


$head_provider = translateFN("provider");
$head_desc_user = translateFN("utenti registrati");
$head_desc_course =translateFN("totale corsi");
$head_desc_sessions = translateFN("edizioni iniziate");
$head_desc_sessions_assigned = translateFN("utenti iscritti");
$head_desc_sessions_closed = translateFN("edizioni chiuse");
$head_desc_messages = translateFN("messaggi");
$head_desc_events = translateFN("appuntamenti");
$head_desc_visits = translateFN("pagine visitate");
$head_desc_chatrooms = translateFN("chat");
$head_desc_video_chatrooms = translateFN("video chat");


$thead_data=array($head_desc_sessions,$head_desc_sessions_closed,$head_desc_messages,$head_desc_events,$head_desc_visits,$head_desc_chatrooms,$head_desc_video_chatrooms);

/* if isset $_SESSION['service_level'] it means that the istallation supports course type */
if(isset($_SESSION['service_level'])){
    $arrayService=array();
    foreach($_SESSION['service_level'] as $key=>$value){
        array_push($arrayService, $value);
    }
    $thead_data=array_merge($arrayService,$thead_data);
}

array_unshift($thead_data,$head_provider,$head_desc_user,$head_desc_sessions_assigned,$head_desc_course);
if($userObj->getType()==AMA_TYPE_ADMIN){
    $totalAr['provider'] = translateFN('totale'); 
    foreach ($log_dataAr as $singleProviderAr) {
        foreach ($singleProviderAr as $key => $value) {
            if (is_numeric($singleProviderAr[$key])) {
                $totalAr[$key] +=  $singleProviderAr[$key];
            }
        }
    }
}
if($userObj->tipo==AMA_TYPE_ADMIN){
    $caption=translateFN('Riepilogo attività dei provider');
}
elseif($userObj->tipo==AMA_TYPE_SWITCHER){
    $caption=translateFN('Riepilogo attività del provider');
}
$table = BaseHtmlLib::tableElement('id:table_log_report',$thead_data, $log_dataAr,$totalAr,$caption);  
  
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

