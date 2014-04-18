<?php
/**
 * Spool
 *
 * @package
 * @author		Guglielmo Celata <guglielmo@celata.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license
 * @link
 * @version		0.1
 */

/**
 * Spool extends the AMA_DataHandler, to communicate with the DB,
 * and implements the API to access data regarding messages.
 * Some functions are implemented in the original Spool, while some others
 * in the derivated classes.
 *
 * The class hierarchy is the following:
 *
 *                  AMA_DataHandler
 *                          ^
 *                          |
 *                        Spool
 *                          ^
 *                          |
 *         ---------------------------------
 *         ^                ^              ^
 *         |                |              |
 *    SimpleSpool       AgendaSpool     ChatSpool
 *
 *
 * @access public
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
class Spool extends Abstract_AMA_DataHandler
{

  var $ntc;       // characteristic time for non-read messages
  var $rtc;       // characteristic time for read messages
  var $type;      // type of messages
  var $user_id;   // user of the spool
  var $cleaned;   // is hygenic to clean only once in a session

  public function __construct($user_id="", $dsn = NULL) {

    // logger("entered Spool constructor - user_id=$user_id", 3);
    $this->user_id = $user_id;
    //AMA_DataHandler::AMA_DataHandler();
    parent::__construct($dsn);
  }


  /**
   * add a message to the spool by writing record in the
   * "messaggi" and "destinatari_messaggi" tables in a transational way
   *
   * @access  public
   *
   * @param   $message_ha        - message data as an hash with keys:
   *                               ID, data_ora, titolo, priorita, testo
   * @param   $recipients_ids_ar - list of recipients ids
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function add_message($message_ha, $recipients_ids_ar, $check_on_uniqueness=TRUE) {

    // 28/12/01 Graffio
    // Modifica per differenziare il trattamento delle date
    // che provengono da send_event e send_message
    // Andra' corretto in send_event
    if ($message_ha['data_ora'] == "now") {
      $timestamp = $this->date_to_ts($message_ha['data_ora']);
    }
    else {
      $timestamp = $message_ha['data_ora'];
    }
    // Fine modifica

    $title     = $this->or_null(/*$this->sql_prepared(*/$message_ha['titolo']/*)*/);
    $id_group  = $this->or_zero($message_ha['id_group']);
    $priority  = $this->or_zero($message_ha['priorita']);
    $body      = $this->or_null(/*$this->sql_prepared(*/$message_ha['testo']/*)*/);
    $type      = /*"'".*/$this->type/*."'"*/;
    $sender_id = $this->user_id;
    $flags     = $this->or_zero($message_ha['flags']);

    $db =& parent::getConnection();
    if (AMA_DB::isError($db)) return $db;


    /*
    $sql = "select id_messaggio from messaggi ".
                          " where data_ora=".$timestamp.
                          "   and tipo=".$type.
                          "   and id_group=".$id_group.
                          "   and titolo=".$title.
                          "   and id_mittente=".$sender_id;
    */
    if($check_on_uniqueness) {
      $sql = 'SELECT id_messaggio FROM messaggi WHERE data_ora=? AND tipo=? AND id_group=? AND titolo=? AND id_mittente=?';


      //log_this("checking unique key by performing query: $sql", 4);
      // verify key uniqueness
      //$id =  $db->getOne($sql);
      $id = parent::getOnePrepared($sql, array($timestamp,$type,$id_group,$title,$sender_id));

      if (AMA_DataHandler::isError($id)) {
        return new AMA_Error(AMA_ERR_GET);
      }
      if ($id) {
        return new AMA_Error(AMA_ERR_UNIQUE_KEY);
      }
      // log_this("key uniqueness verified", 4);
    }

    // insert a row into table messaggi
    /*
    $sql =  "insert into messaggi (data_ora, tipo, id_group, titolo, id_mittente, priorita, testo)";
    $sql .= " values ($timestamp, $type, $id_group, $title, $sender_id, $priority, $body);";
    */

    $sql = 'INSERT INTO messaggi(data_ora,tipo,id_group,titolo,id_mittente,priorita,testo,flags) VALUES(?,?,?,?,?,?,?,?)';

    //log_this("performing query: $sql", 4);

    //$res = parent::executeCritical( $sql );
    $res = parent::executeCriticalPrepared($sql,array($timestamp,$type,$id_group,$title,$sender_id,$priority,$body,$flags));
    if (AMA_DB::isError($res)) {
      //log_this("query failed: $res", 4);
      // $res is an AMA_Error object
      return $res;
    }

    //log_this("query succeeded", 4);

    // get the id of the last inserted message

    // Modified 19/01/2005, Stamatios Filippis
    // we check the message type,if it is a generic chatroom message
    // we stop here the procedure, since we do not need to access the "destinatari_messaggi" table
  	 // and we return the id of the last insert message
    //case of public chat, once we get the id and quit the rest of the function
    if ($this->type == ADA_MSG_CHAT) {
      /*
      $sql = "select id_messaggio from messaggi ".
                          " where data_ora=".$timestamp.
                          "   and tipo=".$type.
      //     "   and titolo=".$title.
                          "   and id_group=".$id_group.
                          "   and id_mittente=".$sender_id;
      */
      $sql = 'SELECT id_messaggio FROM messaggi WHERE data_ora=? AND tipo=? AND id_group=? AND id_mittente=?';
      //$id = $db->getOne($sql);
      $id = parent::getOnePrepared($sql, array($timestamp, $type, $id_group, $sender_id));
      if (AMA_DB::isError($id) || !$id){
        return new AMA_Error(AMA_ERR_NOT_FOUND);
      }

      return $id;

    }//end ADA_MSG_CHAT

    //case of private chat message, we get the id and we go on.
    //we have to access the "destinatari_messaggi" table
    elseif ($this->type == ADA_MSG_PRV_CHAT)
    {
      /* $sql = "select id_messaggio from messaggi ".
                          " where data_ora=".$timestamp.
                          "   and tipo=".$type.
      //                        "   and titolo=".$title.
                          "   and id_group=".$id_group.
                          "   and id_mittente=".$sender_id;
      */
      $sql = 'SELECT id_messaggio FROM messaggi WHERE data_ora=? AND tipo=? AND id_group=? AND id_mittente=?';
      //$id = $db->getOne($sql);
      $id = parent::getOnePrepared($sql, array($timestamp, $type, $id_group, $sender_id));
      if (AMA_DB::isError($id) || !$id){
        return new AMA_Error(AMA_ERR_NOT_FOUND);
      }
    }//end ADA_MSG_PRV_CHAT

    else
    {
      /*$sql = "select id_messaggio from messaggi ".
                          " where data_ora=".$timestamp.
                          "   and tipo=".$type.
                          "   and titolo=".$title.
      //                        "   and id_group=".$id_group.
                          "   and id_mittente=".$sender_id;
      // logger("performing query: $sql", 4);
       */
      $sql = 'SELECT id_messaggio FROM messaggi WHERE data_ora=? AND tipo=? AND titolo=? AND id_mittente=?';
      //$id = $db->getOne($sql);
      $id = parent::getOnePrepared($sql, array($timestamp, $type, $title, $sender_id));

      if (AMA_DB::isError($id) || !$id){
        // logger("query failed", 4);
        return new AMA_Error(AMA_ERR_NOT_FOUND);
      }

    }// end type control

    // start the transaction
    $this->_begin_transaction();

    // push instruction to remove the record into rollback segment
    $this->_rs_add("_remove_message", $id);


    // insert references of the message related to all recipients
    // into the 'destinatari_messaggi' table
    foreach ($recipients_ids_ar as $rid){

      // add message to 'destinatari_messaggi' table
      $sql = "insert into destinatari_messaggi (id_messaggio, id_utente) ".
                  "values ($id, $rid)";

      // logger("performing query: $sql", 4);
      $res = $db->query($sql);
      if (AMA_DB::isError($res)){

        // logger("query failed", 4);

        // rollback in case of error
        $this->_rollback();
        return new AMA_Error(AMA_ERR_ADD);

      }
      // logger("query succeeded", 4);

      // insert instruction into rollback segment
      $this->_rs_add("_clean_message", $id, $rid);

    }

    // final success
    $this->_commit();
    return $id;

  }//end add_message


  /**
   * log a message by writing record in the
   * "utente_messaggio_log" tables
   *
   * @access  public
   *
   * @param   $message_ha        - message data as an hash with keys:
   *                               tempo, mittente, id_corso, id_istanza_corso, titolo, testo, lingua
   * @param   $recipients_ids_ar - list of recipients ids
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function log_message($message_ha, $recipients_ids_ar) {

    // logger("entered Spool::log_message", 3);

    // prepare data to be inserted

    // 28/12/01 Graffio
    // Modifica per differenziare il trattamento delle date
    // che provengono da send_event e send_message
    // Andra' corretto in send_event
    if ($message_ha['data_ora'] == "now") {
      $timestamp = $this->date_to_ts($message_ha['data_ora']);
    }
    else {
      $timestamp = $message_ha['data_ora'];
    }
    // Fine modifica
    /*
    * vito 4 feb 2009
    */
    //       $id_course = $message_ha['id_course'];
    //       $id_course_instance = $message_ha['id_course_instance'];
    if (!isset($message_ha['id_course']) || empty($message_ha['id_course'])) {
      $id_course = 0;
    }
    else {
      $id_course = $message_ha['id_course'];
    }
    if (!isset($message_ha['id_course_instance']) || empty($message_ha['id_course_instance'])) {
      $id_course_instance = 0;
    }
    else {
      $id_course_instance = $message_ha['id_course_instance'];
    }


    if (empty($message_ha['language'])) {
      $language = ADA_DEFAULT_LANGUAGE;
    }
    else {
      $language = $message_ha['language'];
    }

    //vito 4 feb 2009
    $language = $this->sql_prepared($language);

    $title = $this->or_null($this->sql_prepared($message_ha['titolo']));
    // vito 19 gennaio 2009
    //       $text = sql_prepared($message_ha['testo']);
    $text = $this->sql_prepared($message_ha['testo']);

    $type = "'".$this->type."'";


    $sender_id = $this->user_id;
    if ($this->type == ADA_MSG_CHAT){
    		$recipient_ids = "";
    } else  {
      $recipient_ids = implode(",",$recipients_ids_ar);
    }//end ADA_MSG_CHAT

    $status = "1"; // 0: non initialized; 1: logged in DB; 2: logged in DB and removed from message tables; 3 logged to file

    $flags = $this->or_zero($message_ha['flags']);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    // MARK: preparare query
    // insert a row into table utente_messaggio_log
    $sql =  "insert into utente_messaggio_log (tempo, id_mittente, testo, tipo, status, titolo,  id_istanza_corso, id_corso, lingua, id_riceventi, flags)";
    $sql .= " values ($timestamp, $sender_id, $text, $type, $status, $title, $id_course_instance, $id_course, $language, $recipient_ids, $flags);";
    // logger("performing query: $sql", 4);

    /*
     $res = $db->query($sql);
     if (AMA_DB::isError($res) || $db->affectedRows()==0){
     // logger("query failed", 4);
     return new AMA_Error(AMA_ERR_ADD);
     }// logger("query succeeded", 4);
     */
    $res = parent::executeCritical( $sql );
    if ( AMA_DB::isError( $res ) ) {
      // $res is an AMA_Error object
      return $res;
    }
  }//end log_message

  /**
   * get all the messages sent by user and logged verifying the given clause
   * the list of fields specified is returned
   * records are sorted by the given order
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *                            (records are always filtered for user and type)
   * @param   $ordering       - the order
   *
   * @return  a reference to a hash, if more than one fields are required
   *           res_ha[ID_MESSAGGIO] contains an array with all the values
   *          a reference to a linear array if only one field is required
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_logged_messages($fields_list="", $clause="", $ordering="") {

    /* logger("entered Spool::find_logged_messages - ".
     "[fields_list=".serialize($fields_list).
     ", clause=$clause, ordering=$ordering]", 3);
     */

    $user_id = $this->user_id;
    $type = $this->type;

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;


    //prepare fields list
    if($fields_list != "") {
      $fields = "id_messaggio, ".implode(",", $fields_list);
    }
    else {
      $fields = "id_messaggio";
    }
    // logger("fields_list: $fields", 4);

    // set where clause
    $basic_clause = "id_mittente='$user_id' and tipo='$type' ";
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }

    // set ordering instruction
    if ($ordering != "") {
      $ordering = "order by $ordering";
    }

    $sql = "select $fields from utente_messaggio_log ".
               " where $clause $ordering";
    // logger("performing query: $sql", 4);

    //   if ($fields_list != ""){

    // bidimensional array
    //       $res_ar = $db->getAssoc($sql);

    //   } else {

    // linear array
    $res_ar = $db->getCol($sql);

    //  }

    if (AMA_DB::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    // logger("query succeeded", 4);

    return $res_ar;
  }


  /**
   * get all the messages verifying the given clause
   * the list of fields specified is returned
   * records are sorted by the given order
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *                            (records are always filtered for user and type)
   * @param   $ordering       - the order
   *
   * @return  a reference to a hash, if more than one fields are required
   *           res_ha[ID_MESSAGGIO] contains an array with all the values
   *          a reference to a linear array if only one field is required
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_messages($fields_list="", $clause="", $ordering="") {

    $user_id = $this->user_id;
    $type    = $this->type;

    $db =& parent::getConnection();
    if (AMA_DB::isError($db)) return $db;


    if($fields_list != "") {
      $fields = "messaggi.id_messaggio, ".implode(",", $fields_list);
    }
    else {
      $fields = "messaggi.id_messaggio";
    }

    // Modified by Stamatios Filippis 27-01-05
    $tables = "messaggi";

/*
 *     ChatSpool find_messages method overwrites this method
 */
