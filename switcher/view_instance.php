<?php

/**
 * File view_instance.php
 *
 * The switcher can use this module to view the informations about an existing
 * course instance.
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
    AMA_TYPE_SWITCHER => array('layout', 'course_instance')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */
if($courseInstanceObj instanceof Course_instance && $courseInstanceObj->isFull()) {
    if($courseInstanceObj->getStartDate() == '') {
        $start_date = translateFN('Non iniziata');
    } else {
        $start_date = $courseInstanceObj->getStartDate();
    }

    $listData = array(
        'id istanza' => $courseInstanceObj->getId(),
        'data inizio' => $start_date,
        'data inizio previsto' => $courseInstanceObj->getScheduledStartDate(),
        //'layout' => $courseInstanceObj->getLayoutId(),
        'durata' => sprintf('%d giorni', $courseInstanceObj->getDuration()),
        'data fine' => $courseInstanceObj->getEndDate()
    );
    $data = BaseHtmlLib::labeledListElement('class:view_info', $listData);
} else {
    $data = new CText(translateFN('Classe non trovata'));
}

$label = translateFN("Visualizzazione dei dati dell'istanza corso");
$help = translateFN('Da qui il provider admin può visualizzare i dati di una istanza corso esistente');

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'edit_switcher'=>$edit_profile_link->getHtml(),
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);