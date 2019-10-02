<?php
/**
 * MultiPort
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

class MultiPort
{

  /**
   * apply the function passed as first parameter to all tester for a given user
   * @param $DHfunction string
   * @param  $ADAUser $userObj
   * @param  $field_list_ar array
   * @param  $clause string
   * @return $dataHa array
   */

  static public function applyFunction($DHfunction, ADAGenericUser $userObj,$field_ar,$clause) {
    $common_dh = $GLOBALS['common_dh'];
    $dataHa = array();
    $user_id = $userObj->getId();
    foreach($userObj->getTesters() as $tester) {
      ADALogger::log("MultiPort::$DHfunction for tester: $tester");
      $tester_dsn = self::getDSN($tester);
      if($tester_dsn != null) {
        $tester_dataHa = $common_dh->get_tester_info_from_pointer($tester);

        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        $dataHa[] = $tester_dh->$DHfunction($field_ar,$clause);
      }
    }
    return $dataHa;
  }

  /**
   *
   * @param string $tester
   * @return string dsn or null
   */
  static public function getDSN($tester) {
    if(DataValidator::validate_testername($tester,MULTIPROVIDER) === FALSE) {
      return NULL;
    }
    // FIXME:usare require_once?
    include_once(ROOT_DIR.'/clients/'.$tester.'/client_conf.inc.php');

    $tester_in_uppercase = strtoupper($tester);
    $tester_db_type = $tester_in_uppercase.'_DB_TYPE';
    $tester_db_user = $tester_in_uppercase.'_DB_USER';
    $tester_db_pass = $tester_in_uppercase.'_DB_PASS';
    $tester_db_host = $tester_in_uppercase.'_DB_HOST';
    $tester_db_name = $tester_in_uppercase.'_DB_NAME';

    if(defined($tester_db_type) && defined($tester_db_user)
    && defined($tester_db_pass) && defined($tester_db_host)
    && defined($tester_db_name) ) {

      return constant($tester_db_type).'://'.constant($tester_db_user).':'.
      constant($tester_db_pass).'@'.constant($tester_db_host).'/'.
      constant($tester_db_name);
    }
    return null;
  }

  /**
   *
   * @param string $tester
   * @return string dsn or null
   */
  static public function getTesterTimeZone($tester) {
    if(DataValidator::validate_testername($tester,MULTIPROVIDER) === FALSE) {
      return NULL;
    }
    // FIXME:usare require_once?
    include_once(ROOT_DIR.'/clients/'.$tester.'/client_conf.inc.php');

    $tester_in_uppercase = strtoupper($tester);
    $tester_timezone = $tester_in_uppercase.'_TIMEZONE';
    if(defined($tester_timezone)) {
      return constant($tester_timezone);
    }
    return null;
  }

  /**
   * metodo di prova
   * @param  ADAUser $userObj
   * @return
   */
  static public function testAMA(ADAGenericUser $userObj) {

    foreach($userObj->getTesters() as $tester) {
      ADALogger::log("MultiPort::testAMA for tester: $tester");
      $tester_dsn = self::getDSN($tester);
      if($tester_dsn == null) {
        return null;
      }

      $tester_dh = AMA_DataHandler::instance($tester_dsn);
    }
  }

    /*
     * get_instance_exists
     *
     * @access public
     *
     * @param $client
     * @param $id_course_instance
     *
     * @return true if instance exists
     */
    public function course_instance_status_get_on_tester($id_course_instance,$client) {


        $tester_dsn = self::getDSN($client);
        if($tester_dsn == null) {
            return ADA_ERROR_ID_CONNECTING_TO_DB;
        }
        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        $result = $tester_dh->course_instance_status_get($id_course_instance);
        return $result;
        /*
         *
        if ($result < 3 || $result > 0) {
            return true;
        } else {
            return false;
        }
         */
    }

    /*
     * get_cod_subscribed
     *
     * @access public
     *
     * @param $code
     * @param $client
     * @param $id_course_instance
     *
     * @return true if $code already exists in $id_course_instance
     */
    public function get_cod_subscribed_on_tester($code,$id_course_instance,$client) {


        $tester_dsn = self::getDSN($client);
        if($tester_dsn == null) {
            return ADA_ERROR_ID_CONNECTING_TO_DB;
        }
        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        $result = $tester_dh->get_cod_subscribed($code,$id_course_instance);
        return $result;
    }

  /**
     * pre-subscribe a student to a provider (provider = tester)
     *
     * @access public
     *
     * @param $id_studente - student id
     * @param $id_corso    - course instance id
     * @param $livello     - level of subscription (0=beginner, 1=intermediate, 2=advanced)
     * @param $client      - name of provider (es: client1)
     *
     * @return true on success, an AMA_Error object if something goes wrong
     */
    public function course_instance_presubscribe_to_tester($id_istanza_corso, $id_studente, $livello=0, $client, $code) {

          $tester_dsn = self::getDSN($client);
          if($tester_dsn == null) {
            return ADA_ADD_USER_ERROR_TESTER;
          }
          $tester_dh = AMA_DataHandler::instance($tester_dsn);
          $result = $tester_dh->course_instance_student_presubscribe_add($id_istanza_corso, $id_studente, $livello, $code);
          return $result;
    }

        /**
     * -subscribe a student to a provider (provider = tester)
     *
     * @access public
     *
     * @param $id_studente - student id
     * @param $id_corso    - course instance id
     * @param $livello     - level of subscription (0=beginner, 1=intermediate, 2=advanced)
     * @param $client      - name of provider (es: client1)
     *
     * @return true on success, an AMA_Error object if something goes wrong
     */
    public function course_instance_subscribe_to_tester($id_istanza_corso, $id_studente, $livello=1, $client) {

          $tester_dsn = self::getDSN($client);
          if($tester_dsn == null) {
            return ADA_ADD_USER_ERROR_TESTER;
          }
          $tester_dh = AMA_DataHandler::instance($tester_dsn);
          $result = $tester_dh->course_instance_student_subscribe($id_istanza_corso, $id_studente, ADA_STATUS_SUBSCRIBED, $livello);
          return $result;
    }


  /**
   * Adds a new user to the ADA main database.
   * Fails (returning false) if user is already in Common DB
   * @param  ADAGenericUser $userObj
   * @param  $testers array
   * @return boolean
   */
  static public function addUser(ADALoggableUser $userObj, $testers = array()) {


  	/**
     * true if must check user existance in
     * the common provider utente DB table
  	 */
  	$checkUserExistsInCommonDB = true;

  	/**
  	 * @author giorgio 05/mag/2014 12:45:30
  	 *
  	 * if not in a multiprovider environment,
  	 * check if the user to add exists in the selected provider
  	 *
  	 */
  	if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {

  		$tester_dh = AMA_DataHandler::instance(self::getDSN($GLOBALS['user_provider']));
  		if (!is_null($tester_dh)) {
  			/**
             * check if a user with the passed username
             * already exists in the selected provider
  			 */
  			$user_id = $tester_dh->find_students_list(null, 'username =\''.$userObj->getUserName().'\'');
//   			var_dump($user_id);
//   			var_dump(count($user_id)); die();
  			if (AMA_DB::isError($user_id)) {
  				return ADA_ADD_USER_ERROR;
  			} else {
  				if (count($user_id)>0) {
  					return ADA_ADD_USER_ERROR_USER_EXISTS_TESTER;
  				} else {
  					/**
  					 * set $user_id=0 to resume normal operation
  					 */
  					$user_id = 0;
  					/**
                     * don't check if user exists in common DB
  					 */
  					$checkUserExistsInCommonDB = false;
  					/**
  					 * $tester_dh is needed when actually
  					 * adding the user to tester, let's unset it
  					 */
  					unset ($tester_dh);
  				} // if (count($user_id)>0)
  			} // if (AMA_DB::isError($user_id))
  		} else {
  			return ADA_ADD_USER_ERROR_TESTER;
  		} // if (!is_null($tester_dh))
  	} else {
  		$user_id = $userObj->getId();
  		/**
         * set $tester_dh to null for future check
  		 */
  		$tester_dh = null;
  	} // if (!MULTIPROVIDER &&.....)

  	$common_dh = AMA_Common_DataHandler::instance();

    if ($user_id == 0){
      /* If the user isn't in common DB yet
       * add this user to ADA main database
       */
      $user_dataAr = $userObj->toArray();
      unset($user_dataAr['id_utente']);
      $user_id = $common_dh->add_user($user_dataAr, $checkUserExistsInCommonDB);
      /*
       * add_user restituisce o AMA_ERR_UNIQUE_KEY nel caso
       * sia già presente un utente con lo stesso username (email)
       * o restituisce un errore AMA_ERR_ADD o AMA_ERR_GET
       */
      if(AMA_DataHandler::isError($user_id)) {
        if($user_id->code == AMA_ERR_UNIQUE_KEY) {
          /*
           * Esiste gia' un utente con questa mail.
           */
          //$errorType = "a1";
          return ADA_ADD_USER_ERROR_USER_EXISTS;
        }
        else {
          //$user_id->code == AMA_ERR_ADD
          //$user_id->code == AMA_ERR_GET
          return ADA_ADD_USER_ERROR;
          //$errorType = "a2";
        }
        //return $errorType;
      }
      /*
       * Se siamo qui l'utente e' stato inserito correttamente
       * e $User_id e' l'id che gli e' stato assegnato.
       */
      $userObj->setUserId($user_id);
    }

    /*
     * If the user has subscribed to at least one tester, add the user
     * to the utente_tester table in common database.
     */


    foreach($testers as $tester) {
      if ($tester!=NULL){
          ADALogger::log("MultiPort::add:user_to_tester on tester: $tester");
          $testerHa = $common_dh->get_tester_info_from_pointer($tester);
          //var_dump($testerHa);
          $tester_id = $testerHa[0];
          $res = $common_dh->add_user_to_tester($user_id,$tester_id);

          if(AMA_DataHandler::isError($res)) {
            /*
             * add_user_to_tester raises AMA_ERR_ADD
             */
    //        // gestione errore
    //        if (($res->code != AMA_ERR_ADD) AND ($res->code!= AMA_ERR_UNIQUE_KEY)) {
    //          // ?? the user can be already there because of another service.
    //          // return false;
            $errorType = "b1";
    //        }
    //        else {
    //          $errorType = "b2";
    //        }
    //        return $errorType;

            // we don't want to exit here!!!
           // return ADA_ADD_USER_ERROR_TESTER_ASSOCIATION;
          }
      }
    }

    /*
     * If the user has subscribed to at least one tester, add the user
     * to the tester database.
     */
    $user_dataAr = $userObj->toArray();

    foreach($testers as $tester) {
       if ($tester!=NULL){
          ADALogger::log("MultiPort::addUser on tester: $tester");
          $tester_dsn = self::getDSN($tester);

          if($tester_dsn == null) {
            //$errorType = "c";
            //return $errorType;
            return ADA_ADD_USER_ERROR_TESTER;
          }

          $tester_dh = AMA_DataHandler::instance($tester_dsn);

          switch($userObj->getType()) {
            case AMA_TYPE_STUDENT:
              $result = $tester_dh->add_student($user_dataAr);
              break;

            case AMA_TYPE_AUTHOR:
              $result = $tester_dh->add_author($user_dataAr);
              break;

            case AMA_TYPE_SUPERTUTOR:
            case AMA_TYPE_TUTOR:
              $result = $tester_dh->add_tutor($user_dataAr);
              break;

            case AMA_TYPE_SWITCHER:
              $result = $tester_dh->add_user($user_dataAr);
              break;

            case AMA_TYPE_ADMIN:
              $result = $tester_dh->add_user($user_dataAr);
              break;
          }
          if(AMA_DataHandler::isError($result)) {
            if($result->code == AMA_ERR_UNIQUE_KEY) {
                /*
                 * Esiste gia' un utente con questa mail.
                 */
                return ADA_ADD_USER_ERROR_USER_EXISTS_TESTER;
            }
            else {
              return ADA_ADD_USER_ERROR_TESTER;
            }
          } else {
          	$userObj->addTester($tester);
          }
      }
/*
      if(AMA_DataHandler::isError($result)) {
        if (($result->code != AMA_ERR_ADD) AND ($result->code!= AMA_ERR_UNIQUE_KEY)) {
          $errorType = "d1";
          return $errorType;

        } else {
          $errorType = "d2";
          return $errorType;
        }
      }
*/
    }
    // mod steve 5/10/09
    //return true; ???????????
    return $user_id;
    // end mod
  }
  /**
   * Updates an existing user
   *
   * @param  ADAGenericUser $userObj
   * @param  $testers array
   * @return boolean
   */
  static public function setUser(ADALoggableUser $userObj, $new_testers = array(), $update_user_data = FALSE, $extraTableName=false ) {
    $user_id = $userObj->getId();
    $testers = $userObj->getTesters();
    $testers_to_add = array();
    if(!is_array($testers)) {
      $testers = array();
    }
    if ($user_id == 0) {
      return FALSE;
    }

    $common_dh = AMA_Common_DataHandler::instance();
    $user_dataAr = $userObj->toArray();

    if ($update_user_data) {
      $result = $common_dh->set_user($user_id, $user_dataAr);
      if (AMA_Common_DataHandler::isError($result)) {
        //return ADA_SET_USER_ERROR;
      }
    }

    if ($update_user_data) {

    	$idFromPublicTester=0;

      foreach($testers as $tester) {
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        //
        switch($userObj->getType()) {
        case AMA_TYPE_STUDENT:
          /**
		   * @author giorgio
		   * if it's an extraTable I need set_student to return me the id of the inserted
		   * record in the table on DEFAULT that will be used as an id for all other providers
           */
          $result = $tester_dh->set_student($user_id,$user_dataAr, $extraTableName, $userObj, $idFromPublicTester);
          break;

        case AMA_TYPE_AUTHOR:
          $result = $tester_dh->set_author($user_id,$user_dataAr);
          break;

        case AMA_TYPE_SUPERTUTOR:
        case AMA_TYPE_TUTOR:
          $result = $tester_dh->set_tutor($user_id,$user_dataAr);
          break;

        case AMA_TYPE_SWITCHER:
          $result = $tester_dh->set_user($user_id,$user_dataAr);
          break;

        case AMA_TYPE_ADMIN:
          $result = $tester_dh->set_user($user_id,$user_dataAr);
          break;
        }
        if (AMA_DataHandler::isError($result)) {
        	if (!($result instanceof PDOException) && $result->code == AMA_ERR_NOT_FOUND) $testers_to_add[] = $tester;
          //return ADA_SET_USER_ERROR_TESTER;
        }

   		if (defined('MODULES_GDPR') && true === MODULES_GDPR && $userObj->getType() == AMA_TYPE_SWITCHER && array_key_exists('user_gdpr', $_POST)) {
	      	try {
	      		require_once MODULES_GDPR_PATH.'/include/GdprAPI.php';
		    	$gdprAPI = new \Lynxlab\ADA\Module\GDPR\GdprAPI($tester);
	      		$gdprUser = $gdprAPI->getGdprUserByID($userObj);
	      		if (false !== $gdprUser) {
	      			foreach ($gdprUser->getType() as $gdprType) $gdprUser->removeType($gdprType);
	      		} else {
	      			$gdprUser = \Lynxlab\ADA\Module\GDPR\GdprAPI::createGdprUserFromADALoggable($userObj);
	      		}
	      		if (!is_array($_POST['user_gdpr'])) $_POST['user_gdpr'] = array($_POST['user_gdpr']);
	      		foreach ($_POST['user_gdpr'] as $gdprType) {
	      			$gdprUser->addType($gdprType, $gdprAPI);
	      		}
	      		$gdprAPI->saveGdprUser($gdprUser);
	      	} catch (\Exception $e) {
	      		// handle excpetion here if needed
	      	}
      	}

      }
    }

    // se sono qui, verifico se in new_testers ci sono dei tester nuovi e li associo all'utente


    $testers_to_add = array_merge($testers_to_add, array_diff($new_testers, $testers));

    $pwd = $common_dh->_get_user_pwd($user_id);
    if (AMA_DataHandler::isError($pwd)){
    	// if there is an error, we MUST insert a fake password in provider DB
    	$pwd = sha1($user_id);
    }

    $user_dataAr['password'] = $pwd;
    foreach($testers_to_add as $tester_to_add) {
      // aggiungi utente nella tabella utente del tester
      $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester_to_add));

      // we have to use different functions for different user types
      switch($userObj->getType()) {
        case AMA_TYPE_STUDENT:
          $result = $tester_dh->add_student($user_dataAr);
          $result = $tester_dh->set_student($user_id,$user_dataAr, $extraTableName, $userObj);

          if ($userObj->hasExtra()) {

          	if (method_exists('ADAUser','getExtraTableName')) {
          		$tableName = ADAUser::getExtraTableName();
          		if (strlen($tableName)>0) {
          			$tester_dh->set_student($user_id,$user_dataAr, $tableName, $userObj);
          		}
          	}

          	if (method_exists('ADAUser','getLinkedTables')) {
          		$linkedTables = ADAUser::getLinkedTables();
          		if (is_array($linkedTables) && count($linkedTables)>0) {

          			foreach ($linkedTables as $tableName) {
          				// force the record to be saved in the provider we're adding the user
          				if (isset($user_dataAr[$tableName]) && is_array($user_dataAr[$tableName])) {
          					foreach ($user_dataAr[$tableName] as $i=>$notused) {
          						$storedID = $user_dataAr[$tableName][$i][$tableName::getKeyProperty()];
          						$user_dataAr[$tableName][$i][$tableName::getKeyProperty()] = 0;
          						$user_dataAr[$tableName][$i]['_isSaved'] = 0;
          					$result = $tester_dh->set_student($user_id,$user_dataAr, $tableName, $userObj, $storedID);
                                                        if(!AMA_DB::isError($result))
                                                        {
                                                            $user_dataAr[$tableName][$i]['_isSaved'] = 1;
                                                        }
                                                 }
          					//$result = $tester_dh->set_student($user_id,$user_dataAr, $tableName, $userObj, $storedID);
                                          }
                                   }
                          }
                 }
           }
          break;

        case AMA_TYPE_AUTHOR:
          $result = $tester_dh->add_author($user_dataAr);
          break;

        case AMA_TYPE_SUPERTUTOR:
        case AMA_TYPE_TUTOR:
          $result = $tester_dh->add_tutor($user_dataAr);
          break;

        case AMA_TYPE_SWITCHER:
          $result = $tester_dh->add_user($user_dataAr);
          break;

        case AMA_TYPE_ADMIN:
          $result = $tester_dh->add_user($user_dataAr);
          break;
      }
      if (AMA_DataHandler::isError($result)) {
        //return ADA_SET_USER_ERROR_TESTER_ASSOCIATION;
      }
    }

    /*
     * If the user has subscribed to at least one tester, add the user
     * to the utente_tester table in common database.
     */
    foreach($testers_to_add as $tester_to_add) {
      $testerHa = $common_dh->get_tester_info_from_pointer($tester_to_add);
      $tester_id = $testerHa[0];
      $res = $common_dh->add_user_to_tester($user_id,$tester_id);
      if(AMA_DataHandler::isError($res)) {
        if (($res->code != AMA_ERR_ADD) AND ($res->code!= AMA_ERR_UNIQUE_KEY)) {
          //return ADA_SET_USER_ERROR_TESTER;
          return false;
        }
      }
      else {
        $userObj->addTester($tester_to_add);
      }
    }

    if (isset($result)) return $result;
  }


  public static function findUser($id_user, $id_course_instance=NULL) {
    $common_dh = $GLOBALS['common_dh'];


    /*
     $user_dataAr = $common_dh->get_user_info($id_user);
     if(AMA_Common_DataHandler::isError($user_dataAr)) {
     $errObj = new ADA_Error();
     return NULL;
     }
     $user_type = $user_dataAr['tipo'];
     */
    $user_type = $common_dh->get_user_type($id_user);
    if(AMA_Common_DataHandler::isError($user_type)) {
      $errObj = new ADA_Error($user_type, 'An error occurred while retrieving user type in MultiPort::findUser');
    }
    //$user_dataAr['id_utente'] = $user_dataAr['id'];
    //unset($user_dataAr['id']);
    /*
    * Tipi di utenti e cosa fare:
    *
    * utente di tipo autore:
    * ottieni i dati dalla tabella COMUNE.utente richiamando get_user_info()
    *
    */
    switch($user_type) {
      case AMA_TYPE_AUTHOR:
        $user_dataAr = $common_dh->get_author($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving author data in MultiPort::finduser');
        }

        $userObj = new ADAAuthor($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        $return = $userObj;
        break;

      case AMA_TYPE_ADMIN:
        $user_dataAr = $common_dh->get_user_info($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving admin data in MultiPort::finduser');
        }
        $userObj = new ADAAdmin($user_dataAr);
        $userObj->setUserId($id_user);
        // @author giorgio 09/set/2015
        // admin user gets listed on all testers
        $allPointers = $common_dh->get_all_testers(array('puntatore'));
        if (!AMA_DB::isError($allPointers) && is_array($allPointers) && count($allPointers)>0) {
        	foreach ($allPointers as $aPointer) $user_testersAr[] = $aPointer['puntatore'];
        } else {
        	$errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving admin testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        $return = $userObj;
        break;

      case AMA_TYPE_STUDENT:
        $user_dataAr = $common_dh->get_student($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving user data in MultiPort::finduser');
        }
        $userObj = new ADAUser($user_dataAr);
        $userObj->setUserId($id_user);

        if(DataValidator::is_uinteger($id_course_instance) !== FALSE) {
          $userObj->set_course_instance_for_history($id_course_instance);
        }
        elseif (isset($_SESSION['sess_id_course_instance']) &&
        		DataValidator::is_uinteger($_SESSION['sess_id_course_instance']) !== FALSE) {
          $userObj->set_course_instance_for_history($_SESSION['sess_id_course_instance']);
        }

        //return $userObj;

        // QUI DEVO VEDERE QUALI SONO I TESTER ASSOCIATI A QUESTO UTENTE.
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        $return = $userObj;
        break;

      case AMA_TYPE_SUPERTUTOR:
      case AMA_TYPE_TUTOR:
        $user_dataAr = $common_dh->get_tutor($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving practitioner data in MultiPort::finduser');
        }
        $userObj = new ADAPractitioner($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        // TODO: here we have to read user's profile from the tester database
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        //Load user profile info from default tester
        $tester = $userObj->getDefaultTester();
        $tester_dsn = self::getDSN($tester);
        if($tester_dsn != NULL) {
          $tester_dh = AMA_DataHandler::instance($tester_dsn);
          $user_info = $tester_dh->get_tutor($id_user);
          if(!AMA_DataHandler::isError($user_info)) {
            $userObj->setProfile($user_info['profilo']);
            $userObj->setFee($user_info['tariffa']);
          }
        $tester_dh->disconnect();
        }
        $return = $userObj;
        break;

      case AMA_TYPE_SWITCHER:
        $user_dataAr = $common_dh->get_user_info($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving author data in MultiPort::finduser');
        }
        $userObj = new ADASwitcher($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        $return = $userObj;
        break;
      case AMA_TYPE_VISITOR:
      default:
        // FIXME: restituisco questo oggetto oppure alzo un errore?
        $return = NULL;
        break;
    }

    if (!is_null($return) && $return instanceof ADAUser)
    {
    	/**
    	 * @author giorgio 06/giu/2013
    	 *
    	 * load extra fields from DB, if we have some in this customization (i.e. User->hasExtra is true)
    	 * Note that this MUST be done after user testers have been set.
    	 *
    	 */
    	if ($userObj->hasExtra())
    	{
    		$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($user_testersAr[0]));
    		$extraAr = $tester_dh->getExtraData($userObj);
    		if (!AMA_DB::isError($extraAr)) {
    			$userObj->setExtras($extraAr);
    			$return = $userObj;
    		}
                $tester_dh->disconnect();
    	}
    }

    return $return;
  }



  public static function findUserByUsername($username) {
    $common_dh = $GLOBALS['common_dh'];

    $id_user = $common_dh->find_user_from_username($username);
    if(AMA_Common_DataHandler::isError($id_user)) {
        if($id_user->code == AMA_ERR_GET) {
            return NULL;
        }
        $errObj = new ADA_Error($user_type, 'An error occurred while retrieving user id in MultiPort::findUser');
        return NULL;
    }

    $user_type = $common_dh->get_user_type($id_user);
    if(AMA_Common_DataHandler::isError($user_type)) {
      $errObj = new ADA_Error($user_type, 'An error occurred while retrieving user type in MultiPort::findUser');
      return NULL;
    }

    /*
    * Tipi di utenti e cosa fare:
    *
    * utente di tipo autore:
    * ottieni i dati dalla tabella COMUNE.utente richiamando get_user_info()
    *
    */
    switch($user_type) {
      case AMA_TYPE_AUTHOR:
        $user_dataAr = $common_dh->get_author($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving author data in MultiPort::finduser');
          return NULL;
        }

        $userObj = new ADAAuthor($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        return $userObj;
        break;

      case AMA_TYPE_ADMIN:
        $user_dataAr = $common_dh->get_user_info($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving admin data in MultiPort::finduser');
            return NULL;

        }
        $userObj = new ADAAdmin($user_dataAr);
        $userObj->setUserId($id_user);
        return $userObj;
        break;

      case AMA_TYPE_STUDENT:
        $user_dataAr = $common_dh->get_student($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving user data in MultiPort::finduser');
          return NULL;

        }
        $userObj = new ADAUser($user_dataAr);
        $userObj->setUserId($id_user);

        if(isset($id_course_instance) && DataValidator::is_uinteger($id_course_instance) !== FALSE) {
          $userObj->set_course_instance_for_history($id_course_instance);
        }
        elseif (isset($_SESSION['sess_id_course_instance']) && DataValidator::is_uinteger($_SESSION['sess_id_course_instance']) !== FALSE) {
          $userObj->set_course_instance_for_history($_SESSION['sess_id_course_instance']);
        }

        //return $userObj;

        // QUI DEVO VEDERE QUALI SONO I TESTER ASSOCIATI A QUESTO UTENTE.
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        /**
         * get student extra data
         */
        if (!is_null($userObj) && $userObj instanceof ADAUser)
        {
            /**
             * @author giorgio 06/giu/2013
             *
             * load extra fields from DB, if we have some in this customization (i.e. User->hasExtra is true)
             * Note that this MUST be done after user testers have been set.
             *
             */
            if ($userObj->hasExtra())
            {
                    $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($user_testersAr[0]));
                    $extraAr = $tester_dh->getExtraData($userObj);
                    if (!AMA_DB::isError($extraAr)) {
                            $userObj->setExtras($extraAr);
                    }
            $tester_dh->disconnect();
            }
        }
        return $userObj;
        break;

      case AMA_TYPE_SUPERTUTOR:
      case AMA_TYPE_TUTOR:
        $user_dataAr = $common_dh->get_tutor($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving practitioner data in MultiPort::finduser');
        return NULL;

        }
        $userObj = new ADAPractitioner($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        // TODO: here we have to read user's profile from the tester database
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        //Load user profile info from default tester
        $tester = $userObj->getDefaultTester();
        $tester_dsn = self::getDSN($tester);
        if($tester_dsn != NULL) {
          $tester_dh = AMA_DataHandler::instance($tester_dsn);
          $user_info = $tester_dh->get_tutor($id_user);
          if(!AMA_DataHandler::isError($user_info)) {
            $userObj->setProfile($user_info['profilo']);
            $userObj->setFee($user_info['tariffa']);
          }
        $tester_dh->disconnect();
        }
        return $userObj;
        break;

      case AMA_TYPE_SWITCHER:
        $user_dataAr = $common_dh->get_user_info($id_user);
        if(AMA_Common_DataHandler::isError($user_dataAr)) {
          $errObj = new ADA_Error($user_dataAr,'An error occurred while retrieving author data in MultiPort::finduser');
        return NULL;

        }
        $userObj = new ADASwitcher($user_dataAr);
        $userObj->setUserId($id_user);
        $user_testersAr = $common_dh->get_testers_for_user($userObj->getId());
        if(AMA_Common_DataHandler::isError($user_testersAr)) {
          $errObj = new ADA_Error($user_testersAr,'An error occurred while retrieving user testers in MultiPort::finduser');
        }
        $userObj->setTesters($user_testersAr);
        return $userObj;
        break;
      case AMA_TYPE_VISITOR:
      default:
        // FIXME: restituisco questo oggetto oppure alzo un errore?
        return NULL;
        break;
    }
  }


  /**
   *
   * @param $username
   * @param $password
   * @return an instance of
   */
  public static function loginUser($username, $password) {
    $common_dh = $GLOBALS['common_dh'];

    $result = $common_dh->check_identity($username, $password);
    if(AMA_Common_DataHandler::isError($result)) {
      return FALSE;
    }
    $userObj = self::findUser($result['id_utente']);
    return $userObj;
  }


  /**
   * get all services
   * @param  $field_list_ar array
   * @param  $clause string
   * @return $servicesAr array
   */

  static public function find_services_list( $field_list_ar,$clause,$for_registration = FALSE,$max_level=4,$min_level=1) {

    /*NOTE: old version was restricted to user's tester and required $userObj as parameter:

    static public function find_services_list(ADAGenericUser $userObj,$field_list_ar,$clause) {

    Also, it got $user_id from it:

    $user_id = $userObj->getId();
    */

    /*
     *  if ($userObj){ etc
     $user_id = $userObj->getId();
     )
     */


    $to_sub_course_dataHa = array();
    $course_instances = array();
    $common_dh = $GLOBALS['common_dh'];
    $testers_list = $common_dh->get_all_testers();







    /*
     * Obtain services data from testers
     */

    foreach($testers_list as $testerAr){
      //$tester = $testerAr[0];
      $tester = $testerAr['puntatore'];

      ADALogger::log("MultiPort::find_services_list for tester: $tester");
      $tester_dsn = self::getDSN($tester);


      //if(($tester_dsn != null) && ($tester!=ADA_PUBLIC_TESTER)) {
      if($tester_dsn != null)  {
        // FIXME: deve escludere i PUBLIC o, no?

        //        $tester_dataHa = $common_dh->get_tester_info_from_pointer($tester);
        if(AMA_DataHandler::isError($tester_dataHa)) {
          // FIXME: rimuovere e gestire con ADA_Error
        }
        //        $tester_name = $tester_dataHa[1];
        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        // FIXME:  questa versione prende le implementazioni dei servizi, non i servizi !!!!!
        $all_instance = $tester_dh->find_courses_list($field_list_ar, $clause);
        if(AMA_DataHandler::isError($all_instance)) {
          // FIXME: rimuovere e gestire con ADA_Error
        }
        if (is_array($all_instance)){

          foreach($all_instance as $one_instance) {
            $id_course = $one_instance[0];
            $course_instances[$id_course] = $one_instance;
          }
        }
      }
    } // foreach tester


    /*
     * Obtain services level from common db
     */
    $services_info = array();
    foreach($course_instances as $id_course => $course_data) {

      $service_info = $common_dh->get_service_info_from_course($id_course);
      $tester_info  = $common_dh->get_tester_info_from_id_course($id_course);

      // FIXME
      /*
      * Se non siamo riusciti ad ottenere informazioni sul servizio o sul tester
      * a partire da un corso, possiamo assumere che questo servizio non venga erogato
      * e quindi non lo mostriamo tra quelli a cui l'utente puo' fare richiesta
      * di iscrizione.
      */
      if($service_info == NULL || AMA_Common_DataHandler::isError($tester_info)) {
        continue;
      }

      $tester_name = $tester_info['nome'];
      $titolo      = $course_data[2];
      $servizio = $service_info[1];
      $descrizione = $course_data[5];

      if(AMA_DataHandler::isError($service_info)) {
        // echo 'get service info from course <br />';
        continue;
      }
      else {

        $livello = $service_info[3];
        if ( ($livello <=$max_level) AND ($livello >=$min_level)){
            if ($livello > 1){
              if ($for_registration){
                if ($id_course == $_REQUEST['id_course']){
                  $require_link = "<input type=\"radio\" name=\"id_course\" value=$id_course checked=\"checked\"'>";
                }
                else {
                  $require_link = "<input type=\"radio\" name=\"id_course\" value=$id_course>";
                }
              }
              else {
                $require_link = "<a href=" . HTTP_ROOT_DIR .  "/browsing/registration.php?id_course=$id_course>" .translateFN('Richiedi'). "</a>";
              }
              // we want subscribe only to services with level>1
              $info_link = "<a href=" . HTTP_ROOT_DIR .  "/info.php?id_course=$id_course>".translateFN('Info'). "</a>";
    	        $img_link  = "<img src=\"img/title.png\" border=0> ".translateFN('Servizio');
    	        $row = array(
        	        translateFN('Provider')      => $tester_name,
        	        translateFN('Servizio') =>$servizio,
        	        // $img_link                  => $titolo,
        	        //translateFN('Descrizione') => $descrizione,
        	        translateFN('Livello')     => $livello,
        	        translateFN('Info')        => $info_link,
        	        translateFN('Richiedi')    => $require_link
    	        );
    	        array_push($to_sub_course_dataHa,$row);

            }
            else { // public access service, level = 1
              $id_node   = $id_course.'_'.ADA_DEFAULT_NODE;
              $require_link = '<a href="' . HTTP_ROOT_DIR .  '/browsing/view.php?id_course='.$id_course.'&id_node='.$id_node.'">' .translateFN('Entra'). '</a>';
              $info_link = "<a href=" . HTTP_ROOT_DIR .  "/info.php?id_course=$id_course>".translateFN('Info'). "</a>";
              $img_link  = "<img src=\"img/title.png\" border=0> ".translateFN('Servizio');
              $row = array(
                   translateFN('Provider')      => $tester_name,
                   translateFN('Servizio') =>$servizio,
                    //   $img_link                  => $titolo,
                    //translateFN('Descrizione') => $descrizione,
                    translateFN('Livello')     => $livello,
                    translateFN('Info')        => $info_link,
                    translateFN('Richiedi')    => $require_link
               );
               array_push($to_sub_course_dataHa,$row);

          }
       }
    }
    }
    // sorting on:
    // provider, then service, then level
    foreach ($to_sub_course_dataHa as $key => $row) {
    $provider[$key]  = $row[translateFN('Provider')];
    $servizio[$key] = $row[translateFN('Servizio')];
    $livello[$key] = $row[translateFN('Livello')];
}
    array_multisort($provider, SORT_DESC, $livello, SORT_DESC,$servizio, SORT_DESC,$to_sub_course_dataHa);
    return $to_sub_course_dataHa;
  }



  /**
   * get all services to which a given user has subscribed
   * @param  $ADAUser $userObj
   * @param  $field_list_ar array
   * @param  $clause string
   * @return $sub_course_dataHa array
   */
  static public function find_sub_services_data(ADAGenericUser $userObj,$field_ar,$clause,$orderBy = 'service') {
    $common_dh = $GLOBALS['common_dh'];

    $sub_course_dataHa = array();
    $user_id = $userObj->getId();

    /*
     * Obtain tester names
     */
    $tester_names = array();
/*    foreach ($userObj->getTesters() as $tester) {
      $tester_dataHa = $common_dh->get_tester_info_from_pointer($tester);
      $tester_names[$tester] = $tester_dataHa[1];
    }
*/

  // foreach($userObj->getTesters() as $tester) { // only providers in which user is subscribed

  /*  $testers = $common_dh->get_all_testers(); // all providers
    foreach($testers as $testerItem){
  	  $tester = $testerItem['puntatore'];
*/
    $testerPointersAr = $common_dh->get_testers_for_user($user_id);// providers assigned to the user
    foreach ($testerPointersAr as $tester){
     // ADALogger::log("MultiPort::find_sub_services_data for tester: $tester");
	  $tester_dataHa = $common_dh->get_tester_info_from_pointer($tester);

	  $tester_city = $tester_dataHa[5];
	  $tester_country = $tester_dataHa[6];
      $tester_names[$tester] = $tester_dataHa[1];
      $tester_dsn = self::getDSN($tester);
      if($tester_dsn != null) {
        //$tester_dataHa = $common_dh->get_tester_info_from_pointer($tester);
        //$tester_name = 'NOME TESTER';//$tester_dataHa[1];
        $tester_dh = AMA_DataHandler::instance($tester_dsn);

		if (!AMA_DataHandler::isError($tester_dh)){

        	// versioneche cicla solo sulle istanze cui è pre/iscritto
        	$all_instance = $tester_dh->course_instance_student_presubscribe_get_status($user_id);

	        //  versioneche cicla su tutte le istanze
	        /* FIXME: bisogna fare una JOIN su iscrizione e istanze_corso !
    	    $all_instance = $tester_dh->course_instance_find_list($field_ar,$clause);
			var_dump($all_instance);
			*/

        } else {
        	//var_dump($tester_dh);
        	$all_instance = "";
        }

    if (is_array($all_instance)){
        foreach ($all_instance as $one_instance) {
        //	  var_dump($one_instance);
          $history_link = " - ";
          $toc = " - ";
          $info = "- ";
          $tutor = translateFN("Not assigned");
          $tutor_link = $tutor;
          $now = AMA_DataHandler::date_to_ts("now");

          $id_course_instance = $one_instance['istanza_corso'];
          $status =  $one_instance['status'];
          $one_course_instance = $tester_dh->course_instance_get($id_course_instance,true);
          // GESTIRE ERRORE
          $id_course          = $one_course_instance['id_corso'];
          $data_inizio        = $one_course_instance['data_inizio'];
          $durata             =  $one_course_instance['durata'];
          // NOTE: qui sarebbe utile invece il conto dei giorni restanti...
          $data_inizio_previsto = $one_course_instance['data_inizio_previsto'];
          $data_fine = $one_course_instance['data_fine'];


          $service_completed = $data_fine < $now;
         $sub_courses = $tester_dh->get_subscription($user_id, $id_course_instance);
         //      if ($sub_courses['tipo'] == 2) { introducing status 3 (removed) and 4 (visitors)

         if (!AMA_dataHandler::isError($sub_courses)){
          //    if (($sub_courses['tipo'] == ADA_STATUS_SUBSCRIBED) OR ($sub_courses['tipo'] == 4)) {
          /* hack: subscription state is not updated, so we use time from course instance data to show users' status*/
          if (($service_completed) && ($sub_courses['tipo'] == ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED)){
              $tipo = ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED;
          } else {
            $tipo = $sub_courses['tipo'];
          }

          // filtering on completed services if $clause paratemer is passed
         if ( (!$clause) OR  ($tipo != ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED)) { //FIXME: we have to parse the clause !!!

                $tutor_Id = $tester_dh->course_instance_tutor_get($id_course_instance);

                if ($tutor_Id){
                  $tutorInfoHa = $tester_dh->get_tutor($tutor_Id);
                  $tutor_link = '<a href="' . HTTP_ROOT_DIR .'/browsing/practitionerProfile.php?id='
                              . $tutor_Id . '">' . $tutorInfoHa['nome'] . ' ' . $tutorInfoHa['cognome']
                              . '</a>';
                }

                $course = $tester_dh->get_course($id_course);
                 if (is_array($course)){
    	             $id_start = $id_course."_".$course['id_nodo_iniziale'];
    	             $home_label = translateFN("home");
    	             $titolo = translateFN($course['titolo']);
    	             $info = "<a href=" . $http_root_dir .  "../info.php?id_course=$id_course&norequest=1>$titolo</a>";
    	             $start_date =  ts2dFN($data_inizio_previsto);
                 }
                 // mod steve 17/12/09 suspended while wating for  history module
                 // $history_link = "<a href='".HTTP_ROOT_DIR."/browsing/service_info.php?norequest=1&id_course=$id_course&id_course_instance=$id_course_instance'>$start_date</a>";
                 $nome = $course['nome'];

                 switch ($tipo){
                  case ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED://ADA_STATUS_SUBSCRIBED:

                      // mod steve 17/12/09 suspended while wating for  history module
                        $history_link = "<a href='".HTTP_ROOT_DIR."/browsing/service_info.php?norequest=1&id_course=$id_course&id_course_instance=$id_course_instance'>$start_date</a> - ".ts2dFN($data_fine);
                     // $history_link =  $start_date;
                        $toc = "<a href='view.php?$session_id_par"."id_course=$id_course&id_node=$id_start&id_course_instance=$id_course_instance'>".translateFN('Entra'). "</a>";
                    break;
                  case ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED: //ADA_STATUS_PRESUBSCRIBED:
                        // $toc = "<a href='view.php?$session_id_par"."id_course=$id_course&id_node=$id_start&id_course_instance=$id_course_instance'>".translateFN('Entra'). "</a>";
                        $history_link = $start_date." - ".ts2dFN($data_fine);
                    break;
                  case ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED:
                        $history_link =  $start_date." - ".ts2dFN($data_fine);
                    break;
                  case ADA_SERVICE_SUBSCRIPTION_STATUS_SUSPENDED:
                      $history_link =  $start_date." - ";
                    break;
                  case ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED:
                  default: // es ADA_STATUS_VISITOR = 4

                } //case

                // subscription state
                $subscription_state = subscriptionType2stringFN($tipo);

                if ($orderBy == "country"){
                    $row = array(
                        translateFN('Country')=>$tester_country."/".$tester_city,
                        translateFN('Provider')=>$tester_names[$tester],
            	        translateFN('Servizio')=>$info,
            	        translateFN('Dettagli')=>$toc,
            	        translateFN('Period')=>$history_link,
                        translateFN('Durata')=>$durata,
                        translateFN('E-practitioner')=>$tutor_link,
                        translateFN('Stato')=>$subscription_state
                    );
                } else { // default
                    $row = array(
                        translateFN('Servizio')=>$info,
                        translateFN('Country')=>$tester_country."/".$tester_city,
                        translateFN('Provider')=>$tester_names[$tester],
            	        translateFN('Dettagli')=>$toc,
            	        translateFN('Period')=>$history_link,
                        translateFN('Durata')=>$durata,
                        translateFN('E-practitioner')=>$tutor_link,
                        translateFN('Stato')=>$subscription_state
                    );

                }
                // mydebug(__LINE__,__FILE__,$row);

                array_push($sub_course_dataHa,$row);


              } else {
                //$sub_course_dataHa = ""; // translateFN("Nessuna iscrizione");
              }
          } // if not completed
        } // foreach instance
       }
      }
    } // foreach er
    return $sub_course_dataHa;
  }

 function get_service_max_level($user_id) {
 	/**
   * get_service_max_level
   *
   * get max level of services requested by user user_id
   * @param  $user_id integer
   * @return $level_ha array
   */
    $common_dh = $GLOBALS['common_dh'];
    $testers = $common_dh->get_all_testers();
    $level_ha = array();
 	foreach($testers as $testerPointer) {
      $tester_dsn = self::getDSN($testerPointer['puntatore']);
      $level_ha[$testerPointer['puntatore']]['max_level'] = 0; // default;
      if($tester_dsn != null) {
        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        //  versioneche cicla su tutte le istanze di quel tester
        $all_instance = $tester_dh->course_instance_find_list($field_ar,$clause);
        foreach ($all_instance as $one_instance) {
          $id_course_instance = $one_instance[0];
          $level = $tester_dh->_get_student_level($user_id,$id_course_instance);
          if (!AMA_DataHandler::isError($level)){
          	$max_level = $level_ha[$testerPointer['puntatore']]['max_level'];
          	$level_ha[$testerPointer['puntatore']]['max_level'] = max((int)$level,$max_level);
          }
        } // end foreach instances
      } // end tester error
      else {
      	//..
      }
 	} //end foreach tester
    return  $level_ha;

 }

  // MARK: methods used to access user messages and agenda
  /**
   * get_messages
   *
   * get all messages for  a given user
   * @param  $ADAUser $userObj
   * @return $msgs_ha array
   */
  static private function get_sent_messages(ADALoggableUser $userObj, $msgType = ADA_MSG_SIMPLE, $msgFlags=array()){//,$field_ar,$clause) {

    $messages_Ar = array();
    $user_id = $userObj->getId();

    $sess_selected_tester = $_SESSION['sess_selected_tester'];

    $clause = '';

    /*
     * Get messages sent from  this user from all the testers the user has subscribed to.
     */

    $fields_list_Ar = array('id_mittente','data_ora', 'titolo', 'priorita','flags');
    $sort_field     = ' data_ora desc';

    //if($sess_selected_tester === NULL || $sess_selected_tester === ADA_PUBLIC_TESTER) {
    if(self::isUserBrowsingThePublicTester()) {
    // Sono nel tester pubblico, che poi è il caso di user.php, ma anche ...
      foreach($userObj->getTesters() as $tester) {

        //ADALogger::log("MultiPort::get_messages for tester: $tester");
        $tester_dsn = self::getDSN($tester);

        if($tester_dsn != null) {
          $mh = MessageHandler::instance($tester_dsn);

          $msgs_ha = $mh->get_sent_messages($user_id, $msgType, $fields_list_Ar, $sort_field);

          if (!AMA_DataHandler::isError($msgs_ha)){
            if(is_array($msgs_ha) && !empty($msgs_ha)) {
              $messages_Ar[$tester] = $msgs_ha;
            }
          }
          else {
            /*
             * Return a ADA_Error with delayed error handling.
             */
            return new ADA_Error($msgs_ha,translateFN('Errore in ottenimento messaggi'),NULL,NULL,NULL,NULL,TRUE);
          }
        }
      }
    }
    /*
     * Get messages sent from this user only from the current tester.
     */
    else {
      //ADALogger::log("MultiPort::get_messages for tester: $sess_selected_tester");
      $tester_dsn = self::getDSN($sess_selected_tester);
      if($tester_dsn != null) {

        $mh = MessageHandler::instance($tester_dsn);

        $msgs_ha = $mh->get_sent_messages($user_id, $msgType, $fields_list_Ar, $sort_field);
        if (!AMA_DataHandler::isError($msgs_ha)){
          if(is_array($msgs_ha) && !empty($msgs_ha)) {
            $messages_Ar[$sess_selected_tester] = $msgs_ha;

          }
        }
        else {
          /*
           * Return a ADA_Error with delayed error handling.
           */
          return new ADA_Error($msgs_ha,translateFN('Errore in ottenimento messaggi'),NULL,NULL,NULL,NULL,TRUE);
        }
      }
    }

    return $messages_Ar;
  }
  /**
   * get_messages
   *
   * get all messages for  a given user
   * @param  $ADAUser $userObj
   * @return $msgs_ha array
   */
  static private function get_messages(ADALoggableUser $userObj, $msgType = ADA_MSG_SIMPLE, $msgFlags=array(),$retrieve_only_unread_events=false){//,$field_ar,$clause) {

    $messages_Ar = array();
    $user_id = $userObj->getId();

    /*
     * We need to know if the user is browsing a tester or if he is in his own home page
     * or if he is browsing public contents. To do this, we check on session
     * variable sess_selected_tester. If this variable is not set (==NULL) or
     * it's equal to ADA_PUBLIC_TESTER, we can assume that the user is  in his
     * own home page ot that he is browsing public content.
     *
     * In this case, we retrieve all the messages addressed to this user from
     * all the testers he has subscribed to.
     * In the second case (the user is browsing a tester, and not the public one),
     * we retrieve only the messages addressed to this user from users in this
     * tester.
     */

    $sess_selected_tester = isset($_SESSION['sess_selected_tester']) ? $_SESSION['sess_selected_tester'] : null;

    /*
     * If we are retrieving messages representing events or events proposal,
     * filter them by flag too.
     * NOT USED ANYMORE --> Graffio 24/01/2011
     */
    /*
    if($msgType == ADA_MSG_AGENDA) {
      if(empty($msgFlags)) {
        $clause = 'flags & '.ADA_EVENT_CONFIRMED;
      }
      else {
        $clause = 'flags & '.$msgFlags;
      }
    }
    else {
      $clause = '';
    }
     *
     */

    /*
     * Get messagges not yet read
     */
    if ($retrieve_only_unread_events) {
        $clause = 'read_timestamp <= 0';
    }

    /*
     * Get messages addressed to this user from all the testers the user has subscribed to.
     */

    $fields_list_Ar = array('id_mittente', 'data_ora', 'titolo', 'priorita', 'read_timestamp','flags','utente.username','utente.nome','utente.cognome',);
    $sort_field     = ' data_ora desc';

    include_once ROOT_DIR.'/comunica/include/MessageHandler.inc.php';

    //if($sess_selected_tester === NULL || $sess_selected_tester === ADA_PUBLIC_TESTER) {
    if(self::isUserBrowsingThePublicTester()) {
    // Sono nel tester pubblico, che poi è il caso di user.php, ma anche ...
      foreach($userObj->getTesters() as $tester) {

        //ADALogger::log("MultiPort::get_messages for tester: $tester");
        $tester_dsn = self::getDSN($tester);

        if($tester_dsn != null) {
          $mh = MessageHandler::instance($tester_dsn);

          $clause = isset($clause) ? $clause : null;
          $msgs_ha = $mh->find_messages($user_id, $msgType, $fields_list_Ar, $clause, $sort_field);

          if (!AMA_DataHandler::isError($msgs_ha)){
            if(is_array($msgs_ha) && !empty($msgs_ha)) {
              $messages_Ar[$tester] = $msgs_ha;
            }
          }
          else {
            /*
             * Return a ADA_Error with delayed error handling.
             */
            return new ADA_Error($msgs_ha,translateFN('Errore in ottenimento messaggi'),NULL,NULL,NULL,NULL,TRUE);
          }
        }
      }
    }
    /*
     * Get messages addressed to this user only from the current tester.
     */
    else {
      //ADALogger::log("MultiPort::get_messages for tester: $sess_selected_tester");
      $tester_dsn = self::getDSN($sess_selected_tester);
      if($tester_dsn != null) {

        $mh = MessageHandler::instance($tester_dsn);

		$clause = isset($clause) ? $clause : null;
        $msgs_ha = $mh->find_messages($user_id, $msgType, $fields_list_Ar, $clause, $sort_field);

        if (!AMA_DataHandler::isError($msgs_ha)){
          if(is_array($msgs_ha) && !empty($msgs_ha)) {
            $messages_Ar[$sess_selected_tester] = $msgs_ha;
          }
        }
        else {
          /*
           * Return a ADA_Error with delayed error handling.
           */
          return new ADA_Error($msgs_ha,translateFN('Errore in ottenimento messaggi'),NULL,NULL,NULL,NULL,TRUE);
        }
      }
    }

    return $messages_Ar;
  }

  static public function getUserSentMessages(ADAGenericUser $userObj, $display_mode=1) {

    include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

    if($userObj instanceof ADAGuest) {
      //return new CText(translateFN('Non sono presenti messaggi'));
      return array();
    }

    $result_Ar = self::get_sent_messages($userObj, ADA_MSG_SIMPLE);

    return $result_Ar;
  }


  /**
   *
   * @param  $userObj
   * @return unknown_type
   */
  static public function getUserMessages(ADAGenericUser $userObj, $unread=false) {

    include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

    if($userObj instanceof ADAGuest) {
      //return new CText(translateFN('Non sono presenti messaggi'));
      return array();
    }

    $result_Ar = self::get_messages($userObj, ADA_MSG_SIMPLE, array(), $unread);

    return $result_Ar;
  }

  /**
   *
   * @param  $userObj
   * @return $table a CORE table object
   */
  // MARK: restituire $result_Ar, rimuovere tutto lo switch($display_mode)
  // e il passaggio del parametro display_mode
  static public function getUserAgenda(ADAGenericUser $userObj, $display_mode=1) {

    if($userObj instanceof ADAGuest) {
      //return new CText(translateFN('Non sono presenti appuntamenti'));
      return  array();
    }
    $result_Ar = self::get_messages($userObj, ADA_MSG_AGENDA);

    return $result_Ar;
  }
  // MARK: restituire $result_Ar, rimuovere la chiamata a getEventsAsTable
  static public function getUserEvents(ADAGenericUser $userObj) {
    // include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
    if(!($userObj instanceof ADAUser || $userObj instanceof ADAPractitioner)) {
      return array();
    }

    $user_type = $userObj->getType();
    if($user_type == AMA_TYPE_TUTOR) {
      $msgFlags = ADA_EVENT_PROPOSAL_NOT_OK;
    }
    else {
      $msgFlags = ADA_EVENT_PROPOSED;
    }
    $result_Ar = self::get_messages($userObj, ADA_MSG_AGENDA, $msgFlags);

    return $result_Ar;
    //return self::getEventsAsTable($userObj, $result_Ar);
  }

    static public function getUserEventsNotRead(ADAGenericUser $userObj) {
    // include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
    if(!($userObj instanceof ADAUser || $userObj instanceof ADAPractitioner)) {
      return array();
    }

 /*
    $user_type = $userObj->getType();
    if($user_type == AMA_TYPE_TUTOR) {
      $msgFlags = ADA_EVENT_PROPOSAL_NOT_OK;
    }
    else {
      $msgFlags = ADA_EVENT_PROPOSED;
    }
*/
//    $msgFlags = ADA_EVENT_CONFIRMED;
    $retrieve_only_unread_events = true;

    $msgFlags = isset($msgFlags) ? $msgFlags : null;
    $result_Ar = self::get_messages($userObj, ADA_MSG_AGENDA, $msgFlags, $retrieve_only_unread_events);

    return $result_Ar;
    //return self::getEventsAsTable($userObj, $result_Ar);
  }


  static public function getTestersPointersAndIds() {
    $common_dh = $GLOBALS['common_dh'];
    $field_data_Ar = array('id_tester');
    $result_Ar = $common_dh->get_all_testers($field_data_Ar);
    if(AMA_Common_DataHandler::isError($result_Ar)) {
      return array();
    }

    $testers_Ar = array();
    foreach($result_Ar as $tester_info_Ar) {
      $testers_Ar[$tester_info_Ar['puntatore']] = $tester_info_Ar['id_tester'];
    }

    return $testers_Ar;
  }

  static public function hasThisUserAChatAppointment(ADALoggableUser $userObj) {
    $id_course_instance = DataValidator::is_uinteger($_SESSION['sess_id_course_instance']);
    if($id_course_instance === FALSE) {
      return FALSE;
    }

    $fields_list_Ar = array('id_mittente', 'data_ora', 'titolo', 'priorita', 'read_timestamp');
    $clause         = '(flags & '.ADA_CHAT_EVENT.') AND (flags & '.ADA_EVENT_CONFIRMED.')';
    $sort_field     = 'data_ora desc';
    $mh = MessageHandler::instance(self::getDSN($_SESSION['sess_selected_tester']));

    $msgs_ha = $mh->find_messages($userObj->getId(), ADA_MSG_AGENDA, $fields_list_Ar, $clause, $sort_field);
    if (!AMA_DataHandler::isError($msgs_ha)) {
      $today_time = today_timeFN();
      $today_date = today_dateFN();
      $today_time_date = date(ADA_DATE_FORMAT);
      foreach ($msgs_ha as $one_date) {
        $time_2_add = 30*60; // 30 minuti di arrotondamento.
        $unix_date_app_rounded = $one_date[1] + $time_2_add;
        $udate_now = time();
        if ($udate_now >= $one_date[1] and $udate_now <= $unix_date_app_rounded) {

          //$matches = array();
          //preg_match('/([0-9]+#)/',$one_date[2], $matches);
          //$message_internal_identifier = $matches[1];

          $event_token = ADAEventProposal::extractEventToken($one_date[2]);

          $cdh = ChatDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
          $id_chatroom = $cdh->get_chatroom_with_title_prefixFN($event_token);
          if(AMA_DataHandler::isError($id_chatroom)) {
            return FALSE;
          }
          return $id_chatroom;
          //return TRUE;
        }
      }
    }
    return FALSE;
  }

  static public function hasThisUserAVideochatAppointment(ADALoggableUser $userObj) {
   $id_course_instance = DataValidator::is_uinteger($_SESSION['sess_id_course_instance']);
    if($id_course_instance === FALSE) {
      return FALSE;
    }

    $fields_list_Ar = array('id_mittente', 'data_ora', 'titolo', 'priorita', 'read_timestamp');
    $clause         = '(flags & '.ADA_VIDEOCHAT_EVENT.') AND (flags & '.ADA_EVENT_CONFIRMED.')';
    $sort_field     = 'data_ora desc';
    $mh = MessageHandler::instance(self::getDSN($_SESSION['sess_selected_tester']));

    $msgs_ha = $mh->find_messages($userObj->getId(), ADA_MSG_AGENDA, $fields_list_Ar, $clause, $sort_field);
    if (!AMA_DataHandler::isError($msgs_ha)) {
      $today_time = today_timeFN();
      $today_date = today_dateFN();
      $today_time_date = date(ADA_DATE_FORMAT);
      foreach ($msgs_ha as $one_date) {
        $time_2_add = 30*60; // 30 minuti di arrotondamento.
        $unix_date_app_rounded = $one_date[1] + $time_2_add;
        $udate_now = time();
        if ($udate_now >= $one_date[1] and $udate_now <= $unix_date_app_rounded) {
      	  $event_token = ADAEventProposal::extractEventToken($one_date[2]);
          return $event_token;
        }
      }
    }
    return FALSE;
  }

  static public function hasThisUserAnAppointmentInThisDate(ADALoggableUser $userObj, $appointment_timestamp) {


    $fields_list_Ar = array('id_mittente', 'data_ora', 'titolo', 'priorita', 'read_timestamp','flags','utente.username');
    $sort_field     = ' data_ora desc';
    $clause = 'data_ora = '.$appointment_timestamp; //.' AND flags & '.ADA_EVENT_CONFIRMED;
 /*   //$result_Ar = self::get_messages($userObj, ADA_MSG_AGENDA);
    if($userObj instanceof ADAPractitioner) {
      //controlliamo solo sul tester selezionato
      $tester_dsn = self::getDSN($sess_selected_tester);
      if($tester_dsn != null) {
        $mh = MessageHandler::instance($tester_dsn);
        $msgs_ha = $mh->find_messages($userObj->getId(),ADA_MSG_AGENDA,$fields_list_Ar,$clause,$sort_field);
      }
    }

    if($userObj instanceof ADAUser) {
*/      //controlliamo su tutti i tester ai quali l'utente e' associato
      foreach($userObj->getTesters() as $tester) {
        $tester_dsn = self::getDSN($tester);
        if($tester_dsn != null) {
          $mh = MessageHandler::instance($tester_dsn);
          $msgs_ha = $mh->find_messages($userObj->getId(),ADA_MSG_AGENDA,$fields_list_Ar,$clause,$sort_field);
          if(count($msgs_ha) > 0) {
            return TRUE;
          }
        }
      }

  //  }
    return FALSE;
  }

  /**
   *
   * @param  $userObj
   * @param  $appointmentsIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function markUserAppointmentsAsRead(ADALoggableUser $userObj, $appointmentsIdsAr = array()) {
    $user_id = $userObj->getId();

    // TODO: refactor
    foreach ($appointmentsIdsAr as $appointment_id) {
      $data_Ar = self::geTesterAndMessageId($appointment_id);

      $mh = MessageHandler::instance(self::getDSN($data_Ar['tester']));
      $result = $mh->set_messages($user_id, array($data_Ar['message_id']),'R');
      // FIXME: gestione errore?
    }
  }

  /**
   *
   * @param  $userObj
   * @param  $appointmentsIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function markUserAppointmentsAsUnread(ADALoggableUser $userObj, $appointmentsIdsAr = array()) {
    $user_id = $userObj->getId();

    // TODO: refactor
    foreach ($appointmentsIdsAr as $appointment_id) {
      $data_Ar = self::geTesterAndMessageId($appointment_id);

      $mh = MessageHandler::instance(self::getDSN($data_Ar['tester']));
      $result = $mh->set_messages($user_id, array($data_Ar['message_id']),'N');
      // FIXME: gestione errore?
    }
  }

  /**
   *
   * @param  $userObj
   * @param  $messagesIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function markUserMessagesAsRead(ADALoggableUser $userObj, $messagesIdsAr = array()) {
    return self::markUserAppointmentsAsRead($userObj,$messagesIdsAr);
  }

  /**
   *
   * @param  $userObj
   * @param  $messagesIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function markUserMessagesAsUnread(ADALoggableUser $userObj, $messagesIdsAr = array()) {
    return self::markUserAppointmentsAsUnread($userObj,$messagesIdsAr);
  }

  /**
   *
   * @param  $userObj
   * @param  $appointmentsIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function removeUserAppointments(ADALoggableUser $userObj, $appointmentsIdsAr = array()) {
    $user_id = $userObj->getId();

    // TODO: refactor
    foreach ($appointmentsIdsAr as $appointment_id) {
      $data_Ar = self::geTesterAndMessageId($appointment_id);

      $mh = MessageHandler::instance(self::getDSN($data_Ar['tester']));
      $result = $mh->remove_messages($user_id, array($data_Ar['message_id']));
      // FIXME: gestione errore?
    }
  }

  /**
   *
   * @param  $userObj
   * @param  $appointmentsIdsAr
   * @return unknown_type
   */
  // MARK: NON MODIFICARE
  static public function removeUserMessages(ADALoggableUser $userObj, $messagesIdsAr = array()) {
    return self::removeUserAppointments($userObj, $messagesIdsAr);
  }


  /**
   * getUserAppointment
   *
   * @param  ADALoggableUser $userObj
   * @param  string $appointment_id
   * @return array $msg_ha
   */
  // MARK: NON MODIFICARE
  static public function getUserAppointment(ADALoggableUser $userObj, $appointment_id) {


    $data_Ar = self::geTesterAndMessageId($appointment_id);

    $mh = MessageHandler::instance(self::getDSN($data_Ar['tester']));

    $msg_ha = $mh->get_message($userObj->getId(), $data_Ar['message_id']);
    if(AMA_DataHandler::isError($msg_ha)) {
      /*
       * Return a ADA_Error object with delayed error handling
       */
      return new ADA_Error($msg_ha, translateFN('Errore durante lettura appuntamento'),
      NULL,NULL,NULL,NULL,TRUE);

    }

    return $msg_ha;
  }

  /**
   * getUserAppointment
   *
   * @param  ADALoggableUser $userObj
   * @param  string $appointment_id
   * @return array $msg_ha
   */
  // MARK: NON MODIFICARE
  static public function getUserMessage(ADALoggableUser $userObj, $message_id) {
    return self::getUserAppointment($userObj, $message_id);
  }


  /**
   * Given a message_id, returns an associative array with keys tester and message_id set.
   * tester contains the tester pointer from which retrieve the message
   * message_id is the id of the message to retrieve
   *
   * @param  string $appointment_id
   * @return array
   */
  // wrapper for next function with a strange name ...
   static public function getTesterAndMessageId($appointment_id) {
    return self::geTesterAndMessageId($appointment_id);
   }

  // MARK: NON MODIFICARE
  static public function geTesterAndMessageId($appointment_id) {
    /*
     * First, check if appointment is in the form <number> or <number1>_<number2>.
     * In the first case, read the appointment with id = number from $sess_selected_tester.
     * In the second case, read the appointment with id = number2 from tester
     * with id number1.
     */
    $regexp = '/^([1-9][0-9]*)_([0-9]*)$/';
    $matches = array();
    if (preg_match($regexp, $appointment_id, $matches) == 0) {
      // first case
      $message_id = $appointment_id;
      $tester = $_SESSION['sess_selected_tester'];
    }
    else {
      // second case
      $tester_id  = $matches[1];
      $message_id = $matches[2];

      $common_dh = $GLOBALS['common_dh'];
      $tester_infoAr = $common_dh->get_tester_info_from_id($tester_id);
      if(AMA_Common_DataHandler::isError($tester_infoAr)) {
        /*
         * Return a ADA_Error object with delayed error handling
         */
        return new ADA_Error($tester_infoAr, translateFN('Errore in ottenimento informazioni tester'),
        NULL,NULL,NULL,NULL,TRUE);
      }
      // get pointer from $tester_infoAr
      $tester = $tester_infoAr[10];
    }
    return array('tester' => $tester, 'message_id' => $message_id);
  }

  static public function isUserBrowsingThePublicTester() {

    $sess_selected_tester = isset($_SESSION['sess_selected_tester']) ? $_SESSION['sess_selected_tester'] : '';
    return $sess_selected_tester == NULL || $sess_selected_tester == ADA_PUBLIC_TESTER;
  }

  static public function getDataForTesterActivityReport() {
    $common_dh = $GLOBALS['common_dh'];

    $testers_activity_dataAr = array();
    $testers_infoAr = $common_dh->get_all_testers(array('id_tester','nome'));

    if(AMA_Common_DataHandler::isError($testers_infoAr)) {
      return array();
    }
    $current_timestamp = time();
    foreach($testers_infoAr as $tester_infoAr) {
      $tester_dsn = self::getDSN($tester_infoAr['puntatore']);
      if($tester_dsn != NULL) {
        $tester_dh = AMA_DataHandler::instance($tester_dsn);
        $users_count = $tester_dh->count_users_by_type(array(AMA_TYPE_STUDENT));
        if(AMA_DataHandler::isError($users_count)) {
          $users_count = 0;
        }
        $active_eguidance_sessions = $tester_dh->count_active_course_instances($current_timestamp);
        if(AMA_DataHandler::isError($active_eguidance_sessions)) {
          $active_eguidance_sessions = 0;
        }
        $testers_activity_dataAr[] = array(
          'id_tester'	    => $tester_infoAr['id_tester'],
          'nome'		    => $tester_infoAr['nome'],
          'numero_utenti'	=> $users_count,
          'eg_attive'       => $active_eguidance_sessions
        );
      }
    }

    return $testers_activity_dataAr;
  }

  /**
   * count_new_notes
   * @author giorgio 24/apr/2013
   *
   *
   * @param ADALoggableUser $userObj	user data to check for new notes
   * @param int $courseInstanceId		course instance id to check for new notes
   * @param bool $countNodes			true to check for noDes instead of noTes. Defaults to false
   *
   * @return int count of new noTes or noDes
   */


  static public function count_new_notes(ADALoggableUser $userObj, $courseInstanceId) {

  	if($userObj instanceof ADAGuest) {
  		return  0;
  	}
  	$common_dh = $GLOBALS['common_dh'];

  	$testers_activity_dataAr = array();
  	$testers_infoAr = $common_dh->get_all_testers(array('id_tester','nome'));

    if (!MULTIPROVIDER) {
      $testers_infoAr = array_values(array_filter($testers_infoAr, function ($el) {
        return strcmp($el['puntatore'], $GLOBALS['user_provider']) === 0;
      }));
    }

  	if(AMA_Common_DataHandler::isError($testers_infoAr)) {
  		return array();
  	}
  	$userId = $userObj->getId();

  	$result = 0;

  	foreach($testers_infoAr as $tester_infoAr) {
  		$tester_dsn = self::getDSN($tester_infoAr['puntatore']);
  		if($tester_dsn != NULL) {
  			$tester_dh = AMA_DataHandler::instance($tester_dsn);
  			$result +=  $tester_dh->count_new_notes_in_course_instances( $courseInstanceId, $userId);
  		}
  	}
  	return $result;
  }

  /**
   * updates new nodes in session by unsetting visited nodes
   *
   * @param ADALoggableUser $userObj	user to get new nodes array for
   *
   * @return array contains id_nodo, id_istanza and titolo of the new nodes
   *
   * @access public
   *
   * @author giorgio 30/apr/2013
   */
   public static function update_new_nodes_in_session ($userObj)
   {


   		if($userObj instanceof ADAGuest) {
   			return  0;
   		}
   		$common_dh = $GLOBALS['common_dh'];

   		$testers_activity_dataAr = array();
   		$testers_infoAr = $common_dh->get_all_testers(array('id_tester','nome'));

   		if(AMA_Common_DataHandler::isError($testers_infoAr)) {
   			return array();
   		}

      if (!MULTIPROVIDER) {
        $testers_infoAr = array_values(array_filter($testers_infoAr, function ($el) {
          return strcmp($el['puntatore'], $GLOBALS['user_provider']) === 0;
        }));
      }

   		$userId = $userObj->getId();
   		$whatsnew = $userObj->getwhatsnew();

   		$result = array();

   		foreach($testers_infoAr as $tester_infoAr) {
   			$tester_dsn = self::getDSN($tester_infoAr['puntatore']);
   			if($tester_dsn != NULL) {
   				$tester_dh = AMA_DataHandler::instance($tester_dsn);
   				$todel = $tester_dh->get_updates_nodes($userObj,$tester_infoAr['puntatore']);
   				// unset proper array keys
   				foreach ($whatsnew[$tester_infoAr['puntatore']] as $key=>$whatsnewItem)
   				{
   					foreach ($todel as $todelitem)
   						foreach ($todelitem as $todelstring)
   						if (array_search($todelstring, $whatsnewItem)!==false) unset($whatsnew[$tester_infoAr['puntatore']][$key]);
   				}
   			}
   		}
		$userObj->setwhatsnew ($whatsnew);
   		return $userObj->getwhatsnew();
   }


  /**
   * gets new nodes array for given courseinstance and user
   * directly called by user class constructor to get all new nodes to be put in the sess_userObj object.
   *
   * @param ADALoggableUser $userObj	user to get new nodes array for
   *
   * @return array contains id_nodo, id_istanza and titolo of the new nodes
   *
   * @access public
   *
   * @author giorgio 29/apr/2013
   */

  public static function get_new_nodes($userObj)
  {
     	if($userObj instanceof ADAGuest) {
  		return  0;
  	}
  	$common_dh = $GLOBALS['common_dh'];

  	$testers_activity_dataAr = array();
  	$testers_infoAr = $common_dh->get_all_testers(array('id_tester','nome'));
    if (!MULTIPROVIDER) {
      $testers_infoAr = array_values(array_filter($testers_infoAr, function ($el) {
        return strcmp($el['puntatore'], $GLOBALS['user_provider']) === 0;
      }));
    }

  	if(AMA_Common_DataHandler::isError($testers_infoAr)) {
  		return array();
  	}
  	$userId = $userObj->getId();

  	$result = array();

  	foreach($testers_infoAr as $tester_infoAr) {
  		if (!isset($result[$tester_infoAr['puntatore']])) $result[$tester_infoAr['puntatore']] = array();
  		$tester_dsn = self::getDSN($tester_infoAr['puntatore']);
  		if($tester_dsn != NULL) {
  			$tester_dh = AMA_DataHandler::instance($tester_dsn);
  				$result[$tester_infoAr['puntatore']] = array_merge ($result[$tester_infoAr['puntatore']],
  																	$tester_dh->get_new_nodes( $userId ) );
  		}
  	}

  	return $result;
  }

  /**
   * checkWhatsNew
   * @author giorgio 24/apr/2013
   *
   *
   * @param ADALoggableUser $userObj	user data to check for something new
   * @param int $courseInstanceId		course instance id to check for something new
   *
   * @return boolean true if there's some new messages for passed user in passed course instance
   */
  static public function checkWhatsNew($userObj, $courseInstanceId, $courseId=0)
  {
  	  // new nodes in course

  	  $userObj->updateWhatsNew();

  	  $count_new_nodes = 0;
  	   foreach ($userObj->getwhatsnew() as $whatsnewintester)
  	   {
  	    	foreach ($whatsnewintester as $whatsnewelem)
  	    	{
  	    		if (strpos($whatsnewelem['id_nodo'],$courseId)!==false) $count_new_nodes++;
  	    	}
  	   }
      // new messages in forum, AKA NOTES!!!
	  $msg_forum_count = MultiPort::count_new_notes($userObj,$courseInstanceId);

	  return ( ($count_new_nodes >0) || ($msg_forum_count>0) );
  }

  /**
   * removeUserExtraData
   *
   * Removes a row from the user extra datas.
   *
   * @author giorgio 20/giu/2013
   *
   * @param ADALoggableUser $userObj user for which to delete the row
   * @param int $extraTableId	row id to be deleted
   * @param string $extraTableClass class of row to be deleted
   *
   * @return boolean on error | query result
   *
   * @access public
   */

  static public function removeUserExtraData (ADALoggableUser $userObj ,$extraTableId=null, $extraTableClass=false )
  {
  	if ($extraTableId!==null && $extraTableClass!==false)
  	{
  		$user_id = $userObj->getId();
  		$testers = $userObj->getTesters();
  		if(!is_array($testers)) {
  			$testers = array();
  		}
  		if ($user_id == 0) {
  			return FALSE;
  		}

  		foreach($testers as $tester) {
  			$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
  			$result = $tester_dh->remove_user_extraRow($user_id, $extraTableId, $extraTableClass);
  		}
  		return $result;
  	}
  	else return false;
  }

  /*
   * Used by admin/log_report.php
   */
    static public function log_report($pointer=NULL,$Services_Type=NULL) {
    $log_dataAr = array();
    $common_dh = $GLOBALS['common_dh'];
    $filedArray = array('nome','ragione_sociale');
    if(isset($pointer)){
        $testers_list=$common_dh->get_tester_info_from_pointer($pointer);
        if (!AMA_DB::isError($testers_list)) {
            $tester_name = $testers_list[1];
            $tester=$pointer;
            $tester_dsn = self::getDSN($tester);
            if ($tester_dsn != null) {
                $tester_dh = AMA_DataHandler::instance($tester_dsn);
                $result = $tester_dh->tester_log_report($tester_name,null);
                if (!AMA_DB::isError($result)) {
                    $result['provider']= $tester_name;
                    $log_dataAr[$tester] = $result;
                }
            }
        }
    }else{
        $testers_list = $common_dh->get_all_testers($filedArray);
        if (!AMA_DB::isError($testers_list)) {
            foreach ($testers_list as $testerAr) {
                $tester_name = $testerAr['nome'];
                $tester = $testerAr['puntatore'];
                $tester_dsn = self::getDSN($tester);
                if ($tester_dsn != null) {
                    $tester_dh = AMA_DataHandler::instance($tester_dsn);
                    $result = $tester_dh->tester_log_report($tester_name,$Services_Type);
                    if (!AMA_DB::isError($result)) {
                        $result['provider']= $tester_name;
                        $log_dataAr[$tester] = $result;
                    }
                }
            }
        }

    }

    return $log_dataAr;
    }

}

?>