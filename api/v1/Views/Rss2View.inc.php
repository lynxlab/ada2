<?php
/**
 * Rss2View.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;
class Rss2View extends RssView {		
	public function render($template)
	{
		$this->feedObject = new \RSS2FeedWriter();
		$this->feedObject->setLink(HTTP_ROOT_DIR . '/api/'.ADA_API_VERSION.'/'.$template.'.rss2');
		parent::render($template);		
	}
}
?>