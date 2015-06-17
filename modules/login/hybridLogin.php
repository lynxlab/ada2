<?php

/**
 * LOGIN MODULE -hybridLogin.php - performs user login using hybrid lybrary
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
 
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_VISITOR);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_VISITOR => array()
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once MODULES_LOGIN_PATH . '/include/hybridLogin.class.inc.php';
require_once MODULES_LOGIN_PATH . '/include/Hybrid/Auth.php';

/**
 * only allow local execution
 */
if (isset($_SERVER['HTTP_REFERER'])) {
	if(preg_match("#^".HTTP_ROOT_DIR."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1) {
		die ('Only local execution allowed.');
	}
}

if(isset($_GET['id']))
{	
	$remindme = isset($_GET['remindme']) ? intval($_GET['remindme']) : 0;
	$selectedLanguage = isset($_GET['lang']) ? trim($_GET['lang']) : null;
	
	$hybridLogin = new hybridLogin(intval($_GET['id']));
	$options = $hybridLogin->loadOptions();
	$providerName = ucfirst(strtolower($hybridLogin->loadProviderName()));
	
	try {
		switch ($providerName) {
			case 'Google':
			case 'Facebook':
				$config = array(
						'base_url' =>   $options['base_url'],
						'providers' => array (
								$providerName => array (
										'enabled' => true,
										'keys'    => array (
												'id' => $options['id'],
												'secret' => $options['secret'] )
								)
						)
				);
				// optionals
				if (isset($options['scope'])) {
					$config['providers'][$providerName]['scope'] = $options['scope'];
				}
				break;
		}
	
	    $hybridauth = new Hybrid_Auth( $config );
	    $authProvider = $hybridauth->authenticate($providerName);
	    $user_profile = $authProvider->getUserProfile();
	    
	    if ($user_profile && isset($user_profile->identifier)) {
	    	/**
	    	 * Prepare email field
	    	 */
	    	if (isset($user_profile->emailVerified) && strlen($user_profile->emailVerified)>0) {
	    		$email = $user_profile->emailVerified;
	    	} else if (isset($user_profile->email) && strlen($user_profile->email)>0) {
	    		$email = $user_profile->email;
	    	} else $email = null;
	    	
	    	/**
	    	 * look if user is already in ADA DB
	    	 */	    	
	    	$userObj = $hybridLogin->checkADAUser($email);

	    	if (!is_object($userObj) || !$userObj instanceof ADALoggableUser) {
	    		/**
	    		 * if user is not in the ADA DB, prepare data and save
	    		 */
	    		
	    		/**
	    		 * prepare birthdate
	    		 */
		    	if ($user_profile->birthDay>0 && $user_profile->birthMonth>0 && $user_profile->birthYear>0) {
		    		$birthDate = sprintf("%02d",$user_profile->birthDay). '/' .  
		    		 			 sprintf("%02d",$user_profile->birthMonth) .'/' .
		    					 $user_profile->birthYear; 
		    	} else $birthDate = null;
		    	
		    	/**
		    	 * prepare gender
		    	 */
		    	if (strtolower($user_profile->gender) == 'male') $gender = 'M';
		    	else if (strtolower($user_profile->gender) == 'female') $gender = 'F';
		    	else $gender = null;
		    	
		    	/**
		    	 * prepare avatar
		    	 */
		    	if (isset($user_profile->photoURL) && strlen($user_profile->photoURL)>0) {
		    		// get the basename and remove any URL arguments
		    		$avatar = strtok(basename($user_profile->photoURL),'?');
		    		if (stristr($avatar, '.')===false) $avatar .= '.png';
		    	} else $avatar = null;
		    	
		    	/**
		    	 * prepare language
		    	 */
		    	if (isset($user_profile->language) && strlen($user_profile->language)>0) {
		    		if (strlen($user_profile->language)>2) {
		    			$lang = substr($user_profile->language, 0,2);
		    		}
		    		foreach (Translator::getSupportedLanguages() as $supportedLang) {
		    			if (strtolower($supportedLang['codice_lingua']) === strtolower($lang)) {
		    				$language = $supportedLang['id_lingua'];
		    				break;
		    			}
		    		}
		    	} else $language = null;
		    	
		    	/**
		    	 * build user array
		    	 */
		    	$adaUser = array(
		    			'nome' => $user_profile->firstName,
		    			'cognome' => $user_profile->lastName,
		    			'email' => $email,
		    			'username' => $email,
		    			'indirizzo' => (isset($user_profile->address) && strlen($user_profile->address)>0) ? $user_profile->address : null,
		    			'citta' => (isset($user_profile->city) && strlen($user_profile->city)>0) ? $user_profile->city : null,
		    			'provincia' => (isset($user_profile->region) && strlen($user_profile->region)>0) ? $user_profile->region : null,
		    			'nazione' => (isset($user_profile->country) && strlen($user_profile->country)>0) ? $user_profile->country : null,
		    			'birthdate' => $birthDate,
		    			'sesso' => $gender,
		    			'telefono' => (isset($user_profile->phone) && strlen($user_profile->phone)>0) ? $user_profile->phone : null,
		    			'lingua' => $language,
		    			'cap' => (isset($user_profile->zip) && strlen($user_profile->zip)>0) ? $user_profile->zip : '',
		    			'avatar' => $avatar,
		    			'birthcity' => '',
		    			'matricola' => '',
		    			'stato' => ''
		    	);
		    	
		    	$userObj = $hybridLogin->addADAUser($adaUser,
		    			function($newUserObj) use ($hybridLogin, $user_profile, $avatar) {
		    				$hybridLogin->addADASuccessCallBack($newUserObj, $user_profile->photoURL, $avatar);
		    			},
		    			function() use ($hybridLogin, $authProvider) {
		    				$hybridLogin->addADAErrorCallBack($authProvider);
		    			});
	    	}
	    	
	    	/**
	    	 * At this point, either the $userObj was already in
	    	 * ADA DB or had just been created by the above code
	    	 */
	    	if (is_object($userObj) && $userObj instanceof ADALoggableUser) {
	    		/**
	    		 * $selectedLanguage is coming from $_GET and is the
	    		 * user selection in the login form. If the login provider
	    		 * sets an ADA supported user language, set that instead of
	    		 * user selection
	    		 */
	    		if (!is_null($language)) $selectedLanguage = $language;
	    		// set session and redirect
	    		ADALoggableUser::setSessionAndRedirect($userObj, $remindme, $selectedLanguage, $hybridLogin);
	    	} else {
	    		// throw an exception
	    		$authProvider->logout();
	    		throw new Exception(null,9);
	    	}
	    }           
    }
    
    catch( Exception $e )
    { 
    	 require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
         switch( $e->getCode() )
         {
                case 0 : $message = "Errore sconosciuto."; break;
                case 1 : $message = "Errore di configurazione di Hybridauth."; break;
                case 2 : $message = "Provider di login non configurato bene."; break;
                case 3 : $message = "Provider di login disabilitato o sconosciuto."; break;
                case 4 : $message = "Mancano le credenziali dell'applicazione presso il provider di login."; break;
                case 5 : $message = "Autenticazione non riuscita: l'utente ha annullato l'autenticazione o il provider rifiuta la connessione";
                         break;
                case 6 : $message = "Richiesta del profilo utente fallita. Probabilmente non è connesso al provider e deve autenticarsi di nuovo";
                         $authProvider->logout();
                         break;
                case 7 : $message = "Utente non connesso al provider di login.";
                         $authProvider->logout();
                         break;
                case 8 : $message = "Il provider di login non supporta la funzionalità richiesta."; break;
                case 9 : $message = "Problema nel generare l'oggetto utente di ADA"; break;
                default : $message = ""; break;
        }
 
        $message .= "<br /><br /><b>".translateFN("Messaggio d'errore originale").":</b> " . $e->getMessage();
 
        $messagespan = CDOMElement::create('span','class: login-error-message');
        $messagespan->addChild(new CText(translateFN($message)));
        
        $content_dataAr = array(
			'help' => translateFN('Problema Autenticazione ').$providerName,
			'data' => $messagespan->getHtml()
		);
        $self = 'login-error';
        ARE::render(null, $content_dataAr);
        
    }
}
?>
