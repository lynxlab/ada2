<?php

/**
 * USER.
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'default_tester')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require_once 'include/browsing_functions.inc.php';

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
BrowsingHelper::init($neededObjAr);

$courseInstances = array();
$serviceProviders = $userObj->getTesters();
$displayWhatsNew = false;

/**
 * change the two below call to active to let the closed
 * instances completely disappear from the HTML table
 */
if (count($serviceProviders) == 1) {
    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($serviceProviders[0]));
//     $courseInstances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId(), true);
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
//         $courseInstances_provider = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
		$courseInstances_provider = $provider_dh->get_course_instances_for_this_student($userObj->getId(), true);
		if (!is_array($courseInstances_provider)) $courseInstances_provider = array();
        $courseInstances = array_merge($courseInstances, $courseInstances_provider);
    }
}
if(!AMA_DataHandler::isError($courseInstances)) {
	/**
	 * @author giorgio 23/apr/2015
	 *
	 *  filter course instance that are associated to a level of service having:
	 *  - nonzero value in isPublic, so that all instances of public courses will not be shown here
	 *  - zero value in IsPublic and the service level in the $GLOBALS['userHiddenServiceTypes'] array, to hide autosubscription instances
	 */
	if (!is_array($courseInstances)) $courseInstances = array();
	$courseInstances = array_filter($courseInstances, function($courseInstance) {
		if (is_null($courseInstance['tipo_servizio'])) $courseInstance['tipo_servizio'] = DEFAULT_SERVICE_TYPE;
		$actualServiceType = !is_null($courseInstance['istanza_tipo_servizio']) ? $courseInstance['istanza_tipo_servizio']: $courseInstance['tipo_servizio'];
		if (intval($_SESSION['service_level_info'][$actualServiceType]['isPublic'])!==0) {
			$filter = false;
		} else if (in_array($actualServiceType, $GLOBALS['userHiddenServiceTypes'])) {
			$filter = false;
		}
		return (isset($filter) ? $filter : true);
	});

	/**
	 * @author giorgio 22/feb/2016
	 *
	 * if an id_course and id_course_instance are passed in $_GET, filter the found
	 * course instances so that at the end courseInstances array should have one element
	 * and the proper page is shown to the logged user (as if she was subscribed to one course only)
	 */
	if (!isset($_GET['id_node']) && isset($_GET['id_course']) && isset($_GET['id_course_instance'])) {
		$courseInstances = array_filter($courseInstances, function($courseInstance) {
			return (($courseInstance['id_corso'] == $_GET['id_course']) &&
					($courseInstance['id_istanza_corso'] == $_GET['id_course_instance']));
		});
	}

    $found = count($courseInstances);
    $thead_dataAr = array(
           translateFN('Titolo'),
           translateFN('Iniziato'),
           translateFN('Data inizio'),
           translateFN('Durata'),
           translateFN('Data fine'),
           translateFN('Azioni')
        );

/**
 * @author giorgio 24/apr/2013
 *
 * if the 3 $_GET params are all set, display the (kind of) "what's new" page
 */

    $get_id_node            = (isset($_GET['id_node'])) ? $_GET['id_node'] : '';
    $get_id_course          = (isset($_GET['id_course'])) ? $_GET['id_course'] : '';
    $get_id_course_instance = (isset($_GET['id_course_instance'])) ? $_GET['id_course_instance'] : '';

    if ( $get_id_node!=='' && $get_id_course!=='' && $get_id_course_instance!=='')
    {
    	$courseId = $get_id_course;
    	$courseInstanceId = $get_id_course_instance;
    	$nodeId = $get_id_node;

    	$displayWhatsNew = true;
    	// show (kind of) what's new page: user page with user.tpl template
    }
    else { // resume 'normal' operation: user page using default template
	    if( $found > 1) {

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

				// check service completeness
				$provider = $common_dh->get_tester_info_from_id_course($courseId);
				if (array_key_exists('puntatore', $provider)) {
					$_SESSION['sess_selected_tester'] = $provider['puntatore'];
					BrowsingHelper::checkServiceComplete($userObj, $courseId, $courseInstanceId);
					BrowsingHelper::checkRewardedBadges($userObj, $courseId, $courseInstanceId);
					unset($_SESSION['sess_selected_tester']);
				}
				unset($provider);

	            /*
	            if ($isStarted && !$isEnded) {
	                $access_link = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",
	                        translateFN('Accedi'));
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }
	             *
	             */
	            if (!in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_VISITOR, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED))) {
	               $access_link = BaseHtmlLib::link("#", translateFN('Abilitazione in corso...'));
	            } elseif ($isStarted) {
	            	/**
	            	 * @author giorgio 03/apr/2015
	            	 *
	            	 * if user is subscribed or completed and the subscription date + subscription_duration
	            	 * falls after 'now', must set the subscription status to terminated
	            	 */
	            	if (in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_COMPLETED))) {
	            		if (!isset($c['data_iscrizione']) || is_null($c['data_iscrizione']) || intval($c['data_iscrizione'])===0) {
	            			$c['data_iscrizione']=time();
	            		}
	            		if (!isset($c['duration_subscription']) || is_null($c['duration_subscription'])) $c['duration_subscription']= PHP_INT_MAX;
	            		$subscritionEndDate = $common_dh->add_number_of_days($c['duration_subscription'], intval($c['data_iscrizione']));
						/**
			 			 * giorgio 13/01/2021: force subscritionEndDate to have time set to 23:59:59
			 			 */
						$subscritionEndDate = strtotime('tomorrow midnight', $subscritionEndDate) - 1;
	            		if ($isEnded || time()>=$subscritionEndDate) {
	            			$userObj->setTerminatedStatusForInstance($courseId, $courseInstanceId);
	            			$subscription_status = ADA_STATUS_TERMINATED;
	            		}
	            	}

					$access_link = CDOMElement::create('div');
					$link = CDOMElement::create('a');
					$linkParams = array(
							'id_course'=>$courseId,
							'id_course_instance'=>$courseInstanceId
					);
					if ($isEnded || $subscription_status == ADA_STATUS_TERMINATED || $subscription_status == ADA_STATUS_COMPLETED) {
						$link->addChild(new CText(translateFN('Rivedi il corso')));
						$linkScript = 'view.php';
						$linkParams['id_node'] = $nodeId;
					} else if ($isStarted && !$isEnded) {
						$linkScript = 'user.php';
						$link->addChild(new CText(translateFN('Accedi')));
					}
					$link->setAttribute('href', $linkScript.'?'.http_build_query($linkParams));
					$access_link->addChild($link);

					// @author giorgio 24/apr/2013
					// adds whats new link if needed
					if (!$isEnded && ($subscription_status != ADA_STATUS_TERMINATED || $subscription_status != ADA_STATUS_COMPLETED) && MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId)) {
						$link = CDOMElement::create('a','href:user.php?id_node='.$nodeId.
																	 '&id_course='.$courseId.
																	 '&id_course_instance='.$courseInstanceId);
						$link->setAttribute("class", "whatsnewlink");
						$link->addChild(new CText(translateFN('Novit&agrave;')));
						$access_link->addChild($link);
					}
	            }

	            $tbody_dataAr[] = array(
	                $c['titolo'],
	                $started,
	                ts2dFN($start_date),
	                sprintf(translateFN('%d giorni'), $duration),
	                ts2dFN($end_date),
	                $access_link
	            );
	        }

	        $data = BaseHtmlLib::tableElement('class:doDataTable '.ADA_SEMANTICUI_TABLECLASS, $thead_dataAr, $tbody_dataAr);
	    } elseif ($found == 1) {

	    	/**
	    	 * @author giorgio 12/feb/2016
	    	 *
	    	 * always display whatsnew page if user is subscribed to one instance only
	    	 */
	    	$displayWhatsNew = true;

	        $c = reset($courseInstances);
	        $currentTimestamp = time();

	        $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	        $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	        $courseId = $c['id_corso'];
	        $nodeId = $courseId . '_0';
	        $courseInstanceId = $c['id_istanza_corso'];
	        $subscription_status = $c['status'];
	        // automatic entering the course
	        // redirect to the first node of the ONLY instance to which the student is subscribed
	        // only the first time (coming from login page)
	        // TODO: we should use some kind of constant to change this behavoiur for specific installation, provider, service, courses, instances, ....


	        $navigationHistoryObj = $_SESSION['sess_navigation_history'];
	        if(ADA_USER_AUTOMATIC_ENTER && $navigationHistoryObj->userComesFromLoginPage() && $isStarted && !$isEnded
	                && !in_array($subscription_status,array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_VISITOR, ADA_STATUS_TERMINATED))) {
	            header("Location: view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId");
	            exit();
	        }
	        else {
	            $started = ($c['data_inizio'] > 0 && $c['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
	            $start_date = ($c['data_inizio'] > 0) ? $c['data_inizio'] : $c['data_inizio_previsto'];

	            if (isset($c['data_iscrizione']) && intval($c['data_iscrizione'])>0) $start_date = intval($c['data_iscrizione']);
	            if (isset($c['duration_subscription']) && intval($c['duration_subscription'])>0) {
	            	$duration = $c['duration_subscription'];
	            	$end_date = $common_dh->add_number_of_days($duration, $start_date);
	            } else {
	            	$duration = $c['duration_subscription'];
	            	$end_date = $c['data_fine'];
	            }
				/**
				 * giorgio 13/01/2021: force end_date to have time set to 23:59:59
         		 */
				$end_date = strtotime('tomorrow midnight', $end_date) - 1;

				// check service completeness
				$provider = $common_dh->get_tester_info_from_id_course($courseId);
				if (array_key_exists('puntatore', $provider)) {
					$_SESSION['sess_selected_tester'] = $provider['puntatore'];
					BrowsingHelper::checkServiceComplete($userObj, $courseId, $courseInstanceId);
					BrowsingHelper::checkRewardedBadges($userObj, $courseId, $courseInstanceId);
					unset($_SESSION['sess_selected_tester']);
				}
				unset($provider);

	            $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	            $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	            $access_link = BaseHtmlLib::link("#", translateFN('Attendi apertura corso'));
	            /*
	            if ($isStarted && !$isEnded) {
	                $access_link = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",
	                        translateFN('Accedi'));
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }
	             *
	             */
	            if (!in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_VISITOR, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED))) {
	               $access_link = BaseHtmlLib::link("#", translateFN('Abilitazione in corso...'));
	            } else if ($isStarted) {
	            	/**
	            	 * @author giorgio 03/apr/2015
	            	 *
	            	 * if user is subscribed or completed and the subscription date + subscription_duration
	            	 * falls after 'now', must set the subscription status to terminated
	            	 */
	            	if (in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_COMPLETED))) {
	            		if (!isset($c['data_iscrizione']) || is_null($c['data_iscrizione']) || intval($c['data_iscrizione'])===0) $c['data_iscrizione']=time();
	            		if (!isset($c['duration_subscription']) || is_null($c['duration_subscription'])) $c['duration_subscription']= PHP_INT_MAX;
	            		$subscritionEndDate = $common_dh->add_number_of_days($c['duration_subscription'], intval($c['data_iscrizione']));
						/**
			 			 * giorgio 13/01/2021: force subscritionEndDate to have time set to 23:59:59
			 			 */
						$subscritionEndDate = strtotime('tomorrow midnight', $subscritionEndDate) - 1;
	            		if ($isEnded || time()>=$subscritionEndDate) {
	            			$userObj->setTerminatedStatusForInstance($courseId, $courseInstanceId);
	            			$subscription_status = ADA_STATUS_TERMINATED;
	            		}
	            	}

	            	/*
	            	 * @author giorgio 24/apr/2013
	            	 *
	            	 * one course found, with something new to display
	            	 * set displayWhatsNew to true, the renderer will render the appropriate page
	            	 *
	            	 * NOTE: user.php with appropriate parameters is a kind of "whats new" page
	            	 *
	            	 */
					 if (!$isEnded && $subscription_status != ADA_STATUS_TERMINATED && MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId)) {
					 	$displayWhatsNew = true;
					 }
					 else {
					 	// resume 'normal' behaviour
					 	$access_link = CDOMElement::create('div');
					 	$link = CDOMElement::create('a','href:view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
					 	if ($isEnded || $subscription_status == ADA_STATUS_TERMINATED || $subscription_status == ADA_STATUS_COMPLETED) {
					 		$link->addChild(new CText(translateFN('Rivedi il corso')));
					 	} else if ($isStarted && !$isEnded) {
					 		$link->addChild(new CText(translateFN('Accedi')));
					 	}
					 	$access_link->addChild($link);
					 }
	            }

	            $tbody_dataAr[] = array(
	                $c['titolo'],
	                $started,
	                ts2dFN($start_date),
	                sprintf(translateFN('%d giorni'), $duration),
	                ts2dFN($end_date),
	                $access_link
	            );
	            $data = BaseHtmlLib::tableElement('class:doDataTable '.ADA_SEMANTICUI_TABLECLASS, $thead_dataAr, $tbody_dataAr);
	        }
	    } else {
	    	$data = CDOMElement::create('div','class:ui info icon large message');
	    	$data->addChild(CDOMElement::create('i','class:book icon'));
	    	$MSGcontent = CDOMElement::create('div','class:content');
	    	$MSGheader = CDOMElement::create('div','class:header');
	    	$MSGtext = CDOMElement::create('span','class:message');

	    	$data->addChild($MSGcontent);
	    	$MSGcontent->addChild($MSGheader);
	    	$MSGcontent->addChild($MSGtext);

	    	$MSGheader->addChild(new CText(translateFN('Non sei iscritto a nessun corso')));
	    	$MSGtext->addChild(BaseHtmlLib::link(HTTP_ROOT_DIR . '/info.php', translateFN('Clicca qui')));
	    	$MSGtext->addChild (new CText(' '.translateFN('per vedere l\'elenco dei corsi a cui puoi iscriverti')));
	    }
	    // @author giorgio 24/apr/2013
	    // end else... line
	}
} else {
    $data = new CText('');
}
$last_access=$userObj->get_last_accessFN(null,"UT",null);
$last_access=AMA_DataHandler::ts_to_date($last_access);

