<?php
class ADAEvent
{
  public static function generateEventMessageAction($event_type, $id_course, $id_course_instance) {
    $text = '<event_data>'
  		  . "<event_type>$event_type</event_type>"
  		  . "<id_course>$id_course</id_course>"
  		  . "<id_course_instance>$id_course_instance</id_course_instance>"
          . '</event_data>';

    return $text;
  }

  public static function parseMessageText($message_ha = array()) {
    $message_text = $message_ha['testo'];
    $action = self::extractActionFromEventMessage($message_ha);
    $clean_message = self::cleanMessageText($message_text)
                   . self::appendAction($action);
    return $clean_message;
  }

  private static function extractActionFromEventMessage($message_ha = array()) {

    if(!self::createTheEnterLink($message_ha)) {
      return '';
    }
    $message = $message_ha['testo'];

    $pattern = '/<event_data>(?:\s)*<event_type>(.*)<\/event_type>(?:\s)*<id_course>(.*)<\/id_course>(?:\s)*<id_course_instance>(.*)<\/id_course_instance>(?:\s)*<\/event_data>/';
    $matches = array();

    if(preg_match($pattern, $message, $matches) > 0) {
      return "performEnterEventSteps({$matches[1]},{$matches[2]},{$matches[3]});";
    }
    return '';
  }

  private static function createTheEnterLink($message_ha = array()) {
    $event_timestamp   = $message_ha['data_ora'];
    $current_timestamp = time();
    $round = 1800;

    if($current_timestamp > $event_timestamp
    && $current_timestamp < $event_timestamp + $round) {
      return true;
    }
    return false;
  }

  private static function cleanMessageText($message) {
    $pattern = '/<event_data>(?:.*)<\/event_data>/';
    $message_text = nl2br(preg_replace($pattern, '', $message));
/*
    $message_rows = explode(chr(13),  rtrim($message_text));
    $clean_text = '';
    foreach($message_rows as $row) {
      $clean_text .= '<br />';
    }
    return $clean_text;
*/
    return $message_text;
  }


  private static function appendAction($action) {
    if(empty($action)) {
      return '';
    }
    $div = CDOMElement::create('div','id:enter_appointment');
    $link = CDOMElement::create('a');
    $link->setAttribute('href', '#');
    $link->setAttribute('onclick', $action);
    $link->addChild(new CText(translateFN('Enter the appointment')));
    $div->addChild($link);
    return $div->getHtml();
  }
}