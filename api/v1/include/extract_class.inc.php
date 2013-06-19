<?php

define ("INDEX_MIN_CHARS", 6);

class remoteResource {


public function showHtml(){
	echo "<hr>";
	echo $this->htmlSource;
	echo "<hr>";
}

public function getHtml(){
	return $this->htmlSource;
}

}

/* RemoteXMLResource
 * useful to gather data from XML resource
 * 
 */
class remoteXMLResource extends remoteResource {
	var $baseurl = "";
	var $url = "";
	var $resourceType = 'xml'; 
	var $htmlSource = "";
	var $labels = array();
	var $elements = array();
	var $results = array();
	var $contents = array();
	var $XML = "";
	var $allText = "";
        var $cpi = array();
	

public function __construct($url,$labels=array(),$elements=array()) {

    $this->url = $url; // validate...
    $this->labels = $labels;
    $this->elements = $elements;


    $this->XML = simplexml_load_file($this->url);  // da estendere per i POST ?
    //print_r($this->XML);
    $this->content = $this->load_dataFN(); 	
    //$this->cpiAr = $this->load_CPI;
}

public function load_dataFN(){
/* set contents property */
	$XML = $this->XML;
	$labels = $this->labels;
	$elements = $this->elements;
        $element_count = count($elements);
	$content = array(); 
	$contents = array(); 
	$n = 0;
	foreach($XML as $onecontent){
		$n++;
		$content['internalID']	= $n;
		for ($k = 0; $k<$element_count; $k++) {
			//$content[$elements[$k]] = (string)$onecontent->$elements[$k];
			$content[$labels[$k]] = (string)$onecontent->$elements[$k];
		}
                $content['source']=$this->url;
		array_push($contents, $content);
	}
	$this->contents = $contents;

}


public function list_data($label, $summary, $min = 0,  $max = 10){
/* set  htmlSource	 property */
	$contents = $this->contents;
	$elenco_valori = array(); 
	foreach($contents as $onecontent){
			$value = $onecontent[$label]; 
			if ($elenco_valori[$value]!= NULL){
			   $elenco_valori[$value] = $elenco_valori[$value]+1; 
			} else {
				$elenco_valori[$value] = 1; 
			}
	
	}
	$n = 0;
	
	$htmlSource = $summary."<ol start=$min>"; // deprecated !!!
	while (list($key, $val) = each($elenco_valori)) {
		if (($n<$max) AND ($n>=$min)){
			$htmlSource.= "<li> $key: $val</li>";
			$n++;
		}
		
	}
	$htmlSource.= "</ol>";
	
	$this->htmlSource = $htmlSource;
	//var_dump($htmlSource);
}

public function summarize_data($label, $summary, $min = 0,  $max = 10){
/* set  htmlSource	 property as a tagcloud  */
//	require_once("tag_cloud.inc.php");
	
	$tags = array();
	$contents = $this->contents;
	$elenco_valori = array(); 
	foreach($contents as $onecontent){
			$value = $onecontent[$label]; 
			$this->allText.= " $value ";
	}
	//var_dump($this->allText);
	$index = $this->indexData();
	
	foreach ($index as  $tag=>$count){
		$tags[] = array('weight'  =>$count, 'tagname' =>$tag, 'url'=>"?search_term=$tag&min=$min&max=$max");
	}
	
	
	$tagCloud = new tagCloud($tags);
	$htmlSource = $tagCloud -> displayTagCloud();
	$this->htmlSource = $htmlSource;
	//var_dump($htmlSource);
}

/*
 * search training couurses
 * 
 * Meccanismo di ricerca
 * 1. Prima filtra su comune
 * 2. in caso di qualifica o profilo professionale cerca in entrambe
 * 3. caso titolo di studio: Laurea comprende diploma, licenza media, etc... NO!!! 
 */

public function search_training($toSearch=array(),$keyMandatory=null){
/* set result property */
	$contents = $this->contents;
	$list_value = array(); 
	$results = array();
	$n = 0;
        $searchAND = SEARCH_AND;
        if ($keyMandatory != null && $toSearch[$keyMandatory] != 'ALL' && $toSearch[$keyMandatory] != '') {
            $valueMandatory = $toSearch[$keyMandatory];
            foreach($contents as $onecontent){
                $value = $onecontent[$keyMandatory]; 
                if (stristr($value,$valueMandatory)){
                    $n++;
                    $results[] = $onecontent; 
                } 
            }
            $contents = $results;
            $results = array();
        }
        unset($toSearch[$keyMandatory]);
        $foundAr = array();
        foreach ($toSearch as $label => $valueToSearch) {
            unset($pattern);
            $second_value = 'noset';
            if ($valueToSearch != 'ALL' && $valueToSearch != '' &&  $label != 'TitoloDiStudioRichiesto') {
                if (strlen($valueToSearch)> 3) {
                    $second_value = substr($valueToSearch, 0, -1); // genere in modo rozzo (commessa e commesso) Assume che si tratti una parola
                }
                //$valueToSearch = substr($valueToSearch, 0, -1); // genere in modo rozzo (commessa e commesso)
            }
            switch ($label) {
                case 'CourseName':
                    $valueToSearch = trim($valueToSearch);
                    $wordsAr = explode(' ', $valueToSearch);
                    if (count($wordsAr) > 1) {
                        $pattern = '#'.implode('|', $wordsAr).'|'.$second_value.'#i';
                    } else {
                        $pattern = '#'.$valueToSearch.'|'.$second_value.'#i';
                    }
                    break;
                case 'TitoloDiStudioRichiesto':
                    if ($valueToSearch == 'laurea' && $valueToSearch != 'ALL') {
                        $valueToSearch = 'laurea';
                    } elseif ($valueToSearch == 'diploma') {
                        $pattern = '#'.constant($valueToSearch).'#i';
                        $second_value == 'media';
                    } elseif ($valueToSearch == 'medie' || $valueToSearch == 'media'){ 
                        $valueToSearch = 'media';
                        $second_value == 'scuola media';
                    }
                    break;
            }

            foreach($contents as $onecontent){
                $value = $onecontent[$label]; 
                
                switch ($label) {
                    case 'CourseName':
                        $valueA = $onecontent['CourseName'];
//                        print_r(array($pattern,$valueA,$valueB,$valueC));
                        if (isset($pattern)) {
                            if (preg_match($pattern,$valueA)||$valueToSearch == 'ALL' || $valueToSearch == '') {
                                $n++;
                                if (!in_array($onecontent['internalID'],$foundAr)) {
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['internalID']);
                                }
                            }
                        }
                        /*
                        else {
                            if (stristr($value,$valueToSearch) || stristr($value,$second_value) || $valueToSearch == 'ALL' || $valueToSearch == ''){
                                $n++;
                                if (!in_array($onecontent['Id'],$foundAr)) {
                                    $onecontent['CentroPerImpiego'] = $this->getCPI($onecontent['CentroPerImpiego'],$cpiAr); 
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['Id']);
                                }
                            }
                        }
                         * 
                         */
                        
                        break;
                    case 'TitoloDiStudioRichiesto':
                        if (isset($pattern)) {
                            if (preg_match($pattern,$value)) {
                                $n++;
                                if (!in_array($onecontent['internalID'],$foundAr)) {
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['internalID']);
                                }
                            }
                        } else {
                            if (stristr($value,$valueToSearch) || stristr($value,$second_value) || $valueToSearch == 'ALL' || $valueToSearch == ''){
                                $n++;
                                if (!in_array($onecontent['internalID'],$foundAr)) {
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['internalID']);
                                }
                            }
                        }
                        break;
                }
            }
            if ($searchAND) {
                $contents = $results;
                $results = array();
                $foundAr = array();
            }
        }

        if ($searchAND) {
            $this->results = $contents;
        } else {
            $this->results = $results;
        }
