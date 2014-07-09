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
require_once ROOT_DIR . '/include/Forms/UserRemovalForm.inc.php';
/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = DataValidator::is_uinteger($_POST['id_user']);
    if($userId !== false) {
        $userToDeleteObj = MultiPort::findUser($userId);
        if($userToDeleteObj instanceof ADALoggableUser) {
            $userToDeleteObj->setStatus(ADA_STATUS_PRESUBSCRIBED);
            MultiPort::setUser($userToDeleteObj,array(), true);
            $data = new CText(sprintf(translateFN("L'utente \"%s\" è stato disabilitato."),
                              $userToDeleteObj->getFullName()));
        } else {
            $data = new CText(translateFN('Utente non trovato') . '(3)');
        }
    } else {
        $data = new CText(translateFN('Utente non trovato') . '(4)');
    }
} else {
    $userId = DataValidator::is_uinteger($_GET['id_user']);
    if($userId === false) {
        $data = new CText(translateFN('Utente non trovato') . '(1)');
    } else {
        $userToDeleteObj = MultiPort::findUser($userId);
        if($userToDeleteObj instanceof ADALoggableUser) {
            $formData = array(
              'id_user' => $userId  
            );
            $data = new UserRemovalForm();
            $data->fillWithArrayData($formData);
        } else {
            $data = new CText(translateFN('Utente non trovato') . '(2)');
        }
    }
}

$label = translateFN('Cancellazione utente');
$help = translateFN('Da qui il provider admin può disabilitare un utente esistente');

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'edit_switcher'=>$edit_profile_link->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);