<?php
/**
 *
 * @author	Guglielmo Celata <guglielmo@celata.com>
 * @author  giorgio <g.consorti@lynxlab.com>
 * @version
 * @package
 * @license	PHP license version 2.02
 * @copyright (c) 2001-2009 Lynx s.r.l.
 */

/**
 * DB abstraction layer
 */
require_once 'ama_DB.inc.php';

/**
 * @author giorgio 05/giu/2013
 * require the extended ama datahandler to manage extended
 * tables like 'studente', 'tutor', 'autore' (with extended user data)
 */
require_once 'ama_extended.inc.php';

/**
 * rollback handling
 */
require_once('rbstack.inc.php');

/**
 * error class
 */
include_once(ROOT_DIR.'/include/ama_error.inc.php');

/**
 * logger class
 */
include_once(ROOT_DIR.'/include/utilities.inc.php');


/**
 * AMA_DataHandler implements a class to handle complex DB read/write operations
 * for the ADA project.
 *
 * @access public
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 */
abstract class Abstract_AMA_DataHandler {
    /**
     * database connection string
     * @var unknown_type
     */
    protected $dsn;

    /**
     * database connection object
     *
     * @var unknown_type
     */
    protected $db;

    /**
     * rollback stack
     *
     * @var unknown_type
     */
    protected $rbStack;


    //protected static $instance = null;

    /**
     * function AMA_DataHandler
     *
     * @param $db_type
     * @param $db_name
     * @param $db_user
     * @param $db_password
     * @param $db_host
     * @return unknown_type
     */
    public function __construct($dsn = null) {
        if($dsn === null) {
//      $this->dsn = ADA_DB_TYPE.'://'.ADA_DB_USER.':'.ADA_DB_PASS.'@'.
//                   ADA_DB_HOST.'/'.ADA_DB_NAME;
            $this->dsn = ADA_DEFAULT_TESTER_DB_TYPE.'://'.ADA_DEFAULT_TESTER_DB_USER
                    .':'.ADA_DEFAULT_TESTER_DB_PASS.'@'.ADA_DEFAULT_TESTER_DB_HOST
                    .'/'.ADA_DEFAULT_TESTER_DB_NAME;
        }
        else {
            $this->dsn = $dsn;
        }

        $this->db = AMA_DB_NOT_CONNECTED;
        $this->rbStack = new RBStack();
    }

    /**
     * Return the API version
     *
     * @return the API version number as a string
     */
    public function apiVersion() {
        return "0.4.1";
    }

    /**
     * function getConnection
     *
     * Used to handle database connection.
     * Calls AMA_DB::connect() method and returns a reference to
     * the AMA_DB connection object created.
     * If $this->db already stores a connection object, then simply
     * return a reference to it.
     *
     * @return mixed $db - an AMA_DB connection object on success,
     * 					an AMA_Error object on failure.
     */
    protected function &getConnection() {

        if($this->db === AMA_DB_NOT_CONNECTED) {
//            ADALogger::log_db('Creating a new database connection '. $this->dsn);
            $db =& AMA_DB::connect($this->dsn);
            if(AMA_DB::isError($db)) {
            	$retval = new AMA_Error(AMA_ERR_DB_CONNECTION);
                return $retval;
            }
            $this->db =& $db;
        }
        else {
//           ADALogger::log_db('Db già connesso '. $this->dsn . ' ' .$this->db->getDSN());
           if($this->dsn !== $this->db->getDSN()) {
                ADALogger::log_db('dsn diverso chiusura DB ' . $this->dsn . ' ' . $this->db->getDSN());
                // Close existing datababse connection
                if(is_object($this->db) && method_exists($this->db,'disconnect')) {
                    ADALogger::log_db('Closing open connection to database '.  $this->db->getDSN());
                    $this->db->disconnect();
                }
                // Open a new database connection
                $db =& AMA_DB::connect($this->dsn);
                if(AMA_DB::isError($db)) {
                    return new AMA_Error(AMA_ERR_DB_CONNECTION);
                }
                $this->db =& $db;
           }
        }
        return $this->db;
    }

    /**
     * function executeCritical
     *
     * Execute a query and return the number of affected rows (>0) or an AMA_Error
     *
     * @param string $query the INSER, UPDATE or DELETE sql query
     * @return mixed int number of affected rows or AMA_Error object
     */
    protected function executeCritical($query) {
        // use the first 6 chars in $query (corresponding to INSERT, UPDATE, DELETE)
        // to choose wich ama error eventually raise

        $keyword = strtolower(substr($query, 0, 6));
        switch ($keyword) {

            case 'insert':
                $ERROR = AMA_ERR_ADD;
                break;

            case 'update':
                $ERROR = AMA_ERR_UPDATE;
                break;

            case 'delete':
                $ERROR = AMA_ERR_REMOVE;
                break;
        }
        // based on selected DB Abstraction Layer, execute the right code
        // to perform a query and obtain affected rows
        switch (DB_ABS_LAYER) {
            case PDO_DB:
            default:
                $res = $this->DB_execute_critical( $query );
                break;
                /**
                 * Pls handle other databases connection here by adding more cases
                 */
        }
        // $res is the number of affected rows or an error
        // if $res is an error, return an AMA Error with error message as
        // additional debug info
        if( AMA_DB::isError($res)) {
            // get debug info (this works from php 4.3.0)
            $deb_bac = debug_backtrace();
            // create debuginfo
            $error_msg = "while in {$deb_bac[1]['function']} in file {$deb_bac[1]['file']} on line {$deb_bac[1]['line']} " . $res->getMessage();
            // create a new AMA error with error code $ERROR and additional debug info $error_msg
            return new AMA_Error( $ERROR, $error_msg );
        }
        // if $res is not an error, it's the number of rows affected by $query
        if ($res == 0) {
            // get debug info
            $deb_bac = debug_backtrace();
            // create debuginfo referring to the function that called executeCritical
            $error_msg = "while in {$deb_bac[1]['function']} in file {$deb_bac[1]['file']} on line {$deb_bac[1]['line']}: unknown error!";
            // create a new AMA error with error code $ERROR and additional debug info $error_msg
            return new AMA_Error( $ERROR, $error_msg );
        }
        // if $res > 0, query succeeded. we return number of affected rows.
        return $res;
    }

    /**
     * Executes a query and returns the number of affected rows
     *
     * @param string $query
     * @return mixed number of affected rows or an error
     */
    protected function DB_execute_critical($query) {

        //ADALogger::log_db('Call to DB_execute_critical');
        // connect to db if not connected
        $db =& $this->getConnection();
        if(AMA_DB::isError($db)) {
            return $db;
        }
        // execute query, and if there's an error return it
        $res = $db->exec($query);
        if(AMA_DB::isError($res)) {
            return $res;
        }
        // if $res is not an error, return the number of affected rows
        // return $db->affectedRows();
        return $res;
    }

    /**
     *  Functions for SQL string handling
     */

    /**
     * Prepares a string to be submitted to a SQL parser
     *
     * @access private
     *
     * @param $s the string to be prepared
     *
     * @return the prepared string
     */
    public function sql_prepared($s) {
        if (!get_magic_quotes_gpc()) {
            $s =  addslashes($s);
        }
        return "'$s'";
    }

    /**
     * Removes backslashes from prepared string (not so useful, uh?)
     *
     * @access private
     *
     * @param $s the string to be transformed
     *
     * @return the transformed string
     */
    public function sql_deprepared($s) {
        // function used to remove backslashes
        // and other stuff like \', ...
        $s= stripslashes($s);
        return $s;
    }

    /**
     * Assigns a NULL value to a DB field if the value is not properly defined
     *
     * @access private
     *
     * @param $s the value to be checked
     *
     * @return the value or "NULL"
     */
    protected function or_null($s) {
        if (!$s || $s == "''") {
            return "NULL";
        }
        else {
            return $s;
        }
    }

    /**
     * Assigns a ZERO value to a DB field if the value is not properly defined
     *
     * @access private
     *
     * @param $s the value to be checked
     *
     * @return the value or ZERO (0)
     */
    protected function or_zero($s) {
        if (!isset($s) || $s == "''" || $s=="") {
            return "0";
        }
        else {
            return $s;
        }
    }

    /**
     * Converts a timestamp to a date of the format specified as a string
     *
     * @access public
     *
     * @param $timestamp the timestamp
     * @param $format the format used to convert the timestamp (optional, default = ADA_DATE_FORMAT)
     *
     * @return the string representing the timestamp as a date, according to the format
     */
    public static function ts_to_date($timestamp, $format=ADA_DATE_FORMAT) {
        if ($timestamp == "") {
            return "";
        }
        return strftime($format, (float)$timestamp);
    }

    /**
     * Converts a a date of the format specified as a string to an integer timestamp
     *
     * @access public
     *
     * @param $date the date string
     * @param $time the time string (format hh:mm:ss, defaults to null)
     *
     * @return the timestamp as an integer
     */
    public function date_to_ts($date, $time=null) {
        if ($date == "NULL") {
            return $date;
        }

        if ($date == "now") {
            return time();
        }

        // $date_ar = split ('[\\/.-]', $date);
        $date_ar = preg_split ('/[\\/.-]/', $date);
        if (count($date_ar)<3) {
            return 0;
        }

        // $format_ar = split ('[/.-]',ADA_DATE_FORMAT);
        $format_ar = preg_split ('/[\\/.-]/',ADA_DATE_FORMAT);
        if ($format_ar[0]=="%d") {
            $giorno = (int)$date_ar[0];
            $mese = (int)$date_ar[1];
        }
        else {
            $giorno = (int)$date_ar[1];
            $mese = (int)$date_ar[0];
        }

        $anno =(int)$date_ar[2];

        if (!is_null($time)) {
        	list ($ora, $minuti, $secondi) = explode(':', $time);
        } else {
        	$ora = 0;
        	$minuti = 0;
        	$secondi = 0;
        }

        $unix_date = mktime($ora,$minuti,$secondi,$mese,$giorno,$anno);

        return $unix_date;
        //return strtotime($date);
    }

    /**
     * calculates a new timestamp by adding $number_of_days days to the
     * given timestamp or, if it is not given, to the current time stamp.
     *
     * @param
     * @param
     * @return
     */
    public function add_number_of_days($number_of_days, $timestamp=null) {

        if(!is_null($timestamp)) {
            return $timestamp + $number_of_days * AMA_SECONDS_IN_A_DAY;
        }
        else {
            return time() + $number_of_days * AMA_SECONDS_IN_A_DAY;
        }
    }

    /**
     *  Functions for error handling
     */

    /**
     * Tell whether a result code from an AMA method is an error
     *
     * @access public
     *
     * @param $value int result code
     *
     * @return bool whether $value is an error
     */
    public static function isError($value) {
        return (is_object($value) && AMA_DB::isError($value));
                        //         ($value instanceof  AMA_Error)
                        // (get_class($value) == 'AMA_Error' || is_subclass_of($value, 'PEAR_Error')));
    }

    /**
     * Return a textual error message for an AMA error object
     *
     * @access public
     *
     * @param $value error object
     *
     * @return string error message, or translateFN("unknown") if the error code was
     * not recognized
     */
    // now included in AMA_error !
    public function errorMessage($error) {

        if (AMA_DataHandler::isError($error)) {
            return $error->errorMessage();
        }
    }

    /**
     *  Functions for transactions handling
     */

    /**
     * Begin a transaction
     *
     * @access private
     *
     */
    protected function _begin_transaction() {
        // if the rollback stack is not empty, then set up a marker
        if (!$this->rbStack->isEmpty()) {
            $this->rbStack->insert_marker();
        }
    }

    /**
     * Add an instruction to the rollback stack
     *
     * @access private
     *
     */
    protected function _rs_add() {

        // nuber of arguments
        $numargs = func_num_args();

        // generate an error if less than two arguments
        if ($numargs < 2) {
            return new AMA_Error(AMA_ERR_TOO_FEW_ARGS);
        }
        // get all the arguments as an array
        $arg_list = func_get_args();

        // build the element as an hash

        // the name is the first argument
        $element_ha['name'] = $arg_list[0];

        // record the number of arguments (all but the name)
        $element_ha['n_params'] = $numargs-1;

        // all other parameters goes into params_i keys
        for ($i = 1; $i < $numargs; $i++) {
            $element_ha["params_$i"] = $arg_list[$i];
        }

        // put the element onto the stack
        $this->rbStack->push($element_ha);
    }

    /**
     * Do the rollback.
     * Actually performs the rollback, executing all the instructions in the stack
     * (up to the last marker in the markers stack).
     * If something goes wrong, an error is returned.
     *
     * @access private
     *
     * @return a string containing a message
     *
     */
    protected function _rollback() {
        $err_msg = '';

        // get last marker
        $marker = $this->rbStack->remove_marker();

        ADALogger::log_db("entered _rollback (size: ".$this->rbStack->get_size().", marker: $marker)");

        // loop on the stack untill the last marker is reached
        while ($this->rbStack->get_size() > $marker) {

            // get the element from the rollback stack
            $element_ha = $this->rbStack->pop();

            // build the string to call the function (using eval)
            $function_name = $element_ha['name'];
            $last_param = $element_ha['n_params'];

            // the result will be assigned to $res
            $e_str  = "\$res = ";

            // the function name and opening parenthesis
            $e_str .= '$this->'.$function_name . "(";

            // add the parameters
            if ($last_param) {
                // all but last parameters, separated by commas
                for ($i=0; $i<$last_param-1; $i++) {
                    $e_str .= $element_ha["params_$i"] . ",";
                }
                // last parameters
                $e_str .= $element_ha["params_$last_param"];
            }

            // closing parenthesis
            $e_str .= ")";

            // and closing instruction
            $e_str .= ";";

            // evaluate the function

            ADALogger::log_db("_rollback calls: $e_str");
            eval($e_str);

            // add to error message if the instruction in the stack fails, somehow
            if (AMA_DataHandler::isError($res)) {
                $err_msg .= AMA_SEP . "error in function $function_name (" . $res->getMessage() .")";
            }
        }

        // return error message
        if ($err_msg) {
            $err_msg = AMA_ROLLBACK_NOT_SUCCESSFUL . AMA_SEP . $err_msg;
        }
        else {
            $err_msg = AMA_ROLLBACK_SUCCESSFUL;
        }

        return $err_msg;
    }

    /**
     * Do the commit.
     * Delete the rollback stack
     *
     * @access private
     *
     */
    protected function _commit() {
        // get last marker
        $marker = $this->rbStack->remove_marker();

        // loop on the stack untill the last marker is reached
        while ($this->rbStack->get_size() > $marker) {

            // delete the rollback stack statement
            // by assigning it to a dummy variable
            $a = $this->rbStack->pop();
        }
    }

    /**
     * Execution of prepared statements
     */

    /**
     * Prepares and executes a query.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @return object $resultObj - the result object as returned by the AMA_DB layer
     *
     * @access private
     */
    private function prepareAndExecute($sql, $values=array()) {
		$db =& $this->getConnection();
		if (AMA_DB::isError($db)) {
			return $db;
		}

		/**
		 * qui potrebbe esserci il codice che verifica se la query $sql ha gia' uno
		 * statement precompilato nell'array statico $statements degli statement precompilati
		 * che potrebbe essere mantenuto anche qui (anche se forse e' meglio averlo
		 * come attributo della classe).
		 */

		/**
		 * let's check if $sql has alreay been prepared, and let's do it if it's not.
		 */
		if (!$sql instanceof PDOStatement) $stmt = $db->prepare($sql);
		else $stmt = $sql;

		/**
		 * if $values is a scalar, let's transform it into a one-element array
		 */
		if (!is_array($values)) $values = array ($values);

		try {
			$resultObj = $stmt->execute($values);
			if ($resultObj) return $stmt;
			else return new AMA_Error();
		} catch (PDOException $e) {
			return $e;
		}
		/**
		 * sempre nell'ottica del caching a livello di esecuzione dello script degli
		 * statement precompilati, questo $stmt->free() devo toglierlo ed
		 * implementare il __destruct() per AMA e li fare il free di tutti gli statement
		 * presenti nell'array $statements.
		*/
    }

    /**
     * This is the prepared version of the AMA_DB getRow() method.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @param  int    $fetchmode - optional, indicates how to retrieve the results.
     * @return mixed  array when no fetchmode is specified or AMA_FETCH_ASSOC is specified,
     *                object when AMA_FETCH_OBJECT is specified.
     */
    protected function getRowPrepared($sql, $values=array(), $fetchmode=null) {
    	/**
    	 * if $values is a scalar, let's transform it into a one-element array
    	 */
    	if (!is_array($values)) $values = array ($values);

        $resultObj = $this->prepareAndExecute($sql,$values);

        if(AMA_DB::isError($resultObj)) {
            return $resultObj;
        }

        $resultAr = $resultObj->fetch($fetchmode);
        $resultObj->closeCursor();
        return $resultAr;
    }

    /**
     * This is the prepared version of the AMA_DB getAll() method.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @param  int    $fetchmode - optional, indicates how to retrieve the results.
     * @return mixed  array when no fetchmode is specified or AMA_FETCH_ASSOC is specified,
     *                object when AMA_FETCH_OBJECT is specified.
     */
    protected function getAllPrepared($sql, $values=array(), $fetchmode=null, $col = null) {
    	/**
    	 * if $values is a scalar, let's transform it into a one-element array
    	 */
    	if (!is_array($values)) $values = array ($values);

        $resultObj = $this->prepareAndExecute($sql, $values);

        if(AMA_DB::isError($resultObj)) {
            return $resultObj;
        }

        if (is_null($col)) $resultAr = $resultObj->fetchAll ($fetchmode);
        else if (is_numeric($col) && intval($col)>=0) $resultAr = $resultObj->fetchAll($fetchmode,intval($col));

        $resultObj->closeCursor();
        return $resultAr;
    }

    /**
     * This is the prepared version of the AMA_DB getOne() method.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @param  int    $fetchmode - optional, indicates how to retrieve the results.
     * @return mixed  array when no fetchmode is specified or AMA_FETCH_ASSOC is specified,
     *                object when AMA_FETCH_OBJECT is specified.
     */
    protected function getOnePrepared($sql, $values=array()) {
    	return self::getRowPrepared($sql,$values,PDO::FETCH_COLUMN);
    }

    /**
     * This is the prepared version of the AMA_DB getCol() method.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @param  int    $fetchmode - optional, indicates how to retrieve the results.
     * @return mixed  array when no fetchmode is specified or AMA_FETCH_ASSOC is specified,
     *                object when AMA_FETCH_OBJECT is specified.
     */
    protected function getColPrepared($sql, $values=array()) {
    	return self::getAllPrepared($sql,$values,PDO::FETCH_COLUMN,0);
    }

    /**
     * This is the prepared version of the AMA_DB query() method.
     *
     * @param  string $sql       - the sql query with placeholders
     * @param  array  $values    - the values to bind with the prepared statement
     * @param  int    $fetchmode - optional, indicates how to retrieve the results.
     * @return mixed  array when no fetchmode is specified or AMA_FETCH_ASSOC is specified,
     *                object when AMA_FETCH_OBJECT is specified.
     */
    protected function queryPrepared($sql, $values=array(),$fetchmode=null) {
    	/**
    	 * if $values is a scalar, let's transform it into a one-element array
    	 */
    	if (!is_array($values)) $values = array ($values);

        $resultObj = $this->prepareAndExecute($sql, $values);

        if(!AMA_DB::isError($resultObj) && $resultObj === AMA_DB_OK) {
            return true;
        }

        return $resultObj;
    }

    /**
     * This is the prepared version of the ama_pear_mdb2_wrapper exec() method.
     *
     * @param $stmt
     * @param $values
     * @return number of affected rows on success, MDB2 error on failure
     */
    protected function execPrepared($stmt, $values=array()) {
    	$db =& $this->getConnection();
    	if (AMA_DB::isError($db)) {
    		return $db;
    	}

    	/**
    	 * let's check if $sql has alreay been prepared, and let's do it if it's not.
    	 */
    	if (!$sql instanceof PDOStatement) $stmt = $db->prepare($sql);
    	else $stmt = $sql;

    	/**
    	 * if $values is a scalar, let's transform it into a one-element array
    	 */
    	if (!is_array($values)) $values = array ($values);

    	try {
    		$resultObj = $stmt->execute($values);
    		if ($resultObj) return $db->affectedRows($stmt);
    		else return new AMA_Error();
    	} catch (PDOException $e) {
    		return $e;
    	}
    }

    /**
     * This is the prepared version of this class executeCritical() method.
     *
     * @param $sql
     * @param $values
     * @return unknown_type
     */
    protected function executeCriticalPrepared($sql, $values=array()) {

        $keyword = strtolower(substr($sql, 0, 6));
        switch ($keyword) {

            case 'insert':
                $ERROR = AMA_ERR_ADD;
                break;

            case 'update':
                $ERROR = AMA_ERR_UPDATE;
                break;

            case 'delete':
                $ERROR = AMA_ERR_REMOVE;
                break;
        }
        // based on selected DB Abstraction Layer, execute the right code
        // to perform a query and obtain affected rows
        switch (DB_ABS_LAYER) {
            case PDO_DB:
            default:
                $res = $this->DB_execute_critical_prepared($sql,$values);
                break;
                /**
                 * Pls handle other databases connection here by adding more cases
                 */
        }
        // $res is the number of affected rows or an error
        // if $res is an error, return an AMA Error with error message as
        // additional debug info
        if( AMA_DB::isError($res)) {
            // get debug info (this works from php 4.3.0)
            $deb_bac = debug_backtrace();
            // create debuginfo
            $error_msg = "while in {$deb_bac[1]['function']} in file {$deb_bac[1]['file']} on line {$deb_bac[1]['line']} " . $res->getMessage();
            // create a new AMA error with error code $ERROR and additional debug info $error_msg
            return new AMA_Error( $ERROR, $error_msg );
        }
        // if $res is not an error, it's the number of rows affected by $query
        if ($res == 0) {
            // get debug info
            $deb_bac = debug_backtrace();
            // create debuginfo referring to the function that called executeCritical
            $error_msg = "while in {$deb_bac[1]['function']} in file {$deb_bac[1]['file']} on line {$deb_bac[1]['line']}: unknown error!";
            // create a new AMA error with error code $ERROR and additional debug info $error_msg
            return new AMA_Error( $ERROR, $error_msg );
        }
        // if $res > 0, query succeeded. we return number of affected rows.
        return $res;
    }

    /**
     * This is the prepared version of this class' DB_execute_critical() method.
     * Executes a query and returns the number of affected rows
     *
     * @param string $query
     * @return mixed number of affected rows or an error
     */
    protected function DB_execute_critical_prepared($sql, $values=array()) {

        //ADALogger::log_db('Call to DB_execute_critical_prepared');

        // connect to db if not connected
        $db =& $this->getConnection();
        if(AMA_DB::isError($db)) {
            return $db;
        }
        /**
         * qui potrebbe esserci il codice che verifica se la query $sql ha gia' uno
         * statement precompilato nell'array statico $statements degli statement precompilati
         * cmantenuto anche qui come attributo della classe.
         */
        $stmt = $db->prepare($sql);

        // execute query, and if there's an error return it
        $result = $this->queryPrepared($stmt, $values);
        if(AMA_DB::isError($result)) {
            return $result;
        }
        /**
         * sempre nell'ottica del caching a livello di esecuzione dello script degli
         * statement precompilati, questo $stmt->free() devo toglierlo ed
         * implementare il __destruct() per AMA e li fare il free di tutti gli statement
         * presenti nell'array $statements.
         */
        // if $res is not an error, return the number of affected rows
        return $db->affectedRows($stmt);
    }

    /**
     * When no references exist to this object, disconnect from database if connected.
     *
     * @return unknown_type
     */
    public function __destruct() {
        // FIXME: verificare se e' ok chiudere cosi' una connessione al database.

        //ADALogger::log_db('Call to Abstract_AMA_DataHandler destructor');
        if(is_object($this->db) && method_exists($this->db,'disconnect')) {
            //ADALogger::log_db('Closing open connection to database');
            $this->disconnect();
        }
    }

    public function disconnect() {
        //ADALogger::log_db('Call to disconnect');
        if(is_object($this->db) && method_exists($this->db,'disconnect')) {
            //ADALogger::log_db('Closing open connection to database');
            $this->db->disconnect();
            $this->db = AMA_DB_NOT_CONNECTED;
        }
    }
}

/**
 *
 * Common
 *
 */
class AMA_Common_DataHandler extends Abstract_AMA_DataHandler {
    protected static $instance = NULL;
    /**
     *
     * @param  string $dsn - a valid data source name
     * @return an instance of AMA_Common_DataHandler
     */
    public function __construct($dsn = null) {
        $common_db_dsn = ADA_COMMON_DB_TYPE.'://'.ADA_COMMON_DB_USER.':'
                .ADA_COMMON_DB_PASS.'@'.ADA_COMMON_DB_HOST.'/'
                .ADA_COMMON_DB_NAME;
        parent::__construct($common_db_dsn);
    }

    /**
     * Returns an instance of AMA_Common_DataHandler.
     *
     * @param  string $dsn - optional, a valid data source name
     * @return an instance of AMA_Common_DataHandler
     */
    static public function instance($dsn = null) {

        //ADALogger::log_db('AMA_Common_DataHandler: get instance for main db connection');
    	$callerClassName = get_called_class();
    	if (!is_null(self::$instance) && get_class(self::$instance) !== $callerClassName) self::$instance = null;

        if(self::$instance == null) {
            //ADALogger::log_db('AMA_Common_DataHandler: creating a new instance of AMA_Common_DataHandler');
            self::$instance = new $callerClassName();
        }
        return self::$instance;
    }

    /**
     * Methods accessing table `utente`
     */
    // MARK: Methods accessing table `utente`

    /**
     * Checks if exists a user with the given username and password.
     *
     * @param  string $username
     * @param  string $password
     * @return mixed
     */
    public function check_identity($username, $password) {

    	$sql = 'SELECT U.id_utente, U.tipo, U.nome, U.cognome FROM utente U ';
    	$sql_params = array($username, sha1($password));

    	/**
    	 * @author giorgio 05/mag/2014 16:32:07
    	 *
    	 * if not in a multiprovider environment, must
    	 * match the user in the selected provider
    	 */
    	if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {
    		$testerAr = $this->get_tester_info_from_pointer($GLOBALS['user_provider']);
    		$sql .= ',utente_tester UT WHERE '.
    				'U.id_utente = UT.id_utente AND U.username=? AND U.password=? AND UT.id_tester=?';
    		array_push($sql_params, $testerAr[0]);
    	} else {
    		$sql .= 'WHERE U.username=? AND U.password=?';
    	}

        $resultHa = $this->getRowPrepared($sql, $sql_params, AMA_FETCH_ASSOC);

        if(!is_array($resultHa) || empty($resultHa)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        return $resultHa;
    }

    /**
     *
     * @param $user_dataAr
     * @return unknown_type
     */
    public function add_user($user_dataAr=array(), $mustcheck=true) {

        /*
         * Before inserting a row, check if a user with this username already exists
         */
    	if ($mustcheck) {
	        $user_id_sql = 'SELECT id_utente FROM utente WHERE username=?';
	        $user_id = $this->getOnePrepared($user_id_sql,array($user_dataAr['username']));
	        if (AMA_DB::isError($user_id)) {
	            return $user_id;
	        }
	        elseif ($user_id) {
	            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
	        }
    	}

        $add_user_sql = 'INSERT INTO utente(nome,cognome,tipo,e_mail,username,password,layout,
                               indirizzo,citta,provincia,nazione,codice_fiscale,birthdate,sesso,
                               telefono,stato,lingua,timezone,avatar,birthcity,birthprovince)
                 VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $values = array(
                $user_dataAr['nome'],
                $user_dataAr['cognome'],
                $user_dataAr['tipo'],
                $user_dataAr['e_mail'],
                $user_dataAr['username'],
                //sha1($user_dataAr['password']),
                $user_dataAr['password'], // sha1 encoded
                $user_dataAr['layout'],
                $user_dataAr['indirizzo'],
                $user_dataAr['citta'],
                $user_dataAr['provincia'],
                $user_dataAr['nazione'],
                $user_dataAr['codice_fiscale'],
                AMA_Common_DataHandler::date_to_ts($user_dataAr['birthdate']),
                $user_dataAr['sesso'],
                $user_dataAr['telefono'],
//                $this->or_null($user_dataAr['indirizzo']),
//                $this->or_null($user_dataAr['citta']),
//                $this->or_null($user_dataAr['provincia']),
//                $this->or_null($user_dataAr['nazione']),
//                $this->or_null($user_dataAr['codice_fiscale']),
//                $this->or_zero($user_dataAr['birthdate']),
//                $this->or_null($user_dataAr['sesso']),
//                $this->or_null($user_dataAr['telefono']),
                $user_dataAr['stato'],
                $user_dataAr['lingua'],
                $user_dataAr['timezone'],
        		$user_dataAr['avatar'],
        		$user_dataAr['birthcity'],
        		$user_dataAr['birthprovince']

        );

        /*
     * Adds the user
        */
        $result = $this->executeCriticalPrepared($add_user_sql,$values);
        if (AMA_DB::isError($result)) {
            return $result;
        }
        /*
         * Return the user id of the inserted user
         */
        if (!MULTIPROVIDER) {
        	/**
             * If it's not multiprovider there's no other way
             * of getting the ID but a call to lastInsertID
        	 */
        	$user_id = $this->getConnection()->lastInsertID();
        } else {
        	$user_id = $this->find_user_from_username($user_dataAr['username']);
        }

        /*
    $user_id_sql = 'SELECT id_utente FROM utente WHERE username=?';
    $user_id = $this->getOnePrepared($user_id_sql, $user_dataAr['username']);
    if (AMA_DB::isError($user_id)) {
      return new AMA_Error(AMA_ERR_GET);
    }
        */
        return $user_id;

    }

    /**
     * Return the user id of the user with username = $username
     *
     * @param string $username
     * @return AMA_Error|number
     */
    public function find_user_from_username($username) {
        /**
         * @author giorgio 05/mag/2014 15:44:34
         *
         * if not in a multiprovider environment, must
         * match the user in the selected provider
         *
         */
        if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {
        	$testerAr = $this->get_tester_info_from_pointer($GLOBALS['user_provider']);
        	$user_id_sql = 'SELECT U.id_utente FROM utente U, utente_tester UT WHERE '.
        			       'U.id_utente = UT.id_utente AND id_tester=? AND username=?';
        	$sql_params = array ($testerAr[0], $username);
        } else {
        	$user_id_sql = 'SELECT id_utente FROM utente WHERE username=?';
        	$sql_params = $username;
        }
        $user_id = $this->getOnePrepared($user_id_sql, $sql_params);
        if (AMA_DB::isError($user_id) || $user_id == null) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $user_id;
    }

    /**
     * Return the user id of the user with email = $e_mail
     *
     * @param string $email
     * @return AMA_Error|number
     */
    public function find_user_from_email($email) {
    	/**
         * @author giorgio 05/mag/2014 16:29:39
         *
         * if not in a multiprovider environment, must
         * match the user in the selected provider
    	 */
    	if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {
    		$testerAr = $this->get_tester_info_from_pointer($GLOBALS['user_provider']);
    		$user_id_sql = 'SELECT U.id_utente FROM utente U, utente_tester UT WHERE '.
    				'U.id_utente = UT.id_utente AND id_tester=? AND e_mail=?';
    		$sql_params = array ($email, $testerAr[0]);
    	} else {
    		$user_id_sql = 'SELECT id_utente FROM utente WHERE e_mail=?';
    		$sql_params = $email;
    	}
    	$user_id = $this->getOnePrepared($user_id_sql, $sql_params);
        if (AMA_DB::isError($user_id) || $user_id == null) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $user_id;
    }
    /**
     *
     * @param $user_dataAr
     * @return unknown_type
     */
    public function add_user_to_tester($user_id, $tester_id) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'INSERT INTO utente_tester VALUES('.$user_id.','.$tester_id.')';
        $result = $db->query($sql);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }
        return true;
    }

    /* Get password for a user
   *
   * @access private
   *
   * @param $id the user's id
   *
   * @return an array containing all the informations about a user
   *        res_ha['password']
    */


    public function _get_user_pwd($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table UTENTE
        $query = "select password from utente where id_utente=$id";
        $res_ar =  $db->getOne($query, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        return $res_ar;
    }

    /**
     * Get all informations about a user
     *
     * @access private
     *
     * @param $id the user's id
     *
     * @return an array containing all the informations about a user
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['tipo']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     */

    //private function _get_user_info($id) {
    public function _get_user_info($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table UTENTE
        $query = "select nome, cognome, tipo, e_mail AS email, telefono, username, layout, ".
                "indirizzo, citta, provincia, nazione, codice_fiscale, birthdate, sesso, ".
                "telefono, stato, lingua, timezone, cap, matricola, avatar, birthcity, birthprovince from utente where id_utente=$id";
        $res_ar =  $db->getRow($query, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $res_ar['id'] = $id;
        if (isset($res_ar['birthdate'])) $res_ar['birthdate'] = ts2dFN($res_ar['birthdate']);
        return $res_ar;
    }

    /**
     *
     * @param $id
     * @return unknown_type
     */
    public function get_user_info($id) {
        return $this->_get_user_info($id);
    }
    // FIXME: forse deve essere pubblico
    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return unknown_type
     */
    private function _get_student_level($id_user,$id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if (empty($id_course_instance)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // get a row from table iscrizioni
        // FIXME: usare getOne al posto di getRow
        $res_ar =  $db->getRow("select livello from iscrizioni where id_utente_studente=$id_user and  id_istanza_corso=$id_course_instance");
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $res_ar[0];
    }

    /**
     * Get type of a user
     *
     * @access public
     *
     * @param $id the user's id
     *
     * @return an INT (1,2,3,4) or Error
     */
    public function get_user_type($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $result =  $db->getOne("select tipo from utente where id_utente=$id");
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($result)) { //OR is_object($res_ar)){
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /**
     * Get status of a user
     *
     * @access public
     *
     * @param $id the user's id
     *
     * @return an INT (1,2,3,4) or Error
     */
    public function get_user_status($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;
        $query = "select stato from utente where id_utente=$id";
        $result =  $db->getOne($query);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($result)) { //OR is_object($res_ar)){
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /**
     * Get all informations about an author
     *
     * @access public
     *
     * @param $id the author's id
     *
     * @return an array containing all the informations about an author's
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     *        res_ha['tariffa']
     *        res_ha['profilo']
     *        res_ha['layout']
     *
     */
    public function get_author($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(AMA_Common_DataHandler::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table AUTORE
// FIXME: dobbiamo sapere a quale tester e' associato per ottenere il  suo profilo.

//    $get_author_sql = "SELECT tariffa, profilo FROM autore WHERE id_utente_autore=$id";
//    $get_author_result = $db->getRow($get_author_sql, NULL, AMA_FETCH_ASSOC);
//    if (AMA_DB::isError($get_author_result)) {
//      return new AMA_Error(AMA_ERR_GET);
//    }
//    if(!$get_author_result) {
        /* inconsistency found! a message should be logged */
//      return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
//    }

//    return array_merge($get_user_result, $get_author_result);
        return $get_user_result;
    }

    /**
     * Get all informations about student
     *
     * @access public
     *
     * @param $id the student's id
     *
     * @return an array containing all the informations about an administrator
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     *        res_ha['tariffa']
     *        res_ha['profilo']
     *
     */
    public function get_student($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(AMA_Common_DataHandler::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }
        // get_student($id) originally did not return the user id as a result,
        unset($get_user_result['id']);

        return $get_user_result;
    }

    public function get_user($id) {
        return $this->get_student($id);
    }
    /**
     * Get all informations about tutor
     *
     * @access public
     *
     * @param $id the tutor's id
     *
     * @return an array containing all the informations about a tutor
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     *        res_ha['tariffa']
     *        res_ha['profilo']
     *        res_ha['layout']
     *
     *        an AMA_Error object on failure
     *
     */
    public function get_tutor($id) {

        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(AMA_Common_DataHandler::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }
        // get_tutor($id) originally did not return the user id as a result,
        unset($get_user_result['id']);

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        return $get_user_result;
    }

    /**
     * Updates informations related to a user
     *
     * @access public
     *
     * @param $id the user id
     *        $admin_ar the informations. empty fields are not updated
     *
     * @return an error if something goes wrong, true on success
     *
     */
    public function set_user($id, $user_ha) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // verify that the record exists and store old values for rollback
        $user_id_sql =  'SELECT id_utente FROM utente WHERE id_utente=?';
        $user_id = $this->getOnePrepared($user_id_sql, array($id));
        if(AMA_DB::isError($user_id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(is_null($user_id)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // backup old values
        $old_values_ha = $this->get_user($id);

        // verify unique constraint once updated
        /*
 * Nome e cognome non determinano univocamente un utente.
 * E' possibile avere piu' di un utente con lo stesso nome e cognome.
        */

//    $new_nome    = $user_ha['nome'];
//    $new_cognome = $user_ha['cognome'];
//    $old_nome    = $old_values_ha['nome'];
//    $old_cognome = $old_values_ha['cognome'];
//
//
//    if ($new_nome != $old_nome || $new_cognome != $old_cognome){
//
//      //$existing_user_id_sql = 'SELECT id_utente FROM utente WHERE nome=? AND cognome=?';
//      $existing_user_id_sql = 'SELECT id_utente FROM utente WHERE nome=? AND cognome=?';
//      $result = $this->getOnePrepared($existing_user_id_sql, array($new_nome, $new_cognome));
//      if(AMA_DB::isError($result)) {
//        return new AMA_Error(AMA_ERR_GET);
//      }
//      if($result) {
//        return new AMA_Error(AMA_ERR_UNIQUE_KEY);
//      }
//    }

        $where = ' WHERE id_utente=?';
        if(empty($user_ha['password'])) {
            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, telefono=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'telefono=?, stato=?, lingua=?, timezone=?, cap=?, matricola=?, avatar=?, birthcity=?, birthprovince=?';

            $valuesAr = array(
                    $user_ha['nome'],
                    $user_ha['cognome'],
                    $user_ha['e_mail'],  // FIXME: VERIFICARE BENE
                    $user_ha['telefono'],
                    $user_ha['layout'],
                    $user_ha['indirizzo'],
                    $user_ha['citta'],
                    $user_ha['provincia'],
                    $user_ha['nazione'],
                    $user_ha['codice_fiscale'],
                    AMA_Common_DataHandler::date_to_ts($user_ha['birthdate']),
                    $user_ha['sesso'],
                    $user_ha['telefono'],
                    $user_ha['stato'],
                    $user_ha['lingua'],
                    $user_ha['timezone'],
                    $user_ha['cap'],
                    $user_ha['matricola'],
                    $user_ha['avatar'],
            		$user_ha['birthcity'],
            		$user_ha['birthprovince']
            );
        }
        else {
            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, password=?, telefono=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'telefono=?, stato=?, lingua=?, timezone=?, cap=?, matricola=?, avatar=?, birthcity=?, birthprovince=?';

            $valuesAr = array(
                    $user_ha['nome'],
                    $user_ha['cognome'],
                    $user_ha['e_mail'],  // FIXME: VERIFICARE BENE
                    $user_ha['password'], //sha1 encoded
                    $user_ha['telefono'],
                    $user_ha['layout'],
                    $user_ha['indirizzo'],
                    $user_ha['citta'],
                    $user_ha['provincia'],
                    $user_ha['nazione'],
                    $user_ha['codice_fiscale'],
                    AMA_Common_DataHandler::date_to_ts($user_ha['birthdate']),
                    $user_ha['sesso'],
                    $user_ha['telefono'],
                    $user_ha['stato'],
                    $user_ha['lingua'],
                    $user_ha['timezone'],
                    $user_ha['cap'],
                    $user_ha['matricola'],
                    $user_ha['avatar'],
            		$user_ha['birthcity'],
            		$user_ha['birthprovince']
            );
        }
        /**
         * UPDATE USERNAME ONLY IF MODULES_GDPR
         */
        if (defined('MODULES_GDPR') && MODULES_GDPR===true && array_key_exists('username', $user_ha) && strlen($user_ha['username'])>0 && $user_ha['username']!==$old_values_ha['username']) {
        	$update_user_sql .= ',username=?';
        	$valuesAr[] = $user_ha['username'];
        }

        $update_user_sql .= $where;
        $valuesAr[] = $id;

        $result = $this->queryPrepared($update_user_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
        return true;
    }


    /**
     * Updates status  of a student (NOT ANY USER)
     *
     * @access public
     *
     * @param $id the student's id
     *        $status the new status
     *
     * @return  if something goes wrong, new status on success
     *
     */
    public function set_user_status($userid, $status) {
        $student_ha = array();
        $student_ha['stato'] = $status;
        $usertype = $this->get_user_type($userid);
        if ((is_numeric($status)) AND ($user_type == AMA_TYPE_STUDENT)) {
            $result =  $this->set_student($userid, $student_ha);
            if ($self->isError($result)) {
                return $result;
            } else {
                $new_status = $this->get_user_status($userid);
                return $new_status;
            }
        } else {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
    }

    /**
     * Get those users ids verifying the given criterium
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono
     *
     * @param  $usertype the type of users
     *
     * @param  $clause the clause string which will be added to the select
     *
     * @param  $order the ordering filter
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function find_users_list($field_list_ar,  $clause='', $usertype = AMA_TYPE_STUDENT, $order='cognome') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }

        // handle null clause, too
        if ($clause) {
            $clause = ' where '.$clause;
        }

        if ($clause == '') {
            $query = "select id_utente$more_fields from utente where tipo=" . $usertype . " order by $order";
        }
        else {
            $query = "SELECT id_utente$more_fields from utente $clause and tipo=" . $usertype . "  order by $order";
        }

        // do the query
        $users_ar =  $db->getAll($query);
        if (AMA_DB::isError($users_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $users_ar;
    }

    /**
     * Methods accessing table `tester`
     */
    // MARK: Methods accessing table `tester`


    public function get_testers_for_user($id_user) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT T.puntatore FROM utente_tester AS U, tester AS T "
                . "WHERE U.id_utente = $id_user AND T.id_tester = U.id_tester";

        $testers_result = $db->getCol($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    public function get_testers_for_username($username) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT T.puntatore FROM utente AS U, utente_tester AS UT, tester AS T "
                . "WHERE U.username = '$username' AND UT.id_utente= U.id_utente AND T.id_tester = UT.id_tester";

        $testers_result = $db->getCol($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    public function get_all_testers($field_data_Ar=array()) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
        if(!empty($field_data_Ar)) {
            $fields = implode(', ', $field_data_Ar);
            $fields .= ', ';
        }
        else {
            $fields = '';
        }

        $testers_sql = 'SELECT ' .$fields.' puntatore FROM tester WHERE 1';
        $testers_result = $db->getAll($testers_sql, NULL, AMA_FETCH_ASSOC);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    public function get_tester_info_from_id($id_tester, $fetchmode = null) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT id_tester,nome,ragione_sociale,indirizzo,citta,provincia,nazione,telefono,e_mail,responsabile,puntatore,descrizione FROM tester "
                . "WHERE id_tester = ?";

        $testers_result = $db->getRow($testers_sql, $id_tester, $fetchmode);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    public function get_tester_info_from_id_course($id_course) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $tester_sql = "SELECT T.id_tester,T.nome,T.ragione_sociale,T.indirizzo,T.citta,T.provincia,T.nazione,T.telefono,T.e_mail,T.responsabile,T.puntatore,T.descrizione "
                . "FROM tester AS T, servizio_tester AS ST WHERE ST.id_corso=$id_course AND T.id_tester=ST.id_tester";

        $tester_resultAr = $db->getRow($tester_sql,NULL, AMA_FETCH_ASSOC);
        if(self::isError($tester_resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(is_null($tester_resultAr)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $tester_resultAr;
    }

    public function get_tester_info_from_service($id_service) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $tester_sql = "SELECT T.id_tester,T.nome,T.ragione_sociale,T.indirizzo,T.provincia,T.nazione,T.telefono,T.e_mail,T.responsabile,T.puntatore,T.descrizione "
                . "FROM tester AS T, servizio_tester AS ST WHERE ST.id_servizio=$id_service AND T.id_tester=ST.id_tester";

        $tester_resultAr = $db->getRow($tester_sql,NULL, AMA_FETCH_ASSOC);
        if(self::isError($tester_resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(is_null($tester_resultAr)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $tester_resultAr;
    }


    public function get_tester_info_from_pointer($tester) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT id_tester,nome,ragione_sociale,indirizzo,citta,provincia,nazione,telefono,e_mail,responsabile,puntatore,descrizione FROM tester "
                . "WHERE puntatore = '$tester'";

        $testers_result = $db->getRow($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    public function add_tester($tester_dataAr=array()) {

        $tester_sql = 'INSERT INTO tester(nome, ragione_sociale,indirizzo,citta,provincia,nazione,telefono,e_mail,responsabile,puntatore,descrizione) '
                . 'VALUES (?,?,?,?,?,?,?,?,?,?,?)';

        $valuesAr = array(
                $tester_dataAr['tester_name'],
                $tester_dataAr['tester_rs'],
                $tester_dataAr['tester_address'],
        		$tester_dataAr['tester_city'],
                $tester_dataAr['tester_province'],
                $tester_dataAr['tester_country'],
                $tester_dataAr['tester_phone'],
                $tester_dataAr['tester_email'],
        		$tester_dataAr['tester_resp'],
        		$tester_dataAr['tester_pointer'],
                $tester_dataAr['tester_desc']
        );

        $result = $this->queryPrepared($tester_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return $this->getConnection()->lastInsertID();
    }

    public function set_tester($tester_id, $tester_dataAr=array()) {

        $tester_sql = 'UPDATE tester SET nome=?, ragione_sociale=?,indirizzo=?,citta=?,provincia=?,nazione=?,telefono=?,e_mail=?,responsabile=?,puntatore=?,descrizione=? WHERE id_tester=?';

        $valuesAr = array(
                $tester_dataAr['tester_name'],
                $tester_dataAr['tester_rs'],
                $tester_dataAr['tester_address'],
                $tester_dataAr['tester_city'],
                $tester_dataAr['tester_province'],
                $tester_dataAr['tester_country'],
                $tester_dataAr['tester_phone'],
                $tester_dataAr['tester_email'],
                $tester_dataAr['tester_resp'],
                $tester_dataAr['tester_pointer'],
                $tester_dataAr['tester_desc'],
                $tester_id
        );

        $result = $this->queryPrepared($tester_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true; // FIXME: deve restituire l'id del tester appena aggiunto
    }

    /**
     * Methods accessing table `servizio_tester`
     */
    // MARK: Methods accessing table `servizio_tester`
    /**
     * Get the tester where the given service is provided
     *
     * @access public
     *
     * @param $id_service the service's id
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_tester_for_service($id_service) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT id_tester FROM servizio_tester "
                . "WHERE id_servizio = $id_service";

        $testers_result = $db->getCol($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    /**
     * Get informations about service provided by a given tester
     *
     * @access public
     *
     * @param $id_tester the tester's id
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_services_for_tester($id_tester) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT id_servizio FROM servizio_tester "
                . "WHERE id_tester = $id_tester";

        $testers_result = $db->getCol($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    /**
     * Get informations about services and relative tester by a given tester id
     *
     * @access public
     *
     * @param $id_tester the tester's id or array of ids or empty array / false for not apply restriction
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_services_tester_info($id_tester = array()) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        if (!is_array($id_tester)) {
			$id_tester = array($id_tester);
		}

        $sql = "SELECT
					t.`id_tester`, t.`nome` as nome_tester, t.`ragione_sociale`, t.`puntatore`,
					s.*,
					st.`id_corso`
				FROM `tester` t
				JOIN `servizio_tester` st ON (st.`id_tester` = t.`id_tester`)
				JOIN `servizio` s ON (s.`id_servizio` = st.`id_servizio`)";

		if (!empty($id_tester)) {
			$sql.=" WHERE t.`id_tester` IN (".implode(',',$id_tester).")";
		}
		$sql.=" ORDER BY t.`nome` ASC, s.`nome` ASC";

        $res = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(self::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $res;
    }

    /**
     * Get informations about service implementation (=course) provided by a given tester
     *
     * @access public
     *
     * @param $id_tester the tester's id
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_courses_for_tester($id_tester) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $testers_sql = "SELECT id_corso FROM servizio_tester "
                . "WHERE id_tester = $id_tester";

        $testers_result = $db->getCol($testers_sql);
        if(self::isError($testers_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $testers_result;
    }

    /**
     * gets the max id of a course in the whole ADA system
     *
     * Mainly used in course creation (
     *
     * @author giorgio
     *
     * @return AMA_Error | integer
     * @access public
     */
    public function get_course_max_id()
    {
    	$sql = "SELECT MAX(id_corso) FROM servizio_tester";
    	$max_id = $this->getOnePrepared($sql);

    	if (AMA_DB::isError($max_id)) $retval = new AMA_Error(AMA_ERR_GET);
    	else $retval = $max_id;

    	return $retval;
    }

    /**
     * Get informations about service
     *
     * @access public
     *
     * @param $id_service the service's id
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_service_info($id_servizio) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $service_sql = "SELECT id_servizio, nome, descrizione, livello, durata_servizio, min_incontri, max_incontri, durata_max_incontro  FROM servizio "
                . "WHERE id_servizio = $id_servizio";

        $service_result = $db->getRow($service_sql);
        if(self::isError($service_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $service_result;
    }

    /**
     * Get informations about service
     *
     * @access public
     *
     * @param $id_service the service's id
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_info_for_tester_services($id_tester) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $services_sql = "SELECT S.id_servizio, S.nome, S.descrizione, S.livello, S.durata_servizio, S.min_incontri, S.max_incontri, S.durata_max_incontro,"
                . " ST.id_corso FROM servizio AS S, servizio_tester AS ST WHERE ST.id_tester=$id_tester AND S.id_servizio=ST.id_servizio";

        $services_result = $db->getAll($services_sql, NULL, AMA_FETCH_ASSOC);
        if(self::isError($services_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $services_result;
    }

    /*
   * Get level of all services
   *
   * @access public
   *
   * @ return an array: id_service, id_level
   *
   * @return an error if something goes wrong
   *
    */
    public function get_service_levels() {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $service_sql = "SELECT id_servizio, livello  FROM servizio ";
        $service_result = $db->getAll($service_sql);
        if(self::isError($service_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $service_result;
    }


    /*
   * Get all services
   *
   * @access public
   *
   * @ return an array: id_service, service name, id_course , id_provider, provider name, id_departement, departement name, state
   *
   * @return an error if something goes wrong
   *
    */
    public function get_services($orderByAr= NULL,$clause = NULL) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;


        $orderByFields = '';
        if ($orderByAr!= NULL) {
            $orderByFields = 'ORDER BY ';
            foreach ($orderByAr as $field) {

                $orderByFields.= "$field, ";
            }
            $orderByFields = substr($orderByFields,0,count($orderByField)-2);
        }
        if ($clause == NULL) {
            $clause = ' AND s.livello > 1 ';
        } else {
            $clause = " AND $clause ";
        }

        /* //query in provincia table
     $service_sql = "SELECT st.id_servizio, s.nome,  s.livello, st.id_corso, st.id_tester, t.nome, st.id_provincia, p.provincia, p.stato " .
   	"FROM servizio_tester AS st, provincia AS p, servizio AS s, tester AS t ".
    "WHERE  st.id_servizio = s.id_servizio AND st.id_tester = t.id_tester AND st.id_provincia = p.id_pro".
    "ORDER BY s.livello";

        */
        /*
    // query without geographical data
     $service_sql = "SELECT st.id_servizio, s.nome, s.livello, st.id_corso, st.id_tester, t.nome FROM servizio_tester AS st,  servizio AS s, tester AS t ".
    "WHERE  st.id_servizio = s.id_servizio AND st.id_tester = t.id_tester ORDER BY s.livello";
        */
        // query only  in tester table
        $service_sql = "SELECT st.id_servizio, s.nome, s.livello, st.id_corso, st.id_tester, t.nome, t.provincia, t.nazione
     FROM servizio_tester AS st,  servizio AS s, tester AS t
     WHERE  st.id_servizio = s.id_servizio AND st.id_tester = t.id_tester $clause $orderByFields";
        //echo $service_sql;
        $service_result = $db->getAll($service_sql);
        if(self::isError($service_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $service_result;
    }

    /*
   * Get implementors (= courses) for all services
   *
   * @access public
   *
   * @ return an array: id_service, id_course
   *
   * @return an error if something goes wrong
   *
    */
    public function get_service_implementors() {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $service_sql = "SELECT id_servizio, id_corso FROM servizio_tester ";
        $service_result = $db->getAll($service_sql);
        if(self::isError($service_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $service_result;
    }


    /**
     * Get service implementors (courses) for a service and a tester (optional)
     *
     * @access public
     *
     * @param $id_service the service's id
     * @param $id_tester  the tester's id (optional)
     *
     * @return an error if something goes wrong
     *
     */
    public function get_courses_for_service($id_service,$id_tester = NULL) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $courses_sql = "SELECT id_tester, id_corso FROM servizio_tester "
                . "WHERE id_servizio = $id_service";
        if ($id_tester!= NULL) {
            $courses_sql.= " AND id_tester = $id_tester";
        }

        $courses_sql .= ' GROUP BY id_tester';

        $courses_result = $db->getAll($courses_sql, NULL, AMA_FETCH_ASSOC);
        if(self::isError($courses_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $courses_result;
    }



    /**
     * Get informations about a service
     *
     * @access public
     *
     * @param $id_course
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_service_info_from_course($id_course) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
        // FIXME:sistemare query
        $service_sql = "SELECT S.id_servizio, S.nome, S.descrizione, S.livello, S.durata_servizio, S.min_incontri, S.max_incontri, S.durata_max_incontro FROM servizio AS S, "
                . "  servizio_tester as ST "
                . "WHERE ST.id_corso = $id_course "
                . " AND S.id_servizio = ST.id_servizio";
        //. " AND ST.id_servizio = S.id_servizio";

        $service_result = $db->getRow($service_sql);
        if(self::isError($service_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $service_result;
    }

    /**
     * Get informations about the course service type
     *
     * @access public
     *
     * @param $id_course
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_service_type_info_from_course($id_course) {

    	$sql = "SELECT STYPE.* FROM `service_type` as STYPE, " .
    		   "servizio_tester as ST, servizio as S " .
    		   "WHERE ST.id_corso=? " .
    		   "AND S.id_servizio = ST.id_servizio AND S.livello = STYPE.`livello_servizio`";

    	$result = $this->getRowPrepared($sql, $id_course, AMA_FETCH_ASSOC);
    	if(self::isError($result)) {
    		return new AMA_Error(AMA_ERR_GET);
    	}

    	return $result;
    }

    public function add_service($service_dataAr=array()) {

         $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $service_sql = 'INSERT INTO servizio(nome, descrizione, livello, durata_servizio, min_incontri, max_incontri, durata_max_incontro) VALUES(?,?,?,?,?,?,?)';
        $valuesAr = array(
                $service_dataAr['service_name'],
                $service_dataAr['service_description'],
                $service_dataAr['service_level'],
                $service_dataAr['service_duration'],
                $service_dataAr['service_min_meetings'],
                $service_dataAr['service_max_meetings'],
                $service_dataAr['service_meeting_duration']
        );

        $result = $this->queryPrepared($service_sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return $db->lastInsertID();
    }


    public function delete_service($id_service) {
        $service_sql = 'DELETE FROM servizio WHERE id_servizio=?';
        $valuesAr = array(
            $id_service
        );

        $result = $this->queryPrepared($service_sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        return true;
    }

    public function set_service($id_service,$service_dataAr=array()) {

        $service_sql = 'UPDATE servizio SET nome=?, descrizione=?, livello=?, durata_servizio=?, min_incontri=?, max_incontri=?, durata_max_incontro=? WHERE id_servizio=?';
        $valuesAr = array(
                $service_dataAr['service_name'],
                $service_dataAr['service_description'],
                $service_dataAr['service_level'],
                $service_dataAr['service_duration'],
                $service_dataAr['service_min_meetings'],
                $service_dataAr['service_max_meetings'],
                $service_dataAr['service_meeting_duration'],
                $id_service
        );

        $result = $this->queryPrepared($service_sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }

    public function link_service_to_course($id_tester, $id_service, $id_course) {
        $service_sql = 'INSERT INTO servizio_tester(id_tester, id_servizio, id_corso) VALUES(?,?,?)';
        $valuesAr = array(
            $id_tester,
            $id_service,
            $id_course
        );

        $result = $this->queryPrepared($service_sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return true;
    }

    public function unlink_service_from_course($id_service, $id_course) {
        $sql = 'DELETE FROM servizio_tester WHERE id_servizio=? AND id_corso=?';
        $valuesAr = array(
            $id_service,
            $id_course
        );

        $result = $this->queryPrepared($sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        return true;
    }


	/**
	 * giorgio 13/ago/2013
	 * added id_tester parameter that is passed if it's not a multiprovider environment
	 */
    public function get_published_courses($id_tester=null) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $courses_sql = 'SELECT S.id_servizio, S.nome, S.descrizione, S.durata_servizio FROM servizio AS S '.
        		'JOIN `service_type` AS STYPE ON STYPE.`livello_servizio`=S.`livello` AND STYPE.`hiddenFromInfo`!=1 '.
        		'JOIN `servizio_tester` AS ST ON ST.`id_servizio`=S.`id_servizio`';
        if (!is_null($id_tester) && intval($id_tester)>0) $courses_sql .= ' WHERE id_tester='.intval($id_tester);

        $result = $db->getAll($courses_sql, null, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }




    /**
     * Get users' list of a given type
     *
     * @access public
     *
     * @param $user_type
     *
     *
     * @return an error if something goes wrong
     *
     */
    public function get_users_by_type($user_type=array(), $retrieve_extended_data=false) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $type = implode(',', $user_type);
        if ($retrieve_extended_data) {
            $sql = "SELECT nome, cognome, tipo, username FROM utente WHERE tipo IN ($type)";
        } else {
            $sql = "SELECT tipo, username FROM utente WHERE tipo IN ($type)";
        }

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function get_number_of_users_with_status($user_idsAr = array(), $status) {
        if(count($user_idsAr) == 0) {
            return 0;
        }
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $user_ids = implode(',', $user_idsAr);
        $sql = 'SELECT count(id_utente) FROM utente WHERE id_utente IN('.$user_ids.')
    		AND stato='.$status;
        $result = $db->getOne($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }
    /**
     * Methods accessing table `token`
     */
    // MARK: Methods accessing table `token`

    /**
     *
     * @param  $token_dataAr an associative array with the following key set:
     * 	'token'
     * 	'user_id'
     * 	'request_timestamp'
     * 	'expiration_timestamp'
     * 	'action'
     * 	'valid'
     * @return unknown_type
     */
    public function add_token($token_dataAr = array()) {
        $token_sql = 'INSERT INTO token(token, id_utente, timestamp_richiesta, azione, valido) VALUES(?,?,?,?,?)';
        $valuesAr = array(
                $token_dataAr['token'],
                $token_dataAr['id_utente'],
                $token_dataAr['timestamp_richiesta'],
                $token_dataAr['azione'],
                $token_dataAr['valido']
        );

        $result = $this->queryPrepared($token_sql, $valuesAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return true;
    }

    /**
     *
     * @param $token
     * @param $user_id
     * @param $action
     * @return unknown_type
     */
    public function get_token($token, $user_id, $action) {
        $db  =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = "SELECT token, id_utente, timestamp_richiesta, azione, valido FROM token WHERE token='$token' AND id_utente=$user_id AND azione=$action";

        $result = $db->getRow($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result) || !is_array($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function update_token($token_dataAr = array()) {
        $db  =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $valido = $token_dataAr['valido'];
        $token  = $token_dataAr['token'];

        $sql = "UPDATE token SET valido=$valido WHERE token='$token'";

        $result = $db->query($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }

    /**
     * Methods accessing table `messaggi`
     *
     * @see MessageDataHandler.inc.php
     */
    // MARK: Methods accessing table `messaggi`

    /**
     * Methods accessing table `messaggi_sistema`
     */
    // MARK: Methods accessing table `messaggi_sistema`

    /**
     * function find_message_translation
     *
     * @param string $message_text  - ADA system message string to be translated
     * @param string $language_code - ISO 639-1 language code (e.g. 'it' for 'italian')
     * @return mixed - An AMA_Error object if something went wrong or a string.
     */
    public function find_message_translation($message_text, $language_code) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
        $table_name = $this->get_translation_table_name_for_language_code($language_code);

        if (AMA_DB::isError($table_name)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $sql_message = $this->sql_prepared($message_text);
        /*
     * Check if the given message is already in table messaggi_sistema
        */
        $sql_message_id = "SELECT id_messaggio FROM messaggi_sistema WHERE testo_messaggio=$sql_message";

        $result = $db->getRow($sql_message_id);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        /*
     * If the given message is not in table messaggi_sistema, add it.
        */
        if ($result ==  NULL) {
            $insert = $this->add_translation_message($sql_message);
            if (AMA_DB::isError($insert)) {
                return new AMA_Error(AMA_ERR_ADD);
            }
            /*
       * For this message there aren't translations at this moment, so return the original message
            */
            return $message_text;
        }
        /*
     * If the message was in table messaggi_sistema, search for a message translation in the given
     * user language
        */

        $message_id = $result[0];
        $result = $this->select_message_text($table_name, $message_id);

        /*
     * If a translation in the given language is not found, return the original message
        */
        if (AMA_DB::isError($result) OR $result ==  null) {
            return $message_text;
        }

        /*
     * If a messagge translation is found with an empty string, return the original message
        */
        // vito, 2 marzo 2009
        //      $translated_message = $result[0];
        $translated_message = $result['testo_messaggio'];
        if(empty($translated_message)) {
            return $message_text;
        }

        return $translated_message;
    }

    /**
     * function select_message_text by ID
     * @param $table_name
     * @param $message_id
     * @return unknown_type
     */
    public function select_message_text ($table_name, $message_id) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql_translated_message = "SELECT testo_messaggio FROM $table_name WHERE id_messaggio=$message_id";
        $result = $db->getRow($sql_translated_message, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }


    /**
     * function find_translation_for_message
     *
     * @param string  $message_text
     * @param string  $language_code
     * @param integer $limit_results_number_to
     * @return mixed - An AMA_Error object if there were errors, an array of string otherwise
     */
    public function find_translation_for_message($message_text, $language_code, $limit_results_number_to) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $last_char = count($message_text);
        /*
     * Check if the user has specified an exact query (e.g. '"some text"')
     *
        */
        if($message_text[0] == '"' && $message_text[$last_char-1] == '"' ) {
            $sql_prepared_text = $this->sql_prepared(trim($message_text,'"'));
            $sql_for_where     = "testo_messaggio=$sql_prepared_text";
        }
        else if ($message_text[1] == '"' && $message_text[$last_char] == '"') {
            $sql_prepared_text = $this->sql_prepared(trim($message_text,'\"'));
            $sql_for_where     = "testo_messaggio=$sql_prepared_text";
        }
        /*
     * The user entered some search tokens (e.g. 'some text')
        */
        else {
            $sql_for_where = "";
            $token = strtok($message_text, ' ');
            $sql_prepared_text = $this->sql_prepared("%$token%");
            $sql_for_where .= "testo_messaggio LIKE $sql_prepared_text ";
            while (($token = strtok(' ')) !== FALSE) {
                $sql_prepared_text = $this->sql_prepared("%$token%");
                $sql_for_where .= "AND testo_messaggio LIKE $sql_prepared_text ";
            }
            if($limit_results_number_to!=null || $limit_results_number_to!="")
            {
                $sql_for_where .= " LIMIT $limit_results_number_to";
            }
        }

        $table_name = $this->get_translation_table_name_for_language_code($language_code);
        if (AMA_DB::isError($table_name)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        $sql_translated_message = "SELECT id_messaggio,testo_messaggio
                                   FROM $table_name
                                  WHERE $sql_for_where";

        $result = $db->getAll($sql_translated_message, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }


    /**
     * function get_all_system_messages: used to obtain all the messages stored
     * in table 'messaggi_sistema'.
     *
     * @return mixed - An AMA_Error object if there were errors, an array of strings otherwise.
     */
    public function get_all_system_messages() {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql_get_messages = "SELECT testo_messaggio FROM messaggi_sistema";
        $result = $db->getAll($sql_get_messages, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * function add_translation_message: used to add a message into table messaggi_sistema
     * and into the translation related tables (messaggi_it, messaggi_en, ...) to ensure that,
     * given a message, its id points to the corresponding translated message in all of the
     * translation related tables.
     *
     * @access private
     * @param  string $sql_prepared_message - a message already prepared by calling $this->sql_prepare
     * @return true if the message was successfully inserted, an AMA_DB error otherwise
     */
    private function add_translation_message($sql_prepared_message) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        /**
         * Insert this message in table messaggi_sistema
         */
        $sql_insert_message    = "INSERT INTO messaggi_sistema(testo_messaggio) VALUES($sql_prepared_message)";

        $result = $db->query($sql_insert_message);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        /**
         * Get tablename suffixes for each language supported in the user interface message
         * translation and use each suffix to construct the table name to use for message insertion
         */
        $sql_select_translation_tables_suffixes = "SELECT identificatore_tabella FROM lingue";
        $suffixes = $db->getCol($sql_select_translation_tables_suffixes);
        if (AMA_DB::isError($suffixes)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        // ottenere l'id per il messaggio appena inserito

        $sql_id_message    = "SELECT id_messaggio FROM messaggi_sistema WHERE testo_messaggio=$sql_prepared_message";
        $id_message = $db->getOne($sql_id_message);
        if (AMA_DB::isError($id_message)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        foreach($suffixes as $table_suffix) {
            $table_name = 'messaggi_'.$table_suffix;
            /**
             * Insert the messagge in the translation table named $table_name
             */
            $sql_insert_message_in_translation_table = "INSERT INTO $table_name(id_messaggio,testo_messaggio) VALUES($id_message,$sql_prepared_message)";
            $result = $db->query($sql_insert_message_in_translation_table);
            /**
             * If an error occurs while adding the message into this table, then add an empty string, since
             * we don't want to loose identifier one-to-one mapping between this table and table messaggi_sistema
             */
            if (AMA_DB::isError($result)) {
                ADALogger::log_db("Error encountered while adding message $sql_prepared_message into table $table_name");
                $sql_insert_in_case_of_error="INSERT INTO $table_name(testo_messaggio) VALUES('')";
                $result = $db->query($sql_insert_in_case_of_error);
            }
        }

        return true;
    }

    /*
   * vito, 20 ottobre 2008: necessaria ad aggiornare il testo di un messaggio di
   * sistema per un dato language code
    */
    /**
     * function update_message_translation_for_language_code
     *
     * @param integer $message_id    - the identifier of the message in table 'messaggi_sistema'
     * @param string  $message_text  - the text for the translated message
     * @param string  $language_code - the language code that identifies the translation
     * @return mixed  - An AMA_Error object if there were errors, true otherwise
     */
    public function update_message_translation_for_language_code($message_id, $message_text, $language_code) {

        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $table_name = $this->get_translation_table_name_for_language_code($language_code);
        if (AMA_DB::isError($table_name)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        $sql_prepared_message_text = $this->sql_prepared($message_text);
        $sql_update_message_text = "UPDATE $table_name SET testo_messaggio=$sql_prepared_message_text WHERE id_messaggio=$message_id";
        $result = $db->query($sql_update_message_text);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }

    /**
     * function update_message_translation_for_language_code_given_this_text
     *
     * @param string $message_text     - the existing string in the translation
     * @param string $new_message_text - the new string
     * @param string $language_code    - ISO 639-1 code which identifies the translation
     * @return mixed - AMA_Error object if there were errors, the number of affected rows otherwise
     */
    public function update_message_translation_for_language_code_given_this_text( $message_text, $new_message_text, $language_code) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $table_name = $this->get_translation_table_name_for_language_code($language_code);
        if (AMA_DB::isError($table_name)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        $sql_prepared_message_text = $this->sql_prepared($message_text);
        $sql_prepared_new_message_text = $this->sql_prepared($new_message_text);
        /*
     * Check if the given message is already in table messaggi_sistema
        */
        $sql_message_id = "SELECT id_messaggio FROM messaggi_sistema WHERE testo_messaggio=$sql_prepared_message_text";

        $result = $db->getRow($sql_message_id);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        $message_id = $result[0];
        // FIXME: verificare il valore restituito se il messaggio dato non esiste nella tabella.

        $sql_update_message_text = "UPDATE $table_name SET testo_messaggio=$sql_prepared_new_message_text WHERE id_messaggio=$message_id";

        $result = $this->executeCritical($sql_update_message_text);

        if(AMA_DB::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * function add_translated_message: used to add a message translate into translation
     * tables (messaggi_it, messaggi_en, ...)
     *
     * @access public
     * @param  string $sql_prepared_message - a message already prepared by calling $this->sql_prepare
     * @return true if the message was successfully inserted, an AMA_DB error otherwise
     */
    public function add_translated_message($sql_prepared_message, $id, $suffix) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $table_name = 'messaggi_'.$suffix;
        /**
         * Insert the messagge in the translation table named $table_name
         */
        $sql_insert_message_in_translation_table = "INSERT INTO $table_name(id_messaggio, testo_messaggio) VALUES($id, $sql_prepared_message)";
        $result = $db->query($sql_insert_message_in_translation_table);
        /**
         * If an error occurs while adding the message into this table, then add an empty string, since
         * we don't want to loose identifier one-to-one mapping between this table and table messaggi_sistema
         */
        if (AMA_DB::isError($result)) {
            ADALogger::log_db("Error encountered while adding message $sql_prepared_message into table $table_name");
            return $result;

        }

        return true;
    }

    /**
     * function delete_all_messages: used to delete all messages from translation
     * tables (messaggi_it, messaggi_en, ...) and from system messagges (messaggi_sistema)
     *
     * @access public
     * @param  string $suffix - (it, is, es)
     * @return true if the table was emptied, an AMA_DB error otherwise
     */
    public function delete_all_messages($suffix) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $table_name = 'messaggi_'.$suffix;
        /**
         * delete messagges from the translation table named $table_name
         */
        $sql_delete_messages_from_translation_table = "delete from $table_name";
        $result = $db->query($sql_delete_messages_from_translation_table);
        /**
         * If an error occurs while deleting all the messages from this table
         */
        if (AMA_DB::isError($result)) {
            ADALogger::log_db("Error encountered while deleting messages from table $table_name");
            return $result;
        }
        $sql = "ALTER TABLE $table_name AUTO_INCREMENT = 0";
        $result = $db->query($sql);
        if (AMA_DB::isError($result)) {
            ADALogger::log_db("Error encountered while deleting messages from table $table_name");
            return $result;
        }


        return true;
    }
    /**
     * Methods accessing table `lingue`
     */
    // MARK: Methods accessing table `lingue`
    /**
     * function find_languages: used to get the language names for all of the language
     * supported in the user interface message translation
     *
     * @return mixed - An AMA_Error object if there were errors, an array of string otherwise
     */
    public function find_languages() {

        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql_select_languages = "SELECT id_lingua,nome_lingua,codice_lingua FROM lingue";
        $result = $db->getAll($sql_select_languages, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if(empty($result)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /**
     * return the identificatore_tabella of language given a language id
     * added on 17/lug/2013 for course exporting, feel free to use it!
     *
     * @author giorgio
     *
     */
    public function find_language_table_identifier_by_langauge_id ($language_id)
    {
    	$db =& $this->getConnection();
    	if (AMA_DB::isError($db)) return $db;

    	$sql_select_languages = "SELECT identificatore_tabella FROM lingue WHERE id_lingua=".$language_id;
    	$result = $db->getOne($sql_select_languages, null, AMA_FETCH_ASSOC);

    	if (AMA_DB::isError($result)) {
    		return new AMA_Error(AMA_ERR_GET);
    	}

    	if(empty($result)) {
    		return new AMA_Error(AMA_ERR_NOT_FOUND);
    	}

    	return $result;
    }

    /**
     * return the id_lingua of language given a identificatore_tabella
     * added on 17/lug/2013 for course exporting, feel free to use it!
     *
     * @author giorgio
     *
     */
    public function find_language_id_by_langauge_table_identifier ($table_identifier)
    {
    	$db =& $this->getConnection();
    	if (AMA_DB::isError($db)) return $db;

    	$sql_select_languages = "SELECT id_lingua FROM lingue WHERE identificatore_tabella='".$table_identifier."'";
    	$result = $db->getOne($sql_select_languages, null, AMA_FETCH_ASSOC);

    	if (AMA_DB::isError($result)) {
    		return new AMA_Error(AMA_ERR_GET);
    	}

    	if(empty($result)) {
    		return new AMA_Error(AMA_ERR_NOT_FOUND);
    	}

    	return $result;
    }

    /**
     * function get_translation_table_name_for_language_code(): used to obtain the name of
     * the table in which to store a translated message given the language code for the translation.
     *
     * @param  string $language_code - the ISO 639-1 language code (e.g. 'it' for 'italian')
     * @return string $table_name    - a string containing the table name for the given language code
     */
    private function get_translation_table_name_for_language_code($language_code) {

        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
// = AMA_DB_MDB2_wrapper
        $translation_tables_default_prefix = 'messaggi_';

        $sql_translation_table_suffix_for_language_code = "SELECT identificatore_tabella FROM lingue WHERE codice_lingua='$language_code'";

        $result = $db->getRow($sql_translation_table_suffix_for_language_code);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        /*
     * build table name
        */

        $table_suffix = $result[0];
        $table_name = $translation_tables_default_prefix . $table_suffix;

        return $table_name;
    }

    /**
     * (non-PHPdoc)
     * @see include/Abstract_AMA_DataHandler#__destruct()
     */
    public function __destruct() {
        parent::__destruct();
    }
}
/**
 *
 * Tester
 *
 */
abstract class AMA_Tester_DataHandler extends Abstract_AMA_DataHandler {
    protected static $instance = NULL;
    /**
     * Contains the data source name used to create this instance of AMA_DataHandler
     * @var string
     */
    protected static $tester_dsn = NULL;

    /**
     *
     * @param  string $dsn - a valid data source name
     * @return an instance of AMA_DataHandler
     */
    public function __construct($dsn = null) {
        //ADALogger::log_db('AMA_DataHandler constructor');
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
     * Returns an instance of AMA_DataHandler.
     *
     * @param  string $dsn - optional, a valid data source name
     * @return an instance of AMA_DataHandler
     */
    static function instance($dsn = null) {
    	$callerClassName = get_called_class();
    	if (!is_null(self::$instance) && get_class(self::$instance) !== $callerClassName) self::$instance = null;

        if(self::$instance === NULL) {
            self::$instance = new $callerClassName($dsn);
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

    /**
     * Methods accessing table `amministratore_corsi`
     */
    // MARK: Methods accessing table `amministratore_corsi`
    // FIXME: currently we have no methods accessing this table.

    /**
     * Methods accessing table `amministratore_sistema`
     *
     * There aren't methods accessing only this table. Queries on this table
     * are performed by methods add_admin, remove_admin, get_admins_list.
     */
    // MARK: Methods accessing table `amministratore_sistema`

    /**
     * Methods accessing table `autore`
     */
    // MARK: Methods accessing table `autore`
    /**
     * Add an author to the DB
     *
     * @access public
     *
     * @param $author_ha an associative array containing all the author's data
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *         the author id on success
     */
    function add_author($author_ha) {
        /*
     * $author_ha is an associative array with the following keys set:
     * id_utente, nome, cognome, email, username, password, telefono, layout, tariffa, profilo
        */
        /*
     * Add user data in table utenti
        */
        $result = $this->add_user($author_ha);
        if (AMA_DB::isError($result)) {
            // $result is an AMA_Error object
            return $result;//new AMA_Error(AMA_ERR_ADD);
        }

        $add_author_sql = 'INSERT INTO autore(id_utente_autore, tariffa, profilo) VALUES(?,?,?)';

        $add_author_values = array(
                $author_ha['id_utente'],
                $this->or_zero($author_ha['tariffa']),
                $this->or_null($author_ha['profilo'])
        );

        $result = $this->executeCriticalPrepared($add_author_sql, $add_author_values);
        if (AMA_DB::isError($result)) {
            // try manual rollback in case problems arise
            $delete_user_sql = 'DELETE FROM utente WHERE username=?';
            $delete_result   = $this->executeCriticalPrepared($delete_user_sql, array($author_ha['username']));
            if (AMA_DB::isError($delete_result)) {
                return $delete_result;
            }
            /*
       * user data has been successfully removed from table utente, return only
       * the error obtained when adding user data to table autore.
            */
            return $result;
        }

        /*
     * the author data has been successfully added to tables utente and autore,
     * return the user id assigned to this user.
        */
        return $author_ha['id_utente'];
    }

    /**
     * Remove an author from the DB
     *
     * @access public
     *
     * @param $id the unique id of the author
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *         true on success
     */
    public function remove_author($id) {

        $valuesAr = array($id);

        // referential integrity checks
        $id_course_sql = 'SELECT id_corso FROM modello_corso WHERE id_utente_autore=?';
        $result = $this->getOnePrepared($id_course_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
        }
        else if($result) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        $id_node_sql = 'SELECT id_nodo FROM nodo WHERE id_utente=?';
        $result = $this->getOnePrepared($id_node_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
        }
        else if($result) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        $id_link_sql = 'SELECT id_link FROM link WHERE id_utente=?';
        $result = $this->getOnePrepared($id_link_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
        }
        else if($result) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        /*
     * Referential integrity checks are OK, delete the author from tables
     * autore and utente.
        */

        $delete_author_sql = 'DELETE FROM autore WHERE id_utente_autore=?';
        $result = $this->executeCriticalPrepared($delete_author_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }

        $delete_user_sql = 'DELETE FROM utente WHERE id_utente=?';
        $result = $this->executeCriticalPrepared($delete_user_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        /*
     * Author's data was successfully deleted from tables autore and utente.
        */
        return true;
    }

    /**
     * Get a list of authors' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password,
     *        telefono, profilo, tariffa
     *
     * @return a nested array containing the list, or an AMA_Error object or a
     * DB_Error object if something goes wrong
     *
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_authors_list
     */
    public function &get_authors_list($field_list_ar) {
        return $this->find_authors_list($field_list_ar);
    }

    /**
     * Get a list of authors' ids from the DB
     *
     * @access public
     *
     * @return an array containing the list, or an AMA_Error object or a DB_Error
     * object if something goes wrong
     *
     * @see find_authors_list, get_authors_list
     */
    public function &get_authors_ids() {
        return $this->get_authors_list();
    }

    /**
     * Get those authors' ids verifying the given criterium on the tarif fiels
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @param  clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &find_authors_list($field_list_ar, $clause='') {
        // FIXME: the queries performef by this method aren't prepared.
        //tries to connect to db
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) {
            return $db;
        }

        // build comma separated string out of $field_list_ar array
        $more_fields='';
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }
        // add an 'and' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'and '.$clause;
        }
        // do the query
        $authors_ar =  $db->getAll("select id_utente$more_fields from utente, autore where id_utente=id_utente_autore $clause");

        if (AMA_DB::isError($authors_ar)) {
            //return $authors_ar;
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $authors_ar;
    }

    /**
     * Get all informations about an author
     *
     * @access public
     *
     * @param $id the author's id
     *
     * @return an array containing all the informations about an author
     *
     */
    public function get_author($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(self::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table AUTORE
        $get_author_sql = "SELECT tariffa, profilo FROM autore WHERE id_utente_autore=$id";
        $get_author_result = $db->getRow($get_author_sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($get_author_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(!$get_author_result) {
            /* inconsistency found! a message should be logged */
            return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
        }
        return array_merge($get_user_result, $get_author_result);
    }

    /**
     * Updates informations related to an author
     *
     * @access public
     *
     * @param $id the author's id
     *        $author_ar the informations. empty fields are not updated
     *
     * @return an error if something goes wrong, true on success
     *
     */
    public function set_author($id, $author_ha) {

        // backup old values
        $old_values_ha = $this->get_author($id);

        $result = $this->set_user($id,$author_ha);
        if(self::isError($result)) {
            // $result is an AMA_Error object
            return $result;
        }

        $update_author_sql = 'UPDATE autore SET tariffa=?, profilo=? WHERE id_utente_autore=?';
        $valuesAr = array(
                isset($author_ha['tariffa']) ? $author_ha['tariffa'] : null,
                isset($author_ha['profilo']) ? $author_ha['profilo'] : null,
                $id
        );
        $result = $this->queryPrepared($update_author_sql, $valuesAr);

        if(AMA_DB::isError($result)) {
            $valuesAr = array(
                    $old_values_ha['nome'],
                    $old_values_ha['cognome'],
                    $old_values_ha['email'],
                    $old_values_ha['telefono'],
                    $old_values_ha['password'],
                    $old_values_ha['layout'],
                    $old_values_ha['indirizzo'],
                    $old_values_ha['citta'],
                    $old_values_ha['provincia'],
                    $old_values_ha['nazione'],
                    $old_values_ha['codice_fiscale'],
                    AMA_Common_DataHandler::date_to_ts($old_values_ha['birthdate']),
                    $old_values_ha['sesso'],
                    $old_values_ha['stato'],
                    $old_values_ha['lingua'],
                    $old_values_ha['timezone'],
                    $old_values_ha['cap'],
                    $old_values_ha['matricola'],
                    $old_values_ha['avatar'],
            		$old_values_ha['birthcity'],
            		$old_values_ha['birthprovince'],
                    $id
            );

            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, telefono=?, password=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'stato=?, lingua=?, timezone=?, cap=?, matricola=?, avatar=?, birthcity=?, birthprovince=? WHERE id_utente=?';

            $result = $this->executeCriticalPrepared($update_user_sql, $valuesAr);
            // qui andrebbe differenziato il tipo di errore
            if(AMA_DB::isError($result)) {
                return new AMA_Error(AMA_ERR_UPDATE);
            }

            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }



    /**
     * Updates informations related to a user
     *
     * @access public
     *
     * @param $id the user id
     *        $admin_ar the informations. empty fields are not updated
     *
     * @return an error if something goes wrong, true on success
     *
     */
    public function set_user($id, $user_ha) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;


        // verify that the record exists and store old values for rollback
        $user_id_sql =  'SELECT id_utente FROM utente WHERE id_utente=?';
        $user_id = $this->getOnePrepared($user_id_sql, array($id));
        if(AMA_DB::isError($user_id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(is_null($user_id) || $user_id===false) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $where = ' WHERE id_utente=?';
        if(empty($user_ha['password'])) {
            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, telefono=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'telefono=?, stato=?, lingua=?, timezone=?, cap=?, matricola=?, avatar=?, birthcity=?, birthprovince=?';

            $valuesAr = array(
                    $user_ha['nome'],
                    $user_ha['cognome'],
                    $user_ha['e_mail'],  // FIXME: VERIFICARE BENE
                    $user_ha['telefono'],
                    $user_ha['layout'],
                    $user_ha['indirizzo'],
                    $user_ha['citta'],
                    $user_ha['provincia'],
                    $user_ha['nazione'],
                    $user_ha['codice_fiscale'],
                    $this->date_to_ts($user_ha['birthdate']),
                    $user_ha['sesso'],
                    $user_ha['telefono'],
                    $user_ha['stato'],
                    $user_ha['lingua'],
                    $user_ha['timezone'],
                    $user_ha['cap'],
                    $user_ha['matricola'],
                    $user_ha['avatar'],
            		$user_ha['birthcity'],
            		$user_ha['birthprovince']
            );
        }
        else {
            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, password=?, telefono=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'telefono=?,stato=?, lingua=?, timezone=?, cap=?, matricola=?, avatar=?, birthcity=?, birthprovince=?';

            $valuesAr = array(
                    $user_ha['nome'],
                    $user_ha['cognome'],
                    $user_ha['e_mail'],  // FIXME: VERIFICARE BENE
                    $user_ha['password'], //sha1 encoded
                    $user_ha['telefono'],
                    $user_ha['layout'],
                    $user_ha['indirizzo'],
                    $user_ha['citta'],
                    $user_ha['provincia'],
                    $user_ha['nazione'],
                    $user_ha['codice_fiscale'],
                    $this->date_to_ts($user_ha['birthdate']),
                    $user_ha['sesso'],
                    $user_ha['telefono'],
                    $user_ha['stato'],
                    $user_ha['lingua'],
                    $user_ha['timezone'],
                    $user_ha['cap'],
                    $user_ha['matricola'],
                    $user_ha['avatar'],
            		$user_ha['birthcity'],
            		$user_ha['birthprovince']
            );
        }
        /**
         * UPDATE USERNAME ONLY IF MODULES_GDPR
         */
        if (defined('MODULES_GDPR') && MODULES_GDPR===true && array_key_exists('username', $user_ha) && strlen($user_ha['username'])>0) {
        	$update_user_sql .= ',username=?';
        	$valuesAr[] = $user_ha['username'];
        }

        $update_user_sql .= $where;
        $valuesAr[] = $id;

        $result = $this->queryPrepared($update_user_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
        return true;
    }

    /**
     * Methods accessing table `bookmark`
     */
    // MARK: Methods accessing table `bookmark`

    /**
     * Add an item  to table bookmark
     * The date of the adding is set automatically.
     * It is assumed that the IDs have already been checked by the caller
     * The ordering field is automatically filled by the add_bookmark() method
     *
     * access:
     *  public
     *
     * parameters:
     * @param $student_id   the id of the student
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $node_id      the node to be registered in the history
     * @param $description  a textual description of the bookmark
     * @param $ordering     integer if specified, then insert the bookmark with a given ordering
     *
     * @return the id of the added bookmark on success, an AMA_Error object on failure
     */
    public function add_bookmark($node_id, $student_id, $instance_id, $date, $description, $ordering="") {

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;


        // prepare the $node_id string
        $node_id = $this->sql_prepared($node_id);
        $student_id = $this->sql_prepared($student_id);
        $instance_id = $this->sql_prepared($instance_id);

        // get the present date-time as timestamp
        $date = $this->date_to_ts("now");

        // prepare the description
        $description = $this->sql_prepared($description);

        // if ordering is not specified or not an integer, then calculate it
        if (empty($ordering) || !is_int($ordering)) {
            // get last ordering value from the bookmarks
            // of a student in a class
            $sql = "select ordering from bookmark".
                    " where id_utente_studente=$student_id and id_istanza_corso=$instance_id".
                    " order by ordering desc;";

            $ordering =  $db->getOne($sql);
            if (AMA_DB::isError($ordering)) {
                return new AMA_Error(AMA_ERR_GET);
            }

            // if no record is found, then set ordering to zero
            // (so that incrementing it will bring it to one)
            // FIXME: siamo sicuri che getOne resituisca zero se non trova il record?
            // dovrebbe restituire null
            if ($ordering == 0) {
                $ordering = 0;
            }

            // increment ordering
            $ordering ++;
        }

        // find duplicates

        /*  $out_fields_ar = array('id_nodo','descrizione');
     $clause = "descrizione = $description";
     $already_exists= $this->_find_bookmarks_list($out_fields_ar, $clause);
        */
        $sql = "select id_nodo from bookmark".
                " where descrizione = $description and id_utente_studente=$student_id and id_istanza_corso=$instance_id";

        $already_exists =  $db->getOne($sql);
        if (AMA_DB::isError($already_exists)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if ($already_exists) {
            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
        }

        // add a row into table bookmark
        $sql =  "insert into bookmark (id_utente_studente, id_istanza_corso, id_nodo, data, descrizione, ordering)";
        $sql .= " values ($student_id, $instance_id, $node_id, $date, $description, $ordering)";

        $res = $db->query($sql);

        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        $sql = "select id_bookmark from bookmark".
                " where id_nodo = $node_id and descrizione = $description and id_utente_studente=$student_id and id_istanza_corso=$instance_id";

        $new_bookmark =   $db->getRow($sql);
        if(AMA_DB::isError($new_bookmark)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return  $new_bookmark[0];
    }

    /**
     * Get all informations related to a given bookmark.
     *
     * @access public
     *
     * @param $bookmark_id
     *
     * @return an hash with the fields
     *               the keys are:
     * node_id       - the id of the bookmarked node
     * student_id    - the id of the student who bookmarked the node
     * course_id     - the id of the instance of the course  the student is following
     * date          - the date of the bookmark's insertion (as ADA_DATE_FORMAT)
     * description   - the description of the ordering
     * ordering      - the ordering value
     *
     * @return an array on success, an AMA_Error object on failure.
     */
    public function get_bookmark_info($bookmark_id) {

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table bookmark
        $sql  = "select id_nodo, id_utente_studente, id_istanza_corso, data, descrizione, ordering ";
        $sql .= " from bookmark where id_bookmark=$bookmark_id";
        $res_ar =  $db->getRow($sql);

//    if (AMA_DB::isError($res_ar)) {
//      return new AMA_Error(AMA_ERR_GET);
//    }
        if (AMA_DB::isError($res_ar) || !$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        $res_ha['node_id']      = $res_ar[0];
        $res_ha['student_id']   = $res_ar[1];
        $res_ha['course_id']    = $res_ar[2];
        $res_ha['date']         = self::ts_to_date($res_ar[3]);
        $res_ha['description']  = $res_ar[4];
        $res_ha['ordering']     = $res_ar[5];

        return $res_ha;
    }

    /**
     * Get bookmarks which satisfy a given clause
     * Only the fields specified in the $out_fields_ar parameter are inserted
     * in the result set.
     * This function is meant to be used by the public find_bookmarks_list()
     *
     * @access private
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     * @return on success, a bi-dimensional array containing these fields:
     *
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     *		on failure, an AMA_Error object
     */
    private function &_find_bookmarks_list($out_fields_ar, $clause='') {

        $more_fields = '';
        // build comma separated string out of $field_list_ar array
        if (!empty($out_fields_ar) and is_array($out_fields_ar) and count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        }

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // add a 'where' on top of the clause
        // handle null clause, too

        $sql = "select id_bookmark";
        if ($more_fields) {
            $sql.= $more_fields;
        }
        $sql.= " from bookmark ";
        if ($clause) {
            $sql .= 'where '.$clause;
        }
        // do the query
        $res_ar =  $db->getAll($sql);

        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        // return nested array
        return $res_ar;
    }

    /**
     * Get bookmarks
     * Returns all the bookmarks without filtering. Only the fields specified
     * in the $out_fields_ar parameter are inserted in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @return a bi-dimensional array containing the fields as specified
     *
     * @see _find_bookmarks_list
     *
     */
    public function &get_bookmarks_list($out_fields_ar) {
        return $this->_find_bookmarks_list($out_fields_ar);
    }

    /**
     * Get bookmarks for a given student, course instance or node.
     * Returns all the history informations filtering on students, courses or both.
     * If a parameter has the value '', then it is not filtered.
     * Only the fields specified in the $out_fields_ar parameter are inserted
     * in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     * @return a bi-dimensional array containing the fields as specified.
     *
     * @see _find_bookmarks_list
     *
     */
    public function &find_bookmarks_list($out_fields_ar, $student_id=0, $course_instance_id=0, $node_id='') {
        // build the clause
        $clause = '';

        if ($student_id) {
            $clause .= "id_utente_studente =".$this->sql_prepared($student_id);
        }
        if ($course_instance_id) {
            if ($clause) {
                $clause .= ' and ';
            }
            $clause .= "id_istanza_corso =".$this->sql_prepared($course_instance_id);
        }

        if ($node_id) {
            if ($clause) {
                $clause .= ' and ';
            }

            $clause .= "id_nodo =".$this->sql_prepared($node_id);
        }
        // invokes the private method to get all the records
        return $this->_find_bookmarks_list($out_fields_ar, $clause);
    }

    /**
     * Updates informations related to a bookmark
     * only the description and ordering can be updated
     * the date is also changed, but automatically
     *
     * access:
     *  private
     *
     *
     * @param $id              - the bookmark's id
     * @param $new_description - the new description string
     * @param $new_ordering    - the new ordering number
     *
     *
     * @return true on success, an AMA_Error object on failure
     *
     * @see
     *  set_bookmark_description()
     *  swap_bookmarks()
     */
    private function _set_bookmark($id, $new_description, $new_ordering) {
        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // get the present date-time as timestamp
        $date = $this->date_to_ts("now");

        // build the description change
        // leave it blank if it is not required
        if ($new_description) {
            $description_update = "descrizione=".$this->sql_prepared($new_description).", ";
        }
        else {
            $description_update = "";
        }
        // build the ordering change
        // leave it blank if it is not required
        if ($new_ordering) {
            $ordering_update = "ordering=".$new_ordering.", ";
        }
        else {
            $ordering_update = "";
        }
        // verify that the record exists and store old values for rollback
        $res_id =  $db->getRow("select id_bookmark from bookmark where id_bookmark=$id");
//    if (AMA_DB::isError($res_id)) {
//    return $res_id;
        if (AMA_Error($res_id) || $res_id == 0) {
            $db->free();
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // update the rows in the tables
        $sql  = "update bookmark set ".
                $description_update.
                $ordering_update.
                " data=$date ".
                " where id_bookmark=$id";

        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }


    /**
     * Updates a bookmark's description
     *
     * access:
     *  public
     *
     * parameters:
     *  $id              - the bookmark's id
     *  $new_description - the new description string
     *
     * return:
     *  an error if something goes wrong
     *
     * see also:
     *  _set_bookmark()
     */
    public function set_bookmark_description($id, $descr) {
        // invoke private _set_bookmark method to do the job
        if (AMA_DB::isError($this->_set_bookmark($id, $descr, ""))) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
    }


    /**
     * Swap positions between bookmark entries
     * (do it inside a transaction!)
     *
     * @param $id1  - the first bookmark entry
     * @param $id2  - the second bookmark entry
     *
     * @return true on success, an AMA_Error object on failure
     *
     * @see  _set_bookmark()
     */
    public function swap_bookmarks($id1, $id2) {
        // do not check DB connection,
        // since it uses methods which do

        // get ordering for first record
        $res_ha = $this->get_bookmark_info($id1);
        if (AMA_DataHandler::isError($res_ha_)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        $ordering1 = $res_ha['ordering'];

        // get ordering for second record
        $res_ha = $this->get_bookmark_info($id2);
        if (AMA_DataHandler::isError($res_ha_)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        $ordering2 = $res_ha['ordering'];

        // begin the transaction
        $this->_begin_transaction();

        if (AMA_DB::isError(_set_bookmark($id1, "", $ordering2))) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        $this->_add_rs("_set_bookmark", $id1, "", $ordering1);

        if (AMA_DB::isError(_set_bookmark($id2, "", $ordering1))) {

            $this->_rollback();
            return new AMA_Error(AMA_ERR_UPDATE);

        }
        $this->commit();

        return true;
    }

    /**
     * Remove a bookmark
     * all subsequent orderings are decreased by one
     * this is performed inside a transaction
     *
     * @access public
     *
     * @param id the id of the action to be removed
     * @return true on success, an AMA_Error object on failure
     */
    public function remove_bookmark($id) {
        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // get data of record to remove (for rollback)
        $res_ha = $this->get_bookmark_info($id);
        if (AMA_DataHandler::isError($res_ha)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        $ordering = $res_ha['ordering'];

        // get a list of ids having ordering greater than
        // the record to be removed
        $res_ar = $this->_find_bookmarks_list("ordering", "ordering>$ordering");

        // begin complex removal operations

        // start a transaction
        $this->_begin_transaction();

        // removal query
        $id_prep = $this->sql_prepared($id);
        $sql = "delete from bookmark where id_bookmark=$id_prep";
        $res = $db->query($sql);

        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        // insert restoring action into rollback segment
        // for the remove operation


        $this->_rs_add("add_bookmark",
                $res_ha['node_id'],
                $res_ha['student_id'],
                $res_ha['course_id'],
                $res_ha['description'],
                $res_ha['ordering']);

        // update ordering loop
        $n = count($res_ar);
        for ($i=0; $i<$n; $i++) {

            // decrease ordering value

            $res = @$this->_set_bookmark($res_ar[$i][0], "", $res_ar[$i][1]-1);
            if (AMA_DB::isError($res)) {

                // rollback in case of error
                $this->_rollback();
                return new AMA_Error(AMA_ERR_REMOVE);

            }


            // insert restoring action into rollback segment
            // for the ordering update operation
            @$this->_rs_add("_set_bookmark",
                    $res_ar[$i][0], "", $res_ar[$i][1]);

        }

        // final success
        $this->_commit();

        return true;
    }
    /**
     * Methods accessing table `chatroom`
     * @see ChatRoom.inc.php
     */
    // MARK: Methods accessing table `chatroom`

    /**
     * Methods accessing table `clienti`
     *
     * Currently we have no methods for table clienti
     */
    // MARK: Methods accessing table `clienti`

    /**
     * Methods accessing table `destinatari_messaggi`
     * @see MessageDataHandler.inc.php
     */
    // MARK: Methods accessing table `destinatari_messaggi`


    /**
     * Methods accessing table `history_nodi`
     */
    // MARK: Methods accessing table `history_nodi`


    /**
     * Add an item  to table history_nodi
     * Useful during the navigation. The date of the visit is computed automatically.
     *
     * @access public
     *
     * @param $student_id   the id of the student
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $node_id      the node to be registered in the history
     *
     * @return true on success, an AMA_Error object on failure.
     */
    public function add_node_history($student_id, $course_id, $node_id, $remote_address, $installation_path, $access_from, $isAjax = false) {
        // get session id
        $session_id = session_id();
        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // visiting date ...
        $visit_date = $this->date_to_ts("now");

        // exit date ... :)
        $exit_date = $visit_date;

        // update field exit_date in table history_nodi
        $sql  = "select id_history,id_nodo from history_nodi where session_id='$session_id' ORDER BY id_history DESC";
        $res_ar =  $db->getRow($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (isset($res_ar['id_history'])) $last_id_history = $res_ar['id_history'];
        if (isset($res_ar['id_nodo'])) $last_id_nodo = $res_ar['id_nodo'];
        if (!$isAjax && isset($last_id_nodo) && $last_id_nodo == $node_id) return true;

        if  (isset($last_id_history)) {
            $sql = "update history_nodi set data_uscita='$visit_date'  where
        id_history='$last_id_history';";
            $res = $db->query($sql);
            if (AMA_DB::isError($res)) {
                return new AMA_Error(AMA_ERR_UPDATE);
            }
        }

        // if visiting a node...
        if (isset($node_id) && !$isAjax) {
            // prepare the node_id string
            $node_id = $this->sql_prepared($node_id);

            // add a row into table history_nodi
            $sql =  "insert into history_nodi (id_utente_studente, id_istanza_corso, id_nodo, data_visita, data_uscita, session_id, remote_address, installation_path, access_from)";
            $sql .= " values ($student_id, $course_id, $node_id, $visit_date, $exit_date, '$session_id', '$remote_address', '$installation_path', $access_from);";
            $res = $db->query($sql);
            if (AMA_DB::isError($res)) {
                return new AMA_Error(AMA_ERR_ADD);
            }
            // update field N_CONTATTI in table nodo

            $sql  = "select n_contatti from nodo where id_nodo=$node_id";
            $res_ar =  $db->getRow($sql);
            if(AMA_DB::isError($res_ar)) {
                return new AMA_Error(AMA_ERR_GET);
            }

            $visitCount = $res_ar[0];
            $visitCount++;
            $sql = "update nodo set n_contatti=$visitCount  where id_nodo=$node_id;";
            $res = $db->query($sql);
            if (AMA_DB::isError($res)) {
                return new AMA_Error(AMA_ERR_UPDATE);
            }
        }

        return true;
    }


    /**
     * Get all informations related to a given nodes history row.
     *
     * @access public
     *
     * @param $nodes_history_id
     *
     * @return on success, an hash with the fields
     *         the keys are:
     * node_id            - the id of the bookmarked node
     * student_id         - the id of the student
     * course_id          - the id of the instance of the course  the student is following
     * visit_date         - the moment of the visit
     * exit_date          - the moment the user left the node (?)
     * session_id         - session_id at the moment of the visit
     *
     *		on failure, an AMA_Error object
     */
    public function get_nodes_history_info($nodes_history_id) {

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table history_nodi
        $sql  = "select id_nodo, id_utente_studente, id_istanza_corso, data_visita, data_uscita, session_id ";
        $sql .= " from history_nodi where id_history=$nodes_history_id";
        $res_ar =  $db->getRow($sql);
//    if (AMA_DB::isError($res_ar))
//    return $res_ar;

        if (AMA_DB::isError($res_ar) || !$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $res_ha['node_id']      = $res_ar[0];
        $res_ha['student_id']   = $res_ar[1];
        $res_ha['course_id']    = $res_ar[2];
        $res_ha['visit_date']   = self::ts_to_date($res_ar[3]);
        $res_ha['exit_date']    = self::ts_to_date($res_ar[4]);
        $res_ha['session_id']   = $res_ar[5];
        $res_ha['time_spent']   = $res_ar[4]-$res_ar[3];


        return $res_ha;
    }


    /**
     * Get nodes history informations which satisfy a given clause
     * Only the fields specifiedin the $out_fields_ar parameter are inserted
     * in the result set.
     * This function is meant to be used by the public get_nodes_history_list()
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     * @param $return_as_associative return an associative array
     *
     * @return on success, a bi-dimensional array containing these fields
     *
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     *           on failure, an AMA_Error object
     *
     */
    public function &_find_nodes_history_list($out_fields_ar, $clause='', $return_as_associative = false) {
        //tries to connect to db
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        }
        // add a 'where' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }
        // do the query
        $sql = "select id_history$more_fields from history_nodi $clause order by id_history";
        if ($return_as_associative) {
			$res_ar = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
		}
        else {
			$res_ar =  $db->getAll($sql);
		}

        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $res_ar;
    }

    /**
     * Get nodes history informations.
     * Returns all the history informations without filtering. Only the fields specified
     * in the $out_fields_ar parameter are inserted in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @return a bi-dimensional array containing the fields as specified
     *
     * @see
     *
     */
    public function &get_nodes_history_list($out_fields_ar) {
        return $this->_find_nodes_history_list($out_fields_ar);
    }

    /**
     * Get nodes history informations for a given student, course instance or both
     * Returns all the history informations filtering on students, courses or both.
     * If a parameter has the value '', then it is not filtered.
     * Only the fields specified
     * in the $out_fields_ar parameter are inserted in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $student_id
     * @param $course_instance_id
     * @param $node_id
     *
     * @return a bi-dimensional array containing the fields as specified.
     *
     * @see
     *
     */
    public function &find_nodes_history_list($out_fields_ar, $student_id=0, $course_instance_id=0, $node_id='') {
        // build the clause
        $clause = '';

        if ($student_id) {
            $clause .= "id_utente_studente = $student_id";
        }
        if ($course_instance_id) {
            if ($clause) {
                $clause .= ' and ';
            }
            $clause .= "id_istanza_corso = $course_instance_id";
        }

        if ($node_id) {
            $node_id = $this->sql_prepared($node_id);
            if ($clause) {
                $clause .= ' and ';
            }
            $clause .= "id_nodo = $node_id";
        }

        /* modified 6/7/01 steve: redundant with code in _find_nodes_history_list
     if ($clause)
     $clause = ' where '.$clause;
        */

        // invokes the private method to get all the records
        return $this->_find_nodes_history_list($out_fields_ar, $clause);
    }

    /**
     * Return student subscribed course instance
     *
     * @access public
     *
     * @param $id_user pass a single/array student id or use "false" to retrieve all student
     *
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array('tutor id'=>array('course_instance', 'course_instance', 'course_instance'));
     */

    public function get_students_subscribed_course_instance($id_user = false, $presubscription = false, $both = false) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;
        if ($both) {
			$status_Ar = array(ADA_STATUS_PRESUBSCRIBED,ADA_STATUS_SUBSCRIBED,ADA_STATUS_REMOVED,ADA_STATUS_VISITOR, ADA_STATUS_TERMINATED);
		}
		else if ($presubscription) {
			$status_Ar = array(ADA_STATUS_PRESUBSCRIBED);
		}
		else {
			$status_Ar = array(ADA_STATUS_SUBSCRIBED,ADA_STATUS_REMOVED,ADA_STATUS_VISITOR, ADA_STATUS_TERMINATED);
		}

        $sql = "SELECT
					i.`id_utente_studente`,
					c.`id_corso`, c.`titolo`, c.`id_utente_autore`,
					ic.`id_istanza_corso`, ic.`title`
				FROM `iscrizioni` i
				JOIN `istanza_corso` ic ON (ic.`id_istanza_corso`=i.`id_istanza_corso`)
				JOIN `modello_corso` c ON (c.`id_corso`=ic.`id_corso`)
				WHERE i.`status` IN (".(implode(',', $status_Ar)).")";

		if (is_array($id_user) AND !empty($id_user)) {
			$sql.= " AND i.`id_utente_studente` IN (".implode(',',$id_user).")";
		}
		else if ($id_user) {
			$sql.= " AND i.`id_utente_studente` = ".$id_user;
		}

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        else {
			$array = array();
			foreach($result as $k=>$v) {
				$id = $v['id_utente_studente'];
				unset($v['id_utente_studente']);
				$array[$id][] = $v;
			}
			unset($result);
			return $array;
		}
    }

    /***
     * get_students_for_course_instances
     *
     * @param array $id_course_instances
     * @return mixed - an AMA_DB Error if something goes wrong or an associative array on success
     *
     */

    public function get_students_for_course_instance($id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

	$status_Ar = array(ADA_STATUS_SUBSCRIBED,ADA_STATUS_REMOVED,ADA_STATUS_VISITOR,ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED);

        $sql = 'SELECT U.*, I.status,I.data_iscrizione,I.laststatusupdate';

         if(defined('MODULES_CODEMAN') && (MODULES_CODEMAN))
        {
            $sql=$sql.', I.codice';
        }

        $sql=$sql.' FROM utente AS U, iscrizioni AS I '
             . ' WHERE I.id_istanza_corso ='.$id_course_instance
             . ' AND I.status IN ('.implode(',',$status_Ar).')'
             . ' AND U.id_utente = I.id_utente_studente';

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /***
     * get_unique_students_for_course_instances
     * used to fetch an associative array contains users having subscribe same course instance
     *
     * @param array $id_course_instances
     * @return mixed - an AMA_DB Error if something goes wrong or an associative array on success
     *
     * graffio 31/01/2011
     *
     */

    public function get_unique_students_for_course_instances($id_course_instances=array()) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $id_course_instances_list = implode(",", $id_course_instances);

        $sql = 'SELECT U.id_utente, U.username, U.tipo, U.nome, U.cognome
                FROM utente AS U
                JOIN
                (SELECT DISTINCT
                    id_utente_studente
                    FROM iscrizioni
                 WHERE
                    id_istanza_corso IN ('. $id_course_instances_list .')) AS I ON (U.id_utente = I.id_utente_studente)
                 ORDER BY U.cognome ASC';


        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function get_presubscribed_students_for_course_instance($id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = 'SELECT U.*, I.status,I.data_iscrizione,I.laststatusupdate';

        if(defined('MODULES_CODEMAN') && (MODULES_CODEMAN))
        {
        	$sql=$sql.', I.codice';
        }

        $sql = $sql.' FROM utente AS U, iscrizioni AS I '
	               . ' WHERE I.id_istanza_corso ='.$id_course_instance
             	   . ' AND I.status = '.ADA_STATUS_PRESUBSCRIBED
             	   . ' AND U.id_utente = I.id_utente_studente';

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * get_student_visits_for_course_instance
     * Used to fetch an associative array containing informations about
     * user activity in a course instance.
     *
     * @param integer $id_student
     * @param integer $id_course
     * @param integer $id_course_instance
     * @return mixed - an AMA_DB Error if something goes wrong or an associative array on success
     */
    public function get_student_visits_for_course_instance ( $id_student, $id_course, $id_course_instance ) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql_root_node = "SELECT N.id_nodo, N.nome, N.tipo, H.visite AS numero_visite
      FROM (SELECT id_nodo, count(data_visita) AS VISITE FROM history_nodi
      WHERE id_istanza_corso=$id_course_instance AND id_utente_studente=$id_student AND id_nodo='".$id_course."_0' GROUP BY id_nodo)
	 	                      AS H LEFT JOIN nodo AS N ON (N.id_nodo=H.id_nodo)";
        $result_root_node = $db->getRow($sql_root_node, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($result_root_node)) {
            return new AMA_Error(AMA_ERR_GET);
        }

		$nodes_id = array(ADA_LEAF_TYPE,ADA_GROUP_TYPE,ADA_NOTE_TYPE);

        //$sql = "SELECT N.nome, H.id_nodo, count(H.id_nodo) AS visite FROM history_nodi AS H LEFT JOIN nodo AS N ON (N.id_nodo=H.id_nodo) WHERE H.id_utente_studente=$id_student AND H.id_istanza_corso=$id_course_instance GROUP BY H.id_nodo ORDER BY visite DESC";
        $sql = "SELECT N.id_nodo, N.nome, N.tipo, visite.numero_visite
      FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent=N2.id_nodo)
      LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
      WHERE id_istanza_corso=$id_course_instance AND id_utente_studente=$id_student
      GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
      WHERE N.id_nodo LIKE '".$id_course."\_%' AND N.tipo IN (".implode(',',$nodes_id).") AND N2.tipo IN(".implode(',',$nodes_id).")
	             ORDER BY visite.numero_visite DESC";
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result_root_node)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        array_push($result, $result_root_node);

		/**
		 * @author giorgio 16/mag/2013
		 *
		 * just pushing the root_node will result in an array with root node always at last position,
		 * regardless of numer_visite (i.e. visit count). Let's sort the whole array so that the root
		 * node will be properly positioned as well.
		 */
        usort ($result, function ($a,$b) {
        	if ($a['numero_visite'] == $b['numero_visite']) return 0;
        	return ($a['numero_visite'] > $b['numero_visite']) ? -1 : 1;
        });

        return $result;
    }

    /**
     * get_student_visit_time
     * Used to fetch data about student visit time in a course instance.
     *
     * @param string $id_student
     * @param int $id_course_instance
     * @return mixed - an AMA_DB Error if something goes wrong or an associative array on success
     */
    public function get_student_visit_time ( $id_student, $id_course_instance ) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT id_nodo, data_visita, data_uscita, session_id
      FROM history_nodi WHERE id_utente_studente=$id_student AND id_istanza_corso=$id_course_instance ORDER BY session_id,data_uscita ASC";

        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * get_last_visited_nodes_in_period
     * Used to get last visited nodes for a student in a time period
     *
     * @param int $id_student
     * @param int $id_course_instance
     * @param int $period
     * @return - an AMA_DB Error if something goes wrong or an associative array on success
     */
    public function get_last_visited_nodes_in_period ( $id_student, $id_course_instance, $period ) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT H.id_nodo, N.nome, N.tipo, H.data_visita, H.data_uscita
      FROM history_nodi AS H LEFT JOIN nodo AS N ON (N.id_nodo=H.id_nodo)
      WHERE H.id_utente_studente=$id_student
      AND H.id_istanza_corso=$id_course_instance
      AND H.data_visita >= $period
      ORDER BY H.data_uscita DESC, H.data_visita DESC";
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * get_last_visited_nodes
     * Used to get last visited $num_visits nodes for a student
     *
     * @param int $id_student
     * @param int $id_course_instance
     * @param int $num_visits
     * @return - an AMA_DB Error if something goes wrong or an associative array on success
     */
    public function get_last_visited_nodes( $id_student, $id_course_instance, $num_visits ) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT H.id_nodo, N.nome, N.tipo, H.data_visita, H.data_uscita
      FROM history_nodi AS H LEFT JOIN nodo AS N ON (N.id_nodo=H.id_nodo)
      WHERE H.id_utente_studente=$id_student
      AND H.id_istanza_corso=$id_course_instance
      ORDER BY H.data_uscita DESC, H.data_visita DESC LIMIT $num_visits";
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);

        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * Methods accessing table `iscrizioni`
     */
    // MARK: Methods accessing table `iscrizioni`
    public function course_instance_subscribed_students_count($id_istanza_corso) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT count(id_utente_studente) FROM iscrizioni WHERE id_istanza_corso=? AND (status=? OR status=?)';
        $values = array(
          $id_istanza_corso,
          ADA_STATUS_SUBSCRIBED,
          ADA_STATUS_TERMINATED
        );

        $result = $this->getOnePrepared($sql, $values);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }
    /**
     * pre-subscribe a student
     *
     * @access public
     *
     * @param $id_studente - student id
     * @param $id_corso    - course instance id
     * @param $livello     - level of subscription (0=beginner, 1=intermediate, 2=advanced)
     *
     * @return true on success, an AMA_Error object if something goes wrong
     */
    public function course_instance_student_presubscribe_add($id_istanza_corso, $id_studente, $livello=0) {
        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // verify key uniqueness (index)
        $sql = "select id_istanza_corso from iscrizioni where id_istanza_corso=$id_istanza_corso and id_utente_studente=$id_studente";
        $id =  $db->getOne($sql);

        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if ($id) {
            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
        }
        $data_iscrizione = time();
        // insert a row into table iscrizioni
        $sql1 =  "insert into iscrizioni (id_utente_studente, id_istanza_corso, livello, status,data_iscrizione,laststatusupdate)";
        $sql1 .= " values ($id_studente, $id_istanza_corso, $livello, 1,$data_iscrizione,$data_iscrizione);";
        $res = $db->query($sql1);
        // FIXME: usare executeCritical?
        if (AMA_DB::isError($res)) {// || $db->affectedRows()==0)
            return new AMA_Error(AMA_ERR_ADD);
        }
        return true;
    }

    /**
     * Add a whole lot of students pre-subscriptions
     *
     * @access public
     *
     * @param $id_course_instance      the unique id of the course  instance
     *
     * @param $studenti_ar    the array containing the ids of the students to be added
     *
     * @return the number of students successfully added
     *
     */
    public function course_instance_students_presubscribe_add($id_course_instance, $studenti_ar) {
        $successfully_added = 0;

        for ($i=0; $i<count($studenti_ar); $i++) {
            $res = $this->course_instance_student_presubscribe_add($id_course_instance, $studenti_ar[$i]);
            if (!AMA_DataHandler::isError($res)) {
                $successfully_added++;
            }
        }

        return $successfully_added;
    }


    /**
     * Remove a whole lot of students pre-subscriptions
     *  The record is removed from table iscrizioni.
     * @access public
     *
     * @param $id_course_instance      the unique id of the course  instance
     *
     * @param $studenti_ar    the array containing the ids of the students to be removed
     *
     * @return the number of students successfully removed
     *
     */
    public function course_instance_students_presubscribe_remove($id_course_instance, $studenti_ar) {
        $successfully_removed = 0;

        for ($i=0; $i<count($studenti_ar); $i++) {
            $res = $this->course_instance_student_presubscribe_remove($id_course_instance, $studenti_ar[$i]);
            if (!AMA_DataHandler::isError($res)) {
                $successfully_removed++;
            }
        }

        return $successfully_removed;
    }

    /**
     * Removes all the subscriptions to a given course instance
     *
     * @param integer $id_course_instance
     * @return true on success, an AMA_Error object on failure
     */
    public function course_instance_students_subscriptions_remove_all($id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from iscrizioni where id_istanza_corso=$id_course_instance";
        $result = $db->query($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }

        return true;
    }

    /**
     * Remove a student pre-subscription
     * The record is removed from table iscrizioni.
     *
     * @access public
     *
     * @param $id_studente   the id of the student
     * @param $id_corso      the unique id of the course  instance
     *
     * @return an Error object if something goes wrong, true on success
     *
     */
    public function course_instance_student_presubscribe_remove($id_istanza_corso, $id_studente) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from iscrizioni where id_utente_studente=$id_studente and id_istanza_corso=$id_istanza_corso";
        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }

        $sql = 'SELECT count(id_utente_studente) FROM iscrizioni'
             . " WHERE id_istanza_corso=$id_istanza_corso"
             . ' AND status IN (' . ADA_STATUS_SUBSCRIBED.','.ADA_STATUS_TERMINATED.')';

        $res = $db->getOne($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $res;
    }



    /**
     * Set the level of students for instance course
     *
     * @access public
     *
     * @param $id_course_instance      the unique id of the course  instance
     * @param $studenti_ar             Student id array
     * @param $level                   the student levet to update
     *
     *
     * @return true on success, an AMA_Error object on failure
     *
     */
    public function set_student_level($id_course_instance, $studenti_ar, $level) {
        //tries to connect to db
        $db =& $this->getConnection();
        if ( AMA_DB::isError($db)) return $db;

        $n = count($studenti_ar);
        if ($n>0) {
            $sql = "update iscrizioni set livello=$level where id_istanza_corso=$id_course_instance ";
        }
        else {
            return 0;
        }

        for ($i=0; $i<$n; $i++) {
            $studente = $studenti_ar[$i];
            $sql .= " and id_utente_studente=$studente ";
        }

        // update the records
        $affected_rows = $this->executeCritical($sql);
        if (AMA_DB::isError($affected_rows)) {
            return $affected_rows;
        }

        return $affected_rows;

    }


    /**
     * Return the subscription status of all the students in a given cource instances
     *
     * @access public
     *
     * @param $id_course_instance     the unique id of the course instance
     *
     *
     * @return   on success, an array of hash containing the subscription statuses for all
     *           subscribed students
     *           For each element, infos are organized this way:
     *               KEY                  VALUE
     *           - id_studente           the id of the student
     *           - id_istanza_corso      the id of the course instance
     *           - livello               the level of the course
     *           - status                the actual status of subscription
     *
     *           on failure, an AMA_Error object
     *
     */
    public function course_instance_students_presubscribe_get_list($id_course_instance,$status="") {
        //tries to connect to db
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($status=="") {
            $clause = "";  // 1 OR 2
        } elseif ($status == ADA_STATUS_SUBSCRIBED) {
            $clause = 'and (status IN ('.ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED.','.
            		   ADA_STATUS_SUBSCRIBED.','.ADA_STATUS_TERMINATED.'))';
        } else {
            $clause = "and status = $status";
        }
        $sql_clause = "select * from iscrizioni where id_istanza_corso=$id_course_instance $clause";
        // do the query
        $students_ar =  $db->getAll($sql_clause);
        if (AMA_DB::isError($students_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        $n = count($students_ar);
        if($n>0) {
        	foreach ($students_ar as $key => $value) {
                $res_ar[$key]['id_utente_studente'] = $value[0];
                // $res_ar[$key]['istanza_corso'] = $value[1];
                $res_ar[$key]['livello'] = $value[2];
                $res_ar[$key]['status'] = $value[3];
                //   echo    $value[0]." ". $value[2]." ". $value[3]."<br>";
            }
            /*  modificato il 9/08/2001
       for($i=0; $i<$n; $i++){
       $res_ar[$i]['id_studente'] = $students_ar[$i][0];
       $res_ar[$i]['livello'] = $students_ar[$i][2];
       $res_ar[$i]['status'] = $students_ar[$i][3];
       }
            */
            return $res_ar;
        }

        return 0;
    }

    /**
     *
     * @param $id_student
     * @param $id_course_instance
     * @return unknown_type
     */
    public function student_can_subscribe_to_course_instance($id_student, $id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $already_subscribed_sql = "SELECT id_utente_studente FROM iscrizioni
                               WHERE id_utente_studente = $id_student
                               AND id_istanza_corso = $id_course_instance";

        $result = $db->getRow($already_subscribed_sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if(!is_array($result)) {
            $students_subscribed_sql = "SELECT count(id_utente_studente) FROM iscrizioni
      							WHERE id_istanza_corso=$id_course_instance";
            // TODO:verificare modifica apportata, passiamo da getCol a getOne
            $students_subscribed = $db->getOne($students_subscribed_sql);
            if (AMA_DB::isError($students_subscribed)) {
                return new AMA_Error(AMA_ERR_GET);
            }

            return $students_subscribed;
        }

        return false;
    }


    /**
     * Return the subscription status of a student
     *
     * @access public
     *
     * @param $id_student     the unique id of the student
     *
     *
     * @return   an array of hash containing the course_instances
     *           For each element, infos are organized this way:
     *               KEY                  VALUE
     *           - id_istanza_corso      the id of the course instance
     *           - status                the actual status of subscription
     *
     */
    public function course_instance_student_presubscribe_get_status($id_student) {
        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // do the query
        $students_ar =  $db->getAll("select * from iscrizioni where id_utente_studente=$id_student");
        if (AMA_DB::isError($students_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        $n = count($students_ar);
        if($n>0) {
        	foreach ($students_ar as $key => $value) {
                //   $res_ar[$key]['id_utente_studente'] = $value[0];
                $res_ar[$key]['istanza_corso'] = $value[1];
                //  $res_ar[$key]['livello'] = $value[2];
                $res_ar[$key]['status'] = $value[3];
                //   echo    $value[0]." ". $value[2]." ". $value[3]."<br>";
            }
            return $res_ar;
        }
        return 0;
    }


    /**
     * Subscribe a set of students to the course instance.
     * i.e: Set the status of all the students to 2 (definitevly subscribed)
     *
     * @access public
     *
     * @param $id_course_instance       the unique id of the course  instance
     *
     * @param $studenti_ar    the array containing the ids of the students to be removed
     *
     * @return the number of students successfully subscribed
     *
     */
    public function course_instance_students_subscribe($id_course_instance, $studenti_ar,$status=2) {
        $student_subscribed = 0;
        foreach ($studenti_ar as $student) {
            $res = $this->course_instance_student_subscribe($id_course_instance, $student,$status);
            // FIXME: verificare se bisogna ritornare errore o lasciare continuare l'iscrizione degli altri utenti
            if (AMA_DataHandler::isError($res)) {
                return $res;
            }
            $student_subscribed++;

        }
        return  $student_subscribed;

    }

    /**
     *
     * @param $id_course_instance
     * @param $student
     * @param $status
     * @param $user_level if null than this field is not updated
     * @return unknown_type
     */
    public function course_instance_student_subscribe($id_course_instance, $student,$status=2, $user_level=1, $lastupdateTS=null) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if (is_null($lastupdateTS)) $lastupdateTS = time();
        $sql = "update iscrizioni set status=$status, laststatusupdate=$lastupdateTS";
        if (!is_null($user_level)) $sql.=", livello=$user_level";
        $sql.=" where id_istanza_corso=$id_course_instance and id_utente_studente=$student";
        //vito, 2 feb 2009
        //$res = $this->executeCritical( $sql );
        $res = $db->query($sql);
        if (AMA_DB::isError ($res)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }


    /**
     * Unsubscribe a set of students from an instance course.
     * i.e.: set the status of the students back to 1.
     * (to effectively remove the students from table iscrizioni, use
     * course_instance_students_presubscribe_remove)
     *
     * @access public
     *
     * @param $id_corso      the unique id of the course  instance
     *
     * @param $studenti_ar    the array containing the ids of the students to be removed
     *
     * @return the number of students successfully 'removed'
     *
     */
    public function course_instance_students_unsubscribe($id_corso, $studenti_ar) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $n = count($studenti_ar);
        if ($n>0) {
        	$lastupdateTS = time();
            $sql = "update iscrizioni set status=1, laststatusupdate=$lastupdateTS where id_istanza_corso=$id_corso ";
        }
        else {
            return 0;
        }

        for ($i=0; $i<$n; $i++) {
            $studente = $studenti_ar[$i];
            $sql .= " and id_utente_studente=$studente ";
        }
        $affected_rows = $this->executeCritical($sql);
        if (AMA_DB::isError($affected_rows)) {
            return $affected_rows;
        }
        return $affected_rows;
    }



    /**
     * Check if a student is subscribed to a course  and return the type of subscription
     *
     * @access public
     *
     * @param $id_studente student id
     * @param $id_corso    course model id
     *
     * @return an hash containing the following info
     *  istanza_id - id of the instance course the student is subscribed to
     *  istanza_ha - the course instance the student is subscribed to (a hash)
     *  tipo       - type of subscription
     *               0 - no subscription
     *               1 - presubscription
     *               2 - subscription
     *  livello    - the level of the course
     */
    // vito, 2 apr 2009
    //function &get_subscription($id_studente, $id_corso)
    public function &get_subscription($id_studente, $id_istanza_corso) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;


        // vito, 2 apr 2009
        $sql =  "select ic.id_istanza_corso, ic.data_inizio, ic.durata, ic.data_inizio_previsto, isc.livello, isc.status ";
        $sql .= " from istanza_corso as ic,  iscrizioni as isc ";
        $sql .= " where isc.id_utente_studente=$id_studente ";
        $sql .= " and isc.id_istanza_corso=$id_istanza_corso " ;
        $sql .= " and ic.id_istanza_corso=$id_istanza_corso";
        //$sql .= " and isc.id_istanza_corso=ic.id_istanza_corso";

        $res_ar =  $db->getRow($sql);
        if (AMA_DB::isError($res_ar)) {
        	$err = new AMA_Error(AMA_ERR_GET);
            return $err;
        }
        if (is_array($res_ar)) {
            $ret_ha['istanza_id'] = $res_ar[0];
            $ret_ha['istanza_ha']['data_inizio'] = $res_ar[1];
            $ret_ha['istanza_ha']['durata'] = $res_ar[2];
            $ret_ha['istanza_ha']['data_inizio_previsto'] = $res_ar[3];
            $ret_ha['livello'] = $res_ar[4];
            $ret_ha['tipo'] = $res_ar[5];
            return $ret_ha;
        }
        // vito, 7 luglio 2009, se non è un array allora non ho ottenuto i dati che
        // mi servivano e restituisco un errore
        $err = new AMA_Error(AMA_ERR_NOT_FOUND);
        return $err;
    }

    public function get_course_instances_for_this_student($id_student, $extra_fields=false) {
        $sql = 'SELECT C.id_corso, C.titolo, C.crediti, IC.id_istanza_corso,'
             . ' IC.data_inizio, IC.durata, IC.data_inizio_previsto, IC.data_fine, I.status';
        if ($extra_fields) {
            $sql .= ' ,IC.title,I.data_iscrizione,IC.duration_subscription, C.tipo_servizio, IC.self_instruction, IC.tipo_servizio as `istanza_tipo_servizio`';
        }
        $sql .=' FROM modello_corso AS C, istanza_corso AS IC, iscrizioni AS I'
             . ' WHERE I.id_utente_studente=?'
             . ' AND IC.id_istanza_corso = I.id_istanza_corso'
             . ' AND C.id_corso = IC.id_corso';
        $valuesAr = array(
            $id_student
        );

        $result = $this->getAllPrepared($sql, $valuesAr,AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    public function get_course_instances_active_for_this_student($id_student) {
        $currentTime = time();
        $sql = 'SELECT C.id_corso, C.titolo, IC.id_istanza_corso, IC.self_instruction,'
             . ' IC.data_inizio, IC.durata, IC.data_inizio_previsto, IC.data_fine, I.status, C.crediti,'
             . ' I.data_iscrizione, IC.duration_subscription, C.tipo_servizio'
             . ' FROM modello_corso AS C, istanza_corso AS IC, iscrizioni AS I'
             . ' WHERE I.id_utente_studente=?'
             . ' AND IC.id_istanza_corso = I.id_istanza_corso'
             . ' AND C.id_corso = IC.id_corso'
             . ' AND IC.data_fine > ?'
             . ' AND IC.data_fine > 0';
        $valuesAr = array(
            $id_student,
            $currentTime
        );

        $result = $this->getAllPrepared($sql, $valuesAr,AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }


    public function get_id_course_instances_for_this_student($id_student) {
        $sql = 'SELECT id_istanza_corso'
             . ' FROM iscrizioni'
             . ' WHERE id_utente_studente=?';
        $valuesAr = array(
            $id_student
        );

        $result = $this->getColPrepared($sql, $valuesAr); //,AMA_FETCH_ASSOC);
//        $result = $this->getAll($sql) ;
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    // vito, 2 apr 2009
    public function &get_course_instance_for_this_student_and_course_model($id_student, $id_course, $getAll = false) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql  =  "select ic.id_istanza_corso, ic.data_inizio, ic.durata, ic.data_inizio_previsto, isc.livello, isc.status  ";
        $sql .= " from istanza_corso as ic,  iscrizioni as isc ";
        $sql .= " where ic.id_corso=$id_course ";
        $sql .= " and isc.id_istanza_corso=ic.id_istanza_corso";
        $sql .= " and isc.id_utente_studente=$id_student ";

        if ($getAll===false) $result = $db->getRow($sql);
        else $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if(!is_array($result)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        if ($getAll===false) {
	        $ret_ha['istanza_id'] = $result[0];
	        $ret_ha['istanza_ha']['data_inizio'] = $result[1];
	        $ret_ha['istanza_ha']['durata'] = $result[2];
	        $ret_ha['istanza_ha']['data_inizio_previsto'] = $result[3];
	        $ret_ha['livello'] = $result[4];
	        $ret_ha['tipo'] = $result[5];
	        return $ret_ha;
        } else return $result;
    }

    /**
     * Methods accessing table `istanza_corso`
     */
    // MARK: Methods accessing table `istanza_corso`

    /**
     * Detect if a course has instances or not
     *
     * @access public
     *
     * @param $model_id the unique id of the course model
     *
     * @return true if it has instances, false otherwise
     *         an error if something get wrong
     */
    public function course_has_instances($model_id) {

        ADALogger::log_db("entered course_has_instances (model_id: $model_id)");

        $get_instances_count_sql = 'SELECT COUNT(id_istanza_corso) FROM istanza_corso WHERE id_corso=?';

        $instances_count = $this->getOnePrepared($get_instances_count_sql, array($model_id));
        if(AMA_DB::isError($instances_count)) {
            ADALogger::log_db('Error obtaining instances count for course model : '.$model_id.'.'.$instances_count->message);
            return new AMA_Error(AMA_ERR_GET); // era AMA_ERR
        }

        if($instances_count > 0) {
            return true;
        }
        return false;
    }

    /**
     * Add an istance of a course to the table istanza_corso
     * An instance of a course is created by the administrator to make the course
     * available to the students for subscriptions.
     * Only the field data_inizio_previsto is filled at this time.
     * A course is said to be published when an instance of it exist.
     *
     * A class is formed when the fields data_inizio and durata are also filled.
     * At this moment the instance is said to be instituted.
     *
     * This method can be invoked _automatically_ by a script while creating a new class
     * in case the students are too many for a single class.
     *
     *
     * @access public
     *
     * @param $id_corso    - course model the instance originates from
     * @param $istanza_ha  - variables of the instance
     *
     *  data_inizio           - starting date
     *  durata                - duration (in days)
     *  data_inizio_previsto  - supposed starting date
     *  id_layout		  - tpl+css
     *
     * @return an AMA_Error object if something goes wrong, true on success
     */
    public function course_instance_add($id_corso, $istanza_ha) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // prepare values
        $data_inizio = $this->or_zero(isset($istanza_ha['data_inizio']) ? $istanza_ha['data_inizio'] : '');
        $durata = $this->or_zero(isset($istanza_ha['durata']) ? $istanza_ha['durata'] : '');
        $data_inizio_previsto = $this->or_zero(isset($istanza_ha['data_inizio_previsto']) ? $istanza_ha['data_inizio_previsto'] : '');
        $id_layout = $this->or_zero(isset($istanza_ha['id_layout']) ? $istanza_ha['id_layout'] : '');
        $self_instruction = $istanza_ha['self_instruction'];
        $self_registration = $istanza_ha['self_registration'];
        $price = $this->or_zero($istanza_ha['price']);
        $title = $this->sql_prepared($istanza_ha['title']);
        $duration_subscription = $this->or_zero($istanza_ha['duration_subscription']);
        $start_level_student = $this->or_zero($istanza_ha['start_level_student']);
        $open_subscription = $istanza_ha['open_subscription'];
        $duration_hours = $this->or_zero($istanza_ha['duration_hours']);
        $tipo_servizio = $this->or_null($istanza_ha['service_level']);

        // check value of supposed starting date (cannot be empty)
        if (empty($data_inizio_previsto)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in course_instance_add " .
                            AMA_SEP .": empty supposed starting date");
        }

        // vito, 17 apr 2009, set the end date of this course instance
        $data_fine = 0;
        if(empty($data_inizio)) {
            $data_fine = $this->add_number_of_days($durata,$data_inizio_previsto);
        }
        else {
            $data_fine = $this->add_number_of_days($durata,$data_inizio);
        }

        // check if corso exists
        $sql  = "select id_corso from modello_corso where id_corso=$id_corso";
        $res = $db->getOne($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (!$res) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in course_instance_add " .
                            AMA_SEP .": the course model ($id_corso) does not exist!");
        }

        // add the record
        // vito, 17 apr 2009, added data_fine
        $sql  = "insert into istanza_corso (id_corso, data_inizio, durata, ".
        		"data_inizio_previsto,id_layout,data_fine, price, self_instruction, ".
        		"self_registration, title, duration_subscription, start_level_student, ".
        		"open_subscription, duration_hours, tipo_servizio)";
        $sql .= " values ($id_corso, $data_inizio, $durata, ".
        		"$data_inizio_previsto,$id_layout, $data_fine, $price, $self_instruction, ".
        		"$self_registration, $title, $duration_subscription, $start_level_student, ".
        		"$open_subscription, $duration_hours, $tipo_servizio)";
        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            return $res;
        }
        return $db->lastInsertID();
    }

    /**
     * Remove a course from the DB
     *
     * @access public
     *
     * @param $id the unique id of the course
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong,
     * 		true on success
     *
     * @note referential integrity is checked against table iscrizioni
     */
    public function course_instance_remove($id_istanza) {

        ADALogger::log_db("entered course_instance_remove (id_istanza:$id_istanza)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // referential integrity checks
        $ri_id = $db->getOne("select id_utente_studente from iscrizioni where id_istanza_corso=$id_istanza");
        if ($ri_id) {
            ADALogger::log_db("got at least one student (uid: $ri_id) still subscribed to this instance, blocking removal");
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        $ri_id = $db->getOne("select id_utente_tutor from tutor_studenti where id_istanza_corso=$id_istanza");
        if ($ri_id) {
            ADALogger::log_db("got at least one tutor (uid: $ri_id) still assigned to this instance, blocking removal");
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        // get id of course model
        $model_id = $db->getOne("select id_corso from istanza_corso where id_istanza_corso=$id_istanza");
        if (AMA_DB::isError($model_id)) {
            ADALogger::log_db("error detected: ".$model_id->message);
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("model is $model_id");

        $sql = "delete from istanza_corso where id_istanza_corso=$id_istanza";
        ADALogger::log_db("deleting instance: $sql");

        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            return $res;
        }

        // retrieve subscribed students
        $uids = $db->getCol("select id_utente_studente from iscrizioni where id_istanza_corso=$id_istanza");

        if (AMA_DB::isError($uids)) {
            ADALogger::log_db("error detected: ".$uids->message);
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("got ".count($uids)." users");

        // loop
        foreach ($uids as $uid) {
            // delete all notes authored by uid
            $id_node_prefix = $model_id . "\_";
            $sql = "delete from nodo where id_nodo like '$id_node_prefix%' and tipo=2 and id_utente=$uid";
            ADALogger::log_db("removing all notes authored by user $uid: $sql");
            $res = $db->query($sql);
            if (AMA_DB::isError($res)) {
                ADALogger::log_db("error detected: ".$res->message);
                return new AMA_Error(AMA_ERR_REMOVE);
            }
            ADALogger::log_db("deleted!");
        }

        ADALogger::log_db("course instance successfully removed");
        return true;
    }


  /**
   * Get all informations about the users subscribed the course instances
   *
   * @access public
   *
   * @param $id the course's id
   *
   * @return an array containing all the informations about users
   *   corso                - course model the instance is originated from
   *
   */
  public function course_users_instance_get($id) {
        ADALogger::log_db("course_users_instance_get (id_corso:$id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = 'SELECT distinct U.id_utente, U.nome, U.cognome, U.username, U.codice_fiscale,IC.id_corso, I.id_utente_studente, I.id_istanza_corso, IC.data_inizio, I.status FROM
        iscrizioni AS I, istanza_corso AS IC, utente AS U
        WHERE IC.id_corso = '.$id.' AND I.id_istanza_corso = IC.id_istanza_corso AND U.id_utente = I.id_utente_studente order by U.cognome';
        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
          return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
  }


    /**
     * Get all informations about a course instance
     *
     * @access public
     *
     * @param $id the course's id
     *
     * @return an array containing all the informations about a course
     *   corso                - course model the instance is originated from
     *   data_inizio          - starting date
     *   durata               - duration of the course (in days)
     *   data_inizio_previsto - supposed starting date
     *
     */
    // vito,20 apr 2009, added argument $with_end_date.
    public function course_instance_get($id,$with_end_date=false) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table istanza_corso
        $sql = "select id_corso, data_inizio, durata, data_inizio_previsto, id_layout, data_fine, status, " .
               "price, self_instruction, self_registration, title, duration_subscription, start_level_student, ".
               "open_subscription, duration_hours, tipo_servizio as `service_level` ".
               "from istanza_corso where id_istanza_corso=$id";
        $result = $db->getRow($sql,NULL, AMA_FETCH_ASSOC);
//        print_r($result);

//        $res_ar =  $db->getRow("select id_corso, data_inizio, durata, data_inizio_previsto, id_layout, data_fine, status, " .
//                               "price, self_istruction, self_registration, title, duration_subscription, start_level_student from istanza_corso where id_istanza_corso=$id");
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (!is_array($result)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

/*
        // queste andrebbero trasformate in interi e date (devo fare le funzioni di conversione da stringa)
        $res_ha['id_corso']              = $res_ar[0];
        $res_ha['data_inizio']           = $res_ar[1];
        $res_ha['durata']                = $res_ar[2];
        $res_ha['data_inizio_previsto']  = $res_ar[3];
        $res_ha['id_layout']         	 = $res_ar[4];
        if ($with_end_date) {
            $res_ha['data_fine'] = $res_ar[5];
        }
        $res_ha['status']      	          = $res_ar[6];
        return $res_ha;
 *
 */
        return $result;
    }

    /**
     * Get informations about a course instance status
     *
     * @access public
     *
     * @param $id the course's id
     *
     * @return an integer
     *   0         private
     *   1         reserved
     *   2         public
     *
     */

    public function course_instance_status_get($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table istanza_corso
        $res_ar =  $db->getRow("select id_corso, status from istanza_corso where id_istanza_corso=$id");
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (!is_array($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $res = $res_ar[1];
        return $res;
    }



    /**
     * Find those course instances verifying the given criterium
     *
     * @access public
     *
     * @param  $field_list_ar an array containing the desired fields' names
     *         possible values are: ID_CORSO, DATA_INIZIO, DURATA, DATA_INIZIO_PREVISTO,
     *         The value of field ID_ISTANZA_CORSO is always returned
     *
     * @param  $clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &course_instance_find_list($field_list_ar, $clause='') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;
        $more_fields = '';

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }

        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }

        // do the query
        //echo "select id_istanza_corso$more_fields from istanza_corso $clause";
        $query = "select id_istanza_corso$more_fields from istanza_corso $clause";
        $courses_ar =  $db->getAll($query, null, AMA_FETCH_BOTH);
        if (AMA_DB::isError($courses_ar)) {
            $courses_ar = new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array
        //
        return $courses_ar;
    }

    /**
     * get a list of all courses instances originated from a given course model
     * if the $id_course is not given, then all the instances of all the courses are returned
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param $id_course the course model id
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &course_instance_get_list($field_list_ar, $id_corso='') {
        if ($id_corso) {
            return $this->course_instance_find_list($field_list_ar, "id_corso=$id_corso");
        }
        else {
            return $this->course_instance_find_list($field_list_ar);
        }
    }

    /**
     * get a list of all published courses instances
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param $id_course the course model id
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &course_instance_published_get_list($field_list_ar) {
        return $this->course_instance_find_list($field_list_ar, "data_inizio_previsto is not null and data_inizio is null and durata is null");
    }



    public function course_instance_subscribeable_get_list($field_list_ar,$courseId) {
        $today_date = today_dateFN();
        $timestamp = AMA_DataHandler::date_to_ts($today_date);
//        $timestamp = time();
//        return $this->course_instance_find_list($field_list_ar, "id_corso=$courseId AND self_registration=1 AND data_inizio=0 AND data_inizio_previsto >= $timestamp and durata > 0  ORDER BY data_inizio_previsto ASC");
        return $this->course_instance_find_list($field_list_ar, "id_corso=$courseId AND self_registration=1 AND open_subscription=1 ORDER BY data_inizio_previsto DESC");
    }

    /**
     * get a list of all started courses instances
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param $id_course the course model id
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &course_instance_started_get_list($field_list_ar, $id_corso='') {
    	if (strlen($id_corso)<=0) {
        	return $this->course_instance_find_list($field_list_ar, "data_inizio is not null and durata is not null");
    	} else {
    		return $this->course_instance_find_list($field_list_ar, "id__corso=$id_corso AND data_inizio is not null and durata is not null");
    	}
    }

    /**
     * Updates informations related to a course instance
     *
     * @access public
     *
     * @param $id the course's id
     *
     * @param $istanza_ha the hash containing the updating info (empty fields are not updated)
     *
     *  data_inizio           - starting date
     *  durata                - duration (in days)
     *  data_inizio_previsto  - supposed starting date
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
    public function course_instance_set($id, $istanza_ha) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // prepare values
        $data_inizio = $this->or_null($istanza_ha['data_inizio']);
        $durata = $this->or_zero($istanza_ha['durata']);
        $data_inizio_previsto = $this->or_zero($istanza_ha['data_inizio_previsto']);
        $self_instruction = $istanza_ha['self_instruction'];
        $self_registration = $istanza_ha['self_registration'];
        $price = $this->or_zero($istanza_ha['price']);
        $title = $this->sql_prepared($istanza_ha['title']);
        $duration_subscription = $this->or_zero($istanza_ha['duration_subscription']);
        $start_level_student = $this->or_zero($istanza_ha['start_level_student']);
        $open_subscription = $istanza_ha['open_subscription'];
        $duration_hours = $this->or_zero($istanza_ha['duration_hours']);
        $tipo_servizio = $this->or_null($istanza_ha['service_level']);


        // check value of supposed starting date (cannot be empty)
        if (empty($data_inizio_previsto)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_UPDATE) . " in course_instance_set " .
                            AMA_SEP . ": empty supposed starting date");

        }

        $data_fine = 0;
        if($data_inizio == "NULL") {
            $data_fine = $this->add_number_of_days($durata,$data_inizio_previsto);
        }
        else {
            $data_fine = $this->add_number_of_days($durata,$data_inizio);
        }

        // verify that the record exists
        $res_id =  $db->getRow("select id_istanza_corso from istanza_corso where id_istanza_corso=$id");
        if (AMA_DB::isError($res_id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if ($res_id == 0) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $sql  = "update istanza_corso set data_inizio=$data_inizio, durata=$durata, data_inizio_previsto=$data_inizio_previsto, ";
        $sql .= "data_fine=$data_fine, self_instruction=$self_instruction, title=$title, self_registration=$self_registration, ";
        $sql .= "price=$price, duration_subscription=$duration_subscription, start_level_student=$start_level_student, open_subscription=$open_subscription, ";
        $sql .= "duration_hours=$duration_hours, tipo_servizio=$tipo_servizio where id_istanza_corso=$id";
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        } else {
        	if (intval($data_inizio)>0) {
        		$this->_update_students_subscription_after_course_instance_set($id, intval($duration_subscription));
        	}
        }

        return true;
    }

    /**
     * update students subscription status in the passed instance to
     * either SUBSCRIBED or TERMINATED as appropriate, checking if
     * $duration_subscription + student subscription date is in the past or not
     *
     * @param number $instance_id
     * @param number $duration_subscription
     *
     * @author giorgio 02/apr/2015
     */
    private function _update_students_subscription_after_course_instance_set ($instance_id, $duration_subscription) {
    	require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
    	$subscriptions = Subscription::findSubscriptionsToClassRoom($instance_id);
    	if (!AMA_DB::isError($subscriptions) && is_array($subscriptions) && count($subscriptions)>0) {
    		foreach ($subscriptions as $subscription) {
    			$updateSubscription = false;
    			$subscritionEndDate = $this->add_number_of_days($duration_subscription, intval($subscription->getSubscriptionDate()));
    			if ($subscription->getSubscriptionStatus() == ADA_STATUS_SUBSCRIBED &&
    				$subscritionEndDate<=time()) {
    					$subscription->setSubscriptionStatus(ADA_STATUS_TERMINATED);
    					$updateSubscription = true;
    			} else if ($subscription->getSubscriptionStatus() == ADA_STATUS_TERMINATED &&
    				$subscritionEndDate>time()) {
    					$subscription->setSubscriptionStatus(ADA_STATUS_SUBSCRIBED);
    					$updateSubscription = true;
    			}

    			if ($updateSubscription) {
    				$subscription->setStartStudentLevel(null); // null means no level update
    				subscription::updateSubscription($subscription);
    			}
    		}
    	}
    }

    /**
     * get_course_id_for_course_instance
     *
     * @param int $id_course_instance
     * @return mixed - an AMA_DB Error or a course id
     */
    public function get_course_id_for_course_instance($id_course_instance) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT id_corso FROM istanza_corso WHERE id_istanza_corso=$id_course_instance";

        $result = $db->getRow($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result[0];
    }

    /**
     * get_course_info_for_course_instance
     *
     * @param int $id_course_instance
     * @return mixed - an AMA_DB Error or a course id
     */
    public function get_course_info_for_course_instance($id_course_instance) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT concat_ws(' ',U.nome,U.cognome),MC.*,U.id_utente FROM modello_corso as MC, utente as U WHERE U.id_utente = MC.id_utente_autore AND MC.id_corso = (select id_corso from istanza_corso WHERE id_istanza_corso=$id_course_instance)";
//        $sql = "SELECT MC.* FROM modello_corso as MC.id_corso = (select id_corso from istanza_corso WHERE id_istanza_corso=$id_course_instance)";


        $result = $db->getRow($sql,NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * Get course max level (based on max level of his nodes)
	 * returns constant ADA_MAX_USER_LEVEL if max level is zero or null
     *
     * @param int $id_course_instance
     * @return int
	 *
     */
    public function get_course_max_level($id_course_instance) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT MAX(n.`livello`) as max_level FROM `nodo` as n WHERE n.`id_nodo` LIKE '".intval($id_course_instance)."\_%'";

        $result = $db->getOne($sql);
		if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
		else {
			if (is_null($result)) {
				$result = ADA_MAX_USER_LEVEL;
			}
		}

        return $result;
    }

    /**
     * function get_course_instances_student_can_subscribe_to:
     *
     * used to retrieve data about course instances the student can subscribe to.
     *
     * @param  int $id_student - the id of the student
     * @return mixed - an array of course instances or an AMA_DB error.
     */
    public function get_course_instances_student_can_subscribe_to($id_student) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $current_timestamp = time();

        $sql = "SELECT IC.id_istanza_corso, IC.id_corso, IC.data_inizio, IC.durata, IC.data_inizio_previsto
  			  FROM istanza_corso as IC
             WHERE IC.data_inizio_previsto > $current_timestamp
              AND IC.id_istanza_corso NOT IN
              	(SELECT id_istanza_corso
                   FROM iscrizioni
                  WHERE id_utente_studente=$id_student
                    AND (data_inizio_previsto > $current_timestamp
                         OR (data_inizio_previsto < $current_timestamp AND data_fine > $current_timestamp)))";

        $result = $db->getAll($sql);//, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * Methods accessing table `link`
     */
    // MARK: Methods accessing table `link`

    /**
     * Add a link to table link
     *
     * @access public
     *
     * @param $link_ha an associative array containing all the link's data
     *                 the structure is as follows:
     * id_nodo           - the id of the node this link lives in
     * id_nodo_to        - the id of the node this link points to
     * pos_x0, pos_y0,   - the four coordinates of the link's position in the map
     * pos_x1, pos_y1
     * id_utente         - user id of the author
     * tipo              - type of link
     *                     0 - internal
     *                     1 - external (with respect to the node ID_NODE)
     * data_creazione    - date of creation of the link
     * stile             - style of the link
     *                     0 - continuo
     *                     1 - tratteggiato
     *                     2 - tratto-punto
     *                     3 - puntinato
     * significato        - meaning of the link (????)
     * azione             - the action following a click on this link
     *                     0 - jump
     *                     1 - popup
     *                     2 - open application
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong,
     * 		true on success
     *
     */
    public function add_link($link_ha) {

        // prepare variables for insertion
        //mydebug(__LINE__,__FILE__,$link_ha);
        $id_nodo =  $this->sql_prepared($link_ha['id_nodo']);
        $id_nodo_to =  $this->sql_prepared($link_ha['id_nodo_to']);
        $id_utente =  $this->sql_prepared($link_ha['id_utente']);
        $tipo =  $this->sql_prepared($this->or_zero($link_ha['tipo']));
        $data_creazione = $this->date_to_ts($link_ha['data_creazione']);
        $stile = $this->sql_prepared ($this->or_zero($link_ha['stile']));
        $significato = $this->sql_prepared($link_ha['significato']);
        $azione =  $this->sql_prepared($this->or_zero($link_ha['azione']));
        $pos_ar = $link_ha['posizione'];

        // check values
        if (empty($id_nodo)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_link " .
                            AMA_SEP .": no node specified");
        }

        if (empty($id_nodo_to)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) ." in add_link " .
                            AMA_SEP . ": empty destination node");
        }

        if (empty($pos_ar)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_link " .
                            AMA_SEP . ": empty position");
        }

        if (empty($id_utente)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_link " .
                            AMA_SEP . ": undefined author");
        }

        // data regarding the node's position
        $pos_x0 = $pos_ar[0];
        $pos_y0 = $pos_ar[1];
        $pos_x1 = $pos_ar[2];
        $pos_y1 = $pos_ar[3];

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if (AMA_DB::isError($db)) return $db;

        if ($id=$this->_get_id_position($pos_ar)) {
            // if a position is found in the posizione table, the use it
            $id_posizione = $id;
        }
        else {
            // add row to table "posizione"
            if (AMA_DataHandler::isError($res = $this->_add_position($pos_ar))) {
                return new AMA_Error($res->getMessage());
            }
            else {
                // get id of position just added
                $id_posizione = $this->_get_id_position($pos_ar);
            }
        }

        // insert a row into table link
        $sql  = "insert into link (id_link,id_utente,id_nodo,id_nodo_to,id_posizione,tipo,data_creazione,stile,significato,azione)";
        $sql .= " values ('',$id_utente,$id_nodo,$id_nodo_to,$id_posizione,$tipo,$data_creazione,$stile,$significato,$azione);";
        //   mydebug(__LINE__,__FILE__,$sql);
        $res = $db->query($sql);

        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            $err = $this->errorMessage(AMA_ERR_ADD) . " while in add_link " .
                    AMA_SEP .  ": " . $res->getMessage();
            return new AMA_Error($err);
        }

        return true;
    }

    /**
     * Remove a link from table link
     *
     * @access public
     * @param $link_id
     *
     * @return true on success, ana AMA_Error object on failure
     */
    public function remove_link($link_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from link where id_link=$link_id";
        $result = $db->query($sql);

        if(AMA_DB::isError($result)) {
            return new AMA_Error/(AMA_ERR_REMOVE);
        }

        return true;
    }

    /**
     * Get link id starting from the starting node and the target node
     *
     *
     * @param $node     - id of the starting node
     * @param $node_to  - id of the targeted node
     *
     * @return the id of the link or a null value
     *
     */
    public function get_link_id($sqlnode_id, $sqlnode_to_id) {
        ADALogger::log_db("entered get_link_id (node_id: $sqlnode_id, node_to_id: $sqlnode_to_id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // vito, 21 luglio 2008
        $id =  $db->getOne("select id_link from link where id_nodo=$sqlnode_id and id_nodo_to=$sqlnode_to_id");
        //$id =  $db->getOne("select id_link from link where id_nodo='$sqlnode_id' and id_nodo_to='$sqlnode_to_id'");
        if (AMA_DataHandler::isError($id)) {
            // vito, $db e' l'oggetto di connessione, l'errore e' in $id
            //                 return $db;
            return new AMA_Error(AMA_ERR_GET);
        }

        return $id;
    }

    /**
     * Get link info
     * get all informations about a link.
     *
     * @param $link_id the id of the node to query
     *
     * @return an hash containing the following values:
     * id_nodo           - the id of the node this link lives in
     * id_nodo_to        - the id of the node this link points to
     * autore            - hash with author's data
     * posizione         - 4 elements array containing the position
     * tipo              - type of link
     *                     0 - internal
     *                     1 - external (with respect to the node ID_NODE)
     * data_creazione    - date of creation of the link
     * stile             - style of the link
     *                     0 - continuo
     *                     1 - tratteggiato
     *                     2 - tratto-punto
     *                     3 - puntinato
     * significato        - meaning of the link (????)
     * azione             - the action following a click on this link
     *                     0 - jump
     *                     1 - popup
     *                     2 - open application
     *
     */
    public function get_link_info($link_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table link
        $sql  = "select id_nodo, id_utente, id_posizione, ";
        $sql .= "id_nodo_to, tipo, data_creazione, stile, significato, azione";
        $sql .= " from link where id_link='$link_id'";
        $res_ar =  $db->getRow($sql);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (!$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // author is a hash
        $author_id = $res_ar[1];
        $author_ha = $this->get_author($author_id);

        // position is a four elements array
        $pos_id = $res_ar[2];
        $pos_ar = $this->_get_position($pos_id);

        $res_ha['id_nodo']        = $res_ar[0];
        $res_ha['autore']         = $author_ha;
        $res_ha['posizione']      = $pos_ar;
        $res_ha['id_nodo_to']     = $res_ar[3];
        $res_ha['tipo']           = $res_ar[4];
        $res_ha['data_creazione'] = $res_ar[5];
        $res_ha['stile']          = $res_ar[6];
        $res_ha['significato']    = $res_ar[7];
        $res_ha['azione']         = $res_ar[8];

        return $res_ha;
    }

    /**
     * add all links in the array links_ar (within a transaction)
     *
     * @access private
     *
     * @param
     *  - links_ar bi-dimensional array containing a series of links
     *  - $node_id id of the node the links start from
     *
     */
    private function _add_links($links_ar, $node_id) {
        ADALogger::log_db("entered _add_links");
        ADALogger::log_db("starting a transaction");

        $this->_begin_transaction();

        $n = count($links_ar);
        ADALogger::log_db("got $n links to add");
        //mydebug(__LINE__,__FILE__,$links_ar);

        for ($i=1; $i<=$n; $i++) {   // links start with 1 !  steve 23/10/01
            $link_ha['id_nodo'] = $links_ar[$i]['id_nodo'];
            if (empty($link_ha['id_nodo'])) {
                $link_ha['id_nodo'] = $node_id;
            }
            $link_ha['id_nodo_to'] = $links_ar[$i]['id_nodo_to'];
            $link_ha['posizione'] = $links_ar[$i]['posizione'];
            $link_ha['id_utente'] = $links_ar[$i]['id_utente'];
            $link_ha['tipo'] = $links_ar[$i]['tipo'];
            $link_ha['data_creazione'] = $links_ar[$i]['data_creazione'];
            $link_ha['stile'] = $links_ar[$i]['stile'];
            $link_ha['significato'] = $links_ar[$i]['significato'];
            $link_ha['azione'] = $links_ar[$i]['azione'];
            //mydebug(__LINE__,__FILE__,$link_ha);

            ADALogger::log_db("trying to add link $i");
            if (AMA_DataHandler::isError($res = $this->add_link($link_ha))) {
                // does the rollback
                $err  = $res->getMessage() . AMA_SEP . $this->_rollback();
                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);
            } else {
                // add instruction to rollback segment
                $link_id = $this->get_link_id($this->sql_prepared($link_ha['id_nodo']),
                        $this->sql_prepared($link_ha['id_nodo_to']));
                ADALogger::log_db("done ($link_id), adding instruction to rbs");
                $this->_rs_add("remove_link", $link_id);
            }
        }

        ADALogger::log_db("committing links insertion");
        $this->_commit();

        return true;
    }

    /**
     * remove all links connected to node $id_node
     * $id_node must be sql_prepared before being passed to the function
     *
     * @access private
     *
     */
    private function _del_links($sqlnode_id) {
        ADALogger::log_db("enteres _del_links (sqlnode_id: $sqlnode_id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from link where id_nodo=$sqlnode_id";
        $result = $db->query($sql);

        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_DELETE);
        }

        return true;
    }

    /**
     * remove extended node $id_node
     * $id_node must be sql_prepared before being passed to the function
     *
     * @access private
     *
     */
    private function _del_extended_node($sqlnode_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from extended_node where id_node=$sqlnode_id";
        ADALogger::log_db("cleaning extended node: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            ADALogger::log_db($res->message." detected, aborting");
            return new AMA_Error(AMA_ERR_DELETE);
        }
        return true;
    }
    /**
     * Methods accessing table `log_classi`
     */
    // MARK: Methods accessing table `log_classi`
    /**
     * Add an item  to table log_classi
     *
     *
     * @access public
     *
     *
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $class_report      the report to be inserted
     *
     *
     */
    public function add_class_report( $course_id,$course_instance_id, $student_data) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $user_id = $student_data['id'];
        $date = $student_data['date'];
        $visits= $this->or_zero($student_data['visits']);
        $exercises = $this->or_zero($student_data['exercises']);
        $score = $this->or_zero($student_data['score']);
        $msg_out= $this->or_zero($student_data['msg_out']);
        $msg_in = $this->or_zero($student_data['msg_in']);
        $added_notes = $this->or_zero($student_data['added_notes']);
        $read_notes = $this->or_zero($student_data['read_notes']);
        $chat = $this->or_zero($student_data['chat']);
        $bookmarks = $this->or_zero($student_data['bookmarks']);
        $index_att= $student_data['index'];
        $level= $student_data['level'];
        $last_access = $this->or_zero($student_data['last_access']);

        if (MODULES_TEST) {
        	$exercises_test = $this->or_zero($student_data['exercises_test']);
        	$score_test = $this->or_zero($student_data['score_test']);
        	$exercises_survey = $this->or_zero($student_data['exercises_survey']);
        	$score_survey = $this->or_zero($student_data['score_survey']);
        }

        //		print_r($student_data);

        $sql = "SELECT `id_user`,`id_istanza_corso`,`data`,`id_log` from log_classi
   			WHERE `id_istanza_corso`  =  $course_instance_id AND data = $date AND `id_user` = $user_id";

        $res = $this->getRowPrepared($sql, null, AMA_FETCH_ASSOC);
        if (!AMA_DB::isError($res) && !empty($res)) {
        	$id_log = $res['id_log'];
            //data  already written, make an update
            $sql = "update log_classi set visite=".$visits.", punti=".$score.",esercizi=".$exercises.
            	   ", msg_out=".$msg_out.",msg_in=".$msg_in.",notes_out=".$added_notes.
            	   ",notes_in=".$read_notes.",chat=".$chat.",bookmarks=".$bookmarks.
            	   ",indice_att=".$index_att.",level=".$level.", last_access=".$last_access." where id_log=".$id_log;
        }
        else {
            // add a row into table log_classi
            $sql =  "insert into log_classi (id_user,id_corso, id_istanza_corso, data, visite, punti,esercizi, msg_out,msg_in,notes_out,notes_in,chat,bookmarks,indice_att,level,last_access)";
            $sql .= " values ($user_id,$course_id, $course_instance_id, $date, $visits, ";
            $sql .= "$score,$exercises, $msg_out, $msg_in, $added_notes,$read_notes, $chat,$bookmarks, $index_att,$level,$last_access);";
            //echo $sql;
        }

        $res = $db->query($sql);
        // global $debug;$debug=1;mydebug(__LINE__,__FILE__,$res); $debug=0;
        if (AMA_DB::isError($res)) {
        	return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .
                                " while in add_class_report");
        } else {
        	if (MODULES_TEST) {
        		if (!isset($id_log)) $id_log = $db->lastInsertID();
        		$sql = 'update log_classi set `exercises_test`=?, `score_test`=?, `exercises_survey`=?, `score_survey`=? where `id_log`=?';
        		$res = $this->queryPrepared($sql, array($exercises_test, $score_test, $exercises_survey, $score_survey, $id_log));
        		if (AMA_DB::isError($res)) {
        			return new AMA_Error($this->errorMessage(AMA_ERR_UPDATE) .
        					" while in add_class_report");
        		}
        	}
        }

        return true;
    }
    /**
     * Get a set of report data for a single day from table log_classi
     *
     *
     * @access public
     *
     *
     * @param $course_id    the course id
     * @param $course_instance_id     the id of the instance of that course
     * @param $date    the day (0000-00-00)
     *
     */
    public function get_class_report( $course_id,$course_instance_id,$date) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        /**
         * @author giorgio 27/ott/2014
         *
         * if we've been passed a null date, get the latest
         * availeble report for the passed course_instance_id
         */
        if (is_null($date)) {
        	$sql = "SELECT MAX(data) FROM log_classi WHERE id_istanza_corso=".$course_instance_id;
        	$res = $this->getOnePrepared($sql);
        	$date = (!AMA_DB::isError($res) && strlen($res)>0) ? $res : time();
        }

        $sql = "SELECT L.*, U.nome, U.cognome "
                . "FROM (SELECT * from log_classi WHERE id_istanza_corso=$course_instance_id AND data=$date) AS L "
                . "LEFT JOIN utente AS U ON (L.id_user=U.id_utente)";

        $res = $db->getAll($sql,array(),AMA_FETCH_ASSOC);

        //global $debug;$debug=1;mydebug(__LINE__,__FILE__,$res); $debug=0;
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_GET) ." while in get_class_report");
        }

        foreach ($res as $res_item) {
            $id_log = $res_item['id_log'];
            $student_data[$id_log]['id_stud'] = $res_item['id_user'];
            // vito, 27 mar 2009
            $student_data[$id_log]['student'] = $res_item['nome'] .' '. $res_item['cognome'];

            //        $student_data[$id_log]['id_course_instance'] = $res_item[2];
            //        $student_data[$id_log]['id_course'] = $res_item[3];

            $student_data[$id_log]['visits'] = $res_item['visite'];
            $student_data[$id_log]['date'] = $res_item['last_access'];
            //$student_data[$id_log]['visits'] = $res_item[5];
            $student_data[$id_log]['score'] = $res_item['punti'];
            $student_data[$id_log]['exercises'] = $res_item['esercizi'];
            $student_data[$id_log]['notes_out'] = $res_item['notes_out'];
            $student_data[$id_log]['notes_in'] = $res_item['notes_in'];
            $student_data[$id_log]['msg_in'] = $res_item['msg_in'];
            $student_data[$id_log]['msg_out'] = $res_item['msg_out'];
            $student_data[$id_log]['chat'] = $res_item['chat'];
            $student_data[$id_log]['bookmarks'] = $res_item['bookmarks'];
            $student_data[$id_log]['indice_att'] = $res_item['indice_att'];
            $student_data[$id_log]['level'] = $res_item['level'];
            if (MODULES_TEST) {
            	$student_data[$id_log]['exercises_test'] = $res_item['exercises_test'];
            	$student_data[$id_log]['score_test'] = $res_item['score_test'];
            	$student_data[$id_log]['exercises_survey'] = $res_item['exercises_survey'];
            	$student_data[$id_log]['score_survey'] = $res_item['score_survey'];
            }
        }
        /**
         * @author giorgio 27/ott/2014
         *
         * added report generation date
         */
        if (isset($student_data)) {
        	$student_data['report_generation_date'] = $date;
	        return $student_data;
        } else return null;
    }

    /**
     * Get  all items of report data for a single student from table log_classi
     *
     *
     * @access public
     *
     *
     * @param $course_id    the course id
     * @param $course_instance_id     the id of the instance of that course
     *@param $student_id    the student  id
     *
     */

    public function get_student_report ($course_id,$course_instance_id,$student_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT * from log_classi
   	 	WHERE id_istanza_corso  =  $course_instance_id AND id_user = $student_id";

        $res = $db->getAll($sql);
        // global $debug;$debug=1;mydebug(__LINE__,__FILE__,$res); $debug=0;
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_GET) ." while in get_student_report");
        }

        foreach ($res as $res_item) {
            $id_log = $res_item[0];
            //$student_data[$id_log]['id_stud'] = $res_item[1];
            $student_data[$id_log]['id_course_instance'] = $res_item[2];
            //$student_data[$id_log]['id_course'] = $res_item[3];
            $student_data[$id_log]['date'] = $res_item[4];
            $student_data[$id_log]['visits'] = $res_item[5];
            $student_data[$id_log]['score'] = $res_item[6];
            $student_data[$id_log]['exercises'] = $res_item[7];
            $student_data[$id_log]['msg_in'] = $res_item[9];
            $student_data[$id_log]['msg_out'] = $res_item[9];
            $student_data[$id_log]['notes_in'] = $res_item[10];
            $student_data[$id_log]['notes_out'] = $res_item[11];
            $student_data[$id_log]['chat'] = $res_item[11];
            $student_data[$id_log]['bookmarks'] = $res_item[12];
            $student_data[$id_log]['indice_att'] = $res_item[13];
            $student_data[$id_log]['level'] = $res_item[14];
        }
        return $student_data;
    }

    /**
     *
     * @param $student_id
     * @param $course_instance_id
     * @param $clause
     * @param $out_fields_ar
     * @return unknown_type
     */
    public function find_student_report ($student_id,$course_instance_id,$clause,$out_fields_ar) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        }
        // add a 'where' on top of the clause
        // handle null clause, too
        $top_clause = " where id_istanza_corso  =  $course_instance_id AND id_user = $student_id";
        if ($clause) {
            $top_clause .= "AND $clause";
        }
        $sql = "select id_log,id_istanza_corso,id_user$more_fields from log_classi $top_clause order by id_log";
        // do the query
        $res =  $db->getAll($sql);

        // global $debug;$debug=1;mydebug(__LINE__,__FILE__,$res); $debug=0;
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_GET) . " while in find_student_report");
        }

        return $res;
    }

    /**
     * Methods accessing table `modello_corso`
     */
    // MARK: Methods accessing table `modello_corso`

    /**
     * Add a course to the DB
     *
     * @access public
     *
     * @param $course_ha an associative array containing all the course's data
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong,
     *          or the id of new course if it is ok
     *
     */
    public function add_course($course_ha) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // prepare values
        // *(dovrei aggiungere le funzioni per le date)*
        $nome = $this->sql_prepared($course_ha['nome']);
        $titolo = $this->sql_prepared($course_ha['titolo']);
        $descr = $this->or_null($this->sql_prepared($course_ha['descr']));
        $d_create = $this->date_to_ts($this->or_null($course_ha['d_create']));

        /**
         * @author giorgio 15/lug/2013
         * nobody remembers how come this lines of code sets the published date
         * value to either creation or publication date.
         * Anyway, today it was decided to comment 'em out and set the published date
         * to the passed one.
         */

//         if (isset($course_ha['d_create'])) {
//             $date_to_ts = $course_ha['d_create'];
//         }
//         else if(isset($course_ha['d_publish'])) {
//             $date_to_ts = $course_ha['d_publish'];
//         }
//         else {
//             $date_to_ts = null;
//         }

//         $d_publish = $this->date_to_ts($this->or_null($date_to_ts));
		/**
         * @author giorgio 15/lug/2013
         * this line was added
		 */
        $d_publish = $this->date_to_ts($this->or_null($course_ha['d_publish']));

        $id_autore = $this->or_zero($course_ha['id_autore']);
        $id_nodo_iniziale = $this->or_zero($this->sql_prepared($course_ha['id_nodo_toc']));
        $id_nodo_toc = $this->or_zero($this->sql_prepared($course_ha['id_nodo_iniziale']));
        $media_path = $this->or_null($this->sql_prepared($course_ha['media_path']));

        $static_mode = $this->or_zero($this->sql_prepared($course_ha['static_mode']));
        $id_lingua = $this->sql_prepared($course_ha['id_lingua']);
        $crediti =  $this->or_zero($course_ha['crediti']);
        $duration_hours = $this->or_zero($course_ha['duration_hours']);
        $service_type = $this->or_null($course_ha['service_level']);

        // verify key uniqueness (index)
        $id =  $db->getOne("select id_corso from modello_corso where nome = $nome");
        if ($id) {
            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
        }

        // insert a row into table modello_corso
        $sql1 =  "insert into modello_corso (id_corso, nome, titolo, id_utente_autore, descrizione, data_creazione, data_pubblicazione, id_nodo_toc, id_nodo_iniziale, media_path, static_mode, id_lingua, crediti, duration_hours,tipo_servizio)";
        $sql1 .= " values (";
        /**
         * @author giorgio 03/lug/2013
         *
         * call to common dh to get the new id_corso for the course to be inserted
         */
        $sql1 .= (AMA_Common_DataHandler::instance()->get_course_max_id()+1);
        $sql1 .= ", $nome, $titolo, $id_autore, $descr, $d_create, $d_publish, $id_nodo_toc, $id_nodo_iniziale, $media_path,$static_mode,$id_lingua, $crediti, $duration_hours,$service_type);";

        $res = $this->executeCritical( $sql1 );

        if (AMA_DB::isError($res)) {
            return $res;
        }

        $id =  $db->getOne("select id_corso from modello_corso where nome = $nome");
        if(AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $id;
    }

    /**
     * Remove a course model from the DB
     *
     * @access public
     *
     * @param $id the unique id of the course
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *         true on success
     *
     * @note referential integrity is checked against tables
     *  tutor_corso, amministratore_corsi and nodo
     * it must be impossible to remove a course model
     *  if a tutor or an administrator is still assigned to it
     * it must be impossible to remove a course model if nodes relating to this course
     *  still are in the DB
     */
    public function remove_course_model($id) {

        ADALogger::log_db("entered remove_course_model (id:$id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $ri_id = $db->getOne("select id_utente_amministratore from amministratore_corsi where id_corso=$id");
        if ($ri_id) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        $id_node_prefix = $id . "\_";
        $ri_id = $db->getOne("select id_nodo from nodo where id_nodo LIKE '$id_node_prefix%'");

        if ($ri_id) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        // do the removal
        $sql = "delete from modello_corso where id_corso=$id";
        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            return $res;
        }

        ADALogger::log_db("course model successfully removed");

        return true;
    }

    /**
     * Remove a course content from the DB
     * This means all nodes are removed, that have their ID_NODO field
     * starting with the ID_CORSO parameter
     * This function can be useful both in eliminating a whole course from the system,
     * and in removing the content if some errors occur during the upload phase
     *
     * @access public
     *
     * @param $id the unique id of the course the content relates to
     *
     * @return
     *  - true if everything is OK
     *  - an AMA_ERR_NOT_FOUND if no records to remove were found
     *  - another ERROR if something goes wrong
     *
     * @note referential integrity isn't checked since only nodes are removed
     */
    public function remove_course_content($id) {

        ADALogger::log_db("entered remove_course_content (id:$id)");

        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        // get all nodes relating to this course
        $id_node_prefix = $id . "\_";

        $ids = $db->getCol("select id_nodo from nodo where id_nodo LIKE '$id_node_prefix%'");
        ADALogger::log_db("getting all nodes related to this course ".count($ids));
        if (empty($ids)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $n_ids = count($ids);
        ADALogger::log_db("got ".count($ids)." records");

        // start a loop to remove the nodes
        for ($i=0; $i<$n_ids; $i++) {
            $res = $this->remove_node($ids[$i]);
            // FIXME: resituire subito l'errore?
            if (AMA_DataHandler::isError($res)) {
                return $res;
            }
        }
        return true;
    }

    /**
     * Remove a whole course from the DB (content, instances and model)
     * This is done by calling the three functions:
     *  - remove_course_content
     *  - course_instance_remove on all instance related to the model
     *  - remove_course_model
     *
     * @access public
     *
     * @param $id the unique id of the course to be zapped away
     *
     * @return
     *  - nothing if everything is OK
     *  - an AMA_ERR_NOT_FOUND if no such course were found
     *  - another ERROR if something goes wrong with any of the functions called
     *
     * @note this is a kind of 'macro', so referential integrity is not checked
     * (it is checked inside functions);
     */
    public function remove_course($id) {

        ADALogger::log_db("entered remove_course");
        ADALogger::log_db("id: $id");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // remove content
        // return only if error is different from AMA_ERR_NOT_FOUND
        ADALogger::log_db("trying to remove course content");
        $res = $this->remove_course_content($id);

        if (AMA_DataHandler::isError($res) && $res->code != AMA_ERR_NOT_FOUND) {
            return $res;
        }

        ADALogger::log_db("content successfully removed");
        // find all instances related to this model
        ADALogger::log_db("getting instances related to this model -$id-");

        $ids = $db->getCol("select id_istanza_corso from istanza_corso where id_corso=$id");
        if (!AMA_DataHandler::isError($ids)) {
            $n_ids = count($ids);
            ADALogger::log_db("got $n_ids records");
            // start a loop to remove the instances (may be a void loop)
            for ($i=0; $i<$n_ids; $i++) {
                ADALogger::log_db("trying to remove course instance ".$ids[$i]);
                $res = $this->course_instance_remove($ids[$i]);
                if (AMA_DataHandler::isError($res)) {
                    return $res;
                }
                ADALogger::log_db("instance ".$ids[$i]." successfully removed");
            }
        }
        // FIXME: else restituire un errore?

        // remove the course model
        ADALogger::log_db("trying to remove course model ".$id);
        $res = $this->remove_course_model($id);
        if (AMA_DataHandler::isError($res)) {
            return $res;
        }
        ADALogger::log_db("course model $id successfully removed");

        return true;
    }

    /**
     * Get a list of courses' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_courses_list
     */
    public function &get_courses_list($field_list_ar) {
        return $this->find_courses_list($field_list_ar);
    }

    /**
     * Get a list of courses' ids from the DB
     *
     * @access public
     *
     * @return an array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     *
     * @see find_courses_list, get_courses_list
     */
    public function &get_courses_ids() {
        return $this->get_courses_list();
    }

    /**
     * Get courses where a keyword is in one of the fields specified
     *
     * @access public
     *
     * @param  $out_fields_ar an array containing the desired fields' names
     *         possible values are: nome, titolo, id_utente_autore, descrizione,
     *         data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param  $key the keyword or sentence to look for (a string)
     *
     * @param  $search_fields_ar array of fields where the key must be looked for
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_courses_list
     */
    public function &find_courses_list_by_key($out_fields_ar, $key, $search_fields_ar) {

        $clause = '';
        $n = count($search_fields_ar);
        for ($i=0; $i<$n; $i++) {
            if ($i<$n-1) {
                $or = " OR ";
            }
            else {
                $or = "";
            }
            $clause .= $search_fields_ar[$i] . " LIKE '%" . $key . "%' " . $or;
        }

        return $this->find_courses_list($out_fields_ar, $clause);
    }

    /**
     * Find those courses verifying the given criterium
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param  clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &find_courses_list($field_list_ar, $clause='') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }
        // add an 'and' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }
        $query = "select id_corso$more_fields from modello_corso $clause";
        // do the query
        $courses_ar =  $db->getAll($query,null,AMA_FETCH_BOTH);

        if (AMA_DB::isError($courses_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        //
        // return nested array in the form
        //
        if (!$courses_ar) {
            $retval = new AMA_Error(AMA_ERR_NOT_FOUND);
            return $retval;
        }
        if (!is_array($courses_ar)) {
            $retval = new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
            return $retval;
        }
        return $courses_ar;
    }

    /**
     * Among the courses available for subscription,
     * get those where a keyword is in one of the fields specified
     *
     * @access public
     *
     * @param  $out_fields_ar an array containing the desired fields' names
     *         possible values are: nome, titolo, id_utente_autore, descrizione,
     *         data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param  $key the keyword or sentence to look for (a string)
     *
     * @param  $search_fields_ar array of fields where the key must be looked for
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_courses_list
     */
    public function &find_sub_courses_list_by_key($out_fields_ar, $key, $search_fields_ar) {
        $clause = "";
        $n = count($search_fields_ar);
        for ($i=0; $i<$n; $i++) {
            if ($i<$n-1) {
                $or = " OR ";
            }
            else {
                $or = "";
            }
            $clause .= $search_fields_ar[$i] . " LIKE '%" . $key . "%' " . $or;
        }

        return $this->find_sub_courses_list($out_fields_ar, $clause);
    }

    /**
     * Among the courses available for subscription,
     * get those where a criterium is satisfied
     *
     * @access public
     *
     * @param  $out_fields_ar an array containing the desired fields' names
     *         possible values are: nome, titolo, id_utente_autore, descrizione,
     *         data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @param  $clause the criterium (as a where clause, without where)
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_courses_list
     */
    public function &find_sub_courses_list($out_fields_ar, $clause="") {
        $complete_clause = "data_pubblicazione IS NOT NULL";

        if (isset($clause) && $clause != "") {
            $complete_clause .= " AND ($clause)";
        }
        return $this->find_courses_list($out_fields_ar, $complete_clause);
    }

    /**
     * Among the courses available for subscription,
     * get a list of courses' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, titolo, id_utente_autore, descrizione,
     *        data_creazione, data_pubblicazione, media_path, (id_nodo_iniziale), (id_nodo_toc)
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_courses_list
     */
    public function &get_sub_courses_list($field_list_ar) {
        return $this->find_sub_courses_list($field_list_ar);
    }

    /**
     * Get all informations about a course
     *
     * @access public
     *
     * @param $id the course's id
     *
     * @return an array containing all the informations about a course
     *        res_ha['nome']
     *        res_ha['titolo']
     *        res_ha['id_autore']
     *        res_ha['id_layout']
     *        res_ha['descr']
     *        res_ha['d_create']
     *        res_ha['d_publish']
     *        res_ha['id_nodo_iniziale']
     *        res_ha['id_nodo_toc']
     *        res_ha['media_path']
     *
     */
    public function get_course($id) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table MODELLO_CORSO
        $res_ar =  $db->getRow("select nome, titolo, id_utente_autore, id_layout, descrizione, data_creazione, data_pubblicazione, id_nodo_iniziale, id_nodo_toc, media_path,static_mode, id_lingua, crediti, duration_hours,tipo_servizio from modello_corso where id_corso=$id");
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if ((!$res_ar) OR (!is_array($res_ar))) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // queste andrebbero trasformate in interi e date (devo fare le funzioni di conversione da stringa)
        $res_ha['nome']                  = $res_ar[0];
        $res_ha['titolo']                = $res_ar[1];
        $res_ha['id_autore']             = $res_ar[2];
        $res_ha['id_layout']             = $res_ar[3];
        $res_ha['descr']                 = $res_ar[4];
        $res_ha['d_create']              = self::ts_to_date($res_ar[5]);
        $res_ha['d_publish']             = self::ts_to_date($res_ar[6]);
        $res_ha['id_nodo_iniziale']      = $res_ar[7];
        $res_ha['id_nodo_toc']           = $res_ar[8];
        $res_ha['media_path']            = $res_ar[9];
        $res_ha['static_mode']           = $res_ar[10];
        $res_ha['id_lingua']             = $res_ar[11];
        $res_ha['crediti']               = $res_ar[12];
        $res_ha['duration_hours']        = $res_ar[13];
        $res_ha['service_level']         = $res_ar[14];

        return $res_ha;
    }

    /**
     * Updates informations related to a course
     *
     * @access public
     *
     * @param $id the course's id
     *        $course_ar the informations. empty fields are not updated
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
    public function set_course($id, $course_ha) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // prepare values
        // (dovrei aggiungere le funzioni per le date)
        $nome = $this->sql_prepared($course_ha['nome']);
        $titolo = $this->sql_prepared($course_ha['titolo']);
        $descr = $this->or_null($this->sql_prepared($course_ha['descr']));
        $d_create = $this->date_to_ts($this->or_null($course_ha['d_create']));
        $d_publish = $this->date_to_ts($this->or_null($course_ha['d_publish']));
        $id_autore = $this->or_zero($course_ha['id_autore']);
        $id_layout = $this->or_zero(isset($course_ha['id_layout']) ? $course_ha['id_layout'] : '');
        $id_lingua = $this->or_zero($course_ha['id_lingua']);
        $crediti = $this->or_zero($course_ha['crediti']);
        $duration_hours = $this->or_zero($course_ha['duration_hours']);
        $service_type = $this->or_null($course_ha['service_level']);
        /*
     modifica 25/07/01 : non devono essere 0 ma ci devono essere
     $id_nodo_iniziale = $this->or_zero($this->sql_prepared($course_ha['id_nodo_toc']));
     $id_nodo_toc = $this->or_zero($this->sql_prepared($course_ha['id_nodo_iniziale']));
        */

        if (empty($course_ha['id_nodo_iniziale'])) {
            //$id_nodo_iniziale = $id."_0";      dovrebbe essere una stringa !!!
            $id_nodo_iniziale = "0";
        }

        if (empty($course_ha['id_nodo_toc'])) {
            //$id_nodo_toc = $id."_0";           dovrebbe essere una stringa !!!
            $id_nodo_toc = "0";
        }
        /* fine modifica */

        $media_path = $this->or_null($this->sql_prepared($course_ha['media_path']));
        $res_id = 0;

        // verify that the record exists and store old values for rollback
        $res_id =  $db->getRow("select id_corso from modello_corso where id_corso=$id");
        if (AMA_DB::isError($res_id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if ($res_id == 0) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // backup old values
        $old_values_ha = $this->get_course($id);
        // verify unique constraint once updated
        $new_nome = $course_ha['nome'];
        $old_nome = $old_values_ha['nome'];
        if ($new_nome != $old_nome) {
            $res_id = $db->getOne("select id_corso from modello_corso where nome=$nome");
            if (AMA_DB::isError($res_id)) {
                return new AMA_Error(AMA_ERR_GET);
            }
            if ($res_id) {
                return new AMA_Error(AMA_ERR_UNIQUE_KEY);
            }
        }

        // update the rows in the tables
        $sql1  = "update modello_corso set nome=$nome, titolo=$titolo, descrizione=$descr, data_creazione=$d_create, data_pubblicazione=$d_publish, id_utente_autore=$id_autore, id_nodo_toc=$id_nodo_toc, id_nodo_iniziale=$id_nodo_iniziale, media_path=$media_path, id_layout=$id_layout, id_lingua=$id_lingua, crediti=$crediti, duration_hours=$duration_hours,tipo_servizio=$service_type where id_corso=$id";
        $res = $db->query($sql1);
        if (AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }

   /*
    * Get course type
    *
    * @access public
    *
    * @ return course_type
    *
    * @return an error if something goes wrong
    *
    */
    public function get_course_type($id_course) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
        $sql = "SELECT tipo_servizio FROM modello_corso where id_corso=$id_course ";
        $result = $db->getOne($sql);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }



    /**
     * Methods accessing table `nodo`
     */
    // MARK: Methods accessing table `nodo`

        /**
     * Verify node existence
     *
     *
     * @access public
     *
     * @param $node_id
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *
     */
    public function node_exists($node_id) {

        $sql = 'SELECT id_nodo FROM nodo WHERE id_nodo=?';
        $values = array(
            $node_id
        );
        $result = $this->getOnePrepared($sql, $values);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }
    /**
     * Returns the id of the child having order $child_order if it exists and if
     * it is not a note or a private note.
     *
     * @param string $parent_node_id
     * @param integer $child_order
     * @param string operator comparison operator to be used in ordine clause. Can be one of: >, <, =, !=, >=, <=. Defaluts to =
     * @return string the id of the child, or an AMA_Error
     */
    public function child_exists($parent_node_id, $child_order, $user_level=ADA_MAX_USER_LEVEL, $operator='=') {

    	$allowedOperators = array (
    			'>' => array('sortorder'=>'ASC'),
    			'=' => array('sortorder'=>'ASC'),
    			'>='=> array('sortorder'=>'ASC'),
    			'!='=> array('sortorder'=>'ASC'),
    			'<' => array('sortorder'=>'DESC'),
    			'<='=> array('sortorder'=>'DESC')
    	);

    	if (array_key_exists($operator, $allowedOperators)) {

	        $sql = 'SELECT id_nodo FROM nodo WHERE livello <= ? AND id_nodo_parent=? AND ordine'.$operator.'? AND tipo NOT IN (2,21) ORDER BY ordine '.$allowedOperators[$operator]['sortorder'];
	        $values = array(
	            $user_level,
	            $parent_node_id,
	            $child_order
	        );
	        $result = $this->getOnePrepared($sql, $values);
	        if (AMA_DB::isError($result)) {
	            return new AMA_Error(AMA_ERR_GET);
	        }
	        return $result;
    	} else return new AMA_Error(AMA_ERR_WRONG_ARGUMENTS);
    }

    /**
     * Returns the id of the last child of the given node if it exists and if
     * it is not a note or a private note.
     *
     * @param string $parent_node_id
     * @param integere $child_order
     * @return string the id of the child, or an AMA_Error
     */
    public function last_child_exists($parent_node_id, $user_level=ADA_MAX_USER_LEVEL) {

        $sql = 'SELECT id_nodo FROM nodo WHERE livello <= ? AND id_nodo_parent = ?  AND tipo NOT IN (2,21) ORDER BY ordine DESC LIMIT 1';
        $values = array(
            $user_level,
            $parent_node_id
        );
        $result = $this->getOnePrepared($sql, $values);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    public function get_max_idFN($id_course=1,$id_toc='',$depth=1){
      // return the max id_node of the course
      $id_node_max = $this->_get_max_idFN($id_course,$id_toc,$depth);
      // vito, 15/07/2009
      if (AMA_DataHandler::isError($id_node_max)) {
        /*
         * Return a ADA_Error object with delayedErrorHandling set to TRUE.
         */
          return new ADA_Error(
            $id_node_max,translateFN('Errore in lettura max id'),
            'get_max_idFN',
            NULL,NULL,NULL,TRUE
          );
      }
      return $id_node_max;
    }

    /**
     * Get last node for a course
     *
     *
     * @access public
     *
     * @param $id_course, $id_toc, $depth
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *
     */
    public function _get_max_idFN($id_course,$id_toc,$depth) {
        // return the max id_node of the course
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $out_fields_ar = array('nome');
        $key = $id_course . "\_";
        $id_node_max = "";
        $clause = "ID_NODO LIKE '$key%'";
        $nodes = $this->_find_nodes_list($out_fields_ar, $clause);
        if(AMA_DB::isError($nodes)) {
            return $nodes;
        }

        foreach ($nodes as $single_node) {
            $id_node = $single_node[0];
            $id_temp = substr($id_node, 2); // get only the part of node
            $id_temp_ar = explode("_",$id_node);
            $id_temp = $id_temp_ar[1];
            if ($id_temp > $id_node_max) {
                $id_node_max = $id_temp;
            }
        }
        // additional control to ensure that nobody has inserted new node
        // recursive function
        $newNodeId = $id_course ."_". (intval($id_node_max) + 1);
        $clause = "ID_NODO = '$newNodeId'";
        $nodes = $this->_find_nodes_list($out_fields_ar, $clause);
        if(AMA_DB::isError($nodes) || count($nodes) == 0) {
            $id_node_max = $id_course ."_". $id_node_max;
            return $id_node_max;
        } else {
            return $this->_get_max_idFN($id_course,$id_toc,$depth);
        }
    }

    /**
     * Add the node extension (only for ADA_WORD_LEAF_TYPE and ADA_WORD_GROUP_TYPE)
     * only add the node extension.
     * This function is called from the public add_node function.
     *
     * @access private
     *
     * @param $node_ha an associative array containing all the node's data (see add_node public function)
     *
     * @return an AMA_Error object if something goes wrong,
     *         true on success
     *
     * @see add_node()
     */
    protected function _add_extension_node($node_ha) {
        ADALogger::log_db("entered _add_extension_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // FIXME: l'id del nodo dovrebbe venire ottenuto qui e non passato nell'array $node_ha
        $id_node = $this->sql_prepared($node_ha['id']);
        $hyphenation = $this->sql_prepared($node_ha['hyphenation']);
        $grammar = $this->sql_prepared($node_ha['grammar']);
        $semantic = $this->sql_prepared($node_ha['semantic']);
        $notes = $this->sql_prepared($node_ha['notes']);
        $examples = $this->sql_prepared($node_ha['examples']);
        $language = $this->sql_prepared($node_ha['lingua']);

        $sql  = "insert into extended_node (id_node, hyphenation, grammar, semantic, notes, examples, language)";
        $sql .= " values ($id_node,  $hyphenation, $grammar, $semantic, $notes, $examples, $language)";
        ADALogger::log_db("trying inserting the extended_node: $sql");

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            ADALogger::log_db($res->getMessage());
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _add_extension_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }
        ADALogger::log_db("extended_node inserted");
        return true;
    }

    /**
     * Add a node
     * only add a node. Leaves out position, author and course.
     * This function is called from the public add_node function.
     *
     * @access private
     *
     * @param $node_ha an associative array containing all the node's data (see public function)
     *
     * @return an AMA_Error object if something goes wrong,
     *         true on success
     *
     * @see add_node()
     */
    // FIXME: probabiltmente dovrà diventare pubblico.
    protected function _add_node($node_ha) {
        ADALogger::log_db("entered _add_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // FIXME: l'id del nodo dovrebbe venire ottenuto qui e non passato nell'array $node_ha
        // Fixed by Graffio 08/11/2011
        //$id_node = $this->sql_prepared($node_ha['id']);
        $id_author = $node_ha['id_node_author'];
        $name = $this->sql_prepared($this->or_null(isset($node_ha['name']) ? $node_ha['name'] : null));
        $title = $this->sql_prepared($this->or_null(isset($node_ha['title']) ? $node_ha['title'] : null));

        $text = $this->sql_prepared(isset($node_ha['text']) ? $node_ha['text'] : null);
        $type = $this->sql_prepared($this->or_zero(isset($node_ha['type']) ? $node_ha['type'] : null));
        $creation_date = $this->date_to_ts($this->or_null(isset($node_ha['creation_date']) ? $node_ha['creation_date'] : ''));
        $parent_id = $this->sql_prepared(isset($node_ha['parent_id']) ? $node_ha['parent_id'] : null);
        $order = $this->sql_prepared($this->or_null(isset($node_ha['order']) ? $node_ha['order'] : null));
        $level = $this->sql_prepared($this->or_zero(isset($node_ha['level']) ? $node_ha['level'] : null));
        $version = $this->sql_prepared($this->or_zero(isset($node_ha['version']) ? $node_ha['version'] : null));
        $n_contacts = $this->sql_prepared($this->or_zero(isset($node_ha['n_contacts']) ? $node_ha['n_contacts'] : null));
        $icon = $this->sql_prepared($this->or_null(isset($node_ha['icon']) ? $node_ha['icon'] : null));

        // modified 7/7/01 ste
        // $color = $this->or_zero($node_ha['color']);
        $bgcolor = $this->sql_prepared($this->or_null(isset($node_ha['bgcolor']) ? $node_ha['bgcolor'] : null));
        $color = $this->sql_prepared($this->or_null(isset($node_ha['color']) ? $node_ha['color'] : null));
        // end
        $correctness = $this->sql_prepared($this->or_zero(isset($node_ha['correctness']) ? $node_ha['correctness'] : null));
        $copyright = $this->sql_prepared($this->or_zero(isset($node_ha['copyright']) ? $node_ha['copyright'] : null));
        // added 6/7/01 ste
        $id_position = $this->sql_prepared(isset($node_ha['id_position']) ? $node_ha['id_position'] : null);
        $lingua = $this->sql_prepared(isset($node_ha['lingua']) ? $node_ha['lingua'] : null);
        $pubblicato = $this->sql_prepared(isset($node_ha['pubblicato']) ? $node_ha['pubblicato'] : null);
        // end
        // added 24/7/02 ste
        //  $family = $this->date_to_ts($this->or_null($node_ha['family']));
        // end

        // added  2/4/03
        if (array_key_exists('id_instance',$node_ha)) {
            $id_instance = $this->sql_prepared($this->or_null($node_ha['id_instance']));
        }
        else {
            $id_instance = "''";
        }
        //end
        /******
         * graffio 08/11/2012
         * get the last id of the course.
         * If new course the first node of a course MUST be idCourse_0
         */
        if (isset($node_ha['id_course']) and ($node_ha['parent_id'] == null || $node_ha['parent_id'] == '')) {
            $new_node_id = $node_ha['id_course']. '_' . '0';
        } else {
            $parentId = $node_ha['parent_id'];
//             $regExp = '#^([1-9][0-9]+)_#';
            // giorgio 09/mag/2013
            // fixed bug in regexp, it mached two digits only and gived back no match for the first 9 courses!
            $regExp = '#^([1-9][0-9]*)_#';
            preg_match($regExp, $parentId, $stringFound);
            if (count($stringFound) > 0) {
                $idCourse = $stringFound[1];
                $last_node = get_max_idFN($idCourse);
                $tempAr = explode ("_", $last_node);
                $newId =intval($tempAr[1]) + 1;
                $new_node_id = $idCourse . "_" . $newId;
            }
        }
            $id_node = $this->sql_prepared($new_node_id);

        /***************************/
        // verify key uniqueness on nodo
        // Modifica di Graffio del 03/12/01
        // Se il nodo c'e' gia va avanti
        // E' corretto?????
        /***************************/
        /*
        $sql = "select id_nodo from nodo where id_nodo = $id_node";
        ADALogger::log_db("Query: $sql");
        $id =  $db->getOne($sql);
        if(AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("Query result: $id");

        if (!empty($id)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_UNIQUE_KEY) . " while in _add_node.");
        }
         *
         */
        /***************************/
        /*  + family
     $sql  = "insert into nodo (id_nodo, id_utente,id_posizione, nome, titolo, testo, tipo, data_creazione, id_nodo_parent, ordine, livello, versione, n_contatti, icona, colore_didascalia, colore_sfondo, correttezza, copyright, family)";
     $sql .= " values ($id_node,  $id_author, $id_position, $name, $title, $text, $type, $creation_date, $parent_id, $order, $level, $version, $n_contacts, $icon, $color, $bgcolor, $correctness, $copyright, $family)";
        */
        // insert a row into table nodo
        $sql  = "insert into nodo (id_nodo, id_utente,id_posizione, nome, titolo, testo, tipo, data_creazione, id_nodo_parent, ordine, livello, versione, n_contatti, icona, colore_didascalia, colore_sfondo, correttezza, copyright, lingua, pubblicato, id_istanza)";
        $sql .= " values ($id_node,  $id_author, $id_position, $name, $title, $text, $type, $creation_date, $parent_id, $order, $level, $version, $n_contacts, $icon, $color, $bgcolor, $correctness, $copyright, $lingua, $pubblicato, $id_instance)";
        ADALogger::log_db("trying inserting the node: $sql");

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _add_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }

        //return true;
        return $new_node_id;
    }

    /**
     * Edit a node
     *  edit type, title, name and text (title, name and text are compulsory)
     *
     * @access public
     *
     * @param $node_ha an associative array containing all the node's data (see public function)
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
    public function _edit_node($node_ha) {
        ADALogger::log_db("entered _edit_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $id_node = $this->sql_prepared($node_ha['id']);
        $name = $this->sql_prepared($this->or_null($node_ha['name']));
        $title = $this->sql_prepared($this->or_null(isset($node_ha['title']) ? $node_ha['title'] : ''));

        $text = $this->sql_prepared($node_ha['text']);
        //if (isset($node_ha['type'])) {
        $type = $this->sql_prepared($this->or_zero($node_ha['type']));
        //}
        //if (isset($node_ha['id_instance'])) {
        $id_instance = $this->sql_prepared($this->or_null(isset($node_ha['id_instance']) ? $node_ha['id_instance'] : ''));
        //}
        $parent_id = $this->sql_prepared($this->or_null($node_ha['parent_id']));

        $order = $this->sql_prepared($this->or_zero($node_ha['order']));
        $level = $this->sql_prepared($this->or_zero(isset($node_ha['level']) ? $node_ha['level'] : ''));
        $version = $this->sql_prepared($this->or_zero(isset($node_ha['version']) ? $node_ha['version'] : ''));
        $icon = $this->sql_prepared($this->or_null(isset($node_ha['icon']) ? $node_ha['icon'] : ''));
        $correctness = $this->sql_prepared($this->or_zero(isset($node_ha['correctness']) ? $node_ha['correctness'] : ''));

        /*
     * vito, 23 jan 2009
     * check if node position was given
        */
        if (isset($node_ha['pos_x0']) && is_numeric($node_ha['pos_x0']) &&
                isset($node_ha['pos_x1']) && is_numeric($node_ha['pos_x1']) &&
                isset($node_ha['pos_y0']) && is_numeric($node_ha['pos_y0']) &&
                isset($node_ha['pos_y1']) && is_numeric($node_ha['pos_y1'])) {

            $position_ar = array($node_ha['pos_x0'], $node_ha['pos_y0'], $node_ha['pos_x1'], $node_ha['pos_y1']);
            $position_id = $this->_get_id_position($position_ar);
            if(AMA_DB::isError($position_id)) {
                return $position_id;
            }

            if ($position_id == -1) {
                // if position not found
                $res = $this->_add_position($position_ar);
                if(AMA_DB::isError($position_ar)) {
                    return new AMA_Error($res);
                }
                else {
                    $id = $this->_get_id_position($position_ar);
                }
            }
            else {
                $id = $position_id;
            }

            $update_node_position_sql = 'id_posizione='.$id.',';
        }
        else {
            $update_node_position_sql = '';
        }

        $sql = "select id_nodo from nodo where id_nodo = $id_node";
        ADALogger::log_db("Query: $sql");
        $id =  $db->getOne($sql);
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("Query result: $id");
        if (empty($id)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_NOT_FOUND) . " while in _edit_node.");
        }
        // edit a row into table nodo
        $sql  = "update nodo set $update_node_position_sql nome = $name, titolo = $title, ordine = $order , testo = $text,  livello = $level, versione = $version, correttezza = $correctness, id_nodo_parent = $parent_id, icona=$icon";
        //if (isset($type)) {
        $sql  .= ", tipo = $type";  // promoting notes
        //}

        if (isset($id_instance)) {
            $sql  .= ", id_istanza = $id_instance";     // promoting nodes
        }

		// @author giorgio 26/apr/2013
		// force data_creazione to now if appropriate form checkbox is checked
        if (isset($node_ha['forcecreationupdate']) && intval($node_ha['forcecreationupdate'])===1 )
        {
        	$sql .= ', data_creazione='.$this->date_to_ts('now');
        }

        $sql  .= " where id_nodo = $id_node";

        ADALogger::log_db("trying updating the node: $sql");

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            ADALogger::log_db("error while updating node id $id_node result:". $res->getMessage());
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _edit_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }
        else {
            ADALogger::log_db("updating node id $id_node successful;");
        }
        /*
     * Check if parent node type is ADA_GROUP_TYPE. if not change it.
        */

        /*
     * vito, 28 nov 2008
     * The root node of a course has parent_id == 'NULL' (string)
     * So, if current node isn't a root node, makes sense to check
     * for its parent node attributes.
        */

        if ($node_ha['parent_id'] != NULL && $node_ha['parent_id'] !== 'NULL') {
            $parent_node_ha = $this->get_node_info($node_ha['parent_id']);
            if ( AMA_DB::isError($parent_node_ha)) {
                return $parent_node_ha;
            }

            if ( $parent_node_ha['type'] == ADA_LEAF_TYPE ) {
                $result = $this->change_node_type($node_ha['parent_id'], ADA_GROUP_TYPE);
                if ( AMA_DB::isError($result)) {
                    return $result;
                }
            } elseif ( $parent_node_ha['type'] == ADA_LEAF_WORD_TYPE ) {
                $result = $this->change_node_type($node_ha['parent_id'], ADA_GROUP_WORD_TYPE);
                if ( AMA_DB::isError($result)) {
                    return $result;
                }
            }
        }
        // update row to table "extended_node"
        if ($node_ha['type'] == ADA_LEAF_WORD_TYPE OR $node_ha['type'] == ADA_GROUP_WORD_TYPE) {
            $res = $this->_edit_extension_node($node_ha);
            if (AMA_DB::isError($res)) {
                $err = $this->errorMessage(AMA_ERR_ADD). "while in _edit_node($node_id)".
                        AMA_SEP. $res->getMessage();
                        ADALogger::log_db($err);
                return new AMA_Error($err);
            }
            else {
                // add instruction to rollback segment
                ADALogger::log_db("extended_node update to db");
            }

        }
        return true;
    }

   /**
     * edit the node extension (only for ADA_WORD_LEAF_TYPE and ADA_WORD_GROUP_TYPE)
     * only update the node extension.
     * This function is called from the public _edit_node function.
     *
     * @access private
     *
     * @param $node_ha an associative array containing all the node's data (see add_node public function)
     *
     * @return an AMA_Error object if something goes wrong,
     *         true on success
     *
     * @see _edit_node()
     */
    private function _edit_extension_node($node_ha) {
        ADALogger::log_db("entered _add_extension_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // FIXME: l'id del nodo dovrebbe venire ottenuto qui e non passato nell'array $node_ha
        $id_node = $this->sql_prepared($node_ha['id']);
        $hyphenation = $this->sql_prepared($node_ha['hyphenation']);
        $grammar = $this->sql_prepared($node_ha['grammar']);
        $semantic = $this->sql_prepared($node_ha['semantic']);
        $notes = $this->sql_prepared($node_ha['notes']);
        $examples = $this->sql_prepared($node_ha['examples']);
        $language = $this->sql_prepared($node_ha['lingua']);

        $sql  = "update extended_node set hyphenation = $hyphenation, grammar = $grammar, semantic = $semantic, notes=$notes, examples=$examples, language=$language";
        $sql  .= " where id_node = $id_node";
        ADALogger::log_db("trying updating the extended_node: $sql");
        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
//            var_dump($res);
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _edit_extension_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }

        return true;
    }


    /**
     * Remove a node.
     * Only remove a row from table "nodo". Leaves out position, author and the rest.
     * All aspects of referential integrity is handled in the public method remove_node().
     *
     * @access private
     *
     * @param $id the id of the node to be removed
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     * @see remove_node()
     */
    // FIXME: forse deve essere pubblico
    private function _remove_node($sqlnode_id) {
        ADALogger::log_db("entered _remove_node (id:$sqlnode_id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from nodo where id_nodo=$sqlnode_id";
        ADALogger::log_db("trying query: $sql");

        $res = $this->executeCritical( $sql );
        if(AMA_DB::isError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Add a node
     * Add a node, its position, author and everything into the DB.
     * Transactions are handled (with care).
     *
     * @access public
     *
     * @param $node_ha an associative array containing all the node's data
     *                 the structure is as follows:
     * id                - the unique id of the node.
     *                     made out of the unique id inside promenade concatenated to the course_id
     * id_node_author    - the id of the user who created the node
     * pos_x0            - starting x coordinate in the map
     * pos_y0            - starting y coordinate in the map
     * pos_x1            - final x coordinate in the map
     * pos_y1            - final y coordinate in the map
     * name              - the name of the node (what's a name for a node, anyway?)
     * title             - the title of the node
     * text              - the content of the node, i.e. the real stuff
     * type              - the type of node
     *                       0 - Simple node,
     *                       1 - Group of nodes,
     *                       2 - Note,
     *                       3 - Multiple Answer,
     *                       4 - Free Answer,
     *                       5 - Single answer with check,
     *                       6 - Multiple choice (or closing answers)
     *                      99 - History Separators
     * creation_date     - the date of creation of the node (the format is specified in ADA_DATE_FORMAT)
     * parent_id         - the id of this node's parent (same format as the main id)
     * order             - the order relative to the group
     * level             - the level at which the node is visible in the course (0 - 3)
     * version           - version of the node (not used yet)
     * contacts          - number of contacts that the node has received
     * icon              - name of the graphical file containing the icon of the node
     *                     the path is built out of the modello_corso.media_path db field or
     *                     from applicazion configuration parameters
     * bgcolor           - the background color of this node
     * color             - the color of the caption (uh?)
     * correctness       - if the node is of type 3 or 4 (answers), give the correctness
     *                     (0-10 or 0-100 or 0-whateverYouLike) of the answer, else it must be NULL
     * copyright         - boolean (0, 1) if a copyright is held by the author on this node (useful for node modification)
     *
     * links_ar          - array of links associated to the node
     *                     each element will be an associative array link_ha of this form:
     *                     id_author             - unique id of the author of the link (student or author)
     *                     id_to_node            - the node the link points to
     *                     array(x0, y0, x1, y1) - coordinates
     *                     type                  - type of the link (don't know)
     *                     creation_date         - creation date (format is ADA_DATE_FORMAT)
     *                     style                 - graphical style in the map
     *                                             (0 - Continue, 1 - Dotted line, 2 - Small dots, ...)
     *                     meaning               - ???
     *                     action                                 - what to do on click
     *                                             (0 - Jump, 1 - popup, 2 - open application)
     *
     * resources_ar      - array of external resources associated to this node
     *                     each element will be an associative array resource_ha of this form:
     *                     file_name - the file name (unique)
     *                     type      - the type of resources (picture, audio, video, ...)
     *                     copyright - if a copyright exists on the resource
     *
     * actions_ar        - array of actions associated to this node
     *                     each element will be an associative array acition_ha of this form:
     *                       type - the type of action
     *                     text - the text of the action (unique)
     *
     * @param $author_id  - the id of the author of the node (the original author)
     *                     can be a student or an author
     *
     * @return an AMA_Error object if something goes wrong,
     * 		true on success
     *
     */
    public function add_node($node_ha) {
        ADALogger::log_db("entered add_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // data regarding the node's position
        $pos_x0 = ($node_ha['pos_x0']);
        $pos_y0 = ($node_ha['pos_y0']);
        $pos_x1 = ($node_ha['pos_x1']);
        $pos_y1 = ($node_ha['pos_y1']);

        // an array with the four coordinates will be useful
        $pos_ar = array($pos_x0, $pos_y0, $pos_x1, $pos_y1);

        // check that the author is not an administrator
        $user_ha = $this->_get_user_info($node_ha['id_node_author']);
        if (AMA_DB::isError($user_ha)) {
            return $user_ha;
        }
        $type = $user_ha['tipo'];

        ADALogger::log_db("looking for user type ... got $type");

        if ($type == AMA_TYPE_ADMIN) {
            return new AMA_Error(AMA_ERR_WRONG_USER_TYPE);
        }

        // check if the position already exists in the table
        ADALogger::log_db("checking if position ($pos_x0, $pos_y0)($pos_x1, $pos_y1) already exists ... ");
        $id=$this->_get_id_position($pos_ar);
        // FIXME: restituire l'errore?
        //vito, 23 jan 2009
        //if (!is_object($id)) {
        if (!AMA_DB::isError($id) && $id != -1) {
            ADALogger::log_db("it seems it does ($id)");
            $id_position = $id;
        }
        else {
            ADALogger::log_db("it didn't exist, inserting ...");
            // add row to table "posizione"
            $res = $this->_add_position($pos_ar);
            if (AMA_DB::isError($res)) {
                return $res;
            }
            $id_position = $this->_get_id_position($pos_ar);
            if (AMA_DB::isError($id_position)) {
                return $id_position;
            }

            ADALogger::log_db("done ($id_position)");

        }

        $node_ha['id_position'] = $id_position;
        // begin the transaction
        ADALogger::log_db("beginning node insertion transaction");
        $this->_begin_transaction();

        // add row to table "nodo"
        $res = $this->_add_node($node_ha);
        if (AMA_DB::isError($res)) {

            $err = $this->errorMessage(AMA_ERR_ADD). "while in add_node($node_id)".
                    AMA_SEP. $res->getMessage();

            ADALogger::log_db("$err detected");
            return new AMA_Error($err);
        }
        else {
            $node_id = $res;
            $node_ha['id'] = $node_id;
            // add instruction to rollback segment
            ADALogger::log_db("node added to db, adding instruction to rbs");
            $this->_rs_add("_remove_node", $node_id);
        }
        // the sql_prepared form will be of unvaluable help in the future
        $sqlnode_id = $this->sql_prepared($node_id);

        /*
     * if exists a parent node for this node, check if it has type ADA_LEAF_TYPE
     * and change it in ADA_GROUP_TYPE
        */
        if( isset($node_ha['parent_id']) && ($node_ha['parent_id'] != "") ) {
            $parent_node_ha = $this->get_node_info($node_ha['parent_id']);
            // vito, 23 mar 2009.
            //   	 	  if ( AMA_DB::isError($parent_node_ha) )
            //   	 	  {
            //   	 	    return new AMA_Error(AMA_ERR_GET);
            //   	 	  }
            if(!AMA_DB::isError($parent_node_ha)) {
                if ( $parent_node_ha['type'] == ADA_LEAF_TYPE ) {
                    $result = $this->change_node_type($node_ha['parent_id'], ADA_GROUP_TYPE);
                    if ( AMA_DB::isError($parent_node_ha) ) {
                        return $result;
                    }
                } elseif ( $parent_node_ha['type'] == ADA_LEAF_WORD_TYPE ) {
                    $result = $this->change_node_type($node_ha['parent_id'], ADA_GROUP_WORD_TYPE);
                    if ( AMA_DB::isError($parent_node_ha) ) {
                        return $result;
                    }
                }
            }
        }
        ADALogger::log_db("resources added to db, committing node insertion");
        // add rows to table "LINK"
        // checking if the caller really wants to add some
        if (in_array('links_ar',array_keys ($node_ha))) {

            // get the links' infoz
            $link_ar =  $node_ha['links_ar'];

            // add them to the DB
            $res = $this->_add_links($link_ar, $node_id);
            // FIXME: è corretto questo if?
            if (AMA_DB::isError($res)) {
                $err = $this->errorMessage(AMA_ERR_ADD). "while in add_node($node_id)".
                        AMA_SEP. $res->getMessage();

                $this->_rollback();

                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);
            }
            else {
                // add instruction to rollback segment
                ADALogger::log_db("links added to db, adding instruction to rbs");
                $this->_rs_add("_del_links", $sqlnode_id);
            }
        }


        // add row to table "extended_node"
        // checking if the caller really wants to add some
        if ($node_ha['type'] == ADA_LEAF_WORD_TYPE OR $node_ha['type'] == ADA_GROUP_WORD_TYPE) {
            $res = $this->_add_extension_node($node_ha);
            if (AMA_DB::isError($res)) {
                $err = $this->errorMessage(AMA_ERR_ADD). "while in add_node($node_id)".
                        AMA_SEP. $res->getMessage();
                $this->_rollback();
                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);
            }
            else {
                // add instruction to rollback segment
                ADALogger::log_db("links added to db, adding instruction to rbs");
                $this->_rs_add("_del_extended_node", $sqlnode_id);
            }

        }
        // add rows to table "RISORSA_ESTERNA"
        // checking if the caller really wants to add some
        if (in_array('resources_ar',array_keys ($node_ha))) {
            // get the resources' infoz
            $resources_ar = $node_ha['resources_ar'];
            // add them to the DB
            $res = $this->_add_media($resources_ar,$sqlnode_id);
            if (AMA_DataHandler::isError($res)) {
                $err = $this->errorMessage(AMA_ERR_ADD). "while in add_node($node_id)".
                        AMA_SEP. $res->getMessage();
                $this->_rollback();
                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);

            }
            ADALogger::log_db("resources added to db, committing node insertion");
        }


        // everything was ok, so the commit can be issued
        $this->_commit();
        return $node_id;
//        return true;
    }

    /**
     * Set the node position
     *
     * @access public
     * @param $node_ha an associative array containing all the node's data
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
  public function set_node_position($node_ha) {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

    /*
     * vito, 23 jan 2009
     * check if node position was given
     */
        $id_node = $this->sql_prepared($node_ha['id']);
        if (isset($node_ha['pos_x0']) && is_numeric($node_ha['pos_x0']) &&
                isset($node_ha['pos_x1']) && is_numeric($node_ha['pos_x1']) &&
                isset($node_ha['pos_y0']) && is_numeric($node_ha['pos_y0']) &&
                isset($node_ha['pos_y1']) && is_numeric($node_ha['pos_y1'])) {

            $position_ar = array($node_ha['pos_x0'], $node_ha['pos_y0'], $node_ha['pos_x1'], $node_ha['pos_y1']);
            $position_id = $this->_get_id_position($position_ar);
            if(AMA_DB::isError($position_id)) {
                return $position_id;
            }

            if ($position_id == -1) {
                // if position not found
                $res = $this->_add_position($position_ar);
                if(AMA_DB::isError($position_ar)) {
                    return new AMA_Error($res);
                }
                else {
                    $id = $this->_get_id_position($position_ar);
                }
            }
            else {
                $id = $position_id;
            }

            $update_node_position_sql = 'id_posizione='.$id;
        }
        else {
            $update_node_position_sql = '';
        }

        $sql = "select id_nodo from nodo where id_nodo = $id_node";
        ADALogger::log_db("Query: $sql");
        $id =  $db->getOne($sql);
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("Query result: $id");
        if (empty($id)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_NOT_FOUND) . " while in _edit_node.");
        }
        // edit a row into table nodo
        $sql  = "update nodo set ". $update_node_position_sql;
        $sql  .= " where id_nodo = $id_node";
        ADALogger::log_db("trying updating the node position: $sql");

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            ADALogger::log_db("error while updating node position, id $id_node result:". $res->getMessage());
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _edit_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }
        else {
            ADALogger::log_db("updating node position, id $id_node successful;");
        }
        return true;
}

    /**
     *
     * @param $node_id
     * @param $type
     * @return true  on success, false if node type was not changed, an AMA_Error
     *         object on failure
     */
    public function change_node_type($node_id, $type) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "UPDATE nodo SET tipo=$type WHERE id_nodo='$node_id'";

        $result = $this->executeCritical($sql);
        if (AMA_DB::isError($result)) {
            return $result;
        }

        if ($result == 1) {
            return true;
        }
        return false;
    }

    /**
     * Updates the given node text.     *
     *
     * @param string $node_id The id of the node
     * @param string $text The new text for the node
     * @return AMA_Error
     */
    public function set_node_text($node_id, $text) {

        $sql = 'UPDATE nodo SET testo=? WHERE id_nodo=?';
        $values = array(
            $text,
            $node_id
        );
        $result = $this->queryPrepared($sql, $values);
        if (AMA_DataHandler::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
        return true;
    }

    /**
     * Remove a node and all informations related to it (position, links, resources and actions)
     * Also remove all history and bookmarks related to the node.
     * Transactions are not handled since no referential integrity must be checked.
     *
     * Nodes cannot be removed if a reference to them is found in table bookmark. (?)
     * Once a node is removed, all records refering to it in tables risorse_nodi and azioni_nodi
     * must be removed, too.
     *
     * @access public
     *
     * @param $node_id id of the node to be removed
     *
     * @return an error if something goes wrong, true on success
     *
     */
    public function remove_node($node_id) {

        ADALogger::log_db("entered remove_node(node_id:$node_id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // prepare $node_id for sql usage (it's a string)
        $sqlnode_id =  $this->sql_prepared($node_id);

        /*
     * remove resources
        */
        $risorse_ar = $db->getCol("select id_risorsa_ext from risorse_nodi where id_nodo=$sqlnode_id");
        if (AMA_DB::isError($risorse_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("removing ".count($risorse_ar)." resources");
        if (count($risorse_ar)) {
            // delete all references to $node_id in risorse_nodi
            $res_risorse = $this->_del_media($risorse_ar, $sqlnode_id);
            if (AMA_DataHandler::isError($res_risorse)) {

                $err = $this->errorMessage(AMA_ERR_REMOVE). "while in remove_node($node_id)".
                        AMA_SEP.  $res_risorse->getMessage();

                ADALogger::log_db("error: $err");
                return new AMA_Error($err);
            }
            ADALogger::log_db("resources successfully removed");
        }

        /*
     * remove links
        */
        $links_ar = $db->getAll("select * from link where id_nodo=$sqlnode_id");
        if (AMA_DB::isError($links_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        ADALogger::log_db("removing ".count($links_ar)." links");
        if (count($links_ar)) {
            // delete all references to $node_id in link
            $res_links = $this->_del_links($sqlnode_id);
            if (AMA_DataHandler::isError($res_links)) {
                $err = $this->errorMessage(AMA_ERR_REMOVE).  "while in remove_node($node_id)".
                        AMA_SEP. $res_links->getMessage();

                ADALogger::log_db("error: $err");
                return new AMA_Error($err);
            }
            ADALogger::log_db("links successfully removed");
        }

        /*
         * history cleaning
         */
        $sql = "delete from history_nodi where id_nodo=$sqlnode_id";
        ADALogger::log_db("cleaning history_nodi: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            ADALogger::log_db($res->message." detected, aborting");
            return new AMA_Error(AMA_ERR_REMOVE);
        }

        /*
         *  exercises history cleaning
         */
        $sql = "delete from history_esercizi where id_nodo=$sqlnode_id";
        ADALogger::log_db("cleaning history_esercizi: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)){
            ADALogger::log_db($res->message." detected, aborting");
            return $res;
        }

        /*
         * bookmarks cleaning
         */
        $sql = "delete from bookmark where id_nodo=$sqlnode_id";
        ADALogger::log_db("cleaning bookmark: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            ADALogger::log_db($res->message." detected, aborting");
            return new AMA_Error(AMA_ERR_REMOVE);
        }
//        ADALogger::log_db("cleaning successfully terminated");

        /*
         * extension node cleaning
         */
        $sql = "delete from extended_node where id_node=$sqlnode_id";
        ADALogger::log_db("cleaning extended node: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            ADALogger::log_db($res->message." detected, aborting");
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        ADALogger::log_db("cleaning successfully terminated");

        /*
         * node removal
         */
        $res_node = $this->_remove_node($sqlnode_id);
        if (AMA_DataHandler::isError($res_node)) {
            $err = $this->errorMessage(AMA_ERR_REMOVE). "while in remove_node($node_id)".
                    AMA_SEP. $res_node->getMessage();

            ADALogger::log_db("error: $err");
            return new AMA_Error($err);
        }
        ADALogger::log_db("node $sqlnode_id successfully removed");

        return true;
    }

    /**
     * Remove a node and all its children recursively
     * The method calls recursively the remove_node method
     *
     * @access public
     *
     * @param $node_id id of the node to be removed
     *
     * @return an error if something goes wrong
     *
     */
    public function recursive_remove_node($node_id) {
        ADALogger::log_db("entered remove_node(node_id:$node_id)");

        // prepare $node_id for sql usage (it's a string)
        $sqlnode_id =  $this->sql_prepared($node_id);

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // retrieve children's ids
        $ids_ar = $db->getCol("select id_nodo from nodo where id_nodo_parent=$sqlnode_id");
        if (AMA_DB::isError($ids_ar)) {
            ADALogger::log_db($ids_ar->message." detected, aborting recursive removal");
            return new AMA_Error(AMA_ERR_GET);
        }

        // children removal loop
        foreach($ids_ar as $id) {
            $res = $this->remove_node($node_id);
            if (AMA_DataHandler::isError($res)) {
                ADALogger::log_db($res->message." detected, aborting recursive removal");
                return $res;
            }
        }

        return true;
    }


	/**
	 * function get_nodes:
	 * used to obtain nodes data.
	 *
	 * @param array $ids_nodes array that contains ids of desidered notes
	 * @return array
	 */
	function get_nodes($ids_nodes)
	{
		$db =& $this->getConnection();
		if ( AMA_DB::isError( $db ) ) return $db;

		if (is_array($ids_nodes)) {
			$condition = " IN ('".implode("','",$ids_nodes)."')";
		}
		else {
			$condition = " = ".$ids_nodes.")";
		}

		$sql = "SELECT *
				FROM nodo
				WHERE `id_nodo`".$condition;

		$tmp = $db->getAll($sql, null, AMA_FETCH_ASSOC);
		if (AMA_DataHandler::isError($tmp)) {
			return $tmp;
		}
		else {
			$result = array();
			foreach($tmp as $k=>$v) {
				$result[$v['id_nodo']] = $v;
			}
			return $result;
		}
    }

    /**
     * Get all the informations related to a node, except for links, resources and actions
     *
     * @access public
     *
     * @param $node_id id of the node
     *
     * @return an hash with all information about the node
     *         the keys to access the informations are:
     * author             - the author (hash: see get_author)
     * position           - the position (array: (x0, y0, x1, y1))
     * name               - the name of the node (what's a name for a node, anyway?)
     * title              - the title of the node
     * text               - the content of the node, i.e. the real stuff
     * type               - the type of node
     *                      (0 - Page, 1 - Group, 2 - Note, 3 - Multiple Answer,
     *                       4 - Free Answer, 5 - History separators (?))
     * creation_date      - the date of creation of the node (the format is specified in ADA_DATE_FORMAT)
     * parent_id          - the id of this node's parent (same format as the main id)
     * order              - the order relative to the group
     * level              - the level at which the node is visible in the course (0 - 3)
     * version            - version of the node (not used yet)
     * contacts           - number of contacts that the node has received
     * icon               - name of the graphical file containing the icon of the node
     *                      the path is built out of the modello_corso.media_path db field or
     *                      from applicazion configuration parameters
     * bgcolor            - background color of this node
     * color              - the color of the caption (uh?)
     * correctness        - if the node is of type 3 or 4 (answers), give the correctness
     *                      (0-10 or 0-100 or 0-whateverYouLike) of the answer, else it must be NULL
     * copyright          - boolean (0, 1) if a copyright is held by the author on this node
     *                      (useful in the future for node modification)
     *
     *         values related to links, resources and actions are not returned
     *         you have to call the proper functions
     *         Author is returned as a hash (see get_author).
     *         Position is returned as a four elements array (see _get_position)
     *
     * @see get_author
     * @see _get_position
     *
     */

    public function get_node_info($node_id) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table nodo
        $sql  = "select id_utente, id_posizione, nome, titolo, testo, tipo, ";
        $sql .= "data_creazione, id_nodo_parent, ordine, ";
        $sql .= "livello, versione, n_contatti, icona, colore_didascalia, colore_sfondo,";
        $sql .= "correttezza, copyright, id_istanza, lingua, pubblicato";
        $sql .= " from nodo where id_nodo='$node_id'";
        $res_ar =  $db->getRow($sql);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        //if ((!$res_ar) OR (is_Object($res_ar))){
        if(!$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        // author is a hash

        $author_id = $res_ar[0];
        $author_ha =$this->_get_user_info($author_id);
        if(AMA_DB::isError($author_ha)) {
            // shall we stop action if author is not ok?
            //  return $author_ha;
            $author_ha = "";
        } else {

            if ($author_ha['tipo']==AMA_TYPE_AUTHOR) {
                $author_ha = $this->get_author($author_id);  // more info on author than on students
                if(AMA_DB::isError($author_ha)) {
                    // shall we stop action if author is not ok?
                    // return $author_ha;
                    $author_ha = "";
                }
            }
        }// author
        // position is a four elements array
        $pos_id = $res_ar[1];
        $pos_ar = $this->_get_position($pos_id);

        if (AMA_DB::isError($pos_ar)) {
            // shall we stop action if position is not ok?
            //return $pos_ar;
            $pos_ar = "";
        }

        $res_ha['author']      = $author_ha;
        $res_ha['position']    = $pos_ar;
        $res_ha['name']        = $res_ar[2];
        $res_ha['title']       = $res_ar[3];
        $res_ha['text']        = $res_ar[4];
        $res_ha['type']        = $res_ar[5];
        $res_ha['creation_date']    = self::ts_to_date($res_ar[6]);
        $res_ha['parent_id']   = $res_ar[7];
        $res_ha['ordine']      = $res_ar[8];
        $res_ha['order']       = $res_ar[8];
        $res_ha['level']       = $res_ar[9];
        $res_ha['version']     = $res_ar[10];
        $res_ha['contacts']    = $res_ar[11];
        $res_ha['icon']        = $res_ar[12];
        $res_ha['color']       = $res_ar[13];
        $res_ha['bgcolor']     = $res_ar[14];
        $res_ha['correctness'] = $res_ar[15];
        $res_ha['copyright']   = $res_ar[16];
        $res_ha['instance']    = $res_ar[17];
        $res_ha['language']    = $res_ar[18];
        $res_ha['published']   = $res_ar[19];


        return $res_ha;
    }

    /**
     * Get nodes where a keyword is in one of the fields specified
     *
     * @access public
     *
     * @param  $out_fields_ar an array containing the desired fields' names
     *         possible values are: nome, titolo, testo
     *
     * @param  $key the keyword or sentence to look for (a string)
     *
     * @param  $search_fields_ar array of fields where the key must be looked for
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see _find_nodes_list
     */
    public function &find_nodes_list_by_key($out_fields_ar, $key, $search_fields_ar) {
        $clause = '';
        $n = count($search_fields_ar);
        for ($i=0; $i<$n; $i++) {
            if ($i<$n-1) {
                $or = " OR ";
            }
            else {
                $or = "";
            }
            $clause .= $search_fields_ar[$i] . " LIKE '%" . $key . "%' " . $or;
        }

        return $this->_find_nodes_list($out_fields_ar, $clause);
    }

    /**
     * Get nodes informations which satisfy a given clause
     * Only the fields specifiedin the $out_fields_ar parameter are inserted
     * in the result set.
     * This function is meant to be used by the public find_nodes_list_by_key()
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     * @return a bi-dimensional array containing these fields
     *
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     */
    public function &_find_nodes_list($out_fields_ar, $clause='') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        }

        // add a 'where' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }
        // do the query
        $res_ar =  $db->getAll("select id_nodo$more_fields from nodo $clause");
        if (AMA_DB::isError($res_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get nodes informations in a course which satisfy a given clause
     * Only the fields specifiedin the $out_fields_ar parameter are inserted
     * in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     * @param $course_id
     *
     * @return a bi-dimensional array containing these fields
     *
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     */
    public function &find_course_nodes_list($out_fields_ar, $clause='',$course_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        } else $more_fields = '';

        // add an 'and' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'and '.$clause;
        }
        // vito, 16 giugno 2009
        $course_id.="\_"; // $course_id.="\_"; ?

        // do the query
        $sqlquery = "select id_nodo$more_fields from nodo where id_nodo LIKE '$course_id%' $clause";
        $res_ar =  $db->getAll($sqlquery);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get the children of a given node.
     *
     * @access public
     *
     * @param $node_id the id of the father
     *
     * @return an associative array of ids containing all the id's of the children of a given node
     *
     * @see get_node_info
     *
     */
    public function &get_node_children_info($node_id,$id_course_instance="",$order="ordine") {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($id_course_instance!="") {
            $sql  = "select id_nodo,ordine,nome,tipo,livello from nodo where id_nodo_parent='$node_id' AND id_istanza='$id_course_instance' ORDER BY $order ASC";
        }
        else {
            $sql  = "select id_nodo,ordine,nome,tipo,livello from nodo where id_nodo_parent='$node_id' ORDER BY $order ASC";
        }
        $res_ar = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        //$res_ar =  $db->getCol($sql);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        // return an error in case of an empty recordset
        if (!$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get the children of a given node.
     *
     * @access public
     *
     * @param $node_id the id of the father
     *
     * @return an array of ids containing all the id's of the children of a given node
     *
     * @see get_node_info
     *
     */
    public function &get_node_children($node_id,$id_course_instance="") {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($id_course_instance!="") {
            $sql  = "select id_nodo,ordine from nodo where id_nodo_parent='$node_id' AND id_istanza='$id_course_instance' ORDER BY ordine ASC";
        }
        else {
            $sql  = "select id_nodo,ordine from nodo where id_nodo_parent='$node_id' ORDER BY ordine ASC";
        }
        $res_ar =  $db->getCol($sql);
        if (AMA_DB::isError($res_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }
        // return an error in case of an empty recordset
        if (!$res_ar) {
        	$retval = new AMA_Error(AMA_ERR_NOT_FOUND);
            return $retval;
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get the childrens of a given node.
     *
     * @access public
     *
     * @param $node_id the id of the father
     *
     * @return an array of all data containing of the children of a given node
     *
     * @see get_node_info
     *
     */
    public function &get_node_children_complete($node_id,$id_course_instance="") {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($id_course_instance!="") {
            $sql  = "select * from nodo where id_nodo_parent='$node_id' AND id_istanza='$id_course_instance' ORDER BY ordine ASC";
        }
        else {
            $sql  = "select * from nodo where id_nodo_parent='$node_id' ORDER BY ordine ASC";
        }
        $res_ar =  $db->getALL($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }
        // return an error in case of an empty recordset
        if (!$res_ar) {
        	$retval = new AMA_Error(AMA_ERR_NOT_FOUND);
            return $retval;
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get all external links associated to the given node.
     * A list of ids is returned in an array. Each id can be used as a
     * parameter for the function get_link_info, to retrieve all
     * about the link.
     *
     * @access public
     *
     * @param $node_id the node
     *
     * @return an array of link ids
     *
     * @see get_link_info
     *
     */
    public  function &get_node_links($node_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // do the select
        $sql  = "select id_link from link where id_nodo='$node_id'";
        $res_ar =  $db->getCol($sql);
        if (AMA_DB::isError($res_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }

        // return an error in case of an empty recordset
        if (!$res_ar) {
        	$retval = new AMA_Error(AMA_ERR_NOT_FOUND);
            return $retval;
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get all external resources associated to the given node
     *
     * @access public
     *
     * @param $nod_id the node
     *
     * @param $mediatype the type of media to return.
     *                   The default empty value meaning all types are returned.
     *
     * @return an array of hashes, similar to those used in add_node
     *
     * @see add_node
     *
     */
    public function &get_node_resources($node_id, $mediatype="") {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // do the select
        $sql  = "select id_risorsa_ext from risorse_nodi where id_nodo='$node_id'";
        $res_ar =  $db->getCol($sql);
        if (AMA_DB::isError($res_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }

        // return an error in case of an empty recordset
        if (!$res_ar) {
        	$retval = new AMA_Error(AMA_ERR_NOT_FOUND);
            return $retval;
        }
        // return nested array
        return $res_ar;
    }

    /**
     * Get all actions associated with the given node
     *
     *
     * @access public
     *
     * @param $nod_id the node
     *
     * @return an array of hashes, similar to those used in add_node
     *
     * @see add_node
     *
     */
    public function &get_node_actions($node_id) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // do the select
        $sql  = "select id_azione from azioni_nodi where id_nodo=$node_id";
        $res_ar =  $db->getCol($sql);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        // return an error in case of an empty recordset
        if (!res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        return $res_ar;
    }

    /**
     *
     * Get all node and group of a course (doesn't return node type NOTE and WORD)
     *
     * @param $id_course
     * @param $required_info
     * @param $order_by_name
     * @param $id_course_instance
     * @param $id_student
     * @return unknown_type
     */
    public function get_course_data($id_course, $required_info=null, $order_by_name=false, $id_course_instance=null, $id_student=null, $user_level = null) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($order_by_name) {
            $ORDER = "N.nome ASC";
        }
        else {
            $ORDER = "N.id_nodo_parent, N.ordine ASC";
        }

        switch ($required_info) {
            case 1: // get NODE n_contatti field
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.n_contatti, N.icona FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
              		 WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .")
                       AND N2.tipo in (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .") ORDER BY " . $ORDER;
                break;

            case 2:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .")
                       AND N2.tipo in (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .") ORDER BY " . $ORDER;

                break;

            case 3:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.livello, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance AND id_utente_studente=$id_student
          GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .")
                       AND N2.tipo in (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .")";
//                        AND N.livello <= $user_level
           $sql.="ORDER BY " . $ORDER;

                break;

            case null:
            default:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.icona FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
                      WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .")
                        AND N2.tipo in (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .") ORDER BY " . $ORDER;
                break;
        }
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     *
     * Get all node and group of a course (doesn't return node type NOTE and WORD)
     *
     * @param $id_course
     * @param $required_info
     * @param $order_by_name
     * @param $id_course_instance
     * @param $id_student
     * @return unknown_type
     */
    public function get_glossary_data($id_course, $required_info=null, $order_by_name=false, $id_course_instance=null, $id_student=null) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($order_by_name) {
            $ORDER = "N.nome ASC";
        }
        else {
            $ORDER = "N.id_nodo_parent, N.nome ASC, N.ordine ASC";
        }

        switch ($required_info) {
            case 1: // get NODE n_contatti field
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.n_contatti, N.icona FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
              		 WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .")
                       AND N.tipo in (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .") ORDER BY " . $ORDER;
                break;

            case 2:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .")
                       AND N.tipo in (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .") ORDER BY " . $ORDER;

                break;

            case 3:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.livello, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance AND id_utente_studente=$id_student
          GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .")
                       AND N.tipo in (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .") ORDER BY " . $ORDER;

                break;

            case null:
            default:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.icona FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
                      WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo NOT IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") AND N.tipo NOT IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .")
                        AND N.tipo in (". ADA_LEAF_WORD_TYPE .",". ADA_GROUP_WORD_TYPE .") ORDER BY " . $ORDER;
                break;
        }
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }


    /**
     *
     * @param $id_course
     * @param $id_course_instance
     * @param $required_info
     * @param $order_by_name
     * @param $id_student
     * @return unknown_type
     */
    function get_forum_data($id_course, $id_course_instance, $required_info=null, $order_by_name=false, $id_student=null) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if ($order_by_name) {
            $ORDER = "N.nome ASC";
        }
        else {
            $ORDER = "N.id_nodo_parent ASC";
        }

        switch ($required_info) {
            case 3:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.livello, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance AND id_utente_studente=$id_student
          GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo  IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .")
                       AND N2.tipo in (".ADA_LEAF_TYPE .",". ADA_GROUP_TYPE.",". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") ORDER BY " . $ORDER;

                break;

            case null:
            case 2:
            default:
                $sql = " SELECT N.id_nodo, N.nome, N.tipo, N.id_nodo_parent, N.icona, visite.numero_visite FROM nodo AS N LEFT JOIN nodo AS N2 ON (N.id_nodo_parent = N2.id_nodo)
          LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi
          WHERE id_istanza_corso=$id_course_instance GROUP BY id_nodo) AS visite ON (N.id_nodo=visite.id_nodo)
          WHERE N.id_nodo LIKE '".$id_course."\_%' AND N2.id_nodo LIKE '".$id_course."\_%'
                       AND N.tipo  IN (". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .")
                       AND N2.tipo in (".ADA_LEAF_TYPE .",". ADA_GROUP_TYPE.",". ADA_NOTE_TYPE .",". ADA_PRIVATE_NOTE_TYPE .") ORDER BY " . $ORDER;

                break;
        }
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * Methods accessing table `posizione`
     */
    // MARK: Methods accessing table `posizione`

    /**
     * Add a position to table posizione
     *
     * @access private
     *
     * @param pos_ar - the four elements array containing the x0, y0, x1, y1 coordinates
     *
     * @return true on success, an AMA_Error object on failure
     */
    protected function _add_position($pos_ar) {

        ADALogger::log_db("entered _add_position (".serialize($pos_ar).")");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // extract coordinates from array
        list($x0, $y0, $x1, $y1) = $pos_ar;

        $id =  $db->getOne("select id_posizione from posizione where x0 = $x0 AND y0=$y0 AND x1=$x1 AND y1=$y1");
        if ($id) {
            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
        }

        // add a row into table posizione
        $sql =  "insert into posizione (x0, y0, x1, y1)";
        $sql .= " values ($x0, $y0, $x1, $y1);";
        ADALogger::log_db("inserting with query: $sql");
        $res = $db->query($sql);
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD).
                            " while in _add_position");
        }
        return true;
    }

    /**
     * Remove a position to table posizione [private]
     * A position is removed only if no node is still using it
     *
     * @access private
     *
     * @param id - the id of the position to remove
     *
     * @return true on success, ana AMA_Error object on failure
     */
    private function _remove_position($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // check referential integrity with table nodo
        $ri_id = $db->getOne("select id_nodo from nodo where id_posizione=$id");
        if ($ri_id) {
            return new AMA_Error($this->errorMessage(AMA_ERR_REF_INT_KEY) .
                            " while in _remove_position($id)");
        }
        $sql = "delete from posizione where id_posizione=$id";

        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Get a position's id from the coordinates array
     *
     * @access protected
     *
     * @param pos_ar - the four elements array containing the x0, y0, x1, y1 coordinates
     *
     * @return the id if it's found existsing, -1 otherwise, ana AMA_Error object on failure
     *
     *	@author giorgio 16/lug/2013
     *  modified access to proteced (was private) because this method is needed in the
     *  import/export course module own datahandler
     */
    protected function _get_id_position($pos_ar) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // simple names for the coordinates
        list ($x0, $y0, $x1, $y1) = $pos_ar;

        // look for the position
        $id =  $db->getOne("select id_posizione from posizione where x0=$x0 and y0=$y0 and x1=$x1 and y1=$y1");
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if ($id) {
            return $id;
        }
        else {
            return -1;
        }
    }

    /**
     * Get a position array out of table posizione
     * the array (x0, y0, x1, y1) corresponding to the given $id is returned
     *
     * @ access private
     *
     * @param $id id of the position to extract
     *
     * @return an array of four elements on success, ana AMA_Error object on failure
     *
     */
    protected function _get_position($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $result =  $db->getRow("select x0, y0, x1, y1 from posizione where id_posizione=$id");
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * Methods accessing table `risorsa_esterna`, `risorse_nodi`
     */
    // MARK: Methods accessing table `risorsa_esterna`, `risorse_nodi`

    /**
     * Add a record to table risorsa_esterna
     *
     * @access public
     *
     * @param $res_ha hash containing the information to be added
     * nome_file  - name of the file (path is specified as a config param or using corso.media_path)
     type of the external resource
     *              0 -  Image (jpeg, png)
     *              1 -  Sound (wav, mp3, midi, au, ra)
     *              2 -  Video (real, quicktime, avi, mpeg)
     *              3 -  Doc (Excel, Word, Rtf, txt, pdf)
     * 				4 -  link esterno (URL)
     *
     *
     *
     * copyright  - if the resource has a copytight or not (boolean)
     *
     * @param bool forceDuplicate true to force duplicate filename insertion. defaults to false
     * @return
     *  - the id of the resource just inserted on success
     *  - an Error on failure
     */
    public function add_risorsa_esterna($res_ha, $forceDuplicate = false) {

        ADALogger::log_db("entered add_risorsa_esterna");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $nome_file = $this->sql_prepared($res_ha['nome_file']);
        $tipo = $res_ha['tipo'];
        $copyright = $this->or_zero($res_ha['copyright']);
        $id_nodo = $this->sql_prepared(isset($res_ha['id_nodo']) ? $res_ha['id_nodo'] : null);
        $keywords = $this->sql_prepared($res_ha['keywords']);
        $titolo = $this->sql_prepared($res_ha['titolo']);
        $pubblicato = $this->or_zero($res_ha['pubblicato']);
        $descrizione = $this->sql_prepared($res_ha['descrizione']);
        $lingua = $this->sql_prepared($res_ha['lingua']);

        // vito, 19 luglio 2008
        $id_utente = $this->or_zero($res_ha['id_utente']);

        ADALogger::log_db("nome: $nome_file");
        ADALogger::log_db("tipo: $tipo");
        ADALogger::log_db("copyright: $copyright");
        ADALogger::log_db("id_utente: $id_utente");

        // check values
        if (empty($nome_file)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_risorsa_esterna " .
                            AMA_SEP .  ": empty file name");
        }

        if ($tipo<0 || $tipo>POSSIBLE_TYPE) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_risorsa_esterna " .
                            AMA_SEP . ": undefined type");
        }

        // gets the ids of all the resources having the same names
        // as the one that has to be inserted before the insertion
        $sql = "select id_risorsa_ext from risorsa_esterna where nome_file=$nome_file";
        ADALogger::log_db("getting resources: $sql");
        $id = $db->getOne($sql);
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($id) || $forceDuplicate) {
            // insert a row into table risorsa_esterna
            $sql  = "insert into risorsa_esterna (nome_file, tipo, copyright,id_utente, keywords, titolo, descrizione, pubblicato, lingua)";
            $sql .= " values ($nome_file, $tipo, $copyright, $id_utente, $keywords, $titolo, $descrizione, $pubblicato, $lingua)";
            ADALogger::log_db("inserting: $sql");

            $res = $this->executeCritical($sql);
            if (AMA_DB::isError($res)) {
                return $res;
            }

            // preleva l'id della risorsa appena inserita
            $sql = "select id_risorsa_ext from risorsa_esterna where nome_file=$nome_file";
            if ($forceDuplicate) $sql .= ' ORDER BY id_risorsa_ext DESC';
            ADALogger::log_db("getting resources: $sql");
            $id = $db->getOne($sql);
            if (AMA_DB::isError($id)) {
                return new AMA_Error(AMA_ERR_GET);
            }
            // crea relazione tra il nodo e la risorsa esterna
            $res1=$this->_add_risorse_nodi($id_nodo,$id);
            if (AMA_DB::isError($res1)) {
                return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .  " while in risorse_nodi" .
                                AMA_SEP . ": " . $res->getMessage());
            }
        }
        else {
            // return minus id if the resource was already there (a dirty trick!)
            $id = -1*$id;
        }

        ADALogger::log_db("returning: ".$id);
        return $id;
    }

    /**
     *
     * @param $res_ha
     * @return unknown_type
     */
    public function add_only_in_risorsa_esterna($res_ha) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $nome_file = $this->sql_prepared(isset($res_ha['nome_file']) ? $res_ha['nome_file'] : '');
        $titolo = $this->sql_prepared(isset($res_ha['titolo']) ? $res_ha['titolo'] : '');
        $tipo      = isset($res_ha['tipo']) ? $res_ha['tipo'] : null;
        $copyright = $this->or_zero(isset($res_ha['copyright']) ? $res_ha['copyright'] : '');
        $id_utente = $this->or_zero(isset($res_ha['id_utente']) ? $res_ha['id_utente'] : '');
        $keywords = $this->sql_prepared(isset($res_ha['keywords']) ? $res_ha['keywords'] : '');
        $pubblicato = $this->or_zero(isset($res_ha['pubblicato']) ? $res_ha['pubblicato'] : '');
        $descrizione = $this->sql_prepared(isset($res_ha['descrizione']) ? $res_ha['descrizione'] : '');
        $lingua = $this->sql_prepared(isset($res_ha['lingua']) ? $res_ha['lingua'] : '');

        // check values
        if (empty($nome_file)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .  " in add_only_in_risorsa_esterna " .
                            AMA_SEP . ": empty file name");
        }

        if ($tipo<0 || $tipo>POSSIBLE_TYPE) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " in add_only_in_risorsa_esterna " .
                            AMA_SEP . ": undefined type");
        }

        // gets the ids of all the resources having the same names and the same owner
        // as the one that has to be inserted before the insertion
        $sql = "select id_risorsa_ext from risorsa_esterna where nome_file=$nome_file and id_utente = $id_utente";
        $id = $db->getOne($sql);
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (empty($id)) {
            // insert a row into table risorsa_esterna
            $sql  = "insert into risorsa_esterna (nome_file, tipo, copyright, id_utente, keywords, titolo, descrizione, pubblicato, lingua)";
            $sql .= " values ($nome_file, $tipo, $copyright, $id_utente, $keywords, $titolo, $descrizione, $pubblicato, $lingua)";

            $res = $this->executeCritical($sql);
            if (AMA_DB::isError($res)) {
                return $res;
            }
        }
        else {
            $id *= -1;
        }
        return $id;
    }

    /**
     * Remove a record from risorsa_esterna
     * referential integrity is checked against risorse_nodi
     * this must have no records related to the external resource to remove
     *
     * @access public
     *
     * @param res_id the id of the external resource to be removed
     */

    public function remove_risorsa_esterna($res_id) {

        ADALogger::log_db("entering remove_risorsa_esterna (res_id:$res_id)");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $ri_id = $db->getOne("select id_nodo from risorse_nodi where id_risorsa_ext=$res_id");
        if(AMA_DB::isError($ri_id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        ADALogger::log_db("got: ".count($ri_id). " records in risorse_nodi still referring to resource $res_id");
        if (empty($ri_id)) {
            $sql = "delete from risorsa_esterna where id_risorsa_ext=$res_id";
            ADALogger::log_db("deleting record: $sql");
            $res = $db->query($sql);
            if(AMA_DB::isError($res)) {
                return new AMA_Error(AMA_ERR_REMOVE);
            }
            return $res;
        }
        // if there was at least one reference to $res_id into risorse_nodi
        // return without doing anything
        return 0;
    }
    /**
     * Get external resource info starting from the file name and the Id_node
     *
     *
     * @param $file_name - file name of the resource
     * @param $id_node - the id of the current node
     *
     * @return the array containes the onfo about the resource or null or an error value
     *
     */
    public function get_risorsa_esterna_info_from_filename($filename, $id_node) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlfilename = $this->sql_prepared($filename);
        $sql = "select RE.id_risorsa_ext,RE.nome_file,RE.tipo,RE.copyright, RE.id_utente, RE.keywords,RE.titolo, RE.descrizione, RE.pubblicato,RE.lingua, RN.id_nodo from risorsa_esterna as RE, risorse_nodi as RN where RE.nome_file = ? and RE.id_risorsa_ext = RN.id_risorsa_ext and RN.id_nodo = ?";

        $resourceInfoAr =  $db->getRow($sql,array($filename,$id_node),AMA_FETCH_ASSOC);
//        $resourceInfoAr =  $db->getRow("select id_risorsa_ext, nome_file, tipo, copyright, id_utente, keywords, titolo, descrizione, pubblicato, lingua from risorsa_esterna where nome_file=$sqlfilename");
        if (AMA_DB::isError($resourceInfoAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $resourceInfoAr;
    }

    /**
     * Get external resource id starting from the file name
     *
     *
     * @param $file_name - file name of the resource
     *
     * @return the id of the resource or null or an error value
     *
     */
    public function get_risorsa_esterna_id($filename) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlfilename = $this->sql_prepared($filename);
        $id =  $db->getOne("select id_risorsa_ext from risorsa_esterna where nome_file=$sqlfilename");
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $id;
    }

    /**
     * Get external resource id starting from the file name
     *
     *
     * @param $file_name - substring of file name of the resource o
     *
     * @return an array of ids of the resource or null or an error value
     *
     */
    public function get_risorsa_esterna_ids($filename) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlfilename = $this->sql_prepared($filename);
        $sqlquery = "select id_risorsa_ext from risorsa_esterna where nome_file LIKE '%$filename%'";
        $idAr =  $db->getCol($sqlquery);
        if (AMA_DB::isError($idAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $idAr;
    }

    /**
     * Get a node id starting from external resource id
     *
     *
     * @param $res_id - idof the resource
     *
     * @return the id of the node or null or an error value
     *
     */
    public function get_nodo_risorsa_esterna_id($res_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlres_id = $this->sql_prepared($res_id);
        $id =  $db->getOne("select id_nodo  from risorse_nodi where id_risorsa_ext =$sqlres_id");
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $id;
    }

    /**
     * Get the extended node data starting from node id
     *
     *
     * @param $node_id - id of node
     *
     * @return all data of the extended node or null or an error value
     *
     */
    public function get_extended_node($node_id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlnode_id = $this->sql_prepared($node_id);
        $sql = "select *  from extended_node where id_node =$sqlnode_id";
        $extended_nodeHA =  $db->getRow($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($extended_nodeHA)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $extended_nodeHA;
    }

    /**
     *
     * @param $search_text
     * @param $course_id
     * @param $user_level
     * @return unknown_type
     */
    public function find_media_in_course($search_text, $course_id, $user_level=null) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sqlsearch_text = $this->sql_prepared($search_text);
        $sqlsearch_text = substr($sqlsearch_text, 1, count($sqlsearch_text)-2);

        $sql = "SELECT  RE.nome_file, RE.tipo, N.id_nodo, N.id_utente, N.nome, N.titolo
                FROM risorsa_esterna AS RE, risorse_nodi AS RN, nodo AS N
               WHERE RE.nome_file like '%$sqlsearch_text%'
                 AND RN.id_risorsa_ext = RE.id_risorsa_ext
                 AND RN.id_nodo LIKE '{$course_id}\_%'
			     AND N.id_nodo = RN.id_nodo";
        if($user_level != null && is_numeric($user_level)) {
            $sql .= " AND N.livello <= $user_level";
        }

        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * Get all info from the record of risorsa_esterna_info identified by $res_id
     *
     * @param $res_id the id of the record to query
     *
     * @return an hash containing the following values:
     * nome_file         - the id of the node this link lives in
     * tipo              - type of resource
     *                     0 -  Image (jpeg, png)
     *                     1 -  Sound (wav, mp3, midi, au, ra)
     *                     2 -  Video (real, quicktime, avi, mpeg)
     *                     3 -  Doc (Excel, Word, Rtf, txt, pdf)
     * copyright         - if the resource has a copyright or not
     *                     0 - no
     *                     1 - yes
     * id_utente        - the id of the user that added this media
     * keywords         - the keywords of the media (separated by coma)
     * titolo           - title of the media
     * descrizione      - description of the media
     * pubblicato       - published or not (0 = not published. 1 = published)
     * lingua           - the language (numeric value). Contain the id_lingua to point to common.lingue table
     *
     */
    public function get_risorsa_esterna_info($res_id) {

        $sql = 'SELECT nome_file, tipo, copyright, id_utente, keywords, titolo, descrizione, pubblicato, lingua'
             . ' FROM risorsa_esterna WHERE id_risorsa_ext=?';
        $values = array($res_id);

        $result = $this->getRowPrepared($sql, $values, AMA_FETCH_ASSOC);

        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(!$result) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /**
     * Get all info from the record of risorsa_esterna_info identified by $filename AND $author_id
     *
     * @param $res_id the id of the record to query
     *
     * @return an hash containing the following values:
     * nome_file         - the id of the node this link lives in
     * tipo              - type of resource
     *                     0 -  Image (jpeg, png)
     *                     1 -  Sound (wav, mp3, midi, au, ra)
     *                     2 -  Video (real, quicktime, avi, mpeg)
     *                     3 -  Doc (Excel, Word, Rtf, txt, pdf)
     * copyright         - if the resource has a copyright or not
     *                     0 - no
     *                     1 - yes
     * id_utente        - the id of the user that added this media
     * keywords         - the keywords of the media (separated by coma)
     * titolo           - title of the media
     * descrizione      - description of the media
     * pubblicato       - published or not (0 = not published. 1 = published)
     * lingua           - the language (numeric value). Contain the id_lingua to point to common.lingue table
     *
     */
    public function get_risorsa_esterna_info_autore($filename, $author_id) {

        $sql = 'SELECT id_risorsa_ext, nome_file, tipo, copyright, id_utente, keywords, titolo, descrizione, pubblicato, lingua'
             . ' FROM risorsa_esterna WHERE nome_file=? AND id_utente=?';
        $values = array($filename, $author_id);

        $result = $this->getRowPrepared($sql, $values, AMA_FETCH_ASSOC);

        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if(!$result) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /*
     * function set_risorsa_esterna, used to update the media info
     * @param int $id_risorsa
     * @param array $media
     */

     function set_risorsa_esterna ($id_risorsa, $media) {
        $update_risorsa_sql = 'UPDATE risorsa_esterna SET copyright=?, keywords=?, titolo=?, tipo=?,'
                    . 'descrizione=?, pubblicato=?, lingua=? WHERE id_risorsa_ext=?';

            $valuesAr = array(
                    $media['copyright'],
                    $media['keywords'],
                    $media['titolo'],
                    $media['tipo'],
                    $media['descrizione'],
                    $media['pubblicato'],
                    $media['lingua'],
                    $id_risorsa
            );


        $result = $this->queryPrepared($update_risorsa_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
        return true;
     }



    /**
     * function get_risorse_autore, used to get info about author's media in
     * table risorsa_esterna filtered by media type in $media.
     *
     * @param int $author_id
     * @param array $media
     * @return array
     */
    public function get_risorse_autore($author_id, $media=array()) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if (count($media) > 0) {
            $get_media = "";
            while (count($media) > 1) {
                $media_type = array_shift($media);
                $get_media .= "$media_type,";
            }
            if (count($media) == 1) {
                $media_type = array_shift($media);
                $get_media .= "$media_type";
            }
            $sql = "SELECT nome_file, tipo FROM risorsa_esterna WHERE id_utente=$author_id AND tipo IN(".$get_media.")";
        }
        else {
            $sql = "SELECT nome_file, tipo FROM risorsa_esterna WHERE id_utente=$author_id";
        }
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    /**
     * Add a record to table risorse_nodi
     *
     * @access public
     *
     * @param $node_id id of the node to be added
     * @param $res_id  id of the resource to be added
     *
     * @return true on success, an AMA_Error object on failure
     */
    public function _add_risorse_nodi($sqlnode_id, $res_id) {
        ADALogger::log_db("entered _add_risorse_nodi (node_id: $sqlnode_id, res_id: $res_id)");

        if ($sqlnode_id == "''")
        {
        	ADALogger::log_db("passed node id is empty, returning true right away");
        	return true;
        }

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // Modified 29/11/01 by Graffio
        // check if the already exists in the table
        ADALogger::log_db("checking if resource $res_id and node $sqlnode_id already exists in table risorse_nodi ... ");
        $sql_temp = "select id_nodo from risorse_nodi where id_nodo=$sqlnode_id and id_risorsa_ext=$res_id";
        $id = $db->getOne($sql_temp);

        if (AMA_DB::isError($id)) {
            ADALogger::log_db("Error while checking resource in risorse_nodi in query $sql_temp)");
            return new AMA_Error(AMA_ERR_GET);
        }

        if ($id) {
            ADALogger::log_db("it seems it does ($id)");
        }
        else {
            $sql = "insert into risorse_nodi (ID_NODO, ID_RISORSA_EXT) values ($sqlnode_id, $res_id)";
            ADALogger::log_db("inserting using query: $sql");
            $res = $db->query($sql);
            if(AMA_DB::isError($res)) {
                return new AMA_Error(AMA_ERR_ADD);
            }
        }
        return true;
    }

    /**
     * Delete one or more rows from table risorse_nodi
     *
     * @access private
     *
     * @param $node_id id of the node to be removed
     *                 removes all record of that node if $res_id is null
     * @param $res_id  id of the resource to be removed
     *                 a resource cannot be removed if
     *
     * @return a db query result object
     */
    public function _del_risorse_nodi($sqlnode_id, $res_id='') {

        ADALogger::log_db("entering _del_risorse_nodi (sqlnode_id: $sqlnode_id, res_id: $res_id)");
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        //vito, 23 mar 2009
        //$sql = "delete from risorse_nodi where id_nodo=".$this->sql_prepared($sqlnode_id);
        $sql = "delete from risorse_nodi where id_nodo=".$sqlnode_id;

        if ($res_id != '') {
            $sql .= " and id_risorsa_ext = $res_id";
        }
        ADALogger::log_db("deleting record: $sql");

        $result = $db->query($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_REMOVE);
        }
        return true;
    }

    /**
     * Insert multiple records into table risorse_nodi
     *
     * @access private
     *
     * @param $risorse_ar array containing the records as resulting from db->getAll
     *
     * @return a db query result object
     */
    private function _restore_risorse_nodi($risorse_ar) {

        for ($i=0; $i<count($risorse_ar); $i++) {
            $node_id = $risorse_ar[$i][0];
            $res_id = $risorse_ar[$i][1];
            $result = $this->_add_risorse_nodi($node_id, $res_id);
            if(AMA_DB::isError($result)) {
                // FIXME: restituire l'errore o lasciare proseguire?
            }
        }
        // FIXME: per poter restituire true qui non devono essersi verificati errori
        return true;
    }

    /**
     * Insert multiple resources into tables risorsa_esterna and risorse_nodi
     * (within a transaction)
     *
     * @access private
     *
     * @param $risorse_ar array containing the infos
     *        each element has the same structure as that of the hash
     *        passed to add_risorsa_esterna
     *
     * @return an AMA_Error object if something goes wrong, true on success
     */
    private function _add_media($risorse_ar, $sqlnode_id) {

        ADALogger::log_db("entered _add_media");
        $n = count($risorse_ar);

        ADALogger::log_db("got $n resources to add");
        if($n>0) {

            ADALogger::log_db("starting a transaction");
            $this->_begin_transaction();

            for ($i=1; $i<=$n; $i++) {
                $res_ha = $risorse_ar[$i];
                ADALogger::log_db("adding resource $i to risorsa esterna");

                $res_id = $this->add_risorsa_esterna($res_ha);
                if (AMA_DB::isError($res_id)) {
                    // does the rollback
                    $err  = $res_id->getMessage() . AMA_SEP . $this->_rollback();
                    ADALogger::log_db("$err detected, rollbacking");
                    return new AMA_Error($err);
                }
                else {
                    // add instruction to rollback segment only if a new resource was inserted
                    if ($res_id>0) {
                        ADALogger::log_db("done ($res_id), adding instruction to rbs");
                        $this->_rs_add("remove_risorsa_esterna", $res_id);
                    }
                    else {
                        // revert $res_id to positive for future needs
                        $res_id *= -1;
                    }
                }

                ADALogger::log_db("adding resource $i to risorse_nodi");
                $res = $this->_add_risorse_nodi($sqlnode_id, $res_id);

                if (AMA_DB::isError($res)) {
                    // does the rollback
                    $err  = $res->getMessage() . AMA_SEP . $this->_rollback();
                    ADALogger::log_db("$err detected, rollbacking");
                    return new AMA_Error($err);
                }
                else {
                    // add instruction to rollback segment
                    ADALogger::log_db("done, adding instruction to rbs");
                    $this->_rs_add("_del_risorse_nodi", $sqlnode_id, $res_id);
                }
            }
            ADALogger::log_db("committing resources insertion");
            $this->_commit(); // FIXME: e' il posto giusto per $this->_commit?
        }
        return true;
    }

    /**
     * Remove all records related to an external resource from the tables
     * (within a transaction)
     *
     * @access private
     *
     * @param $risorse_ar array containing the ids of the records to remove
     *
     * @return a db query result object
     */
    private function _del_media($risorse_ar, $sqlnode_id) {

        ADALogger::log_db("entered _del_media");
        $this->_begin_transaction();
        $n = count($risorse_ar);

        ADALogger::log_db("got $n resources to remove");

        for ($i=1; $i<=$n; $i++) {
            $res_id = $risorse_ar[$i-1];
            $res = $this->_del_risorse_nodi($sqlnode_id, $res_id);
            if (AMA_DataHandler::isError($res)) {
                // does the rollback
                $err  = $res->getMessage() . AMA_SEP . $this->_rollback();
                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);
            }
            else {
                // add instruction to rollback segment
                ADALogger::log_db("removing from risorse_nodi done ($sqlnode_id, $res_id), adding instruction to rbs");
                $this->_rs_add("_add_risorse_nodi", $sqlnode_id, $res_id);
            }

            $res = $this->remove_risorsa_esterna($res_id);
            if (AMA_DataHandler::isError($res)) {
                // does the rollback
                $err  = $res->getMessage() . AMA_SEP . $this->_rollback();
                ADALogger::log_db("$err detected, rollbacking");
                return new AMA_Error($err);
            }
            else {
                // add instruction to rollback segment
                $res_ha = $this->get_risorsa_esterna_info($res_id);
                ADALogger::log_db("removing from risorsa_esterna done ($res_id), adding instruction to rbs");
                $this->_rs_add("add_risorsa_esterna", $res_ha);
            }
        }

        ADALogger::log_db("committing the removal of resources");
        $this->_commit();
        return true;
    }

    /**
     * Methods accessing table `sessione_eguidance`
     */
    // MARK: Methods accessing table `sessione_eguidance`
    function add_eguidance_session_data($eguidance_dataAr = array()) {
        $sql = 'INSERT INTO sessione_eguidance(id_utente,id_tutor,id_istanza_corso,event_token,data_ora,tipo_eguidance,ud_1,ud_2,ud_3,ud_comments,'
                . 'pc_1,pc_2,pc_3,pc_4,pc_5,pc_6,pc_comments,ba_1,ba_2,ba_3,ba_4,ba_comments,'
                . 't_1,t_2,t_3,t_4,t_comments,pe_1,pe_2,pe_3,pe_comments,ci_1,ci_2,ci_3,ci_4, ci_comments,'
                . 'm_1,m_2,m_comments,other_comments) '
                . 'VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $dataAr = array(
                $eguidance_dataAr['id_utente'],
                $eguidance_dataAr['id_tutor'],
                $eguidance_dataAr['id_istanza_corso'],
                $eguidance_dataAr['event_token'],
                time(),
                $eguidance_dataAr['type_of_guidance'],
                $eguidance_dataAr['ud_1'],
                $eguidance_dataAr['ud_2'],
                $eguidance_dataAr['ud_3'],
                $eguidance_dataAr['ud_comments'],
                $eguidance_dataAr['pc_1'],
                $eguidance_dataAr['pc_2'],
                $eguidance_dataAr['pc_3'],
                $eguidance_dataAr['pc_4'],
                $eguidance_dataAr['pc_5'],
                $eguidance_dataAr['pc_6'],
                $eguidance_dataAr['pc_comments'],
                $eguidance_dataAr['ba_1'],
                $eguidance_dataAr['ba_2'],
                $eguidance_dataAr['ba_3'],
                $eguidance_dataAr['ba_4'],
                $eguidance_dataAr['ba_comments'],
                $eguidance_dataAr['t_1'],
                $eguidance_dataAr['t_2'],
                $eguidance_dataAr['t_3'],
                $eguidance_dataAr['t_4'],
                $eguidance_dataAr['t_comments'],
                $eguidance_dataAr['pe_1'],
                $eguidance_dataAr['pe_2'],
                $eguidance_dataAr['pe_3'],
                $eguidance_dataAr['pe_comments'],
                $eguidance_dataAr['ci_1'],
                $eguidance_dataAr['ci_2'],
                $eguidance_dataAr['ci_3'],
                $eguidance_dataAr['ci_4'],
                $eguidance_dataAr['ci_comments'],
                $eguidance_dataAr['m_1'],
                $eguidance_dataAr['m_2'],
                $eguidance_dataAr['m_comments'],
                $eguidance_dataAr['other_comments']
        );

        $result = $this->queryPrepared($sql, $dataAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return true;
    }
    function update_eguidance_session_data($eguidance_dataAr = array()) {
        $sql = 'UPDATE sessione_eguidance '
                . 'SET tipo_eguidance = ?,ud_1 = ?,ud_2 = ?,ud_3 = ?,ud_comments = ?,'
                . 'pc_1 = ?,pc_2 = ?,pc_3 = ?,pc_4 = ?,pc_5 = ?,pc_6 = ?,pc_comments = ?,ba_1 = ?,ba_2 = ?,ba_3 = ?,ba_4 = ?,ba_comments = ?,'
                . 't_1 = ?,t_2 = ?,t_3 = ?,t_4 = ?,t_comments = ?,pe_1 = ?,pe_2 = ?,pe_3 = ?,pe_comments = ?,ci_1 = ?,ci_2 = ?,ci_3 = ?,ci_4 = ?, ci_comments = ?,'
                . 'm_1 = ?,m_2 = ?,m_comments = ?,other_comments = ? '
                . 'WHERE id = ?';

        $dataAr = array(
                $eguidance_dataAr['type_of_guidance'],
                $eguidance_dataAr['ud_1'],
                $eguidance_dataAr['ud_2'],
                $eguidance_dataAr['ud_3'],
                $eguidance_dataAr['ud_comments'],
                $eguidance_dataAr['pc_1'],
                $eguidance_dataAr['pc_2'],
                $eguidance_dataAr['pc_3'],
                $eguidance_dataAr['pc_4'],
                $eguidance_dataAr['pc_5'],
                $eguidance_dataAr['pc_6'],
                $eguidance_dataAr['pc_comments'],
                $eguidance_dataAr['ba_1'],
                $eguidance_dataAr['ba_2'],
                $eguidance_dataAr['ba_3'],
                $eguidance_dataAr['ba_4'],
                $eguidance_dataAr['ba_comments'],
                $eguidance_dataAr['t_1'],
                $eguidance_dataAr['t_2'],
                $eguidance_dataAr['t_3'],
                $eguidance_dataAr['t_4'],
                $eguidance_dataAr['t_comments'],
                $eguidance_dataAr['pe_1'],
                $eguidance_dataAr['pe_2'],
                $eguidance_dataAr['pe_3'],
                $eguidance_dataAr['pe_comments'],
                $eguidance_dataAr['ci_1'],
                $eguidance_dataAr['ci_2'],
                $eguidance_dataAr['ci_3'],
                $eguidance_dataAr['ci_4'],
                $eguidance_dataAr['ci_comments'],
                $eguidance_dataAr['m_1'],
                $eguidance_dataAr['m_2'],
                $eguidance_dataAr['m_comments'],
                $eguidance_dataAr['other_comments'],
                $eguidance_dataAr['id_eguidance_session']
        );

        $result = $this->queryPrepared($sql, $dataAr);
        if(self::isError($result)) {
            return new AMA_Error(AMA_ERR_ADD);
        }

        return true;
    }

    public function get_eguidance_session_with_event_token($event_token) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT id, id_utente,id_tutor,data_ora,tipo_eguidance,ud_1,ud_2,ud_3,ud_comments,'
                . 'pc_1,pc_2,pc_3,pc_4,pc_5,pc_6,pc_comments,ba_1,ba_2,ba_3,ba_4,ba_comments,'
                . 't_1,t_2,t_3,t_4,t_comments,pe_1,pe_2,pe_3,pe_comments,ci_1,ci_2,ci_3,ci_4, ci_comments,'
                . 'm_1,m_2,m_comments,other_comments '
                . "FROM sessione_eguidance WHERE event_token = '$event_token'";

        $result = $db->getRow($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result) || !is_array($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function get_last_eguidance_session($id_course_instance) {
        $limit_clause = 'LIMIT 1';
        return $this->get_eguidance_sessions($id_course_instance, $limit_clause);
    }

    public function get_eguidance_sessions($id_course_instance, $limit_clause='') {

        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT id_utente,id_tutor,event_token,data_ora,tipo_eguidance,ud_1,ud_2,ud_3,ud_comments,'
                . 'pc_1,pc_2,pc_3,pc_4,pc_5,pc_6,pc_comments,ba_1,ba_2,ba_3,ba_4,ba_comments,'
                . 't_1,t_2,t_3,t_4,t_comments,pe_1,pe_2,pe_3,pe_comments,ci_1,ci_2,ci_3,ci_4, ci_comments,'
                . 'm_1,m_2,m_comments,other_comments '
                . 'FROM sessione_eguidance WHERE id_istanza_corso = ' . $id_course_instance
                . ' ORDER BY id DESC';

        if($limit_clause != '') {
            $sql .= ' ' . $limit_clause;
            $result = $db->getRow($sql, NULL, AMA_FETCH_ASSOC);
        }
        else {
            $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        }

        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function get_eguidance_session_dates($id_course_instance) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT id, data_ora FROM sessione_eguidance WHERE id_istanza_corso = ' . $id_course_instance;

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }


    public function get_eguidance_session($id_course_instance, $row) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT id_utente,id_tutor,data_ora,tipo_eguidance,ud_1,ud_2,ud_3,ud_comments,'
                . 'pc_1,pc_2,pc_3,pc_4,pc_5,pc_6,pc_comments,ba_1,ba_2,ba_3,ba_4,ba_comments,'
                . 't_1,t_2,t_3,t_4,t_comments,pe_1,pe_2,pe_3,pe_comments,ci_1,ci_2,ci_3,ci_4, ci_comments,'
                . 'm_1,m_2,m_comments,other_comments '
                . 'FROM sessione_eguidance WHERE id_istanza_corso = ' . $id_course_instance
                . ' LIMIT ' . $row .',1';
        $result = $db->getRow($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }
    /**
     * Methods accessing table `studente`
     */
    // MARK: Methods accessing table `studente`

    /**
     * Add a student to the DB
     *
     * @access public
     *
     * @param $student_ar an array containing all the student's data
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *
     */
    public function add_student($student_ar) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        /**
         * Add user data in table utenti
         */
        $result = $this->add_user($student_ar);
        if(self::isError($result)) {
            // $result is an AMA_Error object
            return $result;
        }

        $id_student = $student_ar['id_utente'];
        // insert a row into table studente
        $sql  = "insert into studente (id_utente_studente)";
        $sql .= " values ($id_student)";

        // vito, 17 nov 2008: call to $this->executeCritical instead of call to $db->query
        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            // $res is an AMA_Error object
            return $res;
        }
        return $id_student; // return the id of inserted student.
    }

    /**
     * Remove a student from the DB
     *
     * @access public
     *
     * @param $id the unique id of the student
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     * @note the referential integrity with iscrizioni is checked
     */
    public function remove_student($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // referential integrity checks
        $ri_id = $db->getOne("select id_utente_studente from iscrizioni where id_utente_studente=$id");
        if ($ri_id) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }
        $ri_id = $db->getOne("select id_nodo from nodo where id_utente=$id");
        if ($ri_id) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }
        $ri_id = $db->getOne("select id_link from link where id_utente=$id");
        if ($ri_id) {
            return new AMA_Error(AMA_ERR_REF_INT_KEY);
        }

        $sql = "delete from studente where id_utente_studente=$id";
        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            // $res is an AMA_Error object
            return $res;
        }

        $sql = "delete from utente where id_utente=$id";

        $res = $this->executeCritical($sql);
        if (AMA_DB::isError($res)) {
            // $res is an AMA_Error object
            return $res;
        }
        return true;
    }

    /**
     * Get a list of students' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     */
    public function &get_students_list($field_list_ar) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }
        // do the query
        $students_ar =  $db->getAll("select id_utente$more_fields from utente, studente where  tipo=".AMA_TYPE_STUDENT ." and id_utente=id_utente_studente");
        if (AMA_DB::isError($students_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $students_ar;
    }


    /**
     * Get those students ids verifying the given criterium
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono
     *
     * @param  clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &find_students_list($field_list_ar, $clause='', $order='cognome') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }

        // handle null clause, too
        if ($clause) {
            $clause = ' where '.$clause;
        }
        // do the query
        if ($clause == '') {
            $students_ar =  $db->getAll("select id_utente$more_fields from utente, studente where tipo=" . AMA_TYPE_STUDENT . " and id_utente=id_utente_studente order by $order");
        }
        else {
            $students_ar =  $db->getAll("select id_utente$more_fields from utente, studente $clause and tipo=" . AMA_TYPE_STUDENT . " and id_utente=id_utente_studente order by $order");
        }

        if (AMA_DB::isError($students_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $students_ar;
    }

    /**
     * Get a list of students' ids from the DB
     *
     * @access public
     *
     * @return an array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     *
     * @see get_students_list
     */
    public function &get_students_ids() {
        return $this->get_students_list();
    }

    public function get_user($id) {
        return $this->get_student($id);
    }

    /**
     * Get all informations about student
     *
     * @access public
     *
     * @param $id the student's id
     *
     * @return an array containing all the informations about an administrator
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     *        res_ha['tariffa']
     *        res_ha['profilo']
     *
     */
    public function get_student($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(self::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }
        // get_student($id) originally did not return the user id as a result,
        unset($get_user_result['id']);

        return $get_user_result;
    }

    /**
     * Updates informations related to a student
     *
     * @access public
     *
     * @param $id the student's id
     *        $admin_ar the informations. empty fields are not updated
     *
     * @return an error if something goes wrong, true on success
     *
     */
    public function set_student($id, $student_ha) {
        return $this->set_user($id, $student_ha);
    }
    /**
     * Methods accessing table `template`
     */
    // MARK: Methods accessing table `template`

    /**
     * Get the template used by a given type of node and by a given type of user.
     * Looks into the "template" table and returns the text of the template.
     *
     *
     * @access public
     *
     * @param $node_type the type of the node (see add_node)
     *
     * @param $user_type the type of user (see add_user)
     *
     * @return the text of the template, if found
     *
     * @see add_node, add_user
     *
     */
    public function get_template($node_type, $user_type) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table template
        $sql  = "select testo ";
        $sql .= " from template where tipo_pagina=$node_type and profilo_utente=$user_type";
        // FIXME:chiamare getOne al posto di getRow
        $res_ar =  $db->getRow($sql);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (!$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // returns the text
        return $res_ar[0];
    }

    /**
     * Methods accessing table `tutor`
     */
    // MARK: Methods accessing table `tutor`

    /**
     * Add a tutor to the DB
     *
     * @access public
     *
     * @param $tutor_ar an array containing all the tutor's data
     *
     * @return an AMA_Error object or a DB_Error object if something goes wrong
     *
     */
    public function add_tutor($tutor_ha) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        /*
     * Add user data in table utenti
        */
        $result = $this->add_user($tutor_ha);
        if(self::isError($result)) {
            // $result is an AMA_Error object
            return $result;
        }
        $add_tutor_sql = 'INSERT INTO tutor(id_utente_tutor, tariffa, profilo) VALUES(?,?,?)';

        $add_tutor_values = array(
                $tutor_ha['id_utente'],
                $this->or_zero($tutor_ha['tariffa']),
                $this->or_null($tutor_ha['profilo'])
        );

        $result = $this->executeCriticalPrepared($add_tutor_sql, $add_tutor_values);
        if (AMA_DB::isError($result)) {
            // try manual rollback in case problems arise
            $delete_user_sql = 'DELETE FROM utente WHERE username=?';
            $delete_result   = $this->executeCriticalPrepared($delete_user_sql, array($tutor_ha['username']));
            if (AMA_DB::isError($delete_result)) {
                return $delete_result;
            }
            /*
       * user data has been successfully removed from table utente, return only
       * the error obtained when adding user data to table autore.
            */
            return $result;
        }

        // return the tutor id
        return $tutor_ha['id_utente'];
    }

    /**
     * Remove a tutor from the DB
     *
     * @access public
     *
     * @param $id the unique id of the tutor
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
    public function remove_tutor($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from tutor where id_utente_tutor=$id";
        ADALogger::log_db($sql);
        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            // $res is ana AMA_Error object
            return $res;
        }

        $sql = "delete from utente where id_utente=$id";
        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            // $res is ana AMA_Error object
            return $res;
        }
        return true;
    }

    /**
     * Get a list of tutor' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_tutors_list
     */
    public function &get_tutors_list($field_list_ar) {
        return $this->find_tutors_list($field_list_ar,'',false);
    }

    /**
     * Get a list of super tutor' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_tutors_list
     */
    public function &get_supertutors_list($field_list_ar) {
    	return $this->find_tutors_list($field_list_ar,'',true);
    }

    /**
     * Get a list of tutors' ids from the DB
     *
     * @access public
     *
     * @return an array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     *
     * @see find_authors_list, get_authors_list
     */
    public function &get_tutors_ids() {
        return $this->get_tutors_list();
    }

    /**
     * Get those tutors' ids verifying the given criterium on the tarif fiels
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @param  clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &find_tutors_list($field_list_ar, $clause='', $supertutors=false) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }
        // handle null clause, too
        if ($clause) {
            $clause = ' AND '.$clause;
        }

        // do the query
        $sql_query="select id_utente$more_fields from utente, tutor where  tipo=".
        ($supertutors ? AMA_TYPE_SUPERTUTOR : AMA_TYPE_TUTOR) ." and id_utente=id_utente_tutor$clause";
        $tutors_ar =  $db->getAll($sql_query);
        if (AMA_DB::isError($tutors_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $tutors_ar;
    }

    /**
     * Return tutor assigned course instance
     *
     * @access public
     *
     * @param $id_tutor pass a single/array tutor id or use "false" to retrieve all tutors
     * @param $id_course if passed as int, select only instances of the passed course id
     * @param $isSuper true if the tutor is a supertutor
     *
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array('tutor id'=>array('course_instance', 'course_instance', 'course_instance'));
     */
    public function &get_tutors_assigned_course_instance($id_tutor = false, $id_course = false, $isSuper = false) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // do the query
        $sql = "SELECT ".
					(($isSuper) ? $id_tutor." AS `id_utente_tutor`" : "ts.`id_utente_tutor`") .",".
					"c.`id_corso`, c.`titolo`, c.`id_utente_autore`,
					i.`id_istanza_corso`, i.`title`,i.`data_inizio_previsto`,i.`data_fine`,i.`duration_hours`,
                                        i.`durata`,i.`self_instruction`,i.`data_inizio`
				FROM ".
				(($isSuper) ? "" : "`tutor_studenti` ts JOIN ").
				"`istanza_corso` i ".
				(($isSuper) ? "" : "ON (i.`id_istanza_corso`=ts.`id_istanza_corso`)").
				" JOIN `modello_corso` c ON (c.`id_corso`=i.`id_corso`)";

        if (!$isSuper) {
			if (is_array($id_tutor) AND !empty($id_tutor))
			{
				$sql .= " WHERE id_utente_tutor IN (".implode(',',$id_tutor).")";
			}
			else if ($id_tutor)
			{
				$sql .= " WHERE id_utente_tutor = ".$id_tutor;
			}
        }

		if (is_numeric($id_course) && intval($id_course)>0) {

			if (stristr($sql,'where')!==false) $sql .= ' AND ';
			else $sql .= ' WHERE ';

			$sql .= 'c.`id_corso`='.intval($id_course);
		}

        $tutors_ar =  $db->getAll($sql, null, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($tutors_ar)) {
        	$retval = new AMA_Error(AMA_ERR_GET);
            return $retval;
        }
        else {
			$tutors = array();
			foreach($tutors_ar as $k=>$v) {
				$id = $v['id_utente_tutor'];
				unset($v['id_utente_tutor']);
				$tutors[$id][] = $v;
			}
			unset($tutors_ar);

			return $tutors;
		}
    }

    /**
     * Get all informations about tutor
     *
     * @access public
     *
     * @param $id the tutor's id
     *
     * @return an array containing all the informations about a tutor
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     *        res_ha['tariffa']
     *        res_ha['profilo']
     *        res_ha['layout']
     *
     *        an AMA_Error object on failure
     *
     */
    public function get_tutor($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(AMA_Common_DataHandler::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            return $get_user_result;
        }
        // get_tutor($id) originally did not return the user id as a result,
        unset($get_user_result['id']);


        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table TUTOR
        $get_tutor_sql = "select tariffa, profilo from tutor where id_utente_tutor=$id";
        $get_tutor_result = $db->getRow($get_tutor_sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($get_tutor_result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if (!is_array($get_tutor_result)) {
            /* inconsistency found! a message should be logged */
            return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
        }
        return array_merge($get_user_result, $get_tutor_result);
    }

    /**
     * Updates informations related to a tutor
     *
     * @access public
     *
     * @param $id the tutor's id
     *        $tutor_ar the informations. empty fields are not updated
     *
     * @return an error if something goes wrong
     *
     */
    public function set_tutor($id, $tutor_ha) {

        // backup old values
        $old_values_ha = $this->get_tutor($id);

        $result = $this->set_user($id,$tutor_ha);
        if(self::isError($result)) {
            // $result is an AMA_Error object
            return $result;
        }

        $update_tutor_sql = 'UPDATE tutor SET tariffa=?, profilo=? WHERE id_utente_tutor=?';
        $valuesAr = array(
                $this->or_zero($tutor_ha['tariffa']),
                $tutor_ha['profilo'],
                $id
        );
        $result = $this->queryPrepared($update_tutor_sql, $valuesAr);
        if(AMA_DB::isError($result)) {
            $valuesAr = array(
                    $old_values_ha['nome'],
                    $old_values_ha['cognome'],
                    $old_values_ha['email'],
                    $old_values_ha['telefono'],
                    $old_values_ha['password'],
                    $old_values_ha['layout'],
                    $old_values_ha['indirizzo'],
                    $old_values_ha['citta'],
                    $old_values_ha['provincia'],
                    $old_values_ha['nazione'],
                    $old_values_ha['codice_fiscale'],
                    AMA_Common_DataHandler::date_to_ts($old_values_ha['birthdate']),
                    $old_values_ha['sesso'],
                    $old_values_ha['stato'],
                    $old_values_ha['lingua'],
                    $old_values_ha['timezone'],
                    $old_values_ha['cap'],
                    $old_values_ha['matricola'],
                    $old_values_ha['avatar'],
            		$old_values_ha['birthcity'],
            		$old_values_ha['birthprovince'],
                    $id
            );
            $update_user_sql = 'UPDATE utente SET nome=?, cognome=?, e_mail=?, telefono=?, password=?, layout=?, '
                    . 'indirizzo=?, citta=?, provincia=?, nazione=?, codice_fiscale=?, birthdate=?, sesso=?, '
                    . 'stato=?, lingua=?,timezone=?,cap=?,matricola=?,avatar=?, birthcity=?, birthprovince=? WHERE id_utente=?';

            $result = $this->executeCriticalPrepared($update_user_sql, $valuesAr);
            // qui andrebbe differenziato il tipo di errore
            if(AMA_DB::isError($result)) {
                return new AMA_Error(AMA_ERR_UPDATE);
            }

            return new AMA_Error(AMA_ERR_UPDATE);
        }

        return true;
    }

    /**
     * Methods accessing table `tutor_studenti`
     */
    // MARK: Methods accessing table `tutor_studenti`

    /**
     * assign a tutor to the course_instance
     *
     * @access public
     *
     * @param $id_tutor    - tutor id
     * @param $id_corso    - course instance id
     *
     * @return an AMA_Error object if something goes wrong, true on success
     */
    public function course_instance_tutor_subscribe($id_course_instance, $id_tutor) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // verify key uniqueness (index)
        $sql = "select id_istanza_corso from tutor_studenti where id_istanza_corso=$id_course_instance and id_utente_tutor=$id_tutor";
        $id =  $db->getOne($sql);
        if (AMA_DB::isError($id)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if ($id) {
            return new AMA_Error($this->errorMessage(AMA_ERR_UNIQUE_KEY) .
                            " in course_instance_tutor_subscribe ");
        }

        // insert a row into table iscrizioni
        $sql =  "insert into tutor_studenti (id_utente_tutor, id_istanza_corso)";
        $sql .= " values ($id_tutor, $id_course_instance);";
        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            // $res is ana AMA_Error object
            return $res;
        }

        return true;
    }

    /**
     * de-assign a tutor from the course
     *
     * @access public
     *
     * @param $id_tutor      the id of the tutor
     * @param $id_corso      the unique id of the course  instance
     *
     * @return an AMA_Error object if something goes wrong, true on success
     *
     */
    public function course_instance_tutor_unsubscribe($id_course_instance, $id_tutor) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from tutor_studenti where id_utente_tutor='$id_tutor' and id_istanza_corso='$id_course_instance'";
        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            // $res is ana AMA_Error object
            return $res;
        }
        return true;
    }
    /**
     * De-assign all the tutors from this course instance
     *
     * @param int $id_course_instance
     * @return true on success, an AMA_Error object on faulure
     */
    public function course_instance_tutors_unsubscribe($id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "delete from tutor_studenti where id_istanza_corso=$id_course_instance";
        $result = $db->query( $sql );
        if (AMA_DB::isError($result)) {
            // $result is an AMA_Error object
            return $result;
        }
        return true;
    }

    /**
     * get the tutor(s) of the course_instance
     *
     * @access public
     *
     * @param $id_tutor    - tutor id
     * @param $id_instance    - course instance id
     * @param $number    - mode: a single tutor  or array
     *
     * @return an error if something goes wrong, an array if $number >=1, an integer else
     */
    public function course_instance_tutor_get($id_instance,$number=1) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // select row(s) into table tutor_studenti
        $sql =  "select id_utente_tutor from tutor_studenti where id_istanza_corso=$id_instance";
        if ($number==1) {
            $res =  $db->getRow($sql);
        }
        else {
            $res =  $db->getAll($sql);
        }
        if(AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if ((!empty($res)) && (!AMA_dataHandler::isError($res))) {
            if ($number==1) {
                $id_utente_tutor = $res[0];
                return $id_utente_tutor;
            }
            else {
                $tutorAr = array();
                foreach ($res as $tutor) {
                    $id_utente_tutor = $tutor[0];
                    $tutorAr[] = $id_utente_tutor;
                }
                return $tutorAr;
            }
        }

        // no tutor found
        return false;
    }

  /**
   * get the tutor(s) complete informations of the course_instance
   *
   * @access public
   *
   * @param $id_tutor    - tutor id
   * @param $id_instance    - course instance id
   * @param $number    - mode: a single tutor  or array
   *
   * @return an error if something goes wrong, an array if $number >=1, an integer else
   */
  public function course_instance_tutor_info_get($id_instance,$number=0){
    $db =& $this->getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;

    $sql =  "select TS.id_utente_tutor, U.nome, U.cognome, U.username from tutor_studenti AS TS, utente AS U where id_istanza_corso=$id_instance AND TS.id_utente_tutor=U.id_utente";
    if ($number==1) {
      $res =  $db->getRow($sql);
    }
    else {
      $res =  $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
    }
    if(AMA_DB::isError($res)) {
      return new AMA_Error(AMA_ERR_GET);
    }
    return $res;
    /*
    if ((!empty($res)) && (!AMA_dataHandler::isError($res))){
      if ($number==1){
        $id_utente_tutor = $res[0];
        return $id_utente_tutor;
      }
      else {
        $tutorAr = array();
        foreach ($res as $tutor) {
          $id_utente_tutor = $tutor[0];
          $tutorAr[] = $id_utente_tutor;
        }
        return $tutorAr;
      }
    }
     *
     */

    // no tutor found
    return false;
  }


    /**
     * get the course_instance of the tutor
     *
     * @access public
     *
     * @param $id_tutor    - tutor id
     * @param $isSuper     - true if tutor is a supertutor
     *
     * @return
     */
    public function course_tutor_instance_get($id_tutor, $isSuper=false) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // select row into table tuto_studenti
        if (!$isSuper) {
	        $sql =  "select id_istanza_corso,id_utente_tutor from tutor_studenti where id_utente_tutor='$id_tutor'";
        } else {
	        $sql =  "select id_istanza_corso, $id_tutor AS id_utente_tutor FROM istanza_corso";
        }
        $res =  $db->getAll($sql);
        if(AMA_DB::isError($res)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        if(!empty($res)) {
            return $res;
        }
        // no instance found
        return false;
    }

    public function count_active_course_instances($timestamp) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // select row into table tuto_studenti
        $sql =  "SELECT COUNT(id_istanza_corso) FROM istanza_corso WHERE data_inizio < $timestamp AND data_fine > $timestamp";
        $result =  $db->getOne($sql);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * Get notes' count for a given given user and course instance in the selected provider
     *
     * @access public
     *
     * @param courseInstanceId
     *
     * @param userId
     *
     *
     * @return number of notes on success, an AMA_Error object on failure
     */
    public function count_new_notes_in_course_instances($courseInstanceId,$userId) {
    	$db =& $this->getConnection();
    	if ( AMA_DB::isError( $db ) ) return $db;

    	// select row into table nodo
    	$sql =  "SELECT COUNT(n.`id_nodo`)
    	FROM `nodo` n
    	JOIN
    	(
    	SELECT `data_visita`
    	FROM `history_nodi`
    	WHERE `id_utente_studente` = $userId
    	ORDER BY `data_visita` DESC
    	LIMIT 1
    	) h ON (h.`data_visita` < n.`data_creazione`)";

    	$sql .=" WHERE n.`tipo` = ".ADA_NOTE_TYPE;

    	$sql.=" AND n.`id_istanza` = ".$courseInstanceId;

    	$result =  $db->getOne($sql);
    	if(AMA_DB::isError($result)) {
    	return new AMA_Error(AMA_ERR_GET);
    	}

    		return $result;
    	}

    	/**
    	 * gets the list of all nodes that have been visited and are to be removed from the
    	 * user whatsnew array
    	 *
    	 * @param  $userObj	user to perform the action for
    	 *
    	 */
public function get_updates_nodes($userObj, $pointer)
    	{
    		$db =& $this->getConnection();
    		if ( AMA_DB::isError( $db ) ) return $db;

    		$todel = array();
    		$node_id_string = '';
    		$whatsnew = $userObj->getwhatsnew();

    		// build the list of id_nodo stored in session var
    		foreach ($whatsnew[$pointer] as $num=>$item)
    		{
    			$node_id_string .= "'".$item['id_nodo']."'";
    			if ($num < count ($whatsnew[$pointer])-1) $node_id_string .= ',';
    		}
    		/**
    		 * if there are no new nodes stored in session for this provider, break out of the loop
    		 * and skip to the next provider
    		 */
    		if ($node_id_string!=='')
    		{
	    		/**
	    		 * let's execute the query that will return the list of the id_nodo to be
	    		 * removed from the session whatsnew array
	    		 */

	    	    $sql = "SELECT DISTINCT(B.id_nodo)
							FROM nodo B LEFT JOIN history_nodi A ON A.id_nodo = B.id_nodo
							WHERE B.id_nodo IN(".$node_id_string.")
							AND A.`id_utente_studente`=". $userObj->getId() ."
							AND NOT(data_creazione>=data_visita OR ISNULL(data_visita))";
	   			$todel = $db->getAll($sql);
    		}
   			return $todel;
    	}

    	/**
    	 * Get nodes' count for a given given user the selected provider
    	 *
    	 * @access public
    	 *
    	 * @param userId user id to get new nodes for
    	 * @param maxNodes : maximum number of nodes to get, gets all nodes if zero or not passed
    	 *
    	 * @author giorgio 29/apr/2013
    	 *
    	 * @return assoc array containing id_nodo, id_istazna and nome of the matched new nodes
    	 *
    	 * on success, an AMA_Error object on failure
    	 */
    	public function get_new_nodes($userId, $maxNodes = 3) {

    		$nodeTypesArray = array ( ADA_LEAF_TYPE, ADA_GROUP_TYPE );

    		$db =& $this->getConnection();
    		if ( AMA_DB::isError( $db ) ) return $db;

    		$instancesArray = $this->get_course_instances_active_for_this_student ($userId);
    		$result = array();

    		if (!AMA_DB::isError($instancesArray) && is_array($instancesArray) && count($instancesArray)>0) {
	    		foreach ($instancesArray as $instance) {
	    			// check if course instance has been visited
	    			$temp = $this->get_last_visited_nodes ($userId, $instance['id_istanza_corso'],1);
	    			$hasbeenvisited = !empty($temp);

	    			if ($hasbeenvisited)
	    			{
	    				$last_time_visited_class = $temp[0]['data_uscita'];
	    				// get student level
	    				$studentlevel = $this->_get_student_level($userId, $instance['id_istanza_corso']);
	    				/**
	    				 * new nodes are:
	    				 * 1. nodes the user has never visited
	    				 * 2. ndoes with data creazione > of the maximum data_visita for that node
	    				 *
	    				 *     so:
	    				 */

	    				$sql = 'SELECT id_nodo, ID_ISTANZA, nome from nodo where data_creazione >= '. $last_time_visited_class .
	    				' AND id_nodo LIKE \''.$instance['id_corso'].'\_%\' AND livello <=' . $studentlevel .
	    				' AND tipo IN (' . implode (", ", $nodeTypesArray) .') ORDER BY data_creazione
	    						 DESC LIMIT '. $maxNodes;

	    				$tmpresults = $db->getAll($sql, null, AMA_FETCH_ASSOC );

	    				if (!empty($tmpresults)) foreach ($tmpresults as $tempresult) array_push ($result, $tempresult);


	    				// return ($db->getAll($sql, null, AMA_FETCH_ASSOC ));

	    				// 1. get nodes user has never visited

	//     				$sql = "SELECT DISTINCT(B.`id_nodo`) AS `id_nodo` , B.`id_istanza`, B.`nome`
	//     						FROM `nodo` B LEFT JOIN `history_nodi` A ON A.`id_nodo` = B.`id_nodo`
	// 							WHERE B.`tipo` IN (". implode (", ", $nodeTypesArray) .")
	// 							AND ISNULL (`data_visita`)";
	// 					// $sql .= " AND (A.`id_utente_studente` =".$userId ." OR ISNULL(A.`id_utente_studente`))";
	//     				$sql .= " AND B.`id_nodo` LIKE '".$instance[id_corso]."_%'";
	//     				$sql .= " AND B.`livello`<=" . $studentlevel;
	// 					$sql .= " ORDER BY `data_creazione` DESC";

	// 					$nevervisitednodes = $db->getAll($sql, null, AMA_FETCH_ASSOC );

	// 					print_r ($nevervisitednodes);

	// 					$sql= "SELECT HN.id_nodo AS 'id_nodo', HN.data_visita AS 'max_data_visita'
	// 						FROM history_nodi HN
	// 						INNER JOIN (
	// 							SELECT id_nodo, MAX( data_visita ) AS maxdatetime
	// 							FROM history_nodi
	// 							GROUP BY id_nodo
	// 							)	GROUPEDHN ON HN.id_nodo = GROUPEDHN.id_nodo
	// 						AND HN.data_visita = GROUPEDHN.maxdatetime
	// 						AND HN.`id_nodo` LIKE '".$instance[id_corso]."_%'
	// 						AND HN.id_utente_studente =".$userId."
	// 						ORDER BY id_nodo ASC";

	// 					$sql  = "SELECT `id_nodo`, `data_visita` AS `max_data_visita` FROM `history_nodi`";
	// 					$sql .= " WHERE `id_utente_studente`=".$userId;
	// 					$sql .= " AND `id_nodo` LIKE '".$instance[id_corso]."_%'";
	// 					$sql .= " GROUP BY `id_nodo` HAVING MAX(`data_visita`) ";
	// 					$sql .= " ORDER BY max_data_visita DESC";

	// 					$maximumdatas = $db->getAll($sql, null, AMA_FETCH_ASSOC );

	// 					print_r($nevervisitednodes);

	// 					$othernewnodes = array();
	// 					foreach ($maximumdatas as $maxdatafornode)
	// 					{
	// 						$nodeId = $maxdatafornode['id_nodo'];
	// 						$maxData = $maxdatafornode['max_data_visita'];

	// // 						print_r ("$nodeId - $maxData\r\n<br>");

	// 						// execute the query to get new nodes
	// 						$sql ="SELECT DISTINCT(B.`id_nodo`) AS `id_nodo` , B.`id_istanza`, B.`nome`
	// 						FROM `nodo` B LEFT JOIN `history_nodi` A ON A.`id_nodo` = B.`id_nodo`
	// 						WHERE B.`tipo` IN (". implode (", ", $nodeTypesArray) .")";
	// 						$sql .= " AND `data_creazione`>" . $maxData  ;
	// 						$sql .= " AND A.`id_utente_studente` =".$userId;
	// 						$sql .= " AND B.`id_nodo` = '".$nodeId."'";
	// 						$sql .= " AND B.`livello`<=" . $studentlevel;

	// //  						print_r("<hr/>".$sql."<hr/>");


	// 						$tempresults = $db->getAll($sql, null, AMA_FETCH_ASSOC );
	// 						if (!empty($tempresults)) foreach ($tempresults as $tempresult)  array_push ($othernewnodes,$tempresult);

	//  						print_r ($othernewnodes);
	// 					}
	// 					die();

	// 					$retarray = array_merge ($nevervisitednodes, $othernewnodes);
	    			} // if hasbeenvisited
	    		} // foreach instancesarray
    		}

					return $result;


//     		// get id_corso of courses for which user has subscribed
//     		$sql = "SELECT DISTINCT(B.`id_corso`)
//     				FROM `iscrizioni` A, `istanza_corso` B
//     				WHERE A.`id_utente_studente` =$userId
//     				AND A.`id_istanza_corso` = B.`id_istanza_corso`";

//     		$subscribedId = $db->getAll ($sql);

//     		$regexp = '';
//     		foreach ($subscribedId as $num=>$a)
//     		{
//     			foreach ( $a as $val )
//     			{
//     				$regexp .= $val."_";
//     			}
//     			if ($num < count($subscribedId)-1) $regexp .= "|";
//     		}

//     		// regexp now contains a regular expression with all course_id user is subscriped to..
//     		// e.g 102_|107_ this will be used to compare agains id_nodo in nodo table.

//     		// get course id, if none found return 0
// //     		if ($courseId = $this->get_course_id_for_course_instance($courseInstanceId))
// //     		{
// 	    		// select row into table nodo
// 	    		$sql =  "SELECT DISTINCT(n.`id_nodo`) AS `id_nodo` , n.`id_istanza`, n.`nome`
// 	    		FROM `nodo` n
// 	    		JOIN
// 	    		(
// 	    		SELECT `data_visita`
// 	    		FROM `history_nodi`
// 	    		WHERE `id_utente_studente` = $userId
// 	    		ORDER BY `data_visita` DESC
// 	    		LIMIT 1
// 	    		) h ON (h.`data_visita` < n.`data_creazione`)";

// 	    		$sql .= " WHERE n.`tipo` IN (". implode(",", $nodeTypesArray) .")";

// 	    		if ($regexp !== '') $sql .= " AND n.`id_nodo` REGEXP '$regexp'";

// 	    		$sql .= " ORDER BY n.`data_creazione` DESC";



// 	    		// new query
// // 	    		$sql ="SELECT DISTINCT(B.`id_nodo`) AS `id_nodo` , B.`id_istanza`, B.`nome`
// // 	    		FROM `nodo` B LEFT JOIN `history_nodi` A ON A.`id_nodo` = B.`id_nodo`
// // 	    		WHERE B.`tipo` IN (". implode (", ", $nodeTypesArray) .")
// // 	    		AND (`data_creazione`>=`data_visita` OR ISNULL(`data_visita`))";

// // 	    		if ($regexp!== '') $sql .= " AND B.`id_nodo` REGEXP '". $regexp ."'";

// // 	    		$sql .= " ORDER BY `data_creazione` DESC";


// 				// $sql .= " AND n.`id_nodo` LIKE '".$courseId."_%'";
// 				if ($maxNodes > 0) $sql .= " LIMIT ".$maxNodes;

// 	    		$result =  $db->getAll($sql, null, AMA_FETCH_ASSOC );

// 	    		if(AMA_DB::isError($result)) {
// 	    		return new AMA_Error(AMA_ERR_GET);
// 	    		}
// //     		}
// //     		else  $result=null;
//     		return $result;
    	}

    public function get_registered_students_without_tutor() {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // select row into table tuto_studenti
        //$sql =  "SELECT COUNT(id_istanza_corso) FROM istanza_corso WHERE data_inizio < $timestamp AND data_fine > $timestamp";
        $sql = 'SELECT U.nome, U.cognome, U.tipo, U.username
		    FROM utente AS U, iscrizioni AS I, istanza_corso AS IC
			WHERE U.tipo =' . AMA_TYPE_STUDENT . ' AND U.stato = '. ADA_STATUS_REGISTERED
                . ' AND I.id_utente_studente = U.id_utente
			AND IC.id_istanza_corso = I.id_istanza_corso
			AND IC.data_inizio = 0';

        $result =  $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }
    /**
     * Methods accessing table `utente`
     */
    // MARK: Methods accessing table `utente`

    /**
     *
     * @param $user_dataAr
     * @return unknown_type
     */
    public function add_user($user_dataAr=array()) {

        /*
     * Before inserting a row, check if a user with this username already exists
        */
        $user_id_sql = 'SELECT id_utente FROM utente WHERE username=?';
        $user_id = $this->getOnePrepared($user_id_sql,array($user_dataAr['username']));
        if (AMA_DB::isError($user_id)) {
            return $user_id;
        }
        elseif ($user_id) {
            return new AMA_Error(AMA_ERR_UNIQUE_KEY);
        }

        $add_user_sql = 'INSERT INTO utente(id_utente,nome,cognome,tipo,e_mail,username,password,layout,
                               indirizzo,citta,provincia,nazione,codice_fiscale,birthdate,sesso,
                               telefono,stato,lingua,timezone,cap,matricola,avatar,birthcity,birthprovince)
                 VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $values = array(
                $user_dataAr['id_utente'],
                $user_dataAr['nome'],
                $user_dataAr['cognome'],
                $user_dataAr['tipo'],
                $user_dataAr['e_mail'],
                $user_dataAr['username'],
                //sha1($user_dataAr['password']),
                $user_dataAr['password'], // sha1 encoded
                $user_dataAr['layout'],
                $this->or_null($user_dataAr['indirizzo']),
                $this->or_null($user_dataAr['citta']),
                $this->or_null($user_dataAr['provincia']),
                $this->or_null($user_dataAr['nazione']),
                $this->or_null($user_dataAr['codice_fiscale']),
                $this->or_zero($this->date_to_ts($user_dataAr['birthdate'])),
                $this->or_null($user_dataAr['sesso']),
                $this->or_null($user_dataAr['telefono']),
                $user_dataAr['stato'],
                $user_dataAr['lingua'],
                $user_dataAr['timezone'],
                $user_dataAr['cap'],
                $user_dataAr['matricola'],
                $user_dataAr['avatar'],
        		$user_dataAr['birthcity'],
        		$user_dataAr['birthprovince']
        );
        /*
     * Adds the user
        */
        $result = $this->executeCriticalPrepared($add_user_sql,$values);
        if (AMA_DB::isError($result)) {
            return $result;
        }

//    /*
//     * Return the user id of the inserted user
//     */
//    $user_id_sql = 'SELECT id_utente FROM utente WHERE username=?';
//    $user_id = $this->getOnePrepared($user_id_sql, $user_dataAr['username']);
//    if (AMA_DB::isError($user_id)) {
//      return new AMA_Error(AMA_ERR_GET);
//    }
//
//    return $user_id;
        return true;
    }


    /**
     * Get all informations about a user
     *
     * @access private
     *
     * @param $id the user's id
     *
     * @return an array containing all the informations about a user
     *        res_ha['nome']
     *        res_ha['cognome']
     *        res_ha['tipo']
     *        res_ha['e-mail']
     *        res_ha['telefono']
     *        res_ha['username']
     *        res_ha['password']
     */
    // vito 7/9/09
    //private function _get_user_info($id) {
    public function _get_user_info($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table UTENTE
        $query = "select nome, cognome, tipo, e_mail AS email, telefono, username, layout, ".
                "indirizzo, citta, provincia, nazione, codice_fiscale, birthdate, sesso, ".
                "telefono, stato, lingua, timezone, cap, matricola, avatar, birthcity, birthprovince  from utente where id_utente=$id";
        $res_ar =  $db->getRow($query, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $res_ar['id'] = $id;
        if (isset($res_ar['birthdate'])) $res_ar['birthdate'] = ts2dFN($res_ar['birthdate']);
        return $res_ar;
    }

    // FIXME: forse deve essere pubblico
    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return unknown_type
     */
    public function _get_student_level($id_user,$id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        if (empty($id_course_instance)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // get a row from table iscrizioni
        // FIXME: usare getOne al posto di getRow
        $res_ar =  $db->getRow("select livello from iscrizioni where id_utente_studente=$id_user and  id_istanza_corso=$id_course_instance");
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $res_ar[0];
    }

    /**
     * Get type of a user
     *
     * @access public
     *
     * @param $id the user's id
     *
     * @return an INT (1,2,3,4) or Error
     */
    public function get_user_type($id) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $result =  $db->getOne("select tipo from utente where id_utente=$id");
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($result)) { //OR is_object($res_ar)){
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        return $result;
    }

    /**
     * Methods accessing table `utente_chatroom`
     * @see ChatRoom.inc.php
     */
    // MARK: Methods accessing table `utente_chatroom`

    /**
     * Methods accessing table `utente_chatroom_log`
     * @see ChatRoom.inc.php
     */
    // MARK: Methods accessing table `utente_chatroom_log`

    /**
     * Methods accessing table `utente_log`
     * @see
     */
    // MARK: Methods accessing table `utente_log`

    /**
     * Methods accessing table `utente_messaggio_log`
     * @see
     */
    // MARK: Methods accessing table `utente_messaggio_log`


    /**
     * Methods accessing table `openmeetings_room`
     */
    // MARK: Methods accessing table `openmeetings_room`
    /**
     *
     * @param $videoroom_dataAr
     * @return unknown_type
     */
    public function add_videoroom($videoroom_dataAr=array()) {

        $add_room_sql = 'INSERT INTO openmeetings_room(id_room,id_istanza_corso,id_tutor,
    				           tipo_videochat, descrizione_videochat, tempo_avvio, tempo_fine)
                 VALUES(?,?,?,?,?,?,?)';

        $values = array(
                $videoroom_dataAr['id_room'],
                $videoroom_dataAr['id_istanza_corso'],
                $videoroom_dataAr['id_tutor'],
                $videoroom_dataAr['tipo_videochat'],
                $videoroom_dataAr['descrizione_videochat'],
                $videoroom_dataAr['tempo_avvio'],
                $videoroom_dataAr['tempo_fine']
        );
        /*
     * Adds the room
        */
        $result = $this->executeCriticalPrepared($add_room_sql,$values);
        if (AMA_DB::isError($result)) {
            return $result;
        }
        return true;
    }


    /**
     * Get all informations about a videoroom
     *
     * @access public
     *
     * @param $id_istanza_corso the id instance course
     *
     * @return an array containing all the informations about a videoroom
     *        res_ar['id']
     *        res_ar['id_room']
     *        res_ar['id_istanza_corso']
     *        res_ar['id_tutor']
     *        res_ar['tipo_videochat']
     *        res_ar['descrizione_videochat']
     *        res_ar['tempo_avvio']
     *        res_ar['tempo_fine']
     */

    public function get_videoroom_info($id_course_instance, $ora_attuale= NULL) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table OPENMEETINGS_ROOM
        $query = "select id, id_room, id_istanza_corso, id_tutor, tipo_videochat, descrizione_videochat, tempo_avvio, tempo_fine
             from openmeetings_room where id_istanza_corso=$id_course_instance";
        if ($ora_attuale != NULL) {
            $where_more = " and tempo_avvio<=$ora_attuale and $ora_attuale<=tempo_fine";
            $query .= $where_more;
        }
        $res_ar =  $db->getRow($query, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($res_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        if (empty($res_ar) OR is_object($res_ar)) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }
        /*
    $res_ha['id']         = $id;
    $res_ha['nome']       = $res_ar[0];
    $res_ha['cognome']    = $res_ar[1];
    $res_ha['tipo']       = $res_ar[2];
    $res_ha['email']      = $res_ar[3];
    $res_ha['telefono']   = $res_ar[4];
    $res_ha['username']   = $res_ar[5];
    $res_ha['template_family']   = $res_ar[6];

    return $res_ha;
        */
        return $res_ar;
    }

    /**
     *
     * @param $id_romm
     * @return unknown_type
     */
    public function delete_videoroom($id_room) {

        $sql = "DELETE FROM openmeetings_room WHERE id_room = $id_room";

        $res = $this->executeCritical( $sql );
        if (AMA_DB::isError($res)) {
            // $res is ana AMA_Error object
            return $res;
        }
        return true;
    }



    public function get_tester_services_not_started() {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT U1.id_utente, U1.nome, U1.cognome, MC.titolo, I.status,
                   IC.id_istanza_corso, IC.id_corso, IC.data_inizio_previsto AS data_richiesta
              FROM utente AS U1, modello_corso AS MC, iscrizioni AS I, istanza_corso AS IC
             WHERE IC.data_inizio = 0 AND MC.id_corso = IC.id_corso
               AND I.id_istanza_corso = IC.id_istanza_corso
               AND U1.id_utente = I.id_utente_studente
               AND U1.stato=' . ADA_STATUS_REGISTERED
                .' ORDER BY IC.id_istanza_corso DESC';

        $resultAr = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $resultAr;
    }
    public function get_tester_services_started() {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT U1.id_utente, U1.nome, U1.cognome, MC.titolo, I.status,
                   U2.id_utente AS id_tutor, U2.nome AS nome_t, U2.cognome AS cognome_t, U2.username AS username_t,
                   IC.id_istanza_corso, IC.id_corso, IC.data_inizio_previsto AS data_richiesta
              FROM utente AS U1, utente AS U2, modello_corso AS MC, iscrizioni AS I, istanza_corso AS IC, tutor_studenti AS TS
             WHERE IC.data_inizio > 0 AND MC.id_corso = IC.id_corso
               AND I.id_istanza_corso = IC.id_istanza_corso
               AND U1.id_utente = I.id_utente_studente
               AND TS.id_istanza_corso = IC.id_istanza_corso
               AND U2.id_utente = TS.id_utente_tutor
               ORDER BY IC.id_istanza_corso DESC';

        $resultAr = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $resultAr;
    }

  /*
   * Get type of all services
   *
   * @access public
   *
   * @ return an array: id_type_service, type_service(=service_level), name_service,description, custom fields
   *
   * @return an error if something goes wrong
   *
   */
    public function get_service_type($id_user=NULL) {

    $service_sql = "SELECT id_tipo_servizio, livello_servizio,nome_servizio,descrizione_servizio,custom_1,custom_2,custom_3,hiddenFromInfo,isPublic  FROM service_type";
    $common_dh = AMA_Common_DataHandler::instance();

    /* if isset $id_user it means that the admin is asking data for log_report.php, and he have to take data from common db */
    if(isset($id_user)){
        $db=array($common_dh);
    }else{
        $db=array($this,$common_dh);
    }

    foreach ($db as $dbToUse) {
        $service_result = $dbToUse->getAllPrepared($service_sql, NULL, AMA_FETCH_ASSOC);
        if (!AMA_DB::isError($service_result) && $service_result!==false && count($service_result)>0) {
            break;
        }
    }

    if(AMA_DB::isError($service_result)) {
        return new AMA_Error(AMA_ERR_GET);
    }

    return $service_result;
    }

    public function get_number_of_tutored_users($id_tutor) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT count(id_istanza_corso) FROM tutor_studenti WHERE id_utente_tutor='.$id_tutor;

        $result = $db->getOne($sql);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    public function get_tutored_user_ids($id_tutor) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = "SELECT distinct(I.id_utente_studente)
              FROM tutor_studenti AS TS, iscrizioni AS I
             WHERE id_utente_tutor=$id_tutor
               AND I.id_istanza_corso=TS.id_istanza_corso";

        $result = $db->getCol($sql);
        if(AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }

    public function get_list_of_tutored_users($id_tutor) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = 'SELECT U1.id_utente, U1.username, U1.nome, U1.cognome, U1.tipo, MC.titolo, I.status,
                   IC.id_corso, IC.id_istanza_corso, IC.data_inizio, IC.durata, IC.data_fine
          FROM utente AS U1, modello_corso AS MC, iscrizioni AS I, istanza_corso AS IC, tutor_studenti AS TS
         WHERE TS.id_utente_tutor ='.$id_tutor.'
           AND IC.id_istanza_corso = TS.id_istanza_corso
           AND MC.id_corso = IC.id_corso
           AND I.id_istanza_corso = IC.id_istanza_corso
           AND U1.id_utente = I.id_utente_studente
           ';
        $resultAr = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $resultAr;
    }

    public function get_list_of_tutored_unique_users($id_tutor) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;
        $sql = 'SELECT U1.id_utente, U1.username, U1.nome, U1.cognome, U1.tipo
                FROM utente AS U1
                JOIN
                (SELECT DISTINCT I.id_utente_studente FROM
                iscrizioni AS I, istanza_corso AS IC, tutor_studenti AS TS
                WHERE TS.id_utente_tutor ='.$id_tutor.'
                    AND IC.id_istanza_corso = TS.id_istanza_corso
                    AND I.id_istanza_corso = IC.id_istanza_corso)
                AS U2 ON (U1.id_utente = U2.id_utente_studente)
                ';
        $resultAr = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if(AMA_DB::isError($resultAr)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $resultAr;

    }

    public function get_users_by_type($user_type=array(), $retrieve_extended_data=false) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $type = implode(',', $user_type);
        if ($retrieve_extended_data) {
            $sql = "SELECT nome, cognome, tipo, username, e_mail FROM utente WHERE tipo IN ($type) ORDER BY cognome ASC";
        } else {
            $sql = "SELECT tipo, username FROM utente WHERE tipo IN ($type)";
        }

        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function get_users_by_type_from_position_to_position($user_type=array(),$start, $count) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $type = implode(',', $user_type);

        $sql = "SELECT id_utente, nome, cognome, e_mail, username, tipo FROM utente WHERE tipo IN ($type) LIMIT $start,$count";
        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    public function count_users_by_type($user_type=array()) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $type = implode(',', $user_type);

        $sql = "SELECT COUNT(id_utente) FROM utente WHERE tipo IN ($type)";
        $result = $db->getOne($sql);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }


    public function get_tutors_for_student($id_student) {
        $db =& $this->getConnection();
        if (AMA_DB::isError($db)) return $db;

        $sql = "SELECT U.tipo, U.username, U.nome, U.cognome, U.avatar FROM utente AS U, iscrizioni AS I, tutor_studenti AS T
    		 WHERE I.id_utente_studente=$id_student AND T.id_istanza_corso = I.id_istanza_corso
    		 AND U.id_utente = T.id_utente_tutor";
        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * Get a list of admins' fields from the DB
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     * @see find_admins_list
     */
    public function &get_admins_list($field_list_ar = array()) {
        return $this->find_admins_list($field_list_ar);
    }

    /**
     * Get a list of admins' ids from the DB
     *
     * @access public
     *
     * @return an array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     *
     * @see find_admins_list, get_admins_list
     */
    public function &get_admins_ids() {
        return $this->get_admins_list();
    }
    /**
     * Get those admins' ids verifying the given criterium
     *
     * @access public
     *
     * @param $field_list_ar an array containing the desired fields' names
     *        possible values are: nome, cognome, e-mail, username, password, telefono, profilo, tariffa
     *
     * @param  clause the clause string which will be added to the select
     *
     * @return a nested array containing the list, or an AMA_Error object or a DB_Error object if something goes wrong
     * The form of the nested array is:
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     */
    public function &find_admins_list($field_list_ar = array(), $clause='') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // build comma separated string out of $field_list_ar array
        if (count($field_list_ar)) {
            $more_fields = ', '.implode(', ', $field_list_ar);
        }
        // handle null clause, too
        if ($clause) {
            $clause = ' AND '.$clause;
        }

        // do the query
        $sql_query="select id_utente$more_fields from utente where  tipo=".AMA_TYPE_ADMIN." $clause";
        $admins_ar =  $db->getAll($sql_query);
        if (AMA_DB::isError($tutors_ar)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        //
        // return nested array in the form
        //
        return $admins_ar;
    }

    public function get_admin($id) {
        // get a row from table UTENTE
        $get_user_result = $this->_get_user_info($id);
        if(AMA_Common_DataHandler::isError($get_user_result)) {
            // $get_user_result is an AMA_Error object
            //?
        }
        return $get_user_result;
    }


 /** Sara -14/01/2015
   * get some log data for a given tester
   * @return  $res_ar array
   */
    public function tester_log_report($tester = 'default',$Services_TypeAr=NULL) {

    if (defined('CONFIG_LOG_REPORT') && CONFIG_LOG_REPORT && is_array($GLOBALS['LogReport_Array']) && count($GLOBALS['LogReport_Array']) ){
        $res_ar = array();
        $sql = array();
        if(isset($Services_TypeAr)){
            $Services_Type=$Services_TypeAr;
        }elseif(isset($_SESSION['service_level'])){
            $Services_Type=$_SESSION['service_level'];
        }
        foreach($GLOBALS['LogReport_Array'] as $key=>$value){
        /* if a case fails or a query return error, the corresponding column will not appear in log report table */
            switch($key){

                case 'final_users':
                    $sql[$key]="SELECT COUNT(`id_utente`) `tipo` FROM `utente` WHERE `tipo` = ". AMA_TYPE_STUDENT;
                    break;
                case 'user_subscribed':
                    $sql[$key]="SELECT COUNT(DISTINCT(`id_utente_studente`))  FROM `iscrizioni` WHERE `status` IN (". ADA_STATUS_SUBSCRIBED.",".ADA_STATUS_TERMINATED.")";
                    break;
                case 'course':
                    $sql[$key]="SELECT COUNT(`id_corso`) FROM `modello_corso`";
                    break;
                case 'service_level':
                    if(isset($Services_Type)){
                        foreach($Services_Type as $keyService_level=>$value){
                            $sql['course_'.$keyService_level]="SELECT COUNT(`id_corso`) FROM `modello_corso` where `tipo_servizio`=$keyService_level";
                        }

                    }
                    break;
                case 'sessions_started':
                    $sql[$key]="SELECT COUNT(`id_istanza_corso`) FROM `istanza_corso` WHERE `data_inizio` > 0 AND `data_fine` >". time();
                    break;
                case'student_subscribedStatus_sessStarted':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status` IN (".ADA_STATUS_SUBSCRIBED.",".ADA_STATUS_TERMINATED.") AND ic.`data_inizio` > 0 AND ic.`data_fine` >". time();
                    break;
                case 'student_CompletedStatus_sessStarted':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status`= ".ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED." AND ic.`data_inizio` > 0 AND ic.`data_fine` >". time();
                    break;
                case 'sessions_closed':
                    $sql[$key]="SELECT COUNT(`id_istanza_corso`) FROM `istanza_corso` WHERE `data_fine` <= " . time();
                    break;
                case'student_subscribedStatus_sessEnd':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status` IN(".ADA_STATUS_SUBSCRIBED.",".ADA_STATUS_TERMINATED.") AND ic.`data_inizio` > 0 AND ic.`data_fine` <=". time();
                    break;
                case 'student_CompletedStatus_sessionEnd':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status`= ".ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED." AND ic.`data_inizio` > 0 AND ic.`data_fine` <=". time();
                    break;
                case 'tot_student_subscribedStatus':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic  WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status` IN (".ADA_STATUS_SUBSCRIBED.','.ADA_STATUS_TERMINATED.') AND ic.`data_inizio` > 0' ;
                    break;
                case 'tot_student_CompletedStatus':
                    $sql[$key]="SELECT COUNT(`id_utente_studente`) FROM `iscrizioni` AS i,`istanza_corso` AS ic  WHERE i.`id_istanza_corso`= ic.`id_istanza_corso` AND i.`status`=".ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED.' AND ic.`data_inizio` > 0' ;
                    break;
                case 'tot_Session':
                    $sql[$key]="SELECT COUNT(`id_istanza_corso`) FROM `istanza_corso`";
                    break;
                case 'visits':
                    $sql[$key]="SELECT COUNT(`id_history`) FROM `history_nodi` AS hn JOIN `studente` AS st ON hn.id_utente_studente = st.id_utente_studente";
                    break;
                case 'system_messages':
                    $sql[$key]="SELECT COUNT(`id_messaggio`) FROM `messaggi` WHERE `tipo` = '". ADA_MSG_SIMPLE ."'"  ;
                    break;
                case 'chatrooms':
                    $sql[$key]="SELECT COUNT(`id_chatroom`) FROM `chatroom`";
                    break;
                case 'videochatrooms':
                    $sql[$key]="SELECT COUNT(`id`) FROM `openmeetings_room`";
                    break;
            /* Return array of this method must have this key otherwise the corresponding columns will not appear in log-report table */
                case 'student_CompletedStatus_sessStarted_Rate':
                case 'student_CompletedStatus_sessionEnd_Rate':
                case 'tot_student_CompletedStatus_Rate':
                $sql[$key]="SELECT -1";
                    break;
            }

        }
    }

    $db =& $this->getConnection();
    if ( AMA_DB::isError( $db ) ) return $db;
    $res_ar['provider'] = $tester;
    foreach ($sql as $type => $query){
        $res =  $db->getOne($query);
        if(!AMA_DataHandler::isError($res)) {
            $res_ar[$type] = $res;
        }
    }
    return $res_ar;
}


    /**
     * Methods accessing table `banner`
     */
    // MARK: Methods accessing table `banner`

    /**
     * Get banner info from DB
     *
     * @access public
     *
     * @param $id_banner
     *
     * @return an array
     */

    function get_banner($id_banner) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT 'address','image','id_client','id_course','module','keywords','impressions','a_impressions','date_from','date_to' from banner
   	 	WHERE `id_banner`  =  $id_banner";

        $res = $db->getOne($sql, NULL, AMA_FETCH_ASSOC);

        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_NOT_FOUND) .
                            " while in get_banner");
        }

        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .
                            " while in get_banner");
        }
        return $res;
    }

    /**
     * Find banner info from DB
     *
     * @access private
     *
     * @param $out_fields_ar,$clause
     *
     * @return an array
     */
    function &_find_banner_list($out_fields_ar, $clause='') {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;


        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar)) {
            $more_fields = ', '.implode(', ', $out_fields_ar);
        }

        // add a 'where' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }

        // do the query
        $res_ar =  $db->getAll("select id_banner$more_fields from banner $clause order by id_banner");

        if (AMA_DB::isError($res_ar)) {
            return $res_ar;
        }
        return $res_ar;
    }

    /**
     * Find banner info from DB
     *
     * @access public
     *
     * @param $out_fields_ar,$module,$keywords,$client
     *
     * @return an array
     */
    function &find_banner_list($out_fields_ar, $module="", $keywords="", $client='') {
        // build the clause
        $clause = '';

        if ($module) {
            $clause .= "$module =".$this->sql_prepared($module);
        }

        if ($keywords) {
            if ($clause) {
                $clause .= ' and ';
            }

            $clause .= "keywords LIKE ".$this->sql_prepared($keywords);
        }

        if ($client) {
            if ($clause) {
                $clause .= ' and ';
            }

            $clause .= "client =".$this->sql_prepared($client);
        }
        // invokes the private method to get all the records
        return $this->_find_banner_list($out_fields_ar, $clause);
    }

    /**
     * Update impression count
     *
     * @access public
     *
     * @param $id_banner,$id_user,$ymdhms
     *
     * @return an array
     */

    function add_userclick($id_banner,$id_user,$ymdhms) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT 'impressions','a_impressions', 'date_to' from banner
   	 	WHERE `id_banner`  =  $id_banner";

        $res = $db->getOne($sql);

        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_NOT_FOUND) .
                            " while in add_userclick");
        }

        $impressions = $res[0] + 1;
        $acquired_impressions = $res[1];
        $date_to = $res[2];
        if (($impressions < $acquired_impressions) AND ($ymdhms <= $date_to)) {
            $sql = "UPDATE banner SET `impressions`= $impressions WHERE `id_banner`  =  $id_banner";
            $res = $db->query($sql);
            if (AMA_DB::isError($res)) {
                return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .
                                " while in add_userclick");
            }
            return $res;
        } else {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .
                            " while in add_userclick");
        }
    }

    /**
     * Methods accessing table `history_esercizi`
     */
    // MARK: Methods accessing table `history_esercizi`

    /**
     * Add an item  to table history_esercizi
     * Useful during the navigation. The date of the visit is computed automatically.
     *
     * @access public
     *
     * @param $student_id   the id of the student
     * @param $course_id    the id of the instance of course the student is navigating
     * @param $node_id      the node to be registered in the history
     * @param $answer       the answer in case of free answer (a text)
     * @param $remark       a remark to send to the tutor, together with the answer
     * @param $points       the points the tutor assign to the answer (filled by tutor)
     * @param $correction   a textual correction of the free answer (filled by tutor)
     * @param $ripetibile   0 = the student cannot repeat the exercise
     *                      1 = the student can repeat the exercise
     * @param $attach       the file name of attach
     *
     */
    function add_ex_history($student_id, $course_instance_id, $node_id, $answer='', $remark='', $points=0, $correction='', $ripetibile=0, $attach='') {

        $sql = 'INSERT INTO history_esercizi (id_utente_studente, id_istanza_corso, id_nodo, data_visita, data_uscita,'
             . ' risposta_libera, commento, punteggio, correzione_risposta_libera, ripetibile, allegato)'
             . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $valuesAr = array(
            $student_id,
            $course_instance_id,
            $node_id,
            time(),
            time(),
            $answer,
            $remark,
            $points,
            $correction,
            $ripetibile,
            $attach
        );

        $result = $this->queryPrepared($sql, $valuesAr);
        if (AMA_DB::isError($result)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) .
                                 ' while in add_ex_history');
        }

        return true;
    }



    /**
     * Get all informations related to a given exercises history row.
     *
     * @access public
     *
     * @param $ex_history_id
     *
     * @return an hash with the fields
     *         the keys are:
     * node_id            - the id of the bookmarked node
     * student_id         - the id of the student
     * course_id          - the id of the instance of the course  the student is following
     * visit_date         - the moment of the visit
     * exit_date          - the moment the user left the node (?)
     * answer             - the free answer
     * remark             - a comment
     * points             - points assigned
     * correction         - a correction to a free answer
     * ripetibile         - 0 = the student cannot repeat the exercise
     * $attach            - the file name of attach
     *
     */
    function get_ex_history_info($ex_history_id) {

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;

        // get a row from table history_nodi
        $sql  = "select id_history_ex, id_nodo, id_utente_studente, id_istanza_corso, data_visita, data_uscita,";
        $sql .= "risposta_libera, commento, punteggio, correzione_risposta_libera, ripetibile, allegato";
        $sql .= " from history_esercizi where id_history_ex=$ex_history_id";
        $res_ar =  $db->getRow($sql);
        if (AMA_DB::isError($res_ar))
            return $res_ar;

        if (!$res_ar) {
            return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        $res_ha['exe_id']       = $res_ar[0];
        $res_ha['node_id']      = $res_ar[1];
        $res_ha['student_id']   = $res_ar[2];
        $res_ha['course_id']    = $res_ar[3];
        $res_ha['visit_date']   = self::ts_to_date($res_ar[4]);
        $res_ha['exit_date']    = self::ts_to_date($res_ar[5]);
        $res_ha['answer']       = $res_ar[6];
        $res_ha['remark']       = $res_ar[7];
        $res_ha['points']       = $res_ar[8];
        $res_ha['correction']   = $res_ar[9];
        $res_ha['ripetibile']   = $res_ar[10];
        $res_ha['allegato']     = $res_ar[11];


        /*
       global $debug; $debug=1;
       mydebug(__LINE__,__FILE__,$res_ar);
       $debug=0;
        */



        return $res_ha;


    }



    /**
     * Get exercises history informations which satisfy a given clause
     * Only the fields specifiedin the $out_fields_ar parameter are inserted
     * in the result set.
     * This function is meant to be used by the public find_nodes_history_list()
     *
     * @access private
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $clause
     *
     *     array(array(ID1, 'field_1_1', 'field_1_2'),
     *           array(ID2, 'field_2_1', 'field_2_2'),
     *           array(ID3, 'field_3_1', 'field_3_2'))
     *
     */
    function &_find_ex_history_list($out_fields_ar, $clause) {

        $more_fields = "";
        // build comma separated string out of $field_list_ar array
        if (count($out_fields_ar))
        {
			foreach($out_fields_ar as $k=>&$v) $v = 'he.`'.$v.'`';
            $more_fields = ', '.implode(', ', $out_fields_ar);
		}

        //tries to connect to db
        $db =& $this->getConnection();
        // if something goes wrong, $db is an error object
        // so we return the error object
        if ( AMA_DB::isError( $db ) ) return $db;



        // add a 'where' on top of the clause
        // handle null clause, too
        if ($clause) {
            $clause = 'where '.$clause;
        }

        // do the query
        $sql = "SELECT n.`nome`, n.`titolo`, n.`id_nodo_parent`,
						he.`id_history_ex`$more_fields
				FROM `history_esercizi` he
				JOIN `nodo` n ON (n.`id_nodo` = he.`ID_NODO`)
				$clause
				ORDER BY he.`id_history_ex` DESC";

        $res_ar =  $db->getAll($sql);
        if (AMA_DB::isError($res_ar))
            return $res_ar;

        //
        // return nested array
        //
        return $res_ar;

    }

    function &find_exercise_history_for_course_instance($exercise_id, $course_instance_id) {
        $db =& $this->getConnection();
        if (AMA_DB::isError( $db )) {
            return $db;
        }

        $sql = "SELECT id_history_ex, id_utente_studente FROM history_esercizi WHERE id_nodo='$exercise_id' AND id_istanza_corso=$course_instance_id";
        $result = $db->getAll($sql, NULL, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    function find_exercise_history_for_user($exercise_id, $course_instance_id, $user_id) {

        $sql = 'SELECT * FROM history_esercizi WHERE id_utente_studente = ? AND id_nodo = ? AND id_istanza_corso = ?';
        $values = array(
            $user_id,
            $exercise_id,
            $course_instance_id
        );

        $result = $this->getRowPrepared($sql, $values, AMA_FETCH_ASSOC);
        if(AMA_DataHandler::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }
        return $result;
    }
    /**
     * Get exercises history informations.
     * Returns all the history informations without filtering. Only the fields specified
     * in the $out_fields_ar parameter are inserted in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @return a bi-dimensional array containing the fields as specified
     *
     * @see
     *
     */
    function &get_ex_history_list($out_fields_ar) {
        return $this->_find_ex_history_list($out_fields_ar);

    }



    /**
     * Get exercises history informations for a given student, course instance or both
     * Returns all the history informations filtering on students, courses or both.
     * If a parameter has the value '*', then it is not filtered.
     * Only the fields specified
     * in the $out_fields_ar parameter are inserted in the result set.
     *
     * @access public
     *
     * @param $out_fields_ar array of the fields returned
     *
     * @param $student_id
     * @param $course_instance_id
     * @param $node_id
     *
     * @return a bi-dimensional array containing the fields as specified.
     *
     * @see
     *
     */
    function &find_ex_history_list($out_fields_ar, $student_id=0, $course_instance_id=0, $node_id='') {
        // build the clause
        $clause = '';

        if ($student_id)
            $student_id_prep = $this->sql_prepared($student_id);
        $clause .= "id_utente_studente = $student_id_prep";



        if ($course_instance_id) {
            if ($clause)
                $clause .= ' and ';

            $course_istance_id_prep = $this->sql_prepared($course_instance_id);
            $clause .= "id_istanza_corso = $course_istance_id_prep";
        }


        if ($node_id) {
            if ($clause)
                $clause .= ' and ';

            $node_id_prep = $this->sql_prepared($node_id);
            $clause .= "n.id_nodo = $node_id_prep";
        }

        // if ($clause)
        //         $clause = ' where '.$clause;


        // invokes the private method to get all the records
        return $this->_find_ex_history_list($out_fields_ar, $clause);

    }

    function get_ex_report($id_node, $id_course_instance) {
        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        $sql = "SELECT risposta_libera, punteggio, count(risposta_libera) AS risposte
   	 	          FROM history_esercizi
   	 	         WHERE id_istanza_corso=$id_course_instance
   	 	           AND id_nodo = '$id_node' GROUP BY risposta_libera";
        $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_GET);
        }

        return $result;
    }

    /**
     * Update informations of a record in the history_ex table
     *
     * @access public
     *
     * @param $id            - the id of the history_ex voice to modify
     * @param $history_ex_ha - the informations in a hash with keys:
     *                         commento, punteggio, correzione_risposta_libera,da_ripetere
     *
     *
     * @return an error if something goes wrong
     *
     */
    function set_ex_history($id, $history_ex_ha) {

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;
        // verify that the record exists and store old values for rollback
        $res_id =  $db->getRow("select id_history_ex from history_esercizi where id_history_ex=$id");

        if (AMA_DB::isError($res_id)) {
            return $res_id;
        }
        if ($res_id == 0) {
           return new AMA_Error(AMA_ERR_NOT_FOUND);
        }

        // get old values
        $old_ha = $this->get_ex_history_info($id);
        if (AMA_DB::isError($old_ha)) {
            //
        }

        if (isset($history_ex_ha['risposta_libera'])) {
            $risposta_libera = $history_ex_ha['risposta_libera'];
        } else {
            $risposta_libera = $old_ha['answer'];
        }

        if (isset($history_ex_ha['commento'])) {
            $commento = $history_ex_ha['commento'];
        } else {
            $commento = $old_ha['remark'];
        }

        if (isset($history_ex_ha['punteggio'])) {
            $punteggio = $history_ex_ha['punteggio'];
        } else {
            $punteggio = $old_ha['points'];
        }

        if (isset($history_ex_ha['correzione'])) {
            $correzione = $history_ex_ha['correzione'];
        } else {
            $correzione = $old_ha['correction'];
        }

        if (isset($history_ex_ha['da_ripetere'])) {
            $ripetibile = $history_ex_ha['da_ripetere'];
        } else {
            $ripetibile = $old_ha['ripetibile'];
        }

        if (isset($history_ex_ha['allegato'])) {
            $allegato = $history_ex_ha['allegato'];
        } else {
            $allegato = $old_ha['allegato'];
        }

        $values = array(
            $risposta_libera,
            $commento,
            $punteggio,
            $correzione,
            $ripetibile,
            $allegato,
            $id
        );


        $sql = 'UPDATE history_esercizi'
             . ' SET risposta_libera = ?, commento = ?, punteggio = ?, correzione_risposta_libera = ?, ripetibile = ?, allegato = ?'
             . ' WHERE id_history_ex = ?';

        $result = $this->queryPrepared($sql, $values);
        if (AMA_DB::isError($result)) {
            return new AMA_Error(AMA_ERR_UPDATE);
        }
        return true;
    }

   /*
    * Exercise functions
    */

    // vito
    function get_exercise_type( $id_node )
    {
      $db =& $this->getConnection();
      // if something goes wrong, $db is an error object
      // so we return the error object
      if ( AMA_DB::isError( $db ) ) return $db;
      $sql = "SELECT tipo FROM nodo WHERE id_nodo='$id_node'";
      $result = $db->getRow($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }
    function get_exercise_answers ( $id_node )
    {
      $db =& $this->getConnection();
      // if something goes wrong, $db is an error object
      // so we return the error object
      if ( AMA_DB::isError( $db ) ) return $db;
      $sql = "SELECT id_nodo, nome, titolo, testo, tipo, ordine, correttezza FROM nodo WHERE id_nodo_parent='$id_node'";
      $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }
    function get_exercise_answer( $id_node )
    {
      $db =& $this->getConnection();
      // if something goes wrong, $db is an error object
      // so we return the error object
      if ( AMA_DB::isError( $db ) ) return $db;
      $sql = "SELECT id_nodo, nome, titolo, testo, tipo, ordine, correttezza FROM nodo WHERE id_nodo='$id_node'";
      $result = $db->getRow($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }

    function get_other_exercises ( $id_nodo_parent, $ordine, $user )
    {
      $db =& $this->getConnection();
      // if something goes wrong, $db is an error object
      // so we return the error object
      if ( AMA_DB::isError( $db ) ) return $db;
      $sql = "SELECT N.id_nodo, H.ripetibile FROM nodo AS N LEFT JOIN history_esercizi AS H ON (N.id_nodo=H.id_nodo) WHERE N.id_nodo_parent='$id_nodo_parent' AND N.ordine>$ordine";
      $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }

    function get_ordine_max_val ( $id_nodo )
    {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

      $sql = "SELECT MAX(ordine) AS ordine FROM nodo WHERE id_nodo_parent='$id_nodo'";
      $result = $db->getRow($sql, null, AMA_FETCH_ASSOC);
      if ( AMA_DB::isError($result) ) return $result;
      return ($result['ordine'] == "") ? 0 : $result['ordine'];
    }

    function raise_student_level($id_student, $id_course_instance, $increment )
    {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

      $sql = "UPDATE iscrizioni
				SET livello=livello+$increment
				WHERE id_utente_studente=$id_student
				AND id_istanza_corso=$id_course_instance";
      $result = $this->executeCritical($sql);
      return $result;
    }

    /*
     * nuova classe esercizi
     */
    function get_exercise( $id_node )
    {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

      $sql = "SELECT id_nodo, id_utente, nome, titolo, testo, tipo, ordine, id_nodo_parent, livello, correttezza
      FROM nodo
      WHERE id_nodo='$id_node' OR id_nodo_parent='$id_node'";
      $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }

    function get_student_answer ( $id_answer )
    {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

      $sql = "SELECT id_history_ex, id_utente_studente, id_nodo, id_istanza_corso, data_visita, data_uscita, risposta_libera,
      commento, punteggio, correzione_risposta_libera, ripetibile, allegato
      FROM history_esercizi
      WHERE id_history_ex=$id_answer";
      $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
      return $result[0]; //vito, 1 dic 2008.
    }

    /**
     * function get_notes_for_this_course_instance:
     * used to obtain data about public notes in the selected course instance,
     * private notes added by the selected user id, optionally ordered by creation
     * date and optionally showing notes visit count.
     *
     * @param integer $id_course_instance - the selected course instance into which search for notes
     * @param integer $id_user
     * @param boolean $order_by_date
     * @param boolean $show_visits
     * @return array
     */
    function get_notes_for_this_course_instance($id_course_instance, $id_user, $order_by_date=false, $show_visits=false)
    {
      $db =& $this->getConnection();
      if ( AMA_DB::isError( $db ) ) return $db;

      /**
       * @var $order_by_date_sql: if $order_by_date is set to true, then order notes by their
       * creation date in ascending order.
       */
      if ($order_by_date) {
        $order_by_sql = "ORDER BY N.data_creazione DESC";
      }
      else {
		$order_by_sql = "ORDER BY N.nome ASC";
	  }

      /**
       * based on show visits value, obtain info about notes with visits count for each note or not.
       */
      switch($show_visits)
      {
        // get note visits in this course instance
        case true:
			$sql = "
				SELECT
					N.id_nodo, N.id_utente, N.nome AS nome_nodo, N.titolo, N.testo, N.tipo, N.id_nodo_parent, N.data_creazione,
					U.username, U.nome, U.cognome
				FROM nodo N
				LEFT JOIN (SELECT id_nodo, count(id_nodo) AS numero_visite FROM history_nodi WHERE id_istanza_corso=".$id_course_instance." GROUP BY id_nodo) AS V ON (N.id_nodo=V.id_nodo)
				LEFT JOIN utente AS U ON (U.id_utente=N.id_utente)
				WHERE N.id_istanza=".$id_course_instance." AND (N.tipo = ".ADA_NOTE_TYPE." OR (N.tipo=".ADA_PRIVATE_NOTE_TYPE." AND N.id_utente=".$id_user.")) ".
				$order_by_sql;
 	        break;
 	        // do not get note visits
        case false:
        default:
			$sql = "
				SELECT
					N.id_nodo, N.id_utente, N.nome AS nome_nodo, N.titolo, N.testo, N.tipo, N.id_nodo_parent, N.data_creazione,
					U.username, U.nome, U.cognome
				FROM nodo N
				LEFT JOIN utente AS U ON (U.id_utente=N.id_utente)
				WHERE N.id_istanza=".$id_course_instance." AND (N.tipo = ".ADA_NOTE_TYPE." OR (N.tipo=".ADA_PRIVATE_NOTE_TYPE." AND N.id_utente=".$id_user.")) ".
				$order_by_sql;
 	        break;
      }

      $result = $db->getAll($sql, null, AMA_FETCH_ASSOC);
      return $result;
    }

    /**
     * @author giorgio 27/ago/2014
     *
     * gets a menu tree_id from the provider database, if not found
     * tries the common database and if still a menu tree_id is not
     * found for the given script, module and user_type tries the default
     *
     * @param string $module (module for the menu. e.g. browsing, comunica, modules/test)
     * @param string $script (script for the menu, derived from the URL)
     * @param string $user_type AMA_USER_TYPE
     * @param number $self_instruction non zero if course is in self instruction mode
     * @param boolean $get_all set it to true to get also disabled elements. Defaults to false
     *
     * @return boolean false | array (
     * 							'tree_id' the menu tree id to be used
     * 							'isVertical' non zero if this is a vertical menu
     * 							'dbToUse' the DataHandler where the menu was found
     * 						   )
     *
     * @access public
     */
    public function get_menutree_id($module, $script, $user_type, $self_instruction=0) {

    	$default_module = 'main';    // module name to be used as a default value
    	$default_script = 'default'; // script name to be used as a default value
    	$menu_found = false;
    	$retVal = array();

    	// get the query string as an array
    	$queryStringArr = (strlen($_SERVER['QUERY_STRING'])>0) ? explode('&', $_SERVER['QUERY_STRING']) : array();

    	$sql = 'SELECT tree_id, script, isVertical, linked_tree_id FROM menu_page WHERE module=? AND script LIKE ? AND user_type=? AND self_instruction=?';

    	$common_dh = AMA_Common_DataHandler::instance();

    	/**
    	 * Rules used to look for a menu:
    	 * - try passed module/script  in current provider, and if nothing is found
    	 * - try passed module/script  in common  provider, and if nothing is found
    	 * - try passed module/default in current provider, and if nothing is found
    	 * - try passed module/default in common  provider, and if nothing is found
    	 * - try main/default          in current provider, and if nothing is found
    	 * - try main/default          in common  provider, and if nothing is found
    	 * - give up.
    	 */

    	foreach (array($module,$default_module) as $nummodule=>$currentModule) {
    		foreach (array($script,$default_script) as $numscript=>$currentScript) {
    			// skip main module/passed script as per above rules
    			if ($nummodule==1 && $numscript==0) continue;
    			$params = array ($currentModule, $currentScript.'%', $user_type, $self_instruction);
    			foreach (array($this,$common_dh) as $dbToUse) {
    				$candidates = $dbToUse->getAllPrepared($sql, $params, AMA_FETCH_ASSOC);
    				if (!AMA_DB::isError($candidates) && $candidates!==false && count($candidates)>0) {
    					$bestScore = 0;
    					$bestNumOfMatchedParams=0;
    					/**
    					 * main loop to look for a menu to return
    					 */
    					foreach ($candidates as $menuCandidate) {
    						/**
    						 * search if there's a query string and
    						 * load it in the mneuCandidate array
    						 */
    						$querypos = strpos($menuCandidate['script'], '?');
							if ($querypos!==false) {
								$menuCandidate['queryString'] = substr($menuCandidate['script'], $querypos+1);
							} else {
								$menuCandidate['queryString'] = null;
							}

    						if (is_null($menuCandidate['queryString'])) {
    							/**
    							 * if menu candidate has no query string, it's the menu
    							 * only if it's the only candidate or the url had no query string
    							 */
    							if (count($candidates)===1 || count($queryStringArr)===0) {
    								$menu_found = true;
    							} else {
    								/**
    								 * save the menu as a default for this script,
    								 * to be returned if nothing more appropriate is found
    								 */
    								if ($bestScore <=0) $bestMatch = $menuCandidate;
    							}
    						} else {
    							/**
    							 * if menu candidate has a query string the menu is
    							 * the one with the highest number of matching params
    							 */

    							// make the array of the menuCandidate query string
    							$menuCandidateArr = explode ('&', $menuCandidate['queryString']);
    							// find matched parameters array
    							$matchedParams = array_intersect($menuCandidateArr, $queryStringArr);
    							if (count($matchedParams)>0) {

    								$score = count($matchedParams) / count($menuCandidateArr);

    								/**
    								 * if current candidate has a score higher than the bestScore or
    								 * if it has an equal score but with more mathched parameters,
    								 * than it is the new best match
    								 */

    								if ($score > $bestScore ||
    									($score == $bestScore && count($matchedParams)>$bestNumOfMatchedParams)) {
    									$bestScore = $score;
    									$bestNumOfMatchedParams = count($matchedParams);
    									$bestMatch = $menuCandidate;
    								}
    							}
    						}
    						if ($menu_found) break;
    					}
    					/**
    					 * if nothing is found BUT there's a bestMatch, use it as the menu
    					 */
    					if (!$menu_found && isset($bestMatch)) {
    						$menu_found = true;
    						$menuCandidate = $bestMatch;
    					}
    					if ($menu_found) break;
    				}
    			}
    			if ($menu_found) break;
    		}
    		if ($menu_found) break;
    	}
        // if no menu has been found return false right away!
    	if ($menu_found===true) {
    		$retVal['tree_id'] = $menuCandidate['tree_id'];
    		$retVal['isVertical'] = $menuCandidate['isVertical'];
    		$retVal['dbToUse'] = $dbToUse;
    		// if is a linked tree, set the actual tree_id to the linked one
    		if (!is_null($menuCandidate['linked_tree_id'])) {
    			$retVal['tree_id'] = $menuCandidate['linked_tree_id'];
    			$retVal['linked_from'] = $menuCandidate['tree_id'];
    		}
    	} else $retVal = false;

    	return $retVal;
    }

    /**
     * @author giorgio 27/ago/2014
     *
     * gets the left and right submenu trees
     *
     * @param number $tree_id the id of the menu tree to load
     * @param AMA_DataHandler $dbToUse the data handler to be used, either Common or Tester
     * @param boolean $get_all set it to true to get also disabled elements.
     *
     * @return array associative, with 'left' and 'right' keys for each submenu tree
     *
     * @access public
     */
    public function get_menu_children($tree_id, $dbToUse, $get_all = false) {
    	$retVal = array();
    	// get all the first level items, first left and then right side
    	foreach (array(0=>'left',1=>'right') as $sideIndex=>$sideString) {

    		$sql = 'SELECT MI.*, MT.extraClass AS menuExtraClass FROM `menu_items` AS MI JOIN `menu_tree` AS MT ON '.
    			   'MI.item_id=MT.item_id WHERE MT.tree_id=? AND MT.parent_id=0 AND MI.groupRight=?';
    		if (!$get_all) $sql .= ' AND MI.enabledON!="'.Menu::NEVER_ENABLED.'"';
    		$sql .= ' ORDER BY MI.order ASC';

    		$res = $dbToUse->getAllPrepared($sql,array($tree_id,$sideIndex),AMA_FETCH_ASSOC);

    		if (!AMA_DB::isError($res) && count($res)>0) {

    			foreach ($res as $count=>$element) {
    				$res[$count]['children'] = $this->get_menu_children_recursive($tree_id,$element['item_id'],$dbToUse,$get_all);
    			}
    			$retVal[$sideString] = $res;
    		} else {
    			$retVal[$sideString] = null;
    		}
    	}
    	return $retVal;
    }

    /**
     * @author giorgio 19/ago/2014
     *
     * recursively gets all the children of a given menu item
     *
     * @param number $tree_id the id of the menu tree to load
     * @param number $parent_id the id of the parent to get children for
     * @param AMA_DataHandler $dbToUse the data handler to be used, either Common or Tester
     * @param boolean $get_all set it to true to get also disabled elements.
     *
     * @return array of found children or null if no children found
     *
     * @access private
     */
    private function get_menu_children_recursive($tree_id=0,$parent_id,$dbToUse,$get_all) {

    	$sql = 'SELECT MI.*, MT.extraClass AS menuExtraClass FROM `menu_items` AS MI JOIN `menu_tree` AS MT ON '.
    			'MI.item_id=MT.item_id WHERE MT.tree_id=? AND MT.parent_id=?';
    	if (!$get_all) $sql .= ' AND MI.enabledON!="'.Menu::NEVER_ENABLED.'"';
    	$sql .= ' ORDER BY MI.order ASC';

    	$res = $dbToUse->getAllPrepared($sql,array($tree_id,$parent_id),AMA_FETCH_ASSOC);

    	if (AMA_DB::isError($res) || count($res)<=0 || $res===false) return null;
    	else {
    		foreach ($res as $count=>$element) {
    			$res[$count]['children'] = $this->get_menu_children_recursive($tree_id, $element['item_id'], $dbToUse, $get_all);
    		}
    		return $res;
    	}
    }
}
