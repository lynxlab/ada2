<?php

/**
 * File edit_course.php
 *
 * The switcher can use this module to update the informations about an existing
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
    AMA_TYPE_SWITCHER => array('layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
include_once ROOT_DIR . '/services/include/NodeEditing.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/CourseModelForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $providerAuthors = $dh->find_authors_list(array('username'), '');
    $authors = array();
    foreach ($providerAuthors as $author) {
        $authors[$author[0]] = $author[1];
    }

    $availableLanguages = Translator::getSupportedLanguages();
    $languages = array();
    foreach ($availableLanguages as $language) {
        $languages[$language['id_lingua']] = $language['nome_lingua'];
    }

    $form = new CourseModelForm($authors, $languages);
    $form->fillWithPostData();
    if ($form->isValid()) {
        $course = array(
            'nome' => isset($_POST['nome']) ? $_POST['nome'] : null,
            'titolo' => isset($_POST['titolo']) ? $_POST['titolo'] : null,
            'descr' => isset($_POST['descrizione']) ? $_POST['descrizione'] : null,
            'd_create' => isset($_POST['data_creazione']) ? $_POST['data_creazione'] : null,
            'd_publish' => isset($_POST['data_pubblicazione']) ? $_POST['data_pubblicazione'] : null,
            'id_autore' => isset($_POST['id_utente_autore']) ? $_POST['id_utente_autore'] : null,
            'id_nodo_toc' => isset($_POST['id_nodo_toc']) ? $_POST['id_nodo_toc'] : null,
            'id_nodo_iniziale' => isset($_POST['id_nodo_iniziale']) ? $_POST['id_nodo_iniziale'] : null,
            'media_path' => isset($_POST['media_path']) ? $_POST['media_path'] : null,
            'id_lingua' => isset($_POST['id_lingua']) ? $_POST['id_lingua'] : null,
            'static_mode' => isset($_POST['static_mode']) ? $_POST['static_mode'] : null,
            'crediti' => isset($_POST['crediti']) ? $_POST['crediti'] : null,
            'duration_hours' => isset($_POST['duration_hours']) ? $_POST['duration_hours'] : null,
            'service_level' => isset($_POST['service_level']) ? $_POST['service_level'] : null
        );
        $result = $dh->set_course($_POST['id_corso'], $course);

        if (!AMA_DataHandler::isError($result)) {
            $service_dataAr = $common_dh->get_service_info_from_course($_POST['id_corso']);
            if (!AMA_Common_DataHandler::isError($service_dataAr)) {
                $update_serviceDataAr = array(
                    'service_name' => $_POST['titolo'],
                    'service_description' => $_POST['descrizione'],
                    'service_level' => $_POST['service_level'],
                    'service_duration' => $service_dataAr[4],
                    'service_min_meetings' => $service_dataAr[5],
                    'service_max_meetings' => $service_dataAr[6],
                    'service_meeting_duration' => $service_dataAr[7]
                );
                $result = $common_dh->set_service($service_dataAr[0], $update_serviceDataAr);
                if (AMA_Common_DataHandler::isError($result)) {
                     $form = new CText("Si è verificato un errore durante l'aggiornamento dei dati del corso");
                } else {
                    // AGGIORNARE l'oggetto corso in sessione e poi fare il redirect a view_course.php
                    //header('Location: view_course.php?id_course=' . $_POST['id_corso']);
                    header('Location: list_courses.php');
                    exit();
                }
            }
        } else {
             $form = new CText("Si è verificato un errore durante l'aggiornamento dei dati del corso");
        }
    } else {
        $form = new CText('Form non valido');
    }
} else {
    if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $form = new CText(translateFN('Corso non trovato'));
    } else {
        $providerAuthors = $dh->find_authors_list(array('username'), '');
        $authors = array();
        foreach ($providerAuthors as $author) {
            $authors[$author[0]] = $author[1];
        }

        $availableLanguages = Translator::getSupportedLanguages();
        $languages = array();
        foreach ($availableLanguages as $language) {
            $languages[$language['id_lingua']] = $language['nome_lingua'];
        }

        $form = new CourseModelForm($authors, $languages);
        $form->addFileSection();

        if ($courseObj instanceof Course && $courseObj->isFull()) {
            $formData = array(
                'id_corso' => $courseObj->getId(),
                'id_utente_autore' => $courseObj->getAuthorId(),
                'id_lingua' => $courseObj->getLanguageId(),
                'id_layout' => $courseObj->getLayoutId(),
                'nome' => $courseObj->getCode(),
                'titolo' => $courseObj->getTitle(),
                'descrizione' => $courseObj->getDescription(),
                'id_nodo_iniziale' => $courseObj->getRootNodeId(),
                'id_nodo_toc' => $courseObj->getTableOfContentsNodeId(),
                'media_path' => $courseObj->getMediaPath(),
                'static_mode' => $courseObj->getStaticMode(),
                'data_creazione' => $courseObj->getCreationDate(),
                'data_pubblicazione' => $courseObj->getPublicationDate(),
                'crediti' =>  $courseObj->getCredits(), // modifica in Course
                'duration_hours' => $courseObj->getDurationHours(),
                'service_level'  =>$courseObj->getServiceLevel()
            );
            $form->fillWithArrayData($formData);
        } else {
            $form = new CText(translateFN('Corso non trovato'));
        }
    }
}

$label = translateFN('Modifica dei dati del corso');
$help = translateFN('Da qui il provider admin può modificare un corso esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $form->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		ROOT_DIR .'/js/switcher/edit_content.js'
);

$optionsAr['onload_func'] = 'initDateField();  includeFCKeditor(\'descrizione\');';
if ($courseObj instanceof Course && $courseObj->isFull()) {
	$optionsAr['onload_func'] .= 'initEditCourse('.$userObj->getId().','.$courseObj->getId().');';
}
ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);