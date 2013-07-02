<?php
/**
 * @package	OpenLabor Web Service 
 * @author	Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright   Copyright (c) 2013, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version	0.1
 * 
 * @abstract    discovery service.

 */

class DiscoveryController extends openLaborController {
    
    public function getAction($request, $format) {
        $discoveryFile = ROOT_DIR.'/api/'.VERSION.'/'.DISCOVERY_INFO; 
        if ($format == 'xml') {
           if ($fid = @fopen(DISCOVERY_INFO,'r')){
              while (!feof($fid))
                $discoveryInfo .= fread($fid,4096);
              fclose($fid);
            } else {
               $discoveryInfo = 'NO discovery service';
    //           $newsmsg = translateFN("File news non trovato");
            }
                header ("Content-type: text/xml");
        } else {
            header ("Content-type: application/json");
            $discoveryInfoXML = simplexml_load_file($discoveryFile); 
            $discoveryInfo = json_encode($discoveryInfoXML);
//            $discoveryInfoAr = openLaborController::XML2Array($discoveryInfo);
//            $discoveryInfo = json_encode($discoveryInfoAr);
        }
        echo $discoveryInfo;
    }

    
}


?>
