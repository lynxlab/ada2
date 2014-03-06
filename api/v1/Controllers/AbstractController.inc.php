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
abstract class AbstractController {
	
	protected $common_dh;
	protected $slimApp = null;
	
	public function __construct(\Slim\Slim $app) {
		$this->common_dh = \AMA_Common_DataHandler::instance();
		$this->slimApp = $app;
	}
}

?>