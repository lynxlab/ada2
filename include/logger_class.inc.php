<?php
/**
 * 
 * Requires PHP >= 5.2.2 
 * 
 * @author 		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		logger			
 * @version 	0.2
 */

abstract class ADASimpleLogger
{
  /**
   * 
   * @return unknown_type
   */
  
  protected static function getDebugDate() {
    /*
     * It seems there are issues with date, date_format, date_create and
     * microtime.
     * Here we manually add the microseconds part to the date.
     */
    return date('d/m/Y H:i:s') . substr((string)microtime(), 1, 8);
  }
}

/**
 * 
 * @author vito
 *
 */
class ADAScreenLogger extends ADASimpleLogger
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  
  // FIXME: Strict Standards
  /*
   * Strict standards: date() [function.date]: It is not safe to rely on the system's 
   * timezone settings. Please use the date.timezone setting, the TZ environment 
   * variable or the date_default_timezone_set() function. In case you used any 
   * of those methods and you are still getting this warning, you most likely 
   * misspelled the timezone identifier. 
   * We selected 'Europe/Berlin' for 'CEST/2.0/DST' instead in 
   * /var/www/html/ada/include/logger_class.inc.php on line 46
   */
  public static function log($text) {
    echo '<b>' . self::getDebugDate() . '</b> ' . $text . '<br />';
  }
  
  public static function log_error($text) {
    echo $text . '<br />';
  }
}

/**
 * 
 * @author vito
 *
 */
class ADAFileLogger extends ADASimpleLogger
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public static function log($text, $filename=ADA_FILE_LOGGER_OUTPUT_FILE) {
    
   // if(defined('ADA_FILE_LOGGER_OUTPUT_FILE') 
   //    && is_writable(ADA_FILE_LOGGER_OUTPUT_FILE)) {
   if(is_file($filename) && is_writable($filename)) {   
      $log =  self::getDebugDate() . " $text\n";
      
      // available from PHP 5
      //file_put_contents(ADA_FILE_LOGGER_OUTPUT_FILE, $log, FILE_APPEND);
      file_put_contents($filename, $log, FILE_APPEND);
      
      // richiamare clearstatcache() ?   
    }
    else {
    //  ADAScreenLogger::log('FileLogger: output file ' . $filename .' not writable, redirecting log to screen');
    //  ADAScreenLogger::log($text);
    }
  }
  
  public static function log_error($text, $filename=ADA_LOG_ERROR_FILE_LOG_OUTPUT_FILE) {
    
    if(is_file($filename) && is_writable($filename)) {
      /*
       * $text already has date and time infos
       */
      //$log =  self::getDebugDate() . " $text\n";
      $log = " $text\n";
      // available from PHP 5
      file_put_contents($filename, $log, FILE_APPEND);
      // richiamare clearstatcache() ?   
      return TRUE;
    }
    return FALSE;
  }
  
}

/**
 * 
 * @author vito
 *
 */
class ADALogger
{
  /**
   * handles logging of generic messages (not error messages or db messages)
   * 
   * @param $text	the message to log
   * @return void
   */
  public static function log($text) {
    if(ADA_LOGGING_LEVEL & ADA_LOG_GENERIC) {
      
      switch(ADA_LOG_GENERIC_SELECTED_LOGGER) {
        case ADA_LOGGER_SCREEN_LOG:
          ADAScreenLogger::log($text);
          break;
          
        case ADA_LOGGER_FILE_LOG:
          ADAFileLogger::log($text, ADA_LOG_GENERIC_FILE_LOG_OUTPUT_FILE);
          break;
          
        case ADA_LOGGER_NULL_LOG:
        default:
      }
      
    }
  }
  
  /**
   * handles logging of db messages 
   * 
   * @param $text	the message to log
   * @return void
   */
  public static function log_db($text) {
    if(ADA_LOGGING_LEVEL & ADA_LOG_DB) {
      
      switch(ADA_LOG_DB_SELECTED_LOGGER) {
        case ADA_LOGGER_SCREEN_LOG:
          ADAScreenLogger::log($text);
          break;
          
        case ADA_LOGGER_FILE_LOG:
          ADAFileLogger::log($text, ADA_LOG_DB_FILE_LOG_OUTPUT_FILE);
          break;
          
        case ADA_LOGGER_NULL_LOG:
        default:
      }
      
    }
  }
  /**
   * handles logging of error messages
   * 
   * @param $text	the message to log
   * @return void
   */
  public static function log_error($text) {
    /*
     * Always log errors.
     */
    //if(ADA_LOGGING_LEVEL & ADA_LOG_ERROR) {
    switch(ADA_LOG_ERROR_SELECTED_LOGGER) {
      case ADA_LOGGER_SCREEN_LOG:
        ADAScreenLogger::log_error($text);
        break;
        
      case ADA_LOGGER_FILE_LOG:
        ADAFileLogger::log_error($text, ADA_LOG_ERROR_FILE_LOG_OUTPUT_FILE);
        break;
        
      case ADA_LOGGER_NULL_LOG:
      default:
      
    }
    //}
  }
}
?>