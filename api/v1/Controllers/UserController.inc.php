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
			$userObj = \MultiPort::findUser(intval($params['id']));
			
			if (!\AMA_DB::isError($userObj)) return $userObj->toArray();
			else {
				$this->slimApp->halt(404, 'No User Found');
			}
		} else $this->slimApp->halt(404, 'Wrong parameters');
	}
	
	public function post   (array $params = array()) {}
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
}
?>