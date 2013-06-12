<?php
/**
 *
 *
 * @package   comunica
 * @author
 * @copyright Copyright (c) 2009, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
 */

class ADAEventProposal
{
  /**
   * Generates an event token string
   *
   * @param  int $tutoredUserId
   * @param  int $tutorId
   * @param  int $courseInstanceId
   * @return string
   */
  static public function generateEventToken($id_tutored_user, $id_tutor, $id_course_instance) {

    $event_token = $id_tutored_user    . '_'
                 . $id_tutor           . '_'
                 . $id_course_instance . '_'
                 . time()              ;

    return $event_token;
  }

  /**
   * Returns the course instance id from a given event token
   *
   * @param  string $event_token
   * @return int
   */
  static public function extractCourseInstanceIdFromThisToken($event_token) {

    /*
     * first match: tutored user id
     * second match: tutor id
     * third match: course instance id
     * fourth match: timestamp
     */
    $pattern = '/(?:[1-9][0-9]*)_(?:[1-9][0-9]*)_([1-9][0-9]*)_(?:[1-9][0-9]+)/';
    $matches = array();
    if(preg_match($pattern, $event_token, $matches) == 1) {
      return $matches[1];
    }
    return FALSE;

  }

  /**
   * Inspects a string to see if it has an event token prefixed.
   *
   * @param  string $string
   * @return string the event token if found, an empty string otherwise
   */
  static public function extractEventToken($string) {
    $pattern = '/^([0-9_]+)#/';
    $matches = array();
    if(preg_match($pattern, $string, $matches) == 1) {
      return $matches[1];
    }
    return '';
  }

  /**
   * Removes the event token from a given string if it is found as a string prefix.
   *
   * @param string $string
   * @return string
   */
  static public function removeEventToken($string) {
    $pattern = '/^[0-9_]+#/';
    $clean_string = preg_replace($pattern, '', $string, 1);
    return $clean_string;
  }

  /**
   * Generates the event proposal message content
   *
   * @param  array  $datetimesAr an associative array with dates as keys and times as values
   * @param  int    $id_course_instance
   * @param  string $notes
   * @return string
   */
  static public function generateEventProposalMessageContent($datetimesAr=array(), $id_course_instance, $notes) {

    $message = '<proposal>';
    foreach($datetimesAr as $datetimeAr) {
      $date = $datetimeAr['date'];
      $time = $datetimeAr['time'];

      $message .= "<event><date>$date</date><time>$time</time></event>";
    }
    $message .= "<notes>$notes</notes>"
              . "<id_course_instance>$id_course_instance</id_course_instance>"
              . '</proposal>';

    return $message;
  }

  /**
   * Returns an array containing the dates and times proposed for an event
   *
   * @param string $string
   * @return array on success, FALSE on failure
   */
  static public function extractDateTimesFromEventProposalText($string) {
    $pattern = '/<date>([0-9]{2}\/[0-9]{2}\/[0-9]{4})<\/date>(?:\s)*<time>([0-9]{2}:[0-9]{2})<\/time>/';
    $matches = array();
    if(preg_match_all($pattern, $string, $matches)) {
      // costruire array datetimesAr e restituire
      $datetimesAr = array();
      $dates = $matches[1];
      $times = $matches[2];
      $howManyDates = count($dates);
      for($i = 0; $i < $howManyDates; $i++) {
        $datetimesAr[] = array('date' => $dates[$i],
                               'time' => $times[$i]);
      }

      return $datetimesAr;
    }
    return FALSE;
  }

  /**
   * Returns the practitioner's notes
   * @param string $string
   * @return string
   */
  static public function extractNotesFromEventProposalText($string) {
    $pattern = '/<notes>(.*)<\/notes>/';
    $matches = array();
    if(preg_match($pattern, $string, $matches) == 1) {
      return $matches[1];
    }
    return '';
  }

  /**
   * Returns the course instance id
   * @param string $string
   * @return FALSE if not found, an int > 0 otherwise
   */
  static public function extractIdCourseInstanceFromEventProposalText($string) {
    $pattern = '/<id_course_instance>(.*)<\/id_course_instance>/';
    $matches = array();
    if(preg_match($pattern, $string, $matches) == 1) {
      return $matches[1];
    }
    return FALSE;
  }
  /**
   * Checks if an event can be proposed in the given date and time
   *
   * @param string $date
   * @param string $time
   * @return TRUE on success, a ADA error code on failure
   */

  static public function canProposeThisDateTime(ADALoggableUser $userObj, $date, $time, $tester = NULL) {

    $date = DataValidator::validate_date_format($date);
    if($date === FALSE) {
      return ADA_EVENT_PROPOSAL_ERROR_DATE_FORMAT;
    }
    else {
      $current_timestamp = time();

       /**
       * @var timezone management
       */
      $offset = 0;
      if ($tester === NULL) {
      	$tester_TimeZone = SERVER_TIMEZONE;
      } else {
      	$tester_TimeZone = MultiPort::getTesterTimeZone($tester);
		$offset = get_timezone_offset($tester_TimeZone,SERVER_TIMEZONE);
      }

      $timestamp_time_zone = sumDateTimeFN(array($date,"$time:00"));
      $timestamp = $timestamp_time_zone - $offset;

      if($current_timestamp >= $timestamp) {
        return ADA_EVENT_PROPOSAL_ERROR_DATE_IN_THE_PAST;
      }
      if(MultiPort::hasThisUserAnAppointmentInThisDate($userObj, $timestamp)) {
        return ADA_EVENT_PROPOSAL_ERROR_DATE_IN_USE;
      }
    }

    return TRUE;
  }

  /**
   * Returns a new string containing the event token
   * @param string $event_token
   * @param string $string
   * @return string
   */
  static public function addEventToken($event_token, $string) {
    $pattern = '/(?:[1-9][0-9]*)_(?:[1-9][0-9]*)_(?:[1-9][0-9]*)_(?:[1-9][0-9]+)/';
    $matches = array();
    if(preg_match($pattern, $event_token, $matches) == 1) {
      return $event_token . '#' . $string;
    }

    return $string;
  }
}
?>