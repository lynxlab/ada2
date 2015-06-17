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
 * LDAP login provider implementation
 * 
 * this is working from the command line
 * 
 * ldapsearch -x -b "ou=users,dc=lynxlab,dc=com" \
*  -W -H ldap://lynxlab.com -D "uid=YOUR_USER_ID_HERE,ou=users,dc=lynxlab,dc=com"
 * 
 */
class ldapLogin extends AbstractLogin
{
	/**
	 * performs user login using the ADA db
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		
		$options = $this->loadOptions();
		
		$ldap_dn = $options['dn'];
		$ldap_uri = $options['uri'];
		
		$handle = ldap_connect($ldap_uri);
		// this will output a warning in the webserver log on failure
		$bind = ldap_bind($handle, 'uid='.$name.','.$ldap_dn, $pass);
		$result = ldap_search($handle, $ldap_dn, "uid=".$name);
		
		if ($bind !==false && $result!==false) {
			/**
			 * look if user is already in ADA DB
			 */
			$userObj = $this->checkADAUser($name);
			
			if (!is_object($userObj) || !$userObj instanceof ADALoggableUser) {
				
				$entries = ldap_get_entries($handle, $result);
				ldap_unbind($handle);
				if ($entries!==false && is_array($entries) && count($entries)>0) {
					$entries = $entries[0];
					
					/**
					 * build user array
					 */
					$adaUser = array(
							'nome' => $entries['givenname'][0],
							'cognome' => $entries['sn'][0],
							'email' => 'nobody',
							'username' => $entries['uid'][0],
							'cap' => '',
							'matricola' => '',
							'avatar' => '',
							'birthcity' => ''						
					);
					return $this->addADAUser($adaUser);
				}				
			} // user not found in ADA
			
			/**
			 * At this point, either the $userObj was already in
			 * ADA DB or had just been created by the above code
			 */
			if (is_object($userObj) && $userObj instanceof ADALoggableUser) {
				return $userObj;
			}
		} // user not found in LDAP
		else {
			ldap_unbind($handle);
			return new Exception(translateFN("Username  e/o password non valide"));
		}
	}
}
