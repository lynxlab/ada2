<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';


class GuestHtmlLib
{

  /*
   * methods used to display all services list
   */


    static public function displayAllServicesData(ADAGenericUser $UserObj, $service_infoAr=array(), $optionsAr=array()) {

     $to_sub_course_dataHa = array();
	 $callerModule = $optionsAr['callerModule'];
	 $orderBy = $optionsAr['orderBy'];


	// this function is called by browsing/user and by info
	 if ($callerModule == 'user'){
	   $callerModuleUrl = HTTP_ROOT_DIR."/browsing/$callerModule.php";
	 } else {
	   $callerModuleUrl = HTTP_ROOT_DIR."/$callerModule.php";
	 }

     $service_ordering_link = CDOMElement::create('a', 'href:'.$callerModuleUrl.'?ob=service');
     $service_ordering_link->addChild(new CText(translateFN("Servizio")));

     $country_ordering_link = CDOMElement::create('a', 'href:'.$callerModuleUrl.'?ob=country');
     $country_ordering_link->addChild(new CText(translateFN("Country")));

 	 $label1 = $service_ordering_link->getHtml();
 	 $label2 = $country_ordering_link->getHtml();
 	 $label3 = translateFN("Livello del servizio"); // ??
 	 $label4 = translateFN("Richiedi");
 	 $label5 = translateFN("Provider");
	 $label6 = translateFN("Login/Register");
	 $label7 = translateFN("Servizio non accessibile");
	 $label8 = translateFN("Non disponibile");
	 $label9 = translateFN("Iscritto");
	 $label10 = translateFN("Info");
	 $label11 = translateFN("Dettagli");

	 $old_service_country = "";
	 $old_service_title = "";

 	 foreach ($service_infoAr as $course_dataHa){
 	 	 //var_dump($course_dataHa);
 	 	 $id_service = $course_dataHa[0];
     	 $service_title = translateFN($course_dataHa[1]);
     	 $service_info =  CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/service_info.php?id_service='.$id_service);
     	 $service_info->addChild(new CText($label10));
		 $service_info_link = $service_info->getHtml();
     	 $service_level = $course_dataHa[2];
     	 $service_country = $course_dataHa[7]."/".$course_dataHa[6];
     	 $service_implementation_id = $course_dataHa[3];

		 $provider_name = $course_dataHa[5];

          //var_dump($course_dataHa);
         //if (AMA_DataHandler::isError($id_course_instanceAr)){ // never subscribed
          if ($course_dataHa[8] == 0){ // never subscribed
            $norequest = "";
         	// is level attainable for this provider?
		 	$max_level = $level_ha[$provider_pointer]['max_level'];
		 	if (($max_level==0) OR (($service_level - $maxlevel) == 1)){
	           $course_subscription = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/subscribe.php?id_course='.$service_implementation_id);
    	       $course_subscription->addChild(new CText($label4));
        	   $course_subscription_link = $course_subscription->getHtml();
		 	} else {
		 		$course_subscription_link = $label7.":".$service_level."-".$max_level; // level too much higher
		 	}
           } else  {
	           // var_dump($id_course_instanceAr) ;
	           $norequest = "&norequest=1";
			   $id_course_instance = $course_dataHa[8];
	           $course_subscription_link = $label9; /*.": $id_course_instance"*/ ;
           }
		 $course_registration = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/registration.php?id_course='.$service_implementation_id);
         $course_registration->addChild(new CText($label6));
         $course_registration_link =  $course_registration->getHtml();

         $course_info = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/service_info.php?id_course='.$service_implementation_id.$norequest);
 		 $course_info->addChild(new CText($label11));
         $course_info_link = $course_info->getHtml();

     	 if ($orderBy == 'service'){

     	 	if ($service_title!=$old_service_title){
             	$row[$label1] = "<strong>".$service_title."</strong>";
             	$old_service_title = $service_title;
            //    $row[$label10] = $service_info_link;
     	 	} else {
     	 		$row[$label1] = "";
     	 	//	$row[$label10] = "";
     	 	}

            // $course_info->addChild(new CText($provider_name));
       	 	$row[$label2] = $service_country;
            // $row[$label5] = $course_info_link;
            $row[$label5] = $provider_name;
            $row[$label11] = $course_info_link;
         	$row[$label3] =   level2stringFN($service_level);
     	    if ($callerModule == 'user'){
         	    $row[$label4] = $course_subscription_link;
         	} elseif ($callerModule == 'info'){
         	  $row[$label4] = $course_registration_link;
         	}
     	 } elseif ($orderBy == 'country'){
     	 	 if ($service_country != $old_service_country){
         	 	$row[$label2] ="<strong>".$service_country."</strong>";
         	 	$old_service_country = $service_country;
     	 	 }  else {
     	 		$row[$label2] = "";
     	 	}
            // $course_info->addChild(new CText($service_title));

     	 	$row[$label5] = $provider_name;
         	$row[$label1] = $service_title;
           // $row[$label10] = $service_info_link;
         	$row[$label11] = $course_info_link;
         	$row[$label3] = level2stringFN($service_level);
         	if ($callerModule == 'user'){
         	    $row[$label4] = $course_subscription_link;
         	} elseif ($callerModule == 'info'){
         	  $row[$label4] = $course_registration_link;
         	}
     	 }

     	  $to_sub_course_dataHa[] = $row;
 	 }

 	 if (count($to_sub_course_dataHa) > 0) {
    	    $tObj = new Table();
     	    $tObj->initTable('1','center','0','1','100%','','','','','1','1');
    	    $caption = "<strong>".translateFN("Servizi che puoi richiedere")."</strong>";
    	    $summary = translateFN("Elenco dei servizi che puoi richiedere");
    	    $tObj->setTable($to_sub_course_dataHa,$caption,$summary);
    	    $all_services_data = $tObj->getTable();
    	    // $submit_button = "<p align='center'><input type=\"submit\" name=\"submit\" value=".translateFN("Richiedi")."></p>";
    }  else {
		   // $submit_button = "<p align='center'><input type=\"submit\" name=\"submit\" value=".translateFN("Registrati")."></p>";
		   $all_services_data = translateFN("Non ci sono servizi che puoi richiedere");
	}

	$form.= $all_services_data; //.$submit_button;
	return $form;

  }

