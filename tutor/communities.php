<?php
/**
 * communities.php
 *
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 * This is generated from ADA Eclipse Developer Plugin, pls check if file path is ok!
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * This is generated from ADA Eclipse Developer Plugin, use it as an example!
 */
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 * This is generated from ADA Eclipse Developer Plugin, use it as an example!
 */
$allowedUsersAr = array (AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array (
		AMA_TYPE_TUTOR => array('layout')
	);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';
include_once 'include/tutor_functions.inc.php';
include_once 'include/tutor.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
TutorHelper::init($neededObjAr);

/**
 * Your inclusion here
 */
$self = 'tutor';
$data = '';

$courseInstances = array();

/**
 * change the two below call to active to let the closed
 * instances completely disappear from the HTML table
 *
 * NOTE: below method call refers to student but work ok for tutor as well
*/

$provider_dh = $GLOBALS['dh'];
//     $courseInstances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
$courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId(), true);

$data = print_r($courseInstances, true);

if(!AMA_DataHandler::isError($courseInstances) && is_array($courseInstances) && count($courseInstances)>0) {
	$thead_dataAr = array(
			translateFN('Titolo'),
			translateFN('Iniziato'),
			translateFN('Data inizio'),
			translateFN('Durata'),
			translateFN('Data fine'),
			translateFN('Azioni')
	);

	$tbody_dataAr = array();
	foreach($courseInstances as $c) {
		$courseId = $c['id_corso'];
		$nodeId = $courseId . '_0';
		$courseInstanceId = $c['id_istanza_corso'];
		$subscription_status = $c['status'];


		$started = ($c['data_inizio'] > 0 && $c['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
		$start_date = ($c['data_inizio'] > 0) ? $c['data_inizio'] : $c['data_inizio_previsto'];

		if (isset($c['data_iscrizione']) && intval($c['data_iscrizione'])>0) $start_date = intval($c['data_iscrizione']);

		$isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
		$isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
		$access_link = BaseHtmlLib::link("#",
				translateFN('Attendi apertura corso'));

		if (!$isEnded && isset($c['duration_subscription']) && intval($c['duration_subscription'])>0) {
			$duration = $c['duration_subscription'];
			$end_date = $common_dh->add_number_of_days($duration, $start_date);
		} else {
			$duration = $c['durata'];
			$end_date = $c['data_fine'];
		}
		/**
		 * giorgio 13/01/2021: force end_date to have time set to 23:59:59
		 */
		$end_date = strtotime('tomorrow midnight', $end_date) - 1;

		if (!in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_VISITOR, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED))) {
			$access_link = BaseHtmlLib::link("#", translateFN('Abilitazione in corso...'));
		} elseif ($isStarted) {
			/**
			 * @author giorgio 03/apr/2015
			 *
			 * if user is subscribed and the subscription date + subscription_duration
			 * falls after 'now', must set the subscription status to terminated
			 */
			if ($subscription_status == ADA_STATUS_SUBSCRIBED) {
				if (!isset($c['data_iscrizione']) || is_null($c['data_iscrizione'])) {
					$c['data_iscrizione']=time();
				}
				if (!isset($c['duration_subscription']) || is_null($c['duration_subscription'])) $c['duration_subscription']= PHP_INT_MAX;
				$subscritionEndDate = $common_dh->add_number_of_days($c['duration_subscription'], intval($c['data_iscrizione']));
				/**
				 * giorgio 13/01/2021: force subscritionEndDate to have time set to 23:59:59
				 */
				$subscritionEndDate = strtotime('tomorrow midnight', $subscritionEndDate) - 1;
				if ($isEnded || time()>=$subscritionEndDate) {
					if ($userObj instanceof ADAPractitioner) $tmpUserObj = $userObj->toStudent();
					else $tmpUserObj = $userObj;
					$tmpUserObj->setTerminatedStatusForInstance($courseId, $courseInstanceId);
					$subscription_status = ADA_STATUS_TERMINATED;
				}
			}

			$link = CDOMElement::create('a','href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
			if ($isEnded || $subscription_status == ADA_STATUS_TERMINATED || $subscription_status == ADA_STATUS_COMPLETED) {
				$link->addChild(new CText(translateFN('Rivedi i contenuti')));
			} else if ($isStarted && !$isEnded) {
				$link->addChild(new CText(translateFN('Accedi')));
			}
		} else {
			// skip to next iteration if tutor community has not been started by the switcher
			continue;
		}

		$tbody_dataAr[] = array(
				$c['titolo'].' - '.$c['title'], // titolo is course and title is instance
				$started,
				ts2dFN($start_date),
				sprintf(translateFN('%d giorni'), $duration),
				ts2dFN($end_date),
				$link
		);
	}

	$tObj = BaseHtmlLib::tableElement('id:tutorCommunitiesTable', $thead_dataAr, $tbody_dataAr);
	$tObj->setAttribute('class', 'default_table doDataTable '.ADA_SEMANTICUI_TABLECLASS);
	$data = $tObj->getHtml();
} else {
	$data = translateFN('Non sei iscritto a nessuna comunità di tutor');
}


$content_dataAr = array(
    'course_title'=>translateFN('Elenco comunità di tutor'),
    'user_name' => $user_name,
    'user_type' => $user_type,
    'edit_profile'=>$userObj->getEditProfilePage(),
    'messages'=> $user_messages->getHtml(),
    'agenda'=> $user_agenda->getHtml(),
    'help'  => translateFN('Clicca su accedi per entrare in una comunità di tutor'),
    'dati'  => $data,
    'status' => $status,
    'chat_link' => isset($chat_link) ? $chat_link : ''
 );

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS,
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		ROOT_DIR.'/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
		JQUERY_NO_CONFLICT
);

/**
 * add the following line if there's a corresponding js file and
 * some JavaScript initialization is needed
 * $optionsAr ['onload_func'] = 'initDoc();';
 */
$optionsAr ['onload_func'] = 'initDoc();';
/**
 * Sends data to the rendering engine
 */

ARE::render ( $layout_dataAr, $content_dataAr, NULL, (isset ( $optionsAr ) ? $optionsAr : NULL) );
