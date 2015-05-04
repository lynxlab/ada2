<?php
class ADAAddressBook
{
  static protected function fillAddressBook(ADALoggableUser $userObj, $user_types_Ar = array()) {
    $user_type = $userObj->getType();
    $common_dh = $GLOBALS['common_dh'];
    $dh = $GLOBALS['dh'];
    
    // this tells get_users_by_type method to get nome, cognome....
    $retrieve_extended_data = true;

    if(!is_array($user_types_Ar[$user_type]) || empty($user_types_Ar[$user_type])) {
      return FALSE;
    }


    switch($user_type) {

      case AMA_TYPE_ADMIN:
        /*
         * Ottieni tutti i practitioner, gli autori e gli switcher da tutti i
         * tester
         */
        // FIXME: differisce dagli altri casi !!!
        $users[] = $common_dh->get_users_by_type($user_types_Ar[AMA_TYPE_ADMIN], $retrieve_extended_data);
        if(AMA_Common_DataHandler::isError($users)) {
          // Gestione errore
        }

        break;

      case AMA_TYPE_SWITCHER:
        /*
         * Ottieni tutti i practitioner e gli utenti dal suo tester
         */
        $tester = $userObj->getDefaultTester();
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        $tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
        $tester_name = $tester_info_Ar[1];
        
        $users[$tester_name] = $tester_dh->get_users_by_type($user_types_Ar[AMA_TYPE_SWITCHER],$retrieve_extended_data);
        if(AMA_Common_DataHandler::isError($users)) {
        	$users[$tester_name]=array();
        }
        /*
         * Ottiene tutti i practitioner presenti sul tester
         */
//         $practitioners_Ar = $tester_dh->get_users_by_type(array(AMA_TYPE_TUTOR), $retrieve_extended_data);
//         if(AMA_DataHandler::isError($practitioners_Ar) || !is_array($practitioners_Ar)) {
//           $practitioners_Ar = array();
//         }
        /*
         * Ottiene tutti gli utenti che hanno richiesto un servizio sul tester
         * e che sono in attesa di assegnamento ad un practitioner
         */
        // $users_Ar = $tester_dh->get_registered_students_without_tutor();
//         if(AMA_DataHandler::isError($users_Ar) || !is_array($users_Ar)) {
//           $users_Ar = array();
//         }
//         $users[$tester_name] = array_merge($practitioners_Ar, $users_Ar);
        break;

      case AMA_TYPE_TUTOR:
        /*
         * Ottieni lo switcher del suo tester, gli utenti con i quali è in relazione,
         * eventualmente gli altri practitioner sul suo tester
         */
        $tester = $userObj->getDefaultTester();
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        $tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
        $tester_name = $tester_info_Ar[1];
        
        if (in_array(AMA_TYPE_STUDENT, $user_types_Ar[$user_type])) {
	        /*
	         * STUDENTS
	         */
	
//        $users[$tester_name] = $tester_dh->get_list_of_tutored_users($userObj->id_user);
			if (!$userObj->isSuper()) {
		        $students_Ar = $tester_dh->get_list_of_tutored_unique_users($userObj->id_user);				
			} else {
				$students_Ar = $tester_dh->get_users_by_type(array(AMA_TYPE_STUDENT), $retrieve_extended_data);
			}
//        $users[$tester_name] = $tester_dh->get_users_by_type($user_types_Ar[AMA_TYPE_TUTOR], $retrieve_extended_data);
	        if(AMA_DataHandler::isError($students_Ar) || !is_array($students_Ar)) {
	          $students_Ar = array();
	        }
        } else $students_Ar = array();

        if (in_array(AMA_TYPE_TUTOR, $user_types_Ar[$user_type])) {
	        /*
	         * TUTORS
	         */
	
	        $tutors_Ar =  $tester_dh->get_users_by_type(array(AMA_TYPE_TUTOR), $retrieve_extended_data);
	        if(AMA_DataHandler::isError($tutors_Ar) || !is_array($tutors_Ar)) {
	          $tutors_Ar = array();
	        }
        } else $tutors_Ar = array();

        if (in_array(AMA_TYPE_SWITCHER, $user_types_Ar[$user_type])) {
	        /*
	         * SWITCHERS
	         */
	
	        $switchers_Ar =  $tester_dh->get_users_by_type(array(AMA_TYPE_SWITCHER), $retrieve_extended_data);
	        if(AMA_DataHandler::isError($switchers_Ar) || !is_array($switchers_Ar)) {
	          $switchers_Ar = array();
	        }        
        } else $switchers_Ar = array();
        
        $users[$tester_name] = array_merge($tutors_Ar, $students_Ar, $switchers_Ar);


        break;


      case AMA_TYPE_STUDENT:
        /*
         * Se sono all'interno di un tester, vedo solo i practitioner di questo
         * tester con i quali sono in relazione
         * Se sono nella home dell'utente, vedo tutti i practitioner di tutti i
         * tester con i quali sono in relazione
         *
         * Come faccio a capirlo qui? posso Verificare che sess_selected_tester == ADA_DEFAULT_TESTER
         */
        if(MultiPort::isUserBrowsingThePublicTester()) {
         // home di user o navigazione nei contenuti pubblici
          $testers = $userObj->getTesters();
          foreach($userObj->getTesters() as $tester) {
            if(($tester != ADA_PUBLIC_TESTER) OR count($testers) == 1) {
              $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
              $tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
              $tester_name = $tester_info_Ar[1];

              $tutors_Ar = $tester_dh->get_tutors_for_student($userObj->getId());
              if(AMA_DataHandler::isError($tutors_Ar) || !is_array($tutors_Ar)) {
               $tutors_Ar = array();
              }
              $tutors_Ar = array_unique($tutors_Ar, SORT_REGULAR);

              $switcher_Ar =  $tester_dh->get_users_by_type(array(AMA_TYPE_SWITCHER), $retrieve_extended_data);
              if(AMA_DataHandler::isError($switcher_Ar) || !is_array($switcher_Ar)) {
               $switcher_Ar = array();
              }

              /*
               * OTHER STUDENTS RELATED TO USER
               */
              $subscribed_instances = $tester_dh->get_id_course_instances_for_this_student($userObj->getId());
              $students_Ar = $tester_dh->get_unique_students_for_course_instances($subscribed_instances);
              if(AMA_DataHandler::isError($students_Ar) || !is_array($students_Ar)) {
                  $students_Ar = array();
              }

/*
              foreach ($subscribed_instances as $subscribed_instance) {
                  $subscribed_instance_id = $subscribed_instance['id_istanza_corso'];
                  $students_Ar = array_merge($tester_dh->get_students_for_course_instance($subscribed_instance_id));
              }
 *
 */
              $users[$tester_name] = array_merge($tutors_Ar, $switcher_Ar, $students_Ar);
            }
          }
        }
        else {
          $tester = $_SESSION['sess_selected_tester'];
          $tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
          $tester_name = $tester_info_Ar[1];
          $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));


          /*
           * GET TUTORS OF TESTER
           */

          $tutors_Ar = $tester_dh->get_tutors_for_student($userObj->getId());
          if(AMA_DataHandler::isError($tutors_Ar) || !is_array($tutors_Ar)) {
            $tutors_Ar = array();
          }
          
          $tutors_Ar = array_unique($tutors_Ar, SORT_REGULAR);

          /*
           * GET SWITCHER OF TESTER
           */

          $switcher_Ar =  $tester_dh->get_users_by_type(array(AMA_TYPE_SWITCHER), $retrieve_extended_data);
          if(AMA_DataHandler::isError($switcher_Ar) || !is_array($switcher_Ar)) {
           $switcher_Ar = array();
          }

          /*
           * OTHER STUDENTS RELATED TO USER
           */
          $subscribed_instances = $tester_dh->get_id_course_instances_for_this_student($userObj->getId());
          $students_Ar = $tester_dh->get_unique_students_for_course_instances($subscribed_instances);
          if(AMA_DataHandler::isError($students_Ar) || !is_array($students_Ar)) {
              $students_Ar = array();
          }

          $users[$tester_name] = array_merge($tutors_Ar, $switcher_Ar, $students_Ar);

        }
        break;

