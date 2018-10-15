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
    AMA_TYPE_SWITCHER => array('layout','course')
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

require_once ROOT_DIR . '/include/Forms/CourseRemovalForm.inc.php';
/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if($courseObj instanceof Course && $courseObj->isFull()) {
        $form = new CourseRemovalForm($courseObj);
        if($form->isValid()) {
            if($_POST['deleteCourse'] == 1) {
                $courseId = $courseObj->getId();
                $serviceInfo = $common_dh->get_service_info_from_course($courseId);
                if(!AMA_Common_DataHandler::isError($serviceInfo)) {
                    $serviceId = $serviceInfo[0];
                    $result = $common_dh->delete_service($serviceId);
                    if(!AMA_Common_DataHandler::isError($result)) {
                        $result = $common_dh->unlink_service_from_course($serviceId, $courseId);
                        if(!AMA_DataHandler::isError($result)) {
                            $result = $dh->remove_course($courseId);
                            if(AMA_DataHandler::isError($result)) {
                                $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(1)');
                            } else {
                            	if (defined('MODULES_TEST') && MODULES_TEST) {
                            		require_once MODULES_TEST_PATH . '/include/AMATestDataHandler.inc.php';
                            		$test_db = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
                            		if (AMA_DB::isError($test_db->test_removeCourseNodes($courseId))) {
                            			// handle error here if needed
                            		}
                            	}
                                unset($_SESSION['sess_courseObj']);
                                unset($_SESSION['sess_id_course']);
                                header('Location: list_courses.php');
                                exit();
                            }
                        } else {
                            $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(2)');
                        }
                    } else {
                        $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(3)');
                    }
                } else {
                    $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(4)');
                }
            } else {
                $data = new CText(sprintf(translateFN('La cancellazione del corso "%s" è stata annullata.'), $courseObj->getTitle()));
            }
        } else {
            $data = new CText(translateFN('I dati inseriti nel form non sono validi'));
        }
    } else {
        $data = new CText(translateFN('Corso non trovato'));
    }
}
else {
    if($courseObj instanceof Course && $courseObj->isFull()) {
        $result = $dh->course_has_instances($courseObj->getId());
        if(AMA_DataHandler::isError($result)) {
            $data = new CText(translateFN('Si è verificato un errore nella lettura dei dati del corso'));
        } else if($result == true) {
            $data = new CText(
                        sprintf(translateFN('Il corso "%s" ha delle classi associate, non è possibile rimuoverlo direttamente.')
                                , $courseObj->getTitle()
                                )
                    );
        } else {
            $data = new CourseRemovalForm($courseObj);
        }
    }
    else {
        $data = new CText(translateFN('Corso non trovato'));
    }
}


$label = translateFN('Cancellazione di un corso');
$help = translateFN('Da qui il provider admin può cancellare un corso esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);