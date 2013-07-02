<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class NewsController extends openLaborController {
    
    public function getAction($request) {
       $newsfile = 'doc_news/news.html'; 
       if ($fid = @fopen($newsfile,'r')){
          while (!feof($fid))
            $newsmsg .= fread($fid,4096);
          fclose($fid);
        } else {
           $newsmsg = 'NO news';
//           $newsmsg = translateFN("File news non trovato");
        }
        echo $newsmsg;        
        //print_r($remoteJobs->results);
    }

    public function postAction($request) {
        echo "postAction<br />Siamo nel posto giusto";
        //print_r($request);
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
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
