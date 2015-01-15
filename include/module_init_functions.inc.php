<?php
/**
 * Functions used by module_init.inc.php
 *
 * PHP version >= 5.0
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		module_init_functions
 * @version		0.1
 */

/**
 *
 * @return unknown_type
 */
function session_controlFN($neededObjAr=array(), $allowedUsersAr=array(), $trackPageToNavigationHistory = true) {
  //ADALogger::log('session control FN');

  if(!session_start()) {
    /*
     * As of PHP 5.3.0 if session fails to star for some reason,
     * FALSE is returned.
     */
      ADALogger::log('session failed to start');
  }
  
  /**
   * giorgio 11/ago/2013
   * if it's not multiprovider and we're asking for index page,
   * sets the selected provider by detecting it from the filename that's executing
   */
   if (!MULTIPROVIDER) {
   	 
	list($client) = explode ('.',preg_replace('/(http[s]?:\/\/)/', '', $_SERVER['SERVER_NAME']));
	
	if (isset($client) && !empty ($client) && is_dir(ROOT_DIR.'/clients/'.$client))
  	{	  		
  		// $_SESSION['sess_user_provider'] = $client;
  		$GLOBALS['user_provider'] = $client;
  		// other session vars per provider may go here...  		  		
  	}
  	else unset ($GLOBALS['user_provider']);
//   	if (isset($_SESSION['sess_user_provider']) && !empty($_SESSION['sess_user_provider']))
//   		$GLOBALS['user_provider'] = $_SESSION['sess_user_provider'];
  	// if it's not set and its value is not equal to the new passed one, set a cookie that shall expire in one year
//   	if (isset($GLOBALS['user_provider']) && $_COOKIE['ada_provider']!=$GLOBALS['ada_provider'])
//   		setcookie('ada_provider',$GLOBALS['user_provider'],+time()+ 86400 *365 ,'/');
  } // end if !MULTIPROVIDER
  
  /*
   * Navigation history
   */
  require_once ROOT_DIR.'/include/navigation_history.inc.php';

  $debug_backtrace = debug_backtrace();
  $level = sizeof($debug_backtrace) - 1;
  
  /**
   * giorgio 06/set/2013
   * detect mobile device feature
   */  
  if (!isset($_SESSION['mobile-detect']))
  { 
  	$_SESSION['mobile-detect'] = new Mobile_Detect();
  }
  
  /**
   * @author giorgio 10/nov/2014
   * 
   * sets the IE-version session variable to a float holding
   * the IE version or false if non-IE or IE version >= 11.0
   */
  if (isset($_SESSION['mobile-detect']) && !isset($_SESSION['IE-version'])) {
  	$isIE = $_SESSION['mobile-detect']->version('IE');
  	if ($isIE!==false && is_numeric($isIE)) {
  		$_SESSION['IE-version'] = floatval($isIE);
  	} else {
  		$_SESSION['IE-version'] = false;
  	}
  }
  
  if ($trackPageToNavigationHistory) {
	$caller_file     = $debug_backtrace[$level]['file'];
	if ( !isset($_SESSION['sess_navigation_history']) ) {
	  $navigation_history = new NavigationHistory(NAVIGATION_HISTORY_SIZE);
	  $navigation_history->addItem($caller_file);
	  $_SESSION['sess_navigation_history'] = $navigation_history;
	}
	else {
	  $navigation_history = $_SESSION['sess_navigation_history'];
	  $navigation_history->addItem($caller_file);
	  $_SESSION['sess_navigation_history'] = $navigation_history;
	}
  }

  $GLOBALS['sess_id'] = session_id();

  $parm_errorHa = parameter_controlFN($neededObjAr,$allowedUsersAr);
  //var_dump($parm_errorHa);


  if($parm_errorHa['session']) {
  }

  if($parm_errorHa['user']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_USER_REQUIRED_BUT_NOT_FOUND,
      ADA_ERROR_SEVERITY_FATAL,
      'index.php'
    );
  }

  /*
   * URL a cui redirezionare l'utente in caso di errore su corso, istanza_corso, nodo
   */
  $sess_userObj = $_SESSION['sess_userObj'];
  if($sess_userObj instanceof ADAGenericUser) {
    $redirectTo = $sess_userObj->getHomePage();
  }
  else {
    $redirectTo = 'index.php';
  }

  if($parm_errorHa['course']) {
  	/**
  	 * If parameter_controlFN has put an array in the 'course' key
  	 * this means that the user is asking for a node that belongs to
  	 * a course for which the user is subscribed to more than one instance.
  	 * 
  	 *  The list of the insance id is passed in the 'course key as an
  	 *  array and must be passed to the browsing/select_instance script
  	 *  that is responsible for asking the user to select an instance. 
  	 */
  	if (is_array($parm_errorHa['course'])) {  		
  		$errObj = new ADA_Error(
  		  NULL,
  		  NULL,
  		  NULL,
          ADA_ERROR_ID_CINST_REQUIRED_BUT_NOT_FOUND,
          ADA_ERROR_SEVERITY_FATAL,
          'browsing/select_instance.php?node='.$parm_errorHa['node'].'&instances='.urlencode(implode(',',$parm_errorHa['course']))
  		);
  	} else {
	    // FIXME: passare messaggio di errore
	    $errObj = new ADA_Error(
	      NULL,
	      NULL,
	      NULL,
	      ADA_ERROR_ID_SERVICE_REQUIRED_BUT_NOT_FOUND,
	      ADA_ERROR_SEVERITY_FATAL,
	      $redirectTo
	    );
  	}
  }

  if($parm_errorHa['course_instance']) {
    // FIXME: passare messaggio di errore
    // TODO: forse il controllo su ADAGuest in questo if puo' essere rimosso, 
    // dato che non settiamo $parm_errorHa['coutrse_instance'] nel caso in cui 
    // l'utente e' sul tester pubblico (ADAGuest e' solo sul tester pubblico) 
    if (!($sess_userObj instanceof ADAAuthor) &&
   !($sess_userObj instanceof  ADAGuest) // ??? steve 6/9
    ) {
      $errObj = new ADA_Error(
        NULL,
        NULL,
        NULL,
        ADA_ERROR_ID_CINST_REQUIRED_BUT_NOT_FOUND,
        ADA_ERROR_SEVERITY_FATAL,
        $redirectTo
      );
    }
  }

  if($parm_errorHa['node']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_NODE_REQUIRED_BUT_NOT_FOUND,
      ADA_ERROR_SEVERITY_FATAL,
      $redirectTo
    );
  }

  if($parm_errorHa['guest_user_not_allowed']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_CINST_NOT_PUBLIC,
      ADA_ERROR_SEVERITY_FATAL,
      $redirectTo
    );
  }

