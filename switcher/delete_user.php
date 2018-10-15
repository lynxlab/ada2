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

require_once ROOT_DIR . '/include/Forms/UserRemovalForm.inc.php';
/*
 * YOUR CODE HERE
 */
$restore = isset($_REQUEST['restore']);
$prefix = $restore ? '' : 'dis';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = DataValidator::is_uinteger($_POST['id_user']);
    $postKey = $restore ? 'restore' : 'delete';
    if($userId !== false && isset($_POST[$postKey]) && intval($_POST[$postKey])===1) {
        $userToDeleteObj = MultiPort::findUser($userId);
        if($userToDeleteObj instanceof ADALoggableUser) {
            $userToDeleteObj->setStatus($restore ? ADA_STATUS_REGISTERED : ADA_STATUS_PRESUBSCRIBED);
            MultiPort::setUser($userToDeleteObj,array(), true);
            $data = new CText(sprintf(translateFN("L'utente \"%s\" è stato {$prefix}abilitato."),
                              $userToDeleteObj->getFullName()));
        } else {
            $data = new CText(translateFN('Utente non trovato') . '(3)');
        }
    } else {
        $data = new CText(translateFN("Utente non {$prefix}abilitato."));
    }
} else {
    $userId = DataValidator::is_uinteger($_GET['id_user']);
    $restore = (isset($_GET['restore']) && intval($_GET['restore'])===1);
    if($userId === false) {
        $data = new CText(translateFN('Utente non trovato') . '(1)');
    } else {
        $userToDeleteObj = MultiPort::findUser($userId);
        if($userToDeleteObj instanceof ADALoggableUser) {
            $formData = array(
              'id_user' => $userId
            );
            $data = new UserRemovalForm($restore);
            $data->fillWithArrayData($formData);
        } else {
            $data = new CText(translateFN('Utente non trovato') . '(2)');
        }
    }
}

$label = ucfirst(strtolower(translateFN($prefix.'abilitazione utente')));
$help = translateFN('Da qui il provider admin può '.$prefix.'abilitare un utente esistente');

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