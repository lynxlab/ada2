<?php
/**
 * RssView.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;
class RssView extends \Slim\View {
	
	protected $feedObject;	
	private $_rssArray;
	
	public function __construct() {
		parent::__construct();
		/**
		 * This is a modified version of
		 * https://github.com/ajaxray/FeedWriter
		 * Written by: Anis uddin Ahmad <anisniit@gmail.com>
		 */
		include_once("FeedWriter/FeedTypes.php");
	}
		
	private function buildArray() {
		$this->_rssArray = array_map(function($element) {
			return array(
					'title'       => $element['name'],
					'link'        => isset($element['link']) ? $element['link'] : HTTP_ROOT_DIR,
					'description' => $element['description']
			);
		}, $this->data['output']);
	}
	
	public function render($template)
	{
		$this->buildArray();

		$this->feedObject->setTitle('Courses Available on '.PORTAL_NAME);
		$this->feedObject->setChannelElement('language', 'en-us');
		$this->feedObject->setChannelElement('pubDate', date(DATE_RSS, time()));
		
		foreach ($this->_rssArray as $rssElement) {
			$newItem = $this->feedObject->createNewItem();
			$newItem->addElementArray($rssElement);
			$this->feedObject->addItem($newItem);						
		}
		
		$rss = $this->feedObject->generateFeedAsString();
		$this->data['app']->response()->header('Content-Type', $rss['contentType']);
		echo $rss['xmlcode'];
	}
}
?>