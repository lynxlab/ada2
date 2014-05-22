<?php
/**
 * UserController.inc.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */
namespace AdaApi;

/**
 * User controller for handling /users API endpoint
 *
 * @author giorgio
 */
class UserController extends AbstractController implements AdaApiInterface {

	/**
	 * Users own array key mappings
	 * 
	 * @var array
	 */
	private static $_userKeyMappings = array (
			'tipo' => 'type',
			'codice_fiscale' => 'tax_code',
			'sesso' => 'gender',
			'stato' => 'status',
			'matricola' => 'student_number'
	);

	/**
	 * GET method.
	 * 
	 * Must be called with id parameter in the params array
	 * Return the user object converted into an array.
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::get()
	 */
	public function get(array $params = array()) {
		/**
         * Are passed parameters OK?
		 */
		$paramsOK = true;
				
		if (!empty($params)) {
			
			/**
			 * This GLOBAL is needed by the MultiPort 
			 */
			$GLOBALS['common_dh'] = $this->common_dh;
			
			/**
			 * User Object to return
			 */
			$userObj = null;
			
			if (intval($params['id'])>0) {
				
				/**
				 * Check on user type to prevent multiport to
				 * do its error handling if no user found
				 */
				if (!\AMA_DB::isError($this->common_dh->get_user_type ($params['id']))) {
					$userObj = \MultiPort::findUser(intval($params['id']));
				}
			} else if (isset($params['email']) && strlen($params['email'])>0) {
				
				/**
				 * If an email has been passed, validate it
				 */
				$searchString = \DataValidator::validate_email($params['email']);				
			} else if (isset($params['username']) && strlen($params['username'])>0) {
				
				/**
				 * If a username has been passed, validate it
				 */
				$searchString = \DataValidator::validate_username($params['username']);
			} else {
				
				/**
				 * Everything has been tried, passed parameters are not OK
				 */
				$paramsOK = false;
			}
			
			/**
			 * If parameters are ok and userObj is still
			 * null try to do a search by username
			 */
			if ($paramsOK && is_null($userObj) && ($searchString!==false)) {
				$userObj = \MultiPort::findUserByUsername($searchString);
			} else if ($searchString === false) {
				/**
				 * If either the passed email or username are not validated
				 * the parameters are not OK
				 */
				$paramsOK = false;
			}
			
			if ($paramsOK && !is_null($userObj) && !\AMA_DB::isError($userObj)) {
				
				/**
				 * Build the array to be returned from the object
				 */
				$returnArray =  $userObj->toArray();
				
				/**
				 * Unset unwanted keys
				 */
				unset ($returnArray['password']); // hide the password, even if it's encrypted
				unset ($returnArray['tipo']);     // hide the user type as of 13/mar/2014
				unset ($returnArray['stato']);    // hide the user status as of 13/mar/2014
				unset ($returnArray['lingua']);   // hide the user language as of 13/mar/2014
				
				/**
				 * Perform the ADA=>API array key mapping
				 */
				self::ADAtoAPIArrayMap($returnArray, self::$_userKeyMappings);
								
			} else if ($paramsOK) {
				throw new APIException('No User Found', 404);
			}
		} else {
			$paramsOK = false;
		}
		
		/**
		 * Final check: if all OK return the data else throw the exception
		 */
		if ($paramsOK && is_array($returnArray)) {
			return $returnArray;
		} else if (!$paramsOK) {
			throw new APIException('Wrong Parameters', 400);
		} else {
			throw new APIException('Unkonwn error in users get method', 500);
		}
	}
	