      case AMA_TYPE_AUTHOR:
      default:
        return FALSE;
    }
    return $users;
  }



  static protected function getAddressBook(ADALoggableUser $userObj, $user_types_Ar = array(), $result_Ar = array()) {
    $user_type = $userObj->getType();

    $address_book = CDOMElement::create('div','id:addressbook_div');

    $buttons = CDOMElement::create('div','id:buttons_div');

//    $users_Ar = array();
//    foreach($result as $tester => $users) {
//      $users_Ar[$tester][$users['tipo']] = array($users['e_mail'], $users['username']);
//    }

    $selects = CDOMElement::create('div');

    if(in_array(AMA_TYPE_SWITCHER, $user_types_Ar[$user_type])) {
      $switcher_bt = CDOMElement::create('a','id:js_switcher_bt, name:js_switcher_bt');
      $switcher_bt->setAttribute('onclick',"showMeHideOthers('js_switcher_sel');");
      $switcher_bt->addChild(new CText(translateFN('Switcher')));
      $buttons->addChild($switcher_bt);

      $switcher_sel = CDOMElement::create('select', 'id:js_switcher_sel, name:js_switcher_sel, size:10, class: hidden_element');
      $switcher_sel->setAttribute('onchange','add_addressee(this);');

      foreach($result_Ar as $tester_name => $user_data_Ar) {

        $optgroup = CDOMElement::create('optgroup');
        $optgroup->setAttribute('label', $tester_name);
        foreach($user_data_Ar as $user) {
          if($user['tipo'] == AMA_TYPE_SWITCHER) {
            $option = CDOMElement::create('option','value:'.$user['username']);
            if (isset($user['cognome']) || isset($user['nome'])) $displayname = $user['cognome'].' '.$user['nome'];
            else $displayname = $user['username']; 
            $option->addChild(new CText($displayname));
            $optgroup->addChild($option);
          }
        }

        $switcher_sel->addChild($optgroup);
      }

      $selects->addChild($switcher_sel);
    }

    if(in_array(AMA_TYPE_TUTOR, $user_types_Ar[$user_type])) {
      $practitioner_bt = CDOMElement::create('a','id:js_practitioner_bt, name:js_practitioner_bt');
      $practitioner_bt->setAttribute('onclick',"showMeHideOthers('js_practitioner_sel');");

      $practitioner_bt->addChild(new CText(translateFN('Tutor')));
      $buttons->addChild($practitioner_bt);

      $practitioner_sel = CDOMElement::create('select', 'id:js_practitioner_sel, name: js_practitioner_sel, size:10, class: hidden_element');
      $practitioner_sel->setAttribute('onchange','add_addressee(this);');
      foreach($result_Ar as $tester_name => $user_data_Ar) {

       $optgroup = CDOMElement::create('optgroup');
       $optgroup->setAttribute('label', $tester_name);
       foreach($user_data_Ar as $user) {
          if($user['tipo'] == AMA_TYPE_TUTOR) {
            $option = CDOMElement::create('option','value:'.$user['username']);
            if (isset($user['cognome']) || isset($user['nome'])) $displayname = $user['cognome'].' '.$user['nome'];
            else $displayname = $user['username']; 
            $option->addChild(new CText($displayname));
            $optgroup->addChild($option);
          }
        }

        $practitioner_sel->addChild($optgroup);
      }

      $selects->addChild($practitioner_sel);

    }

    if(in_array(AMA_TYPE_STUDENT, $user_types_Ar[$user_type])) {
      $user_bt = CDOMElement::create('a','id:js_user_bt, name:js_user_bt');
      $user_bt->setAttribute('onclick',"showMeHideOthers('js_user_sel');");
      $user_bt->addChild(new CText(translateFN('Students')));
      $buttons->addChild($user_bt);

      $user_sel = CDOMElement::create('select', 'id:js_user_sel, name: js_user_sel, size:10, class: hidden_element');
      $user_sel->setAttribute('onchange','add_addressee(this);');
      foreach($result_Ar as $tester => $user_data_Ar) {

        $optgroup = CDOMElement::create('optgroup');
        $optgroup->setAttribute('label', $tester_name);
        foreach($user_data_Ar as $user) {
         /**
           * @author giorgio 28/apr/2015
           * 
           * tutors are students for an ADA_SERVICE_TUTORCOMMUNITY type of course,
           * so add them to the address book if they're returned in the $result_Ar
           */
          if($user['tipo'] == AMA_TYPE_STUDENT || 
          	($user['tipo'] == AMA_TYPE_TUTOR && !$userObj->isSuper() && $user['id_utente']!=$userObj->getId())) {
            $option = CDOMElement::create('option','value:'.$user['username']);
            if (isset($user['cognome']) || isset($user['nome'])) $displayname = $user['cognome'].' '.$user['nome'];
            else $displayname = $user['username']; 
            $option->addChild(new CText($displayname));
            $optgroup->addChild($option);
          }
        }

        $user_sel->addChild($optgroup);
      }

      $selects->addChild($user_sel);
    }


    $address_book->addChild($buttons);
    $address_book->addChild($selects);
    return $address_book;
  }
}

