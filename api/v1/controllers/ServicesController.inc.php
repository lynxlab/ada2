<?php
/**
 * @package	OpenLabor Web Service 
 * @author	Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright   Copyright (c) 2013, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version	0.1
 * 
 * @abstract    services list.

 */

class ServicesController extends openLaborController {
    
    public function getAction($request, $format,$url_elements) {
        if (isset($url_elements[2])) {
            $servicesFile = ROOT_DIR.'/api/'.VERSION.'/'.DIR_INFO_SERVICES.'/'.$url_elements[2].'.xml';
        } else {
            $servicesFile = ROOT_DIR.'/api/'.VERSION.'/'.SERVICES_INFO; 
        }
        if ($format == 'xml') {
           if ($fid = @fopen($servicesFile,'r')){
              while (!feof($fid))
                $servicesInfo .= fread($fid,4096);
              fclose($fid);
            } else {
               $servicesInfo = 'NO services list';
            }   
                header ("Content-type: text/xml");
        } else {
            header ("Content-type: application/json");
            $servicesInfoXML = simplexml_load_file($servicesFile); 
            $servicesInfo = json_encode($servicesInfoXML);
        }
        echo $servicesInfo;
    }

    
}


?>