//    // check the type of the message
//    if ($type == ADA_MSG_CHAT)// we have to access only the "messaggi" table
//    {
//      // getting the time that users joined the chatroom
//      $id_chatroom=$this->id_chatroom;
//      $et= "select tempo_entrata from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom";
//      $entrance_time = $db->getOne($et);
//      $basic_clause = "tipo='$type' and data_ora>=$entrance_time";
//    }
//    elseif ($type == ADA_MSG_PRV_CHAT)
//    {
//  		  $tables .=", destinatari_messaggi ";
//  		  // getting the time that users joined the chatroom
//  		  $id_chatroom=$this->id_chatroom;
//  		  $et= "select tempo_entrata from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom";
//  		  $entrance_time = $db->getOne($et);
//
//  		  $basic_clause = "id_utente='$user_id' and tipo='$type' and data_ora>=$entrance_time " .
//                          " and messaggi.id_messaggio=destinatari_messaggi.id_messaggio";
//    }
//    else // all other cases
//    {

      if(is_array($fields_list) && in_array('utente.username',$fields_list)) {
        $tables .=", destinatari_messaggi AS DM, utente ";

        $basic_clause = "DM.id_utente=$user_id "
                      . "AND messaggi.tipo='$type' AND messaggi.id_messaggio=DM.id_messaggio "
                      . "AND utente.id_utente=messaggi.id_mittente";

      }
      else {
        $tables .=", destinatari_messaggi AS DM";

        $basic_clause = "DM.id_utente=$user_id "
                      . "AND messaggi.tipo='$type' AND messaggi.id_messaggio=DM.id_messaggio";
      }

