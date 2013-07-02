<?php

class REST_request {
    public function sendRequest ($toSearch=NULL,$curlHeader,$url,$curlPost=false,$mandatary=null) {

       $service_url = $url;
       $curl_post_data = $toSearch;

       
        $ch = curl_init ($url) ;
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true) ;
//        culr_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt ($ch, CURLOPT_POST, $curlPost);

//        if ($toSearch != NULL ) {
        if ($curlPost) {
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $curl_post_data);
        }
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $curlHeader);  
        $head = curl_getinfo ($ch, CURLINFO_HEADER_OUT);     
        $res = curl_exec ($ch) ;
        curl_close ($ch) ;
//        print_r($res);
        return ($res) ;       
       
    }
}

