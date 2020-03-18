<?php
/**
 * create_chat.php
 *
 * @package
 * @author		Stamatios Filippis <st4m0s@gmail.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author		Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2001-2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
//    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout')

);

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
//$self = whoami();
$self = 'list_chatrooms'; // x template

require_once 'include/comunica_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
ComunicaHelper::init($neededObjAr);

require_once 'include/ChatRoom.inc.php';
require_once 'include/ChatDataHandler.inc.php';

$status = translateFN('lista delle chatrooms');

// display message that explains the functionality of the current script
$help = translateFN("Da qui l'utente puo' creare una nuova chatroom inserendo i valori negli appositi campi.
	 <br><br>Attenzione!<br>Per il corretto funzionamento della chat e' importante inserire i valori corretti.");

$star= translateFN("I campi contrassegnati con * sono obbligatori, non possono essere lasciati vuoti!");
$status = translateFN("Creazione di una chatroom");

// initialize a new UserDataHandler object
//$udh = new UserDataHandler();
$udh = UserDataHandler::instance($_SESSION['sess_selected_tester_dsn']);

// initialize a new form object
$f = new Tform();
// different chat type options are available for admins and for tutors
if($id_profile == AMA_TYPE_SWITCHER OR $id_profile == AMA_TYPE_TUTOR) {
  $options_of_chat_types = array(
	'Classe' => 'Classe',
//        'Privata' => 'Privata',
	'Pubblica'=>'Pubblica');
}
/*
 *
if($id_profile == AMA_TYPE_TUTOR){
  $options_of_chat_types = array('Privata' => 'Privata',
	'Classe' => 'Classe',
	'Pubblica'=>'Pubblica');
//  $options_of_chat_types = array('Privata' => 'Privata');
}
 *
 */
//get time and date and transform it to sting format

/*
 * @todo: inserire controllo timezone
 */

$actual_date= time();
$actual_start_time = AMA_DataHandler::ts_to_date($actual_date, "%H:%M:%S");
$actual_start_day = AMA_DataHandler::ts_to_date($actual_date, "%d/%m/%y");
$default_end_date= time()+ SHUTDOWN_CHAT_TIME;
$default_end_time = AMA_DataHandler::ts_to_date($default_end_date, "%H:%M:%S");
$default_end_day = AMA_DataHandler::ts_to_date($default_end_date, "%d/%m/%y");
// default max users numebr
$default_max_users= DEFAULT_MAX_USERS;
// default course instance value

