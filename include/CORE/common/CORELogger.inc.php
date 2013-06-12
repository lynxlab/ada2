<?php
/**
 * CORELogger.inc.php, logging classes.
 *  
 * PHP version >= 5.2.2
 * 
 * @package		ARE
 * @subpackage  CORE
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		core_logger			
 * @version		0.2
 */

define('CORE_LOGGER_NULL_LOG'  , 0);
define('CORE_LOGGER_SCREEN_LOG', 1);
define('CORE_LOGGER_FILE_LOG'  , 2);

define('CORE_SELECTED_LOGGER', CORE_LOGGER_SCREEN_LOG);

/**
 * 
 * @author vito
 *
 */
abstract class CORESimpleLogger
{
  /**
   * 
   * @return unknown_type
   */
  abstract public static function Log($text);
}

/**
 * 
 * @author vito
 *
 */
class COREScreenLogger extends CORESimpleLogger
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public static function Log($text) {
    echo '<b>' . date('d/m/Y  H:i:s:u --') . '</b> ' . $text . '<br />';
  }
}

/**
 * 
 * @author vito
 *
 */
class COREFileLogger extends CORESimpleLogger
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public static function Log($text) {
    $log =  '<b>' . date('d/m/Y  H:i:s:u --') . '</b> ' . $text . '<br />';
  }
}

/**
 * 
 * @author vito
 *
 */
class CORELogger extends CORESimpleLogger
{
  /**
   * 
   * @param $text
   * @return unknown_type
   */
  public static function Log($text) {
    
    switch(CORE_SELECTED_LOGGER) {

      case CORE_LOGGER_SCREEN_LOG:
        COREScreenLogger::Log($text);
        break;
        
      case CORE_LOGGER_FILE_LOG:
        COREFileLogger::Log($text);
        break;
        
      case CORE_LOGGER_NULL_LOG:
      default:
    }  
  }
}
?>