  static public function displayImplementationServiceData(ADAGenericUser $UserObj, $service_infoAr, $optionsAr=array()){

  	 $service_div = CDOMElement::create('div','id:service_info');

  	 $info_provider_thead_data = array($service_infoAr[6]);
  	 $info_provider_tbody_data = array(
  	 	array($service_infoAr[14]),
  	 	array($service_infoAr[15]),
  	 	array($service_infoAr[9]),
  	 	array($service_infoAr[8]."/".$service_infoAr[7]),
  	 	array($service_infoAr[13]),
  	 	array($service_infoAr[12]),
  	 	array($service_infoAr[11])
  	 );
	 $element_attributes ="";
	 $provider_Table = BaseHtmlLib::tableElement($element_attributes, $info_provider_thead_data, $info_provider_tbody_data);

//	 $service_div->addChild($serviceTable);
     $provider_data = $provider_Table->getHtml();

	 $thead_data = array(
  //    translateFN('Nome'),
//      translateFN('Paese'),
  //    translateFN('Città'),
  //    translateFN('Livello'),
  //    translateFN('Durata (gg)'),
  //    translateFN('Numero incontri'),
  //    translateFN('Durata incontro (minuti)'),
      translateFN('Informazioni'),
      translateFN('Descrizione dettagliata'),
      translateFN('Fornitore')
      );


    //  var_dump($service_infoAr);
    $tbody_dataAr = array();

	$tbody_dataAr[] = array(
      //translateFN('Nome')=>$service_infoAr[0],
//      translateFN('Paese')=>$service_infoAr[7]."/".$service_infoAr[8],
   //   translateFN('Città')=>$service_infoAr[9],
   //   translateFN('Livello')=>level2stringFN($service_infoAr[3]),
   //   translateFN('Durata (gg)')=>$service_infoAr[4],
   //   translateFN('Numero incontri')=>$service_infoAr[5],
   //   translateFN('Durata incontro (minuti)')=>$service_infoAr[6],
      translateFN('Informazioni')=>$service_infoAr[1],
      translateFN('Descrizione dettagliata')=>$service_infoAr[10],
      translateFN('Fornitore')=>$provider_data //$service_infoAr[6]."<br />".$service_infoAr[7]."/".$service_infoAr[8].$service_infoAr[11]
      );

	$element_attributes ="class:service_info_tab";
	$serviceTable = BaseHtmlLib::tableElement($element_attributes, $thead_data, $tbody_dataAr);

	$service_div->addChild($serviceTable);
    $service_data = $service_div->getHtml();

	return $service_data;
  }

