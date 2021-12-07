<?php
/**
 * LOGIN MODULE
 *
 * @package     login module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015-2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\Login;

/**
 * ADA login provider implementation
 */
class adaLogin extends abstractLogin
{
	/**
	 * performs user login using the ADA db
	 *
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		$user = \MultiPort::loginUser($name, $pass);
		if (is_object($user) && $user instanceof \ADALoggableUser) {
			// WARNING! For this login provider, no set of options is supported
			$this->setSuccessfulOptionsID(0);
		}
		return $user;
	}
}
