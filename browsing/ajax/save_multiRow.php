<?php
/**
 * save_multiRow.php - save user extended 1:n data in the DB
 *
 * @package
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009-2013, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_STUDENT => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
		AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require ROOT_DIR .'/browsing/include/browsing_functions.inc.php';

/*
 * YOUR CODE HERE
*/
require_once ROOT_DIR.'/include/HtmlLibrary/UserExtraModuleHtmlLib.inc.php';
$languages = Translator::getLanguagesIdAndName();

$retArray = array();
$title = translateFN('Salvataggio');

/**
 * Set the $editUserObj depending on logged user type
 */
$editUserObj = null;

switch($userObj->getType()) {
	case AMA_TYPE_STUDENT:
	case AMA_TYPE_AUTHOR:
		$editUserObj =& $userObj;
		break;
	case AMA_TYPE_SWITCHER:
		$userId = DataValidator::is_uinteger($_POST['studente_id_utente_studente']);
		if ($userId !== false) {
			$editUserObj = MultiPort::findUser($userId);
		}
		break;
}

if (!is_null($editUserObj) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (!isset($_POST['extraTableName'])) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Non so cosa salvare"));
	else
	{
		/**
		 * include and instantiate form class based on extraTableName POST
		 * variable that MUST be set, else dont' know what and how to save.
		 */
		$extraTableClass = trim($_POST['extraTableName']);
		$extraTableFormClass = "User".ucfirst($extraTableClass)."Form";
			
		if (is_file(ROOT_DIR . '/include/Forms/'.$extraTableFormClass.'.inc.php'))
			require_once ROOT_DIR . '/include/Forms/'.$extraTableFormClass.'.inc.php';
		else die ("Form class not found, don't know how to save");
	}

	$form = new $extraTableFormClass($languages);
	$form->fillWithPostData();

	if ($form->isValid()) {
		$arr = array();
		$arr[$extraTableClass][0] = $extraTableClass::buildArrayFromPOST($_POST);
		// setExtras returns the index of the updated element, be it inserted or updated
		$updatedElementKey = $editUserObj->setExtras($arr);
		// setUser returns last insert id, or empty on update
		$result = MultiPort::setUser($editUserObj, array(), true,$extraTableClass);

		if (!AMA_DB::isError($result))
		{
			/**
			 * need to set the added extra arr
			 * state to saved and to give it the returned id
			 */
			$extraTableProperty = 'tbl_'.$extraTableClass;
			// $lastInsertKey = count($editUserObj->$extraTableProperty)-1;

			/**
			 * WEIRD STUFF:  NEED TO ACCESS OBJECT THIS WAY OTHERWISE WON'T WORK
			 */
			$extraTableKeyProperty = $extraTableClass::getKeyProperty();
			$temp1 = $editUserObj->$extraTableProperty;
// 			$temp =  $temp1[$lastInsertKey];
			$temp =  $temp1[$updatedElementKey];
			$temp->$extraTableKeyProperty = $result;
			$temp->setSaveState (true);
			
			$myhtml = UserExtraModuleHtmlLib::extraObjectRow($temp);
			/**
			 * Set the session user to the saved one if it's not
			 * a switcher, that is not saving its own profile
			 */
			if ($userObj->getType() != AMA_TYPE_SWITCHER) {
				$_SESSION['sess_userObj'] = $editUserObj;
			}

			$retArray = array ("status"=>"OK", "title"=>$title, "msg"=>translateFN("Scheda salvata"), 
							    "extraID"=>$result ,"html"=>$myhtml  );
		}
		else
			$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore nel salvataggio") );
	}
	else
	{
		$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("I dati non sono validi") );
	}
} else if (is_null($editUserObj)) {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Utente non trovato"));
} else {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);


?>