//    }
    // set where clause
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }
    // set ordering instruction
    if ($ordering != "") {
      $ordering = "order by $ordering";
    }

    $sql = "select $fields from $tables where $clause $ordering";
    // logger("performing query: $sql", 4);
    if ($fields_list != "")
    {
      // bidimensional array
      $res_ar = $db->getAssoc($sql);
    }
    else
    {
      // linear array
      $res_ar = $db->getCol($sql);

    }

    if (AMA_DB::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    // logger("query succeeded", 4);

    return $res_ar;

  }// end parent's find_messages


  public function &find_chat_messages($fields_list="", $clause="", $ordering="")
  {

    /* logger("entered Spool::find_chat_messages - ".
     "[fields_list=".serialize($fields_list).
     ", clause=$clause, ordering=$ordering]", 3);
     */
  	 $type = $this->type;
  	 $id_group = $this->id_chatroom;

  	 $db =& parent::getConnection();
  	 if ( AMA_DB::isError( $db ) ) return $db;


  	 //prepare fields list
  	 if($fields_list != "") {
  	   $fields = "messaggi.id_mittente, messaggi.testo, messaggi.data_ora, ".implode(",", $fields_list);
  	 }
  	 else {
  	   $fields = "messaggi.id_mittente, messaggi.testo, messaggi.data_ora";
  	 }
  	 // logger("fields_list: $fields", 4);

  	 // Modified by Stamatios Filippis 27-01-05
  	 $tables = "messaggi";

  	 $basic_clause = "tipo='$type' and id_group=$id_group";
  	 // set where clause
  	 if ($clause == "") {
  	   $clause = $basic_clause;
  	 }
  	 else {
  	   $clause .= " and $basic_clause";
  	 }
  	 // set ordering instruction
  	 if ($ordering != "") {
  	   $ordering = "order by $ordering";
  	 }
  	 $sql = "select $fields from $tables where $clause $ordering";
  	 //echo $sql;

  	 // logger("performing query: $sql", 4);

  	 $res_ar = $db->getAll($sql);

  	 if (AMA_DB::isError($res_ar)) {
  	   return new AMA_Error(AMA_ERR_GET);
  	 }
  	 // logger("query succeeded", 4);

  	 return $res_ar;
  }


  /**
   * get all the messages sent by user verifying the given clause
   * the list of fields specified is returned
   * records are sorted by the given order
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *                            (records are always filtered for user and type)
   * @param   $ordering       - the order
   *
   * @return  a reference to a hash, if more than one fields are required
   *           res_ha[ID_MESSAGGIO] contains an array with all the values
   *          a reference to a linear array if only one field is required
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_sent_messages($fields_list="", $clause="", $ordering="")
  {

    /* logger("entered Spool::find_sent_messages - ".
     "[fields_list=".serialize($fields_list).
     ", clause=$clause, ordering=$ordering]", 3);
     */

    $user_id = $this->user_id;
    $type = $this->type;

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    //prepare fields list
    if($fields_list != "") {
      // prepend every element of $field_list with an 'M.' string
      array_map(function($val) { return "M.".$val; } , $fields_list);
      $fields = "M.id_messaggio, ".implode(",", $fields_list);
    }
    else {
      $fields = "M.id_messaggio";
    }
    // logger("fields_list: $fields", 4);

    // set where clause
    $basic_clause = "M.id_mittente='$user_id' and M.tipo='$type' ";
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }
    // set ordering instruction
    if ($ordering != "") {
      $ordering = "order by $ordering";
    }

    // giorgio, new query to get recipient id, name and last name
    $sql ="SELECT $fields , DM.id_utente AS id_destinatatrio, ".
    	  "U.nome AS nome_destinatario, U.cognome AS cognome_destinatario ".
    	  "FROM  `messaggi` M,  `destinatari_messaggi` DM,  `utente` U ".
          "WHERE $clause AND M.id_messaggio = DM.id_messaggio ".
          "AND DM.id_utente = U.id_utente $ordering";

