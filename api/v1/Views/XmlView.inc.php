<?php
/**
 * XmlView.inc.php
*
* @package        API
* @author         Giorgio Consorti <g.consorti@lynxlab.com>
* @copyright      Copyright (c) 2014, Lynx s.r.l.
* @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
* @link           API
* @version		  0.1
*/
namespace AdaApi;
class XmlView extends \Slim\View {
	
	public function render($rootName)
	{
		/**
		 * if rootName is a plural, e.g. "users" than the element name
		 * is going to be singular, e.g. "user"
		 * else the element name is going to be the root name and the
		 * root name shall have a _info appended
		 */
		
		if (substr($rootName, -1)==='s') $elementName = substr($rootName, 0, strlen($rootName)-1);
		else { $elementName = $rootName; $rootName .= '_info'; }
				
		require_once ROOT_DIR.'/include/ArrayToXML/array2xml.inc.php';		
		$this->data['app']->response()->header('Content-Type', 'application/xml');

		/**
		 * If the first element of data['output'] is an array than build an array
		 * of array (to nicely nest the produced XML)
		 * else it's enough to nest one level
		 */
		
		if (is_array(reset($this->data['output'])))
		{
			foreach ($this->data['output'] as $element) $outArr[$elementName][] = $element;
		} else $outArr[$elementName] = $this->data['output'];
		
		echo \ArrayToXML::toXml($outArr,$rootName);
	}
}
?>