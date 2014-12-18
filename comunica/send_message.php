<?php
/**
 * SEND MESSAGE.
 *
 * @package		comunica
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER     => array('layout')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once 'include/StringValidation.inc.php';
include_once 'include/address_book.inc.php';

$success    = HTTP_ROOT_DIR.'/comunica/list_messages.php';
$error_page = HTTP_ROOT_DIR.'/comunica/send_message.php';

if (!isset($op)) {
  $op='default';
}

//$ad = new address_book();

$title = translateFN('ADA - Spedisci messaggio');

$rubrica_ok = 0; // Address book not loaded yet

$err_msg = translateFN('Invio messaggio');


// Has the form been posted?
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($spedisci)) {

    // Initialize errors array
    $errors = array();

    /*
     * If there aren't addressee specified we have to warn the user?
     */
    if(!is_array($destinatari)) {
      $errors['destinatari'] = translateFN('Bisogna specificare almeno un destinatario');
    }

    /*
     * If no subject was specified, add a default one
     */
    if (empty($titolo)){
      $titolo = translateFN('Senza oggetto');
    }
    // FIXME: non è possibile sare questa funzione, dato che il testo è UTF8, verificare chi fa la query sul db e preparare la query
      //else {
        //if (!is_clean_text($titolo, 0, 128)){
        //  $errors['titolo'] = translateFN("L'oggetto del messaggio contiene caratteri non validi");
        //  $titolo = clean_text($titolo, 0, 128);
        //}
      //}
    //}

    // security test
    $form['testo'] = strip_tags ($testo,'<BR><UL><OL><LI><P><B><I><LINK><IMG>');

    // Actually send message only if no errors were found
    if (count($errors) == 0){

      $form['titolo']      = $titolo;

      /*
       * If we are on the public tester, we have to obtain, for each addressee,
       * his tester and then we can send the message
       */
      $errObjs = array();

      //if($sess_selected_tester == NULL || $sess_selected_tester == ADA_PUBLIC_TESTER) {
      if(MultiPort::isUserBrowsingThePublicTester()) {
        $common_dh = $GLOBALS['common_dh'];
        foreach($destinatari as $username) {
          $user_tester_Ar = $common_dh->get_testers_for_username($username);
          /*
           * Now we are sending the message to the first tester found.
           * In future, we may want to send this message to all the tester.
           */
          $user_tester = $user_tester_Ar[0];
          if(AMA_DataHandler::isError($user_tester)) {

          }
          else {
            $mh = MessageHandler::instance(MultiPort::getDSN($user_tester));

            // prepare message to send
            $message_ha = $form;
            $message_ha['destinatari'] = array($username);

            $message_ha['data_ora'] = "now";
            if (isset( $_POST['modo'])) {
              $message_ha['tipo'] =  $_POST['modo'];
            }
            else {
              $message_ha['tipo'] = ADA_MSG_SIMPLE;
            }
            $message_ha['mittente'] = $user_uname;

            // delegate sending to the message handler
            $res = $mh->send_message($message_ha);

            if (AMA_DataHandler::isError($res)){
              // Delayed error handling
              $errObjs[] = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
              NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')),TRUE);
            }

          }
        }
      }
      /*
       * We are on a tester different from the public tester. In this case it's
       * possible to send messages only on this tester.
       */
      else {
        $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));

        // prepare message to send
        $message_ha = $form;
        $message_ha['destinatari'] = $destinatari;

        $message_ha['data_ora'] = "now";
        if (isset( $_POST['modo'])) {
          $message_ha['tipo'] =  $_POST['modo'];
        }
        else {
          $message_ha['tipo'] = ADA_MSG_SIMPLE;
        }
        $message_ha['mittente'] = $user_uname;

        // delegate sending to the message handler
        $res = $mh->send_message($message_ha);

        if (AMA_DataHandler::isError($res)){
          $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
          NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
        }
      }

      if(count($errObjs) > 0) {
        // TODO: handle here errors happened when sending messages
      }

      // redirect to send_success page
      $status = urlencode(translateFN('Messaggio spedito'));
      header("Location: $success?status=$status");
      exit();
    }

    // build up error message
    if (count($errors)) {
      $err_msg = "<strong>";
      foreach ($errors as $err){
        $err_msg .=$err."<br>";
      }
      $err_msg .= "</strong>";
    }

  } //end if Spedisci
}

