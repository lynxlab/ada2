<?php
/**
 * FormatSupported.inc.php
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
 * Middleware that will set the response status to 400
 * if the requested format parameter is not supported
 * 
 * @author giorgio
 */
class FormatSupported extends \Slim\Middleware {

	private $isFormatSupported = false;
	
	public function __construct($formatSupported) {
		if (is_bool($formatSupported)) {
			$this->isFormatSupported = $formatSupported;
		}
	}
	
	/**
	 * if format is not supported sets response status
	 * to 400 and body to 'Unsupported Output Format'
	 *
	 * @see \Slim\Middleware::call()
	 */
	public function call()
	{
		if (!$this->isFormatSupported)
		{
			$this->app->response->setStatus(400);
			$this->app->response->setBody('Unsupported Output Format');
		}
		else 
			$this->next->call();
	}
}
?>