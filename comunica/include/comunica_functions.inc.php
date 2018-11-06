<?php
/**
 * Comunica functions
 *
 * @package
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */
/**
 * Specific room object
 */
require_once ROOT_DIR . '/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
require_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';
require_once ROOT_DIR . '/comunica/include/videoroom.classes.inc.php';
require_once ROOT_DIR . '/include/ViewBaseHelper.php';

/**
 * Comunica helper class
 */
class ComunicaHelper extends ViewBaseHelper
{
  /**
   * Builds array keys for the comunica directory scripts
   *
   * @param array $neededObjAr
   *
   * @return array
   */
  public static function init(array $neededObjAr = array())

  {
    if (count(self::$helperData) === 0) {
      self::$helperData = parent::init($neededObjAr);
      $enabledArr = self::getEnabledArray(self::$helperData['userObj'], isset(self::$helperData['courseObj']) ? self::$helperData['courseObj'] : null);
      self::$helperData = array_merge(
        self::$helperData,
        $enabledArr,
        [
          'status' => self::getStatus(null, 'comunicazione'),
          'user_uname' => self::$helperData['userObj']->getUserName(),
          'user_surname' => self::$helperData['userObj']->getLastName(),
          'user_mail' => self::$helperData['userObj']->getEmail(),
        ],
        self::buildGlobals(),
        self::getUserBrowsingData(self::$helperData['userObj'], $enabledArr['log_enabled'])
      );
      self::extract();
    }
    return self::getHelperData();
  }

  /**
   * Builds the reg_enabled, log_enabled, mod_enabled and com_enabled keys
   * for the comunica directory scripts
   *
   * @param ADAGenericUser $userObj
   * @param Course $courseObj
   *
   * @return array
   */
  protected static function getEnabledArray(ADAGenericUser $userObj = null, Course $courseObj = null)
  {
    /**
     * import globals set from module_init
     */
    global $sess_id_course_instance;
    global $sess_id_user;

    $reg_enabled = true; // links to bookmarks enabled
    $log_enabled = true; // links to history enabled
    $mod_enabled = true; // links to modify nodes  enabled
    $com_enabled = true;  // links to comunicate among users  enabled

    if (method_exists($userObj, 'get_student_status')) {
      $user_status = $userObj->get_student_status($sess_id_user, $sess_id_course_instance);
    } else $user_status = ADA_STATUS_VISITOR;


    if ($user_status == ADA_STATUS_VISITOR || $user_status == ADA_STATUS_TERMINATED) {
      $reg_enabled = false; // links to bookmarks disabled
      $log_enabled = false; // links to history disabled
      $mod_enabled = false; // links to modify nodes  disabled
      $com_enabled = false;  // links to comunicate among users  disabled
    }

    return ['reg_enabled' => $reg_enabled, 'log_enabled' => $log_enabled, 'mod_enabled' => $mod_enabled, 'com_enabled' => $com_enabled];
  }

  /**
   * Builds the sess_id_node, sess_id_course, sess_id_course_instance and sess_id_room
   * array keys that are needed by some of the scritps
   *
   * @return array
   */
  protected static function buildGlobals()
  {
    $retArr = parent::buildGlobals();
    if (isset($_REQUEST['id_room'])) {
      $retArr['sess_id_room'] = intval($_REQUEST['id_room']);
    }
    return $retArr;
  }

  /**
   * Builds user_level, user_score, user_history, user_status...
   *
   * @param ADAGenericUser $userObj
   * @param boolean $log_enabled
   * @return void
   */
  protected static function getUserBrowsingData(ADAGenericUser $userObj, $log_enabled = false)
  {
    if ($userObj->getType() == AMA_TYPE_ADMIN) {
      $homepage = "$http_root_dir/admin/admin.php"; // admin.php
      $msg = urlencode(translateFN("Ridirezionamento automatico"));
      header("Location: $homepage?err_msg=$msg");
    } else {
      return parent::getUserBrowsingData($userObj, $log_enabled);
    }
  }
}
