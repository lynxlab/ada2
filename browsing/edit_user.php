<?php
/**
 * Edit user - this module provides edit user functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com> 
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_AUTHOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/FileUploader.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
$languages = Translator::getLanguagesIdAndName();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new UserProfileForm($languages);
    $form->fillWithPostData();

    if ($form->isValid()) {
        if(isset($_POST['layout']) && $_POST['layout'] != 'none') {
            $user_layout = $_POST['layout'];
        } else {
            $user_layout = '';
        }
        
        // set user datas
        $userObj->fillWithArrayData($_POST);

        // set user extra datas if any
        if ($userObj->hasExtra()) $userObj->setExtras($_POST);
        
        MultiPort::setUser($userObj, array(), true, ADAUser::getExtraTableName() );
//        $_SESSION['sess_userObj'] = $userObj;

        $navigationHistoryObj = $_SESSION['sess_navigation_history'];
        $location = $navigationHistoryObj->lastModule();
        header('Location: ' . $location);
        exit();
    }
} else {
    $allowEditProfile=false;
    $allowEditConfirm=false;
    $user_dataAr = $userObj->toArray();
    
    // the standard UserProfileForm is always needed.
    // Let's create it
    $form = new UserProfileForm($languages,$allowEditProfile, $allowEditConfirm, $self.'.php');
    unset($user_dataAr['password']);
    $user_dataAr['email'] = $user_dataAr['e_mail'];
    unset($user_dataAr['e_mail']);
    $form->fillWithArrayData($user_dataAr);   
    
    if (!$userObj->hasExtra()) {
    	// user has no extra, let's display it
    	$data = $form->render();
    } else {
    	require_once ROOT_DIR .'/include/HtmlLibrary/UserExtraModuleHtmlLib.inc.php';
    	
    	// the extra UserExtraForm is needed as well
    	require_once ROOT_DIR . '/include/Forms/UserExtraForm.inc.php';
    	$extraForm = new UserExtraForm ($languages);
    	$extraForm->fillWithArrayData ($user_dataAr);
    	
		$tabContents = array ();
		
		/**
		 * @author giorgio 22/nov/2013
		 * Uncomment and edit the below array to have the needed
		 * jQuery tabs to be used for user extra data and tables
		 */
		
