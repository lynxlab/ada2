<?php

/**
 * File view_course.php
 *
 * The switcher can use this module to view the informations about an existing
 * course.
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
    AMA_TYPE_SWITCHER => array('layout','course')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */
if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
    $data = new CText(translateFN('Corso non trovato'));
} else {
    $authorObj = MultiPort::findUser($courseObj->getAuthorId());
    $language_info = Translator::getLanguageInfoForLanguageId($courseObj->getLanguageId());

    $formData = array(
        'id corso' => $courseObj->getId(),
        'autore' => $authorObj->getFullName(),
        'lingua' => $language_info['nome_lingua'],
        //'id_layout' => $courseObj->getLayoutId(),
        'codice corso' => $courseObj->getCode(),
        'titolo' => $courseObj->getTitle(),
        'descrizione' => $courseObj->getDescription(),
        'id nodo iniziale' => $courseObj->getRootNodeId(),
        'id nodo toc' => $courseObj->getTableOfContentsNodeId(),
        'media path' => $courseObj->getMediaPath(),
        //'static mode' => $courseObj->getStaticMode(),
        'data di creazione' => $courseObj->getCreationDate(),
        'data di pubblicazione' => $courseObj->getPublicationDate(),
        'crediti' => $courseObj->getCredits()
    );
    $data = BaseHtmlLib::labeledListElement('class:view_info', $formData);
}

$label = translateFN('Visualizzazione dei dati del corso');
$help = translateFN('Da qui il provider admin può visualizzare i dati di un corso esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'edit_profile'=>$userObj->getEditProfilePage(),
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);