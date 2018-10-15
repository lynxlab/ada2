<?php
/**
 * delete_multiRow.php - delete user extended 1:n data from the DB
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

$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require ROOT_DIR .'/browsing/include/browsing_functions.inc.php';

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
BrowsingHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
*/

$languages = Translator::getLanguagesIdAndName();

$retArray = array();
$title = translateFN('Cancellazione');

/**
 * Set the $editUserObj depending on logged user type
 */
$editUserObj = null;

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

switch($userObj->getType()) {
	case AMA_TYPE_STUDENT:
	case AMA_TYPE_AUTHOR:
		$editUserObj =& $userObj;
		break;
	case AMA_TYPE_SWITCHER:
		$userId = DataValidator::is_uinteger($_POST[$extraTableClass::getForeignKeyProperty()]);
		if ($userId !== false) {
			$editUserObj = MultiPort::findUser($userId);
		}
		break;
}

if (!is_null($editUserObj) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (!isset($_POST['extraTableName'])) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Non so cosa cancellare"));
	else
	{
		/**
		 * include and instantiate form class based on extraTableName POST
		 * variable that MUST be set, else dont' know what and how to save.
		 */

		$extraTableClass = trim($_POST['extraTableName']);
		$extraTableId = isset ($_POST['id']) ? intval($_POST['id']) : null;

		$result = MultiPort::removeUserExtraData($editUserObj, $extraTableId ,$extraTableClass);

		if (!AMA_DB::isError($result))
		{
			$editUserObj->removeExtras($extraTableId, $extraTableClass);
			/**
			 * Set the session user to the saved one if it's not
			 * a switcher, that is not saving its own profile
			 */
			if ($userObj->getType() != AMA_TYPE_SWITCHER) {
				$_SESSION['sess_userObj'] = $editUserObj;
			}
			$retArray = array ("status"=>"OK", "title"=>$title, "msg"=>translateFN("Scheda cancellata"));
		}
		else
			$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore di cancellazione") );
	}
} else if (is_null($editUserObj)) {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Utente non trovato"));
} else {
	$retArray = array ("status"=>"ERROR", "title"=>$title, "msg"=>trasnlateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "title"=>$title, "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
?>