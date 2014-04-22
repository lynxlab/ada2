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
require_once ROOT_DIR .'/include/HtmlLibrary/BaseHtmlLib.inc.php';
require_once ROOT_DIR . '/comunica/include/ADAEventProposal.inc.php';

class CommunicationModuleHtmlLib
{

// MARK: Chat
  static public function getChat($data='', ADALoggableUser $userObj, $event_token) {

    $html = CDOMElement::create('div','id:chat');

    $top  = CDOMElement::create('div','id:top');
    $top->addChild(CDOMElement::create('div','id:chatroom_banner'));
    $top->addChild(CDOMElement::create('div','id:chatroom_info'));

    /*
     * middle
     */
    $middle = CDOMElement::create('div','id:middle');

    $chat_control = CDOMElement::create('div','id:chat_control');
    $refresh_button = CDOMElement::create('input_button','id:refresh_chat, name:refresh_chat');
    $refresh_button->setAttribute('onclick', 'refreshChat();');
    $refresh_button->setAttribute('value',translateFN('Aggiorna'));
    $chat_control->addChild($refresh_button);
    //$scroll_label = CDOMElement::create('label','for:autoscroll');
    //$scroll_label->addChild(new CText(translateFN('Scroll automatico')));
    //$chat_control->addChild($scroll_label);
    //$scroll_checkbox = CDOMElement::create('checkbox','id:autoscroll, name:autoscroll, value:on');
    //$chat_control->addChild($scroll_checkbox);

    $middle->addChild($chat_control);
    $middle->addChild(CDOMElement::create('div','id:messages'));

    $controlchat = CDOMElement::create('div', 'id:controlchat');
    $controlchat->addChild(CDOMElement::create('div','id:user_status'));
    //$controlchat->addChild(CDOMElement::create('div','id:user_actions'));
    $controlchat->addChild(CDOMElement::create('div','id:users_list'));
    //$controlchat->addChild(CDOMElement::create('ul','id:invited_users_list'));
    //$control_action = CDOMElement::create('div','id:control_action');
    //$control_input = CDOMElement::create('input_button','id:user_action, name:user_action');
    //$control_input->setAttribute('value',translateFN('Esegui'));
    //$control_input->setAttribute('onclick','executeControlAction();');
    //$control_action->addChild($control_input);
    //$controlchat->addChild($control_input);
    $middle->addChild($controlchat);

    /*
     * bottom
     */
    $bottom = CDOMElement::create('div','id:bottom');
    $sendmessage = CDOMElement::create('div', 'id:sendmessage');
    $text_input = CDOMElement::create('text','id:chatmessage, name:chatmessage, size:50');
    $text_input->setAttribute('onkeydown','catchEnter(event);');
    $button = CDOMElement::create('input_button', 'name:sendchatmessage');
    $button->setAttribute('value',translateFN('Invia messaggio'));
    $button->setAttribute('onclick','sendMessage();');
    $sendmessage->addChild($text_input);
    $sendmessage->addChild($button);
    $bottom->addChild($sendmessage);


    $exitchat = CDOMElement::create('div','id:exitchat');
    $exit_button = CDOMElement::create('input_button', 'name:exitchat');
    $exit_button->setAttribute('value',translateFN('Esci dalla chat'));

    if($userObj instanceof ADAPractitioner) {
      $onclick = "exitChat(1,'event_token=$event_token');";
      $exit_button->setAttribute('onclick',$onclick);
    }
    else {
      $exit_button->setAttribute('onclick','exitChat(0,0);');
    }

    $exitchat->addChild($exit_button);
    $bottom->addChild($exitchat);

    $html->addChild($top);
    $html->addChild($middle);
    $html->addChild($bottom);

    $html->addChild(CDOMElement::create('div','id:debug'));
    $args = CDOMElement::create('div','id:data');
    $args->addChild(new CText($data));
    $html->addChild($args);

    return $html;
  }
// MARK: Events
/*
 * Methods used to display Events user interfaces
 */
  static public function getEventProposalForm($id_user, $data = array(), $errors = array(), $tester = NULL) {

    $error_messages = array(
      ADA_EVENT_PROPOSAL_ERROR_DATE_FORMAT      => translateFN('Attenzione: il formato della data non è corretto.'),
      ADA_EVENT_PROPOSAL_ERROR_DATE_IN_THE_PAST => translateFN("Attenzione: la data e l'ora proposte per l'appuntamento sono antecedenti a quelle attuali."),
      ADA_EVENT_PROPOSAL_ERROR_DATE_IN_USE      => translateFN('Attenzione: è già presente un appuntamento in questa data e ora'),
      ADA_EVENT_PROPOSAL_ERROR_SUBJECT          => translateFN('The given event subject is not valid.')
    );

    if(isset($data['testo'])) {
      //$regexp  = '/<date>([0-9]{2}\/[0-9]{2}\/[0-9]{4})<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>/';
//      $regexp  = '/<date>([0-9\/]+)<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>/';
//      preg_match_all($regexp, $data['testo'], $matches);
//      $dates = $matches[1];
//      $times = $matches[2];
      $datetimesAr = ADAEventProposal::extractDateTimesFromEventProposalText($data['testo']);

//      $regexp = '/<notes>(.*)<\/notes>/';
//      preg_match($regexp, $data['testo'], $matches);
//      $practitioner_notes = $matches[1];
      $practitioner_notes = ADAEventProposal::extractNotesFromEventProposalText($data['testo']);

//      $regexp = '/<id_course_instance>(.*)<\/id_course_instance>/';
//      preg_match($regexp, $data['testo'], $matches);
//      $course_instance = $matches[1];
      $course_instance = ADAEventProposal::extractIdCourseInstanceFromEventProposalText($data['testo']);

      $modify = TRUE;
    }
    else {
      $modify = FALSE;
    }

    $form = CDOMElement::create('form','id:send_event_proposal_form, name:send_event_proposal_form, action:send_event_proposal.php, method:post');

    $idcourseinstance = CDOMElement::create('hidden','id:id_course_instance, name:id_course_instance');
    if($modify) {
      $idcourseinstance->setAttribute('value', $course_instance);
    }
    else {
      $idcourseinstance->setAttribute('value', $_GET['id_course_instance']);
    }

    $form->addChild($idcourseinstance);

    $tutoredUserObj = MultiPort::findUser($id_user);
    $tutored_user_info = CDOMElement::create('div','class:proposal_title');
    $tutored_user_info->addChild(new CText(sprintf(translateFN("Proposta di appuntamento per l'utente: %s"), $tutoredUserObj->nome .' '.$tutoredUserObj->cognome)));
    $form->addChild($tutored_user_info);

    $subject  = CDOMElement::create('div', 'class:proposal_title');

//    if($modify) {
//      $event_title = ADAEventProposal::removeEventToken($data['titolo']);
//      $subject->addChild(new CText($event_title));
//      $input = CDOMElement::create('hidden','id:subject, name:subject');
//      $input->setAttribute('value',$data['titolo']);
//    }
//    else {
//      if(is_array($errors) && isset($errors['subject'])) {
//        $subject_error = CDOMElement::create('div','class:error');
//        $subject_error->addChild(new CText($error_messages[$errors['subject']]));
//        $subject->addChild($subject_error);
//      }
//      $input = CDOMElement::create('text','id:subject, name:subject,maxlength:255, size:60');
//    }

    if(is_array($errors) && isset($errors['subject'])) {
      $subject_error = CDOMElement::create('div','class:error');
      $subject_error->addChild(new CText($error_messages[$errors['subject']]));
      $subject->addChild($subject_error);
      $subject->addChild(new CText(translateFN('Oggetto')));
      $input = CDOMElement::create('text','id:subject, name:subject,maxlength:255, size:60');
    }
    else if($modify) {
      $event_title = ADAEventProposal::removeEventToken($data['titolo']);
      $subject->addChild(new CText(translateFN('Oggetto')));
      $subject->addChild(new CText($event_title));
      $input = CDOMElement::create('hidden','id:subject, name:subject');
      $input->setAttribute('value',$data['titolo']);
    }
    else {
      $subject->addChild(new CText(translateFN('Oggetto')));
      $input = CDOMElement::create('text','id:subject, name:subject,maxlength:255, size:60');
    }

    $subject->addChild($input);

    $offset = 0;
    if ($tester === NULL) {
      $tester_TimeZone = SERVER_TIMEZONE;
    } else {
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
    }
	$now = time() + $offset;
    $zone = translateFN("Time zone:") . " " . $tester_TimeZone . " " . translateFN("actual time: ") . ts2tmFN($now);
    $timezone = CDOMElement::create('div','class:proposal_zone');
    $timezone->addChild(new CText($zone));

    $type = CDOMElement::create('div','class:proposal_type');
    $select = CDOMElement::create('select','id:type, name:type');
    $option1 = CDOMElement::create('option','value:'.ADA_CHAT_EVENT);
    $option1->addChild(new CText(translateFN('Appuntamento in chat')));
    if($modify && ($data['flags'] & ADA_CHAT_EVENT)) {
      $option1->setAttribute('selected','selected');
    }
    $option2 = CDOMElement::create('option','value:'.ADA_VIDEOCHAT_EVENT);
    $option2->addChild(new CText(translateFN('Appuntamento in videochat')));
    if($modify && ($data['flags'] & ADA_VIDEOCHAT_EVENT)) {
      $option2->setAttribute('selected','selected');
    }
    $option3 = CDOMElement::create('option','value:'.ADA_PHONE_EVENT);
    $option3->addChild(new CText(translateFN('Appuntamento telefonico')));
    if($modify && ($data['flags'] & ADA_PHONE_EVENT)) {
      $option3->setAttribute('selected','selected');
    }
    $option4 = CDOMElement::create('option','value:'.ADA_IN_PLACE_EVENT);
    $option4->addChild(new CText(translateFN('Appuntamento in presenza')));
    if($modify && ($data['flags'] & ADA_IN_PLACE_EVENT)) {
      $option4->setAttribute('selected','selected');
    }
    $select->addChild($option1);
    $select->addChild($option2);
    $select->addChild($option3);
    $select->addChild($option4);
    $type->addChild(new CText(translateFN('Tipo di appuntamento')));
    $type->addChild($select);

    $date1  = CDOMElement::create('div','class:proposed_date');
    if(is_array($errors) && isset($errors['date1'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date1']]));
      $date1->addChild($date_error);
    }
    $input1 = CDOMElement::create('text','id:date1, name:date1,maxlength:10, size:10,  class:date_input');
    if($modify) {
      $input1->setAttribute('value', $datetimesAr[0]['date']);
      $time1 = self::getEventProposalFormHoursSelect('time1', $datetimesAr[0]['time']);
    }
    else {
      $time1 = self::getEventProposalFormHoursSelect('time1');
    }
    $date1->addChild(new CText(translateFN('Prima proposta in data (dd/mm/yyyy)')));
    $date1->addChild($input1);


    $calendar_icon = CDOMElement::create('img','src:img/cal.png');
    $calendar_icon->setAttribute('alt', translateFN('Scegli una data'));

    $calendar1 = CDOMElement::create('a');
    $calendar1->setAttribute('href',"javascript:show_calendar('document.send_event_proposal_form.date1', document.send_event_proposal_form.date1.value);");
    $calendar1->addChild($calendar_icon);//new CText(translateFN('Scegli')));
    $date1->addChild($calendar1);

    $date1->addChild(new CText(translateFN('alle ore')));
    $date1->addChild($time1);

    $date2  = CDOMElement::create('div','class:proposed_date');
    if(is_array($errors) && isset($errors['date2'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date2']]));
      $date2->addChild($date_error);
    }
    $input2 = CDOMElement::create('text','id:date2, name:date2,maxlength:10, size:10, class:date_input');
    if($modify) {
      $input2->setAttribute('value', $datetimesAr[1]['date']);
      $time2 = self::getEventProposalFormHoursSelect('time2', $datetimesAr[1]['time']);
    }
    else {
      $time2 = self::getEventProposalFormHoursSelect('time2');
    }
    $date2->addChild(new CText(translateFN('Seconda proposta in data (dd/mm/yyyy)')));
    $date2->addChild($input2);

    $calendar2 = CDOMElement::create('a');
    $calendar2->setAttribute('href',"javascript:show_calendar('document.send_event_proposal_form.date2', document.send_event_proposal_form.date2.value);");
    $calendar2->addChild($calendar_icon);//new CText(translateFN('Scegli')));
    $date2->addChild($calendar2);

    $date2->addChild(new CText(translateFN('alle ore')));
    $date2->addChild($time2);

    $date3  = CDOMElement::create('div','class:proposed_date');
    if(is_array($errors) && isset($errors['date3'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date3']]));
      $date3->addChild($date_error);
    }
    $input3 = CDOMElement::create('text','id:date3, name:date3,maxlength:10, size:10, class:date_input');
    if($modify) {
      $input3->setAttribute('value', $datetimesAr[2]['date']);
      $time3 = self::getEventProposalFormHoursSelect('time3', $datetimesAr[2]['time']);
    }
    else {
      $time3 = self::getEventProposalFormHoursSelect('time3');
    }
    $date3->addChild(new CText(translateFN('Terza proposta in data (dd/mm/yyyy)')));
    $date3->addChild($input3);

    $calendar3 = CDOMElement::create('a');
    $calendar3->setAttribute('href',"javascript:show_calendar('document.send_event_proposal_form.date3', document.send_event_proposal_form.date3.value);");
    $calendar3->addChild($calendar_icon);//new CText(translateFN('Scegli')));
    $date3->addChild($calendar3);

    $date3->addChild(new CText(translateFN('alle ore')));
    $date3->addChild($time3);

    $notes  = CDOMElement::create('div');
    $input4 = CDOMElement::create('textarea','id:notes, name:notes');
    if($modify) {
      $input4->addChild(new CText($practitioner_notes));
    }
    $notes->addChild(new CText(translateFN('Note').'<br />'));
    $notes->addChild($input4);

    $user_id = CDOMElement::create('hidden', 'name:id_user, value:'.$id_user);

    $buttons = CDOMElement::create('div','id:buttons');
    $submit  = CDOMElement::create('submit','id:submit,name:submit, value:'.translateFN('Invia'));
    $reset   = CDOMElement::create('reset');
    $buttons->addChild($submit);
    $buttons->addChild($reset);

    $form->addChild($subject);
    $form->addChild($type);
    $form->addChild($timezone);
    $form->addChild($date1);
    $form->addChild($date2);
    $form->addChild($date3);
    $form->addChild($notes);
    $form->addChild($user_id);
    $form->addChild($buttons);

    return $form;
  }

  static public function getProposedEventForm($data=array(), $errors=array(), $tester = NULL) {

    $error_messages = array(
      ADA_EVENT_PROPOSAL_ERROR_DATE_FORMAT      => translateFN("Attenzione: il formato della data non &egrave; corretto."),
      ADA_EVENT_PROPOSAL_ERROR_DATE_IN_THE_PAST => translateFN("Attenzione: la data e l'ora proposte per l'appuntamento sono antecedenti a quelle attuali."),
      ADA_EVENT_PROPOSAL_ERROR_DATE_IN_USE      => translateFN("Attenzione: &egrave; gi&agrave; presente un appuntamento in questa data e ora")
    );

//    $regexp  = '/<date>([0-9]{2}\/[0-9]{2}\/[0-9]{4})<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>/';
//    preg_match_all($regexp, $data['testo'], $matches);
//    $dates = $matches[1];
//    $times = $matches[2];

    $datetimesAr = ADAEventProposal::extractDateTimesFromEventProposalText($data['testo']);
//    $regexp = '/<notes>(.*)<\/notes>/';
//    preg_match($regexp, $data['testo'], $matches);
//    $practitioner_notes = $matches[1];
    $practitioner_notes = ADAEventProposal::extractNotesFromEventProposalText($data['testo']);

//    $regexp = '/<id_course_instance>(.*)<\/id_course_instance>/';
//    preg_match($regexp, $data['testo'], $matches);
//    $course_instance = $matches[1];
    $course_instance = ADAEventProposal::extractIdCourseInstanceFromEventProposalText($data['testo']);

    $form = CDOMElement::create('form','id:event_proposal_form, name:event_proposal_form, action:event_proposal.php, method:post');

    $hidden = CDOMElement::create('hidden','id:id_course_instance, name:id_course_instance');
    $hidden->setAttribute('value', $course_instance);
    $form->addChild($hidden);

    $subject = CDOMElement::create('div','class:proposal_title');
    $event_title = translateFN("Oggetto: " ) . ADAEventProposal::removeEventToken($data['titolo']);
    $subject->addChild(new CText($event_title));

    $offset = 0;
    if ($tester === NULL) {
      $tester_TimeZone = SERVER_TIMEZONE;
    } else {
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
    }
	$now = time() + $offset;
    $zone = translateFN("Time zone:") . " " . $tester_TimeZone . " " . translateFN("actual time: ") . ts2tmFN($now);
    $timezone = CDOMElement::create('div','class:proposal_zone');
    $timezone->addChild(new CText($zone));


    $type = CDOMElement::create('div','class:proposal_type');
    $type->addChild(new CText(translateFN('Tipo di appuntamento: ')));

    if($data['flags'] & ADA_CHAT_EVENT) {
      $type->addChild(new CText('Appuntamento in chat'));
    }
    else if($data['flags'] & ADA_VIDEOCHAT_EVENT) {
      $type->addChild(new CText('Appuntamento in videochat'));
    }
    else if($data['flags'] & ADA_PHONE_EVENT) {
      $type->addChild(new CText('Appuntamento telefonico'));
    }
    else if($data['flags'] & ADA_IN_PLACE_EVENT) {
      $type->addChild(new CText('Appuntamento in presenza'));
    }

    $descriptive_text = CDOMElement::create('div');
    $descriptive_text->addChild(new CText(translateFN("Seleziona una delle possibilit&agrave; qui di seguito:")));

    $needs_to_be_checked = TRUE;

    $proposal1 = CDOMElement::create('div','class:radio_button');
    if(is_array($errors) && isset($errors['date1'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date1']]));
      $proposal1->addChild($date_error);
      $proposal1->addChild(new CText($datetimesAr[0]['date'] . ' ' . $datetimesAr[0]['time']));
    }
    else {
      $radio1 = CDOMElement::create('radio','name:date,checked:checked,value:'.$datetimesAr[0]['date'].'_'.$datetimesAr[0]['time']);
      $proposal1->addChild($radio1);
      $proposal1->addChild(new CText($datetimesAr[0]['date'] . ' ' . $datetimesAr[0]['time']));

      $needs_to_be_checked = FALSE;
    }

    $proposal2 = CDOMElement::create('div','class:radio_button');
    if(is_array($errors) && isset($errors['date2'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date2']]));
      $proposal2->addChild($date_error);
      $proposal2->addChild(new CText($datetimesAr[1]['date'] . ' ' . $datetimesAr[1]['time']));
    }
    else {
      $radio2 = CDOMElement::create('radio','name: date, value:'.$datetimesAr[1]['date'].'_'.$datetimesAr[1]['time']);
      if($needs_to_be_checked) {
        $radio2->setAttribute('checked','checked');
        $needs_to_be_checked = FALSE;
      }
      $proposal2->addChild($radio2);
      $proposal2->addChild(new CText($datetimesAr[1]['date'] . ' ' . $datetimesAr[1]['time']));
    }

    $proposal3 = CDOMElement::create('div','class:radio_button');
    if(is_array($errors) && isset($errors['date3'])) {
      $date_error = CDOMElement::create('div','class:error');
      $date_error->addChild(new CText($error_messages[$errors['date3']]));
      $proposal3->addChild($date_error);
      $proposal3->addChild(new CText($datetimesAr[2]['date'] . ' ' . $datetimesAr[2]['time']));
    }
    else {
      $radio3 = CDOMElement::create('radio','name:date, value:'.$datetimesAr[2]['date'].'_'.$datetimesAr[2]['time']);
      if($needs_to_be_checked) {
        $radio3->setAttribute('checked','checked');
        $needs_to_be_checked = FALSE;
      }
      $proposal3->addChild($radio3);
      $proposal3->addChild(new CText($datetimesAr[2]['date'] . ' ' . $datetimesAr[2]['time']));
    }
    $proposal4 = CDOMElement::create('div','id:refuse_proposal, class:radio_button');
    $radio4 = CDOMElement::create('radio','name:date, value:0');
    if($needs_to_be_checked) {
      $radio4->setAttribute('checked','checked');
      $needs_to_be_checked = FALSE;
    }
    $proposal4->addChild($radio4);
    $proposal4->addChild(new CText(translateFN('Nessuna tra le date proposte')));

    $notes = CDOMElement::create('div','id:practitioner_notes');
    if(strlen(trim($practitioner_notes)) > 0) {
      $notes->addChild(new CText(translateFN("Note del practitioner:").'<br />'));
      $notes->addChild(new CText($practitioner_notes));
    }

    $buttons = CDOMElement::create('div','id:buttons');
    $submit  = CDOMElement::create('submit','name:submit, value:'.translateFN('Invia'));
    $reset  = CDOMElement::create('reset');
    $buttons->addChild($submit);
    $buttons->addChild($reset);

    $form->addChild($subject);
    $form->addChild($timezone);
    $form->addChild($type);
    $form->addChild($descriptive_text);
    $form->addChild($proposal1);
    $form->addChild($proposal2);
    $form->addChild($proposal3);
    $form->addChild($proposal4);
    $form->addChild($notes);
    $form->addChild($buttons);
    return $form;
  }

  /**
   * This method is not in use
   *
   * @param array $data
   * @return unknown_type
   */
  static public function getConfirmedEventProposalForm($data = array()) {
    $regexp  = '/<event>(?:\s)*<date>([0-9]{2}\/[0-9]{2}\/[0-9]{4})<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>(?:\s)*<\/event>/';
    preg_match_all($regexp, $data['testo'], $matches);
    $dates = $matches[1];
    $times = $matches[2];

    $regexp  = '/<event checked="yes">(?:\s)*<date>([0-9]{2}\/[0-9]{2}\/[0-9]{4})<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>(?:\s)*<\/event>/';
    preg_match($regexp, $data['testo'], $matches);

    $accepted_event_date = $matches[1];
    $accepted_event_time = $matches[2];

    $form = CDOMElement::create('form','action:add_event.php, method:post');

    $proposal1 = CDOMElement::create('div','class:event_accepted');
    $proposal1->addChild(new CText('Proposta accettata per la data ' . $accepted_event_date . ' alle ore ' .$accepted_event_time));

    $proposal2 = CDOMElement::create('div','class:event_refused');
    $proposal2->addChild(new CText('Proposta non accettata per la data ' . $dates[0] . ' alle ore ' .$times[0]));

    $proposal3 = CDOMElement::create('div','class:event_refused');
    $proposal3->addChild(new CText('Proposta non accettata per la data ' . $dates[1] . ' alle ore ' .$times[1]));

    $date = CDOMElement::create('hidden','id:date, name:date, value:'. $accepted_event_date);
    $time = CDOMElement::create('hidden','id:time, name:time, value:'. $accepted_event_time);

    $buttons = CDOMElement::create('div');
    $submit  = CDOMElement::create('submit','name:submit, value:'.translateFN('Invia'));
    $reset  = CDOMElement::create('reset');
    $buttons->addChild($submit);
    $buttons->addChild($reset);

    $form->addChild($proposal1);
    $form->addChild($proposal2);
    $form->addChild($proposal3);
    $form->addChild($date);
    $form->addChild($time);
    $form->addChild($buttons);
    return $form;
  }

  static public function getEventsAsTable(ADAGenericUser $userObj, $data_Ar=array(), $testers_data_Ar=array()) {
    if(empty($data_Ar)) {
      return new CText('');
    }

    $common_dh = $GLOBALS['common_dh'];
    $javascript_ok = check_javascriptFN($_SERVER['HTTP_USER_AGENT']);

    $appointments_Ar = array();
    /*
    if($userObj instanceof ADAUser) {
      $module = 'event_proposal.php';
    }
    else {
      $module = 'send_event_proposal.php';
    }
    */
      $module = 'read_event.php';

    foreach($data_Ar as $tester => $appointment_data_Ar) {

      //$tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
      $tester_id = $testers_data_Ar[$tester];

      if (AMA_Common_DataHandler::isError($tester_info_Ar)) {
        /*
         * Return a ADA_Error with delayed error handling.
         */
        return new ADA_Error($tester_info_Ar,translateFN('Errore in ottenimento informazioni tester'),
                              NULL,NULL,NULL,NULL,TRUE);
      }

      foreach($appointment_data_Ar as $appointment_id => $appointment_Ar) {

        // trasform message content into variable names
        $sender_id      = $appointment_Ar[0];
        $date_time      = $appointment_Ar[1];
        /*
         * Check if the subject has an internal identifier and remove it.
         */
        //$subject        = preg_replace('/[0-9]+#/','',$appointment_Ar[2],1);//$appointment_Ar[2];
        $subject        = ADAEventProposal::removeEventToken($appointment_Ar[2]);
        $priority       = $appointment_Ar[3];
        $read_timestamp = $appointment_Ar[4];
        $data_msg        = AMA_DataHandler::ts_to_date($date_time, "%d/%m/%Y - %H:%M:%S");

        $sender_username = $appointment_Ar[6];


        //$msg_id = $tester_info_Ar[0].'_'.$appointment_id;
        $msg_id = $tester_id.'_'.$appointment_id;

        $url = HTTP_ROOT_DIR.'/comunica/'.$module.'?msg_id='.$msg_id;
        if ($javascript_ok) {
          $subject_link = CDOMElement::create('a');
          $subject_link->setAttribute('href','#');
          $subject_link->setAttribute('onclick',"openMessenger('$url',800,600);");
          $subject_link->addChild(new CText($subject));
        }
        else {
          $subject_link = CDOMElement::create('a',"href:$url, target:_blank");
          $subject_link->addChild(new CText($subject));
        }

        $appointments_Ar[] = array($data_msg,$subject_link,$sender_username,$priority);
      }
    }
    //$thead_data = array(translateFN('Data'),translateFN('Oggetto'), translateFN('Mittente'), translateFN('Priorita'));
    if(count($appointments_Ar) > 0) {
      //$table = BaseHtmlLib::tableElement('',NULL, $appointments_Ar);
      //return $table;
      $div = CDOMElement::create('div', 'id:events');
      foreach($appointments_Ar as $appointment) {
        $d = CDOMElement::create('div');
       // $d->addChild(new CText($appointment[0]));
        /*
        if($userObj instanceof ADAPractitioner) {
          $string = translateFN('Appointment proposal: %s, the user %s asks for new dates');
        }
        else {
          $string = translateFN('Proposta di appuntamento: %s, da %s');
        }
        */
          $string = translateFN('Nuovo appuntamento: %s, da %s');

        $subject_link = $appointment[1];
        $message = sprintf($string, $subject_link->getHtml(), $appointment[2]);

        $d->addChild(new CText($message));
        $div->addChild($d);
      }
      return $div;
    }
    else {
      return new CText('');
    }
  }
// MARK: Messages
/*
 * Methods used to display Messages user interfaces
 */

  static public function getMessagesAsTable($data_Ar=array(), $testers_dataAr=array()) {
    if(empty($data_Ar)) {
      return new CText(translateFN('Non sono presenti messaggi'));
    }
    return self::display_messages_as_table($data_Ar, ADA_MSG_SIMPLE, $testers_dataAr);
  }


  static private function getMessagesFormHeader($header_dataAr = array()) {
    $thead_dataAr = array();
    foreach($header_dataAr as $header_colAr) {
      if(isset($header_colAr['action'])) {
        $column_data = CDOMElement::create('a');
        $column_data->setAttribute('href', $header_colAr['action']);
        $column_data->addChild(new CText(translateFN($header_colAr['text'])));
      }
      else {
        $column_data = new CText(translateFN($header_colAr['text']));
      }
      $thead_dataAr[] = $column_data;
    }
    return $thead_dataAr;
  }

  static private function getSentMessagesFormContent($data_Ar= array(), $testers_dataAr = array()) {

    foreach($data_Ar as $tester => $message_dataAr) {

      $tester_id = $testers_dataAr[$tester];
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);

      foreach($message_dataAr as $message_Ar) {

        // trasform message content into variable names
        $sender_id      = $message_Ar['id_mittente'];
        $date_time      = $message_Ar['data_ora'];
        /*
         * Check if the subject has an internal identifier and remove it.
         */
        $subject        = ADAEventProposal::removeEventToken($message_Ar['titolo']);
        $priority       = $message_Ar['priorita'];

        $date_time_zone = $date_time + $offset;
 		// $zone 			= translateFN("Time zone:") . " " . $tester_TimeZone;
        $data_msg        = AMA_DataHandler::ts_to_date($date_time_zone, "%d/%m/%Y %H:%M:%S"); // ." " . $zone;

        $addressee_username = $message_Ar['nome_destinatario'].' '.$message_Ar['cognome_destinatario'];

        $msg_id = $tester_id.'_'.$message_Ar['id_messaggio'];
        $url = HTTP_ROOT_DIR.'/comunica/read_message.php?msg_id='.$msg_id;
        $subject_link = CDOMElement::create('a',"href:$url");
        $subject_link->addChild(new CText($subject));

        /*
         * If this is a list of simple messages, then deleting is allowed.
         * Otherwise it is disabled.
         */
        $delete = CDOMElement::create('checkbox',"name:form[del][$msg_id],value:$msg_id");
        $action_link = CDOMElement::create('a', "href:list_messages.php?del_msg_id=$msg_id");

        $messages_Ar[] = array($addressee_username, $data_msg, $subject_link);//, $delete, $action_link);
      }
    }
    return $messages_Ar;
  }


  static private function getReceivedMessagesFormContent($data_Ar= array(), $testers_dataAr = array()) {

    $del_img = CDOMElement::create('img','src:img/delete.png, name:del_icon');
    $del_img->setAttribute('alt', translateFN('Rimuovi il messaggio'));

    foreach($data_Ar as $tester => $message_dataAr) {

      $tester_id = $testers_dataAr[$tester];
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);

      foreach($message_dataAr as $message_id => $message_Ar) {

        // trasform message content into variable names
        $sender_id      = $message_Ar[0];
        $date_time      = $message_Ar[1];
        $read_timestamp = $message_Ar[4];
        /*
         * Check if the subject has an internal identifier and remove it.
         */
        $subject        = ADAEventProposal::removeEventToken($message_Ar[2]);
        $priority       = $message_Ar[3];

        $date_time_zone = $date_time + $offset;
 		// $zone 			= translateFN("Time zone:") . " " . $tester_TimeZone;
        $data_msg        = AMA_DataHandler::ts_to_date($date_time_zone, "%d/%m/%Y %H:%M:%S"); // ." " . $zone;

        $sender_username = $message_Ar[6];
        $sender_name_surname = $message_Ar[7]." ".$message_Ar[8];

        $msg_id = $tester_id.'_'.$message_id;
        $url = HTTP_ROOT_DIR.'/comunica/read_message.php?msg_id='.$msg_id;
        $subject_link = CDOMElement::create('a',"href:$url");
        $subject_link->addChild(new CText($subject));

        /*
         * If this is a list of simple messages, then deleting is allowed.
         * Otherwise it is disabled.
         */
        $delete = CDOMElement::create('checkbox',"name:form[del][$msg_id],value:$msg_id");
        $action_link = CDOMElement::create('a', "href:$list_module?del_msg_id=$msg_id");
        $action_link->addChild($del_img);
        $read   = CDOMElement::create('checkbox', "name:form[read][$msg_id],value:$msg_id");
        if($read_timestamp != 0) {
          $read->setAttribute('checked','checked');
        }
        if (isset($sender_name_surname)) {
            $messages_Ar[] = array($sender_name_surname, $data_msg, $subject_link, $priority, $delete, $read, $action_link);
        } else {
            $messages_Ar[] = array($sender_username, $data_msg, $subject_link, $priority, $delete, $read, $action_link);
        }
      }
    }
    return $messages_Ar;
  }

  static public function getSentMessagesAsForm($data_Ar=array(), $testers_dataAr=array()) {
    if(empty($data_Ar)) {
      return new CText(translateFN('Non sono presenti messaggi'));
    }
    //return self::display_ada_messages_as_form($data_Ar, $testers_dataAr, true);

    $header_dataAr = array(
    	array('text' => 'Destinatario'),
    	array('text' => 'Data'),// 'action' => 'list_messages.php?sort_field=data_ora'),
    	array('text' => 'Oggetto'),// 'action'=> 'list_messages.php?sort_field=titolo'),
    	// array('text' => 'Cancella'),
        // array('text' => '')
    );
    $thead_dataAr = self::getMessagesFormHeader($header_dataAr);

    $messages_Ar = self::getSentMessagesFormContent($data_Ar, $testers_dataAr);


   if(count($messages_Ar) > 0) {
      $table = BaseHtmlLib::tableElement('',$thead_dataAr, $messages_Ar);
      $form = CDOMElement::create('form',"name:form, method:post, action:$module");
      $form->addChild($table);
      /**
       * @author giorgio 22/apr/2014
       * buttons are no longer needed, unless the delete checkbox is not working
       */
//       $div = CDOMElement::create('div','id:buttons,class:clearfix');
//       $submit = CDOMElement::create('submit','name:btn_commit value:'.translateFN('Salva'));
//       $reset = CDOMElement::create('reset','name:btn_reset value:'.translateFN('Ripristina'));
//       $div->addChild($submit);
//       $div->addChild($reset);
//       $form->addChild($div);
      return $form;
    }
  }

  static public function getReceivedMessagesAsForm($data_Ar=array(), $testers_dataAr=array()) {
    if(empty($data_Ar)) {
      return new CText(translateFN('Non sono presenti messaggi'));
    }

    // text is translated in getMessagesFormHeader method
    $header_dataAr = array(
    	array('text' => 'Mittente'),// 'action' => 'list_messages.php?sort_field=id_mittente'),
    	array('text' => 'Data'),// 'action' => 'list_messages.php?sort_field=data_ora'),
    	array('text' => 'Oggetto'),// 'action'=> 'list_messages.php?sort_field=titolo'),
    	array('text' => 'Priorit&agrave;'),
    	array('text' => 'Cancella'),
    	array('text' => 'Letto'),
    	array('text' => '')
    );

    $thead_dataAr = self::getMessagesFormHeader($header_dataAr);
    $messages_Ar  = self::getReceivedMessagesFormContent($data_Ar, $testers_dataAr);

    if(count($messages_Ar) > 0) {
      $table = BaseHtmlLib::tableElement('',$thead_dataAr, $messages_Ar);
      $form = CDOMElement::create('form',"name:form, method:post, action:$module");
      $form->addChild($table);
      $div = CDOMElement::create('div','id:buttons,class:clearfix');
      $submit = CDOMElement::create('submit','name:btn_commit,value:'.translateFN('Salva'));
      $reset = CDOMElement::create('reset','name:btn_reset,value:'.translateFN('Ripristina'));
      $div->addChild($submit);
      $div->addChild($reset);
      $form->addChild($div);
      return $form;
    }
  }

  // MARK: Agenda
/*
 * Methods used to display Agenda user interfaces
 */

  static public function getAgendaAsTable($data_Ar=array(), $testers_dataAr=array()) {
    if(empty($data_Ar)) {
      return new CText(translateFN('Non sono presenti appuntamenti'));
    }
    return self::display_messages_as_table($data_Ar, ADA_MSG_AGENDA, $testers_dataAr);
  }

  static public function getAgendaAsForm($data_Ar=array(), $testers_dataAr=array()) {
    if(empty($data_Ar)) {
      return new CText(translateFN('Non sono presenti appuntamenti'));
    }
    return self::display_messages_as_form($data_Ar, ADA_MSG_AGENDA, $testers_dataAr);
  }

  static private function display_messages_as_table($data_Ar=array(), $message_type = ADA_MSG_SIMPLE, $testers_dataAr=array()) {
    $common_dh = $GLOBALS['common_dh'];
    $javascript_ok = check_javascriptFN($_SERVER['HTTP_USER_AGENT']);

    $appointments_Ar = array();

    if($message_type == ADA_MSG_SIMPLE) {
      $module = 'read_message.php';
    }
    else {
      $module = 'read_event.php';
    }

    foreach($data_Ar as $tester => $appointment_data_Ar) {

      //$udh = UserDataHandler::instance(self::getDSN($tester));

      //$tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
      $tester_id = $testers_dataAr[$tester];

      if (AMA_Common_DataHandler::isError($tester_info_Ar)) {
        /*
         * Return a ADA_Error with delayed error handling.
         */
        return new ADA_Error($tester_info_Ar,translateFN('Errore in ottenimento informazioni tester'),
                              NULL,NULL,NULL,NULL,TRUE);
      }
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);

      foreach($appointment_data_Ar as $appointment_id => $appointment_Ar) {

        // trasform message content into variable names
        $sender_id      = $appointment_Ar[0];
        $date_time      = $appointment_Ar[1];
        //$subject        = $appointment_Ar[2];
        /*
         * Check if the subject has an internal identifier and remove it.
         */
        //$subject        = preg_replace('/[0-9]+#/','',$appointment_Ar[2],1);
        $subject        = ADAEventProposal::removeEventToken($appointment_Ar[2]);
        $priority       = $appointment_Ar[3];
        $read_timestamp = $appointment_Ar[4];
        $date_time_zone = $date_time + $offset;
 		$zone 			= translateFN("Time zone:") . " " . $tester_TimeZone;
        $data_msg        = AMA_DataHandler::ts_to_date($date_time_zone, "%d/%m/%Y - %H:%M:%S") ." " . $zone;

        // transform sender's id into sender's name
//        $res_ar = $udh->find_users_list(array("username"), "id_utente=$sender_id");
//        if (AMA_DataHandler::isError($res_ar)) {
//          $sender_username = '';
//        }
//        else {
//          $sender_username = $res_ar[0][1];
//        }
        $sender_username = $appointment_Ar[6];

        //$msg_id = $tester_info_Ar[0].'_'.$appointment_id;
        $msg_id = $tester_id.'_'.$appointment_id;
        $url = HTTP_ROOT_DIR.'/comunica/'.$module.'?msg_id='.$msg_id;

        if ($javascript_ok) {
          $subject_link = CDOMElement::create('a');
          $subject_link->setAttribute('href','#');
          $subject_link->setAttribute('onclick',"openMessenger('$url',800,600);");
          $subject_link->addChild(new CText($subject));
        }
        else {
          $subject_link = CDOMElement::create('a',"href:$url, target:_blank");
          $subject_link->addChild(new CText($subject));
        }

        $appointments_Ar[] = array($data_msg,$subject_link,$sender_username,$priority);
      }
    }
    //$thead_data = array(translateFN('Data'),translateFN('Oggetto'), translateFN('Mittente'), translateFN('Priorita'));
    if(count($appointments_Ar) > 0) {
      $table = BaseHtmlLib::tableElement('', NULL, $appointments_Ar);
      return $table;
    }
    else {
      if($message_type == ADA_MSG_SIMPLE) {
        return new CText(translateFN('Non ci sono nuovi messaggi'));
      }
      return new CText(translateFN('Non ci sono nuovi appuntamenti'));
    }
  }

  static private function display_messages_as_form($data_Ar=array(), $message_type = ADA_MSG_SIMPLE,  $testers_dataAr=array()) {
    $common_dh = $GLOBALS['common_dh'];
    $javascript_ok = check_javascriptFN($GLOBALS['HTTP_USER_AGENT']);

    $appointments_Ar = array();

    if($message_type == ADA_MSG_SIMPLE) {
      $list_module = 'list_messages.php';
      $read_module = 'read_message.php';

      $del_img = CDOMElement::create('img','src:img/delete.png, name:del_icon');
      $del_img->setAttribute('alt', translateFN('Rimuovi il messaggio'));
      $del_text = translateFN('Cancella');
    }
    else {
      $list_module = 'list_events.php';
      $read_module = 'read_event.php';
      $del_text ='';
    }


    $order_by_author_link = CDOMElement::create('a',"href:$list_module?sort_field=id_mittente");
    $order_by_author_link->addChild(new CText(translateFN('Autore')));
    $order_by_time_link = CDOMElement::create('a',"href:$list_module?sort_field=data_ora");
    $order_by_time_link->addChild(new CText(translateFN('Data ed ora')));
    $order_by_subject_link = CDOMElement::create('a',"href:$list_module?sort_field=titolo");
    $order_by_subject_link->addChild(new CText(translateFN('Oggetto')));
    $order_by_priority_link = CDOMElement::create('a',"href:$list_module?sort_field=priorita");
    $order_by_priority_link->addChild(new CText(translateFN('Priorit&agrave;')));

    $thead_data = array(
      $order_by_author_link,
      $order_by_time_link,
      $order_by_subject_link,
      $order_by_priority_link,
      $del_text,
      translateFN('Letto'),
      ''
    );

    foreach($data_Ar as $tester => $appointment_data_Ar) {

      //$udh = UserDataHandler::instance(self::getDSN($tester));

      //$tester_info_Ar = $common_dh->get_tester_info_from_pointer($tester);
      $tester_id = $testers_dataAr[$tester];
//      if (AMA_Common_DataHandler::isError($tester_info_Ar)) {
//        /*
//         * Return a ADA_Error with delayed error handling.
//         */
//        return new ADA_Error($tester_info_Ar,translateFN('Errore in ottenimento informazioni tester'),
//                              NULL,NULL,NULL,NULL,TRUE);
//      }
      $tester_TimeZone = MultiPort::getTesterTimeZone($tester);
	  $offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);

      foreach($appointment_data_Ar as $appointment_id => $appointment_Ar) {

        // trasform message content into variable names
        $sender_id      = $appointment_Ar[0];
        $date_time      = $appointment_Ar[1];
        //$subject        = $appointment_Ar[2];
        /*
         * Check if the subject has an internal identifier and remove it.
         */
        //$subject        = preg_replace('/[0-9]+#/','',$appointment_Ar[2],1);
        $subject        = ADAEventProposal::removeEventToken($appointment_Ar[2]);
        $priority       = $appointment_Ar[3];
        $read_timestamp = $appointment_Ar[4];

        $date_time_zone = $date_time + $offset;
 		$zone 			= translateFN("Time zone:") . " " . $tester_TimeZone;
        $data_msg        = AMA_DataHandler::ts_to_date($date_time_zone, "%d/%m/%Y - %H:%M:%S") ." " . $zone;

//        $data_msg        = AMA_DataHandler::ts_to_date($date_time, "%d/%m/%Y - %H:%M:%S");

        // transform sender's id into sender's name
//        $res_ar = $udh->find_users_list(array("username"), "id_utente=$sender_id");
//        if (AMA_DataHandler::isError($res_ar)) {
//          $sender_username = '';
//        }
//        else {
//          $sender_username = $res_ar[0][1];
//        }
        $sender_username = $appointment_Ar[6];

        //$msg_id = $tester_info_Ar[0].'_'.$appointment_id;
        $msg_id = $tester_id.'_'.$appointment_id;
        $url = HTTP_ROOT_DIR.'/comunica/'.$read_module.'?msg_id='.$msg_id;
        $subject_link = CDOMElement::create('a',"href:$url");
        $subject_link->addChild(new CText($subject));

        /*
         * If this is a list of simple messages, then deleting is allowed.
         * Otherwise it is disabled.
         */
        if($message_type == ADA_MSG_SIMPLE) {
          $delete = CDOMElement::create('checkbox',"name:form[del][$msg_id],value:$msg_id");
          $action_link = CDOMElement::create('a', "href:$list_module?del_msg_id=$msg_id");
          $action_link->addChild($del_img);
        }
        else {
          $delete      = '';
          $delete_link = '';

          // PROVA, POI RIMETTERE A POSTO
          $userObj = $_SESSION['sess_userObj'];
          /*
          if($userObj instanceof ADAPractitioner) {
            $event_token = ADAEventProposal::extractEventToken($appointment_Ar[2]);
            $href = HTTP_ROOT_DIR . '/tutor/eguidance_tutor_form.php?event_token=' . $event_token;
            $action_link = CDOMElement::create('a', "href:$href");
            $action_link->addChild(new CText(translateFN('View eguidance session data')));
          }
           *
           */

        }
        $read   = CDOMElement::create('checkbox', "name:form[read][$msg_id],value:$msg_id");
        if($read_timestamp != 0) {
          $read->setAttribute('checked','checked');
        }

        $appointments_Ar[] = array($sender_username, $data_msg, $subject_link, $priority, $delete, $read, $action_link);

      }
    }

    if(count($appointments_Ar) > 0) {
      $table = BaseHtmlLib::tableElement('',$thead_data, $appointments_Ar);

      $form = CDOMElement::create('form',"name:form, method:post, action:$module");
      $form->addChild($table);
      $div = CDOMElement::create('div','id:buttons');
      $submit = CDOMElement::create('submit','name:btn_commit value:'.translateFN('Salva'));
      $reset = CDOMElement::create('reset','name:btn_reset value:'.translateFN('Ripristina'));
      $div->addChild($submit);
      $div->addChild($reset);
      $form->addChild($div);
      return $form;
    }
    else {
      if($message_type == ADA_MSG_SIMPLE) {
        return new CText(translateFN('Non ci sono nuovi messaggi'));
      }
      return new CText(translateFN('Non ci sono nuovi appuntamenti'));
    }
  }




  static public function getOperationWasSuccessfullView($text) {
    $div = CDOMElement::create('div');
    $div->addChild(new CText($text));
    $div->addChild(new CText(' '));
    $link = CDOMElement::create('a');
    $link->setAttribute('href','#');
    $link->setAttribute('onclick','closeMeAndReloadParent();');
    $link->addChild(new CText(translateFN('Chiudi')));
    $div->addChild($link);
    return $div;
  }
  static private function getEventProposalFormHoursSelect($select_id, $preselect_time_value=NULL) {
    $start_hour = 8;
    $end_hour   = 24;
    $increment_minutes_by = 15;

    $select = CDOMElement::create('select',"id: $select_id, name: $select_id");

    for($hours = $start_hour; $hours < $end_hour; $hours++) {
      $minutes = 0;
      while($minutes < 60) {
        $option_value = sprintf('%02d:%02d',$hours,$minutes);

        $option = CDOMElement::create('option');
        $option->addChild(new CTExt($option_value));
        if($preselect_time_value == $option_value) {
          $option->setAttribute('selected','selected');
        }
        $select->addChild($option);
        $minutes += $increment_minutes_by;
      }
    }

    return $select;
  }
}
?>