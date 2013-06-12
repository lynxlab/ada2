<?php



function _get_services_to_subscribe($orderBy = 'service',$minLevel = 1,$maxLevel = 5){

// used by browsing/user and info
// so  we have to distinguish...
 $common_dh = $GLOBALS['common_dh'];
 $callerModule = $GLOBALS['self'];
 $sess_id_user = $_SESSION['sess_id_user'];

 $userObj = $_SESSION['sess_userObj'];


// services
	// filtering on levels
     $level_ha = Multiport::get_service_max_level($sess_id_user);

    // version using COMMON

	 if (isset($minLevel) AND ($minLevel<5)){
	 	 $livelloMin = $minLevel;
	 } else {
	 	 $livelloMin = 1;
	 }
 	 if (isset($maxLevel) AND ($maxLevel>1)){
	 	$livelloMax = $maxLevel;
	 } else {
	 	 $livelloMax = 5;
	 }


 	 $clause = "s.livello <= $livelloMax AND s.livello >= $livelloMin ";

// 	ordering
 	 if ($orderBy == 'service'){
 	    $service_infoAr = $common_dh->get_services(array('s.nome','t.nazione','s.livello'),$clause);
 	 } elseif($orderBy == 'country'){
 	   $service_infoAr = $common_dh->get_services(array('t.nazione','t.provincia','t.nome'),$clause);
 	 }

	 $s = 0;
	 foreach ($service_infoAr as $course_dataHa){

	 	$provider_name =  $course_dataHa[5];
		$provider_id =  $course_dataHa[4];
		$provider_dataHa =  $common_dh->get_tester_info_from_id($provider_id);
		$provider_pointer = $provider_dataHa[10];
		$provider_dsn = Multiport::getDSN($provider_pointer);
        if($provider_dsn != null) {
            $provider_dataHa = $common_dh->get_tester_info_from_pointer($provider);
            $provider_dh = AMA_DataHandler::instance($provider_dsn);
		    $id_course_instanceAr = $provider_dh->get_course_instance_for_this_student_and_course_model($sess_id_user, $service_implementation_id);
        } else {
          $id_course_instanceAr = NULL;
        }
       // already subscribed?
         if (AMA_DataHandler::isError($id_course_instanceAr)){ // never subscribed
            $id_course_instance = 0;
         } else {
         	$id_course_instance = $id_course_instanceAr['istanza_id'];
         }
         $service_infoAr[$s][8] = $id_course_instance;
         $s++;
	 }

     $optionsAr = array(
		'callerModule'=>$callerModule,
		'orderBy'=>$orderBy
     );
	 return GuestHtmlLib::displayServiceData($userObj,$service_infoAr,$optionsAr);
}




function _get_service_info($id_service){

	$common_dh = $GLOBALS['common_dh'];

	$label_title = translateFN('Titolo');
    $label_level = translateFN('Livello');
    $label_description = translateFN('Descrizione');
    $label_service_time = translateFN('Durata servizio');
    $label_service_min_meetings = translateFN('Numero minimo incontri');
    $label_service_max_meetings = translateFN('Numero massimo incontri');
    $label_service_meeting_max_time = translateFN('Durata incontri');
    $label_provider = translateFN('Erogatore');
	$overall_service_data = "";
    $service_infoHa = array();
	$serviceAr = $common_dh->get_service_info($id_service);

	if (!AMA_DataHandler::isError($serviceAr)){
	  	$service_title =  $serviceAr[1];
	  	$service_description = $serviceAr[2];
	  	$service_level = $serviceAr[3];

	  	// durata_servizio, min_incontri, max_incontri, durata_max_incontro
	  	$service_time =  $serviceAr[4];
	  	$service_min_meetings =  $serviceAr[5];
	  	$service_max_meetings =  $serviceAr[6];
	  	$service_meeting_max_time =  $serviceAr[7];
	  } else {
	  	$service_description = translateFN("Servizio non disponibile");
	  	$service_level = translateFN("?");
	  	$service_title =  translateFN("Servizio non disponibile");

	  }

       $row = array(
			          //$label_provider=>$tester_name,

			          $label_title =>$service_title,
			          $label_level=>$service_level,
			          $label_description=>nl2br($service_description),
			          $label_service_time => $service_time,
	  				  $label_service_min_meetings => $service_min_meetings,
	  				  $label_service_max_meetings => $service_max_meetings,
	  				  $label_service_meeting_max_time => $service_meeting_max_time

			          );
  	  $service_data = BaseHtmlLib::plainListElement("",$row);
      $overall_service_data = $service_data->getHtml();

	  $testerAr = $common_dh->get_tester_for_service($id_service);
	  if (!AMA_DataHandler::isError($testerAr)){
		  foreach ($testerAr as $id_tester){
		  		$row = array();
		     	$tester_dataHa = $common_dh->get_tester_info_from_id($id_tester);
		     	$tester_name = $tester_dataHa[1];
		      	$serviceImplementorsHa = $common_dh->get_courses_for_service($id_service,$id_tester);
		      	if (!AMA_DataHandler::isError($serviceImplementorsHa)){
			      	$id_course_for_service = $serviceImplementorsHa[0];
			      	$service_link = "<a href='info.php?id_course=$id_course_for_service'>$tester_name</a>";
			      	$row[$label_provider]=$service_link;
					array_push($service_infoHa,$row);
		      	}
		    }
		     $tObj = new Table();
		     // $tObj->initTable('1','center','0','1','100%','','','','');
		     $tObj->initTable('1','center','0','1','100%','','','','','1','1');
		     $caption = "<strong>".translateFN("Informazioni dettagliate sul servizio")."</strong>";
		     $summary = translateFN("Informazioni dettagliate sul servizio");
		     $tObj->setTable($service_infoHa,$caption,$summary);
		     $overall_service_data.= $tObj->getTable();
	  } else {
	  	$overall_service_data =  translateFN("Servizio non erogato");
	  }
	return $overall_service_data;
}

