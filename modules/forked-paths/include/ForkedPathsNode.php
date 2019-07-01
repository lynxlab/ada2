<?php
/**
 * @package 	forked-paths module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\ForkedPaths;

require_once (ROOT_DIR . '/include/node_classes.inc.php');

/**
 * ForkedPathsNode class
 *
 * @author giorgio
 *
 */
class ForkedPathsNode extends ForkedPathsBase {

	/**
	 * Node "magic keyword" that tags the node as part of a forked path
	 *
	 * @var string
	 */
	const MAGIC_KEYWORD = '##FORKEDPATHS##';

	/**
	 * true if the children of a forked path node must be removed from the student course index
	 *
	 * @var bool
	 */
	const REMOVE_CHILDREN_FROM_INDEX = REMOVE_CHILDREN_FROM_INDEX;

	/**
	 * max number of buttons to be displayed in a row when viewing the forked path node
	 *
	 * @var int
	 */
	const MAX_BUTTONS_PER_ROW = 3;

	/**
	 * Check if the title (aka keywords) contains the magic keyword
	 *
	 * @param string $title
	 * @return bool
	 */
	public static function checkNodeFromTitle($title) {
		return in_array(self::MAGIC_KEYWORD, self::getKeywordsFromTitle($title));
	}

	/**
	 * Check if the passed node is a forked path node
	 *
	 * @param \Node $node
	 * @return bool
	 */
	public static function checkNode(\Node $node) {
		return self::checkNodeFromTitle($node->title);
	}


	/**
	 * Converts the passed Node to a forked path, by adding the magic keyword
	 * and setting the Node's isForkedPaths property to true
	 *
	 * @param \Node $node
	 * @return \Node
	 */
	public static function toForkedPathsNode(\Node $node) {
		if (!self::checkNodeFromTitle($node->title)) {
			$node->title = self::addMagicKeywordToTitle($node->title);
			$node->isForkedPaths = true;
		}
		return $node;
	}

	/**
	 * Adds the magic keyword to the passed node title (aka keywords)
	 *
	 * @param string $title
	 * @return string
	 */
	public static function addMagicKeywordToTitle($title) {
		if(!self::checkNodeFromTitle($title)) {
			$keywords = self::getKeywordsFromTitle($title);
			array_push($keywords, self::MAGIC_KEYWORD);
			$title = implode(',',$keywords);
		}
		return $title;
	}

	/**
	 * Converts the passed node to a standard node (non forked-paths)
	 * by removing the magic keyword and setting the node's isForkedPaths to false
	 *
	 * @param \Node $node
	 * @return void
	 */
	public function toADANode(\Node $node) {
		$node = self::removeMagicWord($node);
		$node->isForkedPaths = false;
		return $node;
	}

	/**
	 * Removes the magic keyword from the passed title (aka keywords)
	 *
	 * @param string $title
	 * @return string
	 */
	public static function removeMagicWordFromTitle($title) {
		if (self::checkNodeFromTitle($title)) {
			$keywords = self::getKeywordsFromTitle($title);
			foreach ($keywords as $count=>$keyword) {
				if ($keyword == self::MAGIC_KEYWORD) unset($keywords[$count]);
			}
			$title = implode(',', $keywords);
		}
		return $title;
	}

	/**
	 * Removes the magic keyword from the passed node title (aka keywords)
	 *
	 * @param \Node $node
	 * @return \Node
	 */
	public static function removeMagicWord(\Node $node) {
		if (self::checkNodeFromTitle($node->title)) {
			$node->title = self::removeMagicWordFromTitle($node->title);
		}
		return $node;
	}

	/**
	 * Removes the children of any forked paths node from the index array
	 *
	 * @param array $courseData index array, as returned by $dh->get_course_data
	 * @return array
	 */
	public static function removeForkedPathsChildrenFromIndex($courseData) {
		if (self::REMOVE_CHILDREN_FROM_INDEX) {
			$keysCourseData = [];
			foreach ($courseData as $node) $keysCourseData[$node['id_nodo']] = $node;
			return array_filter($courseData, function($nodeArr) use ($keysCourseData) {
				if (self::checkNodeFromTitle($nodeArr['titolo']) && isset($keysCourseData[$nodeArr['id_nodo_parent']]['titolo'])) {
					return !self::checkNodeFromTitle($keysCourseData[$nodeArr['id_nodo_parent']]['titolo']);
				}
				return true;
			});
		} else return $courseData;
	}

	/**
	 * Builds the button to follow the node's forked path when the student is viewing a node
	 *
	 * @param \Node $node
	 * @return \CBase (either \CText or \CDiv)
	 */
	public static function buildForkedPathsButtons(\Node $node) {
		if (self::checkNode($node)) {
			if (is_array($node->children)) {
				$container = \CDOMElement::create('div','class:forkedpaths buttons container');
				$children = self::filterChildren($node);
				// must load all children to find out the ForkedPathsNode ones
				while(count($children)>0) {
					$rowCount = count($children) >= self::MAX_BUTTONS_PER_ROW ? self::MAX_BUTTONS_PER_ROW : count($children);
					$row = \CDOMElement::create('div',"class:$rowCount fluid ui buttons");
					while($rowCount-->0) {
						$child = array_shift($children);
						$button = \CDOMElement::create('button','type:button,class:ui button');
						$jsArgs = [
							'baseUrl' => MODULES_FORKEDPATHS_HTTP,
							'fromId'  => $node->id,
							'toId' => $child->id,
						];
						$button->setAttribute('onclick','javascipt:followForkedPath('.htmlspecialchars(json_encode($jsArgs), ENT_QUOTES, ADA_CHARSET).',$j(this));');
						$button->addChild(new \CText($child->name));
						$row->addChild($button);
					}
					$container->addChild($row);
				}
				return $container;
			}
		}
		return new \CText('');
	}

	/**
	 * Returns all the children of the passed node that are forked paths nodes
	 * eventually sorted by order
	 *
	 * @param \Node $node
	 * @param boolean $sortOrder true to sort the children by order
	 * @return array
	 */
	private static function filterChildren(\Node $node, $sortOrder = true) {
		$retArray = [];
		$orders = [];
		foreach ($node->children as $child) {
			// load node without extended info
			$childNode = new \Node($child, false);
			if (self::checkNode($childNode)) {
				array_push($retArray, $childNode);
				$orders[$childNode->id] = $childNode->order;
			}
		}
		// sort the return array by orders asc
		if ($sortOrder) {
			usort($retArray, function($a, $b) use ($orders) {
				return $orders[$a->id] - $orders[$b->id];
			});
		}
		return $retArray;
	}

	/**
	 * Converts the title string (that is a comma separated list of values)
	 * to an array
	 *
	 * @param string $title
	 * @return array
	 */
	private static function getKeywordsFromTitle($title) {
		// for some strange reason, node's keywords are stored in the title
		$keywords = explode(',', trim($title));
		array_walk($keywords, 'trim');
		return $keywords;
	}

}