// FIXME: controllare su livello utente?
//  if($parm_errorHa['user_level']) {
//  }

  $GLOBALS['sess_id_user']            = isset($_SESSION['sess_id_user']) ? $_SESSION['sess_id_user'] : null;
  $GLOBALS['sess_id_user_type']       = isset($_SESSION['sess_id_user_type']) ? $_SESSION['sess_id_user_type'] : null;
  $GLOBALS['sess_user_level']         = isset($_SESSION['sess_user_level']) ? $_SESSION['sess_user_level'] : null;
  $GLOBALS['sess_id_course']          = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
  $GLOBALS['sess_id_course_instance'] = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
  $GLOBALS['sess_id_node']            = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;
  $GLOBALS['sess_selected_tester']    = isset($_SESSION['sess_selected_tester']) ? $_SESSION['sess_selected_tester'] : null;
  $GLOBALS['sess_user_language']      = isset($_SESSION['sess_user_language']) ? $_SESSION['sess_user_language'] : null;
}

function parameter_controlFN($neededObjAr=array(), $allowedUsersAr=array()) {

  $invalid_session         = FALSE;
  $invalid_user            = FALSE;
  $invalid_node            = FALSE;
  $invalid_course          = FALSE;
  $invalid_course_instance = FALSE;
  $invalid_user_level      = FALSE;
  $guest_user_not_allowed  = FALSE;

  /*
   * ADA common data handler
   */
  $common_dh = isset($GLOBALS['common_dh']) ? $GLOBALS['common_dh'] : null;
  if(!$common_dh instanceof AMA_Common_DataHandler) {
    $common_dh = AMA_Common_DataHandler::instance();
    $GLOBALS['common_dh'] = $common_dh;
  }

  /*
   * User object: always load a user
   */
  $sess_id_user = isset($_SESSION['sess_id_user']) ? (int)$_SESSION['sess_id_user'] : 0;
  $sess_userObj = read_user($sess_id_user);
  if (ADA_Error::isError($sess_userObj)) {
    $sess_userObj->handleError();
  }
  $_SESSION['sess_id_user'] = $sess_id_user;
  if($sess_userObj instanceof ADAGenericUser) {
    $_SESSION['sess_userObj'] = $sess_userObj;
    
    /*
     * Check if this user is allowed to access the current module
     */
    if(!in_array($sess_userObj->getType(), $allowedUsersAr)) {
      header('Location: '.$sess_userObj->getHomePage());
      exit();
    }
  }
  else {
    unset($_SESSION['sess_userObj']);
    $invalid_user = TRUE;
  }

  $id_profile = $sess_userObj->getType();

  loadServiceTypes();
  
  /*
   * Get needed object for this user from $neededObjAr 
   */
  if(is_array($neededObjAr) && isset($neededObjAr[$id_profile]) && is_array($neededObjAr[$id_profile])) {
    $thisUserNeededObjAr = $neededObjAr[$id_profile];
  }
  else {
    $thisUserNeededObjAr = array();
  }

  /*
   * 
   * 'default_tester' AL MOMENTO VIENE RICHIESTO SOLO DA USER.php
   * QUI ABBIAMO NECESSITA' DI CANCELLARE LA VARIABILE DI SESSIONE
   * sess_id_course.
   * Gia' che ci siamo facciamo unset anche di sess_id_node 
   * e di sess_id_course_instance
   * 
   * Tester selection: 
   * 
   * se ho richiesto la connessione al database del tester di default, 
   * controllo che il tipo di utente sia ADAUser (al momento e' l'unico ad
   * avere questa necessita').
   * 
   * se non ho richiesto la connessione al tester di default, allora verifico
   * se l'utente e' di tipo ADAUser, e ottengo la connessione al database
   * tester appropriato. 
   */
  if(in_array('default_tester',$thisUserNeededObjAr) && $id_profile == AMA_TYPE_STUDENT) {
    $_SESSION['sess_selected_tester'] = NULL;

    unset($_SESSION['sess_id_course']);
    unset($_SESSION['sess_id_course_instance']);
    unset($_SESSION['sess_id_node']);

  }
  else if($id_profile == AMA_TYPE_STUDENT){
  	if (isset($_REQUEST['id_course']))
    	$id_course      = DataValidator::is_uinteger($_REQUEST['id_course']/*$GLOBALS['id_course']*/);
  	else
  		$id_course = false;
  	
  	if (isset($_SESSION['sess_id_course']))
    	$sess_id_course = DataValidator::is_uinteger($_SESSION['sess_id_course']);
  	else
  		$sess_id_course = false;
  	
  	if (isset($_REQUEST['id_node']))
  		$req_id_node = DataValidator::validate_node_id($_REQUEST['id_node']);
  	else
  		$req_id_node = false;
    
    if ($id_course === FALSE && $sess_id_course === FALSE && $req_id_node!==FALSE) {
    	$id_course = substr($req_id_node, 0, strpos($req_id_node, '_'));
    }
    
    if($id_course !== FALSE && $id_course !== $sess_id_course) {

      $tester_infoAr = $common_dh->get_tester_info_from_id_course($id_course);
      if (AMA_Common_DataHandler::isError($tester_infoAr)) {
        $selected_tester = NULL;
      }
      else {
        $selected_tester = $tester_infoAr['puntatore'];
      } 
      $_SESSION['sess_selected_tester'] = $selected_tester;  
    }
  }
  
  /* 
   * ADA tester data handler
   * Data validation on $sess_selected_tester is performed by MultiPort::getDSN()
   */
  /**
   * giorgio 12/ago/2013
   * set selected tester if it's not a multiprovider environment
   */
  if (!MULTIPROVIDER && isset($GLOBALS['user_provider']))
  {
  	$sess_selected_tester = $GLOBALS['user_provider'];
  }
  else
  {
  	$sess_selected_tester = isset($_SESSION['sess_selected_tester']) ? $_SESSION['sess_selected_tester'] : null;
  }
  
  //$dh = AMA_DataHandler::instance(MultiPort::getDSN($sess_selected_tester));
  
  $sess_selected_tester_dsn = MultiPort::getDSN($sess_selected_tester);
  $_SESSION['sess_selected_tester_dsn'] = $sess_selected_tester_dsn;

  $dh = new AMA_DataHandler($sess_selected_tester_dsn);
  $GLOBALS['dh'] = $dh;

  if(empty($GLOBALS['sess_id'])) {
    $invalid_session = TRUE;
  }

  /*
   * Node object
   */
  // TODO: portare in sessione $nodeObj?
  if(in_array('node',$thisUserNeededObjAr)) {
    $id_node      = isset($_REQUEST['id_node']) ? DataValidator::validate_node_id($_REQUEST['id_node']/*$GLOBALS['id_node']*/) : false; 
    $sess_id_node = isset($_SESSION['sess_id_node']) ? DataValidator::validate_node_id($_SESSION['sess_id_node']) : false;
    
    if($id_node !== FALSE) {
        $dataHa = $dh->get_node_info($id_node);

        if (AMA_DataHandler::isError($dataHa) || !is_array($dataHa)) {
          $invalid_node = TRUE;            
        } else {
          $_SESSION['sess_id_node'] = $id_node;
        }
    }
    elseif($sess_id_node !== FALSE) {
        $dataHa = $dh->get_node_info($sess_id_node);

        if (AMA_DataHandler::isError($dataHa) || !is_array($dataHa)) {
          $invalid_node = TRUE;            
        } else {
          $_SESSION['sess_id_node'] = $sess_id_node;
        }
    }
    else {
      $invalid_node = TRUE;
    }
  }

  /*
   * Course object
   */
  if(in_array('course',$thisUserNeededObjAr)) {
    
    $id_course      = isset($_REQUEST['id_course']) ? DataValidator::is_uinteger($_REQUEST['id_course']/*$GLOBALS['id_course']*/) : false;
    $sess_id_course = isset ($_SESSION['sess_id_course']) ? DataValidator::is_uinteger($_SESSION['sess_id_course']) : false;
    /* extracting the course id from node id, if given */
    if (isset($_SESSION['sess_id_node']) && !$invalid_node) {
//    if ($nodeObj instanceof Node){
      $courseIdFromNodeId =  substr($_SESSION['sess_id_node'], 0, strpos($_SESSION['sess_id_node'], '_'));
      $sess_courseObj = read_course($courseIdFromNodeId);  
      
      if (ADA_Error::isError($sess_courseObj)) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;      
      }
      else if ($sess_userObj instanceof ADAGuest  && !$sess_courseObj->getIsPublic ()) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;
      } else {
          $_SESSION['sess_courseObj'] = $sess_courseObj;
          $_SESSION['sess_id_course'] = $courseIdFromNodeId;          
      }
    }    
    elseif($id_course !== FALSE) {
      $sess_courseObj = read_course($id_course);
      if (ADA_Error::isError($sess_courseObj)) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;
      } else if ($sess_userObj instanceof ADAGuest  && !$sess_courseObj->getIsPublic ()) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;
      } else {
          $_SESSION['sess_courseObj'] = $sess_courseObj;
          $_SESSION['sess_id_course'] = $id_course;
      }
    }
    elseif($sess_id_course !== FALSE) {
      $sess_courseObj = read_course($sess_id_course);
      if (ADA_Error::isError($sess_courseObj)) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;
      } else if ($sess_userObj instanceof ADAGuest  && !$sess_courseObj->getIsPublic ()) {
          unset($_SESSION['sess_courseObj']);
          $invalid_course = TRUE;
      } else {
          $_SESSION['sess_courseObj'] = $sess_courseObj;
          $_SESSION['sess_id_course'] = $sess_courseObj->getId();
      }
    }
    else {
      unset($_SESSION['sess_courseObj']);
      $invalid_course = TRUE;
    }
  }
  else {
    unset($_SESSION['sess_courseObj']);
  }

  /**
   * If in a valid NON PUBLIC course and user is student or tutor
   * and
   *  $_SESSION['sess_id_course'] (that is the course_id the user is going into)
   * 	IS NOT EQUAL TO
   *  $sess_id_course (that is the course_id the user is coming form)
   *  
   *  The user has clicked a cross course link, and is handled by unsetting the
   *  $_SESSION['sess_id_course_instance'] and looking for a course instance
   *  to which the user is subscribed.
   *  
   */  
  if ( $invalid_course === FALSE && $invalid_node === FALSE && 
  	   isset ($sess_courseObj) && !$sess_courseObj->getIsPublic () &&
  	   in_array($sess_userObj->getType(), array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR)) &&
  	   is_numeric($sess_id_course) &&
  	   intval($_SESSION['sess_id_course']) !== intval($sess_id_course) ) {
  	
  	/**
  	 * unset sess_id_course_instance
  	 */
  	unset ($_SESSION['sess_id_course_instance']);
  	
  	/**
  	 * Try to find an instance of target course where used is subscribed
  	 */
  	$getAll = true;
  	
  	/**
  	 * Need to get instance the user is allowed to browse, based on user type
  	 */
  	switch ($sess_userObj->getType()) {
  		case AMA_TYPE_STUDENT:
  			$instances = $dh->get_course_instance_for_this_student_and_course_model($sess_userObj->getId(), $_SESSION['sess_id_course'], $getAll);
  			break;
  		case AMA_TYPE_TUTOR:
  			$tutorInstances = $dh->get_tutors_assigned_course_instance($sess_userObj->getId(),$_SESSION['sess_id_course']);
  			if (!AMA_DB::isError($tutorInstances)) {  				
  				/**
  				 * the returned array is array[id_tutor]=>array[key]=>array['id_istanza_corso']
  				 * and needs to be converted to reflect the structre returned in student case
  				 */
  				foreach($tutorInstances[$sess_userObj->getId()] as $tutorInstance) {
  					$instances[]['id_istanza_corso'] = $tutorInstance['id_istanza_corso'];
  				}
  			} else $instances = null;
  			break;
  	}
  	
  	if (!AMA_DB::isError($instances) && count($instances)>0) {
  		if (count($instances)==1) {
  			/**
  			 * User is subscribed to one instance only, good!
  			 * Set the $target_course_instance var and proceed
  			 */
  			$target_course_instance = $instances[0]['id_istanza_corso'];
  		} else if (count($instances)>1) {
  			/**
  			 * If there's more than one instance, must build an array of
  			 * found instances to ask the user to select one.
  			 * 
  			 * This array is returned in the 'course' key of the returned
  			 * array and so $invalid_course must be populated accordingly.
  			 * 
  			 * The node that was requested is returned in the 'node' key of
  			 * the returned array and so $invalid_node must be populated. 
  			 */
  			foreach ($instances as $instance) {
  				$invalid_course[] = $instance['id_istanza_corso'];
  				$invalid_node = $_SESSION['sess_id_node'];
  			}
  		}  	
  	} else {
  		/**
  		 * Mark the course as invalid, and unset session var
  		 */
  		$invalid_course = TRUE;
  		unset ($_SESSION['sess_id_course']);
  	}
  }
  
  /*
   * Course_instance object
   */
  if(in_array('course_instance',$thisUserNeededObjAr)) {
    /*
     * Se ci troviamo nel tester pubblico, allora non dobbiamo leggere un'istanza corso
     * dato che non ce ne sono.
     */
        
    if(!$invalid_course && !$sess_courseObj->getIsPublic ()) {

      if (isset($target_course_instance)) {
      	$id_course_instance = DataValidator::is_uinteger($target_course_instance);
      } else if (isset($_REQUEST['id_course_instance'])) {
      	$id_course_instance = DataValidator::is_uinteger($_REQUEST['id_course_instance']/*$GLOBALS['id_course_instance']*/); // FIXME: qui ci va $_REQUEST['id_course_instance']
      } else {
      	$id_course_instance = false;
      }
      
      $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? DataValidator::is_uinteger($_SESSION['sess_id_course_instance']) : false;      
      if($id_course_instance !== FALSE) {
        $course_instanceObj = read_course_instance_from_DB($id_course_instance);
        if (ADA_Error::isError($course_instanceObj)) {
          $invalid_course_instance = TRUE;
        } else {
            
            $UserType = $sess_userObj->getType();
              switch ($sess_userObj->getType()) {
                  case AMA_TYPE_STUDENT:
                      $studentLevel = $dh->_get_student_level($sess_id_user, $id_course_instance);
                      if (AMA_DataHandler::isError($studentLevel)) {
                          $invalid_course_instance = TRUE;
                      }
                      break;
                  case AMA_TYPE_TUTOR:
                      $tutorsInstance = $dh->course_instance_tutor_get($id_course_instance,$number=2);
                      if (AMA_DataHandler::isError($tutorsInstance)) {
                          $invalid_course_instance = TRUE;
                      } elseif (!in_array($sess_id_user, $tutorsInstance)) {
                          $invalid_course_instance = TRUE;
                      }
                      break;
                  default:    
    //                  $invalid_course_instance = TRUE;
                      break;
              }
              if (!$invalid_course_instance) {
                    $_SESSION['sess_id_course_instance'] = $id_course_instance;
                    $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
              }
        }
      } //end id_course_istance passed as parameter in the URL and valid
      elseif($sess_id_course_instance !== FALSE) {
        $instanceIdRequired = array();
        if (isset($_SESSION['sess_id_node']) && !$invalid_node) {
//        if ($nodeObj instanceof Node) { // required a node
              $instanceIdRequired[] = isset($dataHa['instance']) ? $dataHa['instance'] : null; 
              if ($instanceIdRequired[0] == 0) { // the node is NOT a note 
                    $field_list_ar = array();
                    if (isset($_SESSION['sess_id_course']) && !$invalid_course) {
                        $courseIdRequired = $_SESSION['sess_id_course'];
                        $InstanceIdList = $dh->course_instance_get_list($field_list_ar, $courseIdRequired);
                        if (AMA_DataHandler::isError($InstanceIdList) || count($InstanceIdList) == 0 ) {
                           $invalid_course_instance = TRUE;
                        }
                    } else {
                        $invalid_course_instance = TRUE;                        
                    }
                    $instanceIdRequired = array();
                    foreach ($InstanceIdList as $InstanceId) {
                        array_push($instanceIdRequired, $InstanceId[0]); 
                    }
              } // end if NOTE
          } // end if node         
          elseif ($sess_courseObj instanceof Course) {
                    $courseIdRequired = $sess_courseObj->id;
                    $InstanceIdList = $dh->course_instance_get_list(array(), $courseIdRequired);
                    if (AMA_DataHandler::isError($InstanceIdList) || count($InstanceIdList) == 0 ) {
                              $invalid_course_instance = TRUE;
                    }
                    $instanceIdRequired = array();
                    foreach ($InstanceIdList as $InstanceId) {
                        array_push($instanceIdRequired, $InstanceId[0]); 
                    }
          }
//          var_dump($instanceIdRequired,$sess_id_course_instance);
          $UserType = $sess_userObj->getType();
          switch ($UserType) {
              case AMA_TYPE_STUDENT:
              case AMA_TYPE_TUTOR:
                  if (!in_array($sess_id_course_instance,$instanceIdRequired)) {
                      $invalid_course_instance = TRUE;
                  }
                  break;
              case AMA_TYPE_SWITCHER:
              case AMA_TYPE_AUTHOR:
              default:    
                    break;
          } //end switch UserType
          $course_instanceObj = read_course_instance_from_DB($sess_id_course_instance);
          if (ADA_Error::isError($course_instanceObj)) {
              $course_instanceObj->handleError();
          }
          $_SESSION['sess_id_course_instance'] = $sess_id_course_instance;
      }
      else {
       $invalid_course_instance = TRUE;      
      }
    } //end isUserBrowsingThePublicTester
  } // end if in_array
  /*
   * Check if current user is a ADAGuest user and that he/she has requested
   * a public course instance.
   */