function _get_course_instance_info($id_course,$id_course_instance){

	$common_dh = $GLOBALS['common_dh'];
	$dh = $GLOBALS['dh'];
    $sess_id_user = $_SESSION['sess_id_user'];
    $userObj = $_SESSION['sess_userObj'];


    $course_dataHa = $common_dh->get_service_info_from_course($id_course);
    $service_title = $course_dataHa[1];
    $service_level = $course_dataHa[3];
    //..


    $provider_dataHa = $common_dh->get_tester_info_from_id_course($id_course);
    if (!AMA_DataHandler::isError($provider_dataHa)){
	    $provider_pointer = $provider_dataHa['puntatore'];
	    $provider_name =  $provider_dataHa['nome'];
		$provider_dsn = Multiport::getDSN($provider_pointer);
	    if($provider_dsn != null) {
	    	$provider_dh = AMA_DataHandler::instance($provider_dsn);
			$sub_courses = $provider_dh->get_subscription($sess_id_user, $id_course_instance);
			// if (!AMA_DataHandler::isError($sub_courses)&&$sub_courses['tipo'] == 2) { // introducing status 3 (suspended) and 5 (completed)
			 if (!AMA_DataHandler::isError($sub_courses)) { // introducing status 3 (suspended) and 5 (completed)

			    $info_dataHa = array();
		     	$id_tutor = $dh->course_instance_tutor_get($id_course_instance);
		      	// vito, 27 may 2009
		      	if($id_tutor !== false) {
		        	$tutor = $dh->get_tutor($id_tutor);
		        	// vito, 27 may 2009
		        	if( !AMA_DataHandler::isError($tutor) && is_array($tutor)) {
		          		$tutor_name = $tutor['nome']." ".$tutor['cognome'];
		          		if (empty($tutor_name)) {
		            		$tutor_info = translateFN('Non assegnato');
		          		}
		          		else {
			                //  if (isset($sess_id_user)){
			                // $tutor_info = "<a href=\"$http_root_dir/admin/zoom_tutor.php?id=$id_tutor\">$tutor_name</a>";
			                // } else{
			                $tutor_info = $tutor_name;
			                //  }
		          		}
		        	}
		      	}
		   	   // vito, 27 may 2009
		      	else {
		        	$tutor_info =  translateFN('Non assegnato');
		      	}

		      $start_date = ts2dFN($sub_courses['istanza_ha']['data_inizio']);

		      // messaggi
		      $messages_list =""; // FIXME


		      // appuntamenti
		      $msgs_ha = MultiPort::getUserAgenda($userObj);
			  if (AMA_DataHandler::isError($msgs_ha)){
			    $errObj = new ADA_Error($msgs_ha,translateFN('Errore in lettura appuntamenti'));
			  }
			  $testers_dataAr = MultiPort::getTestersPointersAndIds();
		      $meeting_List   = CommunicationModuleHtmlLib::getAgendaAsForm($dataAr, $testers_dataAr);


			//  $label_provider = translateFN('Fornitore');
			//  $label_title = translateFN('Titolo');
			  $label_date = translateFN('Data di inizio');
			  $label_tutor = translateFN('Tutor');
			  $label_meeting = translateFN('Appuntamenti');
			  $label_messages = translateFN('Messaggi');

		      $row = array(
		      //	$label_provider=>$tester_name, // attenzione: è l'ultimo della lista!!!!'
		      //	$label_title=>$service_title,
		        "<img src=\"img/flag.png\" border=0> " .$label_date =>$start_date,

		        $label_tutor=>$tutor_info,
		        $label_meeting=>$meeting_list,
		        $label_messages=>$messages_list

		        //        "<img src=\"img/author.png\" border=0> ".translateFN('Autore')=>$author_info
		      );

		      array_push($info_dataHa,$row);

		      $tObj = new Table();
		      $tObj->initTable('1','center','0','1','100%','','','','',1,1);
		      $caption = "<strong>". translateFN("Storico del servizio"). "</strong>";
		      $summary = translateFN("Storico del servizio");
		      $tObj->setTable($info_dataHa,$caption,$summary);
		      $requested_service_data = $tObj->getTable();
		    }
		    else {
		      $requested_service_data = translateFN("Nessun'informazione disponibile sul servizio $id_course_instance.");
		    }
	    } else {
	    	  $requested_service_data = translateFN("Nessun'informazione disponibile sul servizio $id_course_instance.");
	    }

    }  else {
    	  $requested_service_data = translateFN("Nessun'informazione disponibile sul servizio $id_course_instance.");
    }

	return $requested_service_data;
}