// 		$tabsArray = array (
// 				array (translateFN ("Anagrafica"), $form),
// 				array (translateFN ("Sample Extra 1:1"), $extraForm),
// 				array (translateFN ("Sample Extra 1:n"), 'oneToManyDataSample'), 
// 		);
		
		$data = "";
		$linkedTablesInADAUser = !is_null(ADAUser::getLinkedTables()) ? ADAUser::getLinkedTables() : array();
		for($currTab = 0; $currTab < count ($tabsArray); $currTab ++) {
			
			// if is a subclass of FForm the it's a multirow element
			$doMultiRowTab = !is_subclass_of($tabsArray[$currTab][1], 'FForm');
			
			if ($doMultiRowTab === true)
			{
				$extraTableName = $tabsArray[$currTab][1];
				$extraTableFormClass = "User" . ucfirst ($extraTableName) . "Form";
				
				/*
				 * if extraTableName is not in the linked tables array or there's
				 * no form classes for the extraTableName skip to the next iteration
				 *
				 * NOTE: there's no need to check for classes (data classes, not for ones)
				 * existance here because if they did not existed you'd get an error while loggin in.
				 */ 
				if (!in_array($extraTableName,$linkedTablesInADAUser) ||
				    !@include_once (ROOT_DIR . '/include/Forms/' . $extraTableFormClass . '.inc.php') )
					continue;

				// if the file is included, but still there's no form class defined
				// skip to the next iteration
				if (!class_exists($extraTableFormClass)) continue;
				
				// generate the form
				$form = new $extraTableFormClass ($languages);
				$form->fillWithArrayData (array (
						$extraTableName::getForeignKeyProperty() => $userObj->getId () 
				));
				
				// create a div for placing 'new' and 'discard changes button'
				$divButton = CDOMElement::create ('div', 'class:formButtons');
				
					$showButton = CDOMElement::create ('a');
					$showButton->setAttribute ('href', 'javascript:toggleForm(\'' . $form->getName () . '\', true);');
					$showButton->setAttribute ('class', 'showFormButton ' . $form->getName ());					
					$showButton->addChild (new CText (translateFN ('Nuova scheda')));
					
					$hideButton = CDOMElement::create ('a');
					$hideButton->setAttribute ('href', 'javascript:toggleForm(\'' . $form->getName () . '\');');
					$hideButton->setAttribute ('class', 'hideFormButton ' . $form->getName ());
					$hideButton->setAttribute ('style', 'display:none');
					$hideButton->addChild (new CText (translateFN ('Chiudi e scarta modifiche')));
				
				$divButton->addChild ($showButton);
				$divButton->addChild ($hideButton);
				
				$objProperty = 'tbl_' . $extraTableName;
				// create a div to wrap up all the rows of the array tbl_educationTrainig				
				$container = CDOMElement::create ('div', 'class:extraRowsContainer,id:container_' . $extraTableName);
				
				// if have 3 or more rows, add the new and discard buttons on top also
				if (count ($userObj->$objProperty) >= 3) {
					$divButton->setAttribute('class', $divButton->getAttribute('class').' top');
					$container->addChild (new CText ($divButton->getHtml ()));
					// reset the button class by removing top
					$divButton->setAttribute('class', str_ireplace(' top', '', $divButton->getAttribute('class')));
				}
				
				if (count ($userObj->$objProperty) > 0) {
					foreach ($userObj->$objProperty as $num => $aElement) {
						$keyFieldName = $aElement::getKeyProperty();
						$keyFieldVal = $aElement->$keyFieldName;						
						$container->addChild (new CText (UserExtraModuleHtmlLib::extraObjectRow ($aElement)));
					}
				}
				// in these cases the form is added here
				$container->addChild (CDOMElement::create('div','class:clearfix'));
				$container->addChild (new CText ($form->render()));	
				// unset the form that's going to be userd in next iteration
				unset ($form);
				$container->addChild (CDOMElement::create('div','class:clearfix'));
				// add the new and discard buttons after the container
				$divButton->setAttribute('class', $divButton->getAttribute('class').' bottom');
				$container->addChild (new CText ($divButton->getHtml ()));
			} else {
				/**
				 * place the form in the tab
				 */
				$currentForm = $tabsArray[$currTab][1];
			}

			// if a tabs container is needed, create one
			if (!isset ($tabsContainer))
			{
				$tabsContainer = CDOMElement::create ('div', 'id:tabs');
				$tabsUL = CDOMElement::create ('ul');
				$tabsContainer->addChild ($tabsUL);
			}

			// add a tab only if there's something to fill it with
			if (isset($container) || isset ($currentForm))
			{
				// create a LI
				$tabsLI = CDOMElement::create ('li');
				// add the save icon to the link
				$tabsLI->addChild (CDOMElement::create ('span', 'class:ui-icon ui-icon-disk,id:tabSaveIcon' . $currTab));
				// add a link to the div that holds tab content
				$tabsLI->addChild (BaseHtmlLib::link ('#divTab' . $currTab, $tabsArray [$currTab][0]));
				$tabsUL->addChild ($tabsLI);
				$tabContents [$currTab] = CDOMElement::create ('div', 'id:divTab' . $currTab);
				
				if (isset ($container)) {
					// add the container to the current tab
					$tabContents [$currTab]->addChild ($container);
				} else if (isset ($currentForm)) {
					$tabContents [$currTab]->addChild (new CText ($currentForm->render()));
					unset ($currentForm);				
				}			
				$tabsContainer->addChild ($tabContents [$currTab]);
			}			
		} // end cycle through all tabs
		
		if (isset ($tabsContainer)) { 
			$data .= $tabsContainer->getHtml ();
		}
		else if (isset($form)) {			
			if (isset($extraForm)) {
				// if there are extra controls and NO tabs
				// add the extra controls to the standard form
				UserExtraForm::addExtraControls($form);
				$form->fillWithArrayData($user_dataAr);
			}
			$data .= $form->render();
		}		
		else $data = 'No form to display :(';
	} 
}

$label = translateFN('Modifica dati utente');
$help =  $label; // or set it to whatever you like

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.js'
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.css'
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));

/**
 * do the form have to be submitted with an AJAX call?
 * defalut answer is true, call this method to set it to false.
 * 
 * $userObj->useAjax(false);
 */

$optionsAr['onload_func']  = 'initDoc('.$maxFileSize.','. $userObj->getId().');';
$optionsAr['onload_func'] .= 'initUserRegistrationForm('.(int)(isset($tabsContainer)).', '.(int)$userObj->saveUsingAjax().');';

//$optionsAr['onload_func'] = 'initDateField();';

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => translateFN('Modifica dati utente'),
    'data' => $data,
    'help' => $help
);

ARE::render($layout_dataAr, $content_dataAr,NULL, $optionsAr);