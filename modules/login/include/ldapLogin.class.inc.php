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
 */
class ldapLogin extends AbstractLogin
{
	/**
	 * performs user login using an LDAP server
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		try {
			$options = $this->loadOptions();
			/**
			 * check LDAP configuration in module's option table
			 */
			if (!is_null($options) && is_array($options) && count($options)>0) {
				// mandatory fields
				$mandatoryOptions = array(
						'host' => 'Impostare l\'host LDAP',
						'dn'  => 'Impostare il dn LDAP',
						'ou_users' => 'Impostare l\'ou per gli utenti in LDAP',
						'ou_groups'=> 'Impostare l\'ou per i gruppi in LDAP'
				);
				
				foreach ($mandatoryOptions as $optionName=>$errorMessage) {
					if (!array_key_exists($optionName, $options) || strlen($options[$optionName])<=0) {
						$errorMessage = translateFN($errorMessage) .
										'<br/>(key=\''.$optionName.'\' '.translateFN('nelle opzioni').')';
						throw new Exception($errorMessage);
					}
				}
			} else throw new Exception(translateFN('Impossibile caricare la configurazione LDAP'));
			// connect to host
			$handle = ldap_connect($options['host']);
			// set options
			ldap_set_option($handle, LDAP_OPT_PROTOCOL_VERSION, 3 );
			ldap_set_option($handle, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($handle, LDAP_OPT_NETWORK_TIMEOUT,  30); /* 30 second timeout */
			
			// this will output a warning in the webserver log on failure
			$bind = ldap_bind($handle, 'uid='.$name.',ou='.$options['ou_users'].','.$options['dn'], $pass);
			
			if ($bind !==false) {
				/**
				 * look if user is already in ADA DB
				 */
				$userObj = $this->checkADAUser($name);
				
				if (!is_object($userObj) || !$userObj instanceof ADALoggableUser) {
					/**
					 * If user is not in ADA DB, try loading his data from LDAP
					 */
					$result = ldap_search($handle, $options['dn'], "uid=".$name);
					/**
					 * If $results is false, throw an exception
					 */
					if ($result!==false) $entries = ldap_get_entries($handle, $result);
					else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
					
					if ($entries!==false && is_array($entries) && count($entries)>0) {
						$entries = $entries[0];
						/**
						 * Look for user group name from group id and map it to proper AMA_TYPE
						 * accordingly to module's option table
						 */
						if (!is_null($entries) && isset($entries['gidnumber']) && intval($entries['gidnumber'][0])>0) {
							// look for group name
							$query = "(&(objectClass=posixGroup)(gidNumber=".$entries['gidnumber'][0]."))";
							$groupres = ldap_search($handle, "ou=".$options['ou_groups'].",".$options['dn'], $query);
							/**
							 * If $groupres is false, don't thrown an exception: the user shall be a student
							 */
							$groupentries = ldap_get_entries($handle, $groupres);
							
							if (isset($groupentries[0]['cn']) && strlen($groupentries[0]['cn'][0])>0) {
								$groupname = $groupentries[0]['cn'][0];
								$possibleUserType = null;
								/**
								 * this will map LDAP groups to AMA_TYPE_* 
								 * accordingly to the options table
								 */
								foreach ($options as $optionName=>$groupDesc) {
									if (strpos($optionName,'AMA_TYPE')===0) {
										if (is_array($groupDesc) && in_array($groupname, $groupDesc)) {
											// if value (aka $groupDesc) in the options table is array
											$possibleUserType = strtoupper($optionName);
											break;
										} else if (strcmp($groupDesc, $groupname)===0) {
											// if value (aka $groupDesc) in the options table is string
											$possibleUserType = strtoupper($optionName);
											break;
										}
									}
								}
								
								if (!is_null($possibleUserType) && defined($possibleUserType)) $userType = constant($possibleUserType);
							}
						}
						
						/**
						 * build user array
						 */
						$adaUser = array(
								'nome' => $entries['givenname'][0],
								'cognome' => $entries['sn'][0],
								'email' => 'nobody',
								'username' => $entries['uid'][0],
								'tipo' => isset($userType) ? $userType  : AMA_TYPE_STUDENT,
								'cap' => '',
								'matricola' => '',
								'avatar' => '',
								'birthcity' => ''
						);
						
						if (isset($handle) && !is_null($handle)) ldap_unbind($handle);
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
			} else {
				throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
			}
		} catch (Exception $e) {
			ldap_unbind($handle);
			// 'Invalid credentials' (code:49)  gets ADA's own message as text
			if ($e->getCode()==49) {
				return new Exception(translateFN("Username  e/o password non valide"));
			}
			return $e;
		}
	}
}