function _get_course_info($id_course){

      $common_dh = $GLOBALS['common_dh'];
	  $serviceAr = $common_dh->get_service_info_from_course($id_course);
	  if (!AMA_DataHandler::isError($serviceAr)){
	  	$service_name = $serviceAr[1];
	  	$service_level = $serviceAr[3];
	  	$service_duration = $serviceAr[4];
	  	$service_max_meeting = $serviceAr[5];
	  	$service_max_meeting_time = $serviceAr[7] / 60;
	  }
	  $providerAr = $common_dh->get_tester_info_from_id_course($id_course);

	  if (!AMA_DataHandler::isError($providerAr)){
	    $provider = $providerAr['puntatore'];
	    $provider_dsn = MultiPort::getDSN($provider);
	    $provider_name = $providerAr['nome'];
	    $service_country = $providerAr['nazione']."/".$providerAr['provincia'];
      }
      $provider_dh = AMA_DataHandler::instance($provider_dsn);

 	  $dataHa = $provider_dh->get_course($id_course);
	  if (AMA_DataHandler::isError($dataHa) || (!is_array($dataHa))){
	    $infomsg = $dataHa->getMessage();
	    if ($dataHa->code == AMA_ERR_NOT_FOUND){
	    	$service_description = translateFN("Il provider non fornisce attualmente questo servizio");
	    }
	    // header("Location: $error?err_msg=$msg");
	    //return $infomsg;
	  }
	  else {

	    if (!empty($dataHa['nome'])){
	      $course_infoHa = array();
	      // 1.info from table modello_corso
	      $service_title = $dataHa['titolo'];
	      // $service_name = $dataHa['nome'];

	      // 2. description of instance from table modello_corso
		  $service_instance_description = $dataHa['descr'];

		  // 3. full description from node id_nodo_iniziale
		  $id_desc = $id_course."_".$dataHa['id_nodo_iniziale'];
		  $user_level = "999";
	  	  $nodeHa = $provider_dh->get_node_info($id_desc);
		  if (AMA_DataHandler::isError($nodeHa)) {
	     //	$errorObj = new ADA_error($nodeHa); //FIXME: mancano gli altri dati
	    	$service_description = translateFN("Il provider non fornisce attualmente questo servizio");

	      }
	      else {
		  	$service_description = $nodeHa['text'];
	     }
	    }
	    else {
	    	$service_description = translateFN("Il provider non fornisce attualmente questo servizio");
	    }
	  }
	  // check on level?
		/*
        if ($service_level > 1){
        	$require_link = "<a href=" . HTTP_ROOT_DIR .  "/browsing/registration.php?id_course=$id_course>" .translateFN('Richiedi'). "</a>";
        } else {
        	$require_link = "<a href=" . HTTP_ROOT_DIR .  "/browsing/view.php?id_course=$id_course&id_node=$id_desc>" .translateFN('Entra'). "</a>";
        }
        */

 		if ($service_instance_description == NULL){
 			$service_instance_description = level2descriptionFN($service_level);
 		}


    $service_div = CDOMElement::create('div','id:service_info');

	$thead_data = array(
      translateFN('Fornitore'),
      translateFN('Nome'),
      translateFN('Paese'),
      translateFN('Livello'),
      translateFN('Durata (gg)'),
      translateFN('Numero incontri'),
      translateFN('Durata incontro (minuti)'),
      translateFN('Descrizione servizio'),
      translateFN('Descrizione dettagliata')
      );

    $tbody_dataAr = array();

	$tbody_dataAr[] = array(
      $provider_name,
      $service_name,
      $service_country,
      level2stringFN($service_level),
      $service_duration,
      $service_max_meeting,
      $service_max_meeting_time,
      $service_description,
      $service_instance_description
      );
	$element_attributes ="";
	$serviceTable = BaseHtmlLib::tableElement($element_attributes, $thead_data, $tbody_dataAr);

	$service_div->addChild($serviceTable);
    $service_data = $service_div->getHtml();

	return $service_data;
}

