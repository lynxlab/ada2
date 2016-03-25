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

/**
 * callback function used to check if formmail menu item is to be enabled
 *
 * @param array $allowedTypes optional, can be defined in the DB enabledON field or in the function body
 *
 * @return true if menu must be enabled
 */
function menuEnableFormMail($allowedTypes=null) {

	if (is_null($allowedTypes)) {
		/**
		 * Add here user types for which the formmail menu must be enabled
		 */
		$allowedTypes = array(AMA_TYPE_SWITCHER);
	}

	return defined('MODULES_FORMMAIL') && MODULES_FORMMAIL && isset($_SESSION['sess_userObj']) && in_array($_SESSION['sess_userObj']->getType(), $allowedTypes);
}
?>
