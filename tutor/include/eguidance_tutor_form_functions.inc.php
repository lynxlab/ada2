<?php
/**
 * e-guidance tutor form functions
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
define('ADA_CSV_DELIMITER',"\t");
define('ADA_CSV_NEWLINE',"\n");
require_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';

function createCSVFileToDownload($form_dataAr = array()) {
  
  
  
  if(!isset($form_dataAr['user_fc']) || empty($form_dataAr['user_fc'])) {
    $fiscal_code = translateFN("L'utente non ha fornito il codice fiscale");
  }
  else {
    $fiscal_code = $form_dataAr['user_fc'];
  }
  
  $scoresAr = array(
    0 => '0 - ' . EguidanceSession::textForScore(0),
    1 => '1 - ' . EguidanceSession::textForScore(1),
    2 => '2 - ' . EguidanceSession::textForScore(2),
    3 => '3 - ' . EguidanceSession::textForScore(3)
  );

  $typeAr = array(
    1 => EguidanceSession::textLabelForField('sl_1'),
    2 => EguidanceSession::textLabelForField('sl_2'),        
    3 => EguidanceSession::textLabelForField('sl_3'),
    4 => EguidanceSession::textLabelForField('sl_4'),
    5 => EguidanceSession::textLabelForField('sl_5'),
    6 => EguidanceSession::textLabelForField('sl_6'),
    7 => EguidanceSession::textLabelForField('sl_7')
  );
  
  $t_of_guidance = (int)$form_dataAr['type_of_guidance'];
  $type_of_guidance = $typeAr[$t_of_guidance];
  
  /*
   * CSA total
   */
  $user_fullname    = $form_dataAr['user_fullname'];
  $user_country     = $form_dataAr['user_country'];
  $service_duration = $form_dataAr['service_duration'];
  $ud_1 = $form_dataAr['ud_1'];
  $ud_2 = $form_dataAr['ud_2'];
  $ud_3 = $form_dataAr['ud_3'];
  
  //$csa_total =  (int)$form_dataAr['csa_1_score'] + (int)$form_dataAr['csa_2_score'] + (int)$form_dataAr['csa_3_score']; 
  $csa_comments = $form_dataAr['ud_comments'];
  
  $pcitems1 = (int)$form_dataAr['pc_1'];
  $pcitems2 = (int)$form_dataAr['pc_2'];
  $pcitems3 = (int)$form_dataAr['pc_3'];
  $pcitems4 = (int)$form_dataAr['pc_4'];
  $pcitems5 = (int)$form_dataAr['pc_5'];
  $pcitems6 = (int)$form_dataAr['pc_6'];
  $pcitems_total    = $pcitems1 + $pcitems2 + $pcitems3 + $pcitems4 + $pcitems5 + $pcitems6;
  $pcitems_comments = $form_dataAr['pc_comments'];
  
  $ba1 = (int)$form_dataAr['ba_1'];
  $ba2 = (int)$form_dataAr['ba_2'];
  $ba3 = (int)$form_dataAr['ba_3'];
  $ba4 = (int)$form_dataAr['ba_4'];  
  $ba_total = $ba1 + $ba2 + $ba3 + $ba4;
  $ba_comments = $form_dataAr['ba_comments'];
  
  $t1 = (int)$form_dataAr['t_1'];
  $t2 = (int)$form_dataAr['t_2'];
  $t3 = (int)$form_dataAr['t_3'];
  $t4 = (int)$form_dataAr['t_4'];
  $t_total = $t1 + $t2 + $t3 + $t4;
  $t_comments = $form_dataAr['t_comments'];
  
  $pe1 = (int)$form_dataAr['pe_1'];
  $pe2 = (int)$form_dataAr['pe_2'];
  $pe3 = (int)$form_dataAr['pe_3'];
  $pe_total    = $pe1 + $pe2 + $pe3;
  $pe_comments = $form_dataAr['pe_comments'];
  
  $ci1 = (int)$form_dataAr['ci_1'];
  $ci2 = (int)$form_dataAr['ci_2'];
  $ci3 = (int)$form_dataAr['ci_3'];
  $ci4 = (int)$form_dataAr['ci_4'];
  $ci_total = $ci1 + $ci2 + $ci3 + $ci4;
  $ci_comments = $form_dataAr['ci_comments'];  
  
  $m1 = (int)$form_dataAr['m_1'];
  $m2 = (int)$form_dataAr['m_2'];
  $m_total = $m1 + $m2;
  $m_comments = $form_dataAr['m_comments'];

  $oc_comments = $form_dataAr['other_comments'];
  
  $dataAr = array(
    array(translateFN("Numero di codice fiscale/passaporto"), translateFN("Tipologia di intervento di orientamento a distanza")),
    array($fiscal_code, $type_of_guidance),
    array(translateFN("Nome e cognome dell'utente")),
    array($user_fullname),
    array(translateFN("Nazionalità dell'utente")),
    array($user_country),
    array(translateFN("Durata totale del vostro percorso di orientamento")),
    array($service_duration),
    array(translateFN('Caratteristiche utente'),translateFN('Monitoraggio del percorso di e-guidance')),
    array('',translateFN('Prima sessione di orientamento a distanza'),translateFN('Sessioni di orientamento a distanza successive alla prima'),translateFN('Ultima sessione di orientamento a distanza')),
    array(EguidanceSession::textLabelForField('area_pc')),
    
    array(EguidanceSession::textLabelForField('ud_1'), $ud_1),
    array(EguidanceSession::textLabelForField('ud_2'), $ud_2),
    array(EguidanceSession::textLabelForField('ud_3'), $ud_3),
   // array(translateFN('Totale'), $csa_total),  // COME SI FA A DARE UN PUNTEGGIO QUI?
    array(EguidanceSession::textLabelForField('ud_comments'), $csa_comments),
    
    array(EguidanceSession::textLabelForField('pc_title')),
    array(EguidanceSession::textLabelForField('pc_1'), $scoresAr[$pcitems1]),
    array(EguidanceSession::textLabelForField('pc_2'), $scoresAr[$pcitems2]),
    array(EguidanceSession::textLabelForField('pc_3'), $scoresAr[$pcitems3]),
    array(EguidanceSession::textLabelForField('pc_4'), $scoresAr[$pcitems4]),
    array(EguidanceSession::textLabelForField('pc_5'), $scoresAr[$pcitems5]),
    array(EguidanceSession::textLabelForField('pc_6'), $scoresAr[$pcitems6]),
    array(translateFN('Totale'), $pcitems_total),  
    array(EguidanceSession::textLabelForField('pc_comments'), $pcitems_comments),
    
    array(EguidanceSession::textLabelForField('area_pp')),
    array(EguidanceSession::textLabelForField('ba_title')),
    array(EguidanceSession::textLabelForField('ba_1'), $scoresAr[$ba1]),    
    array(EguidanceSession::textLabelForField('ba_2'), $scoresAr[$ba2]),
    array(EguidanceSession::textLabelForField('ba_3'), $scoresAr[$ba3]),
    array(EguidanceSession::textLabelForField('ba_4'), $scoresAr[$ba4]),
    array(translateFN('Totale'),$ba_total),
    array(EguidanceSession::textLabelForField('ba_comments'), $ba_comments),
    
    array(EguidanceSession::textLabelForField('t_title')),
    array(EguidanceSession::textLabelForField('t_1'), $scoresAr[$t1]),
    array(EguidanceSession::textLabelForField('t_2'), $scoresAr[$t2]),
    array(EguidanceSession::textLabelForField('t_3'), $scoresAr[$t3]),
    array(EguidanceSession::textLabelForField('t_4'), $scoresAr[$t4]),
    array(translateFN('Totale'), $t_total),
    array(EguidanceSession::textLabelForField('t_comments'), $t_comments),
    
    array(EguidanceSession::textLabelForField('pe_title')),
    array(EguidanceSession::textLabelForField('pe_1'), $scoresAr[$pe1]),
    array(EguidanceSession::textLabelForField('pe_2'), $scoresAr[$pe2]),
    array(EguidanceSession::textLabelForField('pe_3'), $scoresAr[$pe3]),
    array(translateFN('Totale'), $pe_total),
    array(EguidanceSession::textLabelForField('pe_comments'), $pe_comments),
    
    array(EguidanceSession::textLabelForField('ci_title')),
    array(EguidanceSession::textLabelForField('ci_1'),$scoresAr[$ci1]),
    array(EguidanceSession::textLabelForField('ci_2'),$scoresAr[$ci2]),
    array(EguidanceSession::textLabelForField('ci_3'),$scoresAr[$ci3]),
    array(EguidanceSession::textLabelForField('ci_4'),$scoresAr[$ci4]),
    array(translateFN('Totale'), $ci_total),
    array(EguidanceSession::textLabelForField('ci_comments'), $ci_comments),
    
    array(EguidanceSession::textLabelForField('m_title')),
    array(EguidanceSession::textLabelForField('m_1'),$scoresAr[$m1]),
    array(EguidanceSession::textLabelForField('m_2'),$scoresAr[$m2]),
    array(translateFN('Totale'), $m_total),
    array(EguidanceSession::textLabelForField('m_comments'), $m_comments),
    array(EguidanceSession::textLabelForField('other_comments'), $oc_comments)  
  );  
  
  $file_content = createCSVFileContent($dataAr);	

  header("Cache-Control: public");
  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=outputform.csv");
  header("Content-type: application/csv; charset=UTF-8");
  
  echo $file_content;
  exit();
}

function createCSVFileContent($dataAr = array()) {
  
  $file_content = "";
  foreach($dataAr as $row) {
    foreach($row as $column_in_row) {
      $file_content .= $column_in_row;
      $file_content .= ADA_CSV_DELIMITER;  
    }
    $file_content .= ADA_CSV_NEWLINE;
  }
  return $file_content;
}
?>