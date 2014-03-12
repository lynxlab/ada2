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

require_once ROOT_DIR.'/include/user_classes.inc.php';

/**
 * User controller for handling /users API endpoint
 *
 * @author giorgio
 */
class UserController extends AbstractController implements AdaApiInterface {

	/**
	 * GET method.
	 * 
	 * Must be called with id parameter in the params array
	 * Return the user object converted into an array.
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::get()
	 */
	public function get (array $params = array()) {
		
		if (!empty($params) && intval($params['id'])>0) {
			// This GLOBAL is needed by the MultiPort
			$GLOBALS['common_dh'] = $this->common_dh;
			
			$userObj = null;
			/**
			 * check on user type to prevent multiport to
			 * do its error handling if no user found
			 */
			if (!\AMA_DB::isError($this->common_dh->get_user_type ($params['id']))) {
				$userObj = \MultiPort::findUser(intval($params['id']));
			}
			
			if (!is_null($userObj) && !\AMA_DB::isError($userObj)) {
				return $userObj->toArray();
			} else {
				throw new APIException('No User Found', 404);
			}
		} else {
			throw new APIException('Wrong Parameters', 400);
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
			// SLIM has converted the body to an array alreay
			$userArr = $this->slimApp->request->getBody();
		} else if (!empty($params) && is_array($params)) {
			// assume we've been passed an array
			$userArr = $params;
		} else {
			throw new APIException('Wrong Parameters', 400);
		}
		
		/**
		 * check for missing values: set needed array keys in $neededValues
		 */
		$neededValues = array ('nome','cognome','e_mail','birthdate');
		$missingValues = null;
				
		foreach ($neededValues as $value) {
			if (!isset($userArr[$value]) || strlen($userArr[$value])<=0) {
				$missingValues[] = $value;
			}
		}
		
		/**
		 * If missingValues is still null, it's ok to save
		 */
		if (is_null($missingValues))
		{
			include_once ROOT_DIR.'/include/token_classes.inc.php';
			
			/**
			 * The user is associated by default to the public tester.
			 */
			$regProvider = array (ADA_PUBLIC_TESTER);
			
			/**
			 * Proceed to the user registration.
			 */
			$userArr['username'] = $userArr['e_mail'];
			// prevents a bug in include/user_class.inc.php line 377
			$userArr['email'] = $userArr['e_mail'];
			
			$userObj = new \ADAUser($userArr);
			$userObj->setLayout('');
			$userObj->setType(AMA_TYPE_STUDENT);
			$userObj->setStatus(ADA_STATUS_PRESUBSCRIBED);
			// Random password.
			$userObj->setPassword(sha1(time()));
			
			/**
			 * Save the user in the public tester and in
			 * the authenticated switcher own tester.
			 * This should be ok for non multiprovider environments.
			 */
			if (!MULTIPROVIDER) {
				foreach ($this->authUserTesters as $tester) {
					array_push ($regProvider, $tester);
				}
			}
			
			// This GLOBAL is needed by the MultiPort
			$GLOBALS['common_dh'] = $this->common_dh;
			$id_user = \Multiport::addUser($userObj,$regProvider);
			
			if ($id_user < 0) {				
				// an error occoured
				$saveResults = array(
					'status'=>'FAILURE',
					'message'=>'Check if a user exists already having passed email and username'
				);
			} else {
				// saved ok
				$saveResults = array(
					'status'=>'SUCCESS',
					'user_id'=>$id_user
				);
			}
			return $saveResults;
		} else {
			throw new APIException('Missing User Values: '.implode(',', $missingValues), 400);
		}
	}
	
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
}
?>