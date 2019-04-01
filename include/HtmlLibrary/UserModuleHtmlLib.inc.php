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
require_once ROOT_DIR . '/include/HtmlLibrary/FormElementCreator.inc.php';

class UserModuleHtmlLib {

  /*
   * CALLED BY index.php
   */
  public static function loginForm($form_action = HTTP_ROOT_DIR, $supported_languages=array(), $login_page_language_code, $login_error_message='') {

    $div = CDOMElement::create('div','id:login_div');
    $form = CDOMElement::create('form',"id:login_form, name:login_form, method:post, action:$form_action");

    $div_username = CDOMElement::create('div','id:username');
    $span_label_uname = CDOMElement::create('span','id:label_uname, class:page_text');
    $label_uname      = CDOMElement::create('label','for:p_username');
    $label_uname->addChild(new CText(translateFN('Username')));
    $span_label_uname->addChild($label_uname);
    $span_username = CDOMElement::create('span','id:span_username, class:page_input');
    $username_input= CDOMElement::create('text','id:p_username, name:p_username');
    $span_username->addChild($username_input);

    $div_username->addChild($span_label_uname);
    $div_username->addChild($span_username);

    $div_password = CDOMElement::create('div','id:password');
    $span_label_pwd = CDOMElement::create('span','id:label_pwd, class:page_text');
    $label_pwd      = CDOMElement::create('label','for:p_password');
    $label_pwd->addChild(new CText(translateFN('Password')));
    $span_label_pwd->addChild($label_pwd);
    $span_password = CDOMElement::create('span','id:span_password, class:page_input');
    $password_input= CDOMElement::create('password','id:p_password, name:p_password');
    $span_password->addChild($password_input);

    $div_password->addChild($span_label_pwd);
    $div_password->addChild($span_password);

    $div_remindme = CDOMElement::create('div','id:remindme');
    $span_label_remindme = CDOMElement::create('span','id:label_remindme, class:page_text');
    $label_remindme = CDOMElement::create('label','for:p_remindme');
    $label_remindme->addChild(new CText(translateFN('Resta collegato')));
    $span_label_remindme->addChild($label_remindme);
    $span_remindme = CDOMElement::create('span','id:span_remindme, class:page_input');
    $remindme_input = CDOMElement::create('checkbox','id:p_remindme,name:p_remindme,value:1');
    $span_remindme->addChild($remindme_input);
    $div_remindme->addChild($span_remindme);
    $div_remindme->addChild($span_label_remindme);

    $div_select = CDOMElement::create('div','id:language_selection');
    $select = CDOMElement::create('select','id:p_selected_language, name:p_selected_language');
    foreach($supported_languages as $language)
    {
      $option = CDOMElement::create('option',"value:{$language['codice_lingua']}");
      if ($language['codice_lingua'] == $login_page_language_code)
      {
        $option->setAttribute('selected','selected');
      }
      $option->addChild(new CText($language['nome_lingua']));
      $select->addChild($option);
    }
    $div_select->addChild($select);

    $div_submit = CDOMElement::create('div','id:login_button');
    if (defined('MODULES_LOGIN') && MODULES_LOGIN) {
    	// load login providers
    	require_once MODULES_LOGIN_PATH . '/include/abstractLogin.class.inc.php';
    	$loginProviders = abstractLogin::getLoginProviders();
    } else $loginProviders = null;

    if (!AMA_DB::isError($loginProviders) && is_array($loginProviders) && count($loginProviders)>0) {
    	$submit = CDOMElement::create('div','id:loginProviders');
    	$form->addChild(CDOMElement::create('hidden','id:selectedLoginProvider, name:selectedLoginProvider'));
    	$form->addChild(CDOMElement::create('hidden','id:selectedLoginProviderID, name:selectedLoginProviderID'));
    	// add a DOM element (or html) foreach loginProvider
    	foreach ($loginProviders as $providerID=>$loginProvider) {
    		include_once  MODULES_LOGIN_PATH . '/include/'.$loginProvider.'.class.inc.php';
    		if (class_exists($loginProvider)) {
    			$loginObject = new $loginProvider($providerID);
    			$CDOMElement = $loginObject->getCDOMElement();
    			if (!is_null($CDOMElement)) {
    				$submit->addChild($CDOMElement);
    			} else {
 					$htmlString  = $loginObject->getHtml();
 					if (!is_null($htmlString)) {
 						$submit->addChild(new CText($htmlString));
 					}
    			}
    		}
    	}
    } else {
    	// standard submit button if no MODULES_LOGIN
    	$value      = translateFN('Accedi');
    	$submit     = CDOMElement::create('submit',"id:p_login, name:p_login");
    	$submit->setAttribute('value' ,$value);
    }

    $div_submit->addChild($submit);

    $form->addChild($div_username);
    $form->addChild($div_password);
    $form->addChild($div_remindme);
    $form->addChild($div_select);

    if ($login_error_message != '') {
      $div_error_message = CDOMElement::create('div','id:login_error_message, class:error');
      $div_error_message->addChild(new CText($login_error_message));
      $form->addChild($div_error_message);
    }
    $form->addChild($div_submit);

    if (isset($_REQUEST['r']) && strlen(trim($_REQUEST['r']))>0) {
      $form->addChild(CDOMElement::create('hidden','name:r,value:'.trim($_REQUEST['r'])));
    }

    $div->addChild($form);
    return $div;
  }

