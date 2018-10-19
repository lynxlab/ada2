<?php
/**
 * Switcher functions
 *
 * @package
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */
require_once ROOT_DIR . '/include/ViewBaseHelper.php';

/**
 * Switcher helper class
 */
class SwitcherHelper extends ViewBaseHelper
{
  /**
   * Builds array keys for the switcher directory scripts
   *
   * @param array $neededObjAr
   *
   * @return array
   */
  public static function init(array $neededObjAr = array())
  {
    if (count(self::$helperData) === 0) {
      self::$helperData = parent::init($neededObjAr);
      self::$helperData = array_merge(
        self::$helperData,
        [
          'user_level' => ADA_MAX_USER_LEVEL,
          'user_score' => '',
          'user_status' => '',
          'user_uname' => self::$helperData['userObj']->getUserName(),
          'user_surname' => self::$helperData['userObj']->getLastName(),
          'user_mail' => self::$helperData['userObj']->getEmail(),
          'user_messages' => self::getUserMessages(self::$helperData['userObj']),
          'user_agenda' => self::getUserAgenda(self::$helperData['userObj'])
        ],
        self::buildGlobals()
      );
      self::extract();
    }
    return self::getHelperData();
  }
}