switch ($op){

  case 'to_user':
    $titolo = "";
    $destinatari = trim($destinatari);
    // echo "destinatari: $dest";
    /*
    $pre_testo = translateFN("Risposta al messaggio");
    $post_testo = translateFN("Inviato da") . " " . $destinatari;

    $testo_ar = explode(chr(13),  trim($testo_replay));

    $testo = "";
    foreach($testo_ar as $riga) {
    if (ord($riga[0]) == 10) $riga[0] = "";
    $testo .= "> " . $riga ."\r";
    }

    $testo = $pre_testo .  "\r" . $testo . "\r" . $post_testo;
    */
    break;


  case 'replay':

    $titolo       = 'Re: ' . trim($_SESSION['titolo_replay']);
    $destinatario = trim($_SESSION['destinatari_replay']);
    $testo_replay = trim($_SESSION['testo_replay']);


    $div = CDOMElement::create('div', "id:$destinatario");
    $checkbox    = CDOMElement::create('checkbox', "name:destinatari[], value:$destinatario, checked: checked");
    $checkbox->setAttribute('onclick', "remove_addressee('$destinatario');");
    $div->addChild($checkbox);
    $div->addChild(new CText($destinatario));
    $destinatari = $div->getHtml();

    $pre_testo  = translateFN('Risposta al messaggio');
    $post_testo = translateFN('Inviato da') . ' ' . $destinatario;

    $testo_ar = preg_split("#[\n]#", $testo_replay);

    $testo = "";
    foreach($testo_ar as $riga) {
      if (ord($riga[0]) == 10)
      $riga[0] = "";
      $testo .= "> " . $riga ; //."\r";
    }

    $testo = $pre_testo .  "\r" . $testo . "\r" . $post_testo;


    unset($_SESSION['titolo_replay']);
    unset($_SESSION['destinatari_replay']);
    unset($_SESSION['destinatari_replay_all']);
    unset($_SESSION['testo_replay']);

    break;

  case 'replay_all':

    $titolo       = 'Re: ' . trim($_SESSION['titolo_replay']);
    $destinatari_all  = trim($_SESSION['destinatari_replay_all']);
    $testo_replay = trim($_SESSION['testo_replay']);

    /*
     * Html per i destinatari
     */
    $destinatari_Ar = explode(',', $destinatari_all);
    $destinatari = '';
    foreach($destinatari_Ar as $d) {
      $destinatario = trim($d);
      if($destinatario != $user_uname) {
        $div = CDOMElement::create('div', "id:$destinatario");
        $checkbox    = CDOMElement::create('checkbox', "name:destinatari[], value:$destinatario, checked: checked");
        $checkbox->setAttribute('onclick', "remove_addressee('$destinatario');");
        $div->addChild($checkbox);
        $div->addChild(new CText($destinatario));
        $destinatari .= $div->getHtml();
      }
    }

    $pre_testo = translateFN('Risposta al messaggio');
    $post_testo = translateFN('Inviato da') . ' ' . trim($destinatari_Ar[0]);

    $testo_ar = preg_split("#[\n]#",  trim($testo_replay));

    $testo = "";
    foreach($testo_ar as $riga) {
      if (ord($riga[0]) == 10)
      $riga[0] = "";
      $testo .= "> " . $riga; // ."\r";
    }

    $testo = $pre_testo .  "\r" . $testo . "\r" . $post_testo;
    break;

  case 'write':
  case 'default':
      if (isset($destinatari)) {
        $destinatario = trim($destinatari);
        $div = CDOMElement::create('div', "id:$destinatario");
        $checkbox    = CDOMElement::create('checkbox', "name:destinatari[], value:$destinatario, checked: checked");
        $checkbox->setAttribute('onclick', "remove_addressee('$destinatario');");
        $div->addChild($checkbox);
        $div->addChild(new CText($destinatario));
        $destinatari = $div->getHtml();
          
      }


    // mod steve 25/3/09: non deve pulire il campo testo
    // $testo = "";
    // end mod
    break;
}


if (!isset($titolo)) {
  $titolo = "";
}
if (!isset($destinatari)) {
  $destinatari = "";
}
if (!isset($course_title)) {
  $course_title = "";
}
if (!isset($testo)) {
  $testo = "";
} else {
	// security test
	$testo = strip_tags ($testo,"<BR><UL><OL><LI><P><B><I><LINK><IMG>");	
}

/*
 * ADA Address Book
 */

$ada_address_book = MessagesAddressBook::create($userObj);

/*
* Last access link
*/

if(isset($_SESSION['sess_id_course_instance'])){
        $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
  else {
        $last_access=$userObj->get_last_accessFN(null,"UT",null);
        $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
  
 if($last_access=='' || is_null($last_access)){
    $last_access='-';
}


$content_dataAr = array(
  'user_name'      => $user_name,
  'titolo'         => $titolo,
  'user_type'      => $user_type,
  'user_level'   => $user_level,
  'testo'          => trim($testo),
  'destinatari'    => $destinatari,
  'last_visit' => $last_access,
  //'student_button' => $student_button,
  //'tutor_button'   => $tutor_button,
  //'author_button'  => $author_button,
  //'admin_button'   => $admin_button,
  //'indirizzi'      => $indirizzi,
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'rubrica'        => $ada_address_book->getHtml(), //$rubrica,
  'status'         => $err_msg
);

$options_Ar = array('onload_func' => 'load_addressbook();');
ARE::render($layout_dataAr, $content_dataAr, NULL, $options_Ar);
?>