//        print_r(array(count($this->results),count($this->contents)));
}


/*
 * Meccanismo di ricerca
 * 1. Prima filtra su comune
 * 2. in caso di qualifica o profilo professionale cerca in entrambe
 * 3. caso titolo di studio: Laurea comprende diploma, licenza media, etc... NO!!! 
 */

public function search_data($toSearch=array(),$keyMandatory=null,$cpiAr=array()){
/* set result property */
	$contents = $this->contents;
//        print_r($contents);
	$list_value = array(); 
	$results = array();
	$n = 0;
        $searchAND = SEARCH_AND;
        if ($keyMandatory != null && $toSearch[$keyMandatory] != 'ALL' && $toSearch[$keyMandatory] != '') {
            $valueMandatory = $toSearch[$keyMandatory];
            foreach($contents as $onecontent){
                $value = $onecontent[$keyMandatory]; 
                if (stristr($value,$valueMandatory)){
                    $n++;
                    $results[] = $onecontent; 
                } 
            }
            $contents = $results;
            $results = array();
        }
        unset($toSearch[$keyMandatory]);
        $foundAr = array();
        foreach ($toSearch as $label => $valueToSearch) {
            unset($pattern);
            $second_value = 'noset';
            if ($valueToSearch != 'ALL' && $valueToSearch != '' &&  $label != 'TipologiaTitoloStudioRichiesto') {
                if (strlen($valueToSearch)> 3) {
                    $second_value = substr($valueToSearch, 0, -1); // genere in modo rozzo (commessa e commesso) Assume che si tratti una parola
                }
                //$valueToSearch = substr($valueToSearch, 0, -1); // genere in modo rozzo (commessa e commesso)
            }
            switch ($label) {
                case 'Qualifica':
                case 'DescrizioneProfiloProfessionale':
                case 'Annotazioni':
                    $valueToSearch = trim($valueToSearch);
                    $wordsAr = explode(' ', $valueToSearch);
                    if (count($wordsAr) > 1) {
                        $pattern = '#'.implode('|', $wordsAr).'|'.$second_value.'#i';
                    } else {
                        $pattern = '#'.$valueToSearch.'|'.$second_value.'#i';
                    }
                    break;
                case 'TipologiaTitoloStudioRichiesto':
                    if ($valueToSearch == 'laurea' && $valueToSearch != 'ALL') {
                        $valueToSearch = 'laurea';
                    } elseif ($valueToSearch == 'diploma') {
                        $pattern = '#'.constant($valueToSearch).'#i';
                        $second_value == 'media';
                    } elseif ($valueToSearch == 'medie' || $valueToSearch == 'media'){ 
                        $valueToSearch = 'media';
                        $second_value == 'scuola media';
                    }
                    break;
            }

            foreach($contents as $onecontent){
                $value = $onecontent[$label]; 
                
                switch ($label) {
                    case 'Qualifica':
                    case 'DescrizioneProfiloProfessionale':
                    case 'Annotazioni':
                        $valueA = $onecontent['Qualifica'];
                        $valueB = $onecontent['DescrizioneProfiloProfessionale'];
                        $valueC = $onecontent['Annotazioni'];
//                        print_r(array($pattern,$valueA,$valueB,$valueC));
                        if (isset($pattern)) {
                            if (preg_match($pattern,$valueA)||preg_match($pattern,$valueB)||preg_match($pattern,$valueC)|| 
                                    $valueToSearch == 'ALL' || $valueToSearch == '') {
                                $n++;
                                if (!in_array($onecontent['Id'],$foundAr)) {
                                    $onecontent['CentroPerImpiego'] = $this->getCPI($onecontent['CentroPerImpiego'],$cpiAr); 
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['Id']);
                                }
                            }
                        }
                        /*
                        else {
                            if (stristr($value,$valueToSearch) || stristr($value,$second_value) || $valueToSearch == 'ALL' || $valueToSearch == ''){
                                $n++;
                                if (!in_array($onecontent['Id'],$foundAr)) {
                                    $onecontent['CentroPerImpiego'] = $this->getCPI($onecontent['CentroPerImpiego'],$cpiAr); 
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['Id']);
                                }
                            }
                        }
                         * 
                         */
                        
                        break;
                    case 'TipologiaTitoloStudioRichiesto':
                        if (isset($pattern)) {
                            if (preg_match($pattern,$value)) {
                                $n++;
                                if (!in_array($onecontent['Id'],$foundAr)) {
                                    $onecontent['CentroPerImpiego'] = $this->getCPI($onecontent['CentroPerImpiego'],$cpiAr); 
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['Id']);
                                }
                            }
                        } else {
                            if (stristr($value,$valueToSearch) || stristr($value,$second_value) || $valueToSearch == 'ALL' || $valueToSearch == ''){
                                $n++;
                                if (!in_array($onecontent['Id'],$foundAr)) {
                                    $onecontent['CentroPerImpiego'] = $this->getCPI($onecontent['CentroPerImpiego'],$cpiAr); 
                                    $results[] = $onecontent;
                                    array_push($foundAr, $onecontent['Id']);
                                }
                            }
                        }
                        break;

                }
            }
            if ($searchAND) {
                $contents = $results;
                $results = array();
                $foundAr = array();
            }
        }

        if ($searchAND) {
            $this->results = $contents;
        } else {
            $this->results = $results;
        }
        //print_r(array(count($this->results),count($this->contents)));
}

