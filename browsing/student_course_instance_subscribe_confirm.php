<?php
/**
 *
 * @package		Subscription Confirm from Paypal
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009-2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.2
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
//require_once ROOT_DIR . '/include/CourseInstance.inc.php';

$self = whoami(); // to select the right template
/*
 * INCLUSIONE SPECIFICA PER PAYPAL
 */
if (file_exists(ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php')) {
    require_once ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php';
    $paypal_allowed = TRUE;
}

$today_date = today_dateFN();
$providerId = DataValidator::is_uinteger($_GET['provider']);
$courseId = DataValidator::is_uinteger($_GET['course']);
$instanceId = DataValidator::is_uinteger($_GET['instance']);
$studentId = DataValidator::is_uinteger($_GET['student']);

$testerInfoAr = $common_dh->get_tester_info_from_id($providerId,'AMA_FETCH_ASSOC');
if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
    $provider_name = $testerInfoAr[1];
    $tester = $testerInfoAr[10];
    $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
    $currentTesterId = $newTesterId;
    $GLOBALS['dh'] = $tester_dh;
    $dh = $tester_dh;

    /*
    * GESTIONE LOG
    */
    $logStr = "";
    $log_file = ROOT_DIR . '/browsing/paypal/'.PAYPAL_IPN_LOG;
    $logFd = fopen($log_file, "a");
    $fpx = fopen($log_file, 'a');

    $debug = 1;
    if ($debug == 1) {
      fwrite($fpx, "INIZIO processo Confirm \n");
      fwrite($fpx, "Student: $studentId \n");

    }
    // id dello studente
    if (!isset($studentId)) {
            $studentId = $sess_id_user ;
    }
    /*
     * Instance Object
     */
    $instanceObj = new course_instance($instanceId);
//    print_r($instanceObj);
    $price = $instanceObj->getPrice();
    $course = $dh->get_course($courseId);
    $course_name = $course['titolo'];

    if (!isset($back_url)) {
            $back_url = "student.php";
    }

    // preparazione output HTML e print dell' output
    $title = translateFN("Conferma pagamento iscrizione al corso");
//    $link_annulla_iscrizione = "<a href=\"".$http_root_dir . "/iscrizione/student_course_instance_unsubscribe.php?id_instance=".
    $id_course_instance ."&id_student=" . $id_studente . "&back_url=student_course_instance_menu.php\">". translateFN('Annulla iscrizione') . "</a>";
    $link_torna_home = "<a href=\"".$http_root_dir . "/browsing/student.php\">". translateFN('Torna alla Home') . "</a>";

    $info_div = CDOMElement::create('DIV', 'id:info_div');
    $info_div->setAttribute('class', 'info_div');
    $label_text = CDOMElement::create('span','class:info');
    $label_text->addChild(new CText(translateFN('La tua iscrizione è stata effettuata con successo.')));
    $info_div->addChild($label_text);
    $homeUser = $userObj->getHomePage();
    $link_span = CDOMElement::create('span','class:info_link');
    $link_to_home = BaseHtmlLib::link($homeUser, translateFN('vai alla home per accedere.'));
    $link_span->addChild($link_to_home);
    $info_div->addChild($link_span);
    //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
    $data = $info_div;


    /*
     * MANAGE PDT FROM PAYPAL
     *
     */
    // assigned session variables to local variables
    $paypal_email_address = PAYPAL_ACCOUNT;
    $product_price = $price;
    $price_currency = CURRENCY_CODE;
    $paypal_ipn_url = PAYPAL_IPN_URL; 

    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-synch';

    $tx_token = $_GET['tx'];
    $auth_token = IDENTITY_CHECK;
    $req .= "&tx=$tx_token&at=$auth_token";

    // post back to PayPal system to validate
    $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
    //$fp = fsockopen ($paypal_ipn_url, 80, $errno, $errstr, 30);
    // If possible, securely post back to paypal using HTTPS
    // Your PHP server will need to be SSL enabled
    $fp = fsockopen ('ssl://'.$paypal_ipn_url, 443, $errno, $errstr, 30);

    if (!$fp) {
        $ipn_log .= "Error connecting to Paypal\n";
        $message = translateFN("Errore di comunicazione con Paypal. Impossibile proseguire.");
        $message .= "<br />".translateFN("Se non riceverei una mail di comunicazione, scrivi a") . ADA_ADMIN_MAIL_ADDRESS;
        if ($debug == 1) { fwrite($fpx, "Error connecting to Paypal\n"); }
    } else {
        if ($debug == 1) { fwrite($fpx, "connecting to Paypal...\n"); }

        fputs ($fp, $header . $req);
        // read the body data
        $res = '';
        $headerdone = false;
        while (!feof($fp)) {
            $line = fgets ($fp, 1024);
//            if ($debug == 1) { fwrite($fpx, $line."\n"); }
            if (strcmp($line, "\r\n") == 0) {
                // read the header
                $headerdone = true;
            }
            else if ($headerdone)
            {
                // header has been read. now read the contents
                $res .= $line;
            }
        }
        // parse the data
        $lines = explode("\n", $res);
        if ($debug == 1) { fwrite($fpx, $lines[0]."\n"); }
        $keyarray = array();
        if (strcmp ($lines[0], "SUCCESS") == 0) {
    //        print_r($lines);
            for ($i=1; $i<count($lines);$i++){
                list($key,$val) = explode("=", $lines[$i]);
                $keyarray[urldecode($key)] = urldecode($val);
            }
            // check the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your Primary PayPal email
            // check that payment_amount/payment_currency are correct
            // process payment
            // $first_name = $keyarray['first_name'];
            // $last_name = $keyarray['last_name'];
            $item_name = $keyarray['item_name'];
            $payment_amount = $keyarray['mc_gross'];
            $payment_currency = $keyarray['mc_currency'];
            $item_number = $keyarray['item_number'];
            $txn_id = $keyarray['txn_id'];
            $receiver_email = $keyarray['business'];
            $payer_email = $keyarray['payer_email'];
            $payment_status = $keyarray['payment_status'];
            if (
                ($receiver_email == $paypal_email_address) &&
                ($payment_amount == $product_price) &&
                ($payment_currency == $price_currency) &&
                ($payment_status == 'Completed')
            ) {
                $date = AMA_DataHandler::ts_to_date(time(), "%d/%m/%Y - %H:%M:%S");
                $ipn_log .= "Paypal PDT DATA OK\n";
                if ($debug == 1) { 
                    fwrite($fpx, "Paypal PDT DATA OK - $date\n");
                }

                $first_name = $userObj->getFirstName();
                $last_name = $userObj->getLastName();
                
//                $body = translateFN("Hai effettuato il pagamento di") . " ". $payment_amount ." EUR ". translateFN('tramite Paypal' . "\n\r").
//                $body .= translateFN('Questo addebito verrà visualizzato sull\'estratto conto della carta di credito o prepagata come pagamento a PAYPAL *Lynx s.r.l.');
                $message_ha["testo"] = translateFN('Gentile') . " " . $first_name .",\r\n" . translateFN("grazie per aver eseguito l'iscrizione al") . " " . $course_name . "\n\r\n\r";
//                $message_ha["testo"] .=  $body_mail;
                //$message_ha["testo"] .= "\n\r\n\r". translateFN("I tuoi dati di accesso sono. username: ") . $username . "\n\r" . translateFN("password:" . " " . $password );
                //$message_ha["testo"] .= "\n\r". translateFN("Buono studio.");
                $message = nl2br($message_ha["testo"]);
                $message .= "<br />" . translateFN('Ti abbiamo inviato una mail di conferma dell\'iscrizione. Cliccando sul link inserito nella mail potrai accedere al corso');
                $message .= "<br />--------<br />" . translateFN('Dettagli di pagamento.');
                $message .= "<br />" . translateFN('Nome e cognome:') . " ". $first_name ." ". $last_name;
                $message .= "<br />" . translateFN('Importo:') . " ". $payment_currency ." ". $payment_amount;
                $message .= "<br />" . translateFN('Iscrizione al corso:')." ". $course_name;
                $message .= "<br />" . translateFN('Data della transazione:')." ". $date;
                $message .= "<br />" . translateFN('ID della transazione:')." ". $txn_id;
                $message .= "<br />--------<br />";

            } else {
                $message = translateFN('Gentile') . " " . $first_name .", <BR />";
                $message .= translateFN('il corso pagato non corrisponde ai dettagli in nostro possesso')."<BR />";
                $message .= translateFN('se hai bisogno di maggiori informazioni scrivi una mail a:') . " " . ADA_ADMIN_MAIL_ADDRESS ."<br />";

                $ipn_log .= "Purchase does not match product details\n";
                if ($debug == 1) { fwrite($fpx, "Purchase does not match product details\n"); }
            }
        }
            else if (strcmp ($lines[0], "FAIL") == 0) {
                $ipn_log .= "Error connecting to Paypal\n";
                $message = translateFN("Errore di comunicazione con Paypal. Impossibile proseguire.");
                $message .= "<br />".translateFN("Se non riceverei una mail di comunicazione, scrivi a ") . ADA_ADMIN_MAIL_ADDRESS ."<br />";
                if ($debug == 1) { fwrite($fpx, "FAIL Error connecting to Paypal\n"); }        // log for manual investigation
        }
    }

    if ($debug == 1) {
            fclose($fpx);
    }
    /*
     * FINE GESTIONE PDT DA PAYPAL
     *
     */

    //$dati = $message;
//    print_r($message);
    $info_div = CDOMElement::create('DIV', 'id:info_div');
    $info_div->setAttribute('class', 'info_div');
    $label_text = CDOMElement::create('span','class:info');
    $label_text->addChild(new CText($message));
    $info_div->addChild($label_text);
    $homeUser = $userObj->getHomePage();
    $link_span = CDOMElement::create('span','class:info_link');
    $link_to_home = BaseHtmlLib::link($homeUser, translateFN('vai alla home per accedere.'));
    $link_span->addChild($link_to_home);
    $info_div->addChild($link_span);
    //$data = new CText(translateFN('La tua iscrizione è stata effettuata con successo.'));
    $data = $info_div;
//    print_r($data->getHtml());
    $path = translateFN('modulo di iscrizione');
    $dati .= $link_torna_home;

    $field_data = array(
     'menu'=>"", //$menu,
     'banner'=>$banner,
     'path'=>$path,
     //'data'=>$dati,
     'data'=>$info_div->getHtml(),
     'help'=>'', // $help,
     'user_name'=>$user_name,
     'user_type'=>$user_type,
     'messages'=>$user_messages->getHtml(),
     'agenda'=>$user_agenda->getHtml(),
     'titolo_corso'=>$course_name,
     'annulla_iscrizione'=>$link_annulla_iscrizione,
     'price'=>$price
    );


} else {
    $dati = translateFN('Impossibile proseguire, Provider non trovato');
    $field_data = array(
     'menu'=>"", //$menu,
     'banner'=>$banner,
     'data'=>$dati,
     'help'=>'', // $help,
     'user_name'=>$user_name,
     'user_type'=>$user_type,
     'messages'=>$user_messages->getHtml(),
     'agenda'=>$user_agenda->getHtml(),
     'titolo_corso'=>$course_name,
     'annulla_iscrizione'=>$link_annulla_iscrizione,
     'price'=>$price
    );

}

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $field_data);

?>