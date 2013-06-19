<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class TrainingController extends openLaborController {
    
    public function getAction($request) {
//    public function getActionSearch($request) {
        require_once(__DIR__ .'/../include/config.inc.php');
//        echo "<br />Siamo nel posto giusto (getAction): ";
        $toSearch = array();
        $toSearch['CourseName'] = $request['keywords'];
        $toSearch['Comune'] = $request['city'];
        $toSearch['TitoloDiStudioRichiesto'] = $request['qualification'];
        $remoteTraining = new remoteXMLResource(URL_OF_NON_FINANZIATA_PROV_ROMA,$trainingLabels,$trainingElements);
        $remoteTraining->search_training($toSearch,'Comune');
        $NumResult = count($remoteTraining->results);
        $this->LogRequest($_REQUEST,$_SERVER,$NumResult);
        $jsonData = $remoteTraining->getJsonData();
        echo $jsonData;
        //print_r($remoteJobs->results);
    }


    public function postAction($request) {
        echo "postAction<br />Siamo nel posto giusto";
        //print_r($request);
        /*
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
         * 
         */
    }

    
}

?>
