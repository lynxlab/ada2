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
    AMA_TYPE_SWITCHER => array('layout', 'course','course_instance')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require_once 'include/switcher_functions.inc.php';

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
SwitcherHelper::init($neededObjAr);

require_once 'include/Subscription.inc.php';
require_once ROOT_DIR . '/include/Forms/CourseInstanceRemovalForm.inc.php';
/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($courseInstanceObj instanceof Course_instance && $courseInstanceObj->isFull()
        && $courseObj instanceof Course && $courseObj->isFull()) {
        $form = new CourseInstanceRemovalForm();
        if($form->isValid()) {
            if($_POST['delete'] == 1) {
                $courseInstanceId = $courseInstanceObj->getId();
                if(Subscription::deleteAllSubscriptionsToClassRoom($courseInstanceId)) {
                    $result = $dh->course_instance_tutors_unsubscribe($courseInstanceId);
                    if($result === true) {
                        $result = $dh->course_instance_remove($courseInstanceId);
                        if(!AMA_DataHandler::isError($result)) {
                           // fare unset di sess_courseInstanceObj se c'è
                           
                        	/**
                        	 * delete budget details if needed
                        	 */
                        	if (defined('MODULES_CLASSBUDGET') && MODULES_CLASSBUDGET) {
                        		require_once MODULES_CLASSBUDGET_PATH . '/include/classbudgetAPI.inc.php';
                        		$budgetAPI = new classbudgetAPI();
                        		if (AMA_DB::isError($budgetAPI->deleteBudgetCourseInstance($courseInstanceId))) {
                        			// handle delete budget error here if you wish
                        		}
                        	}
                        	
                            header('Location: list_instances.php?id_course='.$courseObj->getId());
                            exit();
                        } else {
                            $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della classe.') . '(1)');
                        }
                    } else {
                        $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della classe'). '(2)');
                    }
                } else {
                    $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della classe'). '(3)');
                }
            } else {
                $data = new CText(translateFN('La cancellazione della classe è stata annullata'));
            }
        } else {
            $data = new CText(translateFN('I dati inseriti nel form non sono validi'));
        }
    } else {
        $data = new CText(translateFN('Classe non trovata'));
    }
} else {
    if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $data = new CText(translateFN('Corso non trovato'));
    } elseif (!($courseInstanceObj instanceof Course_instance) || !$courseInstanceObj->isFull()) {
        $data = new CText(translateFN('Classe non trovata'));
    } else {
        $formData = array(
            'id_course' => $courseObj->getId(),
            'id_course_instance' => $courseInstanceObj->getId()
        );
        $data = new CourseInstanceRemovalForm();
        $data->fillWithArrayData($formData);
    }
}

$label = translateFN('Cancellazione di una istanza corso');
$help = translateFN('Da qui il provider admin può cancellare una istanza corso esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module :'',
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);