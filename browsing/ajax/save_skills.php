<?php
/**
 * save skills - save user personal data in the DB
 *
 *
 * @package
 * @author 	giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009-2013, Lynx s.r.l.
 * @license	http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */

// ini_set ('display_errors','1'); error_reporting(E_ALL);

require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

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
require ROOT_DIR .'/browsing/include/browsing_functions.inc.php';

/*
 * YOUR CODE HERE
*/
require_once ROOT_DIR . '/include/Forms/UserSkillsForm.inc.php';
$languages = Translator::getLanguagesIdAndName();

$retArray = array();
$title = translateFN('Salvataggio');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	$form = new UserSkillsForm($languages);
	$form->fillWithPostData();

	if ($form->isValid()) {
		
		$userObj->setExtras($_POST);
		$result = MultiPort::setUser($userObj, array(), true, ADAUser::getExtraTableName());
		if (!AMA_DB::isError($result))
		{
			$_SESSION['sess_userObj'] = $userObj;
			$retArray = array ("status"=>"OK", "title"=>$title, "msg"=>translateFN("Scheda Capacit&agrave; salvata"));
		}
		else 
			$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore nel salvataggio") );

		
	}
	else
	{
		$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("I dati non sono validi") );
	}
}
else {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>trasnlateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore sconosciuto")); 
	
echo json_encode($retArray);

?>