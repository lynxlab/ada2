<?php

/**
 * List users - this module provides list users functionality
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
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */

$type = DataValidator::validate_not_empty_string($_GET['list']);
$fieldsAr = array('nome','cognome','username');
switch($type) {
    case 'authors':
        $usersAr = $dh->get_authors_list($fieldsAr);
        break;
    case 'tutors':
        $usersAr = $dh->get_tutors_list($fieldsAr);
        break;
    case 'students':
    default:
    	/**
    	 * @author giorgio 29/mag/2013
    	 * 
    	 * if we're listing students, let's add the stato field as well
    	 */
    	array_push($fieldsAr, 'stato');
        $usersAr = $dh->get_students_list($fieldsAr);
        break;
}

//retrieve data for tooltips
$ids_users = array();
switch($type) {
	case 'tutors':
	case 'students':
		$do_tooltips = true;
		foreach($usersAr as $k=>$v) {
			$ids_users[] = $v[0];
		}

		if ($type == 'students') {
			$tooltip_course_instances = $dh->get_students_subscribed_course_instance($ids_users,false,true);
		}
		elseif ($type == 'tutors') {
			$tooltip_course_instances = $dh->get_tutors_assigned_course_instance($ids_users);
		}

		//create tooltips with tutor's assignments (html + javascript)
		$tooltips = '';
		$js = '<script type="text/javascript">';
		foreach($tooltip_course_instances as $k=>$v) {
			$ul = CDOMElement::create('ul');
			if (!empty($v)) {
				foreach($v as $i=>$l) {
					$nome_corso = $l['titolo'].(!empty($l['title'])?' - '.$l['title']:'');
					$li = CDOMElement::create('li');
					$li->addChild(new CText($nome_corso));
					$ul->addChild($li);
				}
			}
			else {
				$nome_corso = translateFN('Nessun corso trovato');
				$li = CDOMElement::create('li');
				$li->addChild(new CText($nome_corso));
				$ul->addChild($li);
			}

			$tip = CDOMElement::create('div','id:tooltipContent'.$k);
			if ($type == 'students') {
				$tip->addChild(new CText(translateFN('Studente iscritto ai seguenti corsi:<br />')));
			}
			elseif ($type == 'tutors') {
				$tip->addChild(new CText(translateFN('Tutor assegnato ai seguenti corsi:<br />')));
			}

			$tip->addChild($ul);
			$tooltips.=$tip->getHtml();
			$js.= 'new Tooltip("tooltip'.$k.'", "tooltipContent'.$k.'", {DOM_location: {parentId: "header"}, className: "tooltip", offset: {x:+15, y:0}, hook: {target:"rightMid", tip:"leftMid"}});'."\n";
		}
		$js.= '</script>';
		$tooltips.=$js;
		//end
	break;

	default:
		//for the others case, we don't need tooltips
		$do_tooltips = false;
		$tooltips = '';
	break;
}
if(is_array($usersAr) && count($usersAr) > 0) {
    $UserNum = count($usersAr);
    $thead_data = array(
       translateFN('id'),
       translateFN('nome e cognome'),
       translateFN('username'),
       translateFN('azioni')
    );
    /**
     * @author giorgio 29/mag/2013
     * 
     * if we're listing students, let's add the stato field as well
     */
    if ($type!='authors' && $type!='tutors') array_push ($thead_data, translateFN('Confermato'));
    
    $tbody_data = array();
    $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
    $view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');
    foreach($usersAr as $user) {
        $userId = $user[0];
		if ($do_tooltips)
		{
			$nome_link = CDOMElement::create('a','id:tooltip'.$userId);
			$nome_link->setAttribute('href','javascript:void(0);');
			$nome_link->addChild(new CText($user[1].' '.$user[2]));
			$nome_link = $nome_link->getHtml();
		}
		else
		{
			$nome_link = $user[1].' '.$user[2];
		}

        $edit_link = BaseHtmlLib::link("edit_user.php?id_user=$userId", $edit_img->getHtml());
        $view_link = BaseHtmlLib::link("view_user.php?id_user=$userId", $view_img->getHtml());
        $delete_link = BaseHtmlLib::link("delete_user.php?id_user=$userId",
                translateFN('Delete user'));
        $actions = BaseHtmlLib::plainListElement('class:inline_menu',array($edit_link, $view_link, $delete_link));
        /**
         * @author giorgio 29/mag/2013
         *
         * if we're listing students, let's add the stato field as well
         */
        if ($type!='authors' && $type!='tutors')  $isConfirmed = ($user[4] == ADA_STATUS_REGISTERED) ? translateFN("Si") : translateFN("No");
        
        $tmpArray = array($userId, $nome_link, $user[3], $actions);
        
        /**
         * @author giorgio 29/mag/2013
         *
         * if we're listing students, let's add the stato field as well
         */
        if ($type!='authors' && $type!='tutors') array_push ($tmpArray, $isConfirmed);

        $tbody_data[] = $tmpArray;
    }
    $data = BaseHtmlLib::tableElement('id:table_users', $thead_data, $tbody_data);
} else {
    $data = new CText(translateFN('Non sono stati trovati utenti'));
}

$label = translateFN('Lista utenti');
$help = translateFN('Da qui il provider admin può vedere la lista degli utenti presenti sul provider');
$help .= ' ' .translateFN('Numero utenti'). ': '. $UserNum;

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml().$tooltips,
    'module' => $module,
    'messages' => $user_messages->getHtml()
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT
	);
$layout_dataAr['CSS_filename']= array(
		JQUERY_DATATABLE_CSS
	);
  $render = null;
  $options['onload_func'] = 'dataTablesExec()';
  ARE::render($layout_dataAr, $content_dataAr, $render, $options);

//ARE::render($layout_dataAr, $content_dataAr);