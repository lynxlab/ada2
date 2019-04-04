<?php
/**
 * ADA Error class
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		ada_error
 * @version		0.1
 */
class ADA_Error
{
  private $AMAErrorObject;
  private $errorMessage;
  private $callerName;
  private $ADAErrorCode;
  private $severity;
  private $redirectTo;
  private $errorCode;
  private $file;
  private $line;
  private $sessUserId;
  private $sessCourseId;
  private $sessCourseInstanceId;
  private $sessNodeId;
  private $requestVars;

  public function __construct($AMAErrorObject=NULL,$errorMessage=NULL, $callerName=NULL, $ADAErrorCode=NULL, $severity=NULL, $redirectTo=NULL, $delayErrorHandling=FALSE) {
    //ADALogger::log_error('call to new ADA_Error');

    $this->AMAErrorObject = $AMAErrorObject;
    $this->errorMessage   = $errorMessage;
    $this->callerName     = $callerName;
    $this->ADAErrorCode  = $ADAErrorCode;
    $this->severity       = $severity;
    $this->redirectTo     = $redirectTo;
    $this->errorCode      = 0;
    $this->getFileAndLine();
    $this->sessUserId           = isset($_SESSION['sess_id_user']) ? $_SESSION['sess_id_user'] : 'NONE';
    $this->sessCourseId         = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : 'NONE';
    $this->sessCourseInstanceId = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : 'NONE';
    $this->sessNodeId           = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : 'NONE';
    $this->requestVars = $this->requestAsString();

    if(!$delayErrorHandling) {
      $this->handleError();
    }
  }

  // MARK: common methods
  private function getAction() {
    $ADA_ERROR_SEVERITY = $GLOBALS['ADA_ERROR_SEVERITY'];
    $ADA_ERROR_POLICY   = $GLOBALS['ADA_ERROR_POLICY'];

    if(!is_null($this->ADAErrorCode)) {
      $error_severity = $ADA_ERROR_SEVERITY[$this->ADAErrorCode];
      $action = $ADA_ERROR_POLICY[ADA_ERROR_PHASE][$error_severity];
      $this->errorCode = $this->ADAErrorCode;
    }
    elseif(!is_null($this->AMAErrorObject)) {
      $error_severity = $ADA_ERROR_SEVERITY[$this->AMAErrorObject->code];
      $action   = $ADA_ERROR_POLICY[ADA_ERROR_PHASE][$error_severity];
      $this->errorCode = $this->AMAErrorObject->code;
    }
    else {
      $action = $ADA_ERROR_POLICY[ADA_ERROR_PHASE][ADA_ERROR_SEVERITY_FATAL];
      $this->errorCode = ADA_ERROR_ID_UNKNOWN_ERROR;
    }

    if(!is_null($this->severity)) {
      $action   = $ADA_ERROR_POLICY[ADA_ERROR_PHASE][$this->severity];
    }

    return $action;
  }

  /**
   * Handle this error
   *
   * @return void
   */
  public function handleError() {
    $action = $this->getAction();
    /**
     * Non chiamare translateFN sul messaggio di errore.
     */

    /**
     * Error logging
     */
    if($action & ADA_ERROR_LOG_TO_FILE) {
      ADALogger::log('ADA ERROR LOG TO FILE');

      ADAFileLogger::log_error($this->asTextToLogInFile());
    }

    if ($action & ADA_ERROR_LOG_TO_HTML_COMMENT) {
      ADALogger::log('ADA ERROR LOG TO HTML COMMENT');
    }

    if($action & ADA_ERROR_LOG_TO_HTML) {
      ADAScreenLogger::log_error($this->asTextToLogInHTML());
    }

    if($action & ADA_ERROR_LOG_TO_EMAIL) {
      ADALogger::log('ADA ERROR LOG TO EMAIL');
      // TODO: log via email
      /*
       * Richiamare classe mailer per il log, passando come contenuto
       * $this->asTextToLogInFile()
       */
    }

    if($action & ADA_ERROR_LOG_TO_DB) {
      ADALogger::log('ADA ERROR LOG TO DB');
      // TODO: log su database
      /*
       * Richiamare classe MultiPort per il log su tabella DB passando
       * come argomento $this->asArrayToLogInDB()
       */
    }

    /**
     * Redirect user
     */
    if(is_null($this->redirectTo)) {

      if($action & ADA_ERROR_REDIRECT_TO_LOGIN) {
        // FIXME: login location == index?
        header('Location:'.HTTP_ROOT_DIR);
        exit();
      }

      if($action & ADA_ERROR_REDIRECT_TO_HOMEPAGE) {
        $sess_userObj = $_SESSION['sess_userObj'];
        if($sess_userObj instanceof ADALoggableUser) {
          header('Location:'.$sess_userObj->getHomePage());
          exit();
        }
        else {
          header('Location:'.HTTP_ROOT_DIR);
          exit();
        }
      }

      if($action & ADA_ERROR_REDIRECT_TO_ERROR_PAGE) {
        header('Location:'.HTTP_ROOT_DIR . '/error.php');
        exit();
      }
    }
    else {
      /*
       * Controlliamo se il programmatore ha specificato un indirizzo commpleto
       * contenente HTTP_ROOT_DIR, altrimenti lo appende.
       */
      if(strncmp(HTTP_ROOT_DIR, $this->redirectTo, sizeof(HTTP_ROOT_DIR)) == 0) {
        header('Location: ' . $this->redirectTo);
        exit();
      }
      header('Location:'.HTTP_ROOT_DIR.'/'.$this->redirectTo);
      exit();
    }
  }