  /*
   * CALLED BY registration.php
   */
  public static function loginRegistrationForm($supported_languages=array(), $login_page_language_code, $login_error_message='',$id_course) {
    $form_action = $GLOBALS['http_root_dir'].'/browsing/registration.php';

    $div = CDOMElement::create('div','id:login_div');
    $form = CDOMElement::create('form',"id:login_form, name:login_form, method:post, action:$form_action");

    $div_username = CDOMElement::create('div','id:username');
    $span_label_uname = CDOMElement::create('span','id:label_uname, class:page_text');
    $label_uname      = CDOMElement::create('label','for:p_username');
    $label_uname->addChild(new CText(translateFN('Username')));
    $span_label_uname->addChild($label_uname);
    $span_username = CDOMElement::create('span','id:span_username, class:page_input');
    $username_input= CDOMElement::create('text','id:p_username, name:p_username');
    $span_username->addChild($username_input);

    $div_username->addChild($span_label_uname);
    $div_username->addChild($span_username);

    $div_password = CDOMElement::create('div','id:password');
    $span_label_pwd = CDOMElement::create('span','id:label_pwd, class:page_text');
    $label_pwd      = CDOMElement::create('label','for:p_password');
    $label_pwd->addChild(new CText(translateFN('Password')));
    $span_label_pwd->addChild($label_pwd);
    $span_password = CDOMElement::create('span','id:span_password, class:page_input');
    $password_input= CDOMElement::create('password','id:p_password, name:p_password');
    $span_password->addChild($password_input);

    $div_password->addChild($span_label_pwd);
    $div_password->addChild($span_password);
    //id_course
    $div_id_course = CDOMElement::create('div','id:id_course');
    $id_course_input= CDOMElement::create('hidden','id:p_id_course, name:id_course, value:'.$id_course);

    $div_username->addChild($id_course_input);

    $div_select = CDOMElement::create('div','id:language_selection');
    $select = CDOMElement::create('select','id:p_selected_language, name:p_selected_language');
    foreach($supported_languages as $language)
    {
      $option = CDOMElement::create('option',"value:{$language['codice_lingua']}");
      if ($language['codice_lingua'] == $login_page_language_code)
      {
        $option->setAttribute('selected','selected');
      }
      $option->addChild(new CText($language['nome_lingua']));
      $select->addChild($option);
    }
    $div_select->addChild($select);

    $div_submit = CDOMElement::create('div','id:login_button');
    $value      = translateFN('Richiedi');
    $submit     = CDOMElement::create('submit',"id:p_login, name:p_login, value:$value");
    $div_submit->addChild($submit);

    $form->addChild($div_username);
    $form->addChild($div_password);
    $form->addChild($div_id_course);
    $form->addChild($div_select);

    if ($login_error_message != '') {
      $div_error_message = CDOMElement::create('div','id:login_error_message, class:error');
      $div_error_message->addChild(new CText($login_error_message));
      $form->addChild($div_error_message);
    }
    $form->addChild($div_submit);

    $div->addChild($form);
    return $div;
  }


