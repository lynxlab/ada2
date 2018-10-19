<?php
/**
 * Add user - this module provides add user functionality
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

include_once ROOT_DIR . '/admin/include/AdminUtils.inc.php';
/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserSubscriptionForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $form = new UserSubscriptionForm();
    $form->fillWithPostData();

    if ($form->isValid()) {
        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
            $user_layout = $_POST['layout'];
        } else {
            $user_layout = '';
        }

        $user_dataAr = $_POST;
        $user_dataAr['layout'] = $user_layout;
        $user_dataAr['stato'] = 0;

        switch ($_POST['tipo']) {
            case AMA_TYPE_STUDENT:
                $userObj = new ADAUser($user_dataAr);
                break;
            case AMA_TYPE_AUTHOR:
                $userObj = new ADAAuthor($user_dataAr);
                break;
            case AMA_TYPE_SUPERTUTOR:
            case AMA_TYPE_TUTOR:
                $userObj = new ADAPractitioner($user_dataAr);
                break;
            case AMA_TYPE_SWITCHER:
                $userObj = new ADASwitcher($user_dataAr);
                break;
            case AMA_TYPE_ADMIN:
                $userObj = new ADAAdmin($user_dataAr);
                break;
        }
        $userObj->setPassword($_POST['password']);
        $result = MultiPort::addUser($userObj, array($sess_selected_tester));
        if($result > 0) {
          if($userObj instanceof ADAAuthor) {
              AdminUtils::performCreateAuthorAdditionalSteps($userObj->getId());
          }

          $message = translateFN('Utente aggiunto con successo');
          header('Location: ' . $userObj->getHomePage($message));
          exit();
        } else {
            $form = new CText(translateFN('Si sono verificati dei problemi durante la creazione del nuovo utente'));
        }

    } else {
        $form = new CText(translateFN('I dati inseriti nel form non sono validi'));
    }
} else {
    $form = new UserSubscriptionForm();
}

$label = translateFN('Aggiunta utente');
$help = translateFN('Da qui il provider admin può creare un nuovo utente');

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDateField();';

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

ARE::render($layout_dataAr, $content_dataAr,null,$optionsAr);