function get_subscription($id_user){
        /*
         * troppo lenta....
         */
        // versione che cicla sulle iscrizioni ad un servizio
        $common_dh = $GLOBALS['common_dh'];
        // servizio
        $service_courseAr = $common_dh ->get_service_implementors();

        // corso

        foreach ($service_courseAr as $service_implementor) {
            $id_service =   $service_implementor['id_servizio'];
            $id_course =   $service_implementor['id_corso'];
            //tester
            $providerId = get_tester_for_service($id_service);
            $provider_dataHa = $common_dh-> get_tester_info_from_id($id_tester);
            $provider_pointer = $provider_dataHa[11];
            $provider_dsn = Multiport::getDSN($provider_pointer);

            if($provider_dsn != null) {
                $provider_dh = AMA_DataHandler::instance($provider_dsn);
                // istanze per corso
               $all_instance = $provider_dh->get_course_instance_for_this_student_and_course_model($id_user, $id_course);
              //...
            }
        }
}

function level2descriptionFN($level){
// FIXME: it would be better if we had a DB table for this...
	switch ($level){

		case 1:case"1":default:
			$levelAsDescription = "Information on educational and professional issues are provided by the
tester organisations in order to be self-consulted by users.
The user of one country will also be able to search for educational and vocational information
concerning another one of the partner countries. Information is available in English and in the
language of the information provider.";
		break;
		case 2:case"2":
			$levelAsDescription = "At the end of the self-guidance path the user could need customised advice with a
guidance practitioner on the information found out. It is thus possible to have an interactive interview with
the e-guidance practitioner through the use of chat rooms, free-phone calls, videoconference.
The user of one country could require customised advice on the above-mentioned issues concerning another
partner country. In that case, advice will be delivered in English by the officer/practitioner.";
		break;
		case 3:case"3":
			$levelAsDescription = "A user could need a deep counselling interview in order to receive help in finding a job, tips
on a job interview, CV, information resources and other issues already detailed under the list of the counselling
on educational and vocational issues activity. It is thus possible to have an interactive interview with the e-guidance practitioner through the use of chat rooms, free-phone calls, videoconference and other ICT-based
tools.";
		break;
		case 4:case"4":
			$levelAsDescription = "A user could need a specialised guidance action, highly customised and long ones such as:<br />" .
					"Group counselling for the active job search<br /> Skills assessment paths<br /> Tutoring and support paths to employability for people with more difficulties.";
		break;

	}
	return translateFN($levelAsDescription);
}

function level2stringFN($level){

	switch ($level){

		case 1:case"1":default:
			$levelAsString = "1: Informazioni";
		break;
		case 2:case"2":
			$levelAsString = "2: Colloquio di orientamento";
		break;
		case 3:case"3":
			$levelAsString = "3: Consulenza";
		break;
		case 4:case"4":
			$levelAsString = "4: Consulenza specialistica";
		break;

	}
	return translateFN($levelAsString);
}

function subscriptionType2stringFN($tipo){
/*
define('ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED' , 0);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED' , 1);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED'  , 2);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_SUSPENDED' , 3);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED' , 5);
*/
	switch ($tipo){

		case ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED:default: //ADA_STATUS_REGISTERED:default:
			$typeAsString = "Registrato";
		break;
		case ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED: //ADA_STATUS_PRESUBSCRIBED:
			$typeAsString = "Preiscritto";
		break;
		case ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED: //ADA_STATUS_SUBSCRIBED:
			$typeAsString = "Iscritto";
		break;
		case ADA_SERVICE_SUBSCRIPTION_STATUS_SUSPENDED: //ADA_STATUS_REMOVED:
			$typeAsString = "Sospeso";
		break;
		case ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED:
			$typeAsString = "Completato";
			break;
		case ADA_STATUS_VISITOR:
			$typeAsString = "Visitatore";
		break;

	}
	return translateFN($typeAsString);
}
?>