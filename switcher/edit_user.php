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

/**
 * Check if the switcher is editing a student profile
 */
$isEditingAStudent = (DataValidator::is_uinteger(isset($_GET['usertype']) ? $_GET['usertype'] : null) === AMA_TYPE_STUDENT);

if (!$isEditingAStudent) {
	/**
	 * Code to execute when the switcher is not editing a student
	 */
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	
		/**
		 * @author giorgio 29/mag/2013
		 * 
		 * added parameters to force allowEditConfirm
		 */
	    $form = new UserProfileForm(array(), false, true);
	    $form->fillWithPostData();
	    $password = trim($_POST['password']);
	    $passwordcheck = trim($_POST['passwordcheck']);
	    if(DataValidator::validate_password_modified($password, $passwordcheck) === FALSE) {
	    	$message = translateFN('Le password digitate non corrispondono o contengono caratteri non validi.');
	    	header("Location: edit_user.php?message=$message&id_user=".$_POST['id_utente']);
	    	exit();
	    }	
	    if ($form->isValid()) {
	        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
	            $user_layout = $_POST['layout'];
	        } else {
	            $user_layout = '';
	        }
	        $userId = DataValidator::is_uinteger($_POST['id_utente']);
	        if($userId > 0) {
	            $editedUserObj = MultiPort::findUser($userId);
	            $editedUserObj->fillWithArrayData($_POST);
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
	if (!is_null($editedUserObj)) {
		$label .= ': '.$editedUserObj->getUserName().' ('.$editedUserObj->getFullName().')';
	}
	
	$layout_dataAr['JS_filename'] = array(
			JQUERY,
			JQUERY_MASKEDINPUT,
			JQUERY_NO_CONFLICT
	);
	
	$optionsAr['onload_func'] = 'initDateField();';
	
	/*
	 * Display error message  if the password is incorrect
	 */
	if(isset($_GET['message']))
	{
		$help= $_GET['message'];
	
	}
		
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
} else {
	/**
	 * If the switcher is editing a student, use browsing/edit_user.php
	 */
	include realpath(dirname(__FILE__)).'/../browsing/edit_user.php';
	
	$label = translateFN('Modifica utente');
	$help = translateFN('Da qui il provider admin può modificare il profilo di un utente esistente');
	
	if (!is_null($editUserObj)) {
		$label .= ': '.$editUserObj->getUserName().' ('.$editUserObj->getFullName().')';
	}
	
	$content_dataAr['label'] = $label;
	$content_dataAr['help'] = $help;
} 

ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);