  public static function rightMenu() {

    $div = CDOMElement::create('div','id:right_menu, class:menu');

    $ul = CDOMElement::create('ul', 'id:menu');

    $courses = CDOMElement::create('li','id:courses');
    $courses_link = CDOMElement::create('a', 'href:info.php');
    $courses_link->addChild(new CText(translateFN('Elenco corsi')));
    $courses->addChild($courses_link);

    $subscribe = CDOMElement::create('li','id:subscribe');
    $subscribe_link = CDOMElement::create('a', 'href:browsing/registration.php');
    $subscribe_link->addChild(new CText(translateFN('Registrazione')));
    $subscribe->addChild($subscribe_link);

    $ul->addChild($courses);
    $ul->addChild($subscribe);
    $div->addChild($ul);

    return $div;
  }

  /*
   * CALLED BY user/translation.php
   */

  /**
   * function translationSearchForm(): returns the html needed to display the translation search form.
   *
   * @param array $supported_languages - an array containing a list of supported languages
   * @return string - the html for the form
   */
  public static function translationSearchForm($supported_languages=array()) {

    $search_form = CDOMElement::create('div', 'id:translation_search');
    $form_action = $GLOBALS['http_root_dir'].'/switcher/translation.php?op=search';
    $form = CDOMElement::create('form', "id:translation_search_form, action:$form_action, method:post");

    $span_label = CDOMElement::create('span','id:translation_search_label, class:form_label');
    $label      = CDOMElement::create('label', 'for:post_translation_search_select_language, class:form_label');
    $label->addChild(new CText(translateFN('Cerca nella traduzione')));
    $span_label->addChild($label);

    $span_select = CDOMElement::create('span','id:translation_search_select, class:form_input');
    $select      = self::translationSelectSupportedLanguages('translation_search_select',$supported_languages);
    $span_select->addChild($select);

    $span_textinput = CDOMElement::create('span','id:translation_search_textinput, class:form_input');
    $textinput      = CDOMElement::create('text','name:post_translation_search_text');
    $span_textinput->addChild($textinput);

    $span_submit  = CDOMElement::create('span','translation_search_button, class:form_input');
    $submit_value = translateFN("Cerca");
    $submit       = CDOMElement::create('submit',"name:post_translation_search_button, value:$submit_value");
    $span_submit->addChild($submit);

    $form->addChild($span_label);
    $form->addChild($span_select);
    $form->addChild($span_textinput);
    $form->addChild($span_submit);
    $search_form->addChild($form);

    return $search_form;
  }

  public static function translationSelectSupportedLanguages($container,$supported_languages=array()) {
    $select_name = "post_".$container."_language";
    $select_id   = "post_".$container."_language";

    $select_languages = CDOMElement::create('select',"name:$select_name, id:$select_id");
    foreach($supported_languages as $language) {
      $option = CDOMElement::create('option',"value:{$language['codice_lingua']}");
      $option->addChild(new CText($language['nome_lingua']));
      $select_languages->addChild($option);
    }

    return $select_languages;
  }

  /**
   * function translationImportSystemMessagesForm
   *
   * @param string $form_action
   * @param array  $supported_languages
   * @return string $html - the html for this form
   */
  public static function translationImportSystemMessagesForm($form_action, $supported_languages=array()) {

    $div = CDOMElement::create('div', 'id:translation_import');

    $div_text = CDOMElement::create('div', 'id:translation_import_text, class:page_text');
    $message1 = translateFN('&Egrave; possibile importare un file CSV (delimitato da tab) contenente i messaggi tradotti per una traduzione presente in ADA.');
    $message2 = translateFN('Selezionare il nome della traduzione e procedere all\'upload del file corrispondente.');
    $div_text->addChild(new CText($message1));
    $div_text->addChild(new CText($message2));

    $form = CDOMElement::create('form', "id:translation_import_form, name:translation_import_form, enctype: multipart/form-data, action : $form_action, method:post");
    $label = CDOMElement::create('label','for:select_language');
    $label->addChild(new CText(translateFN("Scegli la lingua della traduzione:")));

    $span_language_select = CDOMElement::create('span','id:translation_import_select, class:form_input');
    $select = self::translationSelectSupportedLanguages('translation_import_select',$supported_languages);
    $span_language_select->addChild($select);

    $div_upload = CDOMElement::create('div','id:translation_import_select, class:form_input');
    $label_select = CDOMElement::create('label','for:upload_file');
    $label_select->addChild(new CText(translateFN('Scegli il file contenente la traduzione:')));
    $fileinput = CDOMElement::create('file','id:post_translation_import_file, name:post_translation_import_file');

    $div_upload->addChild($label_select);
    $div_upload->addChild($fileinput);

    $div_submit = CDOMElement::create('div','id:translation_import_submit, class:form_input');
    $value      = translateFN("Invia file");
    $submit     = CDOMElement::create('submit',"id:post_translation_upload_button, name:post_translation_upload_button, value:$value");
    $div_submit->addChild($submit);

    $form->addChild($label);
    $form->addChild($span_language_select);
    $form->addChild($div_upload);
    $form->addChild($div_submit);

    $div->addChild($div_text);
    $div->addChild($form);

    return $div;
  }


