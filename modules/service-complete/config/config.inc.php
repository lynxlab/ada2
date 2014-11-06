<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

/**
 * the num of rulesets placed in logical OR
 * between the. i.e. the number of cols in
 * the UI table when editing the rule
 */
define ('NUM_RULES_SET',3);

$GLOBALS['completeClasses'][]  = 'completeConditionTime';
$GLOBALS['completeClasses'][]  = 'completeConditionLevel';

?>
