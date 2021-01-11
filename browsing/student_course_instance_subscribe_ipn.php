<?php

/**
 *
 * @package		Subscription IPN from Paypal
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright   Copyright (c) 2009-2012, Lynx s.r.l.
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
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR,);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout',)
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';

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
BrowsingHelper::init($neededObjAr);

require_once ROOT_DIR . '/include/CourseInstance.inc.php';


/*
 * INCLUSIONE SPECIFICA PER PAYPAL
 */
if (file_exists(ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php')) {
    require_once ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php';
    $paypal_allowed = TRUE;
}

/*
* GESTIONE LOG
*/
$logStr = "";
if (!is_dir(ROOT_DIR . '/log/paypal/')) {
    $oldmask = umask(0);
    mkdir (ROOT_DIR . '/log/paypal/', 0775, true);
    umask($oldmask);
}
$log_file = ROOT_DIR . '/log/paypal/' . PAYPAL_IPN_LOG;
$logFd = fopen($log_file, "a");
$fpx = fopen($log_file, 'a');

$debug = 1;
if ($debug == 1) {
    fwrite($fpx, "INIZIO processo IPN\n");
    fwrite($fpx, "Prima di init \n");
}

$today_date = today_dateFN();
$providerId = DataValidator::is_uinteger($_REQUEST['provider']);
$courseId = DataValidator::is_uinteger($_REQUEST['course']);
$instanceId = DataValidator::is_uinteger($_REQUEST['instance']);
$studentId = DataValidator::is_uinteger($_REQUEST['student']);

$testerInfoAr = $common_dh->get_tester_info_from_id($providerId, AMA_FETCH_BOTH);
$buyerObj = read_user($studentId);
if ((is_object($buyerObj)) && (!AMA_dataHandler::isError($buyerObj))) {
    if (!AMA_Common_DataHandler::isError($testerInfoAr)) {
        $provider_name = $testerInfoAr[1];
        $tester = $testerInfoAr[10];
        $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
        // $currentTesterId = $newTesterId;
        $GLOBALS['dh'] = $tester_dh;
        $dh = $tester_dh;

        // id dello studente
        if (!isset($instanceId)) {
            $instanceId = $sess_id_user; // ??????
        }

        /*
         * Instance Object
         */
        $instanceObj = new course_instance($instanceId);
        $price = $instanceObj->getPrice();
        $user_level = $instanceObj->getStartLevelStudent();
        $course = $dh->get_course($courseId);
        $course_name = $course['titolo'];

        /*
         * GESTIONE IPN DA PAYPAL
         *
         */

        // assigned session variables to local variables
        $paypal_email_address = PAYPAL_ACCOUNT;
        $product_price = $price;
        $price_currency = CURRENCY_CODE;
        $paypal_ipn_url = PAYPAL_IPN_URL;

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
            //            if ($debug == 1) { fwrite($fpx, "$key = $value \n"); }
        }

        // post back to PayPal system to validate
        $header  = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n";
        $header .= "Host: $paypal_ipn_url\r\n";
        $header .= "User-Agent: PHP-IPN-Verification-Script\r\n";
        $header .= "Connection: close\r\n\r\n";

        //$fp = fsockopen ($paypal_ipn_url, 80, $errno, $errstr, 30);
        $fp = fsockopen('ssl://' . $paypal_ipn_url, 443, $errno, $errstr, 30);

        // assign posted variables to local variables
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
        // $invoice = $_POST['invoice'];
        $customeripaddress = $_POST['custom'];
        $productname = $_POST['item_name1'];

        if (!$fp) {
            //print "<b>Error Communicating with Paypal.<br>";
            $log_error = 'http error=' . $errno;
            $message = translateFN("Errore di comunicazione con Paypal. Impossibile proseguire");
            if ($debug == 1) {
                fwrite($fpx, "Error connecting to Paypal\n");
                fwrite($fpx, $log_error . "\n");
            }
        } else {
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                if (strcmp($res, "VERIFIED") == 0) {
                    // $ipn_log .= "Paypal IPN VERIFIED\n";
                    if ($debug == 1) {
                        fwrite($fpx, "Paypal IPN VERIFIED\n");
                    }
                    $firstname = $buyerObj->getFirstName();
                    $lastname = $buyerObj->getLastName();
                    $username = $buyerObj->getUserName();

                    if (trim($receiver_email) == '') {
                        $receiver_email = $_POST['receiver_email'];
                    }
                    $ipn_log  = "\nPRODUCT DETAILS CHECK\n";
                    $ipn_log .= "|$receiver_email| : |$paypal_email_address|\n";
                    $ipn_log .= "|$payment_amount| : |$product_price|\n";
                    $ipn_log .= "|$payment_currency| : |$price_currency\n";
                    $ipn_log .= "|$payment_status| : |Completed|\n\n";

                    if ($debug == 1) {
                        fwrite($fpx, "\nStudent: $studentId , Class: $instanceId \n");
                        fwrite($fpx, "\nPRODUCT DETAILS CHECK\n");
                        fwrite($fpx, "|$receiver_email| : |$paypal_email_address|\n");
                        fwrite($fpx, "|$payment_amount| : |$product_price|\n");
                        fwrite($fpx, "|$payment_currency| : |$price_currency|\n");
                        fwrite($fpx, "|$payment_status| : |Completed|\n\n");
                    }
                    if (
                        // ($receiver_email == $paypal_email_address) &&
                        ($payment_amount == $product_price) &&
                        ($payment_currency == $price_currency) &&
                        ($payment_status == 'Completed')
                    ) {
                        $ipn_log .= "Paypal IPN DATA OK\n";
                        if ($debug == 1) {
                            fwrite($fpx, "Paypal IPN DATA OK\n");
                        }
                        $body_mail = translateFN("Hai effettuato il pagamento di") . " " . $payment_amount . " EUR " . translateFN('tramite Paypal' . "\n\r");
                        $body_mail .= translateFN('Questo addebito verrà visualizzato sull\'estratto conto della carta di credito o prepagata come pagamento a PAYPAL') . ' '. PAYPAL_NAME_ACCOUNT;
                        $message_ha["titolo"] = PORTAL_NAME . " - " . translateFN('Conferma di pagamento') . ' - ' . translateFN("Iscrizione al corso:") . " " . $course_name;
                        $sender_email = ADA_ADMIN_MAIL_ADDRESS;
                        $recipients_emails_ar = array($payer_email);
                        if (!in_array($buyerObj->getEmail(), $recipients_emails_ar)) {
                            $recipients_emails_ar[] = $buyerObj->getEmail();
                        }

                        // iscrizione al corso
                        $status = 2;
                        $res = $dh->course_instance_student_subscribe($instanceId, $studentId, $status, $user_level);
                        if (AMA_DataHandler::isError($res)) {
                            $msg = $res->getMessage();
                            //                    $dh->course_instance_student_presubscribe_remove($id_course_instance,$id_studente);
                            //                    header("Location: $error?err_msg=$msg");
                            $message_ha["testo"] = translateFN('Gentile') . " " . $firstname . ",\r\n" . translateFN("Si è verificato un errore nell'iscrizione al corso") . " " . $course_name . "\n\r\n\r";
                            $message_ha["testo"] .=  $body_mail;
                            $message_ha["testo"] .= "\n\r\n\r" . translateFN('Per maggiori informazioni scrivi una mail a:') . " " . ADA_ADMIN_MAIL_ADDRESS;
                            $message_ha["testo"] .= "\n\r" . translateFN("Buono studio.");
                            $sender_email = ADA_ADMIN_MAIL_ADDRESS;
                            $recipients_emails_ar = array($payer_email, $buyerObj->getEmail());
                        } else {
                            //                  header("Location: $back_url?id_studente=$id_studente");
                            // Send mail to the user with his/her data.
                            $switcherTypeAr = array(AMA_TYPE_SWITCHER);
                            $extended_data = TRUE;
                            $switcherList = $dh->get_users_by_type($switcherTypeAr, $extended_data);
                            if (!AMA_DataHandler::isError($switcherList)) {
                                $switcher_email = $switcherList[0]['e_mail'];
                            } else {
                                $switcher_email = ADA_ADMIN_MAIL_ADDRESS;
                            }
                            $notice_mail = sprintf(translateFN('Questa è una risposta automatica. Si prega di non rispondere a questa mail. Per informazioni scrivere a %s'), $switcher_email);
                            $message_ha["testo"] = $notice_mail . "\n\r\n\r";

                            $message_ha["testo"] .= translateFN('Gentile') . " " . $firstname . ",\r\n" . translateFN("grazie per esserti iscritto al corso") . " " . $course_name . "\n\r\n\r";
                            $message_ha["testo"] .=  $body_mail;
                            //$message_ha["testo"] .= "\n\r\n\r". translateFN("Ti ricordiamo i tuoi dati di accesso.\n\r username: ") . $user_name . "\n\r" . translateFN("password:" . " " . $user_password);
                            $message_ha["testo"] .= "\n\r\n\r" . translateFN("Questo è l'indirizzo per accedere al corso: ") . "\n\r" . $http_root_dir . "\n\r";
                            $message_ha["testo"] .= "\n\r" . translateFN("Una volta fatto il login, potrai accedere al corso");
                            $message_ha["testo"] .= "\n\r" . translateFN("Buono studio!");
                            $message_ha["testo"] .= "\n\r" . PORTAL_NAME;
                            $message_ha["testo"] .= "\n\r\n\r --------\r\n" . translateFN('Dettagli di pagamento.');
                            $message_ha["testo"] .= "\r\n" . translateFN('Nome e cognome:') . " " . $firstname . " " . $lastname;
                            $message_ha["testo"] .= "\r\n" . translateFN('Username:') . " " . $username;
                            $message_ha["testo"] .= "\r\n" . translateFN('Importo:') . " " . $payment_currency . " " . $payment_amount;
                            $message_ha["testo"] .= "\r\n" . translateFN('Iscrizione al corso:') . " " . $course_name;
                            $message_ha["testo"] .= "\r\n" . translateFN('ID della transazione:') . " " . $txn_id;
                            $message_ha["testo"] .= "\r\n --------\r\n";
                            //                    $message_ha["testo"] .= "\n\r\n\r". "------------------";

                            if ($debug == 1) {
                                fwrite($fpx, "Inviata mail a " . implode(",", $recipients_emails_ar) . "\n");
                            }
                        }
                        $mailer = new Mailer();
                        $res = $mailer->send_mail($message_ha, $sender_email, $recipients_emails_ar);
                    } else {
                        $message = translateFN('Gentile') . " " . $firstname . ", <BR />";
                        $message .= translateFN('il corso pagato non corrisponde ai dettagli in nostro possesso') . "<BR />";
                        $message .= translateFN('se hai bisogno di maggiori informazioni scrivi una mail a:') . " " . ADA_ADMIN_MAIL_ADDRESS;

                        $ipn_log .= "Purchase does not match product details\n";
                        if ($debug == 1) {
                            fwrite($fpx, "Purchase does not match product details\n");
                        }
                    }
                } else if (strcmp($res, "INVALID") == 0) {
                    /*
                        $message = translateFN('Gentile') . " " . $firstname .", <BR />";
                        $message .= translateFN('Non è possibile verificare il tuo acquisto')."<BR />";
                        $message .= translateFN('Forse provando più tardi riuscirai ad acquistare il corso.');
         *
         */

                    $ipn_log .= "INVALID: We cannot verify the purchase\n";
                    if ($debug == 1) {
                        fwrite($fpx, "INVALID: We cannot verify your purchase\n");
                    }
                }
            }
            fclose($fp);
        }

        $ipn_log = "\nPOST DATA\n";
        if ($debug == 1) {
            fwrite($fpx, "\nPOST DATA\n");
        }

        foreach ($_POST as $key => $value) {
            $ipn_log .= "$key: $value\n";
            if ($debug == 1) {
                fwrite($fpx, "$key: $value\n");
            }
        }

        if ($debug == 1) {
            fclose($fpx);
        }
        /*
         * FINE GESTIONE IPN DA PAYPAL
         *
         */
    } else {
        /*
        * GESTIONE LOG
        */
        $logStr = "";
        $log_file = ROOT_DIR . '/browsing/paypal/' . PAYPAL_IPN_LOG;
        $logFd = fopen($log_file, "a");
        $fpx = fopen($log_file, 'a');

        $debug = 1;
        if ($debug == 1) {
            fwrite($fpx, "IPN Process started \n");
            fwrite($fpx, "IPN internal error of ADA \n");
        }
        fclose($fp);
    }
}
// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");
die();
