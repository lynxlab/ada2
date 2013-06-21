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
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version	0.1
 */
/**
 * Base config file
 */
// ini_set ('display_errors','0'); error_reporting(E_ALL);
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

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
require_once ROOT_DIR . '/include/Forms/UserSkillsForm.inc.php';
require_once ROOT_DIR .'/include/HtmlLibrary/UserExtraModuleHtmlLib.inc.php';
$languages = Translator::getLanguagesIdAndName();
/**
 * @author giorgio 14/giu/2013
 * 
 * data saving is handled through ajax calls now, just check which form
 * to show checking the hasExtra properties of userObj
 * 
 */
$user_dataAr = $userObj->toArray();
// var_dump($userObj); 
if (!$userObj->hasExtra()) {	
	// user has no extra, let's build standard form	
	$form = new UserProfileForm($languages);	
	unset($user_dataAr['password']);
	$user_dataAr['email'] = $user_dataAr['e_mail'];
	unset($user_dataAr['e_mail']);
	$form->fillWithArrayData($user_dataAr);
	$data = $form->render();	
} else {
	
	$tabContents = array();
	
	$etichette = array ( translateFN("Anagrafica"), translateFN("Lavoro") ,
						  translateFN("Formazione"), translateFN("Capacit&agrave;"),
						  translateFN("Lingue") );
	$data = "";
		
	$tabsContainer = CDOMElement::create('div','id:tabs');	
	$tabsUL = CDOMElement::create('ul');
	$tabsContainer->addChild($tabsUL);	
	
	for ($currTab=0; $currTab<count($etichette); $currTab++)
	{	
		// create a LI
		$tabsLI = CDOMElement::create('li');
		// add the save icon to the link
		$tabsLI->addChild(CDOMElement::create('span','class:ui-icon ui-icon-disk,id:tabSaveIcon'.$currTab));
		// add a link to the div that holds tab content
		$tabsLI->addChild(BaseHtmlLib::link('#divTab'.$currTab, $etichette[$currTab]));
		$tabsUL->addChild($tabsLI);				
		$tabContents[$currTab] = CDOMElement::create('div','id:divTab'.$currTab);
		
		$doMultiRowTab = false;
		
		if ($currTab==0) // personal datas, no extra at all!
		{
			$form = new UserProfileForm($languages);
			unset($user_dataAr['password']);
			$user_dataAr['email'] = $user_dataAr['e_mail'];
			unset($user_dataAr['e_mail']);
			$form->fillWithArrayData($user_dataAr);
		}
		else if ($currTab==1) // jobExperience
		{
			$doMultiRowTab = true;
			$extraTableName = "jobExperience";			
		}
		else if ($currTab==2) // educationTraining
		{
			$doMultiRowTab = true;
			$extraTableName = "educationTraining";			
		}
		else if ($currTab==3)
		{
			/**
			 * skills (aka capacita' in italian)
			 * These datas are stored in the table
			 * whose name is returned by ADAUser::getExtraTableName()
			 */
			$form = new UserSkillsForm($languages);
			$form->fillWithArrayData($user_dataAr);			
		}
		else if ($currTab==4) // languageSkills
		{
			$doMultiRowTab = true;
			$extraTableName = "languageSkills";
		}
		
		if ($doMultiRowTab===true)
		{
			// include proper form class definition file
			$extraTableFormClass = "User".ucfirst($extraTableName)."Form";
			
			require_once ROOT_DIR . '/include/Forms/'.$extraTableFormClass.'.inc.php';
			$container = CDOMElement::create('div','class:extraRowsContainer,id:container_'.$extraTableName);
			$objProperty = 'tbl_'.$extraTableName;
			if (count ($userObj->$objProperty) >0)
			{
				// create a div to wrap up all the rows of the array tbl_educationTrainig
				foreach ($userObj->$objProperty as $num=>$aElement)
				{
					$keyFieldName = $aElement::getKeyProperty();
					$keyFieldVal = $aElement->$keyFieldName;
					// $divToAdd = CDOMElement::create('div','id:div_educationTraining_'+$keyFieldVal);
					// $divToAdd->addChild (new CText( UserExtraModuleHtmlLib::extraObjectRow($aElement) ));
					// $tabContents[$currTab]->addChild($divToAdd);
					$container->addChild(new CText( UserExtraModuleHtmlLib::extraObjectRow($aElement) ));
					// $divToAdd = null;
				}
			}
			$tabContents[$currTab]->addChild($container);
			$formClassName = 
			$form = new $extraTableFormClass($languages);
			$form->fillWithArrayData(array (educationTraining::getForeignKeyProperty()=>$userObj->getId()));			
		}

		// add generated form (if any) to proper tab
		if (isset($form))
		{
			$tabContents[$currTab]->addChild(new CText($form->render()));
			unset ($form);
		}
		else
		{
			$tabContents[$currTab]->addChild (new CText(translateFN("Prova tab ".$currTab)));
		}
		
		$tabsContainer->addChild ($tabContents[$currTab]);
	} // end cycle through all tabs
	$data.= $tabsContainer->getHtml();
}

$label = translateFN('Modifica dati utente');

$help = translateFN('Modifica dati utente');

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS
);

$optionsAr['onload_func'] = 'initUserRegistrationForm('.$userObj->hasExtra().');';

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