<?php
/**
 * TesterController.inc.php
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
 * Tester controller for handling /testers API endpoint
 * 
 * @author giorgio
 */
class TesterController extends AbstractController implements AdaApiInterface {

	/**
	 * GET method.
	 * 
	 * Must be called with empty array parameter and shall
	 * return all the tester that an authenticated user calling
	 * the API is subscribed to.
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::get()
	 */
	public function get (array $params = array()) {
		if (empty($params)) {
			// This GLOBAL is needed by the MultiPort
			$GLOBALS['common_dh'] = $this->common_dh;			
			$testers = \MultiPort::getTestersPointersAndIds();
			
			if (!\AMA_DB::isError($testers)) {
				// need to map $testers to id and name pairs
				if (!MULTIPROVIDER && (is_null($this->authUserID) || !is_array($this->authUserTesters))) {
					throw new APIException('No Auth User or Testers Found', 400);
				} else {
					foreach ($testers as $testername=>$testerid) {
						if (MULTIPROVIDER || in_array($testername,$this->authUserTesters))
							$retArray[] = array ('id'=>$testerid, 'name'=>$testername);
					}
					if (isset($retArray) && count($retArray)>0) {
						return $retArray;
					} else {
						throw new APIException('No Tester Found', 404);
					}
				}
			} else {
				throw new APIException('No Tester Found', 404);
			}
		} else {
			throw new APIException('Wrong Parameters', 400);
		}
	}
	
	public function post   (array $params = array()) {}
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
}

?>