	/**
	 * POST method.
	 * 
	 * If it's been reached with an application/json Content-type header
	 * it expects the user json object in the request body,
	 * else the $params array must contain the user data to be saved
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::post()
	 */
	public function post(array $params = array()) {
		
		/**
		 * Check if header says it's json
		 */
		if (strcmp($this->slimApp->request->getContentType(),'application/json')===0) {
			
			/**
			 *  SLIM has converted the body to an array alreay
			 */
			$userArr = $this->slimApp->request->getBody();
		} else if (!empty($params) && is_array($params)) {
			
			/**
			 * Assume we've been passed an array 
			 */
			$userArr = $params;
		} else {
			throw new APIException('Wrong Parameters', 400);
		}

		/**
		 * Some ADA files are needed for the computation
		 */
		require_once ROOT_DIR.'/include/translator_class.inc.php';
		require_once ROOT_DIR.'/include/output_funcs.inc.php';
		require_once ROOT_DIR.'/include/token_classes.inc.php';
		require_once ROOT_DIR.'/include/Forms/UserRegistrationForm.inc.php';
		require_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';
			
		/**
		 * This GLOBAL is needed by the MultiPort and Translator class
		 */
		$GLOBALS['common_dh'] = $this->common_dh;
		
		/**
		 * Load supported languages
		 */
		\Translator::loadSupportedLanguagesInSession();
		
		/**
		 * Convert API array keys to ADA array keys just
		 * before instantiating the user object
		 */
		self::APItoADAArrayMap($userArr, self::$_userKeyMappings);
		
		/**
		 * Unset the id (if any) to save as new user
		 */
		if (isset($userArr['user_id'])) unset ($userArr['user_id']);
		
		/**
		 * Set username to the email.
		 */
		$userArr['username'] = $userArr['e_mail'];				
		$userArr['email'] = $userArr['e_mail'];
		
		/**
		 * Build a user object
		 */
		$userObj = new \ADAUser($userArr);
		$userObj->setLayout('');
		if (!isset($userArr['type']) || strlen($userArr['type'])<=0) $userObj->setType(AMA_TYPE_STUDENT);
		
		/**
		 * New user is always in a presubscribed status
		 */
		$userObj->setStatus(ADA_STATUS_PRESUBSCRIBED);
		
		/**
		 * Generate a random password
		 */
		$userObj->setPassword(sha1(time()));
			
		/**
		 * Temporarly set a session user object needed
		 * to build the UserRegistrationForm and for
		 * below email message translations
		 */
		$_SESSION['sess_userObj'] = $userObj;
		$form = new \UserRegistrationForm();
		$form->fillWithArrayData($userArr);	

		/**
		 * If form is valid, save the user
		 */
		if ($form->isValid()) {
			
			/**
			 * Uncomment if the user is to be associated  
			 * by default to the public tester.
			 */
// 			$regProvider = array (ADA_PUBLIC_TESTER);
			$regProvider = array();
			
			/**
			 * Save the user in the public tester (only if 
			 * this is a multiprovider environment) and in
			 * the authenticated switcher own tester.
			 * This should be ok for non multiprovider environments.
			 */
			foreach ($this->authUserTesters as $tester) {
				array_push ($regProvider, $tester);
			}
			
			if (MULTIPROVIDER) {
				array_unshift($regProvider, ADA_PUBLIC_TESTER);
			}
			
			/**
			 * Actually saves the user
			 */
			$id_user = \Multiport::addUser($userObj,$regProvider);
				
			if ($id_user < 0) {
				
				/**
				 * an error occoured 
				 */				
				$saveResults = array( 'status'=>'FAILURE',
									  'message'=>'Check if a user exists already having passed email and username');
			} else {
				
				/**
				 * saved ok
				 */
				$saveResults = array( 'status'=>'SUCCESS',
									  'user_id'=>$id_user);
				/**
				 * Set HTTP status to 201: Created before returning
				 */
				$this->slimApp->status(201);
				
				/**
				 * Build and send a registration email as per browsing/registration.php file
				 */
				
				/**
				 * Create a registration token for this user and send it to the user
				 * with the confirmation request.
				 */
				$tokenObj = \TokenManager::createTokenForUserRegistration($userObj);
				if($tokenObj != false) {
					
					$token = $tokenObj->getTokenString();
					
					$admTypeAr = array(AMA_TYPE_ADMIN);
					$extended_data = TRUE;
					$admList = $this->common_dh->get_users_by_type($admTypeAr, $extended_data);
					if (!\AMA_DataHandler::isError($admList) && array_key_exists('username',$admList[0]) && $admList[0]['username'] != '' && $admList[0]['username'] != null){
						$adm_uname = $admList[0]['username'];
						$adm_email = $admList[0]['e_mail'];
					} else {
						$adm_uname = ADA_ADMIN_MAIL_ADDRESS;
						$adm_email = ADA_ADMIN_MAIL_ADDRESS;
					}
					
					$switcherObj = \Multiport::findUser($this->authUserID);
					$emailLang = $switcherObj->getLanguage();
						
					$title = PORTAL_NAME.': ' . translateFN('ti chiediamo di confermare la registrazione.',null,$emailLang);
					
					$text = sprintf(translateFN('Gentile %s, ti chiediamo di confermare la registrazione ai %s.',null,$emailLang),
							$userObj->getFullName(), PORTAL_NAME)
							. PHP_EOL . PHP_EOL
							. translateFN('Il tuo nome utente è il seguente:',null,$emailLang)
							. ' ' . $userObj->getUserName()
							. PHP_EOL . PHP_EOL
							. sprintf(translateFN('Puoi confermare la tua registrazione ai %s seguendo questo link:',null,$emailLang),
									PORTAL_NAME)
									. PHP_EOL
									. ' ' . HTTP_ROOT_DIR."/browsing/confirm.php?uid=$id_user&tok=$token"
									. PHP_EOL . PHP_EOL;
					
					$message_ha = array(
							'titolo' => $title,
							'testo' => $text,
							'destinatari' => array($userObj->getUserName()),
							'data_ora' => 'now',
							'tipo' => ADA_MSG_SIMPLE,
							'mittente' => $adm_uname
					);
					
					$mh = \MessageHandler::instance(\MultiPort::getDSN($tester));
					
					/**
					 * Send the message as an internal message,
					 * don't care if an error occours here
					 * 
					 * Commented on 07/mag/2014 15:56:46
					*/
					// $result = $mh->send_message($message_ha);
					
					/**
					 * Send the message as an email message
					 */
					$message_ha['tipo'] = ADA_MSG_MAIL;
					$result = $mh->send_message($message_ha);
					if(\AMA_DataHandler::isError($result)) {
						$saveResults['message'] = 'An error occoured while emailing the user.';
					}
				} else {
					$saveResults['message'] = 'An error occourred while building the confirmation token.';
				}
				
				/**
				 * Done email sending.
				 */
				if (isset($saveResults['message']) && strlen($saveResults['message'])>0) {
					$saveResults['message'] .= 'The confirmation email has not been sent, please contact the user directly.';
				}				
			}
			return $saveResults;
		} else {
			
			/**
			 * Try to investigate what the missing fields are
			 */
			foreach ($form->getControls() as $control) {
				if ($control->getIsMissing()) {
					
					/**
					 * Build an array with missing fields as keys
					 */
					$missingValues[$control->getId()] = TRUE;
				}
			}
			if (isset($missingValues) && sizeof($missingValues)>0) {
				
				/**
				 * Map the missingValues keys to API keys
				 */
				self::ADAtoAPIArrayMap($missingValues,self::$_userKeyMappings);
				
				/**
				 * Extract the missingValues keys to build the
				 * list of missing or invalid value
				 */
				$missingValues = ': '. implode(', ', array_keys($missingValues));
			} else {
				$missingValues = ': Unable to build missing fields list';
			}
			
			/**
			 * Throws the exception
			 */
			throw new APIException('Missing or Invalid User Fields'.$missingValues, 400);
		}
		
		/**
		 * The session user object is no longer needed
		 */		
		unset ($_SESSION['sess_userObj']);		
	}
	
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
}
?>