public function getJsonData() {
    $data = array();
    $results = $this->results;
    $max = count($results);
    for($i=0;$i<$max;$i++) {
            $results[$i]['OLid'] = $i;
            $data[] = $results[$i];
    }
    //$data['numfound'] = $max;
//    print_r(array($max,$data));
    return json_encode($data);
}

public function getCPI($CPI,$cpiAr) {
    foreach ($cpiAr as $oneCPI) {
        if ($oneCPI['idCentro'] == $CPI) {
            return $oneCPI;
        }
    }
    return $CPI;
}

public function load_cpi(){
/* set contents property */
	$XML = $this->XML;
	$labels = $this->labels;
	$elements = $this->elements;
        $element_count = count($elements);
	$content = array(); 
	$contents = array(); 
	$n = 0;
	foreach($XML as $onecontent){
		$n++;
//		$content['ID']	= $n;
		for ($k = 0; $k<$element_count; $k++) {
			$content[$elements[$k]] = (string)$onecontent->$elements[$k];
			//$content[$labels[$k]] = (string)$onecontent->$elements[$k];
		}
		array_push($contents, $content);
	}
	$this->contents = $contents;
}
	

public function show_tabled_data ($summary, $elements,$labelsDesc,$min = 0, $max = 100){
/* set  htmlSource	 property */
	$results = $this->results;
	$labels = $this->labels;
	$n=0;
	$HTML = $summary;
        $HTML .= '<table class=\"course_table\">';
        $HTML .= '<thead><tr>';
        foreach ($labelsDesc as $onelabel) {
            $HTML .= '<td>'.$onelabel .'</td>';
        }
        $HTML .= '</tr></thead>';
        $HTML .= "<tbody>";	
//        print_r($results);
	foreach($results as $onecontent){
            if (($n<=$max) AND ($n>=$min)){
                $HTML .= '<tr>';
                foreach ($elements as $element){
//                                  $parametro = $label; 
                    $parametro = $element;
                    $valore = $onecontent[$parametro];
//                    $HTML .= "<td>".$label."</td>";
                    $HTML .= "<td>".$valore."</td>";
                }
//            $HTML .= "</td>";
              $HTML .= '</tr>';
            }
            $n++;
	}
        $HTML .= "</tbody></table>";	
//	$HTML .= "</table>";	
	$this->htmlSource = $HTML;
}


