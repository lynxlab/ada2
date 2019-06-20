<?php
/**
 * @package 	forked-paths module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\ForkedPaths;

/**
 * ForkedPathsException class to handle custom exceptions
 *
 * @author giorgio
 */
class ForkedPathsException extends \Exception
{
	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
