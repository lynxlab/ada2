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

if(isset($_GET['id']))
{
	$remindme = isset($_GET['remindme']) ? intval($_GET['remindme']) : 0;
	$selectedLanguage = isset($_GET['lang']) ? trim($_GET['lang']) : null;
	$hybridLogin = new hybridLogin(intval($_GET['id']));

	try {
		$hybridLogin->loadHybridAuth();
		$successfulOptionsID = $hybridLogin->authenticate();
		$user_profile = $hybridLogin->getUserProfile();

	    if ($user_profile && isset($user_profile->identifier)) {

	    	$adaUser = $hybridLogin->buildADAUserFromProviderObj($user_profile);
	    	/**
	    	 * look if user is already in ADA DB
	    	 */
	    	$userObj = $hybridLogin->checkADAUser($adaUser['email']);

	    	if (!is_object($userObj) || !$userObj instanceof ADALoggableUser) {
	    		/**
	    		 * if user is not in the ADA DB, add it
	    		 */
		    	$userObj = $hybridLogin->addADAUser($adaUser,
		    			function($newUserObj) use ($hybridLogin, $user_profile, $adaUser) {
		    				$hybridLogin->addADASuccessCallBack($newUserObj, $user_profile->photoURL, $adaUser['avatar']);
		    			},
		    			function() use ($hybridLogin) {
		    				$hybridLogin->addADAErrorCallBack();
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
	    		// WARNING!! only one set of options is supported on this login provider
	    		$hybridLogin->setSuccessfulOptionsID($successfulOptionsID);
	    		// set session and redirect
	    		ADALoggableUser::setSessionAndRedirect($userObj, $remindme, $selectedLanguage, $hybridLogin);
	    	} else {
	    		// throw an exception
	    		$hybridLogin->logOutFromProvider();
	    		throw new Exception(null,9);
	    	}
	    }
    }

    catch( Exception $e )
    {
		 require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
		 BrowsingHelper::init($neededObjAr);
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
                         $hybridLogin->logOutFromProvider();
                         break;
                case 7 : $message = "Utente non connesso al provider di login.";
                         $hybridLogin->logOutFromProvider();
                         break;
                case 8 : $message = "Il provider di login non supporta la funzionalità richiesta."; break;
                case 9 : $message = "Problema nel generare l'oggetto utente di ADA"; break;
                default : $message = ""; break;
        }

        $message = translateFN($message)."<br /><br /><b>".translateFN("Messaggio d'errore originale").":</b> " . $e->getMessage();

        $messagespan = CDOMElement::create('span','class: login-error-message');
        $messagespan->addChild(new CText(translateFN($message)));

        $content_dataAr = array(
			'help' => translateFN('Problema Autenticazione ').$hybridLogin->loadProviderName(),
			'data' => $messagespan->getHtml()
		);
        $self = 'login-error';
        ARE::render(null, $content_dataAr);

    }
}
?>
