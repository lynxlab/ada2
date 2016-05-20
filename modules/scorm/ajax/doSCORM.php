<?php
/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version		   0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array (AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_SUPERTUTOR );

/**
 * Get needed objects
 * This is generated from ADA Eclipse Developer Plugin, use it as an example!
 */
$neededObjAr = array (
		AMA_TYPE_STUDENT =>    array ('layout'),
		AMA_TYPE_TUTOR =>      array ('layout'),
		AMA_TYPE_AUTHOR =>     array ('layout'),
		AMA_TYPE_SWITCHER =>   array ('layout'),
		AMA_TYPE_SUPERTUTOR => array ('layout')
);
/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_SCORM_PATH .'/config/config.inc.php';
require_once MODULES_SCORM_PATH .'/include/functions.inc.php';
require_once MODULES_SCORM_PATH.'/include/AMAScormDataHandler.inc.php';
require_once MODULES_SCORM_PATH.'/include/SCOHelper.class.inc.php';

$GLOBALS['dh'] = AMAScormDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
$retVal = "";

if (isset($scoobject) && isset($scoid) && isset($action)) {
	// prepare message to be logged
	$scohelper = new SCOHelper($scoobject);
	$message = 'userid='.$_SESSION['sess_userObj']->getId().' - '.'action='.$action.' - '.'scoid=' .$scoid;

	if (isset($varname) && strlen($varname)>0) {
		$message .= ' - varname='.$varname;
	}

	if (isset($varvalue) && strlen($varvalue)>0) {
		$message .= ' - varvalue='.$varvalue;
	}

	if (isset($ts) && strlen($ts)>0) {
		$message .= ' - ts='.$ts;
	}

	// execute the action
	if ($_SERVER['REQUEST_METHOD']==='GET') {

		if ($action === 'getValue') {
			if (array_key_exists($varname, $GLOBALS['MODULES_SCORM_STATIC'])) {
				// if asked value is "static", return it without asking the DB
				if (!is_callable($GLOBALS['MODULES_SCORM_STATIC'][$varname])) {
					$retVal = $GLOBALS['MODULES_SCORM_STATIC'][$varname];
				} else {
					$retVal = call_user_func($GLOBALS['MODULES_SCORM_STATIC'][$varname]);
				}
			} else {
				$result = $GLOBALS['dh']->scormGetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
								'scoobject' => $scoobject,
								'scoid' => $scoid,
								'varname' => $varname
							)
						  );
				if (!AMA_DB::isError($result)) $retVal = $result;
				else $message .= "\n#### scormGetValue ERROR ####\n".print_r($result, true);
			}
		} else if ($action === 'initialize' && isset($apiversion) && in_array($apiversion, $GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS'])) {
			/**
			 * Actions done on initialize:
			 *
			 * - if cmi.core.total_time has not been set, set it to '0000:00:00' (according to the spec)
			 * - set cmi.core.session_time to '0000:00:00' as this is a new session
			 * - set cmi.core.credit to 'credit' if not found
			 * - set cmi.core.lesson_status to 'not attempted' if not found
			 * - set cmi.core.entry to 'ab-initio' if not found
			 * - set cmi.launch_data to '' (empty string)
			 */

			if ($apiversion == '1.2') {
				$coreStr = 'core.';
				$setIfNotFound = array (
						'cmi.core.total_time' => SCORM_ZERO_TIME,
						'cmi.core.credit' => 'credit',
						'cmi.core.lesson_status' => 'not attempted',
						'cmi.core.entry' => 'ab-initio',
						'cmi.launch_data' => (strlen($datafromlms)>0 ? $datafromlms : '')
				);
			} else if ($apiversion == '2004') {
				$coreStr = '';
				$setIfNotFound = array (
						'cmi.total_time' => SCORM2004_ZERO_TIME,
						'cmi.credit' => 'credit',
						'cmi.completion_status' => 'not attempted',
						'cmi.entry' => 'ab_initio',
						'cmi.launch_data' => (strlen($datafromlms)>0 ? $datafromlms : '')
				);
			}

			foreach ($setIfNotFound as $setVarname=>$setVarvalue) {
				$oldValue = $GLOBALS['dh']->scormGetValue(
								array ('id_utente' => $_SESSION['sess_userObj']->getId(),
									   'scoobject' => $scoobject,
									   'scoid' => $scoid,
									   'varname' => $setVarname
								)
							);

				if (AMA_DB::isError($oldValue) || strlen($oldValue)<=0) {
					$message .= "\nsetting ".$setVarname.' to: '.$setVarvalue;
					$GLOBALS['dh']->scormSetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
									'scoobject' => $scoobject,
									'scoid' => $scoid,
									'varname' => $setVarname,
									'varvalue' => $setVarvalue,
									'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
							)
					);
				}
			}

			/**
			 * Always set cmi.core.session_time to SCORM_ZERO_TIME
			 */
			$message .= "\nsetting cmi.".$coreStr."session_time to: ".($apiversion=='1.2' ? SCORM_ZERO_TIME : SCORM2004_ZERO_TIME)."\n";
			$GLOBALS['dh']->scormSetValue(
					array ('id_utente' => $_SESSION['sess_userObj']->getId(),
							'scoobject' => $scoobject,
							'scoid' => $scoid,
							'varname' => 'cmi.'.$coreStr.'session_time',
							'varvalue' => ($apiversion=='1.2' ? SCORM_ZERO_TIME : SCORM2004_ZERO_TIME),
							'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
					)
			);

			$retVal = 'true';
		} else if ($action === 'finish' && isset($apiversion) && in_array($apiversion, $GLOBALS['MODULES_SCORM_SUPPORTED_SCHEMAVERSIONS'])) {
			if ($apiversion == '1.2') {
				$coreStr = 'core.';
			} else $coreStr = '';
			/**
			 * Actions done on finish:
			 *
			 * - read cmi.core.total_time , read the last set cmi.core.session_time
			 *   compute the sum and store it as cmi.core.total_time
             *   In all other cases, cmi.core.entry should be set to '' (an empty string).
             * - compute cmi.core.lesson_status according to:
             *   http://www.vsscorm.net/2009/07/29/step-23-more-about-cmi-core-lesson_status/
			 * - If cmi.core.exit has already been set to 'suspend' when the course exits,
			 *   then cmi.core.entry should be set to 'resume'.
			 */
			$totalTime = $GLOBALS['dh']->scormGetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
								   'scoobject' => $scoobject,
								   'scoid' => $scoid,
								   'varname' => 'cmi.'.$coreStr.'total_time'
							)
					     );
			if (AMA_DB::isError($totalTime) || strlen($totalTime)<=0) $totalTime = ($apiversion=='1.2' ? SCORM_ZERO_TIME : SCORM2004_ZERO_TIME);

			$sessionTime = $GLOBALS['dh']->scormGetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
								   'scoobject' => $scoobject,
								   'scoid' => $scoid,
								   'varname' => 'cmi.'.$coreStr.'session_time'
							)
						 );
			if (AMA_DB::isError($sessionTime) || strlen($sessionTime)<=0) $sessionTime = ($apiversion=='1.2' ? SCORM_ZERO_TIME : SCORM2004_ZERO_TIME);

			if ($apiversion == '1.2') {
				// convert total time to seconds
				$time = explode(':',$totalTime);
				$totalSeconds = $time[0]*60*60 + $time[1]*60 + $time[2];

				// convert session time to seconds
				$time = explode(':',$sessionTime);
				$sessionSeconds = $time[0]*60*60 + $time[1]*60 + $time[2];

				$formatStr = "%04d:%02d:%02d";

			} else if ($apiversion == '2004') {
				$formatStr = "PT%dH%dM%dS";

				// convert total time to seconds
				list ($hour, $min, $sec) = sscanf($totalTime, $formatStr);
				$totalSeconds = $hour*60*60 + $min*60 + $sec;

				// convert session time to seconds
				list ($hour, $min, $sec) = sscanf($sessionTime, $formatStr);
				$sessionSeconds = $hour*60*60 + $min*60 + $sec;
			}

			$totalSeconds += $sessionSeconds;

			// break total time into hours, minutes and seconds
			$totalHours = intval($totalSeconds/3600);
			$totalSeconds -= $totalHours * 3600;
			$totalMinutes = intval($totalSeconds/60);
			$totalSeconds -= $totalMinutes * 60;
			// reformat to comply with the SCORM data model
			$totalTime = sprintf($formatStr,$totalHours,$totalMinutes,$totalSeconds);

			$message .= ' - setting cmi.'.$coreStr.'total_time to: '.$totalTime;

			// save totalTime
			$GLOBALS['dh']->scormSetValue(
					array ('id_utente' => $_SESSION['sess_userObj']->getId(),
							'scoobject' => $scoobject,
							'scoid' => $scoid,
							'varname' => 'cmi.'.$coreStr.'total_time',
							'varvalue' => $totalTime,
							'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
					)
			);

			// set cmi.core.lesson_status
			$statusVar = ($apiversion == '1.2' ? 'cmi.core.lesson_status': 'cmi.completion_status' );
			$lessonStatus = $GLOBALS['dh']->scormGetValue(
								array ('id_utente' => $_SESSION['sess_userObj']->getId(),
								   	   'scoobject' => $scoobject,
								       'scoid' => $scoid,
								       'varname' => $statusVar
								)
						 	);

			if (AMA_DB::isError($lessonStatus) || strlen($lessonStatus)<=0) $lessonStatus = 'not attempted';

			if ($lessonStatus == 'not attempted') {
				$newStatus = 'completed';
			} else if (isset($masteryscore) && is_numeric($masteryscore)) {

				$masteryscore = floatval($masteryscore);
				// read student score
				$studentScore = $GLOBALS['dh']->scormGetValue(
									array ('id_utente' => $_SESSION['sess_userObj']->getId(),
										   'scoobject' => $scoobject,
										   'scoid' => $scoid,
										   'varname' => 'cmi.core.score.raw'
							  		)
								);
				if (AMA_DB::isError($studentScore) || strlen($studentScore)<=0) $studentScore=0;
				else $studentScore = floatval($studentScore);

				// compare mastery and student and set status accordingly
				$newStatus = ($studentScore>=$masteryscore) ? 'passed' : 'failed';
			}

			$message .= ' - setting '.$statusVar.' to: '.$newStatus;
			// save entry
			$GLOBALS['dh']->scormSetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
								'scoobject' => $scoobject,
								'scoid' => $scoid,
								'varname' => $statusVar,
								'varvalue' => $newStatus,
								'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
							)
			);

			// get cmi.core.exit
			$exitVal = $GLOBALS['dh']->scormGetValue(
							array ('id_utente' => $_SESSION['sess_userObj']->getId(),
							   	   'scoobject' => $scoobject,
							   	   'scoid' => $scoid,
							   	   'varname' => 'cmi.'.$coreStr.'exit'
							)
					   );
			if (!AMA_DB::isError($exitVal) && $exitVal === 'suspend') {
				$entryVal = 'resume';
			} else $entryVal = '';

			$message .= ' - setting cmi.'.$coreStr.'entry to: '.$entryVal;
			// save entry
			$GLOBALS['dh']->scormSetValue(
					array ('id_utente' => $_SESSION['sess_userObj']->getId(),
							'scoobject' => $scoobject,
							'scoid' => $scoid,
							'varname' => 'cmi.'.$coreStr.'entry',
							'varvalue' => $entryVal,
							'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
					)
			);

			$retVal = 'true';
		}
	} else if ($_SERVER['REQUEST_METHOD']==='POST' && $action==='setValue') {

		$result = $GLOBALS['dh']->scormSetValue(
				array ('id_utente' => $_SESSION['sess_userObj']->getId(),
						'scoobject' => $scoobject,
						'scoid' => $scoid,
						'varname' => $varname,
						'varvalue' => $varvalue,
						'timestamp' => (isset($ts) && strlen($ts)>0 ? $ts : $GLOBALS['dh']->date_to_ts('now'))
				)
		);

		if (!AMA_DB::isError($result)) $retVal = 'true';
		else {
			$retVal = 'false';
			$message .= "\n#### scormSetValue ERROR ####\n".print_r($result, true);
		}
	}

	$message .= ' - returning '.print_r($retVal,true);
	// log the message to the logfile
	$scohelper->logMessage($message);
}

header("Content-Type: text/plain");
echo $retVal;
?>