//
//  if(in_array('user', $neededObjAr[$user_type]) && in_array('course_instance', $neededObjAr[$user_type])) {
//    if(!$invalid_user && $sess_userObj instanceof ADAGuest) {
//      if ($invalid_course_instance || $course_instanceObj->status != ADA_COURSEINSTANCE_STATUS_PUBLIC) {
//        $guest_user_not_allowed = TRUE;
//      }
//    }
//  }

  // TODO: controllo livello utente
  /*
   * controllare che sia settato $sess_user_level e che il valore sia tra 0 e
   * ADA_MAX_USER_LEVEL
   */

  $parm_errorHa = array(
    'session'                => $invalid_session,
    'user'                   => $invalid_user,
    'user_level'             => $invalid_user_level,
    'course'                 => $invalid_course,
    'course_instance'        => $invalid_course_instance,
    'node'                   => $invalid_node,
    'guest_user_not_allowed' => $guest_user_not_allowed
  );
  return $parm_errorHa;
}

/**
 *
 * @param $data
 * @return unknown_type
 */
function clear_dataFN($variableToClearAr=array()) {
  //ADALogger::log('clear data FN');

  $sess_id                 = isset($_SESSION['sess_id']) ? $_SESSION['sess_id']: null;
  $sess_id_user            = isset($_SESSION['sess_id_user']) ? $_SESSION['sess_id_user']: null;
  $sess_id_course          = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
  $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
  $sess_id_node            = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;

  /**
   * Node variables
   */
  $node_parent = isset($GLOBALS['node_parent']) ? $GLOBALS['node_parent'] : null;
  $node_map    = isset($GLOBALS['node_map']) ? $GLOBALS['node_map'] : null;
  $node_index  = isset($GLOBALS['node_index']) ? $GLOBALS['node_index'] : null;
  $node_path   = isset($GLOBALS['node_path']) ? $GLOBALS['node_path'] : null;
  $node_title  = isset($GLOBALS['node_title']) ? $GLOBALS['node_title'] : null;

  /**
   * User variables
   */
  $id_profile     = isset($GLOBALS['id_profile']) ? $GLOBALS['id_profile'] : null;
  $user_name      = isset($GLOBALS['user_name']) ? $GLOBALS['user_name'] : null;
  $user_history   = isset($GLOBALS['user_history']) ? $GLOBALS['user_history'] : null;
  $user_bookmarks = isset($GLOBALS['user_bookmarks']) ? $GLOBALS['user_bookmarks'] : null;
  $user_level     = isset($GLOBALS['user_level']) ? $GLOBALS['user_level'] : null;

  /**
   * Course variables
   */
  $id_course    = isset($GLOBALS['id_course']) ? $GLOBALS['id_course'] : null;
  $course_title = isset($GLOBALS['course_title']) ? $GLOBALS['course_title'] : null;

  /**
   * Layout variables
   */
  $layout_CSS = isset($GLOBALS['layout_CSS']) ? $GLOBALS['layout_CSS'] : null;
  $layout_template = isset($GLOBALS['layout_template']) ? $GLOBALS['layout_template'] : null;

  if (in_array( 'node', $variableToClearAr)) {
    $GLOBALS['node_title']  = '';
    $GLOBALS['node_path']   = '' ;
    $GLOBALS['node_index']  = '';
    $GLOBALS['node_map']    = '';
    $GLOBALS['node_parent'] = $sess_id_course.'_0';
  }

  if (in_array('course', $variableToClearAr)) {
    $GLOBALS['id_course']    = ADA_DEFAULT_COURSE;
    $GLOBALS['course_title'] = '';
    $GLOBALS['id_toc']       = $sess_id_course."_".ADA_DEFAULT_NODE;
  }

  if (in_array('user', $variableToClearAr)) {
    $GLOBALS['id_profile']     = 1;
    $GLOBALS['user_name']      =  translateFN('Ospite');
    $GLOBALS['user_history']   = '';
    $GLOBALS['user_bookmarks'] = '';
    $GLOBALS['user_level']     = 1;
  }

  if (in_array('layout', $variableToClearAr)) {
    $GLOBALS['layout_CSS']      = 'css/default/default.css';
    $GLOBALS['layout_template'] = 'templates/default/default';
  }
}
    
/**
 *  Sara-14/01/2015
 *  set array session containing services_type definition.
 */
function loadServiceTypes(){
 
  if (!isset($_SESSION['service_level'])){
    if($GLOBALS['common_dh'] instanceof AMA_Common_DataHandler) {
        $servicesTypeAr =  $GLOBALS['common_dh']->get_service_type();
        if(!empty($servicesTypeAr) && !AMA_DB::isError($servicesTypeAr)){
            foreach($servicesTypeAr as $servicesType){
                if(isset($servicesType['livello_servizio']) && isset($servicesType['nome_servizio'])){
                    $_SESSION['service_level'][$servicesType['livello_servizio']]=translateFN($servicesType['nome_servizio']);
                    
                }
            }
        } 
        else{
            if(defined('DEFAULT_SERVICE_TYPE') && defined('DEFAULT_SERVICE_TYPE_NAME')){
                $_SESSION['service_level'][DEFAULT_SERVICE_TYPE]=translateFN(DEFAULT_SERVICE_TYPE_NAME);}
        }
     }
  }
}
?>