  /**
   * function translationExportSystemMessagesLink
   *
   * @return string $html - the html string for this element
   */
  public static function translationExportSystemMessagesLink() {

    $div = CDOMElement::create('div', 'id:translation_export');

    $span_text = CDOMElement::create('span','id:translation_export_text, class: page_text');
    $text = translateFN('Per esportare i messaggi di sistema come file excel, in modo da poter effettuare pi&ugrave; agevolmente la traduzione, clicca il link seguente.');
    $span_text->addChild(new CText($text));

    $span_link = CDOMElement::create('span','id:translation_export_link, class:page_link');
    $a_href    = $GLOBALS['http_root_dir'].'/switcher/translation.php?op=export';
    $link      = CDOMElement::create('a',"href:$a_href");
    $link->addChild(new CText(translateFN('Esporta')));
    $span_link->addChild($link);

    $div->addChild($span_text);
    $div->addChild($span_link);

    return $div;
  }

  /**
   * function translationTranslatedMessageEditForm
   *
   * @param array  $message_data  - an array containing id and text for the edited message
   * @param string $language_name -
   * @param string $message_language_code - ISO 639-1 language code (e.g. 'it' for 'italian')
   * @return string $html - the html code for the form
   */
  public static function translationTranslatedMessageEditForm($message_data, $message_language_code, $search_text) {
    $div_edit = CDOMElement::create('div','id:translation_edit');

    $span = CDOMElement::create('span', 'id:go_back_link, class:page_link');
    /*
     * since the superglobal array $_GET is already decoded,
     * recall urlencode on $search_text
     */
    $get_q = urlencode($search_text);
    $a_href = $GLOBALS['http_root_dir'].'/switcher/translation.php?op=search&get_q='.$get_q
            . '&get_code='.$message_language_code;
    $a = CDOMElement::create('a', "href: $a_href");
    $a->addChild(new CText(translateFN('Torna alla pagina precedente')));
    $span->addChild($a);
    $div_edit->addChild($span);

    $form_action = $GLOBALS['http_root_dir'].'/switcher/translation.php?op=update';
    $form = CDOMElement::create('form',"name:edit_form, action: $form_action, method:post");

    $div_textarea = CDOMElement::create('div','id:div_post_translation_edit_textarea');
    $textarea     = CDOMElement::create('textarea','id   : post_translation_edit_textarea,
                                                    name : post_translation_edit_textarea');
    $textarea->addChild(new CText($message_data[1]));
    $div_textarea->addChild($textarea);

    $hidden = CDOMElement::create('hidden',"id:post_message_id, name:post_message_id, value:{$message_data[0]}");

    $hidden2 = CDOMElement::create('hidden',"id: post_message_language_code, name: post_message_language_code, value:$message_language_code");

    $div_submit = CDOMElement::create('div', 'id:translation_edit_button');
    $value      = translateFN("Aggiorna traduzione");
    $submit     = CDOMElement::create('submit',"id:post_translation_edit_button, name:post_translation_edit_button, value:$value");
    $div_submit->addChild($submit);
    $form->addChild($div_textarea);
    $form->addChild($hidden);
    $form->addChild($hidden2);
    $form->addChild($div_submit);

    $div_edit->addChild($form);
    return $div_edit;
  }

  public static function translationFoundMessagesList($found_messages, $message_language_code, $translation_search_text) {
    $found_messages_list = CDOMElement::create('div');

    $span = CDOMElement::create('span', 'id:go_back_link, class:page_link');
    $a_href = $GLOBALS['http_root_dir'].'/switcher/translation.php';
    $a = CDOMElement::create('a', "href: $a_href");
    $a->addChild(new CText(translateFN('Torna alla pagina precedente')));
    $span->addChild($a);

    $div = CDOMElement::create('div','id:translation_search_results_list');
    $list = self::translationFoundMessagesHtmlList($found_messages, $message_language_code, 'translation_search_results_list', $translation_search_text);
    $div->addChild($list);

    $found_messages_list->addChild($span);
    $found_messages_list->addChild($div);

    return $found_messages_list;
  }

  public static function translationFoundMessagesHtmlList($found_messages = array(), $language_code, $container_id, $translation_search_text) {

    $list = CDOMElement::create('ul');

    foreach ($found_messages as $message) {
      $message_text = urlencode($message['testo_messaggio']);
      $search_text  = urlencode($translation_search_text);

      $li = CDOMElement::create('li');

      $span_text = CDOMElement::create('span');
      $span_text->addChild(new CText($message['testo_messaggio']));

      $span_edit = CDOMElement::create('span');
      $a_href = $GLOBALS['http_root_dir'].'/switcher/translation.php?op=edit&get_id='.$message['id_messaggio']
      .'&get_text='.$message_text.'&get_code='.$language_code.'&get_q='.$search_text;

      $a = CDOMElement::create('a',"href:$a_href");
      $a->addChild(new CText(translateFN("Modifica")));
      $span_edit->addChild($a);

      $li->addChild($span_text);
      $li->addChild($span_edit);
      $list->addChild($li);
    }

    return $list;
  }

  public static function uploadForm($action, $id_user, $id_course, $id_course_instance, $id_node, $error_message = null) {

    $div  = CDOMElement::create('div');

    if($error_message !== null) {
      $div_error = CDOMElement::create('div', 'class:error_field');
      $div_error->addChild(new CText($error_message));
      $div->addChild($div_error);
    }

    $form = CDOMElement::create('form', "id:upload_form, name: upload_form, action:$action, method:post");
    $form->setAttribute('onsubmit', 'return checkNec();');
    $form->setAttribute('enctype', 'multipart/form-data');

    $sender = CDOMElement::create('hidden',"id:sender, name:sender, value:$id_user");
    $id_course = CDOMElement::create('hidden', "id:id_course, name:id_course, value:$id_course");
    $id_course_instance = CDOMElement::create('hidden', "id:id_course_instance, name:id_course_instance, value:$id_course_instance");
    $id_node = CDOMElement::create('hidden', "id:id_node, name:id_node, value:$id_node");

    $input_file    = CDOMElement::create('file','id:file_up, name:file_up');
    $copyright_yes = CDOMElement::create('radio','id:copyright, name:copyright, value:1');
    $copyright_no  = CDOMElement::create('radio','id:copyright, name:copyright, value:0');
    $div_copyright = CDOMElement::create('div');
    $div_copyright->addChild($copyright_yes);
    $div_copyright->addChild(new CText(translateFN('Si')));
    $div_copyright->addChild($copyright_no);
    $div_copyright->addChild(new CText(translateFN('No')));

    $submit_text = translateFN('Invia');
    $submit = CDOMElement::create('submit', "id:submit, name:submit, value:$submit_text");
    $reset  = CDOMElement::create('reset','id:reset, name:reset');
    $buttons_div = CDOMElement::create('div');
    $buttons_div->addChild($submit);
    $buttons_div->addChild($reset);

    //<div id='cfl' title='sender,id_course,id_course_instance,id_node'>

    $form->addChild($sender);
    $form->addChild($id_course);
    $form->addChild($id_course_instance);
    $form->addChild($id_node);

    $table_data = array(
      array(translateFN('File da inviare'), $input_file),
      array(translateFN('Copyright'), $div_copyright),
      array($buttons_div, null)
    );

    $form->addChild(BaseHtmlLib::tableElement('class:upload',null, $table_data));

    $div->addChild($form);
    return $div;
  }

  static public function getExternalLinkNavigationFrame($address) {
     $iframe = CDOMElement::create('iframe');
     $iframe->setAttribute('src', $address);
     $iframe->setAttribute('id', 'external_link_browsing');
     return $iframe;
  }

  static public function getEditPractitionerForm($user_dataAr = array(), $errorsAr = array()) {
    return self::getEditUserForm('edit_tutor.php', $user_dataAr, $errorsAr);
  }

  static public function getEditSwitcherForm($user_dataAr = array(), $errorsAr = array()) {
    return self::getEditUserForm('edit_switcher.php', $user_dataAr, $errorsAr);
  }

  static public function getEditAuthorForm($user_dataAr = array(), $errorsAr = array()) {
    return self::getEditUserForm('edit_author.php', $user_dataAr, $errorsAr);
  }

  static private function getEditUserForm($action, $user_dataAr = array(), $errorsAr = array()) {

    $form = CDOMElement::create('form','id:user_form, name:user_form, class:fec, method:post');
    $form->setAttribute('action', $action);

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

    $user_type = CDOMElement::create('hidden','id:user_type, name:user_type');
    $user_type->setAttribute('value',$user_dataAr['user_type']);
    $form->addChild($user_type);

    $user_firstname = FormElementCreator::addTextInput('user_firstname','Nome',$user_dataAr, $errorsAr,'',true);
    $form->addChild($user_firstname);

    $user_lastname = FormElementCreator::addTextInput('user_lastname','Cognome',$user_dataAr, $errorsAr,'',true);
    $form->addChild($user_lastname);

    $user_email = FormElementCreator::addTextInput('user_email','E-mail',$user_dataAr, $errorsAr,'',true);
    $form->addChild($user_email);

//    $user_username = FormElementCreator::addTextInput('user_username','Username (min. 8 caratteri)',$user_dataAr, $errorsAr);
//    $form->addChild($user_username);

    $user_password = FormElementCreator::addPasswordInput('user_password','Password (min. 8 caratteri)', $errorsAr);
    $form->addChild($user_password);

    $user_passwordcheck = FormElementCreator::addPasswordInput('user_passwordcheck','Ripeti password', $errorsAr);
    $form->addChild($user_passwordcheck);

    if($user_dataAr['user_type'] == AMA_TYPE_TUTOR || $user_dataAr['user_type'] == AMA_TYPE_SWITCHER) {
      $user_profile = FormElementCreator::addTextArea('user_profile','Profilo',$user_dataAr, $errorsAr);
      $form->addChild($user_profile);
    }

//    $layoutsAr = Layout::getLayouts();
//    $user_layout = FormElementCreator::addSelect('user_layout', 'Layout', $layoutsAr, $user_dataAr);
//    $form->addChild($user_layout);

    $user_address = FormElementCreator::addTextInput('user_address','Indirizzo', $user_dataAr, $errorsAr);
    $form->addChild($user_address);

    $user_city = FormElementCreator::addTextInput('user_city','Citt&agrave;', $user_dataAr, $errorsAr);
    $form->addChild($user_city);

    $user_province = FormElementCreator::addTextInput('user_province','Provincia', $user_dataAr, $errorsAr);
    $form->addChild($user_province);

    $user_country = FormElementCreator::addTextInput('user_country','Nazione', $user_dataAr, $errorsAr);
    $form->addChild($user_country);

    $user_fiscal_code = FormElementCreator::addTextInput('user_fiscal_code','Codice Fiscale', $user_dataAr, $errorsAr);
    $form->addChild($user_fiscal_code);

    $user_age = FormElementCreator::addTextInput('user_age','Et&agrave;', $user_dataAr, $errorsAr);
    $form->addChild($user_age);

    $sexAr = array(
      'M' => 'M',
      'F' => 'F'
    );

    $user_sex = FormElementCreator::addSelect('user_sex', 'Sesso', $sexAr, $user_dataAr);
    $form->addChild($user_sex);

    $user_phone = FormElementCreator::addTextInput('user_phone','Telefono',$user_dataAr, $errorsAr);
    $form->addChild($user_phone);

    $buttons = FormElementCreator::addSubmitAndResetButtons();
    $form->addChild($buttons);
    return $form;
  }
}
?>