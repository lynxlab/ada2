<?php
/**
 * PhpView.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;
class PhpView extends \Slim\View {
	
	public function render($template)
	{
		$this->data['app']->response()->header('Content-Type', 'text/php');
		echo serialize($this->data['output']);
	}
}
?>