if (!empty($sess_id_course_instance)) {
    $id_course_instance = $sess_id_course_instance;
} else {
    $id_course_instance = 0;
}
// array with data to build the form
$form_data = array(
array(
      'label'=>'Titolo *',
      'type'=>'text',
      'name'=>'chat_title',
      'size'=>'85',
      'maxlenght'=>'120'
      ),
      array(
      'label'=>'Argomento *',
      'type'=>'text',
      'name'=>'chat_topic',
      'size'=>'85',
      'maxlength'=>'120'
      ),
      array(
      'label'=>'Messaggio di benvenuto',
      'type'=>'textarea',
	  'rows'=>'1',
	  'cols'=>'63',
	  'wrap'=>'physical',
      'name'=>'welcome_msg',
      ),
      array(
      'label'=>'Proprietario *',
      'type'=>'text',
      'name'=>'chat_owner',
      'value'=>$user_uname,
      'size'=>'20',
      'maxlength'=>'20'
      ),
      array(
	  'label'=>'Tipo *',
      'type'=>'select',
	  'name'=>'chat_type',
	  'value'=>$options_of_chat_types
      ),
      array(
      'label'=>'Numero utenti',
      'type'=>'text',
      'name'=>'max_users',
      'size'=>'3',
      'maxlength'=>'3',
	  'value'=>$default_max_users
      ),
      array(
      'label'=>'Giorno di apertura<br>(gg/mm/aa)',
      'type'=>'text',
      'name'=>'start_day',
      'size'=>'8',
      'maxlength'=>'8',
	  'value'=>$actual_start_day
      ),
      array(
      'label'=>'Ora di avvio<br>(oo:mm:ss)',
      'type'=>'text',
      'name'=>'start_time',
      'size'=>'8',
      'maxlength'=>'8',
	  'value'=>$actual_start_time
      ),
      array(
      'label'=>'Giorno di chiusura<br>(gg/mm/aa)',
      'type'=>'text',
      'name'=>'end_day',
      'size'=>'8',
      'maxlength'=>'8',
	  'value'=>$default_end_day
      ),
      array(
      'label'=>'Ora di termine<br>(oo:mm:ss)',
      'type'=>'text',
      'name'=>'end_time',
      'size'=>'8',
      'maxlength'=>'8',
      'value'=>$default_end_time
      ),
      array(
      'label'=>'Classe ID',
      'type'=>'text',
          'value'=> $id_course_instance,
      'name'=>'id_course_instance',
      'size'=>'11',
      'maxlength'=>'11'
      ),
      array(
      'label'=>'',
      'type'=>'submit',
      'name'=>'invia',
	  'value'=>'Invia'
	  ),
	  array(
      'type'=>'reset',
      'name'=>'reset',
	  'value'=>'Reset'
	  )
	  );
	  //vito 14 gennaio 2009, eliminato ../comunica/
	  $f->initForm("create_chat.php","POST","","create_chat_form");
	  $f->setForm($form_data);
	  $form = $f->getForm();

	  // ******************************************************
	  //  CONSTRUCTION OF THE FORM CREATE_CHAT_FORM
	  // ******************************************************

	  // Has the form been posted?
          // vito 14 gennaio
	  //	  if ($REQUEST_METHOD == "POST"){
	  if ($_SERVER['REQUEST_METHOD'] == "POST"){

	    /*
	     * vito 14 gennaio 2009 sostituito $HTTP_POST_VARS con $_POST
	     */
	    if (isset($_POST['invia'])){

	      // Initialize errors array
	      $errors = array();

	      // Trim all submitted data
	      $create_chat_form = $_POST;
	      foreach ($create_chat_form as $key => $value){
	        $$key = $value;
	      }

	      // title could non be empty
	      if (empty($_POST['chat_title'])){
	        $errors["chat_title"] = translateFN("Il campo 'Titolo' deve essere impostato!");
	      }
	      // a chat type should be assigned
	      if (empty($_POST['chat_type'])){
	        $errors["chat_type"] = translateFN("Il campo 'Tipo' deve essere impostato!");
	      }
	      // topic could non be empty
	      if (empty($_POST['chat_topic'])){
	        $errors["chat_topic"] = translateFN("Il campo 'Argomento' deve essere impostato!");
	      }

	      if ((empty($_POST['start_day']) and (!empty($_POST['start_time']))) or
	      (!empty($_POST['start_day']) and (empty($_POST['start_time'])))){
	        $errors["start_day"] = translateFN("I campi 'Giorno di apertura' e 'Ora di avvio' devono essere entrambi impostati oppure entrambi lasciati vuoti!");
	      }
	      // verify the inserted start date
	      if (preg_match('/^([0-3][0-9][\/][0-1][0-9][\/][0-9]{2})$/',$_POST['start_day'])){
	        // if the format is correct
	        $date_string = explode("/",$_POST['start_day']);
	        $d = (int)$date_string[0];
	        $m = (int)$date_string[1];
	        $y = (int)$date_string[2];
	        // verify the validity of the date
	        if (!checkdate($m,$d,$y)){
	          $errors["start_day"] = translateFN("Il campo 'Giorno di apertura' contiene una data non valida");
	        }
	      }else{
	        $errors["start_day"] = translateFN("Il campo 'Giorno di apertura' contiene una data non valida");
	      }

	      // verify the inserted start time
	      if (!preg_match('/^([0-9]{2}[\:][0-9]{2}[\:][0-9]{2})$/',$_POST['start_time'])){
	        $errors["start_time"] = translateFN("Il formato del campo 'Ora di avvio' non è valido");
	      }

	      if ((empty($_POST['end_day']) and (!empty($_POST['end_time']))) or
	      (!empty($_POST['end_day']) and (empty($_POST['end_time'])))){
	        $errors["end_day"] = translateFN("I campi 'Giorno di chiusura' e 'Ora di termine' devono essere entrambi impostati oppure entrambi lasciati vuoti!");
	      }

	      // verify the inserted end date
	      if (preg_match('/^([0-3][0-9][\/][0-1][0-9][\/][0-9]{2})$/',$_POST['end_day'])){
	        // if the format is correct
	        $date_string = explode("/",$_POST['end_day']);
	        $d = (int)$date_string[0];
	        $m = (int)$date_string[1];
	        $y = (int)$date_string[2];
	        // verify the validity of the date
	        if (!checkdate($m,$d,$y)){
	          $errors["end_day"] = translateFN("IL campo 'Giorno di chiusura' contiene una data non valida");
	        }
	      }else{
	        $errors["end_day"] = translateFN("IL campo 'Giorno di chiusura' contiene una data non valida");
	      }

	      // verify the inserted end time
	      if (!preg_match('/^([0-9]{2}[\:][0-9]{2}[\:][0-9]{2})$/',$_POST['end_time'])){
	        $errors["end_time"] = translateFN("Il formato del campo 'Ora di avvio' non �valido");
	      }

	      // an owner should be assigned
	      if (empty($_POST['chat_owner'])){
	        $errors["chat_owner"] = translateFN("Il campo 'Proprietario' deve essere impostato!");
	      }
	      else{
	        // transfrom username's into user's_id
	        $owner_name=$_POST['chat_owner'];
	        $res_ar = $udh->find_users_list(array(),"username='$owner_name'");
	        if (AMA_DataHandler::isError($res_ar))
	        return new AMA_Error(AMA_ERR_READ_MSG);
	        // getting only user_id
	        $id_chat_owner= $res_ar[0][0];
	        // we get the info of the user
	        $user_info = $dh->_get_user_info($id_chat_owner);
	        // we get the type of the user
	        $owner_type = $user_info['tipo'];
	        // we verify if the typed username from the user is a valid username
	        if(($owner_type==AMA_TYPE_TUTOR)or($owner_type==AMA_TYPE_SWITCHER)){
	          $msg = translateFN("<b>Utente abilitato</b>");
	        }
	        else{
	          $errors["chat_owner"] = translateFN("Il campo 'Proprietario' contiene un nome utente non esistente oppure non abilitato!");
	        }
	      }

	      // create a unix data date format
	      $start_data_array = array ($_POST['start_day'],$_POST['start_time']);
	      $start_data= sumDateTimeFN ($start_data_array);
	      // create a unix data date format
	      $end_data_array = array ($_POST['end_day'],$_POST['end_time']);
	      $end_data= sumDateTimeFN ($end_data_array);

	      // a new chatroom could be created only if no errors were found
	      if (count($errors) == 0){
	        // prepare message to create
	        //$chatroom_ha = $create_chat_form;

	        switch($_POST['chat_type']){
	          case 'Privata':
	            $chatroom_ha['chat_type']= INVITATION_CHAT;
	            break;
	          case 'Classe':
	            $chatroom_ha['chat_type']= CLASS_CHAT;
	            break;
	          case 'Pubblica':
	            $chatroom_ha['chat_type']= PUBLIC_CHAT;
	            break;
	          default:
	        }// switch

	        $chatroom_ha['id_chat_owner']= $id_chat_owner;
	        $chatroom_ha['chat_title'] = $_POST['chat_title'];
	        $chatroom_ha['chat_topic'] = $_POST['chat_topic'];
	        $chatroom_ha['welcome_msg'] = $_POST['welcome_msg'];
	        $chatroom_ha['max_users']= $_POST['max_users'];
	        $chatroom_ha['start_time']= $start_data;
	        $chatroom_ha['end_time']= $end_data;
	        $chatroom_ha['id_course_instance']= $_POST['id_course_instance'];

	        // add chatroom_ha to the database
	        $chatroom = Chatroom::add_chatroomFN($chatroom_ha);

	        if(!is_object($chatroom)){
	          // the chatroom id
	          $id_chatroom = $chatroom;
	          //Initialize a new chatroom object
	          $chatroomObj = new ChatRoom($id_chatroom);
	          // the link to the chatroom
	          $chatroom_link= "../comunica/ada_chat.php?id_chatroom=$id_chatroom";
	          // invites him self into the chatroom
	          $add_himself = $chatroomObj->add_user_chatroomFN($sess_id_user,$sess_id_user,$id_chatroom,ACTION_INVITE,STATUS_INVITED);
	          // message display
	          $err_msg = translateFN("<b>La chatroom e' stata creata con successo!</b>");
	          // construct link for edit the chat if needed

	          $form_data = array(
	          array(
		                          'label'=>'Titolo *',
		                          'type'=>'text',
                                          'value'=> stripslashes($_POST['chat_title']),
		                          'name'=>'chat_title',
		                          'size'=>'85',
		                          'maxlenght'=>'120'
		                          ),
		                          array(
		                          'label'=>'Argomento *',
		                          'type'=>'text',
		                          'name'=>'chat_topic',
					  'value'=>stripslashes($_POST['chat_topic']),
		                          'size'=>'85',
		                          'maxlength'=>'120'
		                          ),
		                          array(
		                          'label'=>'Messaggio di benvenuto',
		                          'type'=>'textarea',
		                          'name'=>'welcome_msg',
					  'value'=>stripslashes($_POST['welcome_msg']),
		       			  'rows'=>'1',
					  'cols'=>'63',
					  'wrap'=>'physical',
		                          ),
		                          array(
		                          'label'=>'Proprietario *',
		                          'type'=>'text',
		                          'name'=>'chat_owner',
					  'value'=>$_POST['chat_owner'],
		                          'size'=>'20',
		                          'maxlength'=>'20'
		                          ),
		                          array(
					  'label'=>'Tipo *',
		                          'type'=>'select',
					  'name'=>'chat_type',
					  'value'=>$options_of_chat_types
		                          ),
		                          array(
		                          'label'=>'Numero utenti',
		                          'type'=>'text',
		                          'name'=>'max_users',
					  'value'=>$_POST['max_users'],
		                          'size'=>'3',
		                          'maxlength'=>'3'
		                          ),
		                          array(
		                          'label'=>'Giorno di apertura<br>(gg/mm/aa)',
		                          'type'=>'text',
		                          'name'=>'start_day',
					  'value'=>$start_day,
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Ora di avvio<br>(oo:mm:ss)',
		                          'type'=>'text',
					  'value'=>$start_time,
		                          'name'=>'start_time',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Giorno di chiusura<br>(gg/mm/aa)',
		                          'type'=>'text',
					  'value'=>$_POST['end_day'],
		                          'name'=>'end_day',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Ora di termine<br>(oo:mm:ss)',
		                          'type'=>'text',
					  'value'=>$_POST['end_time'],
		                          'name'=>'end_time',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Classe ID',
		                          'type'=>'text',
	                              'value'=> $id_course_instance,
		                          'name'=>'id_course_instance',
					  'value'=>$_POST['id_course_instance'],
		                          'size'=>'11',
		                          'maxlength'=>'11'
		                          ),
		                          array(
		                          'label'=>'',
		                          'type'=>'submit',
		                          'name'=>'invia',
					  			  'value'=>'Invia'
					  			  ),
					  			  array(
		                          'type'=>'reset',
		                          'name'=>'reset',
					  			  'value'=>'Reset'
					  			  )
					  			  );
					  			  $f->initForm("../comunica/create_chat.php","POST","","create_chat_form");
					  			  $f->setForm($form_data);
					  			  $form = $f->getForm();
	        }
	        else{
	          $errorObj = $chatroom->message;
	          if ($errorObj == "errore: record gi�esistente"){
	            $err_msg = translateFN("<b>Errore.Una chatroom con questo titolo, tipo, classe ID e tempo di avvio e' gia' esistente! Inserirne di nuovi.</b>");

	            $form_data = array(
	            array(
		                          'label'=>'Titolo *',
		                          'type'=>'text',
								  'value'=>stripslashes($_POST['chat_title']),
		                          'name'=>'chat_title',
		                          'size'=>'85',
		                          'maxlenght'=>'120'
		                          ),
		                          array(
		                          'label'=>'Argomento *',
		                          'type'=>'text',
		                          'name'=>'chat_topic',
					  			  'value'=>stripslashes($_POST['chat_topic']),
		                          'size'=>'85',
		                          'maxlength'=>'120'
		                          ),
		                          array(
		                          'label'=>'Messaggio di benvenuto',
		                          'type'=>'textarea',
		                          'name'=>'welcome_msg',
					  			  'value'=>stripslashes($_POST['welcome_msg']),
		       			  		  'rows'=>'1',
					  			  'cols'=>'63',
					  			  'wrap'=>'physical',
		                          ),
		                          array(
		                          'label'=>'Proprietario *',
		                          'type'=>'text',
		                          'name'=>'chat_owner',
					  			  'value'=>$_POST['chat_owner'],
		                          'size'=>'20',
		                          'maxlength'=>'20'
		                          ),
		                          array(
					  			  'label'=>'Tipo *',
		                          'type'=>'select',
					  			  'name'=>'chat_type',
					  			  'value'=>$options_of_chat_types),
		                          array(
		                          'label'=>'Numero utenti',
		                          'type'=>'text',
		                          'name'=>'max_users',
					  			  'value'=>$_POST['max_users'],
		                          'size'=>'3',
		                          'maxlength'=>'3'
		                          ),
		                          array(
		                          'label'=>'Giorno di apertura<br>(gg/mm/aa)',
		                          'type'=>'text',
		                          'name'=>'start_day',
					  			  'value'=>$start_day,
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Ora di avvio<br>(oo:mm:ss)',
		                          'type'=>'text',
					  			  'value'=>$start_time,
		                          'name'=>'start_time',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Giorno di chiusura<br>(gg/mm/aa)',
		                          'type'=>'text',
					  			  'value'=>$_POST['end_day'],
		                          'name'=>'end_day',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Ora di termine<br>(oo:mm:ss)',
		                          'type'=>'text',
					   			  'value'=>$_POST['end_time'],
		                          'name'=>'end_time',
		                          'size'=>'8',
		                          'maxlength'=>'8'
		                          ),
		                          array(
		                          'label'=>'Classe ID',
		                          'type'=>'text',
	                              'value'=> $id_course_instance,
		                          'name'=>'id_course_instance',
					  			  'value'=>$_POST['id_course_instance'],
		                          'size'=>'11',
		                          'maxlength'=>'11'
		                          ),
		                          array(
		                          'label'=>'',
		                          'type'=>'submit',
		                          'name'=>'invia',
					  			  'value'=>'Invia'
					  			  ),
					  			  array(
		                          'type'=>'reset',
		                          'name'=>'reset',
					  			  'value'=>'Reset'
					  			  )
					  			  );
					  			  $f->initForm("../comunica/create_chat.php","POST","","create_chat_form");
					  			  $f->setForm($form_data);
					  			  $form = $f->getForm();
	          }
	          if ($errorObj == "errore: aggiunta del record non riuscita"){
	            $err_msg = translateFN("<b>Errore del sistema durante l'operazione di creazione chatroom! Riprova!</b>");

	            $form_data = array(
	            array(
                      'label'=>'Titolo *',
                      'type'=>'text',
                      'value'=>stripslashes($_POST['chat_title']),
                      'name'=>'chat_title',
                      'size'=>'85',
                      'maxlenght'=>'120'
                      ),
                    array(
                      'label'=>'Argomento *',
                      'type'=>'text',
                      'name'=>'chat_topic',
                      'value'=>stripslashes($_POST['chat_topic']),
                      'size'=>'85',
                      'maxlength'=>'120'
                      ),
                    array(
                      'label'=>'Messaggio di benvenuto',
                      'type'=>'textarea',
                      'name'=>'welcome_msg',
                      'value'=>stripslashes($_POST['welcome_msg']),
                      'rows'=>'1',
                      'cols'=>'63',
                      'wrap'=>'physical',
                      ),
                    array(
                      'label'=>'Proprietario *',
                      'type'=>'text',
                      'name'=>'chat_owner',
                      'value'=>$_POST['chat_owner'],
                      'size'=>'20',
                      'maxlength'=>'20'
                      ),
                    array(
                      'label'=>'Tipo *',
                      'type'=>'select',
                      'name'=>'chat_type',
                      'value'=> $options_of_chat_types
                      ),
                    array(
                      'label'=>'Numero utenti',
                      'type'=>'text',
                      'name'=>'max_users',
                      'value'=>$_POST['max_users'],
                      'size'=>'3',
                      'maxlength'=>'3'
                      ),
                    array(
                      'label'=>'Giorno di apertura<br>(gg/mm/aa)',
                      'type'=>'text',
                      'name'=>'start_day',
                      'value'=>$start_day,
                      'size'=>'8',
                      'maxlength'=>'8'
                      ),
                    array(
                      'label'=>'Ora di avvio<br>(oo:mm:ss)',
                      'type'=>'text',
                      'value'=>$start_time,
                      'name'=>'start_time',
                      'size'=>'8',
                      'maxlength'=>'8'
                      ),
                    array(
                      'label'=>'Giorno di chiusura<br>(gg/mm/aa)',
                      'type'=>'text',
                      'value'=>$_POST['end_day'],
                      'name'=>'end_day',
                      'size'=>'8',
                      'maxlength'=>'8'
                      ),
                    array(
                      'label'=>'Ora di termine<br>(oo:mm:ss)',
                      'type'=>'text',
                      'value'=>$_POST['end_time'],
                      'name'=>'end_time',
                      'size'=>'8',
                      'maxlength'=>'8'
                      ),
                    array(
                      'label'=>'Classe ID',
                      'type'=>'text',
                      //'value'=> $id_course_instance,
                      'name'=>'id_course_instance',
                      'value'=>$_POST['id_course_instance'],
                      'size'=>'11',
                      'maxlength'=>'11'
                      ),
                    array(
                      'label'=>'',
                      'type'=>'submit',
                      'name'=>'invia',
                      'value'=>'Invia'
                      ),
                    array(
                      'type'=>'reset',
                      'name'=>'reset',
                      'value'=>'Reset'
                      )
                );
		$f->initForm("../comunica/create_chat.php","POST","","create_chat_form");
		$f->setForm($form_data);
		$form = $f->getForm();
              }
            }

          }// end if count

	      // build up error message
	      if (count($errors)){
	        $err_msg = "<strong>";
	        foreach ($errors as $err){
	          $err_msg .=$err."<br>";
	        }
	        $err_msg .= "</strong>";
	      }
	    } //end if invia

	  } // end if POST

        $banner = include ROOT_DIR . '/include/banner.inc.php';

	  // ******************************************************
	  //  END OF FORM CONSTRUCTION
	  // ******************************************************
	  // array with data to be createed to the browser
	  $data =  array( 'banner'=> $banner,
                'status'=> $status,
                'user_name'=> $user_name,
                'user_type'=> $user_type,
                'edit_profile'=>$userObj->getEditProfilePage(),
                'help' =>$help,
                'star'=>$star,
                'id_chatroom'=>$id_chatroom,
                'chatroom_link'=>$chatroom_link,
                'data'=>$form,
                'create_chat'=>$form,
                'error'=> $err_msg
          );

        ARE::render($layout_dataAr, $data);
	  //end create_chat_message
	  ?>
