<?php

/**
 * @package 	instancesreport module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\InstancesReport;

/**
 * Class for handling module permissions based on user profiles.
 *
 * @author giorgio
 */
class InstancesReportActions
{

    /**
     * global actions, not performed on the single request
     *
     * @var integer
     */
    const EXPORT = 1;

    /**
     * array that defines who can do what
     *
     * @var array
     */
    protected static $CANDOARR = null;

    /**
     * gets the canDo array
     *
     * @return array
     */
    protected static function getCanDoArr()
    {
        return array(
            self::EXPORT => function ($object = null, $userType = null) {
                return in_array($userType, [AMA_TYPE_ADMIN, AMA_TYPE_SWITCHER]);
            },
        );
    }

    /**
     * Gets the constant value whose name is the passed string, if it exists, else returns null
     *
     * @param string $stringConstant
     * @return NULL|mixed
     */
    public static function getConstantFromString($stringConstant)
    {
        return defined(__CLASS__ . '::' . $stringConstant) ? constant(__CLASS__ . '::' . $stringConstant) : null;
    }

    /**
     * Checks if a user has the rights to to an action on the optional passed object.
     * If the action is an array the method will return true if the userType can do at least one
     * of the actions in the group
     *
     * @param int|array $actionID
     * @param unknown $object the object you are checking if the user has permission to do the action
     * @param int $userType, if null it will be set to the session user type
     * @return boolean
     */
    public static function canDo($actionID, $object = null, $userType = null)
    {
        if (is_null(self::$CANDOARR)) self::$CANDOARR = self::getCanDoArr();
        if (is_null($userType) && array_key_exists('sess_userObj', $_SESSION)) $userType = $_SESSION['sess_userObj']->getType();
        if (is_null($userType) || intval($userType) <= 0) return false;
        if (is_array($actionID)) {
            foreach ($actionID as $anAction) {
                if (self::canDo($anAction, $object, $userType)) return true;
            }
            return false;
        } else {
            if (array_key_exists($actionID, self::$CANDOARR)) {
                if (is_callable(self::$CANDOARR[$actionID])) {
                    return call_user_func_array(self::$CANDOARR[$actionID], array($object, $userType));
                } else {
                    return in_array(intval($userType), self::$CANDOARR[$actionID]);
                }
            }
            return false;
        }
    }

    /**
     * Gets the $allowedUsersAr and $neededObjAr that must be defined for module_init checks
     * typical usage is: list($allowedUsersAr, $neededObjAr) = array_values(InstancesReportActions::getAllowedAndNeededAr());
     *
     * @param string $fileName php script to get arrays for. defaults to null, and $_SERVER['SCRIPT_FILENAME'] is used
     * @return array[]|string[][][] array with keys 'allowedUsers' and 'neededObjects' as arrays
     */
    public static function getAllowedAndNeededAr($fileName = null)
    {
        $retArr = array(
            'allowedUsers' => array(),
            'neededObjects' => array()
        );
        if (is_null($fileName)) $fileName = realpath($_SERVER['SCRIPT_FILENAME']);
        $fileName = trim(str_replace(MODULES_INSTANCESREPORT_PATH, '', $fileName), '/');
        if (strlen($fileName) > 0) {
            // admin, coordinator, author and editor have access to everything by default
            $retArr['neededObjects'] = array(
                AMA_TYPE_ADMIN => array('layout'),
                AMA_TYPE_SWITCHER => array('layout'),
                AMA_TYPE_TUTOR => array('layout'),
                AMA_TYPE_SUPERTUTOR => array('layout'),
                AMA_TYPE_AUTHOR => array('layout'),
                AMA_TYPE_STUDENT => array('layout')
            );
            switch ($fileName) {
                // separate index.php from default, prevents too many redirect error
                case 'export.php':
                    $retArr['neededObjects'] = array(
                        AMA_TYPE_SWITCHER => array('layout', 'user', 'course',),
                    );
                    break;
            }
        }
        // if no allowedUsers specified, use the neededObjects keys
        if (count($retArr['allowedUsers']) <= 0) $retArr['allowedUsers'] = array_keys($retArr['neededObjects']);
        return $retArr;
    }
}
