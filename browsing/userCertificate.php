<?php

/**
 * Outputs user's credits as PDF (HTML, JSON etc)
 *
 * @package		view
 * @author		Sara Capotosti <sara@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'user','course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER,AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
   AMA_TYPE_SWITCHER =>array('layout', 'user','course','course_instance'),
   AMA_TYPE_STUDENT=>array('layout', 'course','course_instance')
);

/**
 * Performs basic controls before entering this module
 */

require_once ROOT_DIR . '/include/module_init.inc.php';

require_once ROOT_DIR .'/include/Course.inc.php';
require_once ROOT_DIR .'/include/CourseInstance.inc.php';

require_once 'include/browsing_functions.inc.php';
require_once '../switcher/include/switcher_functions.inc.php';

$self = whoami();

$title =  translateFN('Attestato di frequenza');

$logo='<img src="'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/header-logo.png"  />';

if(isset($_GET['id_user']))
{
    $id_user = $_GET['id_user'];
}
if(isset($_GET['id_instance']))
{
    $id_instance = $_GET['id_instance'];
}
$codice_corso = $courseObj->getCode();

$UserCertificateObj = Multiport::findUser($id_user,$id_instance);

$userFullName = $UserCertificateObj->getFullName();
$gender = $UserCertificateObj->getGender();
$birthplace = $UserCertificateObj->getBirthCity();
$codFisc = $UserCertificateObj->getFiscalCode();
$province = $UserCertificateObj->getProvince();
$birthdate = $UserCertificateObj->getBirthDate();


if (strToUpper($gender) == "F"){
	$nato = translateFN('nata');
} else {
	$nato = translateFN('nato');
}
if((!is_null($birthplace) && stripos($birthplace,'NULL')===false && strlen($birthplace)>0) && (!is_null($birthdate) && $birthdate>0 && strlen($birthdate)>0)){
    $birthSentence="";
}
if(!is_null($codFisc) && stripos($codFisc,'NULL')===false && strlen($codFisc)>0)
{
    $CodeFiscSentence = translateFN(' Codice Fiscale: ').$codFisc;
}
if(!is_null($courseObj->getTitle()) && stripos($courseObj->getTitle(),'NULL')===false && strlen($courseObj->getTitle())>0){
    $mainSentence = '<strong>'.$courseObj->getTitle().'</strong>';
}

$UserCertificateObj->set_course_instance_for_history($id_instance);
$user_historyObj = $UserCertificateObj->history;
$time = $user_historyObj->history_nodes_time_FN();
$timeSentence = translateFN('Monte ore frequentato: ').'<strong>'.$time.translateFN(' ore </strong>');

$data_inizio=$courseInstanceObj->getStartDate();

if($data_inizio!='')
{
    $data_Sentence = translateFN('Data inizio corso: ').'<strong>'.$data_inizio.'</strong>';
}

$testerAr=$common_dh->get_tester_info_from_id_course($courseObj->getId());

if(!is_null($testerAr['nome']) && stripos($testerAr['nome'],'NULL')===false && strlen($testerAr['nome'])){
    $providerSentence = translateFN('Provider che ha organizzato il corso: ').'<strong>'.$testerAr['nome'].'</strong>';
}

$currentData=ts2dFN(time());
$luogo=$testerAr['citta'];
$placeAndDate = $luogo.' '.$currentData;

$responsabile = $testerAr['responsabile'];
$signature = translateFN('Il Rappresentante Legale del Provider: ').$responsabile;

$content_dataAr   = array(
 'logo'=> $logo, 
 'title'=> $title,
 'logoProvider'=>null,
 'userFullName'=>$userFullName,
 'birthSentence'=>$birthSentence,
 'CodeFiscSentence'=>$CodeFiscSentence,
 'mainSentence'=>$mainSentence,
 'timeSentence'=>$timeSentence,
 'data_Sentence'=>$data_Sentence,
 'providerSentence'=>$providerSentence,
 'placeAndDate'=>$placeAndDate,
 'signature'=>$signature
 );
ARE::render($layout_dataAr, $content_dataAr,ARE_PDF_RENDER,array('outputfile'=>translateFN('Attestato').'-['.$codice_corso.']-['.$id_user.']'));


