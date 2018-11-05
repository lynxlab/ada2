<?php

/**
 * @package 	view
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

 /**
  * View helper base class
  *
  * base class used to build global variables used by the scripts
  * in the browsing, switcher, tutor, services folders
  */
abstract class ViewBaseHelper
{
  private static $testers_dataAr = null;
  private static $dumpExtract = false;

  /**
   * Array to store all needed data
   * the key will be the name of the var to be extracted
   *
   * @var array
   */
  protected static $helperData = [];

  /**
   * Builds array keys common to all cases, such as:
   * status, userObj, id_profile, user_type, user_name,
   * reg_enabled, log_enabled, mod_enabled, com_enabled and layout_dataAr
   *
   * @param array $neededObjAr
   *
   * @return array
   */
  public static function init(array $neededObjAr = array()) {
    if (count(self::$helperData) === 0) {
      $userObj = self::getUserObj();
      self::$helperData = array_merge([],
        [
          'status' => self::getStatus(),
          'userObj' => $userObj,
          'user_type' => $userObj->convertUserTypeFN($userObj->getType()),
          'user_name' => $userObj->getFirstName(),
          'id_profile' => $userObj->getType(),
          'layout_dataAr' => []
        ],
        self::getNeededObjects($userObj, $neededObjAr),
        self::getEnabledArray()
      );
      self::$helperData['template_family'] = self::setSessionTemplate(
        self::$helperData['userObj']->template_family,
        isset(self::$helperData['nodeObj']) ? self::$helperData['nodeObj']->template_family : null,
        isset(self::$helperData['$courseInstanceObj']) ? self::$helperData['courseInstanceObj']->template_family : null,
        isset(self::$helperData['courseObj']) ? self::$helperData['courseObj']->template_family : null);
    }
    return self::getHelperData();
  }

  /**
   * Gets the array built by init
   *
   * @return array
   */
  public static function getHelperData()
  {
    return self::$helperData;
  }

  /**
   * Builds the reg_enabled, log_enabled, mod_enabled and com_enabled keys
   *
   * @param ADAGenericUser $userObj
   * @param Course $courseObj
   *
   * @return array
   */
  protected static function getEnabledArray(ADAGenericUser $userObj = null, Course $courseObj = null)
  {
    $reg_enabled = false; // links to bookmarks enabled
    $log_enabled = false; // links to history enabled
    $mod_enabled = false; // links to modify nodes  enabled
    $com_enabled = false; // links to comunicate among users  enabled

    return ['reg_enabled' => $reg_enabled, 'log_enabled' => $log_enabled, 'mod_enabled' => $mod_enabled, 'com_enabled' => $com_enabled];
  }

  /**
   * Gets the status message
   *
   * @param array|null $dataArr if null defaults to $_REQUEST
   * @param string $defaultStatus default text to be used for the status
   *
   * @return string
   */
  protected static function getStatus($dataArr = null, $defaultStatus = 'navigazione')
  {
    if (!is_array($dataArr) || is_null($dataArr)) $dataArr = $_REQUEST;
    if (!isset($dataArr['status'])) {
      if (isset($dataArr['msg'])) {
        return $dataArr['msg'];
          // $msg = $_REQUEST['msg'];
      } else {
        return translateFN($defaultStatus);
      }
    } else {
      return $dataArr['status'];
    }
  }

  /**
   * get User object, either from session or load it from the DB
   *
   * @return ADAGenericUser|null
   */
  protected static function getUserObj()
  {
    global $sess_id_user;

    if ($_SESSION['sess_userObj'] instanceof ADAGenericUser) {
      return $_SESSION['sess_userObj'];
    } else {
      /** @var ADAGenericUser $userObj */
      $userObj = read_user($sess_id_user);
      if (ADA_Error::isError($userObj)) {
        $userObj->handleError();
        return null;
      } else {
        return $userObj;
      }
    }
  }

