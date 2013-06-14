<?php
/**
 * Edit user - this module provides edit user functionality
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
ini_set ('display_errors','0'); error_reporting(E_ALL);
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
$languages = Translator::getLanguagesIdAndName();
/**
 * @author giorgio 14/giu/2013
 * 
 * data saving is handled through ajax calls now, just check which form
 * to show checking the hasExtra properties of userObj
 * 
 */

$user_dataAr = $userObj->toArray();

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
		
	$tabsContainer = CDOMElement::create('div','id:tabs');	
	$tabsUL = CDOMElement::create('ul');
	$tabsContainer->addChild($tabsUL);	
	
	for ($currTab=0; $currTab<count($etichette); $currTab++)
	{	
		// create a LI
		$tabsLI = CDOMElement::create('li');
		// add a link to the div that holds tab content
		$tabsLIContent = BaseHtmlLib::link('#divTab'.$currTab, $etichette[$currTab]);
		// add the save icon to the link
		$tabsLIContent->addChild(CDOMElement::create('span','class:ui-icon ui-icon-disk,id:tabSaveIcon'.$currTab));
		$tabsLI->addChild($tabsLIContent);
		$tabsUL->addChild($tabsLI);				
		$tabContents[$currTab] = CDOMElement::create('div','id:divTab'.$currTab);
		
		switch ($currTab)
		{
			case 0: // personal datas				
				$form = new UserProfileForm($languages);				
				unset($user_dataAr['password']);
				$user_dataAr['email'] = $user_dataAr['e_mail'];
				unset($user_dataAr['e_mail']);
				$form->fillWithArrayData($user_dataAr);
// 				$tabContents[$currTab]->addChild(new CText($form->render()));
				break;
			case 3: // skills (aka capacita' in italian)
				$form = new UserSkillsForm($languages);	
				$form->fillWithArrayData($user_dataAr);
				break;
			default: // job profile				
				break;
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
	}
	$data.= $tabsContainer->getHtml();
}

$label = translateFN('Modifica dati utente VERSIONE ESTESA OPENLABOR');

$help = translateFN('Modifica dati utente VERSIONE ESTESA OPENLABOR');

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