//     $sql = "select $fields from messaggi ".
//                " where $clause $ordering";
    // logger("performing query: $sql", 4);

    //   if ($fields_list != ""){

    // bidimensional array
    //       $res_ar = $db->getAssoc($sql);

    //   } else {

    // linear array
    if($fields_list != "") {
      $res_ar = $db->getAll($sql,NULL,AMA_FETCH_ASSOC);
    }
    else {
      $res_ar = $db->getCol($sql);
    }
    //  }

    if (AMA_DB::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }

    // logger("query succeeded", 4);

    return $res_ar;
  }

  /**
   * get a list of all users data in the utente table
   * which verifies a given clause
   *
   * @access  public
   *
   * @param   $id - the id of the message
   *
   * @return  a refrerence to a 2 elements array,
   *           the first element is a hash with data of the message
   *           the second element is an array of recipients' ids
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &get_message_info($id) {

    // logger("entered Spool::get_message_info - [id=$id]", 3);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    // get info about message
    $sql = "select id_messaggio, data_ora, tipo, titolo, id_mittente, priorita, testo,flags from messaggi ".
               " where id_messaggio=$id";

    // logger("performing query: $sql", 4);
    $res_ar = $db->getRow($sql);
    if (AMA_DB::isError($res_ar) || !is_array($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    // logger("query succeeded", 4);

    $msg_ha['id_messaggio'] = $res_ar[0];
    $msg_ha['data_ora']     = $res_ar[1];
    $msg_ha['tipo']         = $res_ar[2];
    $msg_ha['titolo']       = $res_ar[3];
    $msg_ha['id_mittente']  = $res_ar[4];
    $msg_ha['priorita']     = $res_ar[5];
    $msg_ha['testo']        = $res_ar[6];
    $msg_ha['flags']        = $res_ar[7];

    // get recipients ids
    $sql = "select id_utente from destinatari_messaggi ".
               " where id_messaggio=$id";
    // logger("performing query: $sql", 4);
    $res_ar = $db->getAll($sql);
    if (AMA_DB::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    // logger("query succeeded", 4);

    $recipients_ids = array();
    foreach($res_ar as $res_el){
      $recipients_ids[] = $res_el[0];
    }

    // return the two elements as an array reference
    return array($msg_ha, $recipients_ids);
  }

  /**
   * set all messages to the specified value
   * if the value passed is incorrect, nothing is done
   *
   * @access  public
   *
   * @param   $msgs_ar - array of messages id to change
   * @param   $value   - new status ('R' or 'N')
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function set_messages($msgs_ar, $value) {

    // logger("entered Spool::set_messages - ".
    //        "msgs_ar=".serialize($msgs_ar)." value=$value", 3);


    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    // convert values to timestamps and
    // get inverse values for rollback
    if ($value == 'R')  {
      $inverse_value = 'N';
    }
    elseif ($value == 'N') {
      $inverse_value = 'R';
    }

    if (strstr("RN", $value)){

      // begin a transaction
      $this->_begin_transaction();

      foreach ($msgs_ar as $msg_id){

        // update message
        $res = $this->_set_message($msg_id, "read", $value);
        if (AMA_DataHandler::isError($res)){
          $this->_rollback();
          return new AMA_Error(AMA_ERR_UPDATE);
        }

        // add instruction to rollback segment
        $this->_rs_add("_set_message", $msg_id, "read", $inverse_value);

      } // end foreach


      $this->_commit();

    } // end if

  }// end set_messages

  /**
   * set status of a message (read or deleted) to new value
   *
   * @access  private
   *
   * @param   $msg_id     - id of the message
   * @param   $field      - the name of the field to modify
   *                        can be 'read' or 'deleted'
   * @param   $value      - new status
   *                        'R' | 'N' for read
   *                        'Y' | 'N' for deleted
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function _set_message($msg_id, $field, $value) {

    // logger("entered Spool::_set_message - ".
    //        "[msg_id=$msg_id, field=$field, value=$value]", 3);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    switch(strtolower($field)){

      case 'read':
        $field_name = "read_timestamp";
        if ($value == 'R') {
          $value = $this->date_to_ts("now");
        }
        elseif ($value == 'N') {
          $value = 0;
        }
        break;

      case 'deleted':
        $field_name="deleted";
        break;

      default:
        return new AMA_Error(AMA_ERR_WRONG_ARGUMENTS);
    }

    // update message
    $user_id = $this->user_id;
    $sql = "update destinatari_messaggi".
              " set $field_name='$value' where id_messaggio=$msg_id and id_utente=$user_id";
    // logger("performing query: $sql", 4);


    $res =  $db->query($sql);

    if (AMA_DB::isError($res)) {
      return new AMA_Error(AMA_ERR_UPDATE);
    }
    // logger("query succeeded", 4);

    // vito, 19 gennaio 2009, se tutto ok, restituisce TRUE
    return TRUE;
  }


  /**
   * remove a series of messages from the spool
   * this is done by setting the field deleted of table destinatari_messaggi to 'Y'
   * messages are logged into utente_messaggio_log table
   *
   * @access public
   *
   *
   * @param   $msgs_ar - array of messages id to change
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function remove_messages($msgs_ar) {
    // logger("entered Spool::_remove_messages - ".
    //       "[msgs_ar=".serialize($msgs_ar)."]", 3);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;


    // loop for all messages
    foreach ($msgs_ar as $msg_id){
      // remove message from user's spool means
      // the field deleted is set to 'Y'
      $res = $this->_set_message($msg_id, "deleted", "Y");

      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }
      // 5 dec 2008
      $msg_Ha = $this->get_message_info($msg_id);

      //vito 19 gennaio 2009
      //       $res = $this->log_message($msg_Ha);

      $res = $this->log_message($msg_Ha[0], $msg_Ha[1]);

      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_ADD);
      }
    }
  }

  protected function _remove_message($id) {
	//FIXME: richiamare remove_messages?
  }

  /**
   * remove permanently a series of messages from the spool,
   * then check that no other user are currently referring to
   * these messages and in case remove the message from the
   * messaggi table
   *
   * @access private
   *
   * @param   $user_id - id of the owner of the spool
   * @param   $msgs_ar - array of messages id to change
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  protected function _clean_messages($msgs_ar) {

    // logger("entered Spool::_clean_messages - ".
    //       "[msgs_ar=".serialize($msgs_ar)."]", 3);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    foreach ($msgs_ar as $msg_id){

      // remove message from user's spool
      $res = $this->_clean_message($msg_id, $this->user_id);
      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }

      // check if message is referenced by any other user
      $sql = "select count(*) from destinatari_messaggi where id_messaggio=$msg_id";
      // logger("performing query: $sql", 4);
      $n_refs =  $db->getOne($sql);
      if (AMA_DB::isError($n_refs)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }

      // logger("query returned: $n_refs", 4);

      // if it is not, then remove the message from the 'messaggi' table
      if ($n_refs == 0){

        $res =  $this->_clean_message($msg_id);
        if (AMA_DataHandler::isError($res)) {
          return new AMA_Error(AMA_ERR_REMOVE);
        }
      }
    }
  }


  /**
   * remove permanently a message record from 'messaggi' table
   * or from 'destinatari_messaggi'
   * used in the rollback operations and in _clean_messages
   *
   * @access  private
   *
   * @param   $id  - id of the message to remove
   * @param   $rid - the recipient id
   *                 if the parameter is passed and not null, then
   *                 a row is removed from 'destinatari_messaggi' table
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  protected function _clean_message($id, $rid=0) {

    // logger("entered Spool::_remove_message", 3);

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    if ($rid == 0){

      // remove a row from table messaggi
      $sql =  "delete from messaggi where id_messaggio=$id";
      // logger("performing query: $sql", 4);
      /*
      $res = $db->query($sql);
      //           if (AMA_DB::isError($res) || $db->affectedRows()==0)  ??

      if (AMA_DB::isError($res) || $db->numCols()==0)
      return new AMA_Error(AMA_ERR_REMOVE);
      */
      $res = parent::executeCritical( $sql );
      if ( AMA_DB::isError( $res ) ) {
        // $res is an AMA_Error object
        return $res;
      }
      // logger("query succeeded", 4);

    }
    else {

      // remove a row from table destinatari_messaggi
      $sql =  "delete from destinatari_messaggi ".
                   " where id_messaggio=$id and id_utente=$rid";
      // logger("performing query: $sql", 4);
      /*
      $res = $db->query($sql);
      if (AMA_DB::isError($res) || $db->affectedRows()==0)
      return new AMA_Error(AMA_ERR_REMOVE);
      */
      $res = parent::executeCritical( $sql );
      if ( AMA_DB::isError( $res ) ) {
        // $res is an AMA_Error object
        return $res;
      }

      // logger("query succeeded", 4);
    }

  }

}