if($last_access=='' || is_null($last_access))
{
    $last_access='-';
}
/*
 * Output
 */
if (!$displayWhatsNew)
{
	// set default template
	$self = 'default';
	$content_dataAr = array(
	    'banner' => isset($banner) ? $banner : '',
	    'today' => isset($ymdhms) ? $ymdhms : '',
	    'user_name' => $user_name,
        'user_level'=>translateFN("Nd"),
        'status'=>$status,
	    'user_type' => $user_type,
	    'last_visit' => $last_access,
	    'message' => isset($message) ? $message : '',
        'help'=>isset($help) ? $help : '',
	//    'iscritto' => $sub_course_data,
	//    'iscrivibili' => $to_sub_course_data,
	    'course_title' => translateFN("Home dell'utente"),
	//    'corsi' => $corsi,
	//    'profilo' => $profilo,
	    'data' => $data->getHtml(),
        'edit_profile'=>$userObj->getEditProfilePage(),
	    'messages' => $user_messages->getHtml(),
	    'agenda' => $user_agenda->getHtml(),
	    'events' => $user_events->getHtml(),
	    'status' => $status
	);
}
else {
	// will use user.tpl template here

	// look for passed course in courseInstances array

    if($found>1){
	for ($i=0; $i<count($courseInstances); $i++)
	{
	// break out from the loop if id_corso is found, and in $i
	// we have the array index of the found course
	if ($courseInstances[$i]['id_corso'] == $get_id_course) break;
	}

	$c = $courseInstances[$i];
    }
    else{
        $c = reset($courseInstances);
    }

	$currentTimestamp = time();

	$isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	$isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	$self_instruction = isset($c['self_instruction']) ? $c['self_instruction'] : 0;
	$subscription_status = $c['status'];

	// @author giorgio 24/apr/2013 students link
	$class_label = translateFN("Classe");
	// $students = "<a href='class_info.php?op=students_list&id_course_instance=$courseInstanceId&id_course=$courseId'>$class_label</a>";
	$students =  BaseHtmlLib::link("class_info.php?op=students_list&id_course_instance=$courseInstanceId&id_course=$courseId'",$class_label);
	$students_link = $students->getHtml();
	// @author giorgio
    // TODO: class_info.php non esiste, va creato o si toglie questo link?
    // unset ($students_link);

    // @author giorgio 26/apr/2013 new nodes
	$provider = $common_dh->get_tester_info_from_id_course($courseId);
	$providerId = $provider['id_tester'];


    $whatsnew = $userObj->getwhatsnew();
    $new_nodes = $whatsnew[$provider['puntatore']];

    $new_nodes_html = '';
    //display a link to node if there are new nodes
    if (count($new_nodes) > 0) {
    	$olelem = CDOMElement::create('ol');

    	foreach ($new_nodes as $node)
    	{
    		if (strpos($node['id_nodo'],$courseId)!==false)
    		{
	    		$lielem = CDOMElement::create('li');
	    		$link = CDOMElement::create('a', 'href:view.php?id_node='.$node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
	    		$link->addChild(new CText($node['nome']));
	    		$lielem->addChild ($link);
	    		$olelem->addChild($lielem);
	    		unset ($lielem);
	    		unset($link);
    		}
    	}
    	$new_nodes_html = $olelem->getHtml();
    }



	// @author giorgio 24/apr/2013 forum messages (NOTES!!!!! BE WARNED: THESE ARE NOTES!!!)
	$msg_forum_count = MultiPort::count_new_notes($userObj,$courseInstanceId);

	//display a direct link to forum if there are new messages
	if ($msg_forum_count > 0) {
		$link = CDOMElement::create('a', 'href:main_index.php?op=forum&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
		$link->addChild(new CText($msg_forum_count));
		$msg_forum_count = $link->getHtml();
		unset($link);
	}

	// @author giorgio 24/apr/2013 private messages
	$msg_simple_count = 0;
	$msg_simpleAr =  MultiPort::getUserMessages($userObj);
	foreach ($msg_simpleAr as $msg_simple_provider) {
		$msg_simple_count += count($msg_simple_provider);
	}

	// @author giorgio 24/apr/2013 agenda messages
	$msg_agenda_count = 0;
	$msg_agendaAr = MultiPort::getUserAgenda($userObj);
	foreach ($msg_agendaAr as $msg_agenda_provider) {
		$msg_agenda_count += count($msg_agenda_provider);
	}

	// @author giorgio 24/apr/2013 gocontinue link
	$last_visited_node_id = $userObj->get_last_accessFN($courseInstanceId,"N",AMA_DataHandler::instance(MultiPort::getDSN($provider['puntatore'])));
	if  ((!empty($last_visited_node_id)) AND (!is_object($last_visited_node_id))&& $isStarted && !$isEnded){
		$last_node_visitedObj = BaseHtmlLib::link("view.php?id_course=$courseId&id_node=$last_visited_node_id&id_course_instance=$courseInstanceId",translateFN("Continua"));
		// echo "<!--"; var_dump($last_node_visitedObj);echo "-->";
		$last_node_visited_link =  $last_node_visitedObj->getHtml();

	} else {
		//$last_node_visitedObj = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Continua'));
		$last_node_visitedObj = CDOMElement::create('span','class:disabled');
		$last_node_visitedObj->addChild(new CText(translateFN('Continua')));
		$last_node_visited_link = $last_node_visitedObj->getHtml();
	}

	// @author giorgio 24/apr/2013 gostart, goindex, goforum and gocontinue link
	// va sostituita con una select in AMA

	//	    Graphical disposition:

	$gostart_link = translateFN('Il corso non è ancora iniziato');
	if (!in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_VISITOR, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED))) {
		$gostart = BaseHtmlLib::link("#",
				translateFN('Abilitazione in corso...'));
		$gostart_link = $gostart->getHtml();
		$last_node_visited_link = '';

	} elseif (!$isStarted) {
		$goindex  = CDOMElement::create('span','class:disabled');
		$goindex->addChild(new CText(translateFN('Indice')));
		$goindex_link = $goindex->getHtml();
		$goforum   = CDOMElement::create('span','class:disabled');
		$goforum->addChild(new CText(translateFN('Forum')));
		$goforum_link = $goforum->getHtml();
		$gohistory = CDOMElement::create('span','class:disabled');
		$gohistory->addChild(new CText(translateFN('Cronologia')));
	} elseif ($isStarted && !$isEnded) {
		/**
		 * @author giorgio 03/apr/2015
		 *
		 * if user is subscribed or completed and the subscription date + subscription_duration
		 * falls after 'now', must set the subscription status to terminated
		 */
		if (in_array($subscription_status, array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_COMPLETED))) {
			if (!isset($c['data_iscrizione']) || is_null($c['data_iscrizione']) || intval($c['data_iscrizione'])===0) $c['data_iscrizione']=time();
			if (!isset($c['duration_subscription']) || is_null($c['duration_subscription'])) $c['duration_subscription']= PHP_INT_MAX;
			$subscritionEndDate = $common_dh->add_number_of_days($c['duration_subscription'], intval($c['data_iscrizione']));
			/**
			 * giorgio 13/01/2021: force end_date to have time set to 23:59:59
			 */
			$subscritionEndDate = strtotime('tomorrow midnight', $subscritionEndDate) - 1;
			if ($isEnded || time()>=$subscritionEndDate) {
				$userObj->setTerminatedStatusForInstance($courseId, $courseInstanceId);
				$subscription_status = ADA_STATUS_TERMINATED;
			}
		}

		if ($isEnded || $subscription_status == ADA_STATUS_TERMINATED || $subscription_status == ADA_STATUS_COMPLETED) {
			$startLabel = translateFN('Rivedi il corso');
		} else if ($isStarted && !$isEnded) {
			$startLabel = translateFN('Inizia');
		}

		$gostart = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",$startLabel);
		$gostart_link = $gostart->getHtml();
		$goindex  = BaseHtmlLib::link("main_index.php?id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Indice'));
		$goindex_link = $goindex->getHtml();
		$goforum   = BaseHtmlLib::link("main_index.php?id_course=$courseId&id_course_instance=$courseInstanceId&op=forum",translateFN('Forum'));
		$goforum_link = $goforum->getHtml();
		$gohistory = BaseHtmlLib::link('history.php?id_course='.$courseId.'&id_course_instance='.$courseInstanceId, translateFN('Cronologia'));


		$enddateForTemplate = AMA_DataHandler::ts_to_date(min($c['data_fine'], $subscritionEndDate));

		if ($self_instruction) {
			$self = 'userSelfInstruction';
			if (($subscription_stopUT+AMA_SECONDS_IN_A_DAY) < time()) {
// 				$gostart = BaseHtmlLib::link("#", translateFN('Corso terminato...'));
// 				$gostart_link = $gostart->getHtml();
// 				$last_node_visited_link = '';
// 				$goindex_link = '';
			}
		}
	}

	// must set the DH to the course provider one
	$GLOBALS['dh'] = AMA_DataHandler::instance(MultiPort::getDSN($provider['puntatore']));

	/**
	 * @author giorgio 22/feb/2016
	 * get course description
	 */
	$cd_res = $GLOBALS['dh']->find_courses_list(array('descrizione'),'id_corso='.$courseId);
	if (!AMA_DB::isError($cd_res) && is_array($cd_res) && count($cd_res)>0) {
		$cd_el = reset($cd_res);
		$course_description = $cd_el['descrizione'];
	}

	require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
	$gochat_link = "";
   	$content_dataAr['edit_profile'] = $userObj->getEditProfilePage();
	$content_dataAr['gostart'] = $gostart_link;
	$content_dataAr['gocontinue'] = $last_node_visited_link;
	$content_dataAr['goindex'] = isset($goindex_link) ? $goindex_link: null;
	if ($new_nodes_html!=='') $content_dataAr['new_nodes_links'] = $new_nodes_html;
	// msg forum sono le note in realta'
	$content_dataAr['msg_forum'] = $msg_forum_count;
	$content_dataAr['msg_agenda'] =  $msg_agenda_count;
	$content_dataAr['msg'] = $msg_simple_count;
	$content_dataAr['goclasse'] = $students_link;
	$content_dataAr['goforum'] = isset($goforum_link) ? $goforum_link : null;
	$content_dataAr['gochat'] = isset($gochat_link) ? $gochat_link : null;

	$content_dataAr['banner'] = isset($banner) ? $banner : null;
	$content_dataAr['today'] = $ymdhms;
	$content_dataAr['user_name'] = $user_name;
	$content_dataAr['user_type'] = $user_type;
	//$content_dataAr['last_visit'] = $userObj->get_last_accessFN();
    $content_dataAr['last_visit'] = $last_access;
	$content_dataAr['message'] = isset($message) ? $message : null;
	$content_dataAr['course_title'] = $c['titolo'].' - '.$c['title'];
	$content_dataAr['status'] = $status;
	$content_dataAr['course_description'] = isset($course_description) ? $course_description: null;
	$content_dataAr['enddate'] = isset($enddateForTemplate) ? $enddateForTemplate : '-';
	$content_dataAr['gohistory'] = isset($gohistory) ? $gohistory->getHtml() : null;
	$content_dataAr['subscription_status'] = Subscription::subscriptionStatusArray()[$subscription_status];
	$content_dataAr['messages'] = $user_messages->getHtml();
	$content_dataAr['agenda'] = $user_agenda->getHtml();

	$layout_dataAr['widgets']['instanceReminder'] = [
		'isActive' => (defined('MODULES_CLASSAGENDA') && MODULES_CLASSAGENDA) ? 1 : 0,
	];

	if (defined('MODULES_SERVICECOMPLETE') && MODULES_SERVICECOMPLETE) {
		// need the service-complete module data handler
		require_once MODULES_SERVICECOMPLETE_PATH . '/include/init.inc.php';
		$mydh = AMACompleteDataHandler::instance(MultiPort::getDSN($provider['puntatore']));
		// load the conditionset for this course
		$conditionSet = $mydh->get_linked_conditionset_for_course($courseId);
		$mydh->disconnect();

		if ($conditionSet instanceof CompleteConditionSet) {
			$_SESSION['sess_selected_tester'] = $provider['puntatore'];
			// evaluate the conditionset for this instance ID and course ID
			$summary = $conditionSet->buildSummary(array($courseInstanceId, $userObj->getId()));
			unset($_SESSION['sess_selected_tester']);
			if (is_array($summary) && count($summary)>0) {
				$content_dataAr['completeSummary'] = '';
				foreach($summary as $condition=>$condData) {
					$content_dataAr['completeSummary'] .= $condition::getCDOMSummary($condData)->getHtml();
				}
			}
		}
	}

	if (defined('MODULES_BADGES') && MODULES_BADGES) {
		// need the badges module data handler
		$bdh = \Lynxlab\ADA\Module\Badges\AMABadgesDataHandler::instance(MultiPort::getDSN($provider['puntatore']));
		// load all the badges for this course
		$courseBadges = $bdh->findBy('CourseBadge', ['id_corso' => $courseId] );
		if (!AMA_DB::isError($courseBadges) && is_array($courseBadges) && count($courseBadges)>0) {
			$badgesLink = CDOMElement::create('div','class:item');
			$badgesLink->addChild(CDOMElement::create('i','class:certificate icon'));
			$badgesLink->setAttribute('data-dataurl',MODULES_BADGES_HTTP .'/ajax/getUserBadges.php?courseInstanceId='.$courseInstanceId);
			$badgesLink->setAttribute('data-jsurl',  MODULES_BADGES_HTTP .'/js/badgesToHTML.js');
			$popupLink = CDOMElement::create('a','id:bagesPopupLink,href:javascript:void(0);');
			$popupLink->addChild(new CText(translateFN('Badges')));
			$badgesLink->addChild($popupLink);
			$content_dataAr['badgesLink'] = $badgesLink->getHtml();
		}
	}

	if ($layout_dataAr['widgets']['instanceReminder']['isActive']) {
		$layout_dataAr['widgets']['instanceReminder']['id_course'] =  $courseId;
		$layout_dataAr['widgets']['instanceReminder']['id_course_instance'] = $courseInstanceId;
	}

	if (!isset($content_dataAr['completeSummary'])) {
		$userObj->set_course_instance_for_history($courseInstanceId);
		$user_history = $userObj->getHistoryInCourseInstance($courseInstanceId);
		$span = CDOMElement::create('span','class:percent label item');
		$span->addChild(CDOMElement::create('i','class:ok circle icon'));
		$span->addChild(new CText(translateFN('Contenuti visitati').': <strong>'.$user_history->history_nodes_visitedpercent_FN([ADA_GROUP_TYPE, ADA_LEAF_TYPE]).'%</strong>'));
		$content_dataAr['completeSummary'] = $span->getHtml();
	}
	$GLOBALS['dh']->disconnect();
}

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS,
		'user.css' // this file may use different templates, force user.css inclusion here
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
		JQUERY_DATATABLE_DATE,
		ROOT_DIR.'/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
		JQUERY_NO_CONFLICT,
		'user.js' // this file may use different templates, force user.js inclusion here
);

ARE::render($layout_dataAr,$content_dataAr,NULL,array('onload_func'=>'initDoc();'));

