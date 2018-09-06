<?php
/**
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout'),
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout'),
    AMA_TYPE_ADMIN => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';

$op = isset($_GET['op']) ? DataValidator::validate_string($_GET['op']) : false;
$today_date = today_dateFN();

//$self = 'list_chatrooms'; // x template
$self = whoami();

/**
 * Negotiate page language if needed
 */
if (!isset($_SESSION['sess_user_language'])) {
	Translator::loadSupportedLanguagesInSession();
	$_SESSION['sess_user_language'] = Translator::negotiateLoginPageLanguage();
}

if ($op !== false && $op == 'course_info') {
	$self = 'course-info';
    $serviceId = DataValidator::is_uinteger($_GET['id']);

    if ($serviceId !== false && $serviceId > 0) {
    	$coursesAr = $common_dh->get_courses_for_service($serviceId);
   	}

    if (isset($coursesAr) && !AMA_Common_DataHandler::isError($coursesAr) && is_array($coursesAr) && count($coursesAr)>0) {
    	$currentTesterId = 0;
    	$currentTester = '';
    	$tester_dh = null;
    	// This will be used to populate the template fields
    	$courseInfoContent = array();
		$courseInfoContent['firstcol_wideness'] = 'sixteen wide';

    	foreach ($coursesAr as $courseData) {

    		$newTesterId = $courseData['id_tester'];
    		if ($newTesterId != $currentTesterId) {
    			$testerInfoAr = $common_dh->get_tester_info_from_id($newTesterId,AMA_FETCH_ASSOC);
    			if (!AMA_Common_DataHandler::isError($testerInfoAr)) {

    				$layout_dataAr['widgets']['provider_address_map'] = array (
    						'isActive'=>0
    				);

    				$provider_name = $testerInfoAr['nome'];
    				$courseInfoContent['provider_name'] = $testerInfoAr['nome'];

    				if (isset($testerInfoAr['descrizione']) && strlen($testerInfoAr['descrizione'])>0) {
    					$courseInfoContent['provider_description'] = $testerInfoAr['descrizione'];
    				}

    				if (isset($_SESSION['mobile-detect']) && $_SESSION['mobile-detect']->isMobile()) {
    					$courseInfoContent['provider_phone'] = BaseHtmlLib::link('tel:'.$testerInfoAr['telefono'], $testerInfoAr['telefono'])->getHtml();
    				} else {
    					$courseInfoContent['provider_phone'] = $testerInfoAr['telefono'];
    				}


    				if (isset($testerInfoAr['indirizzo']) && strlen($testerInfoAr['indirizzo'])>0) {
    					$provAddress = $testerInfoAr['indirizzo'];
    					if (isset($testerInfoAr['provincia']) && strlen($testerInfoAr['provincia'])>0) {
    						$provAddress .= ' - '.$testerInfoAr['provincia'];
	    					if (isset($testerInfoAr['citta']) && strlen($testerInfoAr['citta'])>0) {
	    						$provAddress .= ' ('.strtoupper($testerInfoAr['citta']).')';
	    					}
    					}

    					$addressLink = BaseHtmlLib::link('https://www.google.com/maps/place/'.urlencode($provAddress), $provAddress);
    					$addressLink->setAttribute('target', '_blank');
    					$courseInfoContent['provider_address'] = $addressLink->getHtml();

    					// configure map widget
    					$layout_dataAr['widgets']['provider_address_map'] = array (
    						'url' => 'https://maps.googleapis.com/maps/api/staticmap?center='.urlencode($provAddress).'&zoom=17&size=338x199&maptype=roadmap'.
	    					'&markers=size:mid%7C'.urlencode($provAddress),
	    					'isActive'=>1
	    				);
    				}

    				if (isset($testerInfoAr['e_mail']) && strlen($testerInfoAr['e_mail'])>0) {
    					$courseInfoContent['provider_email'] = BaseHtmlLib::link('mailto:'.$testerInfoAr['e_mail'], $testerInfoAr['e_mail'])->getHtml();
    				}

    				$tester = $testerInfoAr['puntatore'];
    				$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
    				$currentTesterId = $newTesterId;
    				$courseId = $courseData['id_corso'];
    				$course_dataHa = $tester_dh->get_course($courseId);
    				if (!AMA_DataHandler::isError($course_dataHa)) {
    					// supponiamo che tutti i dati di un servizio (su tester diversi) abbiano lo stesso valore
    					// quindi prendiamo solo l'ultimo
    					$courseInfoContent['course_title'] = $course_dataHa['titolo'];
    					$courseInfoContent['course_description'] = $course_dataHa['descr'];
    					$creditsLbl = 'Credit'.(intval($course_dataHa['crediti'])===1?'o':'i');
    					$courseInfoContent['course_credits'] = intval($course_dataHa['crediti'])>0 ? $course_dataHa['crediti'] .' '.translateFN($creditsLbl) : null;
    					$courseInfoContent['course_language'] = Translator::getLanguageInfoForLanguageId($course_dataHa['id_lingua'])['nome_lingua'];
    					$durationLbl = 'Or'.(intval($course_dataHa['duration_hours'])===1?'a':'e');
    					$courseInfoContent['course_duration'] = intval($course_dataHa['duration_hours'])>0 ? $course_dataHa['duration_hours'].' '.translateFN($durationLbl) : null;
    					// displayMainIndex uses $hide_visits as a global... set it :(
    					$hide_visits = 1; // default: no visits countg
    					$main_index = CourseViewer::displayMainIndex($userObj, $courseId, 1, 'struct', null,'structIndex', $tester_dh);
    					if ($main_index instanceof CBaseElement) {
    						$courseInfoContent['course_index'] = $main_index->getHtml();
    					}
    				} else {
    					$courseInfoContent['course_title'] = translateFN('Il corso');
    				}
    			}
    		} // if($newTesterId != $currentTesterId)

    		// instances loop
    		$courseId = $courseData['id_corso'];
    		$timestamp = time();

    		$instancesAr = $tester_dh->course_instance_subscribeable_get_list(
    				array('data_inizio_previsto', 'durata', 'data_fine', 'title','price','self_instruction','duration_hours','tipo_servizio'),
    				$courseId);

    		$CourseIstanceIscription=$tester_dh->course_users_instance_get($courseId);
    		$id_node=$courseId.'_0';

    		if(!AMA_DB::isError($instancesAr) && is_array($instancesAr) && count($instancesAr) > 0) {
    			foreach($instancesAr as $instance) {
    				$instanceId = $instance[0];
    				$flagSubscribe_link=false;
    				$isEnded = ($instance[3] > 0 && $instance[3] < time()) ? true : false;
    				if ($isEnded) {
    					$subscribe_link = BaseHtmlLib::link("#",'<i class="ban circle icon"></i>'.translateFN('corso terminato'));
    					$subscribe_link->setAttribute('class', 'red ui labeled icon right floated button');
    					$flagSubscribe_link=true;
    				} else {
    					foreach($CourseIstanceIscription  as $courseIstance) {
    						$id_istanza=$courseIstance['id_istanza_corso'];

    						if ($id_istanza == $instanceId) {
    							$id_utente = $courseIstance ['id_utente'];
    							if ($id_utente == $userObj->getId ()) {
    								/**
    								 * Subscribe button
    								 */
    								$statusUr = $courseIstance ['status'];
    								if ($statusUr == ADA_STATUS_SUBSCRIBED || $statusUr == ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) {
    									if ($userObj->tipo == AMA_TYPE_VISITOR) {
    										$subscribe_link = BaseHtmlLib::link ("#",'<i class="checkmark icon"></i>'.translateFN ( 'Già iscritto' ));
    										$subscribe_link->setAttribute('class', 'green ui labeled icon right floated button');
    										$flagSubscribe_link = true;
    									}
    									if ($userObj->tipo == AMA_TYPE_STUDENT) {
    										$subscribe_link = BaseHtmlLib::link ("browsing/view.php?id_node=$id_node&id_course=$courseId&id_course_instance=$id_istanza",
    												'<i class="angle right icon"></i>'.translateFN ('Accedi'));
    										$subscribe_link->setAttribute('class', 'blue ui labeled icon right floated button');
    										$flagSubscribe_link = true;
    									}
    								} else {
    									$subscribe_link = BaseHtmlLib::link ("info.php?op=subscribe&provider=$currentTesterId&course=$courseId&instance=$instanceId",
    											'<i class="signup icon"></i>'.translateFN ('iscriviti'));
    									$subscribe_link->setAttribute('class', 'green ui labeled icon right floated button');
    									$flagSubscribe_link = true;
    								}
    							}
    						} // if ($id_istanza == $instanceId)

    					}

    					if (!$flagSubscribe_link) {
    						$subscribe_link = BaseHtmlLib::link ("info.php?op=subscribe&provider=$currentTesterId&course=$courseId&instance=$instanceId",
    								'<i class="signup icon"></i>'.translateFN ('iscriviti'));
    						$subscribe_link->setAttribute('class', 'green ui labeled icon right floated button');
    					}
    				} // else of if ($isEnded)

    				/*
    				 * Da migliorare, spostare l'ottenimento dei dati necessari in un'unica query
    				 * per ogni istanza corso (qualcosa che vada a sostituire course_instance_get_list solo in questo caso.
    				 */
    				$tutorId = $tester_dh->course_instance_tutor_get($instanceId);
    				if(!AMA_DataHandler::isError($tutorId) && $tutorId !== false) {
    					$tutor_infoAr = $tester_dh->get_tutor($tutorId);
    					if(!AMA_DataHandler::isError($tutor_infoAr)) {
    						$tutorFullName = $tutor_infoAr['nome'] . ' ' . $tutor_infoAr['cognome'];
    					} else {
    						$tutorFullName = translateFN('Utente non trovato');
    					}
    				} else {
    					$tutorFullName = translateFN('Ancora non assegnato');
    				}

    				/**
    				 * Get instance information
    				 */
    				$duration = sprintf("%d giorni", $instance[2]);
    				$scheduled = AMA_DataHandler::ts_to_date($instance[1]);
    				$end_date =  AMA_DataHandler::ts_to_date($instance[3]);
    				$nome_instanza = $instance[4];
    				// instance price
    				if (intval($instance[5])>=0) {
    					$priceLbl = (intval($instance[5])===0 ? translateFN('Gratuito') : ADA_CURRENCY_SYMBOL.' '.
    								 number_format($instance[5],ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP));
    					$instanceData['price'] = array(
    						'order' => 0,
    						'icon' => 'money',
    						'header' => translateFN('Costo'),
    						'data' => $priceLbl
    					);
    				}

    				// instance tutor or self instruction
    				if (intval($instance[6])>=0) {
    					$instanceData['tutor'] = array(
    						'order' => 4,
    						'icon' => 'user',
    						'header' => translateFN('Tutor'),
    						'data' => (intval($instance[6])===0 ? $tutorFullName : translateFN('Corso in autoistruzione'))
    					);
    				}

    				// instance duration hours
    				if (intval($instance[7])>0) {
    					$instanceData['durata'] = array(
    						'order' => 2,
    						'icon' => 'time',
    						'header' => translateFN('Durata'),
    						'data' => $instance[7].' '.(intval($instance[7])===1 ? translateFN('ora'): translateFN('ore'))
    					);
    				}

    				// instance service type
    				if (strlen($instance[8])>0) {
    					$servicelevel=null;
    					/* if isset $_SESSION['service_level'] it means that the istallation supports course type */
    					if(isset($_SESSION['service_level'][$instance[8]])){
    						$servicelevel=$_SESSION['service_level'][$instance[8]];
    					}
    					if(!isset($servicelevel) || is_null($servicelevel)){$servicelevel=DEFAULT_SERVICE_TYPE_NAME;}

    					$instanceData['servicelevel'] = array(
    						'order' => 1,
    						'icon' => 'browser',
    						'header' => translateFN('Tipo di corso'),
    						'data' => translateFN($servicelevel)
    					);
    				}

    				// instance start and end dates
    				if (strlen($scheduled)>0 || strlen($end_date)>0) {
    					$dates = '';
    					if (strlen($scheduled)>0) $dates .= translateFN('Dal').' '.$scheduled;
    					if (strlen($end_date)>0) $dates .= ' '.translateFN('al').' '.$end_date;
    					if (strlen($dates)>0) {
	    					$instanceData['dates'] = array(
	    							'order' => 3,
	    							'icon' => 'calendar',
	    							'header' => translateFN('Date'),
	    							'data' => $dates
	    					);
    					}
    				}

    				$course_infoAr = $tester_dh->get_course_info_for_course_instance($instanceId);
    				/*
    				 * The first element of the array come from concat_ws
    				 * the key of the array is like this [concat_ws(' ',u.nome,u.cognome)]
    				 * the best way to get the value  is to access directly the value
    				 */
    				$author_name = reset($course_infoAr);
    				/*
    				 * The first element of the array come from concat_ws
    				 */
    				$label = translateFN('Corso') .': '. $course_infoAr['nome'].' - '.$course_infoAr['titolo'] . ' - '
    						. translateFN('Fornito Da').': '.$provider_name; //.' - ' . translateFN('Autore'). ': '. $author_name;

    				if (!isset($instancesCDOM)) {
    					// set first column wideness
    					$courseInfoContent['firstcol_wideness'] = 'eleven wide';
    					// instatiate second column
    					$instancesCDOM = CDOMElement::create('div','class:secondcol five wide column');
    				}

    				// a container for the current instance
    				$container = CDOMElement::create('div','class:classinfo');
    				$instancesCDOM->addChild($container);

    				// instance name
    				$instanceNameDIV = CDOMElement::create('div','class:ui top attached segment item');
    				$instanceNameDIV->addChild(new CText('<i class="users large icon"></i>'.$nome_instanza));
    				$container->addChild($instanceNameDIV);

    				// instance data
    				if (isset($instanceData) && is_array($instanceData) && count($instanceData)>0) {

    					// sort instanceData by 'order' field
    					usort($instanceData, function ($item1, $item2) {
    						if ($item1['order'] == $item2['order']) return 0;
    						return $item1['order'] < $item2['order'] ? -1 : 1;
    					});

    					$instanceDataSEGMENT = CDOMElement::create('div','class:ui attached segment');
	    				$instanceDataDIV = CDOMElement::create('div','class:ui horizontal floated list');
	    				$instanceDataSEGMENT->addChild($instanceDataDIV);
	    				foreach ($instanceData as $aData) {
	    					$instanceDataITEM = CDOMElement::create('div','class:item');
	    					$instanceDataCONTENT = CDOMElement::create('div','class:content');
	    					$instanceDataHEADER = CDOMElement::create('div','class:header');
	    					$instanceDataHEADER->addChild(new CText($aData['header']));
	    					$instanceDataCONTENT->addChild($instanceDataHEADER);
	    					$instanceDataCONTENT->addChild(new CText($aData['data']));

	    					$instanceDataITEM->addChild(CDOMElement::create('i','class:big icon '.$aData['icon']));
	    					$instanceDataITEM->addChild($instanceDataCONTENT);
		    				$instanceDataDIV->addChild($instanceDataITEM);
	    				}
	    				unset($instanceData);
	    				$container->addChild($instanceDataSEGMENT);
    				}

    				// subscribe link
    				if (isset($subscribe_link) && $subscribe_link instanceof CElement) {
	    				$subscribeDIV = CDOMElement::create('div','class:ui bottom attached segment');
	    				$subscribeITEM = CDOMElement::create('div','class:item');
	    				$subscribeITEM->addChild($subscribe_link);
	    				$subscribeDIV->addChild($subscribeITEM);
	    				$container->addChild($subscribeDIV);
    				}

    				$courseInfoContent['instancesColumn'] = $instancesCDOM->getHtml();
    			} // foreach($instancesAr as $instance)
    		} // if(!AMA_DB::isError($instancesAr) && is_array($instancesAr) && count($instancesAr) > 0)
    	} // foreach ($coursesAr as $courseData)

    } else {
    	$errorMSG = CDOMElement::create('div','id:errorMSG,class:ui error icon large message');
    	$errorMSG->addChild(CDOMElement::create('i','class:attention icon'));
    	$MSGcontent = CDOMElement::create('div','class:content');
    	$MSGheader = CDOMElement::create('div','class:header');

    	$errorMSG->addChild($MSGcontent);
    	$MSGcontent->addChild($MSGheader);

    	$MSGheader->addChild(new CText(translateFN('Corso non trovato')));
    }

    $optionsAr['onload_func'] = 'initDoc('.intval(isset($errorMSG)).');';

} else if($op !== false && $op == 'subscribe') {
    $providerId = DataValidator::is_uinteger($_GET['provider']);
    $courseId = DataValidator::is_uinteger($_GET['course']);
    $instanceId = DataValidator::is_uinteger($_GET['instance']);
    $_SESSION['subscription_page'] = HTTP_ROOT_DIR . '/info.php?op=subscribe&provider='.$providerId.
                                     '&course='.$courseId.'&instance='.$instanceId;
    if($userObj instanceof ADAUser) {

        if($providerId !== false && $courseId !== false && $instanceId !== false) {
            $testerInfoAr = $common_dh->get_tester_info_from_id($providerId,AMA_FETCH_ASSOC);
            if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
                $tester = $testerInfoAr['puntatore'];
                $provider_name = $testerInfoAr['nome'];

                $testersAr[0] = $tester; // it is a pointer (string)
                $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
                $course_instance_infoAR = $tester_dh->course_instance_get($instanceId);
                if (!AMA_DataHandler::isError($course_instance_infoAR)) {
                    $startStudentLevel = $course_instance_infoAR['start_level_student'];

                      // add user to tester DB
                    $id_tester_user = Multiport::setUser($userObj,$testersAr,$update_user_data = FALSE);
                    if ($id_tester_user !== FALSE ) {
                        $result = $tester_dh->course_instance_student_presubscribe_add($instanceId, $userObj->getId(),$startStudentLevel);
                        if(!AMA_DataHandler::isError($result) || $result->code == AMA_ERR_UNIQUE_KEY) {

                            $data = CDOMElement::create('div','class:ui success icon large message');
                            $data->addChild(CDOMElement::create('i','class:ok sign icon'));
                            $MSGcontent = CDOMElement::create('div','class:content');
                            $MSGheader = CDOMElement::create('div','class:header');
                            $MSGtext = CDOMElement::create('span','class:message');

                            $data->addChild($MSGcontent);
                            $MSGcontent->addChild($MSGheader);
                            $MSGcontent->addChild($MSGtext);

                            $MSGheader->addChild(new CText(translateFN('La tua preiscrizione è stata effettuata con successo.')));
                            $MSGtext->addChild(BaseHtmlLib::link($userObj->getHomePage(), translateFN('Clicca qui')));
                            $MSGtext->addChild (new CText(' '.translateFN('per tornare alla tua home page')));

                            if ($course_instance_infoAR['price'] > 0) {
                                $args = '?provider='.$providerId.'&id_course='.$courseId.'&id_course_instance='.$instanceId;
                                header('Location: ' . HTTP_ROOT_DIR . '/browsing/student_course_instance_subscribe.php'.$args);
                                exit();
                            } else {
                                $result = $tester_dh->course_instance_student_subscribe($instanceId, $userObj->getId(),ADA_STATUS_SUBSCRIBED, $startStudentLevel);
                                if(!AMA_DataHandler::isError($result)) {

                                	$data = CDOMElement::create('div','class:ui success icon large message');
                                	$data->addChild(CDOMElement::create('i','class:ok sign icon'));
                                	$MSGcontent = CDOMElement::create('div','class:content');
                                	$MSGheader = CDOMElement::create('div','class:header');
                                	$MSGtext = CDOMElement::create('span','class:message');

                                	$data->addChild($MSGcontent);
                                	$MSGcontent->addChild($MSGheader);
                                	$MSGcontent->addChild($MSGtext);

                                	$MSGheader->addChild(new CText(translateFN('La tua iscrizione è stata effettuata con successo.')));
                                	$MSGtext->addChild(BaseHtmlLib::link($userObj->getHomePage(), translateFN('Clicca qui')));
                                	$MSGtext->addChild (new CText(' '.translateFN('per andare alla tua home page e accedere')));
                                }

                            }

//                        } else if($result->code == AMA_ERR_UNIQUE_KEY) {
//                            $data = new CText(translateFN('Risulti già preiscritto a questa edizione del corso'));
                        } else {
                        	$data = CDOMElement::create('div','class:ui error icon large message');
                        	$data->addChild(CDOMElement::create('i','class:attention icon'));
                        	$MSGcontent = CDOMElement::create('div','class:content');
                        	$MSGheader = CDOMElement::create('div','class:header');
                        	$MSGtext = CDOMElement::create('span','class:message');

                        	$data->addChild($MSGcontent);
                        	$MSGcontent->addChild($MSGheader);
                        	$MSGcontent->addChild($MSGtext);

                        	$MSGheader->addChild(new CText(translateFN('Si è verificato un errore')));
                        	$MSGtext->addChild(BaseHtmlLib::link($userObj->getHomePage(), translateFN('Clicca qui')));
                        	$MSGtext->addChild (new CText(' '.translateFN('per tornare alla tua home page')));
                        }
                    } else {
                    	$data = CDOMElement::create('div','class:ui error icon large message');
                    	$data->addChild(CDOMElement::create('i','class:attention icon'));
                    	$MSGcontent = CDOMElement::create('div','class:content');
                    	$MSGheader = CDOMElement::create('div','class:header');
                    	$MSGtext = CDOMElement::create('span','class:message');

                    	$data->addChild($MSGcontent);
                    	$MSGcontent->addChild($MSGheader);
                    	$MSGcontent->addChild($MSGtext);

                    	$MSGheader->addChild(new CText(translateFN('Si è verificato un errore aggiungendo lo studente al provider')));
                    	$MSGtext->addChild(BaseHtmlLib::link($userObj->getHomePage(), translateFN('Clicca qui')));
                    	$MSGtext->addChild (new CText(' '.translateFN('per tornare alla tua home page')));
                    }

                }

                $course_infoAr = $tester_dh->get_course_info_for_course_instance($instanceId);
                /*
                 * The first element of the array come from concat_ws
                 * the key of the array is like this [concat_ws(' ',u.nome,u.cognome)]
                 * the best way to get the value  is to access directly the value
                 */
                $author_name = reset($course_infoAr);
                /*
                 * The first element of the array come from concat_ws
                 */
                $label = translateFN('Corso') .': '. $course_infoAr['nome'].' - '.$course_infoAr['titolo'] . ' - '
                         . translateFN('Ente').': '.$provider_name; //.' - ' . translateFN('Autore'). ': '. $author_name;

            } else {
                $data = new CText('Si è verificato un errore');
            }
        }
    } else {
        header('Location: ' . HTTP_ROOT_DIR . '/login_required.php');
        exit();
    }
} else if (($op !== false && $op == 'undo_subscription')) {
    $providerId = DataValidator::is_uinteger($_GET['provider']);
    $courseId = DataValidator::is_uinteger($_GET['course']);
    $instanceId = DataValidator::is_uinteger($_GET['instance']);
    $studentId = DataValidator::is_uinteger($_GET['student']);
    $testerInfoAr = $common_dh->get_tester_info_from_id($providerId,AMA_FETCH_ASSOC);
    if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
        $tester = $testerInfoAr['puntatore'];
        $provider_name = $testerInfoAr['nome'];

        $testersAr[0] = $tester; // it is a pointer (string)
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        $course_instance_infoAR = $tester_dh->course_instance_get($instanceId);
        if (!AMA_DataHandler::isError($course_instance_infoAR)) {

            $result = $tester_dh->course_instance_student_presubscribe_remove($instanceId, $userObj->getId());
            if(!AMA_DataHandler::isError($result)) {
                $info_div = CDOMElement::create('DIV', 'id:info_div');
                $info_div->setAttribute('class', 'info_div');
                $label_text = CDOMElement::create('span','class:info');
                $label_text->addChild(new CText(translateFN('La tua pre-iscrizione è stata annullata.')));
                $info_div->addChild($label_text);
                $homeUser = $userObj->getHomePage();
                $link_span = CDOMElement::create('span','class:info_link');
                $link_to_home = BaseHtmlLib::link($homeUser, translateFN('Torna alla home.'));
                $link_span->addChild($link_to_home);
                $info_div->addChild($link_span);
                $data = $info_div;
            } else {
                $info_div = CDOMElement::create('DIV', 'id:info_div');
                $info_div->setAttribute('class', 'info_div');
                $label_text = CDOMElement::create('span','class:info');
                $label_text->addChild(new CText(translateFN("C'è stato un problema annullando la tua pre-iscrizione.")));
                $info_div->addChild($label_text);
                $homeUser = $userObj->getHomePage();
                $link_span = CDOMElement::create('span','class:info_link');
                $link_to_home = BaseHtmlLib::link($homeUser, translateFN('Torna alla home.'));
                $link_span->addChild($link_to_home);
                $info_div->addChild($link_span);
                //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
                $data = $info_div;
            }
        }
    }
} else {
	/**
	 * giorgio 13/ago/2013
	 * if it's not a multiprovider environment, must load only published course
	 * of the only selected single provider stored in GLOBALS.
	 * else make the default function call
	 */
	if (!MULTIPROVIDER)
	{
		// if provider is not set or there's an error loading its id, retirect to home
		$redirect = false;

		/**
		 * sets user selected provider name
		 */
		if (isset($GLOBALS['user_provider']))
			$user_provider_name = $GLOBALS['user_provider'];

		/**
		 * check if user selected provider name has a valid id in the database
		 */
		if (isset($user_provider_name))
		{
			$userTesterInfo = $common_dh->get_tester_info_from_pointer($user_provider_name);
			$user_provider_id = (!AMA_DB::isError($userTesterInfo)) ? $userTesterInfo[0] : null;
			$redirect = is_null($user_provider_id);
		}
		else $redirect = true;

		if (!$redirect) $publishedServices = $common_dh->get_published_courses($user_provider_id);
		else {
			header ('Location: '.HTTP_ROOT_DIR.'/info.php');
			die();
		}
		$thead_data = array('&nbsp;', 'ID', translateFN('corso'), translateFN('descrizione'), translateFN('crediti'),'&nbsp;');
	} else {
		$thead_data = array('&nbsp;', 'ID', translateFN('corso'), translateFN('Fornito da'), translateFN('descrizione'), translateFN('crediti'),'&nbsp;');
		$publishedServices = $common_dh->get_published_courses();
	}

    if(!AMA_Common_DataHandler::isError($publishedServices)) {
//      $thead_data = array('nome', 'descrizione', 'durata (giorni)', 'informazioni');
        $tbody_data = array();

        foreach($publishedServices as $service) {
               $serviceId = $service['id_servizio'];
               $coursesAr = $common_dh->get_courses_for_service($serviceId);
               if(!AMA_DB::isError($coursesAr)) {
                    $currentTesterId = 0;
                    $currentTester = '';
                    $tester_dh = null;
                    foreach($coursesAr as $courseData) {
                        $courseId = $courseData['id_corso'];
                        $Flag_course_has_instance=false;
                        $newTesterId = $courseData['id_tester'];
                        if($newTesterId != $currentTesterId) { // stesso corso su altro tester ?
                        	$testerInfoAr = $common_dh->get_tester_info_from_id($newTesterId,AMA_FETCH_ASSOC);
                        	if(!AMA_DB::isError($testerInfoAr)) {
                        		$providerName = $testerInfoAr['nome'];
                        		$tester = $testerInfoAr['puntatore'];
                        		$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
                        		$currentTesterId = $newTesterId;
                        		$course_dataHa = $tester_dh->get_course($courseId);
                        		$instancesAr = $tester_dh->course_instance_subscribeable_get_list(
                        				array('data_inizio_previsto', 'durata', 'data_fine', 'title'),
                        				$courseId);
                        		if(is_array($instancesAr) && count($instancesAr) > 0) {
                        			$Flag_course_has_instance=true;
                        		}
                                if (!AMA_DB::isError($course_dataHa)) {
                                	$credits =  $course_dataHa['crediti'];
                                	// supponiamo che tutti i corsi di un servizio (su tester diversi) abbiano lo stesso numero di crediti
                                	// quindi prendiamo solo l'ultimo
                                } else {
                                	$credits = 1;       // should be ADA_DEFAULT_COURSE_CREDITS
                                }
                        	}
                        }

                        $more_info_link = BaseHtmlLib::link("info.php?op=course_info&id=$serviceId",
                        		'<i class="big icon info"></i>');
                        $more_info_link->setAttribute('title', strip_tags(translateFN('More info')));
                        $more_info_link->setAttribute('class', 'more_info_link');

                        $row = array($Flag_course_has_instance ? '<i class="sign icon add green"></i>' : null, $courseId, $service['nome']);
                        if (MULTIPROVIDER) array_push($row, $providerName);
                        array_push($row,
                        	$service['descrizione'],
                        	$credits,
                        	// $service['durata_servizio'],
                        	$more_info_link
                        );
                        $row['instances'] = null;
                        if ($Flag_course_has_instance) {
                        	// sort by data_inizio_previsto DESC
                        	uasort($instancesAr, function($a, $b){
                        		if ($a['data_inizio_previsto'] == $b['data_inizio_previsto']) return 0;
                        		return ($a['data_inizio_previsto'] > $b['data_inizio_previsto']) ? -1 : 1;
                        	});
                        	foreach ($instancesAr as $instKey => $instanceEl) {
                        		foreach ($instanceEl as $iKey => $iVal) {
	                        		if (is_numeric($iKey)) unset($instancesAr[$instKey][$iKey]);
	                        		else if (stripos($iKey, 'data') !== false) $instancesAr[$instKey][$iKey] = ts2dFN($iVal);
	                        	}
	                        	$instancesAr[$instKey]['isended'] = ($instanceEl[3] > 0 && $instanceEl[3] < time()) ? true : false;
                        	}

	                        $row['instances'] = json_encode($instancesAr);
                        }
                        $tbody_data[] = $row;
                    }
               } else {
               		$credits = 1;       // should be ADA_DEFAULT_COURSE_CREDITS
               }
        }
        $data = BaseHtmlLib::tableElement('id:infotable,class:'.ADA_SEMANTICUI_TABLECLASS, $thead_data, $tbody_data);
        $optionsAr['onload_func'] = 'initDoc('.intval(MULTIPROVIDER).');';
    } else {
        $data = new CText(translateFN('Non sono stati pubblicati corsi'));
    }
}
$title = translateFN('Corsi ai quali puoi iscriverti');
$help = '';

$layout_dataAr['JS_filename'] = array(
		JQUERY_DATATABLE
);

$content_dataAr = array(
    'course_title' => $title,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => isset($label) ? $label : null,
    'help' => $help,
    'data' => isset($data) ? $data->getHtml() : null,
	'errorMSG' => isset($errorMSG) ? $errorMSG->getHtml() : null,
);

/**
 * Merge courseInfoContent into $content_dataAr
 */
if (isset($courseInfoContent)) $content_dataAr = array_merge($content_dataAr, $courseInfoContent);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL));
