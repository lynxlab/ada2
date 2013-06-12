<?php

/**
 * Edit user - this module provides edit user functionality
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
$self = whoami();

include_once 'include/switcher_functions.inc.php';
include_once ROOT_DIR . '/admin/include/AdminUtils.inc.php';
/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	/**
	 * @author giorgio 29/mag/2013
	 * 
	 * added parameters to force allowEditConfirm
	 */
    $form = new UserProfileForm(array(), false, true);
    $form->fillWithPostData();

    if ($form->isValid()) {
        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
            $user_layout = $_POST['layout'];
        } else {
            $user_layout = '';
        }
        $userId = DataValidator::is_uinteger($_POST['id_utente']);
        if($userId > 0) {
            $editedUserObj = MultiPort::findUser($userId);
            $editedUserObj->setFirstName($_POST['nome']);
            $editedUserObj->setLastName($_POST['cognome']);
            $editedUserObj->setBirthDate($_POST['birthdate']);
            $editedUserObj->setGender($_POST['sesso']);
            $editedUserObj->setEmail($_POST['email']);
            $editedUserObj->setPhoneNumber($_POST['telefono']);
            $editedUserObj->setAddress($_POST['indirizzo']);
            $editedUserObj->setCity($_POST['citta']);
            $editedUserObj->setProvince($_POST['provincia']);
            $editedUserObj->setCountry($_POST['nazione']);
            if (trim($_POST['password']) != '') {
                $editedUserObj->setPassword($_POST['password']);
            }
            $editedUserObj->setStatus($_POST['stato']);
            $editedUserObj->setLayout($user_layout);
            $result = MultiPort::setUser($editedUserObj, array(), true);
        }

        if(!AMA_DataHandler::isError($result)) {
            header('Location: view_user.php?id_user=' . $editedUserObj->getId());
            exit();
        }




//        if($result > 0) {
//          if($userObj instanceof ADAAuthor) {
//              AdminUtils::performCreateAuthorAdditionalSteps($userObj->getId());
//          }
//
//          $message = translateFN('Utente aggiunto con successo');
//          header('Location: ' . $userObj->getHomePage($message));
//          exit();
//        } else {
//            $form = new CText(translateFN('Si sono verificati dei problemi durante la creazione del nuovo utente'));
//        }

    } else {
        $form = new CText(translateFN('I dati inseriti nel form non sono validi'));
    }
} else {
    $userId = DataValidator::is_uinteger($_GET['id_user']);
    if($userId === false) {
        $data = new CText('Utente non trovato');
    }
    else {
        $editedUserObj = MultiPort::findUser($userId);
        $formData = $editedUserObj->toArray();
        $formData['email'] = $formData['e_mail'];
        unset($formData['e_mail']);
        /**
         * @author giorgio 29/mag/2013
         *
         * added parameters to force allowEditConfirm
         */
        $data = new UserProfileForm(array(), false, true);
        $data->fillWithArrayData($formData);
    }    
}

$label = translateFN('Modifica utente');
$help = translateFN('Da qui il provider admin può modificare il profilo di un utente esistente');

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
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);