 static public function displayServiceData(ADAGenericUser $UserObj, $service_infoAr=array(), $optionsAr=array()){

    $service_div = CDOMElement::create('div','id:service_info');

    $label_title = translateFN('Service');
    $label_level = translateFN('Level');
    $label_description = translateFN('Description');
    $label_service_time = translateFN('Open for');
    $label_service_min_meetings = translateFN('Min Meetings');
    $label_service_max_meetings = translateFN('Max meetings');
    $label_service_meeting_max_time = translateFN('Meetings duration');

	$overall_service_data = "";


//var_dump($service_infoAr);



	if (!AMA_DataHandler::isError($service_infoAr)){
	  	$service_title =  $service_infoAr[0];

	  	$service_level = level2stringFN($service_infoAr[2]);
        $service_description = $service_infoAr[1];
	  	// durata_servizio, min_incontri, max_incontri, durata_max_incontro
	  	$service_time =  $service_infoAr[3]." ".translateFN("days");
	  	$service_min_meetings =  $service_infoAr[4];
	  	$service_max_meetings =  $service_infoAr[5];
	  	$service_meeting_max_time =  $service_infoAr[6]." ".translateFN("min");

	  } else {
	  	$service_description = translateFN("Not available");
	  	$service_level = translateFN("?");
	  	$service_title =  translateFN("Not available");

	  }

	   $thead_data = array(
	      $label_title,
          $label_level,
        //  $label_description,
          $label_service_time,
          $label_service_min_meetings,
          $label_service_max_meetings,
          $label_service_meeting_max_time

	   );

     	$tbody_dataAr[] = array(
			          //$label_provider=>$tester_name,

			          $label_title =>$service_title,
			          $label_level=>$service_level,
			       //   $label_description=>nl2br($service_description),
			          $label_service_time => $service_time,
	  				  $label_service_min_meetings => $service_min_meetings,
	  				  $label_service_max_meetings => $service_max_meetings,
	  				  $label_service_meeting_max_time => $service_meeting_max_time,



			          );

    $serviceTable = BaseHtmlLib::tableElement($element_attributes, $thead_data, $tbody_dataAr);

	$service_div->addChild($serviceTable);
    $service_data = $service_div->getHtml();



	return $service_data;
	}
/*
static public function displayServiceImplementationData(ADAGenericUser $UserObj, $service_infoAr=array(), $optionsAr=array()){

    $label_title = translateFN('Titolo');
    $label_provider = translateFN('Erogatore');
    $label_provider_country = translateFN('Paese');
    $label_provider_city = translateFN('Città');
	$overall_service_data = "";


// var_dump($service_infoAr);



	if (!AMA_DataHandler::isError($service_infoAr)){
	  	$service_title =  $service_infoAr[0];
	  		  	// provider's infos
	  	$service_provider_name = $service_infoAr[7];
	  	$service_provider_country =$service_infoAr[8];
	  	$service_provider_city = $service_infoAr[9];
	  } else {
	  	$service_title =  translateFN("Servizio non disponibile");

	  }

       $row = array(
			          $label_provider=>$service_provider_name,
			          $label_provider_country=>$service_provider_country,
			          $label_provider_city=>$service_provider_city


			          );
  	  $impl_service_dataList = BaseHtmlLib::plainListElement("",$row);
      $impl_service_data = $impl_service_dataList->getHtml();



	return $impl_service_data;
	}
	*/
}

?>