  /**
   * Saves the path to the file and the line number in which new ADA_Error
   * was called
   *
   * @return void
   */
  private function getFileAndLine() {
    $debug_backtrace = debug_backtrace();
    $caller_info = $debug_backtrace[1];

    $this->file  = $caller_info['file'];
    $this->line  = $caller_info['line'];
  }

  /**
   *
   * @return string $text
   */
  private function asTextToLogInFile() {

    $text  = $this->getLogDate();
    $text .= ' -- file: ' . $this->file . ' line: ' . $this->line;
    $text .= ' life cicle: ' . ADA_ERROR_PHASE;
    $text .= ' error code: ' . $this->errorCode;
    $text .= ' severity: ' . $this->severity;
    $text .= ' user: ' . $this->sessUserId;
    $text .= ' course: ' . $this->sessCourseId;
    $text .= ' course_instance: ' . $this->sessCourseInstanceId;
    $text .= ' node: ' . $this->sessNodeId;
    $text .= ' request vars: ' . $this->requestVars;
    return $text;
  }

  /**
   *
   * @return string $text - html string
   */
  private function asTextToLogInHTML() {

    $text  = '<b>'. $this->getLogDate();
    $text .= ' --</b> file: ' . $this->file . ' line: ' . $this->line;
    $text .= ' phase: ' . ADA_ERROR_PHASE;
    $text .= ' error code: ' . $this->errorCode;
    $text .= ' severity: ' . $this->severity;
    $text .= ' user: ' . $this->sessUserId;
    $text .= ' course: ' . $this->sessCourseId;
    $text .= ' course_instance: ' . $this->sessCourseInstanceId;
    $text .= ' node: ' . $this->sessNodeId;
    $text .= ' request vars: ' . $this->requestVars;
    return $text;
  }

  /**
   *
   * @return array $data
   */
  private function asArrayToLogInDB() {

    $data = array(
      'date_string'     => $this->getLogDate(),
      'file'            => $this->file,
      'line'            => $this->line,
      'phase'           => ADA_ERROR_PHASE,
      'error_code'      => $this->errorCode,
      'severity'        => $this->severity,
      'user'            => $this->sessUserId,
      'course'          => $this->sessCourseId,
      'course_instance' => $this->sessCourseInstanceId,
      'node'            => $this->sessNodeId,
      'request_vars'    => $this->requestVars
    );

    return $data;
  }

  private function getLogDate() {
    /*
     * It seems there are issues with date, date_format, date_create and
     * microtime.
     * Here we manually add the microseconds part to the date.
     */
    $date = date('d/m/Y H:i:s') . substr((string)microtime(), 1, 8);
    return $date;
  }

  private function requestAsString() {
    $string = '';
    foreach ($_REQUEST as $key => $value) {
      $string .= $key .': ' . ((is_array($value) || is_object($value)) ? json_encode($value, JSON_PRETTY_PRINT) : $value).' ';
    }

    return $string;
  }

  static public function isError($object) {
    return $object instanceof ADA_Error;
  }
}
?>