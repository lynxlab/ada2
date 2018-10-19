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
$variableToClearAR = array('layout', 'course', 'course_instance');
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
$self = 'student_course_instance_subscribe_confirm';
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

//require_once ROOT_DIR . '/include/CourseInstance.inc.php';

/*
 * INCLUSIONE SPECIFICA PER PAYPAL
 */
if (file_exists(ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php')) {
    require_once ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php';
    $paypal_allowed = TRUE;
}

$today_date = today_dateFN();
$providerId = DataValidator::is_uinteger($_POST['provider']);
$courseId = DataValidator::is_uinteger($_POST['course']);
$instanceId = DataValidator::is_uinteger($_POST['instance']);
$studentId = DataValidator::is_uinteger($_POST['student']);
$testerInfoAr = $common_dh->get_tester_info_from_id($providerId,'AMA_FETCH_ASSOC');

if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
    $provider_name = $testerInfoAr[1];
    $tester = $testerInfoAr[10];
    $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
    $currentTesterId = $newTesterId;
    $GLOBALS['dh'] = $tester_dh;
    $dh = $tester_dh;

    // id dello studente
    if (!isset($studentId)) {
            $studentId = $sess_id_user ;
    }
    /*
     * Instance Object
     */
    $instanceObj = new course_instance($instanceId);
    $price = $instanceObj->getPrice();
    $course = $dh->get_course($courseId);
    $course_name = $course['titolo'];

    /*
     * User data
     */

    $first_name = $userObj->getFirstName();
    $last_name = $userObj->getLastName();
    $username = $userObj->getUserName();
    $fiscalCode = $userObj->getFiscalCode();
    $userAddress = $userObj->getAddress();
    $userCity = $userObj->getCity();
    $userProvince = $userObj->getProvince();
    $userCountry = $userObj->getCountry();
    $userPhone = $userObj->getPhoneNumber();
    $userEmail = $userObj->getEmail();


    if (!isset($back_url)) {
            $back_url = "student.php";
    }

    // preparazione output HTML e print dell' output
    $title = translateFN("Conferma pagamento iscrizione al corso");
//    $link_annulla_iscrizione = "<a href=\"".$http_root_dir . "/iscrizione/student_course_instance_unsubscribe.php?id_instance=".
    $link_torna_home = "<a href=\"".$http_root_dir . "/browsing/student.php\">". translateFN('Torna alla Home') . "</a>";
    $message_ha["testo"] = translateFN('Gentile') . " " . $first_name .",\r\n" . translateFN("grazie per aver eseguito la pre-iscrizione al") . " " . $course_name . "\n\r\n\r";
    $message = nl2br($message_ha["testo"]);


    $message .= translateFN('Per completare l\'iscrizione devi effettuare il pagamento').'.<br />';
    $message .= translateFN('Il versamento di euro').' '. $price . ' ';
    $message .= translateFN('deve essere effettuato sul c/c n. XXXXX.XX').'<br />';
    $message .= translateFN('intestato a Lynx s.r.l.').'<br />';
    $message .= translateFN('Banca "NOME BANCA"').'<br />';
    $message .= translateFN('filiale di via tale, 9 - 00100 Roma').'<br />';
    $message .= translateFN('ABI 0000 CAB 00000').'<br />';
    $message .= translateFN('Codice Swift: CODICE SWIFT').'<br />';
    $message .= translateFN('Codice IBAN: CODICE IBAN').'<br /><br />';

    $message .= translateFN('Nella causale del bonifico devi indicare').': <br />';
    $message .= translateFN('Acquisto del').' '.$course_name. '<br />';
    $message .= translateFN('da parte di'). ' '. $first_name . ' '. $last_name.'.<br /><br />';

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
    $data = $info_div;

    $path = translateFN('modulo di iscrizione');

    /*
     * mail to administration
     */

    $management_subject = PORTAL_NAME . ' - ' . translateFN("Iscrizione al"). ' ' . $course_name;
    $management_sender_email = ADA_ADMIN_MAIL_ADDRESS;
    $recipient = PAYPAL_ADMIN_MAIL;
    $management_recipients_emails_ar = array($recipient);
    $management_body_mail = $first_name . ' '. $last_name . ', ' . translateFN('Username') . ': '. $username. PHP_EOL.PHP_EOL;
    $management_body_mail .= translateFN("Bonifico da effettuare di") . " ". $price ." EURO ". "\n\r";
    $management_body_mail .= translateFN("Iscrizione al"). ' ' . $course_name . PHP_EOL;

    $management_body_mail .= translateFN("Dati utente") . PHP_EOL;
    $management_body_mail .= '------------'.PHP_EOL;
    $management_body_mail .= translateFN('Nome'). ': '. $first_name . PHP_EOL;
    $management_body_mail .= translateFN('Cognome'). ': '.$last_name . PHP_EOL;
    $management_body_mail .= translateFN('Username'). ': '.$username . PHP_EOL;
    $management_body_mail .= translateFN('email'). ': '.$userEmail . PHP_EOL;
    $management_body_mail .= translateFN('Codice Fiscale'). ': '.$fiscalCode . PHP_EOL;
    $management_body_mail .= translateFN('Indirizzo'). ': '. $userAddress . PHP_EOL;
    $management_body_mail .= translateFN('Città'). ': '.$userCity . PHP_EOL;
    $management_body_mail .= translateFN('Provincia'). ': '.$userProvince . PHP_EOL;
    $management_body_mail .= translateFN('Nazione'). ': '.$userCountry . PHP_EOL;
    $management_body_mail .= translateFN('Telefono'). ': '.$userPhone . PHP_EOL;
    $management_body_mail .= '------------'.PHP_EOL;

    $management_message_ha["titolo"] = $management_subject;
    $management_message_ha["testo"] = $management_body_mail;
    $management_mailer = new Mailer();
    $res = $management_mailer->send_mail($management_message_ha, $management_sender_email, $management_recipients_emails_ar);

/*
 * mail to user
 */
    $user_mail_body = str_replace('<br />', PHP_EOL, $message);
    $user_mail_body .= translateFN('Per ulteriori informazioni scrivi a').': '. PAYPAL_ADMIN_MAIL;
    $user_mail_subject = $management_subject;
    $user_recipients_emails_ar = array($userEmail);
    $user_message_ha["titolo"] = $user_mail_subject;
    $user_message_ha["testo"] = $user_mail_body;
    $user_mailer = new Mailer();
    $res = $user_mailer->send_mail($user_message_ha, $management_sender_email, $user_recipients_emails_ar);


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