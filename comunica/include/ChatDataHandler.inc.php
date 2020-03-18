<?php
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Lynx S.r.l.                                       |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stamatios Filippis <st4m0s@gmail.com>	                      |
// +----------------------------------------------------------------------+

// ADA ChatDataHandler

/*****************************************************************************
 * ChatDataHandler extends the AMA_DataHandler, to communicate with the DB,
 * and implements the API to access data regarding the functionality of the
 * chatrooms.
 *
 * @access public
 * @author Stamatios Filippis
 *****************************************************************************/

class ChatDataHandler extends Abstract_AMA_DataHandler
{
  var $id_chatroom; //the id_chatroom variable

//  public function __construct($id_chatroom = "") {
//    $this->id_chatroom = $id_chatroom;
//    parent::__construct();
//  }
//
//  /**
//   * function instance
//   *
//   * @return ChatDataHandler instance
//   */
//  static function instance()
//  {
//    static $chat_data_handler = null;
//    if ( $chat_data_handler == null )
//    {
//      $chat_data_handler = new ChatDataHandler();
//    }
//
//    return $chat_data_handler;
//  }

  private static $instance = NULL;
  /**
   * Contains the data source name used to create this instance of ChatDataHandler
   * @var string
   */
  private static $tester_dsn = NULL;

  /**
   *
   * @param  string $dsn - a valid data source name
   * @return an instance of ChatDataHandler
   */
  public function __construct($dsn = null) {
    //ADALogger::log_db('ChatDataHandler constructor');
    parent::__construct($dsn);
  }


  /**
   * (non-PHPdoc)
   * @see include/Abstract_AMA_DataHandler#__destruct()
   */
  public function __destruct() {
    parent::__destruct();
  }


  /**
   * Returns an instance of ChatDataHandler.
   *
   * @param  string $dsn - optional, a valid data source name
   * @return an instance of ChatDataHandler
   */
  static function instance($dsn = null) {
    if(self::$instance === NULL) {
      self::$instance = new ChatDataHandler($dsn);
    }
    else {
      self::$instance->setDSN($dsn);
    }
    //return null;
    return self::$instance;
  }
  public function setDSN($dsn = NULL) {
    $this->dsn = $dsn;
  }
  /**
   * Methods accessing database
   */






  /*****************************************************************************
   * Creates a new chatroom to the DB
   *
   * @access public
   * @param  $chatroom_ha an associative array containing all the chatroom's data
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   *****************************************************************************/
  function add_chatroom($chatroom_ha) {
    $chat_type          = $this->sql_prepared($chatroom_ha['chat_type']);
    $chat_title         = $this->sql_prepared($chatroom_ha['chat_title']);
    //vito, 29 mar 2009
    //$chat_topic = $this->or_null($this->sql_prepared($chatroom_ha['chat_topic']));
    $chat_topic         = $this->sql_prepared($chatroom_ha['chat_topic']);
    $id_chat_owner      = $this->or_zero($chatroom_ha['id_chat_owner']);
    $welcome_msg        = $this->or_null($this->sql_prepared($chatroom_ha['welcome_msg']));
    $start_time         = $this->or_zero($chatroom_ha['start_time']);
    $end_time           = $this->or_zero($chatroom_ha['end_time']);
    $max_users          = $this->or_zero($chatroom_ha['max_users']);
    $id_course_instance = $this->or_zero($chatroom_ha['id_course_instance']);

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // verify if a chatroom with the same data already exists
    $sql = "select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and tipo_chat=$chat_type and titolo_chat=$chat_title and tempo_avvio=$start_time";
    $res_id = $db -> getOne($sql);

    if (AMA_DB::isError($res_id))
    return $res_id;
    if ($res_id)
    return new AMA_Error(AMA_ERR_UNIQUE_KEY);

    // insert a row into table chatroom
    $sql1 = "insert into chatroom (id_istanza_corso,tipo_chat,titolo_chat,argomento_chat,id_proprietario_chat,
                              tempo_avvio,tempo_fine,msg_benvenuto,max_utenti)";
    $sql1 .= " values ($id_course_instance,$chat_type,$chat_title,$chat_topic,$id_chat_owner,$start_time,
    $end_time,$welcome_msg,$max_users);";

    $res = parent::executeCritical( $sql1 );
    if (AMA_DB::isError( $res ) ) return $res;

    // get the id of the inserted chatroom
    $id_chatroom = $db->getOne("select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and titolo_chat=$chat_title and id_proprietario_chat=$id_chat_owner and tipo_chat=$chat_type and tempo_avvio=$start_time");
    return $id_chatroom;
  }

  /****************************************************************************
   * Removes a chatroom that has no mesages and users associated with it
   *
   * @access public
   * @param  $id_chatroom
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   *****************************************************************************/
  function remove_unused_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // checks if the chatroom exists
    $ri_id = $db -> getOne("select id_chatroom from chatroom where id_chatroom=$id_chatroom");
    if (!$ri_id)
    return new AMA_Error(AMA_ERR_NOT_FOUND);
    // referential integrity checks
    $ri_id = $db -> getOne("select id_utente from utente_chatroom where id_chatroom=$id");
    if ($ri_id)
    return new AMA_Error(AMA_ERR_REF_INT_KEY);
    // referential integrity checks
    $ri_id = $db -> getOne("select id_messaggio from messaggi where id_chatroom=$id");
    if ($ri_id)
    return new AMA_Error(AMA_ERR_REF_INT_KEY);

    $sql = "delete from chatroom where id_chatroom=$id";

    $res = parent::executeCritical( $sql );
    if (AMA_DB::isError( $res ) ) return $res;

    return true;
  }

  /*****************************************************************************
   * Removes a chatroom from the DB, even if the chatroom is still running!
   *
   * @access public
   * @param  $id_chatroom
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   ******************************************************************************/
  function remove_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // checks if the chatroom exists
    $ri_id = $db -> getOne("select id_chatroom from chatroom where id_chatroom=$id_chatroom");
    if (!$ri_id)
    return new AMA_Error(AMA_ERR_NOT_FOUND);

    // we should provide at this point a BACK UP option before proceed with removing the chatroom

    // referential integrity checks
    $ri_id = $db -> getOne("select id_utente from utente_chatroom where id_chatroom=$id_chatroom");
    if ($ri_id) {
      // removes all the users from the specific chatroom
      $this -> remove_allusers_chatroom($id_chatroom);
    }
    // referential integrity checks
    $ri_id = $db -> getOne("select id_messaggio from messaggi where id_chatroom=$id_chatroom");
    if ($ri_id) {
      // removes all messages from the specific chatroom
      $this -> remove_allmessages_chatroom($id_chatroom);
    }

    $sql = "delete from chatroom where id_chatroom=$id_chatroom";

    $res = parent::executeCritical( $sql );
    if (AMA_DB::isError( $res ) ) return $res;

    return true;
  }

  /*****************************************************************************
   * Updates informations related to a specific chatroom by the given id_chatroom
   *
   * @access public
   * @param  $id_chatroom the chatroom's id
   *         $chatroom_ha the array containing all the information.
   *                                  empty fields are not updated.
   * @return an error if something goes wrong
   *****************************************************************************/
  function set_chatroom($id_chatroom, $chatroom_ha){
    $chat_type = $this -> sql_prepared($chatroom_ha['chat_type']);
    $chat_title = $this -> sql_prepared($chatroom_ha['chat_title']);
    $chat_topic = $this -> sql_prepared($chatroom_ha['chat_topic']);
    $id_chat_owner = $this -> sql_prepared($chatroom_ha['id_chat_owner']);
    $start_time = $this -> sql_prepared($chatroom_ha['start_time']);
    $end_time = $this -> sql_prepared($chatroom_ha['end_time']);
    $welcome_msg = $this -> sql_prepared($chatroom_ha['welcome_msg']);
    $max_users = $this -> sql_prepared($chatroom_ha['max_users']);
    $id_course_instance = $this -> sql_prepared($chatroom_ha['id_course_instance']);

    $res_id = 0;
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // verify that the record exists and store old values for rollback
    $res_id = $db -> getRow("select id_chatroom from chatroom where id_chatroom=$id_chatroom");
    if (AMA_DB::isError($res_id))
    return $res_id;
    if ($res_id == 0) {
      $db -> free();
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    // verify unique constraint once updated
    $new_chat_type = $chatroom_ha['chat_type'];
    $new_chat_title = $chatroom_ha['chat_title'];
    $new_chat_topic = $chatroom_ha['chat_topic'];
    $new_id_chat_owner = $chatroom_ha['id_chat_owner'];
    $new_start_time = $chatroom_ha['start_time'];
    $new_end_time = $chatroom_ha['end_time'];
    $new_welcome_msg = $chatroom_ha['welcome_msg'];
    $new_max_users = $chatroom_ha['max_users'];
    $new_id_course_instance = $chatroom_ha['id_course_instance'];

    // backup old values
    $old_values_ha = $this -> get_info_chatroom($id_chatroom);

    $old_chat_type = $old_values_ha['tipo_chat'];
    $old_chat_title = $old_values_ha['titolo_chat'];
    $old_chat_topic = $old_values_ha['argomento_chat'];
    $old_id_chat_owner = $old_values_ha['id_proprietario_chat'];
    $old_start_time = $old_values_ha['tempo_avvio'];
    $old_end_time = $old_values_ha['tempo_fine'];
    $old_welcome_msg = $old_values_ha['msg_benvenuto'];
    $old_max_users = $old_values_ha['max_utenti'];
    $old_id_course_instance = $old_values_ha['id_istanza_corso'];

    // make sure that the record is not allready updated
    if ($new_chat_type != $old_chat_type || $new_chat_title != $old_chat_title || $new_chat_topic != $old_chat_topic || $new_id_chat_owner != $old_id_chat_owner || $new_start_time != $old_start_time || $new_end_time != $old_end_time || $new_welcome_msg != $old_welcome_msg || $new_max_users != $old_max_users || $new_id_course_instance != $old_id_course_instance) {

      $res_id = $db -> getOne("select id_chatroom from chatroom where tipo_chat=$chat_type and
           id_proprietario_chat=$id_chat_owner and titolo_chat=$chat_title and argomento_chat=$chat_topic and
    		   tempo_avvio=$start_time and tempo_fine=$end_time and msg_benvenuto=$welcome_msg and max_utenti=$max_users
		       and id_istanza_corso=$id_course_instance");

      if (AMA_DB::isError($res_id))
      return $res_id;
      if ($res_id)
      return new AMA_Error(AMA_ERR_UNIQUE_KEY);
    }

    // update the rows in the tables
    $sql1 = "update chatroom set id_istanza_corso=$id_course_instance, tipo_chat=$chat_type,
                   titolo_chat=$chat_title, argomento_chat=$chat_topic, id_proprietario_chat=$id_chat_owner,
                   tempo_avvio=$start_time, tempo_fine=$end_time, msg_benvenuto=$welcome_msg,
                   max_utenti=$max_users where id_chatroom=$id_chatroom";
    $res = $db -> query($sql1);
    if (AMA_DB::isError($res))
    {
      // try manual rollback in case problems arise
      $old_chat_type = $this -> sql_prepared($old_values_ha['tipo_chat']);
      $old_chat_title = $this -> sql_prepared($old_values_ha['titolo_chat']);
      $old_chat_topic = $this -> sql_prepared($old_values_ha['argomento_chat']);
      $old_id_chat_owner = $this -> sql_prepared($old_values_ha['id_proprietario_chat']);
      $old_start_time = $this -> sql_prepared($old_values_ha['tempo_avvio']);
      $old_end_time = $this -> sql_prepared($old_values_ha['tempo_fine']);
      $old_welcome_msg = $this -> sql_prepared($old_values_ha['msg_benvenuto']);
      $old_max_users = $this -> sql_prepared($old_values_ha['max_utenti']);
      $old_id_course_instance = $this -> sql_prepared($old_values_ha['id_istanza_corso']);

      $sql2 = "update chatroom set id_istanza_corso=$old_id_course_instance, tipo_chat=$old_chat_type,
                   titolo_chat=$old_chat_title, argomento_chat=$old_chat_topic, id_proprietario_chat=$old_id_chat_owner,
                   tempo_avvio=$old_start_time, tempo_fine=$old_end_time, msg_benvenuto=$old_welcome_msg,
                   max_utenti=$old_max_users where id_chatroom=$id_chatroom";

      $res = parent::executeCritical( $sql2 );
      if (AMA_DB::isError( $res ) ) return $res;

      // in case manual rollback works return an update error
      return new AMA_Error(AMA_ERR_UPDATE);
    }
  }

  /************************************************************************
   * Gets all the information about a chatroom
   *
   * @access public
   * @param  $id_chatroom the chatroom's id
   * @return an array containing all the informations about a chatroom
   *
   *          res_ha['id_istanza_corso']
   *          res_ha['tipo_chat']
   *          res_ha['titolo_chat']
   *          res_ha['argomento_chat']
   *          res_ha['id_proprietario_chat']
   *          res_ha['tempo_avvio']
   *          res_ha['tempo_fine']
   *          res_ha['msg_benvenuto']
   *          res_ha['max_utenti']
   ******************************************************************************/
  function get_info_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $chatroom_ar = $db -> getRow("select id_istanza_corso,tipo_chat,titolo_chat,argomento_chat,id_proprietario_chat,
                          tempo_avvio,tempo_fine,msg_benvenuto,max_utenti from chatroom where id_chatroom=$id_chatroom");
    if (AMA_DB::isError($chatroom_ar))
    return $chatroom_ar;
    if (!$chatroom_ar){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    $chatroom_ha['id_chatroom'] = $id_chatroom;
    $chatroom_ha['id_istanza_corso'] = $chatroom_ar[0];
    $chatroom_ha['tipo_chat'] = $chatroom_ar[1];
    $chatroom_ha['titolo_chat'] = $chatroom_ar[2];
    $chatroom_ha['argomento_chat'] = $chatroom_ar[3];
    $chatroom_ha['id_proprietario_chat'] = $chatroom_ar[4];
    $chatroom_ha['tempo_avvio'] = $chatroom_ar[5];
    $chatroom_ha['tempo_fine'] = $chatroom_ar[6];
    $chatroom_ha['msg_benvenuto'] = $chatroom_ar[7];
    $chatroom_ha['max_utenti'] = $chatroom_ar[8];

    return $chatroom_ha;
  }

  /*****************************************************************************
   * Gets the list of all the chatrooms running at the moment
   *
   * @return an array containing the ids of all active chatrooms
   *****************************************************************************/
  function get_all_chatrooms(){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $chatrooms_ha = $db -> getCol("select id_chatroom from chatroom");
    if (AMA_DB::isError($chatrooms_ha)){
      return $chatrooms_ha;
    }
    if (!$chatrooms_ha){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    return $chatrooms_ha;
  }

  /*****************************************************************************
   * Finds the chatroom running at the moment, relative to a specific class
   *
   * @param id $id_class
   * @return the id of the chatroom corresponding to the selected classroom
   ******************************************************************************/
  function get_class_chatroom($id_course_instance,$actual_time){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $class_chatroom = $db -> getOne("select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and tipo_chat='C' and (tempo_avvio<=$actual_time) and ((tempo_fine = 0) or (tempo_fine>$actual_time))");

    if (AMA_DB::isError($class_chatroom)){
      return $class_chatroom;
    }
    if (!$class_chatroom){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $class_chatroom;
  }

  /*****************************************************************************
   * Finds the chatroom relative to a specific class and specific type
   *
   * @param id $id_class
   * @param id $type
   * @return the id of the chatroom corresponding to the selected classroom
   ******************************************************************************/
  function get_class_chatroom_for_instance($id_course_instance,$type){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $class_chatroom = $db->getOne("select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and tipo_chat='$type'");

    if (AMA_DB::isError($class_chatroom)){
      return $class_chatroom;
    }
    if (!$class_chatroom){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $class_chatroom;
  }

  /*****************************************************************************
   * Finds the chatroom running at the moment, relative to a specific class
   *
   * @param id $id_class
   * @return the id of the chatroom corresponding to the selected classroom
   ******************************************************************************/
  function get_class_chatroom_with_durationFN($id_course_instance,$start_time, $end_time){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $class_chatroom = $db->getOne("select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and tipo_chat='C' and tempo_avvio=$start_time and tempo_fine = $end_time");

    if (AMA_DB::isError($class_chatroom)){
      return $class_chatroom;
    }
    if (!$class_chatroom){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $class_chatroom;
  }

  function get_chatroom_with_title_prefixFN($title_prefix){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a row from table chatroom
    $id_chatroom = $db->getOne("select id_chatroom from chatroom where titolo_chat like '$title_prefix%'");

    if (AMA_DB::isError($id_chatroom)){
      return $id_chatroom;
    }
    if (!$id_chatroom){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $id_chatroom;
  }

  /*****************************************************************************
   * Finds all the chatrooms relative to a specific class
   *
   * @param id $id_class
   * @return an array contaning with the ids
   ******************************************************************************/
  function get_all_class_chatrooms($id_course_instance){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a col from table chatroom
    $class_chatrooms = $db -> getCol("select id_chatroom from chatroom where id_istanza_corso=$id_course_instance and tipo_chat='C'");

    if (AMA_DB::isError($class_chatrooms)){
      return $class_chatrooms;
    }
    if (!$class_chatrooms){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $class_chatrooms;
  }
  /*****************************************************************************
   * Finds all the private chatrooms
   *
   * @param
   * @return an array with all the id's of the private rooms available for the user
   ******************************************************************************/
  function get_all_private_chatrooms($user_id){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    //local variables in orded to be used into the sql query
    // type of the chatroom
    $i= INVITATION_CHAT;
    // possibile status of the user
    $act= STATUS_ACTIVE;
    $op= STATUS_OPERATOR;
    $inv= STATUS_INVITED;
    $mu= STATUS_MUTE;
    $ex= STATUS_EXIT;
    $bn= STATUS_BAN;
    // get the array with all the private chatrooms where the users is invited
    $private_chatrooms = $db -> getCol("select c.id_chatroom from chatroom c, utente_chatroom u
	where c.id_chatroom=u.id_chatroom and u.id_utente=$user_id and c.tipo_chat='$i' and
	(u.stato_utente='$act' or u.stato_utente='$op' or u.stato_utente='$inv' or u.stato_utente='$mu'
	or u.stato_utente='$ex' or u.stato_utente='$bn')");

    if (AMA_DB::isError($private_chatrooms)){
      return $private_chatrooms;
    }
    if (!$private_chatrooms){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    return $private_chatrooms;
  }
  /*****************************************************************************
   * Finds the first public chatroom running at the moment
   *
   * @param
   * @return the id of the public chatroom
   ******************************************************************************/
  function find_public_chatroom(){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    //clause for the query
    $public = PUBLIC_CHAT;

    // get a row from table chatroom
    $public_chatroom = $db -> getOne("select id_chatroom from chatroom where tipo_chat='$public'");
    if (AMA_DB::isError($public_chatroom))
    return $public_chatroom;

    if (!$public_chatroom){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    return $public_chatroom;
  }

  /*****************************************************************************
   * - if a user it's not present to the chatroom adds him to it.
   *
   * - if $id_utente it is present into the table utente_chatroom, that record gets updated
   *
   * - in both cases a new record is added into the table utente_chatroom_log,
   *   describing the operation
   *
   * @access public
   * @param  $operator_id ,$user_id,$id_chatroom,$entrance_time,$action,$status
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   **********************************************************************************/
  function add_user_chatroom($operator_id,$user_id,$id_chatroom,$entrance_time,$action,$status){
    $status = $this -> sql_prepared($status);
    $action = $this -> sql_prepared($action);
    $res_id = 0;

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // ///////////////////////////////////////////////////
    // UPDATE OR INSERT INTO THE TABLE UTENTE_CHATROOM //
    // ///////////////////////////////////////////////////
    // checks if the user allready exists into the chatroom
    $res_user = $db -> getOne("select id_utente from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom");
    if (AMA_DB::isError($res_user))
    return $res_user;

    if ($res_user){
      // updates the existing record

      // backup old values
      $old_status_value = $this -> get_user_status($user_id, $id_chatroom);
      $old_time_value = $db -> getOne("select tempo_entrata from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");
      // verify unique constraint once updated
      $new_status = $status;
      $new_entrance_time = $entrance_time;

      $old_status = $this -> sql_prepared($old_status_value);
      $old_entrance_time = $old_time_value;

      // make sure that the record is not allready updated
      if ($new_status != $old_status || $new_entrance_time != $old_entrance_time){
        $res_id = $db -> getOne("select id_chatroom from utente_chatroom where tempo_entrata=$entrance_time and stato_utente=$status");
        if (AMA_DB::isError($db))
        return $db;
        if ($res_id)
        return new AMA_Error(AMA_ERR_UNIQUE_KEY);
      }
      // update the rows in the tables
      $sql1 = "update utente_chatroom set stato_utente=$status, tempo_entrata=$entrance_time where id_chatroom=$id_chatroom and id_utente=$user_id";
      $res = $db -> query($sql1);

      if (AMA_DB::isError($res)){
        // try manual rollback in case problems arise
        $old_status = $this -> sql_prepared($old_status_value);
        $old_entrance_time = $old_time_value;
        $sql2 = "update utente_chatroom set stato_utente=$old_status, tempo_entrata=$old_entrance_time where id_chatroom=$id_chatroom and id_utente=$user_id";


        $res = parent::executeCritical( $sql2 );
        if (AMA_DB::isError( $res ) ) return $res;

        // in case manual rollback works return an update error
        return new AMA_Error(AMA_ERR_UPDATE);
      }
    } // end case of updating
    else{
      // inserting a new record into the table utente_chatroom
      $sql = "insert into utente_chatroom (id_utente,id_chatroom,stato_utente,tempo_entrata)";
      $sql .= "values ($user_id,$id_chatroom,$status,$entrance_time)";

      $res = parent::executeCritical( $sql );
      if ( AMA_DB::isError( $res ) ) return $res;
    }
    // /////////////////////////////////////////////
    // INSERT INTO THE TABLE UTENTE_CHATROOM_LOG //
    // /////////////////////////////////////////////
    // checks if the record exists into the table utente_chatroom_log
    $res = $db -> getOne("select id_utente from utente_chatroom_log where id_utente=$user_id and tempo=$entrance_time and azione=$action");
    if (AMA_DB::isError($res))
    return $res;

    if ($res)
    return new AMA_Error(AMA_ERR_UNIQUE_KEY);

    $sql = "insert into utente_chatroom_log (id_utente,azione,id_operatore,id_chatroom,tempo)";
    $sql .= "values ($user_id,$action,$operator_id,$id_chatroom,$entrance_time)";

    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) return $res;
    // get the inserted user
    $id = $db -> getAll("select id_utente, id_chatroom from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom");
    return $id;
  }

  /**************************************************************************************
   * The user quits the chatroom, his $id_user will be removed from the table utente_chatroom
   *
   * @access public
   * @param  $user_id , $id_chatroom,$exit_time,$user_status
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   ***************************************************************************************/
  function quit_chatroom($operator_id,$user_id,$id_chatroom,$exit_time,$action){
    $action = $this -> sql_prepared($action);

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // /////////////////////////////////////////////
    // REMOVE FROM THE TABLE UTENTE_CHATROOM     //
    // /////////////////////////////////////////////

    // checks if the user exists into the chatroom
    $res_id = $db -> getOne("select id_utente from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");
    if (!$res_id)
    return new AMA_Error(AMA_ERR_NOT_FOUND);

    $sql = "delete from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom";
    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) return $res;

    ///////////////////////////////////////////////
    // INSERT INTO THE TABLE UTENTE_CHATROOM_LOG //
    ///////////////////////////////////////////////

    // checks if the record exists into the table utente_chatroom_log
    $res = $db -> getOne("select id_utente from utente_chatroom_log where id_utente=$user_id and tempo=$exit_time and azione=$action");

    if ($res)
    return new AMA_Error(AMA_ERR_UNIQUE_KEY);

    $sql = "insert into utente_chatroom_log (id_utente,azione,id_operatore,id_chatroom,tempo)";
    $sql .= "values ($user_id,$action,$operator_id,$id_chatroom,$exit_time)";
    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) return $res;

    return true;
  }
  /**************************************************************************************
   * Removes permanently a user from the selected chatroom. This action will not be
   * registered into the table utente_chatroom_log
   *
   * @access public
   * @param  $user_id , $id_chatroom
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   **************************************************************************************/
  function remove_user_chatroom($user_id,$id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // checks if the user exists into the chatroom
    $ri_id = $db -> getOne("select id_utente from utente_chatroom where id_utente=$user_id");
    if (!$ri_id)
    return new AMA_Error(AMA_ERR_NOT_FOUND);

    $sql = "delete from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom";
    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) return $res;
    return true;
  }

  /**************************************************************************************
   * Removes permanently all users from the selected chatroom. This action will not be
   * registered into the table utente_chatroom_log
   *
   * @access public
   * @param  $id_chatroom
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   **************************************************************************************/
  function remove_allusers_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // checks if exist any user into the chatroom
    $ri_id = $db -> getOne("select id_chatroom from utente_chatroom where id_chatroom=$id_chatroom");
    if (!$ri_id)
    return new AMA_Error(AMA_ERR_NOT_FOUND);

    $sql = "delete from utente_chatroom where id_chatroom=$id_chatroom";
    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) return $res;

    return true;
  }

  /**************************************************************************************
   * get the list of all active users inside a chatroom
   *
   * @access public
   * @param  $id_chatroom
   * @return an error if something goes wrong
   **************************************************************************************/
  function list_users_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    //local variables assigned to constants
    $operator = STATUS_OPERATOR;
    $active = STATUS_ACTIVE;
    $mute = STATUS_MUTE;
    // select a row from table utente_chatroom
    // vito, 26 settembre 2008
    //$sql = "select id_utente from utente_chatroom where id_chatroom=$id_chatroom and (stato_utente='$operator' or stato_utente='$active' or stato_utente='$mute')";
    $sql = "select U.id_utente, U.username, U.nome, U.cognome
                  from utente_chatroom AS UC, utente AS U
                 where UC.id_chatroom=$id_chatroom and UC.stato_utente IN('$operator','$active','$mute')
                   and U.id_utente = UC.id_utente ORDER BY U.username ASC";
    // vito, 26 settembre 2008
    //$res = $db -> getCol($sql);
    $res = $db -> getAll($sql, null, AMA_FETCH_ASSOC);

    if (AMA_DB::isError($res))
    return $db;
    if (!empty($res)){
      return $res;
    }
    else{
      // no users found in the chat
      return false;
    }
  }

  function list_users_invited_to_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    //local variables assigned to constants
    $invited = STATUS_INVITED;
    $sql = "select U.id_utente, U.username, U.nome, U.cognome
                  from utente_chatroom AS UC, utente AS U
                 where UC.id_chatroom=$id_chatroom and UC.stato_utente ='$invited'
                   and U.id_utente = UC.id_utente ORDER BY U.username ASC";
    $res = $db -> getAll($sql, null, AMA_FETCH_ASSOC);

    if (AMA_DB::isError($res))
    return $db;
    if (!empty($res)){
      return $res;
    }
    else{
      // no users found in the chat
      return false;
    }
  }


  /**************************************************************************************
   * get the list of the banned users into a chatroom
   *
   * @access public
   * @param  $id_chatroom
   * @return an error if something goes wrong
   **************************************************************************************/
  function list_banned_users_chatroom($id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    //local variables assigned to constants
    $ban = STATUS_BAN;

    // select a row from table utente_chatroom
    // vito, 26 settembre 2008
    //$sql = "select id_utente from utente_chatroom where id_chatroom=$id_chatroom and stato_utente='$ban'";
    $sql = "select U.id_utente, U.username
                  from utente_chatroom AS UC, utente AS U
                 where UC.id_chatroom=$id_chatroom and UC.stato_utente='$ban'
                   and U.id_utente = UC.id_utente ORDER BY U.username ASC";

    // vito, 26 settembre 2008
    //$res = $db -> getCol($sql);
    $res = $db -> getAll($sql, null, AMA_FETCH_ASSOC);

    if (AMA_DB::isError($res))
    return $res;
    if (!empty($res)) {
      return $res;
    }
    else{
      // no banned users found in the chat
      return false;
    }
  }

  /**************************************************************************************
   * get the list of all the chatrooms that the user could have access or
   * he is allready present
   *
   * @param  $user_id
   * @return an error if something goes wrong
   **************************************************************************************/
  function list_chatrooms_user($user_id){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // select a row from table utente_chatroom
    $sql = "select id_chatroom from utente_chatroom where id_utente=$user_id";
    $res = $db -> getCol($sql);
    if (AMA_DB::isError($res))
    return $res;
    if (!empty($res)) {
      return $res;
    }
    else{
      // no chatroom available for the user
      return false;
    }
  }
  /**************************************************************************************
   * sets the status of a user into a given chatroom
   * he is allready present
   *
   * @param  $user_id ,$id_chatroom,$status
   * @return an error if something goes wrong
   **************************************************************************************/
  function set_user_status($operator_id,$user_id,$id_chatroom,$action,$status,$time){
    $status = $this -> sql_prepared($status);
    $action = $this -> sql_prepared($action);
    $res_id = 0;

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // //////////////////////////////////////
    // UPDATING THE TABLE UTENTE_CHATROOM //
    // //////////////////////////////////////
    // verify that the record exists and store old values for rollback
    $res_id = $db -> getOne("select id_utente from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");
    if (AMA_DB::isError($res_id))
    return $db;
    if ($res_id == 0) {
      $db -> free();
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    // backup old values
    $old_value = $this -> get_user_status($user_id, $id_chatroom);
    // verify unique constraint once updated
    $new_status = $status;
    $old_status = $this -> sql_prepared($old_value);
    // make sure that the record is not allready updated
    if ($new_status != $old_status){
      $res_id = $db -> getOne("select id_chatroom from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id and stato_utente=$status");
      if (AMA_DB::isError($res_id))
      return $res_id;
      if ($res_id)
      return new AMA_Error(AMA_ERR_UNIQUE_KEY);
    }
    // update the rows in the tables
    $sql1 = "update utente_chatroom set stato_utente=$status where id_chatroom=$id_chatroom and id_utente=$user_id";
    $res = $db -> query($sql1);
    if (AMA_DB::isError($res)) {
      // try manual rollback in case problems arise
      $old_status = $this -> sql_prepared($old_value);
      $sql2 = "update utente_chatroom set stato_utente=$old_status where id_chatroom=$id_chatroom and id_utente=$user_id";
      $res = parent::executeCritical( $sql2 );
      if ( AMA_DB::isError( $res ) ) return $res;

      // in case manual rollback works return an update error
      return new AMA_Error(AMA_ERR_UPDATE);
    }
    // /////////////////////////////////////////////
    // INSERT INTO THE TABLE UTENTE_CHATROOM_LOG //
    // /////////////////////////////////////////////
    // checks if the user allready exists into the chatroom
    $res = $db -> getOne("select id_utente from utente_chatroom_log where id_utente=$user_id and tempo=$time and azione=$action");
    // update user entrance into the chatroom
    if ($res)
    return new AMA_Error(AMA_ERR_UNIQUE_KEY);

    $sql = "insert into utente_chatroom_log (id_utente,azione,id_operatore,id_chatroom,tempo)";
    $sql .= "values ($user_id,$action,$operator_id,$id_chatroom,$time)";

    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res) ) return $res;


    return true;
  }

  /**************************************************************************************
   * gets the status of a user on a specific chatroom
   *
   * @param  $user_id , $id_chatroom
   * @return an error if something goes wrong
   **************************************************************************************/
  function get_user_status($user_id, $id_chatroom){
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get a status row from table utente_chatroom
    $res = $db -> getOne("select stato_utente from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");
    if (AMA_DB::isError($res))
    return $res;

    if (!$res){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    return $res;
  }
  /******************************************************************************
   * sets the time of the last event done by the user side into a given chatroom
   *
   *
   * @param  $user_id ,$id_chatroom,$status,$last_event_time
   * @return an error if something goes wrong
   ******************************************************************************/
  function set_last_event_time($user_id, $id_chatroom,$last_event_time){
    $res_id = 0;

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // verify that the record exists and store old values for rollback
    $res_id = $db->getOne("select id_utente from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");
    if (AMA_DB::isError($res_id))
    return $res_id;
    if ($res_id == 0){
      $db -> free();
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }
    // backup old values
    $old_value = $db->getOne("select tempo_ultimo_evento from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id");

    // verify unique constraint once updated
    $new_time = $last_event_time;
    $old_time = $this ->sql_prepared($old_value);

    // make sure that the record is not allready updated
    if ($new_time != $old_time){
      $res_id = $db -> getOne("select id_chatroom from utente_chatroom where id_chatroom=$id_chatroom and id_utente=$user_id and tempo_ultimo_evento=$last_event_time");
      if (AMA_DB::isError($res_id))
      return $res_id;
      if ($res_id)
      return new AMA_Error(AMA_ERR_UNIQUE_KEY);
    }

    // update the rows in the tables
    $sql1 = "update utente_chatroom set tempo_ultimo_evento=$last_event_time where id_chatroom=$id_chatroom and id_utente=$user_id";
    $res = $db -> query($sql1);
    if (AMA_DB::isError($res)){
      // try manual rollback in case problems arise
      $old_time = $this -> sql_prepared($old_value);
      $sql2 = "update utente_chatroom set tempo_ultimo_evento=$old_time where id_chatroom=$id_chatroom and id_utente=$user_id";
      $res = parent::executeCritical( $sql2 );
      if ( AMA_DB::isError( $res ) ) return $res;

      // in case manual rollback works return an update error
      return new AMA_Error(AMA_ERR_UPDATE);
    }

    return true;
  }

  /*****************************************************************************
   * Gets the time of the last_event that a user done into a specific chatroom
   *
   * @return the last_time_event of the user
   *****************************************************************************/
  function get_last_event_time($user_id, $id_chatroom){

    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    // get an entry from table utente_chatroom
    $event_time = $db -> getOne("select tempo_ultimo_evento from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom");
    if (AMA_DB::isError($event_time)){
      return $event_time;
    }
    if (!$event_time){
      return new AMA_Error(AMA_ERR_NOT_FOUND);
    }

    return $event_time;
  }

  /**************************************************************************************
   /**************************************************************************************
   /**************************************************************************************
   /*************************************************************************************

   /* Removes all messages assosciated to the selected chatroom.
   *
   * @access public
   * @param  $id_chatroom
   * @return an AMA_Error object or a DB_Error object if something goes wrong
   */
  function remove_allmessages_chatroom($id_chatroom) {
    $db =& parent::getConnection();
    if (AMA_DB::isError( $db )) return $db;

    $sql1 = "delete from messaggi where id_chatroom=$id_chatroom";
    $res = parent::executeCritical( $sql1 );
    if (AMA_DB::isError($res)) return $res;
  }

}
//end of class ChatDataHandler
?>