  /**
   * Builds user_level, user_score, user_history, user_status...
   *
   * @param ADAGenericUser $userObj
   * @param boolean $log_enabled
   * @return void
   */
  protected static function getUserBrowsingData(ADAGenericUser $userObj, $log_enabled = false)
  {
    global $sess_id_user, $sess_id_course_instance;

    switch ($userObj->getType()) {
      case AMA_TYPE_STUDENT:
        $user_level = "0";
        $user_score = "0";
        $user_history = "";
        $user_status = $userObj->get_student_status($sess_id_user, $sess_id_course_instance);
        break;
      case AMA_TYPE_TUTOR:
        $user_level = ADA_MAX_USER_LEVEL;
        $user_score = "";
        $user_history = "";
        $user_status = 0;
        break;
      case AMA_TYPE_SWITCHER:
        $user_level = ADA_MAX_USER_LEVEL;
        $user_score = "";
        $user_history = "";
        $user_status = ADA_STATUS_VISITOR;
        break;
      case AMA_TYPE_AUTHOR:
        $user_level = ADA_MAX_USER_LEVEL;
        $user_score = "";
        $user_history = "";
        $user_status = ADA_STATUS_VISITOR;
        break;
      case AMA_TYPE_ADMIN:
        $user_level = ADA_MAX_USER_LEVEL;
        $user_score = "";
        $user_history = "";
        $user_status = ADA_STATUS_VISITOR;
        break;
      default:
        $user_level = "0";
        $user_score = "0";
        $user_history = "";
        $user_status = AMA_TYPE_VISITOR;
        break;
    }

    if ($userObj->getType() == AMA_TYPE_STUDENT && $log_enabled) {
      $user_level = (string)$userObj->get_student_level($sess_id_user, $sess_id_course_instance);
      $user_score = (string)$userObj->get_student_score($sess_id_user, $sess_id_course_instance);
      $user_history = $userObj->history;
    }

    return [
      'user_level' => $user_level,
      'user_score' => $user_score,
      'user_history' => $user_history,
      'user_status' => $user_status
    ];
  }

