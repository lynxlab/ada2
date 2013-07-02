<?php

/**
 * Search in a XML file.
 * return json or HTML
 *
 * @package		OpenLabor
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 */

// error_reporting(E_ALL ^ E_NOTICE);
// ini_set('display_errors','On');
if (!array_key_exists('op', $_POST)) {
//    $op = 'trai';
    $op = 'jobs';
} else {
    $op = $_POST['op'];
}
if (array_key_exists('out', $_POST)) {
    $out = $_POST['out'];
}
if (!($_POST)) {
    $_POST['DescrizioneProfiloProfessionale'] = 'ALL';
    $_POST['Qualifica'] = 'ALL';
    $_POST['ComuneAzienda'] = 'ALL';
    $_POST['TipologiaTitoloStudioRichiesto'] = 'ALL';
} 

$toSearch['DescrizioneProfiloProfessionale'] = $_POST['DescrizioneProfiloProfessionale'];
$toSearch['Qualifica'] = $_POST['Qualifica'];
$toSearch['ComuneAzienda'] = $_POST['ComuneAzienda'];
$toSearch['TipologiaTitoloStudioRichiesto'] = $_POST['TipologiaTitoloStudioRichiesto'];


//print_r($_POST);
/*
 * caso particolare!
 */
if ($toSearch['DescrizioneProfiloProfessionale'] == '') {
    $toSearch['DescrizioneProfiloProfessionale'] = $toSearch['Qualifica'];
}
if ($toSearch['Qualifica'] == '') {
    $toSearch['Qualifica'] = $toSearch['DescrizioneProfiloProfessionale'];
}
$toSearch['Annotazioni'] = $toSearch['DescrizioneProfiloProfessionale'];

require_once('./include/config.inc.php');
require_once("./include/extract_class.inc.php"); // la posizione è quella di view

switch ($op) {
    case 'jobs':
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
//        print_r($remoteJobs->results);
        break;
    case 'training':
        $toSearch['CourseName'] = $toSearch['Qualifica'];
        $toSearch['TitoloDiStudioRichiesto'] = $toSearch['TipologiaTitoloStudioRichiesto'];
        $toSearch['Comune'] = $toSearch['ComuneAzienda'];
        unset($toSearch['DescrizioneProfiloProfessionale']);
        unset($toSearch['Qualifica']);
        unset($toSearch['Annotazioni']);
        unset($toSearch['ComuneAzienda']);
        unset($toSearch['TipologiaTitoloStudioRichiesto']);
        $remoteTraining = new remoteXMLResource(URL_OF_NON_FINANZIATA_PROV_ROMA,$trainingLabels,$trainingElements);
        //print_r($remoteTraining->contents);
        $remoteTraining->search_Training($toSearch,'Comune');
        break;
    case 'both':
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        //print_r($remoteResource->contents);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
        
        
        
}

//require_once("./include/tag_cloud.inc.php");

$summary =  'Risultati della ricerca'; // per: '.$labelsDesc;
$min = 0;
$max = 100;
switch ($op) {
    case 'jobs':
        $elements = array('ComuneAzienda','Qualifica','DescrizioneProfiloProfessionale','TipologiaTitoloStudioRichiesto','CodiceQualifica');
        $labelsDesc = array('Comune', 'Qualifica', 'Profilo', 'Titolo di studio','Codice Qualifica');
        if ($out == 'html') {
                $remoteJobs->show_tabled_data($summary,$elements,$labelsDesc,$min,$max);
                $searchResult = $remoteJobs->getHtml();
                $result = $searchResult;
                echo $result;
        } else {
            $jsonData = $remoteJobs->getJsonData();
            echo $jsonData;
        }
        break;
    case 'training':
        $elements = array('Comune','CourseName','Istituto','DurataCorsoOre');
        $labelsDesc = array('Comune', 'Nome', 'Istituto', 'Durata (ore)');
        if ($out == 'html') {
                $remoteTraining->show_tabled_data($summary,$elements,$labelsDesc,$min,$max);
                $searchResult = $remoteTraining->getHtml();
                $result = $searchResult;
                echo $result;
        } else {
            $jsonData = $remoteTraining->getJsonData();
            echo $jsonData;
        }
        break;
}