public function indexData(){

        $index = Array();
        $index_frequency = Array();

      
        $index = explode(" ", $this->allText);

        // Build new frequency array
        foreach($index as $key=>$value){

                // remove everything except letters
                $value = trim($value,"();,.");

                if($value == '' || strlen($value) < INDEX_MIN_CHARS){
                        continue;
                }

                if(array_key_exists($value, $index_frequency)){
                        $index_frequency[$value] = $index_frequency[$value] + 1;
                } else{
                        $index_frequency[$value] = 1;
                }

        }

        return $index_frequency;

}
	
} // end remoteXMLResource	



/* RemoteHTMLResource
 * Useful to gather data from HTML pages
 * 
 */
 
class remoteHTMLResource extends remoteResource {
	var $baseurl = "";
	var $url = "";
	var $resourceType = 'html'; // 'xml' 
	var $htmlSource = "";
	var $dom = "";

public function __construct($baseurl,$url,$tagName,$attrName,$attrValue){

	$this->baseurl = $baseurl;
	$this->url = $url;
	$remote_url = $this->baseurl.$this->url;
	$this->tagName = $tagName;
	$this->attrName = $attrName;
	$this->attrValue = $attrValue;
	$this->dom = new DOMDocument;
	$this->dom->preserveWhiteSpace = false;
	$this->dom->loadHTMLFile($remote_url);
	
}



 
 
public function resetBaseUrl($js){
	
$baseurl = $this->baseurl;
$html = $this->htmlSource;	
if ($js){
	$tempHtml = str_replace("window.open('","window.open('".$baseurl, $html); 
} else {
	$tempHtml = str_replace('<a href="', '<a target="blank" href="'.$baseurl, $html); 
}	
$this->htmlSource = str_replace('src="', 'src="'.$baseurl, $tempHtml);


}



public function getTags(){
    $html = '';
    $tagName = $this->tagName;
	$attrName = $this->attrName;
	$attrValue = $this->attrValue;
	$dom = $this->dom;
	$xpath = new DOMXPath($dom);

	$newDom = new DOMDocument;
	$newDom->formatOutput = true;
   // Esempi di uso si Xpath:
	// example 1: for everything with an id
	//$elements = $xpath->query("//*[@id]");

	// example 2: for node data in a selected id
	//$elements = $xpath->query("/html/body/div[@id='yourTagIdHere']");

	// example 3: same as above with wildcard
	// $elements = $xpath->query("*/div[@id='yourTagIdHere']");


    $filtered = $xpath->query("//$tagName" . '[@' . $attrName . "='$attrValue']");
    // $filtered =  $domxpath->query('//div[@class="className"]');
    // '//' when you don't know 'absolute' path

    // above returns DomNodeList Object
    
    $i = 0;
    
    while( $myItem = $filtered->item($i++) ){
		
		
        $node = $newDom->importNode( $myItem, true );    // import node
        
        $newDom->appendChild($node);                    // append node
    }
    $html = $newDom->saveHTML(); 
    $this->htmlSource = $html;
}




public function search_element_by_class($response,$element_type, $class){

$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
$doc->loadHTML($response);

$xpath = new DOMXPath($doc);
$xpath->formatOutput = true;

$newDom = new DOMDocument;
$newDom->formatOutput = true;
$filtered = $xpath->query("*/".$element_type."[@class='".$class."']");
   $i = 0;
    
    while( $myItem = $filtered->item($i++) ){
		
		
        $node = $newDom->importNode( $myItem, true );    // import node
        $newDom->appendChild($node);                    // append node
    }
    $html = $newDom->saveHTML(); 
     $this->htmlSource = $html;
}

public function search_element_by_id($response,$element_type, $id){

$doc = new DOMDocument;
$doc->loadHTML($response);

$xpath = new DOMXPath($doc);

$newDom = new DOMDocument;
$newDom->formatOutput = true;
$filtered = $xpath->query("*/".$element_type."[@id='".$id."']");
  $i = 0;
    
    while( $myItem = $filtered->item($i++) ){
		
		
        $node = $newDom->importNode( $myItem, true );    // import node
        $newDom->appendChild($node);                    // append node
    }
    $html = $newDom->saveHTML(); 
    $this->htmlSource = $html;
}
	
public function do_post($remote_url, $post_data){

	$data = "";
    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10); 	
    
//Collect Postdata
    foreach($post_data as $key => $val)
    {
        $data .= "--$boundary\n";
        $data .= "Content-Disposition: form-data; name=\"".$key."\"\n\n".$val."\n";
    }
    
    $data .= "--$boundary\n"; 
    
    	
$opts = array(
  'http'=>array(
    //'method'=>"GET",
    'method'=>"POST",
    'header'=>"Accept-language: it\r\n" .
              //"Content-Type: text/html; charset=iso-8859-1\r\n", 
			  "Content-Type: form-data; boundary=".$boundary,
    'content' => $data 
  )
);
// Genero il context stream
$context = stream_context_create($opts);
 
// Effettuo la richiesta utilizzando gli headers HTTP personalizzati
  $fp = fopen($remote_url, 'rb', false, $context);
  
   if (!$fp) {
      throw new Exception("Problem with $remote_url, $php_errormsg");
   }
 
   $response = @stream_get_contents($fp);
   if ($response === false) {
      throw new Exception("Problem reading data from $remote_url, $php_errormsg");
   }
   return $response; 

}
}// end remoteXMLResource	
 
 
 
?>
