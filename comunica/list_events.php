<?php
/**
 * LIST MESSAGES.
 *
 * @package		comunica
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout'),
  AMA_TYPE_SWITCHER => array('layout'),
  AMA_TYPE_TUTOR => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

/*
 * YOUR CODE HERE
 */
if (!isset($op)) {
  $op = 'default';
}

$title = translateFN('ADA - Lista eventi');

// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($sess_id_course_instance,$online_users_listing_mode);

// CHAT, BANNER etc

$banner = include ROOT_DIR.'/include/banner.inc.php';

// default status:
if ((empty($status)) or (!isset($status))) {
  $status = translateFN('Lista appuntamenti del').' '.today_dateFN().' - '.today_timeFN();
}
else {
  $status = urldecode($status);
}


// Has the form been posted?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // build array of messages ids to be set as read
  if (isset($form) and in_array('read',array_keys($form))
  and (count($form['read']))) {

    $to_set_as_read_ar = $form['read'];
  }
  else {
    $to_set_as_read_ar = array();
  }

  // set all read events

  //$res = $mh->set_messages($sess_id_user, $to_set_as_read_ar, 'R');
  $res = MultiPort::markUserAppointmentsAsRead($userObj,$to_set_as_read_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore'));
  }
  // set all unread events

  // first, get all the events in the user's spool
  //$msgs_ha = $mh->get_messages($sess_id_user, ADA_MSG_AGENDA, array("read_timestamp"));
  $msgs_ha = MultiPort::getUserAgenda($userObj);
  if (AMA_DataHandler::isError($msgs_ha)){
    $errObj = new ADA_Error($msgs_ha,translateFN('Errore in lettura appuntamenti'));
  }

  // then fill the array of ids to set as unread
  $to_set_as_unread_ar = array();
  foreach ($msgs_ha as $pointer => $msgs_tester_Ar) {
      $id_tester_Ar = $common_dh->get_tester_info_from_pointer($pointer);
      if (AMA_DataHandler::isError($id_tester_Ar)){
          $errObj = new ADA_Error($id_tester_Ar, translateFN('Errore'));
      }else {
          foreach ($msgs_tester_Ar as $msg_id => $msg_ar){
            $msg_id_tester = $id_tester_Ar[0] . '_' . $msg_id;
            if (!in_array($msg_id_tester, $to_set_as_read_ar)) {
              $to_set_as_unread_ar[] = $msg_id;
            }
          }
      }
  }

  // last, invoke, the set_events method
  //$res = $mh->set_messages($sess_id_user, $to_set_as_unread_ar, 'N');
  $res = MultiPort::markUserAppointmentsAsUnread($userObj, $to_set_as_unread_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore'));
  }

  // build array of messages ids to be removed
  if (isset($form) and in_array('del',array_keys($form))
  and (count($form['del']))) {
    $to_remove_ar = $form['del'];
  }
  else {
    $to_remove_ar = array();
  }
  // manage events removal
  //$mh->remove_messages($sess_id_user, $to_remove_ar);
  $res = MultiPort::removeUserAppointments($userObj, $to_remove_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore durante la cancellazione dei messaggi'));
  }
  else {
    $status = translateFN('Cancellazione eseguita');
  }

} // end if POST


// remove single event if requested

if (isset($del_msg_id) and !empty($del_msg_id)){
  $res = MultiPort::removeUserAppointments($userObj, array($del_msg_id));
  if (AMA_DataHandler::isError($res)) {
    $errObj = new ADA_Error($res, translateFN('Errore durante la cancellazione del messaggio'));
  }
  else {
    $status = translateFN('Cancellazione eseguita');
  }
}

// analyze the sorting info
if (!isset($sort_field)) {
  $sort_field = "data_ora desc";
}
elseif($sort_field == "data_ora") {
  $sort_field .= " desc";
}
elseif($sort_field == "titolo") {
  $sort_field .= " asc";
}
else {
  $sort_field .= " asc, data_ora desc";
}


$dataAr         = MultiPort::getUserAgenda($userObj);
$testers_dataAr = MultiPort::getTestersPointersAndIds();
$messages       = CommunicationModuleHtmlLib::getAgendaAsForm($dataAr, $testers_dataAr);
$node_title = ""; // empty

if (!isset($course_title)) {
  $course_title = "";
}
else {
  $course_title = '<a href="../browsing/main_index.php">'.$course_title.'</a>';
}

/*
* Last access link
*/

if(isset($_SESSION['sess_id_course_instance'])){
        $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
  else {
        $last_access=$userObj->get_last_accessFN(null,"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
  
 if($last_access=='' || is_null($last_access)){
    $last_access='-';
}

$content_dataAr = array(
  'banner'       => $banner,
  'course_title' => $course_title,
  'go_back'      => isset($go_back) ? $go_back : null,
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'user_level'   => $user_level,
  'last_visit' => $last_access,
  'messages'     => $messages->getHtml(),
  'status'       => $status,
  'chat_users'   => $online_users
);

ARE::render($layout_dataAr, $content_dataAr);
?>