class EventsAddressBook extends ADAAddressBook
{
  static public function create(ADALoggableUser $userObj) {

    $user_types_Ar = array(
      AMA_TYPE_TUTOR => array(AMA_TYPE_STUDENT,AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER),
      AMA_TYPE_SWITCHER    => array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT),
      AMA_TYPE_STUDENT => array(AMA_TYPE_TUTOR,AMA_TYPE_STUDENT)
    );

    $users_Ar = parent::fillAddressBook($userObj, $user_types_Ar);
    if($users_Ar == FALSE) {
      return new CText('');
    }
    return parent::getAddressBook($userObj, $user_types_Ar, $users_Ar);
  }
}

class MessagesAddressBook extends ADAAddressBook
{
  static public function create(ADALoggableUser $userObj) {

    $user_types_Ar = array(
      AMA_TYPE_ADMIN       => array(AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER),
      AMA_TYPE_SWITCHER    => array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT),
      AMA_TYPE_AUTHOR      => array(),
      AMA_TYPE_TUTOR 	   => array(AMA_TYPE_SWITCHER, AMA_TYPE_STUDENT),
      AMA_TYPE_STUDENT     => array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR)
    );
    /**
     * @author giorgio 13/apr/2015
     * 
     * if userObj is a superTutor, add AMA_TYPE_TUTOR to the addressbook
     */
    if ($userObj->getType()==AMA_TYPE_TUTOR && $userObj->isSuper()) {
    	$user_types_Ar[AMA_TYPE_TUTOR][] = AMA_TYPE_TUTOR;
    }

    $users_Ar = parent::fillAddressBook($userObj,$user_types_Ar);
    if($users_Ar == FALSE) {
      return new CText('');
    }
    return parent::getAddressBook($userObj, $user_types_Ar, $users_Ar);
  }
}