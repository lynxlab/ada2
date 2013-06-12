<?php
/**
 * UserDataHandler
 *
 * @package
 * @author		Guglielmo Celata <guglielmo@celata.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license
 * @link
 * @version		0.1
 */

/**
 * UserDataHandler implements the API to access data regarding users
 * The class extends the AMA_DataHandler class
 *
 * @access public
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
class UserDataHandler extends Abstract_AMA_DataHandler
{
  private static $instance = NULL;

  private static $tester_dsn = NULL;
  /**
   * 
   * @param string $dsn - a valid data source name
   */  
  public function __construct($dsn = NULL) {
    $this->tester_dsn = $dsn;
    parent::__construct($dsn);
  }

  /**
   * 
   * @param  string $dsn - a valid data source name 
   * @return UserDataHandler instance
   */
  public static function instance($dsn = NULL) {
    if(self::$instance == NULL || self::$tester_dsn != $dsn) {
      self::$instance = new UserDataHandler($dsn);
    }
    return self::$instance;
  }

  /**
   * get a list of all users data in the utente table
   * which verifies a given clause
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *
   * @return  a refrerence to a 2-dim array,
   *           each row will have id_utente in the 0 element
   *           and the fields specified in the list in the others
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_users_list($fields_list_ar, $clause="") {
    // logger("UserDataHandler::find_users_list entered", 3);
    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;
    
    // build comma separated string out of $field_list_ar array
    $n_fields = count($fields_list_ar);
    if ($n_fields>1) {
      $more_fields = ", ".implode(", ", $fields_list_ar);
    }
    elseif ($n_fields>0) {
      $more_fields = ", ".$fields_list_ar[0];
    }
    else {
      $more_fields = "";
    }
     
    // add an 'and' on top of the clause
    // handle null clause, too
    if ($clause) {
      $clause = 'where '.$clause;
    }
    
    // do the query
    $sql = "select id_utente$more_fields from utente $clause";
    // logger("performing query: $sql", 4);
    $users_ar = $db->getAll($sql);
    if (AMA_DB::isError($users_ar)) {
    //return $db;
      return new AMA_Error(AMA_ERR_GET);
    }
    // logger("query succeeded", 4);
    // return nested array in the form
    return $users_ar;
  }

  /**
   * get a list of all users data in the utente table
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   *
   * @return  a refrerence to a 2-dim array,
   *           each row will have id_utente in the 0 element
   *           and the fields specified in the list in the others
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &get_users_list($fields_list_ar) {
    return $this->find_users_list($fields_list_ar);
  }
}
?>