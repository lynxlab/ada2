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
	const INVALID_USERNAME_EXECEPTION_CODE = 49;
	
	/**
	 * performs user login using an LDAP server
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		$loginResult = null;
		$errorMessages = array();
		$allOptions = $this->loadOptions();

		if (!is_null($allOptions)) {
			if ($allOptions['optionscount']<=1) $allOptions = array ($allOptions);
			unset($allOptions['optionscount']);
			
			foreach ($allOptions as $option_id=>$options) {
				if ($options['enabled']) {
					$loginResult = $this->doLoginAttempt($name, $pass, $remindMe, $language, $options);
					if (is_object($loginResult) && $loginResult instanceof ADALoggableUser) {
						$this->setSuccessfulOptionsID($option_id);
						return $loginResult;
					} else if ((is_object($loginResult)) && ($loginResult instanceof Exception)) {
						if(!in_array($loginResult->getMessage(), $errorMessages)) $errorMessages[] = $loginResult->getMessage();
					}
				}
			}
		}
		if (count($errorMessages)==0) {
			$errorMessages[] = translateFN('Nessun servizio LDAP configurato o attivo');
		}
		return new Exception(implode('<br/>', $errorMessages));
	}
	
	private function doLoginAttempt($name, $pass, $remindMe, $language, $options)
	{
		try {
			/**
			 * If invalid name or password, throw exception
			 */
			if ($name === false || $pass === false) throw new Exception(null,self::INVALID_USERNAME_EXECEPTION_CODE);
			
			/**
			 * check LDAP configuration in module's option table
			 */
			if (!is_null($options) && is_array($options) && count($options)>0) {
				// mandatory fields
				$mandatoryOptions = array(
						'host' => 'Impostare l\'host LDAP',
						'authdn'  => 'Impostare il dn di autenticazione LDAP',
						'basedn' => 'Impostare il dn di ricerca in LDAP',
						'usertype' => 'Specificare il ruolo utente WISP'
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
			$bind = ldap_bind($handle, 'uid='.$name.','.$options['authdn'], $pass);
			
			if ($bind !==false) {
				/**
				 * look if user is already in ADA DB
				 */
				$userObj = $this->checkADAUser($name);
				
				if (!is_object($userObj) || !$userObj instanceof ADALoggableUser) {
					/**
					 * If user is not in ADA DB, try loading his data from LDAP
					 */
					$result = ldap_search($handle, $options['authdn'], "uid=".$name);
					/**
					 * If $results is false, throw an exception
					 */
					if ($result!==false) $entries = ldap_get_entries($handle, $result);
					else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
					
					if ($entries!==false && is_array($entries) && count($entries)>0) {
						$entries = $entries[0];
						/**
						 * If user uid is listed in the memberUid attributes
						 * for the basedn than it's safe to say that his type is $options['usertype']
						 */
						if (!is_null($entries)) {
							$namefilter = '(&(memberUid='.$name.'))';
							if  (isset($options['filter']) && strlen($options['filter'])>0) {
								// extract filter up to last ')' character
								$substr = substr($options['filter'], 0,strrpos($options['filter'], ')'));
								// concatenate $namefilter to passed filter and restore the last ')'
								$query = $substr.$namefilter.')';
							} else $query = $namefilter;
							
							$groupres = ldap_search($handle, $options['basedn'], $query);
							if ($groupres!==false) $groupentries = ldap_get_entries($handle, $groupres); 
							else throw new Exception(ldap_err2str(ldap_errno($handle)), ldap_errno($handle));
							
							if ($groupentries!==false && is_array($groupentries) && count($groupentries)>0) {
								if($groupentries['count']>0) {
									// all went ok here: user has been found, user data has been loaded
									// and user memberUid was found on the passed basedn, create ADA user 
									$userType = $options['usertype'];
									/**
									 * build user array
									 */
									$adaUser = array(
											'nome' => $entries['givenname'][0],
											'cognome' => $entries['sn'][0],
											'email' => 'nobody',
											'username' => $entries['uid'][0],
											'tipo' => $options['usertype'],
											'cap' => '',
											'matricola' => '',
											'avatar' => '',
											'birthcity' => ''
									);
									
									if (isset($handle) && !is_null($handle)) ldap_unbind($handle);
									return $this->addADAUser($adaUser);
								}
							}
							
							return new Exception(translateFN('Utente non trovato nel dn fornito per').' '.$options['name']);
						}
						
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
			if (!is_null($handle)) ldap_unbind($handle);
			// 'Invalid credentials' (code:49)  gets ADA's own message as text
			if ($e->getCode()==self::INVALID_USERNAME_EXECEPTION_CODE) {
				return new Exception(translateFN("Username  e/o password non valide"), self::INVALID_USERNAME_EXECEPTION_CODE);
			}
			return new Exception($e->getMessage().' '.translateFN('di').' '.$options['name']);
		}
	}
}