  /**
   * Builds the array keys as requested by the neededObjAr
   *
   * @param ADAGenericUser $userObj
   * @param array $neededObjAr
   * @param string $user_status
   *
   * @return array
   */
  protected static function getNeededObjects(ADAGenericUser $userObj, array $neededObjAr = array())
  {
    global $sess_id_course, $sess_id_course_instance, $sess_selected_tester, $sess_id_user, $dh;

    if (is_array($neededObjAr) && array_key_exists($userObj->getType(), $neededObjAr) && is_array($neededObjAr[$userObj->getType()])) {
      $thisUserNeededObjAr = $neededObjAr[$userObj->getType()];
    } else {
      $thisUserNeededObjAr = array();
    }
    $retArr = [];

    if (in_array('course', $thisUserNeededObjAr)) {
      /**
       * @var Course $courseObj
       */
      $courseObj = read_course($sess_id_course);
      if (ADA_Error::isError($courseObj)) {
        $courseObj->handleError();
      } else {
        // $course_title = $courseObj->titolo; //title
        // $id_toc = $courseObj->id_nodo_toc;  //id_toc_node
        // $course_media_path = $courseObj->media_path;
        // $course_author_id = $courseObj->id_autore;
        // $course_family = $courseObj->template_family;
        // $course_static_mode = $courseObj->static_mode;
      }

      if (empty($courseObj->media_path)) {
        $media_path = MEDIA_PATH_DEFAULT . $courseObj->id_autore . "/";
      } else {
        $media_path = $courseObj->media_path;
      }
      $retArr['media_path'] = $media_path;
      $retArr['courseObj'] = $courseObj;
    }

    if (in_array('course_instance', $thisUserNeededObjAr)) {
      if (!ADA_Error::isError($courseObj) && !$courseObj->getIsPublic()) {
        if (in_array($userObj->getType(),[AMA_TYPE_STUDENT, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER])) {
          /**
           * 	@var Course_Instance $courseInstanceObj
           */
          $courseInstanceObj = read_course_instance_from_DB($sess_id_course_instance);
          if (ADA_Error::isError($courseInstanceObj)) {
            $courseInstanceObj->handleError();
          } else {
            // $course_instance_family = $courseInstanceObj->template_family;
            // $cistatus = $courseInstanceObj->status;
            // if (($cistatus == ADA_COURSEINSTANCE_STATUS_PUBLIC)
            //   and (($id_profile == AMA_TYPE_STUDENT) or ($id_profile == AMA_TYPE_GUEST))) {
              //   $user_status = ADA_STATUS_VISITOR;
              // }
            $retArr['courseInstanceObj'] = $courseInstanceObj;
          }
        }
      }
    }

    if (in_array('tutor', $thisUserNeededObjAr)) {
      global $sess_id_course_instance;

      if (isset($sess_id_course_instance)) {
        if (method_exists($userObj, 'get_student_status')) {
          $user_status = $userObj->get_student_status($userObj->getId(), $sess_id_course_instance);
        } else $user_status = ADA_STATUS_VISITOR;
        if ($user_status != ADA_STATUS_VISITOR) {
          $tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
          if (!empty($tutor_id) && !AMA_DataHandler::isError($tutor_id)) {
            $tutorAr = $dh->get_tutor($tutor_id);
            if (!AMA_dataHandler::isError($tutorAr)) {
              if (isset($tutorAr['username'])) BrowsingHelper::$tutor_uname = $tutorAr['username'];
              $retArr['tutor_id'] = $tutor_id;
            }
          }
        }
      }
    }

    if (in_array('node', $thisUserNeededObjAr)) {
      global $id_node;
      /**
       * @var Node $nodeObj
       */
      $nodeObj = read_node_from_DB(isset($id_node) ? $id_node : null);
      if (ADA_Error::isError($nodeObj)) {
        $nodeObj->handleError();
      }
      $retArr['nodeObj'] = $nodeObj;
    }

    if (in_array('chatroom', $thisUserNeededObjAr)) {
      global $id_chatroom;

      require_once 'ChatRoom.inc.php';
      require_once 'ChatDataHandler.inc.php';

      /*
       * Check if the user has an appointment
       */
      $retArr['exit_reason'] = NO_EXIT_REASON;
      if (!isset($id_chatroom) && isset($_SESSION['sess_id_course_instance'])) {
        $id_chatroom = ChatRoom::get_class_chatroomFN($_SESSION['sess_id_course_instance']);
        if (AMA_DataHandler::isError($id_chatroom)) {
          $id_chatroom = 0;
        }
      }
      $retArr['chatroomObj'] = new ChatRoom($id_chatroom, $_SESSION['sess_selected_tester_dsn']);
      if ($retArr['chatroomObj']->error == 1) {
        $retArr['exit_reason'] = EXIT_REASON_WRONG_ROOM;
      }
    }

    if (in_array('videoroom', $thisUserNeededObjAr)) {
      /*
       * Check if the user has an appointment today at actual time
       */
      $user_has_app = false;
      if (defined('DATE_CONTROL') and (DATE_CONTROL == false)) {
        $user_has_app = true;
      } else {
        $user_has_app = MultiPort::hasThisUserAVideochatAppointment($userObj);
      }
      if ($user_has_app) {
        $event_token = $user_has_app;
        switch ($userObj->getType()) {
          case AMA_TYPE_STUDENT:
            /**
             * get videoroom Obj
             */
            $videoroomObj = videoroom::getVideoObj();
            $tempo_attuale = time();
            $videoroomObj->videoroom_info($sess_id_course_instance, $tempo_attuale);
            if ($videoroomObj->full) {
              $videoroomObj->serverLogin();
              if ($videoroomObj->login >= 0) {
                $videoroomObj->roomAccess(
                  $userObj->getUserName(),
                  $userObj->getFirstName(),
                  $userObj->getLastName(),
                  $userObj->getEmail(),
                  $sess_id_user,
                  $userObj->getType());
              }
            } else {
              $status = addslashes(translateFN("Room not yet opened"));
              $options_Ar = array('onload_func' => "close_page('$status');");
            }
            break;
          case AMA_TYPE_TUTOR:
            $videoroomObj = videoroom::getVideoObj();
            $tempo_attuale = time();
            $creationDate = Abstract_AMA_DataHandler::ts_to_date($tempo_attuale);
            $videoroomObj->videoroom_info($sess_id_course_instance, $tempo_attuale);
            $videoroomObj->serverLogin();
            if ($videoroomObj->full) {
              if ($videoroomObj->login >= 0) {
                $videoroomObj->roomAccess(
                  $userObj->getUserName(),
                  $userObj->getFirstName(),
                  $userObj->getLastName(),
                  $userObj->getEmail(),
                  $sess_id_user,
                  $userObj->getType(),
                  $sess_selected_tester);
              }
            } else {
              $room_name = $course_title . ' - ' . translateFN('Tutor') . ': ' . $userObj->getUserName() . ' ' . translateFN('data') . ': ' . $creationDate;
              $comment = translateFN('inserimento automatico via') . ' ' . PORTAL_NAME;
              $numUserPerRoom = 4;
              $id_room = $videoroomObj->addRoom($room_name, $sess_id_course_instance, $sess_id_user, $comment, $numUserPerRoom, $course_title, $sess_selected_tester);
              if ($videoroomObj->login >= 0 && ($id_room != false)) {
                $videoroomObj->roomAccess(
                  $userObj->getUserName(),
                  $userObj->getFirstName(),
                  $userObj->getLastName(),
                  $userObj->getEmail(),
                  $sess_id_user,
                  $userObj->getType());
              }
            }
            break;
        }
      } else {
        $close_page_message = addslashes(translateFN("You don't have a videochat appointment at this time."));
        $options_Ar = array('onload_func' => "close_page('$close_page_message');");
      }
      if (isset($videoroomObj)) $retArr['videoroomObj'] = $videoroomObj;
      if (isset($options_Ar))   $retArr['options_Ar'] = $options_Ar;
    }

    return $retArr;
  }


