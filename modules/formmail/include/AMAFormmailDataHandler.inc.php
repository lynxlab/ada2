<?php
/**
 * FORMMAIL MODULE.
 *
 * @package        formmail module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           formmail
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAFormmailDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_formmail_';

	public function saveFormMailHistory($userID, $helpTypeID, $subject, $msgbody, $attachmentsStr, $selfSent, $sentOK) {
		$sql = 'INSERT INTO `'.self::$PREFIX.'history` (`id_utente`,`'.self::$PREFIX.'helptype_id`, '.
		'`subject`,`msgbody`,`attachments`,`selfSent`,`sentOK`,`sentTimestamp`) VALUES (?,?,?,?,?,?,?,?);';

		$res = $GLOBALS['dh']->queryPrepared($sql, array($userID, $helpTypeID, $subject, $msgbody, $attachmentsStr, $selfSent, $sentOK, AMA_DataHandler::date_to_ts('now')));

		if (AMA_DB::isError($res)) {
			$err = new AMA_Error(AMA_ERR_ADD);
			return $err;
		}

		return true;
	}

	public function getHelpTypes($user_type) {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'helptype` WHERE `user_type` =? ORDER BY `description` ASC';
		return $GLOBALS['dh']->getAllPrepared($sql, $user_type, AMA_FETCH_ASSOC);
	}

}
?>
