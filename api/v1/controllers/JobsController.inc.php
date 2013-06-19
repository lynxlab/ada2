<?php

class JobsController extends openLaborController {
    
    /**
     * 
     * @param type $request
     */
    public function getAction($request,$format) {
//        print_r($request);
//    public function getActionSearch($request) {
        require_once(__DIR__ .'/../include/config.inc.php');
//        echo "<br />Siamo nel posto giusto (getActionSearch): ";
//        print_r($request);
        if (!isset($request['jobID'])) {
            $toSearch = array();
            $toSearch['keywords'] = $request['keywords'];
            $toSearch['cityCompany'] = $request['city'];
            $toSearch['qualificationRequired'] = $request['qualification'];
            //print_r($toSearch);
            /*
            $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
            $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
            $cpiAr = $cpiObj->contents;
             * 
    //        print_r($remoteJobs->contents);
            $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
             */
            $jobResult = $this->searchData($toSearch,'cityCompany');
            $NumResult = count($jobResults);
            
        } else {
            $jobResult = $this->getJob($request['jobID']);
        }
        $this->LogRequest($_REQUEST,$_SERVER,$NumResult);
        if ($format == 'xml') {
//                $jobResultXML = openLaborController::array2xml($jobResult,'jobs');
                $jobResult = openLaborController::array2xml($jobResult,'job');
                header ("Content-type: text/xml");
                //$jobResult = $jobResultXML->asXML();
            } else {
            $jobResult = json_encode($jobResult);
        }
        echo $jobResult;
        
    }

    
    public function getActionSimple($request) {
        echo "<br />Siamo nel posto giusto: $request";
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
    }

    public function postAction($request) {
        echo "postAction<br />Siamo nel posto giusto";
        //print_r($request);
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
    }
    public function getJob($jobId) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        $jobOffer = $dh->getJobFromId($jobId);
//        $jobOfferJson = json_encode($jobOffer);
        return $jobOffer;
        //echo $jobOfferJson;
    }    
    public function searchData($toSearch=array(),$keyMandatory=null) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        $today_date = today_dateFN();
        $todayUT = Abstract_AMA_DataHandler::date_to_ts($today_date);

        $clause = 'where jobexpiration >= '.$todayUT;
        if ($toSearch['keywords'] != '' && $toSearch['keywords'] != null) {
            $curlPost = false;
            $urlSemanticApi = URL_LAVORI4.$toSearch['keywords'];
            $keywords = $toSearch['keywords'];
            //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
            $curlHeader = '';
            $jobsCode = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
            
            $resultAR = json_decode($jobsCode, TRUE);
            if (count($resultAR) > 0) {
                $professionalCodes = $resultAR['job_types']['categories'];
                $clause .= ' AND ';
                for ($i=0; $i<=NUMBER_CODE;$i++) {
                    $professionalCode = $professionalCodes[$i]['category'];
                    switch ($i) {
                        case 0:
                            $clause .= '(positionCode like \''.$professionalCode.'%\'';
                            break;
                        case NUMBER_CODE:
                            $clause .= ' OR positionCode like \''.$professionalCode.'%\')';
                            break;
                        default:
                            $clause .= ' OR positionCode like \''.$professionalCode.'%\'';
                            break;
                    }
                }
            }
        }
        if ($toSearch['cityCompany'] != '' && $toSearch['cityCompany'] != null) {
            $clause .= ' AND cityCompany = \''. $toSearch['cityCompany'].'\'';
        }

        if ($toSearch['qualificationRequired'] != '' && $toSearch['qualificationRequired'] != null) {
            $clause .= ' AND '. constant($toSearch['qualificationRequired']);
        }        
        
        $jobOffers = $dh->listOffers($clause);
        return $jobOffers;
        if (count($toSearch) == 0) {
            
        }
    }
}

/*
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
//        print_r($remoteJobs->results);
 * 
 */

?>
