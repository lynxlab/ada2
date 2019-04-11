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
 * given an array of conditions an params coming
 * from the form POST, will build an array ready to be passed
 * the operation builder. e.g.
 *
 	[condition] => Array
        (
            [0] => Array
                (
                    [0] => completeConditionTime
                    [1] => null
                    [2] => null
                )

            [1] => Array
                (
                    [0] => completeConditionTest1
                    [1] => null
                    [2] => null
                )

            [2] => Array
                (
                    [0] => completeConditionTest2
                    [1] => null
                    [2] => null
                )

        )

    [param] => Array
        (
            [0] => Array
                (
                    [0] => 12
                    [1] =>
                    [2] =>
                )

            [1] => Array
                (
                    [0] =>
                    [1] =>
                    [2] =>
                )

            [2] => Array
                (
                    [0] =>
                    [1] =>
                    [2] =>
                )

        )

after running the function, $conditions will be:

	Array
	(
	    [0] => Array
	        (
	            [0] => completeConditionTime::buildAndCheck(12);
	        )

	    [1] => Array
	        (
	            [0] => completeConditionTest1::buildAndCheck();
	        )

	    [2] => Array
	        (
	            [0] => completeConditionTest2::buildAndCheck();
	        )

	)

 *
 *
 * @param array $conditions
 * @param array $params
 */

function fixPOSTArray (&$conditions, $params)
{
	foreach ($conditions as $key=>&$val) {
		if (is_array($val)) {
			fixPOSTArray($val,$params[$key]);
			if (empty($val)) unset ($conditions[$key]);
		} else {
			if ($val==='null') unset ($conditions[$key]);
			else $val .= sprintf ('::buildAndCheck(%s)',$params[$key]);
		}
	}
}

/**
 * extract the parameter from a static methhod call
 * e.g. given foo:bar(666) will return 666
 *
 * @param string $stringCond
 * @return string
 */
function extractParam ($stringCond)
{
	$matches = array();
	preg_match ('/(\w+)::\w+[(](\d*)[)]/',$stringCond,$matches);
	return $matches[2];
}

function logToFile($logLines = array()) {
    require_once ROOT_DIR .'/include/logger_class.inc.php';

	if (!is_dir(MODULES_SERVICECOMPLETE_LOGDIR)) {
		$oldmask = umask(0);
		mkdir (MODULES_SERVICECOMPLETE_LOGDIR, 0775, true);
		umask($oldmask);
	}
	$dataLogFile = MODULES_SERVICECOMPLETE_LOGDIR . 'service-complete-'.date('Ymd').'.log';
    if (!is_file($dataLogFile)) touch ($dataLogFile);

    if (!is_array($logLines)) $logLines = array($logLines);
    array_unshift($logLines, '===============================');
    $logLines[] = '===============================';
	ADAFileLogger::log(implode(PHP_EOL, $logLines).PHP_EOL, $dataLogFile);
}
