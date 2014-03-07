<?php
/**
 * AbstractController.inc.php
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
 * Abstract ADA API Controller
 * All Controllers must extend this class and implement the AdaApiInterface
 * 
 * @author giorgio
 *
 */
abstract class AbstractController {
	/**
	 * ADA common data handler
	 * 
	 * @var AMA_Common_DataHandler
	 */
	protected $common_dh;
	
	/**
	 * The SLIM application object
	 * 
	 * @var Slim
	 */
	protected $slimApp = null;
	
	/**
	 * The OAuth2 authorized user id, if any
	 * 
	 * @var number
	 */
	protected $authUserID = null;
	
	/**
	 * The array of the authorized user's tester
	 * 
	 * @var array
	 */
	protected $authUserTesters = null;
	
	public function __construct(\Slim\Slim $app, $authUserID=0) {
		// get an instance of the ADA common DataBase
		$this->common_dh = \AMA_Common_DataHandler::instance();
		// store the SLIM app object
		$this->slimApp = $app;
		// if an authoized user id is passed, store it
		// and retreive the testers she belongs to
		if ($authUserID>0) {
			$this->authUserID = intval($authUserID);
			$this->authUserTesters = $this->common_dh->get_testers_for_user($this->authUserID);
		}
	}
}
?>