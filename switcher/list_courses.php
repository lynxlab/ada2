<?php

/**
 * List courses - this module provides list courses functionality
 * 
 * 
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */
/**
 * Base config file 
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

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

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */

$coursesAr = $dh->get_courses_list(array('nome', 'titolo', 'descrizione','tipo_servizio'));
if(is_array($coursesAr) && count($coursesAr) > 0) {
    $thead_data = array(
       null,
       translateFN('id'),
       translateFN('codice'),
       translateFN('tipo'),
       translateFN('titolo'),
       translateFN('descrizione'),
       translateFN('azioni')
    );
    $tbody_data = array();

    $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
    $view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');
    $instances_img = CDOMElement::create('img', 'src:img/instances.png,alt:view');
    $add_instance_img= CDOMElement::create('img', 'src:img/add_instances.png,alt:view');
    $survey_img= CDOMElement::create('img', 'src:img/_exer.png,alt:view');
    $delete_img= CDOMElement::create('img', 'src:img/trash.png,alt:view');
            
    foreach($coursesAr as $course) {
    	$isPublicCourse = isset($_SESSION['service_level_info'][$course['tipo_servizio']]['isPublic']) &&     	
    					  ($_SESSION['service_level_info'][$course['tipo_servizio']]['isPublic']!=0);
        $imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
        $imgDetails->setAttribute('class', 'imgDetls tooltip');
        $imgDetails->setAttribute('title', translateFN('visualizza/nasconde la descrizione del corso'));
                
        $courseId = $course[0];
        $actions = array();
        $edit_link = BaseHtmlLib::link("edit_course.php?id_course=$courseId", $edit_img->getHtml());
        
        if(isset($edit_link)){
            $title=translateFN('Clicca per modificare il corso');
            $div_edit = CDOMElement::create('div');
            $div_edit->setAttribute('title', $title);
            $div_edit->setAttribute('class', 'tooltip');
            $div_edit->addChild(($edit_link));
            $actions[] = $div_edit;
        }
        
        $view_link = BaseHtmlLib::link("view_course.php?id_course=$courseId", $view_img->getHtml());
        
        if(isset($view_link)){
            $title=translateFN('Clicca per visualizzare il corso');
            $div_view = CDOMElement::create('div');
            $div_view->setAttribute('title', $title);
            $div_view->setAttribute('class', 'tooltip');
            $div_view->addChild(($view_link));
            $actions[] = $div_view;
        }
        
        if(!$isPublicCourse) $instances_link = BaseHtmlLib::link("list_instances.php?id_course=$courseId", $instances_img->getHtml());
        
        if(isset($instances_link)){
            $title=translateFN('Gestione classi');
            $div_instances = CDOMElement::create('div');
            $div_instances->setAttribute('title', $title);
            $div_instances->setAttribute('class', 'tooltip');
            $div_instances->addChild(($instances_link));
            $actions[] = $div_instances;
        }
        
        if (!$isPublicCourse && MODULES_TEST) {
            $survey_link = BaseHtmlLib::link(MODULES_TEST_HTTP.'/switcher.php?id_course='.$courseId, $survey_img->getHtml());
            $title=translateFN('Sondaggi');
            $div_survey = CDOMElement::create('div');
            $div_survey->setAttribute('title', $title);
            $div_survey->setAttribute('class', 'tooltip');
            $div_survey->addChild(($survey_link));
            $actions[] = $div_survey;
        }

        if(!$isPublicCourse) $add_instance_link = BaseHtmlLib::link("add_instance.php?id_course=$courseId", $add_instance_img->getHtml());
        
        if(isset($add_instance_link)){
            $title=translateFN('Aggiungi classe');
            $div_AddInstances = CDOMElement::create('div');
            $div_AddInstances->setAttribute('title', $title);
            $div_AddInstances->setAttribute('class', 'tooltip');
            $div_AddInstances->addChild(($add_instance_link));
            $actions[] = $div_AddInstances;
        }
        
        $delete_course_link = BaseHtmlLib::link("delete_course.php?id_course=$courseId", $delete_img->getHtml());
        
        if(isset($delete_course_link)){
            $title=translateFN('Cancella corso');
            $div_delete = CDOMElement::create('div');
            $div_delete->setAttribute('title', $title);
            $div_delete->setAttribute('class', 'tooltip');
            $div_delete->addChild(($delete_course_link));
            $actions[] = $div_delete;
        }
        
        $actions = BaseHtmlLib::plainListElement('class:inline_menu',$actions);
        $servicelevel=null;
         /* if isset $_SESSION['service_level'] it means that the istallation supports course type */
        if(isset($_SESSION['service_level'][$course[4]])){
            $servicelevel=$_SESSION['service_level'][$course[4]];
        }
        if(!isset($servicelevel)){$servicelevel='Corso Online';}
        
        
        $tbody_data[] = array($imgDetails,$courseId, $course[1],$servicelevel,  $course[2], $course[3], $actions);
    }
    $data = BaseHtmlLib::tableElement('id:table_list_courses', $thead_data, $tbody_data);
} else {
    $data = new CText(translateFN('Non sono stati trovati corsi'));
}

$filter=null;
if(isset($_GET['filter']) && isset($_SESSION['service_level'])){
    $filter=$_SESSION['service_level'][$_GET['filter']];
    $label = translateFN('Lista corsi di tipo "').$filter.'"';
}
else{ $label = translateFN('Lista corsi'); }

$help = translateFN('Da qui il provider admin può vedere la lista dei corsi presenti sul provider');
$Li_edit_home_page="";
   
$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module : '',
    'edit_profile'=>$userObj->getEditProfilePage(),
    'messages' => $user_messages->getHtml()
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
$filter="'".$filter."'";
$options['onload_func'] = 'initDoc('.$filter.')';

ARE::render($layout_dataAr, $content_dataAr,$render,$options);