  /**
   * Builds the sess_id_node, sess_id_course and sess_id_course_instance
   * array keys that are needed by some of the scritps
   *
   * @return array
   */
  protected static function buildGlobals() {
    if (isset($_REQUEST['id_node'])){
      $sess_id_node = $_REQUEST['id_node'];
    } else {
      $sess_id_node = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;
    }

    if (isset($_REQUEST['id_course'])){
      $sess_id_course = $_REQUEST['id_course'];
    } else {
      $sess_id_course = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
    }

    if (isset($_REQUEST['id_course_instance'])){
      $sess_id_course_instance = $_REQUEST['id_course_instance'];
    } else {
      $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
    }

    return ['sess_id_node' => $sess_id_node, 'sess_id_course' => $sess_id_course, 'sess_id_course_instance' => $sess_id_course_instance];
  }

  /**
   * Builds the sess_template_family array key
   *
   * @param string $user_family
   * @param string $node_family
   * @param string $course_instance_family
   * @param string $course_family
   *
   * @return string
   */
  protected static function setSessionTemplate($user_family = null, $node_family = null, $course_instance_family = null, $course_family = null)
  {
    if ((isset($_REQUEST['family'])) && (!empty($_REQUEST['family']))) { // from GET parameters
      $template_family = trim($_REQUEST['family']);
    } elseif ((isset($node_family)) && (!empty($node_family))) { // from node definition
      $template_family = $node_family;
    } elseif ((isset($course_instance_family)) && (!empty($course_instance_family))) { // from course instance definition
      $template_family = $course_instance_family;
    } elseif ((isset($course_family)) && (!empty($course_family))) { // from course definition
      $template_family = $course_family;
    } elseif ((isset($user_family)) && (!empty($user_family))) { // from user's profile
      $template_family = $user_family;
    } else {
      $template_family = ADA_TEMPLATE_FAMILY; // default template famliy
    }
    $_SESSION['sess_template_family'] = $template_family;
    return $template_family;
  }

  /**
   * Builds the user_messages value with a call to
   * CommunicationModuleHtmlLib::getMessagesAsTable
   *
   * @param ADAGenericUser $userObj
   *
   * @return CDOMElement
   */
  protected static function getUserMessages(ADAGenericUser $userObj)
  {
    require_once ROOT_DIR . '/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
    return CommunicationModuleHtmlLib::getMessagesAsTable(MultiPort::getUserMessages($userObj), self::getTestersDataAr());
  }

  /**
   * Builds the user_agenda value with a call to
   * CommunicationModuleHtmlLib::getAgendaAsTable
   *
   * @param ADAGenericUser $userObj
   *
   * @return CDOMElement
   */
  protected static function getUserAgenda(ADAGenericUser $userObj)
  {
    require_once ROOT_DIR . '/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
    return CommunicationModuleHtmlLib::getAgendaAsTable(MultiPort::getUserAgenda($userObj), self::getTestersDataAr());
  }

  /**
   * Builds the user_events value with a call to
   * CommunicationModuleHtmlLib::getEventsAsTable
   *
   * @param ADAGenericUser $userObj
   *
   * @return CDOMElement
   */
  protected static function getUserEvents(ADAGenericUser $userObj)
  {
    require_once ROOT_DIR . '/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php';
    return CommunicationModuleHtmlLib::getEventsAsTable($userObj, MultiPort::getUserEventsNotRead($userObj), self::getTestersDataAr());
  }

  /**
   * Extracts the helperData array to $GLOBALS
   *
   * @return void
   */
  protected static function extract() {
    foreach (self::getHelperData() as $key => $value) {
      if (self::$dumpExtract === true) {
        if (is_object($value)) $dbgval = 'Object of class '.get_class($value);
        else if (is_array($value)) $dbgval = sprintf("Array with %d elements", count($value));
        else $dbgval = $value;
        var_dump(sprintf("%s \$GLOBALS key '%s': %s",
          !array_key_exists($key, $GLOBALS) ? 'Setting' : 'Overwriting',
          $key, $dbgval)
        );
      }
      $GLOBALS[$key] = $value;
    }
  }

  /**
   * Populates the testers_dataAr property calling
   * MultiPort::getTestersPointersAndIds()
   *
   * @return array
   */
  private static function getTestersDataAr()
  {
    if (is_null(self::$testers_dataAr)) {
      self::$testers_dataAr = MultiPort::getTestersPointersAndIds();
    }
    return self::$testers_dataAr;
  }
}
