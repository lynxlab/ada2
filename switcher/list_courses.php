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
$coursesAr = $dh->get_courses_list(array('nome', 'titolo', 'descrizione'));
if(is_array($coursesAr) && count($coursesAr) > 0) {
    $thead_data = array(
       translateFN('id'),
       translateFN('codice'),
       translateFN('titolo'),
       translateFN('descrizione'),
       translateFN('azioni')
    );
    $tbody_data = array();

    $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
    $view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');
    $instances_img = CDOMElement::create('img', 'src:img/student.png,alt:view');

    foreach($coursesAr as $course) {
        $courseId = $course[0];

        $edit_link = BaseHtmlLib::link("edit_course.php?id_course=$courseId", $edit_img->getHtml());
        $view_link = BaseHtmlLib::link("view_course.php?id_course=$courseId", $view_img->getHtml());
        $instances_link = BaseHtmlLib::link("list_instances.php?id_course=$courseId", $instances_img->getHtml());
		if (MODULES_TEST) {
			$survey_link = BaseHtmlLib::link(MODULES_TEST_HTTP.'/switcher.php?id_course='.$courseId, translateFN('Sondaggi'));
		}

        $add_instance_link = BaseHtmlLib::link("add_instance.php?id_course=$courseId", translateFN('Add instance'));
        $delete_course_link = BaseHtmlLib::link("delete_course.php?id_course=$courseId", translateFN('Delete course'));

		$actions = array($edit_link,$view_link,$instances_link);
		if (MODULES_TEST) {
			$actions[] = $survey_link;
		}
		$actions = array_merge($actions,array($add_instance_link,$delete_course_link));
        $actions = BaseHtmlLib::plainListElement('class:inline_menu',$actions);

        $tbody_data[] = array($courseId, $course[1],  $course[2], $course[3], $actions);
    }
    $data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data);
} else {
    $data = new CText(translateFN('Non sono stati trovati corsi'));
}

$label = translateFN('Lista corsi');
$help = translateFN('Da qui il provider admin può vedere la lista dei corsi presenti sul provider');
$chatrooms_link = '<a href="'.HTTP_ROOT_DIR . '/comunica/list_chatrooms.php">'. translateFN('Lista chatrooms');
$Li_edit_home_page="";
if(!MULTIPROVIDER)
{
   $Edit_home_page= CDOMElement::create('a','href:'.HTTP_ROOT_DIR .'/admin/edit_content.php');
   $Edit_home_page->addChild(new CText(translateFN("Edit home page contents")));
   $li_edit_home_page=CDOMElement::create('li');
   $li_edit_home_page->addChild($Edit_home_page);
   $Li_edit_home_page=$li_edit_home_page->getHtml();
}

   
$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module,
    'edit_home_page'=>$Li_edit_home_page,
    'ajax_chat_link' => $chatrooms_link,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);