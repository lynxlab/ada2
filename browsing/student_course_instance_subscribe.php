<?php
/**
 *
 * @package		subscription
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
    AMA_TYPE_STUDENT => array('layout', 'course', 'course_instance')
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

//require_once ROOT_DIR . '/include/CourseInstance.inc.php';


/*
 * INCLUSIONE SPECIFICA PER PAYPAL
 */
if (file_exists(ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php')) {
    require_once ROOT_DIR . '/browsing/paypal/paypal_conf.inc.php';
    $paypal_allowed = TRUE;
}

$today_date = today_dateFN();

//$id_course_instance = $_REQUEST['id_instance'];
//$id_studente = $_REQUEST['id_student'];

$providerId = DataValidator::is_uinteger($_GET['provider']);
$courseId = DataValidator::is_uinteger($_GET['id_course']);
$instanceId = DataValidator::is_uinteger($_GET['id_course_instance']);


$testerInfoAr = $common_dh->get_tester_info_from_id($providerId,AMA_FETCH_ASSOC);
//var_dump($testerInfoAr);
if(!AMA_Common_DataHandler::isError($testerInfoAr)) {
    $provider_name = $testerInfoAr['nome'];
    $tester = $testerInfoAr['puntatore'];
    $tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($tester));
//	var_dump($newTesterId);die();
    $GLOBALS['dh'] = $tester_dh;
    /*
     * Instance Object
     */
    $instanceObj = new course_instance($instanceId);
//    print_r($instanceObj);
    $price = $instanceObj->getPrice();
    $id_course = $instanceObj->getCourseId();
    $course = $dh->get_course($courseId);
//    print_r($course);
    $course_name = $course['titolo'];
    //$instance_name = $course_instance

    $item_desc = translateFN('Iscrizione al corso');
    if (floatval($price) > 0) {
        $self =  'iscrizione_pagamento';
        $GLOBALS['self'] = $self;
    }else {
        $self =  'iscrizione_gratis';
        $GLOBALS['self'] = $self;
    }

    /*
     * Get/set Paypal defintion
     */
    if ($paypal_allowed) {
        $business = PAYPAL_ACCOUNT;
        $action = PAYPAL_ACTION;
        $currency_code = CURRENCY_CODE;
        $rm = RM;
        $amount1 = $price;
        if ($amount1 > 0) {
            $studentId = $userObj->getId();
            $cmd = PAYPAL_CMD;
            $no_shipping = NO_SHIPPING;
            $price = str_replace(".",",",$amount1);
            $notify_url = "$http_root_dir/browsing/student_course_instance_subscribe_ipn.php?instance=$instanceId&student=$studentId&provider=$providerId&course=$courseId";
            $return_url = "$http_root_dir/browsing/student_course_instance_subscribe_confirm.php?instance=$instanceId&student=$studentId&provider=$providerId&course=$courseId";
            $item_desc = translateFN('Iscrizione al corso');
            $formData = array(
                'id_course_instance' => $instanceId,
                'business'=> $business,
                'action'=> $action,
                'currency_code' => $currency_code,
                'notify_url' => $notify_url,
                'return' => $return_url,
                'upload' => "1",
                'address1' => $userObj->getAddress(),
                'city' => $userObj->getCity(),
                'zip' => '00000', //$userObj->getCAP(),
                'country' => $userObj->getCountry(),
                'first_name' => $userObj->getFirstName(),
                'last_name' => $userObj->getLastName(),
                'address_override' => "0",
                'email' => $userObj->getEmail(),
                'amount_1' => $amount1,
                'cmd' => $cmd,
                'rm' => $rm,
                'item_name_1' => $item_desc . " " . $course_name,
                'no_shipping' => $no_shipping
            );

            require_once ROOT_DIR . '/include/Forms/InstancePaypalForm.inc.php';
            $form = new InstancePaypalForm();
            $form->fillWithArrayData($formData);
            $data = $form->getHtml();
//            print_r($form);
            //$form->fillWithRequestData($request);
        }
    }

    $formDataTransfer = array(
        'instance' => $instanceId,
        'course'=> $courseId,
        'provider'=> $providerId,
        'student' => $studentId
    );

    require_once ROOT_DIR . '/include/Forms/InstanceTransferForm.inc.php';
    $formTransfer = new InstanceTransferForm();
    $formTransfer->fillWithArrayData($formDataTransfer);
    $dataTransfer = $formTransfer->getHtml();
/*
    $href_conferma_bonifico = "$http_root_dir/browsing/student_course_instance_bonifico.php?instance=$instanceId&student=$studentId&provider=$providerId&course=$courseId";
    $link_conferma_bonifico = '<a href="'.$href_conferma_bonifico.'">'.translateFN('pagherò con Bonifico') . '</a>';
 *
 */
    $link_annulla_iscrizione = '<a href="'.$http_root_dir . '/info.php?op=undo_subscription&instance='.$instanceId.'&student='.$studentId
                               .'&provider='.$providerId.'&course='.$courseId
                               .'">'. translateFN('Annulla iscrizione') . '</a>';
    $content_dataAr = array(
    // 'home'=>$home,
    'menu'=>isset($menu) ? $menu : null,
    'banner'=>isset($banner) ? $banner : null,
     'data'=>$data,
     'data_bonifico'=>  $dataTransfer,
     'help'=>isset($help) ? $help : null,
    // 'status'=>$status,
     'user_name'=>$user_name,
     'user_type'=>$user_type,
     'messages'=>$user_messages->getHtml(),
     'agenda'=>$user_agenda->getHtml(),
     'titolo_corso'=>$course_name,
     'annulla_iscrizione'=>$link_annulla_iscrizione,
     'price'=>$price,
     'complete_name'=>$userObj->getFirstName() . ' ' .$userObj->getLastName(),
    );
}
$help = '';
$optionsAr['onload_func'] = 'initDoc();';

//print_r($content_dataAr);
/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);
