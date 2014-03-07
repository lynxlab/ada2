<?php
/**
 * CleanQueryString.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;
/**
 * Middleware to clean the query string from
 * unwanted and unneeded data
 * 
 * @author giorgio
 */
class CleanQueryString extends \Slim\Middleware {

	private $_parametersToRemove = null;
	
	public function __construct($parametersToRemove) {
		if (is_array($parametersToRemove)) {
			$this->_parametersToRemove = $parametersToRemove;
		} else {
			$this->_parametersToRemove = array($parametersToRemove);
		}
	}
	
	/**
	 * cleans the querystring from unwanted parameters
	 *
	 * @see \Slim\Middleware::call()
	 */
	public function call()
	{
		// parse the query string into an array
		parse_str($this->app->environment['QUERY_STRING'],$params);
		if (!is_null($this->_parametersToRemove) && sizeof($this->_parametersToRemove)>0) {
			foreach ($this->_parametersToRemove as $key=>$parameter) {
				if (strlen($parameter)>0 && isset($params[$parameter])) {
					// unset unwanted parameter
					unset ($params[$parameter]);
				}
			}
		}
		// reset the query string without unwanted parameters
		$this->app->environment['QUERY_STRING'] = http_build_query($params);
		// resume execution
		$this->next->call();
	}
}
?>