/**
 * SimpleSpool extends Spool and implements some peculiarities
 * related to the Simple message.
 *
 *
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
class SimpleSpool extends Spool
{

  /**
   * SimpleSpool constructor
   *
   * @access  public
   *
   * @param   $user_id - the user of the spool
   *
   **/
  public function __construct($user_id, $dsn = NULL)
  {
    // logger("entered SimpleSpool constructor", 3);

    $this->ntc = $GLOBALS['SimpleSpool_ntc'];
    $this->rtc = $GLOBALS['SimpleSpool_rtc'];
    $this->type = ADA_MSG_SIMPLE;

    //Spool::Spool($user_id);
    parent::__construct($user_id, $dsn);
  }

  /**
   * first, the cleaning mechanism is invoked, then
   * add a message to the spool by invoking the parent's method
   *
   * @access  public
   *
   * @param   $message_ha        - message data as an hash
   * @param   $recipients_ids_ar - list of recipients ids
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function add_message($message_ha, $recipients_ids_ar) {

    // logger("entered SimpleSpool::add_message", 3);

    $this->clean();

    $res = parent::add_message($message_ha, $recipients_ids_ar, false);
    if (AMA_DataHandler::isError($res)) {
      // $res is an AMA_Error object
      return $res;
    }
  }


  /**
   * first invoke the cleaning mechanism,
   * then get all the messages verifying the given clause
   * by invoking the parent's find_messages method
   * the list of fields specified is returned
   * records are sorted by the given order
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *                            (records are always filtered for user and type)
   * @param   $ordering       - the order
   *
   * @return  a reference to a hash, if more than one fields are required
   *           res_ha[ID_MESSAGGIO] contains an array with all the values
   *          a reference to a linear array if only one field is required
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_messages($fields_list="", $clause="", $ordering=""){

    /* logger("entered SimpleSpool::find_messages - ".
     "[fields_list=".serialize($fields_list).
     ", clause=$clause, ordering=$ordering]", 3);
     */
    // cleaning (don't bother on errors)
    $this->clean();
    // in this spool only messages marked as non deleted are retrieved
    $basic_clause = "deleted='N'";
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }
    // call the parent's find_messages (without clean)
    $res = parent::find_messages($fields_list, $clause, $ordering);

//    if (AMA_DataHandler::isError($res)) {
//      // $res is an AMA_Error object
//      return $res;
//    }
    // $res can ba an AMA_Error object or the messages found
    return $res;
  }

  /**
   * invoke the cleaning mechanism
   * three different messages are removed from the tables:
   *  deleted messages (immediately)
   *  read messages (after rtc seconds)
   *  non read messages (after ntc seconds)
   *
   * @access  public
   *
   * @return  an AMA_Error object if something goes wrong
   *          it is recommended that the error is not treated
   *
   **/
  public function clean() {

    $simpleCleaned= $GLOBALS['simpleCleaned'];

    // logger("entered SimpleSpool::clean", 3);

    // make sure cleaning is only done once per session
    if ($simpleCleaned) {
      return;
    }

    $db =& parent::getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;


    // setting some variables
    $user_id = $this->user_id;
    $type    = $this->type;
    $rtc     = $this->rtc;
    $ntc     = $this->ntc;
    $now     = $this->date_to_ts("now");


    // removing deleted_messages
    /*
    $sql = "select messaggi.id_messaggio from messaggi, destinatari_messaggi ".
    " where id_utente=$user_id and tipo='$type' and ".
    "       messaggi.id_messaggio=destinatari_messaggi.id_messaggio and ".
    "       deleted='Y'";
    */
    // logger("SimpleSpool::clean: removing deleted", 2);
    $res_ar = parent::find_messages("", "deleted='Y'");
    if (AMA_DataHandler::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }

    if (count($res_ar)){
      // FIXME: self::_clean_messages al posto della riga di sotto
      $res = parent::_clean_messages($res_ar);
      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }
    }

    // removing non read messages
    /*
    $sql = "select messaggi.id_messaggio from messaggi, destinatari_messaggi ".
    " where id_utente=$user_id and tipo='$type' and ".
    "       messaggi.id_messaggio=destinatari_messaggi.id_messaggio and ".
    "       read_timestamp = 0 and data_ora < $now-$ntc";
    */
    // logger("SimpleSpool::clean: removing not read", 2);
    $res_ar = parent::find_messages("", "read_timestamp=0 and data_ora<$now-$ntc");
    if (AMA_DataHandler::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }

    if (count($res_ar)){
      // FIXME: self::_clean_messages al posto della riga di sotto
      $res = parent::_clean_messages($res_ar);
      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }
    }


    // removing read messages
    /*
    $sql = "select messaggi.id_messaggio from messaggi, destinatari_messaggi ".
    " where id_utente=$user_id and tipo='$type' and ".
    "       messaggi.id_messaggio=destinatari_messaggi.id_messaggio and ".
    "       read_timestamp > 0 and read_timestamp < $now-$rtc";
    */
    // logger("SimpleSpool::clean: removing read", 2);
    $res_ar = parent::find_messages("", "read_timestamp>0 and read_timestamp<$now-$rtc");
    if (AMA_DataHandler::isError($res_ar)) {
      return new AMA_Error(AMA_ERR_GET);
    }

    if (count($res_ar)){
      // FIXME: self::_clean_messages al posto della riga di sotto
      $res = parent::_clean_messages($res_ar);
      if (AMA_DataHandler::isError($res)) {
        return new AMA_Error(AMA_ERR_REMOVE);
      }
    }

    // done with cleaning for this session
    $simpleCleaned=true;
  }
}



/**
 * AgendaSpool extends Spool and implements some peculiarities
 * related to the Agenda event.
 *
 *
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
class AgendaSpool extends Spool
{

  /**
   * AgendaSpool constructor
   *
   * @access  public
   *
   * @param   $user_id - the user of the spool
   *
   **/
  public function __construct($user_id, $dsn = NULL)
  {
    // logger("entered AgendaSpool constructor", 3);

    $this->ntc = $GLOBALS['AgendaSpool_ntc'];
    $this->rtc = $GLOBALS['AgendaSpool_rtc'];
    $this->type = ADA_MSG_AGENDA;

    //Spool::Spool($user_id);
    parent::__construct($user_id, $dsn);
  }

  /**
   * first invoke the cleaning mechanism,
   * then get all the messages verifying the given clause
   * by invoking the parent's find_messages method
   * the list of fields specified is returned
   * records are sorted by the given order
   *
   * @access  public
   *
   * @param   $fields_list_ar - a list of fields to return
   * @param   $clause         - a clause to filter records
   *                            (records are always filtered for user and type)
   * @param   $ordering       - the order
   *
   * @return  a reference to a hash, if more than one fields are required
   *           res_ha[ID_MESSAGGIO] contains an array with all the values
   *          a reference to a linear array if only one field is required
   *          an AMA_Error object if something goes wrong
   *
   **/
  public function &find_messages($fields_list="", $clause="", $ordering=""){

    /* logger("entered AgendaSpool::find_messages - ".
     "[fields_list=".serialize($fields_list).
     ", clause=$clause, ordering=$ordering]", 3);
     */

    // cleaning (don't bother on errors)
    $this->clean();

    // in this spool only messages marked as non deleted are retrieved
    $basic_clause = "deleted='N'";
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }
    // call the parent's find_messages (without clean)
    $res = parent::find_messages($fields_list, $clause, $ordering);
