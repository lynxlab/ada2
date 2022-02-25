<?php

require_once   ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
require_once   ROOT_DIR.'/include/HtmlLibrary/FormElementCreator.inc.php';
require_once ROOT_DIR . '/include/Layout.inc.php';

class AdminModuleHtmlLib
{
  static public function getAddUserForm($testersAr = array(), $user_dataAr = array(), $errorsAr = array()) {
    return self::getFormForUser('add_user.php',$testersAr, $user_dataAr, $errorsAr);

  }

  static public function getEditUserForm($testersAr = array(), $user_dataAr = array(), $errorsAr = array()) {

//    return self::getFormForUser('edit_user.php',$testersAr, $user_dataAr, $errorsAr);
    $form = CDOMElement::create('form','id:user_form, name:user_form, class:fec, method:post');
    $form->setAttribute('action', 'edit_user.php');

    if(is_array($errorsAr) && isset($errorsAr['registration_error'])) {
      switch($errorsAr['registration_error']) {
        case ADA_ADD_USER_ERROR:
        case ADA_ADD_USER_ERROR_TESTER:
          $error_message = translateFN("Si &egrave; verificato un errore nell'aggiunta dell'utente");
          break;

        case ADA_ADD_USER_ERROR_USER_EXISTS:
        case ADA_ADD_USER_ERROR_USER_EXISTS_TESTER:
          $error_message = translateFN("Esiste gi&agrave; un utente con la stessa email dell'utente che si sta cercando di aggiungere");
          break;

        case ADA_ADD_USER_ERROR_TESTER_ASSOCIATION:
          $error_message = translateFN("Si &egrave; verificato un errore durante l'associazione dell'utente al tester selezionato");
          break;
      }
      $error_div = CDOMElement::create('div','class:error');
      $error_div->addChild(new CText($error_message));
      $form->addChild($error_div);
    }

    if(is_array($user_dataAr) && isset($user_dataAr['user_id'])) {
      $user_id = CDOMElement::create('hidden','id:user_id, name:user_id');
      $user_id->setAttribute('value',$user_dataAr['user_id']);
      $form->addChild($user_id);
    }
/*
    $testers_dataAr = array();
    $testers_dataAr['none']=translateFN("--Scegli un tester da associare--");
    foreach($testersAr as $key => $value) {
      $testers_dataAr[$key] = $value;
    }

    if(is_array($testersAr) && count($testersAr) > 0) {
      $user_testers = FormElementCreator::addSelect('user_tester', "Tester a cui associare l'utente", $testers_dataAr, $user_dataAr, $errorsAr);
      $form->addChild($user_testers);
    }

    $user_typeAr = array(
      'none'                => translateFN("--Seleziona il tipo di utente--"),
      AMA_TYPE_STUDENT         => translateFN('Utente'),
      AMA_TYPE_TUTOR => translateFN('E-Practitioner'),
      AMA_TYPE_SWITCHER     => translateFN('Switcher'),
      AMA_TYPE_AUTHOR       => translateFN('Autore'),
      AMA_TYPE_ADMIN        => translateFN('Amministratore')
    );

    $user_type = FormElementCreator::addSelect('user_type', 'Tipo di utente', $user_typeAr, $user_dataAr, $errorsAr);
*/
    $user_type = CDOMElement::create('hidden','id:user_type, name:user_type');
    $user_type->setAttribute('value',$user_dataAr['user_type']);
    $form->addChild($user_type);

    $user_firstname = FormElementCreator::addTextInput('user_firstname','Nome',$user_dataAr, $errorsAr);
    $form->addChild($user_firstname);

    $user_lastname = FormElementCreator::addTextInput('user_lastname','Cognome',$user_dataAr, $errorsAr);
    $form->addChild($user_lastname);

    $user_email = FormElementCreator::addTextInput('user_email','E-mail',$user_dataAr, $errorsAr);
    $form->addChild($user_email);

    $user_username = FormElementCreator::addTextInput('user_username','Username (min. 8 caratteri)',$user_dataAr, $errorsAr);
    $form->addChild($user_username);

    $user_password = FormElementCreator::addPasswordInput('user_password','Password (min. 8 caratteri)', $errorsAr);
    $form->addChild($user_password);

    $user_passwordcheck = FormElementCreator::addPasswordInput('user_passwordcheck','Ripeti password', $errorsAr);
    $form->addChild($user_passwordcheck);

    if($user_dataAr['user_type'] == AMA_TYPE_TUTOR || $user_dataAr['user_type'] == AMA_TYPE_SWITCHER) {
      $user_profile = FormElementCreator::addTextArea('user_profile','Profilo',$user_dataAr, $errorsAr);
      $form->addChild($user_profile);
    }

    if (defined('MODULES_GDPR') && true === MODULES_GDPR && $user_dataAr['user_type'] == AMA_TYPE_SWITCHER) {
    	require_once MODULES_GDPR_PATH.'/include/GdprAPI.php';
    	$gdprAPI = new \Lynxlab\ADA\Module\GDPR\GdprAPI($user_dataAr['user_tester']);
    	// get all gdpr user types
    	$gdprUserTypes = $gdprAPI->getGdprUserTypes();
    	$gdprUserTypesArr = array_reduce($gdprUserTypes, function($carry, $item) {
    		if (is_null($carry)) $carry = array();
    		$carry[$item->getId()] = translateFN($item->getDescription());
    		return $carry;
    	});

    	if (count($gdprUserTypesArr)>0) {
	    	// get gdpr user object
	    	$gdprUser = $gdprAPI->getGdprUserByID($user_dataAr['user_id']);
	    	// properly set selected user gdpr type
	    	$gdprUserType = (false !== $gdprUser) ? $gdprUser->getType() : $gdprAPI->getGdprNoneUserTypes();
	    	// $gdprUserType is an array, user can be associated to more than one gdprtype
	    	// BUT as of 04/04/2018 only association with one type is permitted, so use reset below
	    	// to support multiple gdpr user types, a multi select is needed and saving implementations
	    	// goes into admin/edit_user.php file
	    	$user_gdpr = FormElementCreator::addSelect('user_gdpr', 'Ruolo GDPR ', $gdprUserTypesArr, array( 'user_gdpr' => intval(reset($gdprUserType)->getId())));
	    	$form->addChild($user_gdpr);
    	}
    }


    /*
    $layoutsAr = array(
      'none'         => translateFN('seleziona un layout'),
      'default'      => 'default',
      'masterstudio' => 'masterstudio'
    );
     */
    $layoutsAr = Layout::getLayouts();

    $user_layout = FormElementCreator::addSelect('user_layout', 'Layout', $layoutsAr, $user_dataAr);
    $form->addChild($user_layout);
    $user_address = FormElementCreator::addTextInput('user_address','Indirizzo', $user_dataAr, $errorsAr);
    $form->addChild($user_address);

    $user_city = FormElementCreator::addTextInput('user_city','Citt&agrave', $user_dataAr, $errorsAr);
    $form->addChild($user_city);

    $user_province = FormElementCreator::addTextInput('user_province','Provincia', $user_dataAr, $errorsAr);
    $form->addChild($user_province);

    $user_country = FormElementCreator::addTextInput('user_country','Nazione', $user_dataAr, $errorsAr);
    $form->addChild($user_country);

    $user_fiscal_code = FormElementCreator::addTextInput('user_fiscal_code','Codice Fiscale', $user_dataAr, $errorsAr);
    $form->addChild($user_fiscal_code);

    $user_birthdate = FormElementCreator::addDateInput('user_birthdate','Data di nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthdate);

    $user_birthcity = FormElementCreator::addTextInput('user_birthcity','Comune o stato estero di nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthcity);

    $user_birthprovince = FormElementCreator::addTextInput('user_birthprovince','Provincia di nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthprovince);

    $sexAr = array(
      'M' => 'M',
      'F' => 'F'
    );

    $user_sex = FormElementCreator::addSelect('user_sex', 'Sesso', $sexAr, $user_dataAr);
    $form->addChild($user_sex);

    $user_phone = FormElementCreator::addTextInput('user_phone','Telefono',$user_dataAr, $errorsAr);
    $form->addChild($user_phone);

    $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
    $form->addChild($buttons);
    return $form;
  }

  static public function getAddTesterForm($testersAr = array(), $tester_dataAr = array(), $errorsAr = array()) {
    return self::getFormForTester('add_tester.php',$testersAr, $tester_dataAr, $errorsAr);
  }

  static public function getEditTesterForm($testersAr = array(), $tester_dataAr = array(), $errorsAr = array()) {
    return self::getFormForTester('edit_tester.php',$testersAr, $tester_dataAr, $errorsAr);
  }

  static public function getAddServiceForm($testersAr = array(), $service_dataAr = array(), $errorsAr = array()) {
    return self::getFormForService('add_service.php',$testersAr, $service_dataAr, $errorsAr);
  }

  static public function getEditServiceForm($testersAr = array(), $service_dataAr = array(), $errorsAr = array()) {
    return self::getFormForService('edit_service.php',$testersAr, $service_dataAr, $errorsAr);
  }

  static public function getEditNewsForm($newsmsg, $fileToEdit, $reqType) {
    return self::getFormForNews('edit_content.php',$newsmsg, $fileToEdit, $reqType);
  }

  static public function getTestersActivityReport($testers_dataAr=array()) {
    $thead_dataAr = array(
      translateFN('Tester'),
      translateFN('Azioni'),
      translateFN('Utenti attivi'),
      translateFN('Sessioni attive')
    );
    $tbody_dataAr = array();
    foreach($testers_dataAr as $tester_dataAr) {
      $href = 'tester_profile.php?id_tester='.$tester_dataAr['id_tester'];
      $link = CDOMElement::create('a',"class:ui tiny button,href:$href");
      $link->addChild(new CText(translateFN("Profilo del tester")));
      $tbody_dataAr[] = array($tester_dataAr['nome'], $link, $tester_dataAr['numero_utenti'], $tester_dataAr['eg_attive']);
    }

    $table = BaseHtmlLib::tableElement('id:provider_profile,class:admin ui table',$thead_dataAr, $tbody_dataAr);
    return $table;
  }

  static public function createActionsMenu($menu_dataAr=array()) {
    $menu_entries = array();

    foreach($menu_dataAr as $menu_entryAr) {
      $link = CDOMElement::create('a');
      $link->setAttribute('href', $menu_entryAr['href']);
      $link->addChild(new CText($menu_entryAr['text']));
      $menu_entries[] = $link;
    }
    return BaseHtmlLib::plainListElement('',$menu_entries);
  }

  static public function displayTesterInfo($id_tester,$tester_dataAr = array()) {
    $div = CDOMElement::create('div','id:tester_info');
    // $div->addChild(new CText(translateFN('Informazioni sul tester')));

    $table = BaseHtmlLib::tableElement('',array(),$tester_dataAr);
    $link = CDOMElement::create('a','class:ui button,href:edit_tester.php?id_tester='.$id_tester);
    $link->addChild(new CText(translateFN('Modifica')));

    $div->addChild($table);
    $div->addChild($link);

    return $div;
  }

  static public function displayServicesOnThisTester($id_tester, $services_on_this_testerAr=array()) {
    $div = CDOMElement::create('div','id:tester_services');
    $div->addChild(new CText(translateFN('Lista dei servizi presenti sul tester')));

    $thead = array(
      translateFN('Id'),
      translateFN('Nome'),
//       translateFN('Descrizione'),
      translateFN('Livello'),
      translateFN('Durata'),
      translateFN('Min. incontri'),
      translateFN('Max incontri'),
      translateFN('Durata max incontro'),
//       translateFN('id provincia'),
      translateFN('id del corso')
    );

    /**
     * @author giorgio 14/mar/2016
     * remove descrizione field from $services_on_this_testerAr
     */
    array_walk ($services_on_this_testerAr, function (&$item) {
    	if (isset($item['descrizione'])) unset ($item['descrizione']);
    });

    $table = BaseHtmlLib::tableElement('',$thead,$services_on_this_testerAr);
    //$link = CDOMElement::create('a','href:manage_tester_services.php?id_tester='.$id_tester);
    //$link->addChild(new CText(translateFN('Associa/disassocia un servizio')));

    $div->addChild($table);
    //$div->addChild($link);
    return $div;
  }

  static public function displayUsersOnThisTester($id_tester, $current_page, $total_pages, $users_dataAr=array(), $withPaginator = true) {
    $div = CDOMElement::create('div');
    if ($withPaginator) {
      $pages = CDOMElement::create('div','id:pages');
      $pages->addChild(new CText('|'));
      for($i = 1; $i <= $total_pages; $i++) {
        if($i == $current_page) {
          $pages->addChild(new CText(" $current_page |"));
        }
        else {
          $link = CDOMElement::create('a',"href:list_users.php?id_tester=$id_tester&page=$i");
          $link->addChild(new CText(" $i "));
          $pages->addChild($link);
          $pages->addChild(new CText('|'));
        }
      }

      $div->addChild($pages);
    }

    $thead_dataAr = array(
      translateFN('Id'),
      translateFN('Nome'),
      translateFN('Cognome'),
      translateFN('E-mail'),
      translateFN('username'),
      translateFN('Tipo'),
      translateFN('Azioni')
    );

    $tbody_dataAr = array();
    foreach($users_dataAr as $user_dataAr) {
      $user_type = ADAGenericUser::convertUserTypeFN($user_dataAr['tipo']);
      $href = 'edit_user.php?id_user='.$user_dataAr['id_utente'].'&id_tester='.$id_tester.'&page='.$current_page;
      $edit_user_link = CDOMElement::create('a',"class:ui tiny button,href:$href");
      $edit_user_link->addChild(new CText(translateFN('Modifica')));
      $tbody_dataAr[] = array(
        $user_dataAr['id_utente'],
        $user_dataAr['nome'],
        $user_dataAr['cognome'],
        $user_dataAr['e_mail'],
        $user_dataAr['username'],
        $user_type,
        $edit_user_link
      );

      if (defined('MODULES_GDPR') && true === MODULES_GDPR && isset($_GET['user_type']) && DataValidator::is_uinteger($_GET['user_type']) == AMA_TYPE_SWITCHER) {
      	require_once MODULES_GDPR_PATH.'/include/GdprAPI.php';
      	if (!isset($gdprApi)) {
      		$tester_info = $GLOBALS['common_dh']->get_tester_info_from_id($id_tester);
      		$gdprAPI = new \Lynxlab\ADA\Module\GDPR\GdprAPI($tester_info[10]);
      		$gdprNoneTypes = $gdprAPI->getGdprNoneUserTypes();
      	}
      	$gdprUser = $gdprAPI->getGdprUserByID($user_dataAr['id_utente']);
      	$gdprUserTypes = (false !== $gdprUser) ? $gdprUser->getType() : $gdprNoneTypes;
      	$gdpr_type = implode(', ', array_map(function($el) { return translateFN($el->getDescription()); }, $gdprUserTypes));
      	$row = array_pop($tbody_dataAr);
		// add gdpr types string before actions column
      	array_splice($row, count($row)-1, 0, array ($gdpr_type));
      	if (count($thead_dataAr) < count($row)) {
      		array_splice($thead_dataAr, count($thead_dataAr)-1, 0, array(translateFN('Ruolo GDPR')));
      	}
      	$tbody_dataAr[] = $row;
      }
    }

    $table = BaseHtmlLib::tableElement('id:admin_list_users,class:ui table',$thead_dataAr,$tbody_dataAr);
    $div->addChild($table);

    return $div;
  }

  static private function getFormForUser($form_action, $testersAr = array(), $user_dataAr = array(), $errorsAr = array()) {
    $form = CDOMElement::create('form','id:user_form, name:user_form, class:fec, method:post');
    $form->setAttribute('action', $form_action);

    if(is_array($errorsAr) && isset($errorsAr['registration_error'])) {
      switch($errorsAr['registration_error']) {
        case ADA_ADD_USER_ERROR:
        case ADA_ADD_USER_ERROR_TESTER:
          $error_message = translateFN("Si &egrave; verificato un errore nell'aggiunta dell'utente");
          break;

        case ADA_ADD_USER_ERROR_USER_EXISTS:
        case ADA_ADD_USER_ERROR_USER_EXISTS_TESTER:
          $error_message = translateFN("Esiste gi&agrave; un utente con la stessa email dell'utente che si sta cercando di aggiungere");
          break;

        case ADA_ADD_USER_ERROR_TESTER_ASSOCIATION:
          $error_message = translateFN("Si &egrave; verificato un errore durante l'associazione dell'utente al tester selezionato");
          break;
      }
      $error_div = CDOMElement::create('div','class:error');
      $error_div->addChild(new CText($error_message));
      $form->addChild($error_div);
    }

    if(is_array($user_dataAr) && isset($user_dataAr['user_id'])) {
      $user_id = CDOMElement::create('hidden','id:user_id, name:user_id');
      $user_id->setAttribute('value',$user_dataAr['user_id']);
      $form->addChild($user_id);
    }

    $testers_dataAr = array();
    $testers_dataAr['none']=translateFN("--Scegli un tester da associare--");
    foreach($testersAr as $key => $value) {
      $testers_dataAr[$key] = $value;
    }

    if(is_array($testersAr) && count($testersAr) > 0) {
      $user_testers = FormElementCreator::addSelect('user_tester', "Tester a cui associare l'utente", $testers_dataAr, $user_dataAr, $errorsAr);
      $form->addChild($user_testers);
    }

    $user_typeAr = array(
      'none'                => translateFN("--Seleziona il tipo di utente--"),
      AMA_TYPE_STUDENT      => translateFN('Utente'),
      AMA_TYPE_TUTOR        => translateFN('Tutor'),
      AMA_TYPE_SUPERTUTOR   => translateFN('Super Tutor'),
      AMA_TYPE_SWITCHER     => translateFN('Switcher'),
      AMA_TYPE_AUTHOR       => translateFN('Autore'),
      AMA_TYPE_ADMIN        => translateFN('Amministratore')
    );

    $user_type = FormElementCreator::addSelect('user_type', 'Tipo di utente', $user_typeAr, $user_dataAr, $errorsAr);
    $form->addChild($user_type);

    $user_firstname = FormElementCreator::addTextInput('user_firstname','Nome',$user_dataAr, $errorsAr);
    $form->addChild($user_firstname);

    $user_lastname = FormElementCreator::addTextInput('user_lastname','Cognome',$user_dataAr, $errorsAr);
    $form->addChild($user_lastname);

    $user_email = FormElementCreator::addTextInput('user_email','E-mail',$user_dataAr, $errorsAr);
    $form->addChild($user_email);

    $user_username = FormElementCreator::addTextInput('user_username','Username (min. 8 caratteri)',$user_dataAr, $errorsAr);
    $form->addChild($user_username);

    $user_password = FormElementCreator::addPasswordInput('user_password','Password (min. 8 caratteri)', $errorsAr);
    $form->addChild($user_password);

    $user_passwordcheck = FormElementCreator::addPasswordInput('user_passwordcheck','Ripeti password', $errorsAr);
    $form->addChild($user_passwordcheck);
/*
    $layoutsAr = array(
      'none'         => translateFN('seleziona un layout'),
      'default'      => 'default',
      'masterstudio' => 'masterstudio'
    );
 *
 */



    $layoutsAr = array(
      'none'         => translateFN('seleziona un layout')
    );
    $layoutObj = new UILayout();
    $availableLayout = $layoutObj->getAvailableLayouts();

    foreach ($availableLayout as $familyLayoutIdentifier=>$familyLayoutValue){
        $layoutsAr[$familyLayoutIdentifier] = $familyLayoutValue;
    }




    $user_layout = FormElementCreator::addSelect('user_layout', 'Layout', $layoutsAr, $user_dataAr);
    $form->addChild($user_layout);
    $user_address = FormElementCreator::addTextInput('user_address','Indirizzo', $user_dataAr, $errorsAr);
    $form->addChild($user_address);

    $user_city = FormElementCreator::addTextInput('user_city','Citt&agrave', $user_dataAr, $errorsAr);
    $form->addChild($user_city);

    $user_province = FormElementCreator::addTextInput('user_province','Provincia', $user_dataAr, $errorsAr);
    $form->addChild($user_province);

    $user_country = FormElementCreator::addTextInput('user_country','Nazione', $user_dataAr, $errorsAr);
    $form->addChild($user_country);

    $user_fiscal_code = FormElementCreator::addTextInput('user_fiscal_code','Codice Fiscale', $user_dataAr, $errorsAr);
    $form->addChild($user_fiscal_code);

    $user_birthdate = FormElementCreator::addDateInput('user_birthdate','Data di Nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthdate);

    $user_birthcity = FormElementCreator::addTextInput('user_birthcity','Comune o stato estero di nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthcity);

    $user_birthprovince = FormElementCreator::addTextInput('user_birthprovince','Provincia di nascita', $user_dataAr, $errorsAr);
    $form->addChild($user_birthprovince);

    $sexAr = array(
      'M' => 'M',
      'F' => 'F'
    );

    $user_sex = FormElementCreator::addSelect('user_sex', 'Sesso', $sexAr, $user_dataAr);
    $form->addChild($user_sex);

    $user_phone = FormElementCreator::addTextInput('user_phone','Telefono',$user_dataAr, $errorsAr);
    $form->addChild($user_phone);

    $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
    $form->addChild($buttons);
    return $form;
  }

  static private function getFormForTester($form_action, $testersAr = array(), $tester_dataAr = array(), $errorsAr = array()) {
    $form = CDOMElement::create('form','id:tester_form, name:tester_form, method:post, class:fec');
    $form->setAttribute('action', $form_action);

    if(is_array($tester_dataAr) && isset($tester_dataAr['tester_id'])) {
      $tester_id = CDOMElement::create('hidden','id:tester_id, name:tester_id');
      $tester_id->setAttribute('value',$tester_dataAr['tester_id']);
      $form->addChild($tester_id);
    }

    $tester_name = FormElementCreator::addTextInput('tester_name','Nome',$tester_dataAr, $errorsAr);
    $form->addChild($tester_name);

    $tester_rs = FormElementCreator::addTextInput('tester_rs','Ragione sociale',$tester_dataAr, $errorsAr);
    $form->addChild($tester_rs);

    $tester_address = FormElementCreator::addTextInput('tester_address','Indirizzo',$tester_dataAr, $errorsAr);
    $form->addChild($tester_address);

    $tester_province = FormElementCreator::addTextInput('tester_province','Provincia',$tester_dataAr, $errorsAr);
    $form->addChild($tester_province);

    $tester_city = FormElementCreator::addTextInput('tester_city','Citt&agrave',$tester_dataAr, $errorsAr);
    $form->addChild($tester_city);

    $tester_country = FormElementCreator::addTextInput('tester_country','Nazione',$tester_dataAr, $errorsAr);
    $form->addChild($tester_country);

    $tester_phone = FormElementCreator::addTextInput('tester_phone','Telefono',$tester_dataAr, $errorsAr);
    $form->addChild($tester_phone);

    $tester_email = FormElementCreator::addTextInput('tester_email','E-mail',$tester_dataAr, $errorsAr);
    $form->addChild($tester_email);

    $tester_desc = FormElementCreator::addTextArea('tester_desc','Descrizione',$tester_dataAr, $errorsAr);
    $form->addChild($tester_desc);

    $tester_resp = FormElementCreator::addTextInput('tester_resp','Responsabile',$tester_dataAr, $errorsAr);
    $form->addChild($tester_resp);

    $tester_iban = FormElementCreator::addTextInput('tester_iban','IBAN',$tester_dataAr, $errorsAr);
    $form->addChild($tester_iban);

    $tester_pointer = FormElementCreator::addTextInput('tester_pointer','Puntatore al database',$tester_dataAr, $errorsAr);
    $form->addChild($tester_pointer);


    $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
    $form->addChild($buttons);
    return $form;
  }

  static private function getFormForService($form_action, $testersAr = array(), $service_dataAr = array(), $errorsAr = array()) {
    $form = CDOMElement::create('form','id:service_form, name:service_form, method:post');
    $form->setAttribute('action', $form_action);

    $service_levelAr = array(
      1 => translateFN('Servizio pubblico'),
      2 => translateFN('Servizio di secondo livello'),
      3 => translateFN('Servizio di terzo livello'),
      4 => translateFN('Servizio di quarto livello'),
    );

    if(is_array($service_dataAr) && isset($service_dataAr['service_id'])) {
      $service_id = CDOMElement::create('hidden','id:service_id, name:service_id');
      $service_id->setAttribute('value',$service_dataAr['service_id']);
      $form->addChild($service_id);
    }

    $service_name = FormElementCreator::addTextInput('service_name','Nome',$service_dataAr, $errorsAr);
    $form->addChild($service_name);

    $service_description = FormElementCreator::addTextArea('service_description','Descrizione',$service_dataAr, $errorsAr);
    $form->addChild($service_description);

    $service_level = FormElementCreator::addSelect('service_level','Livello',$service_levelAr,$service_dataAr);
    $form->addChild($service_level);

    $service_duration = FormElementCreator::addTextInput('service_duration','Durata in giorni',$service_dataAr, $errorsAr);
    $form->addChild($service_duration);

    $service_min_meetings = FormElementCreator::addTextInput('service_min_meetings','Numero minimo di incontri',$service_dataAr, $errorsAr);
    $form->addChild($service_min_meetings);

    $service_max_meetings = FormElementCreator::addTextInput('service_max_meetings','Numero massimo di incontri',$service_dataAr, $errorsAr);
    $form->addChild($service_max_meetings);

    $service_meeting_duration = FormElementCreator::addTextInput('service_meeting_duration','Durata massima di un incontro (in minuti)',$service_dataAr, $errorsAr);
    $form->addChild($service_meeting_duration);

    $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
    $form->addChild($buttons);
    return $form;
  }

  /*
   * getFormForIportLanguage:
   */
    static public function getFormImportLanguage($form_action, $testersAr = array(), $errorsAr = array()) {
    $form = CDOMElement::create('form','id:import_lang_form, name:import_lang_form, method:post');
    $form->setAttribute('action', $form_action);

    if(is_array($errorsAr) && isset($errorsAr['imported'])) {
      $error_message = $errorsAr['imported'];
      $error_div = CDOMElement::create('div','class:error');
      $error_div->addChild(new CText($error_message));
      $form->addChild($error_div);
    }

    if(is_array($errorsAr) && isset($errorsAr['lang_tester'])) {
      $error_message = 'Error in choosen tester';
      $error_div = CDOMElement::create('div','class:error');
      $error_div->addChild(new CText($error_message));
      $form->addChild($error_div);
    }

    $testers_dataAr = array();
    $testers_dataAr['none'] = '--Choose a tester in which import--';
    $testers_dataAr['all']  = 'All Tester';
    foreach($testersAr as $key => $value) {
      $testers_dataAr[$key] = $value;
    }
    $valueAr['lang_tester']="none";
    if(is_array($testersAr) && count($testersAr) > 0) {
      $lang_testers = FormElementCreator::addSelect('lang_tester', "Tester in which import", $testers_dataAr,  $valueAr, $errorsAr);
      $form->addChild($lang_testers);
    }

    $languageAr = array(
      'none' => ('--Choose the language--'),
      'it'   => 'Italian',
      'es'   => 'Spanish',
      'is'   => 'Icelandic',
      'en'   => 'English',
      'ro'   => 'Romanian',
      'bg'   => 'Bulgarian'
    );

    if(is_array($testersAr) && count($testersAr) > 0) {
      $lang_testers = FormElementCreator::addSelect('language', "language", $languageAr,  "", $errorsAr);
      $form->addChild($lang_testers);
    }

	$fileAr = array();
	$fileAr['none']= '--Choose the file--';
    $file_data = read_dir("../db/messaggi","xml");
    /*
    foreach($file_data as $key => $value) {
      $fileAr[$key] = $value;
    }
    */
	for ($i = 0; $i < sizeof($file_data); ++$i) {
		$fileAr[$file_data[$i]['path_to_file']] = $file_data[$i]['file'];
	}


    $file_lang = FormElementCreator::addSelect('file_lang', 'File to import', $fileAr, $errorsAr);
    $form->addChild($file_lang);

	$delete_messagesAr = array(
      'no'  => 'No',
      'yes' => 'Yes'
	);
	$value_selectAr['delete_messages'] = 'no';
	$delete_messages = FormElementCreator::addSelect('delete_messages', 'Delete before insert?', $delete_messagesAr, $value_selectAr, $errorsAr);
    $form->addChild($delete_messages);

	$delete_sistemaAr = array(
      'no'  => 'No',
      'yes' => 'Yes'
	);
	$value_selectAr['delete_sistema'] = "no";
	$delete_sistema = FormElementCreator::addSelect('delete_sistema', 'Delete system message?', $delete_sistemaAr, $value_selectAr, $errorsAr);
    $form->addChild($delete_sistema);


    $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
    $form->addChild($buttons);
    return $form;
  }

  /*
   * getFormForNews:
   */
    static public function getFormForNews($form_action,$newsmsg, $file, $type) {
        $form = CDOMElement::create('form','id:edit_news, name:edit_news, method:post');
        $form->setAttribute('action', $form_action);

        $newsEditText = FormElementCreator::addTextArea($type, $type, $newsmsg);
        $form->addChild($newsEditText);
        $file_edit = CDOMElement::create('hidden','id:file_edit, name:file_edit');
        $file_edit->setAttribute('value',$file);
        $form->addChild($file_edit);

        $reqTypeForm = CDOMElement::create('hidden','id:reqType, name:type');
        $reqTypeForm->setAttribute('value', $type);
        $form->addChild ($reqTypeForm);

        $buttons = FormElementCreator::addSubmitAndResetButtons('ui green button','ui red button');
        $form->addChild($buttons);

        return $form;
  }

}
?>