<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * google login provider implementation
 */
class hybridLogin extends AbstractLogin
{
	/**
	 * performs user login using hybridLogin.php redirection
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		redirect(MODULES_LOGIN_HTTP . '/hybridLogin.php?id='.$this->id.
				'&remindme='.intval($remindMe).'&lang='.$language);
	}
}