//    if (AMA_DataHandler::isError($res)) {
//      // $res is an AMA_Error object
//      return $res;
//    }
    // $res can be an AMA_Error object or the messages list
    return $res;
  }


  /**
   * first, the cleaning mechanism is invoked, then
   * add a message to the spool by invoking the parent's method
   *
   * @access  public
   *
   * @param   $message_ha        - message data as an hash
   * @param   $recipients_ids_ar - list of recipients ids
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function add_message($message_ha, $recipients_ids_ar) {

    // logger("entered AgendaSpool::add_message", 3);
    $this->clean();

    return parent::add_message($message_ha, $recipients_ids_ar, false);
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
  public function clean() {

    // logger("entered AgendaSpool::clean", 3);
  }

}

/**
 * ChatSpool extends Spool and implements some peculiarities
 * related to the Chat sentence.
 *
 *
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
class ChatSpool extends Spool
{

  /**
   * SimpleSpool constructor
   *
   * @access  public
   *
   * @param   $user_id - the user of the spool
   *
   **/
  public function __construct($user_id,$type,$id_chatroom="", $dsn = NULL) {
    // logger("entered ChatSpool constructor", 3);
    $this->ntc = $GLOBALS['ChatSpool_ntc'];
    $this->rtc = $GLOBALS['ChatSpool_rtc'];
    $this->type = $type;

    if (empty($id_chatroom)) {
      $this->id_chatroom = $GLOBALS['id_chatroom'];
    }
    else {
      $this->id_chatroom = $id_chatroom;
    }
    parent::__construct($user_id, $dsn);
  }

  /**
   * first, the cleaning mechanism is invoked, then
   * add a message to the spool by invoking the parent's method
   *
   * @access  public
   *
   * @param   $message_ha        - message data as an hash
   * @param   $recipients_ids_ar - list of recipients ids
   *
   * @return  an AMA_Error object if something goes wrong
   *
   **/
  public function add_message($message_ha, $recipients_ids_ar= array()) {
    $this->clean();
    /*
     * Call parent add_message with no checks on message uniqueness
     */
    return parent::add_message($message_ha, $recipients_ids_ar, FALSE);
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
  public function clean() {
    // logger("entered ChatSpool::clean", 3);
  }


  public function &find_messages($fields_list="",$clause="",$ordering="") {
    // cleaning (don't bother on errors)
    $this->clean();

    // vito, 26 settembre 2008
    // check if fields_list is a numeric value: in this case, it's a time interval
    // TODO: se e' un valore numerico, allora deve essere l'id dell'ultimo messaggio
    // ricevuto
    if (is_numeric($fields_list)) {
      return $this->new_find_messages($fields_list);
    }

    $id_chatroom=$this->id_chatroom;

    /*
     $user_id= $this->user_id;
     // getting the time that users joined the chatroom
     $et = "select tempo_entrata from utente_chatroom where id_utente=$user_id and id_chatroom=$id_chatroom";
     $entrance_time = $db->getOne($et);

     print_r($et);
     print_r($entrance_time);
     */

    // in this spool are retrieved all the messages of the chatroom where the user
    // takes part and are inserted after his entrance into the chatroom.

    $basic_clause = "id_group=$id_chatroom";
    if ($clause == "") {
      $clause = $basic_clause;
    }
    else {
      $clause .= " and $basic_clause";
    }
    // call the parent's find_messages (without clean)
    $res = parent::find_messages($fields_list, $clause, $ordering);
//    if (AMA_DataHandler::isError($res)) {
//      // $res is an AMA_Error
//      return $res;
//    }
    // $res can be an AMA_Error object or the messages list
    return $res;
  }

  private function &new_find_messages($last_read_message_id)
  {

    $id_group = $this->id_chatroom;
    $type     = $this->type;
    $user_id  = $this->user_id;

    $db =& parent::getConnection();
    if ( AMA_DB::isError($db)) return $db;

    if ($last_read_message_id == 0) {
      $message_id_sql = '';
    }
    else {

      $message_id_sql = ' AND id_messaggio > ' . $last_read_message_id;
    }

    if ( $type == ADA_MSG_CHAT ) {

      $sql  = "SELECT U.nome, M.id_messaggio, M.data_ora, M.tipo, M.testo
                       FROM  (SELECT id_messaggio, data_ora, tipo, id_mittente, testo FROM messaggi
                               WHERE id_group=$id_group $message_id_sql AND tipo='$type') AS M
                             LEFT JOIN utente AS U ON (U.id_utente = M.id_mittente)";

    }
    elseif ( $type == ADA_MSG_PRV_CHAT ) {
      $user = $this->user_id;

      $sql = "SELECT U.nome, M.id_messaggio, M.data_ora, M.tipo, M.testo
                  FROM (SELECT id_messaggio, data_ora, tipo, id_mittente, testo FROM messaggi
                             WHERE id_group=$id_group $message_id_sql AND tipo='$type') AS M
                           LEFT JOIN utente AS U ON (U.id_utente = M.id_mittente)
                           LEFT JOIN
                           (SELECT id_messaggio FROM destinatari_messaggi WHERE id_utente=$user_id) AS PM
                           ON (M.id_messaggio=PM.id_messaggio)
            ";
    }

    $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
    if(AMA_DataHandler::isError($result)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    return $result;
  }

}//end class ChatSpool

class Mailer
{

  public function send_mail($message_ha, $sender_email, $recipients_emails_ar)
  /**
   * send an email
   *
   *
   * @access  public
   *
   * @param   $message_ha - contains all data of message to send
   *                        this also means the recipients list
   *                        the parameter is an hash whose keys are:
   *                        data_ora,
   *                        tipo,
   *                        titolo,
   *                        mittente*,
   *                        destinatari**,
   *                        priorita,
   *                        testo
   *            $sender_email -
   *            $recipients_emails_ar - an array of all recipients
   *
   * @return    an AMA_Error object if something goes wrong
   *
   **/

  {
    // logger("entered Mailer::send_mail", 3);

    if (DEV_ALLOW_SENDING_EMAILS) {
        $recipient_list = implode(",",$recipients_emails_ar);

        $headers = "From: $sender_email\n"
                 . "BCC: $sender_email\n"
                 . "Reply-To:$sender_email\n"
                 . "X-Mailer: ADA\n"
                 . "MIME-Version: 1.0\n"
                 . "Content-Type: text/plain; charset=UTF-8\n"
                 . "Content-Trasfer-Encoding: 8bit\n\n";

        $subject = $message_ha['titolo'];

        $message = $message_ha['testo']
                 . "\n"
                 . "\n"
                 . '-----'
                 . "\n"
                 . translateFN('This message has been sent to you by ADA. For additional information please visit the following address: ')
                 . "\n"
                 . HTTP_ROOT_DIR;

        //$res =  @mail($recipient_list,$subject,$message,$headers);
        $res =  @mail($recipient_list,'=?UTF-8?B?' . base64_encode($subject) . '?=',$message,$headers);
        if (!$res){
          $errObj = new ADA_error(NULL,"Errore nell'invio dell'email", 'Mailer');
          return $errObj;
        }
    }
  }

  public function clean()
  {
    // logger("entered MailerSpool